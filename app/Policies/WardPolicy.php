<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ward;
use Illuminate\Auth\Access\HandlesAuthorization;

class WardPolicy
{
    
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->can('просмотр любой палаты');
    }

    public function view(User $user, Ward $ward): bool
    {
        return $user->can('просмотреть палату');
    }

    public function create(User $user): bool
    {
        return $user->can('создать палату');
    }
}
