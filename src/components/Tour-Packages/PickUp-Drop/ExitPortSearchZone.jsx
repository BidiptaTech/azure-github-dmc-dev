import React, { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  Button,
  Paper,
  Divider,
  Container,
} from "@mui/material";
import {
  LocationOn,
  FlightTakeoff,
  FlightLand,
  Schedule,
  CalendarToday,
  Search,
  Business,
} from "@mui/icons-material";


import {
  fetchVehicles,
  setexitpickup,
  setexitdropoff,
  setpickupdate,
  setexittime,
  setSelectionType,
  setPickupPlaceid,
  setDropoffPlaceid,
  resetVehicles,
  fetchPortCity,
  fetchLocalZone,
  setPicktype,
  setDroptype,
  fetchZoneVehicles,
  setPortZoneType,
  setPickupPlaceid1,
  setDropoffPlaceid1,
} from "@/slice/port/pickupDropSlice";
import PortCity from "./PortCity";
import SearchBar from "./PortLocation2";
import LocationSearch from "./PortLocation";
import DateSearch2 from "@/components/activity-list/common/DateSearch2";
import Pickuptime1 from "./Pickuptime1";

const ExitPortSearchZone = ({ Location, portType}) => {
  const dispatch = useDispatch();

  // State for storing the pickup and dropoff locations
  const [pickUpLocation, setPickUpLocation] = useState("");
  const [exitpickUpLocation, setexitPickUpLocation] = useState("");
  const [selectedDate1, setSelectedDate1] = useState("");
  const [entryytime1, setentryytime1] = useState("");
  const errorMessage = useSelector((state) => state.pickupDrop.error);
  const country = useSelector((state) => state.hotels.tourdetails.destination);

  const TourId = useSelector((state) => state.hotels.id);
      console.log("TourId", TourId);

  // Check port city API status
  const portCityStatus = useSelector(
    (state) => state.pickupDrop.portCityStatus
  );
  const portCityData = useSelector((state) => state.pickupDrop.portCityData);

  // Check local zone API status
  const localZoneStatus = useSelector(
    (state) => state.pickupDrop.localZoneStatus
  );

  // Add state for validation
  const [validationTriggered, setValidationTriggered] = useState(false);
  const [cityError, setCityError] = useState(false);
  const [type, setType] = useState("");
  const [pickdropType, setPickdropType] = useState("");

  const [id, setId] = useState("");
  // Track if locations were selected from autocomplete
  const [pickupFromAutocomplete, setPickupFromAutocomplete] = useState(false);
  const [exitPickupFromAutocomplete, setExitPickupFromAutocomplete] = useState(false);
  const [time1, setTime1] = useState(false);
  const [pickid, setpickId] = useState("");
  const [dropid, setdropId] = useState("");

  // State for selected city
  const [selectedCity, setSelectedCity] = useState(null);

  // Sequential enabling states
  const [isCityEnabled, setIsCityEnabled] = useState(true);
  const [isPickupLocationEnabled, setIsPickupLocationEnabled] = useState(false);
  const [isDropoffLocationEnabled, setIsDropoffLocationEnabled] = useState(false);
  const [isSearchButtonEnabled, setIsSearchButtonEnabled] = useState(false);

  // Effect to call fetchPortCity when a city is selected
  useEffect(() => {
    if (selectedCity && TourId) {
      console.log("Selected city:", selectedCity);
      console.log("Tour ID:", TourId, "Type:", typeof TourId);

      // Ensure TourId is passed correctly as a number
      dispatch(
        fetchPortCity({
          country: country,
          city: selectedCity.name,
          type: "hotel",
        })
      )
        .then((result) => {
          
          if (result.error) {
            console.error("API Error:", result.error);
            setIsPickupLocationEnabled(false);
          } else {
            // Enable pickup location after successful city API call
            setIsPickupLocationEnabled(true);
          }
        })
        .catch((error) => {
          console.error("Error dispatching fetchPortCity:", error);
          setIsPickupLocationEnabled(false);
        });
    } else {
      
      setIsPickupLocationEnabled(false);
    }
  }, [selectedCity, TourId, dispatch]);

  // Call fetchLocalZone when pickup location is selected for Exit Port
  useEffect(() => {
    if (exitpickUpLocation && type && id) {
      dispatch(
        fetchLocalZone({
          id: id,
          type: type,
        })
      )
        .then((result) => {
          
          if (result.error) {
            console.error("API Error:", result.error);
            setIsDropoffLocationEnabled(false);
          } else {
            // Enable dropoff location after successful pickup API call
            setIsDropoffLocationEnabled(true);
          }
        })
        .catch((error) => {
          console.error("Error dispatching fetchLocalzone:", error);
          setIsDropoffLocationEnabled(false);
        });
    } else {
      
      setIsDropoffLocationEnabled(false);
    }
  }, [id, type, exitpickUpLocation, dispatch]);

  // Effect to handle port city API response
  useEffect(() => {
   

    // Update pickup location enabled state based on API response
    if (portCityStatus === "succeeded" && portCityData) {
      setIsPickupLocationEnabled(true);
    }
  }, [portCityStatus, portCityData]);

  // Effect to handle local zone API response
  useEffect(() => {
    

    // Update dropoff location enabled state based on API response
    if (localZoneStatus === "succeeded") {
      setIsDropoffLocationEnabled(true);
    }
  }, [localZoneStatus]);

  // Effect to update search button state for Exit Port
  useEffect(() => {
    // Check if all required fields are filled for Exit Port
    const isExitModeComplete =
      !!selectedCity &&
      !!exitpickUpLocation &&
      exitPickupFromAutocomplete &&
      !!pickUpLocation &&
      pickupFromAutocomplete;

    setIsSearchButtonEnabled(isExitModeComplete);
  }, [
    selectedCity,
    exitpickUpLocation,
    pickUpLocation,
    exitPickupFromAutocomplete,
    pickupFromAutocomplete,
  ]);

  // Handler for the button search click event
  const buttonsearch = () => {
    // Set validation triggered to true when search button is clicked
    setValidationTriggered(true);

    // Check if city is selected
    if (!selectedCity) {
      setCityError(true);
      return;
    }

    // Check if all required fields are filled
    if (!isSearchButtonEnabled) {
      // Display validation errors but don't proceed with API call
      return;
    }

    dispatch(setPortZoneType("zone"));
    dispatch(setexitpickup(exitpickUpLocation));
    dispatch(setexitdropoff(pickUpLocation));
    dispatch(setexittime(entryytime1));
    dispatch(setpickupdate(selectedDate1));
    dispatch(setSelectionType("Exit Port"));
    dispatch(setPickupPlaceid1(pickid));
    dispatch(setDropoffPlaceid1(dropid));
    dispatch(setPicktype(pickdropType));
    dispatch(setDroptype("port"));

    // Only fetch vehicles if both locations are valid
    if (pickid && dropid) {
      setTimeout(() => {
        dispatch(fetchZoneVehicles());
      }, 500);
    }
  };

  // Handle location selection from PortCity
  const handleCitySelect = (city) => {
    
    setSelectedCity(city);
    if (city) {
      setCityError(false);
    }
  };

  return (
    <Card 
      elevation={2}
 
    >   
        <Paper 
          elevation={1} 
          sx={{ 
            p: 2, 
            borderRadius: 2,
            background: 'rgba(255, 255, 255, 0.95)',
            backdropFilter: 'blur(10px)'
          }}
        >
          <Grid container spacing={3.5} alignItems="flex-end">
            {/* City Selection */}
            <Grid item xs={12} sm={6} md={6} lg={3}>
              <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                  <Business sx={{ mr: 0.8, color: '#1976d2', fontSize: 18 }} />
                  <Typography 
                    variant="body2" 
                    fontWeight="600"
                    color={!isCityEnabled ? "text.disabled" : "text.primary"}
                    sx={{ fontSize: '0.8rem' }}
                  >
                    City
                  </Typography>
                </Box>
                <Box sx={{ minHeight: '36px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 1 }}>
                  <PortCity
                    onLocationSelect={handleCitySelect}
                    hasError={cityError}
                    setError={setCityError}
                    disabled={!isCityEnabled}
                  />
                </Box>
              </Box>
            </Grid>

            {/* Pick Up Location */}
            <Grid item xs={12} sm={6} md={6} lg={3}>
              <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                  <LocationOn sx={{ mr: 0.8, color: '#2e7d32', fontSize: 18 }} />
                  <Typography 
                    variant="body2" 
                    fontWeight="600"
                    color={!isPickupLocationEnabled ? "text.disabled" : "text.primary"}
                    sx={{ fontSize: '0.8rem' }}
                  >
                    Pick Up Location
                  </Typography>
                </Box>
                <Box sx={{ minHeight: '36px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 1 }}>
                  <SearchBar
                    exitpickUpLocation={exitpickUpLocation}
                    setexitPickUpLocation={setexitPickUpLocation}
                    setType={setType}
                    setPickdropType={setPickdropType}
                    setId={setId}
                    setpickId={setpickId}
                    setdropId={setdropId}
                    validationTriggered={validationTriggered}
                    setPickupFromAutocomplete={setExitPickupFromAutocomplete}
                    setDropoffFromAutocomplete={setExitPickupFromAutocomplete}
                    disabled={!isPickupLocationEnabled}
                    portType={portType}
                  />
                </Box>
              </Box>
            </Grid>

            {/* Drop Off Location */}
            <Grid item xs={12} sm={6} md={6} lg={3}>
              <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                  <FlightTakeoff sx={{ mr: 0.8, color: '#d32f2f', fontSize: 18 }} />
                  <Typography 
                    variant="body2" 
                    fontWeight="600"
                    color={!isDropoffLocationEnabled ? "text.disabled" : "text.primary"}
                    sx={{ fontSize: '0.8rem' }}
                  >
                    Drop Off Location
                  </Typography>
                </Box>
                <Box sx={{ minHeight: '36px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 1 }}>
                  <LocationSearch
                    pickUpLocation={pickUpLocation}
                    setPickUpLocation={setPickUpLocation}
                    setType={setType}
                    portType={portType}
                    setId={setId}
                    setpickId={setpickId}
                    setdropId={setdropId}
                    validationTriggered={validationTriggered}
                    setPickupFromAutocomplete={setPickupFromAutocomplete}
                    setDropoffFromAutocomplete={setPickupFromAutocomplete}
                    disabled={!isDropoffLocationEnabled}
                  />
                </Box>
              </Box>
            </Grid>

            {/* Time Selection */}
            <Grid item xs={12} sm={6} md={6} lg={3}>
              <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'flex-end' }}>
                <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                  <Schedule sx={{ mr: 0.8, color: '#ff9800', fontSize: 18 }} />
                  <Typography 
                    variant="body2" 
                    fontWeight="600"
                    color={!isDropoffLocationEnabled ? "text.disabled" : "text.primary"}
                    sx={{ fontSize: '0.8rem' }}
                  >
                    Exit Time
                  </Typography>
                </Box>
                <Box sx={{ minHeight: '42px', height: '42px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 5 }}>
                  <Pickuptime1
                    entryytime={entryytime1}
                    setentryytime={setentryytime1}
                    setTime={setTime1}
                    disabled={!isDropoffLocationEnabled}
                  />
                </Box>
              </Box>
            </Grid>
          </Grid>

          {/* Second Row - Only Date field */}
          <Grid container spacing={1.5} alignItems="flex-end" sx={{ mt: 1.5 }}>
            {/* Date Selection */}
            <Grid item xs={12} sm={6} md={6} lg={3}>
              <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'flex-end' }}>
                <Box display="flex" alignItems="center" mb={0.8} sx={{ height: '28px' }}>
                  <CalendarToday sx={{ mr: 0.8, color: '#9c27b0', fontSize: 18 }} />
                  <Typography 
                    variant="body2" 
                    fontWeight="600"
                    color={!isDropoffLocationEnabled ? "text.disabled" : "text.primary"}
                    sx={{ fontSize: '0.8rem' }}
                  >
                    Exit Date
                  </Typography>
                </Box>
                <Box sx={{ minHeight: '42px', height: '42px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 5 }}>
                  <DateSearch2
                    selectedDate1={selectedDate1}
                    setSelectedDate1={setSelectedDate1}
                    disabled={!isDropoffLocationEnabled}
                  />
                </Box>
              </Box>
            </Grid>
          </Grid>

          {/* Search Button - Separate Row */}
          <Grid item xs={12} sx={{ mt: 1.5 }}>
            <Box display="flex" justifyContent="center">
              <Button
                variant="contained"
                size="medium"
                onClick={buttonsearch}
                disabled={!isSearchButtonEnabled}
                startIcon={<Search />}
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
                Search Vehicles
              </Button>
            </Box>
          </Grid>
        </Paper>
     
      
      {/* Global CSS for high z-index dropdowns */}
      
    </Card>
  );
};

export default ExitPortSearchZone; 