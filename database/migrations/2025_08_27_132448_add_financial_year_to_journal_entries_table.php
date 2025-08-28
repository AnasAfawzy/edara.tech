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
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('financial_year_id')->nullable()->after('id');
            $table->foreign('financial_year_id')->references('id')->on('financial_years');
            $table->index(['entry_date', 'financial_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            // حذف الـ index
            $table->dropIndex('journal_entries_entry_date_financial_year_id_index');

            // حذف الـ foreign key
            $table->dropForeign(['financial_year_id']);

            // حذف العمود
            $table->dropColumn('financial_year_id');
        });
    }
};
