<?php

namespace App\Http\Controllers;

use App\Models\Advert;
use App\Models\AdvertPaidService;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdvertController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->query('status', ''));
        $categoryId = trim((string) $request->query('category_id', ''));
        $text = trim((string) $request->query('text', ''));

        $categories = Category::orderBy('name')->get();

        /*
        |--------------------------------------------------------------------------
        | Фильтры списка объявлений
        |--------------------------------------------------------------------------
        |
        | По ТЗ:
        | - по статусу
        | - по категории
        | - по тексту
        |
        | Текст ищем по:
        | - title
        | - text
        | - category name
        | - author name
        |
        */

        $adverts = Advert::query()
            ->with(['category', 'author', 'paidServices'])
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($categoryId !== '', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($text !== '', function ($query) use ($text) {
                $query->where(function ($nested) use ($text) {
                    $nested->where('title', 'like', "%{$text}%")
                        ->orWhere('text', 'like', "%{$text}%")
                        ->orWhereHas('category', function ($q) use ($text) {
                            $q->where('name', 'like', "%{$text}%");
                        })
                        ->orWhereHas('author', function ($q) use ($text) {
                            $q->where('name', 'like', "%{$text}%");
                        });
                });
            })
            ->orderByDesc('id')
            ->get();

        return view('adverts.index', compact('adverts', 'categories', 'status', 'categoryId', 'text'));
    }

    public function show(Advert $advert)
    {
        $advert->load(['category', 'author', 'photos', 'paidServices']);

        $allowedTransitions = $this->allowedTransitions($advert->status);

        return view('adverts.show', compact('advert', 'allowedTransitions'));
    }

    public function updateStatus(Request $request, Advert $advert)
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $allowed = $this->allowedTransitions($advert->status);

        if (!in_array($validated['status'], $allowed, true)) {
            return back()->with('error', 'Такой переход статуса запрещён по техзаданию.');
        }

        $advert->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Статус объявления обновлён.');
    }

    public function toggleService(Request $request, Advert $advert)
    {
        $validated = $request->validate([
            'service_type' => ['required', 'string'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Простое переключение платной услуги
        |--------------------------------------------------------------------------
        |
        | Если активная услуга уже есть -> отключаем
        | Если нет -> создаём новую на 7 дней
        |
        | Это простая и понятная логика для учебной версии.
        |
        */

        $existing = $advert->paidServices()
            ->where('service_type', $validated['service_type'])
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if ($existing) {
            $existing->update([
                'is_active' => false,
            ]);

            return back()->with('success', 'Платная услуга отключена.');
        }

        $advert->paidServices()->create([
            'service_type' => $validated['service_type'],
            'connected_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addDays(7),
            'is_active' => true,
        ]);

        return back()->with('success', 'Платная услуга подключена на 7 дней.');
    }

    public function export(Request $request): StreamedResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Экспорт только отфильтрованного списка
        |--------------------------------------------------------------------------
        |
        | Логика фильтров здесь повторяет страницу списка, чтобы в CSV
        | попадали ровно те же записи, что видит пользователь.
        |
        */

        $status = trim((string) $request->query('status', ''));
        $categoryId = trim((string) $request->query('category_id', ''));
        $text = trim((string) $request->query('text', ''));

        $adverts = Advert::query()
            ->with(['category', 'author'])
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($categoryId !== '', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($text !== '', function ($query) use ($text) {
                $query->where(function ($nested) use ($text) {
                    $nested->where('title', 'like', "%{$text}%")
                        ->orWhere('text', 'like', "%{$text}%")
                        ->orWhereHas('category', function ($q) use ($text) {
                            $q->where('name', 'like', "%{$text}%");
                        })
                        ->orWhereHas('author', function ($q) use ($text) {
                            $q->where('name', 'like', "%{$text}%");
                        });
                });
            })
            ->orderBy('id')
            ->get();

        $fileName = 'export_adverts.csv';

        return response()->streamDownload(function () use ($adverts) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Заголовок',
                'Название категории',
                'Цена',
                'Номер телефона автора',
                'Email автора',
                'Текст',
                'Дата размещения',
            ]);

            foreach ($adverts as $advert) {
                fputcsv($handle, [
                    $advert->id,
                    $advert->title,
                    optional($advert->category)->name,
                    $advert->price,
                    optional($advert->author)->phone,
                    optional($advert->author)->email,
                    $advert->text,
                    optional($advert->published_at)?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function allowedTransitions(string $currentStatus): array
    {
        return match ($currentStatus) {
            'moderation' => ['published', 'declined'],
            'published' => ['declined'],
            default => [],
        };
    }
}
