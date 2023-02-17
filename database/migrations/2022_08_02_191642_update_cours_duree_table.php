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
        Schema::table('cours', function (Blueprint $table) {
            $table->decimal('duree')->change();
        });
        Schema::table('cours_sapeur', function (Blueprint $table) {
            $table->decimal('duree')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cours', function (Blueprint $table) {
            $table->smallInteger('duree')->change();
        });
        Schema::table('cours_sapeur', function (Blueprint $table) {
            $table->smallInteger('duree')->change();
        });
    }
};
