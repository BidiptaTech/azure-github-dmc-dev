import React from 'react';
import { 
  Box, Typography, Grid, Chip, Checkbox, FormControlLabel, Divider
} from '@mui/material';
import { CalendarToday, Hotel } from '@mui/icons-material';

/**
 * Night Selection component - Compact checkbox design
 * @param {Object} props Component props
 * @returns {JSX.Element} Night selection component
 */
const NightSelection = ({ 
  dates,
  selectedNightIndices,
  setSelectedNightIndices,
  setSelectedNights,
  hotelConfigurations,
  activeHotelIndex,
  setHotelConfigurations
}) => {
  // Helper function to check if nights are consecutive
  const areNightsConsecutive = (nightIndices) => {
    if (nightIndices.length <= 1) return true;
    
    const sortedIndices = [...nightIndices].sort((a, b) => a - b);
    for (let i = 1; i < sortedIndices.length; i++) {
      if (sortedIndices[i] - sortedIndices[i - 1] !== 1) {
        return false;
      }
    }
    return true;
  };

  // Handle checkbox change
  const handleNightCheckboxChange = (nightIndex, isChecked) => {
    const newSelectedIndices = new Set(selectedNightIndices);
    
    if (isChecked) {
      newSelectedIndices.add(nightIndex);
    } else {
      // If unchecking, check if it would create a gap
      newSelectedIndices.delete(nightIndex);
      
      // Convert to array and check if remaining nights are consecutive
      const remainingNights = Array.from(newSelectedIndices);
      
      if (remainingNights.length > 1 && !areNightsConsecutive(remainingNights)) {
        // Prevent unchecking as it would create a gap
        return;
      }
    }
    
    setSelectedNightIndices(newSelectedIndices);
    setSelectedNights(newSelectedIndices.size);
    
    // Calculate check-in and check-out dates from selected nights
    let checkInDate = null;
    let checkOutDate = null;
    
    if (newSelectedIndices.size > 0) {
      const sortedIndices = Array.from(newSelectedIndices).sort((a, b) => a - b);
      const firstIndex = sortedIndices[0];
      const lastIndex = sortedIndices[sortedIndices.length - 1];
      
      checkInDate = dates[firstIndex] ? dates[firstIndex].format('YYYY-MM-DD') : null;
      checkOutDate = dates[lastIndex + 1] ? dates[lastIndex + 1].format('YYYY-MM-DD') : null;
    }
    
    // Update the active hotel configuration
    const updatedConfig = {
      ...hotelConfigurations[activeHotelIndex],
      nights: newSelectedIndices.size,
      selectedNightIndices: Array.from(newSelectedIndices),
      checkInDate: checkInDate,
      checkOutDate: checkOutDate
    };
    
    const updatedConfigurations = [...hotelConfigurations];
    updatedConfigurations[activeHotelIndex] = updatedConfig;
    setHotelConfigurations(updatedConfigurations);
  };

  // Calculate date range for summary
  const getDateRange = () => {
    if (selectedNightIndices.size === 0) return null;
    
    const sortedIndices = Array.from(selectedNightIndices).sort((a, b) => a - b);
    const firstIndex = sortedIndices[0];
    const lastIndex = sortedIndices[sortedIndices.length - 1];
    const startDate = dates[firstIndex];
    const endDate = dates[lastIndex + 1];
    
    return { startDate, endDate };
  };

  const dateRange = getDateRange();
  const currentHotel = hotelConfigurations[activeHotelIndex];
  
  return (
    <Box sx={{ mt: 2 }}>
      {/* Header with Hotel Name */}
      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2 }}>
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          <Hotel sx={{ mr: 1, color: 'primary.main', fontSize: 20 }} />
          <Box>
            <Typography variant="h6" sx={{ color: 'primary.main', fontWeight: 600, lineHeight: 1.2 }}>
              Select Nights
            </Typography>
            <Typography variant="caption" sx={{ color: 'text.secondary' }}>
              Selected nights for {currentHotel?.hotelName || 'Hotel'}
            </Typography>
          </Box>
        </Box>
        
        {selectedNightIndices.size > 0 && (
          <Chip 
            label={`${selectedNightIndices.size} night${selectedNightIndices.size !== 1 ? 's' : ''}`}
            size="small"
            color="primary"
            variant="filled"
          />
        )}
      </Box>

      {/* Compact Checkbox Grid */}
      <Grid container spacing={1} sx={{ mb: 2 }}>
        {dates.slice(0, dates.length - 1).map((date, nightIndex) => {
          const isSelected = selectedNightIndices.has(nightIndex);
          const nextDate = dates[nightIndex + 1];
          
          // Check if this night can be unchecked
          const selectedArray = Array.from(selectedNightIndices).sort((a, b) => a - b);
          let canUncheck = true;
          if (isSelected && selectedArray.length > 1) {
            const testIndices = selectedArray.filter(idx => idx !== nightIndex);
            canUncheck = testIndices.length <= 1 || areNightsConsecutive(testIndices);
          }
          
          const isDisabled = isSelected && !canUncheck;
          
          return (
            <Grid item xs={6} sm={4} md={3} lg={2} key={nightIndex}>
              <FormControlLabel
                control={
                  <Checkbox
                    checked={isSelected}
                    onChange={(event) => handleNightCheckboxChange(nightIndex, event.target.checked)}
                    disabled={isDisabled}
                    size="small"
                    sx={{
                      color: isDisabled ? '#ff9800' : '#3554D1',
                      '&.Mui-checked': {
                        color: isDisabled ? '#ff9800' : '#4caf50',
                      },
                      '&.Mui-disabled': {
                        color: '#ff9800 !important',
                      }
                    }}
                  />
                }
                label={
                  <Box sx={{ ml: 0.5 }}>
                    <Typography variant="body2" sx={{ 
                      fontWeight: 600, 
                      fontSize: '0.875rem',
                      color: isDisabled ? '#ff9800' : isSelected ? '#2e7d32' : '#666',
                      lineHeight: 1.2
                    }}>
                      Night {nightIndex + 1}
                      {isDisabled && (
                        <Typography component="span" variant="caption" sx={{ 
                          ml: 0.5, 
                          color: '#ff9800',
                          fontWeight: 400,
                          fontSize: '0.7rem'
                        }}>
                          (Required)
                        </Typography>
                      )}
                    </Typography>
                    <Typography variant="caption" sx={{ 
                      color: 'text.secondary',
                      display: 'block',
                      fontSize: '0.75rem',
                      lineHeight: 1.1
                    }}>
                      {date.format('MMM DD')} - {nextDate.format('MMM DD')}
                    </Typography>
                  </Box>
                }
                sx={{
                  width: '100%',
                  margin: 0,
                  padding: 1,
                  borderRadius: 2,
                  border: '1px solid',
                  borderColor: isDisabled ? '#ff9800' : isSelected ? '#4caf50' : '#e0e0e0',
                  bgcolor: isDisabled ? 'rgba(255, 152, 0, 0.05)' : isSelected ? 'rgba(76, 175, 80, 0.05)' : '#ffffff',
                  transition: 'all 0.2s ease',
                  '&:hover': {
                    borderColor: isDisabled ? '#ff9800' : isSelected ? '#2e7d32' : '#3554D1',
                    bgcolor: isDisabled ? 'rgba(255, 152, 0, 0.08)' : isSelected ? 'rgba(76, 175, 80, 0.08)' : 'rgba(53, 84, 209, 0.05)',
                  },
                  '& .MuiFormControlLabel-label': {
                    width: '100%'
                  }
                }}
              />
            </Grid>
          );
        })}
      </Grid>

      {/* Compact Summary */}
      {selectedNightIndices.size > 0 && (
        <Box sx={{ 
          p: 1.5, 
          bgcolor: 'rgba(76, 175, 80, 0.06)', 
          borderRadius: 2,
          border: '1px solid rgba(76, 175, 80, 0.2)'
        }}>
          <Box sx={{ display: 'flex', alignItems: 'center', flexWrap: 'wrap', gap: 1 }}>
            <CalendarToday sx={{ fontSize: 16, color: 'success.main' }} />
            <Typography variant="body2" sx={{ fontWeight: 600, color: 'success.main' }}>
              {selectedNightIndices.size} night{selectedNightIndices.size !== 1 ? 's' : ''} selected
            </Typography>
            
            {dateRange && (
              <>
                <Divider orientation="vertical" flexItem sx={{ mx: 1 }} />
                <Typography variant="body2" sx={{ color: 'success.main' }}>
                  {dateRange.startDate.format('MMM DD')} - {dateRange.endDate.format('MMM DD, YYYY')}
                </Typography>
              </>
            )}
          </Box>
          
          {selectedNightIndices.size > 1 && (
            <Typography variant="caption" sx={{ 
              display: 'block', 
              mt: 0.5, 
              color: 'text.secondary',
              fontStyle: 'italic'
            }}>
              💡 Consecutive nights selected - middle nights cannot be deselected
            </Typography>
          )}
        </Box>
      )}
    </Box>
  );
};

export default NightSelection; 