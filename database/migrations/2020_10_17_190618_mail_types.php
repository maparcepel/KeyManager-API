<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MailTypes extends Migration
{
    /**
     * Run the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function up()
    {
        Schema::create('mail_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->unique();
            $table->timestamps();
        });
        //Initial values for EmployeStatus.
        DB::table('mail_types')->insert([
            ['name' => 'Solicitud cambio de contraseñas.'],
            ['name' => 'Recordatorio cambio de contraseñas.'],
            ['name' => 'Registro en el sistema'],
            ['name' => 'Confirmación cambios.'],
            ['name' => 'Anulación de accesso y claves de empleado.'],
            ['name' => 'Confirmación de baja.']
        ]);

    }

    /**
     * Reverse the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_types');
    }
}
