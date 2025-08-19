<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        if($user->can('view appointments')) {
            return true;
        } else {
            return false;
        }
    }

    public function update(User $user): bool
    {
        if($user->can('update appointments')) {
            return true;
        } else {
            return false;
        }
    }
}
