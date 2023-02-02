<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events_tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id')->index();
            $table->string('name');
            $table->integer('price');
            $table->integer('stock');
            $table->date('from');
            $table->date('to');
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
        Schema::drop('events_tickets');
    }
}
