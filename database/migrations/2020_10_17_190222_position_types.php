<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PositionTypes extends Migration
{
    /**
     * Run the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function up()
    {
        Schema::create('position_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('position_name', 50);
            $table->timestamps();
        });

        //Introducimos los datos por defecto del sistema
        $path = 'database/migrations/PositionTypes.sql';
        DB::unprepared(file_get_contents($path));
    }

    /**
     * Reverse the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('position_types');
    }
}
