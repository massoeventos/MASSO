<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParticipantsColumnsToPaymentsTable extends Migration
{
    /**
     * 'participants_excel_file' / 'participants_count' no son "campos
     * extra" de un formulario dinámico: son 2 campos fijos de una sola
     * feature (carga masiva de participantes por Excel en pagos
     * grupales/custom, ver PublicController::processPay()). Vivían solo
     * dentro del blob serializado/JSON; se promueven a columna real.
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('participants_excel_file')->nullable()->after('user_observation');
            $table->unsignedInteger('participants_count')->nullable()->after('participants_excel_file');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['participants_excel_file', 'participants_count']);
        });
    }
}
