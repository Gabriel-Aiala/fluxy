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
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id'); 
            $table->unsignedBigInteger('transaction_group_id');
            $table->unsignedBigInteger('bank_account_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->unsignedBigInteger('counterparty_id');
            $table->unsignedBigInteger('category_id');
          
            $table->smallInteger('installment_number');
            $table->date('expected_payment_date');
            $table->date('payment_date');
            $table->decimal('amount',10,2);
            $table->enum('payment_status', ['paid', 'payable']);
            $table->enum('expense_type', ['professional', 'personal']);
            $table->enum('type', ['income', 'expense']);
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organization');
            $table->foreign('transaction_group_id')->references('id')->on('transaction_group');
            $table->foreign('bank_account_id')->references('id')->on('bank_account');
            $table->foreign('payment_method_id')->references('id')->on('payment_method');
            $table->foreign('counterparty_id')->references('id')->on('counterparties');
            $table->foreign('category_id')->references('id')->on('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction');
    }
};
