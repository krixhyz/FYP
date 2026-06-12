# 11. Iteration 6 — Advanced Features and UI Enhancement

## 11.1 Iteration Overview

Iteration 6 was carried out between January and March 2026. Since all core transaction features were established in the previous iterations, this phase concentrated on platform discoverability, user trust, content moderation, interface quality, and gamification. The primary deliverables included a sophisticated keyword search with multi-criteria filtering, a transaction-bounded rating and review system, the full deployment of the Digital Brutalism design language defined in the SRS, and back-end performance enhancements via query tuning and response caching. Crucially, the iteration deployed complex administrative financial workflows—granting `super_admin` identities unparalleled configuration and financial oversight while providing `admin` roles concrete architectural endpoints for user verification, escalated dispute resolutions, and automated payment-gateway deposit refunds. Additionally, this iteration introduced the Eco-Score System, a gamified sustainability metric that mathematically awards users eco-points based on the environmental impact of their swap, rent, and sale transactions, encouraging continued engagement with circular economy practices.

## 11.2 Planned and Completed Scope

| Planned Task | Outcome | Status |
|---|---|---|
| Implement advanced keyword search and multi-criteria filtering | Completed. Search uses conditional query constraints with keyword `LIKE` matching, category filtering, transactional listing type filtering, and price-range filtering, while preserving pagination. | Complete |
| Implement ratings, reviews, and dispute reporting mechanism | Completed. Transaction-bound five-star rating with written review; dispute submission and admin resolution workflow added. | Complete |
| Develop user Eco-Score gamification system | Completed. EcoScoreService calculates condition and transaction multipliers, statistically accumulating points to rank users from Bronze to Platinum. | Complete |
| Apply Digital Brutalism UI styling across all application views | Completed. Consistent design system applied using Space Grotesk typeface, high-contrast blocks, and no rounded corners; all views responsive. | Complete |
| Deploy complex dispute, financial, and RBAC workflows across Admin Dashboard | Completed. Admin safely processes external deposit refunds and verifications, while super_admin secures system configuration and financial data. | Complete |
| Optimize application performance | Completed. Eloquent eager loading applied throughout; response caching added for high-frequency listing queries. | Complete |

Table 11.2.1 — Iteration 6 Planned vs. Completed Scope

## 11.3 Requirements Addressed

This version completed the real-time search refinements of FR-08, providing keyword search with category and transaction type filtering on the home page. It completely addressed FR-26 by enforcing that star ratings and comments can only be submitted after a formally completed transaction. The dispute and resolution requirements (FR-27, FR-31) were satisfied alongside moderation tools allowing admins to flag or delete listings (FR-30). Scheduled routines to clean up orphaned reviews and resolved disputes automatically were added to satisfy FR-28. The administrative oversight tools mandated by FR-29 and FR-32 were delivered through a unified Super Admin dashboard. Additionally, the NFRs regarding system responsiveness (NFR-01, under three-second page loads) and mobile adaptability (NFR-03) were fully implemented across every view. The platform gamification feature (Eco-Score metrics) was added beyond formal SRS requisites to explicitly incentivize ecological platform usage.

## 11.4 Design Decisions

### 11.4.1 Advanced Search Implementation
The search was designed using conditional query constraints within the controller to cleanly implement the FR-08 requirement. The `when()` method of the Laravel query builder was used to structure a chainable filter pipeline. This allows active filters to transparently append their constraints to the base query, keeping the controller concise, avoiding separate complex search functions, making each filter easier to test in isolation, and maintaining a linearly scalable architecture for future additions.

### 11.4.2 Transaction-Bound Rating System
To foster absolute trust within the marketplace, ratings are strictly authorized against a successfully finalised transaction (as per FR-26). Both users are presented with the opportunity to review each other only after a transaction has reached the 'completed' state. Any attempt to access the review form via direct URL without a matching transaction identifier is actively rejected with an HTTP 403 response. The public user rating is automatically and dynamically recalculated as a mean average upon any new review submission.

### 11.4.3 Eco-Score Gamification Architecture
To quantify the environmental benefit of platform transactions seamlessly, an `EcoScoreService` was architected. Rather than assigning arbitrary points per action, the system executes a structured formula: `final_eco_points = base_eco_points × condition_multiplier × transaction_multiplier`. Multipliers are heavily weighted to favor pure trades/swaps (1.2x) over outright sales (1.0x) and rentals (0.6x), dynamically promoting the most robust circular transactions. Accumulating scores securely drive user progression through hierarchical tiers (from Bronze at 500 points to Platinum at 5000 points).

### 11.4.4 Performance Optimisations
Two primary architectural optimisations were adopted to strictly enforce NFR-01 constraints. First, Eloquent eager loading was consistently deployed across all controller methods returning paginated collections of models, neutralizing the N+1 query bottlenecks exposed during profiling via the Laravel Debugbar. Second, high-frequency public queries (such as the main product index) were transparently routed through Laravel's cache facade (featuring a five-minute TTL) with an event-driven invalidation strategy triggered by product lifecycle events entirely on the backend.

### 11.4.5 Hierarchical Role-Based Access Control (RBAC)
To rigorously govern the marketplace, the application formalized a strict administrative hierarchy mapped directly within the `User` architecture. Standard `admin` accounts are deliberately restricted to fundamental content moderation tasks—mediating user disputes, verifying user profiles manually, flagging problematic listings, and suspending violative standard accounts. In contrast, the `super_admin` role receives unrestricted global privileges, unlocking the capacity to view highly sensitive platform financial analytics, execute bulk application operations (e.g., mass-deletions), and provision new administrative staff accounts. This structured bifurcation radically minimizes operational risk while maintaining vital oversight.

## 11.5 Implementation

The underlying logic of this iteration unified search filtering, core transaction metrics, and widespread interface modifications throughout the monolithic application base.

### 11.5.1 Search Query Constraints Pipeline
The following excerpt illustrates the chained filter query executed by the product search endpoint. Each explicit conditional constraint is sequentially applied via the `when()` helper, seamlessly attaching query logic without mutating the fundamental base builder when a parameter is predictably absent.


### 11.5.2 Eco-Score Evaluation Logic
The `EcoScoreService` evaluates platform environmental impacts dynamically. It acts as an integration touchpoint during final settlement, systematically aggregating base category statistics against the explicit wear condition and transaction paradigm, writing an immutable record of the `user_eco_scores` payload and calculating overall user level promotion.


### 11.5.3 Review, Dispute Management and Role-Based Administration
Administrative controls were deployed as heavily protected system workflows segmented directly by authorization logic derived from the authenticated role. The centralized Admin Panel surfaces actionable oversight controls globally, natively routing standard admins to user dispute mediation pipelines and product flagging systems. Conversely, logging in with a `super_admin` identity intuitively unlocks advanced methods directly embedded within the exact same interface—rendering the `contentBulkDelete` mass execution panel, unlocking system role-assignment creation forms, and surfacing entirely unobstructed total-revenue financial charts decoupled from standard view constraints. Simultaneously, the frontend templates of these secured dashboards (alongside the entire public-facing suite) were wholly overhauled using the definitive 'Digital Brutalism' styling standard consisting universally of the Space Grotesk typeface, explicit solid geometric blocking, and completely eliminating rounded UI borders.

### 11.5.4 Complex Dispute and Financial Workflows
The administrative layer (`AdminController`) orchestrates substantial cross-service workflows well beyond standard CRUD interactions:
- **Dispute Escalations:** Standard `admin` accounts mediate baseline user disputes, dictating the favored party upon investigation. However, the system strictly intercepts any dispute involving an actively privileged account (another admin), instantly enforcing a mandated `disputeEscalate` vertical workflow resolvable strictly by a `super_admin`.
- **Deposit Refund Processing:** Admins evaluate the physical condition of returned rentals post-completion (categorizing as `good`, `minor_damage`, or `major_damage`). A specialized `RentalDepositRefundService` intercepts these administrative verdicts to dynamically distribute financial outcomes—either forfeiting deposits, calculating partial deductions favoring the owner, or authorizing 100% full refunds by interacting symmetrically with the active payment gateway APIs.
- **Dynamic Configuration & Identity Management:** The `super_admin` role exclusively accesses the `systemConfig` functions, injecting global dynamic rulesets like active `payment_fee_percent` structures deeply influencing all future frontend checkouts. Conversely, standard `admin`s manage ongoing public security by auditing user profiles manually using the `UserVerificationService`, initiating strict account suspensions, and performing security password resets upon request.

## 11.6 Testing

The functional validation deployed in Iteration 6 deliberately focused on core user-accessible discovery patterns, strictly controlled transaction inputs, UI responsiveness, and verifying the accurate accumulation of Eco-Score math. Only highly pivotal workflows achievable interactively via the final user interface are presented.

| TC | Type | Test Description | Expected Result | Actual Result | Pass/Fail |
|---|---|---|---|---|---|
| TC-31 | Black-box | Execute product search exclusively by a keyword prominently present in application title | Matching listings returned to the dashboard; unrelated products visibly omitted | As expected | Pass |
| TC-32 | Black-box | Apply keyword search, 'rent' transaction type filter, and maximum price boundary simultaneously | View correctly outputs rentals satisfying multiple boundaries; pagination query intact | As expected | Pass |
| TC-33 | Black-box | Submit textual review and star rating dynamically following a genuinely completed transaction | Secure payload transmission; user's average rating recalculated accurately on profile | As expected | Pass |
| TC-34 | Black-box / Security | Attempt unauthorized URL access to the targeted review form avoiding completed transactions | Server-side validation restricts request rendering an HTTP 403; access wholly denied | As expected | Pass |
| TC-35 | Black-box | Complete UI transaction flow to verify backend Eco-Score updates on user dashboard | The transaction seamlessly logs `1.2x` or `1.0x` multipliers correctly updating user's tier progression | As expected | Pass |
| TC-36 | Black-box | Verify responsive interface behavior on a narrow 375px viewport target | Navigation gracefully collapses to interactive menu; elements stack structurally; layout intact | As expected | Pass |
| TC-37 | Black-box | Standard admin mathematically resolves rental dispute configuring partial owner penalty via the dashboard | Wallet systematically awards owner precisely; background gateway API seamlessly refunds the remainder | As expected | Pass |
| TC-38 | Black-box | Standard admin purposefully attempts resolving a dispute involving another internal administrative account | Interface instantly intercepts administrative event rendering mandatory vertical escalation routing requirement | As expected | Pass |

Table 11.6.1 — Iteration 6 UI and Functionality Validation Results

### 11.6.1 Test Cases Evidence

TC-31, TC-32: Intelligent Discovery Queries and Responsiveness

[ INSERT SCREENSHOT — Figure 11.6.1 ]
Search results interface depicting active keyword application alongside multiple stacked pricing and typing filters handling native pagination

TC-33, TC-35: Trusted Moderation Validation and Eco Gamification Check

[ INSERT SCREENSHOT — Figure 11.6.2 ]
Authenticated User Dashboard reflecting recent aggregated review completions and real-time graduated Eco-Score metrics mapping following continuous transactions

TC-34: Administrative Security Block

[ INSERT SCREENSHOT — Figure 11.6.3 ]
Administrative panel rendering platform dispute data juxtaposed with an HTTP 403 trigger rejecting unauthorized platform submissions

TC-37, TC-38: Complex Financial Processing and Escalation Protocol

[ INSERT SCREENSHOT — Figure 11.6.4 ]
Administrative dashboard definitively preventing standard admin resolution on an internal dispute, triggering forced escalation modal requiring super_admin involvement

## 11.7 Iteration Retrospective

Iteration 6 successfully finalised the comprehensive advanced functionality envisioned for the platform. Handling the extensive scope—advanced contextual search, validated transaction reviews, complex automated deposit refund architectures, widespread CSS styling updates, and the untracked Eco-Score gamification logic—demanded significant context-switching between localized front-end visual layers and critical backend database mutations. Interlocking the `RentalDepositRefundService` securely within the administrative dashboard drastically mitigates operational risk by eliminating untracked manual gateway refunds altogether. Similarly, firmly walling off systemic configurations (`systemConfig`) behind the exclusive `super_admin` hierarchy substantially fortified the structural integrity of the entire administrative perimeter. Concurrently, executing the entire Digital Brutalism typography (Space Grotesk) and high-contrast styling transitions natively ended up being markedly more time-consuming than initially assessed; distributing stylistic implementations iteratively in earlier phases would have established a smoother developmental pacing. Ultimately, incorporating the overarching Eco-Score gamification efficiently elevated platform accountability while finalizing technical obligations simultaneously.
