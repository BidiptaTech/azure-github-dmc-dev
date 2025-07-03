import moment from 'moment';

/**
 * Generates a unique ID for hotel configurations
 * @returns {string} A unique ID
 */
export const generateUniqueId = () => {
  return Math.random().toString(36).substring(2) + Date.now().toString(36);
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
 * @returns {Array} Array of meal plan IDs for each guest
 */
export const extractGuestMealPlans = (bed, mealPlanOptions) => {
  const guestMealPlans = [];
  const headCount = bed.head_count || 1;
  
  for (let i = 1; i <= headCount; i++) {
    const mealKey = `meal_${i}`;
    if (bed.selectedMeals && bed.selectedMeals[mealKey]) {
      const mealType = bed.selectedMeals[mealKey].type;
      // Find the corresponding meal plan ID
      const mealPlan = mealPlanOptions.find(plan => plan.title === mealType);
      guestMealPlans.push(mealPlan ? mealPlan.id : 'self');
    } else {
      guestMealPlans.push('self');
    }
  }
  
  return guestMealPlans;
};

/**
 * Create a hotel configuration from room and bed data
 * @param {Object} hotelDetails Hotel details object
 * @param {Object} room Room data object
 * @param {Object} bed Bed data object
 * @param {Array} bookingDates Array of booking dates
 * @param {Array} mealPlanOptions Available meal plan options
 * @returns {Object} Hotel configuration object
 */
export const createConfigFromRoomAndBed = (hotelDetails, room, bed, bookingDates, mealPlanOptions) => {
  const guestMealPlans = extractGuestMealPlans(bed, mealPlanOptions);
  const { nights, selectedNightIndices } = calculateNightsFromDates(bookingDates);
  
  return {
    id: generateUniqueId(),
    hotelId: hotelDetails.hotel_id,
    hotelDetails: hotelDetails,
    roomTypeId: room.room_id.toString(),
    roomTypeName: room.room_type,
    bedTypeId: bed.bed_id.toString(),
    bedTypeName: bed.bed_type,
    max_occupancy: bed.max_occupancy || 1,
    bedPrice: bed.price || 0,
    mealPlanId: guestMealPlans[0] || 'self',
    nights: nights,
    selectedNightIndices: selectedNightIndices,
    babyCot: bed.baby_cot === 1,
    occupancyType: getOccupancyType(bed.max_occupancy),
    adultDistribution: { male: 0, female: 0 },
    expanded: true,
    selectedGuests: bed.head_count || 1,
    guestMealPlans: guestMealPlans
  };
}; 