<?php

namespace App\Http\Requests;

use App\Enums\QualificationStatus;
use App\Enums\WebsiteStatus;
use App\Models\CampaignLead;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/** Manual review edits to a lead and its business details. */
class UpdateLeadRequest extends FormRequest
{
    /** Returns the policy response so ownership denials keep their 404 status. */
    public function authorize(): Response
    {
        /** @var CampaignLead $lead */
        $lead = $this->route('lead');

        return Gate::inspect('update', $lead);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'qualification_status' => ['sometimes', Rule::enum(QualificationStatus::class)],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'business' => ['sometimes', 'array:name,category,address,city,state,country,phone,website_url,website_status'],
            'business.name' => ['sometimes', 'required', 'string', 'max:255'],
            'business.category' => ['sometimes', 'nullable', 'string', 'max:120'],
            'business.address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'business.city' => ['sometimes', 'nullable', 'string', 'max:120'],
            'business.state' => ['sometimes', 'nullable', 'string', 'max:120'],
            'business.country' => ['sometimes', 'nullable', 'string', 'max:120'],
            'business.phone' => ['sometimes', 'nullable', 'string', 'max:50', 'regex:/^[0-9+().\-\s]+$/'],
            'business.website_url' => ['sometimes', 'nullable', 'string', 'max:2048', 'url:http,https'],
            'business.website_status' => ['sometimes', Rule::enum(WebsiteStatus::class)],
        ];
    }
}
