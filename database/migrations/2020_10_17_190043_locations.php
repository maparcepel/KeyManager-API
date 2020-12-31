<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Locations extends Migration
{
    /**
     * Run the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('location_number', 9)->unique();
            $table->string('name', 75);
            $table->string('address', 150);
            $table->integer('zip_code_id')->unsigned();
            $table->foreign('zip_code_id')->references('id')->on('zip_codes')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('customer_id')->unsigned();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('pass_expiration_days')->unsigned();
            $table->text('comments');
            $table->timestamps();
            $table->dateTime('cancel_request', 0)->nullable();
            $table->dateTime('cancel_at', 0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
}
