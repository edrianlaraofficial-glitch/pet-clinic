<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Roles live in their own table (never on the users table) so that
 * privilege can't be set by a signup form. Only admins are checked
 * server-side via User::hasRole().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20);
            $table->timestamps();

            $table->unique(['user_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
