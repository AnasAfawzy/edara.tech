<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('warehouses')->where('status', 'active')->update(['status' => '1']);
        DB::table('warehouses')->where('status', 'inactive')->update(['status' => '0']);

        Schema::table('warehouses', function (Blueprint $table) {
            $table->boolean('status')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->change();
        });

        DB::table('warehouses')->where('status', '1')->update(['status' => 'active']);
        DB::table('warehouses')->where('status', '0')->update(['status' => 'inactive']);
    }
};
