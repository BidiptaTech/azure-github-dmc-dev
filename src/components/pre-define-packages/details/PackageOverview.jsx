import React from 'react';
import { Box, Typography, Paper, Grid, Divider } from '@mui/material';

const PackageOverview = ({ packageData }) => {
  return (
    <Paper elevation={1} sx={{ p: 3, mb: 4, borderRadius: '12px' }}>
      <Typography variant="h5" fontWeight="bold" gutterBottom>
        Overview
      </Typography>
      
      <Typography variant="body1" paragraph>
        {packageData.description}
      </Typography>
      
      <Divider sx={{ my: 3 }} />
      
      <Grid container spacing={2}>
        <Grid item xs={12} sm={6} md={4}>
          <Typography variant="subtitle2" color="text.secondary">
            Destination
          </Typography>
          <Typography variant="body1" fontWeight="medium">
            {packageData.destination}
          </Typography>
        </Grid>
        
        <Grid item xs={12} sm={6} md={4}>
          <Typography variant="subtitle2" color="text.secondary">
            Category
          </Typography>
          <Typography variant="body1" fontWeight="medium">
            {packageData.category}
          </Typography>
        </Grid>
        
        <Grid item xs={12} sm={6} md={4}>
          <Typography variant="subtitle2" color="text.secondary">
            Duration
          </Typography>
          <Typography variant="body1" fontWeight="medium">
            {packageData.duration_days} Days
          </Typography>
        </Grid>
        
        <Grid item xs={12} sm={6} md={4}>
          <Typography variant="subtitle2" color="text.secondary">
            Available From
          </Typography>
          <Typography variant="body1" fontWeight="medium">
            {new Date(packageData.start_date).toLocaleDateString()}
          </Typography>
        </Grid>
        
        <Grid item xs={12} sm={6} md={4}>
          <Typography variant="subtitle2" color="text.secondary">
            Available Until
          </Typography>
          <Typography variant="body1" fontWeight="medium">
            {new Date(packageData.expire_date).toLocaleDateString()}
          </Typography>
        </Grid>
        
        <Grid item xs={12} sm={6} md={4}>
          <Typography variant="subtitle2" color="text.secondary">
            Maximum Capacity
          </Typography>
          <Typography variant="body1" fontWeight="medium">
            {packageData.max_pax} people
          </Typography>
        </Grid>
      </Grid>
      
      {packageData.gallery_images && packageData.gallery_images.length > 0 && (
        <Box sx={{ mt: 4 }}>
          <Typography variant="h6" gutterBottom>
            Gallery
          </Typography>
          
          <Grid container spacing={2}>
            {packageData.gallery_images.map((image, index) => (
              <Grid item xs={12} sm={6} md={4} key={index}>
                <Box
                  component="img"
                  src={image}
                  alt={`Gallery image ${index + 1}`}
                  sx={{
                    width: '100%',
                    height: 200,
                    objectFit: 'cover',
                    borderRadius: 1,
                  }}
                />
              </Grid>
            ))}
          </Grid>
        </Box>
      )}
    </Paper>
  );
};

export default PackageOverview; 