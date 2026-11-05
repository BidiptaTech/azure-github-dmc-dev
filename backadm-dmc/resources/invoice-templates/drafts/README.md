# Invoice template local drafts

Edit these HTML/CSS pairs, then preview with a real tour **after Prospect**:

```bash
php artisan invoice-template:check-draft proforma --tour=TOUR_ID
php artisan invoice-template:check-draft proforma --tour=TOUR_ID --pdf
php artisan invoice-template:check-draft proforma-price-only --tour=TOUR_ID
```

Or in Super Admin → Invoice Template Studio → Tour ID → Open HTML/PDF preview.

When the preview matches the look you want, copy the file contents into Studio editors → **Save Draft** → **Publish**.

## Files

| Draft | Template type | Purpose |
|-------|---------------|---------|
| `proforma.*` | `proforma` | Full proforma (services + totals) — professional default layout |
| `proforma-price-only.*` | `proforma_price_only` | Price summary only |
| `final.*` | `final` | Full final invoice |
| `final-price-only.*` | `final_price_only` | Final price summary |
| `alternate.*` | `alternate` | Alternate layout (same professional base) |

## Twig notes

- Use only allowlisted tags/elements (`div`, `table`, `p`, `h1`–`h6`, `img`, … — no `header`/`section`/`a`).
- Main variables: `invoice`, `branding`, `client`, `travel_company`, `travel`, `items`, `services`, `pricing`, `bank_details`, `payment_terms`.
- Logo must be a validated data URI (builder supplies this).

## Multi-city behaviour

When the tour spans more than one city/country:

- `travel.is_multi_city` / `services.is_multi` become `true`
- Services render under **city blocks** via `services.by_city[]` (`label`, `sections`, `subtotal`, `currency`)
- Single-city invoices keep the simpler `services.by_type` layout

## Currency behaviour

Invoices carry **three** currency concepts:

| Field | Meaning |
|-------|---------|
| `pricing.base_currency` | DMC / company accounting currency (e.g. IDR) |
| `pricing.booking_currency` | Currency services were booked in (e.g. SGD) |
| `pricing.display_currency` | Currency shown on the PDF (defaults to base; override with `?currency=SGD`) |

Line `item.total_price` / `item.unit_price` are **converted into `display_currency`**. Native booking amounts stay on `item.total_price_native` / `item.currency_native`.

Preview in booking currency:

```bash
php artisan invoice-template:check-draft proforma --invoice=76 --currency=SGD
```

Previews are written to `storage/app/invoice-template-drafts/`.
