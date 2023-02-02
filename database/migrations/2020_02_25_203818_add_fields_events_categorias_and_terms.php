<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsEventsCategoriasAndTerms extends Migration
{
    private $table = 'events';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!$this->hasColumn('description_eng'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->longText('description_eng');
            });
        }

        if (!$this->hasColumn('organize'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->text('organize');
            });
        }

        Schema::table($this->table, function (Blueprint $table) {
            $table->boolean('is_multiple_selection_ticket')->default(0);
            $table->tinyInteger('max_selection_ticket')->default(1);
            $table->longText('terms_and_conditions');
            $table->longText('terms_and_conditions_eng');
        });
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
