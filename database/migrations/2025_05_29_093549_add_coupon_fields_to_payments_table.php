<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCouponFieldsToPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('coupon_id')->nullable()->after('status');
            $table->unsignedTinyInteger('discount_percentage')->nullable()->after('coupon_id');
            $table->decimal('discount_amount', 10, 2)->nullable()->after('discount_percentage');

            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_id', 'discount_percentage', 'discount_amount']);
        });
    }
}
