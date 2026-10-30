# Payment Service — Operations Guide (Laravel-only)

**Date:** 2026-10-14  
**Engine:** Laravel (`App\Services\Finance\WalletLedgerService`) — Python Azure Functions microservice **removed**.  
**Gateway:** Stripe only — Razorpay **removed**.

---

## What you need to do

1. **Run migrations**
   ```bash
   php artisan migrate
   ```
   Includes `2026_10_14_100000_create_payment_service_foundation.php`.

2. **Configure `.env`**
   ```env
   PAYMENT_WALLET_ENABLED=true
   PAYMENT_ONLINE_ENABLED=false
   PAYMENT_WALLET_LEGACY_UNSCOPED_AGENT=true
   STRIPE_MODE=test
   STRIPE_PUBLIC_KEY=pk_test_...
   STRIPE_SECRET_KEY=sk_test_...
   STRIPE_WEBHOOK_SECRET=whsec_...
   STRIPE_CONNECT_WEBHOOK_SECRET=whsec_...   # optional; falls back to STRIPE_WEBHOOK_SECRET
   STRIPE_APPLICATION_FEE_PERCENT=0         # used when markup not supplied on DMC charges
   FINANCE_DEFAULT_PAYMENT_MODE_ON_CONFIRM=wallet
   ```
   Remove any `RAZORPAY_*` and `FINANCE_SERVICE_BASE_URL` / `FINANCE_API_BEARER_TOKEN` — unused.

3. **Stripe Dashboard**
   - Enable **Connect** (Express recommended).
   - Webhooks → your app:
     - `POST /api/v1/finance/webhook/payment` (or your API webhook route)
     - `POST /stripe/connect/webhook` for `account.updated`
     - Events: `payment_intent.*`, `charge.succeeded`, `account.updated`
   - Keep mode aligned: test keys with `STRIPE_MODE=test`.

4. **Enable Online payments** (after keys work)
   ```http
   POST /payment-service/settings
   { "online_enabled": true, "wallet_enabled": true, "stripe_mode": "test" }
   ```
   Offline cannot be disabled.

5. **DMC Connect onboarding**
   - DMC user: `POST /finance/stripe/connect/onboard` → open `onboarding_url`
   - Return: `GET /finance/stripe/connect/return`

6. **No Python / Azure Functions deploy** — finance runs inside Laravel + PostgreSQL wallet tables.

---

## Application fees (Stripe Connect)

| Charge | Model | Application fee |
|--------|--------|-----------------|
| Online / Mix stage 1 (Travclicks) | Platform PaymentIntent | none (platform keeps funds) |
| Offline / Mix stage 2 (DMC) | Destination charge | `application_fee_amount` = Travclicks markup when known, else `STRIPE_APPLICATION_FEE_PERCENT` |

DMC commission continues via wallet `booking_credit` in Laravel settlement.

---

## Key routes

| Method | Path |
|--------|------|
| GET | `/payment-service/tours/{id}/eligibility` |
| POST | `/payment-service/tours/{id}/pay` |
| POST | `/payment-service/tours/{id}/wallet/complete` |
| POST | `/payment-service/tours/{id}/stripe/complete` |
| GET/POST | `/payment-service/settings` |
| GET/POST | `/finance/stripe/connect/*` |
| POST | `/stripe/connect/webhook` |

---

## Cases

| Order | Accept / Confirm | Card recipient | Wallet |
|-------|------------------|----------------|--------|
| Online | Pay full amount first (read-only) | Travclicks | DMC-scoped agent allocation |
| Offline | Existing confirm; then collect | DMC Connect | DMC-scoped |
| Mix stage 1 | Online portion only | Travclicks | DMC-scoped |
| Mix stage 2 | Part payments after confirm | DMC Connect + app fee | DMC-scoped |

Cash / Cheque / Bank Transfer remain on the existing Add Payment modal.

---

## Rollback

- Disable Online/Wallet via settings (does not cancel in-flight PaymentIntents).
- Additive migrations only; historical `payment_details` and wallet ledger kept.
