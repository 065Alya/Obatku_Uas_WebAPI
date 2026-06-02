<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getActiveUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('is_active', true)
            ->where('role', 'user')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getAdmins(): Collection
    {
        return $this->model->where('role', 'admin')
            ->where('is_active', true)
            ->get();
    }

    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('role', 'user')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
