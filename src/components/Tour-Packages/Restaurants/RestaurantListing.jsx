import React, { useEffect } from 'react';
import { 
  Grid, 
  Autocomplete, 
  TextField, 
  Tooltip, 
  Box,
  Typography,
  Card,
  CardMedia,
  CardContent,
  Stack,
  Divider,
  styled,
  IconButton,
  Chip,
  Paper
} from '@mui/material';
import { useDispatch, useSelector } from 'react-redux';
import { fetchRestaurantsDetails } from '../../../slice/restaurant/RestaurantsSlice';
import { setBookingMode } from '../../../slice/common/commonSlice';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import AccessTimeIcon from '@mui/icons-material/AccessTime';

// Custom styled tooltip
const CustomTooltip = styled(({ className, ...props }) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
  '& .MuiTooltip-tooltip': {
    backgroundColor: 'white',
    color: 'rgba(0, 0, 0, 0.87)',
    maxWidth: 350,
    border: '1px solid #dadde9',
    borderRadius: '8px',
    padding: 0,
    boxShadow: theme.shadows[2]
  },
}));

// Meal availability chip component
const MealChip = ({ isAvailable, label }) => (
  <Chip
    size="small"
    label={label}
    sx={{
      backgroundColor: isAvailable ? 'rgba(76, 175, 80, 0.1)' : 'rgba(0, 0, 0, 0.08)',
      color: isAvailable ? '#2e7d32' : 'text.secondary',
      fontSize: '0.7rem',
      height: 20,
      '& .MuiChip-label': {
        px: 0.8,
      },
    }}
  />
);

// Tooltip content component
const TooltipContent = ({ restaurant }) => {
  const mealAvailability = [];
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  if (restaurant.breakfast_available === 1) mealAvailability.push("Breakfast");
  if (restaurant.lunch_available === 1) mealAvailability.push("Lunch");
  if (restaurant.dinner_available === 1) mealAvailability.push("Dinner");

  return (
    <Box>
      {/* Header Image Section */}
      <Box sx={{ position: 'relative', width: '100%', height: 160 }}>
        <Box
          component="img"
          src={restaurant.image || restaurant.site_images?.[0]}
          alt={restaurant.restaurant_name}
          sx={{
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            borderTopLeftRadius: '8px',
            borderTopRightRadius: '8px',
          }}
        />
        {restaurant.site_images && restaurant.site_images.length > 1 && (
          <Box
            sx={{
              position: 'absolute',
              top: 6,
              right: 6,
              bgcolor: 'rgba(0, 0, 0, 0.6)',
              color: 'white',
              px: 0.8,
              py: 0.4,
              borderRadius: 0.8,
              fontSize: '0.7rem',
            }}
          >
            +{restaurant.site_images.length - 1} photos
          </Box>
        )}
      </Box>

      {/* Content Section */}
      <Box sx={{ p: 1.5 }}>
        {/* Title and Location */}
        <Typography variant="subtitle1" gutterBottom sx={{ fontWeight: 'bold', fontSize: '1rem', mb: 1 }}>
          {restaurant.restaurant_name}
        </Typography>
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
          <LocationOnIcon sx={{ fontSize: 16, color: 'text.secondary', mr: 0.5 }} />
          <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.75rem' }}>
            {restaurant.city}, {restaurant.country}
          </Typography>
        </Box>

        {/* Meal Availability */}
        <Box sx={{ mb: 1.5 }}>
          <Typography variant="body2" gutterBottom sx={{ fontWeight: 500, fontSize: '0.8rem', mb: 0.8 }}>
            Available Meals
          </Typography>
          <Stack direction="row" spacing={0.8} flexWrap="wrap" useFlexGap>
            <MealChip isAvailable={restaurant.breakfast_available === 1} label="Breakfast" />
            <MealChip isAvailable={restaurant.lunch_available === 1} label="Lunch" />
            <MealChip isAvailable={restaurant.dinner_available === 1} label="Dinner" />
          </Stack>
        </Box>

        {/* Opening Hours */}
        {restaurant.opening_hours && (
          <Box sx={{ mb: 1.5 }}>
            <Typography variant="body2" gutterBottom sx={{ fontWeight: 500, fontSize: '0.8rem', mb: 0.8 }}>
              Opening Hours
            </Typography>
            <Stack spacing={0.3}>
              {restaurant.breakfast_available === 1 && (
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <AccessTimeIcon sx={{ fontSize: 14, color: '#4caf50' }} />
                  <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
                    Breakfast: {restaurant.breakfast_open_time} - {restaurant.breakfast_close_time}
                  </Typography>
                </Box>
              )}
              {restaurant.lunch_available === 1 && (
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <AccessTimeIcon sx={{ fontSize: 14, color: '#4caf50' }} />
                  <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
                    Lunch: {restaurant.lunch_open_time} - {restaurant.lunch_close_time}
                  </Typography>
                </Box>
              )}
              {restaurant.dinner_available === 1 && (
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <AccessTimeIcon sx={{ fontSize: 14, color: '#4caf50' }} />
                  <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.7rem' }}>
                    Dinner: {restaurant.dinner_open_time} - {restaurant.dinner_close_time}
                  </Typography>
                </Box>
              )}
            </Stack>
          </Box>
        )}

        {/* Pricing Section */}
        <Box sx={{ mt: 1.5 }}>
          <Typography variant="body2" gutterBottom sx={{ fontWeight: 500, fontSize: '0.8rem', mb: 1 }}>
            Pricing Details
          </Typography>
          {PriceHide !== "1" ? (
          <Grid container spacing={1.5}>
            {/* DMC Prices */}
            {restaurant.dmc_id && (
              <Grid item xs={6}>
                <Paper 
                  variant="outlined" 
                  sx={{ 
                    p: 1,
                    bgcolor: 'rgba(25, 118, 210, 0.02)',
                    borderColor: 'rgba(25, 118, 210, 0.1)'
                  }}
                >
                  <Typography variant="caption" gutterBottom sx={{ color: '#4caf50', fontWeight: 500, fontSize: '0.75rem' }}>
                    DMC Prices
                  </Typography>
                  <Typography variant="caption" sx={{ fontSize: '0.7rem' }}>Available</Typography>
                </Paper>
              </Grid>
            )}

            {/* Travclicks Prices */}
            {restaurant.travclicks_dmc_id && (
              <Grid item xs={6}>
                <Paper 
                  variant="outlined" 
                  sx={{ 
                    p: 1,
                    bgcolor: 'rgba(76, 175, 80, 0.02)',
                    borderColor: 'rgba(76, 175, 80, 0.1)'
                  }}
                >
                  <Typography variant="caption" gutterBottom sx={{ color: '#2e7d32', fontWeight: 500, fontSize: '0.75rem' }}>
                    Travclicks Prices
                  </Typography>
                  <Typography variant="caption" sx={{ fontSize: '0.7rem' }}>Available</Typography>
                </Paper>
              </Grid>
            )}
          </Grid>
          ):(
            <Typography variant="caption" gutterBottom sx={{ color: 'text.secondary', fontWeight: 500, fontSize: '0.75rem' }}>
              Pricing hidden
            </Typography>
          )}
          {/* Tax Information */}
          {restaurant.tax_percentage && (
            <Typography 
              variant="caption" 
              sx={{ 
                display: 'block',
                mt: 0.8,
                color: 'text.secondary',
                fontStyle: 'italic',
                fontSize: '0.65rem'
              }}
            >
              *Prices are subject to {restaurant.tax_percentage}% tax
            </Typography>
          )}
        </Box>
      </Box>
    </Box>
  );
};

const RestaurantListing = ({ restaurants, selectedRestaurant, onRestaurantChange, disabled = false, selectedRestaurantName }) => {
  const dispatch = useDispatch();
  const bookingMode = useSelector((state) => state.common.bookingMode);
  const dmcName = useSelector((state) => state.auth.DmcName) || "DMC";
  const status = useSelector((state) => state.restaurants.status);
  //const isFromMainSearch = useSelector((state) => state.restaurants.isFromMainSearch);

 
  
  // Don't show any restaurants if coming from MainFilterSearchBox
  // if (isFromMainSearch) {
  //   return (
  //     <Box sx={{ flex: 1 }}>
  //       <TextField
  //         label="Search Restaurant"
  //         fullWidth
  //         disabled
  //         value=""
  //         helperText="Please select a hotel first to view available restaurants"
  //         sx={{
  //           '& .MuiInputBase-input': {
  //             fontSize: '0.8rem',
  //             height: '12px',
  //             paddingBottom: '10px',
  //             paddingTop: '0px',
  //           },
  //         }}
  //       />
  //     </Box>
  //   );
  // }

  // Filter restaurants that have at least one pricing mode
  const filteredRestaurants = restaurants ? restaurants.filter(restaurant => {
    if (!restaurant) return false;
    const hasDmcId = restaurant.dmc_id;
    const hasTravclicksId = restaurant.travclicks_dmc_id;
    return hasDmcId || hasTravclicksId; // Must have at least one pricing mode
  }) : [];

  const handleRestaurantClick = (restaurant) => {
    if (!restaurant || disabled) return;
    
    console.log('Selected restaurant:', restaurant);
    
    // Determine which pricing mode to use based on availability
    const hasDmcId = restaurant.dmc_id;
    const hasTravclicksId = restaurant.travclicks_dmc_id;
    
    // Default to DMC if available, otherwise use travclicks
    const mode = hasDmcId ? "dmc" : hasTravclicksId ? "travclicks" : null;
    
    // If no valid pricing mode, don't proceed
    if (!mode) {
      console.log('No valid pricing mode found for restaurant');
      return;
    }
    
    // Ensure the booking mode is updated in Redux
    dispatch(setBookingMode(mode));

    // Get the appropriate dmc_id based on the mode
    const dmc_id = mode === "dmc" ? restaurant.dmc_id : restaurant.travclicks_dmc_id;

    console.log('Dispatching fetchRestaurantsDetails with:', {
      restaurantId: restaurant.id,
      price_mode: mode,
      dmc_id: dmc_id
    });

    // Call the restaurant details API
    dispatch(
      fetchRestaurantsDetails({
        restaurantId: restaurant.id,
        price_mode: mode,
        dmc_id: dmc_id,
      })
    );

    // Call the onRestaurantChange prop if provided
    if (onRestaurantChange) {
      onRestaurantChange(restaurant.id);
    }
  };

  // Create a display value for the autocomplete
  const getDisplayValue = () => {
    if (selectedRestaurantName) {
      // If we have a restaurant name, create a display object
      return {
        id: selectedRestaurant,
        restaurant_name: selectedRestaurantName,
        // Add other required properties with default values
        breakfast_available: 0,
        lunch_available: 0,
        dinner_available: 0,
        dmc_id: null,
        travclicks_dmc_id: null
      };
    }
    return filteredRestaurants.find(r => r.id === selectedRestaurant) || null;
  };

  return (
    <Box sx={{ flex: 1 }}>
      <Autocomplete
        value={getDisplayValue()}
        onChange={(event, newValue) => {
          handleRestaurantClick(newValue);
        }}
        options={filteredRestaurants}
        getOptionLabel={(option) => option.restaurant_name || ''}
        noOptionsText="No restaurants with valid pricing available"
        disabled={disabled}
        sx={{
          '& .MuiInputBase-input': {
            fontSize: '0.8rem',
            height: '12px',
            paddingBottom: '10px',
            paddingTop: '0px',
          },
        }}
        renderOption={(props, option) => {
          // Extract the key from props and remove it from the rest to avoid React warning
          const { key, ...otherProps } = props;
          
          return (
            <CustomTooltip
              key={key}
              title={<TooltipContent restaurant={option} />}
              placement="right"
              arrow
            >
              <Box component="li" {...otherProps}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <RestaurantIcon sx={{ mr: 1, fontSize: 20, color: '#4caf50' }} />
                  <Box>
                    <Typography variant="body2">{option.restaurant_name}</Typography>
                    <Typography variant="caption" color="text.secondary">
                      {[
                        option.breakfast_available === 1 && 'Breakfast',
                        option.lunch_available === 1 && 'Lunch',
                        option.dinner_available === 1 && 'Dinner'
                      ].filter(Boolean).join(', ')}
                    </Typography>
                  </Box>
                </Box>
                {/* Add a small indicator for available pricing modes */}
                <Box sx={{ ml: 'auto', display: 'flex', gap: 0.5 }}>
                  {option.dmc_id && (
                    <Chip 
                      size="small" 
                      label="DMC"
                      sx={{ 
                        height: 18,
                        fontSize: '0.65rem',
                        bgcolor: 'rgba(76, 175, 80, 0.08)',
                        color: '#4caf50'
                      }}
                    />
                  )}
                  {option.travclicks_dmc_id && (
                    <Chip 
                      size="small" 
                      label="Travclicks"
                      sx={{ 
                        height: 18,
                        fontSize: '0.65rem',
                        bgcolor: 'rgba(76, 175, 80, 0.08)',
                        color: '#2e7d32'
                      }}
                    />
                  )}
                </Box>
              </Box>
            </CustomTooltip>
          );
        }}
        renderInput={(params) => (
          <TextField
            {...params}
            label="Search Restaurant"
            fullWidth
          />
        )}
        slotProps={{
          popper: {
            sx: {
              zIndex: 999999
            }
          }
        }}
      />
    </Box>
  );
};

export default RestaurantListing; 