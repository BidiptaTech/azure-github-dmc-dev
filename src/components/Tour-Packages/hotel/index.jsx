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
  
  // Get hotel room data from Redux store
  const { roomDatas, status: roomDataStatus } = useSelector(state => state.rooms);
  console.log("roomDatas", roomDatas);
  
  // Get hotel details from Redux (used for newly selected hotels)
  const currentHotelDetails = useSelector(state => state.hoteldetails?.bookingDetails || {});
  
  // Get booking array from Redux slice (following the same pattern as your HotelDetailsSlice)
  const bookingArray = useSelector(state => state.hoteldetails?.bookingArray || []);
  const totalPrice = useSelector(state => state.hoteldetails?.totalPrice || 0);
  
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
    } else {
      setSelectedNights(1); // Default to 1 if no date range
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
    
    const initialConfig = {
      id: generateUniqueId(),
      hotelId: '',
      hotelDetails: {}, // Store hotel details within each configuration
      roomTypeId: '',
      bedTypeId: '',
      mealPlanId: 'self',
      nights: maxNights > 0 ? maxNights : 1,
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
  const handleAddSimilarHotel = () => {
    // Get current configuration
    const currentConfig = hotelConfigurations[activeHotelIndex];
    
    // Create new configuration with empty hotel (user will need to select)
    const newConfig = {
      ...currentConfig,
      id: generateUniqueId(),
      hotelId: '', // Reset hotel ID - user will select from HotelListing
      hotelDetails: {}, // Reset hotel details since it's a new hotel
      roomTypeId: '', // Reset room type since it's a new hotel
      bedTypeId: '', // Reset bed type since it's a new hotel
      expanded: true
    };
    
    // Add to configurations array
    const updatedConfigurations = [...hotelConfigurations];
    updatedConfigurations.push(newConfig);
    
    setHotelConfigurations(updatedConfigurations);
    setActiveHotelIndex(updatedConfigurations.length - 1);
    
    // Reset selections for the new configuration
    setSelectedHotel('');
    setRoomType('');
    setBedType('');
    
    // Show success message
    setAlert({
      show: true,
      message: 'New hotel configuration added - please select a hotel',
      severity: 'info'
    });
    
    // Hide message after 3 seconds
    setTimeout(() => {
      setAlert({ ...alert, show: false });
    }, 3000);
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
                const updatedConfig = {
                  ...currentConfig,
                  roomTypeId: selected, // Use the selected value directly
                  bedTypeId: '', // Reset bed type
                };
                
                const updatedConfigurations = [...hotelConfigurations];
                updatedConfigurations[activeHotelIndex] = updatedConfig;
                setHotelConfigurations(updatedConfigurations);
                
                console.log('Room type saved:', selected);
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
                          bedTypeId: '',
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
    <Grid item xs={12} md={2.5}>
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
                const updatedConfig = {
                  ...currentConfig,
                  bedTypeId: selected, // Use the selected value directly
                  roomTypeId: roomType, // Keep the current room type
                };
                
                const updatedConfigurations = [...hotelConfigurations];
                updatedConfigurations[activeHotelIndex] = updatedConfig;
                setHotelConfigurations(updatedConfigurations);
                
                console.log('Bed type saved:', selected);
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
      const updatedConfig = {
        ...currentConfig,
        hotelId: selectedHotel,
        hotelDetails: currentConfig.hotelDetails || {},
        roomTypeId: roomType,
        bedTypeId: bedType,
        mealPlanId: mealPlan,
        nights: selectedNights,
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
      {/* Hotel Configurations List */}
      {hotelConfigurations.length > 0 && (
        <Box sx={{ mb: 3 }}>
          <Typography variant="h6" gutterBottom>
            {hotelConfigurations.length === 1 ? 'Hotel Selection Summary' : 'Your Hotel Selections'}
          </Typography>
          <Grid container spacing={2}>
            {hotelConfigurations.map((config, index) => {
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
              />
              {renderRoomDataLoadingIndicator()}
            </Grid>
            
            {/* Dynamic Room Type Section */}
            {renderRoomTypeSection()}
            
            {/* Dynamic Bed Type Section */}
            {renderBedTypeSection()}

            {/* Dynamic Meal Plan Section */}
            {renderMealPlanSection()}
            
            {/* Add/Remove Room Button */}
            <Grid item xs={12} md={2}>
              <Typography variant="subtitle1" fontWeight={500} sx={{ visibility: 'hidden' }}>Hidden Label</Typography>
              <Box sx={{ 
                width: '100%', 
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                mt: 2,
                cursor: 'pointer'
              }}
              onClick={() => {
                // Check if this is an empty configuration that should be removed
                const currentConfig = hotelConfigurations[activeHotelIndex];
                const isEmptyConfig = !currentConfig.roomTypeId && !currentConfig.bedTypeId;
                const isSameHotelConfig = hotelConfigurations.filter(c => c.hotelId === currentConfig.hotelId).length > 1;
                
                if (isEmptyConfig && isSameHotelConfig) {
                  // Remove this empty configuration
                  handleRemoveHotel();
                } else {
                  // Add a new room configuration
                  handleAddMoreRooms();
                }
              }}
              >
                {(() => {
                  // Check if this is an empty configuration that should be removed
                  const currentConfig = hotelConfigurations[activeHotelIndex];
                  const isEmptyConfig = !currentConfig?.roomTypeId && !currentConfig?.bedTypeId;
                  const isSameHotelConfig = hotelConfigurations.filter(c => c.hotelId === currentConfig?.hotelId).length > 1;
                  
                  if (isEmptyConfig && isSameHotelConfig) {
                    // Show remove button
                    return (
                      <>
                        <Avatar sx={{ bgcolor: '#f44336', width: 30, height: 30, mb: 1 }}>
                          <RemoveIcon fontSize="small" />
                        </Avatar>
                        <Typography variant="body1" fontWeight={500} color="error" align="center">
                          Remove Room
                        </Typography>
                      </>
                    );
                  } else {
                    // Show add button
                    return (
                      <>
                        <Avatar sx={{ bgcolor: '#3554D1', width: 30, height: 30, mb: 1 }}>
                          <AddIcon fontSize="small" />
                        </Avatar>
                        <Typography variant="body1" fontWeight={500} color="primary" align="center">
                          Add Room
                        </Typography>
                      </>
                    );
                  }
                })()}
              </Box>
            </Grid>
          </Grid>
          
         
          
          {/* Hotel Details Card */}
          <Card elevation={3} sx={{ mb: 3 }}>
            <Grid container>
              <Grid item xs={12} md={4}>
                <CardMedia
                  component="img"
                  height={200}
                  image={
                    (hotelConfigurations[activeHotelIndex]?.hotelDetails?.image || 
                     hotelConfigurations[activeHotelIndex]?.hotelDetails?.main_image || 
                     apiHotelDetails?.image || 
                     apiHotelDetails?.main_image) || 
                    'https://via.placeholder.com/400x200?text=Select+Hotel'
                  }
                  alt={
                    hotelConfigurations[activeHotelIndex]?.hotelDetails?.hotel_name || 
                    apiHotelDetails?.hotel_name || 
                    'Hotel Image'
                  }
                />
              </Grid>
              <Grid item xs={12} md={8}>
                <CardContent>
                  <Typography variant="h5" gutterBottom>
                    {hotelConfigurations[activeHotelIndex]?.hotelDetails?.hotel_name || 
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
                  
                  <Divider sx={{ my: 2 }} />
                  
                  {/* Room, Bed & Meal Selection Summary */}
                  <Grid container spacing={2} sx={{ mb: 2 }}>
                    <Grid item xs={12} sm={4}>
                      <Box sx={{ 
                        bgcolor: 'rgba(53, 84, 209, 0.05)', 
                        p: 1.5, 
                        borderRadius: 1,
                        height: '100%'
                      }}>
                        <Typography variant="subtitle2" fontWeight={500} color="primary">
                          Room Type
                        </Typography>
                        <Typography variant="body2" sx={{ mt: 1 }}>
                          {roomType ? roomTypes.find(room => room.id === roomType)?.name || 'Not selected' : 'Not selected'}
                        </Typography>
                        
                        {/* Display room pricing if available */}
                        {roomType && roomDatas?.room_data && Array.isArray(roomDatas.room_data) && (
                          <Box mt={1}>
                            <Typography variant="caption" color="text.secondary" display="block">
                              {(() => {
                                const selectedRoom = roomDatas.room_data.find(room => room.room_id.toString() === roomType);
                                if (selectedRoom) {
                                  return `Single: $${parseFloat(selectedRoom.single_price || '0').toFixed(2)} • Double: $${parseFloat(selectedRoom.double_price || '0').toFixed(2)}`;
                                }
                                return '';
                              })()}
                            </Typography>
                          </Box>
                        )}
                      </Box>
                    </Grid>
                    
                    <Grid item xs={12} sm={4}>
                      <Box sx={{ 
                        bgcolor: 'rgba(53, 84, 209, 0.05)', 
                        p: 1.5, 
                        borderRadius: 1,
                        height: '100%'
                      }}>
                        <Typography variant="subtitle2" fontWeight={500} color="primary">
                          Bed Type
                        </Typography>
                        <Typography variant="body2" sx={{ mt: 1 }}>
                          {bedType ? bedTypes.find(bed => bed.id === bedType)?.name || 'Not selected' : 'Not selected'}
                        </Typography>
                        
                        {/* Display bed occupancy if available */}
                        {bedType && (
                          <Box mt={1}>
                            <Typography variant="caption" color="text.secondary" display="block">
                              {(() => {
                                const selectedBed = bedTypes.find(bed => bed.id === bedType);
                                if (selectedBed) {
                                  let occupancyText = `Max Occupancy: ${selectedBed.max_occupancy || '1'}`;
                                  if (selectedBed.adult_count) occupancyText += ` (${selectedBed.adult_count} adults`;
                                  if (selectedBed.child_count) occupancyText += `, ${selectedBed.child_count} children`;
                                  if (selectedBed.adult_count) occupancyText += ')';
                                  
                                  return occupancyText;
                                }
                                return '';
                              })()}
                            </Typography>
                            {(() => {
                              const selectedBed = bedTypes.find(bed => bed.id === bedType);
                              if (selectedBed && (selectedBed.extra_bed || selectedBed.baby_cot)) {
                                return (
                                  <Typography variant="caption" color="text.secondary" display="block">
                                    {selectedBed.extra_bed ? `Extra Bed: $${parseFloat(selectedBed.extra_bed_price || '0').toFixed(2)}` : ''}
                                    {selectedBed.extra_bed && selectedBed.baby_cot ? ' • ' : ''}
                                    {selectedBed.baby_cot ? `Baby Cot: $${parseFloat(selectedBed.baby_cot_price || '0').toFixed(2)}` : ''}
                                  </Typography>
                                );
                              }
                              return null;
                            })()}
                          </Box>
                        )}
                      </Box>
                    </Grid>

                    {/* Guest Meal Plans Section */}
                    <Grid item xs={12} sm={4}>
                      <Box sx={{ 
                        bgcolor: 'rgba(255, 152, 0, 0.05)', 
                        p: 1.5, 
                        borderRadius: 1,
                        height: '100%'
                      }}>
                        <Typography variant="subtitle2" fontWeight={500} color="warning.main">
                          Guest Meal Plans
                        </Typography>
                        
                        {selectedGuests > 0 && guestMealPlans.length > 0 ? (
                          <Box sx={{ mt: 1 }}>
                            {guestMealPlans.map((mealPlanId, guestIndex) => {
                              const mealPlan = mealPlans.find(m => m.id === mealPlanId);
                              return (
                                <Box key={guestIndex} sx={{ 
                                  display: 'flex', 
                                  alignItems: 'center',
                                  mb: 0.5,
                                  fontSize: '0.875rem'
                                }}>
                                  <Avatar sx={{ 
                                    width: 16, 
                                    height: 16, 
                                    fontSize: '0.6rem', 
                                    bgcolor: '#3554D1', 
                                    mr: 0.5 
                                  }}>
                                    {guestIndex + 1}
                                  </Avatar>
                                  <Typography variant="caption" sx={{ flex: 1 }}>
                                    {mealPlan?.title || 'Room Only'}
                                  </Typography>
                                  {mealPlan?.price > 0 && (
                                    <Typography variant="caption" color="success.main" fontWeight={500}>
                                      ${parseFloat(mealPlan.price).toFixed(2)}
                                    </Typography>
                                  )}
                                </Box>
                              );
                            })}
                            
                            {/* Total Cost */}
                            <Divider sx={{ my: 1 }} />
                            <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                              <Typography variant="caption" fontWeight={600}>
                                Total:
                              </Typography>
                              <Typography variant="caption" fontWeight={600} color="success.main">
                                ${guestMealPlans.reduce((total, mealPlanId) => {
                                  const plan = mealPlans.find(p => p.id === mealPlanId);
                                  return total + (plan?.price || 0);
                                }, 0).toFixed(2)}
                              </Typography>
                            </Box>
                          </Box>
                        ) : (
                          <Typography variant="body2" color="text.secondary" sx={{ mt: 1 }}>
                            No meal plans selected
                          </Typography>
                        )}
                      </Box>
                    </Grid>
                  </Grid>
                  
                  {/* Enhanced Nights Selection */}
                  <Box sx={{ mt: 2 }}>
                    <Typography variant="subtitle2" fontWeight={500} color="primary" gutterBottom>
                      Select Stay Nights ({selectedNights} of {dates.length - 1} nights selected)
                    </Typography>
                    
                    {/* Horizontal Night Selection */}
                    <Box sx={{ 
                      display: 'flex', 
                      flexWrap: 'wrap', 
                      gap: 1, 
                      mt: 2,
                      p: 2,
                      bgcolor: 'rgba(53, 84, 209, 0.02)',
                      borderRadius: 2,
                      border: '1px solid rgba(53, 84, 209, 0.1)'
                    }}>
                      {dates.slice(0, dates.length - 1).map((date, nightIndex) => {
                        const isSelected = nightIndex < selectedNights;
                        const nextDate = dates[nightIndex + 1];
                        
                        return (
                          <Box
                            key={nightIndex}
                            onClick={() => {
                              // Toggle night selection
                              const newSelectedNights = isSelected && nightIndex + 1 === selectedNights 
                                ? nightIndex  // Deselect this night
                                : nightIndex + 1; // Select up to this night
                              
                              if (newSelectedNights >= 0 && newSelectedNights <= dates.length - 1) {
                                setSelectedNights(newSelectedNights);
                                
                                // Update the active hotel configuration
                                const updatedConfig = {
                                  ...hotelConfigurations[activeHotelIndex],
                                  nights: newSelectedNights
                                };
                                
                                const updatedConfigurations = [...hotelConfigurations];
                                updatedConfigurations[activeHotelIndex] = updatedConfig;
                                setHotelConfigurations(updatedConfigurations);
                              }
                            }}
                            sx={{
                              minWidth: 120,
                              p: 2,
                              borderRadius: 2,
                              cursor: 'pointer',
                              textAlign: 'center',
                              border: '2px solid',
                              borderColor: isSelected ? '#4caf50' : '#e0e0e0',
                              bgcolor: isSelected ? '#e8f5e8' : '#ffffff',
                              transition: 'all 0.3s ease',
                              '&:hover': {
                                borderColor: isSelected ? '#2e7d32' : '#3554D1',
                                bgcolor: isSelected ? '#c8e6c9' : 'rgba(53, 84, 209, 0.05)',
                                transform: 'translateY(-2px)',
                                boxShadow: '0 4px 12px rgba(0,0,0,0.1)'
                              }
                            }}
                          >
                            <Typography variant="caption" color="text.secondary" display="block">
                              Night {nightIndex + 1}
                            </Typography>
                            <Typography variant="body2" fontWeight={600} sx={{ 
                              color: isSelected ? '#2e7d32' : '#666',
                              mt: 0.5
                            }}>
                              {date.format('MMM DD')}
                            </Typography>
                            <Typography variant="caption" color="text.secondary" display="block">
                              to
                            </Typography>
                            <Typography variant="body2" fontWeight={600} sx={{ 
                              color: isSelected ? '#2e7d32' : '#666'
                            }}>
                              {nextDate.format('MMM DD')}
                            </Typography>
                            
                            {/* Selection Indicator */}
                            {isSelected && (
                              <Box sx={{ 
                                display: 'flex', 
                                justifyContent: 'center', 
                                mt: 1 
                              }}>
                                <Box sx={{
                                  width: 20,
                                  height: 20,
                                  borderRadius: '50%',
                                  bgcolor: '#4caf50',
                                  display: 'flex',
                                  alignItems: 'center',
                                  justifyContent: 'center'
                                }}>
                                  <Typography variant="caption" sx={{ 
                                    color: 'white', 
                                    fontSize: '10px',
                                    fontWeight: 'bold'
                                  }}>
                                    ✓
                                  </Typography>
                                </Box>
                              </Box>
                            )}
                          </Box>
                        );
                      })}
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
                        Selected: {selectedNights} night{selectedNights !== 1 ? 's' : ''} 
                        {selectedNights > 0 && (
                          <span> from {dates[0].format('MMM DD')} to {dates[selectedNights].format('MMM DD')}</span>
                        )}
                      </Typography>
                    </Box>
                  </Box>
                </CardContent>
              </Grid>
            </Grid>
          </Card>
        </>
      {/* )} */}
      
      {/* Redux Booking Array Summary (following your HotelDetailsSlice pattern) */}
      {/* {bookingArray.length > 0 && (
        <Card elevation={3} sx={{ mb: 3, bgcolor: 'rgba(76, 175, 80, 0.05)' }}>
          <CardContent>
            <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center', color: 'success.main' }}>
              🎯 Booking Summary (Redux Store)
              <Chip 
                label={`${bookingArray.length} ${bookingArray.length === 1 ? 'Room' : 'Rooms'}`} 
                size="small" 
                color="success" 
                sx={{ ml: 2 }} 
              />
            </Typography>
            
            <Grid container spacing={2}>
              {bookingArray.map((room, roomIndex) => (
                <Grid item xs={12} md={6} key={roomIndex}>
                  <Paper sx={{ p: 2, bgcolor: 'white' }}>
                    <Typography variant="subtitle2" fontWeight={600} gutterBottom>
                      {room.hotel_details?.hotel_name || 'Hotel'} - {room.room_type}
                    </Typography>
                    
                    {room.beds.map((bed, bedIndex) => (
                      <Box key={bedIndex} sx={{ mt: 1, p: 1, bgcolor: 'rgba(53, 84, 209, 0.05)', borderRadius: 1 }}>
                        <Typography variant="body2">
                          <strong>Bed:</strong> {bed.bed_type} ({bed.head_count} guest{bed.head_count > 1 ? 's' : ''})
                        </Typography>
                        {bed.selectedMeals && Object.keys(bed.selectedMeals).length > 0 && (
                          <Typography variant="caption" display="block" color="text.secondary">
                            Meals: {Object.values(bed.selectedMeals).map(meal => meal.name).join(', ')}
                          </Typography>
                        )}
                        {bed.baby_cot && (
                          <Typography variant="caption" display="block" color="text.secondary">
                            Baby Cot: ${bed.baby_cot_price || 0}
                          </Typography>
                        )}
                      </Box>
                    ))}
                  </Paper>
                </Grid>
              ))}
            </Grid>
            
            <Box sx={{ mt: 3, p: 2, bgcolor: 'rgba(76, 175, 80, 0.1)', borderRadius: 1, textAlign: 'center' }}>
              <Typography variant="h5" fontWeight={600} color="success.main">
                Total Price: ${totalPrice.toFixed(2)}
              </Typography>
              <Typography variant="caption" color="text.secondary">
                (Calculated automatically by your HotelDetailsSlice)
              </Typography>
            </Box>
          </CardContent>
        </Card>
      )} */}

      {/* <Card elevation={3} sx={{ mb: 3 }}>
        <CardContent>
          <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 1 }}>
            <Box>
              <Button 
                variant="outlined" 
                color="primary" 
                startIcon={<AddIcon />}
                onClick={handleAddMoreRooms}
              >
                Add More Rooms
              </Button>
              <Button 
                variant="outlined" 
                color="primary" 
                startIcon={<AddIcon />}
                onClick={handleAddSimilarHotel}
                sx={{ ml: 1 }}
              >
                Add Similar Hotel
              </Button>
           
              <Button 
                variant="contained" 
                color="primary" 
                onClick={updateCurrentConfiguration}
                sx={{ ml: 2 }}
                startIcon={<AddIcon />}
              >
                Save Changes
              </Button>
              

              <Button 
                variant="contained" 
                color="success" 
                onClick={syncToBookingArray}
                sx={{ ml: 1 }}
                startIcon={<RestaurantMenuIcon />}
              >
                Sync to Redux Store
              </Button>
            </Box>
            
            <Box>
              <Button 
                variant="outlined" 
                color="primary" 
                startIcon={<ContentCopyIcon />}
                onClick={handleDuplicateHotel}
              >
                Duplicate
              </Button>
              <Button 
                variant="outlined" 
                color="error" 
                startIcon={<DeleteIcon />} 
                sx={{ ml: 1 }}
                onClick={handleRemoveHotel}
              >
                Remove
              </Button>
            </Box>
          </Box>
        </CardContent>
              </Card> */}
        
        {/* Submit Section */}
        {/* <Card elevation={3} sx={{ mb: 3, bgcolor: 'rgba(76, 175, 80, 0.05)' }}>
          <CardContent>
            <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center', color: 'success.main' }}>
              🎯 Hotel Booking Summary
              <Chip 
                label={`${bookingArray.length} ${bookingArray.length === 1 ? 'Room' : 'Rooms'}`} 
                size="small" 
                color="success" 
                sx={{ ml: 2 }} 
              />
            </Typography>
            
          
            {bookingArray.length > 0 ? (
              <Grid container spacing={2} sx={{ mb: 3 }}>
                {bookingArray.map((room, roomIndex) => (
                  <Grid item xs={12} md={6} key={roomIndex}>
                    <Paper sx={{ p: 2, bgcolor: 'white' }}>
                      <Typography variant="subtitle2" fontWeight={600} gutterBottom>
                        {room.hotel_details?.hotel_name || 'Hotel'} - {room.room_type}
                      </Typography>
                      
                      {room.beds.map((bed, bedIndex) => (
                        <Box key={bedIndex} sx={{ mt: 1, p: 1, bgcolor: 'rgba(53, 84, 209, 0.05)', borderRadius: 1 }}>
                          <Typography variant="body2">
                            <strong>Bed:</strong> {bed.bed_type} ({bed.head_count} guest{bed.head_count > 1 ? 's' : ''})
                          </Typography>
                          <Typography variant="body2">
                            <strong>Nights:</strong> {bed.nights || 1}
                          </Typography>
                          {bed.selectedMeals && Object.keys(bed.selectedMeals).length > 0 && (
                            <Typography variant="caption" display="block" color="text.secondary">
                              Meals: {Object.values(bed.selectedMeals).map(meal => meal.name).join(', ')}
                            </Typography>
                          )}
                          {bed.baby_cot && (
                            <Typography variant="caption" display="block" color="text.secondary">
                              Baby Cot: ${bed.baby_cot_price || 0}
                            </Typography>
                          )}
                        </Box>
                      ))}
                    </Paper>
                  </Grid>
                ))}
              </Grid>
            ) : (
              <Alert severity="info" sx={{ mb: 3 }}>
                Please complete your hotel selections above to see the booking summary.
              </Alert>
            )}
            
           
            <Box sx={{ 
              display: 'flex', 
              justifyContent: 'space-between', 
              alignItems: 'center',
              p: 3,
              bgcolor: 'rgba(76, 175, 80, 0.1)', 
              borderRadius: 2,
              border: '2px solid rgba(76, 175, 80, 0.3)'
            }}>
              <Box>
                <Typography variant="h4" fontWeight={600} color="success.main">
                  Total Price: ${totalPrice.toFixed(2)}
                </Typography>
                <Typography variant="caption" color="text.secondary">
                  Automatically calculated from all selections
                </Typography>
              </Box>
              
              <Box sx={{ display: 'flex', gap: 2 }}>
                <Button 
                  variant="outlined" 
                  color="primary" 
                  size="large"
                  onClick={() => {
                    // Force sync to make sure Redux is up to date
                    syncToBookingArray();
                    console.log('Manual sync completed');
                  }}
                  sx={{ minWidth: 120 }}
                >
                  Refresh Summary
                </Button>
                
                <Button 
                  variant="contained" 
                  color="success" 
                  size="large"
                  onClick={() => {
                    // Ensure latest data is synced to Redux
                    syncToBookingArray();
                    
                    // The bookingArray in Redux already contains all the booking details
                    // No need for separate submission - just use the existing bookingArray
                    console.log('=== CURRENT BOOKING ARRAY (READY FOR SUBMISSION) ===');
                    console.log('Total Price:', totalPrice);
                    console.log('Booking Array:', JSON.stringify(bookingArray, null, 2));
                    console.log('Search Criteria:', JSON.stringify(searchCriteria, null, 2));
                    
                    // Here you can make your API call or further processing using:
                    // - bookingArray (contains all hotel/room/meal selections)
                    // - totalPrice (automatically calculated total)
                    // - searchCriteria (original search parameters)
                    
                    // Example API call structure:
                    // const submissionData = {
                    //   bookingArray: bookingArray,
                    //   totalPrice: totalPrice,
                    //   searchCriteria: searchCriteria,
                    //   submittedAt: new Date().toISOString()
                    // };
                    // await submitBookingAPI(submissionData);
                    
                    setAlert({
                      show: true,
                      message: `Hotel booking ready! ${bookingArray.length} room(s) selected with total price $${totalPrice.toFixed(2)}`,
                      severity: 'success'
                    });
                    
                    setTimeout(() => {
                      setAlert({ show: false });
                    }, 5000);
                  }}
                  disabled={bookingArray.length === 0}
                  sx={{ minWidth: 150 }}
                  startIcon={<HotelIcon />}
                >
                  Submit Booking
                </Button>
              </Box>
            </Box>
          </CardContent>
        </Card> */}
        </Box>
      );
} 
