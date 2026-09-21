<?php
/**
 * Export Controller
 */

class ExportController
{
    public function index(): void
    {
        Session::requireAuth();
        $pageTitle = 'Exports';
        $currentPage = 'exports';

        include TEMPLATE_PATH . '/layouts/app.php';
    }

    public function csv(): void
    {
        Session::requireAuth();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $leads = $this->getLeadsForExport($data);

        $exportService = new ExportService();
        $filename = 'leads_' . date('Y-m-d_His') . '.csv';
        $exportService->exportCsv($leads, $filename);
    }

    public function xlsx(): void
    {
        Session::requireAuth();
        CSRF::validateOrDie();

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $leads = $this->getLeadsForExport($data);

        $exportService = new ExportService();
        $filename = 'leads_' . date('Y-m-d_His') . '.xlsx';
        $exportService->exportXlsx($leads, $filename);
    }

    private function getLeadsForExport(array $data): array
    {
        $leadModel = new Lead();
        $ids = $data['ids'] ?? [];
        $filters = $data['filters'] ?? [];

        return $leadModel->getForExport(
            array_map('intval', $ids),
            $filters
        );
    }
}
