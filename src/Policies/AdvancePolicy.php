<?php

declare(strict_types=1);

namespace Ikay\JRh\Policies;

use Ikay\JRh\Models\Advance;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AdvancePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Advance');
    }

    public function view(AuthUser $authUser, Advance $advance): bool
    {
        return $authUser->can('View:Advance');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Advance');
    }

    public function update(AuthUser $authUser, Advance $advance): bool
    {
        return $authUser->can('Update:Advance');
    }

    public function delete(AuthUser $authUser, Advance $advance): bool
    {
        return $authUser->can('Delete:Advance');
    }
}
