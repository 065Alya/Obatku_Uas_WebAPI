<?php

namespace App\Providers;

use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\EcoMedRepositoryInterface;
use App\Repositories\Contracts\FamilyMemberRepositoryInterface;
use App\Repositories\Contracts\MedicineRepositoryInterface;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use App\Repositories\BaseRepository;
use App\Repositories\EcoMedRepository;
use App\Repositories\FamilyMemberRepository;
use App\Repositories\MedicineRepository;
use App\Repositories\ScheduleRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    protected array $repositories = [
        MedicineRepositoryInterface::class     => MedicineRepository::class,
        ScheduleRepositoryInterface::class     => ScheduleRepository::class,
        FamilyMemberRepositoryInterface::class => FamilyMemberRepository::class,
        EcoMedRepositoryInterface::class       => EcoMedRepository::class,
        \App\Repositories\Contracts\UserRepositoryInterface::class => \App\Repositories\UserRepository::class,
    ];

    /**
     * Register repository bindings.
     */
    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
