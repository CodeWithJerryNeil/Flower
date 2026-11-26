<?php

namespace App\Policies;

use App\Models\CampaignLead;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** A lead belongs to whoever owns its campaign. Denials surface as 404. */
class CampaignLeadPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CampaignLead $lead): Response
    {
        return $this->owns($user, $lead);
    }

    public function update(User $user, CampaignLead $lead): Response
    {
        return $this->owns($user, $lead);
    }

    public function research(User $user, CampaignLead $lead): Response
    {
        return $this->owns($user, $lead);
    }

    private function owns(User $user, CampaignLead $lead): Response
    {
        return $lead->campaign()->where('user_id', $user->getKey())->exists()
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
