<?php

namespace App\Repositories;

use App\Models\FamilyMember;
use App\Repositories\Contracts\FamilyMemberRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FamilyMemberRepository extends BaseRepository implements FamilyMemberRepositoryInterface
{
    public function __construct(FamilyMember $model)
    {
        parent::__construct($model);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model
            ->whereHas('family', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with(['medicines', 'schedules'])
            ->orderBy('name')
            ->get();
    }

    public function getActiveByUser(int $userId): Collection
    {
        return $this->getByUser($userId);
    }
}
