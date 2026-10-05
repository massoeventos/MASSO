<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameEngToEventsTable extends Migration
{
    /**
     * Nombre del evento en inglés, para la vista "View in english".
     * Nullable: si está vacío la vista pública cae al nombre en español.
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('name_eng')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('name_eng');
        });
    }
}
