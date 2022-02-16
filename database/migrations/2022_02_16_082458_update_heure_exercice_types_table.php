<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateHeureExerciceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('heure_exercice_types', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('heure_exercices', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('heure_exercice_types', function (Blueprint $table) {
            $table->unsignedInteger('type')->default(0);
        });
        Schema::table('heure_exercices', function (Blueprint $table) {
            $table->unsignedInteger('type')->default(0);
        });
    }
}
