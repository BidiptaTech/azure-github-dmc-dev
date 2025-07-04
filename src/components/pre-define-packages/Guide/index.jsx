import React, { useState } from 'react';
import { 
  Box, 
  Typography, 
  Paper, 
  Grid, 
  Card, 
  CardContent, 
  CardMedia, 
  Divider, 
  Stack,
  Chip,
  Button,
  Avatar,
  Rating,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  ListItemAvatar
} from '@mui/material';
import { useSelector, useDispatch } from 'react-redux';
import PersonIcon from '@mui/icons-material/Person';
import LanguageIcon from '@mui/icons-material/Language';
import EventAvailableIcon from '@mui/icons-material/EventAvailable';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';

export default function GuideComponent({ preDefinedData }) {
  const dispatch = useDispatch();
  const { searchCriteria } = useSelector((state) => state.tourPackages);
  
  // Mock guide data for the pre-defined package
  const mockGuide = {
    id: 1,
    name: "John Smith",
    image: "https://source.unsplash.com/random/400x400/?portrait",
    description: "Experienced tour guide with deep knowledge of Singapore's history and culture",
    experience: 5, // years
    languages: ["English", "Mandarin", "Malay"],
    rating: 4.9,
    reviews: 156,
    price: 120,
    availability: {
      day2: true,
      day3: true
    },
    specialties: ["History", "Architecture", "Food", "Cultural"],
    services: [
      "Professional guided tours",
      "Personalized itinerary assistance",
      "Local insights and recommendations",
      "Coordination with attractions"
    ]
  };

  // If we have pre-defined data, use it
  const guideData = preDefinedData?.guide || mockGuide;
  
  return (
    <Box>
      <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
        <Box 
          sx={{ 
            bgcolor: 'primary.main', 
            color: 'white', 
            p: 0.8, 
            borderRadius: '50%', 
            mr: 1,
            display: 'flex'
          }}
        >
          <PersonIcon />
        </Box>
        <Typography variant="h5">Tour Guide</Typography>
      </Box>
      
      <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
        Professional tour guide included in this pre-defined package.
      </Typography>

      <Card elevation={2} sx={{ mb: 4 }}>
        <Grid container>
          <Grid item xs={12} md={4}>
            <Box sx={{ 
              p: 3, 
              display: 'flex', 
              flexDirection: 'column', 
              alignItems: 'center',
              textAlign: 'center',
              height: '100%',
              backgroundColor: 'background.paper'
            }}>
              <Avatar
                src={guideData.image}
                alt={guideData.name}
                sx={{ width: 120, height: 120, mb: 2 }}
              />
              <Typography variant="h6" gutterBottom>
                {guideData.name}
              </Typography>
              
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                <Rating 
                  value={guideData.rating} 
                  precision={0.1} 
                  readOnly 
                  size="small" 
                />
                <Typography variant="body2" color="text.secondary" sx={{ ml: 1 }}>
                  ({guideData.reviews} reviews)
                </Typography>
              </Box>
              
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                <EventAvailableIcon color="action" fontSize="small" sx={{ mr: 0.5 }} />
                <Typography variant="body2" color="text.secondary">
                  {guideData.experience} years experience
                </Typography>
              </Box>
              
              <Box sx={{ mb: 2 }}>
                <Typography variant="subtitle2" gutterBottom>
                  Languages:
                </Typography>
                <Box sx={{ display: 'flex', flexWrap: 'wrap', justifyContent: 'center', gap: 0.5 }}>
                  {guideData.languages.map((language, index) => (
                    <Chip 
                      key={index} 
                      icon={<LanguageIcon />} 
                      label={language} 
                      size="small" 
                      variant="outlined" 
                    />
                  ))}
                </Box>
              </Box>
              
              <Box sx={{ mt: 'auto' }}>
                <Typography variant="subtitle1" color="primary" fontWeight={500}>
                  Value: ${guideData.price} / day
                </Typography>
              </Box>
            </Box>
          </Grid>
          
          <Grid item xs={12} md={8}>
            <CardContent>
              <Typography variant="subtitle1" gutterBottom fontWeight={500}>
                About Your Guide
              </Typography>
              <Typography variant="body2" paragraph>
                {guideData.description}
              </Typography>
              
              <Divider sx={{ my: 2 }} />
              
              <Grid container spacing={3}>
                <Grid item xs={12} md={6}>
                  <Typography variant="subtitle1" gutterBottom fontWeight={500}>
                    Specialties
                  </Typography>
                  <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5, mb: 2 }}>
                    {guideData.specialties.map((specialty, index) => (
                      <Chip 
                        key={index} 
                        label={specialty} 
                        size="small" 
                      />
                    ))}
                  </Box>
                </Grid>
                
                <Grid item xs={12} md={6}>
                  <Typography variant="subtitle1" gutterBottom fontWeight={500}>
                    Guide Schedule
                  </Typography>
                  <List dense>
                    {Object.entries(guideData.availability).map(([day, available]) => (
                      <ListItem key={day} disablePadding>
                        <ListItemIcon sx={{ minWidth: 36 }}>
                          <CheckCircleIcon color="primary" fontSize="small" />
                        </ListItemIcon>
                        <ListItemText 
                          primary={`Day ${day.replace('day', '')} - ${available ? 'Available' : 'Not Available'}`} 
                        />
                      </ListItem>
                    ))}
                  </List>
                </Grid>
              </Grid>
              
              <Divider sx={{ my: 2 }} />
              
              <Typography variant="subtitle1" gutterBottom fontWeight={500}>
                Guide Services
              </Typography>
              <List dense>
                {guideData.services.map((service, index) => (
                  <ListItem key={index} disablePadding>
                    <ListItemIcon sx={{ minWidth: 36 }}>
                      <CheckCircleIcon color="primary" fontSize="small" />
                    </ListItemIcon>
                    <ListItemText primary={service} />
                  </ListItem>
                ))}
              </List>
            </CardContent>
          </Grid>
        </Grid>
      </Card>
      
      <Paper variant="outlined" sx={{ p: 2 }}>
        <Typography variant="subtitle1" gutterBottom>
          Note About Guide Service
        </Typography>
        <Typography variant="body2" color="text.secondary">
          Your tour guide will meet you according to the schedule above and assist you throughout your journey. 
          They are knowledgeable about local culture, history, and attractions. Guide service hours are typically 
          from 9:00 AM to 5:00 PM on scheduled days. For any special requests or additional hours, 
          please contact us in advance.
        </Typography>
      </Paper>
    </Box>
  );
}
