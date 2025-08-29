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
  IconButton,
  List,
  ListItem,
  ListItemAvatar,
  Avatar,
  ListItemText,
  Rating
} from '@mui/material';
import { useSelector, useDispatch } from 'react-redux';
import AttractionsIcon from '@mui/icons-material/Attractions';
import InfoIcon from '@mui/icons-material/Info';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import EventIcon from '@mui/icons-material/Event';

export default function AttractionComponent({ preDefinedData }) {
  const dispatch = useDispatch();
  const { searchCriteria } = useSelector((state) => state.tourPackages);
  
  // Mock attraction data for the pre-defined package
  const mockAttractions = [
    {
      id: 1,
      name: "Gardens by the Bay",
      location: "Marina Bay, Singapore",
      image: "https://source.unsplash.com/random/600x400/?garden",
      description: "Iconic gardens featuring Supertrees and conservatories",
      duration: "3 hours",
      rating: 4.8,
      reviews: 1203,
      price: 25,
      included: ["Admission Ticket", "Guide"],
      day: 2,
      time: "09:00 AM"
    },
    {
      id: 2,
      name: "Singapore Flyer",
      location: "Downtown, Singapore",
      image: "https://source.unsplash.com/random/600x400/?ferris-wheel",
      description: "Giant observation wheel with panoramic views of the city",
      duration: "1 hour",
      rating: 4.5,
      reviews: 856,
      price: 33,
      included: ["Admission Ticket"],
      day: 2,
      time: "02:00 PM"
    },
    {
      id: 3,
      name: "Sentosa Island",
      location: "Sentosa, Singapore",
      image: "https://source.unsplash.com/random/600x400/?beach",
      description: "Island resort with beaches, theme parks, and entertainment",
      duration: "Full Day",
      rating: 4.7,
      reviews: 1520,
      price: 40,
      included: ["Transport", "Guide", "Lunch"],
      day: 3,
      time: "10:00 AM"
    }
  ];

  // If we have pre-defined data, use it
  const attractionData = preDefinedData?.attractions || mockAttractions;
  
  // Group attractions by day
  const attractionsByDay = attractionData.reduce((acc, attraction) => {
    const day = attraction.day || 1;
    if (!acc[day]) acc[day] = [];
    acc[day].push(attraction);
    return acc;
  }, {});
  
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
          <AttractionsIcon />
        </Box>
        <Typography variant="h5">Attractions & Activities</Typography>
      </Box>
      
      <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
        Sightseeing tours and activities included in this pre-defined package.
      </Typography>
      
      {/* Display attractions grouped by day */}
      {Object.entries(attractionsByDay).map(([day, attractions]) => (
        <Box key={day} sx={{ mb: 4 }}>
          <Typography variant="h6" gutterBottom>
            Day {day} Activities
          </Typography>
          
          <Grid container spacing={3}>
            {attractions.map((attraction) => (
              <Grid item xs={12} md={6} key={attraction.id}>
                <Card elevation={2}>
                  <CardMedia
                    component="img"
                    height="160"
                    image={attraction.image}
                    alt={attraction.name}
                  />
                  <CardContent>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                      <Typography variant="h6" component="div">
                        {attraction.name}
                      </Typography>
                      <Chip 
                        icon={<AccessTimeIcon />} 
                        label={attraction.duration} 
                        size="small" 
                        color="primary" 
                        variant="outlined" 
                      />
                    </Box>
                    
                    <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                      <Rating 
                        value={attraction.rating} 
                        precision={0.1} 
                        readOnly 
                        size="small" 
                      />
                      <Typography variant="body2" color="text.secondary" sx={{ ml: 1 }}>
                        ({attraction.reviews})
                      </Typography>
                    </Box>
                    
                    <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                      <LocationOnIcon color="action" fontSize="small" sx={{ mr: 0.5 }} />
                      <Typography variant="body2" color="text.secondary">
                        {attraction.location}
                      </Typography>
                    </Box>
                    
                    <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                      <EventIcon color="action" fontSize="small" sx={{ mr: 0.5 }} />
                      <Typography variant="body2" color="text.secondary">
                        {attraction.time}
                      </Typography>
                    </Box>
                    
                    <Typography variant="body2" paragraph>
                      {attraction.description}
                    </Typography>
                    
                    <Divider sx={{ mb: 2 }} />
                    
                    <Typography variant="subtitle2" gutterBottom>
                      Included:
                    </Typography>
                    
                    <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5, mb: 2 }}>
                      {attraction.included && attraction.included.map((item, i) => (
                        <Chip 
                          key={i} 
                          label={item} 
                          size="small" 
                          variant="outlined" 
                        />
                      ))}
                    </Box>
                    
                    <Box sx={{ display: 'flex', justifyContent: 'flex-end' }}>
                      <Typography variant="subtitle1" color="primary" fontWeight={500}>
                        Value: ${attraction.price}
                      </Typography>
                    </Box>
                  </CardContent>
                </Card>
              </Grid>
            ))}
          </Grid>
        </Box>
      ))}
    </Box>
  );
}
