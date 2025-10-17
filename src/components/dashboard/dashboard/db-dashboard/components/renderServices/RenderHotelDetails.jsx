import React from 'react';
import {
  Box,
  Typography,
  Divider,
  List,
  ListItem,
  ListItemAvatar,
  ListItemText,
  Avatar,
  Chip,
  Grid,
  Paper,
} from "@mui/material";
import {
  LocationOn as LocationIcon,
  AccessTime as AccessTimeIcon,
  Phone as PhoneIcon,
  Email as EmailIcon,
} from '@mui/icons-material';

const RenderHotelDetails = ({ details, tabValue }) => {
  if (!details || details.length === 0) {
    return (
      <Box sx={{ p: 3, textAlign: "center" }}>
        <Typography variant="body1" color="textSecondary">
          No hotel details available
        </Typography>
      </Box>
    );
  }
  
  return (
    <List sx={{ width: '100%', bgcolor: 'background.paper' }}>
      {details.map((hotel, index) => (
        <React.Fragment key={hotel.id}>
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
                  src={hotel.main_image}
                  alt={hotel.name}
                  sx={{ 
                    width: '100%',
                    height: '100%',
                    objectFit: 'cover'
                  }}
                />
              </Box>
              <Box sx={{ flex: 1 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                  <Typography variant="h6" fontWeight={600}>
                    {hotel.name}
                  </Typography>
                  <Box sx={{ ml: 1, display: 'flex' }}>
                    {[...Array(parseInt(hotel.hotel_star_rating) || 0)].map((_, i) => (
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
                    {hotel.address || `${hotel.city}, ${hotel.country}`}
                  </Typography>
                </Box>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                  <AccessTimeIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                  <Typography variant="body2" color="text.secondary">
                    Check-in: {hotel.check_in_time} - Check-out: {hotel.check_out_time}
                  </Typography>
                </Box>
                <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                  <PhoneIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                  <Typography variant="body2" color="text.secondary">
                    {hotel.phone && `${hotel.country_code ? '+' + hotel.country_code : ''} ${hotel.phone}`}
                  </Typography>
                </Box>
                {hotel.email && (
                  <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                    <EmailIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                    <Typography variant="body2" color="text.secondary">
                      {hotel.email}
                    </Typography>
                  </Box>
                )}
                <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, mt: 1 }}>
                  {hotel.includes_breakfast === 1 && (
                    <Chip size="small" label="Breakfast Included" color="primary" variant="outlined" />
                  )}
                  {hotel.includes_lunch === 1 && (
                    <Chip size="small" label="Lunch Included" color="primary" variant="outlined" />
                  )}
                  {hotel.includes_dinner === 1 && (
                    <Chip size="small" label="Dinner Included" color="primary" variant="outlined" />
                  )}
                  {hotel.pet_allowed === 1 && (
                    <Chip size="small" label="Pet Friendly" color="primary" variant="outlined" />
                  )}
                </Box>
              </Box>
              <Box sx={{ display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'center', px: 2 }}>
                <Typography variant="h6" color="primary" fontWeight={600}>
                  {hotel.display_id}
                </Typography>
                <Typography variant="caption" color="text.secondary">Hotel ID</Typography>
                
                {hotel.twelve_hours_charge > 0 && (
                  <>
                    <Typography variant="body2" color="primary" fontWeight={600} mt={1}>
                      ${hotel.twelve_hours_charge}
                    </Typography>
                    <Typography variant="caption" color="text.secondary">12 hr rate</Typography>
                  </>
                )}
              </Box>
            </Box>
            
            {tabValue === 1 && (
              <Box mt={2}>
                <Typography variant="subtitle2" gutterBottom>About the Hotel:</Typography>
                <Box
                  sx={{
                    borderRadius: 1,
                    p: 2,
                    bgcolor: 'rgba(0,0,0,0.02)',
                    maxHeight: '200px',
                    overflow: 'auto'
                  }}
                  dangerouslySetInnerHTML={{ __html: hotel.description }}
                />
                
                {/* Image Gallery */}
                {hotel.images && JSON.parse(hotel.images).length > 0 && (
                  <Box mt={2}>
                    <Typography variant="subtitle2" gutterBottom>Photo Gallery:</Typography>
                    <Box sx={{ display: 'flex', gap: 1, mt: 1, flexWrap: 'wrap' }}>
                      {JSON.parse(hotel.images).map((img, idx) => (
                        <Box 
                          key={idx}
                          component="img"
                          src={img}
                          alt={`${hotel.name} image ${idx+1}`}
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
                )}
              </Box>
            )}
          </Box>
          {index < details.length - 1 && <Divider sx={{ my: 2 }} />}
        </React.Fragment>
      ))}
    </List>
  );
};

export default RenderHotelDetails; 