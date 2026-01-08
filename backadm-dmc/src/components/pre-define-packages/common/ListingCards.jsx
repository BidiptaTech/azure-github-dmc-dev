import React, { useState, useEffect, useRef, useCallback } from 'react';
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
  Tooltip,
  IconButton
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
import DeleteIcon from '@mui/icons-material/Delete';
import VisibilityIcon from '@mui/icons-material/Visibility';
import DirectionsBusIcon from '@mui/icons-material/DirectionsBus';
import FlightIcon from '@mui/icons-material/Flight';

// Styled components for blur effect
const BlurOverlay = ({ children, active, hasSearched }) => {
  // Ensure the blur effect is displayed when we have search results
  const shouldShowBlur = active && (hasSearched || window.location.hash.includes('#search-results'));
  
  return (
    <Box sx={{
      position: 'relative',
      width: '100%',
      '&::before': shouldShowBlur ? {
        content: '""',
        position: 'absolute',
        top: 0,
        left: '50%',
        transform: 'translateX(-50%)',
        width: '100vw',
        height: '100%',
        background: 'rgba(255, 255, 255, 0.1)',
        backdropFilter: 'blur(2px)',
        WebkitBackdropFilter: 'blur(2px)',
        zIndex: -1,
      } : {}
    }}>
      {children}
    </Box>
  );
};

const PackageCard = ({ packageData }) => {
  const dispatch = useDispatch();
  const navigate = useNavigate();

  // Parse itinerary data if it exists with defensive checks
  let parsedItinerary = null;
  try {
    if (packageData.itinerary) {
      parsedItinerary = JSON.parse(packageData.itinerary);
    }
  } catch (error) {
    console.error("Failed to parse itinerary data:", error);
  }
  
  const handleViewDetails = () => {
    if (packageData.package_id) {
      dispatch(fetchPackageDetails(packageData.package_id))
        .unwrap()
        .then(() => {
          navigate(`/package-details/${packageData.package_id}`);
        })
        .catch((error) => {
          console.error('Failed to fetch package details:', error);
        });
    }
  };

  // Extract attractions from the parsed itinerary with proper defensive checks
  const attractions = 
    parsedItinerary && 
    parsedItinerary.itinerary && 
    Array.isArray(parsedItinerary.itinerary) 
      ? parsedItinerary.itinerary.flatMap(day => day.attractions || []).slice(0, 3) 
      : [];

  // Extract hotels from the parsed itinerary with proper defensive checks
  const accommodations = 
    parsedItinerary && 
    parsedItinerary.hotels && 
    Array.isArray(parsedItinerary.hotels) 
      ? parsedItinerary.hotels 
      : [];

  // Extract services data from itinerary with proper defensive checks
  const hasArrivalPickup = 
    parsedItinerary && 
    parsedItinerary.itinerary && 
    Array.isArray(parsedItinerary.itinerary)
      ? parsedItinerary.itinerary.some(day => day && day.arrival_pickup === 1)
      : false;
    
  const hasDepartureService = 
    parsedItinerary && 
    parsedItinerary.itinerary && 
    Array.isArray(parsedItinerary.itinerary)
      ? parsedItinerary.itinerary.some(day => day && day.departure_service === 1)
      : false;
    
  const hasTourGuides = 
    parsedItinerary && 
    parsedItinerary.itinerary && 
    Array.isArray(parsedItinerary.itinerary)
      ? parsedItinerary.itinerary.some(day => day && day.guide)
      : false;
    
  const hasTransfers = 
    parsedItinerary && 
    parsedItinerary.itinerary && 
    Array.isArray(parsedItinerary.itinerary)
      ? parsedItinerary.itinerary.some(day => 
          day && 
          day.attractions && 
          Array.isArray(day.attractions) &&
          day.attractions.some(attr => attr && attr.transfer_available === 1)
        )
      : false;

  // Calculate total attractions safely
  const totalAttractions = 
    parsedItinerary && 
    parsedItinerary.itinerary && 
    Array.isArray(parsedItinerary.itinerary)
      ? parsedItinerary.itinerary.reduce((total, day) => 
          total + (day && day.attractions && Array.isArray(day.attractions) ? day.attractions.length : 0), 
        0)
      : 0;

  return (
    <Card sx={{ 
      display: 'flex', 
      flexDirection: 'column', 
      boxShadow: '0 4px 20px rgba(0, 0, 0, 0.1)',
      borderRadius: '12px',
      overflow: 'hidden',
      position: 'relative',
      '&:hover': {
        boxShadow: '0 6px 24px rgba(0, 0, 0, 0.15)',
        transform: 'translateY(-4px)',
        transition: 'all 0.3s ease'
      }
    }}>
      {/* Days Badge */}
      <Box 
        sx={{ 
          position: 'absolute', 
          top: 10, 
          left: 10, 
          bgcolor: '#6a47f2', 
          color: 'white', 
          borderRadius: '4px',
          px: 1.5,
          py: 0.5,
          fontSize: '12px',
          fontWeight: 'bold',
          // zIndex: 2
        }}
      >
        {packageData.duration_days} Days
      </Box>
      
      {/* Active Tag */}
      {packageData.status === 'active' && (
        <Box 
          sx={{ 
            position: 'absolute', 
            top: 10, 
            right: 10, 
            bgcolor: '#4CAF50', 
            color: 'white', 
            borderRadius: '4px',
            px: 1.5,
            py: 0.5,
            fontSize: '12px',
            fontWeight: 'bold',
            zIndex: 2
          }}
        >
          Active
        </Box>
      )}
      
      <CardMedia
        component="img"
        sx={{ 
          width: '100%',
          height: 160,
          objectFit: 'cover'
        }}
        image={packageData.main_image}
        alt={packageData.title}
      />

      <CardContent sx={{ flex: '1 0 auto', p: 2, pb: 1 }}>
        {/* Title and Location Row */}
        <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
          <Box>
            {/* Title */}
            <Typography component="div" variant="subtitle1" fontWeight="bold" sx={{ mb: 0.5 }}>
              {packageData.title}
            </Typography>
            
            {/* Location */}
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <LocationOnIcon sx={{ color: '#666', fontSize: 16, mr: 0.5 }} />
              <Typography variant="body2" color="text.secondary" fontSize="0.8rem">
                {packageData.country || 'Singapore'} - {packageData.city || 'Singapore'}
              </Typography>
            </Box>
          </Box>
          
          {/* Price */}
          <Box sx={{ textAlign: 'right' }}>
            <Typography variant="caption" color="text.secondary">From SGD</Typography>
            <Typography variant="subtitle1" color="primary" fontWeight="bold">
              ${packageData.price_adult || '343.00'}
            </Typography>
          </Box>
        </Box>
        
        <Divider sx={{ my: 1 }} />
        
        {/* Compact sections layout */}
        <Box sx={{ display: 'flex', gap: 1, mb: 1 }}>
          {/* Top Attractions */}
          <Box sx={{ flex: 1 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.5 }}>
              <Box sx={{ 
                display: 'inline-flex', 
                alignItems: 'center', 
                justifyContent: 'center',
                bgcolor: '#f0f7ff', 
                borderRadius: '50%', 
                width: 20, 
                height: 20, 
                mr: 0.5 
              }}>
                <AttractionsIcon sx={{ fontSize: 12, color: '#1976d2' }} />
              </Box>
              <Typography variant="caption" fontWeight="medium">Top Attractions</Typography>
            </Box>
            
            <Box sx={{ display: 'flex', gap: 0.5 }}>
              {attractions.length > 0 ? (
                <>
                  {attractions.slice(0, 2).map((attraction, index) => (
                    <Box 
                      key={attraction.attraction_id || index}
                      component="img"
                      src={attraction.image || '/img/activities/1.png'}
                      sx={{ 
                        width: 36, 
                        height: 36, 
                        borderRadius: '6px',
                        objectFit: 'cover'
                      }}
                      alt={attraction.name || 'Attraction'}
                    />
                  ))}
                  {attractions.length > 2 && (
                    <Box 
                      sx={{ 
                        width: 36, 
                        height: 36, 
                        borderRadius: '6px',
                        bgcolor: '#f5f5f5',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        color: '#1976d2',
                        fontSize: '0.7rem',
                        fontWeight: 'bold'
                      }}
                    >
                      +{totalAttractions - 2}
                    </Box>
                  )}
                </>
              ) : (
                <Box 
                  sx={{ 
                    display: 'flex',
                    alignItems: 'center',
                    height: 36,
                    fontSize: '0.65rem',
                    color: 'text.secondary',
                    fontStyle: 'italic',
                    border: '1px solid #e0e0e0',
                    borderRadius: '6px',
                    px: 1.5,
                    backgroundColor: '#f5f5f5',
                  }}
                >
                  No attractions available
                </Box>
              )}
            </Box>
          </Box>
          
          {/* Accommodations */}
          <Box sx={{ flex: 1 }}>
            <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.5 }}>
              <Box sx={{ 
                display: 'inline-flex', 
                alignItems: 'center', 
                justifyContent: 'center',
                bgcolor: '#f0f7ff', 
                borderRadius: '50%', 
                width: 20, 
                height: 20, 
                mr: 0.5 
              }}>
                <HotelIcon sx={{ fontSize: 12, color: '#1976d2' }} />
              </Box>
              <Typography variant="caption" fontWeight="medium">Accommodations</Typography>
            </Box>
            
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              {accommodations.length > 0 ? (
                <>
                  <Avatar 
                    src={accommodations[0].main_image} 
                    sx={{ 
                      width: 24, 
                      height: 24,
                      mr: 0.5
                    }}
                    alt={accommodations[0].name}
                  />
                  <Typography variant="caption" sx={{ fontWeight: 500 }} noWrap>
                    {accommodations[0].name}
                  </Typography>
                </>
              ) : (
                <Typography variant="caption" sx={{ fontWeight: 500 }} noWrap>
                  No accommodations listed
                </Typography>
              )}
            </Box>
          </Box>
        </Box>
        
        {/* Services */}
        <Box sx={{ mt: 1 }}>
          <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.5 }}>
            <Box sx={{ 
              display: 'inline-flex', 
              alignItems: 'center', 
              justifyContent: 'center',
              bgcolor: '#f0f7ff', 
              borderRadius: '50%', 
              width: 20, 
              height: 20, 
              mr: 0.5 
            }}>
              <LuggageIcon sx={{ fontSize: 12, color: '#1976d2' }} />
            </Box>
            <Typography variant="caption" fontWeight="medium">Services</Typography>
          </Box>
          
          <Box sx={{ display: 'flex', flexWrap: 'nowrap', gap: 0.5, overflow: 'hidden' }}>
            {/* Combine Airport Pickup and Dropoff into a single chip */}
            {(hasArrivalPickup || hasDepartureService) && (
              <Chip 
                icon={<FlightIcon sx={{ fontSize: '0.7rem !important' }} />} 
                label={
                  hasArrivalPickup && hasDepartureService
                    ? "Airport Pickup / Dropoff"
                    : hasArrivalPickup
                      ? "Airport Pickup"
                      : "Airport Dropoff"
                }
                size="small" 
                sx={{ height: 20, fontSize: '0.65rem', whiteSpace: 'nowrap' }} 
              />
            )}
            
            {/* Tour Guide chip */}
            {hasTourGuides && (
              <Chip 
                icon={<ExploreIcon sx={{ fontSize: '0.7rem !important' }} />} 
                label="Tour Guide" 
                size="small" 
                sx={{ height: 20, fontSize: '0.65rem', whiteSpace: 'nowrap' }} 
              />
            )}
            
            {/* Transfers chip */}
            {hasTransfers && (
              <Chip 
                icon={<DirectionsBusIcon sx={{ fontSize: '0.7rem !important' }} />} 
                label="Transfers" 
                size="small" 
                sx={{ height: 20, fontSize: '0.65rem', whiteSpace: 'nowrap' }} 
              />
            )}
            
            {/* No services message */}
            {!hasArrivalPickup && !hasDepartureService && !hasTourGuides && !hasTransfers && (
              <Typography 
                variant="caption" 
                color="text.secondary" 
                sx={{ 
                  fontStyle: 'italic',
                  display: 'flex',
                  alignItems: 'center',
                  height: 20,
                  fontSize: '0.65rem',
                  border: '1px solid #e0e0e0',
                  borderRadius: '16px',
                  px: 1.5,
                  backgroundColor: '#f5f5f5',
                }}
              >
                No services information available
              </Typography>
            )}
          </Box>
        </Box>
      </CardContent>
      
      <Box sx={{ 
        display: 'flex', 
        justifyContent: 'space-between',
        p: 1.5, 
        borderTop: '1px solid #eee'
      }}>
        <Button 
          variant="outlined" 
          startIcon={<VisibilityIcon sx={{ fontSize: 16 }} />}
          onClick={handleViewDetails}
          size="small"
          sx={{ flex: 1, mr: 1, py: 0.5, fontSize: '0.75rem' }}
        >
          Details
        </Button>
      
      </Box>
    </Card>
  );
};

const NoResults = ({ hasSearched }) => (
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

const LoadMoreIndicator = () => (
  <Box sx={{ 
    display: 'flex', 
    justifyContent: 'center', 
    alignItems: 'center', 
    py: 4,
    width: '100%'
  }}>
    <CircularProgress size={32} thickness={4} sx={{ mr: 2, color: 'primary.main' }} />
    <Typography variant="body1" color="text.secondary" fontWeight={500}>
      Loading more packages...
    </Typography>
  </Box>
);

const ListingCards = ({ hasSearched = false }) => {
  const { packages, loading, error, searchParams } = useSelector(state => state.prePackages);
  const dispatch = useDispatch();
  const [visiblePackages, setVisiblePackages] = useState([]);
  const [pageNumber, setPageNumber] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const loaderRef = useRef(null); // Reference for the loader element
  const itemsPerPage = 15;

  // Reset everything when packages change
  useEffect(() => {
    if (packages && packages.length > 0) {
      setPageNumber(1);
      setVisiblePackages(packages.slice(0, itemsPerPage));
      setHasMore(packages.length > itemsPerPage);
      setLoadingMore(false);
    } else {
      setVisiblePackages([]);
      setHasMore(false);
    }
  }, [packages]);

  // Setup intersection observer for infinite scrolling
  useEffect(() => {
    const options = {
      root: null, // viewport
      rootMargin: '0px 0px 200px 0px', // Start loading before user reaches the end (200px margin)
      threshold: 0.1 // Trigger when 10% of the element is visible
    };

    const observer = new IntersectionObserver((entries) => {
      const [entry] = entries;
      
      if (entry?.isIntersecting && hasMore && !loadingMore && !loading) {
        loadMorePackages();
      }
    }, options);

    // Observe loader element
    if (loaderRef.current) {
      observer.observe(loaderRef.current);
    }

    // Cleanup
    return () => {
      if (loaderRef.current) {
        observer.unobserve(loaderRef.current);
      }
    };
  }, [hasMore, loadingMore, loading, packages]);

  // Function to load more packages
  const loadMorePackages = () => {
    if (!hasMore || loadingMore || !packages || loading) return;
    
    setLoadingMore(true);
    
    // Small delay to prevent multiple loads
    setTimeout(() => {
      try {
        const nextPage = pageNumber + 1;
        const startIndex = 0;
        const endIndex = nextPage * itemsPerPage;
        
        if (packages.length > (pageNumber * itemsPerPage)) {
          const nextBatch = packages.slice(startIndex, endIndex);
          setVisiblePackages(nextBatch);
          setPageNumber(nextPage);
          setHasMore(packages.length > endIndex);
        } else {
          setHasMore(false);
        }
      } catch (error) {
        console.error("Error loading more packages:", error);
      } finally {
        setLoadingMore(false);
      }
    }, 800);
  };

  // Show empty space if no search has been performed yet
  if (!searchParams) {
    return (
      <Box sx={{ mt: 4, minHeight: '570px' }}>
        {/* Empty space - content will appear here after search */}
      </Box>
    );
  }
  
  if (loading && !loadingMore) {
    return (
      <BlurOverlay active={true} hasSearched={hasSearched}>
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
      </BlurOverlay>
    );
  }
  
  if (error) {
    return (
      <BlurOverlay active={true} hasSearched={hasSearched}>
        <Box sx={{ mt: 4, p: 3, bgcolor: '#FFF4F4', borderRadius: 2 }}>
          <Typography color="error" variant="h6" gutterBottom>
            Error loading packages
          </Typography>
          <Typography color="error.light">{error}</Typography>
        </Box>
      </BlurOverlay>
    );
  }
  
  if (!packages || packages.length === 0) {
    return (
      <BlurOverlay active={true} hasSearched={hasSearched}>
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
        <NoResults hasSearched={hasSearched} />
      </Box>
      </BlurOverlay>
    );
  }

  return (
    <BlurOverlay active={true} hasSearched={hasSearched}>
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
        
        <Box sx={{ position: 'relative' }}>
          <Grid container spacing={3}>
            {visiblePackages.map((packageItem, index) => (
              <Grid item xs={12} sm={6} md={4} key={packageItem.package_id || index}>
                <PackageCard packageData={packageItem} />
              </Grid>
            ))}
          </Grid>
          
          {/* Loader reference element - this is what triggers loading more */}
          <Box ref={loaderRef} sx={{ width: '100%', height: '10px', mt: 1 }} />
          
          {/* Loading indicator at bottom */}
          {loadingMore && <LoadMoreIndicator />}
          
          {/* No more packages message */}
          {!hasMore && visiblePackages.length > 0 && packages.length > itemsPerPage && (
            <Box sx={{ 
              textAlign: 'center', 
              mt: 4, 
              mb: 2, 
              py: 2,
              borderTop: '1px dashed #e0e0e0',
              borderRadius: 1
            }}>
              <Typography variant="body1" color="text.secondary" fontWeight={500}>
                You've reached the end of the list
              </Typography>
            </Box>
          )}
        </Box>
      </Box>
    </BlurOverlay>
  );
};

export default ListingCards;
