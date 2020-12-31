<?php

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class AccessHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('es_ES');

        //Añadimos un historial de accesos de usuario.
        for ($i = 0; $i < 1000; $i++) {
            DB::table('access_histories')->insert([
                'user_id' => $faker->numberBetween(1, 414),
                'access_ip' => $faker->ipv4,
                'date' => $faker->dateTimeBetween('- 500 days', 'now', 'Europe/Madrid'),
            ]);
        }
    }
}
