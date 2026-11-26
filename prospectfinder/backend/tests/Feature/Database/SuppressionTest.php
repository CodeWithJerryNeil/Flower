<?php

namespace Tests\Feature\Database;

use App\Models\Business;
use App\Models\Contact;
use App\Models\SuppressionEntry;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuppressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_suppressed_by_email_is_identifiable(): void
    {
        $suppressed = Contact::factory()->create(['email' => 'optout@example.com']);
        $allowed = Contact::factory()->create();
        SuppressionEntry::factory()->create(['email' => 'OptOut@Example.com']);

        $this->assertTrue($suppressed->isSuppressed());
        $this->assertFalse($allowed->isSuppressed());
        $this->assertEqualsCanonicalizing([$suppressed->id], Contact::suppressed()->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$allowed->id], Contact::notSuppressed()->pluck('id')->all());
    }

    public function test_suppressing_a_business_suppresses_all_its_contacts(): void
    {
        $business = Business::factory()->create();
        Contact::factory()->count(2)->for($business)->create();
        $other = Contact::factory()->create();
        SuppressionEntry::factory()->create(['email' => null, 'business_id' => $business->id]);

        $this->assertTrue($business->isSuppressed());
        $this->assertSame(2, Contact::suppressed()->count());
        $this->assertSame([$other->id], Contact::notSuppressed()->pluck('id')->all());
    }

    public function test_email_suppression_applies_across_businesses(): void
    {
        Contact::factory()->count(2)->create(['email' => 'owner@example.com']);
        SuppressionEntry::factory()->create(['email' => 'owner@example.com']);

        $this->assertSame(2, Contact::suppressed()->count());
        $this->assertTrue(SuppressionEntry::coversEmail(' OWNER@example.com'));
    }

    public function test_suppression_entry_requires_email_or_business(): void
    {
        $this->expectException(QueryException::class);
        SuppressionEntry::factory()->create(['email' => null, 'business_id' => null]);
    }

    public function test_suppressed_business_cannot_be_deleted_silently(): void
    {
        $business = Business::factory()->create();
        SuppressionEntry::factory()->create(['business_id' => $business->id]);

        $this->expectException(QueryException::class);
        $business->delete();
    }
}
