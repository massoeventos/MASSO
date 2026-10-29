<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaxPerPurchaseToEventsTicketsTable extends Migration
{
    /**
     * Unidades de un mismo ticket que se pueden comprar en una compra
     * (ej. 3 entradas iguales). 1 = como hasta ahora.
     */
    public function up()
    {
        Schema::table('events_tickets', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_per_purchase')->default(1)->after('capacity');
        });
    }

    public function down()
    {
        Schema::table('events_tickets', function (Blueprint $table) {
            $table->dropColumn('max_per_purchase');
        });
    }
}
