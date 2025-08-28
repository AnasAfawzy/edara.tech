<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // إنشاء جدول journal_entries
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->text('description');
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('restrict');
            $table->string('entry_number')->unique();
            $table->string('source_type')->default('manual'); // قيمة افتراضية
            $table->unsignedBigInteger('source_id')->default(0); // قيمة افتراضية
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });

        // إنشاء جدول journal_entry_details
        Schema::create('journal_entry_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('restrict');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->foreignId('cost_center_id')->nullable()->constrained('cost_centers')->onDelete('set null');
            $table->timestamps();

            $table->index(['journal_entry_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_details');
        Schema::dropIfExists('journal_entries');
    }
};
