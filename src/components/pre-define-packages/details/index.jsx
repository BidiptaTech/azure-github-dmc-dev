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
import GuideDetails from './GuideDetails';
import PackagePricing from './PackagePricing';
import TermsConditions from './TermsConditions';

// Import selection components
import SelectionModal from './selection-components/SelectionModal';
import HotelItemRenderer from './selection-components/HotelItemRenderer';
import AttractionItemRenderer from './selection-components/AttractionItemRenderer';
/* import RestaurantItemRenderer from './selection-components/RestaurantItemRenderer'; */
/* import RestaurantItemRenderer from './selection-components/RestaurantItemRenderer'; */
import GuideItemRenderer from './selection-components/GuideItemRenderer';

// Import API endpoints
import { endpoints } from '../../../services/api';
import { formatDate, getItineraryDayDate } from './shared-date-utils';
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
  const [guidesModalOpen, setGuidesModalOpen] = useState(false);
  
  // State for available items (items fetched from API)
  const [availableHotels, setAvailableHotels] = useState([]);
  const [availableAttractions, setAvailableAttractions] = useState([]);
  /* const [availableRestaurants, setAvailableRestaurants] = useState([]); */
  /* const [availableRestaurants, setAvailableRestaurants] = useState([]); */
  const [availableGuides, setAvailableGuides] = useState([]);
  
  // State for selected items
  const [selectedHotels, setSelectedHotels] = useState([]);
  const [selectedAttractions, setSelectedAttractions] = useState([]);
  /* const [selectedRestaurants, setSelectedRestaurants] = useState([]); */
  /* const [selectedRestaurants, setSelectedRestaurants] = useState([]); */
  const [selectedGuides, setSelectedGuides] = useState([]);
  
  // State for globally selected hotel and guide across all days
  const [selectedHotelId, setSelectedHotelId] = useState(null);
  const [selectedGuideId, setSelectedGuideId] = useState(null);
  
  // Track booked attractions by day - { attractionId: dayIndex }
  const [bookedAttractions, setBookedAttractions] = useState({});
  
  // Track transport options
  const [entryPortTransfer, setEntryPortTransfer] = useState(0);
  const [exitPortTransfer, setExitPortTransfer] = useState(0);
  const [attractionWithTransfer, setAttractionWithTransfer] = useState({});
  
  // Track active service tab for each day
  const [dayServiceTabs, setDayServiceTabs] = useState({});
  
  // Ref for content scrolling
  // State for globally selected hotel and guide across all days
  
 
  
  // Track booked attractions by day - { attractionId: dayIndex }
 
  
  // Track transport options
  
  
  // Track active service tab for each day
 
  
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
  
  const { packageDetails, loadingDetails, errorDetails, searchParams } = useSelector(state => state.prePackages);
  // const { packageDetails, loadingDetails, errorDetails, searchParams } = useSelector(state => state.prePackages);
  
  useEffect(() => {
    if (id) {
      dispatch(fetchPackageDetails(id));
    }
  }, [dispatch, id]);
  
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
      
      console.log('Setting package details in parent component:', {
        originalDate: packageDetails.date,
        originalStartDate: packageDetails.start_date,
        searchParamsDate: searchParams?.date,
        finalDate: updatedPackageDetails.date,
        finalStartDate: updatedPackageDetails.start_date,
        searchParams
      });
      
      // Create refs for each day in the itinerary
      if (updatedPackageDetails.duration_days) {
        dayRefs.current = Array(updatedPackageDetails.duration_days)
      if (updatedPackageDetails.duration_days) {
        dayRefs.current = Array(updatedPackageDetails.duration_days)
          .fill()
          .map((_, i) => dayRefs.current[i] || React.createRef());
      }
      
      // Set selected items from package details
      setSelectedHotels(updatedPackageDetails.selected_hotels || []);
      setSelectedAttractions(updatedPackageDetails.selected_attractions || []);
      /* setSelectedRestaurants(updatedPackageDetails.selected_restaurants || []); */
      setSelectedHotels(updatedPackageDetails.selected_hotels || []);
      setSelectedAttractions(updatedPackageDetails.selected_attractions || []);
      /* setSelectedRestaurants(updatedPackageDetails.selected_restaurants || []); */
      
      // Check for both property names for guides (selected_guides or selected_guide)
      // const guides = updatedPackageDetails.selected_guides || updatedPackageDetails.selected_guide || [];
      const guides = updatedPackageDetails.selected_guides || updatedPackageDetails.selected_guide || [];
      
      // If no guides are found, add a sample guide for testing
      if (!guides.length) {
        const sampleGuides = [
          {
            id: 'g1',
            name: 'John Smith',
            image: '/img/team/1.png',
            language: 'English, Spanish',
            experience: 5,
            specialization: 'Cultural Tours',
            certified: true,
            rating: 4.8,
            reviews: 120
          }
        ];
        setSelectedGuides(sampleGuides);
      } else {
        setSelectedGuides(guides);
      }
      
      // Update PackageItinerary component props with the updated package details including date
      setCurrentPackageDetails(updatedPackageDetails);
      
      // Update PackageItinerary component props with the updated package details including date
      setCurrentPackageDetails(updatedPackageDetails);
    }
  }
  }, [packageDetails, searchParams]);

  // }, [packageDetails, searchParams]);
  
  // Add loading state
  const [isLoading, setIsLoading] = useState(false);
  
  // Update the handlers for opening modals
  const handleOpenHotelsModal = async () => {
    try {
      // Show loading state
      setIsLoading(true);
      
      // If we already have available hotels, just open the modal
      if (availableHotels.length > 0) {
        setHotelsModalOpen(true);
        setIsLoading(false);
        return;
      }
      
      // Get the destination from package details for API request
      const destination = packageDetails.destination || packageDetails.city || '';
      
      // Fetch hotels from API
      const response = await endpoints.fetchHotels({
        city: destination,
        // Add any other required parameters
        limit: 10 // Limit the number of results
      });
      
      // Process API response
      let hotels = [];
      
      if (response.data && Array.isArray(response.data)) {
        hotels = response.data;
      } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
        hotels = response.data.data;
      } else {
        console.warn('No hotels found in API response');
        hotels = [];
      }
      
      // Make sure the selected hotel is included in the available hotels
      if (selectedHotels.length > 0) {
        const selectedHotel = selectedHotels[0];
        const selectedHotelExists = hotels.some(
          hotel => (hotel.id === selectedHotel.id) || (hotel._id === selectedHotel._id)
        );
        
        if (!selectedHotelExists) {
          hotels = [...hotels, selectedHotel];
        }
      }
      
      setAvailableHotels(hotels);
      setHotelsModalOpen(true);
    } catch (error) {
      console.error('Error fetching hotels:', error);
      // Fallback to selected hotels in case of error
      setAvailableHotels(selectedHotels.length > 0 ? selectedHotels : []);
      setHotelsModalOpen(true);
    } finally {
      setIsLoading(false);
    }
  };
  
  const handleOpenAttractionsModal = async () => {
    try {
      // Show loading state
      setIsLoading(true);
      
      // If we already have available attractions, just open the modal
      if (availableAttractions.length > 0) {
        setAttractionsModalOpen(true);
        setIsLoading(false);
        return;
      }
      
      // Get the destination from package details for API request
      const destination = packageDetails.destination || packageDetails.city || '';
      
      // Fetch attractions from API
      const response = await endpoints.fetchAttractions({
        city: destination,
        // Add any other required parameters
        limit: 10 // Limit the number of results
      });
      
      // Process API response
      let attractions = [];
      
      if (response.data && Array.isArray(response.data)) {
        attractions = response.data;
      } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
        attractions = response.data.data;
      } else {
        console.warn('No attractions found in API response');
        attractions = [];
      }
      
      // Make sure the selected attraction is included in the available attractions
      if (selectedAttractions.length > 0) {
        const selectedAttraction = selectedAttractions[0];
        const selectedAttractionExists = attractions.some(
          attraction => (attraction.id === selectedAttraction.id) || (attraction._id === selectedAttraction._id)
        );
        
        if (!selectedAttractionExists) {
          attractions = [...attractions, selectedAttraction];
        }
      }
      
      setAvailableAttractions(attractions);
      setAttractionsModalOpen(true);
    } catch (error) {
      console.error('Error fetching attractions:', error);
      // Fallback to selected attractions in case of error
      setAvailableAttractions(selectedAttractions.length > 0 ? selectedAttractions : []);
      setAttractionsModalOpen(true);
    } finally {
      setIsLoading(false);
    }
  };
  
  /* 
  /* 
  const handleOpenRestaurantsModal = async () => {
    try {
      // Show loading state
      setIsLoading(true);
      
      // If we already have available restaurants, just open the modal
      if (availableRestaurants.length > 0) {
        setRestaurantsModalOpen(true);
        setIsLoading(false);
        return;
      }
      
      // Get the destination from package details for API request
      const destination = packageDetails.destination || packageDetails.city || '';
      
      // Fetch restaurants from API
      const response = await endpoints.fetchRestaurants({
        city: destination,
        // Add any other required parameters
        limit: 10 // Limit the number of results
      });
      
      // Process API response
      let restaurantItems = [];
      
      if (response.data && Array.isArray(response.data)) {
        restaurantItems = response.data;
      } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
        restaurantItems = response.data.data;
      } else {
        console.warn('No restaurants found in API response');
        restaurantItems = [];
      }
      
      // Make sure the selected restaurant is included in the available restaurants
      if (selectedRestaurants.length > 0) {
        const selectedRestaurant = selectedRestaurants[0];
        const isSelectedIncluded = restaurantItems.some(
          item => (item.id === selectedRestaurant.id) || (item._id === selectedRestaurant._id)
        );
        
        if (!isSelectedIncluded) {
          restaurantItems = [...restaurantItems, selectedRestaurant];
        }
      }
      
      setAvailableRestaurants(restaurantItems);
      setRestaurantsModalOpen(true);
    } catch (error) {
      console.error('Error fetching restaurants:', error);
      // Fallback to selected restaurants in case of error
      setAvailableRestaurants(selectedRestaurants.length > 0 ? selectedRestaurants : []);
      setRestaurantsModalOpen(true);
    } finally {
      setIsLoading(false);
    }
  };
  */
  
  
  const handleOpenGuidesModal = async () => {
    try {
      // Show loading state
      setIsLoading(true);
      
      // If we already have available guides, just open the modal
      if (availableGuides.length > 0) {
        setGuidesModalOpen(true);
        setIsLoading(false);
        return;
      }
      
      // Get the destination from package details for API request
      const destination = packageDetails.destination || packageDetails.city || '';
      
      // Fetch guides from API using the fetchGuides endpoint
      try {
        const response = await endpoints.fetchGuides({
          city: destination,
          limit: 10 // Limit the number of results
        });
        
        // Process API response
        let guides = [];
        
        if (response.data && Array.isArray(response.data)) {
          guides = response.data;
        } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
          guides = response.data.data;
        } else {
          console.warn('No guides found in API response');
          guides = [];
        }
        
        // Make sure the selected guide is included in the available guides
        if (selectedGuides.length > 0) {
          const selectedGuide = selectedGuides[0];
          const selectedGuideExists = guides.some(
            guide => (guide.id === selectedGuide.id) || (guide._id === selectedGuide._id)
          );
          
          if (!selectedGuideExists) {
            guides = [...guides, selectedGuide];
          }
        }
        
        setAvailableGuides(guides);
      } catch (apiError) {
        console.warn('Guide API endpoint error:', apiError);
        // If API fails, just use the currently selected guides
        setAvailableGuides(selectedGuides.length > 0 ? selectedGuides : []);
      }
      
      setGuidesModalOpen(true);
    } catch (error) {
      console.error('Error fetching guides:', error);
      // Fallback to selected guides in case of error
      setAvailableGuides(selectedGuides.length > 0 ? selectedGuides : []);
      setGuidesModalOpen(true);
    } finally {
      setIsLoading(false);
    }
  };
  
  // Handler for booking/unbooking an attraction for a specific day
  const handleAttractionToggle = (attractionId, dayIndex, isChecked) => {
    console.log(`Attraction ${attractionId} on day ${dayIndex + 1} ${isChecked ? 'booked' : 'unbooked'}`);
    
    setBookedAttractions(prev => {
      const updated = {...prev};
      
      if (isChecked) {
        // Book the attraction for this day
        updated[attractionId] = dayIndex;
      } else {
        // Remove the booking if it exists
        if (updated[attractionId] === dayIndex) {
          delete updated[attractionId];
        }
      }
      
      return updated;
    });
  };
  
  // Handler for toggling attraction transfer
  const handleAttractionTransferToggle = (attractionId, isChecked) => {
    console.log(`Attraction transfer for ${attractionId} ${isChecked ? 'enabled' : 'disabled'}`);
    
    setAttractionWithTransfer(prev => {
      const updated = {...prev};
      
      if (isChecked) {
        // Add transport for this attraction
        updated[attractionId] = 1;
      } else {
        // Remove transport if it exists
        delete updated[attractionId];
      }
      
      return updated;
    });
  };
  
  // Helper to check if an attraction has transfer
  const hasAttractionTransfer = (attractionId) => {
    return attractionWithTransfer[attractionId] === 1;
  };
  
  // Helper to check if an attraction is already booked
  const isAttractionBooked = (attractionId) => {
    return attractionId in bookedAttractions;
  };
  
  // Helper to check if an attraction is booked for a specific day
  const isAttractionBookedForDay = (attractionId, dayIndex) => {
    return bookedAttractions[attractionId] === dayIndex;
  };
  
  // Handlers for hotel and guide selection
  const handleHotelSelect = (hotelId) => {
    console.log(`Selected hotel: ${hotelId}`);
    setSelectedHotelId(hotelId);
  };
  
  const handleGuideSelect = (guideId) => {
    console.log(`Selected guide: ${guideId}`);
    setSelectedGuideId(guideId);
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
    return entity.id || entity._id || `${prefix}-${index}`;
  };
  
  // Helper to get navigation details for sections
  const getNavigationDetails = (currentSection) => {
    const sections = sectionTabs.map(tab => tab.id);
    const currentIndex = sections.findIndex(s => s === currentSection);
    
    return {
      prev: currentIndex > 0 ? {
        id: sections[currentIndex - 1],
        label: sectionTabs[currentIndex - 1].label,
        icon: sectionTabs[currentIndex - 1].icon
      } : null,
      next: currentIndex < sections.length - 1 ? {
        id: sections[currentIndex + 1],
        label: sectionTabs[currentIndex + 1].label,
        icon: sectionTabs[currentIndex + 1].icon
      } : null
    };
  };
  
  // Helper to get the active tab for a day (default to 'hotels')
  const getActiveDayServiceTab = (dayIndex) => {
    return dayServiceTabs[dayIndex] || 'hotels';
  };
  
  // Handle tab change for a specific day
  const handleDayServiceTabChange = (dayIndex, newValue) => {
    setDayServiceTabs(prev => ({
      ...prev,
      [dayIndex]: newValue
    }));
  };
  
  // Add these event handlers for entry/exit port
  const handleEntryPortTransfer = (isChecked) => {
    setEntryPortTransfer(isChecked ? 1 : 0);
  };
  
  const handleExitPortTransfer = (isChecked) => {
    setExitPortTransfer(isChecked ? 1 : 0);
  };
  
  // No need for handlers since these are not user-selectable
  
  // Helper to check if an attraction has transfer available and what type
  const getAttractionTransferType = (attractionId) => {
    // Check if attraction transfer is available in package data
    if (packageDetails?.attractions_with_transfer) {
      const transferValue = packageDetails.attractions_with_transfer[attractionId];
      if (transferValue === 2) return 'bidirectional';
      if (transferValue === 1 || transferValue === true) return 'unidirectional';
    }
    
    // Check if package data has general attraction_with_transfer flag
    if (packageDetails?.attraction_with_transfer === 2) {
      return 'bidirectional';
    }
    if (packageDetails?.attraction_with_transfer === 1 || 
        packageDetails?.attraction_with_transfer === true) {
      return 'unidirectional';
    }
    
    // Default to checking the attraction object itself
    const attraction = selectedAttractions.find(a => (a.id === attractionId || a._id === attractionId));
    if (attraction?.with_transfer === 2) return 'bidirectional';
    if (attraction?.with_transfer === 1 || 
        attraction?.with_transfer === true ||
        attraction?.transfer_available === true) return 'unidirectional';
    
    return null; // No transfer available
  };

  // Helper to check if an attraction has any transfer available
  const hasAttractionTransferAvailable = (attractionId) => {
    return getAttractionTransferType(attractionId) !== null;
  };
  
  // Helper to check if entry port transfer is available
  const hasEntryPortTransfer = () => {
    return packageDetails?.entry_port_transfer === 1 || 
           packageDetails?.entry_port_transfer === true ||
           packageDetails?.entry_port === 1 || 
           packageDetails?.entry_port === true || 
           packageDetails?.has_entry_port_transfer === true;
  };
  
  // Helper to check if exit port transfer is available
  const hasExitPortTransfer = () => {
    return packageDetails?.exit_port_transfer === 1 || 
           packageDetails?.exit_port_transfer === true ||
           packageDetails?.exit_port === 1 || 
           packageDetails?.exit_port === true || 
           packageDetails?.has_exit_port_transfer === true;
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
              <Grid item xs={12} md={3} lg={2}>
                <PackageItinerary 
                  packageDetails={currentPackageDetails || packageDetails}
                  activeDay={activeDay}
                  setActiveDay={setActiveDay}
                  contentRef={contentRef}
                  dayRefs={dayRefs}
                />
              
              </Grid>
              
              {/* Content Columns - Container for main content and price */}
              <Grid item xs={12} md={9} lg={10}>
                <Grid container spacing={3}>
                  {/* Middle column - Scrollable main content */}
                  <Grid item xs={12} md={8}>
                    <Box 
                      ref={contentRef}
                      sx={{ 
                        maxHeight: 'calc(100vh - 200px)', 
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
                        const dayDescription = currentDetails.itinerary && currentDetails.itinerary[dayIndex]
                          ? currentDetails.itinerary[dayIndex].title.split(' - ')[1]
                          : dayIndex === 0 ? 'Arrival' : dayIndex === (currentDetails.duration_days - 1) ? 'Departure' : 'Exploration';
                        
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
                                {/* Service Tabs */}
                                <Tabs 
                                  value={getActiveDayServiceTab(dayIndex)}
                                  onChange={(e, newValue) => handleDayServiceTabChange(dayIndex, newValue)}
                                  variant="fullWidth"
                                  sx={{ 
                                    borderBottom: 1, 
                                    borderColor: 'divider',
                                    mb: 3
                                  }}
                                >
                                  <Tab 
                                    label={
                                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
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
                                        <Typography>Hotels</Typography>
                                      </Box>
                                    } 
                                    value="hotels" 
                                  />
                                  <Tab 
                                    label={
                                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
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
                                        <Typography>Attractions</Typography>
                                      </Box>
                                    } 
                                    value="attractions" 
                                  />
                                  {/* 
                                  <Tab 
                                    label={
                                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                                        <Box sx={{
                                          display: 'flex',
                                          alignItems: 'center',
                                          justifyContent: 'center',
                                          bgcolor: 'warning.main',
                                          color: 'white',
                                          borderRadius: '50%',
                                          width: 28,
                                          height: 28,
                                          mr: 1
                                        }}>
                                          <RestaurantIcon sx={{ fontSize: 16 }} />
                                        </Box>
                                        <Typography>Restaurants</Typography>
                                      </Box>
                                    } 
                                    value="restaurants" 
                                  />
                                  */}
                                  <Tab 
                                    label={
                                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
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
                                        <Typography>Guides</Typography>
                                      </Box>
                                    } 
                                    value="guides" 
                                  />
                                </Tabs>
                                
                                {/* Hotels Tab Panel */}
                                {getActiveDayServiceTab(dayIndex) === 'hotels' && (
                                  <Box sx={{ mt: 5, ml: 1 }}>
                                    <Grid container spacing={2}>
                                      {/* Entry/Exit port options for first/last day */}
                                      {dayIndex === 0 && hasEntryPortTransfer() && (
                                        <Grid item xs={12}>
                                          <Box sx={{ display: 'flex', alignItems: 'center', mb: 2, p: 2, bgcolor: 'primary.light', borderRadius: 2 }}>
                                            <Typography variant="subtitle2" fontWeight="medium" sx={{ display: 'flex', alignItems: 'center' }}>
                                              <span role="img" aria-label="info" style={{ marginRight: '8px' }}>✅</span>
                                              Arrival transfer from entry port (airport/station) is included
                                            </Typography>
                                          </Box>
                                        </Grid>
                                      )}
                                      
                                      {dayIndex === (packageDetails.duration_days - 1) && hasExitPortTransfer() && (
                                        <Grid item xs={12}>
                                          <Box sx={{ display: 'flex', alignItems: 'center', mb: 2, p: 2, bgcolor: 'primary.light', borderRadius: 2 }}>
                                            <Typography variant="subtitle2" fontWeight="medium" sx={{ display: 'flex', alignItems: 'center' }}>
                                              <span role="img" aria-label="info" style={{ marginRight: '8px' }}>✅</span>
                                              Departure transfer to exit port (airport/station) is included
                                            </Typography>
                                          </Box>
                                        </Grid>
                                      )}
                                      
                                      {selectedHotels && selectedHotels.length > 0 ? (
                                        <RadioGroup
                                          name={`hotels-day-${dayIndex}`}
                                          value={selectedHotelId || ''}
                                          onChange={(e) => handleHotelSelect(e.target.value)}
                                          sx={{ width: '100%' }}
                                        >
                                          <Grid container spacing={2}>
                                            {selectedHotels.map((hotel, idx) => (
                                              <Grid item xs={12} sm={6} md={4} key={`hotel-${dayIndex}-${idx}`}>
                                                <Card 
                                                  variant="outlined" 
                                                  sx={{ 
                                                    height: '100%',
                                                    transition: 'all 0.2s ease',
                                                    position: 'relative',
                                                    border: selectedHotelId === getEntityId(hotel, idx, 'hotel') ? '2px solid' : '1px solid',
                                                    borderColor: selectedHotelId === getEntityId(hotel, idx, 'hotel') ? 'primary.main' : 'divider',
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
                                                        {hotel.address || hotel.location || 'Location information unavailable'}
                                                      </Typography>
                                                    </Box>
                                                    <FormControlLabel
                                                      control={
                                                        <Radio 
                                                          size="small" 
                                                          value={getEntityId(hotel, idx, 'hotel')}
                                                          color="primary"
                                                          checked={selectedHotelId === getEntityId(hotel, idx, 'hotel')}
                                                        />
                                                      }
                                                      label="Select for all days"
                                                      sx={{ 
                                                        mt: 0.5, 
                                                        '& .MuiFormControlLabel-label': { 
                                                          fontSize: '0.8rem',
                                                          fontWeight: selectedHotelId === getEntityId(hotel, idx, 'hotel') ? 'bold' : 'normal',
                                                        }
                                                      }}
                                                    />
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
                                    </Grid>
                                  </Box>
                                )}

                                {/* Attractions Tab Panel */}
                                {getActiveDayServiceTab(dayIndex) === 'attractions' && (
                                  <Box sx={{ mt: 5, ml: 1 }}>
                                    <Grid container spacing={2}>
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
                                                    border: isBooked ? '2px solid' : '1px solid',
                                                    borderColor: isBooked ? 'success.main' : 'divider',
                                                    '&:hover': {
                                                      boxShadow: 2,
                                                      borderColor: isBooked ? 'success.main' : 'success.light'
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
                                                    <FormControlLabel
                                                      control={
                                                        <Checkbox 
                                                          size="small" 
                                                          color="success"
                                                          checked={isBooked}
                                                          onChange={(e) => handleAttractionToggle(
                                                            attractionId, 
                                                            dayIndex, 
                                                            e.target.checked
                                                          )}
                                                        />
                                                      }
                                                      label={isBooked ? "Booked for this day" : "Book for this day"}
                                                      sx={{ 
                                                        mt: 0.5,
                                                        '& .MuiFormControlLabel-label': { 
                                                          fontSize: '0.8rem',
                                                          color: isBooked ? 'success.main' : 'text.secondary',
                                                          fontWeight: isBooked ? 'bold' : 'normal'
                                                        }
                                                      }}
                                                    />
                                                    {(() => {
                                                      const transferType = getAttractionTransferType(attractionId);
                                                      if (!transferType) return null;
                                                      
                                                      return (
                                                        <Box sx={{ mt: 0.5, ml: 2, display: 'flex', alignItems: 'center' }}>
                                                          <span role="img" aria-label="transfer" style={{ marginRight: '4px', fontSize: '0.75rem' }}>
                                                            {transferType === 'bidirectional' ? '🔄' : '🚕'}
                                                          </span>
                                                          <Typography variant="caption" color="success.main" fontWeight="medium">
                                                            {transferType === 'bidirectional' ? 'Round trip transfer' : 'One-way transfer'}
                                                          </Typography>
                                                        </Box>
                                                      );
                                                    })()}
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
                                )}

                                {/* Guides Tab Panel */}
                                {getActiveDayServiceTab(dayIndex) === 'guides' && (
                                  <Box sx={{ mt: 5, ml: 1 }}>
                                    <Grid container spacing={2}>
                                      {selectedGuides && selectedGuides.length > 0 ? (
                                        selectedGuides.map((guide, idx) => {
                                          const guideId = getEntityId(guide, idx, 'guide');
                                          console.log(`Rendering guide ${idx}, ID:`, guideId, "Selected ID:", selectedGuideId, "Checked:", selectedGuideId === guideId);
                                          
                                          return (
                                            <Grid item xs={12} sm={6} md={4} key={`guide-${dayIndex}-${idx}`}>
                                              <Card 
                                                variant="outlined" 
                                                sx={{ 
                                                  height: '100%',
                                                  transition: 'all 0.2s ease',
                                                  border: selectedGuideId === guideId ? '2px solid' : '1px solid',
                                                  borderColor: selectedGuideId === guideId ? 'info.main' : 'divider',
                                                  '&:hover': {
                                                    boxShadow: 2,
                                                    borderColor: 'info.light'
                                                  }
                                                }}
                                                onClick={() => {
                                                  console.log(`Card clicked for guide ${idx}, ID: ${guideId}`);
                                                  handleGuideSelect(guideId);
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
                                                  
                                                  {/* Individual radio button with explicit click handler */}
                                                  <Box sx={{ mt: 1, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                                                    <Radio 
                                                      size="small" 
                                                      checked={selectedGuideId === guideId}
                                                      color="info"
                                                      onClick={(e) => {
                                                        e.stopPropagation(); // Prevent double firing with card click
                                                        console.log(`Radio clicked for guide ${idx}, ID: ${guideId}`);
                                                        handleGuideSelect(guideId);
                                                      }}
                                                    />
                                                    <Typography 
                                                      variant="body2" 
                                                      fontSize="0.8rem"
                                                      color={selectedGuideId === guideId ? 'info.main' : 'text.secondary'}
                                                      fontWeight={selectedGuideId === guideId ? 'bold' : 'normal'}
                                                    >
                                                      Select for all days
                                                    </Typography>
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
                                )}
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
