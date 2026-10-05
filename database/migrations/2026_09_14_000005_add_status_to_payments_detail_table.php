<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToPaymentsDetailTable extends Migration
{
    /**
     * payments_detail no tenía ningún ciclo de vida propio: una fila
     * significaba "ticket comprado" sin distinguir reservado/confirmado,
     * y no había forma de anular o reemplazar un ticket ya emitido.
     *
     * 'status' registra ese ciclo de vida ('reservado' al iniciar la
     * compra, 'confirmado' cuando el pago se aprueba, 'anulado'/
     * 'reemplazado' para la acción de administración que se construirá en
     * una sesión posterior). 'replaces_id' permite encadenar el ticket
     * viejo con el nuevo en un reemplazo, sin perder el historial.
     *
     * De paso corrige el índice mal formado que existía
     * ($table->index('payment_id', 'ticket_id') crea un índice simple
     * sobre payment_id nombrado "ticket_id"; ticket_id queda sin índice).
     */
    public function up()
    {
        Schema::table('payments_detail', function (Blueprint $table) {
            $table->string('status', 20)->default('reservado')->after('price');
            $table->unsignedInteger('replaces_id')->nullable()->after('status');

            $table->foreign('replaces_id')->references('id')->on('payments_detail')->onDelete('restrict');
        });

        Schema::table('payments_detail', function (Blueprint $table) {
            $table->dropIndex('ticket_id');
            $table->index(['payment_id', 'ticket_id'], 'payments_detail_payment_id_ticket_id_index');
        });
    }

    public function down()
    {
        Schema::table('payments_detail', function (Blueprint $table) {
            $table->dropIndex('payments_detail_payment_id_ticket_id_index');
            $table->index('payment_id', 'ticket_id');
        });

        Schema::table('payments_detail', function (Blueprint $table) {
            $table->dropForeign(['replaces_id']);
            $table->dropColumn(['status', 'replaces_id']);
        });
    }
}
