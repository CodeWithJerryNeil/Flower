<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('source_type');
            $table->text('source_url')->nullable();
            $table->string('field_name')->nullable();
            $table->jsonb('evidence')->nullable();
            $table->timestamp('collected_at');
            $table->timestamps();

            $table->index(['business_id', 'field_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_sources');
    }
};
