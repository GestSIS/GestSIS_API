<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateComptesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->unsignedInteger('type')->default(0);
            $table->renameColumn('actif', 'produit');
            // 0 : autre
            // 1 : solde
            // 2 : indemnité
            // 3 : frais forfaitaire
            // 4 : frais effectif
            // 5 : charges AVS/AC
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comptes', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->renameColumn('produit', 'actif');
        });
    }
}
