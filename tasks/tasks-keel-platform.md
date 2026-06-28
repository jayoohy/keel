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
  - [ ] 1.1 Confirm actual Hostinger plan details once provisioned: available PHP version, cron minimum interval, whether SSH/Git deploy is exposed in hPanel, whether File Manager can create symlinks
  - [ ] 1.2 Update `config/database.php` and `.env.example` to use MySQL as the default connection; remove any Postgres-specific config
  - [ ] 1.3 Switch `config/cache.php`, `config/session.php`, `config/queue.php` to the `database` driver; run `php artisan queue:table`, `php artisan queue:failed-table`, session table migration, and cache table migration locally, then commit the generated migrations
  - [ ] 1.4 Add a scheduler entry in `routes/console.php` with a trivial heartbeat command first, to validate the single Hostinger cron job (`* * * * * php artisan schedule:run`) actually fires at the confirmed interval
  - [ ] 1.5 Add a `queue:work --stop-when-empty --max-time=...` call into the scheduled cycle so queued jobs drain on every cron tick (since no persistent worker is allowed)
  - [ ] 1.6 Resolve the `storage:link` problem: attempt symlink via File Manager once hosting is live; if unavailable, implement a fallback Laravel route/controller that streams files from `storage/app/public` and use it instead of the public symlink everywhere file URLs are generated
  - [ ] 1.7 Choose a transactional email provider (Resend/Postmark/Mailgun), add credentials to `config/mail.php`/`.env.example`, and send a test email end-to-end
  - [ ] 1.8 Write `docs/deployment.md`: local build steps (`composer install --no-dev`, `npm run build`), the chosen deploy mechanism (hPanel Git feature vs. FTP/File Manager upload of built artifacts), and a rollback note
  - [ ] 1.9 Confirm domain + free SSL are active on the Hostinger account before any webhook URL (Mono) is registered

- [ ] 2.0 Authentication & user management
  - [ ] 2.1 Verify existing starter-kit auth flows (register, login, logout, forgot/reset password, email verification) work end-to-end against MySQL locally
  - [ ] 2.2 Add migration for 2FA columns on `users` (secret, recovery codes, enabled flag)
  - [ ] 2.3 Implement TOTP enrollment/verification service and `Auth/TwoFactorController` (enable, confirm via code, disable, regenerate recovery codes)
  - [ ] 2.4 Build `resources/js/pages/settings/two-factor.tsx` (QR code display, code confirmation, recovery codes view) following the existing settings pages' layout conventions
  - [ ] 2.5 Require 2FA challenge at login when enabled for a user
  - [ ] 2.6 Add `audit_logs` migration + `AuditLog` model; record entries for login, password change, 2FA enable/disable, bank connect/disconnect
  - [ ] 2.7 Add user preference fields (currency display, notification preferences) via migration + settings UI section
  - [ ] 2.8 Write Pest feature tests for 2FA enrollment/challenge and audit log creation

- [ ] 3.0 Mono bank integration
  - [ ] 3.1 Add Mono API credentials to `config/services.php`/`.env.example`
  - [ ] 3.2 Build `app/Services/Mono/MonoClient.php` wrapping Mono Connect, account info, transactions, and institution endpoints
  - [ ] 3.3 Add migrations + models for `bank_connections` (encrypted Mono account id/access token, status) and `accounts`
  - [ ] 3.4 Build the Connect flow: frontend page to launch Mono Connect widget + `BankConnectionController` endpoints to receive and store the resulting account id/token
  - [ ] 3.5 Build `MonoWebhookController`: route + signature verification + queued job to process incoming webhook payloads
  - [ ] 3.6 Add `sync_logs` migration + model; build `MonoSyncService` to pull latest account/transaction data, called from the scheduler set up in 1.4–1.5
  - [ ] 3.7 Handle Mono "reauthorization required" status: detect it during sync, surface a banner/notification, and provide a reconnect action
  - [ ] 3.8 Add an account disconnect action that stops future syncs while retaining historical data
  - [ ] 3.9 Write Pest tests for `MonoClient` (HTTP mocked), webhook signature verification, and `MonoSyncService` idempotency

- [ ] 4.0 Account & transaction management
  - [ ] 4.1 Build `resources/js/pages/accounts/index.tsx` + `AccountController` showing connected accounts and aggregate metrics (total cash position, account count, combined balance)
  - [ ] 4.2 Add `categories` migration/model + `CategorySeeder` for default categories, with support for user-defined custom categories
  - [ ] 4.3 Add `transactions` migration/model (type enum, amount, date, description, category_id, account_id, Mono transaction id unique constraint for idempotent import)
  - [ ] 4.4 Implement rules-based default categorization during import in `MonoSyncService`/a dedicated categorizer class
  - [ ] 4.5 Build `resources/js/pages/transactions/index.tsx` using TanStack Table with search + filters (date range, account, category, type, amount range) and a matching `TransactionController` query endpoint
  - [ ] 4.6 Implement manual re-categorization: endpoint + UI control, plus a `category_overrides` table keyed by merchant/description pattern so future imports reuse the user's correction
  - [ ] 4.7 Write Pest tests for transaction import idempotency, filter queries, and categorization override persistence

- [ ] 5.0 Savings goals & goal allocation engine
  - [ ] 5.1 Add `goals` migration/model (name, description, target_amount, current_amount, deadline, priority, status enum)
  - [ ] 5.2 Build `GoalController` (CRUD) + `resources/js/pages/goals/{index,create,edit,show}.tsx`
  - [ ] 5.3 Add `allocations` migration/model (goal_id, amount, type: manual/automatic/recurring, basis: percentage/fixed, source)
  - [ ] 5.4 Build `AllocationEngine` service: compute total available balance across connected accounts, validate that total allocated never exceeds it, expose the unallocated remainder
  - [ ] 5.5 Build manual allocation UI (per-goal amount inputs with live guardrail validation against available balance)
  - [ ] 5.6 Write Pest/unit tests for `AllocationEngine` guardrails and goal progress percentage calculation

- [ ] 6.0 Rules engine
  - [ ] 6.1 Add `rules` migration/model (trigger type/condition, action basis: percentage/fixed, target goal, active flag)
  - [ ] 6.2 Build `RuleController` (CRUD) + `resources/js/pages/rules/{index,create,edit}.tsx`
  - [ ] 6.3 Build `RuleEvaluator` service that runs during the scheduled sync (hooked into 3.6) and matches active rules against newly imported transactions, applying allocations via `AllocationEngine`
  - [ ] 6.4 Add `rule_executions` migration/model and log every rule firing (which rule, which transaction, resulting allocation)
  - [ ] 6.5 Add a rule execution history view so users can audit automated behavior
  - [ ] 6.6 Write unit tests for `RuleEvaluator` matching logic and execution logging

- [ ] 7.0 Financial dashboard
  - [ ] 7.1 Build a dashboard aggregation service computing total balance, monthly income/expenses/savings, savings rate, net cash flow (cached via the `database` cache driver to keep polling cheap)
  - [ ] 7.2 Replace the placeholder `resources/js/pages/dashboard.tsx` with the real dashboard composing chart components (spending breakdown, goal progress, income/expense trends) via Recharts, pulling data from `DashboardController`
  - [ ] 7.3 Build `resources/js/hooks/use-polling.ts` and wire the dashboard to refetch on an interval (e.g., 30–60s) instead of relying on WebSockets
  - [ ] 7.4 Write tests for the dashboard aggregation calculations

- [ ] 8.0 Forecasting & financial health score
  - [ ] 8.1 Build `ForecastService`: goal completion date projection from recent average savings rate, near-term balance forecast from recurring income/expense patterns
  - [ ] 8.2 Build `HealthScoreService`: 0–100 score from savings rate, emergency fund coverage, goal progress, spending stability, income consistency
  - [ ] 8.3 Add migrations to store forecast/health-score snapshots over time (for trend display)
  - [ ] 8.4 Hook both services into the scheduled job cycle (alongside sync/rules) so they recompute on schedule, not per page load
  - [ ] 8.5 Surface forecast and health score on the dashboard and goal detail pages
  - [ ] 8.6 Write unit tests for forecast math and health score calculation

- [ ] 9.0 AI insights via OpenAI API
  - [ ] 9.1 Add OpenAI credentials to `config/services.php`/`.env.example`; build `app/Services/AI/OpenAiClient.php`
  - [ ] 9.2 Build a batched job that sends transactions left uncategorized by the rules-based categorizer (4.4) to OpenAI and stores the suggested category
  - [ ] 9.3 Build `InsightGenerator` to produce plain-language spending insights (period-over-period comparison) and goal-progress insights (using forecast data from 8.1), stored in an `insights` table
  - [ ] 9.4 Implement anomaly detection (statistical check against historical average/std dev) and flag qualifying transactions as alerts
  - [ ] 9.5 Ensure all AI calls run inside the scheduled batch job, never synchronously on a user request, to avoid PHP execution timeouts
  - [ ] 9.6 Surface insights in a dashboard widget / insights feed UI
  - [ ] 9.7 Write tests for insight generation (OpenAI calls mocked) and anomaly threshold logic

- [ ] 10.0 Notifications
  - [ ] 10.1 Add a notifications data store (Laravel's built-in notifiable + database channel, or a custom `notifications` table if more control is needed)
  - [ ] 10.2 Build per-type Laravel `Notification` classes: goal updates, large spending alerts, goal completion, sync errors, rule execution alerts
  - [ ] 10.3 Build the in-app notification feed UI reusing the polling hook from 7.3
  - [ ] 10.4 Wire email channel delivery through the transactional provider configured in 1.7 for notification types that warrant email
  - [ ] 10.5 Respect the user notification preferences captured in 2.7 (only send what the user opted into)
  - [ ] 10.6 Write tests for notification dispatch logic (mocked mail) and preference filtering
