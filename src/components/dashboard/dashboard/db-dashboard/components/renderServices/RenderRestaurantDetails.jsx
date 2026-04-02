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
  Restaurant as RestaurantIcon,
  CalendarToday as CalendarIcon,
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

  // Helper function to format date
  const formatDate = (dateString) => {
    if (!dateString) return 'Date not specified';
    try {
      const date = new Date(dateString);
      if (isNaN(date.getTime())) {
        return dateString;
      }
      return date.toLocaleDateString('en-US', { 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
      });
    } catch {
      return dateString || 'Date not specified';
    }
  };

  // Helper function to get meal period color
  const getMealPeriodColor = (period) => {
    switch (period?.toLowerCase()) {
      case 'breakfast': return '#FF6B35';
      case 'lunch': return '#F7931E';
      case 'dinner': return '#C1121F';
      default: return '#757575';
    }
  };

  // Check if data is in date-grouped format or flat array format
  const isDateGroupedFormat = details[0]?.date !== undefined && details[0]?.restaurants !== undefined;
  
  // Normalize data structure
  const normalizedData = isDateGroupedFormat 
    ? details 
    : [{ date: null, restaurants: details }];
  
  return (
    <List sx={{ width: '100%', bgcolor: 'background.paper', p: 0 }}>
      {normalizedData.map((dateEntry, dateIndex) => (
        <React.Fragment key={`date-${dateIndex}`}>
          {/* Date Header - only show if date exists */}
          {dateEntry.date && (
            <Box 
              sx={{ 
                display: 'flex', 
                alignItems: 'center', 
                gap: 0.75, 
                mb: 1.5,
                p: 1,
                bgcolor: 'rgba(25, 118, 210, 0.08)',
                borderRadius: 1,
                borderLeft: '3px solid',
                borderLeftColor: 'primary.main'
              }}
            >
              <CalendarIcon color="primary" sx={{ fontSize: 18 }} />
              <Typography variant="body2" fontWeight={600} color="primary.main">
                {formatDate(dateEntry.date)}
              </Typography>
            </Box>
          )}

          {/* Restaurants for this date */}
          {dateEntry.restaurants?.map((restaurant, restIndex) => (
            <React.Fragment key={`rest-${dateIndex}-${restIndex}`}>
              <Box sx={{ mb: 2.5, ml: 1.5 }}>
                <Box 
                  sx={{ 
                    display: 'flex', 
                    flexDirection: { xs: 'column', md: 'row' },
                    gap: 1.5,
                    mb: 1.5
                  }}
                >
                  {/* Restaurant Image */}
                  <Box 
                    sx={{ 
                      width: { xs: '100%', md: 140 },
                      height: 100,
                      position: 'relative',
                      borderRadius: 1.5,
                      overflow: 'hidden',
                      flexShrink: 0
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

                  {/* Restaurant Details */}
                  <Box sx={{ flex: 1, minWidth: 0 }}>
                    <Typography variant="subtitle2" fontWeight={700} gutterBottom sx={{ lineHeight: 1.3 }}>
                      {restaurant.name}
                    </Typography>
                    <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
                      <LocationIcon sx={{ fontSize: 16, mr: 0.5 }} color="action" />
                      <Typography variant="caption" color="text.secondary">
                        {restaurant.city}, {restaurant.country}
                      </Typography>
                    </Box>

                    {/* Meals Section */}
                    {restaurant.meals && restaurant.meals.length > 0 ? (
                      <Box sx={{ mt: 1 }}>
                        <Typography variant="caption" fontWeight={600} gutterBottom display="block" sx={{ mb: 1 }}>
                          Selected Meals:
                        </Typography>
                        <Grid container spacing={1}>
                          {restaurant.meals.map((meal, mealIndex) => (
                            <Grid item xs={12} key={`meal-${mealIndex}`}>
                              <Box 
                                sx={{ 
                                  p: 1.5, 
                                  border: '1px solid', 
                                  borderColor: 'divider', 
                                  borderRadius: 1.5,
                                  bgcolor: 'rgba(0,0,0,0.01)',
                                  transition: 'all 0.2s',
                                  '&:hover': {
                                    bgcolor: 'rgba(0,0,0,0.03)',
                                    boxShadow: 1
                                  }
                                }}
                              >
                                {/* Meal Period Badge */}
                                <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 1, flexWrap: 'wrap', gap: 0.5 }}>
                                  <Chip
                                    label={meal.meal_period}
                                    size="small"
                                    sx={{
                                      bgcolor: getMealPeriodColor(meal.meal_period),
                                      color: 'white',
                                      fontWeight: 600,
                                      fontSize: '0.7rem',
                                      height: 22
                                    }}
                                  />
                                  <Box sx={{ display: 'flex', gap: 0.5, flexWrap: 'wrap' }}>
                                    <Chip
                                      label={meal.meal_type}
                                      size="small"
                                      variant="outlined"
                                      sx={{ fontSize: '0.65rem', height: 22 }}
                                    />
                                    <Chip
                                      label={meal.item_type}
                                      size="small"
                                      variant="outlined"
                                      color={meal.item_type === 'Vegetarian' ? 'success' : 'default'}
                                      sx={{ fontSize: '0.65rem', height: 22 }}
                                    />
                                    <Chip
                                      label={meal.category}
                                      size="small"
                                      variant="outlined"
                                      color={meal.category === 'Alcoholic' ? 'warning' : 'default'}
                                      sx={{ fontSize: '0.65rem', height: 22 }}
                                    />
                                  </Box>
                                </Box>

                                {/* Meal Description */}
                                {meal.item_description && (
                                  <Typography variant="caption" color="text.secondary" sx={{ mb: 1, display: 'block', lineHeight: 1.4 }}>
                                    {meal.item_description}
                                  </Typography>
                                )}

                                {/* Pricing */}
                                <Box sx={{ display: 'flex', gap: 1, flexWrap: 'wrap' }}>
                                  {meal.set_menu_price && (
                                    <Box 
                                      sx={{ 
                                        px: 1.5, 
                                        py: 0.5, 
                                        bgcolor: 'rgba(255, 152, 0, 0.1)', 
                                        borderRadius: 1,
                                        display: 'flex',
                                        gap: 0.5,
                                        alignItems: 'center'
                                      }}
                                    >
                                      <Typography variant="caption" fontWeight={600} color="warning.main">
                                        ${meal.set_menu_price}
                                      </Typography>
                                      <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.65rem' }}>
                                        Set Menu
                                      </Typography>
                                    </Box>
                                  )}
                                  {meal.adult_price && (
                                    <Box 
                                      sx={{ 
                                        px: 1.5, 
                                        py: 0.5, 
                                        bgcolor: 'rgba(255, 152, 0, 0.1)', 
                                        borderRadius: 1,
                                        display: 'flex',
                                        gap: 0.5,
                                        alignItems: 'center'
                                      }}
                                    >
                                      <Typography variant="caption" fontWeight={600} color="warning.main">
                                        ${meal.adult_price}
                                      </Typography>
                                      <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.65rem' }}>
                                        Adult
                                      </Typography>
                                    </Box>
                                  )}
                                  {meal.child_price && (
                                    <Box 
                                      sx={{ 
                                        px: 1.5, 
                                        py: 0.5, 
                                        bgcolor: 'rgba(255, 152, 0, 0.1)', 
                                        borderRadius: 1,
                                        display: 'flex',
                                        gap: 0.5,
                                        alignItems: 'center'
                                      }}
                                    >
                                      <Typography variant="caption" fontWeight={600} color="warning.main">
                                        ${meal.child_price}
                                      </Typography>
                                      <Typography variant="caption" color="text.secondary" sx={{ fontSize: '0.65rem' }}>
                                        Child
                                      </Typography>
                                    </Box>
                                  )}
                                </Box>
                              </Box>
                            </Grid>
                          ))}
                        </Grid>
                      </Box>
                    ) : (
                      // Show available pricing when no meals are selected
                      (restaurant.bf_price || restaurant.lunch_price || restaurant.dinner_price) && (
                        <Box sx={{ mt: 1 }}>
                          <Typography variant="caption" fontWeight={600} gutterBottom display="block" sx={{ mb: 1 }}>
                            Available Pricing:
                          </Typography>
                          <Box sx={{ display: 'flex', gap: 1, flexWrap: 'wrap' }}>
                            {restaurant.breakfast_available && restaurant.bf_price && (
                              <Box 
                                sx={{ 
                                  px: 1.5, 
                                  py: 0.5, 
                                  bgcolor: 'rgba(255, 107, 53, 0.1)', 
                                  borderRadius: 1,
                                  display: 'flex',
                                  gap: 0.5,
                                  alignItems: 'center'
                                }}
                              >
                                <Chip
                                  label="Breakfast"
                                  size="small"
                                  sx={{
                                    bgcolor: getMealPeriodColor('breakfast'),
                                    color: 'white',
                                    fontWeight: 600,
                                    fontSize: '0.65rem',
                                    height: 20
                                  }}
                                />
                                <Typography variant="caption" fontWeight={600} color="warning.main">
                                  ${restaurant.bf_price}
                                </Typography>
                              </Box>
                            )}
                            {restaurant.lunch_available && restaurant.lunch_price && (
                              <Box 
                                sx={{ 
                                  px: 1.5, 
                                  py: 0.5, 
                                  bgcolor: 'rgba(247, 147, 30, 0.1)', 
                                  borderRadius: 1,
                                  display: 'flex',
                                  gap: 0.5,
                                  alignItems: 'center'
                                }}
                              >
                                <Chip
                                  label="Lunch"
                                  size="small"
                                  sx={{
                                    bgcolor: getMealPeriodColor('lunch'),
                                    color: 'white',
                                    fontWeight: 600,
                                    fontSize: '0.65rem',
                                    height: 20
                                  }}
                                />
                                <Typography variant="caption" fontWeight={600} color="warning.main">
                                  ${restaurant.lunch_price}
                                </Typography>
                              </Box>
                            )}
                            {restaurant.dinner_available && restaurant.dinner_price && (
                              <Box 
                                sx={{ 
                                  px: 1.5, 
                                  py: 0.5, 
                                  bgcolor: 'rgba(193, 18, 31, 0.1)', 
                                  borderRadius: 1,
                                  display: 'flex',
                                  gap: 0.5,
                                  alignItems: 'center'
                                }}
                              >
                                <Chip
                                  label="Dinner"
                                  size="small"
                                  sx={{
                                    bgcolor: getMealPeriodColor('dinner'),
                                    color: 'white',
                                    fontWeight: 600,
                                    fontSize: '0.65rem',
                                    height: 20
                                  }}
                                />
                                <Typography variant="caption" fontWeight={600} color="warning.main">
                                  ${restaurant.dinner_price}
                                </Typography>
                              </Box>
                            )}
                          </Box>
                          {restaurant.cuisine && (
                            <Typography variant="caption" color="text.secondary" sx={{ mt: 1, display: 'block' }}>
                              Cuisine: {restaurant.cuisine}
                            </Typography>
                          )}
                        </Box>
                      )
                    )}
                  </Box>
                </Box>
              </Box>
              {restIndex < dateEntry.restaurants.length - 1 && (
                <Divider sx={{ my: 1.5, ml: 1.5 }} />
              )}
            </React.Fragment>
          ))}
          
          {dateIndex < normalizedData.length - 1 && <Divider sx={{ my: 2 }} />}
        </React.Fragment>
      ))}
    </List>
  );
};

export default RenderRestaurantDetails; 