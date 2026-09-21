<?php
/**
 * Email Enrichment Service
 * Crawls public business websites to find contact email addresses
 */

class EmailEnrichmentService
{
    private int $maxPages = 5;
    private int $timeout = 5;
    private float $requestDelay = 1.0; // seconds between requests

    // Priority contact page paths
    private const CONTACT_PATHS = [
        '',           // Homepage
        '/contact',
        '/contact-us',
        '/about',
        '/about-us',
    ];

    // Email patterns to ignore
    private const IGNORE_PATTERNS = [
        'example.com',
        'example.org',
        'test.com',
        'yourcompany.com',
        'yourdomain.com',
        'email.com',
        'noreply',
        'no-reply',
        'mailer-daemon',
        'wixpress.com',
        'sentry.io',
        'wordpress.com',
        'wpengine.com',
    ];

    // Image/file extensions to ignore in email-like strings
    private const FILE_EXTENSIONS = [
        '.png', '.jpg', '.jpeg', '.gif', '.svg', '.webp', '.bmp',
        '.css', '.js', '.woff', '.woff2', '.ttf', '.eot',
    ];

    public function __construct()
    {
        $setting = new Setting();
        $this->maxPages = (int) ($setting->get('max_pages_to_crawl', '5') ?: 5);
        $this->timeout = (int) ($setting->get('crawl_timeout_seconds', '5') ?: 5);
    }

    /**
     * Find email addresses for a lead's website
     */
    public function findEmails(int $leadId, string $websiteUrl): array
    {
        $result = [
            'emails' => [],
            'pages_crawled' => 0,
            'status' => 'not_found',
        ];

        $websiteUrl = $this->normalizeUrl($websiteUrl);
        if (empty($websiteUrl)) {
            $result['status'] = 'website_unreachable';
            return $result;
        }

        $baseDomain = $this->getDomain($websiteUrl);
        if (empty($baseDomain)) {
            $result['status'] = 'website_unreachable';
            return $result;
        }

        // Check robots.txt first
        if (!$this->isAllowedByRobots($websiteUrl)) {
            $result['status'] = 'blocked';
            $this->logCrawl($leadId, $websiteUrl, 0, 0, 'Blocked by robots.txt');
            return $result;
        }

        $allEmails = [];
        $crawledUrls = [];
        $pagesCrawled = 0;

        // Build list of URLs to check
        $urlsToCheck = [];
        foreach (self::CONTACT_PATHS as $path) {
            $url = rtrim($websiteUrl, '/') . $path;
            $urlsToCheck[] = $url;
        }

        // Also look for contact links from homepage
        $homepageHtml = $this->fetchPage($websiteUrl);
        if ($homepageHtml !== null) {
            $pagesCrawled++;
            $crawledUrls[] = $websiteUrl;

            // Extract emails from homepage
            $homeEmails = $this->extractEmails($homepageHtml, $baseDomain);
            $allEmails = array_merge($allEmails, $homeEmails);

            // Find contact page links
            $contactLinks = $this->extractContactLinks($homepageHtml, $websiteUrl, $baseDomain);
            $urlsToCheck = array_merge($urlsToCheck, $contactLinks);

            $this->logCrawl($leadId, $websiteUrl, 200, count($homeEmails));
        } else {
            $this->logCrawl($leadId, $websiteUrl, 0, 0, 'Website unreachable');
            $result['status'] = 'website_unreachable';
            return $result;
        }

        // Remove duplicates and already-crawled URLs
        $urlsToCheck = array_unique($urlsToCheck);

        // Crawl remaining pages
        foreach ($urlsToCheck as $url) {
            if ($pagesCrawled >= $this->maxPages) break;
            if (in_array($url, $crawledUrls)) continue;
            if (!$this->isSameDomain($url, $baseDomain)) continue;

            usleep((int) ($this->requestDelay * 1000000));

            $html = $this->fetchPage($url);
            $pagesCrawled++;
            $crawledUrls[] = $url;

            if ($html !== null) {
                $pageEmails = $this->extractEmails($html, $baseDomain);
                $allEmails = array_merge($allEmails, $pageEmails);
                $this->logCrawl($leadId, $url, 200, count($pageEmails));
            } else {
                $this->logCrawl($leadId, $url, 0, 0, 'Page unreachable');
            }
        }

        // Deduplicate and rank emails
        $uniqueEmails = $this->rankEmails($allEmails, $baseDomain);

        // Store emails
        $leadEmail = new LeadEmail();
        $isPrimary = true;
        foreach ($uniqueEmails as $emailData) {
            $leadEmail->create($leadId, $emailData['email'], $emailData['source'], $isPrimary);
            $isPrimary = false; // Only first is primary
        }

        // Update lead's primary email
        if (!empty($uniqueEmails)) {
            $lead = new Lead();
            $lead->update($leadId, [
                'email' => $uniqueEmails[0]['email'],
                'email_source_url' => $uniqueEmails[0]['source'],
            ]);
        }

        $result['emails'] = $uniqueEmails;
        $result['pages_crawled'] = $pagesCrawled;
        $result['status'] = !empty($uniqueEmails) ? 'found' : 'not_found';

        return $result;
    }

    /**
     * Fetch a web page via cURL
     */
    private function fetchPage(string $url): ?string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; LeadGenBot/1.0; +http://leadgen.local)',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml',
                'Accept-Language: en-US,en;q=0.5',
            ],
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($html === false || $httpCode >= 400) {
            return null;
        }

        return $html;
    }

    /**
     * Extract email addresses from HTML content
     */
    public function extractEmails(string $html, string $baseDomain): array
    {
        $emails = [];

        // Method 1: Extract from mailto: links
        preg_match_all('/mailto:([a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})/i', $html, $mailtoMatches);
        foreach ($mailtoMatches[1] as $email) {
            $email = strtolower(trim($email));
            if ($this->isValidBusinessEmail($email)) {
                $emails[] = ['email' => $email, 'source' => 'mailto'];
            }
        }

        // Method 2: Regex for visible email patterns in text
        // Strip HTML tags first for cleaner matching
        $text = strip_tags($html);
        preg_match_all('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $text, $textMatches);
        foreach ($textMatches[0] as $email) {
            $email = strtolower(trim($email));
            if ($this->isValidBusinessEmail($email)) {
                $emails[] = ['email' => $email, 'source' => 'text'];
            }
        }

        // Method 3: Check HTML attributes (href, data-email, etc.)
        preg_match_all('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $html, $htmlMatches);
        foreach ($htmlMatches[0] as $email) {
            $email = strtolower(trim($email));
            if ($this->isValidBusinessEmail($email)) {
                $emails[] = ['email' => $email, 'source' => 'html'];
            }
        }

        return $emails;
    }

    /**
     * Extract links that likely lead to contact/about pages
     */
    public function extractContactLinks(string $html, string $baseUrl, string $baseDomain): array
    {
        $links = [];
        $contactKeywords = ['contact', 'about', 'reach', 'get-in-touch', 'connect', 'locate'];

        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);

        foreach ($matches[1] as $href) {
            $href = trim($href);

            // Skip anchors, javascript, etc.
            if (str_starts_with($href, '#') || str_starts_with($href, 'javascript:') ||
                str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                continue;
            }

            // Convert relative to absolute
            if (!str_starts_with($href, 'http')) {
                $href = rtrim($baseUrl, '/') . '/' . ltrim($href, '/');
            }

            // Must be same domain
            if (!$this->isSameDomain($href, $baseDomain)) continue;

            // Check if URL contains contact keywords
            $hrefLower = strtolower($href);
            foreach ($contactKeywords as $keyword) {
                if (str_contains($hrefLower, $keyword)) {
                    $links[] = $href;
                    break;
                }
            }
        }

        return array_unique($links);
    }

    /**
     * Validate if an email looks like a legitimate business email
     */
    private function isValidBusinessEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Check against ignore patterns
        foreach (self::IGNORE_PATTERNS as $pattern) {
            if (str_contains($email, $pattern)) {
                return false;
            }
        }

        // Check against file extensions (catches things like image@2x.png)
        foreach (self::FILE_EXTENSIONS as $ext) {
            if (str_ends_with($email, $ext)) {
                return false;
            }
        }

        // Must have a reasonable TLD
        $parts = explode('@', $email);
        $domain = $parts[1] ?? '';
        $tld = substr($domain, strrpos($domain, '.') + 1);

        if (strlen($tld) < 2 || strlen($tld) > 10) {
            return false;
        }

        return true;
    }

    /**
     * Rank and deduplicate emails, preferring same-domain and common business prefixes
     */
    private function rankEmails(array $emails, string $baseDomain): array
    {
        $unique = [];
        $seen = [];

        foreach ($emails as $emailData) {
            $email = $emailData['email'];
            if (isset($seen[$email])) continue;
            $seen[$email] = true;

            $score = 0;

            // Prefer emails on the business's own domain
            $emailDomain = substr($email, strpos($email, '@') + 1);
            if (str_contains($baseDomain, $emailDomain) || str_contains($emailDomain, $baseDomain)) {
                $score += 100;
            }

            // Prefer common business prefixes
            $prefix = strtolower(substr($email, 0, strpos($email, '@')));
            $preferredPrefixes = ['info', 'contact', 'hello', 'sales', 'booking', 'support', 'admin', 'office', 'enquiry', 'inquiry', 'mail'];
            if (in_array($prefix, $preferredPrefixes)) {
                $score += 50;
            }

            // Prefer mailto: source
            if ($emailData['source'] === 'mailto') {
                $score += 20;
            }

            $unique[] = [
                'email' => $email,
                'source' => $emailData['source'],
                'score' => $score,
            ];
        }

        // Sort by score descending
        usort($unique, fn($a, $b) => $b['score'] - $a['score']);

        // Return max 5 emails
        return array_slice($unique, 0, 5);
    }

    /**
     * Normalize URL
     */
    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if (empty($url)) return '';

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        return rtrim($url, '/');
    }

    /**
     * Get domain from URL
     */
    private function getDomain(string $url): string
    {
        $parsed = parse_url($url);
        return $parsed['host'] ?? '';
    }

    /**
     * Check if URL is on same domain
     */
    private function isSameDomain(string $url, string $baseDomain): bool
    {
        $urlDomain = $this->getDomain($url);
        return !empty($urlDomain) && (
            $urlDomain === $baseDomain ||
            str_ends_with($urlDomain, '.' . $baseDomain)
        );
    }

    /**
     * Simple robots.txt check
     */
    private function isAllowedByRobots(string $url): bool
    {
        $parsed = parse_url($url);
        $robotsUrl = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . '/robots.txt';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $robotsUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'LeadGenBot/1.0',
        ]);

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // If robots.txt doesn't exist or can't be fetched, allow
        if ($content === false || $httpCode !== 200) {
            return true;
        }

        // Simple check: if "Disallow: /" for all user agents, block
        $lines = explode("\n", $content);
        $isGlobalAgent = false;
        foreach ($lines as $line) {
            $line = trim($line);
            if (strtolower($line) === 'user-agent: *') {
                $isGlobalAgent = true;
            } elseif (str_starts_with(strtolower($line), 'user-agent:')) {
                $isGlobalAgent = false;
            } elseif ($isGlobalAgent && strtolower(trim($line)) === 'disallow: /') {
                return false;
            }
        }

        return true;
    }

    /**
     * Log crawl attempt
     */
    private function logCrawl(int $leadId, string $url, int $httpStatus, int $emailsFound, ?string $error = null): void
    {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                'INSERT INTO website_crawl_logs (lead_id, url, http_status, emails_found, error_message) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$leadId, $url, $httpStatus, $emailsFound, $error]);
        } catch (\Throwable $e) {
            error_log('Failed to log crawl: ' . $e->getMessage());
        }
    }
}
