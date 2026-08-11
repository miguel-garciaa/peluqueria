<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('professional_schedules', function (Blueprint $table): void {
            $table->ulid('group_id')->nullable()->after('id');
        });

        DB::table('professional_schedules')
            ->select([
                'id', 'professional_id', 'starts_at', 'ends_at',
                'slot_interval_minutes', 'is_active',
            ])
            ->orderBy('id')
            ->get()
            ->groupBy(fn (object $schedule): string => implode('|', [
                $schedule->professional_id,
                $schedule->starts_at,
                $schedule->ends_at,
                $schedule->slot_interval_minutes,
                in_array($schedule->is_active, [true, 1, '1', 't', 'true'], true) ? 1 : 0,
            ]))
            ->each(function ($schedules): void {
                DB::table('professional_schedules')
                    ->whereIn('id', $schedules->pluck('id'))
                    ->update(['group_id' => (string) Str::ulid()]);
            });

        Schema::table('professional_schedules', function (Blueprint $table): void {
            $table->index('group_id', 'professional_schedules_group_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('professional_schedules', function (Blueprint $table): void {
            $table->dropIndex('professional_schedules_group_id_idx');
            $table->dropColumn('group_id');
        });
    }
};
