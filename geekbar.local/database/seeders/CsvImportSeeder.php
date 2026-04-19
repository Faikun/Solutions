<?php

namespace Database\Seeders;

use App\Models\Advert;
use App\Models\AdvertPaidService;
use App\Models\AdvertPhoto;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CsvImportSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Учебный импорт CSV
        |--------------------------------------------------------------------------
        |
        | Мы читаем три файла:
        | - categories.csv
        | - users.csv
        | - adverts.csv
        |
        | И превращаем их в записи базы данных.
        |
        */

        $basePath = database_path('source-data');

        $this->importCategories($basePath . '/categories.csv');
        $this->importUsers($basePath . '/users.csv');
        $this->importAdverts($basePath . '/adverts.csv');
    }

    private function importCategories(string $path): void
    {
        foreach ($this->readCsv($path) as $row) {
            Category::updateOrCreate(
                ['external_id' => $row['id']],
                ['name' => $row['name']]
            );
        }
    }

    private function importUsers(string $path): void
    {
        foreach ($this->readCsv($path) as $row) {
            /*
            |--------------------------------------------------------------------------
            | В исходном CSV пароль записан как шаблон: <hash(name_123)>
            |--------------------------------------------------------------------------
            |
            | Для учебной версии мы достаём текст внутри скобок и хешируем
            | его через bcrypt. Так можно реально войти в систему.
            |
            */

            preg_match('/<hash\((.*?)\)>/', $row['password'], $matches);
            $plainPassword = $matches[1] ?? 'password_123';

            User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'phone' => $row['phone'],
                    'password' => Hash::make($plainPassword),
                    'role' => $row['role'],
                ]
            );
        }
    }

    private function importAdverts(string $path): void
    {
        $rows = $this->readCsv($path);
        $day = 1;

        foreach ($rows as $row) {
            $category = Category::where('external_id', $row['category'])->first();
            $author = User::where('email', $row['author'])->first();

            if (!$category || !$author) {
                continue;
            }

            $advert = Advert::create([
                'title' => $row['title'],
                'text' => $row['text'],
                'status' => $row['status'],
                'price' => (float) $row['price'],
                'views_count' => (int) $row['views_count'],
                'category_id' => $category->id,
                'user_id' => $author->id,

                // Добавим реалистичную дату публикации.
                'published_at' => Carbon::create(2025, 1, min($day, 28), 10, 0, 0),
            ]);

            $day++;

            $photos = json_decode($row['photos'], true) ?: [];
            foreach ($photos as $photoName) {
                AdvertPhoto::create([
                    'advert_id' => $advert->id,
                    'file_name' => $photoName,
                ]);
            }

            $services = json_decode($row['paid_services'], true) ?: [];
            foreach ($services as $index => $serviceType) {
                AdvertPaidService::create([
                    'advert_id' => $advert->id,
                    'service_type' => $serviceType,
                    'connected_at' => Carbon::now()->subDays(10 - $index),
                    'expires_at' => Carbon::now()->addDays(7 + $index),
                    'is_active' => true,
                ]);
            }
        }
    }

    private function readCsv(string $path): array
    {
        $rows = [];

        if (!file_exists($path)) {
            return $rows;
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            return $rows;
        }

        $headers = fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($headers, $data);
        }

        fclose($handle);

        return $rows;
    }
}
