<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPassportToPaymentsTable extends Migration
{
    /**
     * 'passport' se pide en el formulario de registro (EnrollRequest: rut
     * si es Chile, passport en caso contrario) pero nunca tuvo columna
     * propia en payments — solo vivía en el blob y, copiado desde ahí, en
     * events_enroll.passport. Es la última pieza que falta para poder
     * eventualmente dejar de duplicar datos personales en events_enroll:
     * las demás (name/lastname/rut/email/city_id/...) ya se resuelven via
     * payment_detail_id (ver EventEnroll::linkedPayment()).
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('passport')->nullable()->after('rut');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('passport');
        });
    }
}
