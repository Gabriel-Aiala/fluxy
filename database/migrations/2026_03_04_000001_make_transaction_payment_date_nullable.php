<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaction', function (Blueprint $table) {
            $table->date('payment_date')->nullable()->change();
        });

        DB::table('transaction')
            ->where('payment_status', 'payable')
            ->update(['payment_date' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('transaction')
            ->whereNull('payment_date')
            ->update(['payment_date' => DB::raw('expected_payment_date')]);

        Schema::table('transaction', function (Blueprint $table) {
            $table->date('payment_date')->nullable(false)->change();
        });
    }
};
