<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentDetailIdToEventsEnrollTable extends Migration
{
    /**
     * Enlaza el "asistente" con el ticket pagado exacto que lo originó.
     * A partir de ahora, cuando este campo está seteado, el modelo
     * EventEnroll resuelve nombre/rut/email/etc. a través de
     * paymentDetail->payment en vez de sus propias columnas — deja de
     * duplicar los datos personales de quien ya pagó. Los invitados
     * manuales (sin pago) siguen sin este campo, sin cambios.
     */
    public function up()
    {
        Schema::table('events_enroll', function (Blueprint $table) {
            $table->unsignedInteger('payment_detail_id')->nullable()->after('payment_id');
            $table->foreign('payment_detail_id')->references('id')->on('payments_detail')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::table('events_enroll', function (Blueprint $table) {
            $table->dropForeign(['payment_detail_id']);
            $table->dropColumn('payment_detail_id');
        });
    }
}
