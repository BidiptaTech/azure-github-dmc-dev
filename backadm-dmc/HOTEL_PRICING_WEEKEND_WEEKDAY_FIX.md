# Hotel Pricing Fix: Weekday/Weekend Night-by-Night Calculation

## Problem Description

### Previous Behavior (INCORRECT)
The hotel pricing system was checking **only the check-in date** to determine if the stay was a weekend or weekday, and then applying that single price type to **ALL nights** in the booking.

**Example of the problem:**
- If a user selected 10 nights (e.g., Thursday to Sunday of next week)
- If check-in was Thursday (weekday), ALL 10 nights were priced at weekday rates
- Even though Saturday and Sunday should have been charged at weekend rates
- This resulted in incorrect pricing that didn't reflect the actual weekend/weekday distribution

### New Behavior (CORRECT)
The system now calculates the price **for each individual night** based on its specific day of the week, then sums them up for the total.

**Example of correct behavior:**
- User selects 10 nights spanning Thursday to Sunday of next week
- System identifies which nights fall on weekends (Saturday, Sunday)
- Applies weekend pricing to those specific nights
- Applies weekday pricing to the remaining nights
- Calculates accurate total: (weekday_count × weekday_price) + (weekend_count × weekend_price)

## Technical Changes

### 1. `addHotel()` Function (Lines 7237-7303)

#### Before:
```javascript
// Determine if it's weekend based on the check-in date
const checkInDate = moment(tourStartDate).add(startNight-1, 'days');
const dayOfWeek = checkInDate.day(); // 0 = Sunday, 6 = Saturday
const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;

if (isSingleOccupancy) {
    if (isWeekend) {
        roomPrice = parseFloat(selectedOption.dataset.weekendPrice) || 0;
        priceType = 'Single Weekend';
    } else {
        roomPrice = parseFloat(selectedOption.dataset.weekdayPrice) || 0;
        priceType = 'Single Weekday';
    }
}
```

**Issue:** Only checked check-in date, applied single price to all nights.

#### After:
```javascript
// Get prices from dataset
const weekdayPrice = isSingleOccupancy 
    ? parseFloat(selectedOption.dataset.weekdayPrice) || 0
    : parseFloat(selectedOption.dataset.doubleWeekdayPrice) || 0;
const weekendPrice = isSingleOccupancy 
    ? parseFloat(selectedOption.dataset.weekendPrice) || 0
    : parseFloat(selectedOption.dataset.doubleWeekendPrice) || 0;

// Calculate price for each selected night
nightNumbers.forEach(nightNum => {
    const nightDate = moment(tourStartDate).add(nightNum-1, 'days');
    const dayOfWeek = nightDate.day(); // 0 = Sunday, 6 = Saturday
    const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
    
    const nightPrice = isWeekend ? weekendPrice : weekdayPrice;
    totalRoomPrice += nightPrice;
    
    if (isWeekend) {
        weekendNights++;
    } else {
        weekdayNights++;
    }
    
    priceBreakdown.push({
        night: nightNum,
        date: nightDate.format('MMM DD'),
        dayOfWeek: nightDate.format('ddd'),
        isWeekend: isWeekend,
        price: nightPrice
    });
});
```

**Fix:** Loops through each night, checks its day of week, applies correct price, maintains running totals.

### 2. Hotel Data Structure (Lines 7351-7374)

#### New Fields Added:
```javascript
const hotelData = {
    // ... existing fields ...
    price: totalRoomPrice,              // Total calculated room price for all nights
    pricePerNight: totalRoomPrice / nightNumbers.length,  // Average (for reference)
    priceBreakdown: priceBreakdown,     // Detailed per-night pricing array
    weekdayNights: weekdayNights,       // Count of weekday nights
    weekendNights: weekendNights,       // Count of weekend nights
    // ... rest of fields ...
};
```

**Purpose:** Store comprehensive pricing information for accurate calculations and display.

### 3. Display Function - `displaySelectedHotels()` (Lines 7515-7557)

#### Updated Cost Summary Display:
```javascript
<div class="d-flex justify-content-between">
    <span>Room Cost:</span>
    <span>
        ${hotel.weekdayNights || hotel.weekendNights ? `
            ${hotel.weekdayNights ? `${hotel.weekdayNights} weekday @ $${(hotel.price / hotel.totalNights).toFixed(2)}/night` : ''}
            ${hotel.weekdayNights && hotel.weekendNights ? ' + ' : ''}
            ${hotel.weekendNights ? `${hotel.weekendNights} weekend @ $${(hotel.price / hotel.totalNights).toFixed(2)}/night` : ''}
            × ${hotel.numberOfRooms} room(s) = 
        ` : ''}
        $${(hotel.price || 0) * hotel.numberOfRooms}
    </span>
</div>
```

**Result:** Shows user the weekday/weekend night breakdown in the UI.

### 4. `updateHotelDataField()` Function (Lines 1389-1431)

#### Updated Calculation Logic:
```javascript
totalPrice: (() => {
    // Use the stored price from when the hotel was added
    // This price already includes the correct weekday/weekend calculation for all nights
    let totalRoomPrice = parseFloat(hotel.price) || 0;
    
    // If no stored price, calculate from scratch using per-night breakdown
    if (totalRoomPrice === 0 && hotel.priceBreakdown && hotel.priceBreakdown.length > 0) {
        // Sum up the per-night prices
        totalRoomPrice = hotel.priceBreakdown.reduce((sum, night) => sum + night.price, 0);
        console.log(`Calculated room price from breakdown: $${totalRoomPrice}`);
    }
    
    const numRooms = parseInt(hotel.numberOfRooms) || 1;
    const numNights = parseInt(hotel.totalNights) || 1;
    
    // Calculate room cost (price already includes all nights, just multiply by rooms)
    const roomCost = totalRoomPrice * numRooms;
    
    // ... meal and extra bed calculations ...
    
    const total = roomCost + mealCost + extraBedCost;
    return total;
})()
```

**Key Change:** 
- Before: `roomCost = roomPrice * numRooms * numNights` (incorrectly multiplied by nights twice)
- After: `roomCost = totalRoomPrice * numRooms` (price already includes all nights correctly calculated)

## Data Flow

1. **User selects nights:** System tracks all selected night numbers
2. **User clicks "Add Hotel":** `addHotel()` function triggered
3. **Price calculation:**
   - Loop through each selected night
   - Check day of week for that specific night
   - Apply weekend or weekday price accordingly
   - Sum up all night prices
   - Track weekday/weekend night counts
4. **Store hotel data:** Save total price, counts, and breakdown array
5. **Display:** Show breakdown to user
6. **Submit:** `updateHotelDataField()` uses stored price (no recalculation needed)
7. **Backend:** Receives accurate total price in JSON

## Example Calculation

### Scenario:
- Tour starts: Thursday, November 7, 2025
- Selected nights: 1-10 (Thursday Nov 7 → Sunday Nov 17)
- Room type: Double occupancy
- Weekday price: $100/night
- Weekend price: $150/night

### Night-by-Night Breakdown:
```
Night 1 (Thu Nov 7)  - Weekday - $100
Night 2 (Fri Nov 8)  - Weekday - $100
Night 3 (Sat Nov 9)  - Weekend - $150
Night 4 (Sun Nov 10) - Weekend - $150
Night 5 (Mon Nov 11) - Weekday - $100
Night 6 (Tue Nov 12) - Weekday - $100
Night 7 (Wed Nov 13) - Weekday - $100
Night 8 (Thu Nov 14) - Weekday - $100
Night 9 (Fri Nov 15) - Weekday - $100
Night 10 (Sat Nov 16) - Weekend - $150
```

### Total Calculation:
- Weekday nights: 7 × $100 = $700
- Weekend nights: 3 × $150 = $450
- **Total room price: $1,150**

### If 2 rooms booked:
- Room cost: $1,150 × 2 = $2,300
- (Plus meals and extras)

## Console Logging

The fix includes comprehensive console logging for debugging:

```javascript
console.log(`=== PRICE BREAKDOWN FOR ${roomType} ===`);
console.log(`Total nights: ${nightNumbers.length}`);
console.log(`Weekday nights: ${weekdayNights} @ $${weekdayPrice} each`);
console.log(`Weekend nights: ${weekendNights} @ $${weekendPrice} each`);
console.log(`Total room price: $${totalRoomPrice}`);
console.log('Per-night breakdown:', priceBreakdown);
```

## Testing Recommendations

### Test Case 1: All Weekdays
- Select 5 consecutive weekday nights (Mon-Fri)
- Verify: All charged at weekday rate
- Expected: weekdayNights = 5, weekendNights = 0

### Test Case 2: All Weekends
- Select Saturday and Sunday nights only
- Verify: All charged at weekend rate
- Expected: weekdayNights = 0, weekendNights = 2

### Test Case 3: Mixed (Critical Test)
- Select 10 nights spanning Thu-Sun of next week
- Verify: Correct count of weekday vs weekend nights
- Verify: Total = (weekday_count × weekday_price) + (weekend_count × weekend_price)
- Expected: weekdayNights = 7, weekendNights = 3

### Test Case 4: Single Occupancy
- Repeat above tests with single guest
- Verify: Uses single occupancy prices

### Test Case 5: Multiple Rooms
- Select mixed nights with 3 rooms
- Verify: Total = calculated_price × 3

## Benefits

1. **Accuracy:** Pricing now reflects actual weekend/weekday distribution
2. **Transparency:** Users see breakdown of weekday/weekend nights
3. **Flexibility:** Handles any combination of nights correctly
4. **Auditability:** Console logs provide detailed pricing breakdown
5. **Maintainability:** Clear code structure with per-night calculation logic

## Files Modified

- `resources/views/single-tour-package/create.blade.php`
  - `addHotel()` function (lines 7237-7303)
  - Hotel data structure (lines 7351-7374)
  - `displaySelectedHotels()` function (lines 7515-7557)
  - `updateHotelDataField()` function (lines 1389-1431)

## No Breaking Changes

- Existing hotel data structure remains compatible
- New fields are optional (backward compatible)
- Display gracefully handles missing weekday/weekend counts
- Fallback logic included for edge cases

