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
        Schema::table('avs_params', function (Blueprint $table) {
            $table->decimal('franchise_imposition_cantonale')->default(8000);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('avs_params', function (Blueprint $table) {
            $table->dropColumn('franchise_imposition_cantonale');
        });
    }
};
