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
import {
  fetchLocalZone,
  setPicktype,
  setSelectbooking,
} from "@/slice/localtour/Localslice";

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
            p: 2,
            bgcolor: alpha(getHeaderColor(), 0.1),
            color: getHeaderColor(),
            fontWeight: 600,
            display: 'flex',
            alignItems: 'center',
            gap: 1,
            cursor: 'default !important',
            '&:hover': {
              bgcolor: alpha(getHeaderColor(), 0.1) + ' !important',
            }
          }}
        >
          {getHeaderIcon()}
          <Typography variant="subtitle2" sx={{ fontWeight: 600, color: getHeaderColor() }}>
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
        <Box sx={{ display: 'flex', alignItems: 'center', width: '100%', gap: 1, pl: 2 }}>
          {getIcon()}
          <Typography noWrap>{option.name}</Typography>
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
              fontSize: '1rem',
              lineHeight: 1.4,
              borderRadius: '12px',
              padding: '12px 16px',
              maxWidth: '320px',
              boxShadow: '0 8px 32px rgba(21, 101, 192, 0.3)',
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
                  }}
                />
              )}
              ListboxProps={{
                style: {
                  maxHeight: '300px'
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
      {/* Location Input Fields in Horizontal Layout */}
      <Grid container spacing={3}>
      
        {/* Pick-up Location */}
        <Grid item xs={12} md={6}>
          <Typography variant="body2" sx={{ mb: 1, fontWeight: 500 }}>
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
            />
        </Grid>

        {/* Drop-off Location */}
        <Grid item xs={12} md={6}>
          <Typography variant="body2" sx={{ mb: 1, fontWeight: 500 }}>
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
