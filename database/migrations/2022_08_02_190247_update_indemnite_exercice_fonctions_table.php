<?php

use App\Infrastructure\Models\IndemniteExerciceFonction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIndemniteExerciceFonctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('indemnite_exercice_fonctions', function (Blueprint $table) {
            $table->dropColumn('tarif_min');
            $table->dropColumn('tarif_min_pour');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('indemnite_exercice_fonctions', function (Blueprint $table) {
            $table->unsignedDecimal('tarif_min')->nullable();
            $table->unsignedDecimal('tarif_min_pour')->nullable();
        });
    }
}
