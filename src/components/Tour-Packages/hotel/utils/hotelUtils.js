import moment from 'moment';

/**
 * Generates a unique ID for hotel configurations
 * @returns {string} A unique ID
 */
export const generateUniqueId = () => {
  return Math.random().toString(36).substring(2) + Date.now().toString(36);
};

/**
 * Calculate night indices for hotel dates within the full tour date range
 * @param {string} hotelCheckIn Hotel check-in date in DD/MM/YYYY format
 * @param {string} hotelCheckOut Hotel check-out date in DD/MM/YYYY format
 * @param {Array} tourDates Array of moment objects representing the full tour date range
 * @returns {Array} Array of night indices within the tour date range
 */
export const calculateNightIndicesInTourRange = (hotelCheckIn, hotelCheckOut, tourDates) => {
  if (!hotelCheckIn || !hotelCheckOut || !tourDates || tourDates.length === 0) {
    return [];
  }
  
  // Convert hotel dates to moment objects
  const hotelStartDate = moment(hotelCheckIn, 'DD/MM/YYYY');
  const hotelEndDate = moment(hotelCheckOut, 'DD/MM/YYYY');
  
  // Find the start index in the tour date range
  let startIndex = -1;
  for (let i = 0; i < tourDates.length; i++) {
    if (tourDates[i].isSame(hotelStartDate, 'day')) {
      startIndex = i;
      break;
    }
  }
  
  // Find the end index in the tour date range
  let endIndex = -1;
  for (let i = 0; i < tourDates.length; i++) {
    if (tourDates[i].isSame(hotelEndDate, 'day')) {
      endIndex = i;
      break;
    }
  }
  
  // If dates are not found in tour range, return empty array
  if (startIndex === -1 || endIndex === -1 || startIndex >= endIndex) {
    console.warn("Hotel dates not found in tour range or invalid:", {
      hotelCheckIn,
      hotelCheckOut,
      tourRange: tourDates.map(d => d.format('YYYY-MM-DD')),
      startIndex,
      endIndex
    });
    return [];
  }
  
  // Create night indices array (from start to end-1, since last day is checkout)
  const nightIndices = [];
  for (let i = startIndex; i < endIndex; i++) {
    nightIndices.push(i);
  }
  
  console.log("Calculated night indices for hotel within tour range:", {
    hotelCheckIn,
    hotelCheckOut,
    startIndex,
    endIndex,
    nightIndices,
    nights: nightIndices.length
  });
  
  return nightIndices;
};

/**
 * Calculate nights from booking dates
 * @param {Array} bookingDates Array of booking dates in YYYY-MM-DD format
 * @returns {Object} Object containing nights count and selected night indices
 */
export const calculateNightsFromDates = (bookingDates) => {
  let nights = 1;
  let selectedNightIndices = [0];
  
  if (bookingDates && bookingDates.length >= 2) {
    const startDate = moment(bookingDates[0]);
    const endDate = moment(bookingDates[1]);
    nights = endDate.diff(startDate, 'days');
    
    // Create selected night indices
    selectedNightIndices = [];
    for (let i = 0; i < nights; i++) {
      selectedNightIndices.push(i);
    }
  }
  
  return { nights, selectedNightIndices };
};

/**
 * Get occupancy type based on max_occupancy
 * @param {number} maxOccupancy Maximum occupancy
 * @returns {string} Occupancy type (single, double, triple)
 */
export const getOccupancyType = (maxOccupancy) => {
  return maxOccupancy === 1 ? 'single' : 
         maxOccupancy === 2 ? 'double' : 'triple';
};

/**
 * Extract meal plans for guests from bed data
 * @param {Object} bed Bed data object
 * @param {Array} mealPlanOptions Available meal plan options
 * @returns {Array} Array of meal plan data objects for each guest
 */
export const extractGuestMealPlans = (bed, mealPlanOptions) => {
  const guestMealPlans = [];
  const headCount = bed.head_count || 1;
  
  console.log("Extracting meal plans for bed:", bed);
  console.log("Available meal plan options:", mealPlanOptions);
  
  for (let i = 1; i <= headCount; i++) {
    const mealKey = `meal_${i}`;
    if (bed.selectedMeals && bed.selectedMeals[mealKey]) {
      const mealType = bed.selectedMeals[mealKey].type;
      const mealPrice = bed.selectedMeals[mealKey].price || 0;
      
      // Find the corresponding meal plan ID from options
      const mealPlan = mealPlanOptions.find(plan => plan.title === mealType);
      
      // Create meal plan data object with both ID and name/price
      const mealPlanData = {
        id: mealPlan ? mealPlan.id : 'self',
        name: mealType,
        price: mealPrice
      };
      
      console.log(`Guest ${i} meal plan:`, mealPlanData);
      guestMealPlans.push(mealPlanData);
    } else {
      // Default meal plan data
      const defaultMealPlan = { id: 'self', name: 'Room Only', price: 0 };
      console.log(`Guest ${i} default meal plan:`, defaultMealPlan);
      guestMealPlans.push(defaultMealPlan);
    }
  }
  
  console.log("Final guest meal plans:", guestMealPlans);
  return guestMealPlans;
};

/**
 * Create a hotel configuration from room and bed data
 * @param {Object} hotelDetails Hotel details object
 * @param {Object} room Room data object
 * @param {Object} bed Bed data object
 * @param {Array} bookingDates Array of booking dates
 * @param {Array} mealPlanOptions Available meal plan options
 * @param {string|number} bookingId Booking ID for this configuration
 * @returns {Object} Hotel configuration object
 */
export const createConfigFromRoomAndBed = (hotelDetails, room, bed, bookingDates, mealPlanOptions, bookingId = null) => {
  const guestMealPlans = extractGuestMealPlans(bed, mealPlanOptions);
  const { nights, selectedNightIndices } = calculateNightsFromDates(bookingDates);
  
  // Extract specific bed details
  const bedData = {
    bedTypeId: String(bed.bed_id || ''),
    bedTypeName: bed.bed_type || '',
    maxOccupancy: parseInt(bed.max_occupancy || 1),
    bedPrice: parseFloat(bed.price || 0),
    headCount: parseInt(bed.head_count || 1),
    babyCot: bed.baby_cot === 1
  };
  
  // Extract room details
  const roomData = {
    roomTypeId: String(room.room_id || ''),
    roomTypeName: room.room_type || ''
  };
  
  // Create the hotel configuration object
  const config = {
    id: Math.random().toString(36).substring(2) + Date.now().toString(36),
    hotelId: String(hotelDetails.hotel_id || ''),
    hotelDetails: {
      ...hotelDetails,
      // Ensure required fields are present
      hotel_name: hotelDetails.hotel_name || hotelDetails.name || 'Unknown Hotel',
      image: hotelDetails.image || hotelDetails.main_image || hotelDetails.logo || ''
    },
    roomTypeId: roomData.roomTypeId,
    roomTypeName: roomData.roomTypeName,
    bedTypeId: bedData.bedTypeId,
    bedTypeName: bedData.bedTypeName,
    max_occupancy: bedData.maxOccupancy,
    bedPrice: bedData.bedPrice,
    mealPlanId: 'self', // Default meal plan
    nights: nights,
    selectedNightIndices: selectedNightIndices,
    // Add individual hotel booking dates from the booking data
    hotelCheckIn: bookingDates.length > 0 ? moment(bookingDates[0], 'YYYY-MM-DD').format('DD/MM/YYYY') : moment().format('DD/MM/YYYY'),
    hotelCheckOut: bookingDates.length > 1 ? moment(bookingDates[1], 'YYYY-MM-DD').format('DD/MM/YYYY') : moment().add(1, 'day').format('DD/MM/YYYY'),
    babyCot: bedData.babyCot,
    occupancyType: bedData.headCount > 1 ? 'multiple' : 'single',
    adultDistribution: { male: 0, female: 0 },
    expanded: true,
    selectedGuests: bedData.headCount,
    guestMealPlans: guestMealPlans,
    // Store original booking data for reference
    originalData: {
      hotelDetails: { ...hotelDetails },
      room: { ...room },
      bed: { ...bed },
      bookingDates: [...bookingDates],
      booking_id: bookingId // Preserve booking_id from service level
    }
  };
  
  console.log("Created hotel configuration with booking ID:", { configId: config.id, bookingId });
  
  return config;
}; 