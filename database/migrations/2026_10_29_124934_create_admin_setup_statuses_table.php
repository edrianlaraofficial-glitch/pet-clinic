<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_setup_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->timestamp('completed_at')->nullable();
        });

        DB::table('admin_setup_statuses')->insert(['id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_setup_statuses');
    }
};
