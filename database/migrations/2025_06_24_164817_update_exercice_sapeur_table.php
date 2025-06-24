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
            $table->string('justification', length: 1000)->default('')->change();
            $table->string('remarque', length: 1000)->default('')->change();
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
            $table->string('justification')->default('')->change();
            $table->string('remarque')->default('')->change();
        });
    }
};
