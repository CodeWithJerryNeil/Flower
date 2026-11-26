<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only history: each detection run inserts a new row.
        Schema::create('website_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->text('checked_url')->nullable();
            $table->string('status');
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->boolean('is_social_domain')->default(false);
            $table->jsonb('evidence')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->index(['business_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_checks');
    }
};
