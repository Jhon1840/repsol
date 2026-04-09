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
            $table->foreignId('created_by')
                ->nullable()
                ->after('rango')
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->after('created_by')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('creation_source')
                ->default('manual')
                ->after('updated_by');
        });

        Schema::table('rider_movements', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('rider_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rider_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('creation_source');
        });
    }
};
