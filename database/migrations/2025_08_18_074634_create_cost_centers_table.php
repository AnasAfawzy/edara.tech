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
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->integer('position')->nullable();
            $table->integer('ownerEl')->nullable();
            $table->boolean('slave')->default(0);
            $table->string('code', 100)->unique();
            $table->integer('level')->nullable();
            $table->string('creditor', 255)->nullable();
            $table->string('debtor', 255)->nullable();
            $table->boolean('has_sub')->default(0);
            $table->boolean('is_sub')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_centers');
    }
};
