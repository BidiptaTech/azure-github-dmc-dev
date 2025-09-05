import React, { useState, useMemo, useRef, useEffect } from 'react';
import { 
  Box, 
  Paper, 
  Grid, 
  Button, 
  Typography,
  Container,
  Snackbar,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  List,
  ListItem,
  ListItemText,
  Stack,
  Avatar,
  TextField,
  InputAdornment
} from '@mui/material';
import MuiAlert from "@mui/material/Alert";
import { 
  Search as SearchIcon,
  LocationOn as LocationIcon,
  CalendarToday as CalendarIcon,
  People as PeopleIcon,
  Person as PersonIcon,
  Warning as WarningIcon,
  Clear
} from '@mui/icons-material';
import LocationSearch from './LocationSearch';
import DateRangePicker from './DateRangePicker';
import PaxSelector from './PaxSelector';
import { useDispatch, useSelector } from "react-redux";
import moment from "moment";
import {
  resetHotels,
  setId,
  updateSearchState,
  settourdetails,
  fetchHotels,
} from "../../../slice/hotel/hotelSlice";
import {
  setTourId,
  statusUpdate,
  updateStepStatus,
  setType,
} from "../../../slice/common/stepsSlice";
import { fetchGuides } from "../../../slice/tourguide/guideslice";
import { setBookingType } from "../../../slice/common/commonSlice";
import { clearUserInfo } from "../../../slice/common/customerInfo";
import { clearAttractions, resetIsFromMainSearch } from "../../../slice/attractions/attractionSlice";
import { fetchAttractions } from "../../../slice/attractions/attractionSlice";
import {
  fetchRestaurants,
  clearRestaurants,
  setSearchParams as setRestaurantSearchParams,
} from "../../../slice/restaurant/RestaurantsSlice";
import { resetguide } from "../../../slice/tourguide/guideslice";
import { resetVehicles } from "../../../slice/port/pickupDropSlice";
import { resetVehicles1 } from "../../../slice/localtour/Localslice";
import { setSelectedCity } from "../../../slice/common/commonSlice";
import { 
  fetchBookingid,  
  setCheckIn, 
  setCheckOut, 
  setGuest 
} from "../../../slice/common/EnquirySlice";
import { setSearchLocation } from "../../../slice/common/BookingSlice";
import { fetchEnquiryList, clearEnquiryList } from "../../../slice/common/enquiryListSlice";
import { setSearchCriteria, fetchTourPackages, clearPackages, clearAllServices, setAllServices, setPackageData } from "../../../slice/tour-packages/tourPackageSlice";
import { store } from "../../../store/store";
import { setSearchParams as setAttractionSearchParams } from "../../../slice/attractions/attractionSlice";
import { setSearchParams as setGuideSearchParams } from "../../../slice/tourguide/guideslice";
import { fetchAgentList } from "../../../slice/common/agentListSlice";
import { setAgentId } from '@/slice/common/EditSlice';

// Create a reusable alert component
const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

export default function SearchForm({ onNext, setActiveTab, packageData: propPackageData }) {

  const dispatch = useDispatch();
  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  const [selectedLocation, setSelectedLocation] = useState(null);
  
  // State to track if button should be hidden after click
  const [isButtonHidden, setIsButtonHidden] = useState(false);
  const [isupdated, setIsupdated] = useState(false);
  
  // Get all services for validation
  const allServices = useSelector((state) => state.tourPackages.AllServices);
  // console.log("All Services in SearchForm:", allServices);
  
  // Get packageData from Redux state and prioritize it over prop
  const reduxPackageData = useSelector((state) => state.tourPackages.packageData);
  const enquirydetail = useSelector((state) => state.convertToTourList?.enquirydetail);
  // console.log("enquirydetail",enquirydetail);
  // Use Redux state if available, otherwise fall back to prop
  const packageData = reduxPackageData || propPackageData;
  
  // console.log("SearchForm packageData sources:", {
  //   reduxPackageData: reduxPackageData,
  //   propPackageData: propPackageData,
  //   finalPackageData: packageData,
  //   hasValidTourId: packageData?.tour?.tour_id > 0
  // });
  
  // State for date validation dialog
  const [dateValidationDialog, setDateValidationDialog] = useState({
    open: false,
    conflictingServices: [],
    newDateRange: { start: null, end: null }
  });
  
  // Initialize dates with packageData if available
  const getInitialStartDate = () => {
    // Check packageData.tour first
    if (packageData?.tour?.check_in_time) {
      try {
        return moment(packageData.tour.check_in_time).toDate();
      } catch (error) {
        console.error('Error parsing check_in_time from packageData:', error);
        return null;
      }
    }
    
    // Check enquirydetail if packageData.tour doesn't have check_in_time
    if (enquirydetail?.check_in_time) {
      try {
        return moment(enquirydetail.check_in_time).toDate();
      } catch (error) {
        console.error('Error parsing check_in_time from enquirydetail:', error);
        return null;
      }
    }
    
    return null;
  };

  const getInitialEndDate = () => {
    // Check packageData.tour first
    if (packageData?.tour?.check_out_time) {
      try {
        return moment(packageData.tour.check_out_time).toDate();
      } catch (error) {
        console.error('Error parsing check_out_time from packageData:', error);
        return null;
      }
    }
    
    // Check enquirydetail if packageData.tour doesn't have check_out_time
    if (enquirydetail?.check_out_time) {
      try {
        return moment(enquirydetail.check_out_time).toDate();
      } catch (error) {
        console.error('Error parsing check_out_time from enquirydetail:', error);
        return null;
      }
    }
    
    return null;
  };

  // Initialize guest counts with packageData if available
  const getInitialGuestCounts = () => {
    // Check packageData.tour first
    if (packageData?.tour) {
      const tour = packageData.tour;
      return {
        Adults: tour.adult || 1,
        Children: tour.child || 0,
        Infants: tour.infant || 0,
        maleCount: tour.male_count || 0,
        femaleCount: tour.female_count || 0,
        genders: [], // Initialize empty array for compatibility
        ages: (() => {
          try {
            if (tour.child_ages) {
              if (Array.isArray(tour.child_ages)) {
                return tour.child_ages;
              } else if (typeof tour.child_ages === 'string') {
                // Handle comma-separated string format like "16, 14, 14"
                if (tour.child_ages.includes(',')) {
                  return tour.child_ages.split(',').map(age => age.trim()).filter(age => age !== '');
                }
                // Handle single child age like "5" - convert to array
                if (tour.child_ages.trim() !== '') {
                  return [tour.child_ages.trim()];
                }
                // Try to parse as JSON first (for backward compatibility)
                try {
                  return JSON.parse(tour.child_ages);
                } catch {
                  // If JSON parsing fails, return empty array
                  return [];
                }
              } else if (tour.child_ages) {
                return [tour.child_ages];
              }
            }
            return [];
          } catch (error) {
            console.error('Error parsing child_ages from tour:', error);
            return [];
          }
        })(),
      };
    }
    
    // Check enquirydetail if packageData.tour is not available
    if (enquirydetail) {
      return {
        Adults: enquirydetail.adult || 1,
        Children: enquirydetail.child || 0,
        Infants: enquirydetail.infant || 0,
        maleCount: enquirydetail.male_count || 0,
        femaleCount: enquirydetail.female_count || 0,
        genders: [], // Initialize empty array for compatibility
        ages: (() => {
          try {
            if (enquirydetail.child_ages) {
              if (Array.isArray(enquirydetail.child_ages)) {
                return enquirydetail.child_ages;
              } else if (typeof enquirydetail.child_ages === 'string') {
                // Handle comma-separated string format like "16, 14, 14"
                if (enquirydetail.child_ages.includes(',')) {
                  return enquirydetail.child_ages.split(',').map(age => age.trim()).filter(age => age !== '');
                }
                // Handle single child age like "5" - convert to array
                if (enquirydetail.child_ages.trim() !== '') {
                  return [enquirydetail.child_ages.trim()];
                }
                // Try to parse as JSON first (for backward compatibility)
                try {
                  return JSON.parse(enquirydetail.child_ages);
                } catch {
                  // If JSON parsing fails, return empty array
                  return [];
                }
              } else if (enquirydetail.child_ages) {
                return [enquirydetail.child_ages];
              }
            }
            return [];
          } catch (error) {
            console.error('Error parsing child_ages from enquirydetail:', error);
            return [];
          }
        })(),
      };
    }
    
    return {
      Adults: 1,
      Children: 0,
      Infants: 0,
      genders: [""], // Store gender selections for adults
      ages: [""], // Store age selections for children
    };
  };

  const [startDate, setStartDate] = useState(getInitialStartDate());
  const [endDate, setEndDate] = useState(getInitialEndDate());
  const [guestCounts, setGuestCounts] = useState(getInitialGuestCounts());
  const user_country = useSelector((state) => state.auth.user_country);
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const [snackbarSeverity, setSnackbarSeverity] = useState("error");
  console.log("packageDatasss", packageData);

  // Helper functions to get destination and city values
  const getDestinationValue = () => {
    if (packageData?.tour?.destination) {
      return packageData.tour.destination;
    }
    if (enquirydetail?.country) {
      return enquirydetail.country;
    }
    return null;
  };

  const getCityValue = () => {
    if (packageData?.tour?.city) {
      return packageData.tour.city;
    }
    if (enquirydetail?.city) {
      return enquirydetail.city;
    }
    return null;
  };

  // Check if data is coming from enquirydetail (to disable date and guest selection)
  const isDataFromEnquiryDetail = Boolean(enquirydetail && !packageData?.tour) || isupdated; 

  // Helper function to safely get ages array
  const getSafeAges = () => {
    if (!guestCounts.ages || !Array.isArray(guestCounts.ages)) {
      console.warn('getSafeAges: ages is not an array:', guestCounts.ages);
      return [];
    }
    const filteredAges = guestCounts.ages.filter(age => age !== null && age !== undefined);
    console.log('getSafeAges: filtered ages:', filteredAges);
    return filteredAges;
  };

  // Helper function to safely get ages for API calls (maintains backward compatibility)
  const getSafeAgesForAPI = () => {
    const safeAges = getSafeAges();
    if (safeAges.length === 0) {
      return [];
    }
    return safeAges;
  };

  // Log the initialization values for debugging
  React.useEffect(() => {
    if (packageData?.tour || enquirydetail) {
      const dataSource = packageData?.tour || enquirydetail;
      console.log('Initializing form with data:', {
        destination: getDestinationValue(),
        city: getCityValue(),
        checkIn: dataSource.check_in_time,
        checkOut: dataSource.check_out_time,
        adults: dataSource.adult,
        children: dataSource.child,
        maleCount: dataSource.male_count,
        femaleCount: dataSource.female_count,
        agentId: dataSource.agent_id
      });
    }
  }, [packageData, enquirydetail]);

  // Agent selection state
  const [selectedAgent, setSelectedAgent] = useState('');
  const [selectedAgentName, setSelectedAgentName] = useState('');
  const [isAgentFromPackageData, setIsAgentFromPackageData] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const searchInputRef = useRef(null);
  const { agents } = useSelector((state) => state.agentList);
  
  // Fetch agents on component mount
  React.useEffect(() => {
    dispatch(fetchAgentList());
  }, [dispatch]);

  // Filter agents based on search term
  const filteredAgents = agents.filter(agent => {
    if (!searchTerm) return true;
    const searchLower = searchTerm.toLowerCase();
    return (
      agent.name.toLowerCase().includes(searchLower) ||
      (agent.company_name && agent.company_name.toLowerCase().includes(searchLower))
    );
  });

  // Handle search input change
  const handleSearchChange = (event) => {
    setSearchTerm(event.target.value);
  };

  // Clear search
  const handleClearSearch = () => {
    setSearchTerm('');
  };

  // Focus search input when dropdown opens
  useEffect(() => {
    if (searchInputRef.current) {
      const timer = setTimeout(() => {
        searchInputRef.current?.focus();
      }, 100);
      return () => clearTimeout(timer);
    }
  }, []);
  console.log("guestCounts",guestCounts);
  console.log("guestCounts.ages type:", typeof guestCounts.ages, "value:", guestCounts.ages);
  if (guestCounts.ages && Array.isArray(guestCounts.ages)) {
    console.log("ages array contents:", guestCounts.ages.map(age => ({ value: age, type: typeof age })));
  }
  // Auto-select agent based on packageData agent_id and agent_name
  React.useEffect(() => {
    const dataSource = packageData?.tour || enquirydetail;
    if (dataSource?.agent_id && dataSource?.agent_name && !selectedAgent) {
      const agentId = dataSource.agent_id.toString();
      const agentName = dataSource.agent_name;
      
      console.log('Auto-selecting agent from data:', { agentId, agentName });
      
      // Set agent directly from data
      setSelectedAgent(agentId);
      setSelectedAgentName(agentName);
      setIsAgentFromPackageData(true);
      dispatch(setAgentId(agentId));
    }
  }, [packageData?.tour?.agent_id, packageData?.tour?.agent_name, enquirydetail?.agent_id, enquirydetail?.agent_name, selectedAgent, dispatch]);
  
  // Create mapping for country codes to names
  const countryCodeToName = useMemo(() => {
    const mapping = {};
    if (user_country && Array.isArray(user_country)) {
      user_country.forEach(country => {
        if (country && country.name && country.code) {
          mapping[country.code] = country.name;
          mapping[country.code.toLowerCase()] = country.name;
        }
      });
    }
    return mapping;
  }, [user_country]);

  const handleCloseSnackbar = () => {
    setOpenSnackbar(false);
  };

  const handleLocationSelect = (location) => {
    setSelectedLocation(location);
    
    // Clear previous enquiry list data
    
    
    // Clear previous packages data
    dispatch(clearPackages());
    
    
  };

  // Function to validate services against new date range
  const validateServicesAgainstNewDates = (newStartDate, newEndDate) => {
    if (!allServices || allServices.length === 0) {
      return { isValid: true, conflictingServices: [] };
    }

    const startMoment = moment(newStartDate);
    const endMoment = moment(newEndDate);
    const conflictingServices = [];

    console.log("Validating services against new date range:", {
      start: startMoment.format('YYYY-MM-DD'),
      end: endMoment.format('YYYY-MM-DD')
    });

    allServices.forEach((service) => {
      if (service.data && Array.isArray(service.data)) {
        service.data.forEach((item) => {
          if (item.bookingDate) {
            let isConflicting = false;
            let conflictReason = '';
            let bookingDateDisplay = '';

            if (Array.isArray(item.bookingDate)) {
              // Hotel format: [check-in, check-out]
              const checkIn = moment(item.bookingDate[0]);
              const checkOut = moment(item.bookingDate[1]);
              bookingDateDisplay = `${checkIn.format('MMM DD, YYYY')} - ${checkOut.format('MMM DD, YYYY')}`;
              
              if (checkIn.isBefore(startMoment) || checkOut.isAfter(endMoment)) {
                isConflicting = true;
                conflictReason = checkIn.isBefore(startMoment) 
                  ? 'Check-in date is before new tour start'
                  : 'Check-out date is after new tour end';
              }
            } else {
              // Other services format: single date string
              const bookingDate = moment(item.bookingDate);
              bookingDateDisplay = bookingDate.format('MMM DD, YYYY');
              
              if (bookingDate.isBefore(startMoment) || bookingDate.isAfter(endMoment)) {
                isConflicting = true;
                conflictReason = bookingDate.isBefore(startMoment)
                  ? 'Service date is before new tour start'
                  : 'Service date is after new tour end';
              }
            }

            if (isConflicting) {
              conflictingServices.push({
                type: service.type,
                id: item.id,
                name: item.hotelDetails?.hotel_name || 
                      item.AttractionName || 
                      item.RestaurantName || 
                      item.guide_name || 
                      item.name || 
                      `${service.type} service`,
                bookingDate: bookingDateDisplay,
                reason: conflictReason
              });
            }
          }
        });
      }
    });

    return {
      isValid: conflictingServices.length === 0,
      conflictingServices
    };
  };

  const handleDateChange = (dateRange) => {
    // Prevent date changes when data is from enquirydetail
    if (isDataFromEnquiryDetail) {
      return;
    }
    
    if (dateRange && Array.isArray(dateRange) && dateRange.length === 2) {
      const newStartDate = dateRange[0].toDate ? dateRange[0].toDate() : dateRange[0];
      const newEndDate = dateRange[1].toDate ? dateRange[1].toDate() : dateRange[1];
      
      // Just update dates without validation - validation will happen on update
      setStartDate(newStartDate);
      setEndDate(newEndDate);
    }
  };

  // Handle date validation dialog actions
  const handleDateValidationConfirm = () => {
    // User confirmed they want to proceed despite conflicts
    setDateValidationDialog({ open: false, conflictingServices: [], newDateRange: { start: null, end: null } });
    
    // Show warning about services that will be affected
    setSnackbarMessage(
      `Date range updated. ${dateValidationDialog.conflictingServices.length} service(s) may need to be adjusted to fit the new tour dates.`
    );
    setSnackbarSeverity("warning");
    setOpenSnackbar(true);
    
    // Proceed with the actual update
    proceedWithUpdate();
  };

  const handleDateValidationCancel = () => {
    // User cancelled, don't update
    setDateValidationDialog({ open: false, conflictingServices: [], newDateRange: { start: null, end: null } });
  };

  // Extract the actual update logic into a separate function
  const proceedWithUpdate = async () => {
    // Clear previous customer info when starting update
    dispatch(clearUserInfo());
    dispatch(clearAllServices());
    // Clear previous data
    dispatch(clearAttractions());
    dispatch(resetIsFromMainSearch());
    dispatch(clearRestaurants());
    dispatch(resetVehicles());
    dispatch(resetVehicles1()); 
    dispatch(resetguide());
    
    
    // Format Dates
    const formattedCheckIn = moment(startDate).format("DD/MM/YYYY");
    console.log("formattedCheckIn",formattedCheckIn);
    const formattedCheckOut = moment(endDate).format("DD/MM/YYYY");
    const formatedHotelCheckIn = moment(startDate).format("YYYY-MM-DD");
    const formatedHotelCheckOut = moment(endDate).format("YYYY-MM-DD");

    
    // Get the country and city data
    const country = selectedLocation.country;
    const city = selectedLocation.city;
    const countryCode = selectedLocation.countryCode;
    console.log("countryCode",countryCode);
    const cityCode = selectedLocation.cityCode;
    
    // Create genders array based on male and female counts
    const maleCount = guestCounts.maleCount || 0;
    const femaleCount = guestCounts.femaleCount || 0;
    const genders = [
      ...Array(maleCount).fill("Male"),
      ...Array(femaleCount).fill("Female")
    ];

    // Get tour_id from packageData
    const tourId = packageData?.tour?.tour_id;
    dispatch(setAllServices({
      country: country,
      city: city,
      check_in_time: formattedCheckIn,
      check_out_time: formattedCheckOut,
      tour_id: tourId,
      guests: {
        adults: guestCounts.Adults.toString(),
        children: guestCounts.Children.toString(),
        infants: guestCounts.Infants.toString(),
        maleCount: maleCount,
        femaleCount: femaleCount,
        childrenAges: guestCounts.ages || [],
        adultGenders: genders
      }
    }));

    // Update tour packages search criteria in Redux
    dispatch(setSearchCriteria({
      country: country,
      city: city,
      checkIn: formattedCheckIn,
      checkOut: formattedCheckOut,
      guests: {
        adults: guestCounts.Adults.toString(),
        children: guestCounts.Children.toString(),
        infants: guestCounts.Infants.toString(),
        maleCount: maleCount,
        femaleCount: femaleCount,
        childrenAges: guestCounts.ages || [],
        adultGenders: genders
      }
    }));

    // Set attraction search parameters
    const formattedAttractionDate = moment(startDate).format("YYYY-MM-DD"); // Format date for attraction API
    
    dispatch(setAttractionSearchParams({
      location: {
        country: country,
        city: `${city}, (${country})`,
        address: `${city}, (${country})`,
        countryCode: countryCode,
        cityCode: cityCode
      },
      date: moment(startDate),
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId // Use tour_id from packageData
    }));

    // Update the guide search params and fetch guides
    dispatch(setGuideSearchParams({
      location: {
        country: country,
        city: `${city}, (${country})`,
        address: `${city}, (${country})`,
        countryCode: countryCode,
        cityCode: cityCode
      },
      date: moment(startDate),
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId // Use tour_id from packageData
    }));

    // Fetch guides with the required parameters
    dispatch(fetchGuides({
      city: `${city}, (${country})`,
      date: formattedAttractionDate
    }));

    // Fetch attractions based on search criteria
    dispatch(fetchAttractions({
      city: `${city}, (${country})`, // Format city with country
      date: formattedAttractionDate, // Use YYYY-MM-DD format
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId, // Use tour_id from packageData
      selectedDate: moment(startDate),
      fromMainSearch: false,

    }));

    // Fetch restaurants based on search criteria
    console.log('Dispatching fetchRestaurants with params:', {
      city: `${city}, (${country})`,
      date: formattedAttractionDate,
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId,
      fromMainSearch: false
    });

    dispatch(fetchRestaurants({
      city: `${city}, (${country})`,
      date: formattedAttractionDate,
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId, // Use tour_id from packageData
      fromMainSearch: false
    }))
    .then((response) => {
      console.log('fetchRestaurants response:', response);
    })
    .catch((error) => {
      console.error('fetchRestaurants error:', error);
    });

   dispatch(updateSearchState({
  location: [city], // or just city if location is a single string
  ucheckIn: formatedHotelCheckIn,
  ucheckOut: formatedHotelCheckOut,
  guests: guestCounts
}));

// Step 2: Fetch hotels using pagination args
dispatch(fetchHotels());

    // Also update the enquiry slice data for compatibility with other parts of the app
    // Set location data in the right format for EnquirySlice
    
    dispatch(setSearchLocation(countryCode));
    dispatch(setCheckIn(formattedCheckIn));
    dispatch(setCheckOut(formattedCheckOut));
    
    // Set the selected city in common slice
    dispatch(setSelectedCity({
      countryCode: countryCode,
      countryName: country,
      cityCode: cityCode,
      cityName: city,
      combinedCode: cityCode
    }));
    
    // Dispatch guest details to EnquirySlice
    dispatch(
      setGuest({
        adults: guestCounts.Adults.toString(),
        children: guestCounts.Children.toString(),
        infant: guestCounts.Infants.toString(),
        adultGenders: genders,
        childrenAges: guestCounts.ages || [],
        maleCount: maleCount,
        femaleCount: femaleCount
      })
    );

    // Set existing tour data in Redux state
    const dataSource = packageData?.tour || enquirydetail;
    dispatch(updateSearchState({ location: dataSource?.destination }));
    dispatch(setId(tourId));
    dispatch(settourdetails(dataSource));

    

    // Move to the first tab (Itinerary) after update completes
    if (onNext) {
      onNext();
      // If the parent component has a setActiveTab function, call it to show the Itinerary tab
      if (typeof setActiveTab === 'function') {
        setActiveTab(0); // Select the first tab (Itinerary)
      }
    }

    console.log("Tour package updated successfully with tour_id:", tourId);
  };

  

  const handleGuestChange = (updatedGuestCounts) => {
    // Prevent guest changes when data is from enquirydetail
    if (isDataFromEnquiryDetail) {
      return;
    }
    
    setGuestCounts(updatedGuestCounts);
  };

  const handleAgentChange = (event) => {
    setSelectedAgent(event.target.value);
    dispatch(setAgentId(event.target.value));
    console.log('Selected agent for booking:', event.target.value);
  };

  const validateForm = () => {
    if (!selectedLocation || !selectedLocation.country) {
      setSnackbarMessage("Please select a country.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (!selectedLocation.city) {
      setSnackbarMessage("Please select a city.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (!startDate || !endDate) {
      setSnackbarMessage("Please select check-in and check-out dates.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (guestCounts.Adults <= 0) {
      setSnackbarMessage("At least one adult must be selected.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (!selectedAgent) {
      setSnackbarMessage("Please select an agent for this booking.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Check that male + female counts equal the adult count and neither is zero
    const maleCount = guestCounts.maleCount || 0;
    const femaleCount = guestCounts.femaleCount || 0;
    
    if (maleCount === 0 && femaleCount === 0) {
      setSnackbarMessage("Please specify male and female counts for adults.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }
    
    if (maleCount + femaleCount !== guestCounts.Adults) {
      setSnackbarMessage("Male and female count must equal the total adult count.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (
      guestCounts.Children > 0 &&
      getSafeAges().length !== guestCounts.Children
    ) {
      setSnackbarMessage(`Please provide ages for all ${guestCounts.Children} children.`);
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (
      guestCounts.Children > 0 &&
      !getSafeAges().every((age) => {
        // Handle both string and number types for age
        if (typeof age === 'string') {
          return age.trim() !== "";
        } else if (typeof age === 'number') {
          return age > 0;
        }
        return false; // Invalid age type
      })
    ) {
      setSnackbarMessage("Please provide valid ages for all children.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    return true;
  };

  const formatedHotelCheckIn = moment(startDate).format("YYYY-MM-DD");
  console.log("formatedHotelCheckIn",formatedHotelCheckIn); 

  const formatedHotelCheckOut = moment(endDate).format("YYYY-MM-DD");

  const handleSearch = async (e) => {
    e.preventDefault();

    if (!validateForm()) {
      // Show the button again if validation fails
      setIsButtonHidden(false);
      return;
    }

    console.log("=== SEARCH START ===");
    console.log("Before clearing packageData - Redux:", reduxPackageData);
    console.log("Before clearing packageData - Prop:", propPackageData);
    
    // IMPORTANT: Clear packageData FIRST before any other operations
    dispatch(setPackageData(null));
    
    console.log("Dispatched setPackageData(null)");

    // Clear previous customer info when starting new search
    dispatch(clearUserInfo());
    dispatch(clearAllServices());
    // Clear previous data
    dispatch(clearAttractions());
    dispatch(resetIsFromMainSearch());
    dispatch(clearRestaurants());
    dispatch(resetVehicles());
    dispatch(resetVehicles1()); 
    dispatch(resetguide());
    
    
    // Format Dates
    const formattedCheckIn = moment(startDate).format("DD/MM/YYYY");
    console.log("formattedCheckIn",formattedCheckIn);
    const formattedCheckOut = moment(endDate).format("DD/MM/YYYY");
    const formatedHotelCheckIn = moment(startDate).format("YYYY-MM-DD");
    const formatedHotelCheckOut = moment(endDate).format("YYYY-MM-DD");

    
    // Get the country and city data
    const country = selectedLocation.country;
    const city = selectedLocation.city;
    const countryCode = selectedLocation.countryCode;
    console.log("countryCode",countryCode);
    const cityCode = selectedLocation.cityCode;
    
    // Create genders array based on male and female counts
    const maleCount = guestCounts.maleCount || 0;
    const femaleCount = guestCounts.femaleCount || 0;
    const genders = [
      ...Array(maleCount).fill("Male"),
      ...Array(femaleCount).fill("Female")
    ];

    // Get tour_id from packageData
    const tourId = packageData?.tour?.tour_id;
    // dispatch(setAllServices({
    //   country: country,
    //   city: city,
    //   check_in_time: formattedCheckIn,
    //   check_out_time: formattedCheckOut,
    //   tour_id: tourId,
    //   guests: {
    //     adults: guestCounts.Adults.toString(),
    //     children: guestCounts.Children.toString(),
    //     infants: guestCounts.Infants.toString(),
    //     maleCount: maleCount,
    //     femaleCount: femaleCount,
    //     childrenAges: guestCounts.ages || [],
    //     adultGenders: genders
    //   }
    // }));

    // Update tour packages search criteria in Redux
    dispatch(setSearchCriteria({
      country: country,
      city: city,
      checkIn: formattedCheckIn,
      checkOut: formattedCheckOut,
      guests: {
        adults: guestCounts.Adults.toString(),
        children: guestCounts.Children.toString(),
        infants: guestCounts.Infants.toString(),
        maleCount: maleCount,
        femaleCount: femaleCount,
        childrenAges: guestCounts.ages || [],
        adultGenders: genders
      }
    }));

    // Set attraction search parameters
    const formattedAttractionDate = moment(startDate).format("YYYY-MM-DD"); // Format date for attraction API
    
    dispatch(setAttractionSearchParams({
      location: {
        country: country,
        city: `${city}, (${country})`,
        address: `${city}, (${country})`,
        countryCode: countryCode,
        cityCode: cityCode
      },
      date: moment(startDate),
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId // Use tour_id from packageData
    }));
    dispatch(setRestaurantSearchParams({
      location: {
        country: country,
        city: `${city}, (${country})`,
        address: `${city}, (${country})`,
        countryCode: countryCode,
        cityCode: cityCode
      },
      date: moment(startDate),
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId // Use tour_id from packageData
    }));

    // Update the guide search params and fetch guides
    dispatch(setGuideSearchParams({
      location: {
        country: country,
        city: `${city}, (${country})`,
        address: `${city}, (${country})`,
        countryCode: countryCode,
        cityCode: cityCode
      },
      date: moment(startDate),
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId // Use tour_id from packageData
    }));

    // Fetch guides with the required parameters
    dispatch(fetchGuides({
      city: `${city}, (${country})`,
      date: formattedAttractionDate
    }));

    // Fetch attractions based on search criteria
    dispatch(fetchAttractions({
      city: `${city}, (${country})`, // Format city with country
      date: formattedAttractionDate, // Use YYYY-MM-DD format
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId, // Use tour_id from packageData
      selectedDate: moment(startDate),
      fromMainSearch: false,
     
    }));

    // Fetch restaurants based on search criteria
    console.log('Dispatching fetchRestaurants with params:', {
      city: `${city}, (${country})`,
      date: formattedAttractionDate,
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId,
      fromMainSearch: false
    });

    dispatch(fetchRestaurants({
      city: `${city}, (${country})`,
      date: formattedAttractionDate,
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId, // Use tour_id from packageData
      fromMainSearch: false
    }))
    .then((response) => {
      console.log('fetchRestaurants response:', response);
    })
    .catch((error) => {
      console.error('fetchRestaurants error:', error);
    });

   dispatch(updateSearchState({
  location: [city], // or just city if location is a single string
  ucheckIn: formatedHotelCheckIn,
  ucheckOut: formatedHotelCheckOut,
  guests: guestCounts
}));

// Step 2: Fetch hotels using pagination args
dispatch(fetchHotels());

    // Also update the enquiry slice data for compatibility with other parts of the app
    // Set location data in the right format for EnquirySlice
    
    dispatch(setSearchLocation(countryCode));
    dispatch(setCheckIn(formattedCheckIn));
    dispatch(setCheckOut(formattedCheckOut));
    
    // Set the selected city in common slice
    dispatch(setSelectedCity({
      countryCode: countryCode,
      countryName: country,
      cityCode: cityCode,
      cityName: city,
      combinedCode: cityCode
    }));
    
    // Dispatch guest details to EnquirySlice
    dispatch(
      setGuest({
        adults: guestCounts.Adults.toString(),
        children: guestCounts.Children.toString(),
        infant: guestCounts.Infants.toString(),
        adultGenders: genders,
        childrenAges: guestCounts.ages || [],
        maleCount: maleCount,
        femaleCount: femaleCount
      })
    );

    

    // Use our new fetch tour packages action
    dispatch(fetchTourPackages({
      country: country,
      city: city,
      checkIn: formattedCheckIn,
      checkOut: formattedCheckOut,
      guests: {
        adults: guestCounts.Adults,
        children: guestCounts.Children,
        infants: guestCounts.Infants,
        maleCount: maleCount,
        femaleCount: femaleCount,
        childrenAges: guestCounts.ages || [],
      },
      enq_id: enquirydetail?.enquiry_id || null
    }))
      .unwrap()
      .then((data) => {
        console.log("Tour packages response:", data);
        dispatch(updateSearchState({ location: data.destination }));
        dispatch(setId(data.data.tour_id));
        
        // Ensure packageData stays null for new searches to maintain create mode
        console.log("Ensuring packageData remains null for new search");
        dispatch(setPackageData(null));
        
        dispatch(settourdetails(data));
        // Move to the first tab (Itinerary) after search completes
        if (onNext) {
          onNext();
          // If the parent component has a setActiveTab function, call it to show the Itinerary tab
          // This assumes the parent component passes this function as a prop if needed
          if (typeof setActiveTab === 'function') {
            setActiveTab(0); // Select the first tab (Itinerary)
          }
        }
        
        console.log("=== SEARCH COMPLETE ===");
        console.log("Search completed successfully, packageData should be null");
        
        // Show the button again after successful API call
        setIsButtonHidden(true);
      })
      .catch((error) => {
        console.error("Error fetching tour packages:", error);
        setSnackbarMessage(
          "Failed to fetch tour packages. Please try again."
        );
        setSnackbarSeverity("error");
        setOpenSnackbar(true);
        
        // Show the button again after error
        setIsButtonHidden(false);
      });

    // For backward compatibility, also create a booking ID
    // dispatch(fetchBookingid())
    //   .unwrap()
    //   .then((data) => {
    //     console.log("Enquiry response:", data);
    //     const id = data?.enquiry_id || data?.data?.enquiry_id || data?.tour_id || data?.data?.tour_id;

    //     if (id) {
    //       // Update state with API response
    //       dispatch(updateSearchState({ 
    //         location: country,
    //         cityName: city,
    //         countryName: country
    //       }));
          
    //       dispatch(settourdetails(data)); // Set full enquiry details
    //       dispatch(setId(id)); // Set the ID
    //       dispatch(setTourId(id));
    //       dispatch(setBookingType("enquiry")); // Set booking type to enquiry
    //       dispatch(setType("enquiry"));
    //     }
    //   })
    //   .catch((error) => {
    //     console.error("Error creating enquiry:", error);
    //   });
  };

 

  const handleUpdate = async (e) => {
    e.preventDefault();

    if (!validateForm()) {
      // Show the button again if validation fails
      setIsButtonHidden(false);
      return;
    }

    try {
      setIsupdated(true);
    // Clear previous customer info when starting update
    dispatch(clearUserInfo());
    // dispatch(clearAllServices());
    // Clear previous data
    dispatch(clearAttractions());
    dispatch(resetIsFromMainSearch());
    dispatch(clearRestaurants());
    dispatch(resetVehicles());
    dispatch(resetVehicles1()); 
    dispatch(resetguide());
    
    
    // Format Dates
    const formattedCheckIn = moment(startDate).format("DD/MM/YYYY");
    console.log("formattedCheckIn",formattedCheckIn);
    const formattedCheckOut = moment(endDate).format("DD/MM/YYYY");

 

    
    // Get the country and city data
    const country = selectedLocation.country;
    const city = selectedLocation.city;
    const countryCode = selectedLocation.countryCode;
    console.log("countryCode",countryCode);
    const cityCode = selectedLocation.cityCode;
    
    // Create genders array based on male and female counts
    const maleCount = guestCounts.maleCount || 0;
    const femaleCount = guestCounts.femaleCount || 0;
    const genders = [
      ...Array(maleCount).fill("Male"),
      ...Array(femaleCount).fill("Female")
    ];

    // Get tour_id from packageData
    const tourId = packageData?.tour?.tour_id;
    dispatch(setAllServices({
      country: country,
      city: city,
      check_in_time: formattedCheckIn,
      check_out_time: formattedCheckOut,
      tour_id: tourId,
      guests: {
        adults: guestCounts.Adults.toString(),
        children: guestCounts.Children.toString(),
        infants: guestCounts.Infants.toString(),
        maleCount: maleCount,
        femaleCount: femaleCount,
        childrenAges: guestCounts.ages || [],
        adultGenders: genders
      }
    }));

    // Update tour packages search criteria in Redux
    dispatch(setSearchCriteria({
      country: country,
      city: city,
      checkIn: formattedCheckIn,
      checkOut: formattedCheckOut,
      guests: {
        adults: guestCounts.Adults.toString(),
        children: guestCounts.Children.toString(),
        infants: guestCounts.Infants.toString(),
        maleCount: maleCount,
        femaleCount: femaleCount,
        childrenAges: guestCounts.ages || [],
        adultGenders: genders
      }
    }));

    // Set attraction search parameters
    const formattedAttractionDate = moment(startDate).format("YYYY-MM-DD"); // Format date for attraction API
    
    dispatch(setAttractionSearchParams({
      location: {
        country: country,
        city: `${city}, (${country})`,
        address: `${city}, (${country})`,
        countryCode: countryCode,
        cityCode: cityCode
      },
      date: moment(startDate),
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId // Use tour_id from packageData
    }));
    dispatch(setRestaurantSearchParams({
      location: {
        country: country,
        city: `${city}, (${country})`,
        address: `${city}, (${country})`,
        countryCode: countryCode,
        cityCode: cityCode
      },
      date: moment(startDate),
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId // Use tour_id from packageData
    }));

    // Update the guide search params and fetch guides
    dispatch(setGuideSearchParams({
      location: {
        country: country,
        city: `${city}, (${country})`,
        address: `${city}, (${country})`,
        countryCode: countryCode,
        cityCode: cityCode
      },
      date: moment(startDate),
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId // Use tour_id from packageData
    }));

    // Fetch guides with the required parameters
    dispatch(fetchGuides({
      city: `${city}, (${country})`,
      date: formattedAttractionDate
    }));

    // Fetch attractions based on search criteria
    dispatch(fetchAttractions({
      city: `${city}, (${country})`, // Format city with country
      date: formattedAttractionDate, // Use YYYY-MM-DD format
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId, // Use tour_id from packageData
      selectedDate: moment(startDate),
      fromMainSearch: false,
     
    }));

    // Fetch restaurants based on search criteria
    console.log('Dispatching fetchRestaurants with params:', {
      city: `${city}, (${country})`,
      date: formattedAttractionDate,
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId,
      fromMainSearch: false
    });

    dispatch(fetchRestaurants({
      city: `${city}, (${country})`,
      date: formattedAttractionDate,
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourId, // Use tour_id from packageData
      fromMainSearch: false
    }))
    .then((response) => {
      console.log('fetchRestaurants response:', response);
    })
    .catch((error) => {
      console.error('fetchRestaurants error:', error);
    });

   dispatch(updateSearchState({
  location: [city], // or just city if location is a single string
  ucheckIn: formatedHotelCheckIn,
  ucheckOut: formatedHotelCheckOut,
  guests: guestCounts
}));

// Step 2: Fetch hotels using pagination args
dispatch(fetchHotels());


    // Also update the enquiry slice data for compatibility with other parts of the app
    // Set location data in the right format for EnquirySlice
    
    dispatch(setSearchLocation(countryCode));
    dispatch(setCheckIn(formattedCheckIn));
    dispatch(setCheckOut(formattedCheckOut));
    
    // Set the selected city in common slice
    dispatch(setSelectedCity({
      countryCode: countryCode,
      countryName: country,
      cityCode: cityCode,
      cityName: city,
      combinedCode: cityCode
    }));
    
    // Dispatch guest details to EnquirySlice
    dispatch(
      setGuest({
        adults: guestCounts.Adults.toString(),
        children: guestCounts.Children.toString(),
        infant: guestCounts.Infants.toString(),
        adultGenders: genders,
        childrenAges: guestCounts.ages || [],
        maleCount: maleCount,
        femaleCount: femaleCount
      })
    );

    // Set existing tour data in Redux state
    const dataSource = packageData?.tour || enquirydetail;
    dispatch(updateSearchState({ location: dataSource?.destination }));
    dispatch(setId(tourId));
    dispatch(settourdetails(dataSource));

    

    // Move to the first tab (Itinerary) after update completes
    if (onNext) {
      onNext();
      // If the parent component has a setActiveTab function, call it to show the Itinerary tab
      if (typeof setActiveTab === 'function') {
        setActiveTab(0); // Select the first tab (Itinerary)
      }
    }

    console.log("Tour package updated successfully with tour_id:", tourId);
    
    // Show the button again after successful update
    setIsButtonHidden(true);
    
    } catch (error) {
      console.error("Error updating tour package:", error);
      setSnackbarMessage(
        "Failed to update tour package. Please try again."
      );
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      
      // Show the button again after error
      setIsButtonHidden(false);
    }
  };

 
  // Determine which handler to use based on packageData presence
  const isUpdatingExistingPackage = Boolean(packageData?.tour?.tour_id > 0);
  
  // Modified form submit handler to hide button after single click
  const handleFormSubmit = async (e) => {
    // Hide the button immediately after click
    setIsButtonHidden(true);
    
    // Call the appropriate handler
    if (isUpdatingExistingPackage) {
      await handleUpdate(e);
    } else {
      await handleSearch(e);
    }
  };
  
  // Determine button text based on data source
  const getButtonText = () => {
    if (isDataFromEnquiryDetail) {
      return '🚀 Create Tour for Enquiry';
    }
    if (isUpdatingExistingPackage) {
      return '🔄 Update Tour Package';
    }
    return '🚀 Create Amazing Tour Package';
  };
  
  const getButtonSubtext = () => {
    if (isDataFromEnquiryDetail) {
      return '📋 Processing existing enquiry with dates and guest details';
    }
    if (isUpdatingExistingPackage) {
      return '📝 Modify existing tour package details';
    }
    return '✨ Build personalized travel experiences in seconds';
  };

  return (
    <Box component="form" onSubmit={handleFormSubmit} sx={{ width: '100%' }}>
      <Grid container spacing={1.5}>
        {/* Single Row: All Form Fields */}
        <Grid item xs={12}>
          <Grid container spacing={1.5}>
            {/* Location Search - 4 columns */}
            <Grid item xs={12} sm={6} md={4}>
              <Box
                sx={{
                  p: 1,
                  borderRadius: 1.5,
                  bgcolor: '#f8fafc',
                  border: '1px solid #e2e8f0',
                  height: '100%',
                  minHeight: '60px',
                  position: 'relative',
                  zIndex: 1,
                  '&:hover': {
                    borderColor: '#3b82f6',
                    bgcolor: '#f1f5f9'
                  },
                  transition: 'all 0.2s ease-in-out'
                }}
              >
                <Typography 
                  variant="caption" 
                  sx={{ 
                    mb: 0.5, 
                    fontWeight: 600, 
                    color: '#374151',
                    textTransform: 'uppercase',
                    fontSize: '0.65rem',
                    letterSpacing: '0.05em',
                    display: 'flex',
                    alignItems: 'center',
                    gap: 0.5
                  }}
                >
                  <LocationIcon sx={{ color: '#ef4444', fontSize: 14 }} />
                  Destination
                </Typography>
                <LocationSearch 
                  onLocationSelect={handleLocationSelect}
                  defaultDestination={getDestinationValue()}
                  defaultCity={getCityValue()}
                />
              </Box>
            </Grid>

            {/* Date Range - 3 columns */}
            <Grid item xs={12} sm={6} md={3}>
              <Box
                sx={{
                  p: 1,
                  borderRadius: 1.5,
                  bgcolor: isDataFromEnquiryDetail ? '#f5f5f5' : 'white',
                  border: '1px solid #e2e8f0',
                  height: '100%',
                  minHeight: '60px',
                  position: 'relative',
                  zIndex: 10,
                  overflow: 'visible',
                  opacity: isDataFromEnquiryDetail ? 0.7 : 1,
                  '&:hover': {
                    borderColor: isDataFromEnquiryDetail ? '#e2e8f0' : '#3b82f6',
                    boxShadow: isDataFromEnquiryDetail ? 'none' : '0 2px 6px rgba(59, 130, 246, 0.1)'
                  },
                  transition: 'all 0.2s ease-in-out'
                }}
              >
                <Typography 
                  variant="caption" 
                  sx={{ 
                    mb: 0.5, 
                    fontWeight: 600, 
                    color: isDataFromEnquiryDetail ? '#9ca3af' : '#374151',
                    textTransform: 'uppercase',
                    fontSize: '0.65rem',
                    letterSpacing: '0.05em',
                    display: 'flex',
                    alignItems: 'center',
                    gap: 0.5
                  }}
                >
                  <CalendarIcon sx={{ color: isDataFromEnquiryDetail ? '#9ca3af' : '#10b981', fontSize: 14 }} />
                  Travel Dates
                  {isDataFromEnquiryDetail && (
                    <Typography 
                      variant="caption" 
                      sx={{ 
                        ml: 1, 
                        color: '#6b7280', 
                        fontSize: '0.6rem',
                        fontStyle: 'italic'
                      }}
                    >
                      (Locked)
                    </Typography>
                  )}
                </Typography>
                <Box sx={{ position: 'relative' }}>
                  <DateRangePicker 
                    onDateChange={handleDateChange}
                    defaultCheckIn={packageData?.tour?.check_in_time || enquirydetail?.check_in_time}
                    defaultCheckOut={packageData?.tour?.check_out_time || enquirydetail?.check_out_time}
                    isDataFromEnquiryDetail={isDataFromEnquiryDetail}
                  />
                  {isDataFromEnquiryDetail && (
                    <Box
                      sx={{
                        position: 'absolute',
                        top: 0,
                        left: 0,
                        right: 0,
                        bottom: 0,
                        bgcolor: 'transparent',
                        zIndex: 10,
                        cursor: 'not-allowed'
                      }}
                    />
                  )}
                </Box>
              </Box>
            </Grid>

            {/* Guest Selection - 3 columns */}
            <Grid item xs={12} sm={6} md={3}>
              <Box
                sx={{
                  p: 1,
                  borderRadius: 1.5,
                  bgcolor: isDataFromEnquiryDetail ? '#f5f5f5' : 'white',
                  border: '1px solid #e2e8f0',
                  height: '100%',
                  minHeight: '60px',
                  position: 'relative',
                  zIndex: 9,
                  overflow: 'visible',
                  opacity: isDataFromEnquiryDetail ? 0.7 : 1,
                  '&:hover': {
                    borderColor: isDataFromEnquiryDetail ? '#e2e8f0' : '#3b82f6',
                    boxShadow: isDataFromEnquiryDetail ? 'none' : '0 2px 6px rgba(59, 130, 246, 0.1)'
                  },
                  transition: 'all 0.2s ease-in-out'
                }}
              >
                <Typography 
                  variant="caption" 
                  sx={{ 
                    mb: 0.5, 
                    fontWeight: 600, 
                    color: isDataFromEnquiryDetail ? '#9ca3af' : '#374151',
                    textTransform: 'uppercase',
                    fontSize: '0.65rem',
                    letterSpacing: '0.05em',
                    display: 'flex',
                    alignItems: 'center',
                    gap: 0.5
                  }}
                >
                  <PeopleIcon sx={{ color: isDataFromEnquiryDetail ? '#9ca3af' : '#f59e0b', fontSize: 14 }} />
                  Guests
                  {isDataFromEnquiryDetail && (
                    <Typography 
                      variant="caption" 
                      sx={{ 
                        ml: 1, 
                        color: '#6b7280', 
                        fontSize: '0.6rem',
                        fontStyle: 'italic'
                      }}
                    >
                      (Locked)
                    </Typography>
                  )}
                </Typography>
                <Box sx={{ position: 'relative' }}>
                  <PaxSelector 
                    guestCounts={guestCounts}
                    onGuestChange={handleGuestChange}
                    disabled={isDataFromEnquiryDetail}
                  />
                  {isDataFromEnquiryDetail && (
                    <Box
                      sx={{
                        position: 'absolute',
                        top: 0,
                        left: 0,
                        right: 0,
                        bottom: 0,
                        bgcolor: 'transparent',
                        zIndex: 10,
                        cursor: 'not-allowed'
                      }}
                    />
                  )}
                </Box>
              </Box>
            </Grid>

            {/* Agent Selection - 2 columns */}
            <Grid item xs={12} sm={6} md={2}>
              <Box>
                <Typography 
                  variant="caption" 
                  sx={{ 
                    mb: 0.5, 
                    fontWeight: 600, 
                    color: '#374151',
                    textTransform: 'uppercase',
                    fontSize: '0.65rem',
                    letterSpacing: '0.05em',
                    display: 'flex',
                    alignItems: 'center',
                    gap: 0.5
                  }}
                >
                  <PersonIcon sx={{ color: '#8b5cf6', fontSize: 14 }} />
                  Agent
                </Typography>
                <FormControl 
                  fullWidth 
                  size="small"
                  variant="outlined"
                  sx={{ 
                    '& .MuiOutlinedInput-root': {
                      borderRadius: 1,
                      bgcolor: isAgentFromPackageData ? '#f5f5f5' : '#f8fafc',
                      fontSize: '0.8rem',
                      minHeight: '32px',
                      '&:hover': {
                        bgcolor: isAgentFromPackageData ? '#f5f5f5' : 'white'
                      },
                      '&.Mui-focused': {
                        bgcolor: isAgentFromPackageData ? '#f5f5f5' : 'white'
                      }
                    },
                    '& .MuiInputLabel-root': {
                      fontWeight: 500,
                      fontSize: '0.8rem'
                    },
                    '& .MuiSelect-select': {
                      overflow: 'visible'
                    }
                  }}
                >
                  {isAgentFromPackageData ? (
                    // Show readonly input when agent comes from packageData
                    <input
                      type="text"
                      value={`${selectedAgentName}`}
                      readOnly
                      style={{
                        width: '100%',
                        height: '32px',
                        border: 'none',
                        background: 'transparent',
                        fontSize: '0.8rem',
                        padding: '0 8px',
                        cursor: 'default'
                      }}
                    />
                  ) : (
                    <Select
                      labelId="agent-select-label"
                      id="agent-select"
                      value={selectedAgent}
                      onChange={handleAgentChange}
                      label="Select Agent *"
                      required
                      sx={{
                        "& .MuiOutlinedInput-root": {
                          backgroundColor: "#f8fafc",
                          borderColor: "#e2e8f0",
                          "&:hover": {
                            borderColor: "#94a3b8",
                          },
                          "&.Mui-focused": {
                            borderColor: "#3b82f6",
                            boxShadow: "0 0 0 2px rgba(59, 130, 246, 0.1)",
                          },
                        },
                        "& .MuiSelect-icon": {
                          color: "#64748b",
                        },
                        "& .MuiInputLabel-root": {
                          color: "#475569",
                          "&.Mui-focused": {
                            color: "#3b82f6",
                          },
                        },
                      }}
                      MenuProps={{
                        PaperProps: {
                          sx: {
                            maxHeight: 400,
                            boxShadow: "0 10px 25px rgba(0, 0, 0, 0.15)",
                            border: "1px solid #e2e8f0",
                            borderRadius: "8px",
                            "& .MuiMenuItem-root": {
                              fontSize: "0.9rem",
                              padding: "12px 16px",
                              "&:hover": {
                                backgroundColor: "#f1f5f9",
                              },
                              "&.Mui-selected": {
                                backgroundColor: "#dbeafe",
                                color: "#1e40af",
                                "&:hover": {
                                  backgroundColor: "#bfdbfe",
                                },
                              },
                            },
                          },
                        },
                        MenuListProps: {
                          sx: {
                            padding: 0,
                          },
                          onKeyDown: (e) => {
                            // Allow typing in search input
                            if (e.target.tagName === 'INPUT') {
                              e.stopPropagation();
                            }
                          },
                        },
                        disableAutoFocus: true,
                        disableEnforceFocus: true,
                        disableRestoreFocus: true,
                      }}
                    >
                      {/* Search Input in Dropdown */}
                      <Box 
                        sx={{ 
                          p: 2, 
                          borderBottom: "1px solid #e2e8f0", 
                          backgroundColor: "#f8fafc",
                          position: "sticky",
                          top: 0,
                          zIndex: 1
                        }}
                        onClick={(e) => e.stopPropagation()}
                        onMouseDown={(e) => e.stopPropagation()}
                      >
                        <TextField
                          ref={searchInputRef}
                          fullWidth
                          size="small"
                          placeholder="Search agents or companies..."
                          value={searchTerm}
                          onChange={handleSearchChange}
                          autoFocus={false}
                          InputProps={{
                            startAdornment: (
                              <InputAdornment position="start">
                                <SearchIcon sx={{ fontSize: 18, color: "#64748b" }} />
                              </InputAdornment>
                            ),
                            endAdornment: searchTerm && (
                              <InputAdornment position="end">
                                <Clear 
                                  sx={{ 
                                    fontSize: 18, 
                                    color: "#64748b", 
                                    cursor: "pointer",
                                    "&:hover": { color: "#374151" }
                                  }} 
                                  onClick={(e) => {
                                    e.stopPropagation();
                                    handleClearSearch();
                                  }}
                                />
                              </InputAdornment>
                            ),
                            sx: {
                              "& fieldset": { border: "1px solid #e2e8f0" },
                              "&:hover fieldset": { border: "1px solid #94a3b8" },
                              "&.Mui-focused fieldset": { border: "1px solid #3b82f6" },
                              "& input": {
                                padding: "8px 12px",
                                fontSize: "0.9rem",
                                color: "#1e293b",
                                "&::placeholder": {
                                  color: "#64748b",
                                  opacity: 1,
                                },
                              },
                            },
                          }}
                          onKeyDown={(e) => e.stopPropagation()}
                          onKeyUp={(e) => e.stopPropagation()}
                          onKeyPress={(e) => e.stopPropagation()}
                        />
                      </Box>
                      
                      <MenuItem value="" sx={{ fontStyle: 'italic', color: '#6b7280', fontSize: '0.8rem' }}>
                        <Stack direction="row" alignItems="center" spacing={1}>
                          <PersonIcon sx={{ fontSize: 16, color: "#3b82f6" }} />
                          <Box>
                            <Typography variant="body2" sx={{ fontWeight: 500, color: "#6b7280", fontStyle: 'italic' }}>
                              Choose an agent
                            </Typography>
                            <Typography variant="caption" sx={{ color: "#9ca3af", fontSize: "0.7rem" }}>
                              Select from available agents
                            </Typography>
                          </Box>
                        </Stack>
                      </MenuItem>
                      {filteredAgents.length > 0 ? (
                        filteredAgents.map((agent) => (
                        <MenuItem 
                          key={agent.id} 
                          value={agent.agent_id}
                          sx={{
                            '&:hover': {
                              bgcolor: '#f1f5f9'
                            }
                          }}
                        >
                          <Stack direction="row" alignItems="center" spacing={1}>
                            <Avatar 
                              sx={{ 
                                width: 24, 
                                height: 24, 
                                fontSize: "0.8rem",
                                backgroundColor: "#3b82f6",
                                color: "white",
                                fontWeight: 600
                              }}
                            >
                              {agent.name.charAt(0).toUpperCase()}
                            </Avatar>
                            <Box sx={{ minWidth: 0, flex: 1 }}>
                              <Typography variant="body2" sx={{ fontWeight: 500, color: "#1e293b", lineHeight: 1.2, fontSize: '0.8rem' }}>
                                {agent.name}
                              </Typography>
                              <Typography 
                                variant="caption" 
                                sx={{ 
                                  color: "#059669", 
                                  fontSize: "0.7rem",
                                  lineHeight: 1.2,
                                  display: "block",
                                  overflow: "hidden",
                                  textOverflow: "ellipsis",
                                  whiteSpace: "nowrap",
                                  fontWeight: 500
                                }}
                              >
                                {agent.company_name}
                              </Typography>
                            </Box>
                          </Stack>
                        </MenuItem>
                        ))
                      ) : searchTerm ? (
                        <MenuItem disabled>
                          <Stack direction="row" alignItems="center" spacing={1}>
                            <SearchIcon sx={{ fontSize: 18, color: "#64748b" }} />
                            <Typography variant="body2" sx={{ color: "#64748b", fontStyle: "italic" }}>
                              No agents found matching "{searchTerm}"
                            </Typography>
                          </Stack>
                        </MenuItem>
                      ) : null}
                    </Select>
                  )}
                </FormControl>
              </Box>
            </Grid>
          </Grid>
        </Grid>
        
        {/* Attractive Search Button Section */}
        <Grid item xs={12}>
          <Box 
            sx={{ 
              display: 'flex', 
              justifyContent: 'center',
              alignItems: 'center',
              mt: 2,
              mb: 1
            }}
          >
            {!isButtonHidden && (
              <Button 
                type="submit"
                variant="contained" 
                size="large"
                startIcon={<SearchIcon sx={{ fontSize: 24 }} />}
                sx={{ 
                  borderRadius: 3,
                  px: 6,
                  py: 2.5,
                  fontSize: '1.1rem',
                  fontWeight: 700,
                  textTransform: 'none',
                  minWidth: '280px',
                  background: isDataFromEnquiryDetail
                    ? 'linear-gradient(135deg, #6b7280 0%, #4b5563 100%)'
                    : isUpdatingExistingPackage 
                      ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' 
                      : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                  boxShadow: isDataFromEnquiryDetail
                    ? '0 8px 32px rgba(107, 114, 128, 0.4)'
                    : isUpdatingExistingPackage
                      ? '0 8px 32px rgba(16, 185, 129, 0.4)'
                      : '0 8px 32px rgba(102, 126, 234, 0.4)',
                  position: 'relative',
                  overflow: 'hidden',
                  color: 'white',
                  '&::before': {
                    content: '""',
                    position: 'absolute',
                    top: 0,
                    left: '-100%',
                    width: '100%',
                    height: '100%',
                    background: 'linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent)',
                    transition: 'left 0.5s ease'
                  },
                  '&:hover': {
                    background: isDataFromEnquiryDetail
                      ? 'linear-gradient(135deg, #4b5563 0%, #374151 100%)'
                      : isUpdatingExistingPackage
                        ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
                        : 'linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%)',
                    boxShadow: isDataFromEnquiryDetail
                      ? '0 12px 40px rgba(107, 114, 128, 0.6)'
                      : isUpdatingExistingPackage
                        ? '0 12px 40px rgba(16, 185, 129, 0.6)'
                        : '0 12px 40px rgba(102, 126, 234, 0.6)',
                    transform: isDataFromEnquiryDetail ? 'none' : 'translateY(-3px) scale(1.02)',
                    '&::before': {
                      left: '100%'
                    }
                  },
                  '&:active': {
                    transform: isDataFromEnquiryDetail ? 'none' : 'translateY(-1px) scale(1.01)'
                  },
                  transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
                }}
              >
                {getButtonText()}
              </Button>
            )}
          </Box>
          
          {/* Optional: Add helpful text below button */}
          {!isButtonHidden && (
            <Box sx={{ textAlign: 'center', mt: 1 }}>
              <Typography 
                variant="caption" 
                sx={{ 
                  color: '#6b7280',
                  fontSize: '0.75rem',
                  fontStyle: 'italic'
                }}
              >
                {getButtonSubtext()}
              </Typography>
            </Box>
          )}
        </Grid>
      </Grid>
      
      {/* Date Validation Dialog */}
      <Dialog 
        open={dateValidationDialog.open} 
        onClose={handleDateValidationCancel}
        maxWidth="md"
        fullWidth
      >
        <DialogTitle sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          <WarningIcon color="warning" />
          Date Range Conflict Warning
        </DialogTitle>
        <DialogContent>
          <Typography variant="body1" gutterBottom>
            The new date range you selected conflicts with {dateValidationDialog.conflictingServices.length} existing service booking{dateValidationDialog.conflictingServices.length > 1 ? 's' : ''}:
          </Typography>
          
          {dateValidationDialog.newDateRange.start && dateValidationDialog.newDateRange.end && (
            <Typography variant="body2" sx={{ mb: 2, p: 1, bgcolor: '#f0f9ff', borderRadius: 1 }}>
              <strong>New Tour Dates:</strong> {moment(dateValidationDialog.newDateRange.start).format('MMM DD, YYYY')} - {moment(dateValidationDialog.newDateRange.end).format('MMM DD, YYYY')}
            </Typography>
          )}
          
          <List dense>
            {dateValidationDialog.conflictingServices.map((service, index) => (
              <ListItem key={index} sx={{ bgcolor: '#fef2f2', borderRadius: 1, mb: 1 }}>
                <ListItemText
                  primary={
                    <Typography variant="subtitle2" fontWeight={600}>
                      {service.name} ({service.type})
                    </Typography>
                  }
                  secondary={
                    <Box>
                      <Typography variant="body2">
                        Booking Date: {service.bookingDate}
                      </Typography>
                      <Typography variant="caption" color="error">
                        {service.reason}
                      </Typography>
                    </Box>
                  }
                />
              </ListItem>
            ))}
          </List>
          
          <Typography variant="body2" color="text.secondary" sx={{ mt: 2 }}>
            If you proceed, these services will need to be adjusted or removed to fit within the new tour dates.
          </Typography>
        </DialogContent>
        <DialogActions>
          <Button onClick={handleDateValidationCancel} color="inherit">
            Cancel
          </Button>
          <Button 
            onClick={handleDateValidationConfirm} 
            variant="contained" 
            color="warning"
            startIcon={<WarningIcon />}
          >
            OK
          </Button>
        </DialogActions>
      </Dialog>
      
      <Snackbar
        open={openSnackbar}
        autoHideDuration={6000}
        onClose={handleCloseSnackbar}
        anchorOrigin={{ vertical: 'top', horizontal: 'center' }}
      >
        <Alert 
          onClose={handleCloseSnackbar} 
          severity={snackbarSeverity}
          sx={{
            borderRadius: 1.5,
            '& .MuiAlert-message': {
              fontWeight: 500,
              fontSize: '0.8rem'
            }
          }}
        >
          {snackbarMessage}
        </Alert>
      </Snackbar>
    </Box>
  );
}