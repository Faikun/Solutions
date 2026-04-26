<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advert;
use App\Models\AdvertService;
use App\Models\User;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class AdvertController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Публичный список опубликованных объявлений
    |--------------------------------------------------------------------------
    |
    | По заданию список объявлений должен поддерживать:
    | - фильтр по категории;
    | - фильтр по цене;
    | - текстовый поиск;
    | - сортировку по цене или дате;
    | - особую логику TOP и VIP.
    |
    */
    public function index(Request $request)
    {
        // Читаем фильтры из query string.
        // Например: /api/adverts?query=lego&price_to=1200
        $queryText = $request->query('query');
        $priceFrom = $request->query('price_from');
        $priceTo = $request->query('price_to');

        // Строим базовый запрос только по опубликованным объявлениям.
        $base = Advert::query()
            // Сразу подгружаем связи, чтобы избежать лишних SQL-запросов.
            ->with(['category', 'user', 'services', 'views'])
            ->where('status', 'published')

            // Фильтр по категории.
            ->when($request->query('category_id'), fn ($q, $id) => $q->where('category_id', $id))

            // Фильтр по нижней границе цены.
            ->when($priceFrom, fn ($q, $value) => $q->where('price', '>=', (int) $value))

            // Фильтр по верхней границе цены.
            ->when($priceTo, fn ($q, $value) => $q->where('price', '<=', (int) $value))

            // Текстовый поиск сразу по нескольким полям.
            ->when($queryText, function ($q, $text) {
                $q->where(function ($inner) use ($text) {
                    $inner->where('title', 'like', "%{$text}%")
                        ->orWhere('text', 'like', "%{$text}%")
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$text}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$text}%"));
                });
            });

        // По умолчанию сортируем по дате по убыванию.
        $sortBy = $request->query('sort_by', 'date');
        $sort = $request->query('sort', 'desc') === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'price') {
            $base->orderBy('price', $sort);
        } else {
            $base->orderBy('created_at', $sort);
        }

        // Получаем обычный список объявлений без VIP/TOP-перестановки.
        $normal = $base->get();

        // Отдельно считаем TOP-блок.
        $top = $this->topAdverts($request);

        // Отдельно считаем VIP-блок, исключая уже занятые TOP-id.
        $vip = $this->vipAdverts($request, $top->pluck('id'));

        // Собираем финальный список в правильном порядке:
        // 1. TOP
        // 2. VIP
        // 3. обычные объявления без повторов
        $result = $top
            ->concat($vip)
            ->concat($normal->whereNotIn('id', $top->pluck('id')->merge($vip->pluck('id'))))
            ->values();

        // Простейшая пагинация руками на коллекции.
        $page = max((int) $request->query('page', 1), 1);
        $perPage = in_array((int) $request->query('per_page', 10), [10, 20, 30])
            ? (int) $request->query('per_page', 10)
            : 10;

        $paged = $result->forPage($page, $perPage)->values();

        // Возвращаем уже отформатированные объявления.
        return response()->json($paged->map(fn ($advert) => $this->formatAdvert($advert)));
    }

    /*
    |--------------------------------------------------------------------------
    | Детали объявления
    |--------------------------------------------------------------------------
    |
    | Если пользователь авторизован, мы должны учитывать просмотр в таблице views.
    | По заданию:
    | - новая запись создаётся один раз;
    | - повторный просмотр не создаёт новую запись;
    | - обновляется updated_at.
    |
    */
    public function show(Request $request, Advert $advert)
    {
        // Подгружаем связанные данные.
        $advert->load(['category', 'user', 'views', 'services']);

        // Пробуем взять Bearer token.
        $token = $request->bearerToken();

        // Логика учёта просмотра работает только для авторизованных.
        if ($token) {
            $user = User::where('api_token', $token)->first();

            if ($user) {
                View::updateOrCreate(
                    ['advert_id' => $advert->id, 'user_id' => $user->id],
                    ['updated_at' => now()]
                );
            }
        }

        return response()->json($this->formatAdvert($advert->fresh(['category', 'user', 'views', 'services'])));
    }

    /*
    |--------------------------------------------------------------------------
    | Создание объявления
    |--------------------------------------------------------------------------
    |
    | По заданию:
    | - объявление может создать только авторизованный пользователь;
    | - статус при создании всегда draft;
    | - у объявления должна быть хотя бы одна фотография.
    |
    */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'text' => ['required', 'string', 'max:1000'],
            'price' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],

            // Поддерживаем обычный массив photos.
            'photos' => ['required_without:photos[]', 'array', 'min:1'],
            'photos.*' => ['string'],

            // И на всякий случай вариант photos[] из некоторых клиентов.
            'photos[]' => ['sometimes', 'array', 'min:1'],
        ]);

        // Берём массив фото из одного из двух поддерживаемых вариантов.
        $photos = $request->input('photos', $request->input('photos[]', []));

        // Создаём объявление.
        $advert = Advert::create([
            'title' => $data['title'],
            'text' => $data['text'],
            'price' => $data['price'],
            'category_id' => $data['category_id'],
            'user_id' => $request->user()->id,
            'photos' => $photos,
            'status' => 'draft',
        ]);

        return response()->json($this->formatAdvert($advert->load(['category', 'user', 'views', 'services'])));
    }

    /*
    |--------------------------------------------------------------------------
    | Редактирование объявления
    |--------------------------------------------------------------------------
    |
    | По заданию:
    | - автор может редактировать только свои draft-объявления;
    | - модератор может редактировать любое объявление.
    |
    */
    public function update(Request $request, Advert $advert)
    {
        $user = $request->user();

        if (!$user->isModerator()) {
            if ($advert->user_id !== $user->id || $advert->status !== 'draft') {
                return response()->json(['error' => 'You do not have permission to perform this request'], 403);
            }
        }

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:200'],
            'text' => ['sometimes', 'string', 'max:1000'],
            'price' => ['sometimes', 'integer', 'min:0'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'photos' => ['sometimes', 'array', 'min:1'],
        ]);

        $advert->update($data);

        return response()->json($this->formatAdvert($advert->fresh(['category', 'user', 'views', 'services'])));
    }

    /*
    |--------------------------------------------------------------------------
    | Удаление объявления
    |--------------------------------------------------------------------------
    |
    | По заданию удалять объявление может только автор,
    | и только если статус:
    | - draft
    | - archived
    |
    */
    public function destroy(Request $request, Advert $advert)
    {
        if ($advert->user_id !== $request->user()->id) {
            return response()->json(['error' => 'You do not have permission to perform this request'], 403);
        }

        if (!in_array($advert->status, ['draft', 'archived'], true)) {
            return response()->json(['error' => 'Advert can be deleted only in draft or archived status'], 403);
        }

        $advert->delete();

        return response()->json(null, 204);
    }

    /*
    |--------------------------------------------------------------------------
    | Смена статуса объявления
    |--------------------------------------------------------------------------
    |
    | Статусы меняются не произвольно, а только по разрешённым переходам.
    | Для user и moderator набор переходов разный.
    |
    */
    public function updateStatus(Request $request, Advert $advert)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['draft', 'moderation', 'declined', 'published', 'archived'])],
        ]);

        $from = $advert->status;
        $to = $data['status'];
        $user = $request->user();

        // Разрешённые переходы для автора объявления.
        $allowedForUser = [
            'draft' => ['moderation', 'archived'],
            'published' => ['draft', 'archived'],
            'declined' => ['draft', 'archived'],
        ];

        // Разрешённые переходы для модератора.
        $allowedForModerator = [
            'moderation' => ['published', 'declined'],
            'published' => ['declined'],
        ];

        $allowed = false;

        if ($user->isModerator()) {
            $allowed = in_array($to, $allowedForModerator[$from] ?? [], true);
        } elseif ($advert->user_id === $user->id) {
            $allowed = in_array($to, $allowedForUser[$from] ?? [], true);
        }

        if (!$allowed) {
            return response()->json(['error' => 'Status transition is not allowed'], 403);
        }

        $advert->update(['status' => $to]);

        return response()->json($this->formatAdvert($advert->fresh(['category', 'user', 'views', 'services'])));
    }

    /*
    |--------------------------------------------------------------------------
    | Список услуг объявления
    |--------------------------------------------------------------------------
    */
    public function services(Advert $advert)
    {
        return response()->json($advert->services()->latest('activated_at')->get());
    }

    /*
    |--------------------------------------------------------------------------
    | Подключение услуги VIP или TOP
    |--------------------------------------------------------------------------
    |
    | VIP действует 7 дней.
    | TOP действует 3 дня.
    |
    */
    public function enableService(Request $request, Advert $advert, string $type)
    {
        if (!in_array($type, ['vip', 'top'], true)) {
            return response()->json(['error' => 'Service type must be vip or top'], 422);
        }

        if ($advert->user_id !== $request->user()->id && !$request->user()->isModerator()) {
            return response()->json(['error' => 'You do not have permission to perform this request'], 403);
        }

        $days = $type === 'vip' ? 7 : 3;

        $service = AdvertService::create([
            'advert_id' => $advert->id,
            'type' => $type,
            'activated_at' => now(),
            'expires_at' => now()->addDays($days),
        ]);

        return response()->json($service, 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Продление услуги VIP или TOP
    |--------------------------------------------------------------------------
    |
    | Если активной услуги нет, вместо ошибки подключаем новую.
    |
    */
    public function extendService(Request $request, Advert $advert, string $type)
    {
        if (!in_array($type, ['vip', 'top'], true)) {
            return response()->json(['error' => 'Service type must be vip or top'], 422);
        }

        if ($advert->user_id !== $request->user()->id && !$request->user()->isModerator()) {
            return response()->json(['error' => 'You do not have permission to perform this request'], 403);
        }

        $days = $type === 'vip' ? 7 : 3;

        $service = $advert->services()
            ->where('type', $type)
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();

        if (!$service) {
            return $this->enableService($request, $advert, $type);
        }

        $service->update([
            'expires_at' => $service->expires_at->copy()->addDays($days),
        ]);

        return response()->json($service->fresh());
    }

    /*
    |--------------------------------------------------------------------------
    | Единый формат ответа по объявлению
    |--------------------------------------------------------------------------
    */
    public function formatAdvert(Advert $advert): array
    {
        return [
            'id' => $advert->id,
            'status' => $advert->status,
            'title' => $advert->title,
            'text' => $advert->text,
            'price' => $advert->price,

            // Если views_count поля нет, считаем просмотры через связь.
            'views' => $advert->views_count ?? $advert->views()->count(),

            // Возвращаем не только id категории, но и её имя для удобства фронтенда.
            'category_id' => [
                'id' => $advert->category?->id,
                'name' => $advert->category?->name,
            ],

            // То же самое для автора.
            'user_id' => [
                'id' => $advert->user?->id,
                'name' => $advert->user?->name,
                'phone' => $advert->user?->phone,
            ],

            'photos' => $advert->photos ?: [],

            // Приводим услуги к единообразному JSON.
            'services' => $advert->services?->map(fn ($service) => [
                'type' => $service->type,
                'activated_at' => $service->activated_at?->toISOString(),
                'expires_at' => $service->expires_at?->toISOString(),
            ])->values() ?? [],

            // В учебной версии показываем created_at в человекочитаемом формате,
            // а updated_at оставляем ISO-8601.
            'created_at' => $advert->created_at?->format('d.m.Y'),
            'updated_at' => $advert->updated_at?->toISOString(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | TOP-объявления
    |--------------------------------------------------------------------------
    |
    | По заданию TOP идёт перед VIP.
    | TOP:
    | - должен подчиняться всем фильтрам;
    | - сортируется по времени активации услуги, самые новые выше.
    |
    */
    private function topAdverts(Request $request): Collection
    {
        return Advert::query()
            ->with(['category', 'user', 'services', 'views'])
            ->where('status', 'published')
            ->whereHas('services', fn ($q) => $q->where('type', 'top')->where('expires_at', '>', now()))
            ->when($request->query('category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->query('price_from'), fn ($q, $v) => $q->where('price', '>=', (int) $v))
            ->when($request->query('price_to'), fn ($q, $v) => $q->where('price', '<=', (int) $v))
            ->when($request->query('query'), function ($q, $text) {
                $q->where(function ($inner) use ($text) {
                    $inner->where('title', 'like', "%{$text}%")
                        ->orWhere('text', 'like', "%{$text}%")
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$text}%"))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$text}%"));
                });
            })
            ->join('advert_services', function ($join) {
                $join->on('adverts.id', '=', 'advert_services.advert_id')
                    ->where('advert_services.type', '=', 'top')
                    ->where('advert_services.expires_at', '>', now());
            })
            ->orderByDesc('advert_services.activated_at')
            ->select('adverts.*')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | VIP-объявления
    |--------------------------------------------------------------------------
    |
    | По заданию VIP:
    | - занимает первые 3 позиции после TOP-блока;
    | - не должен повторяться в обычной части списка;
    | - может попасть в результат, даже если:
    |   - не совпадает по тексту;
    |   - выходит за price range меньше чем на 100.
    |
    */
    private function vipAdverts(Request $request, Collection $excludeIds): Collection
    {
        $priceFrom = $request->query('price_from');
        $priceTo = $request->query('price_to');

        $vip = Advert::query()
            ->with(['category', 'user', 'services', 'views'])
            ->where('status', 'published')
            ->whereNotIn('id', $excludeIds)
            ->whereHas('services', fn ($q) => $q->where('type', 'vip')->where('expires_at', '>', now()))
            ->when($request->query('category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($priceFrom, fn ($q, $v) => $q->where('price', '>=', max(0, (int) $v - 99)))
            ->when($priceTo, fn ($q, $v) => $q->where('price', '<=', (int) $v + 99))
            ->inRandomOrder()
            ->take(3)
            ->get();

        return $vip->values();
    }
}
