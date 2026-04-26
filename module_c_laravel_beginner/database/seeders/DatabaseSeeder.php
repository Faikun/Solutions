<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /*
    |--------------------------------------------------------------------------
    | Главный сидер приложения
    |--------------------------------------------------------------------------
    |
    | Laravel всегда запускает DatabaseSeeder первым.
    | Внутри него мы можем вызывать другие сидеры.
    |
    */
    public function run(): void
    {
        $this->call(ModuleCSeeder::class);
    }
}
