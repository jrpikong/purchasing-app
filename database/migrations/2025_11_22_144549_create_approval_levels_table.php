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
        Schema::create('approval_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_flow_id');
            $table->integer('level_order');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->string('role_type')->default('specific_user');
            $table->string('role_name', 100)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('approval_flow_id')->references('id')->on('approval_flows')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('set null');

            $table->index(['approval_flow_id', 'level_order']);
            $table->index('approver_id');
            $table->index('role_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_levels');
    }
};
