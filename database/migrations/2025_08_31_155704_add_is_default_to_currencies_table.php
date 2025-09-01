<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table('currencies', function (Blueprint $table) {
            if (!Schema::hasColumn('currencies', 'is_default')) {
                // إضافة العمود في نهاية الجدول بدلاً من بعد is_active
                $table->boolean('is_default')->default(false);
                $table->index('is_default');
            }
        });

        // تحديد العملة الأولى كافتراضية إذا لم توجد عملة افتراضية
        $hasDefaultCurrency = DB::table('currencies')->where('is_default', true)->exists();

        if (!$hasDefaultCurrency) {
            $firstCurrency = DB::table('currencies')->orderBy('id')->first();
            if ($firstCurrency) {
                DB::table('currencies')
                    ->where('id', $firstCurrency->id)
                    ->update(['is_default' => true]);
            }
        }
    }

    public function down()
    {
        Schema::table('currencies', function (Blueprint $table) {
            if (Schema::hasColumn('currencies', 'is_default')) {
                $table->dropIndex(['is_default']);
                $table->dropColumn('is_default');
            }
        });
    }
};
