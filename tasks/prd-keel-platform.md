# Product Requirements Document (PRD)

## Project Name
Keel — Goal-driven personal finance management platform

**Version:** 2.0 (supersedes `old-prd.md`)
**Status:** Planning
**Owner:** Stilt-Tech Digital Solutions

---

## 1. Introduction/Overview

Keel is a goal-driven personal finance platform that connects to a user's bank accounts (via Mono), automatically imports and categorizes transactions, lets users define savings goals (house, wedding, relocation, emergency fund, etc.), virtually allocates money toward those goals, forecasts when goals will be reached, and surfaces AI-generated insights about spending and progress.

This document replaces `old-prd.md`. The product scope and features are carried over, but the **technical approach is rewritten for deployment on Hostinger shared hosting (Premium/basic plan)**, which does not provide SSH/terminal access, PostgreSQL, Redis, or the ability to run persistent processes (queue workers, WebSocket servers). Every section below reflects what is actually achievable on that hosting tier, with workarounds called out explicitly.

## 2. Goals

1. Let a user connect a bank account and have transactions sync automatically on a recurring schedule (not instantaneously — see §7 hosting constraints).
2. Give users a single dashboard showing balances, income, expenses, savings rate, and goal progress.
3. Let users create savings goals and allocate money to them without moving real funds between accounts.
4. Automate recurring allocations via user-defined rules (e.g., "20% of every salary deposit → House Fund").
5. Forecast goal completion dates and produce a Financial Health Score from the user's transaction history.
6. Surface AI-generated categorization and spending/anomaly insights using the OpenAI API.
7. Ship and operate entirely within the constraints of a Hostinger shared hosting plan (no SSH assumed) without requiring a hosting upgrade for MVP.

## 3. User Stories

- **Bank Connectivity** — As a user, I want to connect my bank account so my transactions are imported automatically without manual entry.
- **Goal Creation** — As a user, I want to create a savings goal with a target amount and deadline so I can track progress toward it.
- **Automated Allocation** — As a user, I want a percentage of every salary deposit to be allocated to my goals automatically, so I save consistently without remembering to do it.
- **Financial Forecasting** — As a user, I want to see a projected date for reaching each goal based on my current savings rate, so I can plan confidently.
- **Dashboard Overview** — As a user, I want one screen that shows my total balance, monthly income/expenses, and savings rate, so I don't have to dig through each account.
- **Insights** — As a user, I want to be told in plain language when my spending pattern changes meaningfully, so I notice problems early.

## 4. Functional Requirements

### 4.1 Authentication & User Management
1. The system must let a user register with email + password (already scaffolded by the Laravel starter kit).
2. The system must let a user log in, log out, reset a forgotten password, and verify their email (already scaffolded).
3. The system must add Two-Factor Authentication (TOTP-based, e.g., via an authenticator app) — not present in the current starter kit and must be added.
4. The system must record an audit log entry for security-sensitive actions (login, password change, 2FA change, bank account connect/disconnect).
5. The system must let a user set basic preferences (currency display, notification preferences).

### 4.2 Bank Integration (Mono)
6. The system must let a user initiate a Mono Connect flow to link a bank account.
7. The system must securely store the Mono account ID and access token (Laravel encrypted casts), never the user's bank credentials.
8. The system must retrieve account information, available balance, transaction history, income data, and institution details from Mono.
9. The system must re-sync each connected account on a recurring schedule via a cron-triggered job (see §7 — true real-time sync is not available on this hosting tier).
10. The system must handle and surface reconnection when Mono reports an account needs re-authorization.
11. The system must log every sync attempt (success/failure, timestamp, item counts) for troubleshooting.
12. The system must receive and verify Mono webhook events (e.g., new transactions, sync completed) on a public HTTPS endpoint.

### 4.3 Account Management
13. The system must list all connected accounts with current balance and institution info.
14. The system must show aggregate metrics: total cash position, number of connected accounts, combined balance.
15. The system must let a user disconnect a bank account, which stops future syncs and retains historical data unless the user explicitly requests deletion.

### 4.4 Transaction Management
16. The system must import transactions from Mono and store them with type (debit, credit, transfer, fee, salary, refund), amount, date, and description.
17. The system must let a user search and filter transactions (by date range, account, category, type, amount range).
18. The system must categorize each transaction into a default category (Food, Transport, Utilities, Housing, Shopping, Entertainment, Healthcare, Education, Investment, Savings) or a user-defined custom category.
19. The system must let a user manually re-categorize a transaction, and remember that override for similar future transactions where feasible.

### 4.5 Savings Goals
20. The system must let a user create a goal with name, description, target amount, current amount, deadline, priority, and status (Active, Completed, Paused, Cancelled).
21. The system must let a user edit, pause, cancel, or mark a goal complete.
22. The system must show progress (current/target, % complete, time remaining) per goal.

### 4.6 Goal Allocation Engine
23. The system must let a user virtually allocate available balance across goals without moving real money between bank accounts.
24. The system must support manual allocation (user sets the amount per goal) and automatic allocation (system applies a rule-based split — see §4.7).
25. The system must prevent total allocated amount from exceeding total available balance and must show the unallocated remainder.

### 4.7 Rules Engine
26. The system must let a user create automation rules of the form "when [transaction type/trigger] occurs → allocate [percentage or fixed amount] to [goal]."
27. The system must evaluate active rules against newly imported transactions during the scheduled sync job and apply matching allocations.
28. The system must log every rule execution (which rule fired, on which transaction, what allocation resulted) so a user can audit automated behavior.

### 4.8 Financial Dashboard
29. The system must show total balance, monthly income, monthly expenses, monthly savings, savings rate, and net cash flow.
30. The system must show a spending breakdown by category, goal progress summary, and income/expense trend charts (Recharts).
31. Dashboard data must refresh via polling on a reasonable interval (e.g., every 30–60 seconds while the tab is open) rather than push/WebSocket updates (see §7).

### 4.9 Financial Forecasting
32. The system must forecast each goal's completion date based on the user's recent average savings rate.
33. The system must forecast near-term future balance based on recurring income/expense patterns.
34. Forecasts must be recalculated as part of the scheduled sync job, not on every page load.

### 4.10 Financial Health Score
35. The system must compute a 0–100 score per user from: savings rate, emergency fund coverage, goal progress, spending stability, and income consistency.
36. The score must be recalculated on the same schedule as forecasts and surfaced on the dashboard.

### 4.11 AI Insights (OpenAI API)
37. The system must use the OpenAI API to suggest a category for transactions the rules-based categorizer leaves uncategorized.
38. The system must generate plain-language spending insights (e.g., "You spent 27% more on transport this month than last month").
39. The system must generate goal-progress insights (e.g., "At your current rate, your relocation goal will be reached in 18 months").
40. The system must flag anomalous transactions (e.g., amount far above the user's historical average) and surface them as alerts.
41. AI calls must run inside the scheduled job (batched), not synchronously on the request/response cycle, to avoid PHP execution timeouts on shared hosting.

### 4.12 Notifications
42. The system must notify users (in-app, and email where appropriate) for: goal updates, large spending alerts, goal completion, sync errors, and rule executions.
43. In-app notifications must be delivered via polling (see §7), not WebSockets.
44. Email notifications must be sent through a transactional email provider (see §7 — Open Question), not assumed to work reliably through Hostinger's shared SMTP alone.

## 5. Non-Goals (Out of Scope for this PRD)

- **PostgreSQL and Redis** — not available on Hostinger shared hosting; MySQL/MariaDB and database-backed cache/queue/session drivers are used instead.
- **Laravel Reverb / WebSockets** — no persistent process support on shared hosting; polling is used instead.
- **Laravel Horizon** — requires Redis; replaced by the standard `failed_jobs` table and manual retry tooling.
- **True real-time bank sync** (seconds-level) — sync runs on the cron schedule described in §7.
- **Native mobile apps.**
- **Shared/family accounts, couple savings goals** — deferred to Version 2 roadmap.
- **Investment/stock/treasury bill tracking** — deferred to Version 3 roadmap.
- **AI financial coach / proactive planning assistant** — deferred to Version 4 roadmap.
- **Cooperative savings, business finance, financial marketplace** — deferred to Version 5 roadmap.
- **Multi-currency support** — single-currency (NGN) for this PRD unless a future PRD specifies otherwise.

## 6. Design Considerations

- Frontend stack is already scaffolded: React + TypeScript + Inertia.js + Tailwind CSS v4, with shadcn-style primitives in `resources/js/components/ui`. New UI must follow the same component conventions already established there.
- Per `.claude/rules/code-separation.md`: pages in `resources/js/pages` must only compose components from `resources/js/components`; business/data logic belongs in `resources/js/hooks` or a `resources/js/lib`/`services` layer, not inlined in page files. The same separation principle applies on the backend — controllers stay thin, business logic lives in dedicated service classes under `app/Services` (or similar), not in controllers or models directly.
- Charts via Recharts, tabular transaction views via TanStack Table — both client-side libraries with no server/hosting implications.
- Per `.claude/rules/slug-rule.md`: any slug/code fields introduced (e.g., for shareable goal links, if added later) must be generated server-side and never exposed as editable form fields.

## 7. Technical Considerations (Hostinger Shared Hosting)

This is the most important section of this PRD — it documents constraints discovered from the hosting environment and the workaround chosen for each.

| Area | Old PRD Assumption | Hostinger Shared Reality | Approach for This PRD |
|---|---|---|---|
| Database | PostgreSQL | Only MySQL/MariaDB provided | Use MySQL/MariaDB |
| Cache/Session/Queue | Redis | No Redis on shared plans | Use `database` driver for cache, sessions, and queue |
| Background workers | Laravel Horizon (persistent process) | No persistent processes allowed | Laravel Scheduler (`schedule:run`) triggered by a Hostinger cron job; queued jobs drained in short batches (`queue:work --stop-when-empty --max-time=...`) on each cron tick |
| Real-time updates | Laravel Reverb (WebSockets) | No persistent WebSocket process | Frontend polling on an interval for dashboard/notifications |
| Server access | Assumed SSH | **Confirmed: Premium/basic plan, no SSH** | All `artisan`/`composer`/`npm` commands run locally; only built artifacts (and migrations run via a one-off web-accessible/queued migration route, or hPanel's Git deploy hook if available) are deployed |
| Deployment | Not specified | No SSH terminal | Build locally (`composer install --no-dev`, `npm run build`), then deploy via Hostinger's hPanel Git feature if available, or upload via File Manager/FTP as fallback. This must be confirmed once hosting is provisioned and documented as a runbook. |
| File storage / `storage:link` | Assumed | `php artisan storage:link` needs a CLI run | Confirm whether hPanel File Manager can create the symlink; if not, serve files in `storage/app/public` through a dedicated Laravel route/controller instead of relying on the symlink |
| Cron granularity | Not specified | Typically ~5 minute minimum interval on Premium/basic plans | All "near real-time" expectations (sync, forecasts, rule execution, AI insights) run on a 5–15 minute cadence, not instantly |
| PHP execution time / memory | Not specified | Shared hosting enforces execution time/memory limits | Batch/chunk large operations (transaction import, AI categorization) inside the scheduled job; never run them inline on a user request |
| Outbound HTTP (Mono, OpenAI) | Assumed fine | Outbound HTTPS calls from PHP are unaffected by shared hosting | No change — both integrations work as plain HTTP API calls |
| Inbound webhooks (Mono) | Assumed fine | Hostinger provides free SSL + normal HTTP routing | No change — a standard Laravel route receives and verifies the webhook signature |
| Transactional email | Not specified | Hostinger shared SMTP has weak deliverability/limits for transactional mail | Use a dedicated transactional email API (e.g., Resend, Postmark, or Mailgun) for verification, password reset, and notification emails — provider choice is an open question (§9) |
| Backups | Not specified | Shared hosting backup tooling is limited | Add a scheduled DB export (via the same cron-triggered scheduler) to a stored/exported location as a baseline backup strategy |

## 8. Success Metrics

**User Success**
- User successfully connects a bank account.
- User creates their first goal.
- User configures their first automated allocation rule.

**Product Success**
- ≥95% of scheduled sync runs complete without error.
- ≥80% of transactions are categorized correctly (rules-based + AI fallback combined).
- Dashboard renders in <3 seconds on a warm cache, given the user's data volume.
- Scheduled sync/forecast/AI jobs reliably complete within the PHP execution time limit of the hosting plan (no timeouts), using chunked batches if needed.

**Financial Success**
- Users consistently contribute toward goals over time.
- Increased average monthly savings rate among active users.

## 9. Open Questions

1. **Hostinger plan confirmation** — exact PHP version available, exact cron minimum interval, and whether hPanel's Git/File Manager can create symlinks, must be confirmed once the hosting account is in hand.
2. **Deployment runbook** — without SSH, the precise deploy mechanism (hPanel Git feature vs. FTP upload of built artifacts) needs to be confirmed and documented before the first deploy.
3. **Transactional email provider** — which service (Resend/Postmark/Mailgun/other) will be used, and who owns the account/API key.
4. **Mono API plan** — sandbox vs. live credentials, and confirmation that Mono's supported institutions match the target users' banks (Nigeria-focused, per ₦ amounts in the original PRD).
5. **OpenAI API budget/ownership** — who owns the API key and what monthly spend ceiling should the AI insights features respect, given they run on a batch schedule rather than per-request.
6. **Domain & SSL** — confirm domain is pointed at the Hostinger account and free SSL is provisioned before webhook URLs (Mono) are registered.
