import React from 'react';
import { 
  Box, Typography, Grid, FormControl, InputLabel, Select,
  MenuItem, IconButton, Button, CircularProgress
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import AddIcon from '@mui/icons-material/Add';
import HotelIcon from '@mui/icons-material/Hotel';
import RemoveIcon from '@mui/icons-material/Remove';
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
  handleAddMoreRooms,
  handleAddNewHotel,
  handleRemoveHotel,
  hotelConfigurations,
  activeHotelIndex,
  renderRoomDataLoadingIndicator,
  renderMealPlanSection,
  searchCriteria
}) => {
  
  console.log("HotelSelectionForm render:", {
    selectedHotel,
    roomType,
    roomTypes,
    roomDataStatus,
    roomTypesCount: roomTypes?.length || 0
  });
  
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
  
  return (
    <Grid container spacing={2} sx={{ mb: 3 }}>
      {/* Hotel Selection */}
      <Grid item xs={12} md={2.5} mt={1.5}>
        <HotelListing 
          onSelect={(hotel) => setSelectedHotel(hotel)}
          searchParams={searchCriteria}
          selectedHotelId={selectedHotel}
        />
        {renderRoomDataLoadingIndicator && renderRoomDataLoadingIndicator()}
      </Grid>
      
      {/* Room Type Selection */}
      <Grid item xs={12} md={2}>
        <Typography variant="subtitle1" fontWeight={500}>Select Room Type</Typography>
        <FormControl fullWidth sx={{ mt: 2 }} disabled={!selectedHotel || roomDataStatus === 'loading'}>
          <InputLabel id="room-type-select-label">Room Type</InputLabel>
          <Select
            labelId="room-type-select-label"
            id="room-type-select"
            value={roomType}
            label="Room Type"
            onChange={handleRoomTypeChange}
            endAdornment={
              roomType && (
                <Box sx={{ mr: 1 }}>
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
        <Typography variant="subtitle1" fontWeight={500}>Select Bed Type</Typography>
        <FormControl fullWidth sx={{ mt: 2 }} disabled={!roomType}>
          <InputLabel id="bed-type-select-label">Bed Type</InputLabel>
          <Select
            labelId="bed-type-select-label"
            id="bed-type-select"
            value={bedType}
            label="Bed Type"
            onChange={handleBedTypeChange}
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
        <Typography variant="subtitle1" fontWeight={500}>Select Meal Plan</Typography>
        {renderMealPlanSection && renderMealPlanSection()}
      </Grid>
      
      {/* Action Buttons */}
      <Grid item xs={12} md={2.5}>
        <Typography variant="subtitle1" fontWeight={500} sx={{ visibility: 'hidden' }}>Hidden Label</Typography>
        <Box sx={{ 
          width: '100%', 
          display: 'flex',
          flexDirection: 'column',
          gap: 1,
          mt: 2
        }}>
          {/* Add Room Button - Only show if hotel is selected */}
          {hotelConfigurations[activeHotelIndex]?.hotelId && (() => {
            const currentHotelId = hotelConfigurations[activeHotelIndex].hotelId;
            const roomsForThisHotel = hotelConfigurations.filter(config => config.hotelId === currentHotelId).length;
            const hotelName = hotelConfigurations[activeHotelIndex]?.hotelDetails?.hotel_name?.split(' ')[0] || 'Hotel';
            
            return (
              <Button
                variant="outlined"
                size="small"
                startIcon={<AddIcon />}
                onClick={handleAddMoreRooms}
                sx={{ 
                  borderColor: '#3554D1',
                  color: '#3554D1',
                  '&:hover': {
                    borderColor: '#2a43a8',
                    bgcolor: 'rgba(53, 84, 209, 0.05)'
                  }
                }}
                title={`Add another room to ${hotelConfigurations[activeHotelIndex]?.hotelDetails?.hotel_name}. Currently ${roomsForThisHotel} room${roomsForThisHotel > 1 ? 's' : ''} configured.`}
              >
                Add Room to {hotelName}
                {roomsForThisHotel > 1 && (
                  <Box component="span" sx={{ 
                    ml: 0.5, 
                    px: 0.5, 
                    py: 0.25, 
                    bgcolor: 'rgba(53, 84, 209, 0.1)', 
                    borderRadius: 0.5, 
                    fontSize: '0.7rem',
                    lineHeight: 1 
                  }}>
                    {roomsForThisHotel}
                  </Box>
                )}
              </Button>
            );
          })()}
          
          {/* Add Hotel Button */}
          <Button
            variant="contained"
            size="small"
            startIcon={<HotelIcon />}
            onClick={handleAddNewHotel}
            sx={{ 
              bgcolor: '#4caf50',
              '&:hover': {
                bgcolor: '#45a049'
              }
            }}
          >
            Add Hotel
          </Button>
          
          {/* Remove Configuration Button - Only show if multiple configurations */}
          {hotelConfigurations.length > 1 && (
            <Button
              variant="outlined"
              size="small"
              color="error"
              startIcon={<RemoveIcon />}
              onClick={handleRemoveHotel}
              sx={{ 
                borderColor: '#f44336',
                '&:hover': {
                  borderColor: '#d32f2f',
                  bgcolor: 'rgba(244, 67, 54, 0.05)'
                }
              }}
            >
              Remove
            </Button>
          )}
        </Box>
      </Grid>
    </Grid>
  );
};

export default HotelSelectionForm; 