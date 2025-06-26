import React, { useEffect, useRef, useState } from "react";
import { useSelector, useDispatch } from "react-redux";
import {
  Box,
  TextField,
  Typography,
  Paper,
  Grid,
  InputAdornment,
  Alert,
  Card,
  CardContent,
  List,
  ListItem,
  ListItemText,
  ListItemIcon,
  Divider,
  Fade,
  useTheme,
  alpha,
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
  
  const [searchTerm, setSearchTerm] = useState("");
  const [showDropdown, setShowDropdown] = useState(false);
  const [selectedItem, setSelectedItem] = useState(null);
  const [focused, setFocused] = useState(false);
  const dropdownRef = useRef(null);
  
  // Create unique ID for the input
  const inputId = `dropoff-location-search-day-${dayIndex}`;

  // Filter locations based on search term
  const filteredHotels = zone?.data?.hotels?.filter(hotel => 
    hotel.name.toLowerCase().includes(searchTerm.toLowerCase())
  ) || [];
  
  const filteredAttractions = zone?.data?.attractions?.filter(attraction => 
    attraction.name.toLowerCase().includes(searchTerm.toLowerCase())
  ) || [];

  const filteredRestaurants = zone?.data?.restaurants?.filter(restaurant => 
    restaurant.name.toLowerCase().includes(searchTerm.toLowerCase())
  ) || [];

  // Handle input change
  const handleInputChange = (e) => {
    setSearchTerm(e.target.value);
    setShowDropdown(true);
  };

  // Handle focus events
  const handleFocus = () => {
    setFocused(true);
    setShowDropdown(true);
    if (onFocus) onFocus();
  };

  const handleBlur = () => {
    setFocused(false);
    if (onBlur) onBlur();
    // Delay hiding dropdown to allow for clicks
    setTimeout(() => setShowDropdown(false), 200);
  };

  // Handle selection of a location
  const handleSelect = (item, type) => {
    let selectedData = {};
    
    if (type === 'hotel') {
      selectedData = {
        id: item.hotel_unique_id,
        name: item.name,
        type: type
      };
    } else if (type === 'attraction') {
      selectedData = {
        id: item.attraction_id,
        name: item.name,
        type: type
      };
    } else if (type === 'restaurant') {
      selectedData = {
        id: item.restaurant_id,
        name: item.name,
        type: type
      };
    }
    
    setSelectedItem(selectedData);
    setSearchTerm(selectedData.name);
    setShowDropdown(false);
    
    // Call parent onSelect if provided
    if (onSelect) {
      onSelect(selectedData);
    }
  };

  // Close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setShowDropdown(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  return (
    <Box sx={{ position: 'relative' }} ref={dropdownRef}>
      <TextField
        id={inputId}
        fullWidth
        variant="outlined"
        placeholder="Where is your drop off?"
        value={searchTerm}
        onChange={handleInputChange}
        onFocus={handleFocus}
        onBlur={handleBlur}
        onClick={() => !disabled && setShowDropdown(true)}
        disabled={disabled}
        InputProps={{
          startAdornment: (
            <InputAdornment position="start">
              <PlaceIcon sx={{ color: '#2196f3' }} />
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
                borderColor: '#2196f3',
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
      
      {/* Dropdown */}
      {showDropdown && !disabled && zone?.data && (
        <Paper
          elevation={8}
          sx={{
            position: 'absolute',
            top: '100%',
            left: 0,
            right: 0,
            maxHeight: 300,
            overflow: 'auto',
            zIndex: 10001,
            borderRadius: 2,
            border: `2px solid ${alpha('#2196f3', 0.3)}`,
            mt: 1,
          }}
        >
          {/* Hotels Section */}
          {filteredHotels.length > 0 && (
            <Box>
              <Typography
                variant="subtitle2"
                sx={{
                  p: 2,
                  bgcolor: alpha('#2196f3', 0.1),
                  color: '#2196f3',
                  fontWeight: 600,
                  display: 'flex',
                  alignItems: 'center',
                  gap: 1,
                }}
              >
                <HotelIcon fontSize="small" />
                Hotels
              </Typography>
              {filteredHotels.map((hotel) => (
                <ListItem
                  key={`hotel-${hotel.hotel_unique_id}-day-${dayIndex}`}
                  button
                  onClick={() => handleSelect(hotel, 'hotel')}
                  sx={{
                    '&:hover': {
                      bgcolor: alpha('#2196f3', 0.05),
                    },
                    borderBottom: `1px solid ${alpha('#000', 0.05)}`,
                  }}
                >
                  <ListItemIcon>
                    <HotelIcon sx={{ color: '#2196f3' }} />
                  </ListItemIcon>
                  <ListItemText primary={hotel.name} />
                </ListItem>
              ))}
            </Box>
          )}

          {/* Attractions Section */}
          {filteredAttractions.length > 0 && (
            <Box>
              <Typography
                variant="subtitle2"
                sx={{
                  p: 2,
                  bgcolor: alpha('#ff9800', 0.1),
                  color: '#ff9800',
                  fontWeight: 600,
                  display: 'flex',
                  alignItems: 'center',
                  gap: 1,
                }}
              >
                <AttractionsIcon fontSize="small" />
                Attractions
              </Typography>
              {filteredAttractions.map((attraction) => (
                <ListItem
                  key={`attraction-${attraction.attraction_id}-day-${dayIndex}`}
                  button
                  onClick={() => handleSelect(attraction, 'attraction')}
                  sx={{
                    '&:hover': {
                      bgcolor: alpha('#ff9800', 0.05),
                    },
                    borderBottom: `1px solid ${alpha('#000', 0.05)}`,
                  }}
                >
                  <ListItemIcon>
                    <AttractionsIcon sx={{ color: '#ff9800' }} />
                  </ListItemIcon>
                  <ListItemText primary={attraction.name} />
                </ListItem>
              ))}
            </Box>
          )}

          {/* Restaurants Section */}
          {filteredRestaurants.length > 0 && (
            <Box>
              <Typography
                variant="subtitle2"
                sx={{
                  p: 2,
                  bgcolor: alpha('#4caf50', 0.1),
                  color: '#4caf50',
                  fontWeight: 600,
                  display: 'flex',
                  alignItems: 'center',
                  gap: 1,
                }}
              >
                <RestaurantIcon fontSize="small" />
                Restaurants
              </Typography>
              {filteredRestaurants.map((restaurant) => (
                <ListItem
                  key={`restaurant-${restaurant.restaurant_id}-day-${dayIndex}`}
                  button
                  onClick={() => handleSelect(restaurant, 'restaurant')}
                  sx={{
                    '&:hover': {
                      bgcolor: alpha('#4caf50', 0.05),
                    },
                    borderBottom: `1px solid ${alpha('#000', 0.05)}`,
                  }}
                >
                  <ListItemIcon>
                    <RestaurantIcon sx={{ color: '#4caf50' }} />
                  </ListItemIcon>
                  <ListItemText primary={restaurant.name} />
                </ListItem>
              ))}
            </Box>
          )}

          {/* No results message */}
          {searchTerm.length > 0 && 
           filteredHotels.length === 0 && 
           filteredAttractions.length === 0 && 
           filteredRestaurants.length === 0 && (
            <ListItem>
              <ListItemText 
                primary="No matching results found" 
                sx={{ 
                  color: '#757575', 
                  fontStyle: 'italic',
                  textAlign: 'center',
                  py: 2
                }}
              />
            </ListItem>
          )}
        </Paper>
      )}
    </Box>
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
  console.log("attractions", attractions);
  console.log("restaurants", restaurants);
  console.log("zone", zone);
  
  useEffect(() => {
    if (picktype === "hotel") {
      setPickUpLocation(currentbooking?.hotelDetails?.hotel_id);
      setPickupLatLng(currentbooking?.hotelDetails?.hotel_name);
    } else if (picktype === "attraction") {
      setPickUpLocation(currentbooking?.service_details?.id);
      setPickupLatLng(currentbooking?.service_details?.name);
    } else if (picktype === "restaurant") {
      setPickUpLocation(currentbooking?.service_details?.id);
      setPickupLatLng(currentbooking?.service_details?.name);
    }
  });

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
                setDropOffLocation(selected.id);
                setDropoffLatLng(selected.name);
                setdroptype(selected.type);
                setIsDropoffValid(true);
                console.log(
                  "Selected drop-off location:",
                  selected.name,
                  "with ID:",
                  selected.id,
                  "Type:",
                  selected.type
                );
              }
            }}
            dayIndex={dayIndex}
            onFocus={() => setDropoffFocused(true)}
            onBlur={() => setDropoffFocused(false)}
            disabled={picktype === ""}
            picktype={picktype}
          />

          {/* Disabled State Message */}
          {picktype === "" && (
            <Box mt={1}>
              <Alert 
                severity="info" 
                variant="outlined"
                sx={{ 
                  borderRadius: 2,
                  bgcolor: alpha('#2196f3', 0.05),
                  '& .MuiAlert-message': {
                    color: '#1976d2',
                    fontWeight: 500,
                  }
                }}
              >
                Please select a pickup location first to enable drop-off selection
              </Alert>
            </Box>
          )}
        </Grid>
      </Grid>
    </Box>
  );
};

export default SearchZone;
