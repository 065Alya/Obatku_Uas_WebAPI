<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface FamilyMemberRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUser(int $userId): Collection;

    public function getActiveByUser(int $userId): Collection;
}
