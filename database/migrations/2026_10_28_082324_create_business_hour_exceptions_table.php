<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('business_hour_exceptions', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->boolean('is_closed')->default(true);
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->string('reason')->nullable(); // e.g., "Christmas", "Staff training", "Vacation"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_hour_exceptions');
    }
};