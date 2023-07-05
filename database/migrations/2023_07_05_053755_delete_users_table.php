<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('users');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('sapeur_id')->nullable();
            $table->foreign('sapeur_id')->references('id')->on('sapeurs');

            $table->timestamps();
        });
    }
};
