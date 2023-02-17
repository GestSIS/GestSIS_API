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
        Schema::table('indemnite_intervention_types', function (Blueprint $table) {
            $table->boolean('tarif_min_pro_rata')->default(false);
        });
        Schema::table('ecritures', function (Blueprint $table) {
            $table->boolean('tarif_min_pro_rata')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('indemnite_intervention_types', function (Blueprint $table) {
            $table->dropColumn('tarif_min_pro_rata');
        });
        Schema::table('ecritures', function (Blueprint $table) {
            $table->dropColumn('tarif_min_pro_rata');
        });
    }
};
