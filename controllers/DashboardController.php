<?php
declare(strict_types=1);

class DashboardController
{
    private ReportModel $reportModel;

    public function __construct(?ReportModel $reportModel = null)
    {
        $this->reportModel = $reportModel ?? new ReportModel();
    }

    public function index(string $month): array
    {
        return $this->reportModel->getDashboardData($month);
    }
}
