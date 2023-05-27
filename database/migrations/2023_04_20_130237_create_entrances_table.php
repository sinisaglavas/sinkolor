<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntrancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrances', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->string('article');
            $table->decimal('pcs', 10, 2, true);
            $table->decimal('purchase_price', 10, 2, true);
            $table->decimal('rebate', 5, 2, true)->nullable();
            $table->decimal('discount', 5, 2, true)->nullable();
            $table->integer('tax')->nullable();
            $table->decimal('sum', 10, 2, true);
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices');
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
        Schema::dropIfExists('entrances');
    }
}
