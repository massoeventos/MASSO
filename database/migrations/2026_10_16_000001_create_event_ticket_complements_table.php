<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventTicketComplementsTable extends Migration
{
    /**
     * Qué tickets principales habilitan cada complementario (ej. el taller X
     * solo con la entrada de Médicos). Un complementario sin filas está
     * disponible con todas las entradas principales del evento.
     */
    public function up()
    {
        Schema::create('event_ticket_complements', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('principal_ticket_id');
            $table->unsignedInteger('complement_ticket_id');

            $table->unique(['principal_ticket_id', 'complement_ticket_id'], 'event_ticket_complements_unique');
            $table->index('complement_ticket_id');
            $table->foreign('principal_ticket_id')->references('id')->on('events_tickets')->onDelete('cascade');
            $table->foreign('complement_ticket_id')->references('id')->on('events_tickets')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_ticket_complements');
    }
}
