import React from 'react';
import { 
  Grid, 
  FormControl, 
  InputLabel, 
  Select, 
  MenuItem,
  Box,
  Typography,
  styled
} from '@mui/material';
import { useSelector } from 'react-redux';
import AccessTimeIcon from '@mui/icons-material/AccessTime';

// Styled components for better control
const StyledFormControl = styled(FormControl)(({ theme }) => ({
  '& .MuiInputLabel-shrink': {
    background: 'white',
    paddingRight: '8px',
  }
}));

const TimeSlotSelector = ({ selectedTimeSlot, onTimeSlotChange, attraction, disabled }) => {
  // Get attraction details from Redux store
  const attractionDetails = useSelector((state) => state.attractions.attractionDetails);
  
  // Get time slots from attraction details or use empty array as fallback
  const timeSlots = attractionDetails?.time_slots || [];

  return (
    <Grid item xs={12} sm={12} md={12}>
      <StyledFormControl fullWidth>
        <InputLabel 
          id="time-slot-label"
          sx={{
            backgroundColor: 'white',
            px: 1,
            fontSize: '0.8rem'
          }}
        >
          Select Time Slot
        </InputLabel>
        <Select
          labelId="time-slot-label"
          value={selectedTimeSlot}
          onChange={(e) => onTimeSlotChange(e.target.value)}
          disabled={disabled}
          displayEmpty
          size="small"
          sx={{
            height: '45px',
            '& .MuiSelect-select': {
              fontSize: '0.8rem'
            }
          }}
          renderValue={(selected) => {
            if (!selected) {
              return (
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, color: 'text.secondary' }}>
                  <AccessTimeIcon sx={{ fontSize: 18 }} />
                  <Typography sx={{ fontSize: '0.8rem' }}>Select Time Slot</Typography>
                </Box>
              );
            }
            return (
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                <AccessTimeIcon sx={{ color: 'primary.main', fontSize: 18 }} />
                <Typography sx={{ fontSize: '0.8rem' }}>{selected}</Typography>
              </Box>
            );
          }}
        >
          {timeSlots.length > 0 ? (
            timeSlots.map((slot) => (
              <MenuItem 
                key={slot} 
                value={slot}
                sx={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: 1,
                  fontSize: '0.8rem'
                }}
              >
                <AccessTimeIcon sx={{ color: 'primary.main', fontSize: 18 }} />
                <Typography sx={{ fontSize: '0.8rem' }}>{slot}</Typography>
              </MenuItem>
            ))
          ) : (
            <MenuItem disabled>
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, color: 'text.secondary' }}>
                <AccessTimeIcon sx={{ fontSize: 18 }} />
                <Typography sx={{ fontSize: '0.8rem' }}>No time slots available</Typography>
              </Box>
            </MenuItem>
          )}
        </Select>
      </StyledFormControl>
    </Grid>
  );
};

export default TimeSlotSelector; 