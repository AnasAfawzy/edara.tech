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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('name_en')->nullable();
            $table->string('code', 100)->unique();
            $table->string('barcode')->nullable()->unique();

            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('unit_id')->constrained()->onDelete('restrict');

            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('image')->nullable();

            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->default(0);

            $table->decimal('min_stock', 10, 3)->nullable();
            $table->decimal('max_stock', 10, 3)->nullable();
            $table->decimal('current_stock', 10, 3)->default(0);
            $table->decimal('reorder_level', 10, 3)->nullable();

            $table->boolean('has_expiry')->default(false);
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['name', 'is_active']);
            $table->index(['current_stock', 'min_stock']);
            $table->index(['category_id', 'is_active']);
            $table->index(['code']);
            $table->index(['barcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
