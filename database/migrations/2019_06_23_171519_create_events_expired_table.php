<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsExpiredTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events_expired', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->datetime('date_init')->index();
            $table->datetime('date_finish')->index();
            $table->text('description');
            $table->string('photo');
            $table->string('location');
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
        Schema::drop('events_expired');
    }
}
