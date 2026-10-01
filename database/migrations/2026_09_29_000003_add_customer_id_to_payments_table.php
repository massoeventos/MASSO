<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerIdToPaymentsTable extends Migration
{
    /**
     * Sin backfill histórico a propósito (decisión tomada en el plan):
     * los pagos existentes quedan con customer_id = null. Solo las
     * compras nuevas, hechas después de este cambio, quedan vinculadas
     * a un Customer.
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('customer_id')->nullable()->after('id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
}
