<?php
/**
 * Lead Controller
 */

class LeadController
{
    public function index(): void
    {
        Session::requireAuth();

        $pageTitle = 'All Leads';
        $currentPage = 'leads';

        include TEMPLATE_PATH . '/layouts/app.php';
    }

    public function list(): void
    {
        Session::requireAuth();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($_GET['per_page'] ?? 25)));

        $filters = [
            'search' => $_GET['search'] ?? '',
            'category' => $_GET['category'] ?? '',
            'country' => $_GET['country'] ?? '',
            'city' => $_GET['city'] ?? '',
            'area' => $_GET['area'] ?? '',
            'lead_status' => $_GET['lead_status'] ?? '',
            'has_phone' => $_GET['has_phone'] ?? '',
            'no_phone' => $_GET['no_phone'] ?? '',
            'has_email' => $_GET['has_email'] ?? '',
            'no_email' => $_GET['no_email'] ?? '',
            'has_website' => $_GET['has_website'] ?? '',
            'no_website' => $_GET['no_website'] ?? '',
            'min_rating' => $_GET['min_rating'] ?? '',
            'min_reviews' => $_GET['min_reviews'] ?? '',
            'search_job_id' => $_GET['search_job_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        // Remove empty filters
        $filters = array_filter($filters, fn($v) => $v !== '');

        $leadModel = new Lead();
        $result = $leadModel->list($filters, $page, $perPage);

        Response::paginated($result['data'], $result['total'], $page, $perPage);
    }

    public function show(string $id): void
    {
        Session::requireAuth();

        $leadModel = new Lead();
        $lead = $leadModel->findById((int) $id);

        if (!$lead) {
            Response::error('Lead not found', 404);
            return;
        }

        $leadEmail = new LeadEmail();
        $leadNote = new LeadNote();
        $leadActivity = new LeadActivity();

        $lead['emails'] = $leadEmail->getByLeadId((int) $id);
        $lead['notes'] = $leadNote->getByLeadId((int) $id);
        $lead['activities'] = $leadActivity->getByLeadId((int) $id);

        Response::success($lead);
    }

    public function detail(string $id): void
    {
        Session::requireAuth();

        $leadModel = new Lead();
        $lead = $leadModel->findById((int) $id);

        if (!$lead) {
            http_response_code(404);
            $pageTitle = 'Lead Not Found';
            include TEMPLATE_PATH . '/pages/404.php';
            return;
        }

        $leadEmail = new LeadEmail();
        $leadNote = new LeadNote();
        $leadActivity = new LeadActivity();

        $emails = $leadEmail->getByLeadId((int) $id);
        $notes = $leadNote->getByLeadId((int) $id);
        $activities = $leadActivity->getByLeadId((int) $id);

        $pageTitle = Validator::e($lead['business_name']);
        $currentPage = 'leads_detail';

        include TEMPLATE_PATH . '/layouts/app.php';
    }

    public function update(string $id): void
    {
        Session::requireAuth();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $leadModel = new Lead();
        $lead = $leadModel->findById((int) $id);

        if (!$lead) {
            Response::error('Lead not found', 404);
            return;
        }

        $leadModel->update((int) $id, $data);

        // Log activity
        $activity = new LeadActivity();
        if (isset($data['lead_status']) && $data['lead_status'] !== $lead['lead_status']) {
            $activity->log((int) $id, Session::userId(), 'status_changed',
                "Status changed from {$lead['lead_status']} to {$data['lead_status']}");
        } else {
            $activity->log((int) $id, Session::userId(), 'updated', 'Lead information updated');
        }

        Response::success(null, 'Lead updated successfully');
    }

    public function delete(string $id): void
    {
        Session::requireAuth();
        CSRF::validateOrDie();

        $leadModel = new Lead();
        $leadModel->delete((int) $id);

        Response::success(null, 'Lead deleted successfully');
    }

    public function findEmail(string $id): void
    {
        Session::requireAuth();
        CSRF::validateOrDie();

        $leadModel = new Lead();
        $lead = $leadModel->findById((int) $id);

        if (!$lead) {
            Response::error('Lead not found', 404);
            return;
        }

        if (empty($lead['website_url'])) {
            Response::error('This lead has no website URL. Email enrichment requires a website.');
            return;
        }

        $emailService = new EmailEnrichmentService();
        $result = $emailService->findEmails((int) $id, $lead['website_url']);

        $activity = new LeadActivity();
        if ($result['status'] === 'found') {
            $emailCount = count($result['emails']);
            $activity->log((int) $id, Session::userId(), 'email_found', "Found {$emailCount} email(s)");
            Response::success($result, "Found {$emailCount} email address(es)");
        } else {
            $activity->log((int) $id, Session::userId(), 'email_search', "Email search: {$result['status']}");
            Response::success($result, 'No email addresses found on the website');
        }
    }

    public function refresh(string $id): void
    {
        Session::requireAuth();
        CSRF::validateOrDie();

        $leadModel = new Lead();
        $lead = $leadModel->findById((int) $id);

        if (!$lead) {
            Response::error('Lead not found', 404);
            return;
        }

        $placesService = new GooglePlacesService();
        if (!$placesService->isConfigured()) {
            Response::error('Google Places API key is not configured');
            return;
        }

        $details = $placesService->getPlaceDetails($lead['place_id']);

        if (isset($details['error'])) {
            Response::error('Failed to refresh: ' . $details['error']);
            return;
        }

        $parsed = $placesService->parsePlaceDetails($details);
        $leadModel->upsert(array_merge($parsed, [
            'search_category' => $lead['search_category'],
            'search_country' => $lead['search_country'],
            'search_city' => $lead['search_city'],
            'search_area' => $lead['search_area'],
        ]));

        $activity = new LeadActivity();
        $activity->log((int) $id, Session::userId(), 'refreshed', 'Business details refreshed from Google');

        Response::success(null, 'Lead refreshed successfully');
    }

    public function bulkStatus(): void
    {
        Session::requireAuth();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $ids = $data['ids'] ?? [];
        $status = $data['status'] ?? '';

        if (empty($ids) || empty($status)) {
            Response::error('Please select leads and a status');
            return;
        }

        $ids = array_map('intval', $ids);
        $leadModel = new Lead();
        $count = $leadModel->bulkUpdateStatus($ids, $status);

        Response::success(['updated' => $count], "Updated {$count} leads");
    }

    public function bulkEmail(): void
    {
        Session::requireAuth();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $ids = $data['ids'] ?? [];

        if (empty($ids)) {
            Response::error('Please select leads');
            return;
        }

        $leadModel = new Lead();
        $emailService = new EmailEnrichmentService();
        $found = 0;
        $processed = 0;

        foreach ($ids as $id) {
            $lead = $leadModel->findById((int) $id);
            if (!$lead || empty($lead['website_url'])) continue;
            if (!empty($lead['email'])) continue; // Skip if already has email

            $result = $emailService->findEmails((int) $id, $lead['website_url']);
            $processed++;

            if ($result['status'] === 'found') {
                $found++;
            }
        }

        Response::success([
            'processed' => $processed,
            'found' => $found,
        ], "Processed {$processed} leads, found emails for {$found}");
    }

    public function bulkDelete(): void
    {
        Session::requireAuth();
        Session::requireAdmin();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $ids = $data['ids'] ?? [];

        if (empty($ids)) {
            Response::error('Please select leads to delete');
            return;
        }

        $ids = array_map('intval', $ids);
        $leadModel = new Lead();
        $count = $leadModel->bulkDelete($ids);

        Response::success(['deleted' => $count], "Deleted {$count} leads");
    }

    public function addNote(string $id): void
    {
        Session::requireAuth();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $note = trim($data['note'] ?? '');

        if (empty($note)) {
            Response::error('Note text is required');
            return;
        }

        $leadNoteModel = new LeadNote();
        $noteId = $leadNoteModel->create((int) $id, Session::userId(), $note);

        $activity = new LeadActivity();
        $activity->log((int) $id, Session::userId(), 'note_added', 'Added a note');

        Response::success(['note_id' => $noteId], 'Note added successfully');
    }

    public function deleteNote(string $id): void
    {
        Session::requireAuth();
        CSRF::validateOrDie();

        $leadNoteModel = new LeadNote();
        $leadNoteModel->delete((int) $id);

        Response::success(null, 'Note deleted');
    }
}
