# RELOOP — Mobile App UI Design Plan

> **Application:** RELOOP — A Circular P2P Marketplace  
> **Platform:** React Native (iOS + Android) / Flutter  
> **Purpose:** Allow users to Buy, Rent, and Swap everyday goods in a sustainable P2P economy.  
> **Document Author:** Generated from live web app analysis — June 2026

---

## 1. App Overview & Mission

RELOOP is a circular economy marketplace where users can give preloved items a second life through three transaction modes:

- **Buy / Sell** — Direct P2P purchase
- **Rent / Borrow** — Time-bound item lending with security deposit
- **Swap** — Item-for-item exchange with automated fair-value resolution

The mobile app must translate the full feature-set of the existing web portal into an intuitive, thumb-first mobile experience.

---

## 2. Design Language & Visual Identity

### 2.1 Brand Palette

| Token | Value | Usage |
|---|---|---|
| `primary` | `#1A6B3C` (Deep Forest Green) | CTAs, active states, brand accents |
| `primary-light` | `#E8F5EE` | Card backgrounds, pill highlights |
| `primary-dark` | `#124D2B` | Pressed states, header gradients |
| `surface` | `#F5F6F8` | App background |
| `card` | `#FFFFFF` | Card surfaces |
| `text-primary` | `#111827` | Headings, important values |
| `text-secondary` | `#6B7280` | Labels, meta-text |
| `text-muted` | `#9CA3AF` | Placeholders, timestamps |
| `accent-buy` | `#1A6B3C` | "Buy" mode badge |
| `accent-rent` | `#2563EB` | "Rent" mode badge |
| `accent-swap` | `#7C3AED` | "Swap" mode badge |
| `success` | `#10B981` | Completed, approved states |
| `warning` | `#F59E0B` | Pending, awaiting states |
| `danger` | `#EF4444` | Rejected, disputed states |
| `eco-gold` | `#F59E0B` | Eco Score indicator |

### 2.2 Typography

| Style | Font | Weight | Size |
|---|---|---|---|
| Display | `Outfit` | 700 (Bold) | 28–32sp |
| Heading | `Outfit` | 600 (SemiBold) | 20–22sp |
| Sub-heading | `Outfit` | 500 (Medium) | 16–18sp |
| Body | `Inter` | 400 (Regular) | 14–15sp |
| Caption / Label | `Inter` | 400 | 12sp |
| Micro | `Inter` | 400 | 10sp |

### 2.3 Elevation & Radius

- **Card radius:** `12dp`
- **Button radius:** `10dp`
- **Pill/Badge radius:** `999dp` (fully rounded)
- **Bottom sheet radius (top):** `24dp`
- **Card shadow:** `0 2px 12px rgba(0,0,0,0.07)`
- **Modal shadow:** `0 8px 32px rgba(0,0,0,0.18)`

### 2.4 Micro-animation Principles

- Tab bar item selection: scale `1.0 → 1.15` + color transition (150ms ease-out)
- Card press: scale `1.0 → 0.97` (100ms) + shadow reduction
- Bottom sheet open: slide-up from 30% with spring physics
- Listing type toggle (Buy/Rent/Swap): animated underline slide (200ms)
- Eco Score ring: animated stroke draw on first load (600ms)
- Skeleton loaders on all async data, not spinners

---

## 3. Navigation Architecture

### 3.1 Bottom Tab Bar (5 Tabs)

The primary navigation is a **persistent bottom tab bar** with large tap targets (48dp min).

```
┌────────────────────────────────────────┐
│  🏠 Home  │  🛍 Market │  ➕ List  │  📦 Activity │  👤 Profile │
└────────────────────────────────────────┘
```

| Tab | Icon | Label | Primary Screen |
|---|---|---|---|
| 1 | House icon | Home | Dashboard Overview |
| 2 | Grid/Store icon | Market | Marketplace Browse |
| 3 | **FAB `+`** | List | Create Listing |
| 4 | Box/Activity icon | Activity | Orders, Rentals, Swaps |
| 5 | Person icon | Profile | Profile & Wallet |

**Tab bar design rules:**
- Active tab: icon + label in `primary` green + filled icon variant
- Inactive tab: icon + label in `text-muted` grey + outlined icon variant
- Centre `+` tab is a **floating action pill** (not flat), elevated with green background
- Tab bar has a `1dp` top border in `#E5E7EB`
- Safe area padding respected on iOS (home indicator) and Android

### 3.2 Stack Navigation per Tab

Each tab manages its own independent navigation stack so back navigation is contextual.

---

## 4. Screen-by-Screen Specifications

---

### 4.1 Screen: Splash / Onboarding

**When:** First launch only

**Layout:**
- Full-screen deep green gradient (`#124D2B → #1A6B3C`)
- Centered RELOOP wordmark in white (Outfit Bold, 36sp)
- Tagline: _"Give it a second life."_ in white semi-transparent text
- Animated leaf/loop icon (SVG, 80dp, draws in with stroke animation)
- "Get Started" button (white bg, green text) — leads to Registration
- "Log In" text-link below button

---

### 4.2 Screen: Login

**Layout:**
- Light background (`#F5F6F8`)
- Top: RELOOP logo + "Welcome Back" heading
- Form card (white, rounded-16, shadow):
  - Email input with mail icon prefix
  - Password input with lock icon + eye toggle suffix
  - "Forgot Password?" text link right-aligned
- Primary CTA: "Sign In" (full-width, green, 52dp height)
- Divider: "— or —"
- Social login row (Google / Facebook icons)
- Footer: "New to RELOOP? Register"

---

### 4.3 Screen: Dashboard (Tab 1 — Home)

**Header:**
- `greeting text`: "Welcome back, **krish** 👋" (Outfit SemiBold 20sp)
- Sub-text: "Your marketplace at a glance"
- Top-right: Bell icon (notification count badge in red) + Avatar (tappable → Profile)

**Body — Scrollable vertical feed:**

**Block A — Stats Row (horizontal scroll, 4 cards):**
```
┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
│ 🏷 22    │ │ 📦 5     │ │ 🔄 0     │ │ ⚡ 324   │
│ Active   │ │ Total    │ │ Active   │ │ Eco      │
│ Listings │ │ Orders   │ │ Rentals  │ │ Score    │
└──────────┘ └──────────┘ └──────────┘ └──────────┘
```
- Cards: white bg, 12dp radius, green icon in small chip
- Eco Score card shows a small circular progress arc in gold

**Block B — Eco Score Spotlight Card:**
- Full-width card with gradient background (light green)
- Large circular progress ring (SVG) showing score `324` in centre
- Label: "Your Circular Economy Score"
- Sub-text: "Keep listing, renting, and swapping to earn more points!"

**Block C — Quick Actions Grid (2×2):**
```
┌──────────────┬──────────────┐
│  📋 New      │  🏪 View     │
│  Listing     │  Listings    │
├──────────────┼──────────────┤
│  💰 Open     │  🔔 Check    │
│  Wallet      │  Inbox       │
└──────────────┴──────────────┘
```
- Each action is a card with icon (40dp circle, light green bg) + label
- Tap navigates directly to that section

**Block D — Recent Notifications Preview:**
- Section header: "Recent Activity" + "View All →" right
- Up to 3 notification rows with tag chip (CTR, SWAP, OK, N) + message + timestamp
- If empty: empty-state illustration + "No recent activity"
- "View All" navigates to full Notifications screen

---

### 4.4 Screen: Marketplace (Tab 2 — Market)

**Header (sticky):**
- Search bar (full-width, rounded, magnifier icon prefix, `Search items...` placeholder)
- Below search: horizontally scrolling **Category Chip Row**
  - All | Electronics | Clothing | Books | Furniture | Cameras | Sports | Other
  - Active chip: green bg + white text
  - Inactive chip: white bg + grey border + grey text

**Filter Bar (below chips):**
- "Filter" button (left) with filter icon — opens bottom sheet
- Active filter count badge on button
- Sort dropdown (right): "Newest", "Price: Low-High", "Price: High-Low"

**Product Grid (2-column):**

Each product card:
```
┌───────────────────┐
│  [Product Image]  │  ← 1:1 ratio, rounded top corners
│   10 in stock ▸  │  ← Stock badge (top-right overlay)
├───────────────────┤
│ Drone             │  ← Title (SemiBold 14sp)
│ Cameras           │  ← Category (caption, muted)
│ Rs. 1,200/day ●  │  ← Primary price, coloured dot
│ [BUY][RENT][SWAP] │  ← Mode badges (pill chips)
│ sachet silwal ★  │  ← Seller name + rating
└───────────────────┘
```
- Mode badge colours: BUY=green, RENT=blue, SWAP=purple
- Heart/wishlist icon overlay (top-left of image)
- Tap card → Product Detail screen (push)

**Filter Bottom Sheet:**

Slides up from bottom (drag handle at top):
- **Transaction Type:** "For Sale" / "For Rent" / "For Swap" toggle chips
- **Category:** checkbox list (All, Electronics, Clothing, etc.)
- **Condition:** "Like New", "Good", "Fair", "Poor"
- **Price Range:** dual-handle range slider (Rs. 0 – Rs. 50,000)
- Footer: "Clear All" (ghost) + "Apply Filters" (primary CTA)

---

### 4.5 Screen: Product Detail

**Navigation:** Pushed from Marketplace, My Listings cards, or Wishlist

**Layout (full-screen):**

**Image Section (top 45% of screen):**
- Swipeable image gallery (full-width, no rounded corners at top)
- Page indicator dots at bottom of image area
- Back button (top-left, semi-transparent circle)
- Wishlist toggle (top-right, semi-transparent circle, heart icon)

**Content Section (scrollable, rounded-top card overlapping image):**

- **Listing Type Badge:** small pill (e.g., "Cameras")
- **Title:** large display heading (Outfit Bold 24sp)
- **Seller Row:** avatar + name + "VERIFIED" badge + star rating + "View Profile →"
- **Status Row:** Available count | Condition | Listed date

**Transaction Mode Selector:**
```
┌──────┬──────┬──────┐
│ BUY  │ RENT │ SWAP │  ← Segmented control, animated underline
└──────┴──────┴──────┘
```

**BUY tab content:**
- Price: large green amount (e.g., "Rs. 14,000")
- Quantity stepper (+/−)
- Description (expandable)
- Sticky bottom bar: "Add to Cart" (outline) + "Buy Now" (primary)

**RENT tab content:**
- Daily Rate: `Rs. 1,200/day`
- Security Deposit: `Rs. 3,000`
- Max Duration: `31 days`
- Available From: date text
- Date picker (inline calendar or tap-to-open bottom sheet calendar)
- Summary: "5 days × Rs. 1,200 + Rs. 3,000 deposit = Rs. 9,000"
- Sticky bottom bar: "Request Rental" (primary CTA)

**SWAP tab content:**
- "Your offer" section: grid of user's own listings to select as swap item
- Value comparison bar: "Their item (Est. Rs. 14,000) ↔ Your item (Est. Rs. X)"
- Note field (optional message)
- Sticky bottom bar: "Propose Swap" (primary CTA)

---

### 4.6 Screen: Create Listing (Tab 3 — Centre FAB)

**Header:** "New Listing" + X close button (dismisses to previous tab)

**Step Indicator:** 3-step horizontal progress bar at top
```
● ─────── ○ ─────── ○
Step 1      Step 2     Step 3
Details   Type & Price  Photos
```

**Step 1 — Item Details:**
- Title input (required)
- Description textarea
- Category dropdown (opens modal picker):
  - Electronics, Clothing, Books, Furniture, Cameras, Sports, Other
- Condition selector (segmented or radio):
  - Like New | Good | Fair | Poor
- "Next →" CTA (primary, full-width, bottom)

**Step 2 — Listing Type & Pricing:**
- Toggle section title: "How would you like to offer this?"
- **For Sale** switch:
  - When ON: shows "Selling Price" input + "Stock Quantity" input
- **For Rent** switch:
  - When ON: shows "Daily Rate" input + "Security Deposit" input + "Max Duration (days)" input
- **For Swap** switch:
  - When ON: shows "Estimated Value" input + optional "Swap preferences" note
- At least one option must be enabled (validated before proceeding)
- "← Back" ghost + "Next →" primary CTAs (row)

**Step 3 — Photos:**
- Photo upload grid (3×2, 6 slots max):
  - First slot: "+" icon + "Add Photo" label
  - Filled slots: thumbnail with X remove button overlay
- Tap slot: opens native image picker (camera OR gallery)
- Drag-to-reorder support (first photo = cover)
- "← Back" ghost + "Submit Listing" primary CTAs (row)

**On Submit:** Success bottom sheet with confetti micro-animation + "View Listing" CTA.

---

### 4.7 Screen: Activity Hub (Tab 4)

**Header:** "Activity" title

**Tabs (horizontal tab bar):**
```
[ My Orders ] [ Rentals ] [ Swaps ]
```

---

**My Orders Tab:**

Stats row: "Total Orders: 5" chip

Order card:
```
┌─────────────────────────────────┐
│ [Img] Drone                     │
│       sachet silwal             │
│       May 21, 2026 • PAID       │
│       Rs. 14,000.00             │
│  [Leave Review]  [Report Issue] │
└─────────────────────────────────┘
```
- Status chips colour-coded: PAID=green, PENDING=amber, REFUNDED=blue, DISPUTED=red
- "Leave Review" opens a bottom sheet with star rating + text input
- "Report Issue" opens a dispute form bottom sheet

---

**Rentals Tab:**

Sub-tabs:
```
[ Active ] [ Completed as Renter ] [ Owner Completed ]
```

Rental card:
```
┌─────────────────────────────────┐
│ [Img] DJI Controller            │
│       Owner: sachet silwal      │
│       May 20 → May 25, 2026     │
│       ⏱ 3 days remaining        │
│       [Return Item]             │
└─────────────────────────────────┘
```
- Active rentals show a countdown timer in amber
- Completed rentals show final status + review option

---

**Swaps Tab:**

Stats row: Completed (3) | Non-completed (2) | Total (5)

Sub-tabs:
```
[ Completed (3) ] [ Non-completed (2) ]
```

Swap card:
```
┌─────────────────────────────────┐
│  [Watch img] ↔ [Drone img]      │
│  Casio Watch ↔ Drone            │
│  With: sachet silwal            │
│  PAID • May 21, 2026            │
│  Dispatch Contact: ...          │
└─────────────────────────────────┘
```
- Swap arrows (↔) displayed between the two item thumbnails
- Status badge: COMPLETED=green, PENDING=amber, REJECTED=red, PAID=blue

---

### 4.8 Screen: Profile & Wallet (Tab 5)

**Top Profile Card:**
- Large avatar (80dp circle) — initials or uploaded photo
- Name: "krish" (Display Bold)
- Location: "Pokhara, Gandaki Province" (caption + pin icon)
- Badges row: "VERIFIED" chip + "★ VERIFIED SELLER" chip
- Stats row: Active Listings | Completed Deals | Member Since
- "Edit Profile" button (outline, small)

**Section — Wallet Summary Card:**
- Gradient background (light green)
- "My Wallet" heading
- Available Balance: large amount (e.g., `Rs. 2,357.00`) in green
- Pending Hold: `Rs. 0.00`
- Row: "Active Payouts: 0" | "Ledger Entries: 8"
- "Request Payout →" CTA (white pill button)

**Wallet Payout Form (expanding section or navigate to sub-screen):**
- Amount input
- Note (optional)
- "Submit Request" CTA

**Wallet Tabs:**
```
[ Active Payouts (0) ] [ Ledger (8) ] [ Payout History (2) ]
```

Ledger entry row:
```
+Rs. 2,400  │  Sale: DJI Drone          │  May 21
-Rs. 43     │  Swap Shipping fee        │  May 20
+Rs. 3,000  │  Security Deposit Release │  May 19
```
- Credits: green `+` prefix
- Debits: red `−` prefix

**Section — My Public Profile:**
- "Preview how others see you →" link
- Shows active listings grid (3-column thumbnail grid)
- Shows completed review count and average star rating

**Section — Navigation Links:**
```
🔔  Notifications
❤️  Wishlist
⚙️  Profile Settings
🚪  Logout
```
Each row: icon + label + chevron `›`

---

### 4.9 Screen: Wishlist

**Header:** "My Wishlist" + count badge

**Layout:** 2-column product grid (same card design as Marketplace)
- Remove from wishlist: swipe-left gesture or X icon on card
- Empty state: heart illustration + "Save items you love" + "Browse Marketplace" CTA

---

### 4.10 Screen: Notifications

**Header:** "Notifications" + "Mark All Read" (text button, right)

**Notification types and their chip colours:**
| Type | Chip | Example |
|---|---|---|
| Counter Offer | `CTR` (amber) | "Owner made counter offer of Rs. 14,000 for Drone" |
| Swap Request | `SWAP` (purple) | "New swap request for Casio Watch from sachet silwal" |
| Rental Approved | `OK` (green) | "Your rental request has been approved by the owner" |
| Swap Complete | `N` (grey) | "Your swap has been completed. Funds transferred." |
| System | `N` (grey) | General system messages |

Notification row:
```
┌────────────────────────────────────────┐
│ [CTR]  Owner made a counter offer...   │
│        3 weeks ago            [View →] │
└────────────────────────────────────────┘
```
- Unread rows have a subtle left green border accent
- Tapping "View" deep-links into relevant swap/rental/order screen

---

### 4.11 Screen: Profile Settings

**Sections:**

**Personal Information:**
- Full Name (editable input)
- Email (read-only + "Change Email" link)
- Phone Number
- Location (city/province text)
- Bio / About (textarea)

**Account Security:**
- "Change Password" row → navigates to change-password flow
- "Linked Accounts" (Google/Facebook)

**Seller Settings:**
- "Become a Verified Seller" prompt (if not verified)
- Average Rating display
- Total Deals counter

**Preferences:**
- Push Notifications toggle
- Email Notifications toggle
- Dark Mode toggle (future)

**Footer:**
- "Save Changes" primary CTA (sticky bottom)

---

### 4.12 Screen: Public Seller Profile

**Accessed via:** Product detail → seller name tap

**Layout:**
- Seller avatar (large, 80dp)
- Name + location + verification badges
- Stats: Active Listings | Completed Deals | Member Since | Avg Rating
- Active Listings grid (2-column)
- Reviews section (star summary + individual review cards)

---

## 5. Key UX Patterns & Interaction Guidelines

### 5.1 Bottom Sheets
Used for: Filters, Review forms, Dispute forms, Swap proposal picker, Date pickers, Confirmation dialogs

**Rules:**
- Always has a drag handle (grey pill, 40×4dp, centred)
- Background dims to 40% black overlay
- Dismissable by dragging down or tapping backdrop
- Keyboard-aware (slides up with keyboard)

### 5.2 Empty States
Every list screen must handle empty states:
- Illustration (SVG, on-brand colour)
- Concise message ("No listings yet")
- CTA button to primary action

### 5.3 Loading States
- Use **skeleton screens** (animated shimmer) for all data lists
- Avoid full-page spinners
- Use small circular indicators only for button loading states (after tap)

### 5.4 Error States
- Network error: toast message at bottom (red, auto-dismiss 4s)
- Form validation: inline red border + error label below field
- Full-page error: error illustration + "Try Again" button

### 5.5 Pull-to-Refresh
Every list screen supports pull-to-refresh (green spinner on pull).

### 5.6 Infinite Scroll / Pagination
Marketplace product grid paginates — "load more" trigger at bottom of list (auto-fetch when 3 cards from bottom).

---

## 6. Screen Map / Navigation Flow

```
                    App Launch
                        │
               ┌────────┴────────┐
           First Time         Returning
               │                 │
          Onboarding           Login
               │                 │
               └────────┬────────┘
                        │
                   Bottom Tab Bar
          ┌─────────────┼─────────────┐
         Home        Market      Activity    Profile
          │             │              │         │
       Dashboard   Marketplace    Orders/     Profile+
       Overview       Browse      Rentals/    Wallet
                        │         Swaps
                   Product Detail
                   ┌────┤
                  Buy  Rent  Swap
                        │
                   Create Listing
                   (Tab Centre FAB)
                   Step 1 → Step 2 → Step 3
```

---

## 7. Platform-Specific Considerations

### iOS
- Use SF Symbols where matching (supplement with custom icons)
- Respect safe area insets top (notch/island) and bottom (home indicator)
- Navigation bar: native look or custom matching brand
- Haptic feedback on: CTAs, toggle switches, cart additions, swap proposals

### Android
- Material elevation shadows
- Respect system navigation (gesture nav / 3-button)
- Status bar: transparent, dark icons on light background
- Ripple effect on all tappable items

---

## 8. Accessibility Requirements

- All tappable elements: minimum 44×44dp touch target
- Colour contrast: WCAG AA minimum (4.5:1 for body text)
- Screen reader labels on all icons (no icon-only buttons without label or aria)
- Dynamic font size support (scale up to 150% without layout breakage)
- Focus indicators for keyboard/switch access users

---

## 9. Future Screens (Phase 2)

| Screen | Purpose |
|---|---|
| **Chat / Messaging** | In-app P2P messaging between buyer and seller |
| **Map View** | Browse nearby listings on a map |
| **Eco Impact Report** | Gamified view of CO₂ saved, items saved from landfill |
| **Referral & Rewards** | Earn Eco Score by referring friends |
| **Admin Panel (separate app)** | Manage users, listings, disputes, payouts |
| **Barcode Scanner** | Scan product barcode to auto-fill listing details |

---

## 10. Summary of Screens

| # | Screen | Tab | Route |
|---|---|---|---|
| 1 | Splash / Onboarding | — | `/splash` |
| 2 | Login | — | `/login` |
| 3 | Register | — | `/register` |
| 4 | Dashboard Overview | Home | `/home` |
| 5 | Marketplace Browse | Market | `/marketplace` |
| 6 | Product Detail | Market | `/product/:id` |
| 7 | Create Listing Step 1 | + FAB | `/list/details` |
| 8 | Create Listing Step 2 | + FAB | `/list/type` |
| 9 | Create Listing Step 3 | + FAB | `/list/photos` |
| 10 | My Orders | Activity | `/activity/orders` |
| 11 | Rentals | Activity | `/activity/rentals` |
| 12 | Swaps | Activity | `/activity/swaps` |
| 13 | Profile & Wallet | Profile | `/profile` |
| 14 | Wallet Ledger | Profile | `/profile/wallet` |
| 15 | Wishlist | Profile | `/profile/wishlist` |
| 16 | Notifications | Profile | `/profile/notifications` |
| 17 | Profile Settings | Profile | `/profile/settings` |
| 18 | Public Seller Profile | Pushed | `/user/:id` |

---

*End of RELOOP Mobile App UI Design Plan*
