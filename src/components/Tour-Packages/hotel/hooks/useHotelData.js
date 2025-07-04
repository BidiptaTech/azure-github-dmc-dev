import { useState, useEffect, useCallback } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import moment from 'moment';
import { UpdateCustomPackage } from '@/slice/tour-packages/tourPackageSlice';
import { createConfigFromRoomAndBed } from '../utils/hotelUtils';

/**
 * Custom hook for loading and managing hotel data
 * @param {Array} mealPlanOptions Available meal plan options
 * @returns {Object} Hotel data and functions
 */
const useHotelData = (mealPlanOptions) => {
  const dispatch = useDispatch();
  
  // States for hotel configurations
  const [hotelConfigurations, setHotelConfigurations] = useState([]);
  const [activeHotelIndex, setActiveHotelIndex] = useState(0);
  const [alert, setAlert] = useState({ show: false, message: '', severity: 'info' });
  
  // Get tour ID from URL or Redux
  const router = typeof window !== 'undefined' ? new URL(window.location.href) : null;
  const urlTourId = router ? router.searchParams.get('tour_id') : null;
  const reduxTourId = useSelector((state) => state.hotels.id);
  const tourId = urlTourId || reduxTourId;
  
  // Get package data from Redux store
  const packageData = useSelector(state => state.tourPackages.packageData);
  
  // Fetch existing tour data when component mounts
  useEffect(() => {
    if (tourId) {
      console.log("HOTEL COMPONENT - Fetching existing tour data for tour ID:", tourId);
      dispatch(UpdateCustomPackage({ tour_id: tourId }))
        .then(response => {
          console.log("HOTEL COMPONENT - UpdateCustomPackage response:", response);
        })
        .catch(error => {
          console.error("HOTEL COMPONENT - Error fetching tour data:", error);
        });
    }
  }, [tourId, dispatch]);
  
  // Function to create an initial hotel configuration
  const createInitialHotelConfiguration = useCallback((searchCriteria = {}) => {
    // Handle both uppercase and lowercase property names and calculate total guests
    const adults = parseInt(searchCriteria?.guests?.Adults || searchCriteria?.guests?.adults || 1);
    const children = parseInt(searchCriteria?.guests?.Children || searchCriteria?.guests?.children || 0);
    const initialGuests = adults + children; // Total guests (adults + children)
    
    console.log("Creating initial hotel config with guests:", {
      searchCriteria: searchCriteria?.guests,
      adults,
      children,
      initialGuests
    });
    
    const nightsCount = searchCriteria?.checkIn && searchCriteria?.checkOut ? 
      moment(searchCriteria.checkOut, 'DD/MM/YYYY').diff(moment(searchCriteria.checkIn, 'DD/MM/YYYY'), 'days') : 1;
    
    // Create initial selected night indices
    const initialNightIndices = [];
    for (let i = 0; i < nightsCount; i++) {
      initialNightIndices.push(i);
    }
    
    const initialConfig = {
      id: Math.random().toString(36).substring(2) + Date.now().toString(36),
      hotelId: '',
      hotelDetails: {},
      roomTypeId: '',
      roomTypeName: '',
      bedTypeId: '',
      bedTypeName: '',
      max_occupancy: 1,
      bedPrice: 0,
      mealPlanId: 'self',
      nights: nightsCount,
      selectedNightIndices: initialNightIndices,
      babyCot: false,
      occupancyType: 'single',
      adultDistribution: { male: 0, female: 0 },
      expanded: true,
      selectedGuests: initialGuests,
      guestMealPlans: Array(initialGuests).fill('self')
    };
    
    console.log("Creating initial hotel configuration:", initialConfig);
    return initialConfig;
  }, []);
  
  // Function to load existing hotel data from package data
  const loadExistingHotelData = useCallback(() => {
    if (packageData?.tour?.booking) {
      console.log("HOTEL COMPONENT - Loading existing hotel data from package data");
      
      // Find ALL hotel bookings in the package data (fixed case sensitivity: "hotel" not "Hotel")
      const hotelBookings = packageData.tour.booking.filter(booking => booking.type === "hotel");
      console.log("HOTEL COMPONENT - Found hotel bookings:", hotelBookings.length);
      console.log("HOTEL COMPONENT - Hotel bookings data:", hotelBookings);
      
      if (hotelBookings.length > 0) {
        // Process all hotels and their rooms
        const allConfigurations = [];
        
        // For each hotel booking
        hotelBookings.forEach(hotelBooking => {
          if (hotelBooking.data && hotelBooking.data.length > 0) {
            // Process each hotel in the data array
            hotelBooking.data.forEach(hotelData => {
              console.log("HOTEL COMPONENT - Processing hotel data:", hotelData.hotelDetails?.hotel_name);
              
              if (hotelData.hotelDetails && hotelData.rooms && hotelData.rooms.length > 0) {
                const hotelDetails = hotelData.hotelDetails;
                const bookingDates = hotelData.bookingDate || [];
                
                // Process each room and its beds
                hotelData.rooms.forEach(room => {
                  if (room.beds && room.beds.length > 0) {
                    room.beds.forEach(bed => {
                      // Create configuration from room and bed data
                      const config = createConfigFromRoomAndBed(hotelDetails, room, bed, bookingDates, mealPlanOptions);
                      allConfigurations.push(config);
                    });
                  }
                });
              }
            });
          }
        });
        
        if (allConfigurations.length > 0) {
          console.log("HOTEL COMPONENT - Created configurations from all hotel data:", allConfigurations);
          setHotelConfigurations(allConfigurations);
          
          // Show success message
          setAlert({
            show: true,
            message: `Loaded ${allConfigurations.length} room configuration(s) from ${hotelBookings.length} hotel(s)`,
            severity: 'success'
          });
          
          // Hide message after 5 seconds
          setTimeout(() => {
            setAlert(prev => ({ ...prev, show: false }));
          }, 5000);
          
          return { success: true, configurations: allConfigurations };
        }
      }
    }
    return { success: false };
  }, [packageData, mealPlanOptions]);
  
  // Call loadExistingHotelData when packageData changes
  useEffect(() => {
    if (packageData?.tour?.booking) {
      const result = loadExistingHotelData();
      console.log("HOTEL COMPONENT - Attempted to load existing hotel data, result:", result);
    }
  }, [packageData, loadExistingHotelData]);
  
  // Initialize hotel configurations when component mounts
  useEffect(() => {
    if (hotelConfigurations.length === 0 && !packageData?.tour?.booking) {
      console.log("HOTEL COMPONENT - Creating initial hotel configuration as no existing data found");
      const initialConfig = createInitialHotelConfiguration();
      setHotelConfigurations([initialConfig]);
      setActiveHotelIndex(0);
    }
  }, [packageData, createInitialHotelConfiguration, hotelConfigurations.length]);
  
  return {
    tourId,
    packageData,
    hotelConfigurations,
    setHotelConfigurations,
    activeHotelIndex,
    setActiveHotelIndex,
    alert,
    setAlert,
    loadExistingHotelData,
    createInitialHotelConfiguration
  };
};

export default useHotelData; 