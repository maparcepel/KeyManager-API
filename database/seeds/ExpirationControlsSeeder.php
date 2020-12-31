<?php

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ExpirationControlsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('es_ES');

        for ($i = 1; $i < 404; $i++) {
            DB::table('expiration_controls')->insert([
                'employe_id' => $i,
                'date' => $faker->dateTimeBetween('- 30 days', 'now', 'Europe/Madrid'),
                'employe_status_id' => $faker->numberBetween(1, 3),
            ]);
        }
    }
}
