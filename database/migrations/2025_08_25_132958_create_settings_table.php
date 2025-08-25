<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_address')->nullable();
            $table->string('company_logo')->nullable();

            $table->string('tax_number')->nullable();
            $table->decimal('default_tax_rate', 5, 2)->nullable();

            $table->string('currency')->default('USD');
            $table->string('timezone')->default('UTC');
            $table->string('language')->default('en');

            $table->json('payment_methods')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
