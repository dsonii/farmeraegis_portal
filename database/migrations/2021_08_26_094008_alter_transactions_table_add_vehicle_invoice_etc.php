<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTransactionsTableAddVehicleInvoiceEtc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->text('supplier_invoice_number')->after('selling_price_group_id')->nullable();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->text('vehicle_number')->after('supplier_invoice_number')->nullable();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->date('supplier_invoice_date')->after('vehicle_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
