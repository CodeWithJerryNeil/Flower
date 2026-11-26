<?php

namespace Tests\Feature\Database;

use App\Models\Business;
use App\Models\BusinessProviderIdentity;
use App\Models\Contact;
use App\Models\SocialProfile;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeduplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_identity_is_unique_per_provider(): void
    {
        BusinessProviderIdentity::factory()->create(['provider' => 'google_places', 'external_id' => 'abc']);

        $this->expectException(UniqueConstraintViolationException::class);
        BusinessProviderIdentity::factory()->create(['provider' => 'google_places', 'external_id' => 'abc']);
    }

    public function test_same_external_id_is_allowed_for_different_providers(): void
    {
        BusinessProviderIdentity::factory()->create(['provider' => 'google_places', 'external_id' => 'abc']);
        BusinessProviderIdentity::factory()->create(['provider' => 'yelp', 'external_id' => 'abc']);

        $this->assertDatabaseCount('business_provider_identities', 2);
    }

    public function test_businesses_may_share_a_normalized_name(): void
    {
        Business::factory()->count(2)->create(['normalized_name' => 'corner cafe']);

        $this->assertDatabaseCount('businesses', 2);
    }

    public function test_contact_email_is_normalized(): void
    {
        $contact = Contact::factory()->create(['email' => '  Hello@Example.COM ']);

        $this->assertSame('hello@example.com', $contact->email);
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'email' => 'hello@example.com']);
    }

    public function test_duplicate_contact_email_for_same_business_is_rejected_regardless_of_case(): void
    {
        $business = Business::factory()->create();
        Contact::factory()->for($business)->create(['email' => 'hello@example.com']);

        $this->expectException(UniqueConstraintViolationException::class);
        Contact::factory()->for($business)->create(['email' => 'HELLO@example.com']);
    }

    public function test_first_or_create_does_not_duplicate_contacts(): void
    {
        $business = Business::factory()->create();
        $attributes = ['contact_type' => 'business_email', 'discovered_at' => now()];

        $business->contacts()->firstOrCreate(['email' => 'info@example.com'], $attributes);
        $business->contacts()->firstOrCreate(['email' => 'info@example.com'], $attributes);

        $this->assertSame(1, $business->contacts()->count());
    }

    public function test_same_email_is_allowed_at_different_businesses(): void
    {
        Contact::factory()->create(['email' => 'shared@example.com']);
        Contact::factory()->create(['email' => 'shared@example.com']);

        $this->assertDatabaseCount('contacts', 2);
    }

    public function test_duplicate_social_profile_for_same_business_is_rejected(): void
    {
        $profile = SocialProfile::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);
        SocialProfile::factory()->for($profile->business)->create([
            'platform' => $profile->platform,
            'profile_url' => $profile->profile_url,
        ]);
    }
}
