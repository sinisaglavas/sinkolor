<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEfakturaLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('efaktura_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('sef_status')->nullable(); // npr: success, error
            $table->text('sef_response')->nullable(); // poruka koju vrati SEF
            $table->timestamp('sent_at')->nullable(); // kada je faktura poslata
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('customer_invoices')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('efaktura_logs');
    }
}
