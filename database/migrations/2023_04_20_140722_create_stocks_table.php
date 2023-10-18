<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->string('article');
            $table->string('unit');
            $table->decimal('purchase_price', 10, 2, true)->nullable();
            $table->decimal('margin', 10, 2)->nullable();
            $table->decimal('price', 10, 2, true)->nullable();
            $table->decimal('pcs', 10, 2)->nullable(); // bez "true" ako zelis da decimalni broj ide u minus
            $table->decimal('sum', 10, 2)->nullable();
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
        Schema::dropIfExists('stocks');
    }
}
