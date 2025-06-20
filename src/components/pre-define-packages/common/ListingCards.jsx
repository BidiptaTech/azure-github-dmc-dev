import React from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { fetchPackageDetails } from '../../../slice/tour-packages/prePackagesSlice';
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
  const dispatch = useDispatch();
  const navigate = useNavigate();

  const handleViewDetails = () => {
    if (packageData.package_id) {
      // Dispatch action to fetch package details
      dispatch(fetchPackageDetails(packageData.package_id))
        .unwrap()
        .then(() => {
          // Navigate to the package details page
          navigate(`/package-details/${packageData.package_id}`);
        })
        .catch((error) => {
          console.error('Failed to fetch package details:', error);
          // You could add error handling here, like showing a notification
        });
    }
  };

  return (
    <Card sx={{ 
      height: '100%',
      display: 'flex', 
      flexDirection: 'column', 
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
          width: '100%',
          height: 200,
          objectFit: 'cover'
        }}
        image={packageData.main_image}
        alt={packageData.title}
      />
      <Box sx={{ display: 'flex', flexDirection: 'column', flex: 1 }}>
        <CardContent sx={{ flex: '1 0 auto', p: 3 }}>
          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', mb: 1 }}>
            <Typography component="div" variant="h6" fontWeight="bold">
              {packageData.title}
            </Typography>
            <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end' }}>
              <Typography variant="h6" color="primary" fontWeight="bold">
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
          
          {/* <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
            <Rating value={parseFloat(packageData.rating) || 0} precision={0.5} readOnly size="small" />
            <Typography variant="body2" color="text.secondary" sx={{ ml: 1 }}>
              ({packageData.reviews_count} reviews)
            </Typography>
          </Box> */}
          
          <Typography variant="body2" color="text.secondary" paragraph sx={{ mb: 2 }}>
            {packageData.description.length > 100 
              ? `${packageData.description.substring(0, 100)}...` 
              : packageData.description}
          </Typography>
        </CardContent>
        <Box sx={{ display: 'flex', alignItems: 'center', pl: 3, pb: 3, pr: 3, mt: 'auto' }}>
          <Chip label={packageData.category} size="small" sx={{ mr: 1 }} />
          <Box sx={{ flexGrow: 1 }} />
          <Button 
            variant="contained" 
            color="primary" 
            size="small"
            onClick={handleViewDetails}
          >
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
  <Grid item xs={12} sm={6} md={4}>
    <Card sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
      <Skeleton variant="rectangular" sx={{ width: '100%', height: 200 }} />
      <Box sx={{ p: 3 }}>
        <Skeleton variant="text" width="60%" height={30} />
        <Box sx={{ display: 'flex', justifyContent: 'space-between', my: 2 }}>
          <Skeleton variant="text" width="40%" />
          <Skeleton variant="text" width="20%" />
        </Box>
        <Skeleton variant="text" count={3} height={20} sx={{ my: 1 }} />
        <Box sx={{ display: 'flex', justifyContent: 'flex-end', mt: 3 }}>
          <Skeleton variant="rectangular" width={120} height={40} />
        </Box>
      </Box>
    </Card>
  </Grid>
);

const ListingCards = () => {
  const { packages, loading, error } = useSelector(state => state.prePackages);
  
  if (loading) {
    return (
      <Box sx={{ mt: 4 }}>
        <Typography variant="h5" gutterBottom>
          Searching for packages...
        </Typography>
        <Grid container spacing={3}>
          {[1, 2, 3].map(item => (
            <LoadingSkeleton key={item} />
          ))}
        </Grid>
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
      
      <Grid container spacing={3}>
        {Array.isArray(packages) ? (
          packages.map(packageItem => (
            <Grid item xs={12} sm={6} md={4} key={packageItem.package_id}>
              <PackageCard packageData={packageItem} />
            </Grid>
          ))
        ) : (
          // Handle if packages is an object rather than array
          <Grid item xs={12} sm={6} md={4}>
            <PackageCard packageData={packages} />
          </Grid>
        )}
      </Grid>
    </Box>
  );
};

export default ListingCards;
