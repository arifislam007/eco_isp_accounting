<?php
declare(strict_types=1);

class ReportModel extends BaseModel
{
    public function getBusinesses(): array
    {
        $stmt = $this->pdo->query('SELECT id, name FROM businesses ORDER BY name');
        return $stmt->fetchAll();
    }

    public function getBusinessList(string $month): array
    {
        $businesses = $this->getBusinesses();
        [$monthStart, $fifteenth, $nextMonth] = month_bounds($month);

        $rows = [];
        foreach ($businesses as $business) {
            $businessId = (int) $business['id'];
            $collection = $this->getCollectionRow($businessId, $month);
            $commission = $this->getSingleValue('SELECT percentage FROM commissions WHERE business_id = :business_id LIMIT 1', $businessId);
            $bonus = $this->getSingleValue('SELECT percentage FROM bonuses WHERE business_id = :business_id LIMIT 1', $businessId);
            $discount = $this->getMonthlyDiscount($businessId, $month);
            $totalDeposit = $this->getDepositTotal($businessId, $monthStart, $nextMonth);
            $depositBy15th = $this->getDepositTotal($businessId, $monthStart, $fifteenth, true);

            $totalCollection = (float) ($collection['total_collection'] ?? 0);
            $commissionAmount = $totalCollection * $commission / 100;
            $businessAmount = $totalCollection - $commissionAmount;
            $bonusAmount = $businessAmount * $bonus / 100;
            $dueBeforeBonus = $businessAmount - $discount - $totalDeposit;
            $dueAfterBonus = $dueBeforeBonus - $bonusAmount;

            $rows[] = [
                'business_id' => $businessId,
                'business_name' => (string) $business['name'],
                'total_users' => (int) ($collection['total_users'] ?? 0),
                'total_collection' => $totalCollection,
                'commission_percentage' => $commission,
                'commission_amount' => $commissionAmount,
                'business_amount' => $businessAmount,
                'deposit_by_15th' => $depositBy15th,
                'bonus_percentage' => $bonus,
                'bonus_commission' => $bonusAmount,
                'discount' => $discount,
                'total_deposit' => $totalDeposit,
                'due_before_bonus' => $dueBeforeBonus,
                'due_after_bonus' => $dueAfterBonus,
            ];
        }

        return $rows;
    }

    public function getBusinessDetails(int $businessId, string $month): array
    {
        [$monthStart, $fifteenth, $nextMonth] = month_bounds($month);
        $business = $this->getBusiness($businessId);
        $collection = $this->getCollectionRow($businessId, $month);
        $commission = $this->getSingleValue('SELECT percentage FROM commissions WHERE business_id = :business_id LIMIT 1', $businessId);
        $bonus = $this->getSingleValue('SELECT percentage FROM bonuses WHERE business_id = :business_id LIMIT 1', $businessId);
        $discount = $this->getMonthlyDiscount($businessId, $month);
        $totalDeposit = $this->getDepositTotal($businessId, $monthStart, $nextMonth);
        $depositBy15th = $this->getDepositTotal($businessId, $monthStart, $fifteenth, true);
        $depositHistory = $this->getDepositHistory($businessId, $monthStart, $nextMonth);
        $costs = $this->getMonthlyCosts($month);

        $totalCollection = (float) ($collection['total_collection'] ?? 0);
        $commissionAmount = $totalCollection * $commission / 100;
        $businessAmount = $totalCollection - $commissionAmount;
        $bonusAmount = $businessAmount * $bonus / 100;
        $dueBeforeBonus = $businessAmount - $discount - $totalDeposit;
        $dueAfterBonus = $dueBeforeBonus - $bonusAmount;

        return [
            'business' => $business,
            'month' => $month,
            'month_label' => month_label($month),
            'summary' => [
                'total_users' => (int) ($collection['total_users'] ?? 0),
                'total_collection' => $totalCollection,
                'commission_percentage' => $commission,
                'commission_amount' => $commissionAmount,
                'bill_amount' => $businessAmount,
                'deposit_by_15th' => $depositBy15th,
                'bonus_percentage' => $bonus,
                'bonus_commission' => $bonusAmount,
                'discount' => $discount,
                'total_deposit' => $totalDeposit,
                'due_before_bonus' => $dueBeforeBonus,
                'final_due' => $dueAfterBonus,
            ],
            'deposit_history' => $depositHistory,
            'costs' => $costs,
        ];
    }

    public function getDashboardData(string $month): array
    {
        $snapshot = $this->buildMonthSnapshot($month);
        $snapshot['month'] = $month;
        $snapshot['month_label'] = month_label($month);
        $snapshot['charts'] = $this->getCharts();

        return $snapshot;
    }

    public function getCharts(): array
    {
        $labels = [];
        $collections = [];
        $deposits = [];
        $profits = [];
        $users = [];

        $cursor = new DateTime('first day of this month');
        $cursor->modify('-5 months');

        for ($i = 0; $i < 6; $i++) {
            $month = $cursor->format('Y-m');
            $labels[] = month_label($month);
            $summary = $this->buildMonthSnapshot($month);
            $collections[] = $summary['summary']['total_collection'];
            $deposits[] = $summary['summary']['total_deposit'];
            $profits[] = $summary['summary']['profit'];
            $users[] = $summary['summary']['total_users'];
            $cursor->modify('+1 month');
        }

        return [
            'labels' => $labels,
            'collections' => $collections,
            'deposits' => $deposits,
            'profits' => $profits,
            'users' => $users,
        ];
    }

    private function buildMonthSnapshot(string $month): array
    {
        $businessList = $this->getBusinessList($month);
        $costs = $this->getMonthlyCostSummary($month);
        $costRows = $this->getMonthlyCosts($month);
        $deposits = $this->getMonthlyDeposits($month);

        $totalCollection = 0.0;
        $totalCommission = 0.0;
        $totalDeposit = 0.0;
        $totalDue = 0.0;
        $totalUsers = 0;

        foreach ($businessList as $row) {
            $totalUsers += (int) $row['total_users'];
            $totalCollection += (float) $row['total_collection'];
            $totalCommission += (float) $row['commission_amount'];
            $totalDeposit += (float) $row['total_deposit'];
            $totalDue += max((float) $row['due_after_bonus'], 0.0);
        }

        $totalCost = (float) ($costs['total_cost'] ?? 0);
        $profit = $totalCollection - $totalCommission - $totalDeposit - $totalCost;

        return [
            'summary' => [
                'total_collection' => $totalCollection,
                'total_commission' => $totalCommission,
                'total_deposit' => $totalDeposit,
                'total_due' => $totalDue,
                'total_users' => $totalUsers,
                'total_cost' => $totalCost,
                'profit' => $profit,
                'isp_bill' => (float) ($costs['isp_bill'] ?? 0),
                'software_cost' => (float) ($costs['software_cost'] ?? 0),
                'others' => (float) ($costs['others'] ?? 0),
            ],
            'business_list' => $businessList,
            'costs' => $costs,
            'cost_rows' => $costRows,
            'deposits' => $deposits,
        ];
    }

    private function getBusiness(int $businessId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, name FROM businesses WHERE id = :business_id LIMIT 1');
        $stmt->execute(['business_id' => $businessId]);
        $business = $stmt->fetch();

        return $business ?: ['id' => $businessId, 'name' => 'Unknown business'];
    }

    private function getCollectionRow(int $businessId, string $month): array
    {
        $stmt = $this->pdo->prepare('SELECT total_users, total_collection FROM collections WHERE business_id = :business_id AND month = :month LIMIT 1');
        $stmt->execute([
            'business_id' => $businessId,
            'month' => $month,
        ]);

        $row = $stmt->fetch();
        return $row ?: ['total_users' => 0, 'total_collection' => 0];
    }

    private function getSingleValue(string $sql, int $businessId): float
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['business_id' => $businessId]);
        $value = $stmt->fetchColumn();
        return (float) ($value ?: 0);
    }

    private function getMonthlyDiscount(int $businessId, string $month): float
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(SUM(amount), 0) FROM discounts WHERE business_id = :business_id AND month = :month');
        $stmt->execute([
            'business_id' => $businessId,
            'month' => $month,
        ]);

        return (float) $stmt->fetchColumn();
    }

    private function getDepositTotal(int $businessId, string $start, string $end, bool $inclusiveEnd = false): float
    {
        $operator = $inclusiveEnd ? '<=' : '<';
        $stmt = $this->pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM deposits WHERE business_id = :business_id AND date >= :start_date AND date {$operator} :end_date");
        $stmt->execute([
            'business_id' => $businessId,
            'start_date' => $start,
            'end_date' => $end,
        ]);

        return (float) $stmt->fetchColumn();
    }

    private function getDepositHistory(int $businessId, string $start, string $end): array
    {
        $stmt = $this->pdo->prepare('SELECT id, date, amount, medium, reference, type FROM deposits WHERE business_id = :business_id AND date >= :start_date AND date < :end_date ORDER BY date ASC, id ASC');
        $stmt->execute([
            'business_id' => $businessId,
            'start_date' => $start,
            'end_date' => $end,
        ]);

        return $stmt->fetchAll();
    }

    private function getMonthlyCosts(string $month): array
    {
        $stmt = $this->pdo->prepare('SELECT id, type, amount, month FROM costs WHERE month = :month ORDER BY id ASC');
        $stmt->execute(['month' => $month]);
        return $stmt->fetchAll();
    }

    private function getMonthlyDeposits(string $month): array
    {
        [$start, , $end] = month_bounds($month);
        $stmt = $this->pdo->prepare(
            'SELECT d.id, d.business_id, b.name AS business_name, d.amount, d.date, d.type, d.medium, d.reference
             FROM deposits d
             INNER JOIN businesses b ON b.id = d.business_id
             WHERE d.date >= :start_date AND d.date < :end_date
             ORDER BY d.date DESC, d.id DESC'
        );
        $stmt->execute([
            'start_date' => $start,
            'end_date' => $end,
        ]);

        return $stmt->fetchAll();
    }

    private function getMonthlyCostSummary(string $month): array
    {
        $stmt = $this->pdo->prepare('SELECT type, COALESCE(SUM(amount), 0) AS amount FROM costs WHERE month = :month GROUP BY type');
        $stmt->execute(['month' => $month]);

        $summary = [
            'isp_bill' => 0.0,
            'software_cost' => 0.0,
            'others' => 0.0,
            'total_cost' => 0.0,
        ];

        foreach ($stmt->fetchAll() as $row) {
            $type = strtolower((string) $row['type']);
            $amount = (float) $row['amount'];
            $summary['total_cost'] += $amount;

            if (str_contains($type, 'isp')) {
                $summary['isp_bill'] += $amount;
            } elseif (str_contains($type, 'software')) {
                $summary['software_cost'] += $amount;
            } else {
                $summary['others'] += $amount;
            }
        }

        return $summary;
    }
}
