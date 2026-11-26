<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Not unique: different businesses can share a normalized name. Provider identities dedupe.
            $table->string('normalized_name')->index();
            $table->string('category')->nullable()->index();
            $table->text('address')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone')->nullable();
            $table->text('website_url')->nullable();
            $table->string('website_status')->default('unknown')->index();
            $table->string('business_status')->default('unknown');
            $table->unsignedSmallInteger('lead_score')->nullable();
            $table->timestamp('last_researched_at')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE businesses ADD CONSTRAINT businesses_lead_score_range CHECK (lead_score BETWEEN 0 AND 100)');
        DB::statement('ALTER TABLE businesses ADD CONSTRAINT businesses_latitude_range CHECK (latitude BETWEEN -90 AND 90)');
        DB::statement('ALTER TABLE businesses ADD CONSTRAINT businesses_longitude_range CHECK (longitude BETWEEN -180 AND 180)');
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
