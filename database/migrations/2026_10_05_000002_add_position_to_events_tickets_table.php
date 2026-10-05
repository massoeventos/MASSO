<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPositionToEventsTicketsTable extends Migration
{
    /**
     * Orden manual de los tickets dentro del evento (igual que en
     * event_ticket_categories). Los tickets existentes quedan en 0 y se
     * desempatan por id, así que mantienen el orden que tenían.
     */
    public function up()
    {
        Schema::table('events_tickets', function (Blueprint $table) {
            $table->integer('position')->default(0)->after('category_id');
        });
    }

    public function down()
    {
        Schema::table('events_tickets', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
}
