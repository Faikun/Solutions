<?php

namespace Database\Seeders;

use App\Models\Advert;
use App\Models\AdvertService;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ModuleCSeeder extends Seeder
{
    /*
    |--------------------------------------------------------------------------
    | Тестовые данные для Module C
    |--------------------------------------------------------------------------
    |
    | По условию модуля база не даётся готовой.
    | Поэтому в сидере мы создаём минимальный набор данных,
    | чтобы можно было проверить API руками и автотестами.
    |
    */
    public function run(): void
    {
        // Обязательный пользователь из PDF.
        $ethan = User::create([
            'name' => 'Ethan Brooks',
            'phone' => '+12025550143',
            'email' => 'ethan@ws-s17.kz',
            'password' => Hash::make('ethan_123'),
            'role' => 'user',
            'api_token' => Str::random(64),
        ]);

        // Обязательный модератор из PDF.
        $olivia = User::create([
            'name' => 'Olivia Carter',
            'phone' => '+447700900321',
            'email' => 'olivia@ws-s17.kz',
            'password' => Hash::make('olivia_123'),
            'role' => 'moderator',
            'api_token' => Str::random(64),
        ]);

        // Создаём базовые категории.
        $categories = collect(['LEGO', 'Board Games', 'Books', 'Souvenirs', 'Comics'])->mapWithKeys(function ($name) {
            return [$name => Category::create(['name' => $name])];
        });

        // Набор тестовых объявлений.
        $adverts = [
            [
                'status' => 'published',
                'title' => 'Original LEGO Set Complete',
                'text' => 'Original LEGO set in great condition with all details and manual.',
                'price' => 1200,
                'category_id' => $categories['LEGO']->id,
                'user_id' => $ethan->id,
                'photos' => ['https://example.com/photos/lego-1.jpg'],
            ],
            [
                'status' => 'published',
                'title' => 'Rare Board Game for Collectors',
                'text' => 'Board game with all cards and figures. Good choice for family evenings.',
                'price' => 2300,
                'category_id' => $categories['Board Games']->id,
                'user_id' => $ethan->id,
                'photos' => ['https://example.com/photos/board-game-1.jpg'],
            ],
            [
                'status' => 'draft',
                'title' => 'Fantasy Book Collection',
                'text' => 'Three fantasy books for collectors and readers.',
                'price' => 900,
                'category_id' => $categories['Books']->id,
                'user_id' => $ethan->id,
                'photos' => ['https://example.com/photos/books-1.jpg'],
            ],
            [
                'status' => 'moderation',
                'title' => 'Handmade Geek Souvenir',
                'text' => 'Small handmade souvenir for fans of games and comics.',
                'price' => 500,
                'category_id' => $categories['Souvenirs']->id,
                'user_id' => $ethan->id,
                'photos' => ['https://example.com/photos/souvenir-1.jpg'],
            ],
        ];

        foreach ($adverts as $data) {
            Advert::create($data);
        }

        // Добавляем VIP к первому объявлению.
        AdvertService::create([
            'advert_id' => 1,
            'type' => 'vip',
            'activated_at' => now()->subDay(),
            'expires_at' => now()->addDays(6),
        ]);

        // Добавляем TOP ко второму объявлению.
        AdvertService::create([
            'advert_id' => 2,
            'type' => 'top',
            'activated_at' => now(),
            'expires_at' => now()->addDays(3),
        ]);
    }
}
