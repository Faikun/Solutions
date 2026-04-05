# WorldSkills Kazakhstan 2025 — Module A

Проект выполнен без фреймворков и состоит из следующих страниц:

- `index.html` — Home
- `seller.html` — For Seller
- `seller-services.html` — For Seller / Paid Services
- `buyer.html` — For Buyer
- `contacts.html` — Contacts
- `policy.html` — Policy

## Запуск проекта

### Вариант 1. Через VS Code + Live Server
1. Откройте папку проекта в VS Code.
2. Установите расширение **Live Server**, если оно ещё не установлено.
3. Откройте `index.html`.
4. Нажмите **Go Live**.

### Вариант 2. Через Python
Если у вас установлен Python, в корневой папке проекта выполните:

```bash
python -m http.server 8000
```

После этого откройте в браузере:

```text
http://localhost:8000
```

## Структура проекта

```text
worldskills-module-a/
├── index.html
├── seller.html
├── seller-services.html
├── buyer.html
├── contacts.html
├── policy.html
├── README.md
├── css/
│   └── style.css
└── js/
    ├── config.js
    └── main.js
```

## Как изменить ссылки на кнопках `Опубликовать` и `Посмотреть`

Кнопки `Publish` и `View` вынесены в единый конфиг, чтобы менять их было легко и сразу на всех страницах.

1. Откройте файл `js/config.js`.
2. Найдите объект:

```js
buttonLinks: {
  publish: 'seller.html',
  view: 'buyer.html'
}
```

3. Измените значения `publish` и `view` на нужные ссылки.

Пример:

```js
buttonLinks: {
  publish: 'https://example.com/publish',
  view: 'https://example.com/catalog'
}
```

После сохранения файла новые ссылки автоматически применятся ко всем кнопкам с атрибутами:

- `data-role="publish-link"`
- `data-role="view-link"`

## Важно про `media-files`

В проекте используются относительные пути к медиа-контенту, например:

- `media-files/logo.png`
- `media-files/map.png`
- `media-files/images/...`

Чтобы все изображения отображались корректно, папка `media-files` должна лежать рядом с HTML-файлами в корне проекта.

Пример:

```text
worldskills-module-a/
├── index.html
├── seller.html
├── ...
└── media-files/
```

## Что уже реализовано

- адаптивная вёрстка
- единый header и footer на всех страницах
- второй уровень меню без JavaScript
- разные стили для кнопок через атрибут `data-type`
- микро-анимации на кнопках и ссылках
- SEO meta tags
- OpenGraph meta tags
- семантическая HTML-разметка
- отдельная страница Contacts
- отдельная страница Policy
- расчёт стоимости платных услуг через `input type="range"`
