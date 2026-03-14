<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_records', function (Blueprint $table) {
            $table->decimal('month_4', 15, 2)->nullable()->after('month_3');
            $table->decimal('month_5', 15, 2)->nullable()->after('month_4');
            $table->decimal('month_6', 15, 2)->nullable()->after('month_5');
            $table->decimal('month_7', 15, 2)->nullable()->after('month_6');
            $table->decimal('month_8', 15, 2)->nullable()->after('month_7');
            $table->decimal('month_9', 15, 2)->nullable()->after('month_8');
            $table->decimal('month_10', 15, 2)->nullable()->after('month_9');
            $table->decimal('month_11', 15, 2)->nullable()->after('month_10');
            $table->decimal('month_12', 15, 2)->nullable()->after('month_11');
            $table->string('source_sheet')->nullable()->after('bal_to_achv');
            $table->unsignedInteger('source_row')->nullable()->after('source_sheet');
        });
    }

    public function down(): void
    {
        Schema::table('sales_records', function (Blueprint $table) {
            $table->dropColumn([
                'month_4',
                'month_5',
                'month_6',
                'month_7',
                'month_8',
                'month_9',
                'month_10',
                'month_11',
                'month_12',
                'source_sheet',
                'source_row',
            ]);
        });
    }
};
