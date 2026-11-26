<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            // Stored normalized (trimmed, lowercased) by the Contact model.
            $table->string('email');
            $table->string('role')->nullable();
            $table->string('contact_type');
            $table->string('verification_status')->default('unverified');
            $table->unsignedSmallInteger('confidence_score')->nullable();
            $table->text('source_url')->nullable();
            $table->timestamp('discovered_at');
            $table->timestamps();

            // Also serves as the business_id index.
            $table->unique(['business_id', 'email']);
            $table->index('email');
        });

        DB::statement('ALTER TABLE contacts ADD CONSTRAINT contacts_confidence_score_range CHECK (confidence_score BETWEEN 0 AND 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
