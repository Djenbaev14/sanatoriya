<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->can('просмотреть любое разрешение');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->can('просмотр разрешение');
    }

    public function create(User $user): bool
    {
        return $user->can('создать разрешение');
    }
}
