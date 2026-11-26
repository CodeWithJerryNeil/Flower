<?php

namespace App\Http\Requests;

use App\Enums\CampaignStatus;
use App\Http\Requests\Concerns\ValidatesCampaignSettings;
use App\Models\Campaign;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class UpdateCampaignRequest extends FormRequest
{
    use ValidatesCampaignSettings;

    /** Returns the policy response so ownership denials keep their 404 status. */
    public function authorize(): Response
    {
        return Gate::inspect('update', $this->campaign());
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            ...$this->settingsRules(partial: true),
        ];
    }

    /** Search settings are frozen once a campaign has started; only the name can change. */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($this->campaign()->status === CampaignStatus::Pending) {
                return;
            }

            foreach (self::SETTINGS_KEYS as $key) {
                if ($this->has($key)) {
                    $validator->errors()->add($key, 'Campaign settings can only be changed before the campaign starts.');
                }
            }
        }];
    }

    private function campaign(): Campaign
    {
        /** @var Campaign */
        return $this->route('campaign');
    }
}
