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
        Schema::table('indemnite_exercice_fonctions', function (Blueprint $table) {
            $table->dropForeign(['indemnite_exe_id']);
            $table->foreign('indemnite_exe_id')->references('id')->on('indemnite_exercice_types')->onDelete('cascade');
        });

        Schema::table('indemnite_intervention_fonctions', function (Blueprint $table) {
            $table->dropForeign(['indemnite_int_id']);
            $table->foreign('indemnite_int_id')->references('id')->on('indemnite_intervention_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
