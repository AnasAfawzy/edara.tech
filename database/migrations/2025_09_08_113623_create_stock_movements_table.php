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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('movement_type', ['in', 'out']);
            $table->enum('transaction_type', [
                'opening_stock',
                'purchase',
                'sale',
                'adjustment',
                'transfer_in',
                'transfer_out',
                'production',
                'damage',
                'return_purchase',
                'return_sale'
            ]);
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total_cost', 15, 2);
            $table->decimal('current_balance', 15, 3);
            $table->morphs('reference');
            $table->string('reference_number')->nullable();
            $table->date('movement_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['product_id', 'movement_date']);
            $table->index(['transaction_type', 'movement_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
