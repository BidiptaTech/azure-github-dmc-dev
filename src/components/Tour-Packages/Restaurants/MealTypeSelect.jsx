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
    <FormControl fullWidth disabled={disabled}>
      <InputLabel>Meal Type</InputLabel>
      <Select
        value={value}
        label="Meal Type"
        onChange={onChange}
      >
        {Object.entries(availableMealTypes).map(([type, isAvailable]) => {
          if (!isAvailable) return null;
          const timeSlot = getTimeSlot(type);
          return (
            <MenuItem key={type} value={type}>
              <Box sx={{ display: 'flex', alignItems: 'center', width: '100%' }}>
                <Box sx={{ display: 'flex', alignItems: 'center', mr: 2 }}>
                  {getMealIcon(type)}
                  <Typography sx={{ ml: 1 }}>{type}</Typography>
                </Box>
                {timeSlot && (
                  <Chip
                    size="small"
                    label={timeSlot}
                    sx={{
                      ml: 'auto',
                      bgcolor: 'rgba(76, 175, 80, 0.08)',
                      color: '#4caf50',
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