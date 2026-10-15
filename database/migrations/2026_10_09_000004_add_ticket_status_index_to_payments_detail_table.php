<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTicketStatusIndexToPaymentsDetailTable extends Migration
{
    /**
     * Los vendidos de cada ticket se cuentan en vivo (EventTicket::soldCount,
     * payments_detail confirmados por ticket_id). El índice existente es
     * (payment_id, ticket_id) y no sirve para buscar solo por ticket.
     */
    public function up()
    {
        Schema::table('payments_detail', function (Blueprint $table) {
            $table->index(['ticket_id', 'status'], 'payments_detail_ticket_status_index');
        });
    }

    public function down()
    {
        Schema::table('payments_detail', function (Blueprint $table) {
            $table->dropIndex('payments_detail_ticket_status_index');
        });
    }
}
