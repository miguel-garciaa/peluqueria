<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->timestampTz('push_reminder_sent_at')->nullable()->after('completed_at');
            $table->index(['status', 'push_reminder_sent_at', 'starts_at'], 'bookings_push_reminder_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex('bookings_push_reminder_lookup');
            $table->dropColumn('push_reminder_sent_at');
        });
    }
};
