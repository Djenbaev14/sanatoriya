<?php

namespace App\Policies;

use App\Models\LabTestHistory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LabTestHistoryPolicy
{
    use HandlesAuthorization;
    public function viewAny(User $user): bool
    {
        return $user->can('просмотреть любого касса анализ');
    }

    public function view(User $user, LabTestHistory $labTestHistory): bool
    {
        return $user->can('просмотреть касса анализ');
    }

    public function create(User $user): bool
    {
        return $user->can('создать касса анализ');
    }
}
