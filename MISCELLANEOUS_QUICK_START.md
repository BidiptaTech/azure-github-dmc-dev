# Miscellaneous Feature - Quick Start Guide

## What is it?
A new section in Enquiry Form Pro to add miscellaneous items like Airport Meet & Greet, Visa Assistance, Travel Insurance, etc.

## Where is it?
Located after the **Tour Guide** section in the main form.

## How to Use

### ➕ Adding Items

1. Click **"+ Add"** button
2. Select **Date**
3. Select **Destination** (items will load)
4. Check the items you want
5. Enter quantities (Adults/Child/Infant)
6. Modify charges if needed
7. Click **"Save & Close"**

**Tip**: Click **"Add Another"** to save and add more items without closing the modal.

### ✏️ Editing Items

1. Click the **item name** (blue underlined link) in the table
2. Modal opens with item data pre-filled
3. Modify quantities or charges
4. Click **"Update Item"**

### 🗑️ Removing Items

1. Check the items you want to remove
2. Click **"- Remove"** button
3. Confirm deletion

### 📋 Available Items (Dummy Data)

| Item | Adult Price | Child Price | Infant Price |
|------|-------------|-------------|--------------|
| Airport Meet & Greet | $50 | $25 | $0 |
| Visa Assistance | $100 | $100 | $0 |
| Travel Insurance | $75 | $50 | $25 |
| WiFi Device Rental | $15 | $0 | $0 |
| SIM Card | $20 | $20 | $0 |
| Porter Service | $30 | $0 | $0 |
| Baby Stroller Rental | $0 | $0 | $25 |
| Wheelchair Service | $40 | $0 | $0 |
| Photography Service | $200 | $0 | $0 |
| Laundry Service | $35 | $0 | $0 |

## Table Columns

| Column | Description |
|--------|-------------|
| ☑️ Checkbox | Select for removal |
| 📅 Date/Time | Service date |
| 📝 Item | Item name (clickable for edit) |
| 👨 Adults Qty | Number of adults |
| 💰 Cost/Pax | Cost per adult |
| 💵 Sell/Pax | Sell price per adult |
| 👶 Child Qty | Number of children |
| 💰 Cost/Pax | Cost per child |
| 💵 Sell/Pax | Sell price per child |
| 🍼 Infant Qty | Number of infants |
| 💰 Cost/Pax | Cost per infant |
| 💵 Sell/Pax | Sell price per infant |

## Tips & Tricks

✅ **Multi-Select**: You can select and add multiple items at once
✅ **Inline Edit**: Edit quantities and prices directly in the main table
✅ **Select All**: Use the checkbox in the header to select all items
✅ **Pre-filled Prices**: Default prices are loaded but can be modified
✅ **Validation**: Date and destination are required

## Common Scenarios

### Scenario 1: Add Airport Service
1. Click "+ Add"
2. Select date and destination
3. Check "Airport Meet & Greet"
4. Enter Adults: 2, Child: 1
5. Save

### Scenario 2: Add Multiple Services
1. Click "+ Add"
2. Select date and destination
3. Check "Visa Assistance", "Travel Insurance", "SIM Card"
4. Enter quantities for each
5. Save (creates 3 separate entries)

### Scenario 3: Update Quantities
**Option A - Quick Edit**:
- Change quantities directly in the main table

**Option B - Full Edit**:
- Click item name
- Modify in modal
- Click "Update Item"

### Scenario 4: Bulk Remove
1. Check multiple items in main table
2. Click "- Remove"
3. Confirm

## Keyboard Shortcuts

- **Tab**: Navigate between fields
- **Enter**: (in quantity fields) Move to next field
- **Esc**: Close modal without saving

## Validation Messages

| Message | Cause | Solution |
|---------|-------|----------|
| "Please select at least one miscellaneous item" | No items checked | Check at least one item |
| "Please select date and destination" | Missing required fields | Fill in date and destination |
| "Please select items to remove" | No items selected for removal | Check items before removing |

## Integration

✅ **Totals**: Automatically included in form totals
✅ **Dates**: Updates header date range
✅ **Submission**: Saved with form data

## Troubleshooting

**Q: Items not loading?**
A: Make sure you selected a destination first

**Q: Can't edit item?**
A: Click the item name (blue link), not the row

**Q: Changes not saving?**
A: Make sure to click "Save & Close" or "Update Item"

**Q: Select all not working?**
A: Items must be loaded first (select destination)

## Visual Guide

```
┌─────────────────────────────────────────────┐
│  Miscellaneous              [+ Add] [- Remove] │
├─────────────────────────────────────────────┤
│ ☑ Date       Item Name         Quantities    │
│ ☐ 20 Dec '25 Airport Meet...   2  $50  $50  │
│ ☐ 20 Dec '25 Visa Assistance   2 $100 $100  │
└─────────────────────────────────────────────┘
```

## Modal Layout

```
┌──────────────────────────────────────────────┐
│ 📝 Miscellaneous Items                    [X] │
├──────────────────────────────────────────────┤
│ Date: [20-12-2025]  Destination: [Singapore] │
├──────────────────────────────────────────────┤
│ ☑ Item Name           Adults  Child  Infant  │
│ ☐ Airport Meet...     [0] $50 [0] $25 [0] $0 │
│ ☐ Visa Assistance     [0] $100 [0] $100 [0]  │
│ ...                                           │
├──────────────────────────────────────────────┤
│        [Add Another] [Save & Close] [Close]   │
└──────────────────────────────────────────────┘
```

## Best Practices

1. ✅ Always select destination first
2. ✅ Review prices before saving
3. ✅ Use "Add Another" for multiple bookings
4. ✅ Edit inline for quick quantity changes
5. ✅ Use modal edit for price changes

## Next Steps

After adding miscellaneous items:
1. Review totals at bottom of form
2. Add other services (Hotels, Restaurants, etc.)
3. Submit the enquiry form

## Support

For issues or questions:
- Check console logs (F12 in browser)
- Verify date and destination are selected
- Ensure at least one item is checked
- Try refreshing the page if items don't load

---

**Status**: ✅ Fully Implemented with Dummy Data
**Last Updated**: December 20, 2025
