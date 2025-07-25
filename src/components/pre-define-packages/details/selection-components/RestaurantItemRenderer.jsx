import React from 'react';
import { CardMedia, CardContent, Typography, Box, Stack } from '@mui/material';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import LocalDiningIcon from '@mui/icons-material/LocalDining';

// Default image for restaurants when no image is provided
const defaultRestaurantImage = '/img/restaurants/1.png';

/**
 * Renders a restaurant item in the selection modal
 * 
 * @param {Object} restaurant - The restaurant object to render
 * @returns {JSX.Element} The rendered restaurant item
 */
const RestaurantItemRenderer = (restaurant) => (
  <>
    <Box sx={{ height: '200px', overflow: 'hidden', position: 'relative' }}>
      <CardMedia
        component="img"
        height="100"
        width="100%"
        image={restaurant.image || defaultRestaurantImage}
        alt={restaurant.name}
        sx={{ 
          objectFit: 'cover',
          objectPosition: 'center',
          height: '200px !important',
          maxHeight: '200px !important'
        }}
      />
    </Box>
    <CardContent sx={{ p: 1.5, pb: '16px !important', flex: 1, minHeight: 0 }}>
      <Typography variant="subtitle2" fontWeight="medium" noWrap>
        {restaurant.name}
      </Typography>
      
      <Stack spacing={0.5} sx={{ mt: 0.5 }}>
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          <LocationOnIcon fontSize="small" color="action" sx={{ fontSize: 14, mr: 0.5 }} />
          <Typography variant="caption" color="text.secondary" noWrap>
            {restaurant.city || ''}
          </Typography>
        </Box>
        
        {restaurant.cuisine && (
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <LocalDiningIcon fontSize="small" color="error" sx={{ fontSize: 14, mr: 0.5 }} />
            <Typography variant="caption" color="text.secondary">
              {restaurant.cuisine}
            </Typography>
          </Box>
        )}
      </Stack>
    </CardContent>
  </>
);

export default RestaurantItemRenderer; 