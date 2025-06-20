<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->can('просмотреть любого пользователя');
    }

    public function view(User $user, User $uuser): bool
    {
        return $user->can('просмотреть пользователя');
    }

    public function create(User $user): bool
    {
        return $user->can('создать пользователя');
    }
}
