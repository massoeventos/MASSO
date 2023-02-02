<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsInputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('events_inputs')){
            Schema::create('events_inputs', function (Blueprint $table) {
                $table->increments('id');
                $table->string('event_id')->index();;
                $table->string('name');
                $table->string('name_eng');
                $table->string('type');
                $table->boolean('required');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
