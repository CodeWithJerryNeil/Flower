<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Users may only touch their own campaigns. Denials surface as 404 so that
 * other users' campaign IDs cannot be probed.
 */
class CampaignPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Campaign $campaign): Response
    {
        return $this->owns($user, $campaign);
    }

    public function update(User $user, Campaign $campaign): Response
    {
        return $this->owns($user, $campaign);
    }

    public function delete(User $user, Campaign $campaign): Response
    {
        return $this->owns($user, $campaign);
    }

    public function start(User $user, Campaign $campaign): Response
    {
        return $this->owns($user, $campaign);
    }

    public function cancel(User $user, Campaign $campaign): Response
    {
        return $this->owns($user, $campaign);
    }

    private function owns(User $user, Campaign $campaign): Response
    {
        return $campaign->user_id === $user->getKey()
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
