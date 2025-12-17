<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPurchaseOrderFieldsToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('purchase_order_type')->nullable()->after('user_observation');
            $table->string('purchase_order_number')->nullable()->after('purchase_order_type');
            $table->string('purchase_order_file')->nullable()->after('purchase_order_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('purchase_order_file');
            $table->dropColumn('purchase_order_number');
            $table->dropColumn('purchase_order_type');
        });
    }
}
