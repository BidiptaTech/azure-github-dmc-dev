import React, { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  Box,
  Button,
  Paper,
  Grid,
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
  setpickupdate,
  setentrytime,
  setSelectionType,
  setPickupPlaceid,
  setDropoffPlaceid,
  setPortZoneType,
  resetVehicles,
} from "@/slice/port/pickupDropSlice";
import DateSearch1 from "@/components/activity-list/common/DateSearch1";
import Pickuptime from "./Pickuptime";

const EntryPortSearch = ({ Location }) => {
  const theme = useTheme();
  const dispatch = useDispatch();
  
  // Get values from Redux store to persist state
  const reduxPickUpLocation = useSelector((state) => state.pickupDrop.entrypickup || "");
  const reduxDropOffLocation = useSelector((state) => state.pickupDrop.entrydropoff || "");
  const reduxPickupDate = useSelector((state) => state.pickupDrop.pickupdate || "");
  const reduxEntryTime = useSelector((state) => state.pickupDrop.entrytime || "");
  const reduxPickUpLatLng = useSelector((state) => state.pickupDrop.PickupPlaceid || "");
  const reduxDropOffLatLng = useSelector((state) => state.pickupDrop.DropoffPlaceid || "");
  
  // State for storing the pickup and dropoff locations
  const [pickUpLocation, setPickUpLocation] = useState(reduxPickUpLocation);
  const [dropOffLocation, setDropOffLocation] = useState(reduxDropOffLocation);
  const [selectedDate, setSelectedDate] = useState(reduxPickupDate);
  const [pickUpLatLng, setPickupLatLng] = useState(reduxPickUpLatLng);
  const [dropOffLatLng, setDropoffLatLng] = useState(reduxDropOffLatLng);
  const [entryytime, setentryytime] = useState(reduxEntryTime);
  const [validationTriggered, setValidationTriggered] = useState(false);
  const [pickupFromAutocomplete, setPickupFromAutocomplete] = useState(false);
  const [dropoffFromAutocomplete, setDropoffFromAutocomplete] = useState(false);
  const [time, setTime] = useState(false);
  const errorMessage = useSelector((state) => state.pickupDrop.error);
  

  // Update Redux store whenever local state changes
  useEffect(() => {
    if (pickUpLocation) {
      dispatch(setentrypickup(pickUpLocation));
    }
  }, [pickUpLocation, dispatch]);

  useEffect(() => {
    if (dropOffLocation) {
      dispatch(setentrydropoff(dropOffLocation));
    }
  }, [dropOffLocation, dispatch]);

  useEffect(() => {
    if (selectedDate) {
      dispatch(setpickupdate(selectedDate));
    }
  }, [selectedDate, dispatch]);

  useEffect(() => {
    if (entryytime) {
      dispatch(setentrytime(entryytime));
    }
  }, [entryytime, dispatch]);

  useEffect(() => {
    if (pickUpLatLng) {
      dispatch(setPickupPlaceid(pickUpLatLng));
    }
  }, [pickUpLatLng, dispatch]);

  useEffect(() => {
    if (dropOffLatLng) {
      dispatch(setDropoffPlaceid(dropOffLatLng));
    }
  }, [dropOffLatLng, dispatch]);

 
  // Handler for the button search click event
  const buttonsearch = () => {
    // Set validation triggered to true when search button is clicked
    setValidationTriggered(true);
    dispatch(setPortZoneType(""));
    dispatch(resetVehicles());
    // Only proceed if both locations are selected from autocomplete
    const locationsValid = pickupFromAutocomplete && dropoffFromAutocomplete;
     
    dispatch(setentrypickup(pickUpLocation));
    dispatch(setentrydropoff(dropOffLocation));
    dispatch(setpickupdate(selectedDate));
    dispatch(setentrytime(entryytime));
    dispatch(setSelectionType("Entry Port"));
    dispatch(setPickupPlaceid(pickUpLatLng));
    dispatch(setDropoffPlaceid(dropOffLatLng));

    // Only fetch vehicles if both locations are valid
    if (locationsValid && time) {
      setTimeout(() => {
        dispatch(fetchVehicles());
      }, 500);
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
            colorTheme="blue"
          />
            </Box>
          </Grid>

          {/* Time Selection */}
          <Grid item xs={12} md={3}>
            <Box>
            <Typography variant="body2" fontWeight={600} sx={{ mb: 0.8, color: 'text.primary', fontSize: '0.85rem' }}>
                Pick Up Time
              </Typography>
              <Pickuptime
                entryytime={entryytime}
                setentryytime={setentryytime}
                setTime={setTime}
              />
            </Box>
          </Grid>

          {/* Date Selection */}
          <Grid item xs={12} md={3}>
            <Box>
              <Typography variant="body2" fontWeight={600} sx={{ mb: 0.8, color: 'text.primary', fontSize: '0.85rem' }}>
                Pick Up Date
              </Typography>
              <DateSearch1
                selectedDate={selectedDate}
                setSelectedDate={(date) => {
                 
                  if (date && date._isAMomentObject) {
                    const formattedDate = date.format('YYYY-MM-DD');
                    
                    setSelectedDate(formattedDate);
                  } else {
                    setSelectedDate(date);
                  }
                }}
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

export default EntryPortSearch; 