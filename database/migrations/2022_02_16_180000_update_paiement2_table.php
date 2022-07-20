<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePaiement2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paiements', function (Blueprint $table) {
            $table->renameColumn('frais', 'frais_forfaitaire');
            $table->renameColumn('avs', 'avs_ac');
            $table->decimal('frais_effectif');
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
            $table->renameColumn('frais_forfaitaire', 'frais');
            $table->renameColumn('avs_ac', 'avs');
            $table->dropColumn('frais_effectif');
        });
    }
}
