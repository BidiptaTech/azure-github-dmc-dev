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
    <Box sx={{ height: '200px', overflow: 'hidden', position: 'relative' }}>
      <CardMedia
        component="img"
        height="100"
        width="100%"
        image={hotel.image || defaultHotelImage}
        alt={hotel.name}
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