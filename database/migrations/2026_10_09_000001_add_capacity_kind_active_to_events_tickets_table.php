<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCapacityKindActiveToEventsTicketsTable extends Migration
{
    /**
     * - capacity: cupo total del ticket. Los disponibles se calculan como
     *   capacity - vendidos (payments_detail confirmados), en vez de usar
     *   `stock` (que era solo "lo que queda" y se pisaba al editar el evento).
     *   Se llena con stock + vendidos para que los disponibles queden
     *   exactamente iguales al stock actual.
     * - is_active: apagar un ticket sin borrarlo.
     * - kind: principal (inscripción) o complementario (taller, cena...).
     */
    public function up()
    {
        Schema::table('events_tickets', function (Blueprint $table) {
            $table->unsignedInteger('capacity')->nullable()->after('stock');
            $table->boolean('is_active')->default(true)->after('position');
            $table->string('kind', 20)->default('principal')->after('is_active');
        });

        // Conteo agrupado en una sola pasada: payments_detail no tiene índice
        // por ticket_id, y una subconsulta correlacionada la recorre entera
        // por cada ticket (minutos en una base real).
        DB::statement("
            UPDATE events_tickets t
            LEFT JOIN (
                SELECT ticket_id, COUNT(*) AS sold
                FROM payments_detail
                WHERE status = 'confirmado' AND deleted_at IS NULL
                GROUP BY ticket_id
            ) s ON s.ticket_id = t.id
            SET t.capacity = GREATEST(t.stock, 0) + COALESCE(s.sold, 0)
        ");
    }

    public function down()
    {
        Schema::table('events_tickets', function (Blueprint $table) {
            $table->dropColumn(['capacity', 'is_active', 'kind']);
        });
    }
}
