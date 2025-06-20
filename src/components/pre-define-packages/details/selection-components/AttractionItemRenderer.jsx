import React from 'react';
import { CardMedia, CardContent, Typography, Box, Stack } from '@mui/material';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import CategoryIcon from '@mui/icons-material/Category';

// Default image for attractions when no image is provided
const defaultAttractionImage = '/img/attractions/1.png';

/**
 * Renders an attraction item in the selection modal
 * 
 * @param {Object} attraction - The attraction object to render
 * @returns {JSX.Element} The rendered attraction item
 */
const AttractionItemRenderer = (attraction) => (
  <>
    <CardMedia
      component="img"
      height="120"
      image={attraction.image || defaultAttractionImage}
      alt={attraction.name}
    />
    <CardContent sx={{ p: 1.5, pb: '16px !important' }}>
      <Typography variant="subtitle2" fontWeight="medium" noWrap>
        {attraction.name}
      </Typography>
      
      <Stack spacing={0.5} sx={{ mt: 0.5 }}>
        <Box sx={{ display: 'flex', alignItems: 'center' }}>
          <LocationOnIcon fontSize="small" color="action" sx={{ fontSize: 14, mr: 0.5 }} />
          <Typography variant="caption" color="text.secondary" noWrap>
            {attraction.city || ''}
          </Typography>
        </Box>
        
        {attraction.category && (
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <CategoryIcon fontSize="small" color="primary" sx={{ fontSize: 14, mr: 0.5 }} />
            <Typography variant="caption" color="text.secondary">
              {attraction.category}
            </Typography>
          </Box>
        )}
      </Stack>
    </CardContent>
  </>
);

export default AttractionItemRenderer; 