<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataJsonToEventsEnrollTable extends Migration
{
    /**
     * Columna nueva y aditiva: no toca ni reemplaza `data` (longText
     * serializado con PHP serialize(), a veces doblemente serializado por
     * el bug histórico de SendNotifications). Ver
     * app/Services/LegacySerializedData.php y el comando
     * masso:backfill-data-json para el llenado de esta columna.
     */
    public function up()
    {
        Schema::table('events_enroll', function (Blueprint $table) {
            $table->json('data_json')->nullable()->after('data');
        });
    }

    public function down()
    {
        Schema::table('events_enroll', function (Blueprint $table) {
            $table->dropColumn('data_json');
        });
    }
}
