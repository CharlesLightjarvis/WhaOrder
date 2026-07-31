# Variant-Only Products Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Support simple products and non-sellable parent products whose variants exclusively own price, stock, and images.

**Architecture:** The existence of variants determines product mode. Persistence normalizes parent commercial fields to null for variant products, resources expose server-computed list summaries, and order/AI services reject variant parents without a selected variant.

**Tech Stack:** Laravel 12, PHP 8.4, Pest, MySQL, Inertia React 19, TypeScript, Vite.

## Global Constraints

- Simple products require parent price and stock and may own parent images.
- Variant products require at least one variant with price and stock; parent price, stock, and images are null or absent.
- Switching to variants permanently removes parent images from ImageKit and the database.
- Variant products are never directly orderable.
- List and AI presentation derive price ranges, total stock, and cover images on the server.

---

### Task 1: Schema and conditional validation

**Files:**
- Create: `database/migrations/2026_07_31_160000_support_variant_only_products.php`
- Modify: `app/Http/Requests/Products/StoreProductRequest.php`
- Modify: `app/Http/Requests/Products/UpdateProductRequest.php`
- Modify: `app/Models/Product.php`
- Modify: `resources/js/types/product.ts`
- Test: `tests/Feature/Products/VariantOnlyProductValidationTest.php`

**Interfaces:**
- Consumes: submitted `variants` arrays.
- Produces: nullable `Product::$price`, nullable `Product::$stock`, required non-null variant price and stock.

- [ ] Write request tests proving simple products require parent values and variant products require every variant value.
- [ ] Run `php artisan test --compact tests/Feature/Products/VariantOnlyProductValidationTest.php` and confirm failure.
- [ ] Add a migration that backfills null variant prices from parent prices, nulls parent price/stock for products with variants, makes parent fields nullable, and makes variant price non-null.
- [ ] Implement conditional Laravel validation with `Rule::requiredIf` based on non-empty variants and reject an empty variant entry.
- [ ] Update PHP casts/documentation and TypeScript fields to `number | null`.
- [ ] Rerun the focused tests and confirm success.

### Task 2: Normalize create/update persistence and image ownership

**Files:**
- Modify: `app/Actions/Products/CreateProduct.php`
- Modify: `app/Actions/Products/UpdateProduct.php`
- Modify: `app/Actions/Products/SyncProductStockFromVariants.php`
- Test: `tests/Feature/Products/VariantOnlyProductPersistenceTest.php`
- Test: `tests/Feature/Products/StoreUploadedImagesTest.php`

**Interfaces:**
- Consumes: validated product data.
- Produces: parent price/stock null and zero parent images when variants exist.

- [ ] Write failing persistence tests for simple products, variant parents, mode switching, and ImageKit cleanup.
- [ ] Run the focused tests and confirm failure.
- [ ] Normalize parent fields to null when `variants` is non-empty and prevent parent uploads in that mode.
- [ ] Prune every parent image when switching to variants and remove obsolete stock synchronization into the parent.
- [ ] Preserve simple-product values and prune variants when switching back to simple.
- [ ] Run the focused tests and confirm success.

### Task 3: Product resource and DataTable summaries

**Files:**
- Modify: `app/Repositories/Products/EloquentProductRepository.php`
- Modify: `app/Http/Resources/ProductResource.php`
- Modify: `resources/js/types/product.ts`
- Modify: `resources/js/pages/products/partials/columns.tsx`
- Test: `tests/Feature/Products/ProductPaginationTest.php`
- Test: `tests/Feature/Products/VariantOnlyProductResourceTest.php`

**Interfaces:**
- Produces: `has_variants: boolean`, `cover_image: ProductImage | null`, `price_min: number | null`, `price_max: number | null`, `stock_total: number | null`.

- [ ] Write failing resource tests for simple summaries, equal variant prices, price ranges, total stock, and variant cover images.
- [ ] Run focused tests and confirm failure.
- [ ] Eager-load the minimal parent/variant images and aggregate variant prices and stock without N+1 queries.
- [ ] Serialize the five summary fields with stable nullability.
- [ ] Render the cover, single price or range, and simple stock or variant total in the DataTable.
- [ ] Run PHP resource tests, `npm run types:check`, and `npm run lint:check`.

### Task 4: Product create/edit forms

**Files:**
- Modify: `resources/js/pages/products/create.tsx`
- Modify: `resources/js/pages/products/edit.tsx`
- Test: `tests/Feature/Products/VariantOnlyProductValidationTest.php`

**Interfaces:**
- Consumes: `product.has_variants` on edit.
- Produces: only parent fields for simple mode and only variant commercial/image fields for variant mode.

- [ ] Add an explicit simple/variants mode control initialized from existing product data.
- [ ] Disable and visually mute parent price, stock, and image blocks in variant mode; do not submit their inputs.
- [ ] Require and label variant price and stock, show field-specific server errors, and create the first blank variant when variant mode is selected.
- [ ] On switching back to simple mode, remove variant inputs from submission and enable empty required parent fields.
- [ ] Run `npm run types:check`, `npm run lint:check`, and `npm run build:ssr`.

### Task 5: Ordering, AI tools, and stock alerts

**Files:**
- Modify: `app/Ai/Tools/SearchProductTool.php`
- Modify: `app/Ai/Tools/CheckStockTool.php`
- Modify: `app/Ai/Tools/CalculateTotalTool.php`
- Modify: `app/Ai/Tools/FinalizeOrderTool.php`
- Modify: `app/Ai/Tools/ModifyOrderTool.php`
- Modify: `app/Ai/Tools/GetProductVariantsTool.php`
- Modify: `app/Ai/Tools/SendProductPhotosTool.php`
- Modify: `app/Actions/Orders/ModifyOrder.php`
- Modify: `app/Actions/Products/CheckLowStock.php`
- Test: `tests/Feature/Ai/AgentRoutingTest.php`
- Test: `tests/Feature/Products/LowStockAlertTest.php`
- Test: `tests/Feature/Orders/ModifyOrderTest.php`

**Interfaces:**
- Variant products require a non-null variant ID for stock checks, totals, order writes, and photos.

- [ ] Write failing tests proving a variant parent cannot be priced, stocked, photographed, or ordered without a variant.
- [ ] Run focused tests and confirm failure.
- [ ] Replace parent fallbacks with explicit variant-required errors whenever variants exist.
- [ ] Present price ranges and total stock in product search, then direct the agent to variant selection.
- [ ] Skip parent low-stock evaluation for variant products and retain per-variant alerts.
- [ ] Run all focused AI, order, and stock tests.

### Task 6: Full verification and publication

**Files:**
- Modify only files required by failures attributable to this feature.

- [ ] Run `php artisan test --compact`.
- [ ] Run `vendor/bin/phpstan analyse --no-progress`.
- [ ] Run `vendor/bin/pint --dirty` and re-run affected tests if formatting changes PHP.
- [ ] Run `npm run types:check`, `npm run lint:check`, and `npm run build:ssr`.
- [ ] Inspect `git diff --check` and verify no secret or generated asset is staged.
- [ ] Commit the implementation, push `main`, and confirm `HEAD` equals `origin/main`.
