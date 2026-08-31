# Direpair Repair, Quotation, Payment, and Warranty Workflow

## 1. Default Business Decision

Customer tidak membayar full repair saat booking. Booking belum mempunyai harga valid. Pembayaran mengikuti urutan:

```text
Booking
  -> intake/unit received
  -> diagnosis
  -> quotation
  -> customer approval or decline
  -> optional deposit
  -> repair
  -> quality control
  -> final payment
  -> handover/return
  -> warranty
```

## 2. Booking Rules

- Guest booking; account tidak wajib.
- Device/problem dikumpulkan sebelum contact details.
- Brand/model dapat `tidak tahu` atau free text.
- Photo/video optional dan private.
- Service method tersedia berdasarkan device category dan service area.
- WhatsApp CTA membawa request number bila booking sudah dibuat.
- Form menyimpan consent version and timestamp.
- Request number berbeda dari database primary key.

## 3. Job State Machine

### Main path

```text
submitted
  -> intake_confirmed
  -> unit_received
  -> diagnosing
  -> quoted
  -> awaiting_customer_decision
  -> approved
  -> awaiting_deposit      # conditional
  -> queued_for_repair
  -> repairing
  -> quality_control
  -> awaiting_final_payment
  -> ready_for_handover
  -> returned
  -> warranty_active
  -> closed
```

### Branches

```text
awaiting_customer_decision -> declined -> diagnosis_fee_due -> return_pending -> closed
diagnosing -> unrepairable -> diagnosis_fee_due/waived -> return_pending -> closed
repairing -> waiting_for_part -> repairing
quality_control -> rework_required -> repairing
any pre-repair state -> cancelled
warranty_active -> warranty_claim_open -> warranty_inspection -> resolved/rejected
```

Allowed transitions divalidasi backend. Staff tidak boleh mengubah string status secara bebas.

## 4. Diagnosis

Diagnosis record minimal:

- customer-reported symptom;
- technician findings;
- diagnosed component/cause;
- repairability: repairable, conditionally repairable, unrepairable;
- proposed repair actions;
- parts and availability;
- risk/limitations;
- estimated turnaround;
- internal notes separated from customer-visible notes;
- technician and timestamps.

Diagnosis tidak boleh otomatis menjadi quotation. Staf harus membuat versi quotation yang customer-visible.

## 5. Quotation

Quotation fields:

- version and expiry;
- diagnosis summary;
- labor items;
- parts items;
- pickup/home-service/diagnosis fee;
- discounts and tax if applicable;
- total;
- estimated completion;
- warranty terms;
- risks/exclusions;
- required deposit policy;
- approve/decline token;
- customer decision timestamp and audit evidence.

Setiap perubahan harga atau scope membuat quotation version baru dan membatalkan approval link lama.

## 6. Payment Policy

### Default

- Booking: no payment.
- Diagnosis fee: invoiced only when applicable according to published policy.
- Pickup/home-service scheduling fee: optional, configurable per location/service.
- Deposit: required only for non-stock parts, high-value parts, or risky custom work.
- Final balance: due after quality control and before unit is returned/delivered.
- Drop-off customer may pay cash/manual transfer; staff records it with finance permission and evidence.

### Configurable payment rules

- `booking_fee_mode`: none/fixed;
- `diagnosis_fee_mode`: waived/fixed/by-category;
- `deposit_mode`: none/fixed/percentage;
- `deposit_trigger`: always/non-stock-part/high-value/manual;
- `final_payment_stage`: before_handover/on_handover;
- `manual_payment_methods`: cash/bank_transfer/EDC;
- `quote_expiry_days`;
- `refund_policy_reference`.

Payment policy lives in the operations system, while public explanatory copy lives in WordPress.

## 7. Midtrans Integration

```text
Customer approves quotation
  -> Laravel locks quotation version
  -> invoice created
  -> Laravel creates Midtrans Snap transaction
  -> customer pays in Midtrans UI
  -> Midtrans webhook reaches Laravel
  -> signature/status verified server-side
  -> idempotent payment event stored
  -> invoice balance recalculated
  -> job transition becomes available
```

Rules:

- Server Key is never exposed to Astro/browser.
- Frontend callback is informational only.
- Webhook and GET Status are authoritative.
- `order_id`/external transaction reference is unique.
- Duplicate and out-of-order notifications are safe.
- Failed/delayed webhook can be reconciled by scheduled job or finance action.
- Refund/cancellation requires authorized operation and audit log.

## 8. Customer Status Page

Access uses a high-entropy opaque token. Sequential IDs are not exposed.

Visible:

- request number;
- masked customer/device summary;
- current stage and safe timeline;
- customer-visible diagnosis;
- current quotation and approval action;
- invoice/payment action;
- expected completion;
- pickup/handover instructions;
- warranty summary after completion.

Never visible:

- internal technician notes;
- staff identity not intended for public display;
- supplier costs/margins;
- audit records;
- other customer data;
- raw payment/webhook payload.

Sensitive actions use short-lived signed links and, when risk requires, contact verification/OTP. Customer accounts remain a P2 feature.

## 9. Notifications

MVP:

- transactional email;
- operations dashboard alerts;
- WhatsApp deep-link/template handoff for staff with request number and approved message copy.

P1:

- approved WhatsApp Business provider/API;
- automatic messages for quote ready, payment received, waiting for part, ready for pickup, and warranty expiry/claim.

Notification jobs run after database commit, are retryable, and store delivery status.

## 10. Warranty

Warranty is generated from completed repair data:

- repair job;
- covered labor/action;
- covered part/serial where applicable;
- start and expiry dates;
- exclusions;
- proof of repair/invoice;
- claim channel.

Claim workflow:

```text
claim submitted
  -> eligibility check
  -> unit inspection
  -> covered repair / rejected with reason / goodwill action
  -> resolution
```

Public warranty copy comes from WordPress; the exact warranty attached to a job is snapshotted in Laravel so later policy edits do not alter historical agreements.

