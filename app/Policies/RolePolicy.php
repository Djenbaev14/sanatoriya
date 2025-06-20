<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->can('просмотреть любое роль');
    }

    public function view(User $user,Role $role): bool
    {
        return $user->can('просмотр роль');
    }

    public function create(User $user): bool
    {
        return $user->can('создать роль');
    }
}
