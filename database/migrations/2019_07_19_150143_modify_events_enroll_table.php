<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyEventsEnrollTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events_enroll', function (Blueprint $table) {

            $table->dropColumn('name');
            $table->dropColumn('slug');
            $table->dropColumn('date_init');
            $table->dropColumn('date_finish');
            $table->dropColumn('description');
            $table->dropColumn('status');




        });

        Schema::table('events_enroll', function (Blueprint $table) {

            $table->string('name')->after('event_id');
            $table->string('lastname')->after('name');
            $table->string('passport')->after('lastname');
            $table->string('email')->after('passport');
            $table->string('phone')->after('email');
            $table->string('profession')->after('phone');
            $table->string('speciality')->after('profession');
            $table->string('workplace')->after('speciality');
            $table->string('city')->after('workplace');
            $table->string('country')->after('city');
            $table->string('ticket_id')->after('country');


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

            $table->dropColumn('name');
            $table->dropColumn('lastname');
            $table->dropColumn('passport');
            $table->dropColumn('email');
            $table->dropColumn('phone');
            $table->dropColumn('profession');
            $table->dropColumn('speciality');
            $table->dropColumn('workplace');
            $table->dropColumn('city');
            $table->dropColumn('country');
            $table->dropColumn('ticket_id');

  

        });

        Schema::table('events_enroll', function (Blueprint $table) {



            $table->string('name');
            $table->string('slug')->index();
            $table->datetime('date_init')->index();
            $table->datetime('date_finish')->index();
            $table->text('description');
            $table->integer('status')->index();

        });
    }
}
