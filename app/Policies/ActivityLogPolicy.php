<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Activitylog\Models\Activity;

class ActivityLogPolicy
{
    
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->can('просмотр любого актива');
    }

    public function view(User $user,Activity $role): bool
    {
        return $user->can('просмотр актива');
    }

    public function create(User $user): bool
    {
        return $user->can('создать актива');
    }
}
