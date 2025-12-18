<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('device_token', 64)->unique();
            $table->unsignedInteger('last_payment_id')->nullable();
            $table->unsignedInteger('last_inscription_payment_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_profiles');
    }
}
