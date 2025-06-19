import React from 'react';
import { useSelector } from 'react-redux';
import { 
  Box, 
  Card, 
  CardContent, 
  CardMedia, 
  Typography, 
  Grid, 
  Chip, 
  Divider, 
  Stack, 
  Rating, 
  Skeleton,
  Button,
  List,
  ListItem,
  ListItemIcon,
  ListItemText
} from '@mui/material';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import HotelIcon from '@mui/icons-material/Hotel';
import AttractionsIcon from '@mui/icons-material/Attractions';
import PeopleIcon from '@mui/icons-material/People';
import RestaurantIcon from '@mui/icons-material/Restaurant';

const PackageCard = ({ packageData }) => {
  return (
    <Card sx={{ 
      display: 'flex', 
      flexDirection: { xs: 'column', md: 'row' }, 
      mb: 3,
      boxShadow: '0 4px 20px rgba(0, 0, 0, 0.1)',
      borderRadius: '12px',
      overflow: 'hidden',
      '&:hover': {
        boxShadow: '0 6px 24px rgba(0, 0, 0, 0.15)',
        transform: 'translateY(-4px)',
        transition: 'all 0.3s ease'
      }
    }}>
      <CardMedia
        component="img"
        sx={{ 
          width: { xs: '100%', md: 300 },
          height: { xs: 200, md: 'auto' },
          objectFit: 'cover'
        }}
        image={packageData.main_image}
        alt={packageData.title}
      />
      <Box sx={{ display: 'flex', flexDirection: 'column', width: '100%' }}>
        <CardContent sx={{ flex: '1 0 auto', p: 3 }}>
          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', mb: 1 }}>
            <Typography component="div" variant="h5" fontWeight="bold">
              {packageData.title}
            </Typography>
            <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end' }}>
              <Typography variant="h5" color="primary" fontWeight="bold">
                ${packageData.price_adult}
              </Typography>
              <Typography variant="caption">per adult</Typography>
            </Box>
          </Box>
          
          <Stack direction="row" spacing={1} sx={{ mb: 2 }}>
            <Chip 
              icon={<LocationOnIcon />} 
              label={packageData.city || packageData.destination} 
              size="small" 
              color="primary" 
              variant="outlined" 
            />
            <Chip 
              icon={<CalendarMonthIcon />} 
              label={`${packageData.duration_days} Days`} 
              size="small" 
              variant="outlined" 
            />
            <Chip 
              icon={<PeopleIcon />} 
              label={`Max ${packageData.max_pax} pax`} 
              size="small" 
              variant="outlined" 
            />
          </Stack>
          
          <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
            <Rating value={parseFloat(packageData.rating) || 0} precision={0.5} readOnly size="small" />
            <Typography variant="body2" color="text.secondary" sx={{ ml: 1 }}>
              ({packageData.reviews_count} reviews)
            </Typography>
          </Box>
          
          <Typography variant="body2" color="text.secondary" paragraph sx={{ mb: 2 }}>
            {packageData.description.length > 150 
              ? `${packageData.description.substring(0, 150)}...` 
              : packageData.description}
          </Typography>
          
          <Grid container spacing={2}>
            {packageData.selected_hotels && packageData.selected_hotels.length > 0 && (
              <Grid item xs={12} md={6}>
                <Typography variant="subtitle2" fontWeight="bold">
                  <HotelIcon fontSize="small" sx={{ verticalAlign: 'middle', mr: 1 }} />
                  Accommodations:
                </Typography>
                <List dense disablePadding>
                  {packageData.selected_hotels.slice(0, 3).map((hotel, index) => (
                    <ListItem key={index} disablePadding>
                      <ListItemText primary={hotel.name} />
                    </ListItem>
                  ))}
                </List>
              </Grid>
            )}
            
            {packageData.selected_attractions && packageData.selected_attractions.length > 0 && (
              <Grid item xs={12} md={6}>
                <Typography variant="subtitle2" fontWeight="bold">
                  <AttractionsIcon fontSize="small" sx={{ verticalAlign: 'middle', mr: 1 }} />
                  Attractions:
                </Typography>
                <List dense disablePadding>
                  {packageData.selected_attractions.slice(0, 3).map((attraction, index) => (
                    <ListItem key={index} disablePadding>
                      <ListItemText primary={attraction.name} />
                    </ListItem>
                  ))}
                </List>
              </Grid>
            )}
            
            {packageData.selected_restaurants && packageData.selected_restaurants?.length > 0 && (
              <Grid item xs={12} md={6}>
                <Typography variant="subtitle2" fontWeight="bold">
                  <RestaurantIcon fontSize="small" sx={{ verticalAlign: 'middle', mr: 1 }} />
                  Dining:
                </Typography>
                <List dense disablePadding>
                  {packageData.selected_restaurants.slice(0, 2).map((restaurant, index) => (
                    <ListItem key={index} disablePadding>
                      <ListItemText primary={restaurant.name} />
                    </ListItem>
                  ))}
                </List>
              </Grid>
            )}
          </Grid>
        </CardContent>
        <Box sx={{ display: 'flex', alignItems: 'center', pl: 3, pb: 3, pr: 3 }}>
          <Chip label={packageData.category} size="small" sx={{ mr: 1 }} />
          <Box sx={{ flexGrow: 1 }} />
          <Button variant="contained" color="primary">
            View Details
          </Button>
        </Box>
      </Box>
    </Card>
  );
};

const NoResults = () => (
  <Box sx={{ textAlign: 'center', py: 5 }}>
    <Typography variant="h6">No packages found matching your criteria.</Typography>
    <Typography variant="body1" color="text.secondary">
      Please try different search parameters.
    </Typography>
  </Box>
);

const LoadingSkeleton = () => (
  <Card sx={{ display: 'flex', flexDirection: { xs: 'column', md: 'row' }, mb: 3, height: 350 }}>
    <Skeleton variant="rectangular" sx={{ width: { xs: '100%', md: 300 }, height: { xs: 200, md: '100%' } }} />
    <Box sx={{ display: 'flex', flexDirection: 'column', width: '100%', p: 3 }}>
      <Skeleton variant="text" width="60%" height={40} />
      <Box sx={{ display: 'flex', justifyContent: 'space-between', my: 2 }}>
        <Skeleton variant="text" width="40%" />
        <Skeleton variant="text" width="20%" />
      </Box>
      <Skeleton variant="text" count={3} height={20} sx={{ my: 1 }} />
      <Grid container spacing={2} sx={{ mt: 2 }}>
        <Grid item xs={6}>
          <Skeleton variant="text" width="90%" />
          <Skeleton variant="text" width="70%" />
        </Grid>
        <Grid item xs={6}>
          <Skeleton variant="text" width="90%" />
          <Skeleton variant="text" width="70%" />
        </Grid>
      </Grid>
      <Box sx={{ display: 'flex', justifyContent: 'flex-end', mt: 3 }}>
        <Skeleton variant="rectangular" width={120} height={40} />
      </Box>
    </Box>
  </Card>
);

const ListingCards = () => {
  const { packages, loading, error } = useSelector(state => state.prePackages);
  
  if (loading) {
    return (
      <Box sx={{ mt: 4 }}>
        <Typography variant="h5" gutterBottom>
          Searching for packages...
        </Typography>
        {[1, 2, 3].map(item => (
          <LoadingSkeleton key={item} />
        ))}
      </Box>
    );
  }
  
  if (error) {
    return (
      <Box sx={{ mt: 4, p: 3, bgcolor: '#FFF4F4', borderRadius: 2 }}>
        <Typography color="error" variant="h6" gutterBottom>
          Error loading packages
        </Typography>
        <Typography color="error.light">{error}</Typography>
      </Box>
    );
  }
  
  if (!packages || packages.length === 0) {
    return (
      <Box sx={{ mt: 4 }}>
        <Typography variant="h5" gutterBottom>
          Package Search Results
        </Typography>
        <NoResults />
      </Box>
    );
  }

  return (
    <Box sx={{ mt: 4 }}>
      <Typography variant="h5" gutterBottom>
        Package Search Results
      </Typography>
      <Typography variant="subtitle1" sx={{ mb: 3 }}>
        Found {packages.length} package{packages.length !== 1 ? 's' : ''}
      </Typography>
      
      {Array.isArray(packages) ? (
        packages.map(packageItem => (
          <PackageCard key={packageItem.id} packageData={packageItem} />
        ))
      ) : (
        // Handle if packages is an object rather than array
        <PackageCard packageData={packages} />
      )}
    </Box>
  );
};

export default ListingCards;
