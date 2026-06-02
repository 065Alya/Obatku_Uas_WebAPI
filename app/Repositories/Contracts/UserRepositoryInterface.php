<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function getActiveUsers(int $perPage = 15): LengthAwarePaginator;

    public function getAdmins(): Collection;

    public function search(string $query, int $perPage = 15): LengthAwarePaginator;
}
