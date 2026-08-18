# K&K Digital Solution (Laravel + MySQL)

Marketing website rebuilt on **Laravel 13** with **MySQL** (`kk_digital` on WAMP).

## Run locally

```bash
cd C:\Users\yashi\kk-laravel
php artisan serve --host=127.0.0.1 --port=8088
```

Open: http://127.0.0.1:8088

## Database

- DB: `kk_digital`
- User: `root` (WAMP default, empty password)
- Host: `127.0.0.1:3306`

```bash
php artisan migrate --seed
```

## Forms (saved to MySQL)

- Contact → `contact_inquiries`
- Start Project → `project_requests` (+ optional file in `storage/app/public`)
- Newsletter → `newsletter_subscribers`

## Content tables

`services`, `industries`, `case_studies`, `blog_posts`, `blog_categories`, `job_openings`, `faqs`, `team_members`, `site_settings`
