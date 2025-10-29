import React, { useState, useEffect } from "react";
import {
  Switch,
  FormControlLabel,
  Radio,
  Box,
  Grid,
  Typography,
  TextField,
  Paper,
  Card,
  CardContent,
  CardHeader,
  Button,
  Divider,
  FormControl,
  RadioGroup,
  CircularProgress,
  Chip,
  Tooltip,
  Zoom,
  useMediaQuery,
  useTheme,
  Avatar,
  Badge,
} from "@mui/material";
import { useLocation } from "react-router-dom";
import { styled } from "@mui/material/styles";
import {
  Hotel as HotelIcon,
  LocationOn as LocationIcon,
  ConfirmationNumber as TicketIcon,
  Place as PlaceIcon,
  Person as PersonIcon,
  Restaurant as RestaurantIcon,
  ExpandMore as ExpandMoreIcon,
  ExpandLess as ExpandLessIcon,
  Info as InfoIcon,
  Clear as ClearIcon,
  Help as HelpIcon,
  CheckCircle as CheckCircleIcon,
  ConfirmationNumber as ConfirmationNumberIcon,
  Business as BusinessIcon,
  TravelExplore as TravelIcon,
  Search as SearchIcon,
} from "@mui/icons-material";
import { useDispatch, useSelector } from "react-redux";
import {
  updateServiceDetails,
  setSelectedServices,
  clearServiceDetails,
  clearSpecificService,
} from "@/slice/common/EnquirySlice";
import { fetchEnquiryList } from "@/slice/common/enquiryListSlice";
import {
  fetchDMCsByCountry,
  fetchDMCCount,
  setSelectedDmcIds,
  addDmcToSelection,
  removeDmcFromSelection,
  clearSelectedDmcs,
  selectDMCs,
  selectDMCLoading,
  selectDMCError,
  selectSelectedDmcIds,
  selectSelectedDmcsData,
} from "../../slice/dmc/dmcSlice";
import StarCategorySelect from "./StarCategorySelect";
import PreferredHotelsDropdown from "./PreferredHotelsDropdown";
import PortAddressSearch from "./PortAddressSearch";
import HotelDropOffSearch from "./HotelDropOffSearch";
import PreferredCarsSearch from "./PreferredCarsSearch";
import AttractionSearch from "./AttractionSearch";
import RestaurantSearch from "./RestaurantSearch";
import PreferredGuidesSearch from "./PreferredGuidesSearch";
import AttractionDropOffSearch from "./AttractionDropOffSearch";
import RestaurantDropOffSearch from "./RestaurantDropOffSearch";
import PackageAttractionSearch from "./PackageAttractionSearch";

// Service category colors
const serviceColors = {
  hotel: {
    main: "#1976d2",
    light: "#42a5f5",
    dark: "#0d47a1",
    contrastText: "#fff",
    bg: "rgba(25, 118, 210, 0.08)",
  },
  entryExitPort: {
    main: "#2e7d32",
    light: "#4caf50",
    dark: "#1b5e20",
    contrastText: "#fff",
    bg: "rgba(46, 125, 50, 0.08)",
  },
  attraction: {
    main: "#d32f2f",
    light: "#ef5350",
    dark: "#b71c1c",
    contrastText: "#fff",
    bg: "rgba(211, 47, 47, 0.08)",
  },
  localTour: {
    main: "#7b1fa2",
    light: "#9c27b0",
    dark: "#4a148c",
    contrastText: "#fff",
    bg: "rgba(123, 31, 162, 0.08)",
  },
  tourGuide: {
    main: "#ed6c02",
    light: "#ff9800",
    dark: "#e65100",
    contrastText: "#fff",
    bg: "rgba(237, 108, 2, 0.08)",
  },
  restaurant: {
    main: "#0288d1",
    light: "#03a9f4",
    dark: "#01579b",
    contrastText: "#fff",
    bg: "rgba(2, 136, 209, 0.08)",
  },
  packagedAttractions: {
    main: "#009688",
    light: "#4db6ac",
    dark: "#00695c",
    contrastText: "#fff",
    bg: "rgba(0, 150, 136, 0.08)",
  },
};

// Styled components
const StyledCard = styled(Card)(({ theme, selected, serviceType }) => {
  const colorScheme = serviceColors[serviceType] || serviceColors.hotel;

  return {
    height: "100%",
    position: "relative",
    overflow: "hidden",
    transform: "translateY(0)",
    transition: "all 0.4s ease",
    boxShadow: selected
      ? `0 10px 25px ${colorScheme.main}33`
      : "0 6px 15px rgba(0, 0, 0, 0.07)",
    border: selected ? `1px solid ${colorScheme.main}` : "1px solid #e0e3e8",
    borderRadius: theme.spacing(2),
    "&:hover": {
      boxShadow: `0 15px 30px ${colorScheme.main}26`,
      transform: "translateY(-5px)",
    },
    "&::before": selected
      ? {
          content: '""',
          position: "absolute",
          top: 0,
          left: 0,
          right: 0,
          height: "4px",
          background: `linear-gradient(90deg, ${colorScheme.main}, ${colorScheme.light})`,
        }
      : {},
    // Mobile-specific styling
    [theme.breakpoints.down('sm')]: {
      backgroundColor: selected
        ? `${colorScheme.main}15`
        : "rgba(255, 255, 255, 0.95)",
      backdropFilter: "blur(10px)",
      border: selected
        ? `2px solid ${colorScheme.main}`
        : "2px solid rgba(102, 126, 234, 0.3)",
      boxShadow: selected
        ? `0 6px 24px ${colorScheme.main}25`
        : "0 4px 16px rgba(102, 126, 234, 0.2)",
      "&:hover": {
        boxShadow: `0 8px 28px ${colorScheme.main}30`,
        transform: "translateY(-3px)",
      },
    },
  };
});

const CategoryBadge = styled(Chip)(({ theme, serviceType }) => {
  const colorScheme = serviceColors[serviceType] || serviceColors.hotel;

  return {
    position: "absolute",
    top: 10,
    right: 10,
    fontSize: "10px",
    textTransform: "uppercase",
    backgroundColor: colorScheme.bg || "#edf2ff",
    color: colorScheme.main,
    padding: "4px 8px",
    borderRadius: "10px",
    fontWeight: 600,
    letterSpacing: "0.5px",
    transition: "all 0.3s ease",
    "&:hover": {
      backgroundColor: `${colorScheme.main}26`,
      transform: "scale(1.05)",
    },
    // Mobile-specific styling
    [theme.breakpoints.down('sm')]: {
      backgroundColor: "rgba(102, 126, 234, 0.2)",
      color: "#667eea",
      fontSize: "9px",
      padding: "3px 6px",
      fontWeight: 700,
      backdropFilter: "blur(5px)",
      border: "1px solid rgba(102, 126, 234, 0.3)",
    },
  };
});

const DetailsToggleButton = styled(Button)(({ theme, serviceType }) => {
  const colorScheme = serviceColors[serviceType] || serviceColors.hotel;

  return {
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    width: "100%",
    padding: theme.spacing(1.5),
    marginTop: theme.spacing(2.5),
    backgroundColor: "transparent",
    border: "none",
    borderTop: "1px solid #f0f0f0",
    color: colorScheme.main,
    fontWeight: 500,
    transition: "all 0.3s ease",
    borderRadius: `0 0 ${theme.spacing(2)}px ${theme.spacing(2)}px`,
    "&:hover": {
      backgroundColor: colorScheme.bg || "rgba(53, 84, 209, 0.05)",
    },
  };
});

const HeadingLine = styled(Box)(({ theme }) => ({
  height: "4px",
  width: "60px",
  background: `linear-gradient(90deg, ${theme.palette.primary.main}, ${theme.palette.primary.light})`,
  margin: "15px auto 0",
  borderRadius: "10px",
}));

const SummaryCard = styled(Box)(({ theme, serviceType }) => {
  const colorScheme = serviceColors[serviceType] || serviceColors.hotel;

  return {
    display: "flex",
    alignItems: "center",
    background: "white",
    borderRadius: theme.spacing(1.5),
    padding: theme.spacing(1, 2),
    boxShadow: "0 4px 10px rgba(0, 0, 0, 0.06)",
    minWidth: "120px",
    animation: "summaryCardAppear 0.5s ease",
    borderLeft: `3px solid ${colorScheme.main}`,
    margin: theme.spacing(0.5),
    transition: "all 0.3s ease",
    cursor: "pointer",
    "&:hover": {
      transform: "translateY(-3px)",
      boxShadow: `0 8px 15px ${colorScheme.main}26`,
    },
  };
});

const SummaryIcon = styled(Box)(({ theme, serviceType }) => {
  const colorScheme = serviceColors[serviceType] || serviceColors.hotel;

  return {
    width: "36px",
    height: "36px",
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    background: colorScheme.bg || "#edf2ff",
    borderRadius: "50%",
    marginRight: theme.spacing(1.5),
    color: colorScheme.main,
    fontSize: "16px",
    transition: "all 0.3s ease",
    "&:hover": {
      transform: "rotate(10deg)",
    },
  };
});

const NoSelections = styled(Box)(({ theme }) => ({
  background: "#fff9e7",
  padding: theme.spacing(2),
  borderRadius: theme.spacing(1.5),
  width: "100%",
  display: "flex",
  alignItems: "center",
  color: "#b78105",
}));

const HelpIconButton = styled(HelpIcon)(({ theme, serviceType }) => {
  const colorScheme = serviceColors[serviceType] || serviceColors.hotel;

  return {
    fontSize: "16px",
    marginLeft: theme.spacing(1),
    color: colorScheme.main,
    cursor: "pointer",
    transition: "all 0.3s ease",
    "&:hover": {
      transform: "scale(1.2)",
    },
  };
});

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

// Get service descriptions for tooltips
const getServiceDescription = (option) => {
  switch (option) {
    case "hotel":
      return "Select your preferred hotel accommodations, including star category and specific hotels.";
    case "entryExitPort":
      return "Choose your transportation for arrival and departure, including car type and locations.";
    case "attraction":
      return "Add attractions to your itinerary with optional transportation services.";
    case "packagedAttractions":
      return "Select from available packaged attraction deals, which include multiple attractions bundled together.";
    case "localTour":
      return "Arrange for local tours with transportation in your destination city.";
    case "tourGuide":
      return "Book specialized tour guides for your travel experience.";
    case "restaurant":
      return "Reserve tables at restaurants with optional transportation services.";
    default:
      return "";
  }
};

// Main component
const BookingEnquiries = ({
  bookingOptions,
  setBookingOptions,
  onNext,
  onBack,
}) => {
  // Scroll to top when component mounts
  useEffect(() => {
    window.scrollTo(0, 0);
  }, []);
  const dispatch = useDispatch();
  const location = useLocation();
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  
  // Get the selected city from Redux store
  const selectedCity = useSelector((state) => state.common.selectedCity);
  const enquiryData = useSelector((state) => state.enquiry);
  
  // Get enquiry list loading state for showing feedback when data is being refreshed
  const enquiryListLoading = useSelector((state) => state.enquiryList.loading);
  
  // DMC-related state and selectors
  const apiDMCs = useSelector(selectDMCs);
  const dmcLoading = useSelector(selectDMCLoading);
  const dmcError = useSelector(selectDMCError);
  const selectedDmcIds = useSelector(selectSelectedDmcIds);
  const selectedDmcsData = useSelector(selectSelectedDmcsData);
  
  // Get location data from Redux (from LocationSearch component)
  const locationFromRedux = enquiryData?.searchLocation || selectedCity;
  
  // Get location data from navigation state (passed from MainMenu) - for backward compatibility
  const navigationState = location.state;
  const selectedLocationFromNavigation = navigationState?.selectedLocation;
  const selectedCountryFromNavigation = navigationState?.selectedCountry;
  
  // Determine the active location (priority: Redux > Navigation)
  const activeLocation = locationFromRedux || selectedLocationFromNavigation || 
    (selectedCountryFromNavigation ? { country: selectedCountryFromNavigation.name } : null);
  
  // Component state
  const [dmcOptions, setDmcOptions] = useState([]);
  const [filterText, setFilterText] = useState('');
  const [hasDMCsAvailable, setHasDMCsAvailable] = useState(false);

  // Auto-fetch DMCs when location is selected in LocationSearch component
  useEffect(() => {
    // Priority: Use location from Redux (LocationSearch) > Navigation state
    let countryName = null;
    
    if (locationFromRedux) {
      // From LocationSearch component via Redux
      countryName = locationFromRedux.countryName || locationFromRedux.country;
      console.log('🌍 Using location from LocationSearch (Redux):', locationFromRedux);
    } else if (selectedLocationFromNavigation) {
      // From navigation state (backward compatibility)
      countryName = selectedLocationFromNavigation.country;
      console.log('🌍 Using location from navigation:', selectedLocationFromNavigation);
    } else if (selectedCountryFromNavigation) {
      // Legacy support
      countryName = selectedCountryFromNavigation.name;
      console.log('🌍 Using country from navigation:', selectedCountryFromNavigation);
    }
    
    if (countryName) {
      console.log('🏢 Auto-fetching DMCs for country:', countryName);
      dispatch(fetchDMCsByCountry([countryName]));
    }
  }, [locationFromRedux, selectedLocationFromNavigation, selectedCountryFromNavigation, dispatch]);

  // Process DMC data for sidebar options
  useEffect(() => {
    if (apiDMCs && apiDMCs.data && Array.isArray(apiDMCs.data)) {
      const processedDMCs = apiDMCs.data.map((dmc, index) => ({
        id: `dmc-${index}`,
        dmcId: dmc.userId || null,
        name: dmc.company_name || `DMC ${index + 1}`,
        location: dmc.country || 'Unknown Location',
        logo: dmc.logo || '',
        description: 'Professional destination management services',
        originalData: dmc,
      }));
      
      setDmcOptions(processedDMCs);
      setHasDMCsAvailable(processedDMCs.length > 0);
    } else {
      setDmcOptions([]);
      setHasDMCsAvailable(false);
    }
  }, [apiDMCs]);


  // Fetch enquiry list when component mounts or when city data changes
  useEffect(() => {
    // Check if we have city data to fetch the enquiry list
    if (selectedCity && selectedCity.cityName && selectedCity.countryName) {
      console.log("Fetching enquiry list from BookingEnquiries component", {
        country: selectedCity.countryName,
        city: selectedCity.cityName,
      });

      dispatch(
        fetchEnquiryList({
          country: selectedCity.countryName,
          city: selectedCity.cityName,
        })
      );
    } else if (enquiryData && enquiryData.searchLocation) {
      // Try to use data from enquiryData if selectedCity is not available
      const { country, city } = enquiryData.searchLocation;
      if (country && city) {
        console.log("Fetching enquiry list using enquiry data", {
          country,
          city,
        });

        dispatch(
          fetchEnquiryList({
            country,
            city,
          })
        );
      }
    }
  }, [selectedCity, enquiryData, dispatch]);

  // Refetch enquiry list when DMCs are selected/deselected
  useEffect(() => {
    // Only refetch if we have location data and at least one DMC is selected
    if (selectedDmcsData.length > 0 && activeLocation) {
      let country = null;
      let city = null;

      // Get country and city from activeLocation
      if (activeLocation.city && activeLocation.country) {
        country = activeLocation.country;
        city = activeLocation.city;
      } else if (activeLocation.cityName && activeLocation.countryName) {
        country = activeLocation.countryName;
        city = activeLocation.cityName;
      } else if (activeLocation.country || activeLocation.countryName) {
        country = activeLocation.country || activeLocation.countryName;
        // Try to get city from Redux
        if (selectedCity && selectedCity.cityName) {
          city = selectedCity.cityName;
        }
      }

      // Only make API call if we have both country and city
      if (country && city) {
        console.log("🔄 Refetching enquiry list with selected DMCs:", {
          country,
          city,
          selectedDmcIds: selectedDmcIds,
          dmcCount: selectedDmcsData.length
        });

        dispatch(
          fetchEnquiryList({
            country,
            city,
          })
        );
      } else {
        console.warn("⚠️ Cannot refetch enquiry list - missing country or city data");
        console.log("Available data:", {
          activeLocation,
          selectedCity
        });
      }
    }
  }, [selectedDmcIds, selectedDmcsData, activeLocation, selectedCity, dispatch]);

  // Initialize expandedSections separately from bookingOptions
  const [expandedSections, setExpandedSections] = useState({
    hotel: false,
    entryExitPort: false,
    attraction: false,
    localTour: false,
    tourGuide: false,
    restaurant: false,
    packagedAttractions: false,
  });

  // We need to make sure bookingOptions is properly initialized
  useEffect(() => {
    // Ensure bookingOptions has all necessary keys with boolean values
    const hasAllOptions = [
      "hotel",
      "entryExitPort",
      "attraction",
      "localTour",
      "tourGuide",
      "restaurant",
      "packagedAttractions",
    ].every((key) => typeof bookingOptions[key] === "boolean");

    if (!hasAllOptions) {
      // Initialize with all services unselected by default
      setBookingOptions((prev) => ({
        hotel: false,
        entryExitPort: false,
        attraction: false,
        localTour: false,
        tourGuide: false,
        restaurant: false,
        packagedAttractions: false,
        // Don't spread prev to avoid retaining any true values from previous state
      }));
    }
  }, []);

  // Effect to save selected services to Redux whenever bookingOptions changes
  useEffect(() => {
    dispatch(
      setSelectedServices(
        Object.keys(bookingOptions).filter((key) => bookingOptions[key])
      )
    );
  }, [bookingOptions, dispatch]);

  // Effect to sync local state with Redux data when enquiryData changes
  useEffect(() => {
    if (enquiryData.serviceDetails) {
      // Sync hotel data
      if (enquiryData.serviceDetails.hotel) {
        setCompareHotels(enquiryData.serviceDetails.hotel.compareHotels || "no");
        setSelectedPreferredHotels(enquiryData.serviceDetails.hotel.preferredHotels || []);
        setStarCategory(enquiryData.serviceDetails.hotel.starCategory || "");
      }
      
      // Sync tour guide data
      if (enquiryData.serviceDetails.tourGuide) {
        setSelectedGuides(enquiryData.serviceDetails.tourGuide.preferredGuides || []);
      }
      
                     // Sync entry/exit port data
               if (enquiryData.serviceDetails.entryExitPort) {
                 setSelectedEntryExitCars(enquiryData.serviceDetails.entryExitPort.preferredCars || []);
                 setEntryDropoffLocationType(enquiryData.serviceDetails.entryExitPort.entryDropoffLocationType || "hotel");
                 setExitPickupLocationType(enquiryData.serviceDetails.entryExitPort.exitPickupLocationType || "hotel");
                 setShowEntryPort(enquiryData.serviceDetails.entryExitPort.showEntryPort !== undefined
                   ? enquiryData.serviceDetails.entryExitPort.showEntryPort
                   : true);
                 setShowExitPort(enquiryData.serviceDetails.entryExitPort.showExitPort || false);
                 setSelectedEntryPort(enquiryData.serviceDetails.entryExitPort.portAddress || null);
                 setSelectedExitPort(enquiryData.serviceDetails.entryExitPort.exitPortAddress || null);
                 setSelectedHotelDropOff(enquiryData.serviceDetails.entryExitPort.hotelDropOff || null);
                 setSelectedAttractionDropOff(enquiryData.serviceDetails.entryExitPort.attractionDropOff || null);
                 setSelectedRestaurantDropOff(enquiryData.serviceDetails.entryExitPort.restaurantDropOff || null);
                 setSelectedExitAttractionPickup(enquiryData.serviceDetails.entryExitPort.exitAttractionPickup || null);
                 setSelectedExitRestaurantPickup(enquiryData.serviceDetails.entryExitPort.exitRestaurantPickup || null);
               }
               
               // Sync local tour data
               if (enquiryData.serviceDetails.localTour) {
                 setSelectedLocalTourCars(enquiryData.serviceDetails.localTour.preferredCars || []);
               }
               
               // Sync attraction data (including cars)
               if (enquiryData.serviceDetails.attraction) {
                 setNeedTransport(enquiryData.serviceDetails.attraction.needTransport || false);
                 setDestinationType(enquiryData.serviceDetails.attraction.destinationType || "hotel");
                 setSelectedAttractions(enquiryData.serviceDetails.attraction.selectedAttractions || []);
                 setSelectedDestinations(enquiryData.serviceDetails.attraction.destination || []);
                 setSelectedAttractionCars(enquiryData.serviceDetails.attraction.preferredCars || []);
               }
               
               // Sync restaurant data (including cars)
               if (enquiryData.serviceDetails.restaurant) {
                 setNeedTransportType(enquiryData.serviceDetails.restaurant.needTransport || false);
                 setSelectedRestaurants(enquiryData.serviceDetails.restaurant.selectedRestaurants || []);
                 setSelectedRestaurantCars(enquiryData.serviceDetails.restaurant.preferredCars || []);
               }
      
      // Sync packaged attractions data
      if (enquiryData.serviceDetails.packagedAttractions) {
        setSelectedPackagedAttractions(enquiryData.serviceDetails.packagedAttractions.selectedPackagedAttractions || []);
      }
      
      // Sync remarks and special requirements
      setHotelRemarks(enquiryData.serviceDetails?.hotel?.remarks || "");
      setEntryExitPortRemarks(enquiryData.serviceDetails?.entryExitPort?.remarks || "");
      setAttractionRemarks(enquiryData.serviceDetails?.attraction?.remarks || "");
      setLocalTourRemarks(enquiryData.serviceDetails?.localTour?.remarks || "");
      setRestaurantRemarks(enquiryData.serviceDetails?.restaurant?.remarks || "");
      setPackagedAttractionsRemarks(enquiryData.serviceDetails?.packagedAttractions?.remarks || "");
      setTourGuideSpecialRequirements(enquiryData.serviceDetails?.tourGuide?.specialRequirements || "");
    }
  }, [enquiryData.serviceDetails]);

  const handleToggleChange = (option) => {
    const newValue = !bookingOptions[option];

    // Make a new object to avoid reference issues
    const newBookingOptions = {
      ...bookingOptions,
      [option]: newValue,
    };

    setBookingOptions(newBookingOptions);

    // Update expanded sections independently
    setExpandedSections((prev) => ({
      ...prev,
      [option]: newValue, // Auto-expand when turned on, collapse when turned off
    }));

    // If service is being unselected (turned off), clear its data from Redux and local state
    if (!newValue) {
      dispatch(clearSpecificService(option));
      console.log(`Service ${option} unselected - cleared its data from Redux`);
      
      // Clear corresponding local state variables based on the service
      switch (option) {
        case "hotel":
          setStarCategory("");
          setSelectedPreferredHotels([]);
          setCompareHotels("no");
          setHotelRemarks("");
          break;
        case "entryExitPort":
          setSelectedEntryExitCars([]);
          setSelectedEntryPort(null);
          setSelectedExitPort(null);
          setSelectedHotelDropOff(null);
          setSelectedAttractionDropOff(null);
          setSelectedRestaurantDropOff(null);
          setSelectedExitAttractionPickup(null);
          setSelectedExitRestaurantPickup(null);
          setEntryDropoffLocationType("hotel");
          setExitPickupLocationType("hotel");
          setShowEntryPort(true);
          setShowExitPort(false);
          setEntryExitPortRemarks("");
          break;
        case "attraction":
          setSelectedAttractions([]);
          setSelectedDestinations([]);
          setSelectedAttractionCars([]);
          setNeedTransport(false);
          setDestinationType("hotel");
          setAttractionRemarks("");
          break;
        case "packagedAttractions":
          setSelectedPackagedAttractions([]);
          setPackagedAttractionsRemarks("");
          break;
        case "localTour":
          setSelectedLocalTourCars([]);
          setLocalTourRemarks("");
          break;
        case "tourGuide":
          setSelectedGuides([]);
          setTourGuideSpecialRequirements("");
          break;
        case "restaurant":
          setSelectedRestaurants([]);
          setSelectedRestaurantCars([]);
          setNeedTransportType(false);
          setRestaurantRemarks("");
          break;
        default:
          break;
      }
    }
  };

  const handleExpandSection = (section) => {
    if (!bookingOptions[section]) {
      return; // Don't try to expand a section that's not selected
    }

    // Only toggle the specific section
    setExpandedSections((prev) => ({
      ...prev,
      [section]: !prev[section],
    }));
  };

  // Add separate state for selected cars for each service - initialize with Redux data
  const [selectedEntryExitCars, setSelectedEntryExitCars] = useState(
    enquiryData.serviceDetails?.entryExitPort?.preferredCars || []
  );
  const [selectedLocalTourCars, setSelectedLocalTourCars] = useState(
    enquiryData.serviceDetails?.localTour?.preferredCars || []
  );
  const [selectedAttractionCars, setSelectedAttractionCars] = useState(
    enquiryData.serviceDetails?.attraction?.preferredCars || []
  );
  const [selectedRestaurantCars, setSelectedRestaurantCars] = useState(
    enquiryData.serviceDetails?.restaurant?.preferredCars || []
  );

  // Handle car selection
  const handleCarSelect = (cars, service) => {
    if (service === "entryExitPort") {
      setSelectedEntryExitCars(cars);
      dispatch(
        updateServiceDetails({
          service: "entryExitPort",
          data: { preferredCars: cars },
        })
      );
    } else if (service === "localTour") {
      setSelectedLocalTourCars(cars);
      dispatch(
        updateServiceDetails({
          service: "localTour",
          data: { preferredCars: cars },
        })
      );
    } else if (service === "attraction" && needTransport) {
      setSelectedAttractionCars(cars);
      dispatch(
        updateServiceDetails({
          service: "attraction",
          data: { preferredCars: cars },
        })
      );
    } else if (service === "restaurant" && needTransportType) {
      setSelectedRestaurantCars(cars);
      dispatch(
        updateServiceDetails({
          service: "restaurant",
          data: { preferredCars: cars },
        })
      );
    }
  };

  // Add state for attraction section - initialize with Redux data
  const [needTransport, setNeedTransport] = useState(
    enquiryData.serviceDetails?.attraction?.needTransport || false
  );
  const [destinationType, setDestinationType] = useState(
    enquiryData.serviceDetails?.attraction?.destinationType || "hotel"
  );
  const [selectedAttractions, setSelectedAttractions] = useState(
    enquiryData.serviceDetails?.attraction?.selectedAttractions || []
  );
  const [selectedDestinations, setSelectedDestinations] = useState(
    enquiryData.serviceDetails?.attraction?.destination || []
  );
  const [compareHotels, setCompareHotels] = useState(
    enquiryData.serviceDetails?.hotel?.compareHotels || "no"
  );
  const [selectedPreferredHotels, setSelectedPreferredHotels] = useState(
    enquiryData.serviceDetails?.hotel?.preferredHotels || []
  );
  const [starCategory, setStarCategory] = useState(
    enquiryData.serviceDetails?.hotel?.starCategory || ""
  );
  const [selectedGuides, setSelectedGuides] = useState(
    enquiryData.serviceDetails?.tourGuide?.preferredGuides || []
  );
  const [needTransportType, setNeedTransportType] = useState(
    enquiryData.serviceDetails?.restaurant?.needTransport || false
  );

  // Add state for remarks fields to make TextField components controlled
  const [hotelRemarks, setHotelRemarks] = useState(
    enquiryData.serviceDetails?.hotel?.remarks || ""
  );
  const [entryExitPortRemarks, setEntryExitPortRemarks] = useState(
    enquiryData.serviceDetails?.entryExitPort?.remarks || ""
  );
  const [attractionRemarks, setAttractionRemarks] = useState(
    enquiryData.serviceDetails?.attraction?.remarks || ""
  );
  const [localTourRemarks, setLocalTourRemarks] = useState(
    enquiryData.serviceDetails?.localTour?.remarks || ""
  );
  const [restaurantRemarks, setRestaurantRemarks] = useState(
    enquiryData.serviceDetails?.restaurant?.remarks || ""
  );
  const [packagedAttractionsRemarks, setPackagedAttractionsRemarks] = useState(
    enquiryData.serviceDetails?.packagedAttractions?.remarks || ""
  );
  const [tourGuideSpecialRequirements, setTourGuideSpecialRequirements] = useState(
    enquiryData.serviceDetails?.tourGuide?.specialRequirements || ""
  );

  // Add state for selected restaurants
  const [selectedRestaurants, setSelectedRestaurants] = useState(
    enquiryData.serviceDetails?.restaurant?.selectedRestaurants || []
  );

  // Add state for port addresses
  const [selectedEntryPort, setSelectedEntryPort] = useState(
    enquiryData.serviceDetails?.entryExitPort?.portAddress || null
  );
  const [selectedExitPort, setSelectedExitPort] = useState(
    enquiryData.serviceDetails?.entryExitPort?.exitPortAddress || null
  );

  // Add state for drop-off locations
  const [selectedHotelDropOff, setSelectedHotelDropOff] = useState(
    enquiryData.serviceDetails?.entryExitPort?.hotelDropOff || null
  );
  const [selectedAttractionDropOff, setSelectedAttractionDropOff] = useState(
    enquiryData.serviceDetails?.entryExitPort?.attractionDropOff || null
  );
  const [selectedRestaurantDropOff, setSelectedRestaurantDropOff] = useState(
    enquiryData.serviceDetails?.entryExitPort?.restaurantDropOff || null
  );

  // Add state for exit pickup locations
  const [selectedExitAttractionPickup, setSelectedExitAttractionPickup] = useState(
    enquiryData.serviceDetails?.entryExitPort?.exitAttractionPickup || null
  );
  const [selectedExitRestaurantPickup, setSelectedExitRestaurantPickup] = useState(
    enquiryData.serviceDetails?.entryExitPort?.exitRestaurantPickup || null
  );

  // Handle attraction selection
  const handleAttractionSelect = (attractions) => {
    setSelectedAttractions(attractions);
    dispatch(
      updateServiceDetails({
        service: "attraction",
        data: { selectedAttractions: attractions },
      })
    );
  };

  // Handle destination selection (hotels or restaurants)
  const handleDestinationSelect = (destinations, service, type) => {
    setSelectedDestinations(destinations);

    dispatch(
      updateServiceDetails({
        service: service,
        data: { destination: destinations },
      })
    );
  };

  // Handle guide selection
  const handleGuideSelect = (guides) => {
    // Make sure we're processing guides correctly
    const processedGuides = guides.map(guide => {
      // If guide is already a simple string, return it as is
      if (typeof guide === 'string') {
        return guide;
      }
      
      // Create a simplified guide object with only necessary properties
      const processedGuide = {
        id: guide.id || guide.guide_id || `guide-${Date.now()}-${Math.random()}`,
        name: guide.name || `Guide ${guide.id || guide.guide_id}`,
        city: guide.city || "",
        country: guide.country || "",
        experience_years: guide.experience_years || ""
      };
      
      // Properly handle languages if they exist
      if (guide.languages && Array.isArray(guide.languages)) {
        // Make a deep copy of the languages array with only the needed properties
        processedGuide.languages = guide.languages.map(lang => ({
          language: lang.language || "",
          proficiency: lang.proficiency || ""
        }));
      }
      
      return processedGuide;
    });
    
    setSelectedGuides(processedGuides);
    dispatch(
      updateServiceDetails({
        service: "tourGuide",
        data: { preferredGuides: processedGuides },
      })
    );
  };

  // Handle preferred hotels selection
  const handlePreferredHotelsSelect = (hotels) => {
    setSelectedPreferredHotels(hotels);
    dispatch(
      updateServiceDetails({
        service: "hotel",
        data: { preferredHotels: hotels },
      })
    );
  };



  // Handle star category change
  const handleStarCategoryChange = (category) => {
    setStarCategory(category);
    dispatch(
      updateServiceDetails({
        service: "hotel",
        data: { starCategory: category },
      })
    );
  };

  // Handle compare hotels change
  const handleCompareHotelsChange = (e) => {
    const value = e.target.value;
    setCompareHotels(value);
    dispatch(
      updateServiceDetails({
        service: "hotel",
        data: { compareHotels: value },
      })
    );
  };

  // Handle need transport change
  const handleNeedTransportChange = () => {
    const newValue = !needTransport;
    setNeedTransport(newValue);
    dispatch(
      updateServiceDetails({
        service: "attraction",
        data: { needTransport: newValue },
      })
    );
  };

  // Handle need transport type change
  const handleNeedTransportTypeChange = () => {
    const newValue = !needTransportType;
    setNeedTransportType(newValue);
    dispatch(
      updateServiceDetails({
        service: "restaurant",
        data: { needTransport: newValue },
      })
    );
  };

  // Handle destination type change
  const handleDestinationTypeChange = (e, service) => {
    const value = e.target.value;
    setDestinationType(value);

    if (service === "attraction") {
      dispatch(
        updateServiceDetails({
          service: "attraction",
          data: { destinationType: value },
        })
      );
    } else if (service === "restaurant") {
      dispatch(
        updateServiceDetails({
          service: "restaurant",
          data: { destinationType: value },
        })
      );
    }
  };

  // Handle car type change
  const handleCarTypeChange = (e, service) => {
    const value = e.target.value;
    dispatch(
      updateServiceDetails({
        service: service,
        data: { carType: value },
      })
    );
  };

  // Handle remarks change
  const handleRemarksChange = (e, service) => {
    const value = e.target.value;
    dispatch(
      updateServiceDetails({
        service: service,
        data: { remarks: value },
      })
    );
  };

  // Handle special requirements change
  const handleSpecialRequirementsChange = (e) => {
    const value = e.target.value;
    setTourGuideSpecialRequirements(value);
    dispatch(
      updateServiceDetails({
        service: "tourGuide",
        data: { specialRequirements: value },
      })
    );
  };

  // Handle port address selection
  const handlePortAddressSelect = (port) => {
    setSelectedEntryPort(port);
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { portAddress: port },
      })
    );
  };

  // Handle exit port address selection
  const handleExitPortAddressSelect = (port) => {
    setSelectedExitPort(port);
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { exitPortAddress: port },
      })
    );
  };

  // Handle hotel drop off selection
  const handleHotelDropOffSelect = (hotel) => {
    setSelectedHotelDropOff(hotel);
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { hotelDropOff: hotel },
      })
    );
  };

  // Handle hotel remarks change
  const handleHotelRemarksChange = (e) => {
    const value = e.target.value;
    setHotelRemarks(value);
    dispatch(
      updateServiceDetails({
        service: "hotel",
        data: { remarks: value },
      })
    );
  };

  // Handle entry/exit port remarks change
  const handleEntryExitPortRemarksChange = (e) => {
    const value = e.target.value;
    setEntryExitPortRemarks(value);
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { remarks: value },
      })
    );
  };

  // Handle attraction remarks change
  const handleAttractionRemarksChange = (e) => {
    const value = e.target.value;
    setAttractionRemarks(value);
    dispatch(
      updateServiceDetails({
        service: "attraction",
        data: { remarks: value },
      })
    );
  };

  // Handle local tour remarks change
  const handleLocalTourRemarksChange = (e) => {
    const value = e.target.value;
    setLocalTourRemarks(value);
    dispatch(
      updateServiceDetails({
        service: "localTour",
        data: { remarks: value },
      })
    );
  };

  // Handle restaurant remarks change
  const handleRestaurantRemarksChange = (e) => {
    const value = e.target.value;
    setRestaurantRemarks(value);
    dispatch(
      updateServiceDetails({
        service: "restaurant",
        data: { remarks: value },
      })
    );
  };

  // Handle restaurant selection
  const handleRestaurantSelect = (restaurants) => {
    setSelectedRestaurants(restaurants);
    dispatch(
      updateServiceDetails({
        service: "restaurant",
        data: { selectedRestaurants: restaurants },
      })
    );
  };

  // Handle packaged attractions selection - initialize with Redux data
  const [selectedPackagedAttractions, setSelectedPackagedAttractions] = useState(
    enquiryData.serviceDetails?.packagedAttractions?.selectedPackagedAttractions || []
  );
  const handlePackagedAttractionsSelect = (packages) => {
    setSelectedPackagedAttractions(packages);
    dispatch(
      updateServiceDetails({
        service: "packagedAttractions",
        data: { selectedPackagedAttractions: packages },
      })
    );
  };

  // Handle packaged attractions remarks change
  const handlePackagedAttractionsRemarksChange = (e) => {
    const value = e.target.value;
    setPackagedAttractionsRemarks(value);
    dispatch(
      updateServiceDetails({
        service: "packagedAttractions",
        data: { remarks: value },
      })
    );
  };

  // Handle form submission to go to next step
  const handleSubmitForm = () => {
    console.log("Form submitted - saving all data to Redux");
    
    // Ensure all selected services are saved to Redux
    dispatch(setSelectedServices(
      Object.keys(bookingOptions).filter(key => bookingOptions[key])
    ));
    
    // Save final data to Redux before moving to next step
    Object.keys(bookingOptions).forEach((service) => {
      if (bookingOptions[service]) {
        // Make sure all form data is saved to Redux
        switch(service) {
          case 'hotel':
            dispatch(updateServiceDetails({
              service: 'hotel',
              data: {
                starCategory: starCategory || null,
                compareHotels: compareHotels || 'no',
                preferredHotels: selectedPreferredHotels || [],
                remarks: enquiryData.serviceDetails?.hotel?.remarks || ""
              }
            }));
            break;
            
          case 'entryExitPort':
            dispatch(updateServiceDetails({
              service: 'entryExitPort',
              data: {
                portAddress: enquiryData.serviceDetails?.entryExitPort?.portAddress || null,
                hotelDropOff: enquiryData.serviceDetails?.entryExitPort?.hotelDropOff || null,
                carType: enquiryData.serviceDetails?.entryExitPort?.carType || "sharable",
                preferredCars: selectedEntryExitCars || [],
                remarks: enquiryData.serviceDetails?.entryExitPort?.remarks || ""
              }
            }));
            break;
            
          case 'attraction':
            dispatch(updateServiceDetails({
              service: 'attraction',
              data: {
                selectedAttractions: selectedAttractions || [],
                needTransport: needTransport || false,
                destinationType: enquiryData.serviceDetails?.attraction?.destinationType || "hotel",
                destination: enquiryData.serviceDetails?.attraction?.destination || null,
                carType: enquiryData.serviceDetails?.attraction?.carType || "sharable",
                preferredCars: selectedAttractionCars || [],
                remarks: enquiryData.serviceDetails?.attraction?.remarks || ""
              }
            }));
            break;
            case 'packagedAttractions':
              dispatch(updateServiceDetails({
                service: 'packagedAttractions',
                data: {
                  selectedPackagedAttractions: selectedPackagedAttractions || [],
                  remarks: enquiryData.serviceDetails?.packagedAttractions?.remarks || ""
                }
              }));
              break;
            
          case 'localTour':
            dispatch(updateServiceDetails({
              service: 'localTour',
              data: {
                preferredCars: selectedLocalTourCars || [],
                remarks: enquiryData.serviceDetails?.localTour?.remarks || ""
              }
            }));
            break;
            
          case 'tourGuide':
            dispatch(updateServiceDetails({
              service: 'tourGuide',
              data: {
                preferredGuides: selectedGuides || [],
                specialRequirements: enquiryData.serviceDetails?.tourGuide?.specialRequirements || ""
              }
            }));
            break;
            
          case 'restaurant':
            dispatch(updateServiceDetails({
              service: 'restaurant',
              data: {
                selectedRestaurants: selectedRestaurants || [],
                needTransport: needTransportType || false,
                destinationType: enquiryData.serviceDetails?.restaurant?.destinationType || "hotel",
                destination: enquiryData.serviceDetails?.restaurant?.destination || null,
                carType: enquiryData.serviceDetails?.restaurant?.carType || "sharable",
                preferredCars: selectedRestaurantCars || [],
                remarks: enquiryData.serviceDetails?.restaurant?.remarks || ""
              }
            }));
            break;
            
        
            
          default:
            break;
        }
      }
    });
    
    console.log("All data saved to Redux state");
    
    // Reset all form data (local state only - keep Redux data for ConfirmDetails)
    setStarCategory("");
    setSelectedPreferredHotels([]);
    setCompareHotels("no");
    setSelectedAttractions([]);
    setNeedTransport(false);
    setNeedTransportType(false);
    setSelectedGuides([]);
    setDestinationType("hotel");
    setSelectedDestinations([]);
    setSelectedEntryExitCars([]);
    setSelectedLocalTourCars([]);
    setSelectedAttractionCars([]);
    setSelectedRestaurantCars([]);
    setSelectedPackagedAttractions([]);
    setSelectedRestaurants([]);
    setSelectedEntryPort(null);
    setSelectedExitPort(null);
    setSelectedHotelDropOff(null);
    setSelectedAttractionDropOff(null);
    setSelectedRestaurantDropOff(null);
    setSelectedExitAttractionPickup(null);
    setSelectedExitRestaurantPickup(null);
    setHotelRemarks("");
    setEntryExitPortRemarks("");
    setAttractionRemarks("");
    setLocalTourRemarks("");
    setRestaurantRemarks("");
    setPackagedAttractionsRemarks("");
    setTourGuideSpecialRequirements("");
    
    // Reset form fields by clearing the form input elements
    const inputElements = document.querySelectorAll('input[type="text"], textarea');
    inputElements.forEach(input => {
      input.value = '';
    });
    
    // Clear all expanded sections
    setExpandedSections({
      hotel: false,
      entryExitPort: false,
      attraction: false,
      localTour: false,
      tourGuide: false,
      restaurant: false,
      packagedAttractions: false
    });
    
    // Continue to next step
    if (onNext) {
      onNext();
    }
  };

  const getIconForOption = (option) => {
    switch (option) {
      case "hotel":
        return <HotelIcon />;
      case "entryExitPort":
        return <LocationIcon />;
      case "attraction":
        return <TicketIcon />;
        case "packagedAttractions":
          return <ConfirmationNumberIcon />;
      case "localTour":
        return <PlaceIcon />;
      case "tourGuide":
        return <PersonIcon />;
      case "restaurant":
        return <RestaurantIcon />;
      // Or another suitable icon
      default:
        return null;
    }
  };

  const getCategoryLabel = (option) => {
    switch (option) {
      case "hotel":
        return "Hotel Services";
      case "entryExitPort":
        return "Transport Services";
      case "attraction":
        return "Attraction Services";
      case "packagedAttractions":
        return "Packaged Attraction Services";
      case "localTour":
        return "Local Tour Services";
      case "tourGuide":
        return "Guide Services";
      case "restaurant":
        return "Dining Services";
    
      default:
        return "";
    }
  };

  const getCardTitle = (option) => {
    switch (option) {
      case "hotel":
        return "Hotel";
      case "entryExitPort":
        return "Entry/Exit Port";
      case "attraction":
        return "Attraction";
        case "packagedAttractions":
          return "Packaged Attractions";
      case "localTour":
        return "Local Tour";
      case "tourGuide":
        return "Tour Guide";
      case "restaurant":
        return "Restaurant";
     
      default:
        return "";
    }
  };

  // Add at the top of the component
  const [debugInfo, setDebugInfo] = useState({ visible: false, message: "" });

  // Get the enquiry list state from Redux for debugging
  const enquiryListState = useSelector((state) => state.enquiryList);

  // Add this function in the component
  const handleManualFetch = () => {
    if (selectedCity && selectedCity.cityName && selectedCity.countryName) {
      setDebugInfo({
        visible: true,
        message: `Manually fetching data for ${selectedCity.countryName}, ${selectedCity.cityName}`,
      });

      dispatch(
        fetchEnquiryList({
          country: selectedCity.countryName,
          city: selectedCity.cityName,
        })
      );
    } else if (enquiryData && enquiryData.searchLocation) {
      const { country, city } = enquiryData.searchLocation;
      if (country && city) {
        setDebugInfo({
          visible: true,
          message: `Manually fetching data using enquiry data for ${country}, ${city}`,
        });

        dispatch(
          fetchEnquiryList({
            country,
            city,
          })
        );
      } else {
        setDebugInfo({
          visible: true,
          message: "Error: No city/country data found in enquiry data",
        });
      }
    } else {
      setDebugInfo({
        visible: true,
        message: "Error: No city/country data available for API call",
      });
    }
  };

  // Add this before the return statement to set debug messages based on API state
  useEffect(() => {
    if (enquiryListState.loading) {
      setDebugInfo((prev) => ({
        ...prev,
        message: "Loading enquiry list data...",
      }));
    } else if (enquiryListState.error) {
      setDebugInfo((prev) => ({
        ...prev,
        message: `Error: ${enquiryListState.error}`,
      }));
    } else if (
      enquiryListState.hotels.length > 0 ||
      enquiryListState.attractions.length > 0 ||
      enquiryListState.restaurants.length > 0
    ) {
      setDebugInfo((prev) => ({
        ...prev,
        message: `Success! Found ${enquiryListState.hotels.length} hotels, ${enquiryListState.attractions.length} attractions, ${enquiryListState.restaurants.length} restaurants`,
      }));
    }
  }, [enquiryListState]);

  // Fix the state variables and handlers - initialize with Redux data
  const [entryDropoffLocationType, setEntryDropoffLocationType] = useState(
    enquiryData.serviceDetails?.entryExitPort?.entryDropoffLocationType || "hotel"
  );
  const [exitPickupLocationType, setExitPickupLocationType] = useState(
    enquiryData.serviceDetails?.entryExitPort?.exitPickupLocationType || "hotel"
  );
  const [showEntryPort, setShowEntryPort] = useState(
    enquiryData.serviceDetails?.entryExitPort?.showEntryPort !== undefined 
      ? enquiryData.serviceDetails.entryExitPort.showEntryPort 
      : true
  );
  const [showExitPort, setShowExitPort] = useState(
    enquiryData.serviceDetails?.entryExitPort?.showExitPort || false
  );

  // Fix the handlers
  const handleEntryDropoffLocationTypeChange = (e) => {
    const value = e.target.value;
    setEntryDropoffLocationType(value);
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { entryDropoffLocationType: value },
      })
    );
  };

  const handleExitPickupLocationTypeChange = (e) => {
    const value = e.target.value;
    setExitPickupLocationType(value);
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { exitPickupLocationType: value },
      })
    );
  };

  const handleToggleEntryPort = (event) => {
    const isChecked = event.target.checked;
    setShowEntryPort(isChecked);
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { showEntryPort: isChecked },
      })
    );
  };

  const handleToggleExitPort = (event) => {
    const isChecked = event.target.checked;
    setShowExitPort(isChecked);
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { showExitPort: isChecked },
      })
    );
  };

  // Handle attraction drop off selection
  const handleAttractionDropOffSelect = (attraction) => {
    setSelectedAttractionDropOff(attraction);
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { attractionDropOff: attraction },
      })
    );
  };

  // Handle restaurant drop off selection
  const handleRestaurantDropOffSelect = (restaurant) => {
    setSelectedRestaurantDropOff(restaurant);
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { restaurantDropOff: restaurant },
      })
    );
  };

  // Handle exit port attraction pickup selection
  const handleExitAttractionPickupSelect = (attraction) => {
    setSelectedExitAttractionPickup(attraction);
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { exitAttractionPickup: attraction },
      })
    );
  };

  // Handle exit port restaurant pickup selection
  const handleExitRestaurantPickupSelect = (restaurant) => {
    setSelectedExitRestaurantPickup(restaurant);
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { exitRestaurantPickup: restaurant },
      })
    );
  };

  // === DMC Selection Handlers ===
  const handleDMCCardClick = (dmc) => {
    console.log('🏢 DMC card clicked:', dmc);
    
    // Check if DMC is already selected
    const isSelected = selectedDmcIds.includes(dmc.dmcId);
    
    if (isSelected) {
      // Remove from selection
      dispatch(removeDmcFromSelection({ dmcId: dmc.dmcId }));
    } else {
      // Add to selection
      dispatch(addDmcToSelection({ dmcId: dmc.dmcId, dmcData: dmc }));
    }
  };

  const handleFilterChange = (event) => {
    setFilterText(event.target.value);
  };

  // Filter DMCs based on search text
  const filteredDMCs = dmcOptions.filter(dmc => 
    dmc.name.toLowerCase().includes(filterText.toLowerCase()) ||
    dmc.location.toLowerCase().includes(filterText.toLowerCase())
  );

  // Check if a DMC is selected
  const isDMCSelected = (dmc) => {
    return selectedDmcIds.includes(dmc.dmcId);
  };

  // Render DMC Selection Component
  const renderDMCSelectionPanel = () => (
    <DMCSelectionPanel elevation={3}>
      {/* Header */}


      {/* Location Section - Auto-populated from LocationSearch */}
      <Box sx={{ mb: 3 }}>
        <Typography variant="subtitle2" sx={{ mb: 2, fontWeight: 600, color: '#333', fontSize: '0.9rem' }}>
          📍 Destination
        </Typography>
        
        {activeLocation ? (
          <Box sx={{ 
            p: 2, 
            bgcolor: 'rgba(102, 126, 234, 0.08)', 
            borderRadius: 2, 
            border: '1px solid rgba(102, 126, 234, 0.2)',
            display: 'flex',
            alignItems: 'center'
          }}>
            <LocationIcon sx={{ fontSize: 18, color: '#667eea', mr: 1 }} />
            <Box>
              <Typography variant="body2" sx={{ fontWeight: 500, color: '#333' }}>
                {activeLocation.country || activeLocation.countryName || 'Location Selected'}
              </Typography>
         
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
            🏢 DMC Partners
          </Typography>
          {selectedDmcsData.length > 0 && (
            <Badge badgeContent={selectedDmcsData.length} color="primary" />
          )}
        </Box>
        
        {activeLocation && (
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

        {!activeLocation && !dmcLoading && (
          <Box sx={{ textAlign: 'center', py: 3 }}>
            <TravelIcon sx={{ fontSize: 36, color: '#ddd', mb: 1 }} />
            <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.875rem' }}>
              Select a destination first
            </Typography>
          </Box>
        )}

        {activeLocation && !dmcLoading && filteredDMCs.length === 0 && dmcOptions.length === 0 && (
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

      {/* Selected Summary */}
      {selectedDmcsData.length > 0 && (
        <Box sx={{ mt: 3, pt: 2, borderTop: '1px solid #e0e3e8' }}>
          <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 1 }}>
            <Typography variant="subtitle2" sx={{ fontWeight: 600, color: '#333', fontSize: '0.9rem' }}>
              ✅ Selected ({selectedDmcsData.length})
            </Typography>
            {enquiryListLoading && (
              <CircularProgress size={12} sx={{ color: '#667eea' }} />
            )}
          </Box>
          {enquiryListLoading && (
            <Typography variant="caption" sx={{ color: '#667eea', fontSize: '0.7rem', mb: 1, display: 'block' }}>
              Updating data...
            </Typography>
          )}
          <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5 }}>
            {selectedDmcsData.slice(0, 3).map((dmc) => (
              <Chip
                key={dmc.id}
                label={dmc.name}
                size="small"
                color="primary"
                variant="outlined"
                sx={{ fontSize: '0.75rem' }}
              />
            ))}
            {selectedDmcsData.length > 3 && (
              <Chip
                label={`+${selectedDmcsData.length - 3} more`}
                size="small"
                color="primary"
                sx={{ fontSize: '0.75rem' }}
              />
            )}
          </Box>
        </Box>
      )}
    </DMCSelectionPanel>
  );

  return (
    <Box sx={{ 
      maxWidth: "1400px", 
      margin: "0 auto", 
      px: { xs: 1, sm: 2, md: 3 },
      py: { xs: 1, sm: 2, md: 3 }
    }}>
      {/* Page Header */}
      <Box sx={{ 
        textAlign: "center", 
        mb: { xs: 2, sm: 3, md: 4 }
      }}>
        <Typography 
          variant="h4" 
          component="h1" 
          sx={{ 
            fontWeight: 600,
            fontSize: { xs: '1.5rem', sm: '2rem', md: '2.5rem' },
            color: 'text.primary',
            mb: 1
          }}
        >
          Booking Enquiries
        </Typography>
        <Typography 
          variant="body1" 
          color="text.secondary" 
          sx={{ 
            fontSize: { xs: '0.875rem', sm: '1rem', md: '1.1rem' },
            mb: 2
          }}
        >
          Select your preferred services and customize your travel experience
        </Typography>
        <HeadingLine />
      </Box>

      {/* Main Grid Layout */}
      <Grid container spacing={{ xs: 2, sm: 3, md: 4 }}>
        {/* Left Column - DMC Selection */}
        <Grid item xs={12} md={4} lg={3}>
          {renderDMCSelectionPanel()}
        </Grid>

        {/* Right Column - Booking Form */}
        <Grid item xs={12} md={8} lg={9}>
          {/* Selected DMCs Summary */}
          {selectedDmcsData.length > 0 && (
            <Paper
              elevation={2}
              sx={{
                mb: 3,
                p: 3,
                background: "linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%)",
                borderRadius: 2,
                border: "1px solid rgba(14, 165, 233, 0.2)",
              }}
            >
              <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 2 }}>
                <Box sx={{ display: 'flex', alignItems: 'center' }}>
                  <CheckCircleIcon sx={{ fontSize: 24, color: '#0ea5e9', mr: 1 }} />
                  <Typography variant="h6" sx={{ fontWeight: 600, color: '#0c4a6e' }}>
                    Selected DMC Partners ({selectedDmcsData.length})
                  </Typography>
                </Box>
                {enquiryListLoading && (
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <CircularProgress size={16} sx={{ color: '#0ea5e9' }} />
                    <Typography variant="caption" sx={{ color: '#0ea5e9', fontSize: '0.75rem' }}>
                      Refreshing data...
                    </Typography>
                  </Box>
                )}
              </Box>
              <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1 }}>
                {selectedDmcsData.map((dmc) => (
                  <Chip
                    key={dmc.id}
                    avatar={
                      dmc.logo ? (
                        <Avatar src={dmc.logo} alt={dmc.name} />
                      ) : (
                        <Avatar sx={{ bgcolor: '#0ea5e9' }}>
                          <BusinessIcon fontSize="small" />
                        </Avatar>
                      )
                    }
                    label={`${dmc.name} (${dmc.location})`}
                    color="info"
                    variant="outlined"
                    sx={{ fontWeight: 500 }}
                  />
                ))}
              </Box>
            </Paper>
          )}

          <Grid container spacing={{ xs: 1, sm: 2, md: 3 }} alignItems="flex-start">
            {Object.keys(bookingOptions).map((option) => (
              <Grid item xs={12} sm={12} md={6} lg={6} key={option}>
              <StyledCard
                selected={bookingOptions[option]}
                serviceType={option}
              >
                <Tooltip
                  title={getServiceDescription(option)}
                  arrow
                  placement="top"
                  TransitionComponent={Zoom}
                >
                  <CategoryBadge
                    label={getCategoryLabel(option)}
                    serviceType={option}
                  />
                </Tooltip>
                <CardContent sx={{ flexGrow: 1, p: { xs: 1, sm: 2, md: 3 } }}>
                  <Box
                    sx={{
                      display: "flex",
                      justifyContent: "space-between",
                      alignItems: "center",
                      flexWrap: { xs: "wrap", sm: "nowrap" },
                      gap: { xs: 1, sm: 0 }
                    }}
                  >
                    <Box sx={{ 
                      display: "flex", 
                      alignItems: "center",
                      flex: { xs: "1 1 100%", sm: "1 1 auto" },
                      minWidth: 0
                    }}>
                      <Box
                        sx={{
                          mr: { xs: 0.5, sm: 1 },
                          color: serviceColors[option]?.main || "primary.main",
                          transition: "all 0.3s ease",
                          transform: bookingOptions[option]
                            ? "scale(1.1)"
                            : "scale(1)",
                        }}
                      >
                        {getIconForOption(option)}
                      </Box>
                      <Typography
                        variant="h6"
                        component="h4"
                        sx={{ 
                          fontWeight: 600,
                          fontSize: { xs: '0.875rem', sm: '1rem', md: '1.25rem' },
                          overflow: 'hidden',
                          textOverflow: 'ellipsis',
                          whiteSpace: 'nowrap',
                          flex: 1
                        }}
                      >
                        {getCardTitle(option)}
                        <Tooltip
                          title={getServiceDescription(option)}
                          arrow
                          placement="right"
                          TransitionComponent={Zoom}
                        >
                          <HelpIconButton serviceType={option} />
                        </Tooltip>
                      </Typography>
                    </Box>
                    <FormControlLabel
                      sx={{
                        mt: { xs: 0, sm: 0 },
                        flexShrink: 0
                      }}
                      control={
                        <Switch
                          checked={bookingOptions[option]}
                          onChange={() => handleToggleChange(option)}
                          color="primary"
                          size="small"
                          sx={{
                            "& .MuiSwitch-switchBase.Mui-checked": {
                              color: serviceColors[option]?.main,
                            },
                            "& .MuiSwitch-switchBase.Mui-checked + .MuiSwitch-track":
                              {
                                backgroundColor: serviceColors[option]?.main,
                              },
                          }}
                        />
                      }
                      label={
                        <Typography sx={{ 
                          fontSize: { xs: '0.75rem', sm: '0.875rem' },
                          fontWeight: 500
                        }}>
                          {bookingOptions[option] ? "Yes" : "No"}
                        </Typography>
                      }
                    />
                  </Box>

                  {bookingOptions[option] && expandedSections[option] && (
                    <Box
                      sx={{
                        mt: { xs: 1, sm: 1.5, md: 2.5 },
                        pt: { xs: 1, sm: 1.5, md: 2.5 },
                        borderTop: "1px solid #f0f0f0",
                        animation: "fadeIn 0.4s ease-in-out",
                        "@keyframes fadeIn": {
                          from: {
                            opacity: 0,
                            transform: "translateY(15px)",
                          },
                          to: {
                            opacity: 1,
                            transform: "translateY(0)",
                          },
                        },
                      }}
                    >
                      <Grid container spacing={{ xs: 1, sm: 1.5, md: 2 }}>
                        {option === "hotel" && (
                          <>
                            <Grid item xs={12}>
                              <FormControl>
                                <Typography
                                  variant="body2"
                                  color="text.secondary"
                                  sx={{ mb: 1 }}
                                >
                                  Select Star Category
                                </Typography>
                                <StarCategorySelect
                                  onChange={handleStarCategoryChange}
                                  value={starCategory}
                                />
                              </FormControl>
                            </Grid>
                            {/* <Grid item xs={12}>
                              <FormControl>
                                <Typography
                                  variant="body1"
                                  fontWeight={600}
                                  color="text.secondary"
                                  sx={{ mb: 1 }}
                                >
                                  Compare Hotels Package
                                  <Tooltip
                                    title="Select 'Yes' to compare prices and amenities of multiple hotels"
                                    arrow
                                  >
                                    <HelpIconButton
                                      serviceType={option}
                                      fontSize="small"
                                    />
                                  </Tooltip>
                                </Typography>
                                <RadioGroup
                                  row
                                  value={compareHotels}
                                  onChange={handleCompareHotelsChange}
                                >
                                  <FormControlLabel
                                    value="yes"
                                    control={
                                      <Radio
                                        sx={{
                                          color: serviceColors[option]?.main,
                                          "&.Mui-checked": {
                                            color: serviceColors[option]?.main,
                                          },
                                        }}
                                      />
                                    }
                                    label="Yes"
                                  />
                                  <FormControlLabel
                                    value="no"
                                    control={
                                      <Radio
                                        sx={{
                                          color: serviceColors[option]?.main,
                                          "&.Mui-checked": {
                                            color: serviceColors[option]?.main,
                                          },
                                        }}
                                      />
                                    }
                                    label="No"
                                  />
                                </RadioGroup>
                              </FormControl>
                            </Grid> */}
                            <Grid item xs={12}>
                              <PreferredHotelsDropdown
                                onSelect={handlePreferredHotelsSelect}
                                value={selectedPreferredHotels}
                              />
                            </Grid>
                            <Grid item xs={12}>
                              <TextField
                                fullWidth
                                multiline
                                rows={3}
                                label="Remarks"
                                variant="outlined"
                                value={hotelRemarks}
                                placeholder="Add any special requests or requirements for your hotel stay"
                                onChange={handleHotelRemarksChange}
                                sx={{
                                  "& .MuiOutlinedInput-root": {
                                    "&.Mui-focused fieldset": {
                                      borderColor: serviceColors[option]?.main,
                                    },
                                  },
                                  "& .MuiInputLabel-root.Mui-focused": {
                                    color: serviceColors[option]?.main,
                                  },
                                }}
                              />
                            </Grid>
                          </>
                        )}

                        {option === "entryExitPort" && (
                          <>
                            <Grid item xs={12}>
                              <Typography variant="subtitle1" sx={{ mb: 1, fontWeight: 500 }}>
                                Port Transportation Services
                              </Typography>
                              <Divider sx={{ mb: 2 }} />
                            </Grid>

                            <Grid item xs={12} sx={{ display: 'flex', gap: 2 }}>
                              <FormControlLabel
                                control={
                                  <Switch
                                    checked={showEntryPort}
                                    onChange={handleToggleEntryPort}
                                    sx={{
                                      "& .MuiSwitch-switchBase.Mui-checked": {
                                        color: serviceColors[option]?.main,
                                      },
                                      "& .MuiSwitch-switchBase.Mui-checked + .MuiSwitch-track": {
                                        backgroundColor: serviceColors[option]?.main,
                                      },
                                    }}
                                  />
                                }
                                label="Entry Port (Arrival Transportation)"
                                sx={{ mr: 4 }}
                              />
                              <FormControlLabel
                                control={
                                  <Switch
                                    checked={showExitPort}
                                    onChange={handleToggleExitPort}
                                    sx={{
                                      "& .MuiSwitch-switchBase.Mui-checked": {
                                        color: serviceColors[option]?.main,
                                      },
                                      "& .MuiSwitch-switchBase.Mui-checked + .MuiSwitch-track": {
                                        backgroundColor: serviceColors[option]?.main,
                                      },
                                    }}
                                  />
                                }
                                label="Exit Port (Departure Transportation)"
                              />
                            </Grid>

                            {showEntryPort && (
                              <>
                                {/* Entry Port Section */}
                                <Grid item xs={12}>
                                  <Typography variant="subtitle1" sx={{ mb: 1, fontWeight: 500, color: serviceColors[option]?.main }}>
                                    Entry Port Details
                                  </Typography>
                                  <Divider sx={{ mb: 2 }} />
                                </Grid>

                                {/* Entry Port Pickup Location */}
                                <Grid item xs={12}>
                                  <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                                    Pickup Location (Port/Airport)
                                  </Typography>
                                  <PortAddressSearch
                                    onSelect={handlePortAddressSelect}
                                    value={selectedEntryPort}
                                  />
                                </Grid>

                                {/* Entry Port Dropoff Location Type */}
                                <Grid item xs={12}>
                                  <FormControl>
                                    <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                                      Drop-off Location Type
                                    </Typography>
                                    <RadioGroup 
                                      row 
                                      value={entryDropoffLocationType}
                                      onChange={handleEntryDropoffLocationTypeChange}
                                    >
                                      <FormControlLabel
                                        value="hotel"
                                        control={
                                          <Radio
                                            sx={{
                                              color: serviceColors[option]?.main,
                                              "&.Mui-checked": {
                                                color: serviceColors[option]?.main,
                                              },
                                            }}
                                          />
                                        }
                                        label="Hotel"
                                      />
                                      <FormControlLabel
                                        value="attraction"
                                        control={
                                          <Radio
                                            sx={{
                                              color: serviceColors[option]?.main,
                                              "&.Mui-checked": {
                                                color: serviceColors[option]?.main,
                                              },
                                            }}
                                          />
                                        }
                                        label="Attraction"
                                      />
                                      <FormControlLabel
                                        value="restaurant"
                                        control={
                                          <Radio
                                            sx={{
                                              color: serviceColors[option]?.main,
                                              "&.Mui-checked": {
                                                color: serviceColors[option]?.main,
                                              },
                                            }}
                                          />
                                        }
                                        label="Restaurant"
                                      />
                                    </RadioGroup>
                                  </FormControl>
                                </Grid>

                                {/* Entry Port Dropoff Location Dropdown based on selection */}
                                <Grid item xs={12}>
                                  <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                                    Drop-off Location
                                  </Typography>
                                  {entryDropoffLocationType === "hotel" && (
                                                                    <HotelDropOffSearch
                                  onSelect={handleHotelDropOffSelect}
                                  value={selectedHotelDropOff}
                                />
                                  )}
                                                                      {entryDropoffLocationType === "attraction" && (
                                      <AttractionDropOffSearch
                                        onSelect={handleAttractionDropOffSelect}
                                        value={selectedAttractionDropOff}
                                      />
                                    )}
                                                                      {entryDropoffLocationType === "restaurant" && (
                                      <RestaurantDropOffSearch
                                        onSelect={handleRestaurantDropOffSelect}
                                        value={selectedRestaurantDropOff}
                                      />
                                    )}
                                </Grid>
                              </>
                            )}

                            {showExitPort && (
                              <>
                                {/* Exit Port Section */}
                                <Grid item xs={12}>
                                  <Typography variant="subtitle1" sx={{ mb: 1, fontWeight: 500, color: serviceColors[option]?.main, mt: 4 }}>
                                    Exit Port Details
                                  </Typography>
                                  <Divider sx={{ mb: 2 }} />
                                </Grid>

                                {/* Exit Port Pickup Location Type */}
                                <Grid item xs={12}>
                                  <FormControl>
                                    <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                                      Pickup Location Type
                                    </Typography>
                                    <RadioGroup 
                                      row 
                                      value={exitPickupLocationType}
                                      onChange={handleExitPickupLocationTypeChange}
                                    >
                                      <FormControlLabel
                                        value="hotel"
                                        control={
                                          <Radio
                                            sx={{
                                              color: serviceColors[option]?.main,
                                              "&.Mui-checked": {
                                                color: serviceColors[option]?.main,
                                              },
                                            }}
                                          />
                                        }
                                        label="Hotel"
                                      />
                                      <FormControlLabel
                                        value="attraction"
                                        control={
                                          <Radio
                                            sx={{
                                              color: serviceColors[option]?.main,
                                              "&.Mui-checked": {
                                                color: serviceColors[option]?.main,
                                              },
                                            }}
                                          />
                                        }
                                        label="Attraction"
                                      />
                                      <FormControlLabel
                                        value="restaurant"
                                        control={
                                          <Radio
                                            sx={{
                                              color: serviceColors[option]?.main,
                                              "&.Mui-checked": {
                                                color: serviceColors[option]?.main,
                                              },
                                            }}
                                          />
                                        }
                                        label="Restaurant"
                                      />
                                    </RadioGroup>
                                  </FormControl>
                                </Grid>

                                {/* Exit Port Pickup Location Dropdown based on selection */}
                                <Grid item xs={12}>
                                  <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                                    Pickup Location
                                  </Typography>
                                  {exitPickupLocationType === "hotel" && (
                                    <HotelDropOffSearch
                                      onSelect={(hotel) => 
                                        dispatch(
                                          updateServiceDetails({
                                            service: "entryExitPort",
                                            data: { exitPickupLocation: hotel },
                                          })
                                        )
                                      }
                                      value={selectedHotelDropOff}
                                    />
                                  )}
                                  {exitPickupLocationType === "attraction" && (
                                    <AttractionDropOffSearch
                                      onSelect={handleExitAttractionPickupSelect}
                                      value={selectedExitAttractionPickup}
                                    />
                                  )}
                                  {exitPickupLocationType === "restaurant" && (
                                    <RestaurantDropOffSearch
                                      onSelect={handleExitRestaurantPickupSelect}
                                      value={selectedExitRestaurantPickup}
                                    />
                                  )}
                                </Grid>

                                {/* Exit Port Dropoff Location */}
                                <Grid item xs={12}>
                                  <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                                    Drop-off Location (Port/Airport)
                                  </Typography>
                                  <PortAddressSearch
                                    onSelect={handleExitPortAddressSelect}
                                    value={selectedExitPort}
                                  />
                                </Grid>
                              </>
                            )}

                            {/* Common Car Selection for both Entry and Exit */}
                            <Grid item xs={12} sx={{ mt: 3 }}>
                              <Typography variant="subtitle1" sx={{ mb: 1, fontWeight: 500 }}>
                                Car Details
                              </Typography>
                              <Divider sx={{ mb: 2 }} />
                            </Grid>

                            <Grid item xs={12}>
                              <FormControl>
                                <Typography
                                  variant="body2"
                                  color="text.secondary"
                                  sx={{ mb: 1 }}
                                >
                                  Car Type
                                  <Tooltip
                                    title="Choose between a shared vehicle or a private car for your transportation"
                                    arrow
                                  >
                                    <HelpIconButton
                                      serviceType={option}
                                      fontSize="small"
                                    />
                                  </Tooltip>
                                </Typography>
                                <RadioGroup 
                                  row 
                                  defaultValue="sharable"
                                  onChange={(e) => handleCarTypeChange(e, "entryExitPort")}
                                >
                                  <FormControlLabel
                                    value="sharable"
                                    control={
                                      <Radio
                                        sx={{
                                          color: serviceColors[option]?.main,
                                          "&.Mui-checked": {
                                            color: serviceColors[option]?.main,
                                          },
                                        }}
                                      />
                                    }
                                    label="Sharable"
                                  />
                                  <FormControlLabel
                                    value="private"
                                    control={
                                      <Radio
                                        sx={{
                                          color: serviceColors[option]?.main,
                                          "&.Mui-checked": {
                                            color: serviceColors[option]?.main,
                                          },
                                        }}
                                      />
                                    }
                                    label="Private"
                                  />
                                </RadioGroup>
                              </FormControl>
                            </Grid>

                            <Grid item xs={12}>
                              <PreferredCarsSearch
                                onSelect={(cars) =>
                                  handleCarSelect(cars, "entryExitPort")
                                }
                                value={selectedEntryExitCars}
                              />
                            </Grid>

                            <Grid item xs={12}>
                              <TextField
                                fullWidth
                                multiline
                                rows={3}
                                label="Remarks"
                                variant="outlined"
                                value={entryExitPortRemarks}
                                placeholder="Add any special transportation requirements"
                                onChange={handleEntryExitPortRemarksChange}
                                sx={{
                                  "& .MuiOutlinedInput-root": {
                                    "&.Mui-focused fieldset": {
                                      borderColor: serviceColors[option]?.main,
                                    },
                                  },
                                  "& .MuiInputLabel-root.Mui-focused": {
                                    color: serviceColors[option]?.main,
                                  },
                                }}
                              />
                            </Grid>
                          </>
                        )}

                        {option === "attraction" && (
                          <>
                            <Grid item xs={12}>
                              <AttractionSearch
                                onSelect={handleAttractionSelect}
                                value={selectedAttractions}
                              />
                            </Grid>
                            <Grid item xs={12}>
                              <FormControl>
                                <Typography
                                  variant="body2"
                                  color="text.secondary"
                                  sx={{ mb: 1 }}
                                >
                                  Need Transport?
                                  <Tooltip
                                    title="Select 'Yes' if you need transportation to the attraction"
                                    arrow
                                  >
                                    <HelpIconButton
                                      serviceType={option}
                                      fontSize="small"
                                    />
                                  </Tooltip>
                                </Typography>
                                <FormControlLabel
                                  control={
                                    <Switch
                                      checked={needTransport}
                                      onChange={handleNeedTransportChange}
                                      sx={{
                                        "& .MuiSwitch-switchBase.Mui-checked": {
                                          color: serviceColors[option]?.main,
                                        },
                                        "& .MuiSwitch-switchBase.Mui-checked + .MuiSwitch-track":
                                          {
                                            backgroundColor:
                                              serviceColors[option]?.main,
                                          },
                                      }}
                                    />
                                  }
                                  label={needTransport ? "Yes" : "No"}
                                />
                              </FormControl>
                            </Grid>

                            {needTransport && (
                              <Grid item xs={12}>
                                <FormControl>
                                  <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                                    Car Type
                                  </Typography>
                                  <RadioGroup 
                                    row 
                                    defaultValue="sharable"
                                    onChange={(e) => handleCarTypeChange(e, "attraction")}
                                  >
                                    <FormControlLabel 
                                      value="sharable" 
                                      control={
                                        <Radio
                                          sx={{
                                            color:
                                              serviceColors[option]?.main,
                                            "&.Mui-checked": {
                                              color:
                                                serviceColors[option]?.main,
                                            },
                                          }}
                                        />
                                      } 
                                      label="Sharable" 
                                    />
                                    <FormControlLabel 
                                      value="private" 
                                      control={
                                        <Radio
                                          sx={{
                                            color:
                                              serviceColors[option]?.main,
                                            "&.Mui-checked": {
                                              color:
                                                serviceColors[option]?.main,
                                            },
                                          }}
                                        />
                                      } 
                                      label="Private" 
                                    />
                                  </RadioGroup>
                                </FormControl>
                              </Grid>
                            )}

                            {/* {needTransport && (
                              <Grid item xs={12}>
                                <PreferredCarsSearch
                                  onSelect={(cars) =>
                                    handleCarSelect(cars, "attraction")
                                  }
                                  value={selectedAttractionCars}
                                />
                              </Grid>
                            )} */}

                            <Grid item xs={12}>
                              <TextField
                                fullWidth
                                multiline
                                rows={3}
                                label="Remarks"
                                variant="outlined"
                                value={attractionRemarks}
                                placeholder="Add any special requests for attractions"
                                onChange={handleAttractionRemarksChange}
                                sx={{
                                  '& .MuiOutlinedInput-root': {
                                    '&.Mui-focused fieldset': {
                                      borderColor: serviceColors[option]?.main
                                    }
                                  },
                                  '& .MuiInputLabel-root.Mui-focused': {
                                    color: serviceColors[option]?.main
                                  }
                                }}
                              />
                            </Grid>
                          </>
                        )}
                        {option === "packagedAttractions" && (
                          <>
                            <Grid item xs={12}>
                              <PackageAttractionSearch 
                                onSelect={handlePackagedAttractionsSelect}
                                value={selectedPackagedAttractions}
                              />
                            </Grid>
                            <Grid item xs={12}>
                              <TextField
                                fullWidth
                                multiline
                                rows={3}
                                label="Remarks"
                                variant="outlined"
                                value={packagedAttractionsRemarks}
                                placeholder="Add any special requests for packaged attractions"
                                onChange={handlePackagedAttractionsRemarksChange}
                                sx={{
                                  '& .MuiOutlinedInput-root': {
                                    '&.Mui-focused fieldset': {
                                      borderColor: serviceColors[option]?.main
                                    }
                                  },
                                  '& .MuiInputLabel-root.Mui-focused': {
                                    color: serviceColors[option]?.main
                                  }
                                }}
                              />
                            </Grid>
                          </>
                        )}
                        {option === "localTour" && (
                          <>
                            <Grid item xs={12}>
                              <PreferredCarsSearch
                                onSelect={(cars) =>
                                  handleCarSelect(cars, "localTour")
                                }
                                value={selectedLocalTourCars}
                              />
                            </Grid>
                            <Grid item xs={12}>
                              <TextField
                                fullWidth
                                multiline
                                rows={3}
                                label="Remarks"
                                variant="outlined"
                                value={localTourRemarks}
                                placeholder="Add any special requests for your local tour"
                                onChange={handleLocalTourRemarksChange}
                                sx={{
                                  "& .MuiOutlinedInput-root": {
                                    "&.Mui-focused fieldset": {
                                      borderColor: serviceColors[option]?.main,
                                    },
                                  },
                                  "& .MuiInputLabel-root.Mui-focused": {
                                    color: serviceColors[option]?.main,
                                  },
                                }}
                              />
                            </Grid>
                          </>
                        )}

                        {option === "tourGuide" && (
                          <>
                            <Grid item xs={12}>
                              <PreferredGuidesSearch
                                onSelect={handleGuideSelect}
                                value={selectedGuides}
                              />
                            </Grid>
                            <Grid item xs={12}>
                              <TextField
                                fullWidth
                                multiline
                                rows={3}
                                label="Special Requirements"
                                variant="outlined"
                                value={tourGuideSpecialRequirements}
                                placeholder="Describe any specific requirements for your tour guide (language, expertise, etc.)"
                                onChange={handleSpecialRequirementsChange}
                                sx={{
                                  "& .MuiOutlinedInput-root": {
                                    "&.Mui-focused fieldset": {
                                      borderColor: serviceColors[option]?.main,
                                    },
                                  },
                                  "& .MuiInputLabel-root.Mui-focused": {
                                    color: serviceColors[option]?.main,
                                  },
                                }}
                              />
                            </Grid>
                          </>
                        )}

                        {option === "restaurant" && (
                          <>
                            <Grid item xs={12}>
                              <RestaurantSearch
                                onSelect={handleRestaurantSelect}
                                value={selectedRestaurants}
                              />
                            </Grid>
                            <Grid item xs={12}>
                              <FormControl>
                                <Typography
                                  variant="body2"
                                  color="text.secondary"
                                  sx={{ mb: 1 }}
                                >
                                  Need Transport?
                                  <Tooltip
                                    title="Select 'Yes' if you need transportation to the restaurant"
                                    arrow
                                  >
                                    <HelpIconButton
                                      serviceType={option}
                                      fontSize="small"
                                    />
                                  </Tooltip>
                                </Typography>
                                <FormControlLabel
                                  control={
                                    <Switch
                                      checked={needTransportType}
                                      onChange={handleNeedTransportTypeChange}
                                      sx={{
                                        "& .MuiSwitch-switchBase.Mui-checked": {
                                          color: serviceColors[option]?.main,
                                        },
                                        "& .MuiSwitch-switchBase.Mui-checked + .MuiSwitch-track":
                                          {
                                            backgroundColor:
                                              serviceColors[option]?.main,
                                          },
                                      }}
                                    />
                                  }
                                  label={needTransportType ? "Yes" : "No"}
                                />
                              </FormControl>
                            </Grid>
                            {needTransportType && (
                              <Grid item xs={12}>
                                <FormControl>
                                  <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                                    Car Type
                                  </Typography>
                                  <RadioGroup 
                                    row 
                                    defaultValue="sharable"
                                    onChange={(e) => handleCarTypeChange(e, "restaurant")}
                                  >
                                    <FormControlLabel 
                                      value="sharable" 
                                      control={
                                        <Radio 
                                          sx={{
                                            color: serviceColors[option]?.main,
                                            '&.Mui-checked': {
                                              color: serviceColors[option]?.main,
                                            }
                                          }}
                                        />
                                      } 
                                      label="Sharable" 
                                    />
                                    <FormControlLabel 
                                      value="private" 
                                      control={
                                        <Radio 
                                          sx={{
                                            color: serviceColors[option]?.main,
                                            '&.Mui-checked': {
                                              color: serviceColors[option]?.main,
                                            }
                                          }}
                                        />
                                      } 
                                      label="Private" 
                                    />
                                  </RadioGroup>
                                </FormControl>
                              </Grid>
                            )}

                            {/* {needTransportType && (
                              <Grid item xs={12}>
                                <PreferredCarsSearch
                                  onSelect={(cars) =>
                                    handleCarSelect(cars, "restaurant")
                                  }
                                  value={selectedRestaurantCars}
                                />
                              </Grid>
                            )} */}
                            <Grid item xs={12}>
                              <TextField
                                fullWidth
                                multiline
                                rows={3}
                                label="Remarks"
                                variant="outlined"
                                value={restaurantRemarks}
                                placeholder="Add any dietary restrictions or special dining requirements"
                                onChange={handleRestaurantRemarksChange}
                                sx={{
                                  "& .MuiOutlinedInput-root": {
                                    "&.Mui-focused fieldset": {
                                      borderColor: serviceColors[option]?.main,
                                    },
                                  },
                                  "& .MuiInputLabel-root.Mui-focused": {
                                    color: serviceColors[option]?.main,
                                  },
                                }}
                              />
                            </Grid>
                          </>
                        )}
                        
                    
                        
                      </Grid>
                    </Box>
                  )}
                </CardContent>

                {bookingOptions[option] && (
                  <Box sx={{ mt: "auto" }}>
                    <DetailsToggleButton
                      onClick={() => handleExpandSection(option)}
                      startIcon={
                        expandedSections[option] ? (
                          <ExpandLessIcon />
                        ) : (
                          <ExpandMoreIcon />
                        )
                      }
                      serviceType={option}
                    >
                      {expandedSections[option]
                        ? "Hide Details"
                        : "Show Details"}
                    </DetailsToggleButton>
                  </Box>
                )}
              </StyledCard>
              </Grid>
            ))}
          </Grid>

          <Paper
            elevation={2}
            sx={{
              mt: 5,
              p: 3,
              background: "linear-gradient(135deg, #f5f8fe, #edf2ff)",
              borderRadius: 2,
              transition: "all 0.3s ease",
              "&:hover": {
                boxShadow: "0 12px 24px rgba(0, 0, 0, 0.1)",
              },
            }}
          >
            <Typography variant="h6" sx={{ mb: 2, fontWeight: 600 }}>
              Your Selections
            </Typography>
            <Box sx={{ display: "flex", flexWrap: "wrap", gap: 1 }}>
              {Object.entries(bookingOptions).some(([key, value]) => value) ? (
                Object.entries(bookingOptions).map(
                  ([key, value]) =>
                    value && (
                      <Tooltip
                        key={key}
                        title={getServiceDescription(key)}
                        arrow
                        placement="top"
                        TransitionComponent={Zoom}
                      >
                        <SummaryCard serviceType={key}>
                          <SummaryIcon serviceType={key}>
                            {getIconForOption(key)}
                          </SummaryIcon>
                          <Typography variant="body1" sx={{ fontWeight: 500 }}>
                            {getCardTitle(key)}
                          </Typography>
                        </SummaryCard>
                      </Tooltip>
                    )
                )
              ) : (
                <NoSelections>
                  <InfoIcon sx={{ mr: 1 }} />
                  <Typography>
                    No services selected yet. Toggle the switches above to include
                    services.
                  </Typography>
                </NoSelections>
              )}
            </Box>
          </Paper>

          {/* Add submit button at the bottom of the form */}
          <Box sx={{ display: "flex", justifyContent: "center", mt: 5, mb: 5 }}>
            <Button
              variant="contained"
              color="primary"
              size="large"
              startIcon={<CheckCircleIcon />}
              onClick={handleSubmitForm}
              sx={{
                minWidth: "200px",
                py: 1.5,
                borderRadius: 2,
                boxShadow: "0 8px 16px rgba(0, 0, 0, 0.1)",
                transition: "all 0.3s ease",
                "&:hover": {
                  transform: "translateY(-3px)",
                  boxShadow: "0 12px 20px rgba(0, 0, 0, 0.15)",
                },
              }}
            >
              Continue to Review
            </Button>
          </Box>
        </Grid>
      </Grid>
    </Box>
  );
};

export default BookingEnquiries;
