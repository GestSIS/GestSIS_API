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
        Schema::table('interventions', function (Blueprint $table) {
            $table->string('agent')->default("");
            $table->boolean('rapport_police')->default(false)->change();

            $table->string('lieu')->default('')->change();
            $table->text('description')->default('')->change();
            $table->text('proprietaire')->default('')->change();
            $table->text('responsable')->default('')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropColumn('agent')->default('');
        });
    }
};
