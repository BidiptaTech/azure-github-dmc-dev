import React, { useState, useEffect, useMemo, useRef } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { Box, Collapse, Alert } from '@mui/material';
import moment from 'moment';
import useHotelData from './hooks/useHotelData';
import useRoomData from './hooks/useRoomData';
import useMealPlanData from './hooks/useMealPlanData';
import { MEAL_PLAN_OPTIONS } from './utils/constants';
import HotelSelectionForm from './components/HotelSelectionForm';
import HotelConfigSummary from './components/HotelConfigSummary';
import MealPlanSelection from './components/MealPlanSelection';
import NightSelection from './components/NightSelection';
import { setAllServices } from '@/slice/tour-packages/tourPackageSlice';
import { calculateNightIndicesInTourRange } from './utils/hotelUtils';

export default function HotelComponent({ searchParams }) {
  // Get search state from Redux (populated by SearchForm)
  // This contains: ucheckIn, ucheckOut, guests, location data
  const searchState = useSelector((state) => state.hotels.searchState);
  
  // Get tour package search criteria for date updates
  const tourSearchCriteria = useSelector((state) => state.tourPackages.searchCriteria);
  
  // Ref to track if migration has been completed
  const migrationCompleted = useRef(false);
  
  // Get search criteria from Redux searchState (populated by SearchForm) - MOVED TO TOP
  const searchCriteria = useMemo(() => {
    const criteria = {};
    
    // PRIORITY 1: Use tour package search criteria dates if available (these are the updated tour dates)
    if (tourSearchCriteria?.checkIn && tourSearchCriteria?.checkOut) {
      // Tour package dates might be in different format, handle both YYYY-MM-DD and DD/MM/YYYY
      if (tourSearchCriteria.checkIn.includes('-') && tourSearchCriteria.checkIn.match(/^\d{4}-\d{2}-\d{2}$/)) {
        // YYYY-MM-DD format
        criteria.checkIn = moment(tourSearchCriteria.checkIn, 'YYYY-MM-DD').format('DD/MM/YYYY');
        criteria.checkOut = moment(tourSearchCriteria.checkOut, 'YYYY-MM-DD').format('DD/MM/YYYY');
      } else {
        // DD/MM/YYYY format
        criteria.checkIn = tourSearchCriteria.checkIn;
        criteria.checkOut = tourSearchCriteria.checkOut;
      }
      console.log("Hotel component - Using dates from tour package search criteria:", {
        original: { checkIn: tourSearchCriteria.checkIn, checkOut: tourSearchCriteria.checkOut },
        formatted: { checkIn: criteria.checkIn, checkOut: criteria.checkOut }
      });
    }
    // PRIORITY 2: Fall back to hotel search state dates
    else if (searchState?.ucheckIn && searchState?.ucheckOut) {
      criteria.checkIn = moment(searchState.ucheckIn, 'YYYY-MM-DD').format('DD/MM/YYYY');
      criteria.checkOut = moment(searchState.ucheckOut, 'YYYY-MM-DD').format('DD/MM/YYYY');
      console.log("Hotel component - Using dates from hotel search state:", criteria);
    }
    
    // Get guest information from Redux searchState
    if (searchState?.guests) {
      criteria.guests = searchState.guests;
    }
    
    // Get location from Redux searchState
    if (searchState?.location) {
      criteria.location = searchState.location;
    }
    
    console.log("Hotel component - Final search criteria:", criteria);
    return criteria;
  }, [searchState, tourSearchCriteria]);
  
  // Main hotel data/state management
  const {
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
    createInitialHotelConfiguration
  } = useHotelData(MEAL_PLAN_OPTIONS);

  const dispatch = useDispatch();
  // Get all services from Redux
  const allServices = useSelector((state) => state.tourPackages.AllServices || []);

  // Debug: Log hotelConfigurations to track state
  // useEffect(() => {
  //   console.log("HotelComponent - hotelConfigurations changed:", hotelConfigurations);
  //   console.log("HotelComponent - activeHotelIndex:", activeHotelIndex);
  // }, [hotelConfigurations, activeHotelIndex]);

  // Ensure we always have at least one configuration
  useEffect(() => {
    if (hotelConfigurations.length === 0) {
      console.log("HotelComponent - No configurations found, creating initial one with search criteria");
      const initialConfig = createInitialHotelConfiguration(searchCriteria);
      setHotelConfigurations([initialConfig]);
      setActiveHotelIndex(0);
    }
  }, [hotelConfigurations.length, createInitialHotelConfiguration, setHotelConfigurations, setActiveHotelIndex, searchCriteria]);

  // Ensure activeHotelIndex is valid
  useEffect(() => {
    if (hotelConfigurations.length > 0 && (activeHotelIndex >= hotelConfigurations.length || activeHotelIndex < 0)) {
      console.log("HotelComponent - Invalid activeHotelIndex, resetting to 0");
      setActiveHotelIndex(0);
    }
  }, [hotelConfigurations.length, activeHotelIndex, setActiveHotelIndex]);

  // Migrate existing hotel configurations to include individual date fields
  useEffect(() => {
    // Only run migration once and only if we have configurations but haven't migrated yet
    if (migrationCompleted.current || hotelConfigurations.length === 0) {
      return;
    }
    
    const needsMigration = hotelConfigurations.some(config => 
      !config.hotelCheckIn || !config.hotelCheckOut
    );
    
    if (needsMigration) {
      console.log("Migrating hotel configurations to include individual dates");
      
      setHotelConfigurations(prevConfigurations => {
        return prevConfigurations.map(config => {
          if (!config.hotelCheckIn || !config.hotelCheckOut) {
            // Use tour dates as fallback for existing configurations
            let checkIn, checkOut;
            
            if (tourSearchCriteria?.checkIn && tourSearchCriteria?.checkOut) {
              checkIn = tourSearchCriteria.checkIn.includes('-') ? 
                moment(tourSearchCriteria.checkIn, 'YYYY-MM-DD').format('DD/MM/YYYY') :
                tourSearchCriteria.checkIn;
              checkOut = tourSearchCriteria.checkOut.includes('-') ? 
                moment(tourSearchCriteria.checkOut, 'YYYY-MM-DD').format('DD/MM/YYYY') :
                tourSearchCriteria.checkOut;
            } else if (searchState?.ucheckIn && searchState?.ucheckOut) {
              checkIn = moment(searchState.ucheckIn, 'YYYY-MM-DD').format('DD/MM/YYYY');
              checkOut = moment(searchState.ucheckOut, 'YYYY-MM-DD').format('DD/MM/YYYY');
            } else {
              checkIn = moment().format('DD/MM/YYYY');
              checkOut = moment().add(config.nights || 1, 'days').format('DD/MM/YYYY');
            }
            
            console.log(`Migrating hotel configuration ${config.id} with dates:`, { checkIn, checkOut });
            
            return {
              ...config,
              hotelCheckIn: checkIn,
              hotelCheckOut: checkOut
            };
          }
          return config;
        });
      });
      
      // Mark migration as completed
      migrationCompleted.current = true;
    }
  }, [hotelConfigurations.length]); // Only depend on length, not the full array

  // Room and bed data for the active configuration
  const selectedHotel = hotelConfigurations[activeHotelIndex]?.hotelId || '';
  const roomType = String(hotelConfigurations[activeHotelIndex]?.roomTypeId || '');
  const bedType = String(hotelConfigurations[activeHotelIndex]?.bedTypeId || '');
  
  // Use guest data from search criteria if available, otherwise fall back to configuration
  const selectedGuests = hotelConfigurations[activeHotelIndex]?.selectedGuests || 
                         parseInt(searchCriteria?.guests?.Adults || searchState?.guests?.Adults || 1);
  
  // Debug: Log guest data flow
  // console.log("Hotel component - Guest data flow:", {
  //   hotelConfigGuests: hotelConfigurations[activeHotelIndex]?.selectedGuests,
  //   searchCriteriaGuests: searchCriteria?.guests,
  //   searchStateGuests: searchState?.guests,
  //   finalSelectedGuests: selectedGuests
  // });
  const selectedMealPlan = hotelConfigurations[activeHotelIndex]?.selectedMealPlan || null;
  const selectedNightIndices = new Set(hotelConfigurations[activeHotelIndex]?.selectedNightIndices || []);
  const selectedNights = hotelConfigurations[activeHotelIndex]?.nights || 1;
  
  // Debug: Log search data availability
  // useEffect(() => {
  //   console.log("Hotel component - Search data:", {
  //     searchState,
  //     searchCriteria,
  //     selectedGuests,
  //     dates: dates.length
  //   });
  // }, [searchState, searchCriteria, selectedGuests, dates.length]);

  const {
    roomTypes,
    bedTypes,
    selectedRoomType,
    selectedBedType,
    roomDataStatus
  } = useRoomData(roomType, bedType, selectedHotel);

  // Debug: Track roomTypes changes
  // useEffect(() => {
  //   console.log("roomTypes changed:", roomTypes);
  //   console.log("roomDataStatus:", roomDataStatus);
  //   console.log("selectedHotel:", selectedHotel);
  // }, [roomTypes, roomDataStatus, selectedHotel]);

  const mealPlans = useMealPlanData(roomType, bedType, selectedGuests);

  // Debug: Track state changes
  // useEffect(() => {
  //   console.log("State Update - roomType:", roomType);
  //   console.log("State Update - activeConfig roomTypeId:", hotelConfigurations[activeHotelIndex]?.roomTypeId);
  // }, [roomType, hotelConfigurations, activeHotelIndex]);

  // Handler for selecting a hotel configuration
  const selectHotelConfiguration = (index) => {
    setActiveHotelIndex(index);
  };

  // Handler for room meal plan change
  const handleMealPlanChange = (newMealPlanId) => {
    console.log("Changing meal plan to:", newMealPlanId);
    
    setHotelConfigurations(prevConfigurations => {
      if (!prevConfigurations[activeHotelIndex]) {
        return prevConfigurations;
      }
      
      // Find the meal plan details to store the meal plan ID
      const selectedMealPlanData = mealPlans.find(plan => plan.id === newMealPlanId);
      
      console.log("Selected meal plan data:", selectedMealPlanData);
      
      const updatedConfig = {
        ...prevConfigurations[activeHotelIndex],
        selectedMealPlan: newMealPlanId,
        // Store meal plan details for pricing calculations
        mealPlanDetails: selectedMealPlanData || null
      };
      
      const updatedConfigurations = [...prevConfigurations];
      updatedConfigurations[activeHotelIndex] = updatedConfig;
      
      console.log("Updated hotel configuration with new meal plan:", updatedConfig);
      return updatedConfigurations;
    });
  };

  // Handler for guest count change
  const handleGuestCountChange = (newGuestCount) => {
    console.log("Changing guest count to:", newGuestCount);
    
    setHotelConfigurations(prevConfigurations => {
      if (!prevConfigurations[activeHotelIndex]) {
        return prevConfigurations;
      }
      
      const updatedConfig = {
        ...prevConfigurations[activeHotelIndex],
        selectedGuests: newGuestCount,
        // Reset meal plan selection when guest count changes to recalculate pricing
        selectedMealPlan: prevConfigurations[activeHotelIndex].selectedMealPlan || null,
        mealPlanDetails: null // Will be recalculated with new pricing
      };
      
      const updatedConfigurations = [...prevConfigurations];
      updatedConfigurations[activeHotelIndex] = updatedConfig;
      
      console.log("Updated hotel configuration with new guest count:", updatedConfig);
      return updatedConfigurations;
    });
  };

  // Handler for night selection change
  const setSelectedNightIndicesHandler = (newSet) => {
    setHotelConfigurations(prevConfigurations => {
      if (!prevConfigurations[activeHotelIndex]) {
        return prevConfigurations;
      }
      
      // Calculate new hotel dates based on selected nights
      const selectedNightIndicesArray = Array.from(newSet);
      let newCheckIn, newCheckOut;
      
      if (selectedNightIndicesArray.length > 0 && dates.length > 0) {
        // Get the first and last selected night dates
        const minNightIndex = Math.min(...selectedNightIndicesArray);
        const maxNightIndex = Math.max(...selectedNightIndicesArray);
        
        newCheckIn = dates[minNightIndex]?.format('DD/MM/YYYY') || prevConfigurations[activeHotelIndex].hotelCheckIn;
        newCheckOut = dates[maxNightIndex + 1]?.format('DD/MM/YYYY') || prevConfigurations[activeHotelIndex].hotelCheckOut;
        
        console.log("Updated hotel dates based on night selection:", {
          selectedNights: selectedNightIndicesArray,
          newCheckIn,
          newCheckOut
        });
      } else {
        // Keep existing dates if no nights selected
        newCheckIn = prevConfigurations[activeHotelIndex].hotelCheckIn;
        newCheckOut = prevConfigurations[activeHotelIndex].hotelCheckOut;
      }
      
      const updatedConfig = {
        ...prevConfigurations[activeHotelIndex],
        selectedNightIndices: selectedNightIndicesArray,
        nights: newSet.size,
        hotelCheckIn: newCheckIn,
        hotelCheckOut: newCheckOut
      };
      
      const updatedConfigurations = [...prevConfigurations];
      updatedConfigurations[activeHotelIndex] = updatedConfig;
      return updatedConfigurations;
    });
  };

  // Handler for number of nights
  const setSelectedNightsHandler = (nights) => {
    setHotelConfigurations(prevConfigurations => {
      if (!prevConfigurations[activeHotelIndex]) {
        return prevConfigurations;
      }
      
      const updatedConfig = {
        ...prevConfigurations[activeHotelIndex],
        nights
      };
      
      const updatedConfigurations = [...prevConfigurations];
      updatedConfigurations[activeHotelIndex] = updatedConfig;
      return updatedConfigurations;
    });
  };

  // Handler for hotel selection (from HotelListing)
  const setSelectedHotel = (hotel) => {
    // Add null check to prevent errors when hotel is null
    if (!hotel) {
      console.warn("setSelectedHotel called with null/undefined hotel");
      return;
    }
    
    console.log("Selected hotel data:", hotel);
    
    // Ensure we have a proper hotel object with required fields
    const processedHotel = {
      ...hotel,
      // Ensure these fields exist for Redux integration
      hotel_name: hotel.hotel_name || hotel.name || "Unknown Hotel",
      image: hotel.image || hotel.main_image || hotel.logo || 
             (hotel.site_image && hotel.site_image.length > 0 ? hotel.site_image[0] : "")
    };
    
    // Use functional update to avoid stale closure
    setHotelConfigurations(prevConfigurations => {
      if (!prevConfigurations[activeHotelIndex]) {
        return prevConfigurations;
      }
      
      const updatedConfig = {
        ...prevConfigurations[activeHotelIndex],
        hotelId: processedHotel.id || processedHotel.hotel_id || '',
        hotelDetails: processedHotel
      };
      
      const updatedConfigurations = [...prevConfigurations];
      updatedConfigurations[activeHotelIndex] = updatedConfig;
      return updatedConfigurations;
    });
  };

  // Handler for room type
  const setRoomType = (roomTypeId) => {
    console.log("setRoomType called with:", roomTypeId);
    
    // Ensure roomTypeId is a string for consistent comparison
    const roomTypeIdStr = String(roomTypeId);
    const selectedRoom = roomTypes.find(r => String(r.id) === roomTypeIdStr);
    console.log("selectedRoom found:", selectedRoom?.name || 'None');
    
    // Use functional update to avoid stale closure
    setHotelConfigurations(prevConfigurations => {
      if (!prevConfigurations[activeHotelIndex]) {
        console.log("setRoomType - No active configuration found");
        return prevConfigurations;
      }
      
      const updatedConfig = {
        ...prevConfigurations[activeHotelIndex],
        roomTypeId: roomTypeIdStr,
        roomTypeName: selectedRoom?.name || '',
        bedTypeId: '',
        bedTypeName: ''
      };
      
      const updatedConfigurations = [...prevConfigurations];
      updatedConfigurations[activeHotelIndex] = updatedConfig;
      
      console.log("setRoomType - Successfully updated roomTypeId to:", roomTypeIdStr);
      return updatedConfigurations;
    });
  };

  // Handler for bed type
  const setBedType = (bedTypeId) => {
    // Ensure bedTypeId is a string for consistent comparison
    const bedTypeIdStr = String(bedTypeId);
    const selectedBed = bedTypes.find(b => String(b.id) === bedTypeIdStr);
    
    // Use functional update to avoid stale closure
    setHotelConfigurations(prevConfigurations => {
      if (!prevConfigurations[activeHotelIndex]) {
        return prevConfigurations;
      }
      
      const updatedConfig = {
        ...prevConfigurations[activeHotelIndex],
        bedTypeId: bedTypeIdStr,
        bedTypeName: selectedBed?.name || ''
      };
      
      const updatedConfigurations = [...prevConfigurations];
      updatedConfigurations[activeHotelIndex] = updatedConfig;
      return updatedConfigurations;
    });
  };

  // Handler for adding more rooms to the same hotel
  const handleAddMoreRooms = () => {
    console.log("Add more rooms clicked for hotel:", hotelConfigurations[activeHotelIndex]?.hotelDetails?.hotel_name);
    
    if (!hotelConfigurations[activeHotelIndex]?.hotelId) {
      console.warn("Cannot add room: No hotel selected");
      return;
    }
    
    const currentConfig = hotelConfigurations[activeHotelIndex];
    
    // Create a new room configuration for the same hotel
    const newRoomConfig = {
      id: Math.random().toString(36).substring(2) + Date.now().toString(36),
      hotelId: currentConfig.hotelId, // Same hotel
      hotelDetails: { ...currentConfig.hotelDetails }, // Same hotel details
      roomTypeId: '', // Reset room selection
      roomTypeName: '',
      bedTypeId: '', // Reset bed selection
      bedTypeName: '',
      max_occupancy: 1,
      bedPrice: 0,
      mealPlanId: 'self',
      nights: currentConfig.nights, // Keep same nights
      selectedNightIndices: [...currentConfig.selectedNightIndices], // Keep same night selection
      // Keep same hotel dates
      hotelCheckIn: currentConfig.hotelCheckIn,
      hotelCheckOut: currentConfig.hotelCheckOut,
      babyCot: false,
      occupancyType: 'single',
      adultDistribution: { male: 0, female: 0 },
      expanded: true,
      selectedGuests: currentConfig.selectedGuests, // Keep same guest count
      selectedMealPlan: 'self' // Default to room only
    };
    
    console.log("Creating new room configuration for same hotel:", newRoomConfig);
    
    // Add the new room configuration and make it active
    setHotelConfigurations(prevConfigurations => [...prevConfigurations, newRoomConfig]);
    setActiveHotelIndex(hotelConfigurations.length); // Set to the new configuration index
    
    // Show success message
    setAlert({
      show: true,
      message: `Added new room to ${currentConfig.hotelDetails?.hotel_name || 'selected hotel'}`,
      severity: 'success'
    });
    
    // Hide message after 3 seconds
    setTimeout(() => {
      setAlert(prev => ({ ...prev, show: false }));
    }, 3000);
  };

  // Handler for adding new hotel
  const handleAddNewHotel = () => {
    console.log("Add new hotel clicked");
    const newConfig = createInitialHotelConfiguration(searchCriteria);
    setHotelConfigurations(prevConfigurations => [...prevConfigurations, newConfig]);
    setActiveHotelIndex(prevIndex => prevIndex + 1);
  };

  // Handler for removing hotel
  const handleRemoveHotel = () => {
    console.log("Remove hotel clicked");
    
    setHotelConfigurations(prevConfigurations => {
      if (prevConfigurations.length <= 1) {
        console.log("Cannot remove hotel: Only one configuration remaining");
        return prevConfigurations; // Don't remove if it's the last one
      }
      
      const newConfigurations = prevConfigurations.filter((_, index) => index !== activeHotelIndex);
      console.log("Hotel configuration removed. New configurations count:", newConfigurations.length);
      return newConfigurations;
    });
    
    // Update active index after removal
    setActiveHotelIndex(prevIndex => {
      const newIndex = Math.max(0, prevIndex - 1);
      console.log("Active hotel index updated from", prevIndex, "to", newIndex);
      return newIndex;
    });
  };

  // Handler for removing hotel configuration by specific index (for HotelConfigSummary)
  const removeHotelConfiguration = (index) => {
    console.log("Remove hotel configuration at index:", index);
    
    setHotelConfigurations(prevConfigurations => {
      if (prevConfigurations.length <= 1) {
        console.log("Cannot remove hotel: Only one configuration remaining");
        return prevConfigurations; // Don't remove if it's the last one
      }
      
      const newConfigurations = prevConfigurations.filter((_, i) => i !== index);
      console.log("Hotel configuration removed. New configurations count:", newConfigurations.length);
      return newConfigurations;
    });
    
    // Update active index after removal
    setActiveHotelIndex(prevIndex => {
      if (prevIndex === index) {
        // If we removed the active hotel, select the previous one (or 0 if it was the first)
        const newIndex = Math.max(0, prevIndex - 1);
        console.log("Active hotel index updated from", prevIndex, "to", newIndex, "(removed active hotel)");
        return newIndex;
      } else if (prevIndex > index) {
        // If we removed a hotel before the active one, shift the active index down
        const newIndex = prevIndex - 1;
        console.log("Active hotel index updated from", prevIndex, "to", newIndex, "(removed hotel before active)");
        return newIndex;
      }
      // If we removed a hotel after the active one, keep the same index
      console.log("Active hotel index remains", prevIndex, "(removed hotel after active)");
      return prevIndex;
    });
  };

  // Handler for adding/removing hotels/rooms can be added similarly
  
  // Generate dates array for night selection - ALWAYS use tour date range for full flexibility
  const dates = useMemo(() => {
    let checkInDate, checkOutDate;
    
    // PRIORITY 1: Use tour package search criteria dates (these are the full tour dates)
    if (tourSearchCriteria?.checkIn && tourSearchCriteria?.checkOut) {
      if (tourSearchCriteria.checkIn.includes('-') && tourSearchCriteria.checkIn.match(/^\d{4}-\d{2}-\d{2}$/)) {
        // YYYY-MM-DD format
        checkInDate = moment(tourSearchCriteria.checkIn, 'YYYY-MM-DD');
        checkOutDate = moment(tourSearchCriteria.checkOut, 'YYYY-MM-DD');
      } else {
        // DD/MM/YYYY format
        checkInDate = moment(tourSearchCriteria.checkIn, 'DD/MM/YYYY');
        checkOutDate = moment(tourSearchCriteria.checkOut, 'DD/MM/YYYY');
      }
      console.log("Hotel component dates - Using tour package search criteria (full tour range):", {
        checkIn: checkInDate.format('YYYY-MM-DD'),
        checkOut: checkOutDate.format('YYYY-MM-DD')
      });
    }
    // PRIORITY 2: Use dates from Redux hotel searchState
    else if (searchState?.ucheckIn && searchState?.ucheckOut) {
      checkInDate = moment(searchState.ucheckIn, 'YYYY-MM-DD');
      checkOutDate = moment(searchState.ucheckOut, 'YYYY-MM-DD');
      console.log("Hotel component dates - Using hotel search state (fallback):", {
        checkIn: checkInDate.format('YYYY-MM-DD'),
        checkOut: checkOutDate.format('YYYY-MM-DD')
      });
    } 
    // PRIORITY 3: Try searchCriteria (formatted dates)
    else if (searchCriteria?.checkIn && searchCriteria?.checkOut) {
      checkInDate = moment(searchCriteria.checkIn, 'DD/MM/YYYY');
      checkOutDate = moment(searchCriteria.checkOut, 'DD/MM/YYYY');
      console.log("Hotel component dates - Using search criteria (fallback):", {
        checkIn: checkInDate.format('YYYY-MM-DD'),
        checkOut: checkOutDate.format('YYYY-MM-DD')
      });
    } 
    // PRIORITY 4: Fallback to current hotel configuration
    else if (hotelConfigurations.length > 0 && hotelConfigurations[activeHotelIndex]?.nights) {
      checkInDate = moment();
      checkOutDate = moment().add(hotelConfigurations[activeHotelIndex].nights, 'days');
      console.log("Hotel component dates - Using hotel configuration");
    }
    // Default fallback
    else {
      checkInDate = moment();
      checkOutDate = moment().add(3, 'days'); // Default 3 nights
      console.log("Hotel component dates - Using default dates (3 nights from today)");
    }
    
    const datesArray = [];
    const current = checkInDate.clone();
    
    // Generate array of dates from check-in to check-out
    while (current.isSameOrBefore(checkOutDate)) {
      datesArray.push(current.clone());
      current.add(1, 'day');
    }
    
    console.log("Hotel component - Generated dates for night selection (full tour range):", datesArray.map(d => d.format('YYYY-MM-DD')));
    return datesArray;
  }, [
    tourSearchCriteria, 
    searchState, 
    searchCriteria
  ]); // Simplified dependencies - only tour-level dates, not individual hotel dates

  // Recalculate selected night indices when dates array changes (tour date range changes)
  useEffect(() => {
    if (!dates || dates.length === 0 || !hotelConfigurations.length) return;
    
    console.log("Recalculating night indices for all hotels based on new tour date range");
    
    // Update all hotel configurations with correct night indices within the tour range
    setHotelConfigurations(prevConfigurations => {
      return prevConfigurations.map(config => {
        if (config.hotelCheckIn && config.hotelCheckOut) {
          const nightIndices = calculateNightIndicesInTourRange(
            config.hotelCheckIn, 
            config.hotelCheckOut, 
            dates
          );
          
          return {
            ...config,
            selectedNightIndices: nightIndices,
            nights: nightIndices.length
          };
        }
        return config;
      });
    });
  }, [dates]); // Only depend on dates array changes

  // Handler for updating individual hotel dates
  const updateHotelDates = (checkIn, checkOut) => {
    console.log("Manually updating hotel dates:", { checkIn, checkOut });
    
    setHotelConfigurations(prevConfigurations => {
      if (!prevConfigurations[activeHotelIndex]) {
        return prevConfigurations;
      }
      
      // Calculate nights based on new dates
      const nights = moment(checkOut, 'DD/MM/YYYY').diff(moment(checkIn, 'DD/MM/YYYY'), 'days');
      
      // Create new night indices array based on the new date range
      const newNightIndices = [];
      for (let i = 0; i < nights; i++) {
        newNightIndices.push(i);
      }
      
      const updatedConfig = {
        ...prevConfigurations[activeHotelIndex],
        hotelCheckIn: checkIn,
        hotelCheckOut: checkOut,
        nights: nights,
        selectedNightIndices: newNightIndices
      };
      
      const updatedConfigurations = [...prevConfigurations];
      updatedConfigurations[activeHotelIndex] = updatedConfig;
      
      console.log("Updated hotel configuration with new dates:", {
        hotelName: updatedConfig.hotelDetails?.hotel_name,
        checkIn,
        checkOut,
        nights
      });
      
      return updatedConfigurations;
    });
  };

  // Sync hotel configurations to setAllServices in Redux
  useEffect(() => {
    // Only sync if there is at least one valid hotel configuration
    if (!hotelConfigurations || hotelConfigurations.length === 0) return;

    console.log("%c Redux Sync Started", "background: #e74c3c; color: white; padding: 4px;");
    console.log("Syncing hotel configurations to Redux:", hotelConfigurations);
    
    // Remove all previous hotel services (both 'hotel' and 'Hotel' types)
    const servicesWithoutHotels = allServices.filter(service => 
      service.type !== 'hotel' && service.type !== 'Hotel'
    );
    
    // First, group configurations by hotelId
    const hotelGroups = {};
    
    // Group all configurations by hotel
    hotelConfigurations.forEach(config => {
      if (!config.hotelId) return; // Skip invalid configs
      
      // Initialize the hotel group if it doesn't exist
      if (!hotelGroups[config.hotelId]) {
        hotelGroups[config.hotelId] = [];
      }
      
      // Add this configuration to its hotel group
      hotelGroups[config.hotelId].push(config);
    });
    
    console.log("Hotel groups:", hotelGroups);
    
    // Create hotel services array - one service per hotel
    const hotelServices = [];
    
    // Process each hotel group into a separate hotel service
    Object.entries(hotelGroups).forEach(([hotelId, configs]) => {
      // Use the first config as the base for hotel details
      const baseConfig = configs[0];
      
      console.log("Processing hotel group:", {
        hotelId,
        hotelName: baseConfig.hotelDetails?.hotel_name,
        configsCount: configs.length,
        baseConfigDates: {
          hotelCheckIn: baseConfig.hotelCheckIn,
          hotelCheckOut: baseConfig.hotelCheckOut
        },
        allConfigs: configs.map(c => ({
          id: c.id,
          hotelCheckIn: c.hotelCheckIn,
          hotelCheckOut: c.hotelCheckOut,
          nights: c.nights
        }))
      });
      
      // Debug the hotel details structure
      // console.log("Hotel details for transformation:", {
      //   hotelId: baseConfig.hotelId,
      //   hotelDetails: baseConfig.hotelDetails,
      //   hotelName: baseConfig.hotelDetails?.hotel_name,
      //   name: baseConfig.hotelDetails?.name
      // });
      
      // Ensure hotelDetails has the required fields
      if (baseConfig.hotelDetails) {
        if (!baseConfig.hotelDetails.hotel_name) {
          baseConfig.hotelDetails.hotel_name = baseConfig.hotelDetails.name || "Unknown Hotel";
          console.log("Added missing hotel_name:", baseConfig.hotelDetails.hotel_name);
        }
        
        if (!baseConfig.hotelDetails.image) {
          baseConfig.hotelDetails.image = 
            baseConfig.hotelDetails.main_image || 
            baseConfig.hotelDetails.logo || 
            (baseConfig.hotelDetails.site_image?.[0] || "");
          console.log("Added missing image:", baseConfig.hotelDetails.image);
        }
      }
      
      // Check for valid dates - prioritize individual hotel configuration dates
      let startDate, endDate;
      
      console.log("%c Date Selection Debug", "background: #9b59b6; color: white; padding: 4px;");
      console.log("baseConfig dates:", {
        hotelCheckIn: baseConfig.hotelCheckIn,
        hotelCheckOut: baseConfig.hotelCheckOut,
        hasIndividualDates: !!(baseConfig.hotelCheckIn && baseConfig.hotelCheckOut)
      });
      console.log("tourSearchCriteria:", tourSearchCriteria);
      console.log("searchState dates:", {
        ucheckIn: searchState?.ucheckIn,
        ucheckOut: searchState?.ucheckOut
      });
      
      // PRIORITY 1: Use individual hotel configuration dates if available
      if (baseConfig.hotelCheckIn && baseConfig.hotelCheckOut) {
        // Convert DD/MM/YYYY format to YYYY-MM-DD for booking
        startDate = moment(baseConfig.hotelCheckIn, 'DD/MM/YYYY').format('YYYY-MM-DD');
        endDate = moment(baseConfig.hotelCheckOut, 'DD/MM/YYYY').format('YYYY-MM-DD');
        console.log("%c Using Individual Hotel Dates", "background: #27ae60; color: white; padding: 4px;");
        console.log("Hotel booking sync - Using individual hotel config dates:", { 
          original: { checkIn: baseConfig.hotelCheckIn, checkOut: baseConfig.hotelCheckOut },
          formatted: { startDate, endDate },
          hotelName: baseConfig.hotelDetails?.hotel_name
        });
      }
      // PRIORITY 2: Fall back to tour package search criteria dates
      else if (tourSearchCriteria?.checkIn && tourSearchCriteria?.checkOut) {
        if (tourSearchCriteria.checkIn.includes('-') && tourSearchCriteria.checkIn.match(/^\d{4}-\d{2}-\d{2}$/)) {
          // YYYY-MM-DD format
          startDate = tourSearchCriteria.checkIn;
          endDate = tourSearchCriteria.checkOut;
        } else {
          // DD/MM/YYYY format - convert to YYYY-MM-DD
          startDate = moment(tourSearchCriteria.checkIn, 'DD/MM/YYYY').format('YYYY-MM-DD');
          endDate = moment(tourSearchCriteria.checkOut, 'DD/MM/YYYY').format('YYYY-MM-DD');
        }
        console.log("%c Using Tour Package Dates", "background: #f39c12; color: white; padding: 4px;");
        console.log("Hotel booking sync - Using tour package dates as fallback:", { startDate, endDate });
      }
      // PRIORITY 3: Fall back to hotel search state
      else if (searchState?.ucheckIn && searchState?.ucheckOut) {
        startDate = searchState.ucheckIn;
        endDate = searchState.ucheckOut;
        console.log("%c Using Search State Dates", "background: #3498db; color: white; padding: 4px;");
        console.log("Hotel booking sync - Using hotel search state dates as fallback:", { startDate, endDate });
      }
      // PRIORITY 4: Fall back to generated dates array
      else {
        startDate = dates.length > 0 ? dates[0].format('YYYY-MM-DD') : null;
        // Use the max nights for end date calculation
        const maxNights = Math.max(...configs.map(c => c.nights || 0));
        endDate = dates.length > maxNights ? dates[maxNights].format('YYYY-MM-DD') : null;
        console.log("%c Using Generated Dates", "background: #95a5a6; color: white; padding: 4px;");
        console.log("Hotel booking sync - Using generated dates as fallback:", { startDate, endDate, maxNights });
      }
      
      // Skip if we can't determine dates
      if (!startDate || !endDate) {
        console.warn("Cannot create booking without dates for hotel:", baseConfig.hotelDetails?.hotel_name);
        return; // Skip this hotel
      }

      // Get booking ID only from existing configuration - don't create new ones
      let bookingId = null;
      
      // Check if any configuration has an existing booking ID from previous response
      for (const config of configs) {
        const existingBookingId = getBookingIdForConfig(config);
        if (existingBookingId) {
          bookingId = existingBookingId;
          console.log("Hotel sync - Using existing booking ID from response:", bookingId);
          break;
        }
      }
      
      // For new hotels, we don't create a booking ID - it will be provided by the backend response
      if (!bookingId) {
        console.log("Hotel sync - No existing booking ID found for hotel, will be created by backend");
      }
      
      // Create rooms array from all configurations for this hotel
      const rooms = configs.map(config => {
        // Transform meal plan data for this room
        const selectedMealPlan = config.selectedMealPlan || 'self';
        const mealPlanDetails = config.mealPlanDetails || null;
        
        // Get meal plan information
        const mealPlanName = mealPlanDetails?.title || "Room Only";
        const mealPlanPrice = mealPlanDetails?.price || 0;
        
        // Create meal info for all guests in the room
        const selectedMeals = {
          meal_plan: {
            type: mealPlanName,
            price: mealPlanPrice,
            guest_count: config.selectedGuests || 1
          }
        };
        
        // Create room entry
        return {
          room_id: config.roomTypeId || 0,
          room_type: config.roomTypeName || "Standard",
          beds: [
            {
              bed_id: config.bedTypeId || 0,
              bed_type: config.bedTypeName || "Standard",
              baby_cot: config.babyCot ? 1 : 0,
              head_count: config.selectedGuests || 1,
              max_occupancy: config.max_occupancy || 2,
              price: config.bedPrice || 0,
              mealType: mealPlanName,
              selectedMeals: selectedMeals
            }
          ]
        };
      });
      
      // Calculate total price (sum of all rooms)
      const totalPrice = configs.reduce((sum, config) => {
        // Add bed price
        let roomPrice = config.bedPrice || 0;
        // Add meal plan price (already calculated for all guests in the room)
        if (config.mealPlanDetails) {
          roomPrice += config.mealPlanDetails.price || 0;
        }
        return sum + roomPrice;
      }, 0);
          
      // Find any existing customer info in current services or from the hotel configuration
      const customerInfoService = allServices.find(service => service.type === 'CustomerInfo');
      const configCustomerDetails = baseConfig.customerDetails || {};

      // Create the hotel booking data
      const hotelData = {
        // Customer information fields (will be populated when available or default to empty strings)
        // Priority: 1. CustomerInfo service, 2. Configuration customer details, 3. Empty string
        fullName: customerInfoService?.fullName || configCustomerDetails.fullName || "",
        email: customerInfoService?.email || configCustomerDetails.email || "",
        phone: customerInfoService?.phone || configCustomerDetails.phone || "",
        countryCode: customerInfoService?.countryCode || configCustomerDetails.countryCode || "",
        address1: customerInfoService?.address1 || configCustomerDetails.address1 || "",
        address2: customerInfoService?.address2 || configCustomerDetails.address2 || "",
        state: customerInfoService?.state || configCustomerDetails.state || "",
        zip: customerInfoService?.zip || configCustomerDetails.zip || "",
        specialRequests: customerInfoService?.specialRequests || configCustomerDetails.specialRequests || "",
        
        id: bookingId,
        bookingType: "enquiry",
        bookingDate: [startDate, endDate],
        hotelDetails: {
          hotel_id: baseConfig.hotelId || "",
          hotel_name: baseConfig.hotelDetails?.hotel_name,
          image: baseConfig.hotelDetails?.image,
          location: baseConfig.hotelDetails?.location || 
                   baseConfig.hotelDetails?.address || 
                   baseConfig.location ||
                   "",
          checkInTime: baseConfig.hotelDetails?.checkInTime || "15:00:00",
          checkOutTime: baseConfig.hotelDetails?.checkOutTime || "12:00:00",
          cancellation_charge: baseConfig.hotelDetails?.cancellation_charge || ""
        },
        
        priceMode: "dmc", // Default
        priceModeId: 4, // Default
        rooms: rooms,
        totalPrice: totalPrice,
        tour_id: 0 // Use as reference, main tour_id is at parent level
      };
      
      // Create a separate hotel service for this hotel
      const hotelService = {
        type: 'hotel', // Capital 'H' as in previous format
        agent_id: searchState?.agentId || 5, // Use agentId if available, fallback to 5
        tour_id: tourId || 0,
        data: [hotelData] // Each hotel service has its own data array with one entry
      };
      
      // Only add booking_id if it exists from a previous response (not for new hotels)
      if (bookingId) {
        hotelService.booking_id = bookingId;
        console.log("Hotel sync - Added existing booking_id to hotel service:", bookingId);
      }
      
      // Add this hotel service to our array of hotel services
      hotelServices.push(hotelService);
    });
    
    console.log("Transformed hotel services for Redux:", hotelServices);
    
    // Merge and dispatch - add ALL hotel services to the services without hotels
    const updatedServices = [...servicesWithoutHotels, ...hotelServices];
    dispatch(setAllServices(updatedServices));
  }, [hotelConfigurations, tourId]); // Simplified dependencies to avoid loops

  useEffect(() => {
    console.log("%c Hotel Component Rendered", "background: #6a89cc; color: white; padding: 4px;");
    console.log("Current services in Redux:", allServices);
  }, [allServices]);

  useEffect(() => {
    console.log("%c Hotel Configurations Updated", "background: #f39c12; color: white; padding: 4px;");
    console.log("Hotel configurations:", hotelConfigurations.map((config, index) => ({
      index,
      id: config.id,
      hotelName: config.hotelDetails?.hotel_name || 'Not selected',
      hotelCheckIn: config.hotelCheckIn,
      hotelCheckOut: config.hotelCheckOut,
      nights: config.nights,
      selectedNightIndices: config.selectedNightIndices
    })));
  }, [hotelConfigurations.length, activeHotelIndex]); // Only log when length or active index changes
  return (
    <Box sx={{ '& > *': { mb: 1 } }}>
      {/* Debug/alert panel */}
      <Collapse in={alert.show}>
        <Alert 
          severity={alert.severity}
          sx={{ mb: 0.5 }}
          onClose={() => setAlert({...alert, show: false})}
        >
          {alert.message}
        </Alert>
      </Collapse>

      {/* Hotel configuration summary */}
      <HotelConfigSummary 
        hotelConfigurations={hotelConfigurations}
        activeHotelIndex={activeHotelIndex}
        selectHotelConfiguration={selectHotelConfiguration}
        removeHotelConfiguration={removeHotelConfiguration}
        onAddMoreRooms={handleAddMoreRooms}
        onAddNewHotel={handleAddNewHotel}
        tourDateRange={{
          startDate: searchState?.ucheckIn || 
            (searchCriteria?.checkIn ? moment(searchCriteria.checkIn, 'DD/MM/YYYY').format('YYYY-MM-DD') : null) ||
            (dates.length > 0 ? dates[0].format('YYYY-MM-DD') : null),
          endDate: searchState?.ucheckOut || 
            (searchCriteria?.checkOut ? moment(searchCriteria.checkOut, 'DD/MM/YYYY').format('YYYY-MM-DD') : null) ||
            (dates.length > 0 ? dates[dates.length - 1].format('YYYY-MM-DD') : null)
        }}
      />

      {/* Hotel selection and room configuration form */}
      <HotelSelectionForm
        selectedHotel={selectedHotel}
        setSelectedHotel={setSelectedHotel}
        roomType={roomType}
        setRoomType={setRoomType}
        bedType={bedType}
        setBedType={setBedType}
        roomTypes={roomTypes}
        bedTypes={bedTypes}
        roomDataStatus={roomDataStatus}
        hotelConfigurations={hotelConfigurations}
        activeHotelIndex={activeHotelIndex}
        renderMealPlanSection={() => (
          <MealPlanSelection
            selectedGuests={selectedGuests}
            selectedMealPlan={selectedMealPlan}
            handleMealPlanChange={handleMealPlanChange}
            handleGuestCountChange={handleGuestCountChange}
            bedType={selectedBedType}
            searchCriteria={searchCriteria}
            mealPlans={mealPlans}
          />
        )}
        renderNightSelection={() => (
          <NightSelection
            dates={dates}
            selectedNightIndices={selectedNightIndices}
            setSelectedNightIndices={setSelectedNightIndicesHandler}
            setSelectedNights={setSelectedNightsHandler}
            hotelConfigurations={hotelConfigurations}
            activeHotelIndex={activeHotelIndex}
            setHotelConfigurations={setHotelConfigurations}
          />
        )}
        searchCriteria={searchCriteria}
      />
    </Box>
  );
} 
