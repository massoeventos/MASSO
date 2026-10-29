<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketRulesTable extends Migration
{
    /**
     * Reglas de compra entre tickets, categorías y tipos de un evento
     * (requiere / excluye / máximo / mínimo). Ver Masso\TicketRule.
     */
    public function up()
    {
        Schema::create('ticket_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->string('scope', 10)->default('compra');      // entrada | compra
            $table->string('subject_type', 10);                   // ticket | category | kind
            $table->string('subject_ref', 32);                    // id, o principal/complementario
            $table->string('rule', 10);                           // requires | excludes | max | min
            $table->string('target_type', 10)->nullable();
            $table->string('target_ref', 32)->nullable();
            $table->unsignedSmallInteger('quantity')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index('event_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_rules');
    }
}
