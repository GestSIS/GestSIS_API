<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDecomptesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('decomptes', function (Blueprint $table) {
            $table->decimal('avsTotal');
            $table->decimal('acTotal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('decomptes', function (Blueprint $table) {
            $table->dropColumn('avsTotal');
            $table->dropColumn('acTotal');
        });
    }
}
