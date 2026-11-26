<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\OutreachActivity;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Outreach is attached to a business; access requires reaching that business via an owned campaign. */
class OutreachActivityPolicy
{
    public function update(User $user, OutreachActivity $activity): Response
    {
        return $this->canReach($user, $activity);
    }

    public function delete(User $user, OutreachActivity $activity): Response
    {
        return $this->canReach($user, $activity);
    }

    private function canReach(User $user, OutreachActivity $activity): Response
    {
        return Business::accessibleBy($user)->whereKey($activity->business_id)->exists()
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
