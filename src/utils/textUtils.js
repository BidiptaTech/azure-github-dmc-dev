// Utility function to capitalize the first letter of each word in a string
export const capitalizeWords = (text) => {
  if (!text || typeof text !== 'string') return text;
  
  return text
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ');
};

// Utility function to format package names (alias for capitalizeWords for consistency)
export const formatPackageName = (packageName) => {
  return capitalizeWords(packageName);
};

// Utility function to format ticket names
export const formatTicketName = (ticketName) => {
  return capitalizeWords(ticketName);
};

// Utility function to format attraction names
export const formatAttractionName = (attractionName) => {
  return capitalizeWords(attractionName);
}; 