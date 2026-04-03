<?php

use App\Models\ExerciceSapeur;
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
        Schema::table('exercice_sapeur', function (Blueprint $table) {
            $table->dropColumn('amende');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exercice_sapeur', function (Blueprint $table) {
            $table->integer('amende');
        });
    }
};
