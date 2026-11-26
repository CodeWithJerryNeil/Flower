<?php

namespace App\Http\Resources;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Contact */
class ContactResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'contact_type' => $this->contact_type->value,
            'verification_status' => $this->verification_status->value,
            'confidence_score' => $this->confidence_score,
            'source_url' => $this->source_url,
            'discovered_at' => $this->discovered_at->toIso8601String(),
        ];
    }
}
