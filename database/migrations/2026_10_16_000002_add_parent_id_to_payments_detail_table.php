<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentIdToPaymentsDetailTable extends Migration
{
    /**
     * Cada unidad complementaria cuelga de la unidad principal (la entrada)
     * con la que se compró. Nulo en las entradas y en las compras anteriores.
     */
    public function up()
    {
        Schema::table('payments_detail', function (Blueprint $table) {
            $table->unsignedInteger('parent_id')->nullable()->after('ticket_id');
            $table->index('parent_id');
        });
    }

    public function down()
    {
        Schema::table('payments_detail', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
}
