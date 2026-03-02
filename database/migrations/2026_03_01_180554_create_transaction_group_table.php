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
        Schema::create('transaction_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->enum('type', ['income', 'expense']);
            $table->foreign('organization_id')->references('id')->on('organization');
            $table->string('description')->nullable();
            $table->date('occurred_on');
            $table->smallInteger('customer_installments');
            $table->smallInteger('flow_installments');
            $table->boolean('anticipation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_group');
    }
};
