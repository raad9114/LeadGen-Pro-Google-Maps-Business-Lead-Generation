<?php
/**
 * Search Service
 * Orchestrates the full lead search workflow
 */

class SearchService
{
    private GooglePlacesService $placesService;
    private Lead $leadModel;
    private SearchJob $searchJobModel;
    private EmailEnrichmentService $emailService;

    public function __construct()
    {
        $this->placesService = new GooglePlacesService();
        $this->leadModel = new Lead();
        $this->searchJobModel = new SearchJob();
        $this->emailService = new EmailEnrichmentService();
    }

    /**
     * Start a new search
     */
    public function startSearch(array $params): array
    {
        // Validate API key
        if (!$this->placesService->isConfigured()) {
            return ['error' => 'Google Places API key is not configured. Please set it in Settings.'];
        }

        // Build search query
        $query = $this->buildQuery($params);

        // Create search job
        $jobId = $this->searchJobModel->create([
            'user_id' => $params['user_id'],
            'category_name' => $params['category'],
            'country_name' => $params['country'] ?? null,
            'city_name' => $params['city'] ?? null,
            'area_name' => $params['area'] ?? null,
            'search_query' => $query,
            'search_mode' => $params['search_mode'] ?? 'text',
            'latitude' => $params['latitude'] ?? null,
            'longitude' => $params['longitude'] ?? null,
            'radius_km' => $params['radius_km'] ?? null,
            'max_results' => $params['max_results'] ?? 60,
        ]);

        $this->placesService->setSearchJobId($jobId);

        return ['job_id' => $jobId, 'query' => $query];
    }

    /**
     * Execute search (called step by step for progress updates)
     */
    public function executeSearch(int $jobId): array
    {
        $job = $this->searchJobModel->findById($jobId);
        if (!$job) {
            return ['error' => 'Search job not found'];
        }

        $this->placesService->setSearchJobId($jobId);

        $stats = [
            'total_found' => 0,
            'new_leads' => 0,
            'duplicates' => 0,
            'emails_found' => 0,
            'details_fetched' => 0,
        ];

        try {
            // Phase 1: Search for places
            $this->searchJobModel->updateStatus($jobId, 'searching');

            $searchResult = null;
            if ($job['search_mode'] === 'radius' && $job['latitude'] && $job['longitude']) {
                // Radius search
                $radiusMeters = ($job['radius_km'] ?? 5) * 1000;
                $categorySlug = strtolower(str_replace(' ', '_', $job['category_name']));
                $searchResult = $this->placesService->searchNearby(
                    (float) $job['latitude'],
                    (float) $job['longitude'],
                    $radiusMeters,
                    $categorySlug,
                    (int) $job['max_results']
                );
            } else {
                // Text search
                $searchResult = $this->placesService->searchBusinesses(
                    $job['search_query'],
                    (int) $job['max_results']
                );
            }

            if (isset($searchResult['error'])) {
                $this->searchJobModel->updateStatus($jobId, 'failed', [
                    'error_message' => $searchResult['error'],
                ]);
                return $searchResult;
            }

            $places = $searchResult['places'] ?? [];
            $stats['total_found'] = count($places);

            if (empty($places)) {
                $this->searchJobModel->updateStatus($jobId, 'completed', $stats);
                return ['success' => true, 'stats' => $stats, 'message' => 'No businesses found for this search.'];
            }

            // Phase 2: Fetch details for each place
            $this->searchJobModel->updateStatus($jobId, 'fetching_details', $stats);

            $setting = new Setting();
            $enableEmail = (bool) $setting->get('enable_email_enrichment', '1');
            $autoEmail = (bool) $setting->get('auto_search_emails', '1');
            $leadsWithWebsite = [];

            foreach ($places as $place) {
                $placeId = $place['id'] ?? null;
                if (empty($placeId)) continue;

                // Rate limiting between detail requests
                usleep(100000); // 100ms

                // Fetch full details
                $details = $this->placesService->getPlaceDetails($placeId);

                if (isset($details['error'])) {
                    error_log("Failed to get details for {$placeId}: " . $details['error']);
                    continue;
                }

                $stats['details_fetched']++;

                // Parse details
                $leadData = $this->placesService->parsePlaceDetails($details);
                $leadData['search_category'] = $job['category_name'];
                $leadData['search_country'] = $job['country_name'];
                $leadData['search_city'] = $job['city_name'];
                $leadData['search_area'] = $job['area_name'];
                $leadData['search_query'] = $job['search_query'];
                $leadData['search_job_id'] = $jobId;

                // Upsert lead (handles dedup)
                $result = $this->leadModel->upsert($leadData);

                if ($result['is_new']) {
                    $stats['new_leads']++;

                    // Log activity
                    $activity = new LeadActivity();
                    $activity->log($result['id'], $job['user_id'], 'created', 'Found via search: ' . $job['search_query']);
                } else {
                    $stats['duplicates']++;
                }

                // Track leads with websites for email enrichment
                if (!empty($leadData['website_url'])) {
                    $leadsWithWebsite[] = [
                        'id' => $result['id'],
                        'website' => $leadData['website_url'],
                    ];
                }

                // Update running stats
                $this->searchJobModel->updateStatus($jobId, 'fetching_details', $stats);
            }

            // Phase 3: Email enrichment
            if ($enableEmail && $autoEmail && !empty($leadsWithWebsite)) {
                $this->searchJobModel->updateStatus($jobId, 'finding_emails', $stats);

                foreach ($leadsWithWebsite as $leadInfo) {
                    // Check if lead already has an email
                    $lead = $this->leadModel->findById($leadInfo['id']);
                    if ($lead && !empty($lead['email'])) {
                        $stats['emails_found']++;
                        continue;
                    }

                    $emailResult = $this->emailService->findEmails($leadInfo['id'], $leadInfo['website']);

                    if ($emailResult['status'] === 'found' && !empty($emailResult['emails'])) {
                        $stats['emails_found']++;
                    }

                    // Update running stats
                    $this->searchJobModel->updateStatus($jobId, 'finding_emails', $stats);
                }
            }

            // Complete
            $this->searchJobModel->updateStatus($jobId, 'completed', $stats);

            return ['success' => true, 'stats' => $stats];
        } catch (\Throwable $e) {
            error_log('Search execution error: ' . $e->getMessage());
            $this->searchJobModel->updateStatus($jobId, 'failed', [
                'error_message' => 'Search failed: ' . $e->getMessage(),
            ]);
            return ['error' => 'Search failed: ' . $e->getMessage()];
        }
    }

    /**
     * Build search query string
     */
    private function buildQuery(array $params): string
    {
        $parts = [$params['category']];

        if (!empty($params['custom_location'])) {
            $parts[] = 'in ' . $params['custom_location'];
        } else {
            $location = [];
            if (!empty($params['area'])) $location[] = $params['area'];
            if (!empty($params['city'])) $location[] = $params['city'];
            if (!empty($params['country'])) $location[] = $params['country'];

            if (!empty($location)) {
                $parts[] = 'in ' . implode(', ', $location);
            }
        }

        return implode(' ', $parts);
    }
}
