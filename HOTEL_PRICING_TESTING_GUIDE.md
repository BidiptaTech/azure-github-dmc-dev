# Hotel Pricing Fix - Testing Guide

## Quick Test Checklist

Use this guide to verify the weekend/weekday pricing fix is working correctly.

---

## Prerequisites

1. ✅ Browser console open (F12 → Console tab)
2. ✅ Test hotel with different weekday/weekend prices configured
3. ✅ Tour package creation page loaded

---

## Test Case 1: All Weekday Nights ⭐ CRITICAL

### Setup
- Start date: **Monday**
- Select nights: **1-5** (Monday through Friday)
- Room type with prices:
  - Weekday: $100
  - Weekend: $150

### Steps
1. Create tour with Monday start date
2. Select hotel
3. Select room type
4. Click nights 1-5
5. Click "Add Hotel"

### Expected Results
```javascript
// Console should show:
=== PRICE BREAKDOWN ===
Total nights: 5
Weekday nights: 5 @ $100 each
Weekend nights: 0 @ $150 each
Total room price: $500

// UI should show:
Room Cost: 5 weekday @ $100/night × 1 room(s) = $500
```

### ✅ Pass Criteria
- `weekdayNights = 5`
- `weekendNights = 0`
- `Total room price = $500`

---

## Test Case 2: All Weekend Nights ⭐ CRITICAL

### Setup
- Start date: **Saturday**
- Select nights: **1-2** (Saturday, Sunday)
- Room type with prices:
  - Weekday: $100
  - Weekend: $150

### Steps
1. Create tour with Saturday start date
2. Select hotel
3. Select room type
4. Click nights 1-2
5. Click "Add Hotel"

### Expected Results
```javascript
// Console should show:
=== PRICE BREAKDOWN ===
Total nights: 2
Weekday nights: 0 @ $100 each
Weekend nights: 2 @ $150 each
Total room price: $300

// UI should show:
Room Cost: 2 weekend @ $150/night × 1 room(s) = $300
```

### ✅ Pass Criteria
- `weekdayNights = 0`
- `weekendNights = 2`
- `Total room price = $300`

---

## Test Case 3: Mixed Nights (10-Night Stay) ⭐⭐⭐ MOST CRITICAL

### Setup
- Start date: **Thursday, November 7, 2025**
- Select nights: **1-10** (Thu Nov 7 → Sun Nov 17)
- Room type with prices:
  - Weekday: $100
  - Weekend: $150
- Number of rooms: **2**

### Steps
1. Create tour with Thursday start date
2. Select hotel
3. Select room type
4. Click nights 1-10 (select all)
5. Set number of rooms to 2
6. Click "Add Hotel"

### Expected Results
```javascript
// Console should show:
=== PRICE BREAKDOWN ===
Total nights: 10
Weekday nights: 7 @ $100 each
Weekend nights: 3 @ $150 each
Total room price: $1,150

Per-night breakdown: [
  { night: 1, date: 'Nov 07', dayOfWeek: 'Thu', isWeekend: false, price: 100 },
  { night: 2, date: 'Nov 08', dayOfWeek: 'Fri', isWeekend: false, price: 100 },
  { night: 3, date: 'Nov 09', dayOfWeek: 'Sat', isWeekend: true, price: 150 },
  { night: 4, date: 'Nov 10', dayOfWeek: 'Sun', isWeekend: true, price: 150 },
  { night: 5, date: 'Nov 11', dayOfWeek: 'Mon', isWeekend: false, price: 100 },
  { night: 6, date: 'Nov 12', dayOfWeek: 'Tue', isWeekend: false, price: 100 },
  { night: 7, date: 'Nov 13', dayOfWeek: 'Wed', isWeekend: false, price: 100 },
  { night: 8, date: 'Nov 14', dayOfWeek: 'Thu', isWeekend: false, price: 100 },
  { night: 9, date: 'Nov 15', dayOfWeek: 'Fri', isWeekend: false, price: 100 },
  { night: 10, date: 'Nov 16', dayOfWeek: 'Sat', isWeekend: true, price: 150 }
]

Hotel pricing for [Hotel Name]: Total room price (all nights): $1,150, Rooms: 2, Room cost: $2,300
  - Weekday nights: 7, Weekend nights: 3

// UI should show:
Room Cost: 7 weekday @ $100/night + 3 weekend @ $150/night × 2 room(s) = $2,300
```

### Manual Verification
**Calculate manually:**
- Weekday nights: Thu, Fri, Mon, Tue, Wed, Thu, Fri = 7 nights × $100 = $700
- Weekend nights: Sat, Sun, Sat = 3 nights × $150 = $450
- Total per room: $700 + $450 = $1,150
- For 2 rooms: $1,150 × 2 = $2,300 ✅

### ✅ Pass Criteria
- `weekdayNights = 7`
- `weekendNights = 3`
- `Total room price = $1,150` (per room)
- `Room cost = $2,300` (for 2 rooms)
- Breakdown shows correct day of week for each night

---

## Test Case 4: Single Occupancy Pricing

### Setup
- Start date: **Thursday**
- Select nights: **1-5** (includes 1 weekend)
- **Adults: 1** (single occupancy)
- Room type with prices:
  - Single weekday: $80
  - Single weekend: $120
  - Double weekday: $100
  - Double weekend: $150

### Steps
1. Set adults to 1
2. Create tour with Thursday start date
3. Select hotel
4. Select room type
5. Click nights 1-5
6. Click "Add Hotel"

### Expected Results
```javascript
// Should use SINGLE occupancy prices
=== PRICE BREAKDOWN ===
Total nights: 5
Weekday nights: 4 @ $80 each
Weekend nights: 1 @ $120 each
Total room price: $440  // (4 × $80) + (1 × $120)
```

### ✅ Pass Criteria
- Uses single occupancy prices (not double)
- `Total room price = $440`

---

## Test Case 5: Form Submission & Data Persistence

### Setup
- Complete Test Case 3 setup
- Add hotel with mixed nights

### Steps
1. Add hotel with 10 nights (7 weekday, 3 weekend)
2. Open browser console
3. Click "Save All Bookings" or submit form
4. Check console logs

### Expected Results
```javascript
// In updateHotelDataField() logs:
=== FINAL HOTEL PRICING SUMMARY ===
Hotel 1 ([Hotel Name]):
  - Calculated totalPrice: $2,300
  - Weekday nights: 7, Weekend nights: 3

// The hotel_data field should contain:
{
  "hotelDetails": { ... },
  "totalPrice": 2300,
  "priceBreakdown": [ ... ],  // Array of 10 nights
  ...
}
```

### ✅ Pass Criteria
- `totalPrice` in JSON matches displayed price
- Price breakdown array has 10 entries
- Backend receives correct total

---

## Test Case 6: Multiple Hotels with Different Night Patterns

### Setup
- Tour: 10 nights total
- Hotel 1: Nights 1-3 (mixed)
- Hotel 2: Nights 4-6 (weekend)
- Hotel 3: Nights 7-10 (weekdays)

### Steps
1. Create 10-night tour starting Thursday
2. Add Hotel 1 for nights 1-3
3. Add Hotel 2 for nights 4-6
4. Add Hotel 3 for nights 7-10
5. Check each hotel's pricing

### Expected Results
Each hotel should have its own accurate weekday/weekend breakdown:

**Hotel 1 (Thu-Sat):**
- Weekday: 2, Weekend: 1
- Price: (2 × $100) + (1 × $150) = $350

**Hotel 2 (Sun-Tue):**
- Weekday: 2, Weekend: 1
- Price: (2 × $100) + (1 × $150) = $350

**Hotel 3 (Wed-Sat):**
- Weekday: 3, Weekend: 1
- Price: (3 × $100) + (1 × $150) = $450

**Total:** $350 + $350 + $450 = $1,150

### ✅ Pass Criteria
- Each hotel calculated independently
- Sum of all hotels equals expected total

---

## Test Case 7: Edge Case - Non-Consecutive Nights

### Setup
- Start date: **Friday**
- Select **non-consecutive** nights: 1, 2, 5, 6 (Fri, Sat, Tue, Wed)

### Steps
1. Create tour with Friday start
2. Select hotel
3. Click nights 1, 2, 5, 6 only (skip 3, 4)
4. Add hotel

### Expected Results
```javascript
=== PRICE BREAKDOWN ===
Total nights: 4
Weekday nights: 2 @ $100 each  // Tue, Wed
Weekend nights: 2 @ $150 each  // Fri, Sat
Total room price: $500
```

### ✅ Pass Criteria
- System correctly identifies Fri and Sat as weekend
- System correctly identifies Tue and Wed as weekday
- Total: (2 × $100) + (2 × $150) = $500

---

## Red Flag Scenarios (Should NOT Happen)

### ❌ FAIL Indicators

1. **All nights have same price despite spanning weekday/weekend**
   ```javascript
   // BAD - This should NOT appear:
   Weekday nights: 10 @ $100 each
   Weekend nights: 0
   // When actual selection includes Saturday/Sunday
   ```

2. **Weekend count is 0 when Saturday/Sunday are included**
   ```javascript
   // BAD - If nights include Sat/Sun:
   Weekend nights: 0  // ❌ WRONG
   ```

3. **Total doesn't match manual calculation**
   ```javascript
   // If manual: (7 × $100) + (3 × $150) = $1,150
   // But system shows:
   Total room price: $1,000  // ❌ WRONG
   ```

4. **Price breakdown array is empty or missing**
   ```javascript
   priceBreakdown: []  // ❌ Should have entries
   ```

5. **Same price for Saturday and Tuesday**
   ```javascript
   // BAD:
   { night: 3, dayOfWeek: 'Sat', price: 100 }  // ❌ Should be 150
   { night: 5, dayOfWeek: 'Tue', price: 100 }  // ✅ Correct
   ```

---

## Console Debugging Commands

### Check Current Hotel Data
```javascript
// In browser console after adding hotel:
console.log(selectedHotels);

// Should show array with hotel objects containing:
// - price (total for all nights)
// - weekdayNights
// - weekendNights
// - priceBreakdown (array)
```

### Verify Night Calculation
```javascript
// Check a specific hotel:
const hotel = selectedHotels[0];
console.log('Weekday nights:', hotel.weekdayNights);
console.log('Weekend nights:', hotel.weekendNights);
console.log('Total nights:', hotel.totalNights);
console.log('Price breakdown:', hotel.priceBreakdown);

// Verify math:
const manualTotal = hotel.priceBreakdown.reduce((sum, night) => sum + night.price, 0);
console.log('Manual total:', manualTotal);
console.log('Stored total:', hotel.price);
console.log('Match:', manualTotal === hotel.price);  // Should be true
```

---

## Common Issues & Troubleshooting

### Issue: All nights showing same price
**Cause:** Old code still in cache  
**Fix:** Hard refresh (Ctrl+Shift+R or Cmd+Shift+R)

### Issue: Weekend count is always 0
**Cause:** Day of week calculation error  
**Check:** Verify tourStartDate is set correctly  
```javascript
console.log('Tour start date:', tourStartDate);
```

### Issue: Price breakdown not showing in UI
**Cause:** Missing weekdayNights/weekendNights in hotel object  
**Check:** 
```javascript
console.log(hotel.weekdayNights, hotel.weekendNights);
// Should NOT be undefined
```

---

## Success Criteria Summary

For the fix to be considered successful:

✅ **Accuracy:** Each night priced according to its day of week  
✅ **Weekday/Weekend Split:** Correctly counted and displayed  
✅ **UI Transparency:** User sees breakdown in display  
✅ **Console Logs:** Detailed per-night breakdown visible  
✅ **Data Persistence:** Correct price sent to backend  
✅ **Multiple Rooms:** Price correctly multiplied  
✅ **Edge Cases:** Non-consecutive nights handled  
✅ **Occupancy Types:** Single and double priced differently  

---

## Quick Pass/Fail Test

**Run Test Case 3 (10 nights, Thu-Sun):**
- ✅ PASS: Console shows "Weekday nights: 7" and "Weekend nights: 3"
- ❌ FAIL: Console shows any other counts or missing counts

**If PASS:** Fix is working correctly! 🎉  
**If FAIL:** Check console for errors and verify code changes applied

