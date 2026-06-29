# Keel

Keel is a goal-driven personal finance platform. It connects to a user's bank
accounts (via [Mono](https://mono.co)), imports and categorizes transactions,
lets users define savings goals and automation rules, forecasts goal
completion, and surfaces AI-generated spending insights.

Built with Laravel 12, Inertia.js, React, and TypeScript. The architecture is
deliberately scoped to run on **Hostinger shared hosting** — see
[`tasks/prd-keel-platform.md`](tasks/prd-keel-platform.md) §7 for the full
list of constraints (MySQL instead of PostgreSQL, no Redis, polling instead
of WebSockets, cron-scheduled jobs instead of persistent workers).

## Tech stack

- **Backend:** Laravel 12, MySQL/MariaDB, Laravel Fortify (auth + 2FA)
- **Frontend:** React 19 + TypeScript, Inertia.js, Tailwind CSS v4, shadcn/ui, Recharts, TanStack Table
- **Integrations:** [Mono](https://mono.co) (bank data), OpenAI (AI insights)
- **Testing:** Pest

## Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+ and npm
- A MySQL/MariaDB server — either:
  - **Docker** (recommended): a `docker-compose.yml` is included with a
    ready-to-use MySQL 8 service, or
  - a local MySQL/MariaDB install (e.g. via XAMPP)

## Local setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### Database

Start the bundled MySQL container:

```bash
docker compose up -d mysql
```

This runs MySQL on `127.0.0.1:3307` (database `keel`, passwordless root —
intentionally, since it's a disposable local dev container). Update `.env`
if you'd rather point at your own MySQL install instead:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=keel
DB_USERNAME=root
DB_PASSWORD=
```

Then run migrations and seed the default categories + a test user:

```bash
php artisan migrate
php artisan db:seed
```

The seeder creates a test login:

- **Email:** `test@example.com`
- **Password:** `password`

### Running the app

```bash
composer run dev
```

This runs the PHP dev server, the queue listener, and the Vite dev server
together. Or run them separately:

```bash
php artisan serve
php artisan queue:listen
npm run dev
```

### Scheduled jobs

Several features (bank sync, forecasts/health score, AI insights) run via
Laravel's scheduler rather than on each request — see
[`routes/console.php`](routes/console.php). Run the scheduler locally with:

```bash
php artisan schedule:work
```

In production (Hostinger), this is a single cron entry calling
`php artisan schedule:run` every minute — see
[`docs/deployment.md`](docs/deployment.md) for the full deployment runbook.

## Testing

```bash
php artisan test
```

Tests run against an isolated in-memory SQLite database (see `phpunit.xml`)
and never touch your local MySQL database.

## Frontend

```bash
npm run build      # production build
npm run lint        # eslint
npm run format      # prettier
```

## Project docs

- [`tasks/prd-keel-platform.md`](tasks/prd-keel-platform.md) — product requirements, rewritten for Hostinger shared hosting constraints
- [`tasks/tasks-keel-platform.md`](tasks/tasks-keel-platform.md) — full implementation task list and status
- [`docs/deployment.md`](docs/deployment.md) — deployment runbook for a no-SSH Hostinger plan

## Environment & credentials

Required third-party credentials (see `.env.example`):

- `MONO_SECRET_KEY` / `MONO_PUBLIC_KEY` / `MONO_WEBHOOK_SECRET` — from the [Mono dashboard](https://app.withmono.com)
- `OPENAI_API_KEY` — for AI-generated insights (categorization, spending/goal commentary, anomaly alerts)
- Transactional email provider credentials (`MAIL_*`) — choice still open, see PRD §9
