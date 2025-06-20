<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PatientPolicy
{
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->can('просмотр любой пациента');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->can('просмотр пациента');
    }

    public function create(User $user): bool
    {
        return $user->can('создать пациента');
    }
}
