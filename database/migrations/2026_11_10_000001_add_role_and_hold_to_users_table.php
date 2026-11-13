<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Feature 3: new accounts are "driver" unless the seeder (or the database) says otherwise.
            $table->string('role')->default('driver');

            // Set every time the account signs in (shown on the Accounts page).
            $table->timestamp('last_login_at')->nullable();

            // Filled in while a dispatcher has the account on hold (account moderation).
            $table->timestamp('suspended_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'last_login_at', 'suspended_at']);
        });
    }
};
