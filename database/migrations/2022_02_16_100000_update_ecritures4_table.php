<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEcritures4Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecritures', function (Blueprint $table) {
            $table->dropColumn('avs');
            $table->dropColumn('amende');
            $table->dropColumn('frais_annuel');
            $table->dropColumn('indemnite_annuel');

            $table->dropColumn('solde'); // Supprimé
            $table->dropColumn('indemnite'); // Supprimé
            $table->dropColumn('frais'); // Supprimé
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
        });
    }
}
