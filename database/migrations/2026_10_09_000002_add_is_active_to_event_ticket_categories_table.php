<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsActiveToEventTicketCategoriesTable extends Migration
{
    /**
     * Apagar una categoría sin borrarla: deja de mostrarse en la ficha y en
     * el checkout, junto con los tickets que solo pertenecen a ella.
     */
    public function up()
    {
        Schema::table('event_ticket_categories', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('position');
        });
    }

    public function down()
    {
        Schema::table('event_ticket_categories', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
