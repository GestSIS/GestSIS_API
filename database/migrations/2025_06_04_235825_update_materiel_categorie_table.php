<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('materiel_categories', function (Blueprint $table) {
            $table->dropForeign('materiel_categories_pere_id_foreign');
            $table->renameColumn('pere_id', 'parent_id');

            $table->foreign('parent_id')->references('id')->on('materiel_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('materiel_categories', function (Blueprint $table) {
            $table->dropForeign('materiel_categories_parent_id_foreign');
            $table->renameColumn('parent_id', 'pere_id');

            $table->foreign('pere_id')->references('id')->on('materiel_categories')->onDelete('cascade');
        });
    }
};
