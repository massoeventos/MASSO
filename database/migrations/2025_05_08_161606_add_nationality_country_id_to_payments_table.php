<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNationalityCountryIdToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->integer('nationality_country_id')->unsigned()->nullable()->after('city_id');
            $table->foreign('nationality_country_id')->references('id')->on('countries')->onDelete('restrict');        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['nationality_country_id']);
            $table->dropColumn('nationality_country_id');
        });
    }
}
