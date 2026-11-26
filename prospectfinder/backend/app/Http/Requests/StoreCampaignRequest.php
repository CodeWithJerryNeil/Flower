<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesCampaignSettings;
use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    use ValidatesCampaignSettings;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Campaign::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            ...$this->settingsRules(partial: false),
            // Only automatic research exists. Accepted for forward compatibility, not stored.
            'research_mode' => ['sometimes', 'string', 'in:automatic'],
        ];
    }
}
