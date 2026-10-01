<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProfileFieldsToCustomersTable extends Migration
{
    /**
     * Ítem 3.3c: los datos "default" del formulario de checkout
     * (pasaporte, género, nacionalidad, ubicación) pasan a vivir en
     * `customers` en vez de repetirse en cada `payments`. `email`/`name`/
     * `lastname`/`rut` ya existían desde el ítem 3.1.
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('rut');
            $table->string('passport')->nullable()->after('gender');
            $table->unsignedInteger('nationality_country_id')->nullable()->after('passport');
            $table->unsignedInteger('city_id')->nullable()->after('nationality_country_id');
            $table->unsignedInteger('country_id')->nullable()->after('city_id');
            $table->string('custom_city')->nullable()->after('country_id');

            $table->foreign('nationality_country_id')->references('id')->on('countries');
            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('country_id')->references('id')->on('countries');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['nationality_country_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['country_id']);
            $table->dropColumn(['gender', 'passport', 'nationality_country_id', 'city_id', 'country_id', 'custom_city']);
        });
    }
}
