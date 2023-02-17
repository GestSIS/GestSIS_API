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
        Schema::create('groupes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->integer('type')->default(0);
            $table->integer('no')->nullable();
            $table->string('designation');
            $table->integer('tri');

            $table->bigInteger('pere_id')->unsigned()->nullable();
            $table->foreign('pere_id')->references('id')->on('groupes')->onDelete('cascade');
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
        Schema::dropIfExists('groupes');
        Schema::enableForeignKeyConstraints();
    }
};
