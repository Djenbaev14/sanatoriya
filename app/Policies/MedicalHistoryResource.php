<?php

namespace App\Policies;

use App\Models\MedicalHistory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MedicalHistoryResource
{
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->can('просмотреть любую касса питание и койка');
    }
    public function view(User $user, MedicalHistory $medicalHistory): bool
    {
        return $user->can('просмотреть касса питание и койка');
    }

    public function create(User $user): bool
    {
        return $user->can('создать касса питание и койка');
    }
}
