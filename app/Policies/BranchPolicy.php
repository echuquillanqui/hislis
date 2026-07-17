<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('branches.manage') || $user->branches()->exists();
    }

    public function view(User $user, Branch $branch): bool
    {
        return $user->can('branches.manage') || $user->branches()->whereKey($branch->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->can('branches.manage');
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->can('branches.manage');
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->can('branches.manage') && ! $branch->is_main;
    }
}
