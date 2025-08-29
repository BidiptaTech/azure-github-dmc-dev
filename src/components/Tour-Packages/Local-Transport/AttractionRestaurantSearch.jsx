import React, { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import {
  Box,
  TextField,
  Typography,
  Autocomplete,
  InputAdornment,
  Chip,
  useTheme,
  alpha,
} from '@mui/material';
import SearchIcon from '@mui/icons-material/Search';
import AttractionsIcon from '@mui/icons-material/Attractions';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import HotelIcon from '@mui/icons-material/Hotel';
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
  const hotels = useSelector((state) => state.hotels.hotels);
  
  const [selectedItem, setSelectedItem] = useState(null);
  
  // Create grouped options array with headers
  const options = [];
  
  // Add Hotels section
  if (hotels && hotels.length > 0) {
    options.push({ id: 'hotels-header', name: 'Hotels', type: 'header', isHeader: true });
    hotels.forEach(hotel => {
      options.push({
        id: hotel.id,
        name: hotel.hotel_name,
        type: 'hotel',
        isHeader: false
      });
    });
  }
  
  // Add Attractions section
  if (attractions && attractions.length > 0) {
    options.push({ id: 'attractions-header', name: 'Attractions', type: 'header', isHeader: true });
    attractions.forEach(attraction => {
      options.push({
        id: attraction.id,
        name: attraction.attraction_name,
        type: 'attraction',
        isHeader: false
      });
    });
  }
  
  // Add Restaurants section
  if (restaurants && restaurants.length > 0) {
    options.push({ id: 'restaurants-header', name: 'Restaurants', type: 'header', isHeader: true });
    restaurants.forEach(restaurant => {
      options.push({
        id: restaurant.id,
        name: restaurant.restaurant_name,
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
    
    // Create booking object for dispatch
    const booking = {
      service_details: {
        id: newValue.id,
        name: newValue.name
      }
    };
    
    // Call dispatch functions
    dispatch(fetchLocalZone({ id: newValue.id, type: newValue.type }));
    dispatch(setSelectbooking(booking));
    dispatch(setPicktype(newValue.type));
    
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
    <Autocomplete
      value={selectedItem}
      onChange={handleSelect}
      options={options}
      getOptionLabel={getOptionLabel}
      getOptionKey={getOptionKey}
      getOptionDisabled={getOptionDisabled}
      isOptionEqualToValue={isOptionEqualToValue}
      noOptionsText="No hotels, attractions or restaurants found"
      renderOption={renderOption}
      renderInput={(params) => (
        <TextField
          {...params}
          placeholder="Search hotels, attractions or restaurants..."
          fullWidth
          variant="outlined"
          onFocus={onFocus}
          onBlur={onBlur}
          InputProps={{
            ...params.InputProps,
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
            '& .MuiOutlinedInput-root': {
              height: '47px',
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
  );
};

export default AttractionRestaurantSearch; 