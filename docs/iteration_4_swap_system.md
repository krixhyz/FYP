# 9. Iteration 4 — Swap Transaction System

## 9.1 Iteration Overview

Iteration 4 was carried out between February and April 2026. This iteration delivered the swap transaction mode — the third and final transaction type supported by ReLoop. Unlike a purchase or rental, a swap is a bilateral exchange in which two users agree to trade their products directly, removing money as the primary medium and optionally supplementing the trade with a cash adjustment where item values differ. The iteration focused on the design of the swap data model, the implementation of a bidirectional offer and counter-offer negotiation system, the development of an inventory finalisation workflow, and the delivery of a dedicated swap management section within the user dashboard.

## 9.2 Planned and Completed Scope

| Planned Task | Outcome | Status |
|---|---|---|
| Implement swap offer creation logic | Completed. Swap offer creation with product selection, cash direction options, and server-side validation implemented. | Complete |
| Implement offer and counter-offer functionality | Completed. Counter-offer system with full negotiation history tracking implemented. | Complete |
| Enable mutual acceptance workflow | Completed. Swap processing implemented using a formal status lifecycle; both direct and counter-acceptance paths functional. | Complete |
| Add swap management section to user dashboard | Completed. Outgoing and incoming swap offers displayed with negotiation history and action controls. | Complete |

Table 9.2.1 — Iteration 4 Planned vs. Completed Scope

## 9.3 Requirements Addressed

This iteration fully satisfies FR-05 (users shall be able to propose, counter-propose, and mutually accept product swap offers), which is the primary mandate of the module. The notification system (FR-07) was extended to cover all major swap lifecycle transitions — request submitted, counter-offer issued, offer accepted, and receipt confirmed — dispatched through the Pusher-based infrastructure established in prior iterations. FR-09 was partially addressed by extending the review module so that a Leave Review action becomes available to both parties once a swap reaches the completed state. NFR-01 was addressed by applying eager loading across all swap dashboard queries to eliminate N+1 query patterns when loading product, counterparty, and status data for each swap entry. NFR-02 was satisfied by placing all swap routes behind the authentication middleware and enforcing per-action role validation, ensuring that only the authorised party may perform each operation, with unauthorised attempts returning HTTP 403.

## 9.4 Design Decisions

### 9.4.1 Swap Database Schema

Four tables were designed for the swap system, with each assigned a clearly bounded responsibility. The swap_requests table is the central negotiation entity, housing offer and counter-offer fields, the two participant references, the cash direction indicator, and the current lifecycle status. A separate swaps table is written once at the moment of finalisation and never updated thereafter; it stores the two products, their owners at the time of exchange, and the agreed monetary value as a permanent, immutable snapshot. The swap_negotiation_events table is an append-only audit log in which every state-changing action by either party is recorded as a new, unmodifiable row. The swap_order_confirmations table manages the post-payment phase, holding independent confirmation timestamps per party alongside an auto-expiry timestamp to prevent indefinite holding of funds.

The schema of swap_requests, which governs the negotiation phase, is shown in the table below.

| Column | Data Type | Description |
|---|---|---|
| id | BIGINT (PK) | Auto-incrementing primary key. |
| product_id | FK → products | The target product being requested for swap. |
| offered_product_id | FK → products | The product offered by the requester in return (nullable). |
| owner_id | FK → users | The user who owns the target product. |
| requester_id | FK → users | The user proposing the swap. |
| money_direction | ENUM | Cash flow direction: none, owner_asks_cash, or requester_offers_cash. |
| offered_amount | DECIMAL(10,2) | Cash amount offered by the requester when their product is lower-valued (nullable). |
| asking_amount | DECIMAL(10,2) | Cash amount requested by the owner when the requester's product is lower-valued (nullable). |
| counter_amount | DECIMAL(10,2) | Alternative cash figure proposed in the owner's counter-offer (nullable). |
| counter_message | TEXT | Owner's explanatory message in the counter-offer (nullable). |
| status | ENUM | Current lifecycle state of the swap request. |
| reserved_until | TIMESTAMP | Payment reservation expiry window, set upon acceptance (nullable). |

Table 9.4.1 — swap_requests Table Schema

### 9.4.2 Swap Lifecycle State Machine

The rules governing each swap request are encapsulated in a formal state machine, conforming to FR-05 of the SRS. Transitions are only permitted at the application layer via specific controller actions, and each action is authorised exclusively to the party permitted to perform it. When a requester submits a proposal, the request enters the requested state. The product owner may accept it — advancing the swap to awaiting_payment if a monetary component is involved, or directly to completed if no payment is required — issue a counter-offer that places the request in the countered state, or reject it outright. If a counter is issued, the requester may accept it, which follows the same acceptance path, or reject it, which terminates the negotiation. Where payment is required, a time-limited reservation window (reserved_until) locks both products while the requester completes checkout. Once payment is confirmed, the swap advances to paid and a confirmation record is created. Both parties must then independently confirm receipt, at which point the swap is marked completed and held funds are released. If confirmations are not received within the allotted window, the swap transitions to expired automatically.

[Figure 9.4.1 — State Transition Diagram: Swap Request Lifecycle]

## 9.5 Implementation

The swap module was built to handle the complete swap lifecycle from offer submission through negotiation, payment, and bilateral confirmation. The module is built from four database tables, a SwapOrderService class, an InventoryReservationService class, and a set of dedicated controller actions.

### 9.5.1 Database Structure

The module relies on four interrelated tables, each serving a distinct function:

- **swap_requests**: Manages the negotiation phase. Stores the proposal details — participant references, offered product, cash direction and amounts, counter-offer fields, and a reservation window — as a mutable entity that reflects the current state of the negotiation.
- **swaps**: Provides a permanent, immutable record of each completed exchange. Columns are written once at finalisation and are never updated, ensuring historical records remain accurate regardless of future negotiation schema changes.
- **swap_negotiation_events**: An append-only audit log. Each row records a single event in the negotiation timeline — actor, event type, product referenced, monetary snapshot, and timestamp — and is never modified after insertion.
- **swap_order_confirmations**: Tracks the bilateral receipt confirmation phase for paid swaps, with separate nullable timestamp columns per party, a final completion timestamp, and an auto-expiry timestamp to handle incomplete confirmations.

[Figure 9.5.1 — ER Diagram: Swap System Tables]

### 9.5.2 Offer Creation and Validation

An offered product is required for every swap proposal — a product-only exchange is the minimum valid submission. Beyond the basic type and existence checks, a number of additional validation rules are applied:

- Requesters are blocked from proposing a swap against their own product listing.
- The offered product must belong to the authenticated requester, carry an available status, and hold a positive inventory quantity.
- A requester may not offer the same product that they are requesting.
- The cash direction is validated against a live comparison of both product prices. If the requester's product is lower-valued than the target, the only permitted direction is requester_offers_cash. If the requester's product is higher-valued, the only permitted direction is owner_asks_cash. If both products carry equal prices, no cash adjustment is permitted and the direction must be none. Submitting a direction that contradicts the price relationship is rejected with a specific error message.
- Each direction maps to a dedicated amount field: offered_amount for the requester-pays direction and asking_amount for the owner-requests direction. Populating the wrong field for the selected direction, or populating an amount field when the direction is none, is also rejected.

Upon passing all checks, the swap request is saved and the initial offer is recorded as the first entry in the negotiation event log. The product owner is then notified.

[Figure 9.5.2 — Screenshot: Swap Offer Creation Form]

### 9.5.3 Negotiation and Counter-Offer Lifecycle

When a swap request arrives, the product owner can accept it, reject it, or issue a counter-offer specifying a different cash amount and an explanatory message. The overall negotiation flow, including the payment validation step that applies when a monetary component is involved, is depicted in the activity diagram below.

[Figure 9.5.3 — Activity Diagram: Swap Negotiation and Payment Flow]

Each action taken by either party is recorded in the negotiation event log before the response is returned, ensuring no state transition is left unaccounted for. When submitting a counter-offer, the owner specifies only a cash amount and a message; the cash direction is not chosen by the owner but is instead derived automatically by comparing the listed prices of the two products. This prevents owners from assigning an economically contradictory direction in their counter.

When an offer is accepted — directly or via a counter — exclusive row-level locks are acquired on both participating products before their quantities are checked. This prevents concurrent acceptance requests from driving inventory below zero. If either product is unavailable at the point of lock acquisition, the transaction is rolled back. If both are available, each product's quantity is decremented by one, a terminal status is applied if the quantity reaches zero, and a Swap record is written as a permanent snapshot of the exchange. Where the accepted swap involves a monetary payment, the swap enters awaiting_payment and a reservation window (reserved_until) is set. The party responsible for completing the payment is determined by the money_direction field: when the direction is requester_offers_cash the requester pays, and when it is owner_asks_cash the owner pays. The checkout route enforces this: only the resolved payer may access the payment page. Both parties receive a notification and the payer is redirected to the checkout immediately upon acceptance. Once payment is confirmed, the swap advances to paid and a confirmation record is created for the bilateral receipt phase.

### 9.5.4 Swap Dashboard

A management section within the user dashboard provides tailored views for both parties:

- **Product owners** can review all incoming swap requests in the requested and countered states, with direct action controls (accept, reject, counter) and access to the full negotiation history per request.
- **Requesters** can track all swap proposals they have submitted, view counter-offers, and take the appropriate next action. Completed swaps display a Leave Review prompt.

The dashboard inbox metric surfaces a combined count of all incoming requests in the requested or countered state, giving the owner an at-a-glance indicator of pending obligations without requiring navigation away from the dashboard.

[Figure 9.5.4 — Screenshot: Swap Management Dashboard View]

### 9.5.5 Integration with Existing Modules

The swap module integrates directly with the existing product, payment, notification, and wallet systems. Product quantities are managed through the same InventoryReservationService used by the rental module, ensuring consistent inventory behaviour across all transaction types. Payment processing routes through the established gateway integration, with the swap status updated automatically upon payment confirmation. Wallet fund release upon bilateral confirmation is handled by the WalletLedgerService. Notifications at each lifecycle stage are dispatched using the same notification infrastructure established in prior iterations, extended with swap-specific notification classes for each transition.

## 9.6 Testing

The following test cases cover both black-box functional testing of user-facing flows and white-box testing of state transition guards and concurrency controls. White-box tests were written specifically for the concurrent inventory deduction scenario and for the conditional cash direction validation logic, where internal conditional behaviour required direct inspection.

| TC | Type | Test Description | Expected Result | Actual Result | Pass/Fail |
|---|---|---|---|---|---|
| TC-19 | Black-box | Authenticated user submits a valid swap request with a product and no cash adjustment | Request created with requested status; owner notified; negotiation event recorded | As expected | Pass |
| TC-20 | Black-box | User attempts to propose a swap against their own product | Request rejected with a validation error; no record created | As expected | Pass |
| TC-21 | Black-box | Cash direction set to requester_offers_cash but requester product is higher-valued than target | Submission rejected with a price-direction conflict error | As expected | Pass |
| TC-22 | Black-box | Direction set to none but an amount field is populated | Submission rejected with a descriptive validation error | As expected | Pass |
| TC-23 | Black-box | Product owner issues a counter-offer; direction auto-derived from price comparison | Status updated to countered; money_direction set automatically; requester notified | As expected | Pass |
| TC-24 | Black-box | Requester accepts counter-offer with no monetary amount involved | Both product quantities decremented; Swap record created; status set to completed | As expected | Pass |
| TC-25 | Black-box | Requester accepts counter-offer where owner_asks_cash; owner is the payer | Status set to awaiting_payment; owner redirected to checkout; requester notified | As expected | Pass |
| TC-26 | Black-box | Non-payer attempts to access the checkout page | HTTP 403 returned; no state change | As expected | Pass |
| TC-27 | Black-box | Requester cancels a swap in the countered state | Status set to cancelled; redirect issued | As expected | Pass |
| TC-28 | White-box | Two concurrent requests attempt to accept the same swap simultaneously | Row locking ensures only one succeeds; the second receives a graceful rejection | As expected | Pass |
| TC-29 | Black-box | Both parties independently confirm receipt on a paid swap | Final completion timestamp set; swap marked completed; both parties notified | As expected | Pass |
| TC-30 | Black-box | One party attempts to submit a second confirmation after already confirming | Duplicate rejected with an informative response; no data modified | As expected | Pass |

Table 9.6.1 — Iteration 4 Test Cases and Results

### 9.6.1 Test Cases Evidence

TC-19, TC-23, TC-24: Swap Request, Counter-Offer, and No-Cash Acceptance Flow

[Figure 9.6.1 — Screenshot: Swap request submitted and owner notification received]

[Figure 9.6.2 — Screenshot: Owner counter-offer form — direction auto-derived and displayed]

[Figure 9.6.3 — Screenshot: Swap status set to completed following acceptance with no cash component]

TC-25, TC-29: Paid Swap — Direction-Resolved Payer and Bilateral Confirmation Flow

[Figure 9.6.4 — Screenshot: Checkout page accessed by the direction-resolved payer (owner or requester)]

[Figure 9.6.5 — Screenshot: Confirmation page post-payment with per-party confirmation timestamps]

TC-21, TC-22, TC-26, TC-28: Validation, Authorisation, and Concurrency Controls

[Figure 9.6.6 — Screenshot: Price-direction conflict validation error on offer creation form]

[Figure 9.6.7 — Screenshot: HTTP 403 on non-payer checkout access and concurrent acceptance rejection]

## 9.7 Iteration Retrospective

Iteration 4 successfully delivered the complete swap transaction system across all four planned tasks. The decision to separate the negotiation entity (swap_requests) from the finalised exchange record (swaps) was the correct architectural choice: it kept the schema clean, prevented historical records from being affected by changes to negotiation-phase fields, and produced a reliable filtering basis for the dashboard components. The append-only negotiation event log proved its value immediately during development as a diagnostic tool, providing a complete, actor-attributed history of every state transition, and will serve as the foundation for any future dispute resolution feature built on top of the swap module. The InventoryReservationService, already in place from the rental module, was reused without modification to handle inventory locking during swap acceptance, resolving the concurrent acceptance problem without introducing new infrastructure. The primary area for improvement identified in this iteration is the inline state guard pattern: each controller action contains its own independent status check, which is correct but becomes difficult to maintain consistently as the number of permitted states grows. A centralised state machine class that enumerates all valid transitions in a single location would be the more sustainable design, and is noted as a refactoring priority for a subsequent iteration.
