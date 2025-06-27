import React, { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  
  Grid,
  
  CardContent,
  Typography,

 

  
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
  Box,
 
  Card,
  
 
  Button,
  Paper,
  Divider,
  Container,
} from "@mui/material";

import {
  fetchVehicles,
  setentrypickup,
  setentrydropoff,
  setpickupdate,
  setentrytime,
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
  setSelectedPort,
} from "@/slice/port/pickupDropSlice";
import PortCity from "./PortCity";
import LocationSearch from "./PortLocation";
import SearchBar from "./PortLocation2";
import DateSearch1 from "@/components/activity-list/common/DateSearch1";
import Pickuptime from "@/components/activity-single/filter-box1/Pickuptime";

const EntryPortSearchZone = ({ Location, portType}) => {
  const dispatch = useDispatch();

  // State for storing the pickup and dropoff locations
  const [pickUpLocation, setPickUpLocation] = useState("");
  const [exitpickUpLocation, setexitPickUpLocation] = useState("");
  const [selectedDate, setSelectedDate] = useState("");
  const [entryytime, setentryytime] = useState("");

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
  const [time, setTime] = useState(false);
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
          city: selectedCity.name,
          tourId: parseInt(TourId),
          type: "port",
        })
      )
        .then((result) => {
          console.log("fetchPortCity dispatch result:", result);
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
      console.log("Not calling fetchPortCity, missing:", {
        hasCity: !!selectedCity,
        hasTourId: !!TourId,
        tourIdValue: TourId,
      });
      setIsPickupLocationEnabled(false);
    }
  }, [selectedCity, TourId, dispatch]);

  // Call fetchLocalZone when pickup location is selected
  useEffect(() => {
    if (pickUpLocation && type && id) {
      dispatch(
        fetchLocalZone({
          id: id,
          type: type,
        })
      )
        .then((result) => {
          console.log("fetchlocalzone dispatch result:", result);
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
      console.log(
        "Not calling fetchPortCity, missing:",
        pickUpLocation,
        type,
        id
      );
      setIsDropoffLocationEnabled(false);
    }
  }, [id, type, pickUpLocation, dispatch]);

  // Effect to handle port city API response
  useEffect(() => {
    console.log("Port city status:", portCityStatus);
    console.log("Port city data:", portCityData);

    // Update pickup location enabled state based on API response
    if (portCityStatus === "succeeded" && portCityData) {
      setIsPickupLocationEnabled(true);
    }
  }, [portCityStatus, portCityData]);

  // Effect to handle local zone API response
  useEffect(() => {
    console.log("Local zone status:", localZoneStatus);

    // Update dropoff location enabled state based on API response
    if (localZoneStatus === "succeeded") {
      setIsDropoffLocationEnabled(true);
    }
  }, [localZoneStatus]);

  // Effect to update search button state
  useEffect(() => {
    // Check if all required fields are filled for Entry Port
    const isEntryModeComplete =
      !!selectedCity &&
      !!pickUpLocation &&
      pickupFromAutocomplete &&
      !!exitpickUpLocation &&
      exitPickupFromAutocomplete;

    setIsSearchButtonEnabled(isEntryModeComplete);
  }, [
    selectedCity,
    pickUpLocation,
    exitpickUpLocation,
    pickupFromAutocomplete,
    exitPickupFromAutocomplete,
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
    dispatch(setentrypickup(pickUpLocation));
    dispatch(setentrydropoff(exitpickUpLocation));
    dispatch(setpickupdate(selectedDate));
    dispatch(setentrytime(entryytime));
    dispatch(setSelectionType("Entry Port"));
    
    dispatch(setPickupPlaceid(pickid));
    dispatch(setDropoffPlaceid(dropid));
    dispatch(setPicktype("port"));
    dispatch(setDroptype(pickdropType));

    // Only fetch vehicles if both locations are valid
    if (pickid && dropid) {
      setTimeout(() => {
        dispatch(fetchZoneVehicles());
      }, 500); 
    }
  };

  // Handle location selection from PortCity
  const handleCitySelect = (city) => {
    console.log("City selected:", city);
    setSelectedCity(city);
    if (city) {
      setCityError(false);
    }
  };

  return (
    <Card 
      elevation={3}

    >
 
        <Paper 
          elevation={2} 
          sx={{ 
            p: 3, 
            borderRadius: 2,
            background: 'rgba(255, 255, 255, 0.95)',
            backdropFilter: 'blur(10px)'
          }}
        >
                    <Grid container spacing={2} alignItems="flex-end">
            {/* City Selection */}
            <Grid item xs={12} sm={6} md={2}>
              <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                  <Business sx={{ mr: 1, color: '#1976d2', fontSize: 20 }} />
                  <Typography 
                    variant="subtitle2" 
                    fontWeight="600"
                    color={!isCityEnabled ? "text.disabled" : "text.primary"}
                  >
                    City
                  </Typography>
                </Box>
                <Box sx={{ minHeight: '40px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 1 }}>
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
            <Grid item xs={12} sm={6} md={3}>
              <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                  <LocationOn sx={{ mr: 1, color: '#2e7d32', fontSize: 20 }} />
                  <Typography 
                    variant="subtitle2" 
                    fontWeight="600"
                    color={!isPickupLocationEnabled ? "text.disabled" : "text.primary"}
                  >
                    Pick Up Location
                  </Typography>
                </Box>
                <Box sx={{ minHeight: '40px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 1 }}>
                  <LocationSearch
                    pickUpLocation={pickUpLocation}
                    setPickUpLocation={setPickUpLocation}
                    portType={portType}
                    setType={setType}
                    setId={setId}
                    setpickId={setpickId}
                    setdropId={setdropId}
                    validationTriggered={validationTriggered}
                    setPickupFromAutocomplete={setPickupFromAutocomplete}
                    setDropoffFromAutocomplete={setPickupFromAutocomplete}
                    disabled={!isPickupLocationEnabled}
                  />
                </Box>
              </Box>
            </Grid>

            {/* Drop Off Location */}
            <Grid item xs={12} sm={6} md={3}>
              <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                  <FlightLand sx={{ mr: 1, color: '#d32f2f', fontSize: 20 }} />
                  <Typography 
                    variant="subtitle2" 
                    fontWeight="600"
                    color={!isDropoffLocationEnabled ? "text.disabled" : "text.primary"}
                  >
                    Drop Off Location
                  </Typography>
                </Box>
                <Box sx={{ minHeight: '40px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 1 }}>
                  <SearchBar
                    exitpickUpLocation={exitpickUpLocation}
                    setexitPickUpLocation={setexitPickUpLocation}
                    setType={setType}
                    portType={portType}
                    setId={setId}
                    setpickId={setpickId}
                    setdropId={setdropId}
                    setPickdropType={setPickdropType}
                    validationTriggered={validationTriggered}
                    setPickupFromAutocomplete={setExitPickupFromAutocomplete}
                    setDropoffFromAutocomplete={setExitPickupFromAutocomplete}
                    disabled={!isDropoffLocationEnabled}
                  />
                </Box>
              </Box>
            </Grid>

            {/* Time Selection */}
            <Grid item xs={12} sm={6} md={2}>
              <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'flex-end' }}>
                <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                  <Schedule sx={{ mr: 1, color: '#ff9800', fontSize: 20 }} />
                  <Typography 
                    variant="subtitle2" 
                    fontWeight="600"
                    color={!isDropoffLocationEnabled ? "text.disabled" : "text.primary"}
                  >
                    Pick Up Time
                  </Typography>
                </Box>
                <Box sx={{ minHeight: '48px', height: '48px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 15300 }}>
                  <Pickuptime
                    entryytime={entryytime}
                    setentryytime={setentryytime}
                    setTime={setTime}
                    disabled={!isDropoffLocationEnabled}
                  />
                </Box>
              </Box>
            </Grid>

            {/* Date Selection */}
            <Grid item xs={12} sm={6} md={2}>
              <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column', justifyContent: 'flex-end' }}>
                <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                  <CalendarToday sx={{ mr: 1, color: '#9c27b0', fontSize: 20 }} />
                  <Typography 
                    variant="subtitle2" 
                    fontWeight="600"
                    color={!isDropoffLocationEnabled ? "text.disabled" : "text.primary"}
                  >
                    Pick Up Date
                  </Typography>
                </Box>
                <Box sx={{ minHeight: '48px', height: '48px', display: 'flex', alignItems: 'center', position: 'relative', zIndex: 15200 }}>
                  <DateSearch1
                    selectedDate={selectedDate}
                    setSelectedDate={setSelectedDate}
                    disabled={!isDropoffLocationEnabled}
                  />
                </Box> 
              </Box>
            </Grid>

            {/* Search Button - Separate Row */}
            <Grid item xs={12} sx={{ mt: 2 }}>
              <Box display="flex" justifyContent="center">
                <Button
                  variant="contained"
                  size="large"
                  onClick={buttonsearch}
                  disabled={!isSearchButtonEnabled}
                  startIcon={<Search />}
                  sx={{
                    minWidth: 200,
                    px: 4,
                    py: 1.5,
                    borderRadius: 2,
                    background: 'linear-gradient(135deg, #3b82f6 0%, #1e40af 100%)',
                    fontSize: '1rem',
                    fontWeight: 600,
                    textTransform: 'none',
                    boxShadow: '0 4px 12px rgba(59, 130, 246, 0.3)',
                    '&:hover': {
                      background: 'linear-gradient(135deg, #1e40af 0%, #3b82f6 100%)',
                      boxShadow: '0 6px 16px rgba(59, 130, 246, 0.4)',
                      transform: 'translateY(-1px)',
                    },
                    transition: 'all 0.3s ease',
                  }}
                >
                  Search Vehicles
                </Button>
              </Box>
            </Grid>
          </Grid>
        </Paper>
    
      
      {/* Global CSS for high z-index dropdowns */}
    
    </Card>
  );
};

export default EntryPortSearchZone; 