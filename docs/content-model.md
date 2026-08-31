# Direpair Dynamic Content and Data Model

## 1. Ownership Rule

| Data | System of record | Editable by |
|---|---|---|
| Public marketing/SEO content | WordPress | Content editor/approver |
| Public media | WordPress Media Library | Content editor |
| Public pricing range/policy | WordPress | Authorized content editor |
| Actual diagnosis and quote | Laravel/MySQL | Technician/customer service |
| Customer identity and consent | Laravel/MySQL | Authorized operations staff |
| Payments and payment events | Laravel/MySQL | System/finance |
| Public spareparts later | WooCommerce | Commerce staff |
| Analytics configuration | Environment + controlled global settings | Technical admin |

Public pricing dan actual quotation adalah dua data berbeda. Nilai public boleh diedit klien sebagai panduan, tetapi invoice selalu berasal dari diagnosis dan quotation tersimpan.

## 2. WordPress Content Types

Semua content type mempunyai `external_uuid`, status, slug, title, excerpt, body/sections, featured media, SEO fields, visibility, verification status, author/reviewer, published/updated timestamps, dan redirect history.

| Content type | Important fields |
|---|---|
| Service category | name, summary, hero, supported devices, problems, pricing guides, locations, technicians, cases, warranty, CTA |
| Service detail | repair action, symptoms, supported models, process, limitations, parts info, turnaround, pricing, warranty, evidence |
| Problem page | quick answer, symptoms, possible causes, safe checks, do-not-open warning, diagnosis, repair options, repair-vs-replace, CTA |
| Location | verified address/service area, NAP, phone, WhatsApp, hours, coordinates, directions, pickup coverage, photos, local services/cases/reviews |
| Technician profile | real name, photo, specialties, bio, experience, real certifications, reviewed articles, cases, active flag |
| Repair case | verified device, brand/model, symptom, diagnosis, action, parts, outcome, turnaround range, before/after media, technician, privacy approval |
| Pricing guide | service relation, diagnosis fee, labor starts at/range, parts excluded flag, turnaround range, currency, disclaimer, effective date |
| FAQ | question, answer, applicable services/locations, display order, schema eligibility |
| Warranty policy | repair type, duration, labor/parts coverage, exclusions, claim steps, effective date |
| Knowledge article | quick answer, sections, related service/problem/parts/cases, author, technician reviewer, last reviewed date |
| Global settings | business identity, logo, default contact, social, default hours, CTA labels, service methods, legal page links, default SEO |
| Product, P1 | WooCommerce fields plus compatibility, installation recommendation, related repair service, warranty, condition |

### Taxonomies

- device category;
- brand;
- model family;
- symptom;
- repair action;
- component;
- service method;
- city/service area;
- article topic.

Brand/model pages tidak otomatis index. Field `index_eligibility` hanya dapat aktif bila evidence minimum terpenuhi.

## 3. Operational Data Model

### Core tables

- `customers`
- `customer_contacts`
- `consent_records`
- `devices`
- `service_requests`
- `service_request_attachments`
- `service_appointments`
- `pickups`
- `repair_jobs`
- `job_assignments`
- `diagnoses`
- `diagnosis_components`
- `quotations`
- `quotation_items`
- `quotation_approvals`
- `invoices`
- `payments`
- `payment_events`
- `status_events`
- `repair_actions`
- `parts_used`
- `quality_checks`
- `handovers`
- `warranties`
- `warranty_claims`
- `notifications`
- `audit_logs`

### Catalog references

Operational records menyimpan:

- immutable CMS `external_uuid` bila tersedia;
- label snapshot saat booking;
- free-text fallback untuk brand/model/device yang belum ada;
- source landing page and campaign attribution tanpa PII.

Dengan snapshot, perubahan nama/slug di WordPress tidak merusak histori pekerjaan.

## 4. Dynamic Fallback Behavior

| Missing data | Public behavior |
|---|---|
| Technician photo | Show neutral team/workshop placeholder or hide person card |
| Technician profile | Show generic verified workshop/team section; do not invent a person |
| Price | Show `Estimasi diberikan setelah diagnosis` |
| Price range incomplete | Show only verified available component plus parts disclaimer |
| Repair case | Hide recent-case section; never fabricate evidence |
| Review | Hide rating/review block and Review schema |
| Location not verified | Do not publish/index location page |
| Certification not verified | Omit certification claim |
| Brand/model evidence insufficient | Keep model page draft/noindex or do not generate route |
| Warranty unspecified | Show general diagnostic policy only; block final production launch until legal copy exists |

## 5. Dummy Content Strategy

### Seed design

- WordPress fixtures use stable UUIDs and can be imported repeatedly without duplication.
- Laravel fixtures create demo customers, devices, requests, quotes, and payments only outside production.
- Media fixtures use clearly neutral demo images/placeholders.
- Dummy values carry `is_demo=true` and visible `Konten Demo` labels in staging.
- Production build fails when forbidden demo entities would generate schema or indexable pages.

### What may be dummy during development

- service descriptions;
- pricing ranges labeled demo;
- generic technician cards labeled demo;
- fake operational jobs in staging;
- placeholder workshop/device images;
- sample FAQs and warranties marked for legal review.

### What must never be presented as real in production

- customer reviews/ratings;
- technician certification;
- physical address or Google Business Profile location;
- successful repair case;
- warranty/legal claim;
- exact price commitment;
- partner/distributor relationship.

## 6. Publishing Workflow

```text
Editor creates/updates content
  -> validation
  -> approver review
  -> publish
  -> signed content webhook
  -> Laravel integration endpoint
  -> CI/manual build request
  -> Astro fetch + schema validation
  -> static build + tests
  -> upload artifact to cPanel document root
  -> publish result logged in WordPress
```

If build validation fails, current production deployment remains active and the editor receives an actionable error report.
