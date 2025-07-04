import React, { useState, useEffect, useRef, useCallback, useMemo } from "react";
import { useDispatch } from "react-redux";
import {
  Box,
  Button,
  Container,
  Grid,
  Paper,
  Radio,
  RadioGroup,
  FormControlLabel,
  FormControl,
  Typography,
  useTheme,
  alpha,
} from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';

import LocationSearch from "./LocationSearch";
import {
  fetchVehicles,
  setentrypickup,
  setentrydropoff,
  setexitpickup,
  setentrytime,
  setentrytime1,
  //setexitdropoff,
  setpickupdate,
  setexitpickupdate,
  setSelectionType,
  setPickupPlaceid,
  setDropoffPlaceid,
  setpickdate,
  resetVehicles1,
  setSelectedPort,
  setDroptype,
  fetchZoneVehicles,
  setDropoffZoneid,
  setPickupZoneid,
  setZonetype,
} from "@/slice/localtour/Localslice";
import SearchBar1 from "./LocationSearch1";
import DateSearch1 from "@/components/activity-list/activity-list-v3/DateSearch1";
import DateSearch2 from "@/components/activity-list/activity-list-v3/DateSearch2";
import Pickuptime from "@/components/activity-single/filter-box3/Pickuptime";
import Pickuptime1 from "@/components/activity-single/filter-box4/Pickuptime1";
import { useSelector } from "react-redux";
import SearchZone from "@/components/Tour-Packages/Local-Transport/LocationZoneSearch";
import Pickuptimezone from "@/components/activity-list/activity-list-v3/Pickuptimezone";
import DateSearchZone from "@/components/activity-list/activity-list-v3/DateSearchZone";
import DisabledStateLayout from '../common/DisabledStateLayout';
import PickupDropDisabledLayout from '../common/PickupDropDisabledLayout';

const SearchLocationTransport = ({ Location, dayIndex = 0, date }) => {
  const dispatch = useDispatch();
  
  // Helper function to format date from Itinerary
  const formatItineraryDate = useCallback((itineraryDate) => {
    if (!itineraryDate) return "";
    
    // If it's a moment/dayjs object
    if (itineraryDate && itineraryDate.format) {
      return itineraryDate.format('YYYY-MM-DD');
    }
    
    // If it's already a string
    if (typeof itineraryDate === 'string') {
      return itineraryDate;
    }
    
    // If it's a Date object
    if (itineraryDate instanceof Date) {
      return itineraryDate.toISOString().split('T')[0];
    }
    
    return "";
  }, []);

  // Get values from Redux store to persist state
  const reduxPickUpLocation = useSelector((state) => state.localtour.entrypickup || "");
  const reduxDropOffLocation = useSelector((state) => state.localtour.entrydropoff || "");
  const reduxExitPickUpLocation = useSelector((state) => state.localtour.exitpickup || "");
  const reduxPickupDate = useSelector((state) => state.localtour.pickupdate || "");
  const reduxPickupDate1 = useSelector((state) => state.localtour.exitpickupdate || "");
  const reduxPickUpLatLng = useSelector((state) => state.localtour.PickupPlaceid || "");
  const reduxDropOffLatLng = useSelector((state) => state.localtour.DropoffPlaceid || "");
  const reduxEntryTime = useSelector((state) => state.localtour.entrytime || "");
  const reduxEntryTime1 = useSelector((state) => state.localtour.entrytime1 || "");
  const reduxEntryTimeZone = useSelector((state) => state.localtour.entrytime || "");
  const reduxPickUpZone = useSelector((state) => state.localtour.PickupZoneid || "");
  const reduxDropOffZone = useSelector((state) => state.localtour.DropoffZoneid || "");

  // Memoize the formatted date from Itinerary to prevent continuous recalculation
  const itineraryFormattedDate = useMemo(() => {
    return formatItineraryDate(date);
  }, [date, formatItineraryDate]);

  // Initialize all local state from Redux values to maintain consistency
  const [pickUpLocation, setPickUpLocation] = useState(reduxPickUpLocation);
  const [pickUpZone, setPickUpZone] = useState(reduxPickUpZone);
  

  const [dropOffLocation, setDropOffLocation] = useState(reduxDropOffLocation);
  const [dropOffzone, setDropOffZone] = useState(reduxDropOffZone);
  const [exitpickUpLocation, setexitPickUpLocation] = useState(reduxExitPickUpLocation);
  
  // Initialize date states with Itinerary date as priority, fallback to Redux
  const [selectedDate, setSelectedDate] = useState(itineraryFormattedDate || reduxPickupDate);
  const [selectedDate1, setSelectedDate1] = useState(itineraryFormattedDate || reduxPickupDate1);
  const [selectedDateZone, setSelectedDateZone] = useState(itineraryFormattedDate || reduxPickupDate || "");
  
  const selectedPort = useSelector((state) => state.localtour.selectedPort);
  const [pickUpLatLng, setPickupLatLng] = useState(reduxPickUpLatLng);
  const [dropOffLatLng, setDropoffLatLng] = useState(reduxDropOffLatLng);
  const [entryytime, setentryytime] = useState(reduxEntryTime);
  const [entryytime1, setentryytime1] = useState(reduxEntryTime1);
  const [entryytimezone, setentryytimezone] = useState(reduxEntryTimeZone);
  const zone_on = useSelector((state) => state.auth.zone_on);
  
  // Add state for validation
  const [validationTriggered, setValidationTriggered] = useState(false);

  // Track if locations were selected from autocomplete
  const [pickupFromAutocomplete, setPickupFromAutocomplete] = useState(false);
  const [dropoffFromAutocomplete, setDropoffFromAutocomplete] = useState(false);
  const [exitPickupFromAutocomplete, setExitPickupFromAutocomplete] = useState(false);

  const currentbooking = useSelector((state) => state.localtour.selectbooking);
  const picktype = useSelector((state) => state.localtour.picktype);
  const reduxDropType = useSelector((state) => state.localtour.droptype || "");
  const [droptype, setdroptype] = useState(reduxDropType);
  // Track if time is selected
  const [time, setTime] = useState(!!reduxEntryTime);
  const [time1, setTime1] = useState(!!reduxEntryTime1);
  const [timezone, setTimezone] = useState(!!reduxEntryTimeZone);
  const viewDetails = useSelector((state) => state.viewDetails.bookings);

  // Refs to track state changes and prevent infinite loops
  const isUpdatingFromRedux = useRef(false);
  const isUpdatingToRedux = useRef(false);
  const lastItineraryDate = useRef(itineraryFormattedDate);

  // Consolidated effect to sync FROM Redux TO local state (one-way)
  useEffect(() => {
    if (isUpdatingToRedux.current) return; // Prevent circular updates
    
    isUpdatingFromRedux.current = true;
    
    // Only update if values are different to prevent unnecessary renders
    if (reduxPickUpLocation !== pickUpLocation) {
      setPickUpLocation(reduxPickUpLocation);
    }
    if (reduxDropOffLocation !== dropOffLocation) {
      setDropOffLocation(reduxDropOffLocation);
    }
    if (reduxExitPickUpLocation !== exitpickUpLocation) {
      setexitPickUpLocation(reduxExitPickUpLocation);
    }
    if (reduxPickUpZone !== pickUpZone) {
      setPickUpZone(reduxPickUpZone);
    }
    if (reduxDropOffZone !== dropOffzone) {
      setDropOffZone(reduxDropOffZone);
    }
    if (reduxEntryTime !== entryytime) {
      setentryytime(reduxEntryTime);
    }
    if (reduxEntryTime1 !== entryytime1) {
      setentryytime1(reduxEntryTime1);
    }
    if (reduxEntryTimeZone !== entryytimezone) {
      setentryytimezone(reduxEntryTimeZone);
    }
    if (reduxDropType !== droptype) {
      setdroptype(reduxDropType);
    }
    
    // Handle lat/lng objects
    if (reduxPickUpLatLng && Object.keys(reduxPickUpLatLng).length > 0 && 
        (!pickUpLatLng || 
          pickUpLatLng.lat !== reduxPickUpLatLng.lat || 
          pickUpLatLng.lng !== reduxPickUpLatLng.lng)) {
      setPickupLatLng(reduxPickUpLatLng);
    }
    
    if (reduxDropOffLatLng && Object.keys(reduxDropOffLatLng).length > 0 && 
        (!dropOffLatLng || 
          dropOffLatLng.lat !== reduxDropOffLatLng.lat || 
          dropOffLatLng.lng !== reduxDropOffLatLng.lng)) {
      setDropoffLatLng(reduxDropOffLatLng);
    }
    
    setTimeout(() => {
      isUpdatingFromRedux.current = false;
    }, 100);
  }, [
    reduxPickUpLocation, reduxDropOffLocation, reduxExitPickUpLocation,
    reduxPickUpZone, reduxDropOffZone, reduxEntryTime, reduxEntryTime1, 
    reduxEntryTimeZone, reduxDropType, reduxPickUpLatLng, reduxDropOffLatLng
  ]);

  // Handle Itinerary date changes separately
  useEffect(() => {
    if (itineraryFormattedDate && itineraryFormattedDate !== lastItineraryDate.current) {
      console.log(`Day ${dayIndex + 1} - Updating date from Itinerary:`, itineraryFormattedDate);
      
      setSelectedDate(itineraryFormattedDate);
      setSelectedDate1(itineraryFormattedDate);
      setSelectedDateZone(itineraryFormattedDate);
      
      lastItineraryDate.current = itineraryFormattedDate;
    }
  }, [itineraryFormattedDate, dayIndex]);

  // Debounced dispatch function to prevent rapid Redux updates
  const debouncedDispatch = useRef(null);
  
  const dispatchToRedux = useCallback((updates) => {
    if (isUpdatingFromRedux.current) return; // Prevent circular updates
    
    // Clear previous timeout
    if (debouncedDispatch.current) {
      clearTimeout(debouncedDispatch.current);
    }
    
    // Debounce the dispatch
    debouncedDispatch.current = setTimeout(() => {
      isUpdatingToRedux.current = true;
      
      Object.entries(updates).forEach(([key, value]) => {
        if (value !== undefined && value !== null) {
          switch (key) {
            case 'pickUpLocation':
              if (value !== reduxPickUpLocation) dispatch(setentrypickup(value));
              break;
            case 'dropOffLocation':
              if (value !== reduxDropOffLocation) dispatch(setentrydropoff(value));
              break;
            case 'exitpickUpLocation':
              if (value !== reduxExitPickUpLocation) dispatch(setexitpickup(value));
              break;
            case 'pickUpZone':
              if (value !== reduxPickUpZone) {
                dispatch(setPickupZoneid(value));
              }
              break;
            case 'dropOffZone':
              if (value !== reduxDropOffZone) {
                dispatch(setDropoffZoneid(value));
              }
              break;
            case 'entryytime':
              if (value !== reduxEntryTime) dispatch(setentrytime(value));
              break;
            case 'entryytime1':
              if (value !== reduxEntryTime1) dispatch(setentrytime1(value));
              break;
            case 'entryytimezone':
              if (value !== reduxEntryTimeZone) {
                dispatch(setentrytime(value));
              }
              break;
            case 'droptype':
              if (value !== reduxDropType) dispatch(setDroptype(value));
              break;
            case 'selectedDate':
              if (value !== reduxPickupDate) dispatch(setpickdate(value));
              break;
            case 'selectedDate1':
              if (value !== reduxPickupDate1) dispatch(setexitpickupdate(value));
              break;
            case 'selectedDateZone':
              if (value !== reduxPickupDate) dispatch(setpickdate(value));
              break;
            case 'pickUpLatLng':
              if (value && Object.keys(value).length > 0 && value.lat !== undefined && value.lng !== undefined) {
                const latLng = { lat: value.lat, lng: value.lng };
                dispatch(setPickupPlaceid(latLng));
              }
              break;
            case 'dropOffLatLng':
              if (value && Object.keys(value).length > 0 && value.lat !== undefined && value.lng !== undefined) {
                const latLng = { lat: value.lat, lng: value.lng };
                dispatch(setDropoffPlaceid(latLng));
              }
              break;
          }
        }
      });
      
      setTimeout(() => {
        isUpdatingToRedux.current = false;
      }, 100);
    }, 300); // 300ms debounce
  }, [dispatch, reduxPickUpLocation, reduxDropOffLocation, reduxExitPickUpLocation, reduxPickUpZone, reduxDropOffZone, reduxEntryTime, reduxEntryTime1, reduxEntryTimeZone, reduxDropType, reduxPickupDate, reduxPickupDate1]);

  // Single effect to handle all local state changes and dispatch to Redux
  useEffect(() => {
    const updates = {};
    
    if (pickUpLocation) updates.pickUpLocation = pickUpLocation;
    if (dropOffLocation) updates.dropOffLocation = dropOffLocation;
    if (exitpickUpLocation) updates.exitpickUpLocation = exitpickUpLocation;
    if (pickUpZone) {
      updates.pickUpZone = pickUpZone;
    }
    if (dropOffzone) updates.dropOffZone = dropOffzone;
    if (entryytime) updates.entryytime = entryytime;
    if (entryytime1) updates.entryytime1 = entryytime1;
    if (entryytimezone) updates.entryytimezone = entryytimezone;
    if (droptype) updates.droptype = droptype;
    if (selectedDate) updates.selectedDate = selectedDate;
    if (selectedDate1) updates.selectedDate1 = selectedDate1;
    if (selectedDateZone) updates.selectedDateZone = selectedDateZone;
    if (pickUpLatLng && Object.keys(pickUpLatLng).length > 0 && pickUpLatLng.lat !== undefined && pickUpLatLng.lng !== undefined) {
      updates.pickUpLatLng = pickUpLatLng;
    }
    if (dropOffLatLng && Object.keys(dropOffLatLng).length > 0 && dropOffLatLng.lat !== undefined && dropOffLatLng.lng !== undefined) {
      updates.dropOffLatLng = dropOffLatLng;
    }
    
    if (Object.keys(updates).length > 0) {
      dispatchToRedux(updates);
    }
  }, [
    pickUpLocation, dropOffLocation, exitpickUpLocation, pickUpZone, dropOffzone,
    entryytime, entryytime1, entryytimezone, droptype, selectedDate, selectedDate1, 
    selectedDateZone, pickUpLatLng, dropOffLatLng, dispatchToRedux
  ]);
  
  // Custom handler for time selection
  const handleTimeSelection = (value) => {
    if(selectedPort === "Point To Point"){
      setentryytime(value);
    }
    else if(selectedPort === "Hourly"){
      setentryytime1(value);
    }
    else if(selectedPort === "Local Transfer"){
      setentryytimezone(value);
    }
  };
  
  const handleDateSelection = (date) => {
    // Check if date is a Moment object and convert to string
    if (date && date._isAMomentObject) {
      return date.format('YYYY-MM-DD');
    }
    return date;
  };
  
  const buttonsearch = () => {
    // Set validation triggered to true when search button is clicked
    setValidationTriggered(true);

    if (selectedPort === "Point To Point") {
      // Only proceed if both locations are selected from autocomplete
      const locationsValid = pickupFromAutocomplete && dropoffFromAutocomplete;
      
      console.log("Search conditions:", { 
        pickupFromAutocomplete, 
        dropoffFromAutocomplete,
        locationsValid,
        time,
        "Will call fetchVehicles": locationsValid && time
      });

      // Ensure the date is properly formatted
      const formattedDate = handleDateSelection(selectedDate);
      console.log("Point To Point search with date:", formattedDate);
      
      // Dispatch all necessary data
      const updates = {
        pickUpLocation,
        dropOffLocation,
        entryytime,
        selectedDate: formattedDate
      };
      
      dispatchToRedux(updates);
      dispatch(setSelectionType(selectedPort));
      
      // Check if pickUpLatLng and dropOffLatLng have valid values
      if (!pickUpLatLng || !pickUpLatLng.lat || !pickUpLatLng.lng) {
        console.error("Invalid pickup location coordinates. Please select a location from the dropdown.");
        setPickupFromAutocomplete(false);
        return;
      }
      
      if (!dropOffLatLng || !dropOffLatLng.lat || !dropOffLatLng.lng) {
        console.error("Invalid dropoff location coordinates. Please select a location from the dropdown.");
        setDropoffFromAutocomplete(false);
        return;
      }
      
      // If we have valid coordinates, consider the locations as valid from autocomplete
      setPickupFromAutocomplete(true);
      setDropoffFromAutocomplete(true);
      
      console.log("Search button clicked - lat/lng values:", {
        pickUpLatLng,
        dropOffLatLng
      });
      
      dispatch(setZonetype(""));

      // Check if time is selected and valid
      if (!entryytime) {
        console.error("Please select a pickup time");
        setTime(false);
        return;
      } else {
        setTime(true);
      }

      // Only fetch vehicles if locations and time are valid
      if (pickUpLatLng && dropOffLatLng && entryytime) {
        console.log("All conditions met, fetching vehicles...");
        setTimeout(() => {
          dispatch(fetchVehicles());
        }, 500);
      } else {
        console.error("Cannot fetch vehicles: missing required fields", {
          pickUpLatLng: !!pickUpLatLng,
          dropOffLatLng: !!dropOffLatLng,
          entryytime: !!entryytime
        });
      }
    } else if (selectedPort === "Hourly") {
      // Only proceed if pickup location is selected from autocomplete
      const locationValid = exitPickupFromAutocomplete;

      // Ensure the date is properly formatted
      const formattedDate = handleDateSelection(selectedDate1);
      console.log("Hourly search with date:", formattedDate);

      console.log("Hourly Search conditions:", { 
        exitPickupFromAutocomplete,
        locationValid,
        time1,
        pickUpLatLng,
        "Will call fetchVehicles": locationValid && time1
      });

      // Dispatch all necessary data
      const updates = {
        exitpickUpLocation,
        entryytime1,
        selectedDate1: formattedDate
      };
      
      dispatchToRedux(updates);
      dispatch(setSelectionType(selectedPort));
      
      // Check if pickUpLatLng has valid values
      if (!pickUpLatLng || !pickUpLatLng.lat || !pickUpLatLng.lng) {
        console.error("Invalid pickup location coordinates for Hourly mode. Please select a location from the dropdown.");
        setExitPickupFromAutocomplete(false);
        return;
      }
      
      // If we have valid coordinates, consider the location as valid from autocomplete
      setExitPickupFromAutocomplete(true);
      
      dispatch(setDropoffPlaceid(null));
      dispatch(setZonetype(""));

      // Check if time is selected and valid
      if (!entryytime1) {
        console.error("Please select a pickup time for Hourly mode");
        setTime1(false);
        return;
      } else {
        setTime1(true);
      }

      // Only fetch vehicles if location and time are valid
      if (pickUpLatLng && pickUpLatLng.lat && pickUpLatLng.lng && entryytime1) {
        console.log("All Hourly conditions met, fetching vehicles...");
        setTimeout(() => {
          dispatch(fetchVehicles());
        }, 500);
      } else {
        console.error("Cannot fetch vehicles for Hourly mode: missing required fields", {
          pickUpLatLng: !!pickUpLatLng,
          "pickUpLatLng.lat": pickUpLatLng?.lat,
          "pickUpLatLng.lng": pickUpLatLng?.lng,
          entryytime1: !!entryytime1
        });
      }
    } else if (selectedPort === "Local Transfer") {
      // Ensure the date is properly formatted
      const formattedDate = handleDateSelection(selectedDateZone);
      console.log("Local Transfer search with date:", formattedDate);
      
      // Dispatch all necessary data
      const updates = {
        pickUpZone,
        dropOffZone: dropOffzone,
        entryytimezone,
        selectedDateZone: formattedDate,
        droptype
      };
      
      console.log("Dispatching Local Transfer updates:", updates);
      dispatchToRedux(updates);
      dispatch(setSelectionType(selectedPort));
      dispatch(setentrypickup(pickUpLatLng));
      dispatch(setentrydropoff(dropOffLatLng));
      dispatch(setZonetype("zone"));

      // Debug log all values before checking
      console.log("Local Transfer API call check:");
      console.log("pickUpZone:", pickUpZone);
      console.log("dropOffzone:", dropOffzone);
      console.log("entryytimezone:", entryytimezone);
      console.log("selectedDateZone:", selectedDateZone);
      console.log("droptype:", droptype);
      
      // Check if we have valid values for the API call
      if (
        pickUpZone &&
        dropOffzone &&
        entryytimezone &&
        selectedDateZone &&
        droptype
      ) {
        console.log("✅ All conditions met! Calling fetchZoneVehicles with droptype:", droptype);
        setTimeout(() => {
          dispatch(fetchZoneVehicles());
        }, 500);
      } else {
        console.log("❌ Missing required fields for Local Transfer search:", {
          pickUpZone: pickUpZone || "MISSING",
          dropOffzone: dropOffzone || "MISSING",
          entryytimezone: entryytimezone || "MISSING",
          selectedDateZone: selectedDateZone || "MISSING",
          droptype: droptype || "MISSING",
        });
      }
    }
  };
  // Cleanup timeout on unmount
  useEffect(() => {
    return () => {
      if (debouncedDispatch.current) {
        clearTimeout(debouncedDispatch.current);
      }
    };
  }, []);

  const theme = useTheme();

  return (
    <Paper 
      elevation={0}
      sx={{
        borderRadius: 3,
        bgcolor: 'white',
        p: { xs: 2, md: 3 },
        mt: 2,
        overflow: 'visible',
        position: 'relative',
        zIndex: 1,
      }}
    >
      <Box sx={{ width: '100%', maxWidth: 1730, overflow: 'visible', position: 'relative', zIndex: 1 }}>
        {/* Radio Button Selection */}
        <Box sx={{ display: 'flex', justifyContent: 'center', mb: 3 }}>
          <FormControl component="fieldset">
            <RadioGroup
              row
              value={selectedPort || ""}
              onChange={(e) => {
                dispatch(setSelectedPort(e.target.value));
                dispatch(resetVehicles1());
                setValidationTriggered(false);
              }}
              sx={{ gap: 2 }}
            >
              <FormControlLabel
                value="Point To Point"
                control={
                  <Radio
                    sx={{
                      color: '#e0e0e0',
                      '&.Mui-checked': {
                        color: '#ff6b6b',
                      },
                    }}
                  />
                }
                label={
                  <Typography 
                    variant="body2" 
                    fontWeight={selectedPort === "Point To Point" ? 600 : 400}
                    color={selectedPort === "Point To Point" ? '#ff6b6b' : 'text.primary'}
                  >
                    Point To Point
                  </Typography>
                }
                sx={{
                  border: `2px solid ${selectedPort === "Point To Point" ? '#ff6b6b' : '#e0e0e0'}`,
                  borderRadius: 2,
                  px: 2,
                  py: 0.5,
                  m: 0,
                  bgcolor: selectedPort === "Point To Point" ? alpha('#ff6b6b', 0.05) : 'transparent',
                  '&:hover': {
                    borderColor: '#ff6b6b',
                    bgcolor: alpha('#ff6b6b', 0.05),
                  },
                  transition: 'all 0.3s ease',
                }}
              />
              <FormControlLabel
                value="Hourly"
                control={
                  <Radio
                    sx={{
                      color: '#e0e0e0',
                      '&.Mui-checked': {
                        color: '#ff6b6b',
                      },
                    }}
                  />
                }
                label={
                  <Typography 
                    variant="body2" 
                    fontWeight={selectedPort === "Hourly" ? 600 : 400}
                    color={selectedPort === "Hourly" ? '#ff6b6b' : 'text.primary'}
                  >
                    Hourly
                  </Typography>
                }
                sx={{
                  border: `2px solid ${selectedPort === "Hourly" ? '#ff6b6b' : '#e0e0e0'}`,
                  borderRadius: 2,
                  px: 2,
                  py: 0.5,
                  m: 0,
                  bgcolor: selectedPort === "Hourly" ? alpha('#ff6b6b', 0.05) : 'transparent',
                  '&:hover': {
                    borderColor: '#ff6b6b',
                    bgcolor: alpha('#ff6b6b', 0.05),
                  },
                  transition: 'all 0.3s ease',
                }}
              />
              {zone_on === 1 && (
                <FormControlLabel
                  value="Local Transfer"
                  control={
                    <Radio
                      sx={{
                        color: '#e0e0e0',
                        '&.Mui-checked': {
                          color: '#ff6b6b',
                        },
                      }}
                    />
                  }
                  label={
                    <Typography 
                      variant="body2" 
                      fontWeight={selectedPort === "Local Transfer" ? 600 : 400}
                      color={selectedPort === "Local Transfer" ? '#ff6b6b' : 'text.primary'}
                    >
                      Local Transfer
                    </Typography>
                  }
                  sx={{
                    border: `2px solid ${selectedPort === "Local Transfer" ? '#ff6b6b' : '#e0e0e0'}`,
                    borderRadius: 2,
                    px: 2,
                    py: 0.5,
                    m: 0,
                    bgcolor: selectedPort === "Local Transfer" ? alpha('#ff6b6b', 0.05) : 'transparent',
                    '&:hover': {
                      borderColor: '#ff6b6b',
                      bgcolor: alpha('#ff6b6b', 0.05),
                    },
                    transition: 'all 0.3s ease',
                  }}
                />
              )}
            </RadioGroup>
          </FormControl>
        </Box>

        {selectedPort ? (
          <>
            {/* Form Fields Row */}
            <Grid container spacing={2} alignItems="flex-end" sx={{ mb: 3 }}>
              {/* Location Search */}
              <Grid item xs={12} md={selectedPort === "Point To Point" ? 6 : selectedPort === "Local Transfer" ? 6 : 4}>
                <Box>
                  {selectedPort === "Point To Point" ? (
                    <LocationSearch
                      pickUpLocation={pickUpLocation}
                      setPickUpLocation={setPickUpLocation}
                      dropOffLocation={dropOffLocation}
                      setDropOffLocation={setDropOffLocation}
                      pickUpLatLng={pickUpLatLng}
                      setPickupLatLng={setPickupLatLng}
                      dropOffLatLng={dropOffLatLng}
                      setDropoffLatLng={setDropoffLatLng}
                      Location={Location}
                      validationTriggered={validationTriggered}
                      setPickupFromAutocomplete={setPickupFromAutocomplete}
                      setDropoffFromAutocomplete={setDropoffFromAutocomplete}
                      pickupFromAutocomplete={pickupFromAutocomplete}
                      dropoffFromAutocomplete={dropoffFromAutocomplete}
                      dayIndex={dayIndex}
                    />
                  ) : selectedPort === "Hourly" ? (
                    <SearchBar1
                      exitpickUpLocation={exitpickUpLocation}
                      setexitPickUpLocation={setexitPickUpLocation}
                      pickUpLatLng={pickUpLatLng}
                      setPickupLatLng={setPickupLatLng}
                      Location={Location}
                      validationTriggered={validationTriggered}
                      setPickupFromAutocomplete={setExitPickupFromAutocomplete}
                      pickupFromAutocomplete={exitPickupFromAutocomplete}
                      dayIndex={dayIndex}
                    />
                  ) : selectedPort === "Local Transfer" ? (
                    <SearchZone
                      currentbooking={currentbooking}
                      picktype={picktype}
                      setdroptype={setdroptype}
                      droptype={droptype}
                      setPickUpLocation={setPickUpZone}
                      setPickupLatLng={setPickupLatLng}
                      setDropoffLatLng={setDropoffLatLng}
                      dropOffLocation={dropOffLatLng}
                      validationTriggered={validationTriggered}
                      setDropOffLocation={setDropOffZone}
                      dayIndex={dayIndex}
                    />
                  ) : (
                    <Box>
                      <LocationSearch
                        pickUpLocation={pickUpLocation}
                        setPickUpLocation={setPickUpLocation}
                        dropOffLocation={dropOffLocation}
                        setDropOffLocation={setDropOffLocation}
                        pickUpLatLng={pickUpLatLng}
                        setPickupLatLng={setPickupLatLng}
                        dropOffLatLng={dropOffLatLng}
                        setDropoffLatLng={setDropoffLatLng}
                        Location={Location}
                        validationTriggered={validationTriggered}
                        setPickupFromAutocomplete={setPickupFromAutocomplete}
                        setDropoffFromAutocomplete={setDropoffFromAutocomplete}
                        pickupFromAutocomplete={pickupFromAutocomplete}
                        dropoffFromAutocomplete={dropoffFromAutocomplete}
                        dayIndex={dayIndex}
                        disabled={true}
                      />
                    </Box>
                  )}
                </Box>
              </Grid>

              {/* Time Selection */}
              <Grid item xs={12} md={3}>
                <Box sx={{ mt: (selectedPort === "Point To Point" || selectedPort === "Hourly") ? -12 : 0 }}>
                  {selectedPort === "Point To Point" ? (
                    <Pickuptime
                      entryytime={entryytime}
                      setentryytime={handleTimeSelection}
                      setTime={setTime}
                    />
                  ) : selectedPort === "Hourly" ? (
                    <Pickuptime1
                      entryytime={entryytime1}
                      setentryytime={handleTimeSelection}
                      setTime={setTime1}
                    />
                  ) : selectedPort === "Local Transfer" ? (
                    <Pickuptimezone
                      entryytime={entryytimezone}
                      setentryytime={(value) => {
                        setentryytimezone(value);
                        setTimezone(!!value);
                      }}
                      setTime={setTimezone}
                    />
                  ) : selectedPort && (
                    <Pickuptimezone
                      entryytime={entryytimezone}
                      setentryytime={setentryytimezone}
                      setTime={setTimezone}
                    />
                  )}
                </Box>
              </Grid>

              {/* Date Selection */}
              <Grid item xs={12} md={3}>
                <Box sx={{ mt: (selectedPort === "Point To Point" || selectedPort === "Hourly") ? -12 : 0 }}>
                  <Typography variant="subtitle2" fontWeight={600} sx={{ mb: 1, color: 'text.primary' }}>
                    Pick Up Date
                  </Typography>
                  {selectedPort === "Point To Point" ? (
                    <DateSearch1
                      selectedDate={selectedDate}
                      setSelectedDate={(date) => {
                        console.log("Selected Pickup Date:", date);
                        if (date && date._isAMomentObject) {
                          const formattedDate = date.format('YYYY-MM-DD');
                          console.log("Formatted date:", formattedDate);
                          setSelectedDate(formattedDate);
                        } else {
                          setSelectedDate(date);
                        }
                      }}
                    />
                  ) : selectedPort === "Local Transfer" ? (
                    <DateSearchZone
                      selectedDate1={selectedDateZone}
                      setSelectedDate1={(date) => {
                        if (date && date._isAMomentObject) {
                          const formattedDate = date.format('YYYY-MM-DD');
                          console.log("Formatted zone date:", formattedDate);
                          setSelectedDateZone(formattedDate);
                        } else {
                          setSelectedDateZone(date);
                        }
                      }}
                    />
                  ) : (
                    <DateSearch2
                      selectedDate1={selectedDate1}
                      setSelectedDate1={(date) => {
                        if (date && date._isAMomentObject) {
                          const formattedDate = date.format('YYYY-MM-DD');
                          console.log("Formatted exit date:", formattedDate);
                          setSelectedDate1(formattedDate);
                        } else {
                          setSelectedDate1(date);
                        }
                      }}
                    />
                  )}
                </Box>
              </Grid>
            </Grid>

            {/* Search Button Row */}
            <Box sx={{ display: 'flex', justifyContent: 'center', width: '100%' }}>
              <Button
                variant="contained"
                size="large"
                startIcon={<SearchIcon />}
                onClick={buttonsearch}
                sx={{
                  minWidth: 200,
                  px: 4,
                  py: 1.5,
                  borderRadius: 2,
                  background: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
                  fontSize: '1rem',
                  fontWeight: 600,
                  textTransform: 'none',
                  boxShadow: '0 4px 12px rgba(255, 107, 107, 0.3)',
                  '&:hover': {
                    background: 'linear-gradient(135deg, #ee5a24 0%, #ff6b6b 100%)',
                    boxShadow: '0 6px 16px rgba(255, 107, 107, 0.4)',
                    transform: 'translateY(-1px)',
                  },
                  transition: 'all 0.3s ease',
                }}
              >
                Search
              </Button>
            </Box>
          </>
        ) : (
          <DisabledStateLayout 
            message="Please select a service type to continue"
            showLocationFields={true}
            showTimeField={true}
            showDateField={true}
          />
        )}
      </Box>
    </Paper>
  );
}; 

export default SearchLocationTransport;
