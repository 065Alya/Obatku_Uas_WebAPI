<?php

namespace App\Services;

use App\Repositories\Contracts\MedicineRepositoryInterface;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class MedicineService
{
    public function __construct(
        protected MedicineRepositoryInterface $medicineRepo,
        protected ScheduleRepositoryInterface $scheduleRepo,
    ) {}

    /**
     * Get paginated medicines for a user.
     */
    public function getUserMedicines(int $userId, int $perPage = 15, array $filters = [])
    {
        return $this->medicineRepo->getByUser($userId, $perPage, $filters);
    }

    /**
     * Create a new medicine entry.
     */
    public function createMedicine(array $data): Model
    {
        return $this->medicineRepo->create($data);
    }

    /**
     * Update an existing medicine.
     */
    public function updateMedicine(int $id, array $data): Model
    {
        return $this->medicineRepo->update($id, $data);
    }

    /**
     * Delete a medicine.
     */
    public function deleteMedicine(int $id): bool
    {
        return $this->medicineRepo->delete($id);
    }

    /**
     * Get medicine by ID.
     */
    public function getMedicine(int $id): ?Model
    {
        return $this->medicineRepo->find($id);
    }

    /**
     * Get all low-stock medicines for alerts.
     */
    public function getLowStockAlerts(int $userId): Collection
    {
        return $this->medicineRepo->getLowStock($userId);
    }

    /**
     * Get medicines expiring within the given days.
     */
    public function getExpiryAlerts(int $userId, int $days = 30): Collection
    {
        return $this->medicineRepo->getExpiringSoon($userId, $days);
    }

    /**
     * Get already-expired medicines.
     */
    public function getExpiredMedicines(int $userId): Collection
    {
        return $this->medicineRepo->getExpired($userId);
    }

    /**
     * Search medicines by name/generic name.
     */
    public function searchMedicines(int $userId, string $query): Collection
    {
        return $this->medicineRepo->searchByName($userId, $query);
    }

    /**
     * Get dashboard alert summary.
     */
    public function getAlertSummary(int $userId): array
    {
        return [
            'low_stock' => $this->medicineRepo->getLowStock($userId),
            'expiring_soon' => $this->medicineRepo->getExpiringSoon($userId),
            'expired' => $this->medicineRepo->getExpired($userId),
        ];
    }
}
