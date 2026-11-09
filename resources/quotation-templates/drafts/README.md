# Quotation template drafts (check before Studio save)

Edit the HTML/CSS here, preview with a **real tour**, then copy into Super Admin Quotation Template Studio and Save Draft.

Nothing in this folder is loaded by production PDF download automatically.

## 1. Browser preview (recommended)

1. Log in as Super Admin.
2. Open Quotation Template Studio for a DMC + type.
3. In **Check local draft before saving**, enter a real **Tour ID** that belongs to that DMC.
4. Click **Open HTML preview** or **Open PDF preview**.

Or open directly:

```
/quotation-templates/drafts/acco-service/preview?tour_id=YOUR_TOUR_ID&dmc_id=YOUR_DMC_ID
/quotation-templates/drafts/acco-service/pdf?tour_id=YOUR_TOUR_ID&dmc_id=YOUR_DMC_ID
/quotation-templates/drafts/detailed/preview?tour_id=YOUR_TOUR_ID&dmc_id=YOUR_DMC_ID
```

Optional query params: `currency=USD`, `logo_type=dmc|agency`.

This uses the **same quotation data builder path as PDF download**. It does **not** save to the database.

## 2. CLI check

Sample data only:

```bash
php artisan quotation-template:check-draft acco-service
```

Full tour data (same as PDF):

```bash
php artisan quotation-template:check-draft acco-service --tour=YOUR_TOUR_ID
php artisan quotation-template:check-draft acco-service --tour=YOUR_TOUR_ID --pdf
php artisan quotation-template:check-draft detailed --tour=YOUR_TOUR_ID --type=detailed
```

Previews are written under `storage/app/quotation-template-drafts/`.

## 3. Then save in Studio

If the preview looks correct, copy the `.html` / `.css` contents into the Studio editors and click **Save Draft**.
