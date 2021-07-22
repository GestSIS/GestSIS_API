<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePaiementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->bigInteger('decompte_id')->unsigned()->nullable();
            $table->foreign('decompte_id')->references('id')->on('decomptes')->onDelete('cascade');

            $table->bigInteger('sapeur_id')->unsigned()->nullable();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->decimal('solde');
            $table->decimal('indemnite');
            $table->decimal('frais');
            $table->decimal('amende');
            $table->decimal('avs');
            $table->decimal('total');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->dropForeign(['decompte_id']);
            $table->dropForeign(['sapeur_id']);
            $table->dropColumn('decompte_id');
            $table->dropColumn('sapeur_id');
            $table->dropColumn('solde');
            $table->dropColumn('indemnite');
            $table->dropColumn('frais');
            $table->dropColumn('amende');
            $table->dropColumn('avs');
            $table->dropColumn('total');
        });
    }
}
