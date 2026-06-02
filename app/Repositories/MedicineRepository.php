<?php

namespace App\Repositories;

use App\Models\Medicine;
use App\Repositories\Contracts\MedicineRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MedicineRepository extends BaseRepository implements MedicineRepositoryInterface
{
    public function __construct(Medicine $model)
    {
        parent::__construct($model);
    }

    public function getByUser(int $userId, int $perPage = 15, array $filters = [])
    {
        $familyMemberIds = \App\Models\FamilyMember::whereHas('family', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->pluck('id');

        $query = $this->model
            ->where(function ($q) use ($userId, $familyMemberIds) {
                $q->where(function ($sq) use ($userId) {
                    $sq->where('owner_type', \App\Models\User::class)
                       ->where('owner_id', $userId);
                })->orWhere(function ($sq) use ($familyMemberIds) {
                    $sq->where('owner_type', \App\Models\FamilyMember::class)
                       ->whereIn('owner_id', $familyMemberIds);
                });
            });

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('medicine_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('generic_name', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'low_stock') {
                $query->whereColumn('stock', '<=', 'stock_alert');
            } elseif ($filters['status'] === 'expiring_soon') {
                $query->whereNotNull('expiry_date')
                      ->where('expiry_date', '>', now())
                      ->where('expiry_date', '<=', now()->addDays(30));
            } elseif ($filters['status'] === 'expired') {
                $query->whereNotNull('expiry_date')
                      ->where('expiry_date', '<', now());
            }
        }

        return $query->with(['category', 'owner'])
            ->orderBy('medicine_name')
            ->paginate($perPage);
    }

    public function getByFamily(int $familyMemberId): Collection
    {
        return $this->model
            ->where('owner_type', \App\Models\FamilyMember::class)
            ->where('owner_id', $familyMemberId)
            ->where('is_active', true)
            ->with('category')
            ->orderBy('medicine_name')
            ->get();
    }

    public function getLowStock(int $userId): Collection
    {
        $familyMemberIds = \App\Models\FamilyMember::whereHas('family', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->pluck('id');

        return $this->model
            ->where(function ($q) use ($userId, $familyMemberIds) {
                $q->where(function ($sq) use ($userId) {
                    $sq->where('owner_type', \App\Models\User::class)
                       ->where('owner_id', $userId);
                })->orWhere(function ($sq) use ($familyMemberIds) {
                    $sq->where('owner_type', \App\Models\FamilyMember::class)
                       ->whereIn('owner_id', $familyMemberIds);
                });
            })
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'stock_alert')
            ->with(['category', 'owner'])
            ->orderBy('stock')
            ->get();
    }

    public function getExpiringSoon(int $userId, int $days = 30): Collection
    {
        $familyMemberIds = \App\Models\FamilyMember::whereHas('family', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->pluck('id');

        return $this->model
            ->where(function ($q) use ($userId, $familyMemberIds) {
                $q->where(function ($sq) use ($userId) {
                    $sq->where('owner_type', \App\Models\User::class)
                       ->where('owner_id', $userId);
                })->orWhere(function ($sq) use ($familyMemberIds) {
                    $sq->where('owner_type', \App\Models\FamilyMember::class)
                       ->whereIn('owner_id', $familyMemberIds);
                });
            })
            ->where('is_active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays($days))
            ->with(['category', 'owner'])
            ->orderBy('expiry_date')
            ->get();
    }

    public function getExpired(int $userId): Collection
    {
        $familyMemberIds = \App\Models\FamilyMember::whereHas('family', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->pluck('id');

        return $this->model
            ->where(function ($q) use ($userId, $familyMemberIds) {
                $q->where(function ($sq) use ($userId) {
                    $sq->where('owner_type', \App\Models\User::class)
                       ->where('owner_id', $userId);
                })->orWhere(function ($sq) use ($familyMemberIds) {
                    $sq->where('owner_type', \App\Models\FamilyMember::class)
                       ->whereIn('owner_id', $familyMemberIds);
                });
            })
            ->where('is_active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->with(['category', 'owner'])
            ->orderBy('expiry_date')
            ->get();
    }

    public function searchByName(int $userId, string $query): Collection
    {
        $familyMemberIds = \App\Models\FamilyMember::whereHas('family', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->pluck('id');

        return $this->model
            ->where(function ($q) use ($userId, $familyMemberIds) {
                $q->where(function ($sq) use ($userId) {
                    $sq->where('owner_type', \App\Models\User::class)
                       ->where('owner_id', $userId);
                })->orWhere(function ($sq) use ($familyMemberIds) {
                    $sq->where('owner_type', \App\Models\FamilyMember::class)
                       ->whereIn('owner_id', $familyMemberIds);
                });
            })
            ->where(function ($q) use ($query) {
                $q->where('medicine_name', 'like', "%{$query}%")
                  ->orWhere('generic_name', 'like', "%{$query}%");
            })
            ->with(['category', 'owner'])
            ->orderBy('medicine_name')
            ->get();
    }

    public function getByCategory(int $userId, int $categoryId): Collection
    {
        $familyMemberIds = \App\Models\FamilyMember::whereHas('family', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->pluck('id');

        return $this->model
            ->where(function ($q) use ($userId, $familyMemberIds) {
                $q->where(function ($sq) use ($userId) {
                    $sq->where('owner_type', \App\Models\User::class)
                       ->where('owner_id', $userId);
                })->orWhere(function ($sq) use ($familyMemberIds) {
                    $sq->where('owner_type', \App\Models\FamilyMember::class)
                       ->whereIn('owner_id', $familyMemberIds);
                });
            })
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->with(['category', 'owner'])
            ->orderBy('medicine_name')
            ->get();
    }
}
