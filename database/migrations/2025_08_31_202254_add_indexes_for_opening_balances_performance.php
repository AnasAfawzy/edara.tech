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
        // إضافة فهارس لجدول journal_entries
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index(['source_type', 'financial_year_id'], 'idx_journal_entries_source_financial');
            $table->index(['created_at', 'source_type'], 'idx_journal_entries_created_source');
            $table->index('financial_year_id', 'idx_journal_entries_financial_year');
        });

        // إضافة فهارس لجدول opening_balances
        Schema::table('opening_balances', function (Blueprint $table) {
            $table->index('financial_year_id', 'idx_opening_balances_financial_year');
            $table->index(['financial_year_id', 'account_id'], 'idx_opening_balances_financial_account');
            $table->index('account_id', 'idx_opening_balances_account');
        });

        // إضافة فهارس لجدول accounts
        Schema::table('accounts', function (Blueprint $table) {
            $table->index(['slave', 'has_sub'], 'idx_accounts_slave_has_sub');
            $table->index('ownerEl', 'idx_accounts_owner_el');
            $table->index('code', 'idx_accounts_code');
        });

        // إضافة فهارس لجدول journal_entry_details
        Schema::table('journal_entry_details', function (Blueprint $table) {
            $table->index('journal_entry_id', 'idx_journal_entry_details_entry');
            $table->index('account_id', 'idx_journal_entry_details_account');
            $table->index(['journal_entry_id', 'account_id'], 'idx_journal_entry_details_entry_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex('idx_journal_entries_source_financial');
            $table->dropIndex('idx_journal_entries_created_source');
            $table->dropIndex('idx_journal_entries_financial_year');
        });

        Schema::table('opening_balances', function (Blueprint $table) {
            $table->dropIndex('idx_opening_balances_financial_year');
            $table->dropIndex('idx_opening_balances_financial_account');
            $table->dropIndex('idx_opening_balances_account');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex('idx_accounts_slave_has_sub');
            $table->dropIndex('idx_accounts_owner_el');
            $table->dropIndex('idx_accounts_code');
        });

        Schema::table('journal_entry_details', function (Blueprint $table) {
            $table->dropIndex('idx_journal_entry_details_entry');
            $table->dropIndex('idx_journal_entry_details_account');
            $table->dropIndex('idx_journal_entry_details_entry_account');
        });
    }
};
