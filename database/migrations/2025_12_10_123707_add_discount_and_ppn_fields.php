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
        Schema::table('quotations', function (Blueprint $table) {
            $table->boolean('has_ppn')->default(false)->after('total_amount');
            $table->decimal('ppn_amount', 15, 2)->default(0)->after('has_ppn');
            $table->decimal('subtotal_amount', 15, 2)->default(0)->after('total_amount');
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('discount', 5, 2)->default(0)->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['has_ppn', 'ppn_amount', 'subtotal_amount']);
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }
};
