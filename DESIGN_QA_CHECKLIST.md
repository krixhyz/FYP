# Design QA Checklist (Curated Brutalist)

Use this checklist to review each page against [DESIGN (1).md](DESIGN%20(1).md).

## Global Rules (Apply To Every Page)
- [ ] 0px corners only (no rounded cards/buttons/inputs)
- [ ] No 1px structural dividers (separate sections with tonal blocks + spacing)
- [ ] Primary CTA uses green gradient (primary to primary_container)
- [ ] Typography pairing is consistent: Space Grotesk headings, Manrope body
- [ ] Surface hierarchy is visible: background -> low container -> lowest card
- [ ] Shadows are ambient and soft (no heavy default drop shadows)
- [ ] Floating overlays (dropdowns/modals) use glass + blur behavior
- [ ] BUY/RENT/SWAP controls look like actionable chips (all-caps, strong contrast)

## Public + User Routes

### Marketplace
- [ ] / (`products.index`) -> [resources/views/products/index.blade.php](resources/views/products/index.blade.php)
- [ ] /products/{id} (`products.show`) -> [resources/views/products/show.blade.php](resources/views/products/show.blade.php)
- [ ] /products/{id}/buy (`products.buy`) -> [resources/views/products/buy.blade.php](resources/views/products/buy.blade.php)
- [ ] /products/{id}/rent (`products.rent`) -> [resources/views/products/rent.blade.php](resources/views/products/rent.blade.php)
- [ ] /products/{id}/swap (`products.swap`) -> [resources/views/products/swap.blade.php](resources/views/products/swap.blade.php)

### Seller / Buyer Workspaces
- [ ] /dashboard (`dashboard`) -> [resources/views/dashboard.blade.php](resources/views/dashboard.blade.php)
- [ ] /my-listings (`products.myListings`) -> [resources/views/products/my_listings.blade.php](resources/views/products/my_listings.blade.php)
- [ ] /my-purchases (`products.myPurchases`) -> [resources/views/products/my_purchases.blade.php](resources/views/products/my_purchases.blade.php)
- [ ] /cart (`cart.index`) -> [resources/views/cart/index.blade.php](resources/views/cart/index.blade.php)
- [ ] /cart/checkout (`cart.checkout`) -> [resources/views/cart/checkout.blade.php](resources/views/cart/checkout.blade.php)
- [ ] /wishlist (`wishlist.index`) -> [resources/views/wishlist/index.blade.php](resources/views/wishlist/index.blade.php)

### Orders / Payments
- [ ] /order/product/{product}/checkout (`order.checkout.product`) -> [resources/views/orders/checkout.blade.php](resources/views/orders/checkout.blade.php)
- [ ] /order/{order}/checkout (`order.checkout`) -> [resources/views/orders/checkout.blade.php](resources/views/orders/checkout.blade.php)
- [ ] /rental/checkout/{rentalRequest} (`rental.checkout`) -> [resources/views/rental/checkout.blade.php](resources/views/rental/checkout.blade.php)
- [ ] /swap/checkout/{swapRequest} (`swap.checkout`) -> [resources/views/swaps/checkout.blade.php](resources/views/swaps/checkout.blade.php)

### Disputes / Reviews / Notifications
- [ ] /dispute/create (`dispute.create`) -> [resources/views/disputes/create.blade.php](resources/views/disputes/create.blade.php)
- [ ] /my-disputes (`dispute.my`) -> [resources/views/disputes/my.blade.php](resources/views/disputes/my.blade.php)
- [ ] /review/create (`review.create`) -> [resources/views/reviews/create.blade.php](resources/views/reviews/create.blade.php)
- [ ] /notifications (`notifications.index`) -> [resources/views/notifications/index.blade.php](resources/views/notifications/index.blade.php)

## Auth Routes

- [ ] /login (`login`) -> [resources/views/auth/login.blade.php](resources/views/auth/login.blade.php)
- [ ] /register (`register`) -> [resources/views/auth/register.blade.php](resources/views/auth/register.blade.php)
- [ ] /verify-email (`verification.notice`) -> [resources/views/auth/verify-email.blade.php](resources/views/auth/verify-email.blade.php)
- [ ] /forgot-password (`password.request`) -> [resources/views/auth/forgot-password.blade.php](resources/views/auth/forgot-password.blade.php)
- [ ] /reset-password/{token} (`password.reset`) -> [resources/views/auth/reset-password.blade.php](resources/views/auth/reset-password.blade.php)
- [ ] /confirm-password (`password.confirm`) -> [resources/views/auth/confirm-password.blade.php](resources/views/auth/confirm-password.blade.php)

## Admin Routes

### Core
- [ ] /admin/dashboard (`admin.dashboard`) -> [resources/views/admin/dashboard.blade.php](resources/views/admin/dashboard.blade.php)
- [ ] /admin/users (`admin.users`) -> [resources/views/admin/users/index.blade.php](resources/views/admin/users/index.blade.php)
- [ ] /admin/users/{id} (`admin.users.show`) -> [resources/views/admin/users/show.blade.php](resources/views/admin/users/show.blade.php)
- [ ] /admin/products (`admin.products`) -> [resources/views/admin/products/index.blade.php](resources/views/admin/products/index.blade.php)
- [ ] /admin/products/{product} (`admin.products.show`) -> [resources/views/admin/products/show.blade.php](resources/views/admin/products/show.blade.php)

### Moderation + Data Views
- [ ] /admin/content-moderation (`admin.content`) -> [resources/views/admin/content/index.blade.php](resources/views/admin/content/index.blade.php)
- [ ] /admin/disputes (`admin.disputes`) -> [resources/views/admin/disputes/index.blade.php](resources/views/admin/disputes/index.blade.php)
- [ ] /admin/disputes/{dispute} (`admin.disputes.show`) -> [resources/views/admin/disputes/show.blade.php](resources/views/admin/disputes/show.blade.php)
- [ ] /admin/reviews (`admin.reviews`) -> [resources/views/admin/reviews/index.blade.php](resources/views/admin/reviews/index.blade.php)
- [ ] /admin/transactions (`admin.transactions`) -> [resources/views/admin/transactions/index.blade.php](resources/views/admin/transactions/index.blade.php)
- [ ] /admin/reports (`admin.reports`) -> [resources/views/admin/reports/index.blade.php](resources/views/admin/reports/index.blade.php)

### Super Admin
- [ ] /admin/analytics (`admin.analytics`) -> [resources/views/admin/analytics/index.blade.php](resources/views/admin/analytics/index.blade.php)
- [ ] /admin/system-config (`admin.system.config`) -> [resources/views/admin/system_config/index.blade.php](resources/views/admin/system_config/index.blade.php)

## Shared Layout + Components QA

- [ ] [resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php)
- [ ] [resources/views/layouts/guest.blade.php](resources/views/layouts/guest.blade.php)
- [ ] [resources/views/layouts/admin.blade.php](resources/views/layouts/admin.blade.php)
- [ ] [resources/views/layouts/navigation.blade.php](resources/views/layouts/navigation.blade.php)
- [ ] [resources/views/components/nav-link.blade.php](resources/views/components/nav-link.blade.php)
- [ ] [resources/views/components/responsive-nav-link.blade.php](resources/views/components/responsive-nav-link.blade.php)
- [ ] [resources/views/components/primary-button.blade.php](resources/views/components/primary-button.blade.php)
- [ ] [resources/views/components/secondary-button.blade.php](resources/views/components/secondary-button.blade.php)
- [ ] [resources/views/components/danger-button.blade.php](resources/views/components/danger-button.blade.php)
- [ ] [resources/views/components/dropdown.blade.php](resources/views/components/dropdown.blade.php)
- [ ] [resources/views/components/modal.blade.php](resources/views/components/modal.blade.php)
- [ ] [resources/views/components/section-table.blade.php](resources/views/components/section-table.blade.php)
- [ ] [resources/css/app.css](resources/css/app.css)

## Notes During Review
- Date: 2026-03-30
- Reviewer: GitHub Copilot (GPT-5.3-Codex)
- Screens checked (desktop/mobile): Template-level strict pass (desktop + mobile class rules)
- Inconsistencies found:
	- [resources/views/layouts/navigation.blade.php](resources/views/layouts/navigation.blade.php): circular notification count/dot indicators.
	- [resources/views/users/show.blade.php](resources/views/users/show.blade.php): circular avatar and divider-line review rows.
	- [resources/views/reviews/user.blade.php](resources/views/reviews/user.blade.php): circular avatar tokens.
	- [resources/views/dashboard.blade.php](resources/views/dashboard.blade.php): multiple border-bottom metric separators.
	- [resources/views/disputes/my.blade.php](resources/views/disputes/my.blade.php): border-bottom stack separators and bright blue alert block.
	- [tailwind.config.js](tailwind.config.js): `sans` stack still set to Space Grotesk instead of body font.
- Fixes applied:
	- Replaced circular badges/dots with square chips and square activity indicators.
	- Converted divider-based rows to tonal block rows with spacing rhythm.
	- Changed profile/review avatars from circular to square brand blocks.
	- Updated dispute list sections to tonal cards and converted admin-note highlight to primary-soft tone.
	- Enforced rounded-utility hard reset in [resources/css/app.css](resources/css/app.css) for all rounded utility classes except `.allow-loop-circle`.
	- Updated Tailwind token mapping: `fontFamily.sans = Manrope`, `fontFamily.display = Space Grotesk`.
