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
            $table->decimal('avs_total');
            $table->decimal('ac_total');
            $table->decimal('total');
            $table->date('date');
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
            $table->dropColumn('avs_total');
            $table->dropColumn('ac_total');
            $table->dropColumn('total');
            $table->dropColumn('date');
        });
    }
};
