<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakePaymentsIdentityColumnsNullable extends Migration
{
    /**
     * Ítem 3.3c: `name`/`lastname`/`email` eran las únicas columnas de
     * datos personales en `payments` que seguían NOT NULL sin default
     * (agregadas en 2020, antes de existir `customers`). Deben poder
     * quedar vacías en los pagos nuevos vinculados a un Customer, que
     * resuelven estos campos vía accessor en vez de repetirlos.
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('lastname')->nullable()->change();
            $table->string('email')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->string('lastname')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
        });
    }
}
