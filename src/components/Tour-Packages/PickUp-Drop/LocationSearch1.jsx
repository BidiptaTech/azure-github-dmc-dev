import React, { useEffect, useRef, useState, useCallback } from "react";
import { useSelector } from "react-redux";
import {
  Box,
  Typography,
  TextField,
  Grid,
  InputAdornment,
  useTheme,
  alpha,
} from '@mui/material';
import LocationOnIcon from '@mui/icons-material/LocationOn';

const SearchBar = ({
  exitpickUpLocation,
  setexitPickUpLocation,
  exitdropOffLocation,
  setexitDropOffLocation,
  setPickupLatLng,
  setDropoffLatLng,
  Location,
  validationTriggered = false,
  setPickupFromAutocomplete,
  setDropoffFromAutocomplete,
  colorTheme = "red", // default red theme, can be "blue"
}) => {
  const theme = useTheme();
  const autocompletePickUpPackageRef1 = useRef(null);
  const autocompleteDropOffPackageRef1 = useRef(null);
  const [isPickupValid, setIsPickupValid] = useState(true);
  const [isDropoffValid, setIsDropoffValid] = useState(true);
  const [isInitialized, setIsInitialized] = useState(false);
  const isMountedRef = useRef(true);
  const SelectedPort = useSelector((state) => state.pickupDrop.selectedPort);

  // Define unique IDs for this component
  const pickupInputId = "exit-pick-up-input";
  const dropoffInputId = "exit-drop-off-input";

  // Debug logs
  console.log("LocationSearch1 Props:", {
    exitpickUpLocation,
    exitdropOffLocation,
    Location,
    validationTriggered,
  });

  // Cleanup function to clear autocomplete listeners
  const cleanupAutocomplete = useCallback(() => {
    if (autocompletePickUpPackageRef1.current) {
      window.google.maps.event.clearInstanceListeners(
        autocompletePickUpPackageRef1.current
      );
      autocompletePickUpPackageRef1.current = null;
    }
    if (autocompleteDropOffPackageRef1.current) {
      window.google.maps.event.clearInstanceListeners(
        autocompleteDropOffPackageRef1.current
      );
      autocompleteDropOffPackageRef1.current = null;
    }
  }, []);

  // Initialize autocomplete function with proper error handling
  const initializeAutocomplete1 = useCallback((
    inputId,
    ref,
    setLocation,
    setLatLng,
    setIsValid,
    setParentValid
  ) => {
    if (!isMountedRef.current) return;

    const inputElement = document.getElementById(inputId);
    if (!inputElement) {
      console.error(`Input element with ID ${inputId} not found`);
      return;
    }

    try {
      console.log(`Initializing autocomplete for ${inputId}`, {
        restrictCountry: Array.isArray(Location) ? Location : [Location],
      });
      
      ref.current = new window.google.maps.places.Autocomplete(inputElement, {
        types: [], // Allow all locations
        componentRestrictions: {
          country: Array.isArray(Location) ? Location : [Location],
        },
      });

      ref.current.addListener("place_changed", () => {
        if (!isMountedRef.current) return;

        console.log(`Place changed for ${inputId}`);
        const place = ref.current.getPlace();
        console.log("Place details:", place);
        
        if (place && place.geometry && place.geometry.location) {
          const lat = place.geometry.location.lat();
          const lng = place.geometry.location.lng();

          // Extract only the main name (highlighted) or first part of the address
          let formattedLocation =
            place.name ||
            place.address_components?.[0]?.long_name ||
            "Unknown Location";

          console.log(`Setting location for ${inputId}:`, formattedLocation);
          setLocation(formattedLocation);
          setLatLng({ lat, lng });
          setIsValid(true); // Mark as selected from autocomplete
          setParentValid(true); // Update parent state
        } else {
          console.error(`No geometry found for place from ${inputId}`);
        }
      });
    } catch (error) {
      console.error(`Error initializing autocomplete for ${inputId}:`, error);
    }
  }, [Location]);

  // Setup autocomplete with proper lifecycle management
  useEffect(() => {
    // Don't re-initialize if already done
    if (isInitialized) return;

    // Check if Google Maps API is loaded
    if (!window.google || !window.google.maps || !window.google.maps.places) {
      console.error("Google Maps API not loaded or places library missing.");
      return;
    }

    console.log("Google Maps API is available, setting up autocomplete");
    
    // Check if input elements exist before initializing
    const checkAndInitialize = () => {
      if (!isMountedRef.current) return;

      const pickupInput = document.getElementById(pickupInputId);
      const dropoffInput = document.getElementById(dropoffInputId);
      
      if (!pickupInput) {
        console.error(`Pickup input element not found with ID: ${pickupInputId}`);
        return;
      }
      
      if (!dropoffInput) {
        console.error(`Dropoff input element not found with ID: ${dropoffInputId}`);
        return;
      }

      // Initialize autocomplete for both inputs
      initializeAutocomplete1(
        pickupInputId,
        autocompletePickUpPackageRef1,
        setexitPickUpLocation,
        setPickupLatLng,
        setIsPickupValid,
        setPickupFromAutocomplete
      );
      
      initializeAutocomplete1(
        dropoffInputId,
        autocompleteDropOffPackageRef1,
        setexitDropOffLocation,
        setDropoffLatLng,
        setIsDropoffValid,
        setDropoffFromAutocomplete
      );

      setIsInitialized(true);
    };

    // Use multiple attempts with increasing delays to ensure DOM is ready
    const timeoutId = setTimeout(checkAndInitialize, 100);
    const timeoutId2 = setTimeout(() => {
      if (!isInitialized && isMountedRef.current) {
        checkAndInitialize();
      }
    }, 500);

    return () => {
      clearTimeout(timeoutId);
      clearTimeout(timeoutId2);
    };
  }, [initializeAutocomplete1, isInitialized, setexitPickUpLocation, setexitDropOffLocation, setPickupLatLng, setDropoffLatLng, setPickupFromAutocomplete, setDropoffFromAutocomplete]);

  // Component unmount cleanup
  useEffect(() => {
    return () => {
      isMountedRef.current = false;
      cleanupAutocomplete();
    };
  }, [cleanupAutocomplete]);

  const handlePickupChange = useCallback((e) => {
    setexitPickUpLocation(e.target.value);
    setIsPickupValid(false);
    setPickupFromAutocomplete(false);
  }, [setexitPickUpLocation, setPickupFromAutocomplete]);

  const handleDropoffChange = useCallback((e) => {
    setexitDropOffLocation(e.target.value);
    setIsDropoffValid(false);
    setDropoffFromAutocomplete(false);
  }, [setexitDropOffLocation, setDropoffFromAutocomplete]);

  // Only show validation errors if validationTriggered is true and there's a value in the input
  const showPickupError =
    validationTriggered && exitpickUpLocation && !isPickupValid;
  const showDropoffError =
    validationTriggered && exitdropOffLocation && !isDropoffValid;

  // Color theme configuration
  const themeColors = {
    red: {
      primary: '#ff6b6b',
      hover: '#ff6b6b',
      focus: '#ff6b6b',
    },
    blue: {
      primary: '#3b82f6',
      hover: '#3b82f6',
      focus: '#3b82f6',
    }
  };

  const currentTheme = themeColors[colorTheme] || themeColors.red;

  return (
    <Box sx={{ width: '100%' }}>
      <Grid container spacing={2}>
        {/* Pick-up Location */}
        <Grid item xs={12} md={6}>
          <Box>
            <Typography 
              variant="subtitle2" 
              fontWeight={600} 
              sx={{ mb: 1, color: 'text.primary' }}
            >
              Pick Up Location
            </Typography>
            <TextField
              id={pickupInputId}
              fullWidth
              autoComplete="off"
              type="search"
              placeholder="Where is your pick up?"
              value={exitpickUpLocation}
              onChange={handlePickupChange}
              error={showPickupError}
              helperText={showPickupError ? "Select location from dropdown" : ""}
              InputProps={{
                startAdornment: (
                  <InputAdornment position="start">
                    <LocationOnIcon 
                      sx={{ 
                        color: showPickupError ? 'error.main' : 'text.secondary',
                        fontSize: 20 
                      }} 
                    />
                  </InputAdornment>
                ),
              }}
              sx={{
                '& .MuiOutlinedInput-root': {
                  borderRadius: 2,
                  bgcolor: 'background.paper',
                  '&:hover .MuiOutlinedInput-notchedOutline': {
                    borderColor: currentTheme.hover,
                  },
                  '&.Mui-focused .MuiOutlinedInput-notchedOutline': {
                    borderColor: currentTheme.focus,
                    borderWidth: 2,
                  },
                },
                '& .MuiInputBase-input': {
                  fontSize: 15,
                  fontWeight: 400,
                },
                '& .MuiFormHelperText-root': {
                  fontSize: 14,
                  mt: 0.5,
                },
              }}
            />
          </Box>
        </Grid>

        {/* Drop-off Location */}
        <Grid item xs={12} md={6}>
          <Box>
            <Typography 
              variant="subtitle2" 
              fontWeight={600} 
              sx={{ mb: 1, color: 'text.primary' }}
            >
              Drop Off Location
            </Typography>
            <TextField
              id={dropoffInputId}
              fullWidth
              autoComplete="off"
              type="search"
              placeholder="Where is your drop off?"
              value={exitdropOffLocation}
              onChange={handleDropoffChange}
              error={showDropoffError}
              helperText={showDropoffError ? "Select location from dropdown" : ""}
              InputProps={{
                startAdornment: (
                  <InputAdornment position="start">
                    <LocationOnIcon 
                      sx={{ 
                        color: showDropoffError ? 'error.main' : 'text.secondary',
                        fontSize: 20 
                      }} 
                    />
                  </InputAdornment>
                ),
              }}
              sx={{
                '& .MuiOutlinedInput-root': {
                  borderRadius: 2,
                  bgcolor: 'background.paper',
                  '&:hover .MuiOutlinedInput-notchedOutline': {
                    borderColor: currentTheme.hover,
                  },
                  '&.Mui-focused .MuiOutlinedInput-notchedOutline': {
                    borderColor: currentTheme.focus,
                    borderWidth: 2,
                  },
                },
                '& .MuiInputBase-input': {
                  fontSize: 15,
                  fontWeight: 400,
                },
                '& .MuiFormHelperText-root': {
                  fontSize: 14,
                  mt: 0.5,
                },
              }}
            />
          </Box>
        </Grid>
      </Grid>

      {/* Enhanced UI for Google Autocomplete Dropdown */}
      <style>
        {`
          .pac-container {
            z-index: 10000 !important;
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12) !important;
            margin-top: 4px !important;
            overflow: hidden !important;
            max-height: 240px !important;
            overflow-y: auto !important;
            min-width: 300px !important;
            max-width: 400px !important;
            font-family: inherit !important;
          }

          /* Custom Scrollbar Styling */
          .pac-container::-webkit-scrollbar {
            width: 4px !important;
          }

          .pac-container::-webkit-scrollbar-track {
            background: #f8fafc !important;
          }

          .pac-container::-webkit-scrollbar-thumb {
            background: #cbd5e1 !important;
            border-radius: 2px !important;
          }

          .pac-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8 !important;
          }

          /* Firefox Scrollbar */
          .pac-container {
            scrollbar-width: thin !important;
            scrollbar-color: #cbd5e1 #f8fafc !important;
          }

          .pac-item {
            font-size: 14px !important;
            font-weight: 400 !important;
            color: #374151 !important;
            padding: 12px 16px !important;
            border-bottom: 1px solid #f3f4f6 !important;
            cursor: pointer !important;
            transition: background-color 0.15s ease !important;
            background-color: #ffffff !important;
            line-height: 1.5 !important;
            margin: 0 !important;
          }

          .pac-item:hover {
            background-color: #f9fafb !important;
          }

          .pac-item:last-child {
            border-bottom: none !important;
          }

          .pac-item-query {
            font-weight: 500 !important;
            color: #111827 !important;
          }

          .pac-matched {
            font-weight: 600 !important;
            color: ${currentTheme.primary} !important;
            background-color: rgba(${colorTheme === 'blue' ? '59, 130, 246' : '255, 107, 107'}, 0.1) !important;
            padding: 1px 3px !important;
            border-radius: 3px !important;
          }

          .pac-icon {
            margin-right: 12px !important;
            color: #9ca3af !important;
            font-size: 14px !important;
          }

          .pac-item:hover .pac-icon {
            color: ${currentTheme.primary} !important;
          }

          /* Remove gaps and ensure clean layout */
          .pac-item span {
            margin: 0 !important;
            padding: 0 !important;
            line-height: inherit !important;
          }

          .pac-item table {
            margin: 0 !important;
            border-collapse: collapse !important;
          }

          .pac-item td {
            padding: 0 !important;
            margin: 0 !important;
            vertical-align: middle !important;
          }

          /* Responsive adjustments */
          @media (max-width: 1400px) {
            .pac-container {
              min-width: 280px !important;
              max-width: 350px !important;
            }
          }
          
          @media (max-width: 1200px) {
            .pac-container {
              min-width: 250px !important;
              max-width: 320px !important;
              max-height: 200px !important;
            }
          }
          
          @media (max-width: 1024px) {
            .pac-container {
              min-width: 220px !important;
              max-width: 280px !important;
            }
          }
          
          @media (max-width: 768px) {
            .pac-container {
              min-width: 200px !important;
              max-width: 250px !important;
              max-height: 180px !important;
            }
            
            .pac-item {
              font-size: 13px !important;
              padding: 10px 14px !important;
            }
          }
          
          @media (max-width: 480px) {
            .pac-container {
              min-width: 180px !important;
              max-width: 220px !important;
            }
            
            .pac-item {
              font-size: 12px !important;
              padding: 8px 12px !important;
            }
          }
        `}
      </style>
    </Box>
  );
};

export default SearchBar;
