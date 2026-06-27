# Requirements Document

## Introduction

This document covers the Intelligent PDF → Product Bulk Import feature for **Svaraa Jewels**, an earrings-only jewellery ecommerce store built on Laravel 12 + Filament v3 + Livewire.

The feature extends the existing bulk-upload pipeline to accept PDF catalogues, extract one candidate product per page, detect and suppress duplicate jewellery images (exact and near-duplicate), auto-generate premium product metadata via an AI-style description service, and present an admin review table from which products can be saved as drafts or published immediately.

The implementation runs in a GD-only environment (no Imagick / Ghostscript) on XAMPP Windows and must not alter any existing checkout, payment, cart, order, image-upload, or authentication behaviour.

---

## Glossary

- **PDF_Importer**: The `PdfProductImportService` class responsible for parsing a PDF and producing product candidates.
- **Bulk_Upload_Page**: The `BulkUploadProducts` Filament page (Livewire component) that orchestrates uploads and the review table.
- **Candidate**: A plain serialisable array representing one potential product extracted from a PDF page — contains `stored_path`, `preview_url`, `name`, `price`, `source`, `pdf_page`, `from_embedded`, and duplicate-tracking fields.
- **Review_Table**: The Filament/Livewire UI table that displays Candidates for admin review before persistence.
- **Duplicate_Registry**: The in-process store (array or cache) holding exact-hash → first-seen page and pHash → first-seen page mappings during a single import run.
- **Description_Service**: The `ProductDescriptionService` class that generates premium product copy from a name and category.
- **Placeholder**: A GD-generated 400×400 JPEG image used when no embedded image is found on a PDF page.
- **Draft**: A product with `status = 'draft'`; not visible on the storefront.
- **Active**: A product with `status = 'active'`; visible on the storefront.
- **pHash**: Perceptual image hash — a 64-bit hash sensitive to visual content rather than byte identity.
- **Similarity_Threshold**: 95 % perceptual similarity above which two images are treated as near-duplicates.
- **Source_PDF**: The public-disk-relative path of the uploaded PDF file from which a Candidate was extracted.
- **Queue_Job**: A Laravel queued job (`ProcessPdfImportJob`) that performs CPU-intensive PDF parsing off the main request cycle.
- **Import_Session**: The Livewire component state for one upload batch, cleared after Save Draft / Publish.

---

## Requirements

### Requirement 1: PDF File Upload

**User Story:** As an admin, I want to upload one or more PDF catalogue files through the Bulk Upload page, so that the system can extract product candidates from each page without the UI freezing.

#### Acceptance Criteria

1. THE `Bulk_Upload_Page` SHALL accept PDF files via the existing `pdfUploadForm` FileUpload component with `acceptedFileTypes(['application/pdf'])`.
2. WHEN a PDF file exceeds 50 MB, THE `Bulk_Upload_Page` SHALL reject it and display an inline validation error before any processing begins.
3. WHEN a file whose MIME type is not `application/pdf` or `application/x-pdf` is submitted as a PDF, THE `PDF_Importer` SHALL throw a `RuntimeException` with a message matching `/valid PDF/i`.
4. WHEN a PDF upload is accepted, THE `Bulk_Upload_Page` SHALL dispatch a `Queue_Job` for each uploaded file and immediately set `$step = 'extracting'` so the UI renders a progress indicator.
5. WHILE the `Queue_Job` is running, THE `Bulk_Upload_Page` SHALL remain interactive and SHALL NOT block the Livewire event loop.
6. THE `Bulk_Upload_Page` SHALL support uploading multiple PDF files in a single session, processing each file independently.

---

### Requirement 2: Page-Level Image and Text Extraction

**User Story:** As an admin, I want the system to extract product images and text from every page of an uploaded PDF, so that I receive one candidate per page to review.

#### Acceptance Criteria

1. WHEN a PDF page contains one or more embedded XObject images, THE `PDF_Importer` SHALL extract each image (JPEG, PNG, or WebP) and store it at `pdf-extracted/{baseName}-p{page}-img{index}.{ext}` on the `public` disk.
2. WHEN a PDF page contains no extractable embedded image, THE `PDF_Importer` SHALL generate a `Placeholder` JPEG at `pdf-extracted/{baseName}-p{page}-placeholder.jpg` on the `public` disk.
3. THE `Placeholder` image SHALL be a valid JPEG (byte signature `\xFF\xD8\xFF`) with dimensions 400×400 pixels, generated using the GD extension only.
4. WHEN a page contains readable text, THE `PDF_Importer` SHALL attempt to extract a product name by selecting the first non-trivial line (4–60 characters) that does not consist solely of digits, separators, or punctuation.
5. IF page text extraction fails for any page, THEN THE `PDF_Importer` SHALL fall back to a name derived as `Str::title(str_replace(['-', '_', '.'], ' ', $baseName)) . ' ' . $pageNumber`.
6. WHEN page text contains a price pattern matching `₹N`, `Rs N`, `INR N`, `MRP N`, or `Price N` (where N is a numeric value), THE `PDF_Importer` SHALL extract the numeric portion (digits and decimal point only) and store it in the `price` field of the Candidate.
7. IF no price pattern is detected, THEN THE `PDF_Importer` SHALL set `price = ''` in the Candidate.
8. THE `PDF_Importer` SHALL produce exactly one Candidate per embedded image found per page; a page with zero embedded images produces exactly one placeholder Candidate.

---

### Requirement 3: Encrypted and Corrupt PDF Handling

**User Story:** As an admin, I want the system to reject encrypted or structurally corrupt PDFs gracefully, so that one bad file does not crash the entire import batch.

#### Acceptance Criteria

1. WHEN a PDF file contains an `/Encrypt` dictionary entry in its first 1 024 bytes or in its parsed details, THE `PDF_Importer` SHALL throw a `RuntimeException` with a message matching `/[Ee]ncrypt/`.
2. WHEN smalot/pdfparser throws any exception during `parseFile()`, THE `PDF_Importer` SHALL catch it and re-throw as a `RuntimeException` with message prefix `'Could not parse PDF: '`.
3. WHEN a PDF parses successfully but contains zero pages, THE `PDF_Importer` SHALL throw a `RuntimeException` with message `'The PDF contains no pages.'`.
4. WHEN `extractCandidates()` is called with a path to a non-existent file, THE `PDF_Importer` SHALL throw a `RuntimeException` with a message matching `/not found/i`.
5. WHEN a single PDF in a multi-file upload fails extraction, THE `Bulk_Upload_Page` SHALL skip that file, add its error message to the error list, and continue processing remaining files.
6. WHEN one or more files in a batch fail, THE `Bulk_Upload_Page` SHALL display a persistent warning `Notification` listing each failed filename and its error message after all files are processed.
7. THE `Bulk_Upload_Page` SHALL display a summary after each import batch showing counts: "Extracted products: Y", "Placeholder generated: Z", "Skipped duplicates: X", "Failed pages: N".

---

### Requirement 4: Two-Layer Duplicate Detection

**User Story:** As an admin, I want the system to detect when the same jewellery image appears on multiple PDF pages and collapse those occurrences into a single product candidate, so that I never create duplicate listings.

#### Acceptance Criteria

1. THE `PDF_Importer` SHALL maintain a `Duplicate_Registry` during `extractCandidates()` that maps SHA-256 hashes of raw image bytes to the first-seen page number.
2. WHEN an extracted image's SHA-256 hash already exists in the `Duplicate_Registry`, THE `PDF_Importer` SHALL NOT create a new Candidate; instead it SHALL increment `occurrences_count` and append the current page number to `duplicate_pages` on the first-seen Candidate.
3. THE `PDF_Importer` SHALL compute a perceptual hash (pHash) for each extracted image using a pure-PHP DCT-based implementation (no Imagick) with a 64-bit output.
4. WHEN an extracted image's pHash differs from a previously registered pHash by a Hamming distance of 3 or fewer bits (equivalent to ≥ 95 % similarity on a 64-bit hash), THE `PDF_Importer` SHALL treat it as a near-duplicate: it SHALL NOT create a new Candidate and SHALL update the first-seen Candidate's `duplicate_pages` and `occurrences_count`.
5. WHEN an image is identified as a duplicate (exact or near), THE `PDF_Importer` SHALL store the `source_pdf` path and all duplicate page numbers in the first-seen Candidate's `duplicate_pages` array.
6. WHEN duplicate detection merges page occurrences, THE `PDF_Importer` SHALL set `occurrences_count` on the Candidate equal to the total number of pages (including the first) on which the image appeared.
7. THE `Duplicate_Registry` SHALL be scoped to a single `extractCandidates()` call; it SHALL NOT persist across separate PDF uploads or import sessions.
8. Placeholder images (pages with no embedded image) SHALL be exempt from pHash duplicate detection and SHALL always produce their own Candidate.

---

### Requirement 5: AI-Style Product Metadata Generation

**User Story:** As an admin, I want the system to auto-generate premium product names, categories, and descriptions for each unique candidate, so that I can reduce manual data-entry work.

#### Acceptance Criteria

1. WHEN a Candidate is added to the review table from a PDF source, THE `Bulk_Upload_Page` SHALL call `Description_Service::generate($name, 'Earrings')` and store the result in the Candidate's `description` field.
2. THE `Description_Service` SHALL return a non-empty string for any non-empty name and category input.
3. THE `Description_Service` SHALL return the same description string for the same name and category on repeated calls (deterministic output).
4. THE `Description_Service` SHALL NOT include the words "weight", "purity", "certification", "certified", "shipping", "delivery", or "guaranteed" in any generated description.
5. THE `Bulk_Upload_Page` SHALL default the `category_id` of all PDF-sourced Candidates to the `id` of the Category whose `slug = 'earrings'`.
6. WHERE an Earrings category does not exist in the database, THE `Bulk_Upload_Page` SHALL set `category_id = null` and allow the admin to assign it manually in the Review_Table.
7. WHEN the admin changes the `category_id` on a Review_Table row, THE `Bulk_Upload_Page` SHALL re-call `Description_Service::generate()` with the new category name and update the row's `description` field.

---

### Requirement 6: Admin Review Table

**User Story:** As an admin, I want to see all extracted product candidates in an editable table before saving, so that I can correct names, prices, categories, and remove unwanted items.

#### Acceptance Criteria

1. WHEN at least one Candidate exists, THE `Bulk_Upload_Page` SHALL render the `Review_Table` with columns: Preview thumbnail, Generated Name, Category (dropdown), Description, Price (text input), Source PDF, Page, Duplicate Count, Status badge.
2. THE `Review_Table` SHALL display a per-row checkbox and "Select All" / "Deselect All" controls.
3. WHEN the admin edits the Name field of a row, THE `Bulk_Upload_Page` SHALL update `previews[$index]['name']` via `updateField()`.
4. WHEN the admin edits the Price field of a row, THE `Bulk_Upload_Page` SHALL accept any numeric string (including empty string) without validation errors at edit time.
5. WHEN the admin clicks "Remove" on a row, THE `Bulk_Upload_Page` SHALL call `removeRow($index)`, delete the associated file from `Storage::disk('public')`, and re-index the `$previews` array.
6. WHEN the `Duplicate Count` column value is ≥ 2 for a row, THE `Bulk_Upload_Page` SHALL render that cell with a visible indicator (e.g. badge or icon) to alert the admin.
7. WHEN the `from_embedded` field is `false` for a row (placeholder), THE `Review_Table` SHALL display "Placeholder – replace image" in the Page column suffix.
8. THE `Review_Table` SHALL display the `source_pdf` filename and `pdf_page` number for every PDF-sourced Candidate.

---

### Requirement 7: Validation Rules for Save and Publish

**User Story:** As an admin, I want clear, proportionate validation when I save or publish products, so that mandatory data is enforced without blocking legitimate draft saves.

#### Acceptance Criteria

1. WHEN the admin clicks "Save All Draft", THE `Bulk_Upload_Page` SHALL require each row to have a non-blank `name` and a non-null `category_id`; it SHALL NOT require a non-zero `price`.
2. WHEN the admin clicks "Publish Selected", THE `Bulk_Upload_Page` SHALL require each selected row to have a non-blank `name`, a non-null `category_id`, and a `price` that is a numeric value greater than 0.
3. IF any validation rule is violated, THEN THE `Bulk_Upload_Page` SHALL set `$rowErrors[$index]` for each failing row and display a danger `Notification` with title `'Fix the highlighted errors before saving.'`; it SHALL NOT persist any rows from the batch.
4. THE `Bulk_Upload_Page` SHALL allow saving or publishing when `price` is empty or null during a draft save.
5. THE `Bulk_Upload_Page` SHALL allow saving or publishing when the image is a `Placeholder` (i.e. `from_embedded = false`), provided name and category are present.
6. THE `Bulk_Upload_Page` SHALL allow saving or publishing when `duplicate_pages` is non-empty, provided name and category (and price for publish) are present.

---

### Requirement 8: Product Persistence (Draft and Publish)

**User Story:** As an admin, I want every saved product to start as a draft by default, and I want to be able to publish selected products, so that I control what appears on the storefront.

#### Acceptance Criteria

1. WHEN "Save All Draft" is confirmed, THE `Bulk_Upload_Page` SHALL call `persistRows($previews, 'draft')` inside a `DB::transaction()`, creating one `Product` record per row with `status = 'draft'`.
2. WHEN "Publish Selected" is confirmed, THE `Bulk_Upload_Page` SHALL call `persistRows($selected, 'active')` inside a `DB::transaction()`, creating one `Product` record per selected row with `status = 'active'`.
3. THE `Product` model's `boot()` method SHALL auto-generate a unique `slug` from `Str::slug($name)`, appending a numeric suffix when a collision exists.
4. WHEN a `Product` is created with `status = 'draft'`, THE `Product` SHALL NOT appear in storefront queries filtered by `status = 'active'`.
5. WHEN a `Product` is created with `status = 'active'`, THE `Product::shouldBeSearchable()` SHALL return `true`.
6. THE `Bulk_Upload_Page` SHALL set `thumbnail` to `stored_path` (the public-disk-relative path of the extracted or placeholder image) when creating each Product.
7. WHEN `persistRows()` completes successfully, THE `Bulk_Upload_Page` SHALL clear `$previews`, `$rowErrors`, `$images`, `$pdfs`, and reset forms via `fill()`.
8. THE `Bulk_Upload_Page` SHALL NEVER auto-publish any product; every product created by "Save All Draft" SHALL have `status = 'draft'`.

---

### Requirement 9: Import Summary Notification

**User Story:** As an admin, I want to see a clear summary after each import run showing how many products were extracted, how many duplicates were skipped, and how many pages had placeholders, so that I can trust the process is complete.

#### Acceptance Criteria

1. WHEN `extractCandidates()` completes, THE `PDF_Importer` SHALL return summary counters: `total_candidates`, `skipped_duplicates`, `placeholder_count`, `embedded_count` as part of the return contract (embedded in Candidate metadata or via a dedicated summary key).
2. WHEN processing of all files in an upload batch completes, THE `Bulk_Upload_Page` SHALL display a success `Notification` body containing: `"Extracted products: {Y}"`, `"Placeholder generated: {Z}"`, `"Skipped duplicates: {X}"`.
3. IF any pages failed entirely during extraction, THEN THE `Bulk_Upload_Page` SHALL include `"Failed pages: {N}"` in the notification body.
4. WHEN the notification is shown, THE `Bulk_Upload_Page` SHALL transition to `$step = 'review'`.

---

### Requirement 10: Memory Safety and Temp File Cleanup

**User Story:** As a system operator, I want PDF processing to be memory-safe and clean up all temporary files, so that the server does not run out of memory or disk space during large imports.

#### Acceptance Criteria

1. WHEN processing a PDF with more than 20 pages, THE `Queue_Job` SHALL process pages in chunks of at most 10 pages per chunk to limit peak memory usage.
2. WHEN a `Queue_Job` completes (successfully or with an exception), THE `Queue_Job` SHALL delete any temporary files created during extraction from the system temp directory.
3. THE `PDF_Importer` SHALL call `imagedestroy()` on every GD image resource created during placeholder generation before the method returns.
4. THE `PDF_Importer` SHALL release any large string variables (raw page content, image binary data) by setting them to `null` after use within the processing loop.
5. WHEN a large PDF (50 MB, ≥ 40 pages) is processed, THE `Queue_Job` SHALL complete without exceeding 256 MB of PHP memory (`memory_limit`).

---

### Requirement 11: Backward Compatibility

**User Story:** As a developer, I want the PDF import feature additions to have zero impact on existing checkout, payment, cart, order, image-upload, and authentication flows, so that no regressions are introduced.

#### Acceptance Criteria

1. THE `Bulk_Upload_Page` SHALL continue to process direct image uploads (JPEG / PNG / WebP) via `processImageUploads()` without any modification to that method's behaviour.
2. THE new `Queue_Job`, migration, and service classes SHALL NOT modify any existing model beyond `Product` (which receives new nullable columns via migration).
3. THE new migration SHALL use `Schema::table()` (`ALTER TABLE`) to add new nullable columns to the `products` table; it SHALL NOT drop or modify any existing column.
4. WHEN the existing `BulkUploadTest` suite runs after the feature is implemented, ALL previously passing test cases SHALL continue to pass.
5. THE `resolveRealPath()` and `resolveStoredPath()` methods on `Bulk_Upload_Page` SHALL retain their existing behaviour for all four input cases: UploadedFile object, absolute path, storage-relative path, and unresolvable path.

---

### Requirement 12: New Database Fields for Duplicate Tracking

**User Story:** As a developer, I want the products table to store PDF import metadata (source PDF, page numbers, occurrence count) so that admins can trace exactly which PDF pages each product came from.

#### Acceptance Criteria

1. THE new migration SHALL add the following nullable columns to the `products` table: `source_pdf` (string), `duplicate_pages` (JSON), `occurrences_count` (unsigned integer, default 1).
2. WHEN a product is created from a PDF-sourced Candidate that is the first occurrence of its image, THE `Product` SHALL have `occurrences_count = 1` and `duplicate_pages = []`.
3. WHEN a product is created from a Candidate that was merged from multiple pages, THE `Product` SHALL have `occurrences_count` equal to the total number of pages on which the image appeared, and `duplicate_pages` containing all those page numbers.
4. THE `Product` model SHALL cast `duplicate_pages` as `array` so JSON encoding/decoding is transparent.
5. THE new columns SHALL be fillable (added to `$fillable` on the `Product` model).

---

### Requirement 13: Testing Coverage

**User Story:** As a developer, I want comprehensive automated tests for all new import behaviours, so that regressions are caught immediately and all existing tests continue to pass.

#### Acceptance Criteria

1. THE test suite SHALL include a test verifying that an exact-duplicate image (same SHA-256) on a second page is skipped and `occurrences_count` on the first Candidate equals 2.
2. THE test suite SHALL include a test verifying that a near-duplicate image (pHash Hamming distance ≤ 3) is skipped and merged into the first Candidate.
3. THE test suite SHALL include a test verifying that the same image on three different pages produces exactly one Candidate with `occurrences_count = 3` and `duplicate_pages` containing all three page numbers.
4. THE test suite SHALL include a test verifying that `extractPrice()` correctly extracts a numeric price from text containing the pattern `MRP ₹1,600`.
5. THE test suite SHALL include a test verifying that `Description_Service::generate()` returns a non-empty string and produces the same output on repeated calls with identical inputs.
6. THE test suite SHALL include a test verifying that `persistRows($rows, 'draft')` creates products with `status = 'draft'` that do not appear in `status = 'active'` queries.
7. THE test suite SHALL include a test verifying that `persistRows($rows, 'active')` creates products with `status = 'active'` and `shouldBeSearchable() = true`.
8. THE test suite SHALL include a test verifying that pages with no embedded images produce a valid Placeholder JPEG (`\xFF\xD8\xFF` header).
9. THE test suite SHALL include a test verifying that processing a corrupt / non-PDF file throws a `RuntimeException` matching `/valid PDF/i`.
10. THE test suite SHALL include a test verifying that processing an encrypted PDF throws a `RuntimeException` matching `/[Ee]ncrypt/`.
11. THE test suite SHALL include a test verifying that a large multi-page PDF (≥ 5 pages) produces one Candidate per unique image page without running out of memory.
12. WHEN the full `phpunit` suite runs, ALL previously passing test cases in `BulkUploadTest` and all other existing test files SHALL continue to pass.
