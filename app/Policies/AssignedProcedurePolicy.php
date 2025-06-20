<?php

namespace App\Policies;

use App\Models\AssignedProcedure;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssignedProcedurePolicy
{
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->can('просмотреть любую касса процедуру');
    }

    public function view(User $user, AssignedProcedure $assignedProcedure): bool
    {
        return $user->can('просмотреть касса процедуру');
    }

    public function create(User $user): bool
    {
        return $user->can('создать касса процедуру');
    }
}
