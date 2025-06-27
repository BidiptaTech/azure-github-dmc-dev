import React, { useEffect, useRef, useState } from "react";
import { useSelector } from "react-redux";
import {
  Box,
  TextField,
  Typography,
  Grid,
  InputAdornment,
  Alert,
} from '@mui/material';
import MyLocationIcon from '@mui/icons-material/MyLocation';

const SearchBar1 = ({
  exitpickUpLocation,
  setexitPickUpLocation,
  setPickupLatLng,
  Location,
  validationTriggered = false,
  setPickupFromAutocomplete,
  dayIndex = 0,
}) => {
  const autocompletePickUpRef1 = useRef(null);
  const [isPickupValid, setIsPickupValid] = useState(true);
  const selectedPort = useSelector((state) => state.localtour.selectedPort);
  
  // Create unique ID for input based on day index
  const pickupInputId = `local-transport-hourly-pick-up-input-day-${dayIndex}`;
  
  // Add reference to track if we're in the middle of a selection
  const isSelectingRef = useRef(false);

  // Keep input value in sync with state
  useEffect(() => {
    const inputElement = document.getElementById(pickupInputId);
    if (inputElement && exitpickUpLocation && !isSelectingRef.current) {
      inputElement.value = exitpickUpLocation;
    }
  }, [exitpickUpLocation, pickupInputId]);

  useEffect(() => {
    if (!window.google || !window.google.maps || !window.google.maps.places) {
      console.error("Google Maps API not loaded.");
      return;
    }

    const initializeAutocomplete = (
      inputId,
      ref,
      setLocation,
      setLatLng,
      setIsValid,
      setParentValid
    ) => {
      const inputElement = document.getElementById(inputId);
      if (!inputElement) {
        console.error(`Input element with ID ${inputId} not found. Will retry in 500ms.`);
        // Use setTimeout to retry after DOM is fully loaded
        setTimeout(() => {
          const retryElement = document.getElementById(inputId);
          if (retryElement) {
            console.log(`Element with ID ${inputId} found on retry`);
            initializeAutocomplete(inputId, ref, setLocation, setLatLng, setIsValid, setParentValid);
          } else {
            console.error(`Input element with ID ${inputId} still not found after retry`);
          }
        }, 500);
        return;
      }

      console.log(`Initializing autocomplete for ${inputId}`);
      ref.current = new window.google.maps.places.Autocomplete(inputElement, {
        types: [], // Allow any location
        componentRestrictions: {
          country: Array.isArray(Location) ? Location : [Location],
        },
      });

      ref.current.addListener("place_changed", () => {
        const place = ref.current.getPlace();
        if (place && place.geometry && place.geometry.location) {
          const lat = place.geometry.location.lat();
          const lng = place.geometry.location.lng();
          
          // Set flag to indicate we're in the middle of selection
          isSelectingRef.current = true;
          
          console.log(`Place selected: ${inputId}`, { lat, lng });

          // Validate lat/lng values
          if (lat === undefined || lng === undefined || isNaN(lat) || isNaN(lng)) {
            console.error(`Invalid coordinates returned for ${inputId}:`, { lat, lng });
            isSelectingRef.current = false;
            return;
          }

          // Extract only the main location name (highlighted part)
          let formattedLocation =
            place.name ||
            place.address_components?.[0]?.long_name ||
            "Unknown Location";

          // Important: Set the input value directly to prevent it from being cleared
          const inputElement = document.getElementById(inputId);
          if (inputElement) {
            inputElement.value = formattedLocation;
          }
          
          // Set state after a small delay to ensure React state updates properly
          setTimeout(() => {
            setLocation(formattedLocation);
            setLatLng({ lat, lng });
            console.log(`Setting ${inputId} lat/lng:`, { lat, lng });
            setIsValid(true); // Mark as selected from autocomplete
            setParentValid(true); // Update parent state to indicate autocomplete selection
            
            // Log the successful completion of location selection
            console.log(`✅ Location ${inputId} selected successfully:`, {
              name: formattedLocation,
              coords: { lat, lng }
            });
            
            // Reset the selecting flag
            isSelectingRef.current = false;
          }, 10);
        } else {
          console.error(`No geometry found for selected place in ${inputId}`);
          isSelectingRef.current = false;
        }
      });
    };

    initializeAutocomplete(
      pickupInputId,
      autocompletePickUpRef1,
      setexitPickUpLocation,
      setPickupLatLng,
      setIsPickupValid,
      setPickupFromAutocomplete
    );

    return () => {
      if (autocompletePickUpRef1.current) {
        window.google.maps.event.clearInstanceListeners(autocompletePickUpRef1.current);
        autocompletePickUpRef1.current = null;
      }
    };
  }, [
    Location,
    setexitPickUpLocation,
    setPickupLatLng,
    setPickupFromAutocomplete,
    pickupInputId,
  ]);
  
  const handlePickupChange = (e) => {
    // Skip if we're in the middle of a selection from autocomplete
    if (isSelectingRef.current) return;
    
    const newValue = e.target.value;
    setexitPickUpLocation(newValue);
    
    // Don't reset validation if user is just correcting a typo in a valid selection
    if (!isPickupValid) {
      setIsPickupValid(false);
      setPickupFromAutocomplete(false); // Ensure this is set to false on manual input
    }
    
    // Reset lat/lng if manually typing - but only if the user is making significant changes
    if (newValue && newValue !== exitpickUpLocation) {
      // Remove console.log for Manual pickup input
    }
  };

  // Improve error messages - show validation error if validation is triggered and not valid
  const showPickupError = validationTriggered && !isPickupValid;

  return (
    <Box sx={{ width: '100%', px: 2, py: 2 }}>
      <Grid container spacing={3}>
        {/* Pick-up Location */}
        <Grid item xs={12}>
          <Typography variant="body2" sx={{ mb: 1, fontWeight: 500 }}>
            Pick Up Location
          </Typography>
          <TextField
            id={pickupInputId}
            fullWidth
            variant="outlined"
            placeholder="Where is your pick up?"
            value={exitpickUpLocation}
            onChange={handlePickupChange}
            disabled={selectedPort !== "Hourly"}
            error={showPickupError}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <MyLocationIcon />
                </InputAdornment>
              ),
            }}
          />
          {showPickupError && (
            <Alert severity="error" sx={{ mt: 1 }}>
              Please select location from dropdown suggestions
            </Alert>
          )}
        </Grid>
      </Grid>

      {/* CSS Fixes for Google Autocomplete */}
      <style>
        {`
          .pac-container {
            z-index: 10000 !important;
            background-color: #fff !important;
            border: 1px solid #ccc !important;
            width: 100% !important;
            min-width: 200px !important;
            max-width: 250px !important;
          }

          .pac-item {
            font-size: 15px !important;
            font-weight: 520 !important;
            color: #000 !important;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: ellipsis !important;
            width: 350px !important;
          }

          .pac-item-query {
            font-weight: bold !important;
            color: #000 !important;
          }

          @media (max-width: 1730px) {
            .pac-item {
              width: 250px !important;
            }
          }
          @media (max-width: 1400px) {
            .pac-item {
              width: 200px !important;
            }
          }
          @media (max-width: 1200px) {
            .pac-item {
              width: 300px !important;
            }
          }
          @media (max-width: 1024px) {
            .pac-item {
              width: 300px !important;
            }
          }
          @media (max-width: 768px) {
            .pac-item {
              width: 200px !important;
            }
          }
          @media (max-width: 480px) {
            .pac-item {
              width: 80px !important;
            }
          }
        `}
      </style>
    </Box>
  );
};

export default SearchBar1;
