<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsFileTable extends Migration
{
    
    public function up()
    {
        Schema::create('events_file', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id')->index();
            $table->string('uuid')->index();
            $table->string('name');
            $table->string('file');
            $table->string('extension');
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
        Schema::drop('events_file');
    }

}
