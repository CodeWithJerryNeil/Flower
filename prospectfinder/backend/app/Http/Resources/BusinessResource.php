<?php

namespace App\Http\Resources;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Omits internal fields (normalized_name) and provider identities, whose exposure may be
 * restricted by provider terms. Evidence and URLs are untrusted third-party data.
 *
 * @mixin Business
 */
class BusinessResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'latitude' => $this->latitude === null ? null : (float) $this->latitude,
            'longitude' => $this->longitude === null ? null : (float) $this->longitude,
            'phone' => $this->phone,
            'website_url' => $this->website_url,
            'website_status' => $this->website_status->value,
            'business_status' => $this->business_status->value,
            'lead_score' => $this->lead_score,
            'last_researched_at' => $this->last_researched_at?->toIso8601String(),
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
            'social_profiles' => SocialProfileResource::collection($this->whenLoaded('socialProfiles')),
            'website_checks' => WebsiteCheckResource::collection($this->whenLoaded('websiteChecks')),
            'sources' => ProspectSourceResource::collection($this->whenLoaded('prospectSources')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
