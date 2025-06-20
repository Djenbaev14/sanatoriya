<?php

namespace App\Policies;

use App\Models\Inspection;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InspectionPolicy
{
    
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->can('просмотреть любой осмотр');
    }

    public function view(User $user, Inspection $inspection): bool
    {
        return $user->can('просмотр осмотр');
    }

    public function create(User $user): bool
    {
        return $user->can('создать осмотр');
    }
}
