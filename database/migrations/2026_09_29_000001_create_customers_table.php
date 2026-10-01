<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Identidad del comprador (Bloque 3). `email` es la clave natural —
     * se normaliza a minúsculas/trim antes de guardar (ver modelo
     * Customer). `password`/`phone` son opcionales: nunca se piden en el
     * checkout, solo se configuran después desde el historial de
     * compras (ítem 3.3).
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('lastname')->nullable();
            $table->string('rut')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
