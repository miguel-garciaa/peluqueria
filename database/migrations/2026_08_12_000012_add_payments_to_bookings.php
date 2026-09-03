<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('payment_method', 20)->default('cash')->after('status')->index();
            $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_method');
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->cascadeOnDelete();
            $table->string('method', 20)->index();
            $table->string('status', 20)->default('pending')->index();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->timestamp('paid_at')->nullable()->index();
            $table->string('gateway_provider', 50)->nullable();
            $table->string('gateway_reference', 255)->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        $servicePrices = DB::table('services')->pluck('price_from', 'id');
        $now = now();

        DB::table('bookings')->orderBy('id')->chunkById(100, function ($bookings) use ($servicePrices, $now): void {
            foreach ($bookings as $booking) {
                $amount = $servicePrices->get($booking->service_id);

                DB::table('bookings')->where('id', $booking->id)->update([
                    'payment_amount' => $amount,
                ]);

                if ($booking->status !== 'completed') {
                    continue;
                }

                DB::table('payments')->insertOrIgnore([
                    'booking_id' => $booking->id,
                    'method' => 'cash',
                    'status' => 'paid',
                    'amount' => $amount,
                    'currency' => 'EUR',
                    'paid_at' => $booking->completed_at ?? $booking->ends_at,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }, 'id');
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn(['payment_method', 'payment_amount']);
        });
    }
};
