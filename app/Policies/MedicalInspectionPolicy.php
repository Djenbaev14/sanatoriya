<?php

namespace App\Policies;

use App\Models\MedicalInspection;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MedicalInspectionPolicy
{
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->can('просмотреть любую касса осмотр');
    }

    public function view(User $user, MedicalInspection $medicalInspection): bool
    {
        return $user->can('просмотреть касса осмотр');
    }

    public function create(User $user): bool
    {
        return $user->can('создать касса осмотр');
    }
}
