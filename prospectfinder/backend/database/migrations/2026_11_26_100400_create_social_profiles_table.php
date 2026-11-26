<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->text('profile_url');
            $table->string('username')->nullable();
            $table->text('source_url')->nullable();
            $table->unsignedSmallInteger('confidence_score')->nullable();
            $table->timestamp('discovered_at');
            $table->timestamps();

            $table->unique(['business_id', 'platform', 'profile_url']);
        });

        DB::statement('ALTER TABLE social_profiles ADD CONSTRAINT social_profiles_confidence_score_range CHECK (confidence_score BETWEEN 0 AND 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('social_profiles');
    }
};
