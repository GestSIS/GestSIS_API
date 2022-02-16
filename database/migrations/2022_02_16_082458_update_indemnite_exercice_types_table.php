<?php

use App\Infrastructure\Models\IndemniteExerciceFonction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIndemniteExerciceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Note: Nécessite la saisie des indemnite par exercice une seconde fois
        Schema::table('indemnite_exercice_types', function (Blueprint $table) {
            $table->dropForeign('indemnite_exercice_types_compte_id_foreign');

            $table->dropColumn('solde');
            $table->dropColumn('indemnite');
            $table->dropColumn('solde_min');
            $table->dropColumn('solde_min_pour');
            $table->dropColumn('compte_id');
        });

        IndemniteExerciceFonction::truncate();
        Schema::table('indemnite_exercice_fonctions', function (Blueprint $table) {
            $table->renameColumn('solde', 'tarif');
            $table->dropColumn('indemnite');
            $table->unsignedDecimal('tarif_min')->nullable();
            $table->unsignedDecimal('tarif_min_pour')->nullable();

            $table->unsignedBigInteger('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes');

            $table->unsignedBigInteger('fonction_id')->nullable()->change();

            $table->unsignedInteger('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('indemnite_exercice_types', function (Blueprint $table) {
            $table->unsignedDecimal('solde');
            $table->unsignedDecimal('indemnite');
            $table->unsignedDecimal('solde_min')->nullable();
            $table->unsignedDecimal('solde_min_pour')->nullable();

            $table->unsignedBigInteger('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes');
        });

        Schema::table('indemnite_exercice_fonctions', function (Blueprint $table) {
            $table->renameColumn('tarif', 'solde');
            $table->dropColumn('tarif_min');
            $table->dropColumn('tarif_min_pour');

            $table->dropForeign('indemnite_exercice_fonctions_compte_id_foreign');
            $table->dropColumn('compte_id');
        });
    }
}
