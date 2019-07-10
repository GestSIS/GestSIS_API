<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupesTable extends Migration
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

            $table->integer('type');
            $table->integer('no')->nullable();
            $table->string('designation');
            $table->string('info');
            $table->integer('tri');

            $table->bigInteger('pere_id')->unsigned()->nullable();
            $table->foreign('pere_id')->references('id')->on('groupes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('groupes', function(Blueprint $table) {
            $table->dropForeign(['pere_id']);
        });
        Schema::dropIfExists('groupes');
    }
}
