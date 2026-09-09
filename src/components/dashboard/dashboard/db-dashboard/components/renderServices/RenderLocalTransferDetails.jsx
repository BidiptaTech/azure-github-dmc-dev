import React from 'react';
import {
  Box,
  Typography,
  Divider,
  List,
  Grid,
  Chip,
} from "@mui/material";
import {
  LocationOn as LocationIcon,
  LocalTaxi as LocalTaxiIcon,
  People as PeopleIcon,
} from '@mui/icons-material';

const RenderLocalTransferDetails = ({ details, tabValue }) => {
  if (!details || details.length === 0) {
    return (
      <Box sx={{ p: 3, textAlign: 'center' }}>
        <Typography variant="body1" color="textSecondary">No local transport details available</Typography>
      </Box>
    );
  }
  
  return (
    <List sx={{ width: '100%', bgcolor: 'background.paper' }}>
      {details.map((vehicle, index) => (
        <React.Fragment key={vehicle.id}>
          <Box sx={{ mb: 4 }}>
            <Box 
              sx={{ 
                display: 'flex', 
                flexDirection: { xs: 'column', md: 'row' },
                gap: 2,
                mb: 2
              }}
            >
              <Box 
                sx={{ 
                  width: { xs: '100%', md: 200 },
                  height: 150,
                  position: 'relative',
                  borderRadius: 2,
                  overflow: 'hidden',
                }}
              >
                <Box
                  component="img"
                  src={vehicle.image}
                  alt={vehicle.vehicle_name}
                  sx={{ 
                    width: '100%',
                    height: '100%',
                    objectFit: 'cover'
                  }}
                />
              </Box>
              <Box sx={{ flex: 1 }}>
                <Typography variant="h6" fontWeight={600} gutterBottom>
                  {vehicle.vehicle_name}
                </Typography>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                  <LocalTaxiIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                  <Typography variant="body2" color="text.secondary" fontWeight={500}>
                    {vehicle.vehicle_type} - {vehicle.vehicle_model} ({vehicle.model_year})
                  </Typography>
                </Box>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                  <LocationIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                  <Typography variant="body2" color="text.secondary">
                    {vehicle.city}
                  </Typography>
                </Box>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                  <PeopleIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                  <Typography variant="body2" color="text.secondary">
                    Seating Capacity: {vehicle.seating_capacity} persons
                  </Typography>
                </Box>
                
                <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, mt: 1 }}>
                  {vehicle.sharable === 1 && (
                    <Chip size="small" label="Sharable" color="primary" variant="outlined" />
                  )}
                  {vehicle.vehicle_plate_no && (
                    <Chip size="small" label={`Plate: ${vehicle.vehicle_plate_no}`} color="primary" variant="outlined" />
                  )}
                </Box>
              </Box>
              <Box sx={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'center', px: 2, minWidth: 120 }}>
                <Typography variant="h6" color="primary" fontWeight={600}>
                  ${vehicle.base_price}
                </Typography>
                <Typography variant="caption" color="text.secondary">base price</Typography>
                
                {vehicle.cost_per_hour > 0 && (
                  <>
                    <Typography variant="body2" color="primary" fontWeight={600} mt={1}>
                      ${vehicle.cost_per_hour}
                    </Typography>
                    <Typography variant="caption" color="text.secondary">per hour</Typography>
                  </>
                )}
              </Box>
            </Box>
            
            {tabValue === 1 && (
              <Box mt={2}>
                <Typography variant="subtitle2" gutterBottom>About the Vehicle:</Typography>
                <Box
                  sx={{
                    borderRadius: 1,
                    p: 2,
                    bgcolor: 'rgba(0,0,0,0.02)',
                    maxHeight: '200px',
                    overflow: 'auto'
                  }}
                  dangerouslySetInnerHTML={{ __html: vehicle.description }}
                />
                
                {/* <Grid container spacing={2} mt={1}>
                  <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" gutterBottom>Standard Rates:</Typography>
                    <Box sx={{ 
                      p: 2, 
                      bgcolor: 'rgba(0,0,0,0.01)', 
                      borderRadius: 1,
                      border: '1px solid',
                      borderColor: 'divider'
                    }}>
                      <Grid container spacing={1}>
                        <Grid item xs={8}>
                          <Typography variant="body2" color="text.secondary">Base Rate:</Typography>
                        </Grid>
                        <Grid item xs={4}>
                          <Typography variant="body2" fontWeight={500}>${vehicle.base_price}</Typography>
                        </Grid>
                        
                        <Grid item xs={8}>
                          <Typography variant="body2" color="text.secondary">Cost per KM (below 10km):</Typography>
                        </Grid>
                        <Grid item xs={4}>
                          <Typography variant="body2" fontWeight={500}>${vehicle.cost_per_km_below_10}</Typography>
                        </Grid>
                        
                        <Grid item xs={8}>
                          <Typography variant="body2" color="text.secondary">Cost per KM (10-25km):</Typography>
                        </Grid>
                        <Grid item xs={4}>
                          <Typography variant="body2" fontWeight={500}>${vehicle.cost_per_km_10_to_25}</Typography>
                        </Grid>
                        
                        <Grid item xs={8}>
                          <Typography variant="body2" color="text.secondary">Cost per KM (above 25km):</Typography>
                        </Grid>
                        <Grid item xs={4}>
                          <Typography variant="body2" fontWeight={500}>${vehicle.cost_per_km_above_25}</Typography>
                        </Grid>
                        
                        <Grid item xs={8}>
                          <Typography variant="body2" color="text.secondary">Hourly Rate:</Typography>
                        </Grid>
                        <Grid item xs={4}>
                          <Typography variant="body2" fontWeight={500}>${vehicle.cost_per_hour}</Typography>
                        </Grid>
                        
                        <Grid item xs={8}>
                          <Typography variant="body2" color="text.secondary">Cancellation Fee:</Typography>
                        </Grid>
                        <Grid item xs={4}>
                          <Typography variant="body2" fontWeight={500}>${vehicle.cancel_cost}</Typography>
                        </Grid>
                      </Grid>
                    </Box>
                  </Grid>
                  
                  <Grid item xs={12} md={6}>
                    <Typography variant="subtitle2" gutterBottom>Night Rates:</Typography>
                    <Box sx={{ 
                      p: 2, 
                      bgcolor: 'rgba(0,0,0,0.01)', 
                      borderRadius: 1,
                      border: '1px solid',
                      borderColor: 'divider'
                    }}>
                      <Grid container spacing={1}>
                        <Grid item xs={8}>
                          <Typography variant="body2" color="text.secondary">Night Base Rate:</Typography>
                        </Grid>
                        <Grid item xs={4}>
                          <Typography variant="body2" fontWeight={500}>${vehicle.night_base_price}</Typography>
                        </Grid>
                        
                        <Grid item xs={8}>
                          <Typography variant="body2" color="text.secondary">Night Cost per KM (below 10km):</Typography>
                        </Grid>
                        <Grid item xs={4}>
                          <Typography variant="body2" fontWeight={500}>${vehicle.night_cost_per_km_below_10}</Typography>
                        </Grid>
                        
                        <Grid item xs={8}>
                          <Typography variant="body2" color="text.secondary">Night Cost per KM (10-25km):</Typography>
                        </Grid>
                        <Grid item xs={4}>
                          <Typography variant="body2" fontWeight={500}>${vehicle.night_cost_per_km_10_to_25}</Typography>
                        </Grid>
                        
                        <Grid item xs={8}>
                          <Typography variant="body2" color="text.secondary">Night Cost per KM (above 25km):</Typography>
                        </Grid>
                        <Grid item xs={4}>
                          <Typography variant="body2" fontWeight={500}>${vehicle.night_cost_per_km_above_25}</Typography>
                        </Grid>
                        
                        <Grid item xs={8}>
                          <Typography variant="body2" color="text.secondary">Night Hourly Rate:</Typography>
                        </Grid>
                        <Grid item xs={4}>
                          <Typography variant="body2" fontWeight={500}>${vehicle.night_cost_per_hour}</Typography>
                        </Grid>
                        
                        <Grid item xs={8}>
                          <Typography variant="body2" color="text.secondary">Night Cancellation Fee:</Typography>
                        </Grid>
                        <Grid item xs={4}>
                          <Typography variant="body2" fontWeight={500}>${vehicle.night_cancel_cost}</Typography>
                        </Grid>
                      </Grid>
                    </Box>
                  </Grid>
                </Grid> */}
              </Box>
            )}
          </Box>
          {index < details.length - 1 && <Divider sx={{ my: 2 }} />}
        </React.Fragment>
      ))}
    </List>
  );
};

export default RenderLocalTransferDetails; 