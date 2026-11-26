<?php

namespace Database\Factories;

use App\Enums\ContactType;
use App\Enums\VerificationStatus;
use App\Models\Business;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Contact> */
class ContactFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => null,
            'email' => fake()->unique()->safeEmail(),
            'role' => null,
            'contact_type' => ContactType::BusinessEmail,
            'verification_status' => VerificationStatus::Unverified,
            'confidence_score' => fake()->numberBetween(50, 100),
            'source_url' => 'https://'.fake()->slug(2).'.example/contact',
            'discovered_at' => now(),
        ];
    }
}
