<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rider_movements', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->nullable()
                ->after('rider_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rider_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};
