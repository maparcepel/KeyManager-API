<?php

use Illuminate\Database\Seeder;

class CustomerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function run()
    {
        //Insertamos la relacion cliente-cliente
        for ($i = 1; $i < 11; $i++) {
            DB::table('customer_user')->insert([
                'user_id' => $i + 1,
                'customer_id' => $i,
            ]);
        }

        //Insertamos nuestro cliente de prueba fijo
        DB::table('customer_user')->insert([
            'user_id' => 412, // cliente@keymanager.tk
            'customer_id' => 3,
        ]);

        //Insertamos la relacion cliente - empleado.
        $customer = 1;
        $contador = 0;
        for ($i = 12; $i < 412; $i++) {
            if ($contador == 40) {
                $customer++;
                $contador = 0;
            }
            DB::table('customer_user')->insert([
                'user_id' => $i,
                'customer_id' => $customer,
            ]);
            $contador++;
        }

        //Insertamos nuestro empleado de pruebas fijo.
        DB::table('customer_user')->insert([
            'user_id' => 413,
            'customer_id' => 4,
        ]);

        //Insertamos nuestro multiempleado de pruebas fijo.
        DB::table('customer_user')->insert([
            'user_id' => 414,
            'customer_id' => 6,
        ]);
        DB::table('customer_user')->insert([
            'user_id' => 414,
            'customer_id' => 7,
        ]);
    }
}
