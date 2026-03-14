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
        Schema::create('sales_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_manager_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->decimal('yr_tgt', 15, 2)->nullable();
            $table->decimal('qtr_tgt', 15, 2)->nullable();
            $table->decimal('mon_tgt', 15, 2)->nullable();
            $table->decimal('month_1', 15, 2)->nullable(); // Jan/Apr
            $table->decimal('month_2', 15, 2)->nullable(); // Feb/May
            $table->decimal('month_3', 15, 2)->nullable(); // Mar/Jun
            $table->decimal('total_achieved', 15, 2)->nullable();
            $table->decimal('commit_month', 15, 2)->nullable();
            $table->decimal('percent_achvd_q1', 8, 4)->nullable();
            $table->decimal('bal_to_achv', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_records');
    }
};
