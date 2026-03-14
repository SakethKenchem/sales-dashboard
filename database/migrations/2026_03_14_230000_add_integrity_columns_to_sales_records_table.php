<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_records', function (Blueprint $table) {
            if (! Schema::hasColumn('sales_records', 'source_file')) {
                $table->string('source_file')->nullable()->after('source_row');
            }

            if (! Schema::hasColumn('sales_records', 'row_hash')) {
                $table->char('row_hash', 64)->nullable()->after('source_file');
                $table->unique('row_hash', 'sales_records_row_hash_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_records', function (Blueprint $table) {
            if (Schema::hasColumn('sales_records', 'row_hash')) {
                $table->dropUnique('sales_records_row_hash_unique');
                $table->dropColumn('row_hash');
            }

            if (Schema::hasColumn('sales_records', 'source_file')) {
                $table->dropColumn('source_file');
            }
        });
    }
};
