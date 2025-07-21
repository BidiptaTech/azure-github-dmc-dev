import React from 'react';
import { Typography, Box, Card, CardContent, CardMedia, Stack } from '@mui/material';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import LocalDiningIcon from '@mui/icons-material/LocalDining';
import StarIcon from '@mui/icons-material/Star';

const defaultRestaurantImage = '/img/restaurants/1.png';

const RestaurantsDetails = ({ packageData }) => {
  const restaurants = packageData.selected_restaurants || [];
  
  if (!restaurants.length) {
    return (
      <Typography variant="body2" color="text.secondary">
        No restaurant information available for this package.
      </Typography>
    );
  }
  
  // Only show the first restaurant
  const restaurant = restaurants[0];
  
  return (
    <>
      <Typography variant="body2" sx={{ mb: 1 }}>
        Enjoy delicious meals at this selected restaurant:
      </Typography>
      
      <Card elevation={0} sx={{ 
        display: 'flex',
        flexDirection: { xs: 'column', sm: 'row' },
        border: '1px solid',
        borderColor: 'divider',
        borderRadius: '8px',
        overflow: 'hidden'
      }}>
        <CardMedia
          component="img"
          sx={{ 
            width: { xs: '100%', sm: 180 }, 
            height: { xs: 140, sm: 140 },
            objectFit: 'cover'
          }}
          image={restaurant.image || defaultRestaurantImage}
          alt={restaurant.name}
        />
        <Box sx={{ display: 'flex', flexDirection: 'column', width: '100%' }}>
          <CardContent sx={{ flex: '1 0 auto', p: 2, '&:last-child': { pb: 2 } }}>
            <Typography variant="subtitle1" fontWeight="bold">
              {restaurant.name}
            </Typography>
            
            <Stack spacing={0.75} sx={{ mt: 1 }}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <LocationOnIcon fontSize="small" color="action" sx={{ fontSize: 16, mr: 0.75 }} />
                <Typography variant="body2" color="text.secondary">
                  {restaurant.city || packageData.city || packageData.destination}
                </Typography>
              </Box>
              
              {restaurant.cuisine && (
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <LocalDiningIcon fontSize="small" color="error" sx={{ fontSize: 16, mr: 0.75 }} />
                  <Typography variant="body2" color="text.secondary">
                    {restaurant.cuisine}
                  </Typography>
                </Box>
              )}
              
              {restaurant.rating && (
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <StarIcon fontSize="small" color="warning" sx={{ fontSize: 16, mr: 0.75 }} />
                  <Typography variant="body2" color="text.secondary">
                    {restaurant.rating} / 5
                  </Typography>
                </Box>
              )}
            </Stack>
            
            {restaurant.description && (
              <Typography variant="body2" color="text.secondary" sx={{ mt: 1.5 }}>
                {restaurant.description}
              </Typography>
            )}
          </CardContent>
        </Box>
      </Card>
      
      {restaurants.length > 1 && (
        <Typography variant="caption" color="text.secondary" sx={{ mt: 1, display: 'block', fontStyle: 'italic' }}>
          * {restaurants.length - 1} more restaurant{restaurants.length > 2 ? 's' : ''} available. Click "Change" to view and select.
        </Typography>
      )}
    </>
  );
};

export default RestaurantsDetails; 