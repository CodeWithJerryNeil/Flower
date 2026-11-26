<?php

namespace App\Http\Resources;

use App\Models\SocialProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SocialProfile */
class SocialProfileResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'platform' => $this->platform->value,
            'profile_url' => $this->profile_url,
            'username' => $this->username,
            'source_url' => $this->source_url,
            'confidence_score' => $this->confidence_score,
            'discovered_at' => $this->discovered_at->toIso8601String(),
        ];
    }
}
