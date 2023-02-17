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
        Schema::create('sapeur_telephone', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->bigInteger('sapeur_id')->unsigned();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->bigInteger('telephone_type_id')->unsigned();
            $table->foreign('telephone_type_id')->references('id')->on('telephone_types');

            $table->string('numero');

            $table->integer('priorite');
            $table->boolean('rta')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('sapeur_telephone');
        Schema::enableForeignKeyConstraints();
    }
};
