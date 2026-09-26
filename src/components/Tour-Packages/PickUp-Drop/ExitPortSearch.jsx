import React, { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import SearchBar from "./LocationSearch1";
import {
  fetchVehicles,
  setexitpickup,
  setexitdropoff,
  setpickupdate,
  setentrytime,
  setexittime,
  setSelectionType,
  setPickupPlaceid1,
  setDropoffPlaceid1,
  setPortZoneType,
} from "@/slice/port/pickupDropSlice";
import DateSearch2 from "@/components/activity-list/common/DateSearch2";
import Pickuptime1 from "./Pickuptime1";
import {
  Box,
  Button,
  Paper,
  Grid,
  Typography,
  useTheme,
  alpha,
} from '@mui/material';
import SearchIcon from "@mui/icons-material/Search";

const ExitPortSearch = ({ Location }) => {
  const theme = useTheme();
  const dispatch = useDispatch();
  
  // Log the location prop to debug
  
  
  // Get values from Redux store to persist state
  const reduxExitPickUpLocation = useSelector((state) => state.pickupDrop.exitpickup || "");
  const reduxExitDropOffLocation = useSelector((state) => state.pickupDrop.exitdropoff || "");
  const reduxPickupDate = useSelector((state) => state.pickupDrop.pickupdate || "");
  const reduxExitTime = useSelector((state) => state.pickupDrop.exittime || "");
  const reduxPickUpLatLng = useSelector((state) => state.pickupDrop.PickupPlaceid1 || "");
  const reduxDropOffLatLng = useSelector((state) => state.pickupDrop.DropoffPlaceid1 || "");
  const errorMessage = useSelector((state) => state.pickupDrop.error);
  
  
 
  // State for storing the pickup and dropoff locations
  const [exitpickUpLocation, setexitPickUpLocation] = useState(reduxExitPickUpLocation);
  const [exitdropOffLocation, setexitDropOffLocation] = useState(reduxExitDropOffLocation);
  const [selectedDate1, setSelectedDate1] = useState(reduxPickupDate);
  const [pickUpLatLng, setPickupLatLng] = useState(reduxPickUpLatLng);
  const [dropOffLatLng, setDropoffLatLng] = useState(reduxDropOffLatLng);
  const [entryytime1, setentryytime1] = useState(reduxExitTime);
  const [validationTriggered, setValidationTriggered] = useState(false);
  const [exitPickupFromAutocomplete, setExitPickupFromAutocomplete] = useState(false);
  const [exitDropoffFromAutocomplete, setExitDropoffFromAutocomplete] = useState(false);
  const [time1, setTime1] = useState(false);

  // Update Redux store whenever local state changes
  useEffect(() => {
    if (exitpickUpLocation) {
      
      dispatch(setexitpickup(exitpickUpLocation));
    }
  }, [exitpickUpLocation, dispatch]);

  useEffect(() => {
    if (exitdropOffLocation) {
      
      dispatch(setexitdropoff(exitdropOffLocation));
    }
  }, [exitdropOffLocation, dispatch]);

  useEffect(() => {
    if (selectedDate1) {
      dispatch(setpickupdate(selectedDate1));
    }
  }, [selectedDate1, dispatch]);

  useEffect(() => {
    if (entryytime1) {
      
      dispatch(setentrytime(entryytime1));
      dispatch(setexittime(entryytime1));
    }
  }, [entryytime1, dispatch]);

  useEffect(() => {
    if (pickUpLatLng) {
      
      dispatch(setPickupPlaceid1(pickUpLatLng));
    }
  }, [pickUpLatLng, dispatch]);

  useEffect(() => {
    if (dropOffLatLng) {
      
      dispatch(setDropoffPlaceid1(dropOffLatLng));
    }
  }, [dropOffLatLng, dispatch]);

  

  // Monitor autocomplete state changes
 
  
  
  
  // Set time1 to true whenever entryytime1 has a value
  useEffect(() => {
    if (entryytime1) {
      console.log("Setting time1 to true because entryytime1 has value:", entryytime1);
      setTime1(true);
    } else {
      setTime1(false);
    }
  }, [entryytime1]);

  // Handler for the button search click event
  const buttonsearch = () => {
   
    
    // IMPORTANT: Set selection type to Exit Port FIRST
    // This ensures the correct slice of the Redux store is updated
    dispatch(setSelectionType("Exit Port"));
    dispatch(setPortZoneType(""));
    
    // Set validation triggered to true when search button is clicked
    setValidationTriggered(true);
    
    // Update all Redux values
    dispatch(setexitpickup(exitpickUpLocation));
    dispatch(setexitdropoff(exitdropOffLocation));
    dispatch(setentrytime(entryytime1));
    dispatch(setexittime(entryytime1));
    dispatch(setpickupdate(selectedDate1));
    dispatch(setPickupPlaceid1(pickUpLatLng));
    dispatch(setDropoffPlaceid1(dropOffLatLng));
    
    // Only proceed if both locations are selected from autocomplete
    const locationsValid = exitPickupFromAutocomplete && exitDropoffFromAutocomplete;
   
   

    // Check if we have a time value but time1 state is false (possible state inconsistency)
    if (entryytime1 && !time1) {
     
      setTime1(true);
    }

    // Only fetch vehicles if both locations are valid and time is selected
    if (locationsValid && (time1 || entryytime1)) {
     
      
      // Use setTimeout to ensure all Redux state updates have been processed
      setTimeout(() => {
        // Make sure selectionType is set to Exit Port before fetching
        dispatch(setSelectionType("Exit Port"));
        
        dispatch(fetchVehicles());
      }, 300);
    } else {
      
      // If we have all required data but there might be a state inconsistency, try again
      if (locationsValid && entryytime1) {
        console.log("ExitPortSearch - We have all required data but possible state inconsistency. Trying again.");
        
        // Force set time1 to true
        setTime1(true);
        
        // Use setTimeout to ensure all state updates have been processed
        setTimeout(() => {
          
          dispatch(setSelectionType("Exit Port"));
          dispatch(fetchVehicles());
        }, 500);
      }
    }
  };

  return (
    <Paper 
      elevation={0}
      sx={{
        borderRadius: 2,
        bgcolor: 'white',
        p: { xs: 1.5, md: 2 },
        mt: 1,
      }}
    >
      <Box sx={{ width: '100%', maxWidth: 1730 }}>
        {/* Form Fields Row */}
        <Grid container spacing={1.5} alignItems="flex-end" sx={{ mb: 2 }}>
          {/* Location Search */}
          <Grid item xs={12} md={6}>
            <Box>
              <SearchBar
                exitpickUpLocation={exitpickUpLocation}
                setexitPickUpLocation={setexitPickUpLocation}
                exitdropOffLocation={exitdropOffLocation}
                setexitDropOffLocation={setexitDropOffLocation}
                pickUpLatLng={pickUpLatLng}
                setPickupLatLng={setPickupLatLng}
                dropOffLatLng={dropOffLatLng}
                setDropoffLatLng={setDropoffLatLng}
                Location={Location}
                validationTriggered={validationTriggered}
                setPickupFromAutocomplete={setExitPickupFromAutocomplete}
                setDropoffFromAutocomplete={setExitDropoffFromAutocomplete}
                colorTheme="blue"
              />
            </Box>
          </Grid>

          {/* Time Selection */}
          <Grid item xs={12} md={3}>
            <Box>
              <Typography variant="body2" fontWeight={600} sx={{ mb: 0.8, color: 'text.primary', fontSize: '0.85rem' }}>
                Exit Time
              </Typography>
              <Pickuptime1
                entryytime={entryytime1}
                setentryytime={setentryytime1}
                setTime={setTime1}
              />
            </Box>
          </Grid>

          {/* Date Selection */}
          <Grid item xs={12} md={3}>
            <Box>
              <Typography variant="body2" fontWeight={600} sx={{ mb: 1, color: 'text.primary', fontSize: '0.85rem' }}>
                Exit Date
              </Typography>
              <DateSearch2
                selectedDate1={selectedDate1}
                setSelectedDate1={setSelectedDate1}
              />
            </Box>
          </Grid>
        </Grid>

        {/* Search Button Row */}
        <Box sx={{ display: 'flex', justifyContent: 'center', width: '100%' }}>
          <Button
            variant="contained"
            size="medium"
            startIcon={<SearchIcon />}
            onClick={buttonsearch}
            sx={{
              minWidth: 180,
              px: 3,
              py: 1.2,
              borderRadius: 1.5,
              background: 'linear-gradient(135deg, #3b82f6 0%, #1e40af 100%)',
              fontSize: '0.9rem',
              fontWeight: 600,
              textTransform: 'none',
              boxShadow: '0 3px 10px rgba(59, 130, 246, 0.3)',
              '&:hover': {
                background: 'linear-gradient(135deg, #1e40af 0%, #3b82f6 100%)',
                boxShadow: '0 5px 14px rgba(59, 130, 246, 0.4)',
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

export default ExitPortSearch; 