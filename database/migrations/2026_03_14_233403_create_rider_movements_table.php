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
        Schema::create('rider_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_document_id')->nullable()->constrained('uploaded_documents')->nullOnDelete();
            $table->string('movement_type')->default('purchase');
            $table->string('reference')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->integer('points')->default(0);
            $table->timestamp('occurred_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rider_movements');
    }
};
