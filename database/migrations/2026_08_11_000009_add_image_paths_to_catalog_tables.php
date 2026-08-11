<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->string('image_path')->nullable()->after('description');
        });

        Schema::table('professionals', function (Blueprint $table): void {
            $table->string('image_path')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn('image_path');
        });

        Schema::table('professionals', function (Blueprint $table): void {
            $table->dropColumn('image_path');
        });
    }
};
