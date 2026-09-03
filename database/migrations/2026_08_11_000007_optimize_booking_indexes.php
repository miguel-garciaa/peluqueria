<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->index(
                ['professional_id', 'status', 'starts_at', 'ends_at'],
                'bookings_professional_availability_idx',
            );
            $table->index(['status', 'starts_at'], 'bookings_status_starts_at_idx');
            $table->index(['user_id', 'status', 'starts_at'], 'bookings_user_status_starts_at_idx');
            $table->index('created_at', 'bookings_created_at_idx');
            $table->index('cancelled_at', 'bookings_cancelled_at_idx');
        });

    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex('bookings_professional_availability_idx');
            $table->dropIndex('bookings_status_starts_at_idx');
            $table->dropIndex('bookings_user_status_starts_at_idx');
            $table->dropIndex('bookings_created_at_idx');
            $table->dropIndex('bookings_cancelled_at_idx');
        });
    }
};
