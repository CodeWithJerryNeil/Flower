<?php

namespace App\Http\Requests;

use App\Enums\OutreachChannel;
use App\Enums\OutreachStatus;
use App\Models\CampaignLead;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOutreachActivityRequest extends FormRequest
{
    /** Statuses that represent initiating contact, which suppression forbids. */
    private const INITIATING_STATUSES = [OutreachStatus::Planned, OutreachStatus::Contacted];

    /** Returns the policy response so ownership denials keep their 404 status. */
    public function authorize(): Response
    {
        return Gate::inspect('update', $this->lead());
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'channel' => ['required', Rule::enum(OutreachChannel::class)],
            'status' => ['required', Rule::enum(OutreachStatus::class)],
            'contacted_at' => ['nullable', 'date', 'before_or_equal:now'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** Suppressed businesses may have replies logged, but new outreach cannot be planned or recorded. */
    public function after(): array
    {
        return [function (Validator $validator) {
            $status = OutreachStatus::tryFrom((string) $this->input('status'));

            if (in_array($status, self::INITIATING_STATUSES, true) && $this->lead()->business->isSuppressed()) {
                $validator->errors()->add('status', 'This business is on the suppression list; new outreach cannot be recorded.');
            }
        }];
    }

    private function lead(): CampaignLead
    {
        /** @var CampaignLead */
        return $this->route('lead');
    }
}
