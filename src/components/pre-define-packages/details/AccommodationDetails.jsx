import React from 'react';
import { Typography, Box, Card, CardContent, CardMedia, Stack } from '@mui/material';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import GradeIcon from '@mui/icons-material/Grade';
import AmenitiesIcon from '@mui/icons-material/RoomService';

const defaultHotelImage = '/img/hotels/1.png';

const AccommodationDetails = ({ packageData }) => {
  const hotels = packageData.selected_hotels || [];
  
  if (!hotels.length) {
    return (
      <Typography variant="body2" color="text.secondary">
        No accommodation information available for this package.
      </Typography>
    );
  }
  
  // Only show the first hotel
  const hotel = hotels?.[0];
  
  return (
    <>
      <Typography variant="body2" sx={{ mb: 1 }}>
        Enjoy a comfortable stay at this hotel during your trip:
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
          image={hotel.image || defaultHotelImage}
          alt={hotel.name}
        />
        <Box sx={{ display: 'flex', flexDirection: 'column', width: '100%' }}>
          <CardContent sx={{ flex: '1 0 auto', p: 2, '&:last-child': { pb: 2 } }}>
            <Typography variant="subtitle1" fontWeight="bold">
              {hotel.name}
            </Typography>
            
            <Stack spacing={0.75} sx={{ mt: 1 }}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <LocationOnIcon fontSize="small" color="action" sx={{ fontSize: 16, mr: 0.75 }} />
                <Typography variant="body2" color="text.secondary">
                  {hotel.city || packageData.city || packageData.destination}
                </Typography>
              </Box>
              
              {hotel.star_rating && (
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <GradeIcon fontSize="small" color="warning" sx={{ fontSize: 16, mr: 0.75 }} />
                  <Typography variant="body2" color="text.secondary">
                    {hotel.star_rating}-Star Hotel
                  </Typography>
                </Box>
              )}
              
              {hotel.amenities && (
                <Box sx={{ display: 'flex', alignItems: 'flex-start' }}>
                  <AmenitiesIcon fontSize="small" color="action" sx={{ fontSize: 16, mr: 0.75, mt: 0.25 }} />
                  <Typography variant="body2" color="text.secondary">
                    {Array.isArray(hotel.amenities) 
                      ? hotel.amenities.join(', ') 
                      : hotel.amenities}
                  </Typography>
                </Box>
              )}
            </Stack>
            
            {hotel.description && (
              <Typography variant="body2" color="text.secondary" sx={{ mt: 1.5 }}>
                {hotel.description}
              </Typography>
            )}
          </CardContent>
        </Box>
      </Card>
      
      {hotels.length > 1 && (
        <Typography variant="caption" color="text.secondary" sx={{ mt: 1, display: 'block', fontStyle: 'italic' }}>
          * {hotels.length - 1} more hotel{hotels.length > 2 ? 's' : ''} available. Click "Change" to view and select.
        </Typography>
      )}
    </>
  );
};

export default AccommodationDetails; 