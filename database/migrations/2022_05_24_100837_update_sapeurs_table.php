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
        Schema::table('sapeurs', function (Blueprint $table) {
            $table->smallInteger('type')->default(0);
            // 0 -> Sapeur
            // 1 -> Civil
            // 2 -> ...
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sapeurs', function (Blueprint $table) {
            $table->removeColumn('type');
        });
    }
};
