<?php

namespace App\Policies;

use App\Models\Mission;
use App\Models\User;

class MissionPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    // Permiso para agentes que son lideres de misión
    public function leaderAction(User $user, Mission $mission): bool {
        return $mission->leader_id === $user->agent->id;
    }

    // Permiso para admins y assistants que no sean lideres de misión
    public function managementAction(User $user, Mission $mission): bool {
        return in_array($user->role->name, ['admin', 'assistant']) && $mission->leader_id !== $user->agent->id;
    }
}
