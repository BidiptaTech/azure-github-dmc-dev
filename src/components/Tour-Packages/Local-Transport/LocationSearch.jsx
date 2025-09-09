import React, { useEffect, useRef, useState } from "react";
import { useSelector, useDispatch } from "react-redux";
import { setentrypickup, setentrydropoff, setPickupPlaceid, setDropoffPlaceid } from "@/slice/localtour/Localslice";
import {
  Box,
  TextField,
  Typography,
  Grid,
  InputAdornment,
  Alert,
} from '@mui/material';
import MyLocationIcon from '@mui/icons-material/MyLocation';
import LocationOnIcon from '@mui/icons-material/LocationOn';

const SearchBar = ({
  pickUpLocation,
  setPickUpLocation,
  dropOffLocation,
  setDropOffLocation,
  setPickupLatLng,
  setDropoffLatLng,
  Location,
  validationTriggered = false,
  setPickupFromAutocomplete,
  setDropoffFromAutocomplete,
  dayIndex = 0,
}) => {
  const autocompletePickUpRef1 = useRef(null);
  const autocompleteDropOffRef1 = useRef(null);
  const [isPickupValid, setIsPickupValid] = useState(true);
  const [isDropoffValid, setIsDropoffValid] = useState(true);
  const SelectedPort = useSelector((state) => state.localtour.selectedPort);

  const pickupInputId = `local-transport-pick-up-input-day-${dayIndex}`;
  const dropoffInputId = `local-transport-drop-off-input-day-${dayIndex}`;

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
      if (!inputElement) return;

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
          
          console.log(`Place selected: ${inputId}`, { lat, lng });

          // ✅ Use only the highlighted primary name (or first part of address)
          let formattedLocation =
            place.name || place.address_components?.[0]?.long_name || "";

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
        } else {
          console.error(`No geometry found for selected place in ${inputId}`);
        }
      });
    };

    initializeAutocomplete(
      pickupInputId,
      autocompletePickUpRef1,
      setPickUpLocation,
      setPickupLatLng,
      setIsPickupValid,
      setPickupFromAutocomplete
    );
    initializeAutocomplete(
      dropoffInputId,
      autocompleteDropOffRef1,
      setDropOffLocation,
      setDropoffLatLng,
      setIsDropoffValid,
      setDropoffFromAutocomplete
    );

    return () => {
      if (autocompletePickUpRef1.current) {
        window.google.maps.event.clearInstanceListeners(autocompletePickUpRef1.current);
        autocompletePickUpRef1.current = null;
      }
      if (autocompleteDropOffRef1.current) {
        window.google.maps.event.clearInstanceListeners(autocompleteDropOffRef1.current);
        autocompleteDropOffRef1.current = null;
      }
    };
  }, [
    Location,
    setPickUpLocation,
    setDropOffLocation,
    setPickupLatLng,
    setDropoffLatLng,
    setPickupFromAutocomplete,
    setDropoffFromAutocomplete,
    pickupInputId,
    dropoffInputId,
  ]);

  const handlePickupChange = (e) => {
    const newValue = e.target.value;
    setPickUpLocation(newValue);
    setIsPickupValid(false);
    setPickupFromAutocomplete(false); // Ensure this is set to false on manual input
    
    // Reset lat/lng if manually typing
    if (newValue) {
      // Optional: Reset lat/lng to encourage selection from dropdown
      // setPickupLatLng({});
    }
  };

  const handleDropoffChange = (e) => {
    const newValue = e.target.value;
    setDropOffLocation(newValue);
    setIsDropoffValid(false);
    setDropoffFromAutocomplete(false); // Ensure this is set to false on manual input
    
    // Reset lat/lng if manually typing
    if (newValue) {
      // Optional: Reset lat/lng to encourage selection from dropdown
      // setDropoffLatLng({});
    }
  };

  // Improve error messages
  const showPickupError = validationTriggered && !isPickupValid;
  const showDropoffError = validationTriggered && !isDropoffValid;

  return (
    <Box sx={{ width: '100%', px: 1.5, py: 1.5 }}>
      <Grid container spacing={2}>
        {/* Pick-up Location */}
        <Grid item xs={12} md={6}>
          <Typography variant="body2" sx={{ mb: 0.8, fontWeight: 700, fontSize: '0.85rem', color: '#000' }}>
            Pick Up Location
          </Typography>
          <TextField
            id={pickupInputId}
            fullWidth
            variant="outlined"
            placeholder="Where is your pick up?"
            value={pickUpLocation}
            onChange={handlePickupChange}
            disabled={!SelectedPort || SelectedPort !== "Point To Point"}
            error={showPickupError}
            sx={{
              '& .MuiOutlinedInput-root': {
                height: '47px',
              },
            }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <MyLocationIcon />
                </InputAdornment>
              ),
            }}
          />
          {showPickupError && (
            <Alert severity="error" sx={{ mt: 0.8, fontSize: '0.8rem' }}>
              Please select location from dropdown suggestions
            </Alert>
          )}
        </Grid>

        {/* Drop-off Location */}
        <Grid item xs={12} md={6}>
          <Typography variant="body2" sx={{ mb: 0.8, fontWeight: 700, fontSize: '0.85rem', color: '#000' }}>
            Drop Off Location
          </Typography>
          <TextField
            id={dropoffInputId}
            fullWidth
            variant="outlined"
            placeholder="Where is your drop off?"
            value={dropOffLocation}
            onChange={handleDropoffChange}
            disabled={!SelectedPort || SelectedPort !== "Point To Point"}
            error={showDropoffError}
            sx={{
              '& .MuiOutlinedInput-root': {
                height: '47px',
              },
            }}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <LocationOnIcon />
                </InputAdornment>
              ),
            }}
          />
          {showDropoffError && (
            <Alert severity="error" sx={{ mt: 0.8, fontSize: '0.8rem' }}>
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
            width: 250px !important;
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
              width: 100px !important;
            }
          }
          @media (max-width: 1024px) {
            .pac-item {
              width: 100px !important;
            }
          }
          @media (max-width: 768px) {
            .pac-item {
              width: 80px !important;
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

export default SearchBar;