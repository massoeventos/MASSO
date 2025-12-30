<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeEventTicketsOptionalFieldsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events_tickets', function (Blueprint $table) {
            $table->string('name_eng')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->text('description_eng')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_tickets', function (Blueprint $table) {
            $table->string('name_eng')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->text('description_eng')->nullable(false)->change();
        });
    }
}
