<?php

use App\Models\Rider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('riders')
            ->orderBy('id')
            ->get(['id', 'rider_id'])
            ->each(function (object $rider): void {
                $normalizedRiderId = Rider::normalizeRiderId($rider->rider_id);

                if ($normalizedRiderId === null || $normalizedRiderId === $rider->rider_id) {
                    return;
                }

                $alreadyExists = DB::table('riders')
                    ->where('rider_id', $normalizedRiderId)
                    ->where('id', '!=', $rider->id)
                    ->exists();

                if ($alreadyExists) {
                    return;
                }

                DB::table('riders')
                    ->where('id', $rider->id)
                    ->update(['rider_id' => $normalizedRiderId]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
