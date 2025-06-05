<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSefInvoiceIdToEfakturaLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('efaktura_logs', function (Blueprint $table) {
            $table->string('sef_invoice_id')->nullable()->after('sef_status');

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
            $table->dropColumn('sef_invoice_id');
        });
    }
}
