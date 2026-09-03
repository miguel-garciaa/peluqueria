<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 32)->nullable()->after('email');
        });

        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes');
            $table->decimal('price_from', 8, 2)->nullable();
            $table->boolean('is_custom')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('professionals', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name');
            $table->string('role')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('professional_service', function (Blueprint $table): void {
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['professional_id', 'service_id']);
        });

        Schema::create('professional_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->unsignedTinyInteger('slot_interval_minutes')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['professional_id', 'day_of_week', 'starts_at'], 'professional_schedule_unique');
            $table->index(['day_of_week', 'is_active']);
        });

        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->ulid('reference')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->foreignId('professional_id')->constrained()->restrictOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone', 32);
            $table->text('custom_details')->nullable();
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at');
            $table->string('status', 20)->default('confirmed');
            $table->timestamps();
            $table->index(['professional_id', 'starts_at']);
            $table->index(['user_id', 'starts_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('professional_schedules');
        Schema::dropIfExists('professional_service');
        Schema::dropIfExists('professionals');
        Schema::dropIfExists('services');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('phone');
        });
    }
};
