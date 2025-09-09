import React from 'react';
import { 
  Box, Typography, Grid, FormControl, InputLabel, Select,
  MenuItem, IconButton, CircularProgress, Paper, Chip
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import HotelIcon from '@mui/icons-material/Hotel';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
import InfoIcon from '@mui/icons-material/Info';
import HotelListing from '../HotelListing';

/**
 * Hotel Selection Form component
 * @param {Object} props Component props
 * @returns {JSX.Element} Hotel selection form component
 */
const HotelSelectionForm = ({
  selectedHotel,
  setSelectedHotel,
  roomType,
  setRoomType,
  bedType,
  setBedType,
  roomTypes,
  bedTypes,
  roomDataStatus,
  hotelConfigurations,
  activeHotelIndex,
  renderRoomDataLoadingIndicator,
  renderMealPlanSection,
  renderNightSelection,
  searchCriteria
}) => {
  
  // console.log("HotelSelectionForm render:", {
  //   selectedHotel,
  //   roomType,
  //   roomTypes,
  //   roomDataStatus,
  //   roomTypesCount: roomTypes?.length || 0
  // });
  
  // Handle room type selection
  const handleRoomTypeChange = (e) => {
    const selected = e.target.value;
    console.log("Room type selected:", selected, "from options:", roomTypes);

    setRoomType(selected);
    setBedType(''); // Reset bed type when room type changes
  };
  
  // Handle bed type selection
  const handleBedTypeChange = (e) => {
    const selected = e.target.value;
    console.log("Bed type selected:", selected);
    setBedType(selected);
  };

  // Get current hotel configuration
  const currentConfig = hotelConfigurations[activeHotelIndex];
  const selectedNights = currentConfig?.selectedNightIndices?.length || 0;
  
  return (
    <Box>
      {/* Main Selection Row */}
      <Grid container spacing={1.5} sx={{ mb: 1.5, alignItems: 'flex-start' }}>
        {/* Hotel Selection */}
        <Grid item xs={12} md={2.5}>
          <Typography 
            variant="subtitle1" 
            fontWeight={500} 
            sx={{ 
              mb: 1, 
              fontSize: '0.875rem',
              color: 'text.primary',
              minHeight: '21px',
              display: 'flex',
              alignItems: 'center'
            }}
          >
            Select Hotel
          </Typography>
          <Box sx={{ height: '56px' }}>
            <HotelListing 
              onSelect={(hotel) => setSelectedHotel(hotel)}
              searchParams={searchCriteria}
              selectedHotelId={selectedHotel}
            />
            {renderRoomDataLoadingIndicator && renderRoomDataLoadingIndicator()}
          </Box>
        </Grid>
        
        {/* Room Type Selection */}
        <Grid item xs={12} md={2}>
          <Typography 
            variant="subtitle1" 
            fontWeight={500} 
            sx={{ 
              mb: 1, 
              fontSize: '0.875rem',
              color: 'text.primary',
              minHeight: '21px',
              display: 'flex',
              alignItems: 'center'
            }}
          >
            Select Room Type
          </Typography>
          <FormControl fullWidth sx={{ height: '56px' }} disabled={!selectedHotel || roomDataStatus === 'loading'}>
            <InputLabel id="room-type-select-label">Room Type</InputLabel>
            <Select
              labelId="room-type-select-label"
              id="room-type-select"
              value={roomType}
              label="Room Type"
              onChange={handleRoomTypeChange}
              sx={{ height: '56px' }}
              endAdornment={
                roomType && (
                  <Box sx={{ mr: 0.8 }}>
                    <IconButton
                      size="small"
                      onClick={(e) => {
                        e.stopPropagation();
                        setRoomType('');
                        setBedType('');
                      }}
                    >
                      <CloseIcon fontSize="small" />
                    </IconButton>
                  </Box>
                )
              }
            >
              <MenuItem value="">
                <em>Select a room type</em>
              </MenuItem>
              {roomDataStatus === 'loading' ? (
                <MenuItem disabled>
                  <CircularProgress size={20} sx={{ mr: 1 }} />
                  Loading room types...
                </MenuItem>
              ) : (
                roomTypes.map((room) => (
                  <MenuItem key={room.id} value={room.id}>
                    {room.name}
                  </MenuItem>
                ))
              )}
            </Select>
          </FormControl>
        </Grid>
        
        {/* Bed Type Selection */}
        <Grid item xs={12} md={2}>
          <Typography 
            variant="subtitle1" 
            fontWeight={500} 
            sx={{ 
              mb: 1, 
              fontSize: '0.875rem',
              color: 'text.primary',
              minHeight: '21px',
              display: 'flex',
              alignItems: 'center'
            }}
          >
            Select Bed Type
          </Typography>
          <FormControl fullWidth sx={{ height: '56px' }} disabled={!roomType}>
            <InputLabel id="bed-type-select-label">Bed Type</InputLabel>
            <Select
              labelId="bed-type-select-label"
              id="bed-type-select"
              value={bedType}
              label="Bed Type"
              onChange={handleBedTypeChange}
              sx={{ height: '56px' }}
              endAdornment={
                bedType && (
                  <Box sx={{ mr: 1 }}>
                    <IconButton
                      size="small"
                      onClick={(e) => {
                        e.stopPropagation();
                        setBedType('');
                      }}
                    >
                      <CloseIcon fontSize="small" />
                    </IconButton>
                  </Box>
                )
              }
            >
              <MenuItem value="">
                <em>Select a bed type</em>
              </MenuItem>
              {bedTypes.length > 0 ? (
                bedTypes.map((bed) => (
                  <MenuItem key={bed.id} value={bed.id}>
                    {bed.name} {bed.max_occupancy && `(Max: ${bed.max_occupancy} person${bed.max_occupancy > 1 ? 's' : ''})`}
                    {bed.price > 0 && ` - $${parseFloat(bed.price).toFixed(2)}`}
                  </MenuItem>
                ))
              ) : (
                roomType && (
                  <MenuItem disabled>
                    <em>No bed types available for this room</em>
                  </MenuItem>
                )
              )}
            </Select>
          </FormControl>
        </Grid>
        
        {/* Meal Plan Selection */}
        <Grid item xs={12} md={2}>
          <Typography 
            variant="subtitle1" 
            fontWeight={500} 
            sx={{ 
              mb: 1, 
              fontSize: '0.875rem',
              color: 'text.primary',
              minHeight: '21px',
              display: 'flex',
              alignItems: 'center'
            }}
          >
            Select Meal Plan
          </Typography>
          <Box sx={{ height: '56px' }}>
            {renderMealPlanSection && renderMealPlanSection()}
          </Box>
        </Grid>

        {/* Night Selection Summary */}
        <Grid item xs={12} md={3.5}>
          <Typography 
            variant="subtitle1" 
            fontWeight={500} 
            sx={{ 
              mb: 1, 
              fontSize: '0.875rem',
              color: 'text.primary',
              minHeight: '21px',
              display: 'flex',
              alignItems: 'center'
            }}
          >
            Hotel Night Selection
          </Typography>
          <Paper 
            elevation={0} 
            sx={{ 
              p: 1.5, 
              bgcolor: selectedNights > 0 ? 'rgba(76, 175, 80, 0.06)' : 'rgba(255, 152, 0, 0.06)',
              border: '1px solid',
              borderColor: selectedNights > 0 ? 'rgba(76, 175, 80, 0.2)' : 'rgba(255, 152, 0, 0.2)',
              borderRadius: 2,
              height: '56px',
              display: 'flex',
              alignItems: 'center'
            }}
          >
            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', width: '100%' }}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <CalendarTodayIcon sx={{ 
                  mr: 1, 
                  fontSize: 16,
                  color: selectedNights > 0 ? 'success.main' : 'warning.main'
                }} />
                <Box>
                  <Typography variant="body2" sx={{ 
                    fontWeight: 600,
                    fontSize: '0.875rem',
                    color: selectedNights > 0 ? 'success.main' : 'warning.main',
                    lineHeight: 1.2
                  }}>
                    {selectedNights > 0 ? `${selectedNights} Night${selectedNights !== 1 ? 's' : ''} Selected` : 'No Nights Selected'}
                  </Typography>
                  {selectedNights === 0 && (
                    <Typography variant="caption" sx={{ color: 'warning.main', fontSize: '0.7rem' }}>
                      Select nights below
                    </Typography>
                  )}
                </Box>
              </Box>
              
              {selectedNights > 0 && (
                <Chip 
                  label={selectedNights}
                  size="small"
                  color="success"
                  variant="filled"
                  sx={{ minWidth: 28, height: 20, fontSize: '0.75rem' }}
                />
              )}
            </Box>
          </Paper>
        </Grid>
      </Grid>

      {/* Night Selection Section */}
      {renderNightSelection && (
        <Paper 
          elevation={1} 
          sx={{ 
            mt: 1.5, 
            p: 2, 
            borderRadius: 2,
            border: '1px solid #e0e0e0'
          }}
        >
          {/* <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
            <HotelIcon sx={{ mr: 1, color: 'primary.main' }} />
            <Typography variant="h6" sx={{ color: 'primary.main', fontWeight: 600 }}>
              Select Your Stay Nights
            </Typography>
          </Box> */}
          
          {/* <Typography variant="body2" sx={{ color: 'text.secondary', mb: 3 }}>
            Choose which nights you want to stay at this hotel. Select consecutive nights for your booking.
          </Typography> */}
          
          {renderNightSelection()}
        </Paper>
      )}
    </Box>
  );
};

export default HotelSelectionForm; 