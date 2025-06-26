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

// Import components
import PackageHeader from './PackageHeader';
import PackageOverview from './PackageOverview';
import InclusionsExclusions from './InclusionsExclusions';
import PackageItinerary from './PackageItinerary';
import AccommodationDetails from './AccommodationDetails';
import AttractionsDetails from './AttractionsDetails';
import RestaurantsDetails from './RestaurantsDetails';
import GuideDetails from './GuideDetails';
import PackagePricing from './PackagePricing';
import TermsConditions from './TermsConditions';

// Import selection components
import SelectionModal from './selection-components/SelectionModal';
import HotelItemRenderer from './selection-components/HotelItemRenderer';
import AttractionItemRenderer from './selection-components/AttractionItemRenderer';
import RestaurantItemRenderer from './selection-components/RestaurantItemRenderer';
import GuideItemRenderer from './selection-components/GuideItemRenderer';

// Import API endpoints
import { endpoints } from '../../../services/api';

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
  const [activeSection, setActiveSection] = useState('hotels');
  const [activeDay, setActiveDay] = useState(0); // Track active day in itinerary
  const [currentPackageDetails, setCurrentPackageDetails] = useState(null); // Track updated package details
  
  // State for modals
  const [hotelsModalOpen, setHotelsModalOpen] = useState(false);
  const [attractionsModalOpen, setAttractionsModalOpen] = useState(false);
  const [restaurantsModalOpen, setRestaurantsModalOpen] = useState(false);
  const [guidesModalOpen, setGuidesModalOpen] = useState(false);
  
  // State for available items (items fetched from API)
  const [availableHotels, setAvailableHotels] = useState([]);
  const [availableAttractions, setAvailableAttractions] = useState([]);
  const [availableRestaurants, setAvailableRestaurants] = useState([]);
  const [availableGuides, setAvailableGuides] = useState([]);
  
  // State for selected items
  const [selectedHotels, setSelectedHotels] = useState([]);
  const [selectedAttractions, setSelectedAttractions] = useState([]);
  const [selectedRestaurants, setSelectedRestaurants] = useState([]);
  const [selectedGuides, setSelectedGuides] = useState([]);
  
  // Refs for scrolling to sections
  const itineraryRef = useRef(null);
  const hotelsRef = useRef(null);
  const attractionsRef = useRef(null);
  const restaurantsRef = useRef(null);
  const guidesRef = useRef(null);
  const policiesRef = useRef(null);
  const pricingRef = useRef(null);
  const contentRef = useRef(null);

  // Refs for days in itinerary (will be populated dynamically)
  const dayRefs = useRef([]);
  
  const handleMainTabChange = (event, newValue) => {
    setMainTab(newValue);
  };
  
  const scrollToSection = (sectionId) => {
    setActiveSection(sectionId);
    const sectionRef = {
      itinerary: itineraryRef,
      hotels: hotelsRef,
      attractions: attractionsRef,
      restaurants: restaurantsRef,
      guides: guidesRef,
      policies: policiesRef,
      pricing: pricingRef
    }[sectionId];
    
    if (sectionRef && sectionRef.current && contentRef.current) {
      // Get the content container
      const contentContainer = contentRef.current;
      const sectionElement = sectionRef.current;
      
      // Calculate the section's position relative to the content container
      const sectionRect = sectionElement.getBoundingClientRect();
      const containerRect = contentContainer.getBoundingClientRect();
      
      // Calculate the correct scroll position
      // This accounts for the section's position relative to the container
      const scrollPosition = contentContainer.scrollTop + (sectionRect.top - containerRect.top);
      
      // Add a small offset to account for any sticky headers (20px padding)
      const scrollOffset = -20;
      
      // Scroll to the calculated position
      contentContainer.scrollTo({
        top: scrollPosition + scrollOffset,
        behavior: 'smooth'
      });
    }
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
  
  // Section tabs configuration
  const sectionTabs = [
    // { id: 'itinerary', label: 'Itinerary', icon: <MapIcon /> },
    { id: 'hotels', label: 'Hotels', icon: <HotelIcon /> },
    { id: 'attractions', label: 'Attractions', icon: <AttractionsIcon /> },
    { id: 'restaurants', label: 'Restaurants', icon: <RestaurantIcon /> },
    { id: 'guides', label: 'Tour Guide', icon: <PersonIcon /> },
    // { id: 'policies', label: 'Policies', icon: <GavelIcon /> }
  ];
  
  // Render horizontal section tabs
  const renderSectionTabs = () => (
    <Paper elevation={2} sx={{ borderRadius: '8px', overflow: 'hidden' }}>
      <Tabs
        value={sectionTabs.findIndex(tab => tab.id === activeSection)}
        onChange={(e, value) => scrollToSection(sectionTabs[value].id)}
        variant="scrollable"
        scrollButtons="auto"
        aria-label="section navigation tabs"
        sx={{
          bgcolor: 'background.paper',
          '& .MuiTab-root': {
            minWidth: 'auto',
            py: 1.5
          }
        }}
      >
        {sectionTabs.map(tab => (
          <Tab 
            key={tab.id} 
            icon={tab.icon} 
            label={tab.label} 
            iconPosition="start"
          />
        ))}
      </Tabs>
    </Paper>
  );
  
  const { packageDetails, loadingDetails, errorDetails, searchParams } = useSelector(state => state.prePackages);
  
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
        date: searchParams?.date || packageDetails.date
      };
      
      // Create refs for each day in the itinerary
      if (updatedPackageDetails.duration_days) {
        dayRefs.current = Array(updatedPackageDetails.duration_days)
          .fill()
          .map((_, i) => dayRefs.current[i] || React.createRef());
      }
      
      // Set selected items from package details
      setSelectedHotels(updatedPackageDetails.selected_hotels || []);
      setSelectedAttractions(updatedPackageDetails.selected_attractions || []);
      setSelectedRestaurants(updatedPackageDetails.selected_restaurants || []);
      
      // Check for both property names for guides (selected_guides or selected_guide)
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
    }
  }, [packageDetails, searchParams]);
  
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
                {/* Section tabs that span across middle and right columns - Sticky */}
                <Box 
                  sx={{ 
                    position: 'sticky',
                    top: 20,
                    zIndex: 10,
                    mb: 2,
                    backgroundColor: 'background.default',
                    pt: 0.5
                  }}
                >
                  {renderSectionTabs()}
                </Box>
                
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
                      {/* Hotels Section */}
                      <Box ref={hotelsRef} id="hotels" sx={{ mb: 2 }}>
                        <Card elevation={1} sx={{ borderRadius: '8px', overflow: 'hidden' }}>
                          <SectionHeader 
                            icon={HotelIcon} 
                            title="Hotels"
                            // count={selectedHotels.length}
                            onChangeClick={handleOpenHotelsModal}
                          />
                          <ContentSection>
                            <AccommodationDetails packageData={{...packageDetails, selected_hotels: selectedHotels}} />
                          </ContentSection>
                        </Card>
                        
                        {/* Bottom navigation */}
                        <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 0.5, mb: 1 }}>
                          {(() => {
                            const nav = getNavigationDetails('hotels');
                            return (
                              <>
                                <NavigationArrow
                                  direction="prev"
                                  disabled={!nav.prev}
                                  label="Hotels"
                                />
                                <NavigationArrow
                                  direction="next"
                                  onClick={() => scrollToSection('attractions')}
                                  label="Attractions"
                                />
                              </>
                            );
                          })()}
                        </Box>
                      </Box>
                      
                      {/* Attractions Section */}
                      <Box ref={attractionsRef} id="attractions" sx={{ mb: 2 }}>
                        <Card elevation={1} sx={{ borderRadius: '8px', overflow: 'hidden' }}>
                          <SectionHeader 
                            icon={AttractionsIcon} 
                            title="Attractions"
                            // count={selectedAttractions.length}
                            onChangeClick={handleOpenAttractionsModal}
                          />
                          <ContentSection>
                            <AttractionsDetails packageData={{...packageDetails, selected_attractions: selectedAttractions}} />
                          </ContentSection>
                        </Card>
                        
                        {/* Bottom navigation */}
                        <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 0.5, mb: 1 }}>
                          {(() => {
                            const nav = getNavigationDetails('attractions');
                            return (
                              <>
                                <NavigationArrow
                                  direction="prev"
                                  onClick={() => scrollToSection('hotels')}
                                  label="Hotels"
                                />
                                <NavigationArrow
                                  direction="next"
                                  onClick={() => scrollToSection('restaurants')}
                                  label="Restaurants"
                                />
                              </>
                            );
                          })()}
                        </Box>
                      </Box>
                      
                      {/* Restaurants Section */}
                      <Box ref={restaurantsRef} id="restaurants" sx={{ mb: 2 }}>
                        <Card elevation={1} sx={{ borderRadius: '8px', overflow: 'hidden' }}>
                          <SectionHeader 
                            icon={RestaurantIcon} 
                            title="Restaurants"
                            // count={selectedRestaurants.length}
                            onChangeClick={handleOpenRestaurantsModal}
                          />
                          <ContentSection>
                            <RestaurantsDetails packageData={{...packageDetails, selected_restaurants: selectedRestaurants}} />
                          </ContentSection>
                        </Card>
                        
                        {/* Bottom navigation */}
                        <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 0.5, mb: 1 }}>
                          {(() => {
                            const nav = getNavigationDetails('restaurants');
                            return (
                              <>
                                <NavigationArrow
                                  direction="prev"
                                  onClick={() => scrollToSection('attractions')}
                                  label="Attractions"
                                />
                                <NavigationArrow
                                  direction="next"
                                  onClick={() => scrollToSection('guides')}
                                  label="Tour Guide"
                                />
                              </>
                            );
                          })()}
                        </Box>
                      </Box>
                      
                      {/* Tour Guide Section */}
                      <Box ref={guidesRef} id="guides" sx={{ mb: 2 }}>
                        <Card elevation={1} sx={{ borderRadius: '8px', overflow: 'hidden' }}>
                          <SectionHeader 
                            icon={PersonIcon} 
                            title="Tour Guide"
                            // count={selectedGuides.length}
                            onChangeClick={handleOpenGuidesModal}
                          />
                          <ContentSection>
                            <GuideDetails packageData={{...packageDetails, selected_guides: selectedGuides}} />
                          </ContentSection>
                        </Card>
                        
                        {/* Bottom navigation */}
                        <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 0.5, mb: 1 }}>
                          {(() => {
                            const nav = getNavigationDetails('guides');
                            return (
                              <>
                                <NavigationArrow
                                  direction="prev"
                                  onClick={() => scrollToSection('restaurants')}
                                  label="Restaurants"
                                />
                                {/* <NavigationArrow
                                  direction="next"
                                  onClick={() => scrollToSection('policies')}
                                  label="Policies"
                                /> */}
                              </>
                            );
                          })()}
                        </Box>
                      </Box>
                      
                      {/* Policies Section */}
                      {/* <Box ref={policiesRef} id="policies" sx={{ mb: 2 }}>
                        <Card elevation={1} sx={{ borderRadius: '8px', overflow: 'hidden' }}>
                          <SectionHeader 
                            icon={GavelIcon} 
                            title="Policies"
                          />
                          <ContentSection>
                            <Stack spacing={1}>
                              <InclusionsExclusions packageData={packageDetails} />
                              <TermsConditions packageData={packageDetails} />
                            </Stack>
                          </ContentSection>
                        </Card>
                        
                        
                        <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 0.5, mb: 1 }}>
                          {(() => {
                            const nav = getNavigationDetails('policies');
                            return (
                              <>
                                <NavigationArrow
                                  direction="prev"
                                  onClick={() => scrollToSection('guides')}
                                  label="Tour Guide"
                                />
                                <NavigationArrow
                                  direction="next"
                                  disabled={!nav.next}
                                  label="Policies"
                                />
                              </>
                            );
                          })()}
                        </Box>
                      </Box> */}
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
                        selectedRestaurants={selectedRestaurants}
                        selectedGuides={selectedGuides}
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
