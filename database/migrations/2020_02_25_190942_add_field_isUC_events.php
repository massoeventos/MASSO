<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldIsUCEvents extends Migration
{
    private $table = 'events';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!$this->hasColumn())
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->boolean('isUC');
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
        if ($this->hasColumn()) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('isUC');
            });
        }
    }

    private function hasColumn() {
        $database = env('DB_DATABASE');
        $hasColumn = DB::select("select column_name from information_schema.columns  where table_schema = '{$database}' and table_name = '{$this->table}' and column_name = 'isUC'");
        return (bool) count($hasColumn);
    }
}
