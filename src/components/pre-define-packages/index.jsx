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
import CitySearch from '../hero/hero-2/CitySearch';
import GuestSearch from '../hero/hero-3/GuestSearch';
import HeroGuestSearch from '../hero/hero-1/GuestSearch';
import SelectAgent from '../hero/hero-3/SelectAgent';
import DateSelect from './common/DateSelect';
import Hero1 from '@/components/hero/hero-1';
import { fetchPackages, setSearchParams, clearPackages } from '../../slice/tour-packages/prePackagesSlice';
import ListingCards from './common/ListingCards';
import {
  fetchDMCsByCountry,
  setSelectedDmcId,
  clearSelectedDmc,
  selectDMCs,
  selectDMCLoading,
  selectDMCError,
} from '../../slice/dmc/dmcSlice';
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



const PreDefinePackages = ({ variant = "default" } = {}) => {
  const isHero = variant === "hero";
  const dispatch = useDispatch();
  const { searchParams } = useSelector(state => state.prePackages);
  const { isAuthenticated, userRole } = useSelector(state => state.auth);
  const showAgentSelector = isAuthenticated && userRole !== 'Agent';
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('md'));
  const isTablet = useMediaQuery(theme.breakpoints.down('lg'));
  const [locationData, setLocationData] = useState(null); // Combined location data (country + city)
  const [selectedCountry, setSelectedCountry] = useState(null); // Selected country object for CitySearch
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

  // Auto-fetch DMCs when location is selected (for UI preview)
  useEffect(() => {
    if (locationData && locationData.country && !hasSearched) {
      const countryName = locationData.country;
      // console.log('🏢 Auto-fetching DMCs for country:', countryName);
      dispatch(fetchDMCsByCountry([countryName]));
    }
  }, [locationData, dispatch, hasSearched]);

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
      if (searchParams.country) {
        // Restore country object for CitySearch
        setSelectedCountry({
          name: searchParams.country,
          code: searchParams.country_code || null,
          country_code: searchParams.country_code || null
        });
        
        // Restore full location data
        if (searchParams.city) {
          setLocationData({
            country: searchParams.country,
            city: searchParams.city,
            countryCode: searchParams.country_code || null,
            cityCode: searchParams.city_code || null
          });
        } else {
          setLocationData({
            country: searchParams.country,
            countryCode: searchParams.country_code || null,
            city: null,
            cityCode: null
          });
        }
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
    // console.log('📍 Location data received:', location);
    setLocationData(location);
  };

  const handleCountrySelect = (country) => {
    // Handle country selection from LocationSearch
    setSelectedCountry(country);
    // Update locationData with country info (city will be null until city is selected)
    if (country) {
      setLocationData({
        country: country.name,
        countryCode: country.code || country.country_code,
        city: null,
        cityCode: null
      });
      // Clear packages when country changes (new search will be needed)
      dispatch(clearPackages());
    } else {
      // Country cleared, reset everything
      setSelectedCountry(null);
      setLocationData(null);
      // Clear packages when country is cleared
      dispatch(clearPackages());
    }
  };

  const handleCitySelect = (cityData) => {
    // Handle city selection from CitySearch
    // cityData contains: { country, countryCode, city, cityCode }
    if (cityData) {
      setLocationData(cityData);
      // Clear packages when city changes (new search will be needed)
      dispatch(clearPackages());
    } else {
      // City cleared, keep country but clear city
      if (selectedCountry) {
        setLocationData({
          country: selectedCountry.name,
          countryCode: selectedCountry.code || selectedCountry.country_code,
          city: null,
          cityCode: null
        });
        // Clear packages when city is cleared
        dispatch(clearPackages());
      }
    }
  };

  const handleAgentSelect = (agent) => {
    setSelectedAgent(agent);
  };

  // === DMC Selection Handlers (Single Selection) ===
  const handleDMCCardClick = (dmc) => {
    // console.log('🏢 DMC card clicked:', dmc);
    
    // Check if this DMC is already selected
    const isSelected = selectedDmcId === dmc.dmcId;
    
    if (isSelected) {
      // Deselect: clear the selection
      dispatch(clearSelectedDmc());
      // console.log('🏢 DMC deselected');
    } else {
      // Select: set this DMC as the selected one (replaces any previous selection)
      dispatch(setSelectedDmcId({ dmcId: dmc.dmcId, dmcData: dmc }));
      // console.log('🏢 DMC selected:', dmc.name);
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

  const handleSubmit = async () => {
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
    
    // console.log('📦 Submitting package search with data:', formData);
    
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
    
    // Set search status to true
    setHasSearched(true);
    
    // Clear existing packages immediately to avoid showing stale data
    dispatch(clearPackages());
    
    // Store search parameters first
    dispatch(setSearchParams(searchParams));
    
    // Ensure DMCs are fetched and first one is auto-selected before searching packages
    // This ensures the first DMC is included in the initial package search
    try {
      // Only fetch DMCs if they haven't been fetched yet or if no DMC is selected
      if (locationData && locationData.country && (!dmcOptions || dmcOptions.length === 0 || !selectedDmcId)) {
        // console.log('🏢 Fetching DMCs before package search');
        await dispatch(fetchDMCsByCountry([locationData.country])).unwrap();
        // console.log('🏢 DMCs fetched, first DMC auto-selected');
        
        // Small delay to ensure Redux state is updated with first DMC selection
        await new Promise(resolve => setTimeout(resolve, 100));
      } else {
        // console.log('🏢 DMCs already loaded, DMC ID:', selectedDmcId);
      }
    } catch (dmcError) {
      // console.error('🏢 Failed to fetch DMCs, continuing with package search anyway:', dmcError);
    }
    
    // Now fetch packages - the first DMC ID will be automatically included from Redux state
    dispatch(fetchPackages({ searchParams, start: 0, limit: 5 }))
      .unwrap()
      .then(() => {
        setSnackbarMessage("Search successful! Fetching packages...");
        setSnackbarSeverity("success");
        setOpenSnackbar(true);
      })
      .catch((error) => {
        setSnackbarMessage(error || "Failed to fetch packages. Please try again.");
        setSnackbarSeverity("error");
        setOpenSnackbar(true);
      });
  };


  const listingProps = {
    hasSearched,
    selectedDmcId,
    selectedDmcData,
    locationData,
    dmcOptions,
    filteredDMCs,
    dmcLoading,
    dmcError,
    filterText,
    handleFilterChange,
    handleDMCCardClick,
    isDMCSelected,
  };

  const snackbar = (
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
  );

  const dropdownFixStyles = (
    <style jsx global>{`
      .dropdown-menu {
        z-index: 1050 !important;
        position: relative !important;
      }
      .rmdp-container,
      .rmdp-calendar {
        z-index: 1050 !important;
      }
      .rmdp-wrapper {
        z-index: 1050 !important;
        position: absolute !important;
      }
      body .dropdown-menu.show {
        display: block !important;
        position: absolute !important;
        transform: none !important;
        top: 100% !important;
      }
    `}</style>
  );

  const cellLabel = {
    fontSize: 11,
    fontWeight: 600,
    letterSpacing: "0.04em",
    textTransform: "uppercase",
    color: "#64748b",
    marginBottom: 4,
  };

  if (isHero) {
    const gridCols = showAgentSelector
      ? "1.2fr 1.3fr 0.9fr 1fr 0.9fr auto"
      : "1.3fr 1.4fr 1.1fr 1fr auto";

    const heroSearch = (
      <div
        className="pp-hero-search hero-search-reserve"
        style={{ paddingBottom: 0 }}
      >
        <div
          className="bg-white hero-search-bar pp-hero-search__bar"
          style={{
            position: "relative",
            zIndex: 50,
            borderRadius: 12,
            boxShadow: "none",
            border: 0,
            padding: 8,
            width: "100%",
            maxWidth: "none",
            margin: 0,
            display: "grid",
            gridTemplateColumns: gridCols,
            alignItems: "stretch",
            gap: 5,
          }}
        >
          <div
            className="pp-hero-search__cell"
            style={{
              minWidth: 0,
              borderRight: "1px solid #e2e8f0",
              padding: "8px 14px",
            }}
          >
            <div style={{ ...cellLabel, marginBottom: 2 }}>Location</div>
            <LocationSearch
              onLocationSelect={handleLocationSelect}
              onCountrySelect={handleCountrySelect}
            />
          </div>

          <div
            className="pp-hero-search__cell"
            style={{
              minWidth: 0,
              borderRight: "1px solid #e2e8f0",
            }}
          >
            <CitySearch
              selectedCountry={selectedCountry}
              onCitySelect={handleCitySelect}
            />
          </div>

          {showAgentSelector ? (
            <div
              className="pp-hero-search__cell"
              style={{
                minWidth: 0,
                borderRight: "1px solid #e2e8f0",
                padding: "8px 14px",
              }}
            >
              <div style={{ ...cellLabel, marginBottom: 2 }}>Agent</div>
              <SelectAgent
                onAgentSelect={handleAgentSelect}
                initialValue={selectedAgent}
              />
            </div>
          ) : null}

          <div
            className="pp-hero-search__cell"
            style={{
              minWidth: 0,
              borderRight: "1px solid #e2e8f0",
              padding: "8px 14px",
            }}
          >
            <div style={{ ...cellLabel, marginBottom: 2 }}>Travel date</div>
            <DateSelect onChange={handleDateChange} value={selectedDate} />
          </div>

          <div
            className="pp-hero-search__cell"
            style={{
              minWidth: 0,
              borderRight: "1px solid #e2e8f0",
            }}
          >
            <HeroGuestSearch
              onGuestChange={handleGuestChange}
              guestCounts={guestCounts}
            />
          </div>

          <div
            className="hero-search-bar__cta pp-hero-search__cta-wrap"
            style={{
              display: "flex",
              alignItems: "center",
              padding: 4,
            }}
          >
            <Button
              type="button"
              variant="contained"
              onClick={handleSubmit}
              className="pp-hero-search__cta"
              disableElevation
              sx={{
                minWidth: 150,
                height: "100%",
                minHeight: 52,
                borderRadius: "9px",
                border: "none",
                fontSize: 13,
                fontWeight: 700,
                letterSpacing: 0,
                textTransform: "none",
                color: "#fff",
                background: "#234adc",
                boxShadow: "none",
                px: "18px",
                whiteSpace: "nowrap",
                "&:hover": {
                  background: "#1d3fc0",
                  boxShadow: "none",
                },
              }}
            >
              Find packages →
            </Button>
          </div>
        </div>

        <div className="hero-room-options">
          <button
            type="button"
            className="hero-room-options__btn"
            onClick={() => {
              const guestToggle = document.querySelector(
                ".pp-hero-search .searchMenu-guests [data-bs-toggle='dropdown']"
              );
              if (guestToggle) guestToggle.click();
            }}
          >
            Room &amp; traveller options · 1 room(s) ·{" "}
            {guestCounts.Children || 0} children · {guestCounts.Infants || 0}{" "}
            infants ↓
          </button>
        </div>
      </div>
    );

    return (
      <div className="partner-package-search">
        <Hero1
          pageKicker="Fixed itinerary packages"
          pageTitle="Start with a journey already planned."
          pageSubtitle="Set location, city, date and travellers — then browse packages to customise."
          kicker="Package search"
          title="Where are we taking them?"
          subtitle="Search ready-made packages by destination, date and travellers."
          footerNote="Ready-made packages — hotels, attractions, transfers & more in one itinerary"
          searchSlot={heroSearch}
          showServiceChips={false}
        />

        {hasSearched ? (
          <section className="partner-card partner-packages__results">
            <h2 className="partner-card__title">Matching packages</h2>
            <p className="partner-card__sub">
              Open a package to review the itinerary and continue booking.
            </p>
            <div className="partner-packages__embed">
              <ListingCards {...listingProps} />
            </div>
          </section>
        ) : null}

        {snackbar}
        {dropdownFixStyles}
      </div>
    );
  }

  return (
    <StyledContainer maxWidth="lg">
      <Box sx={{ position: "relative", zIndex: 1 }}>
        <TitleSection>
          <IconContainer>
            <Typography
              variant={isMobile ? "h4" : "h3"}
              component="h1"
              sx={{
                fontWeight: 700,
                color: "white",
                mt: isMobile ? 2 : 3,
                letterSpacing: "0.5px",
                display: "flex",
                alignItems: "center",
                flexDirection: isMobile ? "column" : "row",
                textAlign: isMobile ? "center" : "left",
                gap: isMobile ? 1 : 0,
              }}
            >
              {isMobile ? "Fixed Itinerary" : "Fixed Itinerary Packages"}
              <ExploreIcon
                sx={{ ml: isMobile ? 0 : 1, fontSize: isMobile ? 24 : 28 }}
              />
            </Typography>
          </IconContainer>
          <Divider
            sx={{
              width: isMobile ? "60px" : "100px",
              height: "4px",
              backgroundColor: "secondary.main",
              mb: isMobile ? 2 : 3,
            }}
          />
          <Typography
            variant={isMobile ? "body1" : "subtitle1"}
            color="#ece9f1"
            textAlign="center"
            sx={{ px: isMobile ? 2 : 0 }}
          >
            Discover our exclusive pre-arranged travel experiences
          </Typography>
        </TitleSection>

        <Paper
          elevation={3}
          sx={{
            borderRadius: isMobile ? "16px" : "50px",
            overflow: "visible",
            mb: 4,
            background: "white",
            boxShadow: "0 10px 40px rgba(0, 0, 0, 0.15)",
          }}
        >
          <Box
            sx={{
              display: "flex",
              flexWrap: isMobile ? "wrap" : "nowrap",
              alignItems: "stretch",
              p: isMobile ? 2 : 1,
              gap: isMobile ? 2 : 0,
            }}
          >
            <Box
              sx={{
                flex: isMobile ? "1 1 100%" : "1",
                minWidth: 0,
                borderRight: isMobile ? "none" : "1px solid #e0e0e0",
                px: isMobile ? 0 : 2,
                py: isMobile ? 0 : 1.5,
                display: "flex",
                flexDirection: "column",
                justifyContent: "flex-start",
                height: "100%",
              }}
            >
              <h4
                className="text-15 fw-500 ls-2 lh-16"
                style={{ marginBottom: "8px", marginTop: "0" }}
              >
                Location
              </h4>
              <LocationSearch
                onLocationSelect={handleLocationSelect}
                onCountrySelect={handleCountrySelect}
              />
            </Box>

            <Box
              sx={{
                flex: isMobile ? "1 1 100%" : "1",
                minWidth: 0,
                borderRight: isMobile ? "none" : "1px solid #e0e0e0",
                px: isMobile ? 0 : 2,
                py: isMobile ? 0 : 1.5,
                display: "flex",
                flexDirection: "column",
                justifyContent: "flex-start",
                height: "100%",
              }}
            >
              <h4
                className="text-15 fw-500 ls-2 lh-16"
                style={{ marginBottom: "8px", marginTop: "0" }}
              >
                City
              </h4>
              <CitySearch
                selectedCountry={selectedCountry}
                onCitySelect={handleCitySelect}
              />
            </Box>

            {showAgentSelector && (
              <Box
                sx={{
                  flex: isMobile ? "1 1 100%" : "1",
                  minWidth: 0,
                  borderRight: isMobile ? "none" : "1px solid #e0e0e0",
                  px: isMobile ? 0 : 2,
                  py: isMobile ? 0 : 1.5,
                  display: "flex",
                  flexDirection: "column",
                  justifyContent: "center",
                }}
              >
                <SelectAgent
                  onAgentSelect={handleAgentSelect}
                  initialValue={selectedAgent}
                />
              </Box>
            )}

            <Box
              sx={{
                flex: isMobile ? "1 1 100%" : "1.2",
                minWidth: 0,
                borderRight: isMobile ? "none" : "1px solid #e0e0e0",
                px: isMobile ? 0 : 2,
                py: isMobile ? 0 : 1.5,
                display: "flex",
                flexDirection: "column",
                justifyContent: "flex-start",
                height: "100%",
              }}
            >
              <h4
                className="text-15 fw-500 ls-2 lh-16"
                style={{ marginBottom: "8px", marginTop: "0" }}
              >
                Select Date
              </h4>
              <DateSelect onChange={handleDateChange} value={selectedDate} />
            </Box>

            <Box
              sx={{
                flex: isMobile ? "1 1 100%" : "1",
                minWidth: 0,
                borderRight: isMobile ? "none" : "1px solid #e0e0e0",
                px: isMobile ? 0 : 2,
                py: isMobile ? 0 : 1.5,
                display: "flex",
                flexDirection: "column",
                justifyContent: "flex-start",
                height: "100%",
              }}
            >
              <h4
                className="text-15 fw-500 ls-2 lh-16"
                style={{ marginBottom: "8px", marginTop: "0" }}
              >
                Guests
              </h4>
              <GuestSearch
                onGuestChange={handleGuestChange}
                guestCounts={guestCounts}
              />
            </Box>

            <Box
              sx={{
                flex: isMobile ? "1 1 100%" : "0 0 auto",
                display: "flex",
                alignItems: "flex-end",
                justifyContent: "center",
                px: isMobile ? 0 : 2,
                py: isMobile ? 0 : 1,
                height: "100%",
              }}
            >
              <Button
                variant="contained"
                onClick={handleSubmit}
                startIcon={<i className="icon-search" />}
                sx={{
                  bgcolor: "#1976d2",
                  color: "white",
                  px: isMobile ? 4 : 4,
                  py: isMobile ? 1.5 : 1.75,
                  borderRadius: isMobile ? "12px" : "40px",
                  fontSize: "0.9rem",
                  fontWeight: 600,
                  textTransform: "none",
                  boxShadow: "none",
                  whiteSpace: "nowrap",
                  width: isMobile ? "100%" : "auto",
                  "&:hover": {
                    bgcolor: "#1565c0",
                    boxShadow: "0 4px 12px rgba(25, 118, 210, 0.3)",
                  },
                }}
              >
                {isMobile ? "Search" : "Search Packages"}
              </Button>
            </Box>
          </Box>
        </Paper>

        <ListingCards {...listingProps} />
      </Box>

      {snackbar}
      {dropdownFixStyles}
    </StyledContainer>
  );
};

export default PreDefinePackages;
