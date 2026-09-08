import React, { useEffect, useState } from 'react';
import { 
  Box, Typography, Grid, Chip, Checkbox, FormControlLabel, Divider, Alert, Button
} from '@mui/material';
import { CalendarToday, Hotel, Warning, Refresh, CheckCircle } from '@mui/icons-material';
import moment from 'moment';

/**
 * Night Selection component - Compact checkbox design with date range update handling
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
  const [previousDateRange, setPreviousDateRange] = useState(null);
  const [showDateUpdateAlert, setShowDateUpdateAlert] = useState(false);
  const [hasDateConflict, setHasDateConflict] = useState(false);

  // Check for date range updates
  useEffect(() => {
    if (dates && dates.length > 0) {
      const currentRange = {
        start: dates[0]?.format('YYYY-MM-DD'),
        end: dates[dates.length - 1]?.format('YYYY-MM-DD'),
        totalDays: dates.length
      };

      if (previousDateRange && 
          (previousDateRange.start !== currentRange.start || 
           previousDateRange.end !== currentRange.end)) {
        setShowDateUpdateAlert(true);
        
        // Check if current selected nights are still valid
        const selectedArray = Array.from(selectedNightIndices);
        const maxNightIndex = dates.length - 2; // -1 for length to index, -1 because last date is checkout
        const hasInvalidNights = selectedArray.some(nightIndex => nightIndex > maxNightIndex);
        
        setHasDateConflict(hasInvalidNights);
      }

      setPreviousDateRange(currentRange);
    }
  }, [dates, selectedNightIndices]);

  // Clear selected nights that are outside the new date range
  const handleClearInvalidNights = () => {
    const maxNightIndex = dates.length - 2;
    const validNights = Array.from(selectedNightIndices).filter(nightIndex => nightIndex <= maxNightIndex);
    
    const newSelectedIndices = new Set(validNights);
    setSelectedNightIndices(newSelectedIndices);
    setSelectedNights(newSelectedIndices.size);
    
    // Update hotel configurations
    const currentConfig = hotelConfigurations[activeHotelIndex];
    const currentHotelId = currentConfig?.hotelId;
    
    let checkInDate = null;
    let checkOutDate = null;
    
    if (newSelectedIndices.size > 0) {
      const sortedIndices = Array.from(newSelectedIndices).sort((a, b) => a - b);
      const firstIndex = sortedIndices[0];
      const lastIndex = sortedIndices[sortedIndices.length - 1];
      
      checkInDate = (dates[firstIndex] && dates[firstIndex].format) ? dates[firstIndex].format('YYYY-MM-DD') : null;
      checkOutDate = (dates[lastIndex + 1] && dates[lastIndex + 1].format) ? dates[lastIndex + 1].format('YYYY-MM-DD') : null;
    }
    
    if (!currentHotelId) {
      const updatedConfig = {
        ...currentConfig,
        nights: newSelectedIndices.size,
        selectedNightIndices: Array.from(newSelectedIndices),
        checkInDate: checkInDate,
        checkOutDate: checkOutDate,
        // Add individual hotel dates in DD/MM/YYYY format for Redux sync
        hotelCheckIn: checkInDate ? moment(checkInDate, 'YYYY-MM-DD').format('DD/MM/YYYY') : null,
        hotelCheckOut: checkOutDate ? moment(checkOutDate, 'YYYY-MM-DD').format('DD/MM/YYYY') : null
      };
      
      const updatedConfigurations = [...hotelConfigurations];
      updatedConfigurations[activeHotelIndex] = updatedConfig;
      setHotelConfigurations(updatedConfigurations);
    } else {
      const updatedConfigurations = hotelConfigurations.map(config => {
        if (config.hotelId === currentHotelId) {
          return {
            ...config,
            nights: newSelectedIndices.size,
            selectedNightIndices: Array.from(newSelectedIndices),
            checkInDate: checkInDate,
            checkOutDate: checkOutDate,
            // Add individual hotel dates in DD/MM/YYYY format for Redux sync
            hotelCheckIn: checkInDate ? moment(checkInDate, 'YYYY-MM-DD').format('DD/MM/YYYY') : null,
            hotelCheckOut: checkOutDate ? moment(checkOutDate, 'YYYY-MM-DD').format('DD/MM/YYYY') : null
          };
        }
        return config;
      });
      
      setHotelConfigurations(updatedConfigurations);
    }
    
    setHasDateConflict(false);
    setShowDateUpdateAlert(false);
  };

  // Dismiss the date update alert
  const handleDismissAlert = () => {
    setShowDateUpdateAlert(false);
    if (!hasDateConflict) {
      setHasDateConflict(false);
    }
  };

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

  // Handle checkbox change - Updated to work at hotel level
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
      
      checkInDate = (dates[firstIndex] && dates[firstIndex].format) ? dates[firstIndex].format('YYYY-MM-DD') : null;
      checkOutDate = (dates[lastIndex + 1] && dates[lastIndex + 1].format) ? dates[lastIndex + 1].format('YYYY-MM-DD') : null;
    }

    // Update ALL rooms of the same hotel with the same night selection
    const currentConfig = hotelConfigurations[activeHotelIndex];
    const currentHotelId = currentConfig?.hotelId;
    
    if (!currentHotelId) {
      // If no hotel selected, just update the active room
      const updatedConfig = {
        ...currentConfig,
        nights: newSelectedIndices.size,
        selectedNightIndices: Array.from(newSelectedIndices),
        checkInDate: checkInDate,
        checkOutDate: checkOutDate
      };
      
      const updatedConfigurations = [...hotelConfigurations];
      updatedConfigurations[activeHotelIndex] = updatedConfig;
      setHotelConfigurations(updatedConfigurations);
    } else {
      // Update ALL rooms of the same hotel
      const updatedConfigurations = hotelConfigurations.map(config => {
        if (config.hotelId === currentHotelId) {
          return {
            ...config,
            nights: newSelectedIndices.size,
            selectedNightIndices: Array.from(newSelectedIndices),
            checkInDate: checkInDate,
            checkOutDate: checkOutDate,
            // Add individual hotel dates in DD/MM/YYYY format for Redux sync
            hotelCheckIn: checkInDate ? moment(checkInDate, 'YYYY-MM-DD').format('DD/MM/YYYY') : null,
            hotelCheckOut: checkOutDate ? moment(checkOutDate, 'YYYY-MM-DD').format('DD/MM/YYYY') : null
          };
        }
        return config;
      });
      
      setHotelConfigurations(updatedConfigurations);
    }
  };

  // Calculate date range for summary
  const getDateRange = () => {
    if (selectedNightIndices.size === 0 || !dates || dates.length === 0) return null;
    
    const sortedIndices = Array.from(selectedNightIndices).sort((a, b) => a - b);
    const firstIndex = sortedIndices[0];
    const lastIndex = sortedIndices[sortedIndices.length - 1];
    const startDate = dates[firstIndex];
    const endDate = dates[lastIndex + 1];
    
    // Only return if both dates are valid
    if (!startDate || !endDate) return null;
    
    return { startDate, endDate };
  };

  const dateRange = getDateRange();
  const currentHotel = hotelConfigurations[activeHotelIndex];
  
  // Early return if dates are not available
  if (!dates || dates.length === 0) {
    return (
      <Box sx={{ mt: 2, p: 2, textAlign: 'center' }}>
        <Typography variant="body2" color="text.secondary">
          Loading dates...
        </Typography>
      </Box>
    );
  }
  
  return (
    <Box sx={{ mt: 1 }}>
      {/* Date Update Alert */}
      {(showDateUpdateAlert || hasDateConflict) && (
        <Alert
          severity={hasDateConflict ? "error" : "info"}
          sx={{ mb: 2 }}
          action={
            <Box sx={{ display: 'flex', gap: 1 }}>
              {hasDateConflict && (
                <Button
                  size="small"
                  variant="outlined"
                  color="error"
                  startIcon={<Refresh />}
                  onClick={handleClearInvalidNights}
                >
                  Fix Selection
                </Button>
              )}
              <Button
                size="small"
                variant="text"
                onClick={handleDismissAlert}
              >
                Dismiss
              </Button>
            </Box>
          }
        >
          <Typography variant="body2">
            {hasDateConflict ? (
              <>
                <strong>Date Conflict:</strong> Some selected nights are outside the updated date range. 
                Click "Fix Selection" to automatically adjust your selection.
              </>
            ) : (
              <>
                <strong>Date Range Updated:</strong> Tour dates have been updated. 
                Your current night selection is still valid.
              </>
            )}
          </Typography>
        </Alert>
      )}

      {/* Header with Hotel Name */}
      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 1 }}>
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          <Hotel sx={{ mr: 0.8, color: 'primary.main', fontSize: 18 }} />
          <Box>
            <Typography variant="h6" sx={{ color: 'primary.main', fontWeight: 600, lineHeight: 1.2, fontSize: '1rem' }}>
              Select Hotel Nights
            </Typography>
            <Typography variant="caption" sx={{ color: 'text.secondary', fontSize: '0.7rem' }}>
              Choose nights for {currentHotel?.hotelDetails?.hotel_name || 'this hotel'} (applies to all rooms)
            </Typography>
          </Box>
        </Box>
        
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.8 }}>
          {hasDateConflict && (
            <Chip 
              label="Date Issue"
              size="small"
              color="error"
              variant="filled"
              icon={<Warning />}
              sx={{ fontWeight: 600, height: 20, fontSize: '0.65rem' }}
            />
          )}
          
          {selectedNightIndices.size > 0 && (
            <Chip 
              label={`${selectedNightIndices.size} night${selectedNightIndices.size !== 1 ? 's' : ''}`}
              size="small"
              color={hasDateConflict ? "default" : "primary"}
              variant="filled"
              sx={{ 
                fontWeight: 600,
                fontSize: '0.65rem',
                height: 20
              }}
            />
          )}
        </Box>
      </Box>

      {/* Compact Checkbox Grid */}
      <Grid container spacing={0.8} sx={{ mb: 1.5 }}>
        {dates.slice(0, dates.length - 1).map((date, nightIndex) => {
          // Skip if date or nextDate is undefined
          if (!date || !dates[nightIndex + 1]) {
            return null;
          }
          
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
                  <Box sx={{ ml: 0.4 }}>
                    <Typography variant="body2" sx={{ 
                      fontWeight: 600, 
                      fontSize: '0.8rem',
                      color: isDisabled ? '#ff9800' : isSelected ? '#2e7d32' : '#666',
                      lineHeight: 1.2
                    }}>
                      Night {nightIndex + 1}
                      {isDisabled && (
                        <Typography component="span" variant="caption" sx={{ 
                          ml: 0.4, 
                          color: '#ff9800',
                          fontWeight: 400,
                          fontSize: '0.65rem'
                        }}>
                          (Required)
                        </Typography>
                      )}
                    </Typography>
                    <Typography variant="caption" sx={{ 
                      color: 'text.secondary',
                      display: 'block',
                      fontSize: '0.7rem',
                      lineHeight: 1.1
                    }}>
                      {date && nextDate ? `${date.format('MMM DD')} - ${nextDate.format('MMM DD')}` : 'Date loading...'}
                    </Typography>
                  </Box>
                }
                sx={{
                  width: '100%',
                  margin: 0,
                  padding: 0.8,
                  borderRadius: 1.5,
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
          p: 1, 
          bgcolor: 'rgba(76, 175, 80, 0.06)', 
          borderRadius: 1.5,
          border: '1px solid rgba(76, 175, 80, 0.2)'
        }}>
          <Box sx={{ display: 'flex', alignItems: 'center', flexWrap: 'wrap', gap: 0.8 }}>
            <CalendarToday sx={{ fontSize: 14, color: 'success.main' }} />
            <Typography variant="body2" sx={{ fontWeight: 600, color: 'success.main', fontSize: '0.8rem' }}>
              Hotel booked for {selectedNightIndices.size} night{selectedNightIndices.size !== 1 ? 's' : ''}
            </Typography>
            
            {dateRange && dateRange.startDate && dateRange.endDate && (
              <>
                <Divider orientation="vertical" flexItem sx={{ mx: 0.8 }} />
                <Typography variant="body2" sx={{ color: 'success.main', fontSize: '0.8rem' }}>
                  {dateRange.startDate.format('MMM DD')} - {dateRange.endDate.format('MMM DD, YYYY')}
                </Typography>
              </>
            )}
          </Box>
          
          {selectedNightIndices.size > 1 && (
            <Typography variant="caption" sx={{ 
              display: 'block', 
              mt: 0.4, 
              color: 'text.secondary',
              fontStyle: 'italic',
              fontSize: '0.65rem'
            }}>
              💡 Consecutive hotel nights selected - applies to all rooms in this hotel
            </Typography>
          )}
        </Box>
      )}

      {showDateUpdateAlert && (
        <Alert
          severity={hasDateConflict ? "error" : "success"}
          action={
            <>
              <Button
                size="small"
                variant="outlined"
                color={hasDateConflict ? "error" : "success"}
                onClick={handleClearInvalidNights}
                sx={{ mr: 1 }}
              >
                {hasDateConflict ? "Clear Invalid Nights" : "Accept"}
              </Button>
              <Button
                size="small"
                variant="outlined"
                color="inherit"
                onClick={handleDismissAlert}
              >
                Dismiss
              </Button>
            </>
          }
        >
          {hasDateConflict ? "Some selected nights are outside the new date range. Please clear them." : "Date range updated successfully."}
        </Alert>
      )}
    </Box>
  );
};

export default NightSelection; 