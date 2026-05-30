import React, { useCallback } from 'react';
import {
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Box,
  Typography,
} from '@mui/material';
import AccessTimeIcon from '@mui/icons-material/AccessTime';

const TimeSlotSelect = ({ value, onChange, selectedMealType, restaurantDetails, disabled, bookingDate, formSection }) => {
  // Get booking date from section if not provided as prop
  const sectionBookingDate = formSection?.bookingDate || formSection?.date;
  const effectiveBookingDate = bookingDate || sectionBookingDate || new Date().toISOString().split('T')[0];

  // Log props received from parent component
  React.useEffect(() => {
    console.log('TimeSlotSelect - Received props:', { 
      bookingDate, 
      effectiveBookingDate,
      sectionBookingDate,
      formSectionBookingDate: formSection?.bookingDate
    });
  }, [bookingDate, effectiveBookingDate, sectionBookingDate, formSection?.bookingDate]);

  // Generate time slots based on meal type and restaurant details
  const getTimeSlots = useCallback(() => {
    if (!selectedMealType || !restaurantDetails) {
      console.log('TimeSlotSelect: Missing meal type or restaurant details', { selectedMealType, restaurantDetails });
      return [];
    }

    const mealType = selectedMealType.toLowerCase();
    let openTime, closeTime;

    switch (mealType) {
      case 'breakfast':
        openTime = restaurantDetails.opening_time_bf;
        closeTime = restaurantDetails.closing_time_bf;
        break;
      case 'lunch':
        openTime = restaurantDetails.opening_time_lunch;
        closeTime = restaurantDetails.closing_time_lunch;
        break;
      case 'dinner':
        openTime = restaurantDetails.opening_time_dinner;
        closeTime = restaurantDetails.closing_time_dinner;
        break;
      default:
        console.log('TimeSlotSelect: Unknown meal type', mealType);
        return [];
    }

    console.log('TimeSlotSelect: Time range for', mealType, ':', { openTime, closeTime });

    if (!openTime || !closeTime) {
      console.log('TimeSlotSelect: Missing open/close times');
      return [];
    }

    try {
      // Convert time strings to Date objects for easier manipulation
      const start = new Date(`2000-01-01T${openTime}`);
      const end = new Date(`2000-01-01T${closeTime}`);
      const slots = [];

      // Generate slots every 30 minutes
      let current = new Date(start);
      while (current <= end) {
        slots.push(current.toLocaleTimeString('en-US', {
          hour: '2-digit',
          minute: '2-digit',
          hour12: true
        }));
        current = new Date(current.getTime() + 30 * 60000); // Add 30 minutes
      }

      console.log('TimeSlotSelect: Generated slots', slots);
      return slots;
    } catch (error) {
      console.error('TimeSlotSelect: Error generating time slots', error);
      return [];
    }
  }, [selectedMealType, restaurantDetails]);

  const timeSlots = getTimeSlots();

  // If no time slots are available, return default slots based on meal type
  const getDefaultTimeSlots = useCallback(() => {
    switch (selectedMealType?.toLowerCase()) {
      case 'breakfast':
        return ['07:00 AM', '08:00 AM', '09:00 AM', '10:00 AM'];
      case 'lunch':
        return ['12:00 PM', '01:00 PM', '02:00 PM', '03:00 PM'];
      case 'dinner':
        return ['06:00 PM', '07:00 PM', '08:00 PM', '09:00 PM'];
      default:
        return [];
    }
  }, [selectedMealType]);

  const availableSlots = timeSlots.length > 0 ? timeSlots : getDefaultTimeSlots();

  // Handle time slot selection
  const handleTimeSlotChange = useCallback((e) => {
    const selectedValue = e.target.value;
    console.log('TimeSlotSelect: Time slot selected', selectedValue);
    
    // Call the regular onChange handler with the original event
    // This maintains compatibility with the parent component
    onChange(e);
  }, [onChange]);

  return (
    <FormControl fullWidth disabled={!selectedMealType || disabled} size="small">
      <InputLabel sx={{ fontSize: '0.8rem' }}>Time Slot</InputLabel>
      <Select
        value={value}
        label="Time Slot"
        onChange={handleTimeSlotChange}
        sx={{
          height: '42px',
          '& .MuiSelect-select': {
            fontSize: '0.8rem'
          }
        }}
        renderValue={(selected) => {
          if (!selected) return <em>Select time slot</em>;
          return (
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <AccessTimeIcon fontSize="small" sx={{ mr: 1, color: '#4caf50', fontSize: 18 }} />
              <Typography sx={{ fontSize: '0.8rem' }}>{selected}</Typography>
            </Box>
          );
        }}
      >
        <MenuItem value="" sx={{ fontSize: '0.8rem' }}>
          <em>Select time slot</em>
        </MenuItem>
        {availableSlots.map((slot) => (
          <MenuItem key={slot} value={slot} sx={{ fontSize: '0.8rem' }}>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <AccessTimeIcon fontSize="small" sx={{ mr: 1, color: '#4caf50', fontSize: 18 }} />
              <Typography sx={{ fontSize: '0.8rem' }}>{slot}</Typography>
            </Box>
          </MenuItem>
        ))}
      </Select>
    </FormControl>
  );
};

export default React.memo(TimeSlotSelect); 