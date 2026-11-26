<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('location_query');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country');
            $table->string('category');
            $table->unsignedInteger('target_count');
            $table->string('status')->default('pending')->index();
            $table->jsonb('filters')->default(new Expression("'{}'::jsonb"));
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });

        // The MVP upper bound (10) is enforced in request validation so it can change without a migration.
        DB::statement('ALTER TABLE campaigns ADD CONSTRAINT campaigns_target_count_positive CHECK (target_count >= 1)');
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
