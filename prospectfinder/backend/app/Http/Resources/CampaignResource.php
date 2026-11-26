<?php

namespace App\Http\Resources;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Campaign */
class CampaignResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => [
                'query' => $this->location_query,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
            ],
            'category' => $this->category,
            'target_count' => $this->target_count,
            'status' => $this->status->value,
            'filters' => (object) ($this->filters ?? []),
            'leads_count' => $this->whenCounted('leads'),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
