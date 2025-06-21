import React from 'react';
import { CardMedia, CardContent, Typography, Box, Stack } from '@mui/material';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import GradeIcon from '@mui/icons-material/Grade';

// Default image for hotels when no image is provided
const defaultHotelImage = '/img/hotels/1.png';

/**
 * Renders a hotel item in the selection modal
 * 
 * @param {Object} hotel - The hotel object to render
 * @returns {JSX.Element} The rendered hotel item
 */
const HotelItemRenderer = (hotel) => (
  <>
    <CardMedia
      component="img"
      height="120"
      image={hotel.image || defaultHotelImage}
      alt={hotel.name}
    />
    <CardContent sx={{ p: 1.5, pb: '16px !important' }}>
      <Typography variant="subtitle2" fontWeight="medium" noWrap>
        {hotel.name}
      </Typography>
      
      <Stack spacing={0.5} sx={{ mt: 0.5 }}>
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          <LocationOnIcon fontSize="small" color="action" sx={{ fontSize: 14, mr: 0.5 }} />
          <Typography variant="caption" color="text.secondary" noWrap>
            {hotel.city || ''}
          </Typography>
        </Box>
        
        {hotel.star_rating && (
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <GradeIcon fontSize="small" color="warning" sx={{ fontSize: 14, mr: 0.5 }} />
            <Typography variant="caption" color="text.secondary">
              {hotel.star_rating}-Star Hotel
            </Typography>
          </Box>
        )}
      </Stack>
    </CardContent>
  </>
);

export default HotelItemRenderer; 