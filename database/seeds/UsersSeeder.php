<?php

use App\PasswordHistory;
use App\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('es_ES');

        //Creamos una contraseña ejemplo igual para todos los usuarios.
        $web_password = hash('sha256', '123456789');

        //Valores iniciales para usuario administrador.
        $user = User::create([
            'dni' => '55555555A',
            'name' => 'Administrador',
            'surname' => 'KeyManager',
            'email' => 'info@keymanager.tk',
            'web_password' => $web_password,
            'user_type_id' => 1,
        ]);
        //Añadimos la contraseña a su historial de contraseñas.
        PasswordHistory::create(
            [
                'user_id' => $user->id,
                'password' => hash('sha256', '123456789'),
                'status_type_id' => 2,
            ]
        );

        //Creamos 10 usuarios 'Cliente' - Id. 2 a 11
        for ($i = 0; $i < 10; $i++) {

            $user = User::create([
                'dni' => $faker->unique()->dni,
                'name' => $faker->firstName,
                'surname' => $faker->lastName,
                'email' => $faker->unique()->email,
                'phone' => $faker->tollFreeNumber,
                'web_password' => $web_password,
                'register_ip' => $faker->ipv4,
                'user_type_id' => 2,
            ]);

            //Añadimos la contraseña a su historial de contraseñas.
            PasswordHistory::create(
                [
                    'user_id' => $user->id,
                    'password' => $web_password,
                    'status_type_id' => 2,
                ]
            );
        }

        //Creamos 400 usuarios 'Empleado' - Id. 12 a 411
        for ($i = 0; $i < 400; $i++) {

            $user = User::create([
                'dni' => $faker->unique()->dni,
                'name' => $faker->firstName,
                'surname' => $faker->lastName,
                'email' => $faker->unique()->email,
                'phone' => $faker->tollFreeNumber,
                'web_password' => hash('sha256', '123456789'),
                'register_ip' => $faker->ipv4,
                'user_type_id' => 3,
            ]);
            //Añadimos la contraseña a su historial de contraseñas.
            PasswordHistory::create(
                [
                    'user_id' => $user->id,
                    'password' => $web_password,
                    'status_type_id' => 2,
                ]
            );
        }

        //Modificamos algunos usuarios indicando fechas en cancel_at and cancel_request
        $cambio = true;
        for ($i = 0; $i <= 150; $i++) {
            $user_id = $faker->numberBetween(2, 411);

            if ($cambio) {
                DB::table('users')->where('id', $user_id)->update(['cancel_at' => $faker->dateTimeBetween('- 30 days', 'now', 'Europe/Madrid')]);
                $cambio = false;
            } else {
                DB::table('users')->where('id', $user_id)->update(['cancel_request' => $faker->dateTimeBetween('- 30 days', 'now', 'Europe/Madrid')]);
                $cambio = true;
            }
        }

        //Insertamos un usuario tipo 'Cliente' y otro tipo 'Empleado' fijos (no aleatorios), para pruebas.
        //Insertamos un usuario tipo 'Empleado' fijo que aparecerá en multiples localizaciones, para pruebas.
        $user = User::create([
            'dni' => $faker->unique()->dni,
            'name' => 'Cliente',
            'surname' => 'KeyManager',
            'email' => 'cliente@keymanager.tk',
            'phone' => $faker->tollFreeNumber,
            'web_password' => hash('sha256', '123456789'),
            'register_ip' => $faker->ipv4,
            'user_type_id' => 2,
        ]);
        //Añadimos la contraseña a su historial de contraseñas.
        PasswordHistory::create(
            [
                'user_id' => $user->id,
                'password' => $web_password,
                'status_type_id' => 2,
            ]
        );

        $user = User::create([
            'dni' => $faker->unique()->dni,
            'name' => 'UniEmpleado',
            'surname' => 'KeyManager',
            'email' => 'empleado@keymanager.tk',
            'phone' => $faker->tollFreeNumber,
            'web_password' => hash('sha256', '123456789'),
            'register_ip' => $faker->ipv4,
            'user_type_id' => 3,
        ]);
        //Añadimos la contraseña a su historial de contraseñas.
        PasswordHistory::create(
            [
                'user_id' => $user->id,
                'password' => $web_password,
                'status_type_id' => 2,
            ]
        );
        $user = User::create([
            'dni' => $faker->unique()->dni,
            'name' => 'MultiEmpleado',
            'surname' => 'KeyManager',
            'email' => 'multiempleado@keymanager.tk',
            'phone' => $faker->tollFreeNumber,
            'web_password' => hash('sha256', '123456789'),
            'register_ip' => $faker->ipv4,
            'user_type_id' => 3,
        ]);
        //Añadimos la contraseña a su historial de contraseñas.
        PasswordHistory::create(
            [
                'user_id' => $user->id,
                'password' => $web_password,
                'status_type_id' => 2,
            ]
        );
    }
}
