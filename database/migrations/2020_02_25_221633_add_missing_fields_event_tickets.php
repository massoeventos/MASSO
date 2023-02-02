<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissingFieldsEventTickets extends Migration
{
    private $table = 'events_tickets';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!$this->hasColumn('name_eng'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('name_eng');
            });
        }

        if (!$this->hasColumn('description'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->longText('description');
            });
        }

        if (!$this->hasColumn('description_eng'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->longText('description_eng');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    private function hasColumn($column) {
        $database = env('DB_DATABASE');
        $hasColumn = DB::select("select column_name from information_schema.columns  where table_schema = '{$database}' and table_name = '{$this->table}' and column_name = '{$column}'");
        return (bool) count($hasColumn);
    }
}
