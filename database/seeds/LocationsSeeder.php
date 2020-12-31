<?php

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class LocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('es_ES');

        //Localizaciones para el Customer id=1.
        $customer = 1;
        $contador = 0;
        for ($i = 0; $i < 50; $i++) {
            if ($contador == 5) {
                $customer++;
                $contador = 0;
            }
            DB::table('locations')->insert([
                'location_number' => $faker->unique()->randomNumber(9, true),
                'name' => $faker->company,
                'address' => $faker->streetAddress,
                'zip_code_id' => $faker->numberBetween(1, 1000),
                'pass_expiration_days' => $faker->numberBetween(1, 365),
                'customer_id' => $customer,
                'comments' => $faker->text(300),
            ]);
            $contador++;
        }

        //Modificamos algunas localizaciones indicando fechas en cancel_at and cancel_request
        $cambio = true;
        for ($i = 0; $i < 10; $i++) {
            $location_id = $faker->numberBetween(1, 100);

            if ($cambio) {
                DB::table('locations')->where('id', $location_id)->update(['cancel_at' => $faker->dateTimeBetween('- 30 days', 'now', 'Europe/Madrid')]);
                $cambio = false;
            } else {
                DB::table('locations')->where('id', $location_id)->update(['cancel_request' => $faker->dateTimeBetween('- 30 days', 'now', 'Europe/Madrid')]);
                $cambio = true;
            }
        }
    }
}
