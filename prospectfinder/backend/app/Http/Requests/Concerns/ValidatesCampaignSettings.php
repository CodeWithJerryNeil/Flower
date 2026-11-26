<?php

namespace App\Http\Requests\Concerns;

use App\Enums\WebsiteStatus;
use Illuminate\Validation\Rule;

/** Shared rules and mapping for campaign create/update payloads. */
trait ValidatesCampaignSettings
{
    /** Request keys that define what a campaign searches for. */
    protected const SETTINGS_KEYS = ['location', 'category', 'target_count', 'filters'];

    /** @return array<string, mixed> */
    protected function settingsRules(bool $partial): array
    {
        $required = $partial ? ['sometimes', 'required'] : ['required'];

        return [
            'location' => [...$required, 'array:query,city,state,country'],
            'location.query' => ['exclude_without:location', 'nullable', 'string', 'max:255', 'required_without:location.city'],
            'location.city' => ['exclude_without:location', 'nullable', 'string', 'max:120', 'required_without:location.query'],
            'location.state' => ['exclude_without:location', 'nullable', 'string', 'max:120'],
            'location.country' => ['exclude_without:location', 'required_with:location', 'string', 'max:120'],
            'category' => [...$required, 'string', 'max:120'],
            'target_count' => [
                ...$required, 'integer', 'min:1',
                'max:'.config('prospectfinder.campaigns.max_target_count'),
            ],
            'filters' => ['sometimes', 'array:website_status,prioritize_email'],
            'filters.website_status' => ['sometimes', 'array'],
            'filters.website_status.*' => ['distinct', Rule::enum(WebsiteStatus::class)],
            'filters.prioritize_email' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Maps validated input onto Campaign columns. Only keys present in the request are returned.
     *
     * @return array<string, mixed>
     */
    public function campaignAttributes(): array
    {
        $input = $this->validated();
        $attributes = array_intersect_key($input, array_flip(['name', 'category', 'target_count', 'filters']));

        if (isset($input['location'])) {
            $location = $input['location'];
            $attributes['city'] = $location['city'] ?? null;
            $attributes['state'] = $location['state'] ?? null;
            $attributes['country'] = $location['country'];
            $attributes['location_query'] = $location['query']
                ?? implode(', ', array_filter([$location['city'] ?? null, $location['state'] ?? null, $location['country']]));
        }

        return $attributes;
    }
}
