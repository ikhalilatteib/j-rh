<?php

declare(strict_types=1);

namespace Ikay\JRh\Policies;

use Ikay\JRh\Models\Salary;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SalaryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Salary');
    }

    public function view(AuthUser $authUser, Salary $salary): bool
    {
        return $authUser->can('View:Salary');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Salary');
    }

    public function update(AuthUser $authUser, Salary $salary): bool
    {
        return $authUser->can('Update:Salary');
    }

    public function delete(AuthUser $authUser, Salary $salary): bool
    {
        return $authUser->can('Delete:Salary');
    }
}
