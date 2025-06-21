# React + Vite

This template provides a minimal setup to get React working in Vite with HMR and some ESLint rules.

Currently, two official plugins are available:

- [@vitejs/plugin-react](https://github.com/vitejs/vite-plugin-react/blob/main/packages/plugin-react/README.md) uses [Babel](https://babeljs.io/) for Fast Refresh
- [@vitejs/plugin-react-swc](https://github.com/vitejs/vite-plugin-react-swc) uses [SWC](https://swc.rs/) for Fast Refresh

# Dashboard Code Refactoring

The codebase has been refactored to improve maintainability, readability, and separation of concerns by splitting the large Pending.jsx file into multiple components.

## Original Issue

The original Pending.jsx file was extremely long (over 6800 lines) and included multiple unrelated functionalities:
- Main dashboard UI and listing logic
- PDF generation functionality
- Enquiry handling and submission logic

This made the file difficult to maintain and understand.

## Refactoring Solution

The code has been refactored into three separate components:

1. **Pending.jsx**: The main component that handles:
   - Displaying the dashboard listings
   - Basic state management
   - Routing and navigation
   - Main UI elements

2. **PdfGenerator.jsx**: A dedicated component for PDF functionality:
   - PDF generation logic
   - Pricing calculation (markup and discount)
   - PDF styling and formatting
   - PDF download functionality

3. **EnquiryHandler.jsx**: A component for handling all enquiry-related tasks:
   - Enquiry form and submission
   - Enquiry history display
   - Enquiry status management
   - Accept/Cancel enquiry actions

## Key Benefits

- **Improved Maintainability**: Each component now has a single responsibility
- **Better Code Organization**: Related functionality is grouped together
- **Reduced File Size**: Each file is now manageable in size
- **Enhanced Readability**: Easier to understand what each component does
- **Easier Testing**: Components can be tested in isolation
- **Improved Performance**: Smaller components may lead to better rendering performance

## Usage

The components are used in the main Pending.jsx file as follows:

```jsx
// PDF Generation
<PdfGenerator
  bookings={bookings}
  isPrintModalVisible={isPrintModalVisible}
  setIsPrintModalVisible={setIsPrintModalVisible}
  setOpenSnackbar={setOpenSnackbar}
  setSnackbarMessage={setSnackbarMessage}
  setSnackbarSeverity={setSnackbarSeverity}
/>

// Enquiry Handling
<EnquiryHandler
  bookings={bookings}
  isEnquiryModalVisible={isEnquiryModalVisible}
  setIsEnquiryModalVisible={setIsEnquiryModalVisible}
  tourId={tourId}
  onSuccess={handleSuccessfulEnquiry}
  bookingType1={bookingType1}
  displayId={displayId}
/>
```

## Future Improvements

Further refactoring opportunities:
- Break down the UI/Table components into smaller, reusable pieces
- Create shared utilities for commonly used functions
- Implement proper TypeScript typing for better code safety
- Add comprehensive testing for each component
