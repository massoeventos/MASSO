<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateEventImagesTable extends Migration
{
    public function up()
    {
        Schema::create('event_images', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id')->unsigned();
            $table->string('path');
            $table->string('type')->default('footer'); // banner | footer
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->index(['event_id', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_images');
    }
}
