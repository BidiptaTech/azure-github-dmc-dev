import React from 'react';
import { Box, Typography, Chip, Stack, Rating } from '@mui/material';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import PeopleIcon from '@mui/icons-material/People';

const PackageHeader = ({ packageData }) => {
  return (
    <Box 
      sx={{ 
        position: 'relative',
        overflow: 'hidden',
        borderRadius: '12px',
        boxShadow: '0 4px 20px rgba(0, 0, 0, 0.1)',
      }}
    >
      {/* Header Image with Overlay */}
      <Box sx={{ 
        position: 'relative', 
        height: '300px',
        backgroundImage: `url(${packageData.main_image})`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        display: 'flex',
        alignItems: 'flex-end',
        p: 3
      }}>
        <Box sx={{ 
          position: 'absolute', 
          top: 0, 
          left: 0, 
          width: '100%', 
          height: '100%',
          background: 'linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(0,0,0,0.7) 100%)'
        }} />
        
        {/* Package Title and Basic Info */}
        <Box sx={{ position: 'relative', color: '#fff', width: '100%' }}>
          <Typography variant="h3" fontWeight="bold" gutterBottom>
            {packageData.title}
          </Typography>
          
          <Stack direction="row" spacing={2} alignItems="center" flexWrap="wrap">
            <Chip 
              icon={<LocationOnIcon />} 
              label={packageData.city || packageData.destination} 
              color="primary" 
            />
            <Chip 
              icon={<CalendarMonthIcon />} 
              label={`${packageData.duration_days} Days`} 
              color="primary" 
              variant="outlined"
            />
            <Chip 
              icon={<PeopleIcon />} 
              label={`Max ${packageData.max_pax} people`} 
              color="primary" 
              variant="outlined"
            />
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <Rating value={parseFloat(packageData.rating) || 0} precision={0.5} readOnly />
              <Typography variant="body2" sx={{ ml: 1 }}>
                ({packageData.reviews_count} reviews)
              </Typography>
            </Box>
          </Stack>
        </Box>
      </Box>
      
      {/* Package Tags */}
      <Box sx={{ p: 2, bgcolor: '#f5f5f5', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Chip label={packageData.category} color="secondary" />
        <Box>
          <Typography variant="body2">
            Available: {new Date(packageData.start_date).toLocaleDateString()} - {new Date(packageData.expire_date).toLocaleDateString()}
          </Typography>
        </Box>
      </Box>
    </Box>
  );
};

export default PackageHeader; 