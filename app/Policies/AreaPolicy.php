<?php

namespace App\Policies;

use App\Models\Area;
use App\Models\User;

class AreaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('areas.manage') || $user->accessibleAreas()->exists();
    }

    public function view(User $user, Area $area): bool
    {
        return $user->can('areas.manage') || $user->accessibleAreas()->whereKey($area->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->can('areas.manage');
    }

    public function update(User $user, Area $area): bool
    {
        return $user->can('areas.manage');
    }

    public function delete(User $user, Area $area): bool
    {
        return $user->can('areas.manage') && ! $area->users()->exists();
    }
}
