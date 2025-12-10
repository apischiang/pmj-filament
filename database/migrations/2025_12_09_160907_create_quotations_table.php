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
        Schema::create('quotations', function (Blueprint $table) {
            // Primary Key: UUID
            $table->uuid('uid')->primary();

            // Foreign Key: Customer
            $table->uuid('customer_uid')->index();
            
            // Quotation Number: Unique string (PMJ-{inisial perusahaan}-{bulan}-{nomor})
            $table->string('quotation_number')->unique()->index();
            
            // Dates
            $table->date('date'); // Tanggal pembuatan
            
            // Status: draft, sent
            $table->string('status')->default('draft')->comment('Status: draft, sent');
            
            // Financials
            $table->decimal('total_amount', 15, 2);
            
            // Details
            $table->text('terms_and_conditions')->nullable();
            
            // Meta
            $table->string('created_by')->comment('Nama user pembuat');

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Constraints
            $table->foreign('customer_uid')
                  ->references('uid')
                  ->on('customers')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
