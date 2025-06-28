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
} from "@mui/material";
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
} from "@mui/icons-material";
import { useDispatch, useSelector } from "react-redux";
import {
  updateServiceDetails,
  setSelectedServices,
} from "../../../slice/common/EnquirySlice";
import { fetchEnquiryList } from "../../../slice/common/enquiryListSlice";
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

// Get service descriptions for tooltips
const getServiceDescription = (option) => {
  switch (option) {
    case "hotel":
      return "Select your preferred hotel accommodations, including star category and specific hotels.";
    case "entryExitPort":
      return "Choose your transportation for arrival and departure, including car type and locations.";
    case "attraction":
      return "Add attractions to your itinerary with optional transportation services.";
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
  const dispatch = useDispatch();
  // Get the selected city from Redux store
  const selectedCity = useSelector((state) => state.common.selectedCity);
  const enquiryData = useSelector((state) => state.enquiry);

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

  // Initialize expandedSections separately from bookingOptions
  const [expandedSections, setExpandedSections] = useState({
    hotel: false,
    entryExitPort: false,
    attraction: false,
    localTour: false,
    tourGuide: false,
    restaurant: false,
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

  // Add state for selected cars if needed
  const [selectedCars, setSelectedCars] = useState([]);

  // Handle car selection
  const handleCarSelect = (cars, service) => {
    setSelectedCars(cars);

    if (service === "entryExitPort") {
      dispatch(
        updateServiceDetails({
          service: "entryExitPort",
          data: { preferredCars: cars },
        })
      );
    } else if (service === "localTour") {
      dispatch(
        updateServiceDetails({
          service: "localTour",
          data: { preferredCars: cars },
        })
      );
    } else if (service === "attraction" && needTransport) {
      dispatch(
        updateServiceDetails({
          service: "attraction",
          data: { preferredCars: cars },
        })
      );
    } else if (service === "restaurant" && needTransportType) {
      dispatch(
        updateServiceDetails({
          service: "restaurant",
          data: { preferredCars: cars },
        })
      );
    }
  };

  // Add state for attraction section
  const [needTransport, setNeedTransport] = useState(false);
  const [destinationType, setDestinationType] = useState("hotel");
  const [selectedAttractions, setSelectedAttractions] = useState([]);
  const [selectedDestinations, setSelectedDestinations] = useState([]);
  const [compareHotels, setCompareHotels] = useState("no");
  const [selectedPreferredHotels, setSelectedPreferredHotels] = useState([]);
  const [starCategory, setStarCategory] = useState("");
  const [selectedGuides, setSelectedGuides] = useState([]);
  const [needTransportType, setNeedTransportType] = useState(false);

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
    dispatch(
      updateServiceDetails({
        service: "tourGuide",
        data: { specialRequirements: value },
      })
    );
  };

  // Handle port address selection
  const handlePortAddressSelect = (port) => {
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { portAddress: port },
      })
    );
  };

  // Handle hotel drop off selection
  const handleHotelDropOffSelect = (hotel) => {
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
    dispatch(
      updateServiceDetails({
        service: "restaurant",
        data: { remarks: value },
      })
    );
  };

  // Handle restaurant selection
  const handleRestaurantSelect = (restaurants) => {
    dispatch(
      updateServiceDetails({
        service: "restaurant",
        data: { selectedRestaurants: restaurants },
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
                preferredCars: enquiryData.serviceDetails?.entryExitPort?.preferredCars || [],
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
                remarks: enquiryData.serviceDetails?.attraction?.remarks || ""
              }
            }));
            break;
            
          case 'localTour':
            dispatch(updateServiceDetails({
              service: 'localTour',
              data: {
                preferredCars: enquiryData.serviceDetails?.localTour?.preferredCars || [],
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
                selectedRestaurants: enquiryData.serviceDetails?.restaurant?.selectedRestaurants || [],
                needTransport: needTransportType || false,
                destinationType: enquiryData.serviceDetails?.restaurant?.destinationType || "hotel",
                destination: enquiryData.serviceDetails?.restaurant?.destination || null,
                carType: enquiryData.serviceDetails?.restaurant?.carType || "sharable",
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
    
    // Reset all form data
    setStarCategory("");
    setSelectedPreferredHotels([]);
    setCompareHotels("no");
    setSelectedAttractions([]);
    setNeedTransport(false);
    setNeedTransportType(false);
    setSelectedGuides([]);
    setDestinationType("hotel");
    setSelectedDestinations([]);
    setSelectedCars([]);
    
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
      restaurant: false
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
      case "localTour":
        return <PlaceIcon />;
      case "tourGuide":
        return <PersonIcon />;
      case "restaurant":
        return <RestaurantIcon />;
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

  // Fix the state variables and handlers
  const [entryDropoffLocationType, setEntryDropoffLocationType] = useState("hotel");
  const [exitPickupLocationType, setExitPickupLocationType] = useState("hotel");
  const [showEntryPort, setShowEntryPort] = useState(true);
  const [showExitPort, setShowExitPort] = useState(false);

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
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { attractionDropOff: attraction },
      })
    );
  };

  // Handle restaurant drop off selection
  const handleRestaurantDropOffSelect = (restaurant) => {
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { restaurantDropOff: restaurant },
      })
    );
  };

  // Handle exit port attraction pickup selection
  const handleExitAttractionPickupSelect = (attraction) => {
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { exitAttractionPickup: attraction },
      })
    );
  };

  // Handle exit port restaurant pickup selection
  const handleExitRestaurantPickupSelect = (restaurant) => {
    dispatch(
      updateServiceDetails({
        service: "entryExitPort",
        data: { exitRestaurantPickup: restaurant },
      })
    );
  };

  return (
    <Box sx={{ maxWidth: "1200px", margin: "0 auto", mt: 4 }}>
      <Box sx={{ px: 4, py: 2.5 }}>
        <Box sx={{ textAlign: "center", mb: 5 }}>
          <Typography variant="h4" component="h3" sx={{ fontWeight: 600 }}>
            Booking Enquiries
          </Typography>
          <Typography variant="body1" color="text.secondary" sx={{ mt: 0.5 }}>
            Select your preferred services and customize your travel experience
          </Typography>
          <HeadingLine />
        </Box>

        <Grid container spacing={4} alignItems="flex-start">
          {Object.keys(bookingOptions).map((option) => (
            <Grid item xs={12} md={6} key={option}>
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
                <CardContent sx={{ flexGrow: 1 }}>
                  <Box
                    sx={{
                      display: "flex",
                      justifyContent: "space-between",
                      alignItems: "center",
                    }}
                  >
                    <Box sx={{ display: "flex", alignItems: "center" }}>
                      <Box
                        sx={{
                          mr: 1,
                          mt: 2,
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
                        sx={{ fontWeight: 600 }}
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
                        mt: 3,
                      }}
                      control={
                        <Switch
                          checked={bookingOptions[option]}
                          onChange={() => handleToggleChange(option)}
                          color="primary"
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
                      label={bookingOptions[option] ? "Yes" : "No"}
                    />
                  </Box>

                  {bookingOptions[option] && expandedSections[option] && (
                    <Box
                      sx={{
                        mt: 2.5,
                        pt: 2.5,
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
                      <Grid container spacing={2}>
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
                                />
                              </FormControl>
                            </Grid>
                            <Grid item xs={12}>
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
                            </Grid>
                            <Grid item xs={12}>
                              <PreferredHotelsDropdown
                                onSelect={handlePreferredHotelsSelect}
                              />
                            </Grid>
                            <Grid item xs={12}>
                              <TextField
                                fullWidth
                                multiline
                                rows={3}
                                label="Remarks"
                                variant="outlined"
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
                                    />
                                  )}
                                  {entryDropoffLocationType === "attraction" && (
                                    <AttractionDropOffSearch
                                      onSelect={handleAttractionDropOffSelect}
                                    />
                                  )}
                                  {entryDropoffLocationType === "restaurant" && (
                                    <RestaurantDropOffSearch
                                      onSelect={handleRestaurantDropOffSelect}
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
                                    />
                                  )}
                                  {exitPickupLocationType === "attraction" && (
                                    <AttractionDropOffSearch
                                      onSelect={handleExitAttractionPickupSelect}
                                    />
                                  )}
                                  {exitPickupLocationType === "restaurant" && (
                                    <RestaurantDropOffSearch
                                      onSelect={handleExitRestaurantPickupSelect}
                                    />
                                  )}
                                </Grid>

                                {/* Exit Port Dropoff Location */}
                                <Grid item xs={12}>
                                  <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                                    Drop-off Location (Port/Airport)
                                  </Typography>
                                  <PortAddressSearch
                                    onSelect={(port) => 
                                      dispatch(
                                        updateServiceDetails({
                                          service: "entryExitPort",
                                          data: { exitPortAddress: port },
                                        })
                                      )
                                    }
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
                              />
                            </Grid>

                            <Grid item xs={12}>
                              <TextField
                                fullWidth
                                multiline
                                rows={3}
                                label="Remarks"
                                variant="outlined"
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

                            <Grid item xs={12}>
                              <TextField
                                fullWidth
                                multiline
                                rows={3}
                                label="Remarks"
                                variant="outlined"
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

                        {option === "localTour" && (
                          <>
                            <Grid item xs={12}>
                              <PreferredCarsSearch
                                onSelect={(cars) =>
                                  handleCarSelect(cars, "localTour")
                                }
                              />
                            </Grid>
                            <Grid item xs={12}>
                              <TextField
                                fullWidth
                                multiline
                                rows={3}
                                label="Remarks"
                                variant="outlined"
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
                              />
                            </Grid>
                            <Grid item xs={12}>
                              <TextField
                                fullWidth
                                multiline
                                rows={3}
                                label="Special Requirements"
                                variant="outlined"
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
                            <Grid item xs={12}>
                              <TextField
                                fullWidth
                                multiline
                                rows={3}
                                label="Remarks"
                                variant="outlined"
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
      </Box>

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

   
    </Box>
  );
};

export default BookingEnquiries;
