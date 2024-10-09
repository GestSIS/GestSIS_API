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
        Schema::table('groupes', function (Blueprint $table) {
            $table->dropForeign('groupes_pere_id_foreign');
            $table->renameColumn('pere_id', 'parent_id');

            $table->foreign('parent_id')->references('id')->on('groupes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Déjà existante
        Schema::table('groupes', function (Blueprint $table) {
            $table->dropForeign('groupes_parent_id_foreign');
            $table->renameColumn('parent_id', 'pere_id');

            $table->foreign('pere_id')->references('id')->on('groupes')->onDelete('cascade');
        });
    }
};
