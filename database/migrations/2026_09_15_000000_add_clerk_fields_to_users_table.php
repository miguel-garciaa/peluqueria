<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('clerk_user_id')->nullable()->unique()->after('google_id');
            $table->timestamp('clerk_synced_at')->nullable()->after('clerk_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['clerk_user_id']);
            $table->dropColumn(['clerk_user_id', 'clerk_synced_at']);
        });
    }
};
