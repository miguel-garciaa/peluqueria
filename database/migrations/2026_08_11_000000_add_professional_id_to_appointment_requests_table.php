<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_requests', function (Blueprint $table): void {
            $table->string('professional_id', 32)->default('any')->after('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_requests', function (Blueprint $table): void {
            $table->dropColumn('professional_id');
        });
    }
};
