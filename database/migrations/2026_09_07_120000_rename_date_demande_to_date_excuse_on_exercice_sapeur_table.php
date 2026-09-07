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
        Schema::table('exercice_sapeur', function (Blueprint $table) {
            $table->renameColumn('date_demande', 'date_excuse');
        });
        Schema::table('exercice_sapeur', function (Blueprint $table) {
            $table->dateTime('date_excuse')->nullable()->default(null)->change();
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
            $table->date('date_excuse')->nullable()->default(null)->change();
        });
        Schema::table('exercice_sapeur', function (Blueprint $table) {
            $table->renameColumn('date_excuse', 'date_demande');
        });
    }
};
