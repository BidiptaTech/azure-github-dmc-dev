import { createSlice, createAsyncThunk } from "@reduxjs/toolkit";
import axios from "axios";
import Cookies from "js-cookie";
import { setTourIdd, logoutUser } from "./authSlices";
import { setDateService } from "./dateServicesSlice";
import { setAttractionService } from "../attractions/attractionSlice";
import { setRestaurantsService } from "../restaurant/RestaurantsSlice";
import { setEntryport, setExitport } from "../port/pickupDropSlice";
import { setHourly, setPointToPoint } from "../localtour/Localslice";
// import { setbookedGuide } from "../tourguide/guideslice";
import { BASE_URL } from "@/services/api";
import { setCity } from "./citySlice";
import { setAuthenticated } from "@/pages/login/loginSlice";

/** Proposals list — same shape as tour-list / fetchLists. Agent-only for now. */
export const fetchProposals = createAsyncThunk(
  "enquiry/fetchProposals",
  async (params = {}, { getState, rejectWithValue, dispatch }) => {
    const { agentId = null, start = 0, limit = 10, reset = false } = params;
    const dmcId = getState().dmc?.dmcId;
    const agentFromEditing = getState().editing?.agentId;

    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      const agent = agentFromEditing || agentId || AgentId;

      const response = await axios.get(`${BASE_URL}/proposals`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          "agent-id": AgentId,
        },
        params: {
          agent_id: agent,
          dmc_id: dmcId ?? undefined,
          start,
          limit,
        },
      });

      return { data: response.data.data, reset, start, limit };
    } catch (error) {
      if (error.response?.status === 401) {
        await dispatch(logoutUser());
        dispatch(setAuthenticated(false));
      }
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

export const fetchBookingid = createAsyncThunk(
  "fetchBookingid",
  async (optionalParams, { getState, rejectWithValue, dispatch }) => {
    const state = getState();
    const enquiryState = state.enquiry;
    // Get selected DMC IDs from dmcSlice
    const dmcState = state.dmc;
    const selectedDmcIds = dmcState?.selectedDmcIds || [];
    const selectedDmcId = dmcState?.dmcId || null;

    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken && !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      const { searchLocation, checkIn, checkOut, guests, preferredHotels } = enquiryState;
      const { maleCount, femaleCount } = guests;

      // Log the searchLocation object to debug
      // console.log("SearchLocation data before request:", searchLocation);
      // console.log("Guests data before request:", guests);
      // console.log("Selected DMC IDs before request:", selectedDmcIds);

      // Prepare request body with explicit country and city
      // Multi-city enquiries send comma-separated city/country strings
      const requestBody = {
        country: searchLocation?.country || '',
        city: searchLocation?.city || '',
        check_in: checkIn,
        check_out: checkOut,
        adult: parseInt(guests.adults) || 0,
        child: parseInt(guests.children) || 0,
        infant: parseInt(guests.infant) || 0,
        male: parseInt(maleCount) || 0,
        female: parseInt(femaleCount) || 0,
        children_ages: guests.childrenAges?.length > 0 ? guests.childrenAges.join(", ") : "",
        // Client / group name shown on Quotes & requests
        customer_name: (enquiryState.customer_name || "").trim(),
        // Add selected DMC IDs to the request
        dmc_ids: selectedDmcIds.length > 0 ? selectedDmcIds : [selectedDmcId],
      };

      if (searchLocation?.cityWiseDates?.length) {
        requestBody.city_wise_dates = searchLocation.cityWiseDates;
      }
      if (searchLocation?.cities?.length) {
        requestBody.cities = searchLocation.cities;
      }

      // Log the final request body for debugging
      console.log("Final API request payload with DMC IDs:", requestBody);
      // console.log("SearchLocation type:", typeof searchLocation);
      // console.log("SearchLocation keys:", searchLocation ? Object.keys(searchLocation) : 'null');

      // Add preferred hotels to request if they exist
      if (preferredHotels && preferredHotels.length > 0) {
        requestBody.preferred_hotels = preferredHotels.map(hotel => hotel.hotel_unique_id).join(',');
      }

      // Add any additional parameters passed to the thunk
      if (optionalParams) {
        Object.assign(requestBody, optionalParams);
      }

      const response = await axios.post(
          `${BASE_URL}/create-enquiry`,
        requestBody,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "Content-Type": "application/json",
            "agent-id": AgentId,
          },
        }
      );
      // console.log("Tour create", response);

      const responseData = response.data;
      console.log("Create Enquiry API response:", responseData);
      
      // Extract enquiry_id from the response
      const enquiryId = responseData.data?.enquiry_id;
      console.log("Extracted enquiry_id:", enquiryId);

      // Handle tour_id for backward compatibility
      const { tour_id, data } = responseData;

      // Dispatch setTourIdd if needed
      if (tour_id) {
        dispatch(setTourIdd(data.tour_id));
      }
      if (data?.service?.date_service) {
        // console.log("Date Service Data:", data.service?.date_service);
        dispatch(setDateService(data.service?.date_service));
      }

      if (data?.city) {
        // console.log("City Data:", data.city);
        dispatch(setCity(data.city));
        // console.log("City Data:", data.city);
        dispatch(setCity(data.city));
      }
      if (data) {
        // console.log("Service Data:", data);
        //dispatch(setDateService(data.service));
      }

      // Dispatch the date_service data from nested service object to Redux
      if (data?.service) {
        // console.log("Service Data:", data.service);
        //dispatch(setDateService(data.service));
      }
      // Dispatch the date_service data from nested service object to Redux
      if (data?.service?.attraction) {
        // console.log("Service Data:", data?.service?.attraction);
        dispatch(setAttractionService(data.service.attraction));
      }

      if (data?.service?.restaurant) {
        // console.log("Service Data:", data?.service?.restaurant);
        dispatch(setRestaurantsService(data.service.restaurant));
      }
      if (data?.service?.entry_port) {
        // console.log("entry port Data:", data?.service?.entry_port);
        dispatch(setEntryport(data.service.entry_port));
      }
      if (data?.service?.exit_port) {
        // console.log("exit port Data:", data?.service?.exit_port);
        dispatch(setExitport(data.service.exit_port));
      }
      if (data?.service?.travel_point) {
        // console.log("pointtopoint Data:", data?.service?.travel_point);
        dispatch(setPointToPoint(data.service.travel_point));
      }
      if (data?.service?.travel_hourly) {
        // console.log("hourly Data:", data?.service?.travel_hourly);
        dispatch(setHourly(data.service.travel_hourly));
      }
      // if (data?.service?.guide) {
      //   console.log("guide Data:", data?.service?.guide); // Print to console
      //   dispatch(setbookedGuide(data.service.guide));
      // }

      return responseData; // Return the full response data
    } catch (error) {
      console.error("API Error:", error);
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

/** Build one city's update-enquiry-form service payload from booking options + details */
export const buildCityServicePayload = (bookingOptions = {}, serviceDetails = {}) => {
  const selected = Object.keys(bookingOptions).filter((k) => bookingOptions[k]);
  const cityPayload = {
    hotel: selected.includes("hotel"),
    port: selected.includes("entryExitPort"),
    local_transfer: selected.includes("localTour"),
    attraction: selected.includes("attraction"),
    restaurant: selected.includes("restaurant"),
    guide: selected.includes("tourGuide"),
  };

  if (cityPayload.hotel) {
    const hotelDetails = serviceDetails.hotel || {};
    cityPayload.hotel_ids =
      hotelDetails.preferredHotels?.map(
        (hotel) => hotel.hotel_unique_id || hotel.id
      ) || [];
    cityPayload.hotel_categories = hotelDetails.starCategory
      ? [hotelDetails.starCategory]
      : [];
    cityPayload.hotel_remarks = hotelDetails.remarks || "";
    cityPayload.hotel_compare = hotelDetails.compareHotels || "no";
  }

  if (cityPayload.port) {
    const entryExitDetails = serviceDetails.entryExitPort || {};
    cityPayload.entry_port = entryExitDetails.showEntryPort !== false;
    cityPayload.exit_port = entryExitDetails.showExitPort === true;
    cityPayload.port_transport_type = entryExitDetails.carType || "sharable";
    cityPayload.port_remarks = entryExitDetails.remarks || "";

    const portIds = (entryExitDetails.preferredCars || [])
      .map((port) =>
        typeof port === "string" ? port : port.id || port.vehicle_id || ""
      )
      .filter(Boolean);
    if (portIds.length > 0) cityPayload.port_ids = portIds;

    if (cityPayload.entry_port) {
      cityPayload.entry_dropoff_type =
        entryExitDetails.entryDropoffLocationType || "hotel";
      if (entryExitDetails.portAddress) {
        cityPayload.entry_port_address =
          typeof entryExitDetails.portAddress === "string"
            ? entryExitDetails.portAddress
            : entryExitDetails.portAddress.port_name ||
              entryExitDetails.portAddress.name ||
              "";
        cityPayload.entry_port_id =
          typeof entryExitDetails.portAddress === "object"
            ? entryExitDetails.portAddress.port_id ||
              entryExitDetails.portAddress.id ||
              ""
            : "";
      }
      let entryDropoffId = "";
      if (
        entryExitDetails.entryDropoffLocationType === "hotel" &&
        entryExitDetails.hotelDropOff
      ) {
        entryDropoffId =
          typeof entryExitDetails.hotelDropOff === "object"
            ? entryExitDetails.hotelDropOff.hotel_unique_id ||
              entryExitDetails.hotelDropOff.id ||
              ""
            : "";
      } else if (entryExitDetails.entryDropoffLocationType === "attraction") {
        const attraction =
          entryExitDetails.attractionDropOff ||
          entryExitDetails.destination ||
          {};
        entryDropoffId =
          typeof attraction === "object"
            ? attraction.attraction_id || attraction.id || ""
            : "";
      } else if (entryExitDetails.entryDropoffLocationType === "restaurant") {
        const restaurant =
          entryExitDetails.restaurantDropOff ||
          entryExitDetails.destination ||
          {};
        entryDropoffId =
          typeof restaurant === "object"
            ? restaurant.restaurant_id || restaurant.id || ""
            : "";
      }
      cityPayload.entry_dropoff_location_id = entryDropoffId;
    }

    if (cityPayload.exit_port) {
      cityPayload.exit_pickup_type =
        entryExitDetails.exitPickupLocationType || "hotel";
      const exitPickupLocation =
        entryExitDetails.exitPickupLocation ||
        (Array.isArray(entryExitDetails.destination) &&
        entryExitDetails.destination.length > 0
          ? entryExitDetails.destination[0]
          : entryExitDetails.destination) ||
        {};
      let exitPickupId = "";
      if (entryExitDetails.exitPickupLocationType === "hotel") {
        exitPickupId =
          typeof exitPickupLocation === "object"
            ? exitPickupLocation.hotel_unique_id ||
              exitPickupLocation.id ||
              ""
            : typeof exitPickupLocation === "string"
              ? exitPickupLocation
              : "";
      } else if (entryExitDetails.exitPickupLocationType === "attraction") {
        const exitAttraction =
          entryExitDetails.exitAttractionPickup || exitPickupLocation || {};
        exitPickupId =
          typeof exitAttraction === "object"
            ? exitAttraction.attraction_id || exitAttraction.id || ""
            : "";
      } else if (entryExitDetails.exitPickupLocationType === "restaurant") {
        const exitRestaurant =
          entryExitDetails.exitRestaurantPickup || exitPickupLocation || {};
        exitPickupId =
          typeof exitRestaurant === "object"
            ? exitRestaurant.restaurant_id || exitRestaurant.id || ""
            : "";
      }
      cityPayload.exit_pickup_location_id = exitPickupId;
      if (entryExitDetails.exitPortAddress) {
        cityPayload.exit_port_address =
          typeof entryExitDetails.exitPortAddress === "string"
            ? entryExitDetails.exitPortAddress
            : entryExitDetails.exitPortAddress.port_name ||
              entryExitDetails.exitPortAddress.name ||
              "";
        cityPayload.exit_port_id =
          typeof entryExitDetails.exitPortAddress === "object"
            ? entryExitDetails.exitPortAddress.port_id ||
              entryExitDetails.exitPortAddress.id ||
              ""
            : "";
      }
    }
  }

  if (cityPayload.local_transfer) {
    const localTourDetails = serviceDetails.localTour || {};
    const carIds = (localTourDetails.preferredCars || [])
      .map((car) =>
        typeof car === "string" ? car : car.id || car.vehicle_id || ""
      )
      .filter(Boolean);
    if (carIds.length > 0) cityPayload.local_transport_vehicle_ids = carIds;
    cityPayload.local_transfer_remarks = localTourDetails.remarks || "";
  }

  if (cityPayload.attraction) {
    const attractionDetails = serviceDetails.attraction || {};
    let attractionIds = [];
    if (Array.isArray(attractionDetails.selectedAttractions)) {
      attractionIds = attractionDetails.selectedAttractions.map(
        (a) => a.attraction_id || a.id
      );
    } else if (attractionDetails.selectedAttractions) {
      const a = attractionDetails.selectedAttractions;
      if (a.attraction_id || a.id) attractionIds = [a.attraction_id || a.id];
    }
    cityPayload.attraction_ids = attractionIds;
    cityPayload.attraction_remarks = attractionDetails.remarks || "";
    cityPayload.attraction_transport = attractionDetails.needTransport || false;
    cityPayload.attraction_transport_type =
      attractionDetails.carType || "sharable";
  }

  if (cityPayload.restaurant) {
    const restaurantDetails = serviceDetails.restaurant || {};
    let restaurantIds = [];
    if (Array.isArray(restaurantDetails.selectedRestaurants)) {
      restaurantIds = restaurantDetails.selectedRestaurants.map(
        (r) => r.id || r.restaurant_id
      );
    } else if (restaurantDetails.selectedRestaurants) {
      const r = restaurantDetails.selectedRestaurants;
      if (r.id || r.restaurant_id) restaurantIds = [r.id || r.restaurant_id];
    }
    cityPayload.restaurant_ids = restaurantIds;
    cityPayload.restaurant_remarks = restaurantDetails.remarks || "";
    cityPayload.restaurant_transport = restaurantDetails.needTransport || false;
    cityPayload.restaurant_transport_type =
      restaurantDetails.carType || "sharable";
  }

  if (cityPayload.guide) {
    const guideDetails = serviceDetails.tourGuide || {};
    cityPayload.guide_ids =
      guideDetails.preferredGuides?.map((g) => g.id || g.guide_id) || [];
    cityPayload.guide_remarks = guideDetails.specialRequirements || "";
  }

  return cityPayload;
};

// New thunk for submitting enquiry form
export const submitEnquiryForm = createAsyncThunk(
  "submitEnquiryForm",
  async (enquiryId, { getState, rejectWithValue }) => {
    console.log("submitEnquiryForm thunk started with provided ID:", enquiryId);
    
    const state = getState();
    const enquiryState = state.enquiry;
    const { selectedServices, serviceDetails, cityWiseServiceDetails } = enquiryState;
    
    // Get data from enquiry list for real pricing
    const enquiryListState = state.enquiryList || {};
    const { hotels = [], attractions = [], restaurants = [], guides = [], vehicles = [] } = enquiryListState;
    
    // If no ID is provided, try to get it from the state
    // Prefer multi_enq_id for multi-DMC enquiries, fallback to individual enquiry_id
    const multiEnqId = enquiryState.multiEnqId;
    const individualEnqId = enquiryId || enquiryState.enquiryId || enquiryState.id || enquiryState.tourId;
    
    const idToUse = multiEnqId || individualEnqId;
    const isMultiEnquiry = !!multiEnqId;
    
    console.log("ID selection logic:", {
      providedEnquiryId: enquiryId,
      stateMultiEnqId: multiEnqId,
      stateIndividualEnqId: individualEnqId,
      finalIdToUse: idToUse,
      isMultiEnquiry: isMultiEnquiry
    });
    
    // console.log("Final enquiry_id to use:", idToUse);
    // console.log("Current state in thunk:", { 
    //   selectedServices, 
    //   serviceDetails,
    //   stateEnquiryId: enquiryState.enquiryId,
    //   stateId: enquiryState.id,
    //   stateTourId: enquiryState.tourId
    // });

    if (!idToUse) {
      return rejectWithValue("No enquiry ID available. Please try again or start a new booking.");
    }

    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");
      
      console.log("Auth credentials:", { 
        hasAuthToken: !!authToken, 
        hasAgentId: !!AgentId 
      });

      if (!authToken) {
        console.error("Missing auth token");
        throw new Error("Authorization token is missing.");
      }

      // Get approximate price from component state instead of recalculating
      // This avoids data inconsistency issues between different calculation methods
      let approxPrice = 0;
      
      // Try to get the price from the component's calculation first
      if (getState().enquiry?.calculatedPrice) {
        approxPrice = getState().enquiry.calculatedPrice;
        console.log(`Using pre-calculated price from state: $${approxPrice}`);
      } else {
        console.log("No pre-calculated price found, using fallback basic calculation");
        // Fallback: Simple calculation based on service selection
        const baseServicePrice = 100; // Base price per service
        approxPrice = selectedServices.length * baseServicePrice;
      }

      // Format data according to API requirements
      const requestBody = {
        hotel: selectedServices.includes("hotel"),
        port: selectedServices.includes("entryExitPort"),
        local_transfer: selectedServices.includes("localTour"),
        attraction: selectedServices.includes("attraction"),
        restaurant: selectedServices.includes("restaurant"),
        guide: selectedServices.includes("tourGuide"),
        approx_price: approxPrice, // Add the calculated approximate price
      };

      // Add the appropriate ID field based on enquiry type
      if (isMultiEnquiry) {
        requestBody.enquiry_id = idToUse;
        console.log("Using multi_enq_id:", idToUse);
      } else {
        requestBody.enquiry_id = idToUse;
        console.log("Using enquiry_id:", idToUse);
      }

      // Add hotel details if selected
      if (requestBody.hotel) {
        const hotelDetails = serviceDetails.hotel;
        requestBody.hotel_ids = hotelDetails.preferredHotels?.map(hotel => 
          hotel.hotel_unique_id || hotel.hotel_unique_id
        ) || [];
        requestBody.hotel_categories = hotelDetails.starCategory ? [hotelDetails.starCategory] : [];
        requestBody.hotel_remarks = hotelDetails.remarks || "";
        requestBody.hotel_compare = hotelDetails.compareHotels || "no";
      }

      // Add port (entry/exit port) details if selected
      if (requestBody.port) {
        const entryExitDetails = serviceDetails.entryExitPort || {};
        
        // Add entry/exit flags to know which services are enabled
        requestBody.entry_port = entryExitDetails.showEntryPort !== false;
        requestBody.exit_port = entryExitDetails.showExitPort === true;
        
        // Add car type
        requestBody.port_transport_type = entryExitDetails.carType || "sharable";
        
        // Extract car IDs properly from preferred cars
        let portIds = [];
        if (entryExitDetails.preferredCars && entryExitDetails.preferredCars.length > 0) {
          portIds = entryExitDetails.preferredCars.map(port => {
            if (typeof port === 'string') return port;
            return port.id || port.vehicle_id || '';
          }).filter(id => id); // Remove empty values
        }
        
        // Add car IDs to request if available
        if (portIds.length > 0) {
          requestBody.port_ids = portIds;
        }
        
        // Entry port data
        if (requestBody.entry_port) {
          // Drop-off location type and details
          requestBody.entry_dropoff_type = entryExitDetails.entryDropoffLocationType || "hotel";
          
          // Add entry port address and ID (similar to exit port)
          if (entryExitDetails.portAddress) {
            requestBody.entry_port_address = typeof entryExitDetails.portAddress === 'string' 
              ? entryExitDetails.portAddress 
              : entryExitDetails.portAddress.port_name || entryExitDetails.portAddress.name || '';
              
            requestBody.entry_port_id = typeof entryExitDetails.portAddress === 'object' 
              ? entryExitDetails.portAddress.port_id || entryExitDetails.portAddress.id || '' 
              : '';
          }
          
          // Remove all the type-specific IDs, we'll just use the generic location_id
          // Initialize a single location ID field
          requestBody.entry_dropoff_location_id = '';
          
          if (entryExitDetails.entryDropoffLocationType === "hotel" && entryExitDetails.hotelDropOff) {
            requestBody.entry_dropoff_location_id = typeof entryExitDetails.hotelDropOff === 'object' 
              ? entryExitDetails.hotelDropOff.hotel_unique_id || entryExitDetails.hotelDropOff.id || '' 
              : '';
          } else if (entryExitDetails.entryDropoffLocationType === "attraction") {
            // Handle attraction type - use the specific attractionDropOff field if available
            // First check if we have a dedicated attraction field, then fall back to destination
            const attraction = entryExitDetails.attractionDropOff || entryExitDetails.destination || {};
            
            // Debug the attraction data to see what's available
            console.log("Entry port attraction data:", {
              attractionDropOff: entryExitDetails.attractionDropOff,
              destination: entryExitDetails.destination,
              attractionType: typeof attraction,
              attractionKeys: typeof attraction === 'object' ? Object.keys(attraction) : 'n/a',
              attractionId: typeof attraction === 'object' ? 
                (attraction.attraction_id || attraction.id || '') : 'n/a',
              attractionName: typeof attraction === 'object' ? 
                (attraction.name || attraction.attraction_name || '') : 'n/a',
            });
            
            // Try multiple approaches to get the attraction ID
            let attractionId = '';
            if (typeof attraction === 'object') {
              // UPDATED: Prioritize attraction_id over id
              attractionId = attraction.attraction_id || attraction.id || 
                           attraction.attractionId || attraction.attraction_unique_id || '';
              
              // If still empty and we have a numeric ID as a string property, use that
              if (!attractionId && attraction.id && typeof attraction.id === 'string') {
                const numericId = parseInt(attraction.id);
                if (!isNaN(numericId)) {
                  attractionId = numericId.toString();
                }
              }
            } else if (typeof attraction === 'number') {
              // If it's directly a number, convert to string
              attractionId = attraction.toString();
            } else if (typeof attraction === 'string' && !isNaN(parseInt(attraction))) {
              // If it's a numeric string, use it directly
              attractionId = attraction;
            }
            
            // Use our enhanced approach to get the ID - only set the generic location_id
            requestBody.entry_dropoff_location_id = attractionId;
            
            // Log the final values we're using
            console.log("Final entry attraction values:", {
              locationId: requestBody.entry_dropoff_location_id
            });
          } else if (entryExitDetails.entryDropoffLocationType === "restaurant") {
            // Handle restaurant type - use the specific restaurantDropOff field if available
            // First check if we have a dedicated restaurant field, then fall back to destination
            const restaurant = entryExitDetails.restaurantDropOff || entryExitDetails.destination || {};
            
            // UPDATED: Extract restaurant ID, prioritizing restaurant_id
            let restaurantId = '';
            if (typeof restaurant === 'object') {
              // Try all possible ID fields, prioritizing restaurant_id
              restaurantId = restaurant.restaurant_id || restaurant.id || '';
            } else if (typeof restaurant === 'string' && !isNaN(parseInt(restaurant))) {
              // If it's a numeric string, use it directly
              restaurantId = restaurant;
            }
            
            // Add only the generic location_id
            requestBody.entry_dropoff_location_id = restaurantId;
            
            // Log the final values we're using
            console.log("Final entry restaurant values:", {
              locationId: requestBody.entry_dropoff_location_id
            });
          }
        }
        
        // Exit port data
        if (requestBody.exit_port) {
          // Pickup location type and details
          requestBody.exit_pickup_type = entryExitDetails.exitPickupLocationType || "hotel";
          
          // Only use the generic location ID field
          requestBody.exit_pickup_location_id = '';
          
          // For exit port, we need to look for destination data
          const exitPickupLocation = entryExitDetails.exitPickupLocation || 
            (Array.isArray(entryExitDetails.destination) && entryExitDetails.destination.length > 0 ? 
              entryExitDetails.destination[0] : entryExitDetails.destination) || {};
          
          if (entryExitDetails.exitPickupLocationType === "hotel") {
            requestBody.exit_pickup_location_id = typeof exitPickupLocation === 'object' 
              ? exitPickupLocation.hotel_unique_id || exitPickupLocation.id || '' 
              : (typeof exitPickupLocation === 'string' ? exitPickupLocation : '');
          } else if (entryExitDetails.exitPickupLocationType === "attraction") {
            // Use the specific exitAttractionPickup field if available, otherwise fall back to exitPickupLocation or destination
            const exitAttractionPickup = entryExitDetails.exitAttractionPickup || exitPickupLocation || 
              (Array.isArray(entryExitDetails.destination) && entryExitDetails.destination.length > 0 ? 
                entryExitDetails.destination[0] : entryExitDetails.destination) || {};
            
            // UPDATED: Extract attraction ID, prioritizing attraction_id
            let attractionId = '';
            if (typeof exitAttractionPickup === 'object') {
              // Try all possible ID fields, prioritizing attraction_id
              attractionId = exitAttractionPickup.attraction_id || exitAttractionPickup.id || '';
            } else if (typeof exitAttractionPickup === 'string' && !isNaN(parseInt(exitAttractionPickup))) {
              // If it's a numeric string, use it directly
              attractionId = exitAttractionPickup;
            }
            
            // Add only the generic location_id
            requestBody.exit_pickup_location_id = attractionId;
            
            // Log the values we're using
            console.log("Final exit attraction values:", {
              locationId: requestBody.exit_pickup_location_id
            });
          } else if (entryExitDetails.exitPickupLocationType === "restaurant") {
            // Use the specific exitRestaurantPickup field if available, otherwise fall back to exitPickupLocation or destination
            const exitRestaurantPickup = entryExitDetails.exitRestaurantPickup || exitPickupLocation || 
              (Array.isArray(entryExitDetails.destination) && entryExitDetails.destination.length > 0 ? 
                entryExitDetails.destination[0] : entryExitDetails.destination) || {};
            
            // UPDATED: Extract restaurant ID, prioritizing restaurant_id
            let restaurantId = '';
            if (typeof exitRestaurantPickup === 'object') {
              // Try all possible ID fields, prioritizing restaurant_id
              restaurantId = exitRestaurantPickup.restaurant_id || exitRestaurantPickup.id || '';
            } else if (typeof exitRestaurantPickup === 'string' && !isNaN(parseInt(exitRestaurantPickup))) {
              // If it's a numeric string, use it directly
              restaurantId = exitRestaurantPickup;
            }
            
            // Add only the generic location_id
            requestBody.exit_pickup_location_id = restaurantId;
            
            // Log the values we're using
            console.log("Final exit restaurant values:", {
              locationId: requestBody.exit_pickup_location_id
            });
          }
          
          // Port address for drop-off
          if (entryExitDetails.exitPortAddress) {
            requestBody.exit_port_address = typeof entryExitDetails.exitPortAddress === 'string' 
              ? entryExitDetails.exitPortAddress 
              : entryExitDetails.exitPortAddress.port_name || entryExitDetails.exitPortAddress.name || '';
              
            requestBody.exit_port_id = typeof entryExitDetails.exitPortAddress === 'object' 
              ? entryExitDetails.exitPortAddress.port_id || entryExitDetails.exitPortAddress.id || '' 
              : '';
          }
        }
        
        // Add remarks
        requestBody.port_remarks = entryExitDetails.remarks || "";
      }

      // Add local transfer details if selected
      if (requestBody.local_transfer) {
        const localTourDetails = serviceDetails.localTour || {};
        
        // Extract car IDs properly from preferred cars
        let carIds = [];
        if (localTourDetails.preferredCars && localTourDetails.preferredCars.length > 0) {
          carIds = localTourDetails.preferredCars.map(car => {
            if (typeof car === 'string') return car;
            return car.id || car.vehicle_id || '';
          }).filter(id => id); // Remove empty values
        }
        
        // Add car IDs to request if available
        if (carIds.length > 0) {
          requestBody.local_transport_vehicle_ids = carIds;
        }
        
        requestBody.local_transfer_remarks = localTourDetails.remarks || "";
      }

      // Add attraction details if selected
      if (requestBody.attraction && serviceDetails.attraction) {
        const attractionDetails = serviceDetails.attraction || {};
        let attractionIds = [];
        
        // Handle both when selectedAttractions is an array and when it's a single object
        if (Array.isArray(attractionDetails.selectedAttractions)) {
          attractionIds = attractionDetails.selectedAttractions.map(attraction => 
            attraction.attraction_id || attraction.id
          ) || [];
        } else if (attractionDetails.selectedAttractions) {
          // Handle case when it's a single object
          const attraction = attractionDetails.selectedAttractions;
          if (attraction.attraction_id || attraction.id) {
            attractionIds = [attraction.attraction_id || attraction.id];
          }
        }
        
        requestBody.attraction_ids = attractionIds;
        requestBody.attraction_remarks = attractionDetails.remarks || "";
        requestBody.attraction_transport = attractionDetails.needTransport || false;
        requestBody.attraction_transport_type = attractionDetails.carType || "sharable";
        
        // console.log("Attraction details:", {
        //   attractionDetails,
        //   isArray: Array.isArray(attractionDetails.selectedAttractions),
        //   ids: attractionIds,
        //   needTransport: attractionDetails.needTransport,
        //   carType: attractionDetails.carType
        // });
      }

      // Add restaurant details if selected
      if (requestBody.restaurant && serviceDetails.restaurant) {
        const restaurantDetails = serviceDetails.restaurant || {};
        let restaurantIds = [];
        
        // Handle both when selectedRestaurants is an array and when it's a single object
        if (Array.isArray(restaurantDetails.selectedRestaurants)) {
          restaurantIds = restaurantDetails.selectedRestaurants.map(restaurant => 
            restaurant.id || restaurant.restaurant_id
          ) || [];
        } else if (restaurantDetails.selectedRestaurants) {
          // Handle case when it's a single object
          const restaurant = restaurantDetails.selectedRestaurants;
          if (restaurant.id || restaurant.restaurant_id) {
            restaurantIds = [restaurant.id || restaurant.restaurant_id];
          }
        }
        
        requestBody.restaurant_ids = restaurantIds;
        requestBody.restaurant_remarks = restaurantDetails.remarks || "";
        requestBody.restaurant_transport = restaurantDetails.needTransport || false;
        requestBody.restaurant_transport_type = restaurantDetails.carType || "sharable";
        
        // console.log("Restaurant details:", {
        //   restaurantDetails,
        //   isArray: Array.isArray(restaurantDetails.selectedRestaurants),
        //   ids: restaurantIds,
        //   needTransport: restaurantDetails.needTransport,
        //   carType: restaurantDetails.carType
        // });
      }

      // Add guide details if selected
      if (requestBody.guide) {
        requestBody.guide_ids = serviceDetails.tourGuide.preferredGuides?.map(guide => 
          guide.id || guide.guide_id
        ) || [];
        requestBody.guide_remarks = serviceDetails.tourGuide.specialRequirements || "";
      }

      // Multi-city: attach city-wise service selections for update-enquiry-form
      const cityWiseMap = cityWiseServiceDetails || {};
      const cityNames = Object.keys(cityWiseMap);
      if (cityNames.length > 0) {
        const city_wise_services = {};
        cityNames.forEach((cityName) => {
          const cityDraft = cityWiseMap[cityName] || {};
          city_wise_services[cityName] = buildCityServicePayload(
            cityDraft.bookingOptions || {},
            cityDraft.serviceDetails || {}
          );
        });
        requestBody.city_wise_services = city_wise_services;

        // Recompute top-level service flags as OR across cities
        requestBody.hotel = cityNames.some(
          (c) => city_wise_services[c]?.hotel
        );
        requestBody.port = cityNames.some((c) => city_wise_services[c]?.port);
        requestBody.local_transfer = cityNames.some(
          (c) => city_wise_services[c]?.local_transfer
        );
        requestBody.attraction = cityNames.some(
          (c) => city_wise_services[c]?.attraction
        );
        requestBody.restaurant = cityNames.some(
          (c) => city_wise_services[c]?.restaurant
        );
        requestBody.guide = cityNames.some(
          (c) => city_wise_services[c]?.guide
        );

        // Flatten IDs across cities for backward-compatible top-level fields
        const flatHotelIds = [];
        const flatAttractionIds = [];
        const flatRestaurantIds = [];
        const flatGuideIds = [];
        const flatPortIds = [];
        const hotelCategories = new Set();
        cityNames.forEach((c) => {
          const p = city_wise_services[c];
          if (p.hotel_ids) flatHotelIds.push(...p.hotel_ids);
          if (p.attraction_ids) flatAttractionIds.push(...p.attraction_ids);
          if (p.restaurant_ids) flatRestaurantIds.push(...p.restaurant_ids);
          if (p.guide_ids) flatGuideIds.push(...p.guide_ids);
          if (p.port_ids) flatPortIds.push(...p.port_ids);
          (p.hotel_categories || []).forEach((cat) => hotelCategories.add(cat));
        });
        if (requestBody.hotel) {
          requestBody.hotel_ids = [...new Set(flatHotelIds)];
          requestBody.hotel_categories = [...hotelCategories];
        }
        if (requestBody.attraction) {
          requestBody.attraction_ids = [...new Set(flatAttractionIds)];
        }
        if (requestBody.restaurant) {
          requestBody.restaurant_ids = [...new Set(flatRestaurantIds)];
        }
        if (requestBody.guide) {
          requestBody.guide_ids = [...new Set(flatGuideIds)];
        }
        if (requestBody.port && flatPortIds.length > 0) {
          requestBody.port_ids = [...new Set(flatPortIds)];
        }

        console.log("City-wise services payload:", city_wise_services);
      }
      
      const headers = {
        'Authorization': `Bearer ${authToken}`,
        'Content-Type': 'application/json',
      };
      
      if (AgentId) {
        headers['agent-id'] = AgentId;
      }
      
    

      // Try two approaches - first with axios
      try {
        console.log("Attempting API call with direct axios");
        const response = await axios.post(
          `${BASE_URL}/update-enquiry-form`,
          requestBody,
          { headers }
        );
        
        console.log("API response received:", response.data);
        return response.data;
      } catch (axiosError) {
        console.error("Direct axios call failed:", axiosError.message);
        
        // Second attempt with the API utility if available
        console.log("Attempting with API utility");
        const api = await import("@/services/api").then(module => module.default);
        
        if (api && typeof api.request === 'function') {
          const apiResponse = await api.request(
            'post',
            '/update-enquiry-form',
            requestBody
          );
          
          console.log("API utility response received:", apiResponse.data);
          return apiResponse.data;
        } else {
          // If API utility not available or not working, rethrow the original error
          throw axiosError;
        }
      }
    } catch (error) {
      console.error("Form submission error:", error);
      console.error("Error details:", {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status,
        statusText: error.response?.statusText
      });
      
      return rejectWithValue(error.response?.data || error.message);
    }
  }
);

const EnquirySlice = createSlice({
  name: "enquiry",
  initialState: {
    hotel: [],
    status: "idle",
    error: null,
    searchLocation: null, // Changed from array to object to store country and city data
    checkIn: "",
    checkOut: "",
    destination: [],
    guests: {
      adults: "",
      children: "",
      infant: "",
      adultGenders: [],
      childrenAges: [],
      maleCount: 0,
      femaleCount: 0,
    },
    tourId: null,
    enquiryId: null, // Add a dedicated field for enquiryId
    multiEnqId: null, // Add field for multi-enquiry ID
    id: null,
    bookings: [],
    dateService: [],
    attractionService: [],
    serviceDetails: {
      hotel: {
        starCategory: null,
        compareHotels: "no",
        preferredHotels: [],
        remarks: ""
      },
      entryExitPort: {
        portAddress: null,
        hotelDropOff: null,
        carType: "sharable",
        preferredCars: [],
        remarks: "",
        showEntryPort: true,
        showExitPort: false,
        entryDropoffLocationType: "hotel",
        exitPickupLocationType: "hotel",
        exitPortAddress: null,
        exitPickupLocation: null,
        destination: null,
        attractionDropOff: null,
        restaurantDropOff: null,
        exitAttractionPickup: null,
        exitRestaurantPickup: null
      },
      attraction: {
        selectedAttractions: [],
        needTransport: false,
        destinationType: "hotel",
        destination: null,
        carType: "sharable",
        remarks: ""
      },
      localTour: {
        preferredCars: [],
        remarks: ""
      },
      tourGuide: {
        preferredGuides: [],
        specialRequirements: ""
      },
      restaurant: {
        selectedRestaurants: [],
        needTransport: false,
        destinationType: "hotel",
        destination: null,
        carType: "sharable",
        remarks: ""
      }
    },
    selectedServices: [],
    // Multi-city drafts: { [cityName]: { bookingOptions, serviceDetails, selectedServices } }
    cityWiseServiceDetails: {},
    calculatedPrice: 0, // Store the calculated price from ConfirmDetails
    customer_name: "", // Client / group name for Quotes & requests
    proposals: [],
    proposalsStatus: "idle",
    proposalsError: null,
  },
  reducers: {
    clearProposals: (state) => {
      state.proposals = [];
      state.proposalsStatus = "idle";
      state.proposalsError = null;
    },
    setCustomerName: (state, action) => {
      state.customer_name =
        typeof action.payload === "string" ? action.payload : "";
    },
    setSearchLocation: (state, action) => {
      console.log('Enquiry Slice setSearchLocation action payload:', action.payload);
      // Ensure we're storing the data correctly
      if (action.payload && typeof action.payload === 'object') {
        state.searchLocation = {
          country: action.payload.country || '',
          city: action.payload.city || '',
          // Multi-city support (optional extras; create-enquiry still uses country/city strings)
          cities: action.payload.cities || null,
          cityWiseDates: action.payload.cityWiseDates || null,
        };
      } else {
        state.searchLocation = action.payload;
      }
      console.log('Enquiry Slice state after update:', state.searchLocation);
    },
    setCheckIn: (state, action) => {
      state.checkIn = action.payload;
    },
    setCheckOut: (state, action) => {
      state.checkOut = action.payload;
    },
    setGuest: (state, action) => {
      const {
        adults,
        children,
        infant,
        adultGenders = [],
        childrenAges = [],
        maleCount = 0,
        femaleCount = 0,
      } = action.payload;

      console.log("Setting guest data in Redux:", action.payload);

      state.guests = {
        adults: adults || '0',
        children: children || '0',
        infant: infant || '0',
        adultGenders: adultGenders || [],
        childrenAges: childrenAges || [],
        maleCount: maleCount || 0,
        femaleCount: femaleCount || 0,
      };
    },
    setDateService: (state, action) => {
      state.dateService = action.payload; // Store date_service in state
    },
    setAttractionService: (state, action) => {
      state.attractionService = action.payload;
    },
    updateServiceDetails: (state, action) => {
      const { service, data } = action.payload;
      
      console.log("Updating service details:", service, data);
      
      // If updating hotel service, ensure we properly handle the remarks field
      if (service === 'hotel' && data.remarks !== undefined) {
        console.log("Updating hotel remarks:", data.remarks);
      }
      
      // Update the service details with the new data
      state.serviceDetails[service] = {
        ...state.serviceDetails[service],
        ...data
      };
    },
    updateCalculatedPrice: (state, action) => {
      state.calculatedPrice = action.payload;
      console.log("Updated calculated price in Redux:", action.payload);
    },
    setSelectedServices: (state, action) => {
      state.selectedServices = action.payload;
      console.log("Selected services:", state.selectedServices);
    },
    clearServiceDetails: (state) => {
      state.serviceDetails = {};
      console.log("Cleared all service details");
    },
    clearSpecificService: (state, action) => {
      const serviceToClear = action.payload;
      if (state.serviceDetails[serviceToClear]) {
        delete state.serviceDetails[serviceToClear];
        console.log(`Cleared service details for: ${serviceToClear}`);
      }
    },
    setCityWiseServiceDetails: (state, action) => {
      // Replace entire city-wise map, or merge one city when { city, data } is passed
      const payload = action.payload;
      if (payload && payload.city && payload.data) {
        state.cityWiseServiceDetails = {
          ...state.cityWiseServiceDetails,
          [payload.city]: payload.data,
        };
      } else if (payload && typeof payload === "object") {
        state.cityWiseServiceDetails = payload;
      }
      console.log(
        "Updated cityWiseServiceDetails:",
        state.cityWiseServiceDetails
      );
    },
    clearCityWiseServiceDetails: (state) => {
      state.cityWiseServiceDetails = {};
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchBookingid.pending, (state) => {
        state.status = "loading";
        state.error = null;
      })
      .addCase(fetchBookingid.fulfilled, (state, action) => {
        state.status = "succeeded";

        const data = action.payload?.data || {};

        // Store both tourId and enquiryId 
        state.tourId = action.payload?.tour_id || null;
        state.enquiryId = data.enquiry_id || null;
        state.id = data.enquiry_id || action.payload?.tour_id || null;
        
        // Store multi_enq_id if it exists (for multi-DMC enquiries)
        state.multiEnqId = action.payload?.multi_enq_id || null;

        console.log("Stored enquiry_id in state:", state.enquiryId);
        console.log("Stored multi_enq_id in state:", state.multiEnqId);

        // Log the city from the API response
        const city = data.city || action.payload?.city;
        if (city) {
          console.log("City from API Response:", city);
        }

        const newBooking = {
          tourId: action.payload?.tour_id,
          enquiryId: data.enquiry_id,
          multiEnqId: action.payload?.multi_enq_id,
          checkIn: state.checkIn,
          checkOut: state.checkOut,
          pax: state.guests.adults + state.guests.children,
          destination: data.destination || data.city || "",
        };

        state.bookings.push(newBooking);
      })
      .addCase(fetchBookingid.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload || action.error.message;
        state.tourId = null;
      })
      .addCase(submitEnquiryForm.pending, (state) => {
        state.status = "submitting";
        state.error = null;
      })
      .addCase(submitEnquiryForm.fulfilled, (state, action) => {
        state.status = "submitted";
        // You can store any response data if needed
      })
      .addCase(submitEnquiryForm.rejected, (state, action) => {
        state.status = "submission_failed";
        state.error = action.payload || action.error.message;
      })
      .addCase(fetchProposals.pending, (state) => {
        state.proposalsStatus = "loading";
        state.proposalsError = null;
      })
      .addCase(fetchProposals.fulfilled, (state, action) => {
        state.proposalsStatus = "succeeded";
        const { data, reset } = action.payload || {};
        const list = Array.isArray(data) ? data : [];
        if (reset) {
          state.proposals = list;
        } else {
          const existingIds = new Set(state.proposals.map((p) => p.id));
          const fresh = list.filter((p) => !existingIds.has(p.id));
          state.proposals = [...state.proposals, ...fresh];
        }
      })
      .addCase(fetchProposals.rejected, (state, action) => {
        state.proposalsStatus = "failed";
        state.proposalsError =
          action.payload || action.error?.message || "Failed to fetch proposals";
      });
  },
});

export const { 
  setSearchLocation, 
  setCheckIn, 
  setCheckOut, 
  setGuest,
  setCustomerName,
  updateServiceDetails,
  updateCalculatedPrice,
  setSelectedServices,
  clearServiceDetails,
  clearSpecificService,
  setCityWiseServiceDetails,
  clearCityWiseServiceDetails,
  clearProposals,
} = EnquirySlice.actions;

export default EnquirySlice.reducer;
