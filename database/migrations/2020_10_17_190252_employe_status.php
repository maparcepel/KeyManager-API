<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmployeStatus extends Migration
{
    /**
     * Run the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function up()
    {
        Schema::create('employe_status', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->unique();
            $table->timestamps();
        });

        //Initial values for EmployeStatus.
        DB::table('employe_status')->insert([
            ['name' => 'Activo'],
            ['name' => 'Caducado'],
            ['name' => 'Caducado 2'],
            ['name' => 'Anulado'],
            ['name' => 'Baja']
        ]);
    }

    /**
     * Reverse the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employe_status');
    }
}
