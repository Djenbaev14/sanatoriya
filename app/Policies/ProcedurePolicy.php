<?php

namespace App\Policies;

use App\Models\Procedure;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProcedurePolicy
{
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->can('просмотреть любой лечение');
    }

    public function view(User $user, Procedure $procedure): bool
    {
        return $user->can('просмотр лечения');
    }

    public function create(User $user): bool
    {
        return $user->can('создать лечения');
    }
}
