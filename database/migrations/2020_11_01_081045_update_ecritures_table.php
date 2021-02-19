<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEcrituresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecritures', function (Blueprint $table) {
            $table->unsignedBigInteger('decompte_id')->nullable();
            $table->foreign('decompte_id')->references('id')->on('decomptes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecritures', function (Blueprint $table) {
            $table->dropForeign(['decompte_id']);
            $table->dropColumn('decompte_id');
        });
    }
}
