<?php

namespace Tests\Feature\Database;

use App\Enums\WebsiteStatus;
use App\Models\Business;
use App\Models\WebsiteCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteCheckHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_check_history_is_preserved(): void
    {
        $business = Business::factory()->create();

        WebsiteCheck::factory()->for($business)->create([
            'status' => WebsiteStatus::Inaccessible,
            'checked_url' => 'https://corner-cafe.example',
            'http_status' => 503,
            'evidence' => ['reason' => 'timeout'],
            'checked_at' => now()->subDay(),
        ]);
        WebsiteCheck::factory()->for($business)->create([
            'status' => WebsiteStatus::HasWebsite,
            'checked_url' => 'https://corner-cafe.example',
            'http_status' => 200,
            'checked_at' => now(),
        ]);

        $history = $business->websiteChecks()->orderBy('checked_at')->get();

        $this->assertCount(2, $history);
        $this->assertSame(WebsiteStatus::Inaccessible, $history[0]->status);
        $this->assertSame(['reason' => 'timeout'], $history[0]->evidence);
        $this->assertSame(WebsiteStatus::HasWebsite, $history[1]->status);
    }
}
