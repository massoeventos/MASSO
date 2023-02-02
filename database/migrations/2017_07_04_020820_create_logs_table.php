<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogsTable extends Migration
{

    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->string('area')->index();
            $table->string('module')->index();
            $table->string('action');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('logs');
    }

}
