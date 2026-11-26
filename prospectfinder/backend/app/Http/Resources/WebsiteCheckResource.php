<?php

namespace App\Http\Resources;

use App\Models\WebsiteCheck;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Evidence may contain third-party page content: clients must render it as text.
 *
 * @mixin WebsiteCheck
 */
class WebsiteCheckResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'checked_url' => $this->checked_url,
            'status' => $this->status->value,
            'http_status' => $this->http_status,
            'is_social_domain' => $this->is_social_domain,
            'evidence' => $this->evidence,
            'checked_at' => $this->checked_at->toIso8601String(),
        ];
    }
}
