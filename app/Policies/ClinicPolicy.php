<?php

namespace App\Policies;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClinicPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        if($user->can('view clinics')) {
            return true;
        } else {
            return false;
        }
    }

    public function create(User $user): bool
    {
        if($user->can('create clinics')) {
            return true;
        } else {
            return false;
        }
    }

    public function edit(User $user): bool
    {
        if($user->can('edit clinics')) {
            return true;
        } else {
            return false;
        }
    }

    public function delete(User $user): bool
    {
        if($user->can('delete clinics')) {
            return true;
        } else {
            return false;
        }
    }
}
