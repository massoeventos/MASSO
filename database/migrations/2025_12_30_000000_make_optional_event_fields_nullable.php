<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeOptionalEventFieldsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->string('photo')->nullable()->change();
            $table->string('organize')->nullable()->change();
            $table->longText('terms_and_conditions')->nullable()->change();
            $table->longText('terms_and_conditions_eng')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
            $table->string('photo')->nullable(false)->change();
            $table->string('organize')->nullable(false)->change();
            $table->longText('terms_and_conditions')->nullable(false)->change();
            $table->longText('terms_and_conditions_eng')->nullable(false)->change();
        });
    }
}
