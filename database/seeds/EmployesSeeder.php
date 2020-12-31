<?php

use App\Helpers\Security;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class EmployesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('es_ES');

        $x = 1;
        $locStart = 1;
        $locEnd = 5;
        $contador = 0;
        for ($i = 12; $i < 412; $i++) {
            $location = $faker->numberBetween($locStart, $locEnd);
            if ($contador == 40) {
                $locStart += 5;
                $locEnd += 5;
                $contador = 0;
            }
            DB::table('employes')->insert([
                'location_id' => $location,
                'user_id' => $i,
                'position_type_id' => $faker->numberBetween(1, 10),
                'call_order' => $faker->randomDigit,
                'caller_password' => Security::encrypt($faker->text(30)),
                'personal_password' => Security::encrypt($faker->text(30)),
                'panel_order' => $faker->randomDigit,
                'panel_code' => Security::encrypt($faker->randomNumber(4, true)),
                'biometric_id' => $faker->numberBetween(1, 100),
            ]);
            //Añadimos un historial de contraseñas al empleado.
            for ($y = 0; $y < 3; $y++) {
                DB::table('employe_histories')->insert([
                    'location_id' => $location,
                    'employe_id' => $x,
                    'caller_password' => Security::encrypt($faker->text(30)),
                    'personal_password' => Security::encrypt($faker->text(30)),
                    'panel_order' => $faker->randomDigit,
                    'panel_code' => Security::encrypt($faker->randomNumber(4, true)),
                    'biometric_id' => $faker->numberBetween(1, 100),
                ]);
            }
            $x++;
            $contador++;
        }
        //Modificamos algunos empleados indicando fechas en cancel_at and cancel_request
        $cambio = true;
        for ($i = 0; $i <= 150; $i++) {
            $employe_id = $faker->numberBetween(1, 411);

            if ($cambio) {
                DB::table('employes')->where('id', $employe_id)->update(['cancel_at' => $faker->dateTimeBetween('- 30 days', 'now', 'Europe/Madrid')]);
                $cambio = false;
            } else {
                DB::table('employes')->where('id', $employe_id)->update(['cancel_request' => $faker->dateTimeBetween('- 30 days', 'now', 'Europe/Madrid')]);
                $cambio = true;
            }
        }
        //Añadimos nuestros empleado de pruebas
        DB::table('employes')->insert([
            'location_id' => 18,
            'user_id' => 413,
            'position_type_id' => $faker->numberBetween(1, 10),
            'call_order' => $faker->randomDigit,
            'caller_password' => Security::encrypt($faker->text(30)),
            'personal_password' => Security::encrypt($faker->text(30)),
            'panel_order' => $faker->randomDigit,
            'panel_code' => Security::encrypt($faker->randomNumber(4, true)),
            'biometric_id' => $faker->numberBetween(1, 100),
        ]);
        //Añadimos nuestro multiempleado
        DB::table('employes')->insert([
            'location_id' => 30,
            'user_id' => 414,
            'position_type_id' => $faker->numberBetween(1, 10),
            'call_order' => $faker->randomDigit,
            'caller_password' => Security::encrypt($faker->text(30)),
            'personal_password' => Security::encrypt($faker->text(30)),
            'panel_order' => $faker->randomDigit,
            'panel_code' => Security::encrypt($faker->randomNumber(4, true)),
            'biometric_id' => $faker->numberBetween(1, 100),
        ]);

        DB::table('employes')->insert([
            'location_id' => 33,
            'user_id' => 414,
            'position_type_id' => $faker->numberBetween(1, 10),
            'call_order' => $faker->randomDigit,
            'caller_password' => Security::encrypt($faker->text(30)),
            'personal_password' => Security::encrypt($faker->text(30)),
            'panel_order' => $faker->randomDigit,
            'panel_code' => Security::encrypt($faker->randomNumber(4, true)),
            'biometric_id' => $faker->numberBetween(1, 100),
        ]);

    }
}
