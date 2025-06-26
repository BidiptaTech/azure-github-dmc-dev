import React, { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import {
  Box,
  TextField,
  Typography,
  Paper,
  InputAdornment,
  List,
  ListItem,
  ListItemText,
  ListItemIcon,
  useTheme,
  alpha,
} from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';
import AttractionsIcon from '@mui/icons-material/Attractions';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import {
  fetchLocalZone,
  setPicktype,
  setSelectbooking,
} from "@/slice/localtour/Localslice";

const AttractionRestaurantSearch = ({ onSelect, dayIndex = 0, onFocus, onBlur }) => {
  const theme = useTheme();
  const dispatch = useDispatch();
  const attractions = useSelector((state) => state.attractions.attractions);
  const restaurants = useSelector((state) => state.restaurants.restaurants);
  
  const [searchTerm, setSearchTerm] = useState("");
  const [showDropdown, setShowDropdown] = useState(false);
  const [selectedItem, setSelectedItem] = useState(null);
  const [focused, setFocused] = useState(false);
  const dropdownRef = useRef(null);
  
  // Create unique ID for the input
  const inputId = `attraction-restaurant-search-day-${dayIndex}`;

  // Filter attractions and restaurants based on search term
  const filteredAttractions = attractions?.filter(attraction => 
    attraction.attraction_name.toLowerCase().includes(searchTerm.toLowerCase())
  ) || [];
  
  const filteredRestaurants = restaurants?.filter(restaurant => 
    restaurant.restaurant_name.toLowerCase().includes(searchTerm.toLowerCase())
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

  // Handle selection of an attraction or restaurant
  const handleSelect = (item, type) => {
    const selectedData = {
      id: item.id,
      name: type === 'attraction' ? item.attraction_name : item.restaurant_name,
      type: type
    };
    
    setSelectedItem(selectedData);
    setSearchTerm(selectedData.name);
    setShowDropdown(false);
    
    // Create booking object for dispatch
    const booking = {
      service_details: {
        id: selectedData.id,
        name: selectedData.name
      }
    };
    
    // Call dispatch functions (from handleBookTransfer)
    dispatch(fetchLocalZone({ id: selectedData.id, type: selectedData.type }));
    dispatch(setSelectbooking(booking));
    dispatch(setPicktype(selectedData.type));
    
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
        placeholder="Search attractions or restaurants..."
        value={searchTerm}
        onChange={handleInputChange}
        onFocus={handleFocus}
        onBlur={handleBlur}
        onClick={() => setShowDropdown(true)}
        InputProps={{
          startAdornment: (
            <InputAdornment position="start">
              <SearchIcon sx={{ color: '#4caf50' }} />
            </InputAdornment>
          ),
          sx: {
            borderRadius: 2,
            bgcolor: 'white',
            '& .MuiOutlinedInput-root': {
              '& fieldset': {
                borderColor: alpha('#4caf50', 0.3),
                borderWidth: 2,
              },
              '&:hover fieldset': {
                borderColor: '#4caf50',
              },
              '&.Mui-focused fieldset': {
                borderColor: '#4caf50',
                borderWidth: 2,
              },
            },
          },
        }}
        sx={{
          '& .MuiInputLabel-root': {
            color: '#4caf50',
          },
          '& .MuiInputLabel-root.Mui-focused': {
            color: '#4caf50',
          },
        }}
      />
      
      {/* Dropdown */}
      {showDropdown && (searchTerm.length > 0 || filteredAttractions.length > 0 || filteredRestaurants.length > 0) && (
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
            border: `2px solid ${alpha('#4caf50', 0.3)}`,
            mt: 1,
          }}
        >
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
                  key={`attraction-${attraction.id}-day-${dayIndex}`}
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
                  <ListItemText primary={attraction.attraction_name} />
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
                  key={`restaurant-${restaurant.id}-day-${dayIndex}`}
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
                  <ListItemText primary={restaurant.restaurant_name} />
                </ListItem>
              ))}
            </Box>
          )}

          {/* No results message */}
          {searchTerm.length > 0 && filteredAttractions.length === 0 && filteredRestaurants.length === 0 && (
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

export default AttractionRestaurantSearch; 