<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEventsEnroll extends Migration
{
    
    public function up()
    {
        Schema::create('events_enroll', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('event_id')->index();
            $table->string('slug')->index();
            $table->datetime('date_init')->index();
            $table->datetime('date_finish')->index();
            $table->text('description');
            $table->integer('status')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('events_enroll');
    }
}
