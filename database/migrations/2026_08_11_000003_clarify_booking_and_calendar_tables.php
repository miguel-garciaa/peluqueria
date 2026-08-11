<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('appointments', 'bookings');
        Schema::rename('appointment_requests', 'legacy_appointment_requests');

        Schema::create('professional_calendar_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('type', ['blocked', 'available']);
            $table->boolean('all_day')->default(false);
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->unsignedTinyInteger('slot_interval_minutes')->default(30);
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->index(['professional_id', 'date', 'type'], 'professional_calendar_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_calendar_entries');
        Schema::rename('legacy_appointment_requests', 'appointment_requests');
        Schema::rename('bookings', 'appointments');
    }
};
