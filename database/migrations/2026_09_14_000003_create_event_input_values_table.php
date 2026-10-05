<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventInputValuesTable extends Migration
{
    /**
     * Da columna propia a las respuestas de los campos dinámicos definidos
     * en events_inputs, que hasta ahora solo vivían dentro del blob
     * serializado/JSON de payments.data. No reemplaza esa columna: se
     * escribe en paralelo (ver PublicController::process() y el comando
     * masso:backfill-event-input-values) mientras se valida en staging.
     */
    public function up()
    {
        Schema::create('event_input_values', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payment_id');
            // events_inputs.id es `int` (signed), no unsigned, a pesar de que
            // su migración original usa increments(): se respeta el tipo real
            // de la columna en la BD para que la FK sea compatible.
            $table->integer('event_input_id');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->foreign('event_input_id')->references('id')->on('events_inputs')->onDelete('cascade');

            $table->unique(['payment_id', 'event_input_id'], 'event_input_values_payment_input_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_input_values');
    }
}
