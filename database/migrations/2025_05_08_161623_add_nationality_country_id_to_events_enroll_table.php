<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNationalityCountryIdToEventsEnrollTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events_enroll', function (Blueprint $table) {
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
        Schema::table('events_enroll', function (Blueprint $table) {
            $table->dropForeign(['nationality_country_id']);
            $table->dropColumn('nationality_country_id');
        });
    }
}
