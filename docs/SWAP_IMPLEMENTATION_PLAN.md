# Swap System Implementation Plan - Complete Redesign
## Current Date: April 18, 2026

---

## Executive Summary

**Goal:** Complete the swap flow with proper negotiation history, dual-party confirmation, escrow-based payment settlement, and order-parity UI/validation.

**Key Changes:**
1. Allow user to specify either `asking_amount` (request cash) or standard `offered_amount` (offer cash).
2. Add negotiation_events table for immutable timeline.
3. Add swap_order_confirmations table (both parties must confirm before completion).
4. Keep cash in escrow until both confirm received.
5. Send order-style details to both parties via email + in-app.
6. Redesign swap request form with solid validation and UX parity to buy/rent orders.
7. Add dashboard tabs for status lifecycle.

**Timeline:** 6–8 weeks (realistic, assuming 1 full-time dev).

---

## Phase 1: Database & Models (Week 1)

### 1.1 Migrations

#### Migration 1: adjust_swap_requests_table
**File:** `database/migrations/2026_04_18_000001_adjust_swap_requests_table.php`

**Purpose:** Add asking_amount, remove/clarify offered_amount, add enum for money direction.

**Changes:**
- Add column `asking_amount DECIMAL(10,2) NULLABLE` (user asks requester to add cash).
- Rename or clarify: `offered_amount` → keep as-is (user offering cash with the item).
- Add column `money_direction ENUM('none', 'owner_asks_cash', 'requester_offers_cash')` DEFAULT 'none'.
- Update status enum to include 'paid' (after payment success, before confirmation).

**Up:**
```php
Schema::table('swap_requests', function (Blueprint $table) {
    if (!Schema::hasColumn('swap_requests', 'asking_amount')) {
        $table->decimal('asking_amount', 10, 2)->nullable()->after('offered_amount');
    }
    if (!Schema::hasColumn('swap_requests', 'money_direction')) {
        $table->enum('money_direction', ['none', 'owner_asks_cash', 'requester_offers_cash'])
              ->default('none')->after('asking_amount');
    }
});

DB::statement("ALTER TABLE swap_requests MODIFY status ENUM('requested', 'countered', 'awaiting_payment', 'paid', 'confirmation_pending', 'completed', 'rejected', 'cancelled', 'expired') DEFAULT 'requested'");
```

**Down:**
```php
Schema::table('swap_requests', function (Blueprint $table) {
    if (Schema::hasColumn('swap_requests', 'asking_amount')) {
        $table->dropColumn('asking_amount');
    }
    if (Schema::hasColumn('swap_requests', 'money_direction')) {
        $table->dropColumn('money_direction');
    }
});

DB::statement("ALTER TABLE swap_requests MODIFY status ENUM('requested', 'countered', 'awaiting_payment', 'accepted', 'rejected', 'cancelled') DEFAULT 'requested'");
```

---

#### Migration 2: create_swap_negotiation_events_table
**File:** `database/migrations/2026_04_18_000002_create_swap_negotiation_events_table.php`

**Purpose:** Immutable timeline of all offer/counter/accept/reject/cancel actions.

**Schema:**
```sql
CREATE TABLE swap_negotiation_events (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  swap_request_id BIGINT NOT NULL FOREIGN KEY,
  actor_id BIGINT NOT NULL FOREIGN KEY (users.id),
  event_type ENUM('initial_offer', 'counter_offer', 'accept', 'reject', 'cancel') NOT NULL,
  offered_product_id BIGINT NULLABLE FOREIGN KEY,
  offered_amount DECIMAL(10, 2) NULLABLE,
  asking_amount DECIMAL(10, 2) NULLABLE,
  message TEXT NULLABLE,
  metadata JSON NULLABLE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (swap_request_id),
  INDEX (actor_id),
  INDEX (created_at)
);
```

---

#### Migration 3: create_swap_order_confirmations_table
**File:** `database/migrations/2026_04_18_000003_create_swap_order_confirmations_table.php`

**Purpose:** Track dual confirmation status (both parties must confirm received items).

**Schema:**
```sql
CREATE TABLE swap_order_confirmations (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  swap_request_id BIGINT NOT NULL UNIQUE FOREIGN KEY,
  owner_confirmed_at TIMESTAMP NULLABLE,
  owner_notes TEXT NULLABLE,
  requester_confirmed_at TIMESTAMP NULLABLE,
  requester_notes TEXT NULLABLE,
  final_completed_at TIMESTAMP NULLABLE,
  auto_expired_at TIMESTAMP NULLABLE COMMENT 'If one party does not confirm in X days, mark expired',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (swap_request_id)
);
```

---

#### Migration 4: add_swap_email_sent_tracking
**File:** `database/migrations/2026_04_18_000004_add_swap_email_sent_tracking.php`

**Purpose:** Track order email notifications sent to both parties.

**Changes:**
```php
Schema::table('swap_requests', function (Blueprint $table) {
    if (!Schema::hasColumn('swap_requests', 'order_details_sent_at')) {
        $table->timestamp('order_details_sent_at')->nullable()->after('countered_at');
    }
});
```

---

### 1.2 Models

#### Update: App/Models/SwapRequest.php

**Changes:**
- Add `asking_amount`, `money_direction`, `order_details_sent_at` to `$fillable`.
- Add relations:
  - `negotiationEvents()` HasMany SwapNegotiationEvent
  - `orderConfirmation()` HasOne SwapOrderConfirmation
- Add accessor/mutator for money direction helper.

**File content snippet:**
```php
protected $fillable = [
    'product_id', 'offered_product_id', 'owner_id', 'requester_id',
    'offered_amount', 'asking_amount', 'message', 'counter_message',
    'counter_amount', 'countered_at', 'reserved_until', 'order_details_sent_at',
    'money_direction', 'status',
];

public function negotiationEvents()
{
    return $this->hasMany(SwapNegotiationEvent::class);
}

public function orderConfirmation()
{
    return $this->hasOne(SwapOrderConfirmation::class);
}

public function getMoneyFlowAttribute()
{
    return match($this->money_direction) {
        'owner_asks_cash' => "Owner asks Rs. {$this->asking_amount}",
        'requester_offers_cash' => "Requester offers Rs. {$this->offered_amount}",
        default => 'No cash involved',
    };
}
```

---

#### Create: App/Models/SwapNegotiationEvent.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwapNegotiationEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'swap_request_id', 'actor_id', 'event_type',
        'offered_product_id', 'offered_amount', 'asking_amount',
        'message', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function swapRequest()
    {
        return $this->belongsTo(SwapRequest::class);
    }

    public function actor()
    {
        return $this->belongsTo(User\User::class, 'actor_id');
    }
}
```

---

#### Create: App/Models/SwapOrderConfirmation.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwapOrderConfirmation extends Model
{
    protected $fillable = [
        'swap_request_id', 'owner_confirmed_at', 'owner_notes',
        'requester_confirmed_at', 'requester_notes', 'final_completed_at',
        'auto_expired_at',
    ];

    protected $casts = [
        'owner_confirmed_at' => 'datetime',
        'requester_confirmed_at' => 'datetime',
        'final_completed_at' => 'datetime',
        'auto_expired_at' => 'datetime',
    ];

    public function swapRequest()
    {
        return $this->belongsTo(SwapRequest::class);
    }

    public function getBothConfirmedAttribute()
    {
        return $this->owner_confirmed_at && $this->requester_confirmed_at;
    }
}
```

---

## Phase 2: Core Controllers & Payment Logic (Weeks 2–3)

### 2.1 SwapRequestController Updates

**File:** `app/Http/Controllers/User/SwapRequestController.php`

#### Update: `showRequestForm()` method
- **Change:** GET route instead of POST.
- **Route:** Change [routes/web.php](routes/web.php) line 170 to GET.
- **Logic:** Fetch user's swappable products and pass to view.

#### Update: `store()` validation
- **Changes:**
  - Add `money_direction` validation: in:none,owner_asks_cash,requester_offers_cash.
  - If money_direction is owner_asks_cash, require asking_amount.
  - If money_direction is requester_offers_cash, allow optional offered_amount.
  - Offered product is conditionally required (if not money_direction=none, offered_product_id is required).
  - Add inventory check: offered product is available, user owns it, is swappable.

#### New: `acceptWithConfirmation()` method
- Called after payment success.
- Sets status to 'confirmation_pending'.
- Creates SwapOrderConfirmation record.
- Sends order details email.

#### New: `confirmReceived()` method
- Requires auth, requester or owner only.
- Sets owner_confirmed_at or requester_confirmed_at with optional notes.
- If both confirmed, calls `completeSwap()`.

#### New: `completeSwap()` method (private)
- Sets status to 'completed'.
- Sets final_completed_at.
- Triggers fund release via WalletLedgerService.
- Sends completion notification.

#### Update: `rejectCounter()` notification
- Use a new CounterRejected notification (not generic SwapRejected).

---

### 2.2 PaymentController Updates

**File:** `app/Http/Controllers/User/PaymentController.php`

#### Update: `createSwapPayment()` method
- **Change:** Move status to 'paid' in request_payload, not directly update swapRequest.
- **Validation:** Ensure offered_product_id is not null (after Phase 1 clarification).
- **Money calculation:** Handle both asking_amount and offered_amount logic.

#### Update: `completePaymentBySource()` swap branch
- **Old behavior:** Set status to 'accepted' + create Swap record immediately.
- **New behavior:**
  1. Check swap request status is 'awaiting_payment'.
  2. Set status to 'paid'.
  3. Create SwapOrderConfirmation record.
  4. Send order-details email to both.
  5. Do NOT create Swap record yet.
  6. Return redirect to order details/confirmation page (not dashboard).

#### New: `sendSwapOrderEmail()` method (private, or in a service)
- Template: `mails.SwapOrderCreated` (new, similar to OrderCreated).
- Recipients: owner + requester.
- Content: product details, money terms, instructions to confirm received.

**Implementation in SwapRequestController:**

```php
private function sendSwapOrderEmail(SwapRequest $swapRequest): void
{
    // Send to both parties with context-aware content
    Mail::to($swapRequest->owner->email)->queue(
        new SwapOrderCreated($swapRequest, role: 'owner')
    );
    
    Mail::to($swapRequest->requester->email)->queue(
        new SwapOrderCreated($swapRequest, role: 'requester')
    );
}
```

**New Mail Class: `app/Mail/SwapOrderCreated.php`** (reference OrderCreated pattern)

```php
<?php

namespace App\Mail;

use App\Models\SwapRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SwapOrderCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $swapRequest;
    public $role; // 'owner' or 'requester'

    public function __construct(SwapRequest $swapRequest, string $role = 'owner')
    {
        $this->swapRequest = $swapRequest;
        $this->role = $role;
    }

    public function envelope(): Envelope
    {
        $subject = $this->role === 'owner' 
            ? 'New Swap Offer: ' . $this->swapRequest->offered_product->title
            : 'Your Swap Request: ' . $this->swapRequest->product->title;
            
        return new Envelope(
            subject: $subject,
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.swap-order-created',
            with: [
                'swapRequest' => $this->swapRequest,
                'role' => $this->role,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
```

**New Email Template: `resources/views/emails/swap-order-created.blade.php`** (reference order-created pattern)

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #006a38; color: white; padding: 20px; text-align: center; margin-bottom: 20px; border-radius: 4px; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { background-color: #f9f9f9; padding: 20px; border-radius: 4px; }
        h2 { color: #006a38; margin-bottom: 20px; }
        h3 { color: #006a38; margin-top: 25px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        tr { border: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f3f3f3; }
        td { padding: 12px; text-align: left; }
        td:first-child { font-weight: bold; width: 30%; }
        .footer { margin-top: 30px; padding: 15px; background-color: #f3f3f3; border-left: 4px solid #006a38; border-radius: 4px; }
        .footer p { margin: 0; font-size: 14px; }
        .status-badge { display: inline-block; padding: 5px 10px; background-color: #006a38; color: white; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }} - Swap Offer</h1>
        </div>

        <div class="content">
            <h2>
                @if($role === 'owner')
                    You Received a Swap Offer
                @else
                    Your Swap Request Details
                @endif
            </h2>

            <h3>Swap Overview</h3>
            <table>
                <tr>
                    <td>Swap Status</td>
                    <td><span class="status-badge">{{ ucfirst(str_replace('_', ' ', $swapRequest->status)) }}</span></td>
                </tr>
                <tr>
                    <td>Initiated</td>
                    <td>{{ $swapRequest->created_at->format('M d, Y \a\t g:i A') }}</td>
                </tr>
            </table>

            @if($role === 'owner')
                <h3>Their Item (They're Offering)</h3>
                <table>
                    <tr>
                        <td>Product</td>
                        <td>{{ $swapRequest->offered_product->title }}</td>
                    </tr>
                    <tr>
                        <td>Description</td>
                        <td>{{ $swapRequest->offered_product->description }}</td>
                    </tr>
                    <tr>
                        <td>Condition</td>
                        <td>{{ $swapRequest->offered_product->condition ?? 'Good' }}</td>
                    </tr>
                </table>

                <h3>Your Item (They're Requesting)</h3>
                <table>
                    <tr>
                        <td>Product</td>
                        <td>{{ $swapRequest->product->title }}</td>
                    </tr>
                    <tr>
                        <td>Your Item Value</td>
                        <td>Rs. {{ number_format($swapRequest->product->price, 2) }}</td>
                    </tr>
                </table>
            @else
                <h3>Your Item (You're Offering)</h3>
                <table>
                    <tr>
                        <td>Product</td>
                        <td>{{ $swapRequest->offered_product->title }}</td>
                    </tr>
                    <tr>
                        <td>Description</td>
                        <td>{{ $swapRequest->offered_product->description }}</td>
                    </tr>
                    <tr>
                        <td>Condition</td>
                        <td>{{ $swapRequest->offered_product->condition ?? 'Good' }}</td>
                    </tr>
                </table>

                <h3>Item They're Trading (You're Requesting)</h3>
                <table>
                    <tr>
                        <td>Product</td>
                        <td>{{ $swapRequest->product->title }}</td>
                    </tr>
                    <tr>
                        <td>Item Value</td>
                        <td>Rs. {{ number_format($swapRequest->product->price, 2) }}</td>
                    </tr>
                </table>
            @endif

            @if($swapRequest->money_direction !== 'none')
                <h3>Cash Component</h3>
                <table>
                    <tr>
                        <td>Money Direction</td>
                        <td>
                            @if($swapRequest->money_direction === 'they_ask_cash')
                                They're asking for additional cash
                            @elseif($swapRequest->money_direction === 'we_offer_cash')
                                Offering additional cash
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Amount</td>
                        <td>Rs. {{ number_format($swapRequest->asking_amount ?? $swapRequest->offered_amount, 2) }}</td>
                    </tr>
                </table>
            @endif

            @if($swapRequest->message)
                <h3>Message</h3>
                <p>{{ $swapRequest->message }}</p>
            @endif

            <h3>
                @if($role === 'owner')
                    Their Contact Info
                @else
                    Recipient Contact Info
                @endif
            </h3>
            <table>
                <tr>
                    <td>Name</td>
                    <td>
                        @if($role === 'owner')
                            {{ $swapRequest->requester->name }}
                        @else
                            {{ $swapRequest->owner->name }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Phone</td>
                    <td>
                        @if($role === 'owner')
                            {{ $swapRequest->requester->phone ?? 'Not provided' }}
                        @else
                            {{ $swapRequest->owner->phone ?? 'Not provided' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>
                        @if($role === 'owner')
                            {{ $swapRequest->requester->email }}
                        @else
                            {{ $swapRequest->owner->email }}
                        @endif
                    </td>
                </tr>
            </table>

            <div class="footer">
                <p>
                    @if($role === 'owner')
                        <strong>Next Step:</strong> Review this offer and respond via the {{ config('app.name') }} app. 
                        You can accept, counter-offer, or decline this swap.
                    @else
                        <strong>Next Step:</strong> Awaiting their response to your swap request. 
                        You'll be notified when they accept, make a counter-offer, or decline.
                    @endif
                </p>
                <p style="margin-top: 10px; font-size: 12px;">
                    All communication regarding this swap should happen through {{ config('app.name') }} app for your safety and protection.
                </p>
            </div>

            <div style="margin-top: 40px; text-align: center; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #888;">
                <p style="margin: 5px 0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
```

**Called in PaymentController after payment success:**

```php
// In completePaymentBySource() swap branch, after creating SwapOrderConfirmation:
$this->sendSwapOrderEmail($swapRequest);
```

**Called again in SwapRequestController after both parties confirm:**

```php
// In completeSwap() method:
$swapRequest->owner->notify(new SwapCompletedNotification($swapRequest));
$swapRequest->requester->notify(new SwapCompletedNotification($swapRequest));

// Plus final summary email (optional, or included in notification)
// $this->sendSwapOrderEmail($swapRequest); // For completion summary
```

---

### 2.3 WalletLedgerService Updates

**File:** `app/Services/WalletLedgerService.php` (likely already exists)

#### New: `releaseSwapFunds()` method
- **Input:** SwapRequest id.
- **Logic:**
  1. Fetch swap and payment.
  2. Credit owner_a: seller_amount (product value + any top-up cash they received).
  3. Credit platform: platform_amount.
  4. Credit owner_b if applicable (return any escrow).
  5. Log transaction type: 'swap_completion'.

---

### 2.4 Console/Scheduler Updates

**File:** `routes/console.php`

#### Update: `inventory:cleanup-expired-reservations` command
- **New logic:** Also check swap_order_confirmations.
  - If awaiting_payment status has reserved_until > now, release and mark expired.
  - If confirmation_pending > X days (e.g., 7 days), one party hasn't confirmed → auto-expire, release funds, notify both.

---

## Phase 3: Routes (Week 2)

### 3.1 Route Changes

**File:** `routes/web.php`

**Old → New:**

1. Line 170: `Route::post('/swap/request/{product}', ...)` 
   - → `Route::get('/swap/request/{product}', ...)`

2. Add: Show swap order confirmation page (after payment)
   - `Route::get('/swap/{swapRequest}/confirmation', [SwapRequestController::class, 'confirmation'])->name('swap.confirmation');`

3. Add: Dual confirmation endpoints
   - `Route::post('/swap/{swapRequest}/confirm/received', [SwapRequestController::class, 'confirmReceived'])->name('swap.confirm.received');`

4. Keep existing: counter offer, reject, cancel as-is (but test middleware).

---

## Phase 4: Forms & Validation (Week 3)

### 4.1 Redesigned Swap Request Form

**File:** `resources/views/swaps/create.blade.php`

**Sections:**
1. **Target Product Display** (read-only).
   - Image, title, price, description, owner.

2. **Your Offer Section** (3 tabs or progressive disclosure).
   - Tab A: "Fair trade (no cash)" → Hidden fields, just confirm.
   - Tab B: "I offer cash" → offered_amount input.
   - Tab C: "I ask for cash" → asking_amount input.
   - (User selects via radio or tab; JS hides/shows accordingly).

3. **Your Product Section** (if offering item).
   - Dropdown: Your products (only swappable, available).
   - Shows selected product image + price inline.
   - Validation message if product is unavailable/not swappable.

4. **Message/Notes Section**.
   - Textarea: "Explain condition or why you want this swap."

5. **Submit Button**.
   - Disabled until valid.
   - Shows error badges if: no product chosen, cash fields invalid, etc.

**Validation (client-side + server-side):**
- If offering/asking cash: amount >= 0, <= user balance (for asking_amount check).
- If trading product: product exists, user owns it, is available, is swappable, not same as target.
- Message: max 2000 chars.

---

### 4.2 Swap Confirmation Page (After Payment)

**File:** `resources/views/swaps/confirmation.blade.php`

**Content:**
1. **Order Summary** (like checkout receipt).
   - Your product (image + title).
   - Their product (image + title).
   - Cash flow (if any): "You ask for Rs. 500" or "You offer Rs. 500".
   - Total terms.

2. **Order Details** (like shipping order).
   - Swap ID, date, status.
   - Both parties' names and contact (masked for privacy, but available for handover).

3. **Next Steps**.
   - "Handover items with the other party."
   - "Once received, confirm via the button below."
   - "Both must confirm for completion."

4. **Confirmation Section**.
   - Text area: "Notes about receipt (e.g., "Item received in good condition")."
   - Submit: "Confirm I received the item."
   - Shows confirmation status for both parties (✓ or ⏳).

5. **Contact Other Party** (optional button).
   - Opens a safe contact form (pre-filled with swap details).
   - Not direct messaging, but a nudge system.

---

### 4.3 Updated Counter Offer Form

**File:** `resources/views/swaps/show.blade.php` (section expand)

**Changes:**
- Counter offer form now shows both `counter_amount` and `counter_message`.
- Client validates that counter_amount is different from original offered_amount (can't just echo).
- Validation: counter_amount >= 0, message <= 2000 chars.
- Add hint: "Counter with a different cash amount or message."

---

## Phase 5: Views & Dashboard (Week 3–4)

### 5.1 Dashboard Tabs for Swaps

**File:** `resources/views/layouts/dashboard.blade.php` + new swap sections

**Navigation:**
- Main: "Swaps" (new top-level or under transactions).
- Tabs:
  1. Incoming Requests (owner perspective).
  2. Outgoing Requests (requester perspective).
  3. Pending Confirmations (in-progress swaps awaiting both to confirm).
  4. Completed Swaps.
  5. Cancelled/Expired.

---

### 5.2 Incoming Requests View

**File:** `resources/views/swaps/requests.blade.php` (update)

**Filter logic:**
- Show status = 'requested' (initial offers only, no counters).
- Add expandable: "See negotiation history" (timeline of events).

**Actions available per status:**
- status='requested' → Accept, Reject, Counter.
- status='countered' (owner view) → Requester countered, show their offer, can re-counter or accept their counter.

---

### 5.3 Outgoing Requests View

**New file:** `resources/views/swaps/my_requests.blade.php`

**Perspective:** Requester (who initiated).

**Sections:**
1. Pending (awaiting owner response).
2. Countered (owner sent counter; I can accept/counter/reject).
3. Awaiting Payment (owner accepted; I must pay).
4. Paid (payment done; awaiting confirmation).
5. Confirmation Pending (waiting for both to receive).
6. Completed.

---

### 5.4 Pending Confirmations View

**New file:** `resources/views/swaps/pending_confirmations.blade.php`

**For each swap in confirmation_pending status:**
- Order summary card (like 5.2 but compact).
- Your confirmation status: "Not yet confirmed" or "✓ Confirmed on [date]".
- Other party status: "✓ Confirmed on [date]" or "Awaiting their confirmation".
- Confirm button (if user hasn't confirmed yet).
- Auto-expires in X days banner (if close).

---

### 5.5 Completed & History View

**New file:** `resources/views/swaps/completed.blade.php`

**Shows:**
- Swap details (both products, cash flow).
- Completion date.
- Leave review button.

---

## Phase 6: Notifications & Emails (Week 4)

### 6.1 New Notifications

1. **SwapAccepted** (existing, keep as-is).

2. **SwapCountered** (existing, keep as-is).

3. **SwapPaymentReceivedNotification** (new).
   - Sent to owner when requester pays.
   - Message: "Payment received for your swap. Awaiting confirmation from both parties."

4. **SwapConfirmationRequestNotification** (new).
   - Sent when status moves to confirmation_pending.
   - Alert: "Confirm you received the item from [other party]. Both must confirm to complete."

5. **SwapConfirmedNotification** (new).
   - Sent to other party when one confirms.
   - Message: "Other party confirmed receipt. Awaiting your confirmation."

6. **SwapCompletedNotification** (new).
   - Sent to both when final_completed_at is set.
   - Message: "Swap completed! Funds transferred."

7. **SwapConfirmationExpiredNotification** (new).
   - Sent if auto_expired_at triggers.
   - Message: "Swap confirmation expired (no mutual agreement). Funds released."

---

### 6.2 New Email

**File:** `app/Mail/SwapOrderCreated.php` (new)

**Template:** `resources/views/mails/swap_order_created.blade.php` (new)

**Recipients:** Both owner and requester.

**Content:**
- Subject: "Swap Order #[ID] - Awaiting Confirmation"
- Body:
  - Your product vs. their product (images + titles).
  - Money flow (if any).
  - Instructions: "Handover items and confirm receipt here: [button to confirmation page]."
  - Deadline: "Confirm by [date] or order will auto-expire."

---

## Phase 7: Admin Tools (Week 4–5)

### 7.1 Swap Admin Dashboard

**File:** `app/Http/Controllers/Admin/AdminController.php` (update)

**Add methods:**
- `swapTransactions()` → List all swaps, filter by status, see negotiation history.
- `swapDetail()` → Full swap details with negotiation timeline and confirmation status.
- `forceCompleteSwap()` → Admin can manually confirm both parties (manual intervention).
- `forceExpireSwap()` → Admin can cancel and release funds (dispute resolution).

**Routes:**
```
GET    /admin/swaps
GET    /admin/swaps/{swapRequest}
PATCH  /admin/swaps/{swapRequest}/force-complete
PATCH  /admin/swaps/{swapRequest}/force-expire
```

---

### 7.2 Admin Views for Swaps

**Files (new):**
- `resources/views/admin/swaps/index.blade.php`
- `resources/views/admin/swaps/show.blade.php`

**Features:**
- Negotiation timeline (all events).
- Confirmation status.
- Payment & fund tracking.
- Manual override buttons.

---

## Phase 8: Testing (Weeks 5–6)

### 8.1 Feature Tests

**File:** `tests/Feature/SwapFlow/` (new directory)

1. **SwapCreationTest.php**
   - Test: Can not create swap if offered product doesn't belong to user.
   - Test: Can not create swap with self (same user).
   - Test: Can not create swap if offered product is not swappable.
   - Test: Money direction validation (asking_amount vs offered_amount).
   - Test: Invalid route method (POST vs GET).

2. **SwapNegotiationTest.php**
   - Test: Counter offer appends to negotiation_events table (immutable).
   - Test: Timeline shows all events in order.
   - Test: Counter accept moves to awaiting_payment.

3. **SwapPaymentTest.php**
   - Test: Payment success sets status to 'paid', not 'accepted'.
   - Test: Order confirmation record created after payment.
   - Test: Swap record NOT created until both confirm.
   - Test: Email sent to both parties.

4. **SwapConfirmationTest.php**
   - Test: Owner confirms received (sets owner_confirmed_at).
   - Test: Requester confirms received (sets requester_confirmed_at).
   - Test: Both confirm → status becomes 'completed', funds released.
   - Test: Idempotency: confirming twice is safe.

5. **SwapExpiryTest.php**
   - Test: Unconfirmed swap expires after X days.
   - Test: Auto-expiry releases funds and sends notification.

6. **SwapAuthorizationTest.php**
   - Test: Only owner can confirm owner_confirmed_at.
   - Test: Only requester can confirm requester_confirmed_at.
   - Test: Only owner can see incoming requests.
   - Test: Only requester can see outgoing requests.

---

### 8.2 Unit Tests

**File:** `tests/Unit/Models/SwapRequestTest.php` (update/new)

1. Test SwapRequest relations (negotiationEvents, orderConfirmation).
2. Test SwapRequest money flow accessor.

---

### 8.3 Admin Tests

**File:** `tests/Feature/Admin/SwapAdminTest.php` (new)

1. Test admin can force-complete swap.
2. Test admin can force-expire swap.
3. Test access control (only admin).

---

## Phase 9: Utilities & Helpers (Week 5)

### 9.1 Service: SwapOrderService (new)

**File:** `app/Services/SwapOrderService.php`

**Methods:**

1. `createNegotiationEvent($swapRequest, $actorId, $eventType, $data)`
   - Atomically append event to timeline.

2. `acceptSwapAndInitiatePayment($swapRequest)`
   - Validate state, reserve inventory, set to awaiting_payment.

3. `completeSwapAfterConfirmation($swapRequest)`
   - Create final Swap record, release funds, send notification.

4. `expireSwapConfirmation($swapRequest)`
   - Mark as expired, release funds, notify both.

---

### 9.2 Mail Service Update

Extend existing mail service to include:
- `SwapOrderDetails` template with both-party recipients support.

---

## Phase 10: Database Seeding & Fixtures (Week 5)

### 10.1 Factories

**Update:** `database/factories/SwapRequestFactory.php` (if exists, or create)

**New:** `database/factories/SwapNegotiationEventFactory.php`

**New:** `database/factories/SwapOrderConfirmationFactory.php`

Useful for testing.

---

## Phase 11: Migration & Data Cleanup (Week 6)

### 11.1 Data Migration Script

**File:** `app/Console/Commands/MigrateSwapData.php`

**Purpose:** For existing swap records (if any), backfill:
- negotiation_events table with 'initial_offer' event.
- swap_order_confirmations (mark old swaps as already confirmed to close them out).

---

## Phase 12: Documentation & Deployment (Week 6–7)

### 12.1 Runbook

**File:** `docs/SWAP_IMPLEMENTATION.md`

Sections:
- Business rules recap (item offer, money direction, dual confirmation).
- Database schema changes.
- API/route changes.
- State machine diagram.
- Example user flows (happy path + failure cases).
- Admin intervention playbook.

### 12.2 Deployment Checklist

- [ ] Run migrations in order (1→2→3→4).
- [ ] Update .env (if new config; e.g., SWAP_CONFIRMATION_EXPIRY_DAYS=7).
- [ ] Seed admin/test data.
- [ ] Run feature tests.
- [ ] Manual UAT on staging (create swap, counter, pay, confirm).
- [ ] Check notification emails in a test mailbox.
- [ ] Backup production database.
- [ ] Deploy to production.
- [ ] Monitor error logs.
- [ ] Smoke test: one real user through full flow.

---

## Summary: Files to Create/Update

### Create (New Files)
1. `database/migrations/2026_04_18_000001_adjust_swap_requests_table.php`
2. `database/migrations/2026_04_18_000002_create_swap_negotiation_events_table.php`
3. `database/migrations/2026_04_18_000003_create_swap_order_confirmations_table.php`
4. `database/migrations/2026_04_18_000004_add_swap_email_sent_tracking.php`
5. `app/Models/SwapNegotiationEvent.php`
6. `app/Models/SwapOrderConfirmation.php`
7. `app/Mail/SwapOrderCreated.php`
8. `app/Services/SwapOrderService.php`
9. `app/Console/Commands/MigrateSwapData.php`
10. `resources/views/swaps/confirmation.blade.php`
11. `resources/views/swaps/my_requests.blade.php`
12. `resources/views/swaps/pending_confirmations.blade.php`
13. `resources/views/swaps/completed.blade.php`
14. `resources/views/mails/swap_order_created.blade.php`
15. `resources/views/admin/swaps/index.blade.php`
16. `resources/views/admin/swaps/show.blade.php`
17. `tests/Feature/SwapFlow/SwapCreationTest.php`
18. `tests/Feature/SwapFlow/SwapNegotiationTest.php`
19. `tests/Feature/SwapFlow/SwapPaymentTest.php`
20. `tests/Feature/SwapFlow/SwapConfirmationTest.php`
21. `tests/Feature/SwapFlow/SwapExpiryTest.php`
22. `tests/Feature/SwapFlow/SwapAuthorizationTest.php`
23. `tests/Feature/Admin/SwapAdminTest.php`
24. `tests/Unit/Models/SwapRequestTest.php`
25. `database/factories/SwapNegotiationEventFactory.php`
26. `database/factories/SwapOrderConfirmationFactory.php`
27. `docs/SWAP_IMPLEMENTATION.md`

### Update (Existing Files)
1. `app/Models/SwapRequest.php` — Add relations, fillable, accessors.
2. `app/Http/Controllers/User/SwapRequestController.php` — Replace/add methods per Phase 2.1.
3. `app/Http/Controllers/User/PaymentController.php` — Update swap payment flow per Phase 2.2.
4. `app/Services/WalletLedgerService.php` — Add releaseSwapFunds() method.
5. `routes/web.php` — Change swap form route to GET, add confirmation routes.
6. `routes/console.php` — Update scheduler logic per Phase 2.4.
7. `resources/views/swaps/create.blade.php` — Redesign per Phase 4.1.
8. `resources/views/swaps/show.blade.php` — Update counter form + add confirmation section per Phase 4.3.
9. `resources/views/swaps/requests.blade.php` — Filter logic update per Phase 5.2.
10. `resources/views/layouts/dashboard.blade.php` — Add swaps nav + tabs.
11. `app/Http/Controllers/Admin/AdminController.php` — Add swap admin methods per Phase 7.1.
12. `database/factories/SwapRequestFactory.php` — If exists, update; else create.

---

## Detailed Technical Spec: Swap Fund Holding & Release Mechanism

### Overview
The implementation uses the existing WalletLedgerService to implement escrow-until-confirmation. Funds are held after payment success and only released when both parties confirm receipt.

### Fund Holding Flow (Phase 2.2: Payment Success)

**eSewa/Khalti Callback → `completePaymentBySource()` swap branch**

```
1. Payment gateway returns success (status = COMPLETE)
2. Verify signature & amount match
3. In DB transaction:
   a. Set payment.status = 'complete'
   b. Set swap_request.status = 'paid' (NOT 'accepted' yet!)
   c. Calculate amounts:
      - seller_amount = product.price + offered_amount (if requester offers cash)
      - platform_amount = service_fee (deducted from total)
   d. Store amounts in Payment.request_payload for audit
   e. Create SwapOrderConfirmation record (empty, awaiting confirmations)
   f. Send order-details email to both parties
   g. DO NOT create Swap record yet
   h. DO NOT credit wallets yet
4. Redirect to /swap/{id}/confirmation page (show summary + confirm button)
```

**Result:** Funds remain in Payment ledger escrow. Wallet balances unchanged.

### Fund Release Flow (Phase 2.1 & 2.3: Dual Confirmation)

**Confirmation Sequence**

```
Owner confirms:     swap_order_confirmation.owner_confirmed_at = now()
Requester confirms: swap_order_confirmation.requester_confirmed_at = now()

When both set:
  → Call SwapOrderService::completeSwapAfterConfirmation($swapRequest)
  → Call WalletLedgerService::releaseSwapFunds($swapRequest)
```

**Release Implementation (New in WalletLedgerService)**

```php
public function releaseSwapFunds(SwapRequest $swapRequest): void
{
    DB::transaction(function () use ($swapRequest) {
        // 1. Fetch payment with amounts
        $payment = Payment::lockForUpdate()
            ->find($swapRequest->payment_id);
        if (!$payment || $payment->status !== 'complete') {
            return; // Safety: only release if payment already complete
        }
        
        $sellerAmount = (float) ($payment->seller_amount ?? 0);
        $platformAmount = (float) ($payment->platform_amount ?? 0);
        
        // 2. Credit owner (product owner gets: product value + any requester's top-up)
        if ($sellerAmount > 0) {
            $this->creditSaleIfMissing(
                $swapRequest->owner_id,
                $sellerAmount,
                'swap_completion',
                'payment',
                $payment->id,
                [
                    'swap_request_id' => $swapRequest->id,
                    'money_flow' => $swapRequest->money_direction,
                ]
            );
        }
        
        // 3. Credit platform (capture service fee)
        if ($platformAmount > 0) {
            $this->creditPlatformFeeIfMissing(
                $platformAmount,
                'swap_completion_fee',
                'payment',
                $payment->id,
                ['swap_request_id' => $swapRequest->id]
            );
        }
        
        // 4. Create immutable Swap record (final completion proof)
        Swap::create([
            'swap_request_id' => $swapRequest->id,
            'product_a_id' => $swapRequest->product_id,
            'product_b_id' => $swapRequest->offered_product_id,
            'owner_a_id' => $swapRequest->owner_id,
            'owner_b_id' => $swapRequest->requester_id,
            'offered_amount' => $swapRequest->offered_amount,
            'notes' => $swapRequest->message,
            'status' => 'completed',
        ]);
        
        // 5. Update swap request & confirmation
        $swapRequest->status = 'completed';
        $swapRequest->save();
        
        $swapRequest->orderConfirmation()->update([
            'final_completed_at' => now(),
        ]);
    });
}
```

**Idempotency Guarantee:** `creditSaleIfMissing()` checks if entry already exists for this (payment.id, entry_type, reference_type). Calling twice is safe.

### Auto-Expiry & Manual Intervention (Phase 2.4: Scheduler & Phase 7: Admin)

**Scenario 1: Auto-Expiry After 7 Days**

```php
// In routes/console.php, update inventory:cleanup-expired-reservations
$swapRequests = SwapRequest::where('status', 'confirmation_pending')
    ->with('orderConfirmation')
    ->chunkById(100, function ($requests) use ($inventory, $walletService) {
        foreach ($requests as $request) {
            $confirmation = $request->orderConfirmation;
            
            if ($confirmation->auto_expired_at) {
                continue; // Already processed
            }
            
            if ($confirmation->created_at->addDays(7)->isPast()) {
                // Auto-expire
                $walletService->releaseSwapFunds($request);
                $confirmation->auto_expired_at = now();
                $confirmation->save();
                
                // Notify both parties
                $request->owner->notify(new SwapConfirmationExpiredNotification($request));
                $request->requester->notify(new SwapConfirmationExpiredNotification($request));
            }
        }
    });
```

**Scenario 2: Admin Force-Complete (Dispute Resolution)**

```php
// In AdminController
public function forceCompleteSwap(SwapRequest $swapRequest, Request $request)
{
    $this->authorize('admin');
    
    $data = $request->validate([
        'admin_note' => 'required|string|max:500',
    ]);
    
    DB::transaction(function () use ($swapRequest, $data) {
        // Release funds
        $this->walletService->releaseSwapFunds($swapRequest);
        
        // Log action
        WalletLedgerEntry::create([
            'wallet_id' => $swapRequest->owner->wallet->id,
            'direction' => 'credit',
            'entry_type' => 'swap_admin_intervention',
            'amount' => 0, // No additional credit, just logging
            'description' => "Admin force-completed swap {$swapRequest->id}",
            'metadata' => ['note' => $data['admin_note']],
            'created_by' => Auth::id(),
        ]);
        
        // Send notification
        $swapRequest->owner->notify(new SwapCompletedNotification($swapRequest));
        $swapRequest->requester->notify(new SwapCompletedNotification($swapRequest));
    });
    
    return back()->with('success', 'Swap force-completed and funds released.');
}

public function forceExpireSwap(SwapRequest $swapRequest, Request $request)
{
    $this->authorize('admin');
    
    $data = $request->validate([
        'reason' => 'required|string|max:500',
    ]);
    
    DB::transaction(function () use ($swapRequest, $data) {
        // Release funds (both parties get back what they had)
        $this->inventory->releaseSwapReservation($swapRequest);
        
        // Set to expired
        $swapRequest->status = 'expired';
        $swapRequest->save();
        
        $swapRequest->orderConfirmation()->update([
            'auto_expired_at' => now(),
        ]);
        
        // Notify
        $swapRequest->owner->notify(new SwapExpiredNotification($swapRequest, $data['reason']));
        $swapRequest->requester->notify(new SwapExpiredNotification($swapRequest, $data['reason']));
    });
    
    return back()->with('success', 'Swap expired, funds and inventory released.');
}
```

### Safety & Audit Guarantees

| Scenario | Safe? | How |
|----------|-------|-----|
| Double payment callback | ✅ | `creditSaleIfMissing()` is idempotent; second call is no-op |
| Funds lost if one party absent | ✅ | Auto-expiry after 7 days releases funds & notifies both |
| Admin can manually settle | ✅ | Force-complete/force-expire methods in admin panel |
| Wallet audit trail | ✅ | All entries logged in WalletLedgerEntry with full metadata |
| Inventory not restored on release | ✅ | `inventory->releaseSwapReservation()` restores qty + status |
| Payment idempotency | ✅ | Payment record locked during release transaction |

---

## Execution Order (Recommended)

**Week 1:**
- Create all 4 migrations.
- Create SwapNegotiationEvent + SwapOrderConfirmation models.
- Update SwapRequest model.

**Week 2:**
- Update SwapRequestController (phases 2.1).
- Update PaymentController (phase 2.2).
- Update routes (phase 3).
- Create service SwapOrderService (phase 9.1).

**Week 3:**
- Update + redesign forms (phase 4).
- Begin view updates (phase 5.1–5.3).
- Update WalletLedgerService (phase 2.3).

**Week 3–4:**
- Finish dashboard views.
- Create notification classes.
- Create mail template.

**Week 4–5:**
- Create admin tools (phase 7).
- Begin feature tests.
- Update scheduler (phase 2.4).

**Week 5–6:**
- Finish all tests.
- Create factories + seeding.
- Create data migration script.

**Week 6–7:**
- Docs + deployment checklist.
- Staging UAT.
- Production deployment.

---

## Success Criteria

✅ **Phase 1 Complete:** All migrations run, models created, existing data migrated.

✅ **Phase 2–3 Complete:** Swap flow is atomically safe; payment doesn't auto-complete until both parties confirm.

✅ **Phase 4 Complete:** Forms are intuitive; validation is solid (no edge cases slip through).

✅ **Phase 5 Complete:** Dashboard shows full lifecycle; user confusion is minimized.

✅ **Phase 6–7 Complete:** Notifications are timely; admin can intervene safely.

✅ **Phase 8 Complete:** Feature tests pass; new behavior is locked in.

✅ **Claims are now OBJECTIVELY TRUE:**
- ✅ Swap offer creation is complete and reachable.
- ✅ Negotiation history is immutable.
- ✅ Mutual acceptance (confirmation from both) + funds released.
- ✅ Dashboard shows full incoming, outgoing, pending, completed lifecycle.
- ✅ Order-style details sent to both parties.
- ✅ Solid validation in forms and controllers.

---

## Effort Estimate

- **Total Dev Time:** ~200–250 hours (1 full-time dev, 6–7 weeks with code review + UAT).
- **Breakdown:**
  - DB + Models: ~20 hrs.
  - Controllers + Services: ~40 hrs.
  - Routes + Forms: ~30 hrs.
  - Views: ~50 hrs.
  - Notifications + Emails: ~20 hrs.
  - Admin: ~15 hrs.
  - Tests: ~50 hrs.
  - Docs + Deployment: ~15 hrs.

---

End of Plan
