import React, { useState, useMemo } from 'react';
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
  MenuItem
} from '@mui/material';
import MuiAlert from "@mui/material/Alert";
import { 
  Search as SearchIcon,
  LocationOn as LocationIcon,
  CalendarToday as CalendarIcon,
  People as PeopleIcon,
  Person as PersonIcon
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
import { clearAttractions } from "../../../slice/attractions/attractionSlice";
import { fetchAttractions } from "../../../slice/attractions/attractionSlice";
import {
  fetchRestaurants,
  clearRestaurants,
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
import { setSearchCriteria, fetchTourPackages, clearPackages, clearAllServices } from "../../../slice/tour-packages/tourPackageSlice";
import { store } from "../../../store/store";
import { setSearchParams as setAttractionSearchParams } from "../../../slice/attractions/attractionSlice";
import { setSearchParams as setGuideSearchParams } from "../../../slice/tourguide/guideslice";
import { fetchAgentList } from "../../../slice/common/agentListSlice";
import { setAgentId } from '@/slice/common/EditSlice';

// Create a reusable alert component
const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

export default function SearchForm({ onNext, setActiveTab, packageData }) {
  const dispatch = useDispatch();
  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  const [selectedLocation, setSelectedLocation] = useState(null);
  
  // Initialize dates with packageData if available
  const getInitialStartDate = () => {
    if (packageData?.tour?.check_in_time) {
      try {
        return moment(packageData.tour.check_in_time).toDate();
      } catch (error) {
        console.error('Error parsing check_in_time:', error);
        return null;
      }
    }
    return null;
  };

  const getInitialEndDate = () => {
    if (packageData?.tour?.check_out_time) {
      try {
        return moment(packageData.tour.check_out_time).toDate();
      } catch (error) {
        console.error('Error parsing check_out_time:', error);
        return null;
      }
    }
    return null;
  };

  // Initialize guest counts with packageData if available
  const getInitialGuestCounts = () => {
    if (packageData?.tour) {
      const tour = packageData.tour;
      return {
        Adults: tour.adult || 1,
        Children: tour.child || 0,
        Infants: tour.infant || 0,
        maleCount: tour.male_count || 0,
        femaleCount: tour.female_count || 0,
        genders: [], // Initialize empty array for compatibility
        ages: tour.child_ages ? 
          (Array.isArray(tour.child_ages) ? tour.child_ages : 
           JSON.parse(tour.child_ages || '[]')) : [],
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

  // Log the initialization values for debugging
  React.useEffect(() => {
    if (packageData?.tour) {
      console.log('Initializing form with packageData:', {
        destination: packageData.tour.destination,
        checkIn: packageData.tour.check_in_time,
        checkOut: packageData.tour.check_out_time,
        adults: packageData.tour.adult,
        children: packageData.tour.child,
        maleCount: packageData.tour.male_count,
        femaleCount: packageData.tour.female_count,
        agentId: packageData.tour.agent_id
      });
    }
  }, [packageData]);

  // Agent selection state
  const [selectedAgent, setSelectedAgent] = useState('');
  const [selectedAgentName, setSelectedAgentName] = useState('');
  const [isAgentFromPackageData, setIsAgentFromPackageData] = useState(false);
  const { agents } = useSelector((state) => state.agentList);
  
  // Fetch agents on component mount
  React.useEffect(() => {
    dispatch(fetchAgentList());
  }, [dispatch]);

  // Auto-select agent based on packageData agent_id and agent_name
  React.useEffect(() => {
    if (packageData?.tour?.agent_id && packageData?.tour?.agent_name && !selectedAgent) {
      const agentId = packageData.tour.agent_id.toString();
      const agentName = packageData.tour.agent_name;
      
      console.log('Auto-selecting agent from packageData:', { agentId, agentName });
      
      // Set agent directly from packageData
      setSelectedAgent(agentId);
      setSelectedAgentName(agentName);
      setIsAgentFromPackageData(true);
      dispatch(setAgentId(agentId));
    }
  }, [packageData?.tour?.agent_id, packageData?.tour?.agent_name, selectedAgent, dispatch]);
  
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

  const handleDateChange = (dateRange) => {
    if (dateRange && Array.isArray(dateRange) && dateRange.length === 2) {
      setStartDate(dateRange[0].toDate ? dateRange[0].toDate() : dateRange[0]);
      setEndDate(dateRange[1].toDate ? dateRange[1].toDate() : dateRange[1]);
    }
  };

  const handleGuestChange = (updatedGuestCounts) => {
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
      !guestCounts.ages.every((age) => age.trim() !== "")
    ) {
      setSnackbarMessage("Please provide an age for all children.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    return true;
  };

  const handleSearch = async (e) => {
    e.preventDefault();

    if (!validateForm()) return;

    // Clear previous customer info when starting new search
    dispatch(clearUserInfo());
    dispatch(clearAllServices());
    // Clear previous data
    dispatch(clearAttractions());
    dispatch(clearRestaurants());
    dispatch(resetVehicles());
    dispatch(resetVehicles1()); 
    dispatch(resetguide());
    
    // Format Dates
    const formattedCheckIn = moment(startDate).format("DD/MM/YYYY");
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
      tour_id: tourdetails?.tour_id
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
      tour_id: tourdetails?.tour_id
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
      tour_id: tourdetails?.tour_id, // Add tour_id
      selectedDate: moment(startDate),
      fromMainSearch: false
    }));

    // Fetch restaurants based on search criteria
    console.log('Dispatching fetchRestaurants with params:', {
      city: `${city}, (${country})`,
      date: formattedAttractionDate,
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourdetails?.tour_id,
      fromMainSearch: false
    });

    dispatch(fetchRestaurants({
      city: `${city}, (${country})`,
      date: formattedAttractionDate,
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourdetails?.tour_id,
      fromMainSearch: false
    }))
    .then((response) => {
      console.log('fetchRestaurants response:', response);
    })
    .catch((error) => {
      console.error('fetchRestaurants error:', error);
    });

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
      }
    }))
      .unwrap()
      .then((data) => {
        console.log("Tour packages response:", data);
        dispatch(updateSearchState({ location: data.destination }));
        dispatch(setId(data.data.tour_id));
        
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
      })
      .catch((error) => {
        console.error("Error fetching tour packages:", error);
        setSnackbarMessage(
          "Failed to fetch tour packages. Please try again."
        );
        setSnackbarSeverity("error");
        setOpenSnackbar(true);
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

    if (!validateForm()) return;

    // Clear previous customer info when starting update
    dispatch(clearUserInfo());
    dispatch(clearAllServices());
    // Clear previous data
    dispatch(clearAttractions());
    dispatch(clearRestaurants());
    dispatch(resetVehicles());
    dispatch(resetVehicles1()); 
    dispatch(resetguide());
    
    // Format Dates
    const formattedCheckIn = moment(startDate).format("DD/MM/YYYY");
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

    // Update tour packages search criteria in Redux
    dispatch(setSearchCriteria({
      country: country,
      city: city,
      checkIn: packageData?.tour?.check_in_time,
      checkOut: packageData?.tour?.check_out_time,
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
      fromMainSearch: false
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
    dispatch(updateSearchState({ location: packageData?.tour?.destination }));
    dispatch(setId(tourId));
    dispatch(settourdetails(packageData.tour));

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

  // Determine which handler to use based on packageData presence
  const isUpdatingExistingPackage = Boolean(packageData?.tour?.tour_id);
  const handleFormSubmit = isUpdatingExistingPackage ? handleUpdate : handleSearch;

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
                  defaultDestination={packageData?.tour?.destination}
                  defaultCity={packageData?.tour?.city}
                />
              </Box>
            </Grid>

            {/* Date Range - 3 columns */}
            <Grid item xs={12} sm={6} md={3}>
              <Box
                sx={{
                  p: 1,
                  borderRadius: 1.5,
                  bgcolor: 'white',
                  border: '1px solid #e2e8f0',
                  height: '100%',
                  minHeight: '60px',
                  position: 'relative',
                  zIndex: 10,
                  overflow: 'visible',
                  '&:hover': {
                    borderColor: '#3b82f6',
                    boxShadow: '0 2px 6px rgba(59, 130, 246, 0.1)'
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
                  <CalendarIcon sx={{ color: '#10b981', fontSize: 14 }} />
                  Travel Dates
                </Typography>
                <DateRangePicker 
                  onDateChange={handleDateChange}
                  defaultCheckIn={packageData?.tour?.check_in_time}
                  defaultCheckOut={packageData?.tour?.check_out_time}
                />
              </Box>
            </Grid>

            {/* Guest Selection - 3 columns */}
            <Grid item xs={12} sm={6} md={3}>
              <Box
                sx={{
                  p: 1,
                  borderRadius: 1.5,
                  bgcolor: 'white',
                  border: '1px solid #e2e8f0',
                  height: '100%',
                  minHeight: '60px',
                  position: 'relative',
                  zIndex: 9,
                  overflow: 'visible',
                  '&:hover': {
                    borderColor: '#3b82f6',
                    boxShadow: '0 2px 6px rgba(59, 130, 246, 0.1)'
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
                  <PeopleIcon sx={{ color: '#f59e0b', fontSize: 14 }} />
                  Guests
                </Typography>
                <PaxSelector 
                  guestCounts={guestCounts}
                  onGuestChange={handleGuestChange}
                />
              </Box>
            </Grid>

            {/* Agent Selection - 2 columns */}
            <Grid item xs={12} sm={6} md={2}>
              <Box
                // sx={{
                //   p: 1,
                //   borderRadius: 1.5,
                //   bgcolor: 'white',
                //   border: '1px solid #e2e8f0',
                //   height: '100%',
                //   minHeight: '60px',
                //   position: 'relative',
                //   zIndex: 8,
                //   overflow: 'visible',
                //   '&:hover': {
                //     borderColor: '#3b82f6',
                //     boxShadow: '0 2px 6px rgba(59, 130, 246, 0.1)'
                //   },
                //   transition: 'all 0.2s ease-in-out'
                // }}
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
                      value={`${selectedAgentName} (ID: ${selectedAgent})`}
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
                      MenuProps={{
                        PaperProps: {
                          sx: {
                            maxHeight: 200,
                            mt: 1,
                            boxShadow: '0 4px 20px rgba(0,0,0,0.1)',
                            borderRadius: 2
                          }
                        }
                      }}
                    >
                      <MenuItem value="" sx={{ fontStyle: 'italic', color: '#6b7280', fontSize: '0.8rem' }}>
                        Choose an agent
                      </MenuItem>
                      {agents && agents.map((agent) => (
                        <MenuItem 
                          key={agent.id} 
                          value={agent.agent_id}
                          sx={{
                            '&:hover': {
                              bgcolor: '#f3f4f6'
                            }
                          }}
                        >
                          <Box>
                            <Typography variant="body2" sx={{ fontWeight: 500, fontSize: '0.8rem' }}>
                              {agent.name}
                            </Typography>
                            <Typography variant="caption" sx={{ color: '#6b7280', fontSize: '0.7rem' }}>
                              ID: {agent.agent_id}
                            </Typography>
                          </Box>
                        </MenuItem>
                      ))}
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
                background: isUpdatingExistingPackage 
                  ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' 
                  : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                boxShadow: isUpdatingExistingPackage
                  ? '0 8px 32px rgba(16, 185, 129, 0.4)'
                  : '0 8px 32px rgba(102, 126, 234, 0.4)',
                position: 'relative',
                overflow: 'hidden',
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
                  background: isUpdatingExistingPackage
                    ? 'linear-gradient(135deg, #059669 0%, #047857 100%)'
                    : 'linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%)',
                  boxShadow: isUpdatingExistingPackage
                    ? '0 12px 40px rgba(16, 185, 129, 0.6)'
                    : '0 12px 40px rgba(102, 126, 234, 0.6)',
                  transform: 'translateY(-3px) scale(1.02)',
                  '&::before': {
                    left: '100%'
                  }
                },
                '&:active': {
                  transform: 'translateY(-1px) scale(1.01)'
                },
                transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
              }}
            >
              {isUpdatingExistingPackage ? '🔄 Update Tour Package' : '🚀 Create Amazing Tour Package'}
            </Button>
          </Box>
          
          {/* Optional: Add helpful text below button */}
          <Box sx={{ textAlign: 'center', mt: 1 }}>
            <Typography 
              variant="caption" 
              sx={{ 
                color: '#6b7280',
                fontSize: '0.75rem',
                fontStyle: 'italic'
              }}
            >
              {isUpdatingExistingPackage 
                ? '📝 Modify existing tour package details' 
                : '✨ Build personalized travel experiences in seconds'}
            </Typography>
          </Box>
        </Grid>
      </Grid>
      
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