<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_role');
    }

    /**
     * Determine whether the user can view the model.
     *
     
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->can('view_role');
    }

    /**
     * Determine whether the user can create models.
     *
     
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create_role');
    }

    /**
     * Determine whether the user can update the model.
     *
     
     * @return bool
     */
    public function update(User $user,Role $role): bool
    {
        return $user->can('update_role');
    }

    /**
     * Determine whether the user can delete the model.
     *
     
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->can('delete_role');
    }

}
