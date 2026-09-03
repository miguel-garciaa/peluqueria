<?php

use App\Support\SpanishPhone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeColumn('users', 'phone');
        $this->normalizeColumn('bookings', 'customer_phone');
    }

    public function down(): void
    {
        // The previous spacing cannot be reconstructed reliably.
    }

    private function normalizeColumn(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)
            ->select(['id', $column])
            ->whereNotNull($column)
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($table, $column): void {
                foreach ($rows as $row) {
                    $formatted = SpanishPhone::format($row->{$column});

                    if ($formatted !== $row->{$column}) {
                        DB::table($table)
                            ->where('id', $row->id)
                            ->update([$column => $formatted]);
                    }
                }
            });
    }
};
