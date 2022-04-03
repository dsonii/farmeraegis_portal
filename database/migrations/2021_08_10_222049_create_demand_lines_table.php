<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDemandLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demand_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('transaction_id')->unsigned();
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->integer('variation_id')->unsigned();
            $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');
            $table->decimal('quantity', 22, 4);
            $table->decimal('pp_without_discount', 22, 4)->default(0)->comment('Demand price before inline discounts');
            $table->decimal('discount_percent', 5, 2)->default(0)->comment('Inline discount percentage');
            $table->decimal('demand_price', 22, 4);
            $table->decimal('demand_price_inc_tax', 22, 4)->default(0);
            $table->decimal('item_tax', 22, 4)->comment("Tax for one quantity");
            $table->integer('tax_id')->unsigned()->nullable();
            $table->foreign('tax_id')->references('id')->on('tax_rates')->onDelete('cascade');
            $table->integer('demand_order_line_id')->nullable();
            $table->decimal('quantity_sold', 22, 4)->default(0)->comment("Quanity sold from this Demand line");
            $table->decimal('quantity_adjusted', 22, 4)->default(0)->comment("Quanity adjusted in stock adjustment from this demand line");
            $table->decimal('quantity_returned', 22, 4)->default(0);
            $table->text('demand_order_ids')->nullable();
            $table->decimal('po_quantity_demanded', 22, 4)->default(0);
            $table->decimal('mfg_quantity_used', 22, 4)->default(0);
            $table->date('mfg_date')->nullable();
            $table->date('exp_date')->nullable();
            $table->string('lot_number')->nullable();
            $table->index('lot_number');
            $table->integer('sub_unit_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demand_lines');
    }
}
