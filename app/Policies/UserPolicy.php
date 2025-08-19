<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        if($user->can('view users')) {
            return true;
        } else {
            return false;
        }
    }

    public function create(User $user): bool
    {
        if($user->can('create users')) {
            return true;
        } else {
            return false;
        }
    }

    public function edit(User $user): bool
    {
        if($user->can('edit users')) {
            return true;
        } else {
            return false;
        }
    }

    public function delete(User $user): bool
    {
        if($user->can('delete users')) {
            return true;
        } else {
            return false;
        }
    }
}
