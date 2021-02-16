<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvsParamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avs_params', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            
            $table->decimal('taux_avs', 6, 5);
            $table->decimal('taux_ac', 6, 5);
            $table->decimal('franchise_avs');
            $table->decimal('franchise_imposition');

            // Comptes
            $table->unsignedBigInteger('compte_id');
            $table->foreign('compte_id')->references('id')->on('comptes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avs_params');
    }
}
