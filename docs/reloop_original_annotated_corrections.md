# ReLoop Chapter: Original Description with Mistakes Highlighted and Corrected

This document uses your original chapter wording where issues were found.  
For each case:
- `Original (with mistake highlighted)` shows the problematic line/segment.
- `Corrected version` gives the accurate replacement text.

---

## 1) Iteration Count Statement

Chapter heading number: 4 (opening paragraph)

### Original (with mistake highlighted)
"The development process was structured into **seven cycles inception, six iterations and a final test and documentation cycle**..."

### Corrected version
"The development process was structured into **eight phases: one initiation phase, six development iterations, and one final testing and documentation phase**..."

---

## 2) Laravel Version in Technology Stack

Chapter heading number: 5.4

### Original (with mistake highlighted)
"Backend Framework: **Laravel 10 (PHP 8.2)**"

### Corrected version
"Backend Framework: **Laravel 12 (PHP 8.2)**"

---

## 3) Role Definitions in Requirements/Architecture Narrative

Chapter heading number: 5.2 and 6.4.2

### Original (with mistake highlighted)
"The system shall support role-based access control distinguishing **buyer, seller, and administrator roles**."

### Corrected version
"The system shall support role-based access control with platform roles of **user, admin, and super_admin**. Buyer/seller behavior is handled by transaction workflows rather than separate platform role values."

---

## 4) Product Image Storage Design

Chapter heading number: 5.3.3 and 7.4.3

### Original (with mistake highlighted)
"A separate table, **product_images**, was created to store the file path for the individual image uploads..."

### Corrected version
"Image paths are stored directly in the **products** table using a primary `image` field and an `images` array field, while files are stored on the public filesystem disk."

---

## 5) Search Implementation Claim (FULLTEXT)

Chapter heading number: 11.4

### Original (with mistake highlighted)
"Search was performed using **MySQL FULLTEXT index** on the title and description columns..."

### Corrected version
"Search is implemented using conditional query constraints in the controller with keyword `LIKE` matching, category filtering, listing type filtering, and price-range filtering, while preserving pagination query strings."

---

## 6) Search Implementation Claim (Response Cache)

Chapter heading number: 11.4

### Original (with mistake highlighted)
"...the most frequently called index query... was wrapped in a **five-minute cache**..."

### Corrected version
"The project optimizes listing retrieval primarily through query construction and eager loading in relevant areas; the chapter should not claim a five-minute response cache unless that exact implementation is present."

---

## 7) Payment Verification Example (Data Model Mismatch)

Chapter heading number: 10.5

### Original (with mistake highlighted)
```php
$payment = Payment::where('transaction_ref', $request->pidx)->firstOrFail();
```

### Corrected version
```php
$payment = Payment::where('transaction_uuid', $transactionUuid)->first();
```

And for Khalti return handling, the flow should describe lookup and verification using `pidx` from callback/request payload before marking payment complete.

---

## 8) Swap Mutual Acceptance Description

Chapter heading number: 9.4 and 9.5

### Original (with mistake highlighted)
"Enable mutual acceptance workflow: **Two-flag confirmation system** prevents unilateral acceptance."

### Corrected version
"Swap processing is implemented using request statuses (`requested`, `countered`, `awaiting_payment`, `rejected`, `completed`) and guarded transitions, ensuring unilateral completion is prevented by workflow rules."

---

## 9) Database Cardinality and Table Count Statement

Chapter heading number: 5.3.3

### Original (with mistake highlighted)
"The database consists of **11 tables**..."

### Corrected version
"The final database includes more than 11 tables, including core transaction, moderation, and support tables (e.g., users, products, categories, orders, order_items, rentals, rental_requests, rented_rentals, swaps, swap_requests, payments, reviews, disputes, wishlists, and additional project-specific tables)."

---

## 10) Products Schema Description (Static vs Migrated)

Chapter heading number: 5.3.3 and 7.4.1

### Original (with mistake highlighted)
"The products table... with ... **category** ..."

### Corrected version
"The products table evolved through migrations and uses a `category_id` relationship in the final design, with `type` stored as JSON and status controlled by allowed enum states."

---

## 11) Final Iteration Deadline Typo

Chapter heading number: 12.1

### Original (with mistake highlighted)
"...deliver the final product before the provided deadline in **3/2006**."

### Corrected version
"...deliver the final product before the provided deadline in **March 2026**."

---

## 12) Terminology and Naming Consistency

Chapter heading number: cross-cutting (applies across 4-13)

### Original (with mistake highlighted)
"e Sewa", "Reloop", "Breathe", mixed status names and inconsistent class naming in narrative.

### Corrected version
Use consistent terms throughout:
- `ReLoop` (project name)
- `eSewa` and `Khalti`
- `Laravel Breeze` (if referenced)
- status names exactly as implemented in code and database

---

## 13) Corrected Replacement Paragraph for the Selected Line (Registration Flow)

Chapter heading number: 6.5.1

### Original (with mistake highlighted)
"Registration uses a dedicated request class for validation and a controller that creates the user, hashes the password, and logs the user in. After registration, the user is redirected to the email verification notice."

### Corrected version
"Registration is handled through a dedicated request validation flow and controller logic that creates the user record with secure password hashing, authenticates the user session, and redirects to the email verification notice."

---

## Suggested Use

Apply each corrected segment back into your full chapter document at the corresponding location. This keeps your original narrative style while making the technical details consistent with the implemented project.
