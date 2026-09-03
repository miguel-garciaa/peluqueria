<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('notifications');

        if (Schema::hasColumn('bookings', 'push_reminder_sent_at')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->dropColumn('push_reminder_sent_at');
            });
        }
    }

    public function down(): void
    {
        // La funcionalidad Web Push eliminada no se recrea durante un rollback.
    }
};
