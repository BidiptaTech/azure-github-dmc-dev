import React from 'react';
import {
  Box,
  Typography,
  Paper,
  Grid,
  Chip,
} from "@mui/material";
import {
  DirectionsBoat as DirectionsBoatIcon,
  LocationOn as LocationIcon,
  Hotel as HotelIcon,
  Restaurant as RestaurantIcon,
  AccessTime as AccessTimeIcon,
  Attractions as AttractionsIcon,
} from '@mui/icons-material';

const RenderPortDetails = ({ enquiry, tabValue }) => {
  if (!enquiry || (!enquiry.entry_port && !enquiry.exit_port)) {
    return (
      <Box sx={{ p: 3, textAlign: 'center' }}>
        <Typography variant="body1" color="textSecondary">
          No port details available
        </Typography>
      </Box>
    );
  }
  
  return (
    <Box sx={{ width: '100%', bgcolor: 'background.paper' }}>
      {/* Entry Port Section */}
      {enquiry.entry_port && (
        <Box sx={{ mb: 4 }}>
          <Typography variant="h6" fontWeight={600} color="primary" gutterBottom>
            Entry Port
          </Typography>
          <Paper sx={{ p: 3, mb: 3, bgcolor: 'rgba(25, 118, 210, 0.03)' }}>
            <Grid container spacing={2}>
              <Grid item xs={12} md={6}>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                  <DirectionsBoatIcon fontSize="small" color="primary" sx={{ mr: 1 }} />
                  <Typography variant="body1" fontWeight={500}>
                    {enquiry.entry_port_address}
                  </Typography>
                </Box>
                
                <Box sx={{ mt: 2 }}>
                  <Typography variant="subtitle2" gutterBottom>
                    Drop-off Information:
                  </Typography>
                  <Box sx={{ display: 'flex', alignItems: 'center', ml: 1 }}>
                    <Chip 
                      size="small" 
                      color="primary" 
                      variant="outlined"
                      label={
                        enquiry.entry_dropoff_type === 'hotel' 
                          ? 'Hotel Drop-off' 
                          : enquiry.entry_dropoff_type === 'restaurant'
                            ? 'Restaurant Drop-off'
                            : enquiry.entry_dropoff_type === 'attraction'
                              ? 'Attraction Drop-off'
                              : enquiry.entry_dropoff_type
                      }
                      icon={
                        enquiry.entry_dropoff_type === 'hotel' 
                          ? <HotelIcon fontSize="small" /> 
                          : enquiry.entry_dropoff_type === 'restaurant'
                            ? <RestaurantIcon fontSize="small" />
                            : enquiry.entry_dropoff_type === 'attraction'
                              ? <AttractionsIcon fontSize="small" />
                              : <LocationIcon fontSize="small" />
                      }
                      sx={{ mb: 1 }}
                    />
                  </Box>
                </Box>
              </Grid>
              
              <Grid item xs={12} md={6}>
                {enquiry.entry_dropoff_location && (
                  <Box sx={{ 
                    p: 2, 
                    border: '1px solid', 
                    borderColor: 'divider', 
                    borderRadius: 2,
                    bgcolor: 'background.paper' 
                  }}>
                    {enquiry.entry_dropoff_type === 'hotel' && (
                      <>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <HotelIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body1" fontWeight={500}>
                            {enquiry.entry_dropoff_location.name}
                          </Typography>
                          <Box sx={{ ml: 1, display: 'flex' }}>
                            {[...Array(parseInt(enquiry.entry_dropoff_location.hotel_star_rating) || 0)].map((_, i) => (
                              <Box 
                                key={i} 
                                component="span" 
                                sx={{ 
                                  color: 'gold', 
                                  fontSize: '16px',
                                  lineHeight: 1
                                }}
                              >
                                ★
                              </Box>
                            ))}
                          </Box>
                        </Box>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <LocationIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body2" color="text.secondary">
                            {enquiry.entry_dropoff_location.address || `${enquiry.entry_dropoff_location.city}, ${enquiry.entry_dropoff_location.country}`}
                          </Typography>
                        </Box>
                      </>
                    )}
                    
                    {enquiry.entry_dropoff_type === 'restaurant' && (
                      <>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <RestaurantIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body1" fontWeight={500}>
                            {enquiry.entry_dropoff_location.name}
                          </Typography>
                        </Box>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <LocationIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body2" color="text.secondary">
                            {enquiry.entry_dropoff_location.city}, {enquiry.entry_dropoff_location.country}
                          </Typography>
                        </Box>
                      </>
                    )}
                    
                    {enquiry.entry_dropoff_type === 'attraction' && (
                      <>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <AttractionsIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body1" fontWeight={500}>
                            {enquiry.entry_dropoff_location.name}
                          </Typography>
                        </Box>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <LocationIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body2" color="text.secondary">
                            {enquiry.entry_dropoff_location.location}, {enquiry.entry_dropoff_location.country}
                          </Typography>
                        </Box>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <AccessTimeIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body2" color="text.secondary">
                            Open: {JSON.parse(enquiry.entry_dropoff_location.open_time)[0]} - Close: {JSON.parse(enquiry.entry_dropoff_location.close_time)[0]}
                          </Typography>
                        </Box>
                        <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, mt: 1 }}>
                          {enquiry.entry_dropoff_location.morning_opening === 1 && (
                            <Chip size="small" label="Morning" color="primary" variant="outlined" />
                          )}
                          {enquiry.entry_dropoff_location.afternoon_opening === 1 && (
                            <Chip size="small" label="Afternoon" color="primary" variant="outlined" />
                          )}
                          {enquiry.entry_dropoff_location.evening_opening === 1 && (
                            <Chip size="small" label="Evening" color="primary" variant="outlined" />
                          )}
                          {enquiry.entry_dropoff_location.night_opening === 1 && (
                            <Chip size="small" label="Night" color="primary" variant="outlined" />
                          )}
                        </Box>
                      </>
                    )}
                    
                    {/* For other types of drop-off locations */}
                    {enquiry.entry_dropoff_type !== 'hotel' && enquiry.entry_dropoff_type !== 'restaurant' && enquiry.entry_dropoff_type !== 'attraction' && (
                      <Typography variant="body2">
                        {JSON.stringify(enquiry.entry_dropoff_location)}
                      </Typography>
                    )}
                  </Box>
                )}
              </Grid>
            </Grid>
          </Paper>
        </Box>
      )}
      
      {/* Exit Port Section */}
      {enquiry.exit_port && (
        <Box sx={{ mb: 4 }}>
          <Typography variant="h6" fontWeight={600} color="primary" gutterBottom>
            Exit Port
          </Typography>
          <Paper sx={{ p: 3, bgcolor: 'rgba(25, 118, 210, 0.03)' }}>
            <Grid container spacing={2}>
              <Grid item xs={12} md={6}>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                  <DirectionsBoatIcon fontSize="small" color="primary" sx={{ mr: 1 }} />
                  <Typography variant="body1" fontWeight={500}>
                    {enquiry.exit_port_address}
                  </Typography>
                </Box>
                
                <Box sx={{ mt: 2 }}>
                  <Typography variant="subtitle2" gutterBottom>
                    Pickup Information:
                  </Typography>
                  <Box sx={{ display: 'flex', alignItems: 'center', ml: 1 }}>
                    <Chip 
                      size="small" 
                      color="primary" 
                      variant="outlined"
                      label={
                        enquiry.exit_pickup_type === 'restaurant' 
                          ? 'Restaurant Pickup' 
                          : enquiry.exit_pickup_type === 'hotel'
                            ? 'Hotel Pickup'
                            : enquiry.exit_pickup_type === 'attraction'
                              ? 'Attraction Pickup'
                              : enquiry.exit_pickup_type
                      }
                      icon={
                        enquiry.exit_pickup_type === 'restaurant' 
                          ? <RestaurantIcon fontSize="small" /> 
                          : enquiry.exit_pickup_type === 'hotel'
                            ? <HotelIcon fontSize="small" />
                            : enquiry.exit_pickup_type === 'attraction'
                              ? <AttractionsIcon fontSize="small" />
                              : <LocationIcon fontSize="small" />
                      }
                      sx={{ mb: 1 }}
                    />
                  </Box>
                </Box>
              </Grid>
              
              <Grid item xs={12} md={6}>
                {enquiry.exit_pickup_location && (
                  <Box sx={{ 
                    p: 2, 
                    border: '1px solid', 
                    borderColor: 'divider', 
                    borderRadius: 2,
                    bgcolor: 'background.paper' 
                  }}>
                    {enquiry.exit_pickup_type === 'hotel' && (
                      <>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <HotelIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body1" fontWeight={500}>
                            {enquiry.exit_pickup_location.name}
                          </Typography>
                          <Box sx={{ ml: 1, display: 'flex' }}>
                            {[...Array(parseInt(enquiry.exit_pickup_location.hotel_star_rating) || 0)].map((_, i) => (
                              <Box 
                                key={i} 
                                component="span" 
                                sx={{ 
                                  color: 'gold', 
                                  fontSize: '16px',
                                  lineHeight: 1
                                }}
                              >
                                ★
                              </Box>
                            ))}
                          </Box>
                        </Box>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <LocationIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body2" color="text.secondary">
                            {enquiry.exit_pickup_location.address || `${enquiry.exit_pickup_location.city}, ${enquiry.exit_pickup_location.country}`}
                          </Typography>
                        </Box>
                      </>
                    )}
                    
                    {enquiry.exit_pickup_type === 'restaurant' && (
                      <>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <RestaurantIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body1" fontWeight={500}>
                            {enquiry.exit_pickup_location.name}
                          </Typography>
                        </Box>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <LocationIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body2" color="text.secondary">
                            {enquiry.exit_pickup_location.city}, {enquiry.exit_pickup_location.country}
                          </Typography>
                        </Box>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <RestaurantIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body2" color="text.secondary" fontWeight={500}>
                            {enquiry.exit_pickup_location.cuisine}
                          </Typography>
                        </Box>
                        
                        <Box sx={{ mt: 1 }}>
                          <Typography variant="caption" color="text.secondary">
                            Operating Hours
                          </Typography>
                          <Grid container spacing={1} sx={{ mt: 0.5 }}>
                            {enquiry.exit_pickup_location.breakfast_available === 1 && (
                              <Grid item xs={4}>
                                <Typography variant="caption" display="block" color="text.secondary">
                                  Breakfast
                                </Typography>
                                <Typography variant="body2" sx={{ fontWeight: 500 }}>
                                  {enquiry.exit_pickup_location.opening_time_bf?.substring(0, 5)} - {enquiry.exit_pickup_location.closing_time_bf?.substring(0, 5)}
                                </Typography>
                              </Grid>
                            )}
                            
                            {enquiry.exit_pickup_location.lunch_available === 1 && (
                              <Grid item xs={4}>
                                <Typography variant="caption" display="block" color="text.secondary">
                                  Lunch
                                </Typography>
                                <Typography variant="body2" sx={{ fontWeight: 500 }}>
                                  {enquiry.exit_pickup_location.opening_time_lunch?.substring(0, 5)} - {enquiry.exit_pickup_location.closing_time_lunch?.substring(0, 5)}
                                </Typography>
                              </Grid>
                            )}
                            
                            {enquiry.exit_pickup_location.dinner_available === 1 && (
                              <Grid item xs={4}>
                                <Typography variant="caption" display="block" color="text.secondary">
                                  Dinner
                                </Typography>
                                <Typography variant="body2" sx={{ fontWeight: 500 }}>
                                  {enquiry.exit_pickup_location.opening_time_dinner?.substring(0, 5)} - {enquiry.exit_pickup_location.closing_time_dinner?.substring(0, 5)}
                                </Typography>
                              </Grid>
                            )}
                          </Grid>
                        </Box>
                      </>
                    )}
                    
                    {enquiry.exit_pickup_type === 'attraction' && (
                      <>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <AttractionsIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body1" fontWeight={500}>
                            {enquiry.exit_pickup_location.name}
                          </Typography>
                        </Box>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <LocationIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body2" color="text.secondary">
                            {enquiry.exit_pickup_location.location}, {enquiry.exit_pickup_location.country}
                          </Typography>
                        </Box>
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <AccessTimeIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                          <Typography variant="body2" color="text.secondary">
                            Open: {JSON.parse(enquiry.exit_pickup_location.open_time)[0]} - Close: {JSON.parse(enquiry.exit_pickup_location.close_time)[0]}
                          </Typography>
                        </Box>
                        <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, mt: 1 }}>
                          {enquiry.exit_pickup_location.morning_opening === 1 && (
                            <Chip size="small" label="Morning" color="primary" variant="outlined" />
                          )}
                          {enquiry.exit_pickup_location.afternoon_opening === 1 && (
                            <Chip size="small" label="Afternoon" color="primary" variant="outlined" />
                          )}
                          {enquiry.exit_pickup_location.evening_opening === 1 && (
                            <Chip size="small" label="Evening" color="primary" variant="outlined" />
                          )}
                          {enquiry.exit_pickup_location.night_opening === 1 && (
                            <Chip size="small" label="Night" color="primary" variant="outlined" />
                          )}
                        </Box>
                      </>
                    )}
                    
                    {/* For other types of pickup locations */}
                    {enquiry.exit_pickup_type !== 'hotel' && enquiry.exit_pickup_type !== 'restaurant' && enquiry.exit_pickup_type !== 'attraction' && (
                      <Typography variant="body2">
                        {JSON.stringify(enquiry.exit_pickup_location)}
                      </Typography>
                    )}
                  </Box>
                )}
              </Grid>
            </Grid>
          </Paper>
        </Box>
      )}
      
      {tabValue === 1 && (
        <Box mt={3}>
          <Typography variant="subtitle1" gutterBottom>
            Additional Port Information
          </Typography>
          <Box sx={{ 
            p: 2, 
            bgcolor: 'rgba(0,0,0,0.02)', 
            borderRadius: 1,
            border: '1px solid',
            borderColor: 'divider'
          }}>
            <Typography variant="body2">
              {enquiry.port_remarks || "No additional information available for this port service."}
            </Typography>
            
            {enquiry.port_details && enquiry.port_details.length > 0 && (
              <Box mt={2}>
                <Typography variant="subtitle2" gutterBottom>
                  Port Details:
                </Typography>
                {enquiry.port_details.map((detail, idx) => (
                  <Box key={idx} sx={{ mb: 1 }}>
                    <Typography variant="body2">
                      {JSON.stringify(detail)}
                    </Typography>
                  </Box>
                ))}
              </Box>
            )}
          </Box>
        </Box>
      )}
    </Box>
  );
};

export default RenderPortDetails; 