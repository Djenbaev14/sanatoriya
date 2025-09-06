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
    public function view(Role $role): bool
    {
        return $role->can('view_role');
    }

    /**
     * Determine whether the user can create models.
     *
     
     * @return bool
     */
    public function create(Role $role): bool
    {
        return $role->can('create_role');
    }

    /**
     * Determine whether the user can update the model.
     *
     
     * @return bool
     */
    public function update(Role $role): bool
    {
        return $role->can('update_role');
    }

    /**
     * Determine whether the user can delete the model.
     *
     
     * @return bool
     */
    public function delete(Role $role): bool
    {
        return $role->can('delete_role');
    }

}
