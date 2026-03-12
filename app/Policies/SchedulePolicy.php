<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchedulePolicy
{
    use HandlesAuthorization;

    public function viewDate(User $user): bool
    {
        if($user->can('view schedule dates')) {
            return true;
        } else {
            return false;
        }
    }

    public function createDate(User $user): bool
    {
        if($user->can('create schedule dates')) {
            return true;
        } else {
            return false;
        }
    }

    public function editDate(User $user): bool
    {
        if($user->can('edit schedule dates')) {
            return true;
        } else {
            return false;
        }
    }

    public function view(User $user): bool
    {
        if($user->can('view schedules')) {
            return true;
        } else {
            return false;
        }
    }

    public function download(User $user): bool
    {
        if($user->can('download schedules')) {
            return true;
        } else {
            return false;
        }
    }

    public function update(User $user): bool
    {
        if($user->can('update schedules')) {
            return true;
        } else {
            return false;
        }
    }

    public function delete(User $user): bool
    {
        if($user->can('delete schedules')) {
            return true;
        } else {
            return false;
        }
    }

    public function viewHistory(User $user): bool
    {
        if($user->can('view schedules history')) {
            return true;
        } else {
            return false;
        }
    }
}
