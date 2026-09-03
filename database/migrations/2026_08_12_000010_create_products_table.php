<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('sku', 80)->nullable()->unique();
            $table->string('category', 40)->index();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('units')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(3);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['is_active', 'units']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
