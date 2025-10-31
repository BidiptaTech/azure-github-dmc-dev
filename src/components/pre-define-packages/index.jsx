import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import {
  Box,
  Typography,
  Button,
  Snackbar,
  Container,
  Paper,
  styled,
  Divider,
  useTheme,
  useMediaQuery,
} from '@mui/material';
import MuiAlert from "@mui/material/Alert";
import LocationSearch from '../hero/hero-2/LocationSearch';
// import CitySearch from '../hero/hero-3/CitySearch';
import GuestSearch from '../hero/hero-3/GuestSearch';
import SelectAgent from '../hero/hero-3/SelectAgent';
import DateSelect from './common/DateSelect';
import { fetchPackages, setSearchParams } from '../../slice/tour-packages/prePackagesSlice';
import ListingCards from './common/ListingCards';
import {
  fetchDMCsByCountry,
  setSelectedDmcId,
  clearSelectedDmc,
  selectDMCs,
  selectDMCLoading,
  selectDMCError,
} from '../../slice/dmc/dmcSlice';
// import LuggageIcon from '@mui/icons-material/Luggage';
// import LuggageIcon from '@mui/icons-material/Luggage';
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
  [theme.breakpoints.down('md')]: {
    marginTop: theme.spacing(2),
    marginBottom: theme.spacing(2),
  },
}));

const IconContainer = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  marginBottom: theme.spacing(2),
  [theme.breakpoints.down('md')]: {
    marginBottom: theme.spacing(1),
  },
}));



const PreDefinePackages = () => {
  const dispatch = useDispatch();
  const { searchParams } = useSelector(state => state.prePackages);
  const { isAuthenticated, userRole } = useSelector(state => state.auth);
  const showAgentSelector = isAuthenticated && userRole !== 'Agent';
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  const isTablet = useMediaQuery(theme.breakpoints.down('lg'));
  const [locationData, setLocationData] = useState(null); // Combined location data (country + city)
  const [selectedAgent, setSelectedAgent] = useState(null);
  
  // DMC-related state and selectors
  const apiDMCs = useSelector(selectDMCs);
  const dmcLoading = useSelector(selectDMCLoading);
  const dmcError = useSelector(selectDMCError);
  const selectedDmcId = useSelector((state) => state.dmc.dmcId); // Single DMC selection
  const selectedDmcData = useSelector((state) => state.dmc.selectedDmcData); // Single DMC data
  
  // Component state for DMC management
  const [dmcOptions, setDmcOptions] = useState([]);
  const [filterText, setFilterText] = useState('');
  const [hasDMCsAvailable, setHasDMCsAvailable] = useState(false);
  const [selectedDate, setSelectedDate] = useState(new Date().toISOString().split('T')[0]); // Today's date
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
  const [hasSearched, setHasSearched] = useState(false);
  const [isInitializing, setIsInitializing] = useState(true);

  // Auto-fetch DMCs when location is selected
  useEffect(() => {
    if (locationData && locationData.country) {
      const countryName = locationData.country;
      console.log('🏢 Auto-fetching DMCs for country:', countryName);
      dispatch(fetchDMCsByCountry([countryName]));
    }
  }, [locationData, dispatch]);

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

  // Check if search parameters exist when component mounts
  useEffect(() => {
    if (searchParams) {
      setHasSearched(true);
      
      // Restore location data (country + city combined)
      if (searchParams.country && searchParams.city) {
        setLocationData({
          country: searchParams.country,
          city: searchParams.city,
          countryCode: searchParams.country_code || null,
          cityCode: searchParams.city_code || null
        });
      }
      
      if (searchParams.agent_id && showAgentSelector) setSelectedAgent(searchParams.agent_id);
      if (searchParams.date) setSelectedDate(searchParams.date);
      
      // Restore guest counts
      const updatedGuestCounts = {
        Adults: searchParams.adults || 1,
        Children: searchParams.children || 0,
        Infants: searchParams.infants || 0,
        maleCount: searchParams.male_count || 0,
        femaleCount: searchParams.female_count || 0,
        ages: searchParams.children_ages ? searchParams.children_ages.split(',') : []
      };
      setGuestCounts(updatedGuestCounts);
    }
    
    // Mark initialization as complete after a short delay
    setTimeout(() => {
      setIsInitializing(false);
    }, 100);
  }, [searchParams, showAgentSelector]);
  
  

  const handleDateChange = (date) => {
    setSelectedDate(date);
  };

  const handleGuestChange = (newGuestCounts) => {
    setGuestCounts(newGuestCounts);
  };
  
  const handleLocationSelect = (location) => {
    // LocationSearch (hero-2) returns combined data: { country, countryCode, city, cityCode }
    // or null when cleared
    console.log('📍 Location data received:', location);
    setLocationData(location);
  };

  const handleAgentSelect = (agent) => {
    setSelectedAgent(agent);
  };

  // === DMC Selection Handlers (Single Selection) ===
  const handleDMCCardClick = (dmc) => {
    console.log('🏢 DMC card clicked:', dmc);
    
    // Check if this DMC is already selected
    const isSelected = selectedDmcId === dmc.dmcId;
    
    if (isSelected) {
      // Deselect: clear the selection
      dispatch(clearSelectedDmc());
      console.log('🏢 DMC deselected');
    } else {
      // Select: set this DMC as the selected one (replaces any previous selection)
      dispatch(setSelectedDmcId({ dmcId: dmc.dmcId, dmcData: dmc }));
      console.log('🏢 DMC selected:', dmc.name);
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
    return selectedDmcId === dmc.dmcId;
  };
  
  const handleCloseSnackbar = () => {
    setOpenSnackbar(false);
  };

  const validateForm = () => {
    // Validate location selection (country)
    if (!locationData || !locationData.country) {
      setSnackbarMessage("Please select a country");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Validate city selection
    if (!locationData.city) {
      setSnackbarMessage("Please select a city");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }
    
   

    // Validate agent selection only if the agent selector is shown
    if (showAgentSelector && !selectedAgent) {
      setSnackbarMessage("Please select an agent");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Validate agent selection only if the agent selector is shown
    if (showAgentSelector && !selectedAgent) {
      setSnackbarMessage("Please select an agent");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Validate agent selection only if the agent selector is shown
    if (showAgentSelector && !selectedAgent) {
      setSnackbarMessage("Please select an agent");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Validate date selection
    if (!selectedDate) {
      setSnackbarMessage("Please select a valid date");
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
      location: locationData,
      date: selectedDate,
      guests: {
        adults: guestCounts.Adults,
        maleCount: guestCounts.maleCount || 0,
        femaleCount: guestCounts.femaleCount || 0,
        children: guestCounts.Children,
        childrenAges: guestCounts.ages || [],
        infants: guestCounts.Infants
      }
    };

    // Add agent data only if the agent selector is shown
    if (showAgentSelector && selectedAgent) {
      formData.agent = selectedAgent;
    }
    
    console.log('📦 Submitting package search with data:', formData);
    
    // Format the data for API request
    const searchParams = {
      country: locationData.country,
      city: locationData.city,
      country_code: locationData.countryCode || null,
      city_code: locationData.cityCode || null,
      date: selectedDate,
      adults: guestCounts.Adults,
      male_count: guestCounts.maleCount || 0,
      female_count: guestCounts.femaleCount || 0,
      children: guestCounts.Children,
      children_ages: guestCounts.ages?.join(','),
      infants: guestCounts.Infants
    };

    // Add agent_id parameter only if the agent selector is shown
    if (showAgentSelector && selectedAgent) {
      searchParams.agent_id = selectedAgent?.id;
    }
    
    // Add DMC ID if one is selected (single selection)
    if (selectedDmcId) {
      searchParams.dmc_id = selectedDmcId;
      console.log('🏢 Including DMC ID in search:', selectedDmcId);
    }
    

    
    // Set search status to true
    setHasSearched(true);
    
    // Dispatch actions to fetch packages and store search parameters
    dispatch(setSearchParams(searchParams));
    dispatch(fetchPackages({ searchParams, start: 0, limit: 5 }))
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
      <Box sx={{ position: 'relative', zIndex: 1 }}>
        <TitleSection>
          <IconContainer>
            <Typography 
              variant={isMobile ? "h4" : "h3"}
              component="h1" 
              sx={{ 
                fontWeight: 700, 
                color: 'white',
                mt: isMobile ? 2 : 3,
                letterSpacing: '0.5px',
                display: 'flex',
                alignItems: 'center',
                flexDirection: isMobile ? 'column' : 'row',
                textAlign: isMobile ? 'center' : 'left',
                gap: isMobile ? 1 : 0
              }}
            >
              {isMobile ? 'Fixed Itinerary' : 'Fixed Itinerary Packages'}
              <ExploreIcon sx={{ ml: isMobile ? 0 : 1, fontSize: isMobile ? 24 : 28 }} />
            </Typography>
          </IconContainer>
          <Divider sx={{ 
            width: isMobile ? '60px' : '100px', 
            height: '4px', 
            backgroundColor: 'secondary.main', 
            mb: isMobile ? 2 : 3 
          }} />
          <Typography 
            variant={isMobile ? "body1" : "subtitle1"} 
            color="#ece9f1" 
            textAlign="center"
            sx={{ px: isMobile ? 2 : 0 }}
          >
            Discover our exclusive pre-arranged travel experiences
          </Typography>
        </TitleSection>

        <Paper elevation={3} sx={{ borderRadius: '8px', overflow: 'visible', mb: 4 }}>
          <div className="mainSearch bg-white pr-20 py-20 lg:px-20 lg:pt-5 lg:pb-20 rounded-4">
            <div className="button-grid items-center" style={{ 
              display: 'flex', 
              flexWrap: isMobile ? 'wrap' : 'nowrap',
              gap: isMobile ? '16px' : '0'
            }}>
              {/* Combined Location Search (Country + City) - Uses hero-2 pattern */}
              <div style={{ 
                flex: isMobile ? '1 1 100%' : showAgentSelector ? '1.5' : '2', 
                minWidth: '0',
                marginBottom: isMobile ? '8px' : '0'
              }}>
                <LocationSearch onLocationSelect={handleLocationSelect} />
              </div>
              {/* <div style={{ 
                flex: isMobile ? '1 1 100%' : showAgentSelector ? '1.5' : '2', 
                minWidth: '0',
                marginBottom: isMobile ? '8px' : '0'
              }}>
                <CitySearch onLocationSelect={handleLocationSelect} />
              </div> */}

              {showAgentSelector && (
                <div style={{ 
                  flex: isMobile ? '1 1 100%' : '1', 
                  minWidth: '0',
                  marginBottom: isMobile ? '8px' : '0'
                }}>
                  <SelectAgent onAgentSelect={handleAgentSelect} initialValue={selectedAgent} />
                </div>
              )}

              <div style={{ 
                flex: isMobile ? '1 1 100%' : '1.2', 
                minWidth: '0',
                marginBottom: isMobile ? '8px' : '0'
              }} className="searchMenu-date px-30 lg:py-20 lg:px-0 js-form-dd js-calendar">
                <div>
                  <h4 className="text-15 fw-500 ls-2 lh-16">
                    Select Date
                  </h4>
                  <DateSelect onChange={handleDateChange} value={selectedDate} />
                </div>
              </div>

              <div style={{ 
                flex: isMobile ? '1 1 100%' : '1', 
                minWidth: '0',
                marginBottom: isMobile ? '8px' : '0'
              }}>
                <GuestSearch onGuestChange={handleGuestChange} guestCounts={guestCounts} />
              </div>

              <div className="button-item" style={{ 
                width: isMobile ? '100%' : 'auto', 
                padding: isMobile ? '0' : '0 15px', 
                display: 'flex', 
                alignItems: 'flex-end',
                marginTop: isMobile ? '8px' : '0'
              }}>
                <button
                  className="mainSearch__submit button -dark-1 py-15 px-35 h-60 col-12 rounded-4 bg-blue-1 text-white"
                  onClick={handleSubmit}
                  style={{ 
                    whiteSpace: 'nowrap', 
                    marginBottom: '5px',
                    width: isMobile ? '100%' : 'auto'
                  }}
                >
                  <i className="icon-search text-20 mr-10" />
                  {isMobile ? 'Search' : 'Search Packages'}
                </button>
              </div>
            </div>
          </div>
        </Paper>
        
        <ListingCards 
          hasSearched={hasSearched} 
          selectedDmcId={selectedDmcId}
          selectedDmcData={selectedDmcData}
          locationData={locationData}
          dmcOptions={dmcOptions}
          filteredDMCs={filteredDMCs}
          dmcLoading={dmcLoading}
          dmcError={dmcError}
          filterText={filterText}
          handleFilterChange={handleFilterChange}
          handleDMCCardClick={handleDMCCardClick}
          isDMCSelected={isDMCSelected}
        />
        
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
          .MuiGrid-item {
            margin-bottom: 8px;
          }
          
          .searchMenu-loc, 
          .searchMenu-date, 
          .searchMenu-guests {
            margin-bottom: 16px;
          }
        }

        @media (max-width: 480px) {
          .MuiContainer-root {
            padding-left: 8px !important;
            padding-right: 8px !important;
          }
        }
      `}</style>
    </StyledContainer>
  );
};

export default PreDefinePackages;
