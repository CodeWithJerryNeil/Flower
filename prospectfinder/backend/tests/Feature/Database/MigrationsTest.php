<?php

namespace Tests\Feature\Database;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Runs the full migration set against a clean database, rolls it all back, and re-applies it.
 * Deliberately does not use RefreshDatabase; it leaves a freshly migrated schema behind.
 */
class MigrationsTest extends TestCase
{
    private const DOMAIN_TABLES = [
        'campaigns', 'businesses', 'business_provider_identities', 'contacts', 'social_profiles',
        'website_checks', 'prospect_sources', 'campaign_leads', 'research_jobs',
        'outreach_activities', 'suppression_list',
    ];

    public function test_migrations_run_from_clean_database_and_roll_back(): void
    {
        $this->artisan('migrate:fresh')->assertSuccessful();
        foreach (self::DOMAIN_TABLES as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table {$table}");
        }

        $this->artisan('migrate:reset')->assertSuccessful();
        foreach (self::DOMAIN_TABLES as $table) {
            $this->assertFalse(Schema::hasTable($table), "Table {$table} not dropped");
        }

        $this->artisan('migrate')->assertSuccessful();
        $this->assertTrue(Schema::hasTable('suppression_list'));
    }
}
