<?php

namespace App\Policies;

use App\Models\LabTest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LabTestPolicy
{
    
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->can('просмотреть любой анализ');
    }

    public function view(User $user, LabTest $labTest): bool
    {
        return $user->can('просмотр анализа');
    }

    public function create(User $user): bool
    {
        return $user->can('создать анализа');
    }
}
