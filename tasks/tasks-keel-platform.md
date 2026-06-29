## Relevant Files

### Backend — config / infra
- `config/database.php` - Switch default connection to MySQL.
- `config/cache.php`, `config/session.php`, `config/queue.php` - Switch drivers to `database` (no Redis available).
- `config/services.php` - Add Mono and OpenAI API credentials.
- `config/mail.php` - Configure transactional email provider.
- `routes/console.php` - Scheduler definitions (sync, forecasts/health score, AI insights, rule evaluation, DB backup export).
- `routes/web.php` - New routes for bank connections, accounts, transactions, goals, allocations, rules, dashboard, notifications.
- `.env.example` - Document new required env vars (DB_*, MAIL_*, MONO_*, OPENAI_*).
- `docs/deployment.md` - Deployment runbook for a no-SSH Hostinger shared plan (build locally, deploy via hPanel Git or FTP, storage symlink workaround).

### Backend — database
- `database/migrations/*_create_audit_logs_table.php`
- `database/migrations/*_add_two_factor_columns_to_users_table.php`
- `database/migrations/*_create_bank_connections_table.php`
- `database/migrations/*_create_accounts_table.php`
- `database/migrations/*_create_sync_logs_table.php`
- `database/migrations/*_create_categories_table.php`
- `database/migrations/*_create_transactions_table.php`
- `database/migrations/*_create_category_overrides_table.php`
- `database/migrations/*_create_goals_table.php`
- `database/migrations/*_create_allocations_table.php`
- `database/migrations/*_create_rules_table.php`
- `database/migrations/*_create_rule_executions_table.php`
- `database/migrations/*_create_forecasts_table.php` / `*_create_health_scores_table.php`
- `database/migrations/*_create_insights_table.php`
- `database/migrations/*_create_notifications_table.php` (or use Laravel's built-in notifications migration)
- `database/seeders/CategorySeeder.php`

### Backend — models / services / controllers
- `app/Models/User.php` - Add 2FA fields, preferences relation.
- `app/Models/{BankConnection,Account,Transaction,Category,Goal,Allocation,Rule,RuleExecution,SyncLog,Insight,AuditLog}.php`
- `app/Services/Mono/MonoClient.php` - Thin HTTP client for Mono API.
- `app/Services/Mono/MonoSyncService.php` - Pulls accounts/transactions, idempotent import, triggers categorization.
- `app/Services/Allocation/AllocationEngine.php` - Validates and applies manual/automatic allocations against available balance.
- `app/Services/Rules/RuleEvaluator.php` - Matches active rules against new transactions and applies allocations.
- `app/Services/Forecasting/ForecastService.php` and `app/Services/Forecasting/HealthScoreService.php`
- `app/Services/AI/OpenAiClient.php` and `app/Services/AI/InsightGenerator.php`
- `app/Http/Controllers/Auth/TwoFactorController.php`
- `app/Http/Controllers/{BankConnectionController,MonoWebhookController,AccountController,TransactionController,GoalController,AllocationController,RuleController,DashboardController,NotificationController}.php`
- `app/Notifications/*` - Per-notification-type Laravel notification classes (mail + database channel).

### Frontend (Inertia/React)
- `resources/js/pages/dashboard.tsx` - Already exists as a placeholder; becomes the real dashboard.
- `resources/js/pages/settings/two-factor.tsx` - New.
- `resources/js/pages/bank-connections/index.tsx`, `resources/js/pages/accounts/index.tsx`, `resources/js/pages/transactions/index.tsx`
- `resources/js/pages/goals/{index,create,edit,show}.tsx`
- `resources/js/pages/rules/{index,create,edit}.tsx`
- `resources/js/components/dashboard/*` (charts, summary cards), `resources/js/components/transactions/*`, `resources/js/components/goals/*`, `resources/js/components/notifications/*`
- `resources/js/hooks/use-polling.ts` - Shared interval-polling hook used by dashboard + notifications.
- `resources/js/lib/format-currency.ts`
- `resources/js/types/*.d.ts` - Shared types for accounts/transactions/goals/etc.

### Tests
- `tests/Feature/Auth/TwoFactorTest.php`
- `tests/Feature/Mono/{ConnectFlowTest,WebhookTest,SyncServiceTest}.php`
- `tests/Feature/Transactions/{ImportTest,FilterTest,CategorizationTest}.php`
- `tests/Feature/Goals/GoalCrudTest.php`
- `tests/Unit/Services/AllocationEngineTest.php`
- `tests/Unit/Services/RuleEvaluatorTest.php`
- `tests/Unit/Services/ForecastServiceTest.php`
- `tests/Unit/Services/HealthScoreServiceTest.php`
- `tests/Feature/AI/InsightGenerationTest.php`
- `tests/Feature/NotificationDispatchTest.php`

### Notes

- This is a Laravel 12 + Inertia + React app (Pest for tests, not PHPUnit-style `Test` suffix conventions beyond what Pest scaffolds). Run tests with `php artisan test` or `vendor/bin/pest`. Run a single file with `vendor/bin/pest tests/Feature/Mono/SyncServiceTest.php`.
- Frontend: `npm run lint` (eslint) and `npm run format` (prettier) before committing. No JS test runner is configured yet in `package.json` — if tests are added for hooks/components, a runner (e.g., Vitest) must be introduced as part of that task.
- Per `.claude/rules/code-separation.md`: keep Inertia pages thin (composition only); put fetch/polling logic in `hooks`, formatting/helpers in `lib`, and keep controllers thin by pushing business logic into `app/Services`.
- Per `.claude/rules/slug-rule.md`: no new slug/code field gets a form input anywhere in this task list — if any entity needs a public-facing slug later, generate it server-side only.
- All "scheduled job" sub-tasks below assume a single Hostinger cron entry (`* * * * * php artisan schedule:run`) driving Laravel's scheduler, with real cadence floored at ~5 minutes per the PRD's hosting constraints — do not design any feature around sub-minute timing.

## Tasks

- [ ] 0.0 Create feature branch
  - [ ] 0.1 Create and checkout a new branch for this work (e.g., `git checkout -b feature/keel-platform-mvp`)

- [ ] 1.0 Hostinger environment, deployment pipeline & data layer setup
  - [ ] 1.1 **Blocked — needs real account.** Confirm actual Hostinger plan details once provisioned: available PHP version, cron minimum interval, whether SSH/Git deploy is exposed in hPanel, whether File Manager can create symlinks
  - [x] 1.2 Update `.env.example` to default to MySQL (`config/database.php` already ships with a correct `mysql` connection out of the box — no change needed there)
  - [x] 1.3 Confirmed the starter kit already defaults `cache`/`session`/`queue` to the `database` driver, with `sessions`, `cache`, `jobs`, `job_batches`, and `failed_jobs` migrations already present — nothing further needed
  - [x] 1.4 Added a `heartbeat` command + `Schedule::command('heartbeat')->everyMinute()` in `routes/console.php`; verified via `php artisan schedule:list`
  - [x] 1.5 Added `Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()->withoutOverlapping()` in `routes/console.php` so queued jobs drain every cron tick
  - [x] 1.6 Resolved via Laravel's built-in disk-serving feature instead of a custom route: set `'serve' => true` on the `public` disk in `config/filesystems.php` with a distinct `/public-storage` URL prefix (avoids colliding with the private `local` disk's reserved `/storage` route) — verified both routes register cleanly via `php artisan route:list`
  - [ ] 1.7 **Partially done.** Scaffolded generic SMTP mail config in `.env.example` (works with any transactional provider's SMTP credentials); actual provider choice (Resend/Postmark/Mailgun) and a real end-to-end test send are still pending a decision + account
  - [x] 1.8 Wrote `docs/deployment.md`: local build steps, deploy mechanism options, cron setup, storage approach, rollback note
  - [ ] 1.9 **Blocked — needs real account.** Confirm domain + free SSL are active on the Hostinger account before any webhook URL (Mono) is registered

- [x] 2.0 Authentication & user management
  - [x] 2.1 Verified existing starter-kit auth flows work end-to-end against real local MySQL (ran migrations + model CRUD directly against MySQL; full Pest suite — which exercises the same Eloquent code paths — passes)
  - [x] 2.2 Added 2FA columns via `database/migrations/2025_08_14_170933_add_two_factor_columns_to_users_table.php`
  - [x] 2.3 **Superseded by adopting Laravel Fortify** instead of a hand-rolled TOTP service (see project decision) — `Laravel\Fortify\TwoFactorAuthenticatable` trait + Fortify's own enable/confirm/disable/recovery-code routes and actions; `Settings\TwoFactorAuthenticationController` renders the page
  - [x] 2.4 Built `resources/js/pages/settings/two-factor.tsx` + `resources/js/hooks/use-two-factor-auth.ts`
  - [x] 2.5 2FA challenge at login handled by Fortify's `RedirectIfTwoFactorAuthenticatable` pipeline + `resources/js/pages/auth/two-factor-challenge.tsx`
  - [x] 2.6 Added `audit_logs` migration + `AuditLog` model; `AuditLogSubscriber` records login, password change/reset, 2FA enable/confirm/disable; bank connect/disconnect logged in `BankConnectionController` (3.0)
  - [x] 2.7 Added `currency`/`notification_preferences` columns + `resources/js/pages/settings/preferences.tsx` + `Settings\PreferencesController`
  - [x] 2.8 Pest tests: `tests/Feature/Auth/TwoFactorAuthenticationTest.php`, `tests/Feature/AuditLogTest.php`, `tests/Feature/Settings/PreferencesTest.php`
  - **Note:** adopting Fortify meant replacing the starter kit's custom `Auth` controllers entirely (login/register/password/email-verification now flow through Fortify, with Inertia views wired up in `app/Providers/FortifyServiceProvider.php`) rather than only bolting on 2FA. All 40 auth-related tests pass.

- [x] 3.0 Mono bank integration
  - [x] 3.1 Added Mono credentials to `config/services.php`/`.env.example` (`MONO_SECRET_KEY`, `MONO_PUBLIC_KEY`, `MONO_WEBHOOK_SECRET`, `MONO_BASE_URL`)
  - [x] 3.2 Built `app/Services/Mono/MonoClient.php` (exchangeToken, getAccount, getTransactions, sync, unlink)
  - [x] 3.3 `bank_connections`/`accounts` migrations + models (done in the platform-wide migration pass)
  - [x] 3.4 Connect flow: `resources/js/hooks/use-mono-connect.ts` + `resources/js/pages/bank-connections/index.tsx` + `BankConnectionController`
  - [x] 3.5 `MonoWebhookController` (shared-secret header check, since Mono uses a static `mono-webhook-secret` header rather than an HMAC signature) + `SyncBankConnection` queued job
  - [x] 3.6 `sync_logs` migration/model + `MonoSyncService`; scheduled via `app:sync-bank-connections` (routes/console.php, every 15 minutes)
  - [x] 3.7 Reauth required: `MonoSyncService` sets `status=reauth_required` on a 401 from Mono (and the webhook handles `issues.reauthorisation_required` directly); surfaced via the status badge on the bank-connections page. Reconnecting reuses the same "Connect a bank account" button (`updateOrCreate` keyed on `mono_account_id`+`user_id`) rather than a separate reconnect flow.
  - [x] 3.8 Disconnect action in `BankConnectionController::destroy` (marks `disconnected`, keeps historical accounts/transactions)
  - [x] 3.9 `tests/Feature/Mono/ConnectFlowTest.php` (mocked HTTP, idempotency), `tests/Feature/Mono/WebhookTest.php` (signature check, event routing)

- [x] 4.0 Account & transaction management
  - [x] 4.1 `resources/js/pages/accounts/index.tsx` + `AccountController` (total balance, account count)
  - [x] 4.2 `categories` migration/model + `CategorySeeder` (10 default categories) + user-defined custom categories supported (nullable `user_id`)
  - [x] 4.3 `transactions` migration/model (done in the platform-wide migration pass; unique `mono_transaction_id` for idempotent import)
  - [x] 4.4 `app/Services/Transactions/TransactionCategorizer.php` (keyword map + user override lookup), hooked into `MonoSyncService::importTransactions`
  - [x] 4.5 `resources/js/pages/transactions/index.tsx` + `components/transactions/transactions-table.tsx` (TanStack Table, installed `@tanstack/react-table`) with account/category/type/date-range/amount-range filters via `hooks/use-transaction-filters.ts`, backed by `TransactionController::index`
  - [x] 4.6 Inline category `<Select>` in the table → `TransactionController::update` → `TransactionCategorizer::remember()` upserts a `category_overrides` row keyed by the transaction's lowercased description
  - [x] 4.7 `tests/Feature/Transactions/{FilterTest,CategorizationTest}.php`, `tests/Feature/AccountsTest.php` (import idempotency already covered by `Mono/ConnectFlowTest`)

- [x] 5.0 Savings goals & goal allocation engine
  - [x] 5.1 `goals` migration/model (done in the platform-wide migration pass)
  - [x] 5.2 `GoalController` (index/create/store/show/edit/update, no destroy per PRD §4.5) + `resources/js/pages/goals/{index,create,edit,show}.tsx` + shared `components/goals/{goal-form,goal-progress-card}.tsx`
  - [x] 5.3 `allocations` migration/model (done in the platform-wide migration pass)
  - [x] 5.4 `app/Services/Allocation/AllocationEngine.php` (`availableBalance`, `totalAllocated`, `unallocatedBalance`, `allocate` throws `InsufficientBalanceException`, `deallocate`)
  - [x] 5.5 Manual allocation form on `goals/show.tsx` + `AllocationController`, validation error surfaced via Inertia form errors
  - [x] 5.6 `tests/Unit/Services/AllocationEngineTest.php` + `tests/Feature/Goals/GoalCrudTest.php` (also had to extend `tests/Pest.php`'s `RefreshDatabase` binding to the `Unit` suite, since these "unit" tests exercise real Eloquent models)

- [x] 6.0 Rules engine
  - [x] 6.1 `rules` migration/model (done in the platform-wide migration pass)
  - [x] 6.2 `RuleController` (full CRUD incl. destroy) + `resources/js/pages/rules/{index,create,edit,show}.tsx` + shared `components/rules/rule-form.tsx`
  - [x] 6.3 `app/Services/Rules/RuleEvaluator.php` hooked into `MonoSyncService::importTransactions` right after categorization, applying allocations via `AllocationEngine` (skips silently — no log — if the allocation would exceed the unallocated balance)
  - [x] 6.4 `rule_executions` migration/model (done in the platform-wide migration pass); `RuleEvaluator::execute()` logs rule+transaction+allocation
  - [x] 6.5 `rules/show.tsx` execution history page (linked from `rules/index.tsx`)
  - [x] 6.6 `tests/Unit/Services/RuleEvaluatorTest.php` (matching by transaction_type/merchant, inactive rules, insufficient-balance skip) + `tests/Feature/Rules/RuleCrudTest.php`

- [x] 7.0 Financial dashboard
  - [x] 7.1 `app/Services/Dashboard/DashboardAggregator.php` (summary/spendingByCategory/incomeExpenseTrend, each `Cache::remember`'d for 60s on the `database` store)
  - [x] 7.2 Real `resources/js/pages/dashboard.tsx` composing `components/dashboard/{spending-by-category-chart,income-expense-trend-chart}.tsx` (shadcn `Chart` wrapping Recharts — already installed, used the `shadcn` skill + `npx shadcn@latest docs chart` to confirm usage) and reused `GoalProgressCard`; data from `DashboardController`
  - [x] 7.3 `resources/js/hooks/use-polling.ts`; dashboard polls `summary`/`spendingByCategory`/`trend`/`goals` via Inertia partial reload every 30s
  - [x] 7.4 `tests/Unit/Services/DashboardAggregatorTest.php`

- [x] 8.0 Forecasting & financial health score
  - [x] 8.1 `app/Services/Forecasting/ForecastService.php` (`averageMonthlySaving`, `forecastGoal`, `forecastBalance`)
  - [x] 8.2 `app/Services/Forecasting/HealthScoreService.php` (weighted: savings rate 25%, emergency fund 25%, goal progress 20%, spending stability 15%, income consistency 15%; neutral 50 defaults when there's not enough history yet)
  - [x] 8.3 `goal_forecasts`/`financial_health_scores` migrations (done in the platform-wide migration pass)
  - [x] 8.4 New `app:compute-forecasts` command, scheduled every 30 minutes in `routes/console.php`
  - [x] 8.5 `components/dashboard/health-score-card.tsx` on the dashboard; projected completion date surfaced on `goals/show.tsx`
  - [x] 8.6 `tests/Unit/Services/{ForecastServiceTest,HealthScoreServiceTest}.php`

- [x] 9.0 AI insights via OpenAI API
  - [x] 9.1 OpenAI credentials in `config/services.php`/`.env.example`; `app/Services/AI/OpenAiClient.php` (thin Chat Completions wrapper, bound as a singleton in `AppServiceProvider`)
  - [x] 9.2 `app/Services/AI/AiTransactionCategorizer.php` — sends transactions left uncategorized by 4.4's rules-based categorizer to OpenAI, only accepts a suggestion that matches a real category name
  - [x] 9.3 `app/Services/AI/InsightGenerator.php` — spending insight (period-over-period, biggest %-change category) and goal-progress insight (using `ForecastService` data); figures computed deterministically in PHP, OpenAI only phrases the sentence; deduped (one spending insight/user/month, one goal insight/goal/month)
  - [x] 9.4 `app/Services/AI/AnomalyDetector.php` — pure statistics (mean + 2×stddev over trailing 30 days, min 5-transaction sample), no AI call; `InsightGenerator::generateAnomalyInsight` dedupes per transaction
  - [x] 9.5 All three AI-touching operations only run from the new `app:generate-insights` command, scheduled daily in `routes/console.php` — never on a request
  - [x] 9.6 `components/insights/insight-card.tsx` widget on the dashboard (latest 5, polled) + full `insights/index.tsx` feed page + `InsightController` (mark read / dismiss)
  - [x] 9.7 `tests/Unit/Services/{AiTransactionCategorizerTest,AnomalyDetectorTest,InsightGeneratorTest}.php` (OpenAI mocked via `Http::fake`) + `tests/Feature/InsightsTest.php`

- [x] 10.0 Notifications
  - [x] 10.1 Laravel's built-in notifiable + database channel (`notifications` table, done in the platform-wide migration pass) — `User` already used `Notifiable` via Fortify's auth setup
  - [x] 10.2 `app/Notifications/{GoalUpdated,GoalCompleted,LargeSpendingAlert,SyncErrorAlert,RuleExecuted}.php`, dispatched from `AllocationEngine::allocate` (also auto-completes a goal that reaches its target), `RuleEvaluator::execute`, `MonoSyncService::failedLog`, and the `app:generate-insights` command (anomaly → `LargeSpendingAlert`)
  - [x] 10.3 `components/notifications/notification-bell.tsx` in the app header (unread badge, polls `unreadNotificationsCount` via `use-polling`) + full `notifications/index.tsx` feed page + `NotificationController`
  - [x] 10.4 Each notification's `via()` returns `['database', 'mail']` (or `[]` if opted out) — real provider wiring is still pending the choice in PRD §9 open question #3, but the `toMail()` methods are already written against the standard `MailMessage` API, so plugging in a provider later is just config
  - [x] 10.5 `app/Notifications/Concerns/RespectsNotificationPreferences.php` checks `user->notification_preferences[$key] ?? true`, keyed to the same `PreferencesController::NOTIFICATION_TYPES` from 2.7
  - [x] 10.6 `tests/Feature/NotificationDispatchTest.php` (dispatch logic + preference suppression) + `tests/Feature/NotificationsTest.php` (controller, shared unread count)
