// This is a utility file to help extract policy data from the API response

// Example of how to extract policy data from the hotel details response
export const extractPolicyData = (apiResponse) => {
  if (!apiResponse || !apiResponse.policy) {
    console.log("No policy data found in API response");
    return null;
  }
  
  console.log("Policy data found:", apiResponse.policy);
  return apiResponse.policy;
};

// Example to format the check-in/check-out times
export const formatHotelTimes = (hotelDetails) => {
  const checkInTime = hotelDetails?.check_in_time || "15:00:00";
  const checkOutTime = hotelDetails?.check_out_time || "11:00:00";
  
  // Format the times
  const formatTime = (timeString) => {
    try {
      if (!timeString) return "";
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
  
  return {
    formattedCheckInTime: formatTime(checkInTime),
    formattedCheckOutTime: formatTime(checkOutTime),
  };
};

// Example hotel API response for reference
const exampleHotelResponse = {
  id: "e1bef35.48089355",
  hotel_name: "Hotel Boss",
  check_in_time: "15:00:00",
  check_out_time: "11:00:00",
  policy: [
    {
      property_policy: {
        name: "TAJ HOTEL",
        policy: "rffrr",
        file: "",
        extras: "tr",
        property: "erf"
      },
      cancellation_policy: {
        policy: "sdd",
        cancellation_pdf: "",
        cancellation_type: false
      },
      refund_policy: {
        refundpolicy: "",
        refundpolicy_pdf: ""
      },
      child_policy: {
        childpolicy: "",
        childpolicy_pdf: ""
      },
      pet_policy: {
        petpolicy: "",
        pet_allowed: false,
        petpolicy_pdf: ""
      },
      terms_policy: {
        termspolicy: "",
        termspolicy_pdf: ""
      }
    }
  ],
  // Rest of the API response...
}; 