<?php

use App\Infrastructure\Models\Ecriture;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEcritures2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecritures', function (Blueprint $table) {
            $table->unsignedInteger('module')->default(0);
            $table->unsignedInteger('type')->default(0);
        });

        Ecriture::whereNotNull('exercice_id')->update(['module' => 1]);
        Ecriture::whereNotNull('intervention_id')->update(['module' => 2]);
        // Ecriture::whereNotNull('frais_annuel')->update(['module' => 3]);
        // Ecriture::whereNotNull('indemnite_annuel')->update(['module' => 4]);
        // Ecriture::whereNotNull('avs')->update(['module' => 5]);
        // Ecriture::whereNotNull('amende')->update(['module' => 6]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecritures', function (Blueprint $table) {
            $table->dropColumn('module');
        });
    }
}
