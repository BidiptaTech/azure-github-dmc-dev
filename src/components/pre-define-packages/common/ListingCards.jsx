import React, { useState, useEffect, useRef, useCallback } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { fetchPackageDetails, fetchPackages, setSearchParams } from '../../../slice/tour-packages/prePackagesSlice';
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
  IconButton,
  TextField,
  Badge,
} from '@mui/material';
import { styled } from '@mui/material/styles';
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
import PersonIcon from '@mui/icons-material/Person';
import GroupIcon from '@mui/icons-material/Group';
import BusinessIcon from '@mui/icons-material/Business';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import InfoIcon from '@mui/icons-material/Info';
import TravelExploreIconMui from '@mui/icons-material/TravelExplore';

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

// DMC Selection Panel Components
const DMCSelectionPanel = styled(Paper)(({ theme }) => ({
  padding: theme.spacing(3),
  height: 'fit-content',
  maxHeight: '80vh',
  overflowY: 'auto',
  position: 'sticky',
  top: theme.spacing(2),
  background: 'linear-gradient(135deg, #ffffff 0%, #f8fafc 100%)',
  border: '1px solid rgba(102, 126, 234, 0.1)',
  borderRadius: theme.spacing(2),
  boxShadow: '0 8px 32px rgba(102, 126, 234, 0.1)',
  '&::-webkit-scrollbar': {
    width: '6px',
  },
  '&::-webkit-scrollbar-track': {
    background: 'rgba(0,0,0,0.05)',
    borderRadius: '3px',
  },
  '&::-webkit-scrollbar-thumb': {
    background: 'rgba(102, 126, 234, 0.3)',
    borderRadius: '3px',
    '&:hover': {
      background: 'rgba(102, 126, 234, 0.5)',
    },
  },
}));

const DMCCard = styled(Card)(({ theme, selected }) => ({
  marginBottom: theme.spacing(1.5),
  border: selected ? '2px solid #667eea' : '1px solid #e0e3e8',
  backgroundColor: selected ? 'rgba(102, 126, 234, 0.05)' : 'white',
  cursor: 'pointer',
  transition: 'all 0.3s ease',
  borderRadius: theme.spacing(1.5),
  '&:hover': {
    boxShadow: '0 4px 12px rgba(102, 126, 234, 0.15)',
    transform: 'translateY(-2px)',
    border: '2px solid #667eea',
  },
}));

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
      height: '100%',
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
          zIndex: 1
        }}
      >
        {packageData.duration_days} Days
      </Box>
      

      
      <Box sx={{ position: 'relative' }}>
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
        
        {/* Overlay for title and location */}
        <Box sx={{
          position: 'absolute',
          bottom: 0,
          left: 0,
          right: 0,
          background: 'linear-gradient(transparent, rgba(0,0,0,0.7))',
          color: 'white',
          p: 1.5,
          pb: 1
        }}>
          {/* Title */}
          <Typography 
            component="div" 
            variant="subtitle1" 
            fontWeight="bold" 
            sx={{ 
              mb: 0.5,
              color: 'white',
              textShadow: '0 2px 4px rgba(0,0,0,0.9)',
              fontSize: '1rem',
              lineHeight: 1.3,
              overflow: 'hidden',
              textOverflow: 'ellipsis',
              display: '-webkit-box',
              WebkitLineClamp: 2,
              WebkitBoxOrient: 'vertical'
            }}
          >
            {packageData.title}
          </Typography>
          
          {/* Location */}
          <Box sx={{ display: 'flex', alignItems: 'center' }}>
            <LocationOnIcon sx={{ color: 'white', fontSize: 16, mr: 0.5 }} />
            <Typography 
              variant="body2" 
              sx={{ 
                color: 'white',
                fontSize: '0.8rem',
                textShadow: '0 2px 4px rgba(0,0,0,0.9)',
                opacity: 0.95,
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                whiteSpace: 'nowrap'
              }}
            >
              {packageData.country || 'Singapore'} - {packageData.city || 'Singapore'}
            </Typography>
          </Box>
        </Box>
      </Box>

      <CardContent sx={{ flex: '1 0 auto', p: 2.5, pb: 1.5 }}>
        {/* Price and Status Row */}
        <Box sx={{ 
          display: 'flex', 
          justifyContent: 'space-between', 
          alignItems: 'center',
          mb: 1.5,
          p: 2,
          bgcolor: '#f8f9fa',
          borderRadius: '10px',
          border: '1px solid #e9ecef'
        }}>
          {/* Price Section */}
          <Box sx={{ flex: 1 }}>
            <Typography variant="caption" color="text.secondary" sx={{ display: 'block', mb: 0.5, fontSize: '0.7rem', fontWeight: 500 }}>
              From SGD
            </Typography>
            <Typography variant="h6" color="primary" fontWeight="bold" sx={{ fontSize: '1.25rem', lineHeight: 1.2 }}>
              ${packageData.price_adult || '343.00'}
            </Typography>
          </Box>
          
          {/* Category Section */}
          <Box sx={{ 
            display: 'flex', 
            flexDirection: 'column', 
            alignItems: 'center',
            ml: 2
          }}>
            <Chip 
              label={packageData.category || 'Standard'} 
              size="small" 
              sx={{ 
                height: 24,
                fontSize: '0.65rem',
                fontWeight: 'bold',
                textTransform: 'uppercase',
                letterSpacing: '0.5px',
                bgcolor: '#e3f2fd',
                color: '#1565c0',
                border: '1px solid #bbdefb'
              }} 
            />
          </Box>
        </Box>
        
        <Divider sx={{ my: 1.5 }} />
        
        {/* Compact sections layout */}
        <Box sx={{ display: 'flex', gap: 1.5, mb: 1.5 }}>
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
            
            <Box sx={{ display: 'flex', gap: 0.75 }}>
              {attractions.length > 0 ? (
                <>
                  {attractions.slice(0, 2).map((attraction, index) => (
                    <Box 
                      key={`attraction-${packageData.package_id}-${attraction.attraction_id || 'unknown'}-${index}`}
                      component="img"
                      src={attraction.image || '/img/activities/1.png'}
                      sx={{ 
                        width: 44, 
                        height: 44, 
                        borderRadius: '8px',
                        objectFit: 'cover',
                        border: '1px solid #e0e0e0'
                      }}
                      alt={attraction.name || 'Attraction'}
                    />
                  ))}
                  {attractions.length > 2 && (
                    <Box 
                      key={`more-attractions-${packageData.package_id}`}
                      sx={{ 
                        width: 44, 
                        height: 44, 
                        borderRadius: '8px',
                        bgcolor: '#f5f5f5',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        color: '#1976d2',
                        fontSize: '0.75rem',
                        fontWeight: 'bold',
                        border: '1px solid #e0e0e0'
                      }}
                    >
                      +{totalAttractions - 2}
                    </Box>
                  )}
                </>
              ) : (
                <Box 
                  key={`no-attractions-${packageData.package_id}`}
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
            
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              {accommodations.length > 0 ? (
                <>
                  <Avatar 
                    src={accommodations[0].main_image} 
                    sx={{ 
                      width: 32, 
                      height: 32,
                      border: '1px solid #e0e0e0'
                    }}
                    alt={accommodations[0].name}
                  />
                  <Typography variant="caption" sx={{ fontWeight: 500, fontSize: '0.75rem', flex: 1 }} noWrap>
                    {accommodations[0].name}
                  </Typography>
                </>
              ) : (
                <Typography variant="caption" sx={{ fontWeight: 500, fontSize: '0.75rem', color: 'text.secondary', fontStyle: 'italic' }} noWrap>
                  No accommodations listed
                </Typography>
              )}
            </Box>
          </Box>
        </Box>
        
     
        
        {/* Services */}
        <Box sx={{ mt: 1.5 }}>
          <Box sx={{ display: 'flex', alignItems: 'center', mb: 0.75 }}>
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
          
          <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.75, overflow: 'hidden' }}>
            {/* Combine Airport Pickup and Dropoff into a single chip */}
            {(hasArrivalPickup || hasDepartureService) && (
              <Chip 
                key={`airport-service-${packageData.package_id}`}
                icon={<FlightIcon sx={{ fontSize: '0.75rem !important' }} />} 
                label={
                  hasArrivalPickup && hasDepartureService
                    ? "Airport Pickup / Dropoff"
                    : hasArrivalPickup
                      ? "Airport Pickup"
                      : "Airport Dropoff"
                }
                size="small" 
                sx={{ height: 24, fontSize: '0.7rem', whiteSpace: 'nowrap', px: 1 }} 
              />
            )}
            
            {/* Tour Guide chip */}
            {hasTourGuides && (
              <Chip 
                key={`tour-guide-${packageData.package_id}`}
                icon={<ExploreIcon sx={{ fontSize: '0.75rem !important' }} />} 
                label="Tour Guide" 
                size="small" 
                sx={{ height: 24, fontSize: '0.7rem', whiteSpace: 'nowrap', px: 1 }} 
              />
            )}
            
            {/* Transfers chip */}
            {hasTransfers && (
              <Chip 
                key={`transfers-${packageData.package_id}`}
                icon={<DirectionsBusIcon sx={{ fontSize: '0.75rem !important' }} />} 
                label="Transfers" 
                size="small" 
                sx={{ height: 24, fontSize: '0.7rem', whiteSpace: 'nowrap', px: 1 }} 
              />
            )}
            
            {/* No services message */}
            {!hasArrivalPickup && !hasDepartureService && !hasTourGuides && !hasTransfers && (
              <Typography 
                key={`no-services-${packageData.package_id}`}
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
        p: 2, 
        pt: 1.5,
        borderTop: '1px solid #eee',
        mt: 'auto'
      }}>
        <Button 
          variant="outlined" 
          startIcon={<VisibilityIcon sx={{ fontSize: 18 }} />}
          onClick={handleViewDetails}
          size="medium"
          sx={{ 
            flex: 1, 
            py: 1, 
            fontSize: '0.8rem',
            fontWeight: 600,
            borderRadius: '8px'
          }}
        >
          View Details
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
  <Grid item xs={12} sm={12} md={6} lg={6}>
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
      <Box sx={{ p: 2.5 }}>
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

const ListingCards = ({ 
  hasSearched = false, 
  selectedDmcId = null, // Single DMC ID
  selectedDmcData = null, // Single DMC data
  locationData = null,
  dmcOptions = [],
  filteredDMCs = [],
  dmcLoading = false,
  dmcError = null,
  filterText = '',
  handleFilterChange = () => {},
  handleDMCCardClick = () => {},
  isDMCSelected = () => false,
}) => {
  const { packages, loading, error, searchParams } = useSelector(state => state.prePackages);
  const dispatch = useDispatch();
  
  // Log DMC information for debugging
  console.log('🏢 ListingCards - Selected DMC ID:', selectedDmcId);
  console.log('🏢 ListingCards - Selected DMC Data:', selectedDmcData);
  
  // Infinite scroll states
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const loaderRef = useRef(null);
  
  // Track if this is the first render to avoid unnecessary refetch
  const isFirstRender = useRef(true);
  const previousDmcId = useRef(selectedDmcId);



  // Reset pagination when packages array becomes empty (new search)
  useEffect(() => {
    if (packages.length === 0) {
      setCurrentPage(1);
      setHasMore(true);
      setIsLoadingMore(false);
    }
  }, [packages.length]);

  // Reset pagination when searchParams change (new search)
  useEffect(() => {
    if (searchParams) {
      setCurrentPage(1);
      setHasMore(true);
      setIsLoadingMore(false);
    }
  }, [searchParams]);

  // Refetch packages when DMC selection changes (Single DMC)
  useEffect(() => {
    // Skip on first render
    if (isFirstRender.current) {
      isFirstRender.current = false;
      previousDmcId.current = selectedDmcId;
      return;
    }
    
    // Check if DMC ID actually changed
    const dmcIdChanged = previousDmcId.current !== selectedDmcId;
    
    if (!dmcIdChanged) {
      return;
    }
    
    // Update previous DMC ID
    previousDmcId.current = selectedDmcId;
    
    // Only refetch if we have searchParams AND packages have been loaded (not during initial load)
    // This prevents double-fetching when first DMC is auto-selected during initial search
    if (searchParams && packages.length > 0 && !loading) {
      console.log('🏢 DMC selection changed, refetching packages with DMC ID:', selectedDmcId);
      
      // Update searchParams with new DMC ID (single selection)
      const updatedSearchParams = { ...searchParams };
      
      // Set or remove dmc_id based on selection
      if (selectedDmcId) {
        updatedSearchParams.dmc_id = selectedDmcId;
      } else {
        delete updatedSearchParams.dmc_id;
      }
      
      // Remove dmc_ids if it exists (ensure we only use single dmc_id)
      delete updatedSearchParams.dmc_ids;
      
      // Update Redux searchParams
      dispatch(setSearchParams(updatedSearchParams));
      
      // Refetch packages from the beginning
      setCurrentPage(1);
      setHasMore(true);
      setIsLoadingMore(false);
      
      dispatch(fetchPackages({ searchParams: updatedSearchParams, start: 0, limit: 5 }))
        .unwrap()
        .then((response) => {
          console.log('🏢 Packages refetched successfully with DMC filter');
        })
        .catch((error) => {
          console.error('🏢 Failed to refetch packages:', error);
        });
    } else if (dmcIdChanged && selectedDmcId) {
      console.log('🏢 First DMC auto-selected, will be used in initial package fetch');
    }
  }, [selectedDmcId, searchParams, dispatch, packages.length, loading]); // Track single selectedDmcId

  // Infinite scroll effect - load more packages when scrolling
  useEffect(() => {
    const handleScroll = () => {
      const scrollPosition = window.innerHeight + document.documentElement.scrollTop;
      const documentHeight = document.documentElement.offsetHeight;
      const threshold = documentHeight - 500;
      
      
      if (
        scrollPosition >= threshold &&
        hasMore &&
        !isLoadingMore &&
        searchParams
      ) {
        setIsLoadingMore(true);
        setCurrentPage(prev => prev + 1);
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, [hasMore, isLoadingMore, searchParams]);

  // Effect to load more packages when currentPage changes
  useEffect(() => {
    if (currentPage > 1 && searchParams) {
      const start = (currentPage - 1) * 5;
      
      dispatch(fetchPackages({ searchParams, start, limit: 5 }))
        .then((response) => {
          setIsLoadingMore(false);
          
          // Check for empty response in different formats
          const isEmptyResponse = 
            !response.payload || 
            response.payload.length === 0 ||
            (response.payload.packages && response.payload.packages.length === 0) ||
            (response.payload.data && response.payload.data.length === 0) ||
            (typeof response.payload === 'object' && Object.keys(response.payload).length === 0);
          
          if (isEmptyResponse) {
            setHasMore(false);
            return; // Stop here, don't make confirmation call
          } else if (response.payload.length < 5) {
            setHasMore(false);
          }
        })
        .catch((error) => {
          setIsLoadingMore(false);
        });
    }
  }, [currentPage, searchParams, dispatch, packages.length]);



  // Render DMC Selection Panel
  const renderDMCSelectionPanel = () => (
    <DMCSelectionPanel elevation={2}>
      {/* Location Section */}
      <Box sx={{ mb: 2 }}>
        <Typography variant="subtitle2" sx={{ mb: 2, fontWeight: 600, color: '#333', fontSize: '0.9rem' }}>
          📍 Destination
        </Typography>
        
        {locationData ? (
          <Box sx={{ 
            p: 2, 
            bgcolor: 'rgba(102, 126, 234, 0.08)', 
            borderRadius: 2, 
            border: '1px solid rgba(102, 126, 234, 0.2)',
            display: 'flex',
            alignItems: 'center'
          }}>
            <LocationOnIcon sx={{ fontSize: 18, color: '#667eea', mr: 1 }} />
            <Box>
              <Typography variant="body2" sx={{ fontWeight: 500, color: '#333' }}>
                {locationData.country}
              </Typography>
              {locationData.city && (
                <Typography variant="caption" sx={{ color: '#666' }}>
                  {locationData.city}
                </Typography>
              )}
            </Box>
          </Box>
        ) : (
          <Box sx={{ 
            p: 2, 
            bgcolor: 'rgba(255, 152, 0, 0.08)', 
            borderRadius: 2, 
            border: '1px solid rgba(255, 152, 0, 0.2)',
            display: 'flex',
            alignItems: 'center'
          }}>
            <InfoIcon sx={{ fontSize: 18, color: '#ff9800', mr: 1 }} />
            <Typography variant="body2" sx={{ color: '#666', fontSize: '0.875rem' }}>
              Please select a location from the search form first
            </Typography>
          </Box>
        )}
      </Box>

      {/* DMC Filter & Selection */}
      <Box sx={{ mb: 3 }}>
        <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2 }}>
          <Typography variant="subtitle2" sx={{ fontWeight: 600, color: '#333', fontSize: '0.9rem' }}>
            🏢 Select DMC Partner
          </Typography>
          {selectedDmcData && (
            <CheckCircleIcon sx={{ color: '#4caf50', fontSize: 18 }} />
          )}
        </Box>
        
        {locationData && (
          <TextField
            fullWidth
            size="small"
            placeholder="Search DMCs..."
            value={filterText}
            onChange={handleFilterChange}
            InputProps={{
              startAdornment: <SearchIcon sx={{ color: '#999', mr: 0.5, fontSize: '1.2rem' }} />
            }}
            sx={{ 
              mb: 2,
              '& .MuiOutlinedInput-input': {
                fontSize: '0.875rem'
              }
            }}
          />
        )}
      </Box>

      {/* DMC List */}
      <Box sx={{ maxHeight: '400px', overflowY: 'auto' }}>
        {dmcLoading && (
          <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', py: 3 }}>
            <CircularProgress size={20} sx={{ mr: 1 }} />
            <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.875rem' }}>
              Loading DMCs...
            </Typography>
          </Box>
        )}

        {dmcError && (
          <Box sx={{ p: 2, bgcolor: '#fff3e0', borderRadius: 1, mb: 2, border: '1px solid #ffb74d' }}>
            <Typography variant="body2" color="error" sx={{ fontSize: '0.875rem' }}>
              Error: {dmcError}
            </Typography>
          </Box>
        )}

        {!locationData && !dmcLoading && (
          <Box sx={{ textAlign: 'center', py: 3 }}>
            <TravelExploreIconMui sx={{ fontSize: 36, color: '#ddd', mb: 1 }} />
            <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.875rem' }}>
              Select a destination first
            </Typography>
          </Box>
        )}

        {locationData && !dmcLoading && filteredDMCs.length === 0 && dmcOptions.length === 0 && (
          <Box sx={{ textAlign: 'center', py: 3 }}>
            <InfoIcon sx={{ fontSize: 36, color: '#ddd', mb: 1 }} />
            <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.875rem' }}>
              No DMCs available
            </Typography>
          </Box>
        )}

        {filteredDMCs.length === 0 && dmcOptions.length > 0 && (
          <Box sx={{ textAlign: 'center', py: 3 }}>
            <SearchIcon sx={{ fontSize: 36, color: '#ddd', mb: 1 }} />
            <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.875rem' }}>
              No matches found
            </Typography>
          </Box>
        )}

        {filteredDMCs.map((dmc) => (
          <DMCCard
            key={dmc.id}
            selected={isDMCSelected(dmc)}
            onClick={() => handleDMCCardClick(dmc)}
          >
            <CardContent sx={{ p: 1.5 }}>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                {dmc.logo ? (
                  <Avatar
                    src={dmc.logo}
                    alt={dmc.name}
                    sx={{ width: 32, height: 32, mr: 1.5 }}
                  >
                    <BusinessIcon fontSize="small" />
                  </Avatar>
                ) : (
                  <Avatar sx={{ width: 32, height: 32, mr: 1.5, bgcolor: '#667eea' }}>
                    <BusinessIcon fontSize="small" />
                  </Avatar>
                )}
                
                <Box sx={{ flex: 1, minWidth: 0 }}>
                  <Typography variant="subtitle2" sx={{ 
                    fontWeight: 600, 
                    color: isDMCSelected(dmc) ? '#667eea' : '#333',
                    overflow: 'hidden',
                    textOverflow: 'ellipsis',
                    whiteSpace: 'nowrap',
                    fontSize: '0.875rem',
                    lineHeight: 1.2
                  }}>
                    {dmc.name}
                  </Typography>
                  <Typography variant="body2" color="text.secondary" sx={{ 
                    fontSize: '0.75rem',
                    overflow: 'hidden',
                    textOverflow: 'ellipsis',
                    whiteSpace: 'nowrap',
                    lineHeight: 1.2
                  }}>
                    📍 {dmc.location}
                  </Typography>
                </Box>
                
                {isDMCSelected(dmc) && (
                  <CheckCircleIcon sx={{ color: '#4caf50', fontSize: 18 }} />
                )}
              </Box>
            </CardContent>
          </DMCCard>
        ))}
      </Box>

      {/* Selected DMC Summary */}
      {selectedDmcData && (
        <Box sx={{ mt: 3, pt: 2, borderTop: '1px solid #e0e3e8' }}>
          <Typography variant="subtitle2" sx={{ fontWeight: 600, color: '#333', fontSize: '0.9rem', mb: 1 }}>
            ✅ Selected DMC
          </Typography>
          <Box sx={{ 
            p: 1.5, 
            bgcolor: 'rgba(76, 175, 80, 0.08)', 
            borderRadius: 1.5, 
            border: '1px solid rgba(76, 175, 80, 0.2)',
            display: 'flex',
            alignItems: 'center'
          }}>
            {selectedDmcData.logo ? (
              <Avatar
                src={selectedDmcData.logo}
                alt={selectedDmcData.name}
                sx={{ width: 28, height: 28, mr: 1 }}
              >
                <BusinessIcon fontSize="small" />
              </Avatar>
            ) : (
              <Avatar sx={{ width: 28, height: 28, mr: 1, bgcolor: '#4caf50' }}>
                <BusinessIcon fontSize="small" />
              </Avatar>
            )}
            <Box sx={{ flex: 1, minWidth: 0 }}>
              <Typography variant="body2" sx={{ 
                fontWeight: 600, 
                color: '#2e7d32',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                whiteSpace: 'nowrap',
                fontSize: '0.875rem',
                lineHeight: 1.2
              }}>
                {selectedDmcData.name}
              </Typography>
              <Typography variant="caption" sx={{ 
                color: '#666',
                fontSize: '0.7rem'
              }}>
                {selectedDmcData.location}
              </Typography>
            </Box>
          </Box>
        </Box>
      )}
    </DMCSelectionPanel>
  );

  // Show empty space if no search has been performed yet (NO DMC panel before search)
  if (!searchParams) {
    return (
      <Box sx={{ mt: 4, minHeight: '570px' }}>
        {/* Empty space - DMC panel and results will appear here after search */}
      </Box>
    );
  }
  
  if (loading && !isLoadingMore) {
    return (
      <BlurOverlay active={true} hasSearched={hasSearched}>
        <Box sx={{ mt: 4 }}>
          {/* Package Search Results Header - Full Width */}
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

          {/* Grid Layout: DMC Panel (Left) + Loading Skeletons (Right) */}
          <Grid container spacing={3}>
            {/* Left Column - DMC Selection */}
            <Grid item xs={12} md={4} lg={3}>
              {renderDMCSelectionPanel()}
            </Grid>

            {/* Right Column - Loading Skeletons */}
            <Grid item xs={12} md={8} lg={9}>
              <Grid container spacing={3}>
                {[1, 2, 3].map(item => (
                  <LoadingSkeleton key={`skeleton-${item}`} />
                ))}
              </Grid>
            </Grid>
          </Grid>
        </Box>
      </BlurOverlay>
    );
  }
  
  if (error) {
    return (
      <BlurOverlay active={true} hasSearched={hasSearched}>
        <Box sx={{ mt: 4 }}>
          {/* Package Search Results Header - Full Width */}
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
                  color: 'error.main' 
                }} 
              />
              <Typography 
                variant="h5" 
                sx={{ 
                  fontWeight: 600,
                  color: 'error.main'
                }}
              >
                Package Search Results
              </Typography>
            </Box>
          </Paper>

          {/* Grid Layout: DMC Panel (Left) + Error Message (Right) */}
          <Grid container spacing={3}>
            {/* Left Column - DMC Selection */}
            <Grid item xs={12} md={4} lg={3}>
              {renderDMCSelectionPanel()}
            </Grid>

            {/* Right Column - Error Message */}
            <Grid item xs={12} md={8} lg={9}>
              <Box sx={{ p: 3, bgcolor: '#FFF4F4', borderRadius: 2 }}>
                <Typography color="error" variant="h6" gutterBottom>
                  Error loading packages
                </Typography>
                <Typography color="error.light">{error}</Typography>
              </Box>
            </Grid>
          </Grid>
        </Box>
      </BlurOverlay>
    );
  }
  
  if (!packages || packages.length === 0) {
    return (
      <BlurOverlay active={true} hasSearched={hasSearched}>
        <Box sx={{ mt: 4 }}>
          {/* Package Search Results Header - Full Width */}
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

          {/* Grid Layout: DMC Panel (Left) + No Results (Right) */}
          <Grid container spacing={3}>
            {/* Left Column - DMC Selection */}
            <Grid item xs={12} md={4} lg={3}>
              {renderDMCSelectionPanel()}
            </Grid>

            {/* Right Column - No Results */}
            <Grid item xs={12} md={8} lg={9}>
              <NoResults hasSearched={hasSearched} />
            </Grid>
          </Grid>
        </Box>
      </BlurOverlay>
    );
  }

  return (
    <BlurOverlay active={true} hasSearched={hasSearched}>
      <Box sx={{ mt: 4 }}>
        {/* Package Search Results Header - Full Width */}
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
          
          {/* DMC Information Display (Single DMC) */}
          {selectedDmcData && (
            <Box sx={{ mt: 2, pt: 2, borderTop: '1px solid #e0e7ee' }}>
              <Typography 
                variant="caption" 
                sx={{ 
                  fontWeight: 600, 
                  color: 'text.secondary',
                  display: 'flex',
                  alignItems: 'center',
                  mb: 1
                }}
              >
                <BusinessIcon sx={{ fontSize: 14, mr: 0.5 }} />
                Showing packages from:
              </Typography>
              <Chip
                avatar={
                  selectedDmcData.logo ? (
                    <Avatar src={selectedDmcData.logo} alt={selectedDmcData.name} />
                  ) : (
                    <Avatar sx={{ bgcolor: '#0ea5e9' }}>
                      <BusinessIcon fontSize="small" />
                    </Avatar>
                  )
                }
                label={`${selectedDmcData.name} (${selectedDmcData.location})`}
                color="info"
                variant="outlined"
                sx={{ fontSize: '0.75rem', fontWeight: 500 }}
              />
            </Box>
          )}
        </Paper>

        {/* Grid Layout: DMC Panel (Left) + Package Listing (Right) */}
        <Grid container spacing={3}>
          {/* Left Column - DMC Selection Panel */}
          <Grid item xs={12} md={4} lg={3}>
            {renderDMCSelectionPanel()}
          </Grid>

          {/* Right Column - Package Cards */}
          <Grid item xs={12} md={8} lg={9}>
            <Box sx={{ position: 'relative' }}>
              <Grid container spacing={3}>
                {packages.map((packageItem, index) => (
                  <Grid item xs={12} sm={12} md={6} lg={6} key={`package-${packageItem.package_id || `temp-${index}`}`}>
                    <PackageCard packageData={packageItem} />
                  </Grid>
                ))}
              </Grid>
              
              {/* Loading more skeleton */}
              {isLoadingMore && <LoadMoreIndicator />}
              
              {/* Debug button for testing */}
              {packages.length > 0 && (
                <div className="text-center py-4">
                </div>
              )}
              
              {/* No more data indicator */}
              {!hasMore && packages.length > 0 && (
                <div className="text-center py-20">
                  <Typography
                    variant="body2"
                    sx={{
                      color: '#6c757d',
                      fontSize: '14px',
                      fontWeight: 500,
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      gap: 1
                    }}
                  >
                    <span style={{ fontSize: '16px' }}>📭</span>
                    No more data • {packages.length} packages found
                  </Typography>
                </div>
              )}
            </Box>
          </Grid>
        </Grid>
      </Box>
    </BlurOverlay>
  );
};

export default ListingCards;
