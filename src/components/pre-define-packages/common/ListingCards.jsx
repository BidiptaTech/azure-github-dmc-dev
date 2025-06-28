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
  ListItemText,
  Paper,
  Avatar,
  CircularProgress,
  LinearProgress,
  Tooltip
} from '@mui/material';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import CalendarMonthIcon from '@mui/icons-material/CalendarMonth';
import HotelIcon from '@mui/icons-material/Hotel';
import AttractionsIcon from '@mui/icons-material/Attractions';
import PeopleIcon from '@mui/icons-material/People';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import LuggageIcon from '@mui/icons-material/Luggage';
import ExploreIcon from '@mui/icons-material/Explore';
import TravelExploreIcon from '@mui/icons-material/TravelExplore';
import SearchOffIcon from '@mui/icons-material/SearchOff';
import FilterAltOffIcon from '@mui/icons-material/FilterAltOff';
import TuneIcon from '@mui/icons-material/Tune';
import RefreshIcon from '@mui/icons-material/Refresh';
import SearchIcon from '@mui/icons-material/Search';
import HourglassTopIcon from '@mui/icons-material/HourglassTop';

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
            <Box sx={{ display: 'flex', alignItems: 'center', ml: 1 }}>
              <Typography variant="body1" color="primary" fontWeight="bold" sx={{ mr: 0.5 }}>
                SGD {packageData.price_adult}
              </Typography>
              <Typography variant="caption" sx={{ fontSize: '0.7rem', whiteSpace: 'nowrap' }}>/ adult</Typography>
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
          
          <Box sx={{ display: 'flex', gap: 1, mb: 1.5 }}>
            <Tooltip 
              title="Begin your adventure on this date" 
              arrow 
              placement="top"
            >
              <Chip 
                icon={<CalendarMonthIcon sx={{ fontSize: '0.85rem' }} />} 
                label={new Date(packageData.start_date).toLocaleDateString('en-US', { weekday: 'short', day: '2-digit', month: 'short', year: '2-digit' })}
                size="small" 
                color="success" 
                variant="outlined"
                sx={{ 
                  height: '22px',
                  '& .MuiChip-label': { 
                    px: 0.75, 
                    fontSize: '0.80rem'
                  }
                }} 
              />
            </Tooltip>
            <Tooltip 
              title="Last day to enjoy this exclusive offer" 
              arrow 
              placement="top"
            >
              <Chip 
                icon={<CalendarMonthIcon sx={{ fontSize: '0.85rem' }} />} 
                label={new Date(packageData.expire_date).toLocaleDateString('en-US', { weekday: 'short', day: '2-digit', month: 'short', year: '2-digit' })}
                size="small" 
                color="error" 
                variant="outlined" 
                sx={{ 
                  height: '22px',
                  '& .MuiChip-label': { 
                    px: 0.75, 
                    fontSize: '0.80rem'
                  }
                }}
              />
            </Tooltip>
          </Box>
          
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
  <Paper
    elevation={2}
    sx={{
      p: 5,
      borderRadius: 3,
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      gap: 2,
      maxWidth: '600px',
      mx: 'auto',
      backgroundColor: '#f8fafc',
      border: '1px dashed rgba(25, 118, 210, 0.3)',
    }}
  >
    <Avatar
      sx={{
        width: 80,
        height: 80,
        backgroundColor: 'rgba(25, 118, 210, 0.08)',
        mb: 1
      }}
    >
      <SearchOffIcon 
        sx={{ 
          fontSize: 40, 
          color: 'primary.main' 
        }} 
      />
    </Avatar>
    
    <Typography 
      variant="h5" 
      sx={{ 
        fontWeight: 600,
        color: 'primary.main',
        textAlign: 'center'
      }}
    >
      No packages found
    </Typography>
    
    <Typography 
      variant="body1" 
      color="text.secondary" 
      sx={{ 
        textAlign: 'center',
        maxWidth: '450px',
        mb: 1
      }}
    >
      We couldn't find any packages matching your search criteria. Please try adjusting your filters.
    </Typography>
    
    <Box sx={{ display: 'flex', mt: 1, gap: 2 }}>
      <Button 
        variant="outlined" 
        startIcon={<TuneIcon />}
        size="medium"
      >
        Adjust Filters
      </Button>
      <Button 
        variant="contained" 
        color="primary"
        startIcon={<RefreshIcon />}
        size="medium"
      >
        Reset Search
      </Button>
    </Box>
  </Paper>
);

const LoadingSkeleton = () => (
  <Grid item xs={12} sm={6} md={4}>
    <Card sx={{ 
      height: '100%', 
      display: 'flex', 
      flexDirection: 'column',
      borderRadius: '12px',
      overflow: 'hidden',
      boxShadow: '0 4px 20px rgba(0, 0, 0, 0.08)',
      position: 'relative'
    }}>
      <Box sx={{ 
        position: 'absolute', 
        top: 0, 
        left: 0, 
        right: 0, 
        height: '2px', 
        zIndex: 1 
      }}>
        <LinearProgress color="primary" />
      </Box>
      <Skeleton variant="rectangular" sx={{ width: '100%', height: 200 }} />
      <Box sx={{ p: 3 }}>
        <Skeleton variant="text" width="60%" height={30} />
        <Box sx={{ display: 'flex', justifyContent: 'space-between', my: 2 }}>
          <Skeleton variant="text" width="40%" />
          <Skeleton variant="text" width="20%" />
        </Box>
        <Skeleton variant="text" height={20} sx={{ my: 1 }} />
        <Skeleton variant="text" height={20} sx={{ my: 1 }} />
        <Skeleton variant="text" height={20} sx={{ my: 1 }} />
        <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mt: 3 }}>
          <Skeleton variant="circular" width={40} height={40} />
          <Skeleton variant="rectangular" width={120} height={40} sx={{ borderRadius: '20px' }} />
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
        <Paper
          elevation={0}
          sx={{
            p: 2.5,
            mb: 3,
            borderRadius: 2,
            background: 'linear-gradient(135deg, #f5f7fa 0%, #eef2f7 100%)',
            border: '1px solid #e0e7ee',
            display: 'flex',
            flexDirection: 'column',
            gap: 1
          }}
        >
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <Box sx={{ position: 'relative', mr: 2 }}>
              <SearchIcon
                sx={{
                  fontSize: 28,
                  color: 'primary.main'
                }}
              />
              <CircularProgress
                size={36}
                thickness={2}
                sx={{
                  position: 'absolute',
                  top: -4,
                  left: -4,
                  color: 'primary.main',
                  opacity: 0.8
                }}
              />
            </Box>
            <Typography
              variant="h5"
              sx={{
                fontWeight: 600,
                background: 'linear-gradient(45deg, #2196F3 30%, #21CBF3 90%)',
                WebkitBackgroundClip: 'text',
                WebkitTextFillColor: 'transparent'
              }}
            >
              Searching for packages...
            </Typography>
          </Box>
          <Box sx={{ display: 'flex', alignItems: 'center', ml: 0.5 }}>
            <HourglassTopIcon
              sx={{
                fontSize: 16,
                mr: 1,
                color: 'text.secondary'
              }}
            />
            <Typography
              variant="body1"
              sx={{
                fontWeight: 500,
                color: 'text.secondary'
              }}
            >
              Finding the best travel options for you
            </Typography>
          </Box>
          <LinearProgress
            sx={{
              mt: 1.5,
              mb: 0.5,
              height: 4,
              borderRadius: 2,
              backgroundColor: 'rgba(25, 118, 210, 0.1)'
            }}
          />
        </Paper>
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
        <Paper 
        elevation={0} 
        sx={{ 
          p: 2.5, 
          mb: 3, 
          borderRadius: 2, 
          background: 'linear-gradient(135deg, #f5f7fa 0%, #eef2f7 100%)',
          border: '1px solid #e0e7ee'
        }}
      >
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
          <TravelExploreIcon 
            sx={{ 
              fontSize: 32, 
              mr: 1.5, 
              color: 'primary.main' 
            }} 
          />
          <Typography 
            variant="h5" 
            sx={{ 
              fontWeight: 600,
              background: 'linear-gradient(45deg, #2196F3 30%, #21CBF3 90%)',
              WebkitBackgroundClip: 'text',
              WebkitTextFillColor: 'transparent'
            }}
          >
            Package Search Results
          </Typography>
        </Box>
        
      </Paper>
        <NoResults />
      </Box>
    );
  }

  return (
    <Box sx={{ mt: 4 }}>
      <Paper 
        elevation={0} 
        sx={{ 
          p: 2.5, 
          mb: 3, 
          borderRadius: 2, 
          background: 'linear-gradient(135deg, #f5f7fa 0%, #eef2f7 100%)',
          border: '1px solid #e0e7ee'
        }}
      >
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
          <TravelExploreIcon 
            sx={{ 
              fontSize: 32, 
              mr: 1.5, 
              color: 'primary.main' 
            }} 
          />
          <Typography 
            variant="h5" 
            sx={{ 
              fontWeight: 600,
              background: 'linear-gradient(45deg, #2196F3 30%, #21CBF3 90%)',
              WebkitBackgroundClip: 'text',
              WebkitTextFillColor: 'transparent'
            }}
          >
            Package Search Results
          </Typography>
        </Box>
        <Box sx={{ display: 'flex', alignItems: 'center', ml: 0.5 }}>
          <LuggageIcon 
            sx={{ 
              fontSize: 18, 
              mr: 1, 
              color: 'text.secondary' 
            }} 
          />
          <Typography 
            variant="subtitle1" 
            sx={{ 
              fontWeight: 500, 
              color: 'text.secondary',
              display: 'flex',
              alignItems: 'center'
            }}
          >
            Found <Box component="span" sx={{ mx: 0.5, fontWeight: 600, color: 'primary.main' }}>{packages.length}</Box> 
            package{packages.length !== 1 ? 's' : ''} for you
          </Typography>
        </Box>
      </Paper>
      
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
