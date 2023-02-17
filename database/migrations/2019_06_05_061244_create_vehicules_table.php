<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('designation');
            $table->boolean('statut');
            $table->integer('tri');
            $table->decimal('forfait', 5, 2);
            $table->decimal('unite', 5, 2);

            $table->bigInteger('type_unite_id')->unsigned()->nullable();
            $table->foreign('type_unite_id')->references('id')->on('type_unites');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicules');
    }
};
