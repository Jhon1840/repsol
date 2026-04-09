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
        Schema::table('riders', function (Blueprint $table) {
            $table->string('branch')->nullable()->index();
        });

        Schema::table('rider_movements', function (Blueprint $table) {
            $table->string('branch')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rider_movements', function (Blueprint $table) {
            $table->dropColumn('branch');
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn('branch');
        });
    }
};
