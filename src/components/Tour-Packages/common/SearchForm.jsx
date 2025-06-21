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
import { Search as SearchIcon } from '@mui/icons-material';
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
import { setSearchCriteria, fetchTourPackages, clearPackages } from "../../../slice/tour-packages/tourPackageSlice";
import { store } from "../../../store/store";
import { setSearchParams as setAttractionSearchParams } from "../../../slice/attractions/attractionSlice";
import { setSearchParams as setGuideSearchParams } from "../../../slice/tourguide/guideslice";
import { fetchAgentList } from "../../../slice/common/agentListSlice";
import { setAgentId } from '@/slice/common/EditSlice';

// Create a reusable alert component
const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

export default function SearchForm({ onNext, setActiveTab }) {
  const dispatch = useDispatch();
  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  const [selectedLocation, setSelectedLocation] = useState(null);
  const [startDate, setStartDate] = useState(null);
  const [endDate, setEndDate] = useState(null);
  const [guestCounts, setGuestCounts] = useState({
    Adults: 1,
    Children: 0,
    Infants: 0,
    genders: [""], // Store gender selections for adults
    ages: [""], // Store age selections for children
  });
  const user_country = useSelector((state) => state.auth.user_country);
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const [snackbarSeverity, setSnackbarSeverity] = useState("error");
  
  // Agent selection state
  const [selectedAgent, setSelectedAgent] = useState('');
  const { agents } = useSelector((state) => state.agentList);
  
  // Fetch agents on component mount
  React.useEffect(() => {
    dispatch(fetchAgentList());
  }, [dispatch]);
  
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
    
    // Clear previous data
    dispatch(clearAttractions());
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

  return (
    <Box component="form" onSubmit={handleSearch} sx={{ width: '100%' }}>
      <Paper 
        elevation={3} 
        sx={{ 
          p: 3, 
          borderRadius: 2,
          background: 'white',
        }}
      >
        <Grid container spacing={3}>
          <Grid item xs={12} sm={12} md={12}>
            <LocationSearch 
              onLocationSelect={handleLocationSelect}
            />
          </Grid>
          
          <Grid item xs={12} sm={6} md={4}>
            <DateRangePicker 
              onDateChange={handleDateChange}
            />
          </Grid>
          
          <Grid item xs={12} sm={6} md={4}>
            <PaxSelector 
              guestCounts={guestCounts}
              onGuestChange={handleGuestChange}
            />
          </Grid>
          
          <Grid item xs={12} sm={6} md={4}>
            <FormControl 
              fullWidth 
              variant="outlined"
              sx={{ 
                '& .MuiOutlinedInput-root': {
                  borderRadius: 2,
                }
              }}
            >
              <InputLabel id="agent-select-label">Select Agent *</InputLabel>
              <Select
                labelId="agent-select-label"
                id="agent-select"
                value={selectedAgent}
                onChange={handleAgentChange}
                label="Select Agent *"
                required
              >
                <MenuItem value="">
                  <em>Choose an agent</em>
                </MenuItem>
                {agents && agents.map((agent) => (
                  <MenuItem key={agent.id} value={agent.agent_id}>
                    {agent.name}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Grid>
          
          <Grid item xs={12} sx={{ mt: 2, textAlign: 'center' }}>
            <Button 
              type="submit"
              variant="contained" 
              color="primary" 
              size="large"
              startIcon={<SearchIcon />}
              sx={{ 
                borderRadius: 2,
                px: 4,
                py: 1.5,
                fontSize: '1rem',
                textTransform: 'none'
              }}
            >
              Search Tour Packages
            </Button>
          </Grid>
        </Grid>
      </Paper>
      
      <Snackbar
        open={openSnackbar}
        autoHideDuration={6000}
        onClose={handleCloseSnackbar}
        anchorOrigin={{ vertical: 'top', horizontal: 'center' }}
      >
        <Alert 
          onClose={handleCloseSnackbar} 
          severity={snackbarSeverity}
        >
          {snackbarMessage}
        </Alert>
      </Snackbar>
    </Box>
  );
} 