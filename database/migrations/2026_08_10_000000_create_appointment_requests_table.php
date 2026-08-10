<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('phone', 32);
            $table->string('service_id', 32);
            $table->date('requested_date')->index();
            $table->string('time_slot', 5);
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_requests');
    }
};
