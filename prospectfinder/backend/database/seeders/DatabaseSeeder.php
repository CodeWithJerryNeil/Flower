<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeds clearly labelled demo data for local development only.
     * Production accounts are provisioned separately (Phase 2); demo leads must never mix with real ones.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo seeding is only allowed in local or testing environments.');
        }

        $this->call(DemoDataSeeder::class);
    }
}
