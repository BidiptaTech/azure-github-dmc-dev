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
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Rating
} from '@mui/material';
import { useSelector, useDispatch } from 'react-redux';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import EventIcon from '@mui/icons-material/Event';
import FastfoodIcon from '@mui/icons-material/Fastfood';
import AccessTimeIcon from '@mui/icons-material/AccessTime';

export default function RestaurantComponent({ preDefinedData }) {
  const dispatch = useDispatch();
  const { searchCriteria } = useSelector((state) => state.tourPackages);
  
  // Mock meal data for the pre-defined package
  const mockMeals = [
    {
      id: 1,
      name: "Hawker Center Lunch",
      location: "Maxwell Food Centre, Singapore",
      type: "Lunch",
      image: "https://source.unsplash.com/random/600x400/?food-market",
      description: "Experience Singapore's famous hawker food culture with a variety of local dishes",
      cuisine: "Local Street Food",
      rating: 4.6,
      reviews: 983,
      price: 15,
      included: ["Set Menu", "Soft Drink"],
      day: 2,
      time: "12:30 PM"
    },
    {
      id: 2,
      name: "Seafood Dinner",
      location: "East Coast Seafood Centre, Singapore",
      type: "Dinner",
      image: "https://source.unsplash.com/random/600x400/?seafood",
      description: "Fresh seafood dinner featuring Singapore's famous chili crab and black pepper crab",
      cuisine: "Seafood",
      rating: 4.8,
      reviews: 756,
      price: 40,
      included: ["Set Menu", "Dessert"],
      day: 3,
      time: "7:00 PM"
    },
    {
      id: 3,
      name: "Hotel Breakfast",
      location: "Marina Bay Hotel, Singapore",
      type: "Breakfast",
      image: "https://source.unsplash.com/random/600x400/?breakfast-buffet",
      description: "International breakfast buffet with local and western options",
      cuisine: "International",
      rating: 4.5,
      reviews: 421,
      price: 25,
      included: ["Buffet", "Coffee/Tea"],
      day: 2,
      time: "7:00 AM - 10:00 AM"
    },
    {
      id: 4,
      name: "Hotel Breakfast",
      location: "Marina Bay Hotel, Singapore",
      type: "Breakfast",
      image: "https://source.unsplash.com/random/600x400/?breakfast-buffet",
      description: "International breakfast buffet with local and western options",
      cuisine: "International",
      rating: 4.5,
      reviews: 421,
      price: 25,
      included: ["Buffet", "Coffee/Tea"],
      day: 3,
      time: "7:00 AM - 10:00 AM"
    }
  ];

  // If we have pre-defined data, use it
  const mealData = preDefinedData?.meals || mockMeals;
  
  // Group meals by day
  const mealsByDay = mealData.reduce((acc, meal) => {
    const day = meal.day || 1;
    if (!acc[day]) acc[day] = [];
    acc[day].push(meal);
    return acc;
  }, {});

  // Count meals by type
  const mealCounts = mealData.reduce((acc, meal) => {
    const type = meal.type.toLowerCase();
    acc[type] = (acc[type] || 0) + 1;
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
          <RestaurantIcon />
        </Box>
        <Typography variant="h5">Meals</Typography>
      </Box>
      
      <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
        Meals included in this pre-defined package.
      </Typography>
      
      {/* Meal summary */}
      <Paper variant="outlined" sx={{ p: 2, mb: 3 }}>
        <Typography variant="subtitle1" gutterBottom>
          Meal Summary
        </Typography>
        <Grid container spacing={2}>
          <Grid item xs={4}>
            <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
              <Typography variant="h4" color="primary">
                {mealCounts.breakfast || 0}
              </Typography>
              <Typography variant="body2">Breakfasts</Typography>
            </Box>
          </Grid>
          <Grid item xs={4}>
            <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
              <Typography variant="h4" color="primary">
                {mealCounts.lunch || 0}
              </Typography>
              <Typography variant="body2">Lunches</Typography>
            </Box>
          </Grid>
          <Grid item xs={4}>
            <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center' }}>
              <Typography variant="h4" color="primary">
                {mealCounts.dinner || 0}
              </Typography>
              <Typography variant="body2">Dinners</Typography>
            </Box>
          </Grid>
        </Grid>
      </Paper>
      
      {/* Display meals grouped by day */}
      {Object.entries(mealsByDay).map(([day, meals]) => (
        <Box key={day} sx={{ mb: 4 }}>
          <Typography variant="h6" gutterBottom>
            Day {day} Meals
          </Typography>
          
          <Grid container spacing={3}>
            {meals.map((meal) => (
              <Grid item xs={12} md={6} key={meal.id}>
                <Card elevation={2}>
                  <Grid container>
                    <Grid item xs={12} sm={4}>
                      <CardMedia
                        component="img"
                        sx={{ height: '100%', minHeight: 160 }}
                        image={meal.image}
                        alt={meal.name}
                      />
                    </Grid>
                    <Grid item xs={12} sm={8}>
                      <CardContent>
                        <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                          <Typography variant="h6" component="div">
                            {meal.name}
                          </Typography>
                          <Chip 
                            label={meal.type} 
                            size="small" 
                            color="primary" 
                          />
                        </Box>
                        
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <FastfoodIcon fontSize="small" sx={{ mr: 0.5 }} />
                          <Typography variant="body2" color="text.secondary">
                            {meal.cuisine}
                          </Typography>
                        </Box>
                        
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                          <LocationOnIcon color="action" fontSize="small" sx={{ mr: 0.5 }} />
                          <Typography variant="body2" color="text.secondary">
                            {meal.location}
                          </Typography>
                        </Box>
                        
                        <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                          <EventIcon color="action" fontSize="small" sx={{ mr: 0.5 }} />
                          <Typography variant="body2" color="text.secondary">
                            {meal.time}
                          </Typography>
                        </Box>
                        
                        <Typography variant="body2" sx={{ mb: 1.5 }}>
                          {meal.description}
                        </Typography>
                        
                        <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5, mb: 1.5 }}>
                          {meal.included && meal.included.map((item, i) => (
                            <Chip 
                              key={i} 
                              label={item} 
                              size="small" 
                              variant="outlined" 
                            />
                          ))}
                        </Box>
                        
                        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                          <Box sx={{ display: 'flex', alignItems: 'center' }}>
                            <Rating 
                              value={meal.rating} 
                              precision={0.1} 
                              readOnly 
                              size="small" 
                            />
                            <Typography variant="body2" color="text.secondary" sx={{ ml: 0.5 }}>
                              ({meal.reviews})
                            </Typography>
                          </Box>
                          <Typography variant="subtitle1" color="primary" fontWeight={500}>
                            Value: ${meal.price}
                          </Typography>
                        </Box>
                      </CardContent>
                    </Grid>
                  </Grid>
                </Card>
              </Grid>
            ))}
          </Grid>
        </Box>
      ))}
      
      <Box sx={{ mt: 4 }}>
        <Typography variant="subtitle1" gutterBottom>
          Meals Schedule
        </Typography>
        <TableContainer component={Paper} variant="outlined">
          <Table size="small">
            <TableHead>
              <TableRow>
                <TableCell>Day</TableCell>
                <TableCell>Breakfast</TableCell>
                <TableCell>Lunch</TableCell>
                <TableCell>Dinner</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {Object.entries(mealsByDay).map(([day, meals]) => {
                // Find meals by type for this day
                const breakfast = meals.find(m => m.type.toLowerCase() === 'breakfast');
                const lunch = meals.find(m => m.type.toLowerCase() === 'lunch');
                const dinner = meals.find(m => m.type.toLowerCase() === 'dinner');
                
                return (
                  <TableRow key={day}>
                    <TableCell>Day {day}</TableCell>
                    <TableCell>{breakfast ? breakfast.name : 'Not included'}</TableCell>
                    <TableCell>{lunch ? lunch.name : 'Not included'}</TableCell>
                    <TableCell>{dinner ? dinner.name : 'Not included'}</TableCell>
                  </TableRow>
                );
              })}
            </TableBody>
          </Table>
        </TableContainer>
      </Box>
    </Box>
  );
}
