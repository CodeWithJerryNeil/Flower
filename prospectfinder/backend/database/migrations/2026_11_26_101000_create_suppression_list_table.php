<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppression_list', function (Blueprint $table) {
            $table->id();
            // Stored normalized (trimmed, lowercased). Matches contacts at any business.
            $table->string('email')->nullable()->index();
            // Restrict: a suppressed business cannot be deleted silently, which would drop its opt-out.
            $table->foreignId('business_id')->nullable()->index()->constrained()->restrictOnDelete();
            $table->string('reason');
            $table->string('source');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE suppression_list ADD CONSTRAINT suppression_list_target_present CHECK (email IS NOT NULL OR business_id IS NOT NULL)');
    }

    public function down(): void
    {
        Schema::dropIfExists('suppression_list');
    }
};
