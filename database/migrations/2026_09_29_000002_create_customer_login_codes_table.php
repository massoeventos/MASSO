<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerLoginCodesTable extends Migration
{
    /**
     * Códigos temporales de acceso (OTP), por email o SMS. Se guarda el
     * hash del código, no el código en texto plano — igual que una
     * contraseña, aunque sea de corta vida. `attempts` limita los
     * intentos de validación; el límite de *reenvío* (1 código nuevo
     * cada ~60s) se controla a nivel de aplicación mirando el último
     * `created_at` para ese customer_id, no acá.
     */
    public function up()
    {
        Schema::create('customer_login_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->string('code_hash');
            $table->string('channel', 10)->default('email');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->index(['customer_id', 'consumed_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_login_codes');
    }
}
