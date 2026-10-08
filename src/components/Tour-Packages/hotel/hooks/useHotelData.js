import { useState, useEffect, useCallback, useRef } from 'react';
import { useSelector } from 'react-redux';
import moment from 'moment';
import { createConfigFromRoomAndBed } from '../utils/hotelUtils';

/**
 * Custom hook for loading and managing hotel data
 * packageData (read-only) → hotelConfigurations (local draft)
 * AllServices sync lives in HotelComponent (single write path).
 * @param {Array} mealPlanOptions Available meal plan options
 * @returns {Object} Hotel data and functions
 */
const useHotelData = (mealPlanOptions) => {
  // States for hotel configurations
  const [hotelConfigurations, setHotelConfigurations] = useState([]);
  const [activeHotelIndex, setActiveHotelIndex] = useState(0);
  const [alert, setAlert] = useState({ show: false, message: '', severity: 'info' });
  
  // Get tour ID from URL or Redux
  const router = typeof window !== 'undefined' ? new URL(window.location.href) : null;
  const urlTourId = router ? router.searchParams.get('tour_id') : null;
  const reduxTourId = useSelector((state) => state.hotels.id);
  const tourId = urlTourId || reduxTourId;
  
  // Get package data from Redux store (source for edit hydrate only)
  const packageData = useSelector(state => state.tourPackages.packageData);
  
  // Track whether we already attempted packageData hydrate (prevents empty-placeholder race)
  const hasHydratedRef = useRef(false);
  
  // Function to get booking ID for a specific hotel configuration (simplified)
  const getBookingIdForConfig = useCallback((config) => {
    return config?.originalData?.booking_id || null;
  }, []);
  
  // Function to get all booking IDs for tour update (simplified)
  const getAllBookingIds = useCallback(() => {
    return hotelConfigurations
      .map(config => config.originalData?.booking_id)
      .filter(Boolean); // Remove null/undefined values
  }, [hotelConfigurations]);
  
  // Function to create an initial hotel configuration
  const createInitialHotelConfiguration = useCallback((searchCriteria = {}) => {
    // Handle both uppercase and lowercase property names and calculate total guests
    const adults = parseInt(searchCriteria?.guests?.Adults || searchCriteria?.guests?.adults || 1);
    const children = parseInt(searchCriteria?.guests?.Children || searchCriteria?.guests?.children || 0);
    const initialGuests = adults + children; // Total guests (adults + children)
    
    // Calculate individual hotel booking dates
    let hotelCheckIn, hotelCheckOut;
    if (searchCriteria?.checkIn && searchCriteria?.checkOut) {
      hotelCheckIn = searchCriteria.checkIn;
      hotelCheckOut = searchCriteria.checkOut;
    } else {
      // Default to today and tomorrow
      hotelCheckIn = moment().format('DD/MM/YYYY');
      hotelCheckOut = moment().add(1, 'day').format('DD/MM/YYYY');
    }
    
    const nightsCount = moment(hotelCheckOut, 'DD/MM/YYYY').diff(moment(hotelCheckIn, 'DD/MM/YYYY'), 'days');
    
    // Create initial selected night indices
    const initialNightIndices = [];
    for (let i = 0; i < nightsCount; i++) {
      initialNightIndices.push(i);
    }
    
    return {
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
      hotelCheckIn: hotelCheckIn,
      hotelCheckOut: hotelCheckOut,
      babyCot: false,
      occupancyType: 'single',
      adultDistribution: { male: 0, female: 0 },
      expanded: true,
      selectedGuests: initialGuests,
      selectedMealPlan: 'self',
      mealPlanDetails: null,
      customerDetails: {
        fullName: "",
        email: "",
        phone: "",
        countryCode: "",
        address1: "",
        address2: "",
        state: "",
        zip: "",
        specialRequests: ""
      }
    };
  }, []);
  
  // Function to load existing hotel data from package data
  const loadExistingHotelData = useCallback(() => {
    if (!packageData?.tour?.booking) {
      return { success: false };
    }

    // Case-insensitive: API may send "hotel" or "Hotel"
    const hotelBookings = packageData.tour.booking.filter(
      (booking) => String(booking.type || '').toLowerCase() === 'hotel'
    );

    if (hotelBookings.length === 0) {
      return { success: false };
    }

    const allConfigurations = [];

    hotelBookings.forEach((hotelBooking) => {
      if (!hotelBooking.data || hotelBooking.data.length === 0) return;

      const bookingId = hotelBooking.booking_id;

      hotelBooking.data.forEach((hotelData) => {
        if (!hotelData.hotelDetails || !hotelData.rooms || hotelData.rooms.length === 0) return;

        const hotelDetails = hotelData.hotelDetails;
        const bookingDates = hotelData.bookingDate || [];

        hotelData.rooms.forEach((room) => {
          if (!room.beds || room.beds.length === 0) return;

          room.beds.forEach((bed) => {
            const customerDetails = {
              fullName: hotelData.fullName || "",
              email: hotelData.email || "",
              phone: hotelData.phone || "",
              countryCode: hotelData.countryCode || "",
              address1: hotelData.address1 || "",
              address2: hotelData.address2 || "",
              state: hotelData.state || "",
              zip: hotelData.zip || "",
              specialRequests: hotelData.specialRequests || ""
            };

            const config = createConfigFromRoomAndBed(
              hotelDetails,
              room,
              bed,
              bookingDates,
              mealPlanOptions,
              bookingId
            );

            config.customerDetails = customerDetails;
            allConfigurations.push(config);
          });
        });
      });
    });

    if (allConfigurations.length > 0) {
      setHotelConfigurations(allConfigurations);
      setActiveHotelIndex(0);
      setAlert({
        show: true,
        message: `Loaded ${allConfigurations.length} room configuration(s) from ${hotelBookings.length} hotel(s)`,
        severity: 'success'
      });
      setTimeout(() => {
        setAlert((prev) => ({ ...prev, show: false }));
      }, 5000);
      return { success: true, configurations: allConfigurations };
    }

    return { success: false };
  }, [packageData, mealPlanOptions]);
  
  // Hydrate from packageData once per package identity (edit mode)
  useEffect(() => {
    const bookingKey = packageData?.tour?.tour_id
      ? `tour-${packageData.tour.tour_id}`
      : packageData?.tour?.booking
        ? `booking-${packageData.tour.booking.length}`
        : null;

    if (!bookingKey) {
      // No package yet — allow empty placeholder in parent
      hasHydratedRef.current = true;
      return;
    }

    // Re-hydrate when a different package is loaded
    if (hasHydratedRef.current === bookingKey) {
      return;
    }

    const result = loadExistingHotelData();
    hasHydratedRef.current = bookingKey;

    // If package has no hotels, still mark hydrated so empty placeholder can appear
    if (!result.success && hotelConfigurations.length === 0) {
      // leave empty; parent may create placeholder after hydrate
    }
  }, [packageData, loadExistingHotelData, hotelConfigurations.length]);
  
  // Create empty config only for NEW packages (no booking list at all)
  useEffect(() => {
    if (!hasHydratedRef.current) return;
    if (packageData?.tour?.booking) return; // edit/create-with-booking handled by hydrate
    if (hotelConfigurations.length === 0) {
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
    getBookingIdForConfig,
    getAllBookingIds,
    loadExistingHotelData,
    createInitialHotelConfiguration,
    hasHydratedFromPackage: Boolean(hasHydratedRef.current)
  };
};

export default useHotelData; 