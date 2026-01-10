# Guide Popup - Quick Reference

## What Was Implemented

A compact, table-based guide selection popup with:
- **Date** field
- **Destination** dropdown (filters guides by city)
- **Guide** table showing:
  - Guide Name
  - Languages dropdown (from guide's available languages)
  - Hours dropdown (2, 4, 6, 8, 10, 12) - **Default: 12**
  - Day Rate (auto-populated based on hours)
  - Sell Price (editable)

## Key Features

### ✅ Three-Way Filtering
Guides are filtered by:
1. **DMC ID** - Only guides for user's DMC
2. **Status** - Only active guides
3. **Destination** - Only guides in selected city

### ✅ Dynamic Pricing
- Select hours → Cost auto-updates
- 12 hours (default) → Uses `twelve_hour_price` or `day_rate`
- Other hours → Uses corresponding price field

### ✅ Language Support
- Dropdown shows only languages the guide speaks
- Displays proficiency level (e.g., "English (Fluent)")

### ✅ Compact Design
- Similar to attractions modal
- Table-based for easy comparison
- Multi-select with checkboxes

## How It Works

### Opening Modal
```javascript
User clicks "+ Add" in Tour Guide section
→ Modal opens with date and destination pre-filled
→ Guides load automatically for default destination
```

### Selecting Guides
```javascript
1. Select destination (if different)
2. Guides table populates
3. Check guide(s) you want
4. Select language from dropdown
5. Select hours (default 12)
6. Cost auto-fills based on hours
7. Edit sell price if needed
8. Click "Save & Close" or "Add Another"
```

### Data Saved
```javascript
{
  dateTime: "2025-01-15",
  tourName: "Guide Service - John Doe",
  language: "English",
  name: "John Doe",
  hours: 12,
  cost: 150.00,
  sell: 150.00,
  guideId: 123
}
```

## Files Modified

| File | Changes |
|------|---------|
| `EnquiryFormPro.php` | Added `getGuidesByDestination()` AJAX method |
| `Guide.php` | Added `protected $primaryKey = 'guide_id'` |
| `web.php` | Added route for `get-guides` |
| `create.blade.php` | Replaced modal HTML + JavaScript functions |

## API Endpoint

**URL:** `/enquiry-form-pro/get-guides?destination=Singapore`

**Response:**
```json
{
  "success": true,
  "guides": [
    {
      "guide_id": 1,
      "name": "John Doe",
      "city": "Singapore",
      "day_rate": 150.00,
      "twelve_hour_price": 150.00,
      "languages": [
        {"language": "English", "proficiency": "Fluent"},
        {"language": "Mandarin", "proficiency": "Basic"}
      ]
    }
  ],
  "count": 1
}
```

## Testing

1. **Open Modal**: Click "+ Add" in Tour Guide section
2. **Select Destination**: Choose "Singapore"
3. **Verify Filters**: Only Singapore guides for your DMC should appear
4. **Select Guide**: Check a guide
5. **Choose Language**: Pick from dropdown
6. **Choose Hours**: Select 12 (default) or other
7. **Verify Pricing**: Cost should match the hour price
8. **Save**: Click "Save & Close"
9. **Verify**: Guide appears in Tour Guide table

## Quick Troubleshooting

| Issue | Solution |
|-------|----------|
| No guides showing | Check destination is selected and guides exist for that city |
| Language dropdown empty | Guide has no languages assigned in database |
| Cost is 0 | Guide has no pricing set for selected hours |
| Can't select guide | Make sure checkbox is checked and language is selected |

## Default Values

- **Hours**: 12 (full day)
- **Cost**: `twelve_hour_price` or `day_rate`
- **Sell**: Same as cost (editable)
- **Date**: Tour start date from header
- **Destination**: User's country from header

## Comparison with Old Modal

| Feature | Old Modal | New Modal |
|---------|-----------|-----------|
| Design | Simple form | Table-based |
| Guide Selection | Manual entry | Select from list |
| Languages | Manual entry | Dropdown from guide's languages |
| Pricing | Manual entry | Auto-populated by hours |
| Filtering | None | DMC + Status + Destination |
| Batch Add | No | Yes (multiple guides) |
| Reference | Single Tour Package | Similar to Attractions modal |

## Summary

✅ Compact design like attractions modal  
✅ Date, Destination, Guide fields  
✅ Languages dropdown from guide's available languages  
✅ Hours selector (2, 4, 6, 8, 10, 12) with default 12  
✅ Day rate auto-populates based on hours  
✅ Dynamic filtering by DMC ID, status, and destination  
✅ Multi-select support  
✅ Consistent with single tour package patterns  

The implementation is complete and ready to use! 🎉

