<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsMissingEventsEnroll extends Migration
{
    private $table = 'events_enroll';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!$this->hasColumn('data'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->longText('data');
            });
        }
        if (!$this->hasColumn('payment_id'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->integer('payment_id')->default(0);
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
