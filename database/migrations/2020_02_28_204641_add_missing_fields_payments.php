<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissingFieldsPayments extends Migration
{
    private $table = 'payments';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!$this->hasColumn('name'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('name');
            });
        }
        if (!$this->hasColumn('lastname'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('lastname');
            });
        }
        if (!$this->hasColumn('email'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('email');
            });
        }
        if (!$this->hasColumn('dte'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('dte');
            });
        }
        if (!$this->hasColumn('document'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('document');
            });
        }
        if (!$this->hasColumn('managment'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('managment')->default('webpay');
            });
        }
        if (!$this->hasColumn('data'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->longText('data')->nullable($value = true);
            });
        }
        if (!$this->hasColumn('notified'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->boolean('notified')->default(0);
            });
        }
        if (!$this->hasColumn('type'))
        {
            Schema::table($this->table, function (Blueprint $table) {
                $table->string('type')->default('inscription');
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
