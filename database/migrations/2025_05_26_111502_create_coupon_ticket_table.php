<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponTicketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_ticket', function (Blueprint $table) {
            $table->unsignedInteger('coupon_id');
            $table->unsignedInteger('event_ticket_id');

            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->foreign('event_ticket_id')->references('id')->on('events_tickets')->onDelete('cascade');

            $table->primary(['coupon_id', 'event_ticket_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon_ticket');
    }
}
