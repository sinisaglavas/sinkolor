<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHttpStatusToEfakturaLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('efaktura_logs', function (Blueprint $table) {
            $table->string('http_status')->nullable()->after('sef_response');
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
            $table->dropColumn('http_status');
        });
    }
}
