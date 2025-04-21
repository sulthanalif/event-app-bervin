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
        Schema::create('sellouts', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->nullable();
            $table->date('date')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->foreignId('sales_id')->constrained()->onDelete('cascade');
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellouts');
    }
};
