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
        Schema::table('ecritures', function (Blueprint $table) {
            $table->mediumText('complement')->nullable();
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->renameColumn('amende', 'autre');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecritures', function (Blueprint $table) {
            $table->dropColumn('complement');
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->renameColumn('autre', 'amende');
        });
    }
};
