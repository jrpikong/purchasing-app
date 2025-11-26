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
        Schema::create('vendor_quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_request_id');
            $table->unsignedBigInteger('vendor_id');
            $table->string('quotation_number', 100)->nullable();
            $table->date('quotation_date');
            $table->date('valid_until');
            $table->decimal('total_amount', 15, 2);
            $table->string('file_path', 500)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->boolean('is_selected')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors');

            $table->index('purchase_request_id');
            $table->index('vendor_id');
            $table->index('quotation_date');
            $table->index('valid_until');
            $table->index('is_selected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_quotations');
    }
};
