<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments_detail', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type');
            $table->integer('payment_id');
            $table->integer('ticket_id');
            $table->integer('price');
            $table->timestamps();
            $table->softDeletes();

            $table->index('payment_id', 'ticket_id');
        });
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
