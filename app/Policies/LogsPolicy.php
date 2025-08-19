<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogsPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        if($user->can('view logs')) {
            return true;
        } else {
            return false;
        }
    }
}
