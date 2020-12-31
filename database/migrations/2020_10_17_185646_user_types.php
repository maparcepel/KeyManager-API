<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserTypes extends Migration
{
    /**
     * Run the migrations.
     * Coded by: Víctor Castellanos Pérez
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type_name', 50)->unique();
            $table->timestamps();
        });

        //Valores iniciales para UserType.
        DB::table('user_types')->insert([
            ['type_name' => 'Sistema'],
            ['type_name' => 'Cliente'],
            ['type_Name' => 'Empleado'],
        ]);
    }

    /**
     * Reverse the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_types');
    }
}
