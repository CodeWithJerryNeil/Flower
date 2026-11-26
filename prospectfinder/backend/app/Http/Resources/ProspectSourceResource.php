<?php

namespace App\Http\Resources;

use App\Models\ProspectSource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Evidence may contain third-party page content: clients must render it as text.
 *
 * @mixin ProspectSource
 */
class ProspectSourceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'source_type' => $this->source_type,
            'source_url' => $this->source_url,
            'field_name' => $this->field_name,
            'evidence' => $this->evidence,
            'collected_at' => $this->collected_at->toIso8601String(),
        ];
    }
}
