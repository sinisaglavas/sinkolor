<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerOutputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_outputs', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->string('article');
            $table->decimal('pcs', 10,2,true);
            $table->decimal('price', 10, 2, true);
            $table->decimal('sum', 10, 2, true);
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('customer_invoices');
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
        Schema::dropIfExists('customer_outputs');
    }
}
