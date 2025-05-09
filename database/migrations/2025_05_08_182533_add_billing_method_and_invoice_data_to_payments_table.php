<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBillingMethodAndInvoiceDataToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('billing_method', ['receipt', 'invoice'])->default('receipt')->after('country_id');
            $table->json('invoice_data')->nullable()->after('billing_method');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['billing_method', 'invoice_data']);
        });
    }
}
