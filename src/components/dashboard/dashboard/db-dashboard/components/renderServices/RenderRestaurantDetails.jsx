import React from 'react';
import {
  Box,
  Typography,
  Divider,
  List,
  Grid,
} from "@mui/material";
import {
  LocationOn as LocationIcon,
  Restaurant as RestaurantIcon,
} from '@mui/icons-material';

const RenderRestaurantDetails = ({ details, tabValue }) => {
  if (!details || details.length === 0) {
    return (
      <Box sx={{ p: 3, textAlign: "center" }}>
        <Typography variant="body1" color="textSecondary">
          No restaurant details available
        </Typography>
      </Box>
    );
  }
  
  return (
    <List sx={{ width: '100%', bgcolor: 'background.paper' }}>
      {details.map((restaurant, index) => (
        <React.Fragment key={restaurant.id}>
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
                  src={restaurant.master_image}
                  alt={restaurant.name}
                  sx={{ 
                    width: '100%',
                    height: '100%',
                    objectFit: 'cover'
                  }}
                />
              </Box>
              <Box sx={{ flex: 1 }}>
                <Typography variant="h6" fontWeight={600} gutterBottom>
                  {restaurant.name}
                </Typography>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                  <RestaurantIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                  <Typography variant="body2" color="text.secondary" fontWeight={500}>
                    {restaurant.cuisine}
                  </Typography>
                </Box>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                  <LocationIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                  <Typography variant="body2" color="text.secondary">
                    {restaurant.city}, {restaurant.country}
                  </Typography>
                </Box>
                
                <Box sx={{ mt: 2 }}>
                  <Typography variant="subtitle2" gutterBottom>Operating Hours:</Typography>
                  <Grid container spacing={1}>
                    {restaurant.breakfast_available === 1 && (
                      <Grid item xs={12} sm={4}>
                        <Box sx={{ 
                          p: 1, 
                          border: '1px solid', 
                          borderColor: 'divider', 
                          borderRadius: 1, 
                          bgcolor: 'rgba(0,0,0,0.01)' 
                        }}>
                          <Typography variant="caption" color="text.secondary">Breakfast</Typography>
                          <Typography variant="body2" fontWeight={500}>
                            {restaurant.opening_time_bf?.substring(0, 5)} - {restaurant.closing_time_bf?.substring(0, 5)}
                          </Typography>
                        </Box>
                      </Grid>
                    )}
                    
                    {restaurant.lunch_available === 1 && (
                      <Grid item xs={12} sm={4}>
                        <Box sx={{ 
                          p: 1, 
                          border: '1px solid', 
                          borderColor: 'divider', 
                          borderRadius: 1, 
                          bgcolor: 'rgba(0,0,0,0.01)' 
                        }}>
                          <Typography variant="caption" color="text.secondary">Lunch</Typography>
                          <Typography variant="body2" fontWeight={500}>
                            {restaurant.opening_time_lunch?.substring(0, 5)} - {restaurant.closing_time_lunch?.substring(0, 5)}
                          </Typography>
                        </Box>
                      </Grid>
                    )}
                    
                    {restaurant.dinner_available === 1 && (
                      <Grid item xs={12} sm={4}>
                        <Box sx={{ 
                          p: 1, 
                          border: '1px solid', 
                          borderColor: 'divider', 
                          borderRadius: 1, 
                          bgcolor: 'rgba(0,0,0,0.01)' 
                        }}>
                          <Typography variant="caption" color="text.secondary">Dinner</Typography>
                          <Typography variant="body2" fontWeight={500}>
                            {restaurant.opening_time_dinner?.substring(0, 5)} - {restaurant.closing_time_dinner?.substring(0, 5)}
                          </Typography>
                        </Box>
                      </Grid>
                    )}
                  </Grid>
                </Box>
              </Box>
              <Box sx={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'center', px: 2, minWidth: 120 }}>
                {restaurant.bf_price > 0 && (
                  <>
                    <Typography variant="body2" color="primary" fontWeight={600}>
                      ${restaurant.bf_price}
                    </Typography>
                    <Typography variant="caption" color="text.secondary">breakfast</Typography>
                  </>
                )}
                
                {restaurant.lunch_price > 0 && (
                  <>
                    <Typography variant="body2" color="primary" fontWeight={600} mt={1}>
                      ${restaurant.lunch_price}
                    </Typography>
                    <Typography variant="caption" color="text.secondary">lunch</Typography>
                  </>
                )}
                
                {restaurant.dinner_price > 0 && (
                  <>
                    <Typography variant="body2" color="primary" fontWeight={600} mt={1}>
                      ${restaurant.dinner_price}
                    </Typography>
                    <Typography variant="caption" color="text.secondary">dinner</Typography>
                  </>
                )}
              </Box>
            </Box>
            
            {tabValue === 1 && (
              <Box mt={2}>
                <Typography variant="subtitle2" gutterBottom>About the Restaurant:</Typography>
                <Box
                  sx={{
                    borderRadius: 1,
                    p: 2,
                    bgcolor: 'rgba(0,0,0,0.02)',
                    maxHeight: '200px',
                    overflow: 'auto'
                  }}
                  dangerouslySetInnerHTML={{ __html: restaurant.description }}
                />
                
                {/* Image Gallery */}
                {/* {restaurant.images && JSON.parse(restaurant.images).length > 0 && (
                  <Box mt={2}>
                    <Typography variant="subtitle2" gutterBottom>Photo Gallery:</Typography>
                    <Box sx={{ display: 'flex', gap: 1, mt: 1, flexWrap: 'wrap' }}>
                      {JSON.parse(restaurant.images).map((img, idx) => (
                        <Box 
                          key={idx}
                          component="img"
                          src={img}
                          alt={`${restaurant.name} image ${idx+1}`}
                          sx={{ 
                            width: 80,
                            height: 60,
                            objectFit: 'cover',
                            borderRadius: 1
                          }}
                        />
                      ))}
                    </Box>
                  </Box>
                )} */}
              </Box>
            )}
          </Box>
          {index < details.length - 1 && <Divider sx={{ my: 2 }} />}
        </React.Fragment>
      ))}
    </List>
  );
};

export default RenderRestaurantDetails; 