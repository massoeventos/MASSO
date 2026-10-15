<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventTicketCategoryTicketTable extends Migration
{
    /**
     * Un ticket puede pertenecer a varias categorías (ej. un taller abierto
     * a Médicos y Residentes). Se llena desde events_tickets.category_id, que
     * queda como columna legada (la primera categoría del ticket).
     */
    public function up()
    {
        Schema::create('event_ticket_category_ticket', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->unsignedInteger('ticket_id');

            $table->unique(['category_id', 'ticket_id']);
            $table->index('ticket_id');
            $table->foreign('category_id')->references('id')->on('event_ticket_categories')->onDelete('cascade');
            $table->foreign('ticket_id')->references('id')->on('events_tickets')->onDelete('cascade');
        });

        DB::statement("
            INSERT INTO event_ticket_category_ticket (category_id, ticket_id)
            SELECT category_id, id FROM events_tickets WHERE category_id IS NOT NULL
        ");
    }

    public function down()
    {
        Schema::dropIfExists('event_ticket_category_ticket');
    }
}
