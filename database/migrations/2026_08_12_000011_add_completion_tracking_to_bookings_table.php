<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->timestampTz('completed_at')->nullable()->after('cancelled_at')->index();
            $table->index(['status', 'ends_at'], 'bookings_completion_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex('bookings_completion_lookup');
            $table->dropColumn('completed_at');
        });
    }
};
