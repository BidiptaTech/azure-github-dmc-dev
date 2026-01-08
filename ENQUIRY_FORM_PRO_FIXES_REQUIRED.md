# Enquiry Form Pro - Fixes Required

## Issues Reported

### 1. **Can't Edit Tour Guide**
- **Issue**: Guide entries cannot be edited
- **Current**: Click on guide shows alert "You can edit guide details directly in the table cells"
- **Required**: Allow proper editing of guide entries

### 2. **Checkboxes Not Showing for Selected Items**
- **Issue**: Already selected transfers and guides don't show checkboxes
- **Current**: Checkbox column shows "Linked" text instead
- **Required**: Show checkboxes for all items to allow removal

### 3. **Update Logic Adding New Instead of Updating**
- **Issue**: When updating transfers/guides, it adds new entries instead of updating existing ones
- **Required**: Update existing entries when editing

### 4. **Attraction Don't Add Guide**
- **Issue**: Need clarification - attractions should not automatically add guides?
- **Required**: Clarify expected behavior

### 5. **Remove Fields from Restaurant**
- **Issue**: Restaurant section has unnecessary fields
- **Fields to Remove**:
  - Guide
  - Optional
  - Supplement
- **Required**: Clean up restaurant UI

### 6. **Accommodation Needs Transfer**
- **Issue**: No transfer option in accommodation section
- **Required**: Add transfer checkbox/option to accommodation

### 7. **Arrival/Departure Needs Transfer**
- **Issue**: No transfer option in arrival/departure sections
- **Required**: Add transfer option to entry and exit port sections

## Priority Order

1. **High Priority**:
   - Remove Guide/Optional/Supplement from restaurant (Quick fix)
   - Show checkboxes for all items (Affects user workflow)
   - Fix update logic (Data integrity issue)

2. **Medium Priority**:
   - Add transfer to Accommodation
   - Add transfer to Arrival/Departure
   - Fix guide edit functionality

3. **Low Priority**:
   - Clarify attraction guide behavior

## Implementation Plan

### Phase 1: Restaurant Cleanup
- Remove Guide field
- Remove Optional field
- Remove Supplement field
- Test restaurant add/edit functionality

### Phase 2: Checkbox Display Fix
- Update `updateGuideTable()` to always show checkboxes
- Update `updateTransferTable()` to always show checkboxes
- Remove "Linked" text logic
- Test selection and removal

### Phase 3: Update Logic Fix
- Implement proper edit mode detection
- Update instead of adding new when editing
- Test edit workflow

### Phase 4: Transfer Options
- Add transfer checkbox to accommodation modal
- Add transfer checkbox to arrival port modal
- Add transfer checkbox to departure port modal
- Implement transfer data saving

### Phase 5: Guide Edit Fix
- Implement proper guide edit modal
- Or enhance inline editing
- Test guide editing

## Files to Modify

1. `resources/views/enquiryform_pro/create.blade.php`
   - Restaurant section HTML
   - Guide table update function
   - Transfer table update function
   - Accommodation modal
   - Arrival/Departure modals
   - Update logic functions

## Testing Checklist

- [ ] Restaurant: No Guide/Optional/Supplement fields
- [ ] Guide table: All entries show checkboxes
- [ ] Transfer table: All entries show checkboxes
- [ ] Edit guide: Updates existing entry
- [ ] Edit transfer: Updates existing entry
- [ ] Accommodation: Has transfer option
- [ ] Arrival port: Has transfer option
- [ ] Departure port: Has transfer option
- [ ] Remove selected guides: Works correctly
- [ ] Remove selected transfers: Works correctly

## Questions for User

1. **Attraction Guide**: Should attractions never add guides, or should they have an optional guide field?
2. **Transfer Type**: For accommodation/arrival/departure transfers, what details are needed?
   - Just a checkbox?
   - Transfer type (private/SIC)?
   - Transfer way (1-way/2-way)?
   - Vehicle selection?

