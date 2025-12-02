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
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lead_id');
            $table->date('invoice_date');
            $table->string('invoice_no');
            $table->string('order_no')->nullable();
            $table->string('submission_no')->nullable();
            $table->string('po_no')->nullable();
            $table->json('line_items'); // Array of items with details, qty, price, total
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('lead_id')
                ->references('id')
                ->on('leads')
                ->onDelete('cascade');
                
            // Indexes
            $table->index(['lead_id']);
            $table->index(['invoice_no']);
            $table->index(['invoice_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
