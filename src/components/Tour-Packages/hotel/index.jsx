import React, { useState, useEffect, useRef } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { 
  Box, Typography, Grid, Card, CardContent, CardMedia, 
  Divider, Button, FormControlLabel, Checkbox, TextField, 
  Paper, MenuItem, Select, FormControl, InputLabel, Avatar, 
  AvatarGroup, Chip, CircularProgress, List, ListItem,
  IconButton, Collapse, Alert, Menu
} from '@mui/material';
import moment from 'moment';
import HotelIcon from '@mui/icons-material/Hotel';
import PersonIcon from '@mui/icons-material/Person';
import WomanIcon from '@mui/icons-material/Woman';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import BabyChangingStationIcon from '@mui/icons-material/BabyChangingStation';
import ContentCopyIcon from '@mui/icons-material/ContentCopy';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import RestaurantMenuIcon from '@mui/icons-material/RestaurantMenu';
import StarIcon from '@mui/icons-material/Star';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import ExpandLessIcon from '@mui/icons-material/ExpandLess';
import RemoveIcon from '@mui/icons-material/Remove';
import CloseIcon from '@mui/icons-material/Close';
import HotelListing from './HotelListing';
import MealPlanListing from './MealPlanListing';
import { 
  setBookingArray, 
  updateBookingArray, 
  setTotalPrice
} from '@/slice/hotel/HotelDetailsSlice';
import { setAllServices } from '@/slice/tour-packages/tourPackageSlice';




// Mock meal plan data - moved outside component to prevent recreation
const mealPlanOptions = [
  { id: 'self', title: 'Room Only', description: 'No meals included', icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} /> },
  { id: 'breakfast', title: 'Room with Breakfast', description: 'Includes daily breakfast', icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} /> },
  { id: 'lunch', title: 'Room with Lunch', description: 'Includes daily lunch', icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} /> },
  { id: 'dinner', title: 'Room with Dinner', description: 'Includes daily dinner', icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} /> },
  { id: 'fullboard', title: 'Room with Full Board', description: 'Includes breakfast, lunch and dinner', icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} /> },
  { id: 'allinclusive', title: 'Room with All Inclusive', description: 'All meals and selected drinks included', icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} /> }
];

export default function HotelComponent({ searchParams }) {
  const dispatch = useDispatch();
  
  // States for single hotel
  const [selectedHotel, setSelectedHotel] = useState('');
  const [roomType, setRoomType] = useState('');
  const [bedType, setBedType] = useState('');
  const [occupancyType, setOccupancyType] = useState('single');
  const [mealPlan, setMealPlan] = useState('self');
  const [babyCotSelected, setBabyCotSelected] = useState(false);
  const [adultDistribution, setAdultDistribution] = useState({
    male: 0,
    female: 0
  });
  const [mealPlanPerPerson, setMealPlanPerPerson] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedNights, setSelectedNights] = useState(1);
  const [selectedNightIndices, setSelectedNightIndices] = useState(new Set());
  const [totalPackageNights, setTotalPackageNights] = useState(0);
  const [allocatedNights, setAllocatedNights] = useState(0);
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);
  
  // Multiple hotels management state
  const [hotelConfigurations, setHotelConfigurations] = useState([]);
  const [activeHotelIndex, setActiveHotelIndex] = useState(0);
  const [alert, setAlert] = useState({ show: false, message: '', severity: 'info' });
  
  // Add new state for guest management
  const [selectedGuests, setSelectedGuests] = useState(1);
  const [totalPackageGuests, setTotalPackageGuests] = useState(0);
  const [allocatedGuests, setAllocatedGuests] = useState(0);
  
  // Add new state for bed and meal quantities
  const [bedTypeQuantities, setBedTypeQuantities] = useState({});
  const [mealPlanQuantities, setMealPlanQuantities] = useState({});
  
  // Add new state for guest-specific meal plans
  const [guestMealPlans, setGuestMealPlans] = useState([]);
  
  // Add state to control dropdown open/close
  const [guestMealAnchorEl, setGuestMealAnchorEl] = useState(null);
  const isGuestMealMenuOpen = Boolean(guestMealAnchorEl);
  
  // Get data from Redux store
  const searchCriteria = useSelector(state => state.tourPackages?.searchCriteria || {});
  const selectedCity = useSelector(state => state.common?.selectedCity?.cityName);
  const selectedCountry = useSelector(state => state.common?.selectedCity?.countryName);
  const agentId = useSelector((state) => state.editing?.agentId);
  const tourId = useSelector((state) => state.hotels.id);
  
  // Get hotel room data from Redux store
  const { roomDatas, status: roomDataStatus } = useSelector(state => state.rooms);
  console.log("roomDatas", roomDatas);
  
  // Get hotel details from Redux (used for newly selected hotels)
  const currentHotelDetails = useSelector(state => state.hoteldetails?.bookingDetails || {});
  
  // Get booking array from Redux slice (following the same pattern as your HotelDetailsSlice)
  const bookingArray = useSelector(state => state.hoteldetails?.bookingArray || []);
  const totalPrice = useSelector(state => state.hoteldetails?.totalPrice || 0);
  
  // Get existing services from Redux state (similar to vehicle dropdown)
  const existingServices = useSelector(state => state.tourPackages.AllServices || []);
  
  // Get hotel details for the active configuration
  const apiHotelDetails = hotelConfigurations[activeHotelIndex]?.hotelDetails || currentHotelDetails;
  

  
  // Generate dates array from the selected date range
  const dates = React.useMemo(() => {
    if (!searchCriteria?.checkIn || !searchCriteria?.checkOut) return [];
    
    const startDate = moment(searchCriteria.checkIn, 'DD/MM/YYYY');
    const endDate = moment(searchCriteria.checkOut, 'DD/MM/YYYY');
    const dayDiff = endDate.diff(startDate, 'days');
    
    // Generate an array of all dates in the range
    const datesArray = [];
    for (let i = 0; i <= dayDiff; i++) {
      datesArray.push(moment(startDate).add(i, 'days'));
    }
    return datesArray;
  }, [searchCriteria]);

  // Calculate maximum nights based on date range
  const maxNights = dates.length > 0 ? dates.length - 1 : 0;
  
  // Initialize selected nights when dates change
  useEffect(() => {
    if (maxNights > 0) {
      setSelectedNights(maxNights); // Default to maximum nights
      
      // Update selected indices for consecutive range
      const allIndices = new Set();
      for (let i = 0; i < maxNights; i++) {
        allIndices.add(i);
      }
      setSelectedNightIndices(allIndices);
    } else {
      setSelectedNights(1); // Default to 1 if no date range
      setSelectedNightIndices(new Set([0]));
    }
  }, [maxNights]);

  // Get total nights from search criteria
  useEffect(() => {
    const totalNights = maxNights || 1;
    setTotalPackageNights(totalNights);
  }, [maxNights]);

  // Calculate remaining available nights
  const remainingNights = totalPackageNights - allocatedNights;

  // Update allocated nights when hotel configurations change
  useEffect(() => {
    const totalAllocated = hotelConfigurations.reduce((sum, config) => sum + (config.nights || 0), 0);
    setAllocatedNights(totalAllocated);
  }, [hotelConfigurations]);

  // Handle night selection change
  const handleNightChange = (event) => {
    const newNightCount = parseInt(event.target.value);
    if (newNightCount <= remainingNights + (hotelConfigurations[activeHotelIndex]?.nights || 0)) {
      setSelectedNights(newNightCount);
      
      // Auto-save nights selection
      setTimeout(() => {
        updateCurrentConfiguration();
      }, 50);
    }
  };
  
  // Create initial hotel configuration
  const createInitialHotelConfiguration = () => {
    const initialGuests = parseInt(searchCriteria?.guests?.adults || 1);
    const nightsCount = maxNights > 0 ? maxNights : 1;
    
    // Create initial selected night indices
    const initialNightIndices = [];
    for (let i = 0; i < nightsCount; i++) {
      initialNightIndices.push(i);
    }
    
    const initialConfig = {
      id: generateUniqueId(),
      hotelId: '',
      hotelDetails: {}, // Store hotel details within each configuration
      roomTypeId: '',
      roomTypeName: '', // Initialize room type name
      bedTypeId: '',
      bedTypeName: '', // Initialize bed type name
      max_occupancy: 1, // Initialize max occupancy
      bedPrice: 0, // Initialize bed price
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
    
    setHotelConfigurations([initialConfig]);
    setSelectedHotel('');
    setSelectedGuests(initialGuests);
    setGuestMealPlans(Array(initialGuests).fill('self'));
  };

  // Initialize hotel configurations when component mounts
  useEffect(() => {
    if (hotelConfigurations.length === 0) {
      createInitialHotelConfiguration();
    }
  }, []);

  // Load a hotel configuration into the state
  const loadHotelConfiguration = (config) => {
    setSelectedHotel(config.hotelId || '');
    setRoomType(config.roomTypeId || '');
    setBedType(config.bedTypeId || '');
    setMealPlan(config.mealPlanId || 'self');
    setSelectedNights(config.nights || 1);
    
    // Load selected night indices from configuration
    if (config.selectedNightIndices) {
      setSelectedNightIndices(new Set(config.selectedNightIndices));
    } else {
      // Fallback: generate indices based on nights count for backward compatibility
      const indices = new Set();
      for (let i = 0; i < (config.nights || 1); i++) {
        indices.add(i);
      }
      setSelectedNightIndices(indices);
    }
    
    setBabyCotSelected(config.babyCot || false);
    setOccupancyType(config.occupancyType || 'single');
    setAdultDistribution(config.adultDistribution || { male: 0, female: 0 });
    setSelectedGuests(config.selectedGuests || 1);
    setGuestMealPlans(config.guestMealPlans || Array(config.selectedGuests || 1).fill('self'));
  };

  // Update the active hotel configuration with current selections
  const updateActiveHotelConfiguration = () => {
    const updatedConfig = {
      ...hotelConfigurations[activeHotelIndex],
      hotelId: selectedHotel,
      hotelDetails: hotelConfigurations[activeHotelIndex]?.hotelDetails || {},
      roomTypeId: roomType,
      bedTypeId: bedType,
      mealPlanId: mealPlan,
      nights: selectedNights,
      selectedNightIndices: Array.from(selectedNightIndices),
      babyCot: babyCotSelected,
      occupancyType: occupancyType,
      adultDistribution: adultDistribution,
      selectedGuests: selectedGuests,
      guestMealPlans: guestMealPlans
    };
    
    const updatedConfigurations = [...hotelConfigurations];
    updatedConfigurations[activeHotelIndex] = updatedConfig;
    
    setHotelConfigurations(updatedConfigurations);
  };

  // Generate a unique ID for hotel configurations
  const generateUniqueId = () => {
    return Math.random().toString(36).substring(2) + Date.now().toString(36);
  };

  // Sync hotel configurations with Redux booking array (following your HotelDetailsSlice pattern)
  const syncToBookingArray = () => {
    // Clear existing booking array first
    dispatch(setBookingArray([]));
    
    // Process each hotel configuration
    hotelConfigurations.forEach((config) => {
      if (!config.hotelId || !config.roomTypeId || !config.bedTypeId) {
        console.warn('Skipping incomplete configuration:', config);
        return; // Skip incomplete configurations
      }

      // Find room and bed details from API data
      const roomData = roomTypes.find(r => r.id === config.roomTypeId);
      const bedData = bedTypes.find(b => b.id === config.bedTypeId);
      
      // Prepare selected meals in the format expected by your slice
      const selectedMeals = {};
      if (config.guestMealPlans && config.guestMealPlans.length > 0) {
        config.guestMealPlans.forEach((mealPlanId, guestIndex) => {
          const mealPlan = mealPlans.find(m => m.id === mealPlanId);
          if (mealPlan && mealPlan.id !== 'self') {
            selectedMeals[`meal_${guestIndex + 1}`] = {
              type: mealPlan.id,
              name: mealPlan.title,
              price: mealPlan.price || 0,
              description: mealPlan.description || ''
            };
          }
        });
      }

      // Prepare bed data in your slice format
      const bedDataFormatted = {
        bed_id: config.bedTypeId,
        bed_type: bedData?.name || 'Unknown Bed',
        head_count: config.selectedGuests || 1,
        max_occupancy: bedData?.max_occupancy || 1,
        baby_cot: config.babyCot || false,
        baby_cot_price: (config.babyCot && bedData?.baby_cot_price) ? parseFloat(bedData.baby_cot_price) : 0,
        selectedMeals: selectedMeals,
        mealTypes: Object.values(selectedMeals).map(meal => meal.type),
        nights: config.nights || 1,
        price: bedData?.price || 0,
        room_type: roomData?.name || 'Unknown Room'
      };

      // Use your existing updateBookingArray action to add each configuration
      dispatch(updateBookingArray({
        roomId: config.roomTypeId,
        bedId: config.bedTypeId,
        operation: "increment",
        data: bedDataFormatted
      }));

      console.log(`Added to booking array: ${config.hotelDetails?.name || 'Hotel'} - ${roomData?.name} - ${bedData?.name}`);
    });

    console.log("All hotel configurations synced to Redux booking array");
  };

  // Dispatch hotel configurations to Redux (similar to vehicle dropdown pattern)
  const dispatchHotelsToRedux = () => {
    // Filter completed hotel configurations
    const completedConfigurations = hotelConfigurations.filter(config => 
      config.hotelId && 
      config.roomTypeId && 
      config.bedTypeId && 
      config.nights > 0 &&
      config.selectedGuests > 0
    );

    console.log("Hotel - Completed configurations:", completedConfigurations);

    if (completedConfigurations.length > 0) {
      // Find any existing customer info in current services
      const customerInfoService = existingServices.find(service => service.type === 'CustomerInfo');

      // Group configurations by hotel ID to consolidate rooms
      const hotelGroups = {};
      
      completedConfigurations.forEach(config => {
        const hotel = config.hotelDetails || {};
        const hotelId = config.hotelId;
        const hotelName = hotel.hotel_name || hotel.name || 'Selected Hotel';
        const hotelKey = `${hotelId}_${hotelName}`;
        
        if (!hotelGroups[hotelKey]) {
          hotelGroups[hotelKey] = {
            hotelId: hotelId,
            hotelDetails: hotel,
            configs: [],
            totalPrice: 0,
            firstConfig: config // Keep the first config for common hotel data
          };
        }
        
        hotelGroups[hotelKey].configs.push(config);
      });
      
      // Format hotel data with consolidated rooms
      const hotelBookingsData = Object.values(hotelGroups).map(group => {
        const firstConfig = group.firstConfig;
        const hotel = group.hotelDetails;
        
        // Generate a stable ID for this hotel
        const hotelBookingId = firstConfig.id || `hotel-${Date.now()}-${Math.random().toString(36).substring(2)}`;
        
        // Create a date range covering all configurations for this hotel
        const allDates = [];
        group.configs.forEach(config => {
          const selectedIndices = config.selectedNightIndices || [];
          if (selectedIndices.length > 0) {
            selectedIndices.forEach(index => {
              if (dates[index] && !allDates.includes(dates[index].format('YYYY-MM-DD'))) {
                allDates.push(dates[index].format('YYYY-MM-DD'));
              }
            });
          }
        });
        
        // Sort dates and get first and last
        allDates.sort();
        const bookingDates = allDates.length >= 2 ? 
          [allDates[0], allDates[allDates.length-1]] : 
          [];
        
        // Consolidate all rooms from all configurations for this hotel
        const consolidatedRooms = group.configs.map(config => {
          // Use stored names directly from the configuration, instead of looking up from arrays
          // which might not have the correct data for all hotels at this point
          const roomTypeName = config.roomTypeName || 'Unknown Room';
          const bedTypeName = config.bedTypeName || 'Unknown Bed';
          const maxOccupancy = config.max_occupancy || 1;
          
          // Prepare selected meals object for the bed
          const selectedMeals = {};
          const mealTypes = [];
          let bedTotalPrice = config.bedPrice || bedTypes.find(b => b.id === config.bedTypeId)?.price || 0;
          
          (config.guestMealPlans || []).forEach((mealPlanId, index) => {
            const mealPlan = mealPlans.find(m => m.id === mealPlanId);
            if (mealPlan) {
              mealTypes.push(mealPlan.title);
              selectedMeals[`meal_${index + 1}`] = {
                type: mealPlan.title,
                price: mealPlan.price || 0
              };
              bedTotalPrice += (mealPlan.price || 0);
            }
          });
          
          // Calculate room price based on bed price and nights
          const roomPrice = bedTotalPrice * (config.nights || 1);
          
          // Add to total hotel price
          group.totalPrice += roomPrice;
          
          return {
            room_id: parseInt(config.roomTypeId) || 0,
            room_type: roomTypeName, // Use stored name directly from configuration
            beds: [{
              bed_id: parseInt(config.bedTypeId) || 0,
              bed_type: bedTypeName, // Use stored name directly from configuration
              max_occupancy: maxOccupancy,
              head_count: config.selectedGuests || 1,
              baby_cot: config.babyCot ? 1 : 0,
              mealTypes: mealTypes.length > 0 ? mealTypes : ["Room Only"],
              selectedMeals: Object.keys(selectedMeals).length > 0 ? selectedMeals : {
                meal_1: { type: "Room Only", price: bedTotalPrice }
              },
              price: bedTotalPrice,
              room_type: roomTypeName // Use stored name directly from configuration
            }]
          };
        });

        // Create the hotel booking data matching the exact payload structure from booking-page
        const hotelBookingData = {
          // Add all formData properties (spread from customer info if available)
          ...(customerInfoService ? {
            customer_name: customerInfoService.fullName || customerInfoService.name,
            customer_email: customerInfoService.email,
            customer_phone: customerInfoService.phone,
            address1: customerInfoService.address1,
            address2: customerInfoService.address2,
            state: customerInfoService.state,
            zip: customerInfoService.zip,
            specialRequests: customerInfoService.specialRequests,
            countryCode: customerInfoService.countryCode
          } : {}),
          
          // Core payload structure from booking-page/index.jsx
          rooms: consolidatedRooms,
          totalPrice: group.totalPrice || 0,
          bookingType: "booking",
          priceMode: "dmc",
          priceModeId: 4,
          hotelDetails: {
            hotel_id: group.hotelId || "",
            hotel_name: hotel.hotel_name || hotel.name || 'Selected Hotel',
            checkInTime: hotel.checkInTime || hotel.check_in_time || "15:00:00",
            checkOutTime: hotel.checkOutTime || hotel.check_out_time || "12:00:00",
            location: hotel.address || hotel.location || "",
            image: hotel.image || hotel.main_image || "",
            cancellation_charge: hotel.cancellation_charge || ""
          },
          bookingDate: bookingDates,
          
          // Additional fields for tour package context
          id: hotelBookingId,
          tour_id: parseInt(searchCriteria?.tourId) || 0
        };
        
        return hotelBookingData;
      });

      console.log("Hotel - Formatted bookings with consolidated rooms:", hotelBookingsData);

      // Get existing hotel services and non-hotel services
      const existingHotelServices = existingServices.filter(service => service.type === "Hotel");
      const nonHotelServices = existingServices.filter(service => service.type !== "Hotel");

      // Create a map of existing hotel data by hotel ID for reference
      const existingHotelMap = {};
      existingHotelServices.forEach(service => {
        if (service.data && service.data.length > 0) {
          const hotelData = service.data[0];
          if (hotelData && hotelData.hotelDetails && hotelData.hotelDetails.hotel_id) {
            existingHotelMap[hotelData.hotelDetails.hotel_id] = hotelData;
          }
        }
      });

      // Build the new list of hotel services - preserving existing ones not in current config
      let updatedHotelServices = [];
      
      // First, add all existing hotels that aren't in the current configurations
      existingHotelServices.forEach(service => {
        if (service.data && service.data.length > 0) {
          const hotelData = service.data[0];
          if (hotelData && hotelData.hotelDetails && hotelData.hotelDetails.hotel_id) {
            const hotelId = hotelData.hotelDetails.hotel_id;
            
            // Check if this hotel is in the current configurations
            const isInCurrentConfigs = completedConfigurations.some(
              config => config.hotelId === hotelId
            );
            
            // If not in current configs, keep it as is
            if (!isInCurrentConfigs) {
              updatedHotelServices.push(service);
            }
          }
        }
      });
      
      // Then, add all hotels from the current configurations
      hotelBookingsData.forEach(hotelData => {
        updatedHotelServices.push({
          type: "Hotel", 
          agent_id: agentId,
          tour_id: tourId,
          data: [hotelData]
        });
      });

      // Create the final list of services combining non-hotel services with updated hotel services
      const updatedServices = [...nonHotelServices, ...updatedHotelServices];
      
      console.log("Hotel - Dispatching finalServices to Redux:", updatedServices);
      dispatch(setAllServices(updatedServices));
    }
  };

  // Add a new hotel/room using the Redux pattern (similar to your updateBookingArray)
  const addToBookingArray = (hotelConfig) => {
    if (!hotelConfig.hotelId || !hotelConfig.roomTypeId || !hotelConfig.bedTypeId) {
      console.warn("Incomplete hotel configuration, skipping add to booking array");
      return;
    }

    const roomData = roomTypes.find(r => r.id === hotelConfig.roomTypeId);
    const bedData = bedTypes.find(b => b.id === hotelConfig.bedTypeId);
    
    // Prepare selected meals
    const selectedMeals = {};
    if (hotelConfig.guestMealPlans && hotelConfig.guestMealPlans.length > 0) {
      hotelConfig.guestMealPlans.forEach((mealPlanId, guestIndex) => {
        const mealPlan = mealPlans.find(m => m.id === mealPlanId);
        if (mealPlan && mealPlan.id !== 'self') {
          selectedMeals[`meal_${guestIndex + 1}`] = {
            type: mealPlan.id,
            name: mealPlan.title,
            price: mealPlan.price || 0
          };
        }
      });
    }

    const bedData_formatted = {
      bed_id: hotelConfig.bedTypeId,
      bed_type: bedData?.name || 'Unknown Bed',
      head_count: hotelConfig.selectedGuests || 1,
      baby_cot: hotelConfig.babyCot || false,
      baby_cot_price: bedData?.baby_cot_price || 0,
      selectedMeals: selectedMeals,
      mealTypes: Object.values(selectedMeals).map(meal => meal.type),
      room_type: roomData?.name || 'Unknown Room',
      price: bedData?.price || 0
    };

    // Use your existing updateBookingArray action
    dispatch(updateBookingArray({
      roomId: hotelConfig.roomTypeId,
      bedId: hotelConfig.bedTypeId,
      operation: "increment",
      data: bedData_formatted
    }));
  };
  
  // Handle meal plan selection from cards
  const handleMealPlanCardSelect = (mealType) => {
    setMealPlan(mealType);
    
    // Update meal plan for all persons to match
    if (mealPlanPerPerson.length > 0) {
      const newMealPlans = mealPlanPerPerson.map(() => mealType);
      setMealPlanPerPerson(newMealPlans);
    }
  };

  // Handle baby cot toggle
  const handleBabyCotToggle = () => {
    setBabyCotSelected(!babyCotSelected);
  };

  // Duplicate the current hotel configuration
  const handleDuplicateHotel = () => {
    // Get current configuration
    const currentConfig = hotelConfigurations[activeHotelIndex];
    
    // Create a duplicate with a new ID
    const duplicatedConfig = {
      ...currentConfig,
      id: generateUniqueId(),
      expanded: true
    };
    
    // Add to configurations array
    const updatedConfigurations = [...hotelConfigurations];
    updatedConfigurations.push(duplicatedConfig);
    
    setHotelConfigurations(updatedConfigurations);
    setActiveHotelIndex(updatedConfigurations.length - 1);
    
    // Show success message
    setAlert({
      show: true,
      message: 'Hotel configuration duplicated successfully',
      severity: 'success'
    });
    
    // Hide message after 3 seconds
    setTimeout(() => {
      setAlert({ ...alert, show: false });
    }, 3000);
  };

  // Add a new similar hotel (new hotel but keep room type and other settings)
  // Add New Hotel (completely new hotel - resets everything)
  const handleAddNewHotel = () => {
    // Create a completely fresh hotel configuration
    const initialGuests = parseInt(searchCriteria?.guests?.adults || 1);
    const nightsCount = maxNights > 0 ? maxNights : 1;
    
    // Create initial selected night indices
    const initialNightIndices = [];
    for (let i = 0; i < nightsCount; i++) {
      initialNightIndices.push(i);
    }
    
    const newHotelConfig = {
      id: generateUniqueId(),
      hotelId: '', // Empty - user will select from HotelListing
      hotelDetails: {}, // Empty - will be populated on hotel selection
      roomTypeId: '',
      roomTypeName: '', // Initialize room type name
      bedTypeId: '',
      bedTypeName: '', // Initialize bed type name
      max_occupancy: 1, // Initialize max occupancy
      bedPrice: 0, // Initialize bed price
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
    
    // Add to configurations array
    const updatedConfigurations = [...hotelConfigurations];
    updatedConfigurations.push(newHotelConfig);
    
    setHotelConfigurations(updatedConfigurations);
    setActiveHotelIndex(updatedConfigurations.length - 1);
    
    // Reset ALL selections for the new hotel (including hotel selection state)
    setSelectedHotel(''); // Reset selected hotel state
    setRoomType('');
    setBedType('');
    setMealPlan('self');
    setSelectedNights(nightsCount);
    
    // Reset selected night indices
    const allIndices = new Set();
    for (let i = 0; i < nightsCount; i++) {
      allIndices.add(i);
    }
    setSelectedNightIndices(allIndices);
    
    setBabyCotSelected(false);
    setOccupancyType('single');
    setAdultDistribution({ male: 0, female: 0 });
    setSelectedGuests(initialGuests);
    setGuestMealPlans(Array(initialGuests).fill('self'));
    
    // Reset search term and dropdown state for hotel selection
    setSearchTerm('');
    setIsDropdownOpen(false);
    
    // Show success message
    setAlert({
      show: true,
      message: 'New hotel configuration created - please select a hotel from the list',
      severity: 'success'
    });
    
    // Hide message after 3 seconds
    setTimeout(() => {
      setAlert({ ...alert, show: false });
    }, 3000);
  };

  const handleAddSimilarHotel = () => {
    // Just call the new handleAddNewHotel function
    handleAddNewHotel();
  };

  // Remove the current hotel configuration
  const handleRemoveHotel = () => {
    if (hotelConfigurations.length <= 1) {
      // Show error if there's only one hotel configuration
      setAlert({
        show: true,
        message: 'Cannot remove the only hotel configuration',
        severity: 'error'
      });
      
      // Hide message after 3 seconds
      setTimeout(() => {
        setAlert({ ...alert, show: false });
      }, 3000);
      
      return;
    }
    
    // Check if this is part of multiple configurations for the same hotel
    const currentConfig = hotelConfigurations[activeHotelIndex];
    const hotelConfigs = hotelConfigurations.filter(c => c.hotelId === currentConfig.hotelId);
    const isMultipleRoomsForHotel = hotelConfigs.length > 1;
    
    // Remove the current configuration
    const updatedConfigurations = hotelConfigurations.filter((_, index) => index !== activeHotelIndex);
    
    // Update active index if needed
    const newActiveIndex = activeHotelIndex >= updatedConfigurations.length 
      ? updatedConfigurations.length - 1 
      : activeHotelIndex;
    
    setHotelConfigurations(updatedConfigurations);
    setActiveHotelIndex(newActiveIndex);
    
    // Load the new active configuration
    const newActiveConfig = updatedConfigurations[newActiveIndex];
    if (newActiveConfig) {
      setSelectedHotel(newActiveConfig.hotelId || '');
      setRoomType(newActiveConfig.roomTypeId || '');
      setBedType(newActiveConfig.bedTypeId || '');
      setMealPlan(newActiveConfig.mealPlanId || 'self');
      setSelectedNights(newActiveConfig.nights || 1);
      setBabyCotSelected(newActiveConfig.babyCot || false);
    }
    
    // Show success message
    setAlert({
      show: true,
      message: isMultipleRoomsForHotel ? 'Room configuration removed' : 'Hotel configuration removed',
      severity: 'success'
    });
    
    // Hide message after 3 seconds
    setTimeout(() => {
      setAlert({ ...alert, show: false });
    }, 3000);
  };

  // Toggle the expanded state of a hotel configuration
  const toggleHotelExpanded = (index) => {
    const updatedConfigurations = [...hotelConfigurations];
    updatedConfigurations[index] = {
      ...updatedConfigurations[index],
      expanded: !updatedConfigurations[index].expanded
    };
    
    setHotelConfigurations(updatedConfigurations);
  };

  // Select a hotel configuration to edit
  const selectHotelConfiguration = (index) => {
    console.log(`Switching to configuration ${index}:`, hotelConfigurations[index]);
    
    // Update the active index
    setActiveHotelIndex(index);
    
    // Load the configuration using the existing function
    const config = hotelConfigurations[index];
    if (config) {
      loadHotelConfiguration(config);
      
      // If the configuration has hotel details, make sure they're displayed
      if (config.hotelDetails && Object.keys(config.hotelDetails).length > 0) {
        console.log(`Loading hotel details for ${config.hotelDetails.hotel_name || 'Unknown Hotel'}`);
      }
    }
  };
  
  // If no dates available, show a message
  if (dates.length === 0) {
    return (
      <Paper sx={{ p: 4, textAlign: 'center' }}>
        <Typography variant="h5">
          Please select travel dates to view your hotel options
        </Typography>
      </Paper>
    );
  }
  

  
  // Update state to handle multiple selections
  const [selectedRoomTypes, setSelectedRoomTypes] = useState([]);
  const [selectedBedTypes, setSelectedBedTypes] = useState([]);
  const [selectedMealPlans, setSelectedMealPlans] = useState([]);

  // Handle multiple selections
  const handleRoomTypeChange = (event) => {
    const {
      target: { value },
    } = event;
    setSelectedRoomTypes(typeof value === 'string' ? value.split(',') : value);
  };

  const handleBedTypeChange = (event) => {
    const {
      target: { value },
    } = event;
    setSelectedBedTypes(typeof value === 'string' ? value.split(',') : value);
  };

  const handleMealPlanChange = (event) => {
    const {
      target: { value },
    } = event;
    setSelectedMealPlans(typeof value === 'string' ? value.split(',') : value);
  };
  
  // Handle bed type quantity change
  const handleBedTypeQuantityChange = (bedId, change) => {
    setBedTypeQuantities(prev => {
      const currentQuantity = prev[bedId] || 0;
      const newQuantity = currentQuantity + change;
      
      // Don't allow negative quantities
      if (newQuantity < 0) return prev;
      
      // Don't allow exceeding total guests
      const totalSelectedBeds = Object.values(prev).reduce((sum, qty) => sum + qty, 0) - currentQuantity + newQuantity;
      if (totalSelectedBeds > selectedGuests) return prev;
      
      return {
        ...prev,
        [bedId]: newQuantity
      };
    });
  };

  // Handle meal plan quantity change
  
  // Update room type and bed type options when room data is loaded
  useEffect(() => {
    if (roomDataStatus === 'succeeded' && roomDatas) {
      // Reset previous selections as we have new data
      setRoomType('');
      setBedType('');
      setMealPlan('self');
      
      if (roomDatas?.room_data && Array.isArray(roomDatas.room_data) && roomDatas.room_data.length > 0) {
        console.log("API response contains room_data with length:", roomDatas.room_data.length);
      } else if (Array.isArray(roomDatas) && roomDatas.length > 0) {
        console.log("API response is an array with length:", roomDatas.length);
      } else {
        console.log("API response has unexpected format:", roomDatas);
      }
      
      // Show success message
      setAlert({
        show: true,
        message: 'Hotel details loaded successfully',
        severity: 'success'
      });
      
      // Hide message after 3 seconds
      setTimeout(() => {
        setAlert({ show: false });
      }, 3000);
    }
  }, [roomDatas, roomDataStatus]);

  // Extract room types from API response
  const apiRoomTypes = React.useMemo(() => {
    // Check if roomDatas is an object with room_data array (from the actual API response)
    if (roomDatas?.room_data && Array.isArray(roomDatas.room_data)) {
      console.log("Using room_data from API response:", roomDatas.room_data);
      return roomDatas.room_data.map((room) => ({
        id: room.room_id.toString(),
        name: room.room_type,
        hotel_id: selectedHotel,
        description: roomDatas.description || '',
        image: room.room_image && room.room_image.length > 0 ? room.room_image[0] : null,
        price: room.single_price || room.double_price || '0',
        tax_percentage: room.tax_percentage,
        complemenatry_breakfast: room.complemenatry_breakfast_included,
        breakfast: room.breakfast,
        lunch: room.lunch,
        dinner: room.dinner,
        roomOptions: room.bed_details || []
      }));
    }
    
    // Fallback to direct roomDatas if it's an array (might be from a different API format)
    if (roomDatas && Array.isArray(roomDatas) && roomDatas.length > 0) {
      return roomDatas.map((room, index) => ({
        id: room.id?.toString() || index.toString(),
        name: room.room_type || `Room Type ${index + 1}`,
        hotel_id: selectedHotel,
        description: room.description || '',
        roomOptions: room.roomOptions || []
      }));
    }
    
    // Return empty array if no valid data
    return [];
  }, [roomDatas, selectedHotel]);
  
  // Extract bed types from API response based on selected room
  const apiBedTypes = React.useMemo(() => {
    if (!roomDatas || !roomType) return [];
    
    // Check if roomDatas is an object with room_data array (from the actual API response)
    if (roomDatas?.room_data && Array.isArray(roomDatas.room_data)) {
      const selectedRoomData = roomDatas.room_data.find(room => room.room_id.toString() === roomType);
      
      if (!selectedRoomData || !Array.isArray(selectedRoomData.bed_details)) {
        return [];
      }
      
      console.log("Found bed details for room", roomType, ":", selectedRoomData.bed_details);
      return selectedRoomData.bed_details.map((bed) => ({
        id: bed.bed_id.toString(),
        name: bed.bed_type,
        room_type_id: roomType,
        max_occupancy: bed.max_occupancy || (bed.adult_count + bed.child_count) || 2,
        extra_bed: bed.extra_bed,
        extra_bed_price: bed.extra_bed_price,
        baby_cot: bed.baby_cot,
        baby_cot_price: bed.baby_cot_price,
        adult_count: bed.adult_count,
        child_count: bed.child_count
      }));
    }
    
    // Fallback to previous format
    const selectedRoomData = Array.isArray(roomDatas) 
      ? roomDatas.find(room => room.id?.toString() === roomType || room.room_type === roomType)
      : null;
    
    if (!selectedRoomData || !selectedRoomData.roomOptions) {
      return [];
    }
    
    return selectedRoomData.roomOptions.map((option, index) => ({
      id: option.id?.toString() || index.toString(),
      name: option.bed_type || option.name || `Bed Type ${index + 1}`,
      room_type_id: roomType,
      max_occupancy: option.max_occupancy || option.guests || 2,
      price: option.price || 0,
      description: option.description || ''
    }));
  }, [roomDatas, roomType]);
  
  // Extract meal plans from API response
  const apiMealPlans = React.useMemo(() => {
    if (!roomDatas || !roomType) return mealPlanOptions;
    
    // Extract meal plan information from the API response structure
    if (roomDatas?.room_data && Array.isArray(roomDatas.room_data)) {
      const selectedRoomData = roomDatas.room_data.find(room => room.room_id.toString() === roomType);
      
      if (!selectedRoomData) return mealPlanOptions;
      
      // Create dynamic meal plan options based on room data
      const availableMealPlans = [];
      
      // Always add Room Only option
      availableMealPlans.push({
        id: 'self',
        title: 'Room Only',
        description: 'No meals included',
        price: 0,
        icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} />
      });
      
      // Add breakfast option if available
      if (selectedRoomData.breakfast === 1) {
        availableMealPlans.push({
          id: 'breakfast',
          title: `Room with ${selectedRoomData.breakfast_type || 'Breakfast'}`,
          description: selectedRoomData.complemenatry_breakfast_included 
            ? 'Complimentary breakfast included' 
            : `Breakfast: $${selectedRoomData.breakfast_price || 0}`,
          price: selectedRoomData.complemenatry_breakfast_included ? 0 : parseFloat(selectedRoomData.breakfast_price || 0),
          icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} />
        });
      }
      
      // Add lunch option if available
      if (selectedRoomData.lunch === 1) {
        availableMealPlans.push({
          id: 'lunch',
          title: `Room with ${selectedRoomData.lunch_type || 'Lunch'}`,
          description: `Lunch: $${selectedRoomData.lunch_price || 0}`,
          price: parseFloat(selectedRoomData.lunch_price || 0),
          icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} />
        });
      }
      
      // Add dinner option if available
      if (selectedRoomData.dinner === 1) {
        availableMealPlans.push({
          id: 'dinner',
          title: `Room with ${selectedRoomData.dinner_type || 'Dinner'}`,
          description: `Dinner: $${selectedRoomData.dinner_price || 0}`,
          price: parseFloat(selectedRoomData.dinner_price || 0),
          icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} />
        });
      }
      
      // Add full board option (breakfast, lunch, dinner) if all are available
      if (selectedRoomData.breakfast === 1 && selectedRoomData.lunch === 1 && selectedRoomData.dinner === 1) {
        const fullBoardPrice = 
          (selectedRoomData.complemenatry_breakfast_included ? 0 : parseFloat(selectedRoomData.breakfast_price || 0)) + 
          parseFloat(selectedRoomData.lunch_price || 0) + 
          parseFloat(selectedRoomData.dinner_price || 0);
        
        availableMealPlans.push({
          id: 'fullboard',
          title: 'Room with Full Board',
          description: `Includes breakfast, lunch and dinner: $${fullBoardPrice.toFixed(2)}`,
          price: fullBoardPrice,
          icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} />
        });
      }
      
      console.log("Generated meal plans from room data:", availableMealPlans);
      return availableMealPlans.length > 0 ? availableMealPlans : mealPlanOptions;
    }
    
    // Fallback to previous logic
    const selectedRoomData = Array.isArray(roomDatas)
      ? roomDatas.find(room => room.id?.toString() === roomType || room.room_type === roomType)
      : null;
      
    if (!selectedRoomData || !selectedRoomData.roomOptions) return mealPlanOptions;
    
    const selectedBedOption = selectedRoomData.roomOptions.find(option => option.id?.toString() === bedType);
    if (!selectedBedOption || !selectedBedOption.meal_plans) return mealPlanOptions;
    
    // If API provides meal plans, use those
    if (Array.isArray(selectedBedOption.meal_plans) && selectedBedOption.meal_plans.length > 0) {
      return selectedBedOption.meal_plans.map((meal, index) => ({
        id: meal.id?.toString() || meal.type || index.toString(),
        title: meal.name || meal.type || `Meal Plan ${index + 1}`,
        description: meal.description || '',
        price: meal.price || 0,
        icon: <RestaurantMenuIcon sx={{ fontSize: 18 }} />
      }));
    }
    
    // Fallback to default meal plans
    return mealPlanOptions;
  }, [roomDatas, roomType, bedType]);
  
  // Use only API data - removing memoization that was causing issues
  const roomTypes = apiRoomTypes;
  const bedTypes = apiBedTypes;
  const mealPlans = apiMealPlans.length > 0 ? apiMealPlans : mealPlanOptions;

  // Get total guests from search criteria
  useEffect(() => {
    const totalGuests = parseInt(searchCriteria?.guests?.adults || 1);
    setTotalPackageGuests(totalGuests);
  }, [searchCriteria]);

  // Calculate remaining available guests
  
  const remainingGuests = totalPackageGuests - allocatedGuests;

  // Update allocated guests when hotel configurations change
  useEffect(() => {
    const totalAllocated = hotelConfigurations.reduce((sum, config) => sum + (config.selectedGuests || 0), 0);
    setAllocatedGuests(totalAllocated);
  }, [hotelConfigurations]);

  // Handle guest selection change
  const handleGuestChange = (event) => {
    const newGuestCount = parseInt(event.target.value);
    if (newGuestCount <= remainingGuests + (hotelConfigurations[activeHotelIndex]?.selectedGuests || 0)) {
      setSelectedGuests(newGuestCount);
      
      // Auto-save guest count selection
      setTimeout(() => {
        updateCurrentConfiguration();
      }, 50);
    }
  };
  
  // Replace Room Type Section to use the dynamic room types
  const renderRoomTypeSection = () => (
    <Grid item xs={12} md={2.5}>
      <Typography variant="subtitle1" fontWeight={500}>Select Room Type</Typography>
      <FormControl fullWidth sx={{ mt: 2 }} disabled={!selectedHotel || roomDataStatus === 'loading'}>
        <InputLabel id="room-type-select-label">Room Type</InputLabel>
        <Select
          labelId="room-type-select-label"
          id="room-type-select"
          value={roomType}
          label="Room Type"
          onChange={(e) => {
            const selected = e.target.value;
            setRoomType(selected);
            setBedType(''); // Reset bed type when room type changes
            
            // Auto-save room type selection with updated values
            setTimeout(() => {
              if (hotelConfigurations[activeHotelIndex]) {
                const currentConfig = hotelConfigurations[activeHotelIndex];
                
                
                
                
                // Find the selected room type details to store the name
                const selectedRoomType = roomTypes.find(r => r.id === selected);
                
                const updatedConfig = {
                  ...currentConfig,
                  roomTypeId: selected, // Use the selected value directly
                  roomTypeName: selectedRoomType?.name || 'Unknown Room', // Store the name directly
                  roomTypeName: selectedRoomType?.name || 'Unknown Room', // Store the name directly
                  bedTypeId: '', // Reset bed type
                  bedTypeName: '', // Reset bed type name
                  bedTypeName: '', // Reset bed type name
                };
                
                const updatedConfigurations = [...hotelConfigurations];
                updatedConfigurations[activeHotelIndex] = updatedConfig;
                setHotelConfigurations(updatedConfigurations);
                
                console.log('Room type saved:', selected, 'with name:', selectedRoomType?.name);
                console.log('Room type saved:', selected, 'with name:', selectedRoomType?.name);
              }
            }, 50);
          }}
          endAdornment={
            roomType && (
              <Box sx={{ mr: 1 }}>
                <IconButton
                  size="small"
                  onClick={(e) => {
                    e.stopPropagation();
                    setRoomType('');
                    setBedType('');
                    
                    // Save cleared room type
                    setTimeout(() => {
                      if (hotelConfigurations[activeHotelIndex]) {
                        const currentConfig = hotelConfigurations[activeHotelIndex];
                        const updatedConfig = {
                          ...currentConfig,
                          roomTypeId: '',
                          roomTypeName: '',
                          roomTypeName: '',
                          bedTypeId: '',
                          bedTypeName: '',
                          bedTypeName: '',
                        };
                        
                        const updatedConfigurations = [...hotelConfigurations];
                        updatedConfigurations[activeHotelIndex] = updatedConfig;
                        setHotelConfigurations(updatedConfigurations);
                        
                        console.log('Room type cleared');
                      }
                    }, 50);
                  }}
                >
                  <CloseIcon fontSize="small" />
                </IconButton>
              </Box>
            )
          }
        >
          <MenuItem value="">
            <em>Select a room type</em>
          </MenuItem>
          {roomDataStatus === 'loading' ? (
            <MenuItem disabled>
              <CircularProgress size={20} sx={{ mr: 1 }} />
              Loading room types...
            </MenuItem>
          ) : (
            roomTypes.map((room) => (
              <MenuItem key={room.id} value={room.id}>
                {room.name}
              </MenuItem>
            ))
          )}
        </Select>
      </FormControl>
    </Grid>
  );

  // Replace Bed Type Section to use the dynamic bed types
  const renderBedTypeSection = () => (
    <Grid item xs={12} md={2}>
      <Typography variant="subtitle1" fontWeight={500}>Select Bed Type</Typography>
      <FormControl fullWidth sx={{ mt: 2 }} disabled={!roomType}>
        <InputLabel id="bed-type-select-label">Bed Type</InputLabel>
        <Select
          labelId="bed-type-select-label"
          id="bed-type-select"
          value={bedType}
          label="Bed Type"
          onChange={(e) => {
            const selected = e.target.value;
            setBedType(selected);
            
            // Set occupancy type based on bed's max occupancy
            const selectedBed = bedTypes.find(bed => bed.id === selected);
            if (selectedBed) {
              if (selectedBed.max_occupancy === 1) {
                setOccupancyType('single');
              } else if (selectedBed.max_occupancy === 2) {
                setOccupancyType('double');
              } else if (selectedBed.max_occupancy === 3) {
                setOccupancyType('triple');
              }
            }
            
            // Auto-save bed type selection with updated values
            setTimeout(() => {
              if (hotelConfigurations[activeHotelIndex]) {
                const currentConfig = hotelConfigurations[activeHotelIndex];
                
                
                
                
                // Find the selected bed type details to store the name
                const selectedBedType = bedTypes.find(b => b.id === selected);
                
                const updatedConfig = {
                  ...currentConfig,
                  bedTypeId: selected, // Use the selected value directly
                  bedTypeName: selectedBedType?.name || 'Unknown Bed', // Store the name directly
                  bedTypeName: selectedBedType?.name || 'Unknown Bed', // Store the name directly
                  roomTypeId: roomType, // Keep the current room type
                  max_occupancy: selectedBedType?.max_occupancy || 1, // Store max occupancy
                  bedPrice: selectedBedType?.price || 0, // Store the bed price
                  max_occupancy: selectedBedType?.max_occupancy || 1, // Store max occupancy
                  bedPrice: selectedBedType?.price || 0, // Store the bed price
                };
                
                const updatedConfigurations = [...hotelConfigurations];
                updatedConfigurations[activeHotelIndex] = updatedConfig;
                setHotelConfigurations(updatedConfigurations);
                
                console.log('Bed type saved:', selected, 'with name:', selectedBedType?.name, 'price:', selectedBedType?.price);
                console.log('Bed type saved:', selected, 'with name:', selectedBedType?.name, 'price:', selectedBedType?.price);
              }
            }, 50);
          }}
          endAdornment={
            bedType && (
              <Box sx={{ mr: 1 }}>
                <IconButton
                  size="small"
                  onClick={(e) => {
                    e.stopPropagation();
                    setBedType('');
                    
                    // Save cleared bed type
                    setTimeout(() => {
                      if (hotelConfigurations[activeHotelIndex]) {
                        const currentConfig = hotelConfigurations[activeHotelIndex];
                        const updatedConfig = {
                          ...currentConfig,
                          bedTypeId: '',
                          bedTypeName: '',
                          bedPrice: 0, // Reset the bed price
                          bedTypeName: '',
                          bedPrice: 0, // Reset the bed price
                        };
                        
                        const updatedConfigurations = [...hotelConfigurations];
                        updatedConfigurations[activeHotelIndex] = updatedConfig;
                        setHotelConfigurations(updatedConfigurations);
                        
                        console.log('Bed type cleared');
                      }
                    }, 50);
                  }}
                >
                  <CloseIcon fontSize="small" />
                </IconButton>
              </Box>
            )
          }
        >
          <MenuItem value="">
            <em>Select a bed type</em>
          </MenuItem>
          {bedTypes.length > 0 ? (
            bedTypes.map((bed) => (
              <MenuItem key={bed.id} value={bed.id}>
                {bed.name} {bed.max_occupancy && `(Max: ${bed.max_occupancy} person${bed.max_occupancy > 1 ? 's' : ''})`}
                {bed.price > 0 && ` - $${parseFloat(bed.price).toFixed(2)}`}
                {bed.price > 0 && ` - $${parseFloat(bed.price).toFixed(2)}`}
              </MenuItem>
            ))
          ) : (
            roomType && (
              <MenuItem disabled>
                <em>No bed types available for this room</em>
              </MenuItem>
            )
          )}
        </Select>
      </FormControl>
      
      {/* Bed Type Quantities */}
      {/* {bedType && (
        <Box sx={{ mt: 2 }}>
          <Typography variant="subtitle2" gutterBottom>Select Quantities:</Typography>
          {bedTypes
            .filter(bed => bed.id === bedType)
            .map((bed) => (
              <Box key={bed.id} sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                <Typography variant="body2" sx={{ flex: 1 }}>
                  {bed.name}
                </Typography>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <IconButton 
                    size="small" 
                    onClick={() => handleBedTypeQuantityChange(bed.id, -1)}
                    disabled={!bedTypeQuantities[bed.id]}
                  >
                    <RemoveIcon fontSize="small" />
                  </IconButton>
                  <Typography sx={{ mx: 1 }}>
                    {bedTypeQuantities[bed.id] || 0}
                  </Typography>
                  <IconButton 
                    size="small" 
                    onClick={() => handleBedTypeQuantityChange(bed.id, 1)}
                    disabled={
                      Object.values(bedTypeQuantities).reduce((sum, qty) => sum + qty, 0) >= selectedGuests
                    }
                  >
                    <AddIcon fontSize="small" />
                  </IconButton>
                </Box>
              </Box>
            ))
          }
        </Box>
      )} */}
    </Grid>
  );

  // Single dropdown for guests and meal plans
  const renderMealPlanSection = () => (
    <Grid item xs={12} md={2.5}>
      <Typography variant="subtitle1" fontWeight={500}>Guests & Meal Plans</Typography>
      
              {/* Button + Menu approach for better control */}
        <Box sx={{ mt: 2 }}>
          <Button
            fullWidth
            variant="outlined"
            disabled={!bedType}
            onClick={(event) => setGuestMealAnchorEl(event.currentTarget)}
            sx={{ 
              justifyContent: 'flex-start',
              textAlign: 'left',
              py: 1.5,
              px: 2,
              color: 'text.primary',
              borderColor: 'rgba(0, 0, 0, 0.23)',
              '&:hover': {
                borderColor: 'rgba(0, 0, 0, 0.87)',
              }
            }}
            endIcon={<ExpandMoreIcon />}
          >
            {(() => {
            const totalTourGuests = parseInt(searchCriteria?.guests?.adults || 1);
            const selectedBed = bedTypes.find(bed => bed.id === bedType);
            const maxBedOccupancy = selectedBed?.max_occupancy || totalTourGuests;
            
            if (selectedGuests === 0) {
                return <Typography variant="body2" color="text.secondary">
                  Select number of guests (Tour: {totalTourGuests}{bedType ? `, Bed: ${maxBedOccupancy}` : ''})
                </Typography>;
              }
              if (guestMealPlans.length === 0 || guestMealPlans.every(plan => plan === 'self')) {
                return <Typography variant="body2">
                  {selectedGuests}/{totalTourGuests} guest{selectedGuests > 1 ? 's' : ''} - Select meal plans
                </Typography>;
            }
            const summary = guestMealPlans.map((plan, index) => {
              const planName = mealPlans.find(p => p.id === plan)?.title || 'Room Only';
              return `Guest ${index + 1}: ${planName}`;
            }).join(', ');
              return <Typography variant="body2" noWrap>
                {summary.length > 50 ? `${selectedGuests}/${totalTourGuests} guests with meal plans` : summary}
              </Typography>;
            })()}
          </Button>
          
          <Menu
            anchorEl={guestMealAnchorEl}
            open={isGuestMealMenuOpen}
            onClose={(event, reason) => {
              // Only close on backdrop click or escape key
              if (reason === 'backdropClick' || reason === 'escapeKeyDown') {
                setGuestMealAnchorEl(null);
              }
            }}
            MenuListProps={{
              'aria-labelledby': 'guest-meal-button',
              sx: { maxHeight: 400, width: 350 }
            }}
            anchorOrigin={{
              vertical: 'bottom',
              horizontal: 'left',
            }}
            transformOrigin={{
              vertical: 'top',
              horizontal: 'left',
          }}
        >
                     {/* Guest count section */}
           <MenuItem disabled sx={{ bgcolor: '#e3f2fd', fontWeight: 'bold' }}>
             <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', width: '100%' }}>
               <Typography variant="subtitle2">Number of Guests</Typography>
               <Typography variant="caption" color="text.secondary">
                 {(() => {
                   const totalTourGuests = parseInt(searchCriteria?.guests?.adults || 1);
                   const selectedBed = bedTypes.find(bed => bed.id === bedType);
                   const maxBedOccupancy = selectedBed?.max_occupancy || totalTourGuests;
                   return `Tour: ${totalTourGuests}${bedType ? ` | Bed: ${maxBedOccupancy}` : ''}`;
                 })()}
               </Typography>
             </Box>
           </MenuItem>
          
                     {(() => {
             // Get total guests from tour package
             const totalTourGuests = parseInt(searchCriteria?.guests?.adults || 1);
             
             // Get bed occupancy limit
             const selectedBed = bedTypes.find(bed => bed.id === bedType);
             const maxBedOccupancy = selectedBed?.max_occupancy || totalTourGuests;
             
             // Calculate maximum selectable guests (minimum of tour guests and bed capacity)
             const maxSelectableGuests = Math.min(totalTourGuests, maxBedOccupancy);
             
             return Array.from({ length: maxSelectableGuests }, (_, i) => i + 1).map((num) => (
               <MenuItem 
                 key={`guest-count-${num}`}
                 onClick={() => {
                   setSelectedGuests(num);
                   setGuestMealPlans(Array(num).fill('self'));
                   
                   // Update configuration immediately
                   if (hotelConfigurations[activeHotelIndex]) {
                     const updatedConfig = {
                       ...hotelConfigurations[activeHotelIndex],
                       selectedGuests: num,
                       guestMealPlans: Array(num).fill('self')
                     };
                     
                     const updatedConfigurations = [...hotelConfigurations];
                     updatedConfigurations[activeHotelIndex] = updatedConfig;
                     setHotelConfigurations(updatedConfigurations);
                   }
                   // Dropdown stays open due to controlled open state
                 }}
                 sx={{ 
                   pl: 4,
                   bgcolor: selectedGuests === num ? 'rgba(53, 84, 209, 0.08)' : 'transparent'
                 }}
               >
                 <Box sx={{ display: 'flex', alignItems: 'center', width: '100%', justifyContent: 'space-between' }}>
                   <Typography>
                     {num} {num === 1 ? 'Guest' : 'Guests'}
                     {num === totalTourGuests && (
                       <Chip label="From Tour" size="small" color="info" sx={{ ml: 1, height: 18, fontSize: '0.6rem' }} />
                     )}
                     {num === maxBedOccupancy && bedType && (
                       <Chip label="Max Bed" size="small" color="warning" sx={{ ml: 1, height: 18, fontSize: '0.6rem' }} />
                     )}
                   </Typography>
                   {selectedGuests === num && (
                     <Chip label="✓ Selected" size="small" color="primary" sx={{ height: 20 }} />
                   )}
                 </Box>
               </MenuItem>
             ));
           })()}

          {/* Divider */}
          {selectedGuests > 0 && (
            <MenuItem disabled sx={{ borderBottom: '2px solid #e0e0e0', py: 0, minHeight: 'auto' }}>
              <Box sx={{ width: '100%' }} />
            </MenuItem>
          )}

          {/* Meal plans for each guest */}
          {selectedGuests > 0 && Array.from({ length: selectedGuests }).map((_, guestIndex) => (
            <Box key={guestIndex}>
              {/* Guest header with person icon */}
              <MenuItem disabled sx={{ bgcolor: '#f5f5f5', fontWeight: 'bold' }}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <PersonIcon sx={{ mr: 1, fontSize: 18 }} />
                  <Typography variant="subtitle2">
                    Guest {guestIndex + 1}
                  </Typography>
                </Box>
              </MenuItem>
              
              {/* Meal options for this guest */}
              {mealPlans.map((plan) => (
                <MenuItem 
                  key={`${guestIndex}-${plan.id}`}
                  onClick={() => {
                    handleGuestMealPlanChange(guestIndex, plan.id);
                    // Dropdown stays open due to controlled open state
                  }}
                  sx={{ 
                    pl: 4,
                    bgcolor: guestMealPlans[guestIndex] === plan.id ? 'rgba(53, 84, 209, 0.08)' : 'transparent'
                  }}
                >
                  <Box sx={{ display: 'flex', alignItems: 'center', width: '100%', justifyContent: 'space-between' }}>
                    <Box sx={{ display: 'flex', alignItems: 'center' }}>
                      {plan.icon}
                      <Typography sx={{ ml: 1 }}>{plan.title}</Typography>
                      {guestMealPlans[guestIndex] === plan.id && (
                        <Chip label="✓ Selected" size="small" color="primary" sx={{ ml: 1, height: 20 }} />
                      )}
                    </Box>
                    <Typography variant="body2" color="text.secondary">
                      {plan.price > 0 ? `$${parseFloat(plan.price).toFixed(2)}` : 'Free'}
                    </Typography>
                  </Box>
                </MenuItem>
              ))}
              
              {/* Divider between guests */}
              {guestIndex < selectedGuests - 1 && (
                <MenuItem disabled sx={{ borderBottom: '1px solid #e0e0e0', py: 0, minHeight: 'auto' }}>
                  <Box sx={{ width: '100%' }} />
                </MenuItem>
              )}
            </Box>
          ))}
          </Menu>
      
      {/* Summary */}
      {guestMealPlans.length > 0 && guestMealPlans.some(plan => plan && plan !== 'self') && (
        <Box sx={{ mt: 2, p: 1, bgcolor: 'rgba(53, 84, 209, 0.05)', borderRadius: 1 }}>
          <Typography variant="body2" color="text.secondary" sx={{ mb: 0.5 }}>
            <strong>Total Cost:</strong> ${guestMealPlans.reduce((total, mealPlanId) => {
              const plan = mealPlans.find(p => p.id === mealPlanId);
              return total + (plan?.price || 0);
            }, 0).toFixed(2)}
          </Typography>
        </Box>
      )}
        </Box>
    </Grid>
  );

  // Add room data loading indicator
  const renderRoomDataLoadingIndicator = () => {
    if (roomDataStatus === 'loading') {
      return (
        <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', py: 2 }}>
          <CircularProgress size={20} sx={{ mr: 1 }} />
          <Typography variant="body2" color="text.secondary">
            Loading hotel details...
          </Typography>
        </Box>
      );
    }
    return null;
  };

  // Add a new room configuration for the same hotel
  const handleAddMoreRooms = () => {
    // Get current hotel configuration
    const currentConfig = hotelConfigurations[activeHotelIndex];
    
    // Check if there's already an empty configuration for this hotel
    const existingEmptyConfig = hotelConfigurations.findIndex(config => 
      config.hotelId === currentConfig.hotelId && 
      !config.roomTypeId && 
      !config.bedTypeId
    );
    
    if (existingEmptyConfig !== -1 && existingEmptyConfig !== activeHotelIndex) {
      // Switch to the existing empty configuration instead of creating a new one
      setActiveHotelIndex(existingEmptyConfig);
      
      // Show info message
      setAlert({
        show: true,
        message: 'Please complete the existing empty room configuration first',
        severity: 'info'
      });
      
      // Hide message after 3 seconds
      setTimeout(() => {
        setAlert({ ...alert, show: false });
      }, 3000);
      
      return;
    }
    
    // Create new configuration with same hotel but reset room selections
    const newConfig = {
      id: generateUniqueId(),
      hotelId: currentConfig.hotelId,
      hotelDetails: currentConfig.hotelDetails || {}, // Copy hotel details
      roomTypeId: '', // Reset room type for new configuration
      bedTypeId: '', // Reset bed type for new configuration
      mealPlanId: 'self', // Reset to default meal plan
      nights: currentConfig.nights,
      babyCot: false,
      occupancyType: 'single',
      adultDistribution: { male: 0, female: 0 },
      expanded: true,
      selectedGuests: currentConfig.selectedGuests || 1,
      guestMealPlans: Array(currentConfig.selectedGuests || 1).fill('self')
    };
    
    // Add to configurations array
    const updatedConfigurations = [...hotelConfigurations];
    updatedConfigurations.push(newConfig);
    
    setHotelConfigurations(updatedConfigurations);
    setActiveHotelIndex(updatedConfigurations.length - 1);
    
    // Reset selections for the new configuration
    setRoomType('');
    setBedType('');
    setMealPlan('self');
    setBabyCotSelected(false);
    setSelectedGuests(newConfig.selectedGuests);
    setGuestMealPlans(newConfig.guestMealPlans);
    
    // Show success message
    setAlert({
      show: true,
      message: 'Added new room configuration for this hotel',
      severity: 'success'
    });
    
    // Hide message after 3 seconds
    setTimeout(() => {
      setAlert({ ...alert, show: false });
    }, 3000);
  };

  // Calculate summary of hotel selections
  const hotelSummary = React.useMemo(() => {
    if (hotelConfigurations.length === 0) return [];
    
    // Group configurations by hotel
    const hotelGroups = {};
    
    hotelConfigurations.forEach(config => {
      if (!hotelGroups[config.hotelId]) {
        hotelGroups[config.hotelId] = {
          hotelId: config.hotelId,
          hotelDetails: config.hotelDetails || {},
          roomConfigs: []
        };
      }
      
      hotelGroups[config.hotelId].roomConfigs.push(config);
    });
    
    // Convert to array
    return Object.values(hotelGroups).map(group => {
      const hotel = group.hotelDetails || {};
      return {
        ...group,
        hotelName: hotel.hotel_name || hotel.name || 'Selected Hotel',
        totalRooms: group.roomConfigs.length,
        totalNights: group.roomConfigs.reduce((sum, config) => sum + (config.nights || 0), 0)
      };
    });
  }, [hotelConfigurations]);
  
  // Render hotel summary section
  const renderHotelSummary = () => {
    // Show summary even for single configurations to display detailed selection
    if (hotelConfigurations.length === 0) return null;
    
    return (
      <Card elevation={3} sx={{ mb: 3 }}>
        <CardContent>
          <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
            <HotelIcon sx={{ mr: 1 }} />
            Hotel Selection Summary
          </Typography>
          
          {hotelConfigurations.map((config, configIndex) => {
            // Get hotel details
            const hotel = config.hotelDetails || {};
            const hotelName = hotel.hotel_name || hotel.name || 'Selected Hotel';
            
            // Get room details
            const roomType = roomTypes.find(r => r.id === config.roomTypeId);
            const bedType = bedTypes.find(b => b.id === config.bedTypeId);
            
            // Get guest meal plans
            const guestMealPlans = config.guestMealPlans || [];
            const selectedGuests = config.selectedGuests || 0;
            
            return (
              <Box key={config.id} sx={{ mb: configIndex < hotelConfigurations.length - 1 ? 3 : 0 }}>
                <Paper 
                  variant="outlined" 
                  sx={{ 
                    p: 2, 
                    bgcolor: activeHotelIndex === configIndex ? 'rgba(53, 84, 209, 0.05)' : 'transparent',
                    border: activeHotelIndex === configIndex ? '2px solid #3554D1' : '1px solid #e0e0e0'
                  }}
                >
                  {/* Hotel Configuration Header */}
                  <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
                    <Typography variant="subtitle1" fontWeight={600} color="primary">
                      Configuration {configIndex + 1}
                      {activeHotelIndex === configIndex && (
                        <Chip label="Active" size="small" color="primary" sx={{ ml: 1 }} />
                      )}
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      {config.nights || 0} night{(config.nights || 0) !== 1 ? 's' : ''}
                    </Typography>
                  </Box>

                  <Grid container spacing={2}>
                    {/* Hotel Information */}
                    <Grid item xs={12} md={6}>
                      <Box sx={{ p: 1.5, bgcolor: 'rgba(25, 118, 210, 0.05)', borderRadius: 1 }}>
                        <Typography variant="subtitle2" color="primary" gutterBottom>
                          🏨 Hotel Details
                        </Typography>
                        <Typography variant="body2" fontWeight={500}>{hotelName}</Typography>
                        <Typography variant="caption" color="text.secondary" display="block">
                          {hotel.address || hotel.location || 'Hotel Location'}
                        </Typography>
                      </Box>
                    </Grid>

                    {/* Room & Bed Information */}
                    <Grid item xs={12} md={6}>
                      <Box sx={{ p: 1.5, bgcolor: 'rgba(76, 175, 80, 0.05)', borderRadius: 1 }}>
                        <Typography variant="subtitle2" color="success.main" gutterBottom>
                          🛏️ Room & Bed Details
                        </Typography>
                        <Typography variant="body2">
                          <strong>Room:</strong> {roomType?.name || 'Not selected'}
                        </Typography>
                        <Typography variant="body2">
                          <strong>Bed:</strong> {bedType?.name || 'Not selected'}
                          {bedType?.max_occupancy && (
                            <Chip 
                              label={`Max ${bedType.max_occupancy}`} 
                              size="small" 
                              sx={{ ml: 1, height: 18, fontSize: '0.6rem' }}
                            />
                          )}
                        </Typography>
                      </Box>
                    </Grid>

                    {/* Guest Meal Plans */}
                    <Grid item xs={12}>
                      <Box sx={{ p: 1.5, bgcolor: 'rgba(255, 152, 0, 0.05)', borderRadius: 1 }}>
                        <Typography variant="subtitle2" color="warning.main" gutterBottom>
                          🍽️ Guest Meal Plans ({selectedGuests} guest{selectedGuests !== 1 ? 's' : ''})
                        </Typography>
                        
                        {selectedGuests > 0 && guestMealPlans.length > 0 ? (
                          <Grid container spacing={1}>
                            {guestMealPlans.map((mealPlanId, guestIndex) => {
                              const mealPlan = mealPlans.find(m => m.id === mealPlanId);
                              return (
                                <Grid item xs={12} sm={6} md={4} key={guestIndex}>
                                  <Box sx={{ 
                                    display: 'flex', 
                                    alignItems: 'center', 
                                    p: 1, 
                                    bgcolor: 'white', 
                                    borderRadius: 1,
                                    border: '1px solid #e0e0e0'
                                  }}>
                                    <Avatar sx={{ width: 24, height: 24, fontSize: '0.7rem', bgcolor: '#3554D1', mr: 1 }}>
                                      {guestIndex + 1}
                                    </Avatar>
                                    <Box sx={{ flex: 1 }}>
                                      <Typography variant="body2" fontWeight={500}>
                                        Guest {guestIndex + 1}
                                      </Typography>
                                      <Typography variant="caption" color="text.secondary">
                                        {mealPlan?.title || 'Room Only'}
                                      </Typography>
                                      {mealPlan?.price > 0 && (
                                        <Typography variant="caption" color="success.main" display="block">
                                          ${parseFloat(mealPlan.price).toFixed(2)}
                                        </Typography>
                                      )}
                                    </Box>
                                  </Box>
                                </Grid>
                              );
                            })}
                          </Grid>
                        ) : (
                          <Typography variant="body2" color="text.secondary">
                            No guests or meal plans selected
                          </Typography>
                        )}

                        {/* Total Cost */}
                        {guestMealPlans.length > 0 && (
                          <Box sx={{ mt: 2, p: 1, bgcolor: 'rgba(76, 175, 80, 0.1)', borderRadius: 1 }}>
                            <Typography variant="body2" fontWeight={600} color="success.main">
                              Total Meal Cost: ${guestMealPlans.reduce((total, mealPlanId) => {
                                const plan = mealPlans.find(p => p.id === mealPlanId);
                                return total + (plan?.price || 0);
                              }, 0).toFixed(2)}
                            </Typography>
                          </Box>
                        )}
                      </Box>
                    </Grid>
                  </Grid>
                </Paper>
              </Box>
            );
          })}
        </CardContent>
      </Card>
    );
  };

  // Auto-sync to Redux when hotel configurations are complete
  useEffect(() => {
    // Only sync if we have complete configurations
    const completeConfigurations = hotelConfigurations.filter(config => 
      config.hotelId && config.roomTypeId && config.bedTypeId
    );
    
    if (completeConfigurations.length > 0 && roomTypes.length > 0 && bedTypes.length > 0) {
      syncToBookingArrayDebounced();
    }
  }, [hotelConfigurations.length, roomTypes.length, bedTypes.length]); // Only trigger on significant changes

  // Monitor hotel configurations and dispatch to tour packages Redux when complete (similar to vehicle dropdown)
  useEffect(() => {
    // Only proceed if we have room types and bed types data (prevents early triggers)
    if (roomTypes.length === 0 || bedTypes.length === 0 || dates.length === 0) {
      return;
    }

    // Check completion status for all configurations
    const completedConfigurations = hotelConfigurations.filter(config => 
      config.hotelId && 
      config.roomTypeId && 
      config.bedTypeId && 
      config.nights > 0 &&
      config.selectedGuests > 0
    );

    console.log("Hotel - Checking completion status for configurations:", hotelConfigurations);
    console.log("Hotel - Completed configurations count:", completedConfigurations.length);

    if (completedConfigurations.length > 0) {
      // Use setTimeout to debounce the dispatch and prevent rapid successive calls
      const timeoutId = setTimeout(() => {
        dispatchHotelsToRedux();
      }, 300);

      // Cleanup function to clear timeout if effect runs again
      return () => clearTimeout(timeoutId);
    }
  }, [
    hotelConfigurations, 
    roomTypes.length, // Use length instead of the full array to reduce sensitivity
    bedTypes.length,  // Use length instead of the full array to reduce sensitivity
    mealPlans.length, // Use length instead of the full array to reduce sensitivity
    dates.length,     // Use length instead of the full array to reduce sensitivity
    selectedCity, 
    selectedCountry
    // Removed existingServices to prevent infinite loop
  ]);

  // Render room configuration indicators
  const renderRoomConfigIndicators = () => {
    if (hotelConfigurations.length <= 1) return null;
    
    // Get current hotel ID
    const currentHotelId = hotelConfigurations[activeHotelIndex]?.hotelId;
    if (!currentHotelId) return null;
    
    // Get all configurations for this hotel
    const hotelConfigs = hotelConfigurations.filter(c => c.hotelId === currentHotelId);
    if (hotelConfigs.length <= 1) return null;
    
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', mb: 2 }}>
        <Paper 
          elevation={0}
          sx={{ 
            display: 'flex', 
            p: 0.5, 
            bgcolor: 'rgba(53, 84, 209, 0.1)', 
            borderRadius: 2
          }}
        >
          {hotelConfigs.map((config, index) => {
            const isActive = config.id === hotelConfigurations[activeHotelIndex].id;
            const configIndex = hotelConfigurations.findIndex(c => c.id === config.id);
            
            return (
              <Box 
                key={config.id}
                sx={{ 
                  width: 30, 
                  height: 30, 
                  borderRadius: '50%', 
                  bgcolor: isActive ? '#3554D1' : 'white',
                  color: isActive ? 'white' : '#3554D1',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  mx: 0.5,
                  cursor: 'pointer',
                  border: isActive ? 'none' : '1px solid #3554D1',
                  fontWeight: 'bold',
                  fontSize: '0.875rem'
                }}
                onClick={() => selectHotelConfiguration(configIndex)}
              >
                {index + 1}
              </Box>
            );
          })}
        </Paper>
      </Box>
    );
  };

  // Initialize guest meal plans when guests changes (removed mealPlan dependency to prevent loops)
  useEffect(() => {
    if (selectedGuests > 0 && guestMealPlans.length !== selectedGuests) {
      // Only update if the length actually changed
      const updatedGuestMealPlans = [...guestMealPlans];
      
      // Ensure we have the right number of entries
      while (updatedGuestMealPlans.length < selectedGuests) {
        updatedGuestMealPlans.push('self'); // Default to 'self' instead of mealPlan to prevent loops
      }
      
      // Trim if we have too many
      if (updatedGuestMealPlans.length > selectedGuests) {
        updatedGuestMealPlans.length = selectedGuests;
      }
      
      setGuestMealPlans(updatedGuestMealPlans);
    }
  }, [selectedGuests]); // Removed mealPlan from dependencies
  
  // Manual update function to save current selections to configuration
  const updateCurrentConfiguration = () => {
    if (hotelConfigurations.length === 0 || activeHotelIndex < 0 || !hotelConfigurations[activeHotelIndex]) return;
    
    // Use setTimeout to ensure state has been updated
    setTimeout(() => {
      const currentConfig = hotelConfigurations[activeHotelIndex];
      
     
      
      // Find room and bed details to store names
      const selectedRoomType = roomTypes.find(r => r.id === roomType);
      const selectedBedType = bedTypes.find(b => b.id === bedType);
      
      const updatedConfig = {
        ...currentConfig,
        hotelId: selectedHotel,
        hotelDetails: currentConfig.hotelDetails || {},
        roomTypeId: roomType,
        roomTypeName: selectedRoomType?.name || currentConfig.roomTypeName || 'Unknown Room',
        roomTypeName: selectedRoomType?.name || currentConfig.roomTypeName || 'Unknown Room',
        bedTypeId: bedType,
        bedTypeName: selectedBedType?.name || currentConfig.bedTypeName || 'Unknown Bed',
        max_occupancy: selectedBedType?.max_occupancy || currentConfig.max_occupancy || 1,
        bedPrice: selectedBedType?.price || currentConfig.bedPrice || 0,
        bedTypeName: selectedBedType?.name || currentConfig.bedTypeName || 'Unknown Bed',
        max_occupancy: selectedBedType?.max_occupancy || currentConfig.max_occupancy || 1,
        bedPrice: selectedBedType?.price || currentConfig.bedPrice || 0,
        mealPlanId: mealPlan,
        nights: selectedNights,
        selectedNightIndices: Array.from(selectedNightIndices),
        babyCot: babyCotSelected,
        occupancyType: occupancyType,
        adultDistribution: adultDistribution,
        selectedGuests: selectedGuests,
        guestMealPlans: [...guestMealPlans]
      };
      
      const updatedConfigurations = [...hotelConfigurations];
      updatedConfigurations[activeHotelIndex] = updatedConfig;
      setHotelConfigurations(updatedConfigurations);
      
      console.log('Configuration updated:', updatedConfig);
      
      // Auto-sync to Redux after updating configuration
      syncToBookingArrayDebounced();
      
      // The useEffect will handle dispatching to tour packages Redux automatically
      // No need for manual dispatch here to prevent double dispatching
    }, 100);
  };

  // Debounced sync function to prevent too many Redux updates
  const syncToBookingArrayDebounced = React.useCallback(
    (() => {
      let timeoutId;
      return () => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
          syncToBookingArray();
        }, 500); // Wait 500ms before syncing
      };
    })(),
    [hotelConfigurations, roomTypes, bedTypes, mealPlans]
  );

  // Handle hotel selection for the active configuration
  const handleHotelSelection = (hotelData) => {
    console.log(`Selecting hotel for configuration ${activeHotelIndex}:`, hotelData);
    
    // Update the active configuration with the new hotel details
    const updatedConfig = {
      ...hotelConfigurations[activeHotelIndex],
      hotelId: hotelData.id || hotelData.hotel_id || '',
      hotelDetails: {...hotelData}, // Store a copy of hotel details for this configuration
      // Reset room and bed selections when changing hotel
      roomTypeId: '',
      bedTypeId: '',
      mealPlanId: 'self',
      guestMealPlans: Array(hotelConfigurations[activeHotelIndex]?.selectedGuests || 1).fill('self')
    };
    
    const updatedConfigurations = [...hotelConfigurations];
    updatedConfigurations[activeHotelIndex] = updatedConfig;
    setHotelConfigurations(updatedConfigurations);
    
    // Update UI state
    setSelectedHotel(hotelData.id || hotelData.hotel_id || '');
    setRoomType('');
    setBedType('');
    setMealPlan('self');
    setGuestMealPlans(Array(hotelConfigurations[activeHotelIndex]?.selectedGuests || 1).fill('self'));
    
    console.log(`Hotel ${hotelData.hotel_name || 'Unknown'} assigned to configuration ${activeHotelIndex}`);
  };
  
  // Handle meal plan change for a specific guest
  const handleGuestMealPlanChange = (guestIndex, newMealPlan) => {
    // Check if the meal plan actually changed to prevent unnecessary updates
    if (guestMealPlans[guestIndex] === newMealPlan) return;
    
    const updatedGuestMealPlans = [...guestMealPlans];
    updatedGuestMealPlans[guestIndex] = newMealPlan;
    setGuestMealPlans(updatedGuestMealPlans);
    
    // Manually update the configuration to avoid useEffect loops
    if (hotelConfigurations[activeHotelIndex]) {
    const updatedConfig = {
      ...hotelConfigurations[activeHotelIndex],
      guestMealPlans: updatedGuestMealPlans
    };
    
    const updatedConfigurations = [...hotelConfigurations];
    updatedConfigurations[activeHotelIndex] = updatedConfig;
    setHotelConfigurations(updatedConfigurations);
    }
  };
  
  // DISABLED: This useEffect was causing re-renders when hotelConfigurations changed
  // We'll handle this in the selectHotelConfiguration function instead
  // useEffect(() => {
  //   if (hotelConfigurations[activeHotelIndex]?.guestMealPlans) {
  //     setGuestMealPlans(hotelConfigurations[activeHotelIndex].guestMealPlans);
  //   }
  // }, [activeHotelIndex, hotelConfigurations]);

  // DISABLED: This useEffect was causing hotel details to overwrite each other
  // Now using handleHotelSelection() to manage hotel details per configuration
  // useEffect(() => {
  //   // This was causing issues where selecting a new hotel would update all configurations
  // }, [currentHotelDetails, selectedHotel]);

  // Person Count Meal Plan Dropdown component
  const PersonCountMealPlanDropdown = () => {
    const [anchorEl, setAnchorEl] = useState(null);
    const open = Boolean(anchorEl);
    
    const handleClick = (event) => {
      setAnchorEl(event.currentTarget);
    };
    
    const handleClose = () => {
      setAnchorEl(null);
    };
    
    // Assign the same meal plan to all guests
    const assignMealPlanToAll = (mealPlanId) => {
      const updatedGuestMealPlans = Array(selectedGuests).fill(mealPlanId);
      setGuestMealPlans(updatedGuestMealPlans);
      
      // Update the active hotel configuration
      const updatedConfig = {
        ...hotelConfigurations[activeHotelIndex],
        guestMealPlans: updatedGuestMealPlans
      };
      
      const updatedConfigurations = [...hotelConfigurations];
      updatedConfigurations[activeHotelIndex] = updatedConfig;
      setHotelConfigurations(updatedConfigurations);
      
      handleClose();
    };
    
    // Group guests by meal plan
    const mealPlanGroups = {};
    
    // Add each guest to their meal plan group
    guestMealPlans.forEach((mealPlanId, guestIndex) => {
      if (!mealPlanGroups[mealPlanId]) {
        mealPlanGroups[mealPlanId] = [];
      }
      mealPlanGroups[mealPlanId].push(guestIndex + 1); // Guest numbers are 1-indexed for display
    });
    
    // Generate summary text for the button
    const generateSummaryText = () => {
      if (guestMealPlans.length === 0) return "No meal plans selected";
      
      // If all guests have the same meal plan
      if (Object.keys(mealPlanGroups).length === 1) {
        const mealPlanId = Object.keys(mealPlanGroups)[0];
        const mealPlan = mealPlans.find(plan => plan.id === mealPlanId);
        return `All guests: ${mealPlan?.title || 'Unknown meal plan'}`;
      }
      
      // Otherwise, show a count
      return `${selectedGuests} guests with ${Object.keys(mealPlanGroups).length} meal plans`;
    };
    
    return (
      <Box>
        <Button
          id="meal-plan-button"
          aria-controls={open ? 'meal-plan-menu' : undefined}
          aria-haspopup="true"
          aria-expanded={open ? 'true' : undefined}
          onClick={handleClick}
          variant="outlined"
          endIcon={<ExpandMoreIcon />}
          fullWidth
          sx={{ justifyContent: 'space-between', py: 1 }}
        >
          <Box sx={{ display: 'flex', alignItems: 'center', width: '100%' }}>
            <PersonIcon sx={{ mr: 1 }} />
            <Typography noWrap sx={{ textAlign: 'left', flex: 1 }}>
              {generateSummaryText()}
            </Typography>
          </Box>
        </Button>
        <Menu
          id="meal-plan-menu"
          anchorEl={anchorEl}
          open={open}
          onClose={handleClose}
          MenuListProps={{
            'aria-labelledby': 'meal-plan-button',
            sx: { maxHeight: 400, width: '100%', minWidth: 320 }
          }}
          anchorOrigin={{
            vertical: 'bottom',
            horizontal: 'center',
          }}
          transformOrigin={{
            vertical: 'top',
            horizontal: 'center',
          }}
        >
          <MenuItem sx={{ bgcolor: 'primary.light', color: 'white' }}>
            <Typography variant="subtitle2" sx={{ width: '100%', textAlign: 'center' }}>
              Meal Plan Distribution
            </Typography>
          </MenuItem>
          
          {/* Detailed breakdown list */}
          <Box sx={{ maxHeight: 250, overflow: 'auto', p: 2 }}>
            {Object.entries(mealPlanGroups).map(([mealPlanId, guestNumbers]) => {
              const mealPlan = mealPlans.find(plan => plan.id === mealPlanId);
              if (!mealPlan) return null;
              
              return (
                <Box key={mealPlanId} sx={{ mb: 2 }}>
                  <Typography variant="subtitle2" sx={{ display: 'flex', alignItems: 'center' }}>
                    {mealPlan.icon}
                    <span style={{ marginLeft: '8px' }}>{mealPlan.title}</span>
                  </Typography>
                  
                  <Typography variant="body2" color="text.secondary" sx={{ mt: 0.5, mb: 1, fontWeight: 'bold' }}>
                    {guestNumbers.length} × {mealPlan.title}
                  </Typography>
                  
                  <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5, ml: 2, mb: 1 }}>
                    {guestNumbers.map(guestNumber => (
                      <Chip
                        key={guestNumber}
                        label={`Guest ${guestNumber}`}
                        size="small"
                        variant="outlined"
                        sx={{ fontSize: '0.7rem' }}
                      />
                    ))}
                  </Box>
                </Box>
              );
            })}
          </Box>
          
          <Divider />
          
          <MenuItem sx={{ bgcolor: 'rgba(53, 84, 209, 0.05)' }}>
            <Typography variant="subtitle2" sx={{ width: '100%' }}>
              Quick Assign to All Guests:
            </Typography>
          </MenuItem>
          
          <Box sx={{ p: 1, display: 'flex', flexWrap: 'wrap', gap: 1, justifyContent: 'center' }}>
            {mealPlans.map((plan) => (
              <Chip
                key={plan.id}
                icon={plan.icon}
                label={plan.title}
                onClick={() => assignMealPlanToAll(plan.id)}
                color={guestMealPlans.every(mp => mp === plan.id) ? 'primary' : 'default'}
                sx={{ m: 0.5 }}
              />
            ))}
          </Box>
          
          <Divider />
          
          <MenuItem onClick={() => {
            handleClose();
            // Open the detailed meal plan selection
            const element = document.getElementById('guest-meal-plan-selection');
            if (element) {
              element.scrollIntoView({ behavior: 'smooth' });
            }
          }}>
            <Button 
              variant="contained" 
              color="primary" 
              fullWidth
              startIcon={<PersonIcon />}
            >
              Assign Individual Meal Plans
            </Button>
          </MenuItem>
        </Menu>
      </Box>
    );
  };

  // Guest Meal Plan Selection component
  const GuestMealPlanSelection = () => {
    if (selectedGuests <= 0) return null;
    
    return (
      <Card elevation={3} sx={{ mb: 3, mt: 3 }} id="guest-meal-plan-selection">
        <CardContent>
          <Typography variant="h6" gutterBottom>Guest Meal Plan Selection</Typography>
          <Typography variant="body2" color="text.secondary" gutterBottom>
            Assign meal plans for each guest in this room
          </Typography>
          
          <Divider sx={{ my: 2 }} />
          
          {/* Guest Count Selector */}
          <GuestCountSelector />
          
          <Divider sx={{ my: 2 }} />
          
          {/* Compact Meal Plan Distribution Dropdown */}
          <Box sx={{ mb: 3 }}>
            <Typography variant="subtitle1" fontWeight={500} gutterBottom>
              Quick Meal Plan Overview
            </Typography>
            <PersonCountMealPlanDropdown />
          </Box>
          
          <Divider sx={{ my: 2 }} />
          
          <Grid container spacing={2}>
            {Array.from({ length: selectedGuests }).map((_, guestIndex) => (
              <Grid item xs={12} sm={6} md={4} key={guestIndex}>
                <Box sx={{ 
                  p: 2, 
                  border: '1px solid #e0e0e0', 
                  borderRadius: 1,
                  bgcolor: 'background.paper'
                }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
                    <Avatar sx={{ bgcolor: '#3554D1', mr: 1 }}>
                      <PersonIcon />
                    </Avatar>
                    <Typography variant="subtitle1">
                      Guest {guestIndex + 1}
                    </Typography>
                  </Box>
                  
                  <FormControl fullWidth size="small">
                    <InputLabel id={`guest-${guestIndex}-meal-plan-label`}>Meal Plan</InputLabel>
                    <Select
                      labelId={`guest-${guestIndex}-meal-plan-label`}
                      id={`guest-${guestIndex}-meal-plan`}
                      value={guestMealPlans[guestIndex] || 'self'}
                      label="Meal Plan"
                      onChange={(e) => handleGuestMealPlanChange(guestIndex, e.target.value)}
                    >
                      {mealPlans.map((plan) => (
                        <MenuItem key={plan.id} value={plan.id}>
                          <Box sx={{ display: 'flex', alignItems: 'center' }}>
                            {plan.icon}
                            <Typography variant="body2" sx={{ ml: 1 }}>
                              {plan.title}
                            </Typography>
                          </Box>
                        </MenuItem>
                      ))}
                    </Select>
                  </FormControl>
                  
                  {/* Display selected meal plan details */}
                  {guestMealPlans[guestIndex] && (
                    <Box sx={{ mt: 1.5, p: 1, bgcolor: 'rgba(53, 84, 209, 0.05)', borderRadius: 1 }}>
                      <Typography variant="caption" display="block">
                        {mealPlans.find(plan => plan.id === guestMealPlans[guestIndex])?.description || ''}
                      </Typography>
                      {mealPlans.find(plan => plan.id === guestMealPlans[guestIndex])?.price > 0 && (
                        <Typography variant="caption" fontWeight="bold" display="block" sx={{ mt: 0.5 }}>
                          Price: ${parseFloat(mealPlans.find(plan => plan.id === guestMealPlans[guestIndex])?.price || 0).toFixed(2)}
                        </Typography>
                      )}
                    </Box>
                  )}
                </Box>
              </Grid>
            ))}
          </Grid>
          
          <Divider sx={{ my: 2 }} />
          
          {/* Meal Plan Summary */}
          <MealPlanSummary />
        </CardContent>
      </Card>
    );
  };

  // Guest count selector component
  const GuestCountSelector = () => {
    return (
      <Box sx={{ mb: 3 }}>
        <Typography variant="subtitle1" fontWeight={500} gutterBottom>
          Number of Guests in this Room
        </Typography>
        
        <Grid container spacing={2} alignItems="center">
          <Grid item xs={12} sm={6} md={4}>
            <FormControl fullWidth>
              <InputLabel id="guest-count-label">Guests</InputLabel>
              <Select
                labelId="guest-count-label"
                id="guest-count"
                value={selectedGuests}
                label="Guests"
                onChange={handleGuestChange}
              >
                {Array.from({ length: 10 }, (_, i) => i + 1).map((num) => (
                  <MenuItem key={num} value={num}>
                    {num} {num === 1 ? 'Guest' : 'Guests'}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Grid>
          
          <Grid item xs={12} sm={6} md={8}>
            <Box sx={{ p: 2, bgcolor: 'rgba(53, 84, 209, 0.05)', borderRadius: 1 }}>
              <Typography variant="body2">
                <strong>Total Package Guests:</strong> {totalPackageGuests}
              </Typography>
              <Typography variant="body2">
                <strong>Allocated Guests:</strong> {allocatedGuests}
              </Typography>
              <Typography variant="body2">
                <strong>Remaining Guests to Allocate:</strong> {totalPackageGuests - allocatedGuests}
              </Typography>
            </Box>
          </Grid>
        </Grid>
      </Box>
    );
  };
  
  // Meal plan summary component
  const MealPlanSummary = () => {
    if (selectedGuests <= 0 || guestMealPlans.length === 0) return null;
    
    // Group guests by meal plan
    const mealPlanGroups = {};
    
    // Add each guest to their meal plan group
    guestMealPlans.forEach((mealPlanId, guestIndex) => {
      if (!mealPlanGroups[mealPlanId]) {
        mealPlanGroups[mealPlanId] = [];
      }
      mealPlanGroups[mealPlanId].push(guestIndex + 1); // Guest numbers are 1-indexed for display
    });
    
    // Sort meal plans by number of guests (descending)
    const sortedMealPlans = Object.entries(mealPlanGroups)
      .sort((a, b) => b[1].length - a[1].length);
    
    return (
      <Box sx={{ mt: 2 }}>
        <Typography variant="subtitle2" gutterBottom>Meal Plan Summary</Typography>
        <Paper variant="outlined" sx={{ p: 2 }}>
          {sortedMealPlans.map(([mealPlanId, guestNumbers]) => {
            const mealPlan = mealPlans.find(plan => plan.id === mealPlanId);
            if (!mealPlan) return null;
            
            return (
              <Box key={mealPlanId} sx={{ mb: 2 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                  {mealPlan.icon}
                  <Typography variant="subtitle2" sx={{ ml: 1 }}>
                    {mealPlan.title}
                  </Typography>
                </Box>
                
                <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, ml: 3 }}>
                  {guestNumbers.map(guestNumber => (
                    <Chip
                      key={guestNumber}
                      label={`Guest ${guestNumber}`}
                      size="small"
                      color="primary"
                      variant="outlined"
                    />
                  ))}
                </Box>
                
                <Typography variant="body2" color="text.secondary" sx={{ mt: 1, ml: 3 }}>
                  {guestNumbers.length} × {mealPlan.title}
                </Typography>
              </Box>
            );
          })}
          
          {/* Total price calculation */}
          <Box sx={{ mt: 2, p: 1, bgcolor: 'rgba(53, 84, 209, 0.05)', borderRadius: 1 }}>
            <Typography variant="body2">
              <strong>Total Meal Plan Cost:</strong> $
              {guestMealPlans.reduce((total, mealPlanId) => {
                const mealPlan = mealPlans.find(plan => plan.id === mealPlanId);
                return total + parseFloat(mealPlan?.price || 0);
              }, 0).toFixed(2)}
            </Typography>
          </Box>
        </Paper>
      </Box>
    );
  };

  

  // Modify the hotel selection section
  return (
    <Box>
      {/* Alert for actions */}
      <Collapse in={alert.show}>
        <Alert 
          severity={alert.severity}
          sx={{ mb: 2 }}
          onClose={() => setAlert({...alert, show: false})}
        >
          {alert.message}
        </Alert>
      </Collapse>

      {/* Debug Panel - Configuration Details */}
      {/* {hotelConfigurations.length > 0 && (
        <Card elevation={2} sx={{ mb: 3, bgcolor: 'rgba(255, 193, 7, 0.05)' }}>
          <CardContent>
            <Typography variant="h6" gutterBottom sx={{ color: 'warning.main' }}>
              🔍 Debug: Hotel Configuration Details
            </Typography>
            <Grid container spacing={2}>
              {hotelConfigurations.map((config, index) => (
                <Grid item xs={12} md={6} key={config.id}>
                  <Paper sx={{ 
                    p: 2, 
                    bgcolor: activeHotelIndex === index ? 'rgba(25, 118, 210, 0.1)' : 'white',
                    border: activeHotelIndex === index ? '2px solid #1976d2' : '1px solid #e0e0e0'
                  }}>
                    <Typography variant="subtitle2" fontWeight={600} gutterBottom>
                      Configuration {index + 1} {activeHotelIndex === index && '(Active)'}
                    </Typography>
                    <Typography variant="body2">
                      <strong>Hotel ID:</strong> {config.hotelId || 'Not set'}
                    </Typography>
                    <Typography variant="body2">
                      <strong>Hotel Name:</strong> {config.hotelDetails?.name || 'Not set'}
                    </Typography>
                    <Typography variant="body2">
                      <strong>Room Type:</strong> {
                        config.roomTypeId 
                          ? (roomTypes.find(r => r.id === config.roomTypeId)?.name || `ID: ${config.roomTypeId}`)
                          : 'Not set'
                      }
                    </Typography>
                    <Typography variant="body2">
                      <strong>Bed Type:</strong> {
                        config.bedTypeId 
                          ? (bedTypes.find(b => b.id === config.bedTypeId)?.name || `ID: ${config.bedTypeId}`)
                          : 'Not set'
                      }
                    </Typography>
                    <Typography variant="body2">
                      <strong>Guests:</strong> {config.selectedGuests || 0}
                    </Typography>
                    <Typography variant="body2">
                      <strong>Nights:</strong> {config.nights || 0}
                    </Typography>
                    <Typography variant="body2">
                      <strong>Hotel Details Keys:</strong> {config.hotelDetails ? Object.keys(config.hotelDetails).length : 0} properties
                    </Typography>
                  </Paper>
                </Grid>
              ))}
            </Grid>
          </CardContent>
        </Card>
      )} */}
      {/* Hotel Configurations List - Only show configurations with hotels selected */}
      {hotelConfigurations.filter(config => config.hotelId).length > 0 && (
        <Box sx={{ mb: 3 }}>
          <Typography variant="h6" gutterBottom>
            {hotelConfigurations.filter(config => config.hotelId).length === 1 ? 'Hotel Selection Summary' : 'Your Hotel Selections'}
          </Typography>
          <Grid container spacing={2}>
            {hotelConfigurations.filter(config => config.hotelId).map((config, index) => {
              // Use hotel data from individual configuration
              const hotel = config.hotelDetails || {};
              const roomType = roomTypes.find(r => r.id === config.roomTypeId);
              const bedType = bedTypes.find(b => b.id === config.bedTypeId);
              const guestMealPlans = config.guestMealPlans || [];
              
              // Count how many configurations exist for this hotel
              const hotelConfigs = hotelConfigurations.filter(c => c.hotelId === config.hotelId);
              const configIndex = hotelConfigs.findIndex(c => c.id === config.id) + 1;
              const totalConfigsForHotel = hotelConfigs.length;
              
              return (
                <Grid item xs={12} key={config.id}>
                  <Card 
                    variant="outlined" 
                    sx={{ 
                      borderColor: activeHotelIndex === index ? '#3554D1' : 'divider',
                      bgcolor: activeHotelIndex === index ? 'rgba(53, 84, 209, 0.05)' : 'transparent'
                    }}
                  >
                    <CardContent sx={{ p: 2, '&:last-child': { pb: config.expanded ? 2 : 2 } }}>
                      {/* Header Section */}
                      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <Box sx={{ display: 'flex', alignItems: 'center', cursor: 'pointer', flex: 1 }} onClick={() => selectHotelConfiguration(index)}>
                          <Box sx={{ display: 'flex', alignItems: 'center', mr: 2 }}>
                            <HotelIcon sx={{ color: '#3554D1', mr: 1 }} />
                            <Typography variant="subtitle1" fontWeight={600}>
                            {config.hotelDetails?.hotel_name || config.hotelDetails?.name || 'Hotel'}
                            </Typography>
                            {activeHotelIndex === index && (
                              <Chip label="Active" size="small" color="primary" sx={{ ml: 1 }} />
                            )}
                          </Box>
                          
                          {/* Quick Summary Chips */}
                          <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5 }}>
                            {totalConfigsForHotel > 1 && (
                              <Chip 
                                size="small" 
                                label={`Room ${configIndex}/${totalConfigsForHotel}`} 
                                color="primary"
                                variant="outlined"
                              />
                            )}
                            {config.nights && (
                              <Chip 
                                size="small" 
                                label={`${config.nights} night${config.nights > 1 ? 's' : ''}`} 
                                color="secondary"
                                variant="outlined"
                              />
                            )}
                            {config.selectedGuests && (
                              <Chip 
                                size="small" 
                                label={`${config.selectedGuests} guest${config.selectedGuests > 1 ? 's' : ''}`} 
                                color="info"
                                variant="outlined"
                              />
                            )}
                            {roomType && (
                              <Chip 
                                size="small" 
                                label={roomType.name} 
                                sx={{ bgcolor: 'rgba(53, 84, 209, 0.1)' }}
                              />
                            )}
                            {bedType && (
                              <Chip 
                                size="small" 
                                label={bedType.name} 
                                sx={{ bgcolor: 'rgba(76, 175, 80, 0.1)' }}
                              />
                            )}
                          </Box>
                        </Box>
                        
                        <Box sx={{ display: 'flex', alignItems: 'center' }}>
                          {/* Meal Plan Cost Summary */}
                          {guestMealPlans.length > 0 && (
                            <Typography variant="body2" color="success.main" fontWeight={600} sx={{ mr: 2 }}>
                              ${guestMealPlans.reduce((total, mealPlanId) => {
                                const plan = mealPlans.find(p => p.id === mealPlanId);
                                return total + (plan?.price || 0);
                              }, 0).toFixed(2)}
                            </Typography>
                          )}
                          
                          <IconButton 
                            onClick={(e) => {
                              e.stopPropagation();
                              toggleHotelExpanded(index);
                            }} 
                            size="small"
                          >
                            {config.expanded ? <ExpandLessIcon /> : <ExpandMoreIcon />}
                          </IconButton>
                        </Box>
                      </Box>

                      {/* Expanded Details Section */}
                      <Collapse in={config.expanded}>
                        <Box sx={{ mt: 2 }}>
                          <Divider sx={{ mb: 2 }} />
                          
                          {/* Detailed Information Grid */}
                          <Grid container spacing={2}>
                            {/* Hotel Information */}
                            <Grid item xs={12} md={6}>
                              <Box sx={{ p: 2, bgcolor: 'rgba(25, 118, 210, 0.05)', borderRadius: 1 }}>
                                <Typography variant="subtitle2" color="primary" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
                                  <HotelIcon sx={{ mr: 1, fontSize: 18 }} />
                                  Hotel Details
                                </Typography>
                                <Typography variant="body2" fontWeight={500}>
                          {config.hotelDetails?.hotel_name || config.hotelDetails?.name || 'Hotel Name'}
                                </Typography>
                                <Typography variant="caption" color="text.secondary" display="block">
                          {config.hotelDetails?.address || config.hotelDetails?.location || 'Hotel Location'}
                                </Typography>
                                                        {config.hotelDetails?.hotel_star_rating && (
                                  <Box sx={{ display: 'flex', alignItems: 'center', mt: 0.5 }}>
                                    <StarIcon sx={{ fontSize: 14, color: '#FFD700' }} />
                                    <Typography variant="caption" sx={{ ml: 0.5 }}>
                              {config.hotelDetails.hotel_star_rating} Star
                                    </Typography>
                                  </Box>
                                )}
                              </Box>
                            </Grid>

                            {/* Room & Bed Information */}
                            <Grid item xs={12} md={6}>
                              <Box sx={{ p: 2, bgcolor: 'rgba(76, 175, 80, 0.05)', borderRadius: 1 }}>
                                <Typography variant="subtitle2" color="success.main" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
                                  <RestaurantMenuIcon sx={{ mr: 1, fontSize: 18 }} />
                                  Room & Bed Details
                                </Typography>
                                <Typography variant="body2">
                                  <strong>Room:</strong> {roomType?.name || 'Not selected'}
                                </Typography>
                                <Typography variant="body2">
                                  <strong>Bed:</strong> {bedType?.name || 'Not selected'}
                                  {bedType?.max_occupancy && (
                                    <Chip 
                                      label={`Max ${bedType.max_occupancy}`} 
                                      size="small" 
                                      sx={{ ml: 1, height: 16, fontSize: '0.6rem' }}
                                    />
                                  )}
                                </Typography>
                                <Typography variant="body2">
                                  <strong>Nights:</strong> {config.nights || 0}
                                </Typography>
                              </Box>
                            </Grid>

                            {/* Guest Meal Plans Information */}
                            <Grid item xs={12}>
                              <Box sx={{ p: 2, bgcolor: 'rgba(255, 152, 0, 0.05)', borderRadius: 1 }}>
                                <Typography variant="subtitle2" color="warning.main" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
                                  <PersonIcon sx={{ mr: 1, fontSize: 18 }} />
                                  Guest Meal Plans ({config.selectedGuests || 0} guest{(config.selectedGuests || 0) !== 1 ? 's' : ''})
                                </Typography>
                                
                                {guestMealPlans.length > 0 ? (
                                  <Grid container spacing={1}>
                                    {guestMealPlans.map((mealPlanId, guestIndex) => {
                                      const mealPlan = mealPlans.find(m => m.id === mealPlanId);
                                      return (
                                        <Grid item xs={12} sm={6} md={4} key={guestIndex}>
                                          <Box sx={{ 
                                            display: 'flex', 
                                            alignItems: 'center', 
                                            p: 1.5, 
                                            bgcolor: 'white', 
                                            borderRadius: 1,
                                            border: '1px solid #e0e0e0'
                                          }}>
                                            <Avatar sx={{ 
                                              width: 24, 
                                              height: 24, 
                                              fontSize: '0.7rem', 
                                              bgcolor: '#3554D1', 
                                              mr: 1 
                                            }}>
                                              {guestIndex + 1}
                                            </Avatar>
                                            <Box sx={{ flex: 1 }}>
                                              <Typography variant="body2" fontWeight={500}>
                                                Guest {guestIndex + 1}
                                              </Typography>
                                              <Typography variant="caption" color="text.secondary">
                                                {mealPlan?.title || 'Room Only'}
                                              </Typography>
                                              {mealPlan?.price > 0 && (
                                                <Typography variant="caption" color="success.main" display="block" fontWeight={500}>
                                                  ${parseFloat(mealPlan.price).toFixed(2)}
                                                </Typography>
                                              )}
                                            </Box>
                                          </Box>
                                        </Grid>
                                      );
                                    })}
                                    
                                    {/* Total Cost Display */}
                                    <Grid item xs={12}>
                                      <Box sx={{ 
                                        mt: 1, 
                                        p: 1.5, 
                                        bgcolor: 'rgba(76, 175, 80, 0.1)', 
                                        borderRadius: 1,
                                        display: 'flex',
                                        justifyContent: 'space-between',
                                        alignItems: 'center'
                                      }}>
                                        <Typography variant="body2" fontWeight={600}>
                                          Total Meal Plan Cost:
                                        </Typography>
                                        <Typography variant="body2" fontWeight={600} color="success.main">
                                          ${guestMealPlans.reduce((total, mealPlanId) => {
                                            const plan = mealPlans.find(p => p.id === mealPlanId);
                                            return total + (plan?.price || 0);
                                          }, 0).toFixed(2)}
                                        </Typography>
                                      </Box>
                                    </Grid>
                                  </Grid>
                                ) : (
                                  <Typography variant="body2" color="text.secondary">
                                    No guests or meal plans selected
                                  </Typography>
                                )}
                              </Box>
                            </Grid>
                          </Grid>
                        </Box>
                      </Collapse>
                    </CardContent>
                  </Card>
                </Grid>
              );
            })}
          </Grid>
        </Box>
      )}

      {/* Workflow Information Panel */}
      {/* {hotelConfigurations.filter(config => config.hotelId).length === 0 && (
        <Card elevation={2} sx={{ mb: 3, bgcolor: 'rgba(33, 150, 243, 0.05)' }}>
          <CardContent>
            <Typography variant="h6" gutterBottom sx={{ color: 'primary.main', display: 'flex', alignItems: 'center' }}>
              <HotelIcon sx={{ mr: 1 }} />
              Hotel Booking Workflow
            </Typography>
            <Grid container spacing={2}>
              <Grid item xs={12} md={4}>
                <Box sx={{ p: 2, bgcolor: 'white', borderRadius: 1, border: '1px solid #e3f2fd' }}>
                  <Typography variant="subtitle2" color="primary" gutterBottom>
                    1️⃣ Select Hotel
                  </Typography>
                  <Typography variant="body2" color="text.secondary">
                    Choose a hotel from the list below. This will show detailed configuration options.
                  </Typography>
                </Box>
              </Grid>
              <Grid item xs={12} md={4}>
                <Box sx={{ p: 2, bgcolor: 'white', borderRadius: 1, border: '1px solid #e3f2fd' }}>
                  <Typography variant="subtitle2" color="primary" gutterBottom>
                    2️⃣ Configure Room & Guests
                  </Typography>
                  <Typography variant="body2" color="text.secondary">
                    Select room type, bed type, guest count, meal plans, and nights.
                  </Typography>
                </Box>
              </Grid>
              <Grid item xs={12} md={4}>
                <Box sx={{ p: 2, bgcolor: 'white', borderRadius: 1, border: '1px solid #e3f2fd' }}>
                  <Typography variant="subtitle2" color="primary" gutterBottom>
                    3️⃣ Add More Options
                  </Typography>
                  <Typography variant="body2" color="text.secondary">
                    <strong>Add Room:</strong> Same hotel, different room configuration<br/>
                    <strong>Add Hotel:</strong> Completely new hotel booking
                  </Typography>
                </Box>
              </Grid>
            </Grid>
          </CardContent>
        </Card>
      )} */}

      {/* Hotel Selection & Room Configuration */}
      {/* {hotelConfigurations[activeHotelIndex]?.expanded && ( */}
        <>
          <Grid container spacing={2} sx={{ mb: 3 }}>
            <Grid item xs={12} md={2.5} mt={1.5}>
              <HotelListing 
                onSelect={(hotel) => {
                  // Use our new handler that properly manages hotel details per configuration
                  handleHotelSelection(hotel);
                }}
                searchParams={searchCriteria}
                selectedHotelId={selectedHotel} // Pass selected hotel ID to control the component
              />
              {renderRoomDataLoadingIndicator()}
            </Grid>
            
            {/* Dynamic Room Type Section */}
            {renderRoomTypeSection()}
            
            {/* Dynamic Bed Type Section */}
            {renderBedTypeSection()}

            {/* Dynamic Meal Plan Section */}
            {renderMealPlanSection()}
            
            {/* Action Buttons Section */}
            <Grid item xs={12} md={2.5}>
              <Typography variant="subtitle1" fontWeight={500} sx={{ visibility: 'hidden' }}>Hidden Label</Typography>
              <Box sx={{ 
                width: '100%', 
                display: 'flex',
                flexDirection: 'column',
                gap: 1,
                mt: 2
              }}>
                {/* Add Room Button - Only show if hotel is selected */}
                {hotelConfigurations[activeHotelIndex]?.hotelId && (
                  <Button
                    variant="outlined"
                    size="small"
                    startIcon={<AddIcon />}
                    onClick={handleAddMoreRooms}
                    sx={{ 
                      borderColor: '#3554D1',
                      color: '#3554D1',
                      '&:hover': {
                        borderColor: '#2a43a8',
                        bgcolor: 'rgba(53, 84, 209, 0.05)'
                      }
                    }}
                  >
                    Add Room
                  </Button>
                )}
                
                {/* Add Hotel Button */}
                <Button
                  variant="contained"
                  size="small"
                  startIcon={<HotelIcon />}
                  onClick={handleAddNewHotel}
                  sx={{ 
                    bgcolor: '#4caf50',
                    '&:hover': {
                      bgcolor: '#45a049'
                    }
                  }}
                >
                  Add Hotel
                </Button>
                
                {/* Remove Configuration Button - Only show if multiple configurations */}
                {hotelConfigurations.length > 1 && (
                  <Button
                    variant="outlined"
                    size="small"
                    color="error"
                    startIcon={<RemoveIcon />}
                    onClick={handleRemoveHotel}
                    sx={{ 
                      borderColor: '#f44336',
                      '&:hover': {
                        borderColor: '#d32f2f',
                        bgcolor: 'rgba(244, 67, 54, 0.05)'
                      }
                    }}
                  >
                    Remove
                  </Button>
                )}
              </Box>
            </Grid>
          </Grid>
          
         
          
          {/* Hotel Details Card - Only show when hotel is selected */}
          {hotelConfigurations[activeHotelIndex]?.hotelId && (
            <Card elevation={3} sx={{ mb: 3 }}>
              <Grid container>
                
                <Grid item xs={12} md={8}>
                  <CardContent>
                    <Typography variant="h5" gutterBottom>
                      {hotelConfigurations[activeHotelIndex]?.hotelDetails?.hotel_name || 
                       hotelConfigurations[activeHotelIndex]?.hotelDetails?.name ||
                       apiHotelDetails?.hotel_name || 
                       'Select a Hotel'}
                    </Typography>
                    <Typography variant="body2" color="text.secondary" gutterBottom>
                      {hotelConfigurations[activeHotelIndex]?.hotelDetails?.address || 
                       hotelConfigurations[activeHotelIndex]?.hotelDetails?.location || 
                       apiHotelDetails?.address || 
                       apiHotelDetails?.location || 
                       'Hotel Location'}
                    </Typography>
                    
                    {/* Hotel Star Rating */}
                    {(hotelConfigurations[activeHotelIndex]?.hotelDetails?.hotel_star_rating || 
                      hotelConfigurations[activeHotelIndex]?.hotelDetails?.category) && (
                      <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                        <StarIcon sx={{ color: '#FFD700', mr: 0.5 }} />
                        <Typography variant="body2" color="text.secondary">
                          {hotelConfigurations[activeHotelIndex]?.hotelDetails?.hotel_star_rating || 
                           hotelConfigurations[activeHotelIndex]?.hotelDetails?.category} Star Hotel
                        </Typography>
                      </Box>
                    )}
                    
                    <Divider sx={{ my: 2 }} />
                    
                    {/* Night Selection Section */}
                    <Box sx={{ mt: 2 }}>
                      <Typography variant="h6" gutterBottom sx={{ color: 'primary.main' }}>
                        Select Nights
                      </Typography>
                      
                      {/* Select All / Deselect All Buttons */}
                      {/* <Box sx={{ display: 'flex', gap: 1, mb: 2 }}>
                        <Button 
                          size="small" 
                          variant="outlined" 
                          color="primary"
                          onClick={() => {
                            const allIndices = new Set();
                            for (let i = 0; i < dates.length - 1; i++) {
                              allIndices.add(i);
                            }
                            setSelectedNightIndices(allIndices);
                            setSelectedNights(allIndices.size);
                            
                            // Update the active hotel configuration
                            const updatedConfig = {
                              ...hotelConfigurations[activeHotelIndex],
                              nights: allIndices.size,
                              selectedNightIndices: Array.from(allIndices)
                            };
                            
                            const updatedConfigurations = [...hotelConfigurations];
                            updatedConfigurations[activeHotelIndex] = updatedConfig;
                            setHotelConfigurations(updatedConfigurations);
                          }}
                        >
                          Select All
                        </Button>
                        <Button 
                          size="small" 
                          variant="outlined" 
                          color="secondary"
                          onClick={() => {
                            setSelectedNightIndices(new Set());
                            setSelectedNights(0);
                            
                            // Update the active hotel configuration
                            const updatedConfig = {
                              ...hotelConfigurations[activeHotelIndex],
                              nights: 0,
                              selectedNightIndices: []
                            };
                            
                            const updatedConfigurations = [...hotelConfigurations];
                            updatedConfigurations[activeHotelIndex] = updatedConfig;
                            setHotelConfigurations(updatedConfigurations);
                          }}
                        >
                          Deselect All
                        </Button>
                      </Box> */}
                      
                      {/* Checkbox Night Selection */}
                      <Box sx={{ 
                        p: 2,
                        bgcolor: 'rgba(53, 84, 209, 0.02)',
                        borderRadius: 2,
                        border: '1px solid rgba(53, 84, 209, 0.1)'
                      }}>
                        <Grid container spacing={1}>
                          {dates.slice(0, dates.length - 1).map((date, nightIndex) => {
                            const isSelected = selectedNightIndices.has(nightIndex);
                            const nextDate = dates[nightIndex + 1];
                            
                            return (
                              <Grid item xs={12} sm={6} md={4} key={nightIndex}>
                                <FormControlLabel
                                  control={
                                    <Checkbox
                                      checked={isSelected}
                                      onChange={(event) => {
                                        const newSelectedIndices = new Set(selectedNightIndices);
                                        
                                        if (event.target.checked) {
                                          newSelectedIndices.add(nightIndex);
                                        } else {
                                          newSelectedIndices.delete(nightIndex);
                                        }
                                        
                                        setSelectedNightIndices(newSelectedIndices);
                                        setSelectedNights(newSelectedIndices.size);
                                        
                                        // Update the active hotel configuration
                                        const updatedConfig = {
                                          ...hotelConfigurations[activeHotelIndex],
                                          nights: newSelectedIndices.size,
                                          selectedNightIndices: Array.from(newSelectedIndices)
                                        };
                                        
                                        const updatedConfigurations = [...hotelConfigurations];
                                        updatedConfigurations[activeHotelIndex] = updatedConfig;
                                        setHotelConfigurations(updatedConfigurations);
                                      }}
                                      sx={{
                                        color: '#3554D1',
                                        '&.Mui-checked': {
                                          color: '#4caf50',
                                        },
                                      }}
                                    />
                                  }
                                  label={
                                    <Box sx={{ ml: 1 }}>
                                      <Typography variant="body2" fontWeight={600} sx={{ 
                                        color: isSelected ? '#2e7d32' : '#666'
                                      }}>
                                        Night {nightIndex + 1}
                                      </Typography>
                                      <Typography variant="caption" color="text.secondary" display="block">
                                        {date.format('MMM DD')} to {nextDate.format('MMM DD')}
                                      </Typography>
                                      <Typography variant="caption" color="text.secondary" display="block">
                                        {date.format('dddd')} to {nextDate.format('dddd')}
                                      </Typography>
                                    </Box>
                                  }
                                  sx={{
                                    width: '100%',
                                    margin: 0,
                                    padding: 1.5,
                                    borderRadius: 2,
                                    border: '1px solid',
                                    borderColor: isSelected ? '#4caf50' : '#e0e0e0',
                                    bgcolor: isSelected ? 'rgba(76, 175, 80, 0.05)' : '#ffffff',
                                    transition: 'all 0.2s ease',
                                    '&:hover': {
                                      borderColor: isSelected ? '#2e7d32' : '#3554D1',
                                      bgcolor: isSelected ? 'rgba(76, 175, 80, 0.1)' : 'rgba(53, 84, 209, 0.05)',
                                    }
                                  }}
                                />
                              </Grid>
                            );
                          })}
                        </Grid>
                      </Box>
                      
                      {/* Summary */}
                      <Box sx={{ 
                        mt: 2, 
                        p: 1.5, 
                        bgcolor: 'rgba(76, 175, 80, 0.05)', 
                        borderRadius: 1,
                        border: '1px solid rgba(76, 175, 80, 0.2)'
                      }}>
                        <Typography variant="body2" color="success.main" fontWeight={600}>
                          Selected: {selectedNightIndices.size} night{selectedNightIndices.size !== 1 ? 's' : ''} 
                          {selectedNightIndices.size > 0 && (
                            <>
                              <br />
                              <Typography variant="caption" color="text.secondary" component="span">
                                Nights: {Array.from(selectedNightIndices).sort((a, b) => a - b).map(index => index + 1).join(', ')}
                              </Typography>
                              {(() => {
                                // Calculate date range from selected checkboxes
                                const sortedIndices = Array.from(selectedNightIndices).sort((a, b) => a - b);
                                if (sortedIndices.length > 0) {
                                  const firstIndex = sortedIndices[0];
                                  const lastIndex = sortedIndices[sortedIndices.length - 1];
                                  const startDate = dates[firstIndex];
                                  const endDate = dates[lastIndex + 1]; // +1 because night index refers to the start date
                                  
                                  if (startDate && endDate) {
                                    return (
                                      <>
                                        <br />
                                        <Typography variant="caption" color="success.main" component="span" fontWeight={600}>
                                          Range: {startDate.format('MMM DD')} to {endDate.format('MMM DD, YYYY')}
                                        </Typography>
                                      </>
                                    );
                                  }
                                }
                                return null;
                              })()}
                            </>
                          )}
                        </Typography>
                      </Box>
                    </Box>
                  </CardContent>
                </Grid>
              </Grid>
            </Card>
          )}
        </>
      {/* )} */}
      
     

    
      </Box>
    );
} 
