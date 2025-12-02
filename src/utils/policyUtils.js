/**
 * Utility functions for handling hotel policy data
 */

/**
 * Extracts policy data from API response
 * @param {Object} apiResponse - The API response object
 * @returns {Array|null} - The policy data array or null if not found
 */
export const extractPolicyData = (apiResponse) => {
  if (!apiResponse) return null;
  
  // Check if policy exists directly on the response
  if (apiResponse.policy && Array.isArray(apiResponse.policy)) {
    return apiResponse.policy;
  }
  
  return null;
};

/**
 * Formats a time string from HH:MM:SS to 12-hour format
 * @param {string} timeString - Time string in HH:MM:SS format
 * @returns {string} - Formatted time string
 */
export const formatTime = (timeString) => {
  try {
    if (!timeString) return "";
    // Handle cases where time is already in a readable format
    if (!timeString.includes(':')) return timeString;
    
    const timeParts = timeString.split(':');
    if (timeParts.length < 2) return timeString;
    
    const hours = parseInt(timeParts[0], 10);
    const minutes = timeParts[1];
    const period = hours >= 12 ? 'PM' : 'AM';
    const displayHours = hours % 12 || 12;
    
    return `${displayHours}:${minutes} ${period}`;
  } catch (error) {
    console.error("Error formatting time:", error);
    return timeString;
  }
};

/**
 * Checks if a hotel has any extra bed options
 * @param {Object} hotelDetails - The hotel details object
 * @returns {boolean} - True if hotel has extra bed options
 */
export const hasExtraBedOption = (hotelDetails) => {
  if (!hotelDetails?.room_data) return false;
  
  return hotelDetails.room_data.some(room => 
    room.bed_details && room.bed_details.some(bed => 
      bed.extra_bed || bed.baby_cot
    )
  );
};

/**
 * Retrieves policy data from various sources (Redux store, API response, localStorage)
 * @param {Object} hotelPolicies - Policies from Redux store
 * @param {Object} roomsData - Room data that might contain policies
 * @returns {Object} - The policy data and source
 */
export const getPolicyData = (hotelPolicies, roomsData) => {
  let policyData = null;
  let policySource = 'none';
  let policyArray = null;
  
  // First check Redux store
  if (hotelPolicies && hotelPolicies.length > 0) {
    policyData = hotelPolicies[0];
    policyArray = hotelPolicies;
    policySource = 'redux';
  } 
  // Then check roomsData
  else if (roomsData && roomsData.policy && roomsData.policy.length > 0) {
    policyData = roomsData.policy[0];
    policyArray = roomsData.policy;
    policySource = 'api';
  } 
  // Lastly, check localStorage
  else {
    try {
      const roomDatasString = localStorage.getItem('roomDatas');
      if (roomDatasString) {
        const localRoomDatas = JSON.parse(roomDatasString);
        if (localRoomDatas && localRoomDatas.policy && localRoomDatas.policy.length > 0) {
          policyData = localRoomDatas.policy[0];
          policyArray = localRoomDatas.policy;
          policySource = 'localStorage';
        }
      }
    } catch (error) {
      console.error("Error getting policy data from localStorage:", error);
    }
  }
  
  return { 
    policyData, 
    policyArray, 
    policySource 
  };
}; 