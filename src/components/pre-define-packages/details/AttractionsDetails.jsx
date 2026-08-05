import React from 'react';
import { Typography, Box, Card, CardContent, CardMedia, Stack } from '@mui/material';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import CategoryIcon from '@mui/icons-material/Category';
import AccessTimeIcon from '@mui/icons-material/AccessTime';

const defaultAttractionImage = '/img/attractions/1.png';

const AttractionsDetails = ({ packageData }) => {
  const attractions = packageData.selected_attractions || [];
  
  if (!attractions.length) {
    return (
      <Typography variant="body2" color="text.secondary">
        No attraction information available for this package.
      </Typography>
    );
  }
  
  // Only show the first attraction
  const attraction = attractions[0];
  
  return (
    <>
      <Typography variant="body2" sx={{ mb: 1 }}>
        Discover amazing sights and experiences at this attraction:
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
          image={attraction.image || defaultAttractionImage}
          alt={attraction.name}
        />
        <Box sx={{ display: 'flex', flexDirection: 'column', width: '100%' }}>
          <CardContent sx={{ flex: '1 0 auto', p: 2, '&:last-child': { pb: 2 } }}>
            <Typography variant="subtitle1" fontWeight="bold">
              {attraction.name}
            </Typography>
            
            <Stack spacing={0.75} sx={{ mt: 1 }}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <LocationOnIcon fontSize="small" color="action" sx={{ fontSize: 16, mr: 0.75 }} />
                <Typography variant="body2" color="text.secondary">
                  {attraction.city || packageData.city || packageData.destination}
                </Typography>
              </Box>
              
              {attraction.category && (
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <CategoryIcon fontSize="small" color="primary" sx={{ fontSize: 16, mr: 0.75 }} />
                  <Typography variant="body2" color="text.secondary">
                    {attraction.category}
                  </Typography>
                </Box>
              )}
              
              {attraction.duration && (
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <AccessTimeIcon fontSize="small" color="action" sx={{ fontSize: 16, mr: 0.75 }} />
                  <Typography variant="body2" color="text.secondary">
                    {attraction.duration}
                  </Typography>
                </Box>
              )}
            </Stack>
            
            {attraction.description && (
              <Typography variant="body2" color="text.secondary" sx={{ mt: 1.5 }}>
                {attraction.description}
              </Typography>
            )}
          </CardContent>
        </Box>
      </Card>
      
      {attractions.length > 1 && (
        <Typography variant="caption" color="text.secondary" sx={{ mt: 1, display: 'block', fontStyle: 'italic' }}>
          * {attractions.length - 1} more attraction{attractions.length > 2 ? 's' : ''} available. Click "Change" to view and select.
        </Typography>
      )}
    </>
  );
};

export default AttractionsDetails; 