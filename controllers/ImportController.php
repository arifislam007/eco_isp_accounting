<?php
declare(strict_types=1);

class ImportController
{
    private ImportModel $importModel;

    public function __construct(?ImportModel $importModel = null)
    {
        $this->importModel = $importModel ?? new ImportModel();
    }

    public function importUploadedFile(array $file, string $entity): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Please upload a valid CSV file.'];
        }

        $path = $file['tmp_name'];
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'Unable to read uploaded file.'];
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return ['success' => false, 'message' => 'CSV file is empty.'];
        }

        $header = array_map(static fn ($value) => strtolower(trim((string) $value)), $header);
        $rowsImported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $record = [];
            foreach ($header as $index => $column) {
                $record[$column] = $row[$index] ?? '';
            }

            $this->importRecord($entity, $record);
            $rowsImported++;
        }

        fclose($handle);

        return ['success' => true, 'message' => $rowsImported . ' rows imported.'];
    }

    private function importRecord(string $entity, array $record): void
    {
        $businessName = trim((string) ($record['business_name'] ?? ''));
        $businessId = (int) ($record['business_id'] ?? 0);

        if ($businessId <= 0 && $businessName !== '') {
            $businessId = $this->importModel->upsertBusiness($businessName);
        }

        match ($entity) {
            'businesses' => $this->importModel->upsertBusiness((string) ($record['name'] ?? $businessName)),
            'collections' => $this->importModel->insertCollection(
                $businessId,
                (int) ($record['total_users'] ?? 0),
                (float) ($record['total_collection'] ?? 0),
                (string) ($record['month'] ?? date('Y-m'))
            ),
            'deposits' => $this->importModel->insertDeposit(
                $businessId,
                (float) ($record['amount'] ?? 0),
                (string) ($record['date'] ?? date('Y-m-d')),
                (string) ($record['type'] ?? 'deposit'),
                (string) ($record['medium'] ?? 'cash'),
                (string) ($record['reference'] ?? '')
            ),
            'costs' => $this->importModel->insertCost(
                (string) ($record['type'] ?? 'others'),
                (float) ($record['amount'] ?? 0),
                (string) ($record['month'] ?? date('Y-m'))
            ),
            'commissions' => $this->importModel->insertCommission(
                $businessId,
                (float) ($record['percentage'] ?? 0)
            ),
            'bonuses' => $this->importModel->insertBonus(
                $businessId,
                (float) ($record['percentage'] ?? 0)
            ),
            'discounts' => $this->importModel->insertDiscount(
                $businessId,
                (float) ($record['amount'] ?? 0),
                (string) ($record['month'] ?? date('Y-m'))
            ),
            default => throw new InvalidArgumentException('Unsupported entity: ' . $entity),
        };
    }
}
