<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSefFieldsToEfakturaLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('efaktura_logs', function (Blueprint $table) {
            $table->string('sef_sales_invoice_id')->nullable()->after('sef_response');
            $table->string('sef_purchase_invoice_id')->nullable()->after('sef_response');
            $table->uuid('sef_document_id')->nullable()->after('sef_response');
            $table->text('sef_response_raw')->nullable()->after('sef_response');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('efaktura_logs', function (Blueprint $table) {
            //
        });
    }
}
