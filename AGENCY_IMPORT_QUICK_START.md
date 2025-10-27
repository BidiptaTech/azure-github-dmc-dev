# Agency Import - Quick Start Guide

## 🚀 Access the Feature

**URL:** `/agencies/import`

**From UI:** 
- Go to Agencies List
- Click "Import Agencies" button (next to "Add New Agency")

---

## 📋 Quick Steps

### 1️⃣ Download Template
Click "Download CSV Template" button to get the pre-formatted template with sample data.

### 2️⃣ Fill Your Data
Open the template and add your agencies:

| Column Name | Required | Example |
|-------------|----------|---------|
| agency_name | ✅ Yes | ABC Travel Agency |
| email | ✅ Yes | info@abctravel.com |
| phone | ❌ No | +1234567890 |
| country | ✅ Yes | United States |
| city | ✅ Yes | New York |
| address | ❌ No | 123 Main Street |
| postal_code | ❌ No | 10001 |
| contact_person | ❌ No | John Doe |

### 3️⃣ Upload File
- Save as CSV format
- Upload on the import page
- Wait for confirmation

---

## ✨ Key Features

✅ **Auto-Generated IDs** - Unique agency_id created automatically
✅ **Smart Card Type** - ID card type auto-populated from country
✅ **Duplicate Prevention** - Existing emails are skipped
✅ **Restore Deleted** - Soft-deleted agencies restored if email matches
✅ **Detailed Errors** - Row-specific validation messages
✅ **Security** - Card numbers not imported for security

---

## ⚠️ Important Notes

- **Branches NOT imported** - Only head office details
- **Logos must be uploaded** manually after import
- **Max file size:** 10MB
- **Format:** CSV only (not Excel)
- **Status:** All imported agencies are Active by default

---

## 🎯 CSV Template Example

```csv
agency_name,email,phone,country,city,address,postal_code,contact_person
ABC Travel Agency,info@abctravel.com,+1234567890,United States,New York,123 Main Street,10001,John Doe
XYZ Tours,contact@xyztours.com,+9876543210,United Kingdom,London,456 High Street,SW1A 1AA,Jane Smith
Global Travels,info@global.com,+1122334455,France,Paris,789 Rue de Paris,75001,Marie Dupont
```

---

## 🐛 Common Issues & Solutions

| Problem | Solution |
|---------|----------|
| File format error | Save as CSV (Comma delimited) |
| Some rows skipped | Check required fields and duplicate emails |
| Card type not set | Verify country name matches database |
| File too large | Split into files of 500 records each |

---

## 📞 Need Help?

- Check the detailed error messages in the import results
- Review the full testing guide: `AGENCY_IMPORT_TESTING_GUIDE.md`
- Check Laravel logs: `storage/logs/laravel.log`

---

**Ready to import? Visit:** `/agencies/import`

