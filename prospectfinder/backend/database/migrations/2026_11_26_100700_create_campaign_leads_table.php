<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->index()->constrained()->cascadeOnDelete();
            $table->string('qualification_status')->default('pending')->index();
            $table->unsignedSmallInteger('score')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Also serves as the campaign_id index.
            $table->unique(['campaign_id', 'business_id']);
        });

        DB::statement('ALTER TABLE campaign_leads ADD CONSTRAINT campaign_leads_score_range CHECK (score BETWEEN 0 AND 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_leads');
    }
};
