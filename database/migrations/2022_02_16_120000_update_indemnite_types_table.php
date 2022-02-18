<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIndemniteTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('indemnite_annuel_types', 'frais_indemnite_annuel_types');
        Schema::rename('indemnite_annuels', 'frais_indemnite_annuels');

        Schema::table('frais_indemnite_annuel_types', function (Blueprint $table) {
            $table->unsignedInteger('type')->default(2);
        });

        Schema::table('frais_indemnite_annuels', function (Blueprint $table) {
            $table->dropForeign('indemnite_annuels_indemnite_annuel_type_id_foreign');
            $table->renameColumn('indemnite_annuel_type_id', 'frais_indemnite_annuel_type_id');
            $table->foreign('frais_indemnite_annuel_type_id')->references('id')->on('frais_indemnite_annuel_types')->onDelete('cascade');
        });

        Schema::table('frais_annuels', function (Blueprint $table) {
            $table->dropForeign('frais_annuels_frais_annuel_type_id_foreign');
        });
        Schema::dropIfExists('frais_annuel_types');
        Schema::dropIfExists('frais_annuels');
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
