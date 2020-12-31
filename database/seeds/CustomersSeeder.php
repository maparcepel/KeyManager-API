<?php

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class CustomersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('es_ES');

        //Clientes dados de alta.
        for ($i = 0; $i < 10; $i++) {
            DB::table('customers')->insert([
                'customer_number' => $faker->unique()->randomNumber(9, true),
                'cif' => $faker->unique()->vat,
                'name' => $faker->company,
                'address' => $faker->streetAddress,
                'zip_code_id' => $faker->numberBetween(1, 1000),
                'comments' => $faker->text(300),
            ]);
        }

        //Marcamos un cliente como baja y otro con solicitud de baja.
        DB::table('customers')->where('id', 5)->update(['cancel_at' => $faker->dateTimeBetween('- 30 days', 'now', 'Europe/Madrid')]);
        DB::table('customers')->where('id', 10)->update(['cancel_request' => $faker->dateTimeBetween('- 30 days', 'now', 'Europe/Madrid')]);

    }
}
