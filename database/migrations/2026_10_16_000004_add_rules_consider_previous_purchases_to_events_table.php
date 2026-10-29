<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRulesConsiderPreviousPurchasesToEventsTable extends Migration
{
    /**
     * Si las reglas "por compra" suman lo que el comprador ya compró en el
     * evento (ej. máximo 1 inscripción por persona entre todas sus compras).
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('rules_consider_previous_purchases')->default(false)->after('max_selection_ticket');
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('rules_consider_previous_purchases');
        });
    }
}
