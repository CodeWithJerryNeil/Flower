<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_provider_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->index()->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('external_id');
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_provider_identities');
    }
};
