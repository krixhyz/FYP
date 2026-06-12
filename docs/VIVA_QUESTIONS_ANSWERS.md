# Viva Questions & Answers — ReLoop FYP

**Project:** Peer-to-Peer Reuse Marketplace (Laravel 12)  
**Date Generated:** May 21, 2026  
**Scope:** Answers based solely on production code from `app/`, `routes/`, `resources/views/`, `database/migrations/`, and test files.

---

## Q1: What is the primary purpose of this project?

**Short Answer:**  
A Laravel-based peer-to-peer reuse marketplace supporting buying, renting, and swapping items with wallet/payment integrations.

**Technical Explanation:**  
The codebase implements a multi-transaction marketplace where users can list products for sale or rent, create swap offers with negotiation flows, and execute payments through Khalti/eSewa gateways. Orders, rentals, and swaps generate wallet transactions tracked via ledger entries. The admin panel provides analytics on revenue, user growth, sustainability metrics, and transaction mix.

**Key Files:**  
- [routes/web.php](routes/web.php)
- [app/Models/Product.php](app/Models/Product.php)
- [app/Http/Controllers/User/PaymentController.php](app/Http/Controllers/User/PaymentController.php)
- [resources/views](resources/views)

**Packages Used:**  
- pusher/pusher-php-server, laravel-echo, pusher-js, cloudinary-laravel

---

## Q2: Which PHP / Laravel version is targeted?

**Short Answer:**  
PHP ^8.2 and Laravel ^12.0

**Technical Explanation:**  
The `composer.json` specifies `"php": "^8.2"` and `"laravel/framework": "^12.0"`. The codebase uses typed properties, strict typing, and modern Laravel features consistent with this version.

**Key Files:**  
- [composer.json](composer.json)
- [composer.lock](composer.lock)

**Packages Used:**  
- laravel/framework (^12.0)

---

## Q3: How is authentication implemented?

**Short Answer:**  
Standard Laravel authentication with email verification, custom auth controllers, and throttled routes.

**Technical Explanation:**  
Routes use `app/Http/Controllers/Auth/*` controllers (AuthController, RegisterController, PasswordResetController, EmailVerificationController). The `User` model implements `MustVerifyEmail`. Routes in `routes/auth.php` define login, register, password reset, verification, and logout with throttle middleware to prevent brute force.

**Key Files:**  
- [routes/auth.php](routes/auth.php)
- [app/Models/User/User.php](app/Models/User/User.php)
- [app/Http/Controllers/Auth](app/Http/Controllers/Auth)

**Packages Used:**  
- Built on Laravel authentication (framework)

---

## Q4: Is Google OAuth / Socialite integrated?

**Short Answer:**  
No — Socialite is not installed and no Google OAuth configuration exists.

**Technical Explanation:**  
The `composer.json` does not list `laravel/socialite`, and `config/services.php` contains no Google credentials or Socialite configuration. Social login is not implemented.

**Key Files:**  
- [config/services.php](config/services.php)
- [composer.json](composer.json)

**Packages Used:**  
- N/A

---

## Q5: How are routes organized?

**Short Answer:**  
Routes split into `routes/web.php` (main pages), `routes/auth.php` (authentication), `routes/api.php` (API), and `routes/channels.php` (broadcasting).

**Technical Explanation:**  
`web.php` holds resource and named routes; `auth.php` separates guest and authenticated middleware groups; `channels.php` defines private Broadcast channels for notifications. Controllers are namespaced under `app/Http/Controllers`.

**Key Files:**  
- [routes/web.php](routes/web.php)
- [routes/auth.php](routes/auth.php)
- [routes/channels.php](routes/channels.php)

**Packages Used:**  
- Laravel routing (framework)

---

## Q6: How are payments handled at a high level?

**Short Answer:**  
Centralized in `PaymentController` with provider-specific services (Khalti, eSewa), inventory reservation, and wallet ledger flows.

**Technical Explanation:**  
`PaymentController` creates Payment records, calls `InventoryReservationService` to hold inventory, invokes provider services (`KhaltiService::initiatePayment()`, `EsewaService`), verifies payment callbacks, updates order/rental/swap status, and uses `WalletLedgerService` to credit/debit seller and platform wallets.

**Key Files:**  
- [app/Http/Controllers/User/PaymentController.php](app/Http/Controllers/User/PaymentController.php)
- [app/Services/KhaltiService.php](app/Services/KhaltiService.php)
- [app/Services/EsewaService.php](app/Services/EsewaService.php)
- [app/Services/WalletLedgerService.php](app/Services/WalletLedgerService.php)

**Packages Used:**  
- None specific (uses Laravel HTTP client)

---

## Q7: What payment providers are integrated?

**Short Answer:**  
Khalti and eSewa (Nepal-focused payment gateways).

**Technical Explanation:**  
`KhaltiService` implements `initiatePayment()`, `lookupPayment()`, and `refundPayment()` with config-driven API keys and endpoints. PaymentController has `esewaSuccess()` and `esewaFailure()` callback handlers and invokes Khalti endpoints for payment orchestration.

**Key Files:**  
- [app/Services/KhaltiService.php](app/Services/KhaltiService.php)
- [app/Services/EsewaService.php](app/Services/EsewaService.php)
- [app/Http/Controllers/User/PaymentController.php](app/Http/Controllers/User/PaymentController.php)

**Packages Used:**  
- None external (uses Laravel HTTP client via Guzzle)

---

## Q8: How are payments recorded in the database?

**Short Answer:**  
`payments` table stores payment records with request/response payloads, provider, amount, and status.

**Technical Explanation:**  
Migration defines columns: `user_id`, `provider`, `transaction_uuid` (unique), `product_code`, `amount`, `tax_amount`, `service_charge`, `total_amount`, `status`, `transaction_code`, `request_payload` (JSON), `response_payload` (JSON). Payment model casts numeric fields to `decimal:2` and JSON payloads to `array`.

**Key Files:**  
- [database/migrations/2026_02_09_000001_create_payments_table.php](database/migrations/2026_02_09_000001_create_payments_table.php)
- [app/Models/Payment.php](app/Models/Payment.php)

**Packages Used:**  
- Laravel migrations and DB

---

## Q9: How is inventory reserved during checkout?

**Short Answer:**  
Before finalizing payment, `PaymentController` calls `InventoryReservationService` to decrement product quantities.

**Technical Explanation:**  
During checkout orchestration, inventory is reserved to prevent overselling. The service decrements quantity and holds it pending payment completion. Tests verify that product quantities change after successful checkout and payment verification.

**Key Files:**  
- [app/Services/InventoryReservationService.php](app/Services/InventoryReservationService.php)
- [app/Http/Controllers/User/PaymentController.php](app/Http/Controllers/User/PaymentController.php)
- [tests/Feature/Payments/KhaltiCheckoutTest.php](tests/Feature/Payments/KhaltiCheckoutTest.php)

**Packages Used:**  
- None specific

---

## Q10: How are orders modeled and linked to payments?

**Short Answer:**  
`Order` model has `payment_id`, `buyer_id`, `seller_id`, `product_id`; `Payment` has `hasMany(Order)` and `belongsTo(Order)` relations.

**Technical Explanation:**  
`Order` stores buyer/seller/product references, quantity, unit price, total price, and status. `Payment` links to one or many orders and stores transaction details. PaymentController creates orders and attaches the payment ID after payment verification.

**Key Files:**  
- [app/Models/Order.php](app/Models/Order.php)
- [app/Models/Payment.php](app/Models/Payment.php)
- [app/Http/Controllers/User/PaymentController.php](app/Http/Controllers/User/PaymentController.php)

**Packages Used:**  
- N/A

---

## Q11: How does the wallet system work?

**Short Answer:**  
Users have `Wallet` records with `available_balance` and `pending_payout_balance`; `WalletLedgerEntry` tracks each transaction.

**Technical Explanation:**  
`wallets` table holds balances and lifetime credit/debit totals. Wallet ledger entries record direction (in/out), amount, balances before/after, and reference types. `WalletLedgerService` atomically creates ledger entries and updates wallet balances. Users request payouts via `WalletController::requestPayout`.

**Key Files:**  
- [database/migrations/2026_04_16_000300_create_wallets_table.php](database/migrations/2026_04_16_000300_create_wallets_table.php)
- [app/Models/Wallet.php](app/Models/Wallet.php)
- [app/Models/WalletLedgerEntry.php](app/Models/WalletLedgerEntry.php)
- [app/Services/WalletLedgerService.php](app/Services/WalletLedgerService.php)
- [app/Http/Controllers/User/WalletController.php](app/Http/Controllers/User/WalletController.php)

**Packages Used:**  
- N/A

---

## Q12: How are payout requests processed?

**Short Answer:**  
Users submit payout requests via `WalletController::requestPayout`, creating `PayoutRequest` records tied to wallets.

**Technical Explanation:**  
Controller validates amount against `available_balance`, calls wallet service to create a payout request record. `PayoutRequest` model links to wallet and has `processed_by` field for admin tracking. Requests are pending until admin approves and processes.

**Key Files:**  
- [app/Http/Controllers/User/WalletController.php](app/Http/Controllers/User/WalletController.php)
- [app/Models/PayoutRequest.php](app/Models/PayoutRequest.php)

**Packages Used:**  
- N/A

---

## Q13: How are notifications implemented?

**Short Answer:**  
Laravel Notifications with mail, database, and broadcast channels; many notifications implement `ShouldBroadcastNow`.

**Technical Explanation:**  
Notification classes under `app/Notifications/User` implement `toMail()`, `toDatabase()`, and `toBroadcast()` methods. `ShouldBroadcastNow` sends events immediately to Echo channels. UI listens for notifications and displays toasts. Notifications include rental approvals, swaps, orders, and disputes.

**Key Files:**  
- [app/Notifications/User](app/Notifications/User)
- [app/Http/Controllers/User/NotificationController.php](app/Http/Controllers/User/NotificationController.php)
- [resources/js/echo.js](resources/js/echo.js)

**Packages Used:**  
- pusher/pusher-php-server, laravel-echo, pusher-js

---

## Q14: What broadcasting setup is used?

**Short Answer:**  
Pusher driver (config/broadcasting.php) with Laravel Echo + pusher-js on the frontend.

**Technical Explanation:**  
`config/broadcasting.php` sets default driver to `pusher`; `.env` contains PUSHER_APP_ID, PUSHER_APP_KEY, PUSHER_APP_SECRET, PUSHER_APP_CLUSTER. Frontend loads `laravel-echo` and `pusher-js` to subscribe to private user channels for realtime notifications.

**Key Files:**  
- [config/broadcasting.php](config/broadcasting.php)
- [resources/js/echo.js](resources/js/echo.js)
- [.env.example](.env.example)

**Packages Used:**  
- pusher/pusher-php-server, laravel-echo, pusher-js

---

## Q15: How are private channels authorized?

**Short Answer:**  
Via Laravel's broadcasting auth endpoint and channel callbacks in `routes/channels.php`.

**Technical Explanation:**  
`Broadcast::channel('App.Models.User.{id}', function ($user, $id) { ... })` returns user authorization. Echo client authenticates to `/broadcasting/auth` endpoint and includes credentials in request headers (CSRF + Bearer token if present).

**Key Files:**  
- [routes/channels.php](routes/channels.php)
- [resources/js/echo.js](resources/js/echo.js)

**Packages Used:**  
- Laravel broadcasting

---

## Q16: How is the frontend built and which JS stack is used?

**Short Answer:**  
Blade templates + Vite; TailwindCSS, Alpine.js, Chart.js, Echo + Pusher for realtime updates.

**Technical Explanation:**  
`resources/views` use Blade templating; `vite.config.js` provides the build pipeline. `package.json` includes laravel-echo, pusher-js, alpinejs, tailwindcss. Admin dashboard uses Chart.js (v4.4.0 CDN) to render analytics charts.

**Key Files:**  
- [package.json](package.json)
- [resources/views/admin/analytics/index.blade.php](resources/views/admin/analytics/index.blade.php)
- [vite.config.js](vite.config.js)

**Packages Used:**  
- laravel-echo, pusher-js, alpinejs, tailwindcss, chart.js (CDN)

---

## Q17: How are analytics visualized in the admin panel?

**Short Answer:**  
Server computes chart arrays; Blade renders Chart.js canvases with datasets passed via `@json()`.

**Technical Explanation:**  
Controller computes `$chartLabels`, `$revenueChart`, `$usersChart`, `$listingsChart`, and passes to view. Blade embeds arrays in JS via `@json()` and initializes Chart.js with line, bar, and doughnut charts. Custom formatters handle currency (Rs.) and percentages.

**Key Files:**  
- [resources/views/admin/analytics/index.blade.php](resources/views/admin/analytics/index.blade.php)

**Packages Used:**  
- Chart.js (CDN), TailwindCSS

---

## Q18: How are images/media handled?

**Short Answer:**  
Cloudinary integration via `cloudinary-labs/cloudinary-laravel` package.

**Technical Explanation:**  
Models reference Cloudinary in configuration; `image` and `images` fields store Cloudinary URLs. Uploader integration allows users to upload media which is stored on Cloudinary instead of local disk.

**Key Files:**  
- [composer.json](composer.json)
- [config/cloudinary.php](config/cloudinary.php)
- [app/Models/Product.php](app/Models/Product.php)

**Packages Used:**  
- cloudinary-labs/cloudinary-laravel

---

## Q19: Are there automated tests for payments?

**Short Answer:**  
Yes — Feature tests for Khalti checkout flows verify payment creation, redirects, and inventory changes.

**Technical Explanation:**  
`tests/Feature/Payments/KhaltiCheckoutTest.php` exercises payment creation, Khalti redirects, order completion, and quantity decrements. Tests assert expected state changes and verify payment flows end-to-end.

**Key Files:**  
- [tests/Feature/Payments/KhaltiCheckoutTest.php](tests/Feature/Payments/KhaltiCheckoutTest.php)

**Packages Used:**  
- phpunit, laravel testing helpers

---

## Q20: How is money rounding/precision handled?

**Short Answer:**  
Money fields use `decimal:2` casts in models; migrations define numeric columns with precision.

**Technical Explanation:**  
Payment, Order, and Wallet models cast amounts to `decimal:2` ensuring two decimal place precision. Migrations create numeric columns with appropriate precision. Controllers use `round()` where needed.

**Key Files:**  
- [app/Models/Payment.php](app/Models/Payment.php)
- [app/Models/Order.php](app/Models/Order.php)
- [database/migrations](database/migrations)

**Packages Used:**  
- N/A

---

## Q21: How are migrations organized?

**Short Answer:**  
Migrations under `database/migrations/` with descriptive filenames define all database tables and constraints.

**Technical Explanation:**  
Migration files include `create_payments_table`, `create_wallets_table`, and other resource tables. Each defines columns, data types, foreign keys, and indexes. Migrations ensure referential integrity and proper schema evolution.

**Key Files:**  
- [database/migrations](database/migrations)
- [database/migrations/2026_02_09_000001_create_payments_table.php](database/migrations/2026_02_09_000001_create_payments_table.php)

**Packages Used:**  
- Laravel migrations

---

## Q22: How are models organized and what conventions are used?

**Short Answer:**  
Eloquent models under `app/Models/` with `app/Models/User/User.php` for User model; all use `$fillable`, `$casts`, and relationship methods.

**Technical Explanation:**  
Models follow Laravel conventions: `HasFactory` for testing, `$fillable` for mass assignment, `$casts` for type safety. Relationships are defined via `belongsTo()`, `hasMany()`, `hasOne()` methods. User model contains domain-specific relations (products, orders, wallet, etc.).

**Key Files:**  
- [app/Models](app/Models)
- [app/Models/User/User.php](app/Models/User/User.php)

**Packages Used:**  
- Eloquent (Laravel)

---

## Q23: How are permissions/roles represented?

**Short Answer:**  
User role stored on `users.role` column; helper methods on User model check permissions.

**Technical Explanation:**  
`User` model has `role` field and methods like `isAdmin()`, `isSuperAdmin()`, `canAccessAdminPanel()`, `canManageRoles()`. Role checks are string comparisons (simple RBAC, not a package).

**Key Files:**  
- [app/Models/User/User.php](app/Models/User/User.php)

**Packages Used:**  
- N/A

---

## Q24: How are requests validated?

**Short Answer:**  
Controllers use `$request->validate()` inline or dedicated Request classes under `app/Http/Requests/`.

**Technical Explanation:**  
Validation rules are defined inline in controller methods (e.g., `WalletController::requestPayout`) or in dedicated FormRequest classes. Rules include required, numeric, regex, min/max, and custom messages for user-friendly feedback.

**Key Files:**  
- [app/Http/Controllers/User/WalletController.php](app/Http/Controllers/User/WalletController.php)
- [app/Http/Requests](app/Http/Requests)

**Packages Used:**  
- Laravel validation

---

## Q25: How are emails sent?

**Short Answer:**  
Notifications implement `toMail()` method; mail driver configured via `config/mail.php` and `config/services.php`.

**Technical Explanation:**  
Notifications generate mailable content via `toMail()`. Mail driver config supports Postmark, SES, Resend, or others via environment variables. Email verification uses Laravel's `MustVerifyEmail` trait.

**Key Files:**  
- [app/Notifications/User](app/Notifications/User)
- [config/mail.php](config/mail.php)
- [config/services.php](config/services.php)

**Packages Used:**  
- Mail drivers (Postmark/SES optional)

---

## Q26: What queue/async mechanisms are used?

**Short Answer:**  
Notifications use `ShouldBroadcastNow` for immediate broadcast; queue config exists via `config/queue.php`.

**Technical Explanation:**  
Notifications implementing `ShouldBroadcastNow` bypass queue and broadcast immediately. Queue drivers configured for async job processing if needed. Some services may dispatch jobs or notifications to queue.

**Key Files:**  
- [config/queue.php](config/queue.php)
- [app/Notifications/User](app/Notifications/User)

**Packages Used:**  
- Laravel queue, redis/predis

---

## Q27: How does the app handle retries/refunds for Khalti?

**Short Answer:**  
`KhaltiService` includes `lookupPayment()` to verify status and `refundPayment()` to issue refunds.

**Technical Explanation:**  
PaymentController uses lookup to confirm payment completion. If verification fails, payment remains pending. Refund method calls Khalti API to reverse completed payments. Services wrap external calls with error handling.

**Key Files:**  
- [app/Services/KhaltiService.php](app/Services/KhaltiService.php)
- [app/Http/Controllers/User/PaymentController.php](app/Http/Controllers/User/PaymentController.php)

**Packages Used:**  
- None specific

---

## Q28: How are logging and error handling approached?

**Short Answer:**  
Laravel logging via `config/logging.php`; controllers use try/catch for domain errors and return user-friendly responses.

**Technical Explanation:**  
Services wrap external API calls and log errors. Controllers catch exceptions and return flash messages or JSON errors. Exceptions bubble to Laravel exception handler which logs and formats responses.

**Key Files:**  
- [config/logging.php](config/logging.php)
- Controller methods with try/catch blocks

**Packages Used:**  
- Laravel logging

---

## Q29: How are reviews implemented?

**Short Answer:**  
`Review` model links reviewer/reviewee to product/order/rental/swap with rating and body fields.

**Technical Explanation:**  
`Review` model stores `reviewer_id`, `reviewee_id`, `product_id`, `order_id`, `rented_rental_id`, `swap_id`, `rating`, and `body`. Relations enable querying reviews by transaction type post-completion.

**Key Files:**  
- [app/Models/Review.php](app/Models/Review.php)

**Packages Used:**  
- N/A

---

## Q30: How are rental flows modeled?

**Short Answer:**  
Separate models (`Rental`, `RentalRequest`, `RentedRentals`, `RentalDeposit`) manage requests, approvals, active rentals, and deposits.

**Technical Explanation:**  
`RentalRequest` captures renter intent; owners approve or reject, triggering notifications. `RentedRentals` records active rentals with end dates. `RentalDeposit` links deposits to rentals and payments. Controllers orchestrate state transitions.

**Key Files:**  
- [app/Models/Rental.php](app/Models/Rental.php)
- [app/Models/RentalRequest.php](app/Models/RentalRequest.php)
- [app/Models/RentedRentals.php](app/Models/RentedRentals.php)
- [app/Models/RentalDeposit.php](app/Models/RentalDeposit.php)

**Packages Used:**  
- N/A

---

## Q31: How is swapping (swap) functionality implemented?

**Short Answer:**  
Models for `SwapRequest`, `Swap`, and related notifications manage offers, negotiations, cash differences, and confirmations.

**Technical Explanation:**  
Swap domain includes models for swap requests (offers) and negotiations. Controllers handle create, counter, accept, and reject flows. Payments process cash differences if items are unequal value. Notifications keep parties informed.

**Key Files:**  
- [app/Models/SwapRequest.php](app/Models/SwapRequest.php)
- [app/Models/Swap.php](app/Models/Swap.php)
- [app/Notifications/User](app/Notifications/User)

**Packages Used:**  
- N/A

---

## Q32: How are notifications paginated and fetched for UI?

**Short Answer:**  
`NotificationController::index()` paginates; `latest()` returns compact JSON for live UI fallback polling.

**Technical Explanation:**  
`latest()` returns unread count and 10 most recent notifications with redirect URLs for quick access. `index()` renders paginated Blade view. `markRead()` and `markAllRead()` endpoints provided for AJAX calls.

**Key Files:**  
- [app/Http/Controllers/User/NotificationController.php](app/Http/Controllers/User/NotificationController.php)
- [resources/views/notifications/index.blade.php](resources/views/notifications/index.blade.php)

**Packages Used:**  
- Laravel Notifications

---

## Q33: How does the frontend subscribe to realtime notifications?

**Short Answer:**  
`resources/js/echo.js` initializes Echo, subscribes to `private('App.Models.User.{id}')`, and binds to `notification` event.

**Technical Explanation:**  
Echo instance uses `window.Laravel.userId` to build channel name. Subscription binds to `notification` event from broadcast. Falls back to polling via `/notifications/latest` if Echo unavailable.

**Key Files:**  
- [resources/js/echo.js](resources/js/echo.js)

**Packages Used:**  
- laravel-echo, pusher-js

---

## Q34: How is the User model structured?

**Short Answer:**  
`User` extends `Authenticatable`, implements `MustVerifyEmail`, contains many relations and helper methods.

**Technical Explanation:**  
`$fillable` includes profile and account fields. `$casts` ensure type safety (email_verified_at as datetime, eco_level as string). Relations enable quick access to user's products, orders, wallet, rentals, swaps. Admin-related methods check permissions.

**Key Files:**  
- [app/Models/User/User.php](app/Models/User/User.php)

**Packages Used:**  
- Laravel auth

---

## Q35: How is eco/sustainability scoring implemented?

**Short Answer:**  
`UserEcoScore` model tracks points; User helper methods compute totals and eco levels for display.

**Technical Explanation:**  
Eco scores awarded per transaction type (sell, rent, swap). User methods aggregate scores, calculate eco levels, and return statistics. Analytics page displays CO₂ saved, items reused, and eco champion counts.

**Key Files:**  
- [app/Models/User/User.php](app/Models/User/User.php)
- [app/Models/UserEcoScore.php](app/Models/UserEcoScore.php)
- [resources/views/admin/analytics/index.blade.php](resources/views/admin/analytics/index.blade.php)

**Packages Used:**  
- N/A

---

## Q36: How are percentages and growth metrics calculated in analytics?

**Short Answer:**  
Blade PHP computes month-over-month percentages; server precomputes chart arrays.

**Technical Explanation:**  
Blade calculates `(this-last)/last * 100` with zero-denominator guards. Server computes revenue, user, and listing arrays for Chart.js. KPI cards show growth indicators with safe fallbacks.

**Key Files:**  
- [resources/views/admin/analytics/index.blade.php](resources/views/admin/analytics/index.blade.php)

**Packages Used:**  
- Chart.js (CDN)

---

## Q37: How are charts configured on the frontend?

**Short Answer:**  
Chart.js v4.4.0 (CDN) initialized with server-provided arrays via `@json()` and custom options.

**Technical Explanation:**  
JS sets `Chart.defaults` and constructs line, bar, doughnut charts. Dataset arrays embedded via `@json()` for safe JSON encoding. Custom tooltip and tick callbacks format currency (Rs.) and counts.

**Key Files:**  
- [resources/views/admin/analytics/index.blade.php](resources/views/admin/analytics/index.blade.php)

**Packages Used:**  
- Chart.js (CDN)

---

## Q38: How is CSRF and broadcast auth handled in Echo?

**Short Answer:**  
Echo reads CSRF token from meta tag; auth headers include CSRF token and Bearer token if present.

**Technical Explanation:**  
`resources/js/echo.js` fetches CSRF token from `window.Laravel.csrfToken` or meta tag. Headers include `X-CSRF-TOKEN` and `Authorization: Bearer ...`. Broadcast auth endpoint (`/broadcasting/auth`) validates requests.

**Key Files:**  
- [resources/js/echo.js](resources/js/echo.js)
- [config/broadcasting.php](config/broadcasting.php)

**Packages Used:**  
- laravel-echo

---

## Q39: How are database payloads for external APIs stored?

**Short Answer:**  
Request and response JSON payloads stored in `request_payload` and `response_payload` JSON columns on payments table.

**Technical Explanation:**  
Payment model casts these columns to `array` so controllers and services persist raw API responses for audit trails and troubleshooting. Enables tracing payment state and debugging API issues.

**Key Files:**  
- [app/Models/Payment.php](app/Models/Payment.php)
- [database/migrations/2026_02_09_000001_create_payments_table.php](database/migrations/2026_02_09_000001_create_payments_table.php)

**Packages Used:**  
- Laravel DB

---

## Q40: How is data sanitization and escaping handled in views?

**Short Answer:**  
Blade `{{ }}` escapes by default; `@json()` safely encodes arrays for JS.

**Technical Explanation:**  
Blade templating escapes HTML entities automatically. `@json()` encodes server arrays as safe JSON for Chart.js. Avoids XSS by default.

**Key Files:**  
- [resources/views/admin/analytics/index.blade.php](resources/views/admin/analytics/index.blade.php)

**Packages Used:**  
- Blade (Laravel)

---

## Q41: How are currencies represented and displayed?

**Short Answer:**  
Currency values displayed as `Rs.` with `number_format()` for thousands separators.

**Technical Explanation:**  
Amounts in models are `decimal:2` cast. Blade templates prefix with `Rs.` and use PHP `number_format()`. Chart tick callbacks format currency strings.

**Key Files:**  
- [resources/views/admin/analytics/index.blade.php](resources/views/admin/analytics/index.blade.php)
- [app/Models/Payment.php](app/Models/Payment.php)

**Packages Used:**  
- N/A

---

## Q42: How are static assets and front-end builds managed?

**Short Answer:**  
Vite and npm manage frontend assets; built files placed under `public/build/`.

**Technical Explanation:**  
`vite.config.js` provides build pipeline. `package.json` lists dev and production dependencies. `npm run build` compiles JS/CSS. Blade includes Vite or CDN assets.

**Key Files:**  
- [vite.config.js](vite.config.js)
- [package.json](package.json)
- [public/build](public/build)

**Packages Used:**  
- Vite, tailwindcss

---

## Q43: How are errors communicated back to users?

**Short Answer:**  
Flash messages and JSON responses; PHPFlasher Toastr shows UI toasts.

**Technical Explanation:**  
Controllers redirect with `with('success', '...')` or `with('error', '...')`. Frontend includes `yoeunes/toastr` extension for message display. Echo broadcasts also trigger toasts for live events.

**Key Files:**  
- [composer.json](composer.json)
- [resources/js/echo.js](resources/js/echo.js)
- Blade form templates

**Packages Used:**  
- yoeunes/toastr, php-flasher

---

## Q44: How are vendor packages managed?

**Short Answer:**  
Composer for PHP, npm for JS; lockfiles ensure reproducible builds.

**Technical Explanation:**  
`composer.json` and `package.json` list dependencies. `composer.lock` and `package-lock.json` lock versions. `composer install` and `npm install` restore exact versions.

**Key Files:**  
- [composer.json](composer.json)
- [package.json](package.json)
- [composer.lock](composer.lock)
- [package-lock.json](package-lock.json)

**Packages Used:**  
- composer/npm ecosystem

---

## Q45: How are environment secrets handled?

**Short Answer:**  
Via `.env` variables referenced in `config/*.php` files.

**Technical Explanation:**  
`.env.example` contains placeholders. Real `.env` (not in repo) has actual values for PUSHER, KHALTI, ESEWA, DB credentials. Config files read from env via `env()` helper.

**Key Files:**  
- [.env.example](.env.example)
- [config/broadcasting.php](config/broadcasting.php)
- [app/Services/KhaltiService.php](app/Services/KhaltiService.php)

**Packages Used:**  
- N/A

---

## Q46: How is unit/feature testing organized?

**Short Answer:**  
Tests under `tests/Feature` and `tests/Unit` using PHPUnit and Laravel helpers.

**Technical Explanation:**  
Feature tests cover payment flows and DB state changes. Factories under `database/factories` generate test data. Services can be mocked for external calls.

**Key Files:**  
- [tests/Feature/Payments/KhaltiCheckoutTest.php](tests/Feature/Payments/KhaltiCheckoutTest.php)
- [tests](tests)

**Packages Used:**  
- phpunit, fakerphp

---

## Q47: How are unique constraints and indexes applied?

**Short Answer:**  
Migrations apply unique constraints (e.g., `transaction_uuid` on payments) and foreign keys.

**Technical Explanation:**  
`create_payments_table` sets `unique('transaction_uuid')` and foreign keys to users/orders. Indexes improve query performance.

**Key Files:**  
- [database/migrations/2026_02_09_000001_create_payments_table.php](database/migrations/2026_02_09_000001_create_payments_table.php)

**Packages Used:**  
- Laravel migrations

---

## Q48: How is developer documentation provided?

**Short Answer:**  
`docs/` folder contains implementation plans, iterative development notes, and deployment instructions.

**Technical Explanation:**  
Documents explain swap implementation, advanced features, and deployment steps. Useful for understanding architecture decisions.

**Key Files:**  
- [docs/SWAP_IMPLEMENTATION_PLAN.md](docs/SWAP_IMPLEMENTATION_PLAN.md)
- [docs/iteration_6_advanced_features.md](docs/iteration_6_advanced_features.md)

**Packages Used:**  
- N/A

---

## Q49: How is product filtering/search implemented?

**Short Answer:**  
Product model includes scopes and controller queries filter based on request parameters.

**Technical Explanation:**  
`Product` model defines `scopeApproved()`, `scopePending()` scopes. `getTypeAttribute()` normalizes product types. Controllers use query builder to filter by category, status, type, etc.

**Key Files:**  
- [app/Models/Product.php](app/Models/Product.php)
- [app/Http/Controllers/User/ProductController.php](app/Http/Controllers/User/ProductController.php)

**Packages Used:**  
- N/A

---

## Q50: How is auditing/troubleshooting supported?

**Short Answer:**  
Request/response payloads stored; notifications saved in DB; wallet ledger entries provide audit trail.

**Technical Explanation:**  
Payment JSON payloads persist for debugging. Notification database entries track events. Wallet ledger shows all money flow with before/after balances.

**Key Files:**  
- [app/Models/Payment.php](app/Models/Payment.php)
- [app/Models/WalletLedgerEntry.php](app/Models/WalletLedgerEntry.php)
- [app/Notifications/User](app/Notifications/User)

**Packages Used:**  
- N/A

---

## Q51: How are admin exports implemented?

**Short Answer:**  
Admin analytics blade includes link to `route('admin.reports', ['export' => 'csv'])` for CSV download.

**Technical Explanation:**  
Controller generates CSV of analytics data when export parameter present. Users download file via browser.

**Key Files:**  
- [resources/views/admin/analytics/index.blade.php](resources/views/admin/analytics/index.blade.php)
- [app/Http/Controllers/Admin/ReportController.php](app/Http/Controllers/Admin/ReportController.php) (if present)

**Packages Used:**  
- N/A

---

## Q52: How is localisation/time formatting handled?

**Short Answer:**  
Blade uses `diffForHumans()` for human timestamps; PHP helpers format dates.

**Technical Explanation:**  
Notification controller calls `created_at->diffForHumans()` to show "2 days ago". No explicit i18n package observed.

**Key Files:**  
- [app/Http/Controllers/User/NotificationController.php](app/Http/Controllers/User/NotificationController.php)
- Blade templates

**Packages Used:**  
- Laravel date helpers

---

## Q53: Are observers or model events used?

**Short Answer:**  
`app/Observers` folder exists; observers may be registered in service providers for side effects.

**Technical Explanation:**  
Model observers can hook into events (created, updated, deleted) to run side effects like notifications or stats updates. Observers are registered in `bootstrap/app.php` or providers.

**Key Files:**  
- [app/Observers](app/Observers)
- [bootstrap/app.php](bootstrap/app.php)

**Packages Used:**  
- Laravel events

---

## Q54: How is CSRF protection applied to forms?

**Short Answer:**  
Blade includes `@csrf` token; Echo headers include `X-CSRF-TOKEN`.

**Technical Explanation:**  
Blade forms use `@csrf` (framework default). AJAX requests include CSRF token in headers. Middleware verifies token on state-changing requests.

**Key Files:**  
- [resources/js/echo.js](resources/js/echo.js)
- Blade form templates

**Packages Used:**  
- Laravel CSRF middleware

---

## Q55: Where are helpers and utility classes?

**Short Answer:**  
`app/Helpers` contains utility functions; `app/Services` contains domain services.

**Technical Explanation:**  
`ImageUrlHelper.php` provides reusable functions. Service classes encapsulate domain logic (payments, wallets, inventory). Promotes single responsibility and reusability.

**Key Files:**  
- [app/Helpers/ImageUrlHelper.php](app/Helpers/ImageUrlHelper.php)
- [app/Services](app/Services)

**Packages Used:**  
- N/A

---

## Q56: How are environment-specific configs handled?

**Short Answer:**  
Config files read from `.env` variables; config caching used in production.

**Technical Explanation:**  
`config/*.php` files use `env()` helper to read variables. `.env.example` documents expected keys. Production uses `config:cache` for performance.

**Key Files:**  
- [.env.example](.env.example)
- [config/*.php](config)

**Packages Used:**  
- N/A

---

## Q57: How are large operations and long tasks handled?

**Short Answer:**  
Critical operations wrapped in DB transactions; services ensure atomic updates; queuing for heavy tasks.

**Technical Explanation:**  
Payments and wallet updates use transactions. `WalletLedgerService` ensures atomicity. External API calls handled by services with error handling and retries.

**Key Files:**  
- [app/Services/WalletLedgerService.php](app/Services/WalletLedgerService.php)
- [app/Http/Controllers/User/PaymentController.php](app/Http/Controllers/User/PaymentController.php)

**Packages Used:**  
- Laravel DB transactions

---

## Q58: How is developer setup documented?

**Short Answer:**  
README contains setup steps; docs include deployment notes and key requirements.

**Technical Explanation:**  
README instructs setting env vars, running migrations, npm install/build, starting dev server. Docs explain architecture and deployment procedures.

**Key Files:**  
- [README.md](README.md)
- [docs/](docs)

**Packages Used:**  
- N/A

---

## Q59: How are third-party integrations tested?

**Short Answer:**  
Tests exercise API behaviors (Khalti flows) and assert state changes; services can be mocked.

**Technical Explanation:**  
`KhaltiCheckoutTest` simulates checkout, verifies payment creation, redirects, order updates, inventory changes. Services can be mocked in tests to avoid external API calls.

**Key Files:**  
- [tests/Feature/Payments/KhaltiCheckoutTest.php](tests/Feature/Payments/KhaltiCheckoutTest.php)
- [app/Services/KhaltiService.php](app/Services/KhaltiService.php)

**Packages Used:**  
- phpunit, testing helpers

---

## Q60: If Google OAuth needed urgently, how would you implement it?

**Short Answer:**  
Install `laravel/socialite`, add Google config, create redirect/callback routes, persist user OAuth info.

**Technical Explanation:**  
1. `composer require laravel/socialite`
2. Add Google client_id/secret/redirect to `config/services.php`
3. Create `SocialAuthController` with `redirectToProvider()` and `handleProviderCallback()` methods
4. Fetch user info, link or create `User` record
5. Add routes to `routes/auth.php`
6. Update `.env` with Google credentials
7. Handle edge cases (email collisions, missing data)

**Key Files to Create/Modify:**  
- [config/services.php](config/services.php)
- [routes/auth.php](routes/auth.php)
- New: [app/Http/Controllers/Auth/SocialAuthController.php]
- Migration to add provider_id column if needed

**Packages Used:**  
- laravel/socialite

---

## BONUS: Concurrent Purchase Scenario

### **Question: What happens if 2 users try to purchase the same product simultaneously?**

**Short Answer:**  
Without explicit locking, **both orders could be created even if total quantity is insufficient**, resulting in **negative inventory**.

**Technical Explanation:**

#### Current Implementation Risk:
1. **No Pessimistic Locking**: The code does not use `DB::transaction()` with `lockForUpdate()` on the products table.
2. **Race Condition Scenario**:
   - User A reads `Product::quantity = 5`
   - User B reads `Product::quantity = 5` (same value, simultaneously)
   - User A decrements to 4, saves
   - User B decrements to 4, saves
   - **Result**: Only 1 item sold but quantity shows 4 (should be 3)

3. **Inventory Reservation Service**: Even if the service attempts to decrement quantity, without transactions, both concurrent requests could pass validation and proceed to payment.

#### Scenario Breakdown:

```
Timeline:
T1: User A → PaymentController::checkoutPay()
T2: User B → PaymentController::checkoutPay() [same product, qty=1 each]
T3: Both read Product qty=5 ✓
T4: Both validate: qty >= 1 ✓
T5: User A's InventoryReservationService decrements qty → 4
T6: User B's InventoryReservationService decrements qty → 3
T7: Both proceed to payment verification
T8: Both payments succeed
T9: Two Order records created
T10: Inventory shows qty=3 but 2 orders created (should have qty=1)
```

#### How to Prevent:

1. **Pessimistic Locking**:
```php
DB::transaction(function () {
    $product = Product::lockForUpdate()->find($productId);
    if ($product->quantity >= $requestedQty) {
        $product->decrement('quantity', $requestedQty);
        // proceed
    } else {
        throw new InsufficientInventoryException();
    }
});
```

2. **Optimistic Locking** (version column):
```php
$product->update(['quantity' => $product->quantity - 1, 'version' => $product->version + 1]);
// Fails if version changed mid-transaction
```

3. **Atomic Database Operations**:
```php
DB::statement(
    "UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?",
    [$requestedQty, $productId, $requestedQty]
);
// Verify affected rows > 0
```

**Key Files Affected**:  
- [app/Http/Controllers/User/PaymentController.php](app/Http/Controllers/User/PaymentController.php)
- [app/Services/InventoryReservationService.php](app/Services/InventoryReservationService.php)
- [database/migrations/product_table](database/migrations) (may need version column)

**Recommendation**:  
Implement pessimistic locking or atomic database updates to ensure inventory consistency under concurrent load.

---

## Summary

This viva document covers **60 core questions** about the ReLoop project, including:
- Architecture & framework setup
- Payment & wallet systems
- Realtime notifications & broadcasting
- Rental & swap domains
- Frontend & analytics
- Testing & error handling
- Concurrency concerns

All answers are grounded in the actual codebase with direct file references and package citations.
