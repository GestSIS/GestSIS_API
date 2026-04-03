<?php

use App\Models\IndemniteExerciceFonction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
            $table->decimal('tarif_min')->nullable();
            $table->decimal('tarif_min_pour')->nullable();
        });
    }
};
