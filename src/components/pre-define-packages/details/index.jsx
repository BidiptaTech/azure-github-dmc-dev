import React, { useEffect, useState, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useSelector, useDispatch } from 'react-redux';
import { fetchPackageDetails } from '../../../slice/tour-packages/prePackagesSlice';
import {
  Container,
  Box,
  Button,
  CircularProgress,
  Alert,
  Typography,
  Tabs,
  Tab,
  AppBar,
  Grid,
  Divider,
  List,
  ListItem,
  ListItemText,
  ListItemIcon,
  Paper,
  Stack,
  Card,
  CardContent,
  Chip,
  CardHeader,
  Avatar,
  IconButton,
  Badge,
  Collapse,
  CardMedia
} from '@mui/material';
import HotelIcon from '@mui/icons-material/Hotel';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import PersonIcon from '@mui/icons-material/Person';
import AttractionsIcon from '@mui/icons-material/Attractions';
import MapIcon from '@mui/icons-material/Map';
import GavelIcon from '@mui/icons-material/Gavel';
import InfoIcon from '@mui/icons-material/Info';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
import TodayIcon from '@mui/icons-material/Today';
import FiberManualRecordIcon from '@mui/icons-material/FiberManualRecord';
import KeyboardArrowUpIcon from '@mui/icons-material/KeyboardArrowUp';
import KeyboardArrowDownIcon from '@mui/icons-material/KeyboardArrowDown';
import KeyboardArrowRightIcon from '@mui/icons-material/KeyboardArrowRight';
import KeyboardArrowLeftIcon from '@mui/icons-material/KeyboardArrowLeft';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import GradeIcon from '@mui/icons-material/Grade';
import CategoryIcon from '@mui/icons-material/Category';
import LocalDiningIcon from '@mui/icons-material/LocalDining';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import CloseIcon from '@mui/icons-material/Close';
import EditIcon from '@mui/icons-material/Edit';
import Checkbox from '@mui/material/Checkbox';
import FormControlLabel from '@mui/material/FormControlLabel';
import FormGroup from '@mui/material/FormGroup';
import Radio from '@mui/material/Radio';
import RadioGroup from '@mui/material/RadioGroup';





// Import components
import PackageHeader from './PackageHeader';
import PackageOverview from './PackageOverview';
import InclusionsExclusions from './InclusionsExclusions';
import PackageItinerary from './PackageItinerary';
import AccommodationDetails from './AccommodationDetails';
import AttractionsDetails from './AttractionsDetails';
/* import RestaurantsDetails from './RestaurantsDetails'; */
/* import RestaurantsDetails from './RestaurantsDetails'; */
/* import RestaurantsDetails from './RestaurantsDetails'; */
import GuideDetails from './GuideDetails';
import PackagePricing from './PackagePricing';
import TermsConditions from './TermsConditions';

// Import selection components
import SelectionModal from './selection-components/SelectionModal';
import HotelItemRenderer from './selection-components/HotelItemRenderer';
import AttractionItemRenderer from './selection-components/AttractionItemRenderer';
/* import RestaurantItemRenderer from './selection-components/RestaurantItemRenderer'; */
/* import RestaurantItemRenderer from './selection-components/RestaurantItemRenderer'; */
/* import RestaurantItemRenderer from './selection-components/RestaurantItemRenderer'; */
import GuideItemRenderer from './selection-components/GuideItemRenderer';

// Import API endpoints
import { endpoints } from '../../../services/api';
import { formatDate, getItineraryDayDate } from './shared-date-utils';
// import { formatDate, getItineraryDayDate } from './shared-date-utils';
// import { formatDate, getItineraryDayDate } from './shared-date-utils';

// Navigation arrow for section navigation
const NavigationArrow = ({ direction = 'next', onClick, disabled = false, label }) => {
  const Icon = direction === 'next' ? KeyboardArrowRightIcon : KeyboardArrowLeftIcon;

  return (
    <Button
      variant="text"
      color="primary"
      size="small"
      startIcon={direction === 'prev' ? <Icon fontSize="small" /> : null}
      endIcon={direction === 'next' ? <Icon fontSize="small" /> : null}
      onClick={onClick}
      disabled={disabled}
      sx={{
        borderRadius: '16px',
        fontSize: '0.75rem',
        py: 0.25,
        px: 0.75,
        minWidth: 0,
        '&.Mui-disabled': {
          color: 'text.disabled'
        }
      }}
    >
      {label}
    </Button>
  );
};

// Section header component with change button
const SectionHeader = ({ icon: Icon, title, count, onChangeClick }) => (
  <Box sx={{
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: 'primary.main',
    color: 'primary.contrastText',
    px: 2,
    py: 0.8,
    borderRadius: '8px 8px 0 0'
  }}>
    <Box sx={{ display: 'flex', alignItems: 'center' }}>
      <Avatar
        sx={{
          bgcolor: 'rgba(255, 255, 255, 0.2)',
          width: 28,
          height: 28,
          mr: 1.5
        }}
      >
        <Icon fontSize="small" />
      </Avatar>
      <Typography variant="subtitle2" fontWeight="bold">
        {title}
      </Typography>
    </Box>

    <Box sx={{ display: 'flex', alignItems: 'center' }}>
      {count && (
        <Chip
          label={`${count}`}
          size="small"
          sx={{
            bgcolor: 'rgba(255, 255, 255, 0.2)',
            color: 'inherit',
            fontWeight: 'bold',
            height: 20,
            mr: 1,
            '& .MuiChip-label': { px: 0.8, py: 0 }
          }}
        />
      )}

      {onChangeClick && (
        <IconButton
          size="small"
          onClick={onChangeClick}
          sx={{
            color: 'inherit',
            bgcolor: 'rgba(255, 255, 255, 0.2)',
            p: 0.5,
            '&:hover': {
              bgcolor: 'rgba(255, 255, 255, 0.3)',
            }
          }}
        >
          <EditIcon fontSize="small" />
        </IconButton>
      )}
    </Box>
  </Box>
);

// Compact content section wrapper
const ContentSection = ({ children }) => (
  <Box sx={{
    p: 1.5,
    '& .MuiTypography-h5': {
      fontSize: '1rem',
      mb: 0.5
    },
    '& .MuiTypography-h6': {
      fontSize: '0.9rem',
      mb: 0.5
    },
    '& .MuiTypography-body1': {
      fontSize: '0.875rem',
      lineHeight: 1.4
    },
    '& .MuiTypography-body2': {
      fontSize: '0.8rem',
      lineHeight: 1.3
    },
    '& .MuiCardContent-root': {
      padding: '12px',
      '&:last-child': {
        paddingBottom: '12px'
      }
    },
    '& .MuiGrid-container': {
      '& > .MuiGrid-item': {
        paddingTop: '8px',
        paddingBottom: '8px'
      }
    },
    '& .MuiCard-root': {
      '& .MuiCardContent-root': {
        padding: '8px'
      }
    },
    '& .MuiChip-root': {
      height: '20px',
      fontSize: '0.7rem'
    }
  }}>
    {children}
  </Box>
);

const PackageDetailsContainer = () => {
  const { id } = useParams();
  const dispatch = useDispatch();
  const navigate = useNavigate();

  const [mainTab, setMainTab] = useState(0);
  const [activeDay, setActiveDay] = useState(0);
  const [currentPackageDetails, setCurrentPackageDetails] = useState(null);



  // State for modals
  const [hotelsModalOpen, setHotelsModalOpen] = useState(false);
  const [attractionsModalOpen, setAttractionsModalOpen] = useState(false);
  /* const [restaurantsModalOpen, setRestaurantsModalOpen] = useState(false); */
  /* const [restaurantsModalOpen, setRestaurantsModalOpen] = useState(false); */
  /* const [restaurantsModalOpen, setRestaurantsModalOpen] = useState(false); */
  const [guidesModalOpen, setGuidesModalOpen] = useState(false);

  // State for available items (items fetched from API)
  const [availableHotels, setAvailableHotels] = useState([]);
  const [availableAttractions, setAvailableAttractions] = useState([]);
  /* const [availableRestaurants, setAvailableRestaurants] = useState([]); */
  /* const [availableRestaurants, setAvailableRestaurants] = useState([]); */
  /* const [availableRestaurants, setAvailableRestaurants] = useState([]); */
  const [availableGuides, setAvailableGuides] = useState([]);

  // State for selected items
  const [selectedHotels, setSelectedHotels] = useState([]);
  const [selectedAttractions, setSelectedAttractions] = useState([]);
  /* const [selectedRestaurants, setSelectedRestaurants] = useState([]); */
  /* const [selectedRestaurants, setSelectedRestaurants] = useState([]); */
  /* const [selectedRestaurants, setSelectedRestaurants] = useState([]); */
  const [selectedGuides, setSelectedGuides] = useState([]);

  // State for globally selected hotel and guide across all days
  const [selectedHotelId, setSelectedHotelId] = useState(null);
  const [selectedGuideId, setSelectedGuideId] = useState(null);

  // Track booked attractions by day - { attractionId: dayIndex }
  const [bookedAttractions, setBookedAttractions] = useState({});

  // Ref for content scrolling
  const contentRef = useRef(null);

  // Refs for days in itinerary (will be populated dynamically)
  const dayRefs = useRef([]);

  const handleMainTabChange = (event, newValue) => {
    setMainTab(newValue);
  };

  // Scroll to specific day in itinerary
  const scrollToDay = (dayIndex) => {
    setActiveDay(dayIndex);
    if (dayRefs.current[dayIndex] && dayRefs.current[dayIndex].current && contentRef.current) {
      const contentContainer = contentRef.current;
      const dayElement = dayRefs.current[dayIndex].current;

      // Calculate the day element's position relative to the content container
      const dayRect = dayElement.getBoundingClientRect();
      const containerRect = contentContainer.getBoundingClientRect();

      // Calculate the correct scroll position
      const scrollPosition = contentContainer.scrollTop + (dayRect.top - containerRect.top);

      // Add a small offset for better visibility
      const scrollOffset = -20;

      // Scroll to the calculated position
      contentContainer.scrollTo({
        top: scrollPosition + scrollOffset,
        behavior: 'smooth'
      });
    }
  };

  // Add scroll stabilization with hysteresis
  useEffect(() => {
    if (!contentRef.current || dayRefs.current.length === 0) return;

    let scrollTimer = null;
    let isScrolling = false;
    let lastSetDayIndex = activeDay;
    let selectionLocked = false;
    let unlockSelectionTimer = null;

    // Disable the intersection observer approach completely
    // as it's causing too much instability

    // Calculate which day is most visible and in focus
    const calculateMostVisibleDay = () => {
      if (!contentRef.current) return null;

      const containerRect = contentRef.current.getBoundingClientRect();
      const containerTop = containerRect.top;
      const containerHeight = containerRect.height;

      // Create structure to track visibility of days
      const visibilityData = [];

      for (let i = 0; i < dayRefs.current.length; i++) {
        if (dayRefs.current[i] && dayRefs.current[i].current) {
          const dayRect = dayRefs.current[i].current.getBoundingClientRect();

          // Calculate how much of this day is visible in the viewport
          const visibleTop = Math.max(dayRect.top, containerTop);
          const visibleBottom = Math.min(dayRect.bottom, containerTop + containerHeight);

          if (visibleBottom > visibleTop) {
            const visibleHeight = visibleBottom - visibleTop;
            const visiblePercentage = visibleHeight / dayRect.height;
            const topPosition = dayRect.top - containerTop;

            visibilityData.push({
              dayIndex: i,
              visiblePercentage,
              topPosition
            });
          }
        }
      }

      // No days visible
      if (visibilityData.length === 0) return null;

      // Sort by visible percentage
      visibilityData.sort((a, b) => b.visiblePercentage - a.visiblePercentage);

      // Get the current most visible day
      const mostVisible = visibilityData[0];

      // If selection is locked, don't change unless the new day is significantly more visible
      if (selectionLocked && lastSetDayIndex !== null) {
        // Find the current day in the visibility data
        const currentDayData = visibilityData.find(d => d.dayIndex === lastSetDayIndex);

        // If current day is still reasonably visible (at least 20%), stick with it
        if (currentDayData && currentDayData.visiblePercentage >= 0.2) {
          return lastSetDayIndex;
        }

        // If most visible day is very dominant (over 60%), switch to it
        if (mostVisible.visiblePercentage >= 0.6) {
          // Unlock for this significant change
          selectionLocked = false;
          if (unlockSelectionTimer) clearTimeout(unlockSelectionTimer);
          unlockSelectionTimer = setTimeout(() => {
            selectionLocked = false;
          }, 1000);
          return mostVisible.dayIndex;
        }

        // Otherwise, stay with current selection while locked
        return lastSetDayIndex;
      }

      // Apply hysteresis - require at least 30% visibility to change days
      if (mostVisible.visiblePercentage >= 0.3) {
        return mostVisible.dayIndex;
      }

      // If nothing is significantly visible, don't change selection
      return lastSetDayIndex;
    };

    // Improved scroll handler with strong hysteresis
    const handleScroll = () => {
      // Mark that we're scrolling
      isScrolling = true;

      // Clear any existing timer
      if (scrollTimer) {
        clearTimeout(scrollTimer);
      }

      // Set a new timer to update ONLY after scrolling has completely stopped
      scrollTimer = setTimeout(() => {
        isScrolling = false;

        // Find the most visible day
        const mostVisibleDay = calculateMostVisibleDay();

        if (mostVisibleDay !== null && mostVisibleDay !== activeDay) {
          setActiveDay(mostVisibleDay);
          lastSetDayIndex = mostVisibleDay;

          // Lock selection for a short period to prevent flickering
          selectionLocked = true;

          // Set a timer to unlock the selection after 1 second
          if (unlockSelectionTimer) clearTimeout(unlockSelectionTimer);
          unlockSelectionTimer = setTimeout(() => {
            selectionLocked = false;
          }, 1000);
        }
      }, 350); // Even longer debounce for more stability
    };

    const scrollContainer = contentRef.current;
    scrollContainer.addEventListener('scroll', handleScroll);

    // Initial check to set the correct active day on mount
    setTimeout(() => {
      const initialDay = calculateMostVisibleDay();
      if (initialDay !== null && initialDay !== activeDay) {
        setActiveDay(initialDay);
        lastSetDayIndex = initialDay;
      }
    }, 100);

    return () => {
      if (scrollContainer) {
        scrollContainer.removeEventListener('scroll', handleScroll);
      }
      if (scrollTimer) {
        clearTimeout(scrollTimer);
      }
      if (unlockSelectionTimer) {
        clearTimeout(unlockSelectionTimer);
      }
    };
  }, [activeDay, dayRefs.current.length]);

  const { packageDetails, loadingDetails, errorDetails, searchParams } = useSelector(state => state.prePackages);
  // const { packageDetails, loadingDetails, errorDetails, searchParams } = useSelector(state => state.prePackages);

  useEffect(() => {
    if (id) {
      // console.log('Fetching package details for ID:', id);
      dispatch(fetchPackageDetails(id));
    }
  }, [dispatch, id]);

  // Add a separate effect to log package details when they're loaded
  useEffect(() => {
    if (packageDetails) {
      // console.log('API Response - Package Details:', packageDetails);
      // console.log('API Response - All fields:', Object.keys(packageDetails));
      // console.log('API Response - Selected Hotels:', packageDetails.selected_hotels);
      // console.log('API Response - Selected Attractions:', packageDetails.selected_attractions);
    }
  }, [packageDetails]);

  // Initialize day refs and selected items when package details load
  useEffect(() => {
    if (packageDetails) {
      // Create a memoized version of packageDetails with date from searchParams
      const updatedPackageDetails = {
        ...packageDetails,
        date: searchParams?.date || packageDetails.date,
        // Include searchParams directly to ensure getItineraryDayDate has access to it
        searchParams
      };

      // Create refs for each day in the itinerary
      if (updatedPackageDetails.duration_days) {
        dayRefs.current = Array(updatedPackageDetails.duration_days)
          .fill()
          .map((_, i) => dayRefs.current[i] || React.createRef());
      }

      // Process the API response data structure
      try {
        // console.log('Processing package details...');
        
        // Handle hotels from the API response
        if (updatedPackageDetails.selected_hotels && Array.isArray(updatedPackageDetails.selected_hotels)) {
          // console.log('Setting hotels:', updatedPackageDetails.selected_hotels);
          setSelectedHotels(updatedPackageDetails.selected_hotels.map(hotel => ({
            ...hotel,
            // Ensure required fields exist
            name: hotel.name || "Unknown Hotel",
            image: hotel.image || '/img/hotels/1.png',
            // Ensure days array is valid
            days: Array.isArray(hotel.days) ? hotel.days : []
          })));
        }

        // Handle attractions from the API response
        if (updatedPackageDetails.selected_attractions && Array.isArray(updatedPackageDetails.selected_attractions)) {
          // console.log('Setting attractions:', updatedPackageDetails.selected_attractions);
          const allAttractions = [];
          const bookedByDay = {};

          updatedPackageDetails.selected_attractions.forEach(attraction => {
            // Ensure required fields exist
            const safeAttraction = {
              ...attraction,
              name: attraction.name || "Unknown Attraction",
              image: attraction.image || '/img/attractions/1.png'
            };

            allAttractions.push(safeAttraction);

            // Map attractions to days using the 'day' field from API response
            if (attraction.day && typeof attraction.day === 'number') {
              // Convert to 0-based index for internal use
              bookedByDay[attraction.id] = attraction.day - 1;
            }
          });

          // console.log('Processed attractions:', allAttractions);
          // console.log('Booked attractions by day:', bookedByDay);
          setSelectedAttractions(allAttractions);
          setBookedAttractions(bookedByDay);
        }

        // Handle guides from the API response (if available)
        if (updatedPackageDetails.selected_guides && Array.isArray(updatedPackageDetails.selected_guides)) {
          setSelectedGuides(updatedPackageDetails.selected_guides.map(guide => ({
            ...guide,
            // Ensure required fields exist
            name: guide.name || "Tour Guide",
            image: guide.image || '/img/team/1.png',
            languages: guide.languages || ["English"],
          })));
        } else if (updatedPackageDetails.selected_guide) {
          // Handle single guide case
          setSelectedGuides([{
            ...updatedPackageDetails.selected_guide,
            name: updatedPackageDetails.selected_guide.name || "Tour Guide",
            image: updatedPackageDetails.selected_guide.image || '/img/team/1.png',
            languages: updatedPackageDetails.selected_guide.languages || ["English"],
          }]);
        }

        // Also try to parse itinerary JSON if it exists (for backward compatibility)
        if (typeof updatedPackageDetails.itinerary === 'string' && updatedPackageDetails.itinerary) {
          const parsedItinerary = JSON.parse(updatedPackageDetails.itinerary);
          
          // If we have parsed itinerary data, it might override the direct fields
          if (parsedItinerary && parsedItinerary.hotels && Array.isArray(parsedItinerary.hotels)) {
            setSelectedHotels(parsedItinerary.hotels.map(hotel => ({
              ...hotel,
              name: hotel.name || "Unknown Hotel",
              image: hotel.image || '/img/hotels/1.png',
              days: Array.isArray(hotel.days) ? hotel.days : []
            })));
          }

          if (parsedItinerary && parsedItinerary.itinerary && Array.isArray(parsedItinerary.itinerary)) {
            const allAttractions = [];
            const bookedByDay = {};

            parsedItinerary.itinerary.forEach((dayData, index) => {
              if (dayData && dayData.attractions && Array.isArray(dayData.attractions)) {
                const safeAttractions = dayData.attractions.map(attraction => ({
                  ...attraction,
                  name: attraction.name || "Unknown Attraction",
                  image: attraction.image || '/img/attractions/1.png'
                }));

                allAttractions.push(...safeAttractions);

                safeAttractions.forEach(attraction => {
                  if (attraction && attraction.attraction_id) {
                    bookedByDay[attraction.attraction_id] = index;
                  }
                });
              }
            });

            if (allAttractions.length > 0) {
              setSelectedAttractions(allAttractions);
              setBookedAttractions(bookedByDay);
            }

            const guides = parsedItinerary.itinerary
              .filter(day => day && day.guide)
              .map(day => ({
                ...day.guide,
                name: day.guide.name || "Tour Guide",
                image: day.guide.image || '/img/team/1.png',
                languages: day.guide.languages || ["English"],
              }));

            if (guides.length > 0) {
              setSelectedGuides(guides);
            }
          }
        }
      } catch (error) {
        console.error('Error processing package details:', error);
        // Use existing data from packageDetails as fallback
        setSelectedHotels(updatedPackageDetails.selected_hotels || []);
        setSelectedAttractions(updatedPackageDetails.selected_attractions || []);
        setSelectedGuides(updatedPackageDetails.selected_guides || updatedPackageDetails.selected_guide || []);
      }

      // Set current package details with updates
      setCurrentPackageDetails(updatedPackageDetails);
      // console.log('Set current package details:', updatedPackageDetails);
    }
  }, [packageDetails, searchParams]);

  // }, [packageDetails, searchParams]);

  // Add loading state
  const [isLoading, setIsLoading] = useState(false);



  // Helper to check if an attraction is already booked
  const isAttractionBooked = (attractionId) => {
    return attractionId in bookedAttractions;
  };

  // Helper to check if an attraction is booked for a specific day
  const isAttractionBookedForDay = (attractionId, dayIndex) => {
    // First, check if we have parsed data from JSON structure
    if (typeof bookedAttractions[attractionId] === 'number') {
      return bookedAttractions[attractionId] === dayIndex;
    }

    // For compatibility with any legacy data
    return bookedAttractions[attractionId] === dayIndex;
  };

  // Handlers for hotel and guide selection
  const handleHotelSelect = (hotelId) => {
    // console.log(`Selected hotel: ${hotelId}`);
    setSelectedHotelId(hotelId);
  };



  // Get itinerary dates for all days
  const getItineraryDates = () => {
    // Use currentPackageDetails that has searchParams attached to it
    const detailsWithSearchParams = currentPackageDetails || packageDetails;

    if (!detailsWithSearchParams || !detailsWithSearchParams.duration_days) return [];

    const dates = [];
    for (let i = 0; i < detailsWithSearchParams.duration_days; i++) {
      const dayDate = getItineraryDayDate(detailsWithSearchParams, i);
      dates.push({
        day: i + 1,
        date: formatDate(dayDate),
        full_date: dayDate
      });
    }
    return dates;
  };

  // Helper function to ensure consistent ID handling
  const getEntityId = (entity, index, prefix) => {
    // Try to get ID in different formats that might exist in the data
    if (entity.attraction_id) return entity.attraction_id; // New format for attractions
    if (entity.id) return entity.id; // Generic id field
    if (entity._id) return entity._id; // MongoDB style id
    return `${prefix}-${index}`; // Fallback to index-based id
  };



  // No need for handlers since these are not user-selectable

  // Helper to check if an attraction has transfer available and what type
  const getAttractionTransferType = (attractionId) => {
    // Find the attraction in the selected attractions
    const attraction = selectedAttractions.find(a =>
      (a.id === attractionId || a._id === attractionId || a.attraction_id === attractionId)
    );

    if (!attraction) return null;

    // Check for transfer_type in the new structure
    if (attraction.transfer_type === 'both_way') return 'bidirectional';
    if (attraction.transfer_type === 'one_way') return 'unidirectional';

    // Check for transfer_available in the new structure
    if (attraction.transfer_available === 1 || attraction.transfer_available === true)
      return 'unidirectional';

    // Fall back to legacy checks
    if (attraction?.with_transfer === 2) return 'bidirectional';
    if (attraction?.with_transfer === 1 ||
      attraction?.with_transfer === true) return 'unidirectional';

    return null;
  };


  if (loadingDetails) {
    return (
      <Container>
        <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: '50vh' }}>
          <CircularProgress />
          <Typography variant="h6" sx={{ ml: 2 }}>Loading package details...</Typography>
        </Box>
      </Container>
    );
  }

  if (errorDetails) {
    return (
      <Container>
        <Box sx={{ mt: 4 }}>
          <Alert severity="error">{errorDetails}</Alert>
          <Button
            variant="outlined"
            color="primary"
            sx={{ mt: 2 }}
            onClick={() => navigate(-1)}
          >
            Go Back
          </Button>
        </Box>
      </Container>
    );
  }

  if (!packageDetails) {
    // console.log('No package details found');
    return (
      <Container>
        <Box sx={{ mt: 4 }}>
          <Alert severity="info">No package details found.</Alert>
          <Button
            variant="outlined"
            color="primary"
            sx={{ mt: 2 }}
            onClick={() => navigate(-1)}
          >
            Go Back
          </Button>
        </Box>
      </Container>
    );
  }

  // console.log('Rendering with package details:', packageDetails);
  // console.log('Selected hotels:', selectedHotels);
  // console.log('Selected attractions:', selectedAttractions);
  // console.log('Booked attractions:', bookedAttractions);

  return (
    <Container maxWidth="lg" sx={{ py: 4 }}>
      <Button
        variant="outlined"
        sx={{ mb: 3 }}
        onClick={() => navigate(-1)}
      >
        Back to Packages
      </Button>

      {/* Package Header with Image, Title and Basic Info */}
      <PackageHeader packageData={packageDetails} />

      {/* Main Tabs */}
      <Box sx={{ mt: 3 }}>
        <AppBar position="static" color="default" elevation={0}>
          <Tabs
            value={mainTab}
            onChange={handleMainTabChange}
            variant="fullWidth"
            aria-label="main package tabs"
            sx={{
              '& .MuiTab-root': {
                fontWeight: 'bold',
                fontSize: '1rem',
              }
            }}
          >
            <Tab label="Itinerary & Details" />
            <Tab label="Policies" />
            <Tab label="Summary" />
          </Tabs>
        </AppBar>

        {/* Itinerary & Details Tab - Three-column layout */}
        {mainTab === 0 && (
          <Box sx={{ mt: 3, position: 'relative' }}>
            <Grid container spacing={3}>
              {/* Left sidebar with day-by-day navigation - Sticky */}
              <Grid item xs={12} md={3} lg={2} sx={{ display: 'flex', flexDirection: 'column' }}>
                <Box sx={{
                  height: 'calc(100vh - 200px)',
                  position: 'relative'
                }}>
                  <PackageItinerary
                    packageDetails={currentPackageDetails || packageDetails}
                    activeDay={activeDay}
                    setActiveDay={setActiveDay}
                    contentRef={contentRef}
                    dayRefs={dayRefs}
                  />
                </Box>
              </Grid>

              {/* Content Columns - Container for main content and price */}
              <Grid item xs={12} md={9} lg={10}>
                <Grid container spacing={3}>
                  {/* Middle column - Scrollable main content */}
                  <Grid item xs={12} md={8}>
                    <Box
                      ref={contentRef}
                      sx={{
                        height: 'calc(100vh - 200px)',
                        overflowY: 'auto',
                        pr: 2,
                        // Add custom scrollbar styling
                        '&::-webkit-scrollbar': {
                          width: '8px',
                        },
                        '&::-webkit-scrollbar-track': {
                          backgroundColor: 'rgba(0,0,0,0.05)',
                          borderRadius: '8px',
                        },
                        '&::-webkit-scrollbar-thumb': {
                          backgroundColor: 'rgba(0,0,0,0.2)',
                          borderRadius: '8px',
                          '&:hover': {
                            backgroundColor: 'rgba(0,0,0,0.3)',
                          },
                        },
                      }}
                    >
                      {/* Day-by-Day Itinerary with Service Selection */}
                      {(currentPackageDetails || packageDetails).duration_days &&
                        Array((currentPackageDetails || packageDetails).duration_days).fill().map((_, dayIndex) => {
                          // Create references for each day if not already created
                          if (!dayRefs.current[dayIndex]) {
                            dayRefs.current[dayIndex] = React.createRef();
                          }

                          // Get the day date using currentPackageDetails that has searchParams for consistency
                          const detailsWithSearchParams = currentPackageDetails || packageDetails;
                          const dayDate = getItineraryDayDate(detailsWithSearchParams, dayIndex);
                          const formattedDate = formatDate(dayDate);

                          // Get description for the day
                          const currentDetails = detailsWithSearchParams;
                          const dayDescription = dayIndex === 0 ? 'Arrival' : 
                            dayIndex === (currentDetails.duration_days - 1) ? 'Departure' : 
                            'Exploration';

                          return (
                            <Box
                              key={`day-${dayIndex}`}
                              ref={dayRefs.current[dayIndex]}
                              id={`day-${dayIndex}`}
                              sx={{ mb: 4 }}
                            >
                              {/* Day Header */}
                              <Card elevation={2} sx={{ borderRadius: '8px 8px 0 0', overflow: 'hidden', mb: 0 }}>
                                <Box sx={{
                                  bgcolor: activeDay === dayIndex ? 'primary.main' : 'grey.100',
                                  color: activeDay === dayIndex ? 'primary.contrastText' : 'text.primary',
                                  px: 3,
                                  py: 1.5,
                                  display: 'flex',
                                  justifyContent: 'space-between',
                                  alignItems: 'center'
                                }}>
                                  <Box>
                                    <Typography variant="h6" fontWeight="bold">
                                      Day {dayIndex + 1} - {dayDescription}
                                    </Typography>
                                    <Typography variant="body2" color={activeDay === dayIndex ? 'primary.contrastText' : 'text.secondary'}>
                                      {formattedDate}
                                    </Typography>
                                  </Box>
                                  <Chip
                                    label={`Day ${dayIndex + 1}`}
                                    color={activeDay === dayIndex ? "primary" : "default"}
                                    onClick={() => setActiveDay(dayIndex)}
                                    variant={activeDay === dayIndex ? "filled" : "outlined"}
                                  />
                                </Box>
                              </Card>

                              {/* Day Services */}
                              <Card elevation={1} sx={{ borderRadius: '0 0 8px 8px', overflow: 'hidden', mb: 2 }}>
                                <ContentSection>


                                  {/* Hotels Section */}
                                  <Box sx={{ mb: 4 }}>
                                    <Box sx={{
                                      display: 'flex',
                                      alignItems: 'center',
                                      mb: 2,
                                      pb: 1,
                                      borderBottom: '1px solid',
                                      borderColor: 'divider'
                                    }}>
                                      <Box sx={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        bgcolor: 'primary.main',
                                        color: 'white',
                                        borderRadius: '50%',
                                        width: 28,
                                        height: 28,
                                        mr: 1
                                      }}>
                                        <HotelIcon sx={{ fontSize: 16 }} />
                                      </Box>
                                      <Typography variant="subtitle1" fontWeight="bold">Accommodation</Typography>
                                    </Box>

                                    {/* Entry/Exit port options for first/last day */}
                                    <Grid container spacing={2} sx={{ mt: 2 }}>
                                      {/* Entry port transfer for first day - Check if entry port transfer is enabled */}
                                      {dayIndex === 0 && (() => {
                                        const details = currentPackageDetails || packageDetails;
                                        // Check for explicit transport flags
                                        const hasEntryTransfer = details?.entry_port_transfer === 1 || 
                                                               details?.entry_port === 1 || 
                                                               details?.arrival_pickup === 1 ||
                                                               details?.has_entry_port_transfer === true;
                                        
                                        // Also check if description mentions airport pickup
                                        const descriptionIncludesPickup = details?.description?.toLowerCase().includes('airport pickup') ||
                                                                         details?.description?.toLowerCase().includes('airport transfer') ||
                                                                         details?.description?.toLowerCase().includes('pickup service');
                                        
                                        // Check inclusions for transport services
                                        const inclusionsIncludePickup = details?.inclusions?.toLowerCase().includes('airport pickup') ||
                                                                       details?.inclusions?.toLowerCase().includes('airport transfer') ||
                                                                       details?.inclusions?.toLowerCase().includes('pickup service');
                                        
                                        // For now, let's show airport pickup for first day as it's common in packages
                                        // You can remove this fallback if you want to be more strict
                                        const showPickupByDefault = true; // Set to false if you want to be strict
                                        
                                        return hasEntryTransfer || descriptionIncludesPickup || inclusionsIncludePickup || showPickupByDefault;
                                      })() && (
                                          <Grid item xs={12}>
                                            <Box sx={{
                                              display: 'flex',
                                              alignItems: 'center',
                                              mb: 2,
                                              p: 2,
                                              bgcolor: 'rgba(25, 118, 210, 0.08)',
                                              borderRadius: 2,
                                              border: '1px solid',
                                              borderColor: 'primary.light'
                                            }}>
                                              <Box sx={{
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center',
                                                bgcolor: 'primary.main',
                                                color: 'white',
                                                borderRadius: '50%',
                                                width: 36,
                                                height: 36,
                                                mr: 2,
                                                flexShrink: 0
                                              }}>
                                                <span role="img" aria-label="airplane" style={{ fontSize: '18px' }}>✈️</span>
                                              </Box>
                                              <Box>
                                                <Typography variant="subtitle2" fontWeight="bold" sx={{ color: 'primary.dark' }}>
                                                  Airport Pickup Service Included
                                                </Typography>
                                                <Typography variant="body2" color="text.secondary">
                                                  Our representative will meet you at the airport and transfer you to your hotel
                                                </Typography>
                                              </Box>
                                            </Box>
                                          </Grid>
                                        )}

                                      {/* Exit port transfer for last day - Check if exit port transfer is enabled */}
                                      {dayIndex === (packageDetails.duration_days - 1) && (() => {
                                        const details = currentPackageDetails || packageDetails;
                                        // Check for explicit transport flags
                                        const hasExitTransfer = details?.exit_port_transfer === 1 || 
                                                              details?.exit_port === 1 || 
                                                              details?.departure_service === 1 ||
                                                              details?.has_exit_port_transfer === true;
                                        
                                        // Also check if description mentions airport drop-off
                                        const descriptionIncludesDropoff = details?.description?.toLowerCase().includes('airport drop') ||
                                                                          details?.description?.toLowerCase().includes('airport transfer') ||
                                                                          details?.description?.toLowerCase().includes('drop-off service');
                                        
                                        // Check inclusions for transport services
                                        const inclusionsIncludeDropoff = details?.inclusions?.toLowerCase().includes('airport drop') ||
                                                                        details?.inclusions?.toLowerCase().includes('airport transfer') ||
                                                                        details?.inclusions?.toLowerCase().includes('drop-off service');
                                        
                                        // For now, let's show airport drop-off for last day as it's common in packages
                                        // You can remove this fallback if you want to be more strict
                                        const showDropoffByDefault = true; // Set to false if you want to be strict
                                        
                                        return hasExitTransfer || descriptionIncludesDropoff || inclusionsIncludeDropoff || showDropoffByDefault;
                                      })() && (
                                          <Grid item xs={12}>
                                            <Box sx={{
                                              display: 'flex',
                                              alignItems: 'center',
                                              mb: 2,
                                              p: 2,
                                              bgcolor: 'rgba(25, 118, 210, 0.08)',
                                              borderRadius: 2,
                                              border: '1px solid',
                                              borderColor: 'primary.light'
                                            }}>
                                              <Box sx={{
                                                display: 'flex',
                                                alignItems: 'center',
                                                justifyContent: 'center',
                                                bgcolor: 'primary.main',
                                                color: 'white',
                                                borderRadius: '50%',
                                                width: 36,
                                                height: 36,
                                                mr: 2,
                                                flexShrink: 0
                                              }}>
                                                <span role="img" aria-label="taxi" style={{ fontSize: '18px' }}>🚕</span>
                                              </Box>
                                              <Box>
                                                <Typography variant="subtitle2" fontWeight="bold" sx={{ color: 'primary.dark' }}>
                                                  Airport Drop-off Service Included
                                                </Typography>
                                                <Typography variant="body2" color="text.secondary">
                                                  We'll arrange transportation from your hotel to the airport for your departure
                                                </Typography>
                                              </Box>
                                            </Box>
                                          </Grid>
                                        )}

                                      {/* Hotel Selection - Filtered by days array */}
                                      {selectedHotels && selectedHotels.length > 0 ? (
                                        <RadioGroup
                                          name={`hotels-day-${dayIndex}`}
                                          value={selectedHotelId || ''}
                                          onChange={(e) => handleHotelSelect(e.target.value)}
                                          sx={{ width: '100%' }}
                                        >
                                          <Grid container spacing={2}>
                                            {selectedHotels
                                              .filter(hotel =>
                                                // Show hotel if it has days array and current day (1-indexed) is included
                                                hotel.days && Array.isArray(hotel.days) &&
                                                hotel.days.includes(dayIndex + 1)
                                              )
                                              .map((hotel, idx) => (
                                                <Grid item xs={12} sm={6} md={4} key={`hotel-${dayIndex}-${idx}`}>
                                                  <Card
                                                    variant="outlined"
                                                    sx={{
                                                      height: '100%',
                                                      transition: 'all 0.2s ease',
                                                      position: 'relative',
                                                      border: '1px solid',
                                                      borderColor: 'divider',
                                                      '&:hover': {
                                                        boxShadow: 2,
                                                        borderColor: 'primary.light'
                                                      }
                                                    }}
                                                  >
                                                    <Box
                                                      component="img"
                                                      src={hotel.image || '/img/hotels/1.png'}
                                                      alt={hotel.name}
                                                      sx={{
                                                        width: '100%',
                                                        height: 120,
                                                        objectFit: 'cover',
                                                        borderBottom: '1px solid',
                                                        borderColor: 'divider'
                                                      }}
                                                    />
                                                    <CardContent sx={{ p: 2 }}>
                                                      <Typography variant="subtitle1" fontWeight="bold" noWrap>{hotel.name}</Typography>
                                                      <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                                                        <LocationOnIcon sx={{ fontSize: 16, color: 'text.secondary', mr: 0.5 }} />
                                                        <Typography variant="caption" color="text.secondary" noWrap>
                                                          {hotel.address || hotel.location || hotel.city || 'Location information unavailable'}
                                                        </Typography>
                                                      </Box>
                                                      <Box sx={{
                                                        mt: 1,
                                                        display: 'flex',
                                                        alignItems: 'center',
                                                        px: 1.5,
                                                        py: 0.5,
                                                        bgcolor: 'primary.light',
                                                        color: 'primary.contrastText',
                                                        borderRadius: 1,
                                                        fontSize: '0.75rem',
                                                        fontWeight: 'medium'
                                                      }}>
                                                        <CalendarTodayIcon sx={{ fontSize: 14, mr: 0.5 }} />
                                                        Assigned by Tour Operator
                                                      </Box>
                                                    </CardContent>
                                                  </Card>
                                                </Grid>
                                              ))}
                                          </Grid>
                                        </RadioGroup>
                                      ) : (
                                        <Grid item xs={12}>
                                          <Alert severity="info" sx={{ mb: 1 }}>No hotels available for this day.</Alert>
                                        </Grid>
                                      )}

                                      {/* Show message when there are hotels but none assigned to this day */}
                                      {selectedHotels && selectedHotels.length > 0 &&
                                        !selectedHotels.some(hotel => hotel.days && Array.isArray(hotel.days) && hotel.days.includes(dayIndex + 1)) && (
                                          <Grid item xs={12}>
                                            <Alert severity="info" sx={{ mb: 1 }}>No hotels assigned for Day {dayIndex + 1}.</Alert>
                                          </Grid>
                                        )}
                                    </Grid>
                                  </Box>

                                  {/* Attractions Section */}
                                  <Box sx={{ mb: 4 }}>
                                    <Box sx={{
                                      display: 'flex',
                                      alignItems: 'center',
                                      mb: 2,
                                      pb: 1,
                                      borderBottom: '1px solid',
                                      borderColor: 'divider'
                                    }}>
                                      <Box sx={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        bgcolor: 'success.main',
                                        color: 'white',
                                        borderRadius: '50%',
                                        width: 28,
                                        height: 28,
                                        mr: 1
                                      }}>
                                        <AttractionsIcon sx={{ fontSize: 16 }} />
                                      </Box>
                                      <Typography variant="subtitle1" fontWeight="bold">Attractions</Typography>
                                    </Box>

                                    <Grid container spacing={2} sx={{ mt: 2 }}>
                                      {/* Filter attractions to only show those not booked on other days */}
                                      {(selectedAttractions && selectedAttractions.length > 0 ?
                                        selectedAttractions.filter(attraction => {
                                          const attractionId = getEntityId(attraction, null, 'attraction');
                                          // Show if not booked anywhere OR booked for this specific day
                                          return !isAttractionBooked(attractionId) ||
                                            isAttractionBookedForDay(attractionId, dayIndex);
                                        }) : []
                                      ).length > 0 ? (
                                        selectedAttractions
                                          .filter(attraction => {
                                            const attractionId = getEntityId(attraction, null, 'attraction');
                                            // Show if not booked anywhere OR booked for this specific day
                                            return !isAttractionBooked(attractionId) ||
                                              isAttractionBookedForDay(attractionId, dayIndex);
                                          })
                                          .map((attraction, idx) => {
                                            const attractionId = getEntityId(attraction, idx, 'attraction');
                                            const isBooked = isAttractionBookedForDay(attractionId, dayIndex);

                                            return (
                                              <Grid item xs={12} sm={6} md={4} key={`attraction-${dayIndex}-${idx}`}>
                                                <Card
                                                  variant="outlined"
                                                  sx={{
                                                    height: '100%',
                                                    transition: 'all 0.2s ease',
                                                    border: '1px solid',
                                                    borderColor: 'divider',
                                                    '&:hover': {
                                                      boxShadow: 2,
                                                      borderColor: 'success.light'
                                                    }
                                                  }}
                                                >
                                                  <Box
                                                    component="img"
                                                    src={attraction.image || '/img/attractions/1.png'}
                                                    alt={attraction.name || attraction.title}
                                                    sx={{
                                                      width: '100%',
                                                      height: 120,
                                                      objectFit: 'cover',
                                                      borderBottom: '1px solid',
                                                      borderColor: 'divider'
                                                    }}
                                                  />
                                                  <CardContent sx={{ p: 2 }}>
                                                    <Typography variant="subtitle1" fontWeight="bold" noWrap>
                                                      {attraction.name || attraction.title}
                                                    </Typography>
                                                    <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                                                      <LocationOnIcon sx={{ fontSize: 16, color: 'text.secondary', mr: 0.5 }} />
                                                      <Typography variant="caption" color="text.secondary" noWrap>
                                                        {attraction.address || attraction.location || 'Location information unavailable'}
                                                      </Typography>
                                                    </Box>

                                                    {/* Display transfer information */}
                                                    {(() => {
                                                      const transferType = getAttractionTransferType(attractionId);
                                                      if (!transferType) return null;

                                                      return (
                                                        <Box sx={{ mt: 0.5, mb: 1, display: 'flex', alignItems: 'center' }}>
                                                          <span role="img" aria-label="transfer" style={{ marginRight: '4px', fontSize: '0.75rem' }}>
                                                            {transferType === 'bidirectional' ? '🔄' : '🚕'}
                                                          </span>
                                                          <Typography variant="caption" color="success.main" fontWeight="medium">
                                                            {transferType === 'bidirectional' ? 'Round trip transfer' : 'One-way transfer'}
                                                          </Typography>
                                                        </Box>
                                                      );
                                                    })()}

                                                    <Box sx={{
                                                      mt: 1,
                                                      display: 'flex',
                                                      alignItems: 'center',
                                                      px: 1.5,
                                                      py: 0.5,
                                                      bgcolor: 'success.light',
                                                      color: 'success.contrastText',
                                                      borderRadius: 1,
                                                      fontSize: '0.75rem',
                                                      fontWeight: 'medium'
                                                    }}>
                                                      <CalendarTodayIcon sx={{ fontSize: 14, mr: 0.5 }} />
                                                      Assigned by Tour Operator
                                                    </Box>
                                                  </CardContent>
                                                </Card>
                                              </Grid>
                                            );
                                          })
                                      ) : (
                                        <Grid item xs={12}>
                                          <Alert severity="info" sx={{ mb: 1 }}>
                                            {selectedAttractions && selectedAttractions.length > 0
                                              ? 'All attractions have been booked on other days.'
                                              : 'No attractions available for this day.'}
                                          </Alert>
                                        </Grid>
                                      )}
                                    </Grid>
                                  </Box>

                                  {/* Guides Section */}
                                  <Box sx={{ mb: 2 }}>
                                    <Box sx={{
                                      display: 'flex',
                                      alignItems: 'center',
                                      mb: 2,
                                      pb: 1,
                                      borderBottom: '1px solid',
                                      borderColor: 'divider'
                                    }}>
                                      <Box sx={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        bgcolor: 'info.main',
                                        color: 'white',
                                        borderRadius: '50%',
                                        width: 28,
                                        height: 28,
                                        mr: 1
                                      }}>
                                        <PersonIcon sx={{ fontSize: 16 }} />
                                      </Box>
                                      <Typography variant="subtitle1" fontWeight="bold">Tour Guides</Typography>
                                    </Box>

                                    <Grid container spacing={2} sx={{ mt: 2 }}>
                                      {selectedGuides && selectedGuides.length > 0 ? (
                                        selectedGuides.map((guide, idx) => {
                                          const guideId = getEntityId(guide, idx, 'guide');

                                          return (
                                            <Grid item xs={12} sm={6} md={4} key={`guide-${dayIndex}-${idx}`}>
                                              <Card
                                                variant="outlined"
                                                sx={{
                                                  height: '100%',
                                                  transition: 'all 0.2s ease',
                                                  position: 'relative',
                                                  border: '1px solid',
                                                  borderColor: 'divider',
                                                  '&:hover': {
                                                    boxShadow: 2,
                                                    borderColor: 'primary.light'
                                                  }
                                                }}
                                              >
                                                <Box sx={{
                                                  display: 'flex',
                                                  justifyContent: 'center',
                                                  padding: 2,
                                                  borderBottom: '1px solid',
                                                  borderColor: 'divider'
                                                }}>
                                                  <Avatar
                                                    src={guide.image || '/img/team/1.png'}
                                                    alt={guide.name}
                                                    sx={{ width: 80, height: 80 }}
                                                  />
                                                </Box>
                                                <CardContent sx={{ p: 2 }}>
                                                  <Typography variant="subtitle1" fontWeight="bold" align="center" noWrap>
                                                    {guide.name}
                                                  </Typography>
                                                  <Typography variant="caption" color="text.secondary" align="center" sx={{ display: 'block' }}>
                                                    {guide.language || 'English'} • {guide.experience || '5'} years exp.
                                                  </Typography>

                                                  {/* Replace selection with assigned label */}
                                                  <Box sx={{
                                                    mt: 1.5,
                                                    display: 'flex',
                                                    alignItems: 'center',
                                                    justifyContent: 'center',
                                                    px: 1.5,
                                                    py: 0.5,
                                                    bgcolor: 'info.light',
                                                    color: 'info.contrastText',
                                                    borderRadius: 1,
                                                    fontSize: '0.75rem',
                                                    fontWeight: 'medium'
                                                  }}>
                                                    <CalendarTodayIcon sx={{ fontSize: 14, mr: 0.5 }} />
                                                    Assigned by Tour Operator
                                                  </Box>
                                                </CardContent>
                                              </Card>
                                            </Grid>
                                          );
                                        })
                                      ) : (
                                        <Grid item xs={12}>
                                          <Alert severity="info" sx={{ mb: 1 }}>No guides available for this day.</Alert>
                                        </Grid>
                                      )}
                                    </Grid>
                                  </Box>
                                </ContentSection>
                              </Card>

                              {/* Day navigation controls */}
                              <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 0.5, mb: 1 }}>
                                <NavigationArrow
                                  direction="prev"
                                  onClick={() => dayIndex > 0 && scrollToDay(dayIndex - 1)}
                                  disabled={dayIndex === 0}
                                  label="Previous Day"
                                />
                                <NavigationArrow
                                  direction="next"
                                  onClick={() => dayIndex < (packageDetails.duration_days - 1) && scrollToDay(dayIndex + 1)}
                                  disabled={dayIndex === (packageDetails.duration_days - 1)}
                                  label="Next Day"
                                />
                              </Box>
                            </Box>
                          );
                        })}
                    </Box>
                  </Grid>

                  {/* Right column - Price summary - Sticky */}
                  <Grid item xs={12} md={4}>
                    <Box
                      sx={{
                        position: 'sticky',
                        top: 100,
                      }}
                    >
                      {/* Price Summary */}
                      <PackagePricing
                        packageData={packageDetails}
                        selectedHotels={selectedHotels}
                        selectedAttractions={selectedAttractions}
                        selectedGuides={selectedGuides}
                        bookedAttractions={bookedAttractions}
                        selectedHotelId={selectedHotelId}
                        selectedGuideId={selectedGuideId}
                        itineraryDates={getItineraryDates()}
                      // bookedAttractions={bookedAttractions}
                      // selectedHotelId={selectedHotelId}
                      // selectedGuideId={selectedGuideId}
                      // itineraryDates={getItineraryDates()}
                      />
                    </Box>
                  </Grid>
                </Grid>
              </Grid>
            </Grid>
          </Box>
        )}

        {/* Policies Tab */}
        {mainTab === 1 && (
          <Box sx={{ mt: 3 }}>
            <TermsConditions packageData={packageDetails} />
            <Box sx={{ mt: 3 }}>
              <InclusionsExclusions packageData={packageDetails} />
            </Box>
          </Box>
        )}

        {/* Summary Tab */}
        {mainTab === 2 && (
          <Box sx={{ mt: 3 }}>
            <PackageOverview packageData={packageDetails} />
            {/* <Box sx={{ mt: 3 }}>
              <PackagePricing 
                packageData={packageDetails} 
                selectedHotels={selectedHotels}
                selectedAttractions={selectedAttractions}
                selectedRestaurants={selectedRestaurants}
                selectedGuides={selectedGuides}
              />
            </Box> */}
          </Box>
        )}
      </Box>

      {/* Selection Modals */}
      <SelectionModal
        open={hotelsModalOpen}
        onClose={() => setHotelsModalOpen(false)}
        title="Select Hotels"
        items={availableHotels}
        selectedItems={selectedHotels}
        onSelectionChange={setSelectedHotels}
        renderItem={HotelItemRenderer}
        loading={isLoading}
      />

      <SelectionModal
        open={attractionsModalOpen}
        onClose={() => setAttractionsModalOpen(false)}
        title="Select Attractions"
        items={availableAttractions}
        selectedItems={selectedAttractions}
        onSelectionChange={setSelectedAttractions}
        renderItem={AttractionItemRenderer}
        loading={isLoading}
      />

      {/* 
      {/* 
      <SelectionModal
        open={restaurantsModalOpen}
        onClose={() => setRestaurantsModalOpen(false)}
        title="Select Restaurants"
        items={availableRestaurants}
        selectedItems={selectedRestaurants}
        onSelectionChange={setSelectedRestaurants}
        renderItem={RestaurantItemRenderer}
        loading={isLoading}
      />
      */}


      <SelectionModal
        open={guidesModalOpen}
        onClose={() => setGuidesModalOpen(false)}
        title="Select Tour Guides"
        items={availableGuides}
        selectedItems={selectedGuides}
        onSelectionChange={setSelectedGuides}
        renderItem={GuideItemRenderer}
        loading={isLoading}
      />
    </Container>
  );
};

export default PackageDetailsContainer;
