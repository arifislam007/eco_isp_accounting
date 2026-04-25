<?php
declare(strict_types=1);

class BusinessController
{
    private ReportModel $reportModel;

    public function __construct(?ReportModel $reportModel = null)
    {
        $this->reportModel = $reportModel ?? new ReportModel();
    }

    public function list(string $month): array
    {
        return $this->reportModel->getBusinessList($month);
    }

    public function details(int $businessId, string $month): array
    {
        return $this->reportModel->getBusinessDetails($businessId, $month);
    }
}
