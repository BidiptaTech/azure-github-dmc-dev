import React, { useState, useEffect, useRef } from "react";
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

const SearchLocationTransport = ({ Location, dayIndex = 0 }) => {
  const dispatch = useDispatch();

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
  const reduxEntryTimeZone = useSelector((state) => state.localtour.entrytimezone || "");
  const reduxPickUpZone = useSelector((state) => state.localtour.PickupZoneid || "");
  const reduxDropOffZone = useSelector((state) => state.localtour.DropoffZoneid || "");

  // State for storing the pickup and dropoff locations
  const [pickUpLocation, setPickUpLocation] = useState(reduxPickUpLocation);
  const [pickUpZone, setPickUpZone] = useState(reduxPickUpZone);
  const [dropOffLocation, setDropOffLocation] = useState(reduxDropOffLocation);
  const [dropOffzone, setDropOffZone] = useState(reduxDropOffZone);
  const [exitpickUpLocation, setexitPickUpLocation] = useState(reduxExitPickUpLocation);
  const [selectedDate, setSelectedDate] = useState(reduxPickupDate);
  const [selectedDate1, setSelectedDate1] = useState(reduxPickupDate1);
  const [selectedDateZone, setSelectedDateZone] = useState(reduxPickupDate || "");
  const selectedPort = useSelector((state) => state.localtour.selectedPort);
  const [pickUpLatLng, setPickupLatLng] = useState(reduxPickUpLatLng);
  const [dropOffLatLng, setDropoffLatLng] = useState(reduxDropOffLatLng);
  const [entryytime, setentryytime] = useState(reduxEntryTime);
  const [entryytime1, setentryytime1] = useState(reduxEntryTime1);
  const [entryytimezone, setentryytimezone] = useState(reduxEntryTimeZone);
  const zone_on = useSelector((state) => state.auth.zone_on);

  // Log Redux date values when component mounts
  useEffect(() => {
    console.log("Initial Redux date values:", { 
      reduxPickupDate, 
      reduxPickupDate1,
      selectedDate,
      selectedDate1,
      selectedDateZone
    });
  }, []);

  // Add state for validation
  const [validationTriggered, setValidationTriggered] = useState(false);

  // Track if locations were selected from autocomplete
  const [pickupFromAutocomplete, setPickupFromAutocomplete] = useState(false);
  const [dropoffFromAutocomplete, setDropoffFromAutocomplete] = useState(false);
  const [exitPickupFromAutocomplete, setExitPickupFromAutocomplete] =
    useState(false);

  const currentbooking = useSelector((state) => state.localtour.selectbooking);
  const picktype = useSelector((state) => state.localtour.picktype);
  const reduxDropType = useSelector((state) => state.localtour.droptype || "");
  const [droptype, setdroptype] = useState(reduxDropType);
  // Track if time is selected
  const [time, setTime] = useState(!!reduxEntryTime);
  const [time1, setTime1] = useState(!!reduxEntryTime1);
  const [timezone, setTimezone] = useState(!!reduxEntryTimeZone);
  const viewDetails = useSelector((state) => state.viewDetails.bookings);

  // Add imports for useRef
  const prevPickUpLocationRef = useRef(reduxPickUpLocation);
  const prevDropOffLocationRef = useRef(reduxDropOffLocation);
  const prevPickUpLatLngRef = useRef(reduxPickUpLatLng);
  const prevDropOffLatLngRef = useRef(reduxDropOffLatLng);
  const prevExitPickUpLocationRef = useRef(reduxExitPickUpLocation);
  const prevSelectedDateRef = useRef(reduxPickupDate);
  const prevSelectedDate1Ref = useRef(reduxPickupDate1);
  const prevSelectedDateZoneRef = useRef(reduxPickupDate || "");
  
  // Sync Redux state with local state when the component mounts or Redux values change
  useEffect(() => {
    // Use refs to track if we're in the middle of a sync operation
    const isInitialSync = !pickUpLocation && !dropOffLocation;
    
    if (reduxPickUpLocation && (isInitialSync || reduxPickUpLocation !== pickUpLocation)) {
      console.log("Syncing from Redux: pickUpLocation", reduxPickUpLocation);
      setPickUpLocation(reduxPickUpLocation);
    }
    
    if (reduxDropOffLocation && (isInitialSync || reduxDropOffLocation !== dropOffLocation)) {
      console.log("Syncing from Redux: dropOffLocation", reduxDropOffLocation);
      setDropOffLocation(reduxDropOffLocation);
    }

    // Also sync lat/lng values from Redux
    if (reduxPickUpLatLng && Object.keys(reduxPickUpLatLng).length > 0 && 
        (!pickUpLatLng || 
          pickUpLatLng.lat !== reduxPickUpLatLng.lat || 
          pickUpLatLng.lng !== reduxPickUpLatLng.lng)) {
      console.log("Syncing from Redux: pickUpLatLng", reduxPickUpLatLng);
      setPickupLatLng(reduxPickUpLatLng);
    }
    
    if (reduxDropOffLatLng && Object.keys(reduxDropOffLatLng).length > 0 && 
        (!dropOffLatLng || 
          dropOffLatLng.lat !== reduxDropOffLatLng.lat || 
          dropOffLatLng.lng !== reduxDropOffLatLng.lng)) {
      console.log("Syncing from Redux: dropOffLatLng", reduxDropOffLatLng);
      setDropoffLatLng(reduxDropOffLatLng);
    }
  }, [reduxPickUpLocation, reduxDropOffLocation, reduxPickUpLatLng, reduxDropOffLatLng]);

  useEffect(() => {
    const isInitialSync = !exitpickUpLocation;
    if(exitpickUpLocation && (isInitialSync || exitpickUpLocation !== reduxExitPickUpLocation)){
      console.log("Syncing from Redux: exitpickUpLocation", exitpickUpLocation);
      setexitPickUpLocation(reduxExitPickUpLocation);
    }
  }, [reduxExitPickUpLocation]);

  // Sync date values from Redux
  useEffect(() => {
    if (reduxPickupDate && reduxPickupDate !== prevSelectedDateRef.current) {
      console.log("Syncing from Redux: pickupDate", reduxPickupDate);
      setSelectedDate(reduxPickupDate);
      prevSelectedDateRef.current = reduxPickupDate;
    }
  }, [reduxPickupDate]);

  useEffect(() => {
    if (reduxPickupDate1 && reduxPickupDate1 !== prevSelectedDate1Ref.current) {
      console.log("Syncing from Redux: exitPickupDate", reduxPickupDate1);
      setSelectedDate1(reduxPickupDate1);
      prevSelectedDate1Ref.current = reduxPickupDate1;
    }
  }, [reduxPickupDate1]);

  // Update Redux state when local state changes
  useEffect(() => {
    // Only dispatch if the value has actually changed from what's in Redux
    if (pickUpLocation && pickUpLocation !== prevPickUpLocationRef.current) {
      prevPickUpLocationRef.current = pickUpLocation;
      dispatch(setentrypickup(pickUpLocation));
    }
  }, [pickUpLocation, dispatch]);
  
  useEffect(() => {
    if (exitpickUpLocation && exitpickUpLocation !== prevExitPickUpLocationRef.current) {
      prevExitPickUpLocationRef.current = exitpickUpLocation;
      dispatch(setexitpickup(exitpickUpLocation));
    }
  }, [exitpickUpLocation, dispatch]);
  
  useEffect(() => {
    // Only dispatch if the value has actually changed from what's in Redux
    if (dropOffLocation && dropOffLocation !== prevDropOffLocationRef.current) {
      prevDropOffLocationRef.current = dropOffLocation;
      dispatch(setentrydropoff(dropOffLocation));
    }
  }, [dropOffLocation, dispatch]);

  // Update Redux state when date values change
  useEffect(() => {
    if (selectedDate && selectedDate !== prevSelectedDateRef.current) {
      prevSelectedDateRef.current = selectedDate;
      console.log("Dispatching pickupdate to Redux:", selectedDate);
      dispatch(setpickdate(selectedDate));
    }
  }, [selectedDate, dispatch]);

  useEffect(() => {
    if (selectedDate1 && selectedDate1 !== prevSelectedDate1Ref.current) {
      prevSelectedDate1Ref.current = selectedDate1;
      console.log("Dispatching exitpickupdate to Redux:", selectedDate1);
      dispatch(setexitpickupdate(selectedDate1));
    }
  }, [selectedDate1, dispatch]);

  useEffect(() => {
    if (selectedDateZone && selectedDateZone !== prevSelectedDateZoneRef.current) {
      prevSelectedDateZoneRef.current = selectedDateZone;
      console.log("Dispatching zone date to Redux:", selectedDateZone);
      dispatch(setpickdate(selectedDateZone));
    }
  }, [selectedDateZone, dispatch]);
  
  // Update Redux state when lat/lng values change
  useEffect(() => {
    if (pickUpLatLng && Object.keys(pickUpLatLng).length > 0 && 
        (!prevPickUpLatLngRef.current ||
         prevPickUpLatLngRef.current.lat !== pickUpLatLng.lat ||
         prevPickUpLatLngRef.current.lng !== pickUpLatLng.lng)) {
      
      prevPickUpLatLngRef.current = pickUpLatLng;
      const pickUpLatLng1 = {
        lat: pickUpLatLng.lat,
        lng: pickUpLatLng.lng
      }
      dispatch(setPickupPlaceid(pickUpLatLng1));
    }
  }, [pickUpLatLng, dispatch]);
  
  useEffect(() => {
    if (dropOffLatLng && Object.keys(dropOffLatLng).length > 0 && 
        (!prevDropOffLatLngRef.current ||
         prevDropOffLatLngRef.current.lat !== dropOffLatLng.lat ||
         prevDropOffLatLngRef.current.lng !== dropOffLatLng.lng)) {
      
      prevDropOffLatLngRef.current = dropOffLatLng;
      const dropOffLatLng1 = {
        lat: dropOffLatLng.lat,
        lng: dropOffLatLng.lng
      }
      dispatch(setDropoffPlaceid(dropOffLatLng1));
    }
  }, [dropOffLatLng, dispatch]);
  
  // Custom handler for pickup location change that updates both name and latlng in Redux
  const handleLocationChange = () => {
    // Only update Redux if we have valid data that differs from what's already in Redux
    if (pickUpLocation && pickUpLatLng && 
        (pickUpLocation !== prevPickUpLocationRef.current || 
         !prevPickUpLatLngRef.current || 
         prevPickUpLatLngRef.current.lat !== pickUpLatLng.lat || 
         prevPickUpLatLngRef.current.lng !== pickUpLatLng.lng)) {
      
      prevPickUpLocationRef.current = pickUpLocation;
      prevPickUpLatLngRef.current = pickUpLatLng;
      
      dispatch(setentrypickup(pickUpLocation));

      const pickUpLatLng1 = {
        lat: pickUpLatLng.lat,
        lng: pickUpLatLng.lng
      } 
      dispatch(setPickupPlaceid(pickUpLatLng1));
    }
    if (exitpickUpLocation && pickUpLatLng && 
        (exitpickUpLocation !== prevExitPickUpLocationRef.current || 
         !prevExitPickUpLocationRef.current || 
         prevExitPickUpLocationRef.current.lat !== pickUpLatLng.lat || 
         prevExitPickUpLocationRef.current.lng !== pickUpLatLng.lng)) {
      prevExitPickUpLocationRef.current = exitpickUpLocation;
      dispatch(setexitpickup(exitpickUpLocation));
    }
    
    if (dropOffLocation && dropOffLatLng && 
        (dropOffLocation !== prevDropOffLocationRef.current || 
         !prevDropOffLatLngRef.current ||
         prevDropOffLatLngRef.current.lat !== dropOffLatLng.lat || 
         prevDropOffLatLngRef.current.lng !== dropOffLatLng.lng)) {
      
      prevDropOffLocationRef.current = dropOffLocation;
      prevDropOffLatLngRef.current = dropOffLatLng;
      
      dispatch(setentrydropoff(dropOffLocation));
      const dropOffLatLng1 = {
        lat: dropOffLatLng.lat,
        lng: dropOffLatLng.lng
      }
      dispatch(setDropoffPlaceid(dropOffLatLng1));
    }
  };
  
  // Ensure location values are persisted when Pickuptime changes
  const handleTimeSelection = (value) => {
    // Use conditional statements to update only the relevant time state
    if(selectedPort === "Point To Point"){
      setentryytime(value);
    }
    else if(selectedPort === "Hourly"){
      setentryytime1(value);
    }
    else if(selectedPort === "Local Transfer"){
      setentryytimezone(value);
    }
    
    // No need to manually trigger handleLocationChange here
    // Let the state update naturally when the time is selected
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
      
      dispatch(setentrypickup(pickUpLocation));
      dispatch(setentrydropoff(dropOffLocation));
      dispatch(setentrytime(entryytime));
      dispatch(setpickdate(formattedDate));
      dispatch(setSelectionType(selectedPort));
      
      // Check if pickUpLatLng and dropOffLatLng have valid values
      if (!pickUpLatLng || !pickUpLatLng.lat || !pickUpLatLng.lng) {
        console.error("Invalid pickup location coordinates. Please select a location from the dropdown.");
        // Force pickupFromAutocomplete to false to show validation message
        setPickupFromAutocomplete(false);
        return;
      }
      
      if (!dropOffLatLng || !dropOffLatLng.lat || !dropOffLatLng.lng) {
        console.error("Invalid dropoff location coordinates. Please select a location from the dropdown.");
        // Force dropoffFromAutocomplete to false to show validation message
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
      
      const pickUpLatLng1 = {
        lat: pickUpLatLng.lat,
        lng: pickUpLatLng.lng
      };
      
      const dropOffLatLng1 = {
        lat: dropOffLatLng.lat,
        lng: dropOffLatLng.lng
      };
      
      dispatch(setPickupPlaceid(pickUpLatLng1));
      dispatch(setDropoffPlaceid(dropOffLatLng1));
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

      dispatch(setexitpickup(exitpickUpLocation));
      dispatch(setpickdate(formattedDate));
      dispatch(setexitpickupdate(formattedDate));
      dispatch(setentrytime(entryytime1));
      dispatch(setentrytime1(entryytime1));
      dispatch(setSelectionType(selectedPort));
      
      // Check if pickUpLatLng has valid values
      if (!pickUpLatLng || !pickUpLatLng.lat || !pickUpLatLng.lng) {
        console.error("Invalid pickup location coordinates for Hourly mode. Please select a location from the dropdown.");
        setExitPickupFromAutocomplete(false);
        return;
      }
      
      // If we have valid coordinates, consider the location as valid from autocomplete
      setExitPickupFromAutocomplete(true);
      
      const pickUpLatLng1 = {
        lat: pickUpLatLng.lat,
        lng: pickUpLatLng.lng
      };
      
      dispatch(setPickupPlaceid(pickUpLatLng1));
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
      // Only proceed if both locations are selected from autocomplete
      // Ensure the date is properly formatted
      const formattedDate = handleDateSelection(selectedDateZone);
      console.log("Local Transfer search with date:", formattedDate);
      
      dispatch(setPickupZoneid(pickUpZone));
      dispatch(setDropoffZoneid(dropOffzone));
      dispatch(setentrypickup(pickUpLatLng));
      dispatch(setentrydropoff(dropOffLatLng));
      dispatch(setSelectionType(selectedPort));
      dispatch(setDroptype(droptype));
      dispatch(setentrytime(entryytimezone));
      dispatch(setpickdate(formattedDate));
      dispatch(setZonetype("zone"));

      // Check if we have valid values for the API call
      if (
        pickUpZone &&
        dropOffzone &&
        entryytimezone &&
        selectedDateZone &&
        droptype
      ) {
        console.log("Local Transfer search with droptype:", droptype);
        setTimeout(() => {
          dispatch(fetchZoneVehicles());
        }, 500);
      } else {
        console.log("Missing required fields for Local Transfer search:", {
          pickUpLocation,
          dropOffLocation,
          entryytimezone,
          selectedDateZone,
          droptype,
        });
      }
    }
  };

    const theme = useTheme();

  return (
    <Paper 
      elevation={0}
      sx={{
        borderRadius: 3,
        bgcolor: 'white',
        p: { xs: 2, md: 3 },
        mt: 2,
      }}
    >
      <Box sx={{ width: '100%', maxWidth: 1730 }}>
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
                <Box sx={{ textAlign: 'center', py: 4 }}>
                  {/* <Typography variant="body2" color="text.secondary">
                    Please select a journey type first
                  </Typography> */}
                </Box>
              )}
            </Box>
          </Grid>

          {/* Time Selection */}
          <Grid item xs={12} md={3}>
            <Box>
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
                  setentryytime={setentryytimezone}
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
            <Box>
              <Typography variant="subtitle2" fontWeight={600} sx={{ mb: 1, color: 'text.primary' }}>
                {selectedPort === "Point To Point" ? "Pick Up Date" : "Exit Date"}
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
                      dispatch(setpickdate(formattedDate));
                    } else {
                      setSelectedDate(date);
                      dispatch(setpickdate(date));
                    }
                    handleLocationChange();
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
                      dispatch(setpickdate(formattedDate));
                    } else {
                      setSelectedDateZone(date);
                      dispatch(setpickdate(date));
                    }
                    handleLocationChange();
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
                      dispatch(setexitpickupdate(formattedDate));
                      dispatch(setpickdate(formattedDate));
                    } else {
                      setSelectedDate1(date);
                      dispatch(setexitpickupdate(date));
                      dispatch(setpickdate(date));
                    }
                    handleLocationChange();
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
      </Box>
    </Paper>
  );
}; 

export default SearchLocationTransport;
