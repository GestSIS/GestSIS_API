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
        Schema::dropIfExists('materiel_alerte_type_pour');
        Schema::dropIfExists('materiel_alerte_types');
        Schema::dropIfExists('materiel_event_type_pour');
        Schema::dropIfExists('materiel_events');
        Schema::dropIfExists('materiel_event_types');
        Schema::dropIfExists('materiel_alertes');

        Schema::dropIfExists('materiel_nominals');
        Schema::dropIfExists('materiel_generiques');
        Schema::dropIfExists('materiel_personnels');

        Schema::dropIfExists('vehicules');
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
