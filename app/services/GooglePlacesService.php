<?php
/**
 * Google Places API (New) v1 Service
 * Handles all communication with Google Places API
 */

class GooglePlacesService
{
    private string $apiKey;
    private string $baseUrl = 'https://places.googleapis.com/v1';
    private int $requestsPerMinute;
    private int $timeout = 10;
    private ?int $searchJobId = null;

    // Field masks for cost optimization
    private const TEXT_SEARCH_FIELDS = 'places.id,places.displayName,places.formattedAddress,places.types,places.location';

    private const DETAIL_FIELDS = 'id,displayName,formattedAddress,nationalPhoneNumber,internationalPhoneNumber,websiteUri,googleMapsUri,location,types,rating,userRatingCount,businessStatus';

    private const NEARBY_SEARCH_FIELDS = 'places.id,places.displayName,places.formattedAddress,places.types,places.location';

    public function __construct()
    {
        $setting = new Setting();
        $this->apiKey = $setting->get('google_places_api_key', '') ?: Config::get('GOOGLE_PLACES_API_KEY', '');
        $this->requestsPerMinute = (int) ($setting->get('requests_per_minute', '30') ?: 30);
    }

    public function setSearchJobId(int $id): void
    {
        $this->searchJobId = $id;
    }

    /**
     * Check if API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Text Search — find places matching a text query
     */
    public function searchText(string $query, ?string $pageToken = null, int $maxResultCount = 20): array
    {
        $body = [
            'textQuery' => $query,
            'languageCode' => 'en',
        ];

        if ($pageToken) {
            $body['pageToken'] = $pageToken;
        } else {
            $body['pageSize'] = min($maxResultCount, 20);
        }

        return $this->post(
            '/places:searchText',
            $body,
            self::TEXT_SEARCH_FIELDS
        );
    }

    /**
     * Nearby Search — find places within a radius
     */
    public function searchNearby(float $lat, float $lng, float $radiusMeters, string $type, int $maxResultCount = 20): array
    {
        $body = [
            'includedTypes' => [$type],
            'maxResultCount' => min($maxResultCount, 20),
            'locationRestriction' => [
                'circle' => [
                    'center' => [
                        'latitude' => $lat,
                        'longitude' => $lng,
                    ],
                    'radius' => $radiusMeters,
                ],
            ],
            'languageCode' => 'en',
        ];

        return $this->post(
            '/places:searchNearby',
            $body,
            self::NEARBY_SEARCH_FIELDS
        );
    }

    /**
     * Place Details — get full info for a place
     */
    public function getPlaceDetails(string $placeId): array
    {
        return $this->get(
            '/places/' . $placeId,
            self::DETAIL_FIELDS
        );
    }

    /**
     * Search for businesses (high-level method with pagination)
     */
    public function searchBusinesses(string $query, int $maxResults = 60): array
    {
        $allPlaces = [];
        $pageToken = null;
        $pageCount = 0;
        $maxPages = ceil($maxResults / 20);

        do {
            $this->rateLimit();

            $result = $this->searchText($query, $pageToken, 20);

            if (isset($result['error'])) {
                return $result;
            }

            $places = $result['places'] ?? [];
            $allPlaces = array_merge($allPlaces, $places);

            $pageToken = $result['nextPageToken'] ?? null;
            $pageCount++;

            if (count($allPlaces) >= $maxResults) {
                $allPlaces = array_slice($allPlaces, 0, $maxResults);
                break;
            }

            // Small delay between paginated requests
            if ($pageToken) {
                usleep(200000); // 200ms
            }
        } while ($pageToken && $pageCount < $maxPages);

        return ['places' => $allPlaces, 'total' => count($allPlaces)];
    }

    /**
     * POST request to Google Places API
     */
    private function post(string $endpoint, array $body, string $fieldMask): array
    {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Content-Type: application/json',
            'X-Goog-Api-Key: ' . $this->apiKey,
            'X-Goog-FieldMask: ' . $fieldMask,
        ];

        return $this->makeRequest('POST', $url, $headers, json_encode($body), $endpoint);
    }

    /**
     * GET request to Google Places API
     */
    private function get(string $endpoint, string $fieldMask): array
    {
        $url = $this->baseUrl . $endpoint;

        $headers = [
            'Content-Type: application/json',
            'X-Goog-Api-Key: ' . $this->apiKey,
            'X-Goog-FieldMask: ' . $fieldMask,
        ];

        return $this->makeRequest('GET', $url, $headers, null, $endpoint);
    }

    /**
     * Execute cURL request with retry logic
     */
    private function makeRequest(string $method, string $url, array $headers, ?string $body, string $logEndpoint): array
    {
        $maxRetries = 3;
        $retryDelay = 1; // seconds

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $startTime = microtime(true);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            $elapsed = (int) ((microtime(true) - $startTime) * 1000);

            // Log API usage
            $this->logApiUsage($logEndpoint, $httpCode, $elapsed, $response, $curlError);

            // Handle cURL errors
            if ($response === false) {
                if ($attempt < $maxRetries) {
                    sleep($retryDelay * $attempt); // exponential backoff
                    continue;
                }
                return ['error' => 'Request failed: ' . $curlError, 'http_status' => 0];
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['error' => 'Invalid JSON response', 'http_status' => $httpCode];
            }

            // Success
            if ($httpCode >= 200 && $httpCode < 300) {
                return $data ?? [];
            }

            // Rate limited — retry with backoff
            if ($httpCode === 429) {
                if ($attempt < $maxRetries) {
                    sleep($retryDelay * $attempt * 2);
                    continue;
                }
                return [
                    'error' => 'API rate limit exceeded. Please try again later.',
                    'http_status' => 429,
                ];
            }

            // Quota exceeded
            if ($httpCode === 403) {
                $errorMsg = $data['error']['message'] ?? 'API access denied';
                return ['error' => $errorMsg, 'http_status' => 403];
            }

            // Other errors — retry transient, fail on permanent
            if ($httpCode >= 500 && $attempt < $maxRetries) {
                sleep($retryDelay * $attempt);
                continue;
            }

            $errorMsg = $data['error']['message'] ?? 'API request failed';
            return ['error' => $errorMsg, 'http_status' => $httpCode];
        }

        return ['error' => 'Max retries exceeded', 'http_status' => 0];
    }

    /**
     * Rate limiter
     */
    private function rateLimit(): void
    {
        static $requestTimes = [];

        $now = microtime(true);

        // Remove requests older than 60 seconds
        $requestTimes = array_filter($requestTimes, fn($t) => ($now - $t) < 60);

        if (count($requestTimes) >= $this->requestsPerMinute) {
            $sleepTime = 60 - ($now - $requestTimes[0]);
            if ($sleepTime > 0) {
                usleep((int) ($sleepTime * 1000000));
            }
        }

        $requestTimes[] = microtime(true);
    }

    /**
     * Log API usage to database
     */
    private function logApiUsage(string $endpoint, int $httpCode, int $responseTimeMs, $response, ?string $error): void
    {
        try {
            $result = 'success';
            $errorMsg = null;

            if ($httpCode === 429) {
                $result = 'rate_limited';
                $errorMsg = 'Rate limited';
            } elseif ($httpCode === 403) {
                $result = 'quota_exceeded';
                $data = json_decode($response, true);
                $errorMsg = $data['error']['message'] ?? 'Quota/access error';
            } elseif ($httpCode === 0) {
                $result = 'timeout';
                $errorMsg = $error;
            } elseif ($httpCode >= 400) {
                $result = 'error';
                $data = json_decode($response, true);
                $errorMsg = $data['error']['message'] ?? "HTTP {$httpCode}";
            }

            $apiLog = new ApiLog();
            $apiLog->log([
                'endpoint' => $endpoint,
                'search_job_id' => $this->searchJobId,
                'http_status' => $httpCode,
                'response_time_ms' => $responseTimeMs,
                'request_result' => $result,
                'error_message' => $errorMsg,
            ]);
        } catch (\Throwable $e) {
            error_log('Failed to log API usage: ' . $e->getMessage());
        }
    }

    /**
     * Parse a place from Text Search results into a normalized array
     */
    public function parsePlaceFromSearch(array $place): array
    {
        return [
            'place_id' => $place['id'] ?? '',
            'business_name' => $place['displayName']['text'] ?? 'Unknown',
            'formatted_address' => $place['formattedAddress'] ?? null,
            'types_json' => !empty($place['types']) ? json_encode($place['types']) : null,
            'latitude' => $place['location']['latitude'] ?? null,
            'longitude' => $place['location']['longitude'] ?? null,
        ];
    }

    /**
     * Parse Place Details into a normalized array
     */
    public function parsePlaceDetails(array $place): array
    {
        $types = $place['types'] ?? [];
        $primaryCategory = !empty($types) ? str_replace('_', ' ', ucfirst($types[0])) : null;

        return [
            'place_id' => $place['id'] ?? '',
            'business_name' => $place['displayName']['text'] ?? 'Unknown',
            'primary_category' => $primaryCategory,
            'types_json' => !empty($types) ? json_encode($types) : null,
            'formatted_address' => $place['formattedAddress'] ?? null,
            'national_phone' => $place['nationalPhoneNumber'] ?? null,
            'international_phone' => $place['internationalPhoneNumber'] ?? null,
            'website_url' => $place['websiteUri'] ?? null,
            'google_maps_url' => $place['googleMapsUri'] ?? null,
            'latitude' => $place['location']['latitude'] ?? null,
            'longitude' => $place['location']['longitude'] ?? null,
            'rating' => $place['rating'] ?? null,
            'review_count' => $place['userRatingCount'] ?? null,
            'business_status' => $place['businessStatus'] ?? null,
        ];
    }
}
