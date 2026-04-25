<?php
declare(strict_types=1);

class ManualEntryController
{
    private ImportModel $importModel;

    public function __construct(?ImportModel $importModel = null)
    {
        $this->importModel = $importModel ?? new ImportModel();
    }

    public function save(array $payload): array
    {
        $entryType = (string) ($payload['entry_type'] ?? '');

        return match ($entryType) {
            'billing' => $this->saveBillingEntry($payload),
            'billing_update' => $this->updateBillingEntry($payload),
            'deposit' => $this->saveDepositEntry($payload),
            'deposit_update' => $this->updateDepositEntry($payload),
            'cost' => $this->saveCostEntry($payload),
            'cost_update' => $this->updateCostEntry($payload),
            default => ['success' => false, 'message' => 'Invalid manual entry type.'],
        };
    }

    private function saveBillingEntry(array $payload): array
    {
        $businessId = $this->resolveBusinessId($payload);
        if ($businessId <= 0) {
            return ['success' => false, 'message' => 'Please select a business or provide a new business name.'];
        }

        $month = (string) ($payload['month'] ?? date('Y-m'));
        $users = max(0, (int) ($payload['total_users'] ?? 0));
        $collection = max(0.0, (float) ($payload['total_collection'] ?? 0));
        $commission = max(0.0, (float) ($payload['commission_percentage'] ?? 0));
        $bonus = max(0.0, (float) ($payload['bonus_percentage'] ?? 0));
        $discount = max(0.0, (float) ($payload['discount_amount'] ?? 0));

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $this->importModel->insertCollection($businessId, $users, $collection, $month);
            $this->importModel->insertCommission($businessId, $commission);
            $this->importModel->insertBonus($businessId, $bonus);

            if ($discount > 0) {
                $this->importModel->insertDiscount($businessId, $discount, $month);
            }

            $pdo->commit();
            return ['success' => true, 'message' => 'Manual billing entry saved successfully.'];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return ['success' => false, 'message' => 'Failed to save billing entry: ' . $e->getMessage()];
        }
    }

    private function saveDepositEntry(array $payload): array
    {
        $businessId = $this->resolveBusinessId($payload);
        if ($businessId <= 0) {
            return ['success' => false, 'message' => 'Please select a business or provide a new business name.'];
        }

        $amount = max(0.0, (float) ($payload['amount'] ?? 0));
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Deposit amount must be greater than zero.'];
        }

        $date = (string) ($payload['date'] ?? date('Y-m-d'));
        $type = trim((string) ($payload['type'] ?? 'deposit')) ?: 'deposit';
        $medium = trim((string) ($payload['medium'] ?? 'cash')) ?: 'cash';
        $reference = trim((string) ($payload['reference'] ?? ''));

        try {
            $this->importModel->insertDeposit($businessId, $amount, $date, $type, $medium, $reference);
            return ['success' => true, 'message' => 'Deposit entry saved successfully.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Failed to save deposit entry: ' . $e->getMessage()];
        }
    }

    private function saveCostEntry(array $payload): array
    {
        $type = trim((string) ($payload['cost_type'] ?? ''));
        $month = (string) ($payload['cost_month'] ?? date('Y-m'));
        $amount = max(0.0, (float) ($payload['cost_amount'] ?? 0));

        if ($type === '') {
            return ['success' => false, 'message' => 'Cost type is required.'];
        }

        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Cost amount must be greater than zero.'];
        }

        try {
            $this->importModel->insertCost($type, $amount, $month);
            return ['success' => true, 'message' => 'Cost entry saved successfully.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Failed to save cost entry: ' . $e->getMessage()];
        }
    }

    private function updateBillingEntry(array $payload): array
    {
        $businessId = (int) ($payload['business_id'] ?? 0);
        if ($businessId <= 0) {
            return ['success' => false, 'message' => 'Invalid business for billing update.'];
        }

        $month = (string) ($payload['month'] ?? date('Y-m'));
        $users = max(0, (int) ($payload['total_users'] ?? 0));
        $collection = max(0.0, (float) ($payload['total_collection'] ?? 0));
        $commission = max(0.0, (float) ($payload['commission_percentage'] ?? 0));
        $bonus = max(0.0, (float) ($payload['bonus_percentage'] ?? 0));
        $discount = max(0.0, (float) ($payload['discount_amount'] ?? 0));

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $this->importModel->insertCollection($businessId, $users, $collection, $month);
            $this->importModel->insertCommission($businessId, $commission);
            $this->importModel->insertBonus($businessId, $bonus);
            $this->importModel->replaceMonthlyDiscount($businessId, $month, $discount);
            $pdo->commit();

            return ['success' => true, 'message' => 'Billing entry updated successfully.'];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            return ['success' => false, 'message' => 'Failed to update billing entry: ' . $e->getMessage()];
        }
    }

    private function updateCostEntry(array $payload): array
    {
        $costId = (int) ($payload['cost_id'] ?? 0);
        if ($costId <= 0) {
            return ['success' => false, 'message' => 'Invalid cost ID.'];
        }

        $type = trim((string) ($payload['cost_type'] ?? ''));
        $month = (string) ($payload['cost_month'] ?? date('Y-m'));
        $amount = max(0.0, (float) ($payload['cost_amount'] ?? 0));

        if ($type === '') {
            return ['success' => false, 'message' => 'Cost type is required.'];
        }

        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Cost amount must be greater than zero.'];
        }

        try {
            $this->importModel->updateCost($costId, $type, $amount, $month);
            return ['success' => true, 'message' => 'Cost entry updated successfully.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Failed to update cost entry: ' . $e->getMessage()];
        }
    }

    private function updateDepositEntry(array $payload): array
    {
        $depositId = (int) ($payload['deposit_id'] ?? 0);
        if ($depositId <= 0) {
            return ['success' => false, 'message' => 'Invalid deposit ID.'];
        }

        $businessId = $this->resolveBusinessId($payload);
        if ($businessId <= 0) {
            return ['success' => false, 'message' => 'Please select a business or provide a new business name.'];
        }

        $amount = max(0.0, (float) ($payload['amount'] ?? 0));
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Deposit amount must be greater than zero.'];
        }

        $date = (string) ($payload['date'] ?? date('Y-m-d'));
        $type = trim((string) ($payload['type'] ?? 'deposit')) ?: 'deposit';
        $medium = trim((string) ($payload['medium'] ?? 'cash')) ?: 'cash';
        $reference = trim((string) ($payload['reference'] ?? ''));

        try {
            $this->importModel->updateDeposit($depositId, $businessId, $amount, $date, $type, $medium, $reference);
            return ['success' => true, 'message' => 'Deposit entry updated successfully.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Failed to update deposit entry: ' . $e->getMessage()];
        }
    }

    private function resolveBusinessId(array $payload): int
    {
        $businessId = (int) ($payload['business_id'] ?? 0);
        if ($businessId > 0) {
            return $businessId;
        }

        $newBusinessName = trim((string) ($payload['new_business_name'] ?? ''));
        if ($newBusinessName === '') {
            return 0;
        }

        return $this->importModel->upsertBusiness($newBusinessName);
    }
}
