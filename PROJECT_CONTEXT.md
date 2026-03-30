# Reloop - Project Context

## 📋 Project Overview

**Reloop** is a sustainable circular economy marketplace platform built with Laravel 11. It enables users to buy, rent, swap, and review second-hand products, promoting sustainable consumption and resource sharing.

**Key Vision:** Digital art gallery aesthetic for sustainable fashion using Digital Brutalism design principles with high-contrast, geometric layouts and no rounded corners.

---

## 🎯 Core Features

### 1. **Product Management**
- Create, edit, delete product listings
- Support for multiple listing types per product:
  - **Buy**: One-time purchase with inventory management
  - **Rent**: Time-based rental with deposit + daily fare
  - **Swap**: Product exchange with optional cash top-up
- Product categories: Electronics, Clothing, Furniture, General
- Multi-image upload (max 6 images, 4MB each)
- Product search, filtering, and recent viewing history

### 2. **Buying & Cart System**
- Shopping cart with quantity management
- Cart checkout with eSewa payment integration
- Order tracking in purchase history
- Quantity-based inventory management

### 3. **Rental System**
- Rental request workflow:
  1. User requests rental with start/end dates
  2. Owner approves/rejects request
  3. Upon approval, renter pays rental fee + deposit
  4. Status tracking: pending → approved → completed
- Auto-return date enforcement
- Deposit refund handling
- Rental history in "My Purchases" dashboard

### 4. **Swap System** ⭐ (Complex)
- **Swap Request Creation**: User proposes swap, optionally offering different product + cash
- **Owner Response Options**:
  - ✅ Accept → If payment required, goes to checkout
  - ❌ Reject
  - 🔄 Counter-offer → Propose different product/amount
- **Swap Request States**: `requested` → `accepted`/`rejected`/`countered` → `awaiting_payment` → `completed`
- **Item Reservation**: Inventory reserved during payment window (configurable minutes)
- **Payment Flow**: Cash top-up payments via eSewa for swaps
- **Completed Swap Tracking**: Both parties see transaction in "My Purchases"

### 5. **Payments (eSewa Integration)**
- eSewa configured for:
  - Direct product purchase from cart
  - Rental fee payments
  - Swap cash top-up payments
- Payment states: pending → success/failure
- Configurable payment gateway settings in `config/esewa.php`

### 6. **Reviews & Disputes**
- Review system for buy/rent/swap transactions
- Dispute resolution system:
  - Status: pending → in_review → resolved → rejected
  - Auto-delete after 10 days of resolution
- Orphaned reviews (no associated transaction) auto-deleted after 30 days

### 7. **User Dashboard**
- Profile with listing/purchase statistics
- Sections for:
  - **My Listings**: Products listed for sale/rent/swap with incoming requests
  - **My Purchases**: Completed orders, active rentals, pending swaps, completed swaps
  - Quick stats: Total spent, active rentals, completed swaps

### 8. **Notifications System**
- Real-time notifications for major events:
  - Rental requests, approvals, rejections
  - Swap requests, counters, acceptances, rejections
  - Dispute status updates
- Notifications are **read-only** (no click navigation)
- Auto-delete after 10 days
- Mark as read functionality

### 9. **Wishlist**
- Save products for later
- View all wishlisted items with quick actions

### 10. **Admin Dashboard**
- Platform overview with statistics
- Product moderation:
  - View all products with associated reviews/disputes/requests
  - Flag suspicious listings
  - Delete products
- Transaction auditing: view all buy/rent/swap activity
- User management

---

## 🏗️ Technical Architecture

### Stack
- **Backend**: Laravel 11 (PHP)
- **Frontend**: Blade templates with Tailwind CSS
- **Database**: PostgreSQL/MySQL
- **Frontend Bundler**: Vite
- **Asset Processing**: PostCSS, Tailwind
- **Broadcasting**: Laravel Reverb (for real-time features)
- **Payment Gateway**: eSewa (Nepal)

### Key Services
- **EsewaService**: Handles payment gateway integration
- **InventoryReservationService**: Manages product reservations during transactions

### Database Tables (Key Models)
| Model | Purpose |
|-------|---------|
| `users` | Platform users with roles (user, admin) |
| `products` | Product listings with type/price/availability |
| `orders` | Buy transactions |
| `rentals` | Active rental instances |
| `rental_requests` | Pending rental requests |
| `swaps` | Completed swap transactions |
| `swap_requests` | Pending/active swap negotiations |
| `payments` | Payment records |
| `reviews` | User reviews for transactions |
| `disputes` | Transaction disputes |
| `cart_items` | Shopping cart storage |
| `wishlists` | User's saved products |
| `recently_viewed` | Product view history |
| `notifications` | System notifications |

---

## 🎨 Design System

### Design Philosophy: Digital Brutalism with Sustainability Focus
- **No rounded corners**: All components have 0px border-radius (except circular badges)
- **High-contrast color blocks**: Define space through colors, not borders
- **Primary Green** (#138a4d / #006a38): Brand identity and sustainability messaging
- **Monochrome foundation**: White (#f9f9f9) to dark gray (#1a1c1c) surfaces
- **Typography**: Space Grotesk for geometric, techno-vintage aesthetic
- **Elevation**: Defined through surface-container tiers, not shadows
- **Spacing**: Generous whitespace (7rem-8.5rem between major sections)

### Key Design Components
- **Surface Hierarchy**: `surface` → `surface_container_low` → `surface_container` → `surface_container_high` → `surface_container_highest`
- **Cards**: `surface-card` and `surface-card-strong` for prominent sections
- **Buttons**: Rectangular primary/secondary/tertiary styles
- **Badges**: Discrete colored badges for product types (Buy/Rent/Swap)
- **Loop Badge**: Perfect circle accent badge inspired by Reloop logo

---

## 📂 Directory Structure

```
/app
  /Console/Commands        - Artisan commands (cleanup tasks)
  /Events                  - Event broadcasting
  /Http
    /Controllers          - Route handlers
    /Middleware           - Auth, role-based access
    /Requests             - Form validation
  /Models                 - Eloquent models (User, Product, Order, etc)
  /Notifications          - Notification classes
  /Services              - Business logic (EsewaService, InventoryReservation)
  /View/Components       - Reusable Blade components

/routes
  web.php                 - Web routes
  api.php                 - API routes (if any)
  auth.php                - Authentication scaffolding
  channels.php            - Broadcasting channels

/resources
  /views                  - Blade templates organized by feature
  /css, /js, /sass       - Frontend assets (processed by Vite)

/database
  /migrations            - Schema migrations
  /factories            - Model factories for testing
  /seeders              - Database seeders

/config
  app.php, database.php, esewa.php, etc. - Framework configuration
```

---

## 🔄 Key Workflows

### Rental Workflow
```
User selects product → Creates rental request → Owner approves/rejects
  → (if approved) User pays rental fee + deposit → Rental active
  → Rental completes automatically → Can write review
```

### Swap Workflow
```
User proposes swap → Owner has 3 options:
  1. Accept → Items reserved → Swap completed
  2. Reject → Swap request cancelled
  3. Counter → Offer different product/amount → Requester responds to counter
  
If payment required → Checkout with eSewa → Swap finalized
```

### Buy Workflow
```
User adds product to cart → Proceeds to checkout → eSewa payment
  → Payment confirmed → Order completed → Can write review
```

---

## 🔐 Security & Authorization

- **Authentication**: Laravel Sanctum (if API used) or session-based
- **Authorization**: 
  - Users can only manage own products/orders
  - Admin middleware for admin-only routes
  - Policy classes for rental/swap/order authorization
- **CSRF Protection**: Form requests validated
- **Password Reset**: Email verification flow

---

## ⏰ Scheduled Tasks

Artisan console commands configured to run daily:
- `notifications:prune` - Delete notifications older than 10 days
- `disputes:prune` - Delete resolved disputes after 10 days
- `listings:prune` - Delete flagged listings after 30 days
- `reviews:prune` - Delete orphaned reviews after 30 days

Run via: `php artisan schedule:run` (via cron job)

---

## 🚀 Recent Implementation Changes

### Recent Additions (From IMPLEMENTATION_SUMMARY.md)
1. ✅ Swap request redirect to dashboard (not swap requests page)
2. ✅ Cart UI updated to card-based layout
3. ✅ Notifications made read-only (removed click actions)
4. ✅ Auto-cleanup commands for data retention
5. ✅ Scheduled daily cleanup tasks

---

## 💾 Configuration

Key config files:
- **config/esewa.php**: Payment gateway credentials and settings
- **config/app.php**: App name, environment, locale
- **.env**: Database, mail, eSewa API credentials

---

## 🧪 Testing & Development

### Running Locally
```bash
# Install dependencies
composer install
npm install

# Setup
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan seed

# Development server
php artisan serve
npm run dev
```

### PHPUnit Tests
```bash
php artisan test
```

---

## 📊 Database Relationships Summary

```
User
  ├─ has many Products (user_id)
  ├─ has many Orders (buyer_id)
  ├─ has many Rentals (as owner)
  ├─ has many RentalRequests (as renter/owner)
  ├─ has many SwapRequests (as requester/owner)
  └─ has many Notifications

Product
  ├─ belongs to User
  ├─ has many Orders
  ├─ has many RentalRequests
  ├─ has many SwapRequests (requested/offered)
  ├─ has many Reviews
  ├─ has many Disputes
  └─ has many CartItems

SwapRequest
  ├─ belongs to Product (requested_product_id)
  ├─ belongs to Product (offered_product_id)
  ├─ belongs to User (owner_id, requester_id)
  └─ has one Swap

Swap
  ├─ belongs to SwapRequest
  ├─ belongs to Product (product_a_id, product_b_id)
  ├─ belongs to User (owner_a_id, owner_b_id)
  └─ has many Reviews/Disputes
```

---

## 🎯 Critical Business Rules

1. **User cannot self-interact**: Cannot rent/swap/buy own products
2. **Inventory enforcement**: Product quantity decreases on purchase
3. **Rental exclusivity**: Product can only be rented to one user at a time (via reservation)
4. **Swap fairness**: Owner accepts swap or can counter-propose
5. **Payment blocking**: Swaps/rentals don't finalize until eSewa payment confirmed
6. **Deposit refunds**: Rental deposits returned after rental completion
7. **Review eligibility**: Can only review completed transactions
8. **Dispute resolution**: Only product owner or transaction participant can dispute

---

## 📝 Notes for Developers

- **Timestamps**: All models use `timestamps()` (created_at, updated_at)
- **Soft deletes**: Not widely used; hard deletes for cleanup commands
- **Broadcasting**: Reverb configured for real-time notifications
- **Locale**: Multi-language support infrastructure (if configured)
- **File storage**: Images stored in public/storage; use Storage facade
- **Cache**: Redis recommended for session/cache management in production

---

## 🔗 Related Docs

- [DESIGN.md](DESIGN.md) - Detailed design system specification
- [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md) - Recent changes and updates
- [README.md](README.md) - Setup instructions
