<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMaterielEvent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('materiel_events', function (Blueprint $table) {
            // $table->dropForeign(['materiel_event_id']);
            // $table->dropColumn('materiel_event_id');

            // $table->unsignedBigInteger('materiel_event_type_id');
            // $table->foreign('materiel_event_type_id')->references('id')->on('materiel_event_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('materiel_events', function (Blueprint $table) {
            $table->dropForeign(['materiel_event_type_id']);
            $table->dropColumn('materiel_event_type_id');

            $table->unsignedBigInteger('materiel_event_id');
            $table->foreign('materiel_event_id')->references('id')->on('materiel_events');
        });
    }
};
