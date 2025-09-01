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
        // إنشاء جدول الأرصدة الافتتاحية
        Schema::create('opening_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('financial_year_id')->constrained('financial_years')->onDelete('cascade');
            $table->decimal('debit_balance', 15, 2)->default(0.00);
            $table->decimal('credit_balance', 15, 2)->default(0.00);
            $table->date('balance_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // فهارس لتحسين الأداء
            $table->unique(['account_id', 'financial_year_id'], 'unique_account_financial_year');
            $table->index('financial_year_id');
            $table->index('balance_date');
            $table->index('created_by');
        });

        // إضافة عمود financial_year_id لجدول journal_entries إذا لم يكن موجود
        if (!Schema::hasColumn('journal_entries', 'financial_year_id')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->foreignId('financial_year_id')->nullable()->after('id')->constrained('financial_years')->onDelete('cascade');
                $table->index('financial_year_id');
            });
        }

        // إضافة عمود reference_number لجدول journal_entries إذا لم يكن موجود
        if (!Schema::hasColumn('journal_entries', 'reference_number')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->string('reference_number', 50)->nullable()->after('id');
                $table->index('reference_number');
            });
        }

        // إضافة عمود source_type لجدول journal_entries إذا لم يكن موجود
        if (!Schema::hasColumn('journal_entries', 'source_type')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->string('source_type', 50)->default('manual')->after('description');
                $table->index('source_type');
            });
        }

        // إضافة عمود status لجدول journal_entries إذا لم يكن موجود
        if (!Schema::hasColumn('journal_entries', 'status')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->enum('status', ['draft', 'posted', 'cancelled'])->default('posted')->after('source_type');
                $table->index('status');
            });
        }

        // إضافة أعمدة total_debit و total_credit لجدول journal_entries إذا لم تكن موجودة
        if (!Schema::hasColumn('journal_entries', 'total_debit')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->decimal('total_debit', 15, 2)->default(0.00)->after('currency_id');
                $table->decimal('total_credit', 15, 2)->default(0.00)->after('total_debit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف جدول الأرصدة الافتتاحية
        Schema::dropIfExists('opening_balances');

        // حذف الأعمدة المضافة من journal_entries
        Schema::table('journal_entries', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entries', 'financial_year_id')) {
                $table->dropForeign(['financial_year_id']);
                $table->dropColumn('financial_year_id');
            }

            if (Schema::hasColumn('journal_entries', 'reference_number')) {
                $table->dropColumn('reference_number');
            }

            if (Schema::hasColumn('journal_entries', 'source_type')) {
                $table->dropColumn('source_type');
            }

            if (Schema::hasColumn('journal_entries', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('journal_entries', 'total_debit')) {
                $table->dropColumn(['total_debit', 'total_credit']);
            }
        });
    }
};
