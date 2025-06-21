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
    maxWidth: 400,
    border: '1px solid #dadde9',
    borderRadius: '12px',
    padding: 0,
    boxShadow: theme.shadows[3]
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
      fontSize: '0.75rem',
      height: 24,
      '& .MuiChip-label': {
        px: 1,
      },
    }}
  />
);

// Tooltip content component
const TooltipContent = ({ restaurant }) => {
  const mealAvailability = [];
  if (restaurant.breakfast_available === 1) mealAvailability.push("Breakfast");
  if (restaurant.lunch_available === 1) mealAvailability.push("Lunch");
  if (restaurant.dinner_available === 1) mealAvailability.push("Dinner");

  return (
    <Box>
      {/* Header Image Section */}
      <Box sx={{ position: 'relative', width: '100%', height: 200 }}>
        <Box
          component="img"
          src={restaurant.image || restaurant.site_images?.[0]}
          alt={restaurant.restaurant_name}
          sx={{
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            borderTopLeftRadius: '12px',
            borderTopRightRadius: '12px',
          }}
        />
        {restaurant.site_images && restaurant.site_images.length > 1 && (
          <Box
            sx={{
              position: 'absolute',
              top: 8,
              right: 8,
              bgcolor: 'rgba(0, 0, 0, 0.6)',
              color: 'white',
              px: 1,
              py: 0.5,
              borderRadius: 1,
              fontSize: '0.75rem',
            }}
          >
            +{restaurant.site_images.length - 1} photos
          </Box>
        )}
      </Box>

      {/* Content Section */}
      <Box sx={{ p: 2 }}>
        {/* Title and Location */}
        <Typography variant="h6" gutterBottom sx={{ fontWeight: 'bold', fontSize: '1.1rem' }}>
          {restaurant.restaurant_name}
        </Typography>
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
          <LocationOnIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 0.5 }} />
          <Typography variant="body2" color="text.secondary">
            {restaurant.city}, {restaurant.country}
          </Typography>
        </Box>

        {/* Meal Availability */}
        <Box sx={{ mb: 2 }}>
          <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500 }}>
            Available Meals
          </Typography>
          <Stack direction="row" spacing={1} flexWrap="wrap" useFlexGap>
            <MealChip isAvailable={restaurant.breakfast_available === 1} label="Breakfast" />
            <MealChip isAvailable={restaurant.lunch_available === 1} label="Lunch" />
            <MealChip isAvailable={restaurant.dinner_available === 1} label="Dinner" />
          </Stack>
        </Box>

        {/* Opening Hours */}
        {restaurant.opening_hours && (
          <Box sx={{ mb: 2 }}>
            <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500 }}>
              Opening Hours
            </Typography>
            <Stack spacing={0.5}>
              {restaurant.breakfast_available === 1 && (
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <AccessTimeIcon sx={{ fontSize: 16, color: 'text.secondary' }} />
                  <Typography variant="body2" color="text.secondary">
                    Breakfast: {restaurant.breakfast_open_time} - {restaurant.breakfast_close_time}
                  </Typography>
                </Box>
              )}
              {restaurant.lunch_available === 1 && (
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <AccessTimeIcon sx={{ fontSize: 16, color: 'text.secondary' }} />
                  <Typography variant="body2" color="text.secondary">
                    Lunch: {restaurant.lunch_open_time} - {restaurant.lunch_close_time}
                  </Typography>
                </Box>
              )}
              {restaurant.dinner_available === 1 && (
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  <AccessTimeIcon sx={{ fontSize: 16, color: 'text.secondary' }} />
                  <Typography variant="body2" color="text.secondary">
                    Dinner: {restaurant.dinner_open_time} - {restaurant.dinner_close_time}
                  </Typography>
                </Box>
              )}
            </Stack>
          </Box>
        )}

        {/* Pricing Section */}
        <Box sx={{ mt: 2 }}>
          <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500 }}>
            Pricing Details
          </Typography>
          <Grid container spacing={2}>
            {/* DMC Prices */}
            {restaurant.dmc_id && (
              <Grid item xs={6}>
                <Paper 
                  variant="outlined" 
                  sx={{ 
                    p: 1.5,
                    bgcolor: 'rgba(25, 118, 210, 0.02)',
                    borderColor: 'rgba(25, 118, 210, 0.1)'
                  }}
                >
                  <Typography variant="subtitle2" gutterBottom sx={{ color: 'primary.main', fontWeight: 500 }}>
                    DMC Prices
                  </Typography>
                  <Typography variant="body2">Available</Typography>
                </Paper>
              </Grid>
            )}

            {/* Travclicks Prices */}
            {restaurant.travclicks_dmc_id && (
              <Grid item xs={6}>
                <Paper 
                  variant="outlined" 
                  sx={{ 
                    p: 1.5,
                    bgcolor: 'rgba(76, 175, 80, 0.02)',
                    borderColor: 'rgba(76, 175, 80, 0.1)'
                  }}
                >
                  <Typography variant="subtitle2" gutterBottom sx={{ color: '#2e7d32', fontWeight: 500 }}>
                    Travclicks Prices
                  </Typography>
                  <Typography variant="body2">Available</Typography>
                </Paper>
              </Grid>
            )}
          </Grid>

          {/* Tax Information */}
          {restaurant.tax_percentage && (
            <Typography 
              variant="caption" 
              sx={{ 
                display: 'block',
                mt: 1,
                color: 'text.secondary',
                fontStyle: 'italic'
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

const RestaurantListing = ({ restaurants, selectedRestaurant, onRestaurantChange }) => {
  const dispatch = useDispatch();
  const bookingMode = useSelector((state) => state.common.bookingMode);
  const dmcName = useSelector((state) => state.auth.DmcName) || "DMC";
  const status = useSelector((state) => state.restaurants.status);

  // Debug logs
  useEffect(() => {
    console.log('RestaurantListing - Props:', { restaurants, selectedRestaurant });
    console.log('RestaurantListing - Status:', status);
  }, [restaurants, selectedRestaurant, status]);

  // Filter restaurants that have at least one pricing mode
  const filteredRestaurants = restaurants ? restaurants.filter(restaurant => {
    if (!restaurant) return false;
    const hasDmcId = restaurant.dmc_id;
    const hasTravclicksId = restaurant.travclicks_dmc_id;
    return hasDmcId || hasTravclicksId; // Must have at least one pricing mode
  }) : [];

  const handleRestaurantClick = (restaurant) => {
    if (!restaurant) return;
    
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

  return (
    <Box sx={{ flex: 1 }}>
      <Autocomplete
        value={filteredRestaurants.find(r => r.id === selectedRestaurant) || null}
        onChange={(event, newValue) => {
          handleRestaurantClick(newValue);
        }}
        options={filteredRestaurants}
        getOptionLabel={(option) => option.restaurant_name || ''}
        noOptionsText="No restaurants with valid pricing available"
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
                  <RestaurantIcon sx={{ mr: 1, fontSize: 20, color: 'text.secondary' }} />
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
                        height: 20,
                        fontSize: '0.7rem',
                        bgcolor: 'rgba(25, 118, 210, 0.08)',
                        color: 'primary.main'
                      }}
                    />
                  )}
                  {option.travclicks_dmc_id && (
                    <Chip 
                      size="small" 
                      label="Travclicks"
                      sx={{ 
                        height: 20,
                        fontSize: '0.7rem',
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
      />
    </Box>
  );
};

export default RestaurantListing; 