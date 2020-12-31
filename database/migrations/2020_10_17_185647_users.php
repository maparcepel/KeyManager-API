<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Users extends Migration
{
    /**
     * Run the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('dni', 9)->nullable();
            $table->string('name', 25);
            $table->string('surname', 75);
            $table->string('email', 75);
            $table->string('phone', 13)->nullable();
            $table->string('web_password', 100);
            $table->integer('user_type_id')->unsigned();
            $table->foreign('user_type_id')->references('id')->on('user_types')->onDelete('restrict')->onUpdate('cascade');
            $table->string('register_ip', 15)->default('127.0.0.1');
            $table->rememberToken();
            $table->integer('attempts')->default(0);
            $table->boolean('locked')->default(false);
            $table->dateTime('cancel_request', 0)->nullable();
            $table->dateTime('cancel_at', 0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
