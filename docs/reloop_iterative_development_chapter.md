# ReLoop: Iterative Development Chapter Draft

## 4. Iterative Development of ReLoop

This chapter describes the iterative development of the ReLoop project from August 2025 to April 2026. The work was organised into one initiation phase, six development iterations, and one final testing and documentation phase. Each iteration followed the same structure: scope, implementation, testing, and retrospective. This approach allowed the project to be delivered incrementally while keeping the codebase stable and testable at every stage.

### 4.1 Iteration Overview

The table below summarises the development timeline and the main deliverable from each phase.

| Phase | Title | Timeline | Progress | Key Deliverable |
|---|---|---|---|---|
| Init. | Project Initiation and Requirements | Aug - Sep 2025 | Complete | Scope document, wireframes, project plan |
| Iter. 1 | Core Setup and Authentication | Sep - Oct 2025 | Complete | Laravel base, custom authentication, RBAC |
| Iter. 2 | Product Listing and Selling | Oct - Nov 2025 | Complete | Product CRUD, image upload, cart and sell flow |
| Iter. 3 | Rental System Implementation | Nov - Dec 2025 | Complete | Rental schema, booking logic, dashboard |
| Iter. 4 | Swap Mechanism Development | Nov - Dec 2025 | Complete | Swap logic, counter-offer flow, acceptance logic |
| Iter. 5 | Payment Gateway and Notifications | Dec 2025 - Jan 2026 | Complete | Khalti/eSewa integration, real-time notifications |
| Iter. 6 | Advanced Features and UI Enhancement | Jan - Feb 2026 | Complete | Search, reviews, disputes, UI styling, admin panel |
| Final | Testing, Documentation and Deployment | Feb - Apr 2026 | Complete | Full test suite, documentation, final submission |

The following sections discuss the phases in sequence.

### 4.2 Testing Methodology

Testing was carried out continuously throughout development. Each iteration included verification before the next iteration began, which reduced regression risk and ensured that working software was available at every stage.

Black-box testing was the primary method used for functional features. Test cases were written from the user perspective and validated the expected system output without relying on internal implementation details.

White-box testing was used for logic-heavy functionality where internal conditions needed direct verification. This included the rental availability overlap check, the swap acceptance and counter-offer logic, and payment verification for both gateways.

Integration testing was performed after each iteration to confirm that new features worked correctly with functionality completed in earlier iterations.

Security testing was applied to route protection, payment callback verification, and replay prevention.

User acceptance testing was conducted informally during the product listing and UI improvement iterations, using representative users to complete realistic tasks and provide feedback.

---

## 5. Project Initiation and Requirements Phase

### 5.1 Phase Overview

The initiation phase took place during August and September 2025. Its purpose was to define the problem space, establish scope, gather requirements, set up the development environment, and produce low-fidelity wireframes for the main screens.

ReLoop was conceived to address a gap in the Nepali student marketplace: existing platforms support resale, but do not provide a unified workflow for selling, renting, and swapping items in one application. Early competitor analysis and informal user feedback confirmed that a combined platform would be practical and useful.

### 5.2 Requirements Gathering and Prioritisation

Requirements were derived through self-appraisal, competitor analysis, and informal interviews with potential users. Each requirement was classified as functional or non-functional and prioritised using the MoSCoW method.

The Software Requirements Specification is provided separately in the report appendix. The most relevant requirements are summarised below.

| ID | Requirement Description | Priority | Category |
|---|---|---|---|
| FR-01 | Users shall be able to register, log in, and manage their profiles. | Must Have | Functional |
| FR-02 | The system shall support role-based access control for regular users, admins, and super admins. | Must Have | Functional |
| FR-03 | Users shall be able to create, edit, and delete product listings with image uploads. | Must Have | Functional |
| FR-04 | The system shall support a complete rental workflow including booking, active rental tracking, and return confirmation. | Must Have | Functional |
| FR-05 | Users shall be able to propose, counter-propose, and mutually accept product swap offers. | Must Have | Functional |
| FR-06 | The system shall integrate Khalti and eSewa payment gateways for sale and rental payments. | Must Have | Functional |
| FR-07 | Users shall receive real-time notifications for transactional events via Pusher. | Should Have | Functional |
| FR-08 | The system shall provide keyword search and multi-criteria filtering for listings. | Should Have | Functional |
| FR-09 | Users shall be able to submit and view ratings and written reviews after completed transactions. | Could Have | Functional |
| NFR-01 | The system shall respond to user interactions within two seconds under normal load. | Must Have | Non-Functional |
| NFR-02 | Passwords shall be hashed securely and sessions managed safely. | Must Have | Non-Functional |
| NFR-03 | The application shall be responsive on desktop and mobile browsers. | Should Have | Non-Functional |

### 5.3 System Design

#### 5.3.1 System Architecture Overview

ReLoop is implemented as a Laravel web application using the MVC architecture. Requests enter through the routing layer, pass through middleware for authentication and role checks, and are then handled by controllers. Controllers coordinate Eloquent model operations, which persist data to MySQL and render Blade views for the user interface.

Two external service layers support the core application. Khalti and eSewa handle payment flows through redirect-based gateway integration with server-side verification. Pusher is used for real-time event delivery through Laravel Broadcasting and private channels.

The architecture therefore consists of the browser, Laravel application server, MySQL database, payment gateways, broadcasting service, and the public storage layer for uploaded files.

#### 5.3.2 Context Diagram

The system interacts with registered users, guests, administrators, super administrators, payment services, notification services, and the database. Users can browse, list, buy, rent, swap, review, and receive notifications. Guests can browse approved public listings. Administrators manage moderation, disputes, products, and users. Super administrators also manage admin accounts and system-level configuration.

#### 5.3.3 Database Design

The final data model uses separate tables for users, categories, products, orders, order items, rental requests, rentals, swap requests, swaps, payments, reviews, disputes, wishlists, and related support tables.

The products table stores listings for all three transaction modes using a JSON type field, a status field, a quantity field, and image-related fields. Later migrations add a category_id foreign key and other product attributes used by the final application.

The reviews table stores transaction-bound reviews linked to orders, rentals, or swaps. The disputes table stores complaints against order, rental, or swap transactions, together with the dispute status and resolution metadata.

#### 5.3.4 Class Diagram

The class model is implemented through Laravel Eloquent relationships rather than a separate inheritance hierarchy. The core entities are User, Product, Order, RentalRequest, Rental, SwapRequest, Swap, Payment, Review, and Dispute. The User model provides role helpers such as isAdmin() and isSuperAdmin(), while Product provides relationships to category, owner, rentals, orders, reviews, and swap requests.

#### 5.3.5 Low-Fidelity Wireframes

Low-fidelity wireframes were prepared for the landing page, registration and login screens, product browsing, product detail pages, rental request forms, swap offer flows, and dashboards. These wireframes defined layout direction without constraining the final visual style.

### 5.4 Development Environment Configuration

ReLoop was built with Laravel 12 and PHP 8.2. The application uses MySQL 8.0, Blade templates, Tailwind CSS, Pusher broadcasting, and the Khalti and eSewa APIs. The project is managed with Git and GitHub and runs locally using Laravel Artisan and a standard PHP development environment.

### 5.5 Phase Retrospective

The initiation phase completed successfully. Requirements were stable, the technology stack was appropriate for the project size, and the wireframes provided a solid foundation for implementation. The staged planning approach reduced uncertainty in the later development iterations.

---

## 6. Iteration 1 - Core Setup and Authentication

### 6.1 Iteration Overview

Iteration 1 took place during September and October 2025. The objective was to establish the project structure, implement custom authentication, and create a role-based access control system that could support the rest of the project.

### 6.2 Planned and Completed Scope

| Planned Task | Outcome | Status |
|---|---|---|
| Initialize Laravel base project and directory structure | Standard MVC structure established with project-specific configuration | Complete |
| Configure MySQL database and create initial migrations | Users table and base schema created through migrations | Complete |
| Implement custom user registration controller | Registration written from scratch with validation and password hashing | Complete |
| Implement custom user login and logout controller | Login and logout implemented using Laravel auth facilities | Complete |
| Implement role-based access control via middleware | Role middleware added for route protection | Complete |
| Create home page layout and base Blade template | Shared layout and reusable UI structure established | Complete |
| Conduct internal review and smoke testing | Authentication and access control manually verified | Complete |

### 6.3 Requirements Addressed

This iteration addressed FR-01, FR-02, and NFR-02 by implementing account creation, login, logout, secure password hashing, session regeneration, and role-based route protection.

### 6.4 Design Decisions

#### 6.4.1 Custom Authentication Controllers

Authentication was implemented manually rather than using a scaffolding package. This gave the project full control over the login flow, reduced generated boilerplate, and made the implementation easier to explain in an academic setting.

#### 6.4.2 Role-Based Access Control Design

The application uses a simple role model with user, admin, and super_admin. The role is stored on the users table, and route protection is enforced through middleware and model helper methods such as isAdmin() and isSuperAdmin().

#### 6.4.3 Password Security

Passwords are hashed by Laravel using the built-in hashing system. Session tokens are regenerated after login, and sessions are invalidated on logout.

### 6.5 Implementation

#### 6.5.1 Registration Flow

Registration uses a dedicated request class for validation and a controller that creates the user, hashes the password, and logs the user in. After registration, the user is redirected to the email verification notice.

#### 6.5.2 Login and Logout Flow

Login validates credentials, starts a session, and redirects the user to the correct dashboard. Logout destroys the active session and regenerates the token.

#### 6.5.3 Role Middleware

Middleware checks the user role before allowing access to protected routes. Regular users cannot access admin-only pages, and admins are prevented from accessing user-only flows where appropriate.

### 6.6 Testing

The authentication layer was manually tested using registration, login, logout, and role-protection scenarios. All cases passed during initial verification.

### 6.7 Iteration Retrospective

Iteration 1 established a stable foundation for the rest of the system. The custom authentication approach required more initial effort than scaffolding, but it gave the project cleaner control over the implementation and stronger alignment with the report narrative.

---

## 7. Iteration 2 - Product Listing and Selling

### 7.1 Iteration Overview

Iteration 2 was delivered during October and November 2025. This iteration introduced the product listing workflow, image uploads, listing management, and the initial shopping cart flow.

### 7.2 Planned and Completed Scope

| Planned Task | Outcome | Status |
|---|---|---|
| Develop product CRUD operations | Full create, read, update, and delete functionality implemented | Complete |
| Enable the selling mechanism with price and listing status | Sellers can publish listings with defined prices | Complete |
| Implement image upload for listings | Multiple uploads supported with validation and filesystem storage | Complete |
| Implement shopping cart and checkout flow | Cart and checkout flow introduced | Complete |
| Conduct basic user testing | Informal feedback collected from proxy users | Complete |
| Apply feedback-based refinements | Usability refinements added to the listing form | Complete |

### 7.3 Requirements Addressed

This iteration fulfilled FR-03 and the selling part of FR-06. It also laid the groundwork for FR-08 through the browse and listing detail pages.

### 7.4 Design Decisions

#### 7.4.1 Unified Product Table

The products table was designed to support all three transaction modes through a JSON type column. This keeps browsing and filtering simple while allowing each transaction mode to use its own workflow tables.

#### 7.4.2 Product Status Model

The product status field supports available, sold, rented, and swapped states. This prevents conflicting transactions from being processed on the same listing simultaneously.

#### 7.4.3 Image Storage Strategy

Uploaded images are stored on the public filesystem disk. Each product stores a main image and an array of additional images, both validated server-side.

### 7.5 Implementation

The listing form validates title, description, category, condition, price, transaction type, quantity, and images. Uploaded images are saved in the public storage directory, and the first image is used as the cover image.

The browse page uses filtered product queries to show available listings, and the detail page displays listing information and uploaded images.

### 7.6 Testing

Black-box testing covered listing creation, validation errors for oversized images, listing updates, deletion, and access control for editing another user's listing.

### 7.7 Iteration Retrospective

Iteration 2 successfully delivered the core listing experience. Early usability feedback improved the description field and image preview experience, which made the listing process more usable in later testing.

---

## 8. Iteration 3 - Rental System Implementation

### 8.1 Iteration Overview

Iteration 3 was delivered during November and December 2025. The focus was the rental workflow, which is the most state-heavy transaction mode in ReLoop.

### 8.2 Planned and Completed Scope

| Planned Task | Outcome | Status |
|---|---|---|
| Design and implement rental database schema | Rental request and rental tables created | Complete |
| Implement rental booking and availability logic | Overlap checks implemented | Complete |
| Implement rental state machine | Rental transitions guarded at the application layer | Complete |
| Build rental dashboard views | Owner and renter dashboards created | Complete |
| Perform integration testing | End-to-end rental flow verified | Complete |

### 8.3 Requirements Addressed

This iteration implemented FR-04 and the rental part of FR-06. Payment finalisation for rentals was connected later when the gateway work was completed.

### 8.4 Design Decisions

The rental system uses a request-and-finalisation model. Users submit rental requests, owners review them, approved rentals are paid for through the payment system, and active rentals are tracked until return or expiry.

The availability check uses strict date-overlap logic so that adjacent rentals are allowed while true conflicts are rejected.

### 8.5 Implementation

Rental requests store the proposed start and end dates, the agreed fee, and the current status. Approved rentals move into the rental tracking table, where the system can track active periods and return status.

The availability logic checks for overlap against existing approved or active rentals before a request is accepted.

### 8.6 Testing

Testing covered migrations, non-overlapping booking, overlapping booking rejection, owner approval, renter activation, and completion flow.

### 8.7 Iteration Retrospective

Iteration 3 delivered the rental workflow successfully. The date-overlap boundary logic required one correction during testing, which improved the reliability of the booking process.

---

## 9. Iteration 4 - Swap Mechanism Development

### 9.1 Iteration Overview

Iteration 4 was carried out in parallel with Iteration 3 during November and December 2025. The feature introduced the swap negotiation workflow.

### 9.2 Planned and Completed Scope

| Planned Task | Outcome | Status |
|---|---|---|
| Implement swap offer creation logic | Swap offer creation completed | Complete |
| Implement offer and counter-offer functionality | Negotiation flow and history tracking implemented | Complete |
| Enable mutual acceptance workflow | Two-party acceptance protection implemented | Complete |
| Add swap management section to dashboard | Incoming and outgoing swap requests displayed | Complete |

### 9.3 Requirements Addressed

This iteration implemented FR-05 and parts of FR-06 relating to swap-supported transactions.

### 9.4 Design Decisions

The swap system stores the offer, the counter-offer, and the final acceptance state in the swap request record. This prevents unilateral completion and preserves the negotiation history.

### 9.5 Implementation

Swap requests can be created, accepted, rejected, countered, and finalised. Where a swap includes cash compensation, payment is deferred until the request is accepted and reserved.

### 9.6 Testing

Testing covered offer creation, counter-offers, mutual acceptance, rejection, and cancellation.

### 9.7 Iteration Retrospective

The main challenge in this iteration was ensuring that the acceptance logic handled multi-step negotiations safely. The final implementation correctly prevents premature completion.

---

## 10. Iteration 5 - Payment Gateway and Notifications

### 10.1 Iteration Overview

Iteration 5 ran from December 2025 to January 2026. It introduced the payment layer and the real-time notification system.

### 10.2 Planned and Completed Scope

| Planned Task | Outcome | Status |
|---|---|---|
| Integrate Khalti payment gateway | Initiation and server-side verification implemented | Complete |
| Integrate eSewa payment gateway | Redirect flow and signature verification implemented | Complete |
| Implement real-time notification system using Pusher | Private channels and broadcasts implemented | Complete |
| Conduct payment workflow testing | Sandbox payment scenarios verified | Complete |
| Implement replay attack prevention | Duplicate callback handling added | Complete |
| Implement notification cleanup command | Notifications older than ten days pruned | Complete |

### 10.3 Requirements Addressed

This iteration implemented FR-06 and FR-07, and it also supported the payment-related parts of rental and swap workflows.

### 10.4 Design Decisions

Payment was implemented as a redirect-based flow. Users complete the payment on the gateway site, and the application then verifies the result server-side before marking the transaction complete.

Both eSewa and Khalti are verified on the server using their respective verification rules. This protects the application from manipulated browser callbacks and replay attempts.

The notification system uses Laravel Broadcasting with Pusher and private channels, allowing users to receive transaction events in real time.

### 10.5 Implementation

The payment controller verifies eSewa responses using the server-side signature check and verifies Khalti returns by looking up the payment with the gateway before confirming completion. If a payment has already been processed, the callback is ignored.

### 10.6 Testing

Testing covered sandbox payments, successful and failed callbacks, duplicate callback protection, and notification delivery.

### 10.7 Iteration Retrospective

Iteration 5 successfully added the two payment gateways and the notification layer. eSewa required more debugging because the sandbox responses were less consistent than Khalti, but the final verification flow was stable.

---

## 11. Iteration 6 - Advanced Features and UI Enhancement

### 11.1 Iteration Overview

Iteration 6 ran from January to February 2026. The focus moved to discoverability, trust, moderation, and interface quality.

### 11.2 Planned and Completed Scope

| Planned Task | Outcome | Status |
|---|---|---|
| Implement advanced keyword search and multi-criteria filtering | Search and filters completed | Complete |
| Implement ratings, reviews, and dispute reporting | Review and dispute workflows added | Complete |
| Apply UI styling across application views | Unified responsive styling completed | Complete |
| Complete admin dashboard | Admin management and moderation views completed | Complete |
| Optimise application performance | Eager loading and query refinement applied | Complete |

### 11.3 Requirements Addressed

This iteration completed FR-08, FR-09, dispute workflows, and the admin moderation requirements. It also supported NFR-01 and NFR-03 through performance improvements and responsive styling.

### 11.4 Design Decisions

Search is implemented with conditional query constraints, allowing keyword search, category filtering, transaction type filtering, and price-range filtering to be combined cleanly.

Reviews are transaction-bound and stored against completed orders, rentals, or swaps. Disputes are also transaction-bound and tracked through their own status workflow.

The final UI styling is consistent across the application and responsive on smaller screens.

### 11.5 Implementation

The listing query applies filters only when the corresponding request parameters are present. This keeps the search endpoint flexible and preserves pagination links through withQueryString().

The admin dashboard includes moderation, reporting, and content-management capabilities.

### 11.6 Testing

Testing covered keyword search, transaction-type filtering, combined filters, review submission, review access control, and responsive layout checks.

### 11.7 Iteration Retrospective

Iteration 6 completed the platform’s feature set and polished the interface. The most time-consuming work was distributing styling and moderation logic across multiple subsystems, but the result was a more complete and coherent product.

---

## 12. Final Iteration - Testing, Documentation and Deployment

### 12.1 Iteration Overview

The final iteration took place from February to April 2026. The emphasis moved from feature development to full-system testing, documentation, and delivery. Only limited feature work was done in this phase, mainly to resolve issues discovered during testing.

### 12.2 Planned and Completed Scope

| Planned Task | Outcome | Status |
|---|---|---|
| Conduct full test suite | Unit, integration, system, UAT, and security tests completed | Complete |
| Identify and resolve defects discovered during testing | Critical and major defects resolved | Complete |
| Prepare complete project documentation | Report chapters completed and reviewed | Complete |
| Prepare and rehearse final presentation | Presentation and demo environment prepared | Complete |
| Submit completed project | Project submitted before the deadline | Complete |

### 12.3 Testing Strategy

The final test strategy consolidated the work from earlier iterations. Black-box testing covered the functional requirements, white-box testing targeted logic-heavy components, integration testing validated end-to-end workflows, security testing checked access control and payment verification, and user acceptance testing assessed usability.

### 12.4 User Acceptance Testing

User acceptance testing was conducted with five volunteers from the target audience. The tasks included registration, listing creation, rental requests, swap offers, checkout, search, and dispute submission. Overall feedback confirmed that the workflows were understandable and usable.

### 12.5 Defect Log

The final testing phase identified a small set of defects. The most important issues were the rental overlap boundary case, swap counter-offer notification delivery, broken images for listings without uploads, and pagination parameter persistence. All were fixed before submission.

### 12.6 Final Iteration Retrospective

The final iteration completed the planned project deliverables on time. Focusing on defect severity kept the effort manageable and ensured that the final submission remained stable. The iterative model proved suitable for a single-developer academic project because it kept scope controlled and made progress easy to audit.

---

## 13. Chapter Summary

This chapter presented the full iterative development lifecycle of ReLoop, from initiation through to final testing and submission. The project delivered the core platform features in a controlled sequence: authentication, product listings, rentals, swaps, payments, notifications, search, reviews, disputes, and administrative moderation.

Each phase followed a consistent pattern of scope definition, implementation, testing, and reflection. That structure provides a clear technical record of what was built and how the system evolved over time.
