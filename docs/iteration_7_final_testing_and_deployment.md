# 12. Final Iteration — Testing, Documentation and Deployment

## 12.1 Iteration Overview

The final iteration was executed between February 2026 and April 2026. The emphasis of this phase deliberately shifted away from the implementation of core features toward rigorous system validation, comprehensive documentation formatting, and final project delivery. While minor feature refinements were completed for the production version, the primary goal was to perform a full lifecycle suite of tests across the entire platform, systematically fix bugs discovered during this testing phase, finalize all report documentation, prepare the final academic presentation, and deliver the finalized product ahead of the April 2026 deadline. Overall scope was strictly controlled during this phase; rigid feature-freezes were enforced so that the quality of the deliverable report and the stability of the platform were not sacrificed to accommodate late-stage scope creep.

## 12.2 Planned and Completed Scope

| Planned Task | Outcome | Status |
|---|---|---|
| Conduct full test suite (unit, integration, system, UAT, security) | Completed. All test levels executed; detailed validation results are documented within their respective iteration sections. | Complete |
| Identify and resolve defects discovered during testing | Completed. All critical and major defects were fully resolved; minor cosmetic anomalies logged in the defect tracker. | Complete |
| Prepare complete project documentation | Completed. All chapters of the final project report produced, peer-reviewed, and rigorously formatted. | Complete |
| Prepare and rehearse final project presentation | Completed. Presentation deck formulated and live demonstration environment successfully provisioned. | Complete |
| Submit completed project | Completed. Project formally submitted successfully ahead of the final deadline. | Complete |

Table 12.2.1 — Final Iteration Planned vs. Completed Scope

## 12.3 Testing Strategy

Testing was conducted incrementally throughout all developmental iterations, with each distinct phase subject to its own immediate verification protocols as documented in preceding sections. The final iteration consolidated these modular efforts into a rigid system-level and user acceptance testing framework, summarized in Table 12.3.1. All foundational test cases and granular results are catalogued directly within the respective iteration documents.

| Test Level | Scope | Method |
|---|---|---|
| Black-box Testing | All functional requirements across every phase | Manual test cases specifying exact inputs and expected deterministic application outputs |
| White-box Testing | Rental availability locks, mutual swap agreements, payment verification | Internal architectural state inspection and programmatic boundary condition checks |
| Integration Testing | End-to-end transaction sequences across all three modes with API payments | Manual scripted behavioral flows successfully exercising deeply interconnected modules |
| Security Testing | RBAC permission scopes, CSRF protection, transactional API signature verification | Direct URL forced-access attempts, session manipulation, and payment gateway replay verification |
| User Acceptance Testing | Platform UX/UI organically assessed by the representative target demographic | Structured task-based observational testing utilizing five distinct volunteer participants |

Table 12.3.1 — Consolidated Testing Strategy

### 12.3.1 System and Integration Test Cases (Final Pass)

While component-level test cases (TC-01 through TC-38) were validated in previous iterations, the final iteration executed a concluding suite of end-to-end integration and system resilience tests. These specific tests verify that no regressions occur across distinct modules when chained together, and validate the platform's readiness for final production deployment. 

| ID | Parameter | Scenario | Expected Outcome | Actual Outcome | Status |
|---|---|---|---|---|---|
| TC-39 | E2E Integration | Complete full lifecycle: User registers, verifies email, filters products, executes eSewa checkout, and leaves a verified rating in one continuous flow. | Cross-module state transitions execute perfectly; sessions persist cleanly without UI state pollution. | As expected | Pass |
| TC-40 | Concurrency Limits | Two separate accounts simultaneously attempt final checkout on a singular remaining available product using Khalti and eSewa concurrently. | Pessimistic locking triggers successfully; the first gateway transaction commits while the second gracefully rejects due to unavailable quantity. | As expected | Pass |
| TC-41 | Security (XSS / SQLi) | Malicious user submits deeply nested JSON payloads and HTML `<script>` injections directly into the product creation and messaging forms. | Laravel sanitization and strict Eloquent parameter binding intercept payloads; content safely encoded as plaintext rendering harmlessly. | As expected | Pass |
| TC-42 | Security (Replay) | Simulated external attacker repeatedly POSTs a previously valid historical Khalti payment success callback to the system endpoints. | Idempotency validations instantly recognize the previously registered transaction ID and reject subsequent payloads seamlessly. | As expected | Pass |
| TC-43 | Fault Tolerance | The primary WebSocket (Pusher) daemon experiences temporary simulated blackout during a critical mutual swap negotiation. | Event horizon degradation handled correctly; UI falls back gracefully to polling the persistent database notification queue instead. | As expected | Pass |
| TC-44 | Session Recovery | Administrator explicitly invalidates active security tokens exactly as a user is returning from third-party gateway redirect URL. | Auth-layer intercepts the callback appropriately; pending financial state held in suspension until user successfully re-authenticates. | As expected | Pass |
| TC-45 | Production Load | System subjected to rapid, concurrent structural querying simulating 100 concurrent discovery searches on the FULLTEXT index. | Server load dissipates effectively; P95 response times remain well beneath the NFR-01 2-second baseline parameter without degrading. | As expected | Pass |

Table 12.3.2 — Final System Integration Test Matrix

## 12.4 User Acceptance Testing

User acceptance testing (UAT) was executed utilizing a cohort of five volunteers strictly selected from the target user demographic (students and young urban professionals in Nepal). Participants were instructed to execute a predefined set of isolated tasks on the production platform entirely unaided. These tasks specifically evaluated the primary functional workflows of the application. The observational outcomes are detailed below.

| P# | Task | Completed | Observations |
|---|---|---|---|
| P1 | Register an account and successfully publish a product listing | Yes | No difficulties encountered; the multi-image upload procedure was highly intuitive. |
| P2 | Submit a rental request for an actively available product | Yes | The deposit value distinction was initially ambiguous; participant requested minor UI clarification. |
| P3 | Propose a bidirectional swap offer for another user's asset | Yes | Participant navigated the negotiation interface effortlessly after comprehending the internal action labels. |
| P4 | Add a product to the cart and proceed through eSewa checkout | Yes | Participant required a brief explanation of sandbox authentication credentials for testing the integrated gateway. |
| P5 | Employ the search bar and category filters to efficiently locate an item | Yes | Search queries returned instantly; simultaneous filter overlay applications functionally chained perfectly. |
| P6 | Submit a formal dispute report utilizing the post-transaction interface | Yes | The dispute action button was securely located; explicit confirmation modals were noted as highly reassuring. |

Table 12.4.1 — User Acceptance Testing Results

## 12.5 Defect Log

Table 12.5.1 chronicles the specific defects uncovered during the final testing matrix, categorizing the severity and detailing the deployed architectural resolution. All critical and major severity bugs were definitively resolved prior to systemic submission.

| ID | Severity | Description | Module | Status | Resolution |
|---|---|---|---|---|---|
| BUG-01 | Major | Availability bounds mistakenly rejected valid bookings starting on the precise day a prior rental ended | Rental System | Resolved | Logical comparison operators accurately refactored from `<=` to strict `<` / `>` boundaries |
| BUG-02 | Major | Active counter-offer submission failed to inherently broadcast socket notifications to the initial coordinator | Swap Mechanism | Resolved | Explicit `SwapCountered` Pusher event broadcast functionally injected into the handling controller |
| BUG-03 | Minor | Listings traversing without a provided image rendered a visually broken interface element | Product Listing | Resolved | A default aesthetic placeholder injected conditionally directly within the Blade template pipeline |
| BUG-04 | Minor | Global pagination controls silently stripped active URL filter queries across page changes | Search Engine | Resolved | Logical `withQueryString()` successfully appended locally to the internal paginator instance |

Table 12.5.1 — Final Iteration Defect Log

## 12.6 Final Iteration Retrospective

The final iteration flawlessly fulfilled the planned phase deliverables strictly within the allocated timeframe. The final engineering effort was strategically triaged based on explicit defect severity classifications. By aggressively targeting critical operational vulnerabilities and cataloging minor visual issues for streamlined deployment, the testing workload was efficiently optimized. The top four primary functional defects were absolutely eradicated before final project delivery, and critically, no underlying architectural vulnerabilities were identified during the intensive security testing pass.

Assessing the undertaking comprehensively, adopting an iterative developmental lifecycle architecture proved to be an exceptionally sensible and cohesive organizational framework for a solo academic endeavor. Scoping the application into distinct, highly focused delivery milestones with dedicated retrospectives aggressively anchored the development velocity and decisively prevented feature creep. Evaluated formally against commercial effort metrics utilizing the COCOMO Basic paradigm, the application equated roughly to 51 person-months of sustained commercial labor—quantifying an extremely accurate order of magnitude given this monolithic architecture seamlessly integrates three disparate transaction modes, two separate external financial gateways, a real-time event-driven notification framework, bespoke architectural Eco-Score gamification, and a securely tiered hierarchical administrative oversight methodology.

---

# 13. Chapter Summary

This chapter has presented a highly comprehensive, meticulous step-by-step account of the iterative development lifecycle underpinning the ReLoop platform. Seven discrete development phases were rigorously documented directly from project inception through to the definitive testing deployment, sequentially detailing the initial baseline authentication matrix, complex product CRUD mechanics, granular rental tracking sequences, bidirectional bartering algorithms, dual-integration payment gateways, robust gamified search mechanisms, protected administrative financial boundaries, and the concluding validation workflows.

Each iteration was deliberately chronicled utilizing a fiercely consistent analytical structure: an explicit statement of objectives, a comparative matrix of intended versus successfully completed scope scopes, a direct correlation to mapping the established SRS functional parameters, deep-dive explorations investigating critical architectural design methodologies, tangible algorithmic implementation excerpts, formal tabular structures explicitly verifying executed test scenarios against outcomes, visual interface proofing, and a concluding retrospective assessment isolating systemic wins alongside developmental friction points. This exhaustive structure ensures the chapter perfectly mirrors both an exacting codebase technical schematic and a completely transparent chronological engineering journal.

Table 13.1.1 presents an essential consolidated macroscopic summary spanning the entirety of the developmental iterations for immediate reference.

| Phase | Principal Deliverable | Key Design Decision | Test Cases | Notable Outcome |
|---|---|---|---|---|
| Init. | SRS, architectural schemas, technology stack selection | MoSCoW metric prioritisation; Laravel 11 selected | — | Stable systemic requirements maintained flawlessly throughout |
| Iter. 1 | Custom authentication layers and strict RBAC middleware | Three dedicated middleware interceptors; granular enum roles | TC-01–12 | Bespoke implementation ensures entirely transparent baseline security |
| Iter. 2 | Product CRUD workflows, image pipelines, persistent carts | Unified monolothic schema serving disparate transaction boundaries | TC-07–12 | High-utility unified state machine engineered; heavily reused later |
| Iter. 3 | Rental negotiation algorithms and robust availability bounds | Isolated `rental_requests` architecture; locked state transitions | TC-13–18 | Complex temporal overlap collision anomalies gracefully intercepted |
| Iter. 4 | Swap negotiation routing, counter-offers, mutual contracts | Status-bound acceptance gateways; internal messaging layers | TC-19–24 | Row-locking inherently mitigated extreme concurrency race-conditions |
| Iter. 5 | External Khalti/eSewa APIs; Pusher WebSocket integrations | Secured redirect financial lifecycles; strict signature verification | TC-25–30 | Defensive anti-replay cryptographic verifications seamlessly deployed |
| Iter. 6 | Advanced discovery, Eco-Score gamification, Admin tools | FULLTEXT schemas; multi-tiered financial escrow refunds | TC-31–38 | N+1 bottlenecks removed globally; internal administration strictly enforced |
| Final | Rigorous test suites, targeted UAT panels, defect resolution | Granular five-level systemic testing; targeted severity triage | Full Suite | Universal eradication of major internal logic failures prior to deployment |

Table 13.1.1 — Consolidated Development Iteration Matrix
