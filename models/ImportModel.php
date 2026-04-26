<?php
declare(strict_types=1);

class ImportModel extends BaseModel
{
    public function upsertBusiness(string $name): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM businesses WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            return (int) $existing;
        }

        $stmt = $this->pdo->prepare('INSERT INTO businesses (name) VALUES (:name)');
        $stmt->execute(['name' => $name]);

        return (int) $this->pdo->lastInsertId();
    }

    public function deleteBusiness(int $businessId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM businesses WHERE id = :id');
        $stmt->execute([
            'id' => $businessId,
        ]);
    }

    public function insertCollection(int $businessId, int $totalUsers, float $totalCollection, string $month): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO collections (business_id, total_users, total_collection, month) VALUES (:business_id, :total_users, :total_collection, :month)
             ON DUPLICATE KEY UPDATE total_users = VALUES(total_users), total_collection = VALUES(total_collection)'
        );
        $stmt->execute([
            'business_id' => $businessId,
            'total_users' => $totalUsers,
            'total_collection' => $totalCollection,
            'month' => $month,
        ]);
    }

    public function deleteCollectionForMonth(int $businessId, string $month): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM collections WHERE business_id = :business_id AND month = :month');
        $stmt->execute([
            'business_id' => $businessId,
            'month' => $month,
        ]);
    }

    public function replaceMonthlyDiscount(int $businessId, string $month, float $amount): void
    {
        $delete = $this->pdo->prepare('DELETE FROM discounts WHERE business_id = :business_id AND month = :month');
        $delete->execute([
            'business_id' => $businessId,
            'month' => $month,
        ]);

        if ($amount > 0) {
            $this->insertDiscount($businessId, $amount, $month);
        }
    }

    public function deleteMonthlyDiscount(int $businessId, string $month): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM discounts WHERE business_id = :business_id AND month = :month');
        $stmt->execute([
            'business_id' => $businessId,
            'month' => $month,
        ]);
    }

    public function insertDeposit(int $businessId, float $amount, string $date, string $type, string $medium, string $reference): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO deposits (business_id, amount, date, type, medium, reference) VALUES (:business_id, :amount, :date, :type, :medium, :reference)');
        $stmt->execute([
            'business_id' => $businessId,
            'amount' => $amount,
            'date' => $date,
            'type' => $type,
            'medium' => $medium,
            'reference' => $reference,
        ]);
    }

    public function updateDeposit(int $depositId, int $businessId, float $amount, string $date, string $type, string $medium, string $reference): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE deposits
             SET business_id = :business_id,
                 amount = :amount,
                 date = :date,
                 type = :type,
                 medium = :medium,
                 reference = :reference
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $depositId,
            'business_id' => $businessId,
            'amount' => $amount,
            'date' => $date,
            'type' => $type,
            'medium' => $medium,
            'reference' => $reference,
        ]);
    }

    public function deleteDeposit(int $depositId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM deposits WHERE id = :id');
        $stmt->execute([
            'id' => $depositId,
        ]);
    }

    public function insertCost(string $type, float $amount, string $month): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO costs (type, amount, month) VALUES (:type, :amount, :month)');
        $stmt->execute([
            'type' => $type,
            'amount' => $amount,
            'month' => $month,
        ]);
    }

    public function updateCost(int $costId, string $type, float $amount, string $month): void
    {
        $stmt = $this->pdo->prepare('UPDATE costs SET type = :type, amount = :amount, month = :month WHERE id = :id');
        $stmt->execute([
            'id' => $costId,
            'type' => $type,
            'amount' => $amount,
            'month' => $month,
        ]);
    }

    public function deleteCost(int $costId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM costs WHERE id = :id');
        $stmt->execute([
            'id' => $costId,
        ]);
    }

    public function insertCommission(int $businessId, float $percentage): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO commissions (business_id, percentage) VALUES (:business_id, :percentage)
             ON DUPLICATE KEY UPDATE percentage = VALUES(percentage)'
        );
        $stmt->execute([
            'business_id' => $businessId,
            'percentage' => $percentage,
        ]);
    }

    public function insertBonus(int $businessId, float $percentage): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO bonuses (business_id, percentage) VALUES (:business_id, :percentage)
             ON DUPLICATE KEY UPDATE percentage = VALUES(percentage)'
        );
        $stmt->execute([
            'business_id' => $businessId,
            'percentage' => $percentage,
        ]);
    }

    public function insertDiscount(int $businessId, float $amount, string $month): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO discounts (business_id, amount, month) VALUES (:business_id, :amount, :month)');
        $stmt->execute([
            'business_id' => $businessId,
            'amount' => $amount,
            'month' => $month,
        ]);
    }
}
