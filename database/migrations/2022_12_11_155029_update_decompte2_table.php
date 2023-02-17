<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('decomptes', function (Blueprint $table) {
            $table->decimal('a_payer_total')->default(0.0);
            $table->decimal('a_facturer_total')->default(0.0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('decomptes', function (Blueprint $table) {
            $table->dropColumn('a_payer_total');
            $table->dropColumn('a_facturer_total');
        });
    }
};
