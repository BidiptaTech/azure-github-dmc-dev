import React, { useEffect, useRef, useState } from "react";
import { useSelector, useDispatch } from "react-redux";
import {
  Box,
  TextField,
  Typography,
  Autocomplete,
  Grid,
  InputAdornment,
  Alert,
  Card,
  CardContent,
  Chip,
  Divider,
  Fade,
  useTheme,
  alpha,
  Tooltip,
  ClickAwayListener,
} from '@mui/material';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import MyLocationIcon from '@mui/icons-material/MyLocation';
import PlaceIcon from '@mui/icons-material/Place';
import HotelIcon from '@mui/icons-material/Hotel';
import AttractionsIcon from '@mui/icons-material/Attractions';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import AttractionRestaurantSearch from "./AttractionRestaurantSearch";
import PortCity from "./PortCity";
import {
  fetchLocalZone,
  setPicktype,
  setSelectbooking,
} from "@/slice/localtour/Localslice";
import { fetchHotels } from "@/slice/hotel/hotelSlice";
import { fetchAttractions } from "@/slice/attractions/attractionSlice";
import { fetchRestaurants } from "@/slice/restaurant/RestaurantsSlice";

// Drop-off Location Search Component
const DropOffLocationSearch = ({ onSelect, dayIndex = 0, onFocus, onBlur, disabled = false, picktype }) => {
  const theme = useTheme();
  const zone = useSelector((state) => state.localtour.zone);
  
  const [selectedItem, setSelectedItem] = useState(null);
  const [tooltipOpen, setTooltipOpen] = useState(false);

  const handleDisabledClick = () => {
    if (disabled) {
      setTooltipOpen(true);
      setTimeout(() => setTooltipOpen(false), 3000); // Auto-hide after 3 seconds
    }
  };

  const handleTooltipClose = () => {
    setTooltipOpen(false);
  };
  
  // Create grouped options array with headers from zone data
  const options = [];
  
  // Add Hotels section
  if (zone?.data?.hotels && zone.data.hotels.length > 0) {
    options.push({ id: 'hotels-header', name: 'Hotels', type: 'header', isHeader: true });
    zone.data.hotels.forEach(hotel => {
      options.push({
        id: hotel.hotel_unique_id,
        name: hotel.name,
        type: 'hotel',
        isHeader: false
      });
    });
  }
  
  // Add Attractions section
  if (zone?.data?.attractions && zone.data.attractions.length > 0) {
    options.push({ id: 'attractions-header', name: 'Attractions', type: 'header', isHeader: true });
    zone.data.attractions.forEach(attraction => {
      options.push({
        id: attraction.attraction_id,
        name: attraction.name,
        type: 'attraction',
        isHeader: false
      });
    });
  }
  
  // Add Restaurants section
  if (zone?.data?.restaurants && zone.data.restaurants.length > 0) {
    options.push({ id: 'restaurants-header', name: 'Restaurants', type: 'header', isHeader: true });
    zone.data.restaurants.forEach(restaurant => {
      options.push({
        id: restaurant.restaurant_id,
        name: restaurant.name,
        type: 'restaurant',
        isHeader: false
      });
    });
  }

  // Handle selection
  const handleSelect = (event, newValue) => {
    if (!newValue || newValue.isHeader) {
      setSelectedItem(null);
      return;
    }

    setSelectedItem(newValue);
    
    // Call parent onSelect if provided
    if (onSelect) {
      onSelect(newValue);
    }
  };

  // Render option component
  const renderOption = (props, option) => {
    // Render header
    if (option.isHeader) {
      const getHeaderColor = () => {
        switch (option.type) {
          case 'header':
            if (option.name === 'Hotels') return '#2196f3';
            if (option.name === 'Attractions') return '#ff9800';
            if (option.name === 'Restaurants') return '#4caf50';
            return '#666';
          default:
            return '#666';
        }
      };

      const getHeaderIcon = () => {
        if (option.name === 'Hotels') return <HotelIcon fontSize="small" />;
        if (option.name === 'Attractions') return <AttractionsIcon fontSize="small" />;
        if (option.name === 'Restaurants') return <RestaurantIcon fontSize="small" />;
        return null;
      };

      return (
        <Box 
          component="li" 
          {...props}
          sx={{
            p: 1.5,
            bgcolor: alpha(getHeaderColor(), 0.1),
            color: getHeaderColor(),
            fontWeight: 600,
            display: 'flex',
            alignItems: 'center',
            gap: 0.8,
            cursor: 'default !important',
            '&:hover': {
              bgcolor: alpha(getHeaderColor(), 0.1) + ' !important',
            }
          }}
        >
          {getHeaderIcon()}
          <Typography variant="subtitle2" sx={{ fontWeight: 600, color: getHeaderColor(), fontSize: '0.85rem' }}>
            {option.name}
          </Typography>
        </Box>
      );
    }

    // Render regular option
    const getIcon = () => {
      switch (option.type) {
        case 'hotel':
          return <HotelIcon sx={{ fontSize: '1rem', color: '#2196f3' }} />;
        case 'attraction':
          return <AttractionsIcon sx={{ fontSize: '1rem', color: '#ff9800' }} />;
        case 'restaurant':
          return <RestaurantIcon sx={{ fontSize: '1rem', color: '#4caf50' }} />;
        default:
          return null;
      }
    };

    return (
      <Box 
        component="li" 
        {...props}
        sx={{
          '&:hover': {
            bgcolor: alpha('#000', 0.05),
          },
          borderBottom: `1px solid ${alpha('#000', 0.05)}`,
        }}
      >
        <Box sx={{ display: 'flex', alignItems: 'center', width: '100%', gap: 0.8, pl: 1.5 }}>
          {getIcon()}
          <Typography noWrap sx={{ fontSize: '0.85rem' }}>{option.name}</Typography>
        </Box>
      </Box>
    );
  };

  // Get option label
  const getOptionLabel = (option) => {
    if (option?.isHeader) return '';
    return option?.name || '';
  };

  // Check if option equals value
  const isOptionEqualToValue = (option, value) => {
    if (!option || !value || option.isHeader || value.isHeader) return false;
    return option.id === value.id && option.type === value.type;
  };

  // Get unique key for options
  const getOptionKey = (option) => `${option.type}-${option.id}`;

  // Filter out headers from selectable options
  const getOptionDisabled = (option) => option.isHeader;

  return (
    <ClickAwayListener onClickAway={handleTooltipClose}>
      <Box sx={{ position: 'relative' }}>
        <Tooltip
          title="Please select a pickup location first to enable drop-off selection"
          open={disabled ? (tooltipOpen || undefined) : false}
          disableHoverListener={!disabled}
          disableFocusListener
          disableTouchListener={!disabled}
          arrow
          placement="top"
          enterDelay={disabled ? 500 : 0}
          leaveDelay={disabled ? 200 : 0}
          sx={{
            '& .MuiTooltip-tooltip': {
              bgcolor: '#1565c0',
              color: 'white',
              fontWeight: 600,
              fontSize: '0.9rem',
              lineHeight: 1.3,
              borderRadius: '10px',
              padding: '10px 14px',
              maxWidth: '300px',
              boxShadow: '0 6px 24px rgba(21, 101, 192, 0.3)',
              border: '1px solid rgba(255, 255, 255, 0.2)',
              backdropFilter: 'blur(10px)',
            },
            '& .MuiTooltip-arrow': {
              color: '#1565c0',
              '&::before': {
                border: '1px solid rgba(255, 255, 255, 0.2)',
              },
            },
          }}
        >
          <Box sx={{ position: 'relative' }}>
            {/* Invisible overlay to capture clicks when disabled */}
            {disabled && (
              <Box
                onClick={handleDisabledClick}
                onMouseDown={handleDisabledClick}
                sx={{
                  position: 'absolute',
                  top: 0,
                  left: 0,
                  right: 0,
                  bottom: 0,
                  zIndex: 1,
                  cursor: 'not-allowed',
                  backgroundColor: 'transparent',
                }}
              />
            )}
            <Autocomplete
              value={selectedItem}
              onChange={handleSelect}
              options={options}
              getOptionLabel={getOptionLabel}
              getOptionKey={getOptionKey}
              getOptionDisabled={getOptionDisabled}
              isOptionEqualToValue={isOptionEqualToValue}
              noOptionsText="No drop-off locations available"
              disabled={disabled}
              renderOption={renderOption}
              renderInput={(params) => (
                <TextField
                  {...params}
                  placeholder="Where is your drop off?"
                  fullWidth
                  variant="outlined"
                  disabled={disabled}
                  onFocus={onFocus}
                  onBlur={onBlur}
                  InputProps={{
                    ...params.InputProps,
                    startAdornment: (
                      <InputAdornment position="start">
                        <PlaceIcon sx={{ color: disabled ? '#ccc' : '#2196f3' }} />
                      </InputAdornment>
                    ),
                    sx: {
                      borderRadius: 2,
                      bgcolor: 'white',
                      '& .MuiOutlinedInput-root': {
                        '& fieldset': {
                          borderColor: alpha('#2196f3', 0.3),
                          borderWidth: 2,
                        },
                        '&:hover fieldset': {
                          borderColor: disabled ? alpha('#2196f3', 0.3) : '#2196f3',
                        },
                        '&.Mui-focused fieldset': {
                          borderColor: '#2196f3',
                          borderWidth: 2,
                        },
                      },
                    },
                  }}
                  sx={{
                    '& .MuiInputLabel-root': {
                      color: '#2196f3',
                    },
                    '& .MuiInputLabel-root.Mui-focused': {
                      color: '#2196f3',
                    },
                    '& .MuiOutlinedInput-root': {
                      height: '47px',
                    },
                  }}
                />
              )}
              ListboxProps={{
                style: {
                  maxHeight: '250px'
                }
              }}
              slotProps={{
                popper: {
                  sx: {
                    zIndex: 999999
                  }
                }
              }}
              forcePopupIcon={false}
            />
          </Box>
        </Tooltip>
      </Box>
    </ClickAwayListener>
  );
};

const SearchZone = ({
  currentbooking,
  picktype,
  setdroptype,
  dropOffLocation,
  setDropOffLocation,
  setPickupLatLng,
  setDropoffLatLng,
  setPickUpLocation,
  validationTriggered,
  dayIndex = 0,
}) => {
  const theme = useTheme();
  const dispatch = useDispatch();
  const autocompletePickUpRef = useRef(null);
  const autocompleteDropOffRef = useRef(null);
  const [isPickupValid, setIsPickupValid] = useState(true);
  const [isDropoffValid, setIsDropoffValid] = useState(true);
  const [pickupFocused, setPickupFocused] = useState(false);
  const [dropoffFocused, setDropoffFocused] = useState(false);
  const selectedPort = useSelector((state) => state.localtour.selectedPort);
  const zone = useSelector((state) => state.localtour.zone);
  const attractions = useSelector((state) => state.attractions.attractions);
  const restaurants = useSelector((state) => state.restaurants.restaurants);
  
  // City selection state
  const [selectedCity, setSelectedCity] = useState(null);
  const [cityError, setCityError] = useState(false);
  const [isCityEnabled, setIsCityEnabled] = useState(true);
  const [isPickupLocationEnabled, setIsPickupLocationEnabled] = useState(false);
  
  // Get additional selectors
  const country = useSelector((state) => state.tourPackages.searchCriteria.country);
  const tour = useSelector((state) => state.hotels.tourdetails);
  
  console.log("tour", tour);
  
  // Reset pickup location state when city changes or component mounts
  useEffect(() => {
    if (!selectedCity) {
      setIsPickupLocationEnabled(false);
    }
  }, [selectedCity]);

  // Debug effect to track isPickupLocationEnabled changes
  useEffect(() => {
    console.log("isPickupLocationEnabled changed to:", isPickupLocationEnabled);
  }, [isPickupLocationEnabled]);

  // Handle city selection
  const handleCitySelect = (city) => {
    console.log("City selected:", city);
    console.log("Current isPickupLocationEnabled:", isPickupLocationEnabled);
    setSelectedCity(city);
    
    if (city) {
      setCityError(false);
      // Disable pickup location until all API calls are successful
      console.log("Disabling pickup location - waiting for API responses");
      setIsPickupLocationEnabled(false);
      
      // Get search criteria for API calls
      const searchCriteria = {
        checkIn: "01/01/2024", // Default dates - you may want to get these from props or state
        checkOut: "02/01/2024",
        guests: {
          adults: 1,
          children: 0,
          infant: 0
        }
      };
      
      // Dispatch all three API calls simultaneously
      console.log("Dispatching all three APIs with params:", {
        city: `${city.name}, (${country})`,
        searchCriteria
      });
      
      const hotelPromise = dispatch(fetchHotels({ 
        location: `${city.name}, (${country})`, 
        ucheckIn: searchCriteria.checkIn,
        ucheckOut: searchCriteria.checkOut,
        guests: searchCriteria.guests
      }));
      
      const attractionPromise = dispatch(fetchAttractions({ 
        city: `${city.name}, (${country})`, 
        date: searchCriteria.checkIn,
        adults: searchCriteria.guests.adults,
        children: searchCriteria.guests.children,
        tour_id: tour?.tour_id || 0,
        selectedDate: searchCriteria.checkIn,
        fromMainSearch: false
      }));
      
      const restaurantPromise = dispatch(fetchRestaurants({ 
        city: `${city.name}, (${country})`, 
        date: searchCriteria.checkIn,
        adults: searchCriteria.guests.adults,
        children: searchCriteria.guests.children,
        tour_id: tour?.tour_id || 0,
        selectedDate: searchCriteria.checkIn,
        fromMainSearch: false
      }));
      
      // Wait for all three API calls to complete
      Promise.allSettled([hotelPromise, attractionPromise, restaurantPromise])
        .then((results) => {
          console.log("All API results:", results);
          
          const hotelResult = results[0];
          const attractionResult = results[1];
          const restaurantResult = results[2];
          
          const hotelSuccess = hotelResult.status === 'fulfilled' && !hotelResult.value.error;
          const attractionSuccess = attractionResult.status === 'fulfilled' && !attractionResult.value.error;
          const restaurantSuccess = restaurantResult.status === 'fulfilled' && !restaurantResult.value.error;
          
          console.log("API Success Status:", {
            hotels: hotelSuccess,
            attractions: attractionSuccess,
            restaurants: restaurantSuccess
          });
          
          if (hotelSuccess && attractionSuccess && restaurantSuccess) {
            console.log("All APIs succeeded - enabling pickup location");
            setIsPickupLocationEnabled(true);
          } else {
            console.log("One or more APIs failed - keeping pickup location disabled");
            console.error("Failed APIs:", {
              hotels: hotelResult.status === 'rejected' ? hotelResult.reason : hotelResult.value.error,
              attractions: attractionResult.status === 'rejected' ? attractionResult.reason : attractionResult.value.error,
              restaurants: restaurantResult.status === 'rejected' ? restaurantResult.reason : restaurantResult.value.error
            });
            setIsPickupLocationEnabled(false);
          }
        })
        .catch((error) => {
          console.error("Error in Promise.allSettled:", error);
          console.log("Promise.allSettled failed - keeping pickup location disabled");
          setIsPickupLocationEnabled(false);
        });
    } else {
      // If no city selected, disable pickup location
      console.log("No city selected - disabling pickup location");
      setIsPickupLocationEnabled(false);
    }
  };
  
  useEffect(() => {
    if (picktype === "hotel" && currentbooking?.hotelDetails) {
      console.log("Setting pickup from hotel booking:", currentbooking.hotelDetails);
      setPickUpLocation(currentbooking.hotelDetails.hotel_id);
      setPickupLatLng(currentbooking.hotelDetails.hotel_name);
    } else if (picktype === "attraction" && currentbooking?.service_details) {
      console.log("Setting pickup from attraction booking:", currentbooking.service_details);
      setPickUpLocation(currentbooking.service_details.id);
      setPickupLatLng(currentbooking.service_details.name);
    } else if (picktype === "restaurant" && currentbooking?.service_details) {
      console.log("Setting pickup from restaurant booking:", currentbooking.service_details);
      setPickUpLocation(currentbooking.service_details.id);
      setPickupLatLng(currentbooking.service_details.name);
    }
  }, [picktype, currentbooking, setPickUpLocation, setPickupLatLng]);

  const showPickupError = validationTriggered && !isPickupValid;
  const showDropoffError = validationTriggered && !isDropoffValid;

  return (
    <Box sx={{ width: '100%' }}>
      {/* Location Input Fields in Horizontal Layout - 3 columns for location fields */}
      <Grid container spacing={{ xs: 1.5, sm: 1.5, md: 1.5 }} alignItems="flex-end">
      
        {/* City Selection */}
        <Grid item xs={12} sm={12} md={3.5}>
          <Typography variant="body2" sx={{ mb: 0.8, fontWeight: 700, fontSize: '0.85rem', color: '#000' }}>
            City
          </Typography>
          <PortCity
            onLocationSelect={handleCitySelect}
            hasError={cityError}
            setError={setCityError}
            disabled={!isCityEnabled}
          />
        </Grid>

        {/* Pick-up Location */}
        <Grid item xs={12} sm={6} md={4.5}>
          <Typography variant="body2" sx={{ mb: 0.8, fontWeight: 700, fontSize: '0.85rem', color: '#000' }}>
            Pick Up Location 
          </Typography>
          <AttractionRestaurantSearch 
              onSelect={(selected) => {
                if (selected) {
                  const booking = {
                    service_details: {
                      id: selected.id,
                      name: selected.name
                    }
                  };
                  
                  console.log("✅ Pickup location selected:", selected.name, "ID:", selected.id);
                  
                  dispatch(fetchLocalZone({ id: selected.id, type: selected.type }));
                  dispatch(setSelectbooking(booking));
                  dispatch(setPicktype(selected.type));
                  
                  setPickUpLocation(selected.id);
                  setPickupLatLng(selected.name);
                  setIsPickupValid(true);
                }
              }}
              dayIndex={dayIndex}
              onFocus={() => setPickupFocused(true)}
              onBlur={() => setPickupFocused(false)}
              disabled={!isPickupLocationEnabled}
            />
        </Grid>

        {/* Drop-off Location */}
        <Grid item xs={12} sm={6} md={4}>
          <Typography variant="body2" sx={{ mb: 0.8, fontWeight: 700, fontSize: '0.85rem', color: '#000' }}>
            Drop Off Location
          </Typography>
            <DropOffLocationSearch 
              onSelect={(selected) => {
                if (selected) {
                  console.log("✅ Dropoff location selected:", selected.name, "ID:", selected.id, "Type:", selected.type);
                  
                  setDropOffLocation(selected.id);
                  setDropoffLatLng(selected.name);
                  setdroptype(selected.type);
                  setIsDropoffValid(true);
                }
              }}
              dayIndex={dayIndex}
              onFocus={() => setDropoffFocused(true)}
              onBlur={() => setDropoffFocused(false)}
              disabled={picktype === ""}
              picktype={picktype}
            />
        </Grid>

        
      </Grid>
    </Box>
  );
};

export default SearchZone;
