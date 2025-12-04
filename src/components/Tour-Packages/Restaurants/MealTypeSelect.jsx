import React from 'react';
import {
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Box,
  Typography,
  Chip,
} from '@mui/material';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import WbSunnyIcon from '@mui/icons-material/WbSunny';
import WbTwilightIcon from '@mui/icons-material/WbTwilight';
import NightsStayIcon from '@mui/icons-material/NightsStay';

const MealTypeSelect = ({ value, onChange, restaurantDetails, disabled }) => {
  // Get available meal types from restaurant details
  const availableMealTypes = {
    Breakfast: restaurantDetails?.breakfast_available === 1,
    Lunch: restaurantDetails?.lunch_available === 1,
    Dinner: restaurantDetails?.dinner_available === 1
  };

  // Get time slots for each meal type
  const getTimeSlot = (type) => {
    switch (type.toLowerCase()) {
      case 'breakfast':
        return restaurantDetails?.breakfast_available === 1
          ? `${restaurantDetails.opening_time_bf} - ${restaurantDetails.closing_time_bf}`
          : null;
      case 'lunch':
        return restaurantDetails?.lunch_available === 1
          ? `${restaurantDetails.opening_time_lunch} - ${restaurantDetails.closing_time_lunch}`
          : null;
      case 'dinner':
        return restaurantDetails?.dinner_available === 1
          ? `${restaurantDetails.opening_time_dinner} - ${restaurantDetails.closing_time_dinner}`
          : null;
      default:
        return null;
    }
  };

  // Get icon for meal type
  const getMealIcon = (type) => {
    switch (type.toLowerCase()) {
      case 'breakfast':
        return <WbSunnyIcon fontSize="small" />;
      case 'lunch':
        return <WbTwilightIcon fontSize="small" />;
      case 'dinner':
        return <NightsStayIcon fontSize="small" />;
      default:
        return <RestaurantIcon fontSize="small" />;
    }
  };

  return (
    <FormControl fullWidth disabled={disabled} size="small">
      <InputLabel sx={{ fontSize: '0.8rem' }}>Meal Type</InputLabel>
      <Select
        value={value}
        label="Meal Type"
        onChange={onChange}
        sx={{
          height: '42px',
          '& .MuiSelect-select': {
            fontSize: '0.8rem'
          }
        }}
        renderValue={(selected) => {
          if (!selected) return <em>Select meal type</em>;
          return (
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              {getMealIcon(selected)}
              <Typography sx={{ ml: 1, fontSize: '0.8rem' }}>{selected}</Typography>
            </Box>
          );
        }}
      >
        <MenuItem value="" sx={{ fontSize: '0.8rem' }}>
          <em>Select meal type</em>
        </MenuItem>
        {Object.entries(availableMealTypes).map(([type, isAvailable]) => {
          if (!isAvailable) return null;
          const timeSlot = getTimeSlot(type);
          return (
            <MenuItem key={type} value={type} sx={{ fontSize: '0.8rem' }}>
              <Box sx={{ display: 'flex', alignItems: 'center', width: '100%' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', mr: 1.5 }}>
                  {getMealIcon(type)}
                  <Typography sx={{ ml: 1, fontSize: '0.8rem' }}>{type}</Typography>
                </Box>
                {timeSlot && (
                  <Chip
                    size="small"
                    label={timeSlot}
                    sx={{
                      ml: 'auto',
                      bgcolor: 'rgba(76, 175, 80, 0.08)',
                      color: '#4caf50',
                      fontSize: '0.7rem',
                      height: '18px'
                    }}
                  />
                )}
              </Box>
            </MenuItem>
          );
        })}
      </Select>
    </FormControl>
  );
};

export default MealTypeSelect; 