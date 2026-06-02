<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface MedicineRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUser(int $userId, int $perPage = 15, array $filters = []);

    public function getByFamily(int $familyMemberId): Collection;

    public function getLowStock(int $userId): Collection;

    public function getExpiringSoon(int $userId, int $days = 30): Collection;

    public function getExpired(int $userId): Collection;

    public function searchByName(int $userId, string $query): Collection;

    public function getByCategory(int $userId, int $categoryId): Collection;
}
