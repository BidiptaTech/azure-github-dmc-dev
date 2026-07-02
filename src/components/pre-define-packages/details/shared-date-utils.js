/**
 * Formats a date using consistent formatting across the application
 */
export const formatDate = (date) => {
  if (!date) return '';
  
  // If date is a string, convert it to a Date object
  const dateObj = typeof date === 'string' ? new Date(date) : date;
  
  return dateObj.toLocaleDateString('en-GB', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: '2-digit'
  }).replace(',', '');
};

/**
 * Calculate the date for a specific day in the itinerary
 * This ensures consistent date calculation across all components
 * 
 * IMPORTANT: Always prioritizes the user's search date from searchParams over package details date
 */
export const getItineraryDayDate = (packageDetails, dayIndex) => {
  // Get the starting date, with fallbacks - ALWAYS prioritize searchParams.date if available
  let startDate;
  
  // First check for searchParams.date (search date has highest priority)
  if (packageDetails.searchParams?.date) {
    startDate = new Date(packageDetails.searchParams.date);
  }
  // Then check for date property from package details that was set from searchParams
  else if (packageDetails.date) {
    startDate = new Date(packageDetails.date);
  } 
  // Fallback to package start date
  else if (packageDetails.start_date) {
    startDate = new Date(packageDetails.start_date);
  } 
  // Last resort is current date
  else {
    startDate = new Date();
  }
  
  // Check if the date is valid, if not use current date
  if (isNaN(startDate.getTime())) {
    console.warn('Invalid date detected, using current date instead');
    startDate = new Date();
  }
  

  
  // Create a new date object to avoid modifying the original
  const dayDate = new Date(startDate);
  
  // Add the day index to get the specific day's date
  dayDate.setDate(startDate.getDate() + dayIndex);

  // const formatted = formatDate(dayDate);

  
  return dayDate;
};

/**
 * Format a date to DD-MM-YYYY format
 */
export const formatDateToDDMMYYYY = (dateString) => {
  if (!dateString) return '';
  
  const date = new Date(dateString);
  
  // Check if date is valid
  if (isNaN(date.getTime())) {
    return '';
  }
  
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  
  return `${day}-${month}-${year}`;
}; 