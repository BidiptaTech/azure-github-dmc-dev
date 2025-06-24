import React, { useState } from 'react';
import { useDispatch } from 'react-redux';
import {
  Box,
  Typography,
  Button,
  Snackbar,
  Container,
  Paper,
  styled,
  Divider,
} from '@mui/material';
import MuiAlert from "@mui/material/Alert";
import LocationSearch from '../hero/hero-3/LocationSearch';
import GuestSearch from '../hero/hero-3/GuestSearch';
import DateSearch from '../hero/DateSearch';
import CitySearch from '../hero/hero-3/CitySearch';
import { fetchPackages, setSearchParams } from '../../slice/tour-packages/prePackagesSlice';
import ListingCards from './common/ListingCards';
import LuggageIcon from '@mui/icons-material/Luggage';
import ExploreIcon from '@mui/icons-material/Explore';

// Create a reusable alert component
const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

// Styled components to fix dropdown z-index issues
const StyledContainer = styled(Container)(({ theme }) => ({
  position: 'relative',
  zIndex: 10,
  '& .dropdown-menu': {
    zIndex: 1050,
  },
  '& .shadow-2': {
    zIndex: 1050,
  },
  '& .rmdp-wrapper': {
    zIndex: 1050,
  },
  '& .rmdp-container': {
    zIndex: 1050,
  },
  '& .rmdp-calendar': {
    zIndex: 1050,
  },
  '& .counter-box': {
    zIndex: 1050,
  }
}));

// Styled title section
const TitleSection = styled(Box)(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  marginTop: theme.spacing(4),
  marginBottom: theme.spacing(4),
}));

const IconContainer = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  marginBottom: theme.spacing(2),
}));

const PreDefinePackages = () => {
  const dispatch = useDispatch();
  const [selectedLocation, setSelectedLocation] = useState(null);
  const [selectedCity, setSelectedCity] = useState(null);
  const [dateRange, setDateRange] = useState([
    new Date().toISOString().split('T')[0], // Today
    new Date(Date.now() + 86400000).toISOString().split('T')[0] // Tomorrow
  ]);
  const [guestCounts, setGuestCounts] = useState({
    Adults: 1,
    Children: 0,
    Infants: 0,
    maleCount: 0,
    femaleCount: 0,
    ages: []
  });
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const [snackbarSeverity, setSnackbarSeverity] = useState("error");

  const handleDateRangeChange = (dates) => {
    if (dates && Array.isArray(dates) && dates.length === 2) {
      setDateRange(dates.map(date => date.format("YYYY-MM-DD")));
    }
  };

  const handleGuestChange = (newGuestCounts) => {
    setGuestCounts(newGuestCounts);
  };
  
  const handleLocationSelect = (location) => {
    setSelectedLocation(location);
    // Reset city when location changes
    setSelectedCity(null);
  };

  const handleCitySelect = (city) => {
    setSelectedCity(city);
  };
  
  const handleCloseSnackbar = () => {
    setOpenSnackbar(false);
  };

  const validateForm = () => {
    // Validate location selection
    if (!selectedLocation) {
      setSnackbarMessage("Please select a location");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Validate city selection
    if (!selectedCity) {
      setSnackbarMessage("Please select a city");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Validate date range
    if (!dateRange || dateRange.length !== 2) {
      setSnackbarMessage("Please select valid dates for your stay");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Validate passenger information
    if (!guestCounts.Adults || guestCounts.Adults < 1) {
      setSnackbarMessage("At least one adult is required");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Validate gender counts
    const maleCount = guestCounts.maleCount || 0;
    const femaleCount = guestCounts.femaleCount || 0;
    
    if (maleCount === 0 && femaleCount === 0) {
      setSnackbarMessage("Please select at least one male or female guest");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }
    
    if (maleCount + femaleCount !== guestCounts.Adults) {
      setSnackbarMessage("Male and female count must equal the total adult count");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Validate children ages if any
    if (guestCounts.Children > 0) {
      const childrenWithoutAge = guestCounts.ages.filter(age => !age).length;
      if (childrenWithoutAge > 0) {
        setSnackbarMessage("Please select ages for all children");
        setSnackbarSeverity("error");
        setOpenSnackbar(true);
        return false;
      }
    }

    return true;
  };

  const handleSubmit = () => {
    if (!validateForm()) {
      return;
    }

    // Format the data for submission
    const formData = {
      location: selectedLocation,
      city: selectedCity,
      dates: {
        checkIn: dateRange[0],
        checkOut: dateRange[1],
      },
      guests: {
        adults: guestCounts.Adults,
        maleCount: guestCounts.maleCount || 0,
        femaleCount: guestCounts.femaleCount || 0,
        children: guestCounts.Children,
        childrenAges: guestCounts.ages || [],
        infants: guestCounts.Infants
      }
    };
    
    console.log('Form data submitted:', formData);
    
    // Format the data for API request
    const searchParams = {
      country: selectedLocation,
      city: selectedCity,
      check_in: dateRange[0],
      check_out: dateRange[1],
      adults: guestCounts.Adults,
      male_count: guestCounts.maleCount || 0,
      female_count: guestCounts.femaleCount || 0,
      children: guestCounts.Children,
      children_ages: guestCounts.ages?.join(','),
      infants: guestCounts.Infants
    };
    
    // Dispatch actions to fetch packages and store search parameters
    dispatch(setSearchParams(searchParams));
    dispatch(fetchPackages(searchParams))
      .unwrap()
      .then(() => {
        setSnackbarMessage("Search successful! Fetching packages...");
        setSnackbarSeverity("success");
        setOpenSnackbar(true);
        // Here you can add navigation to packages list page if needed
      })
      .catch((error) => {
        setSnackbarMessage(error || "Failed to fetch packages. Please try again.");
        setSnackbarSeverity("error");
        setOpenSnackbar(true);
      });
  };

  return (
    <StyledContainer maxWidth="lg">
      <Box p={4}>
        <TitleSection>
          <IconContainer>
            <LuggageIcon sx={{ fontSize: 36, color: 'primary.main', mr: 1 }} />
            <Typography 
              variant="h3" 
              component="h1" 
              sx={{ 
                fontWeight: 700, 
                color: 'primary.main',
                textTransform: 'uppercase',
                letterSpacing: '0.5px',
                display: 'flex',
                alignItems: 'center'
              }}
            >
              Pre Define Packages
              <ExploreIcon sx={{ ml: 1, fontSize: 28 }} />
            </Typography>
          </IconContainer>
          <Divider sx={{ width: '100px', height: '4px', backgroundColor: 'secondary.main', mb: 3 }} />
          <Typography variant="subtitle1" color="text.secondary" textAlign="center">
            Discover our exclusive pre-arranged travel experiences
          </Typography>
        </TitleSection>

        <Paper elevation={3} sx={{ borderRadius: '8px', overflow: 'visible', mb: 4 }}>
          <div className="mainSearch bg-white pr-20 py-20 lg:px-20 lg:pt-5 lg:pb-20 rounded-4">
            <div className="button-grid items-center" style={{ display: 'flex', flexWrap: 'nowrap' }}>
              <div style={{ flex: '1', minWidth: '0' }}>
                <LocationSearch onLocationSelect={handleLocationSelect} />
              </div>
              
              <div style={{ flex: '1', minWidth: '0' }}>
                <CitySearch selectedCountry={selectedLocation} onCitySelect={handleCitySelect} />
              </div>

              <div style={{ flex: '1.2', minWidth: '0' }} className="searchMenu-date px-30 lg:py-20 lg:px-0 js-form-dd js-calendar">
                <div>
                  <h4 className="text-15 fw-500 ls-2 lh-16">
                    Check in - Check out
                  </h4>
                  <DateSearch onDateChange={handleDateRangeChange} />
                </div>
              </div>

              <div style={{ flex: '1', minWidth: '0' }}>
                <GuestSearch onGuestChange={handleGuestChange} guestCounts={guestCounts} />
              </div>

              <div className="button-item" style={{ width: 'auto', padding: '0 15px', display: 'flex', alignItems: 'flex-end' }}>
                <button
                  className="mainSearch__submit button -dark-1 py-15 px-35 h-60 col-12 rounded-4 bg-blue-1 text-white"
          onClick={handleSubmit}
                  style={{ whiteSpace: 'nowrap', marginBottom: '5px' }}
        >
                  <i className="icon-search text-20 mr-10" />
          Search Packages
                </button>
              </div>
            </div>
          </div>
        </Paper>
        
        <ListingCards />
      </Box>
      
      <Snackbar
        open={openSnackbar}
        autoHideDuration={6000}
        onClose={handleCloseSnackbar}
        anchorOrigin={{ vertical: "top", horizontal: "center" }}
      >
        <Alert onClose={handleCloseSnackbar} severity={snackbarSeverity}>
          {snackbarMessage}
        </Alert>
      </Snackbar>

      <style jsx global>{`
        /* Additional global CSS fixes for dropdowns */
        .dropdown-menu {
          z-index: 1050 !important;
          position: relative !important;
        }
        
        .rmdp-container {
          z-index: 1050 !important;
        }
        
        .rmdp-calendar {
          z-index: 1050 !important;
        }
        
        .rmdp-wrapper {
          z-index: 1050 !important;
          position: absolute !important;
        }
        
        .mainSearch {
          overflow: visible !important;
        }
        
        .searchMenu-loc, 
        .searchMenu-date, 
        .searchMenu-guests {
          position: relative;
          z-index: 40;
        }
        
        /* Ensure specific dropdown ordering */
        .searchMenu-loc .shadow-2 {
          z-index: 1060 !important;
        }
        
        .searchMenu-date .rmdp-wrapper {
          z-index: 1050 !important;
          top: 100% !important;
        }
        
        .searchMenu-guests .shadow-2 {
          z-index: 1040 !important;
        }
        
        /* Fix for position context */
        .button-grid {
          position: relative;
          overflow: visible !important;
        }
        
        /* Ensure dropdown menus are not cut off */
        .button-item {
          overflow: visible !important;
        }
        
        /* Fix global dropdown issues */
        body .dropdown-menu.show {
          display: block !important;
          position: absolute !important;
          transform: none !important;
          top: 100% !important;
        }
      `}</style>
    </StyledContainer>
  );
};

export default PreDefinePackages;
