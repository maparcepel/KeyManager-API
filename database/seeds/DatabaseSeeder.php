<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Coded by: Víctor Castellanos Pérez
     * @return void
     */

    public function run()
    {
        $this->call(UsersSeeder::class);
        $this->call(CustomersSeeder::class);
        $this->call(LocationsSeeder::class);
        $this->call(EmployesSeeder::class);
        $this->call(CustomerUserSeeder::class);
        $this->call(ExpirationControlsSeeder::class);
        $this->call(MailHistorySeeder::class);
        $this->call(AccessHistorySeeder::class);
    }
}
