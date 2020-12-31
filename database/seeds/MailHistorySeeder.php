<?php

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class MailHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('es_ES');
        for ($i = 0; $i < 2; $i++) {
            for ($x = 1; $x < 404; $x++) {
                DB::table('mail_histories')->insert([
                    'employe_id' => $x,
                    'date' => $faker->dateTimeBetween('- 7 days', 'now', 'Europe/Madrid'),
                    'mail_type_id' => $faker->numberBetween(1, 4),
                ]);
            }
        }
    }
}
