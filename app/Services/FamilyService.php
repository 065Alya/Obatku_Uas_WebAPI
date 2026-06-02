<?php

namespace App\Services;

use App\Repositories\Contracts\FamilyMemberRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class FamilyService
{
    public function __construct(
        protected FamilyMemberRepositoryInterface $familyRepo,
    ) {}

    public function getUserFamily(int $userId): Collection
    {
        return $this->familyRepo->getByUser($userId);
    }

    public function getActiveFamily(int $userId): Collection
    {
        return $this->familyRepo->getActiveByUser($userId);
    }

    public function createMember(array $data): Model
    {
        return $this->familyRepo->create($data);
    }

    public function updateMember(int $id, array $data): Model
    {
        return $this->familyRepo->update($id, $data);
    }

    public function deleteMember(int $id): bool
    {
        return $this->familyRepo->delete($id);
    }

    public function getMember(int $id): ?Model
    {
        return $this->familyRepo->find($id);
    }
}
