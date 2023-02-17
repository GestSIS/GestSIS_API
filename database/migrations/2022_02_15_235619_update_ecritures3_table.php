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
        Schema::table('ecritures', function (Blueprint $table) {
            $table->renameColumn('solde_min', 'tarif_min');
            $table->renameColumn('solde_min_pour', 'tarif_min_pour');

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
        Schema::table('ecritures', function (Blueprint $table) {
            $table->renameColumn('tarif_min', 'solde_min');
            $table->renameColumn('tarif_min_pour', 'solde_min_pour');
        });
    }
};
