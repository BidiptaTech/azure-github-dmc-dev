import React, { useEffect, useState, useRef } from "react";
import { Swiper } from "swiper/react";
import { Navigation, Pagination } from "swiper";
import { useSelector, useDispatch } from "react-redux";
import { useNavigate } from "react-router-dom";
import {
  selectRestaurants,
  fetchRestaurants,
  selectFilters,
  updateModeMap,
  fetchRestaurantsDetails,
  setHighestTotalPrice,
} from "../../../slice/restaurant/RestaurantsSlice";
import { selectBookingType, setBookingMode } from "../../../slice/common/commonSlice";
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../../slice/dmc/dmcSlice"; // Import DMC slice selectors
import TopHeaderFilter from "@/components/restaurants/tour-list-v1/TopHeaderFilter";
import {
  Box,
  Button,
  FormControl,
  // FormControlLabel,
  Radio,
  RadioGroup,
  Typography,
  Avatar,
} from "@mui/material";

// Create a Skeleton component
const RestaurantSkeleton = () => (
  <div className="col-12">
    <div className="border-top-light pt-30">
      <div className="row x-gap-20 y-gap-20">
        <div className="col-md-auto">
          <div className="cardImage ratio ratio-1:1 w-250 md:w-1/1 rounded-4">
            <div style={{ 
              background: '#f0f0f0',
              height: '200px',
              animation: 'pulse 1.5s infinite'
            }} />
          </div>
        </div>
        <div className="col-md">
          <div style={{ 
            background: '#f0f0f0',
            height: '24px',
            width: '70%',
            marginBottom: '10px',
            animation: 'pulse 1.5s infinite'
          }} />
          <div style={{ 
            background: '#f0f0f0',
            height: '18px',
            width: '40%',
            marginBottom: '10px',
            animation: 'pulse 1.5s infinite'
          }} />
          <div style={{ 
            background: '#f0f0f0',
            height: '18px',
            width: '60%',
            animation: 'pulse 1.5s infinite'
          }} />
        </div>
        <div className="col-md-auto">
          <div style={{ 
            background: '#f0f0f0',
            height: '150px',
            width: '160px',
            marginBottom: '10px',
            animation: 'pulse 1.5s infinite'
          }} />
          <div style={{ 
            background: '#f0f0f0',
            height: '40px',
            width: '160px',
            animation: 'pulse 1.5s infinite'
          }} />
        </div>
      </div>
    </div>
  </div>
);

// Add CSS for skeleton animation
const skeletonStyles = `
  @keyframes pulse {
    0% { opacity: 0.6; }
    50% { opacity: 1; }
    100% { opacity: 0.6; }
  }
`;

const TourProperties = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const filters = useSelector(selectFilters);
  const restaurants = useSelector(selectRestaurants);
  const modeMap = useSelector((state) => state.restaurants.modeMap);
  const [sortOrder, setSortOrder] = useState("asc");
  const [sortedRestaurants, setSortedRestaurants] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isSearching, setIsSearching] = useState(false);
  // Get DMC logo and company name from DMC slice instead of auth slice
  const dmcLogo = useSelector(selectSelectedDmcLogo);
  const dmcCompanyName = useSelector(selectSelectedDmcCompanyName) || 'DMC';
  const [selectedModes, setSelectedModes] = useState({});
  const bookingType = useSelector(selectBookingType);
  // Add this to track the restaurant loading status from Redux
  const restaurantStatus = useSelector((state) => state.restaurants.status);
  
  // Add currency information selectors
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencySymbol = useSelector((state) => state.auth.usdCurrencySymbol);
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  // Add PriceHide selector
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  

  useEffect(() => {
    dispatch(fetchRestaurants()).then((response) => {
      response.payload?.forEach((restaurant) => {
        const hasValidDmcPrice = 
          restaurant.dmc_breakfast_price > 0 || 
          restaurant.dmc_lunch_price > 0 || 
          restaurant.dmc_dinner_price > 0;
        const hasValidTravClicksPrice = 
          restaurant.travClicks_breakfast_price > 0 || 
          restaurant.travClicks_lunch_price > 0 || 
          restaurant.travClicks_dinner_price > 0;

        // Set initial mode based on available prices
        let initialMode;
        if (hasValidDmcPrice) {
          initialMode = "dmc";
        } else if (hasValidTravClicksPrice) {
          initialMode = "travclicks";
        } else {
          initialMode = "dmc"; // fallback
        }

        // Always dispatch the updateModeMap action with the determined mode
        dispatch(
          updateModeMap({
            restaurantId: restaurant.id,
            mode: initialMode,
            prices: {
              breakfast: getPrice(restaurant, initialMode, "breakfast"),
              lunch: getPrice(restaurant, initialMode, "lunch"),
              dinner: getPrice(restaurant, initialMode, "dinner"),
            },
          })
        );
      });
    });
  }, []);

  // Update the useEffect to handle search loading based on Redux state
  useEffect(() => {
    if (restaurantStatus === 'loading') {
      // Show skeleton loader when restaurants are loading
      setIsLoading(true);
      setIsSearching(true);
      
      // Set a maximum timeout for the loading state (5 seconds)
      const maxLoadingTimer = setTimeout(() => {
        setIsLoading(false);
        setIsSearching(false);
      }, 5000); // Maximum 5 seconds of loading, then show "no results" if needed
      
      return () => clearTimeout(maxLoadingTimer);
    } else if (restaurantStatus === 'succeeded' || restaurantStatus === 'failed') {
      // Hide loader after data is loaded or if the request failed
      const timer = setTimeout(() => {
        setIsLoading(false);
        setIsSearching(false);
      }, 1500); // 1.5 seconds delay
      
      return () => clearTimeout(timer);
    }
  }, [restaurantStatus]);

  // Modify the useEffect for initial loading to only run once
  useEffect(() => {
    // Only run on initial mount, not when isSearching changes
    const timer = setTimeout(() => {
      if (restaurantStatus !== 'loading') {
        setIsLoading(false);
      }
    }, 2000); // 2 seconds delay for initial load
    
    return () => clearTimeout(timer);
  }, []); // Empty dependency array means this runs once on mount

  const calculateTotalPrice = (restaurant) => {
    const breakfast = restaurant.breakfast_available
      ? Number(restaurant.dmc_breakfast_price) || 0
      : 0;
    const lunch = restaurant.lunch_available
      ? Number(restaurant.dmc_lunch_price) || 0
      : 0;
    const dinner = restaurant.dinner_available
      ? Number(restaurant.dmc_dinner_price) || 0
      : 0;
    const total = breakfast + lunch + dinner;
    return total;
  };

  useEffect(() => {
    if (restaurants.length > 0) {
      const highestPrice = Math.max(...restaurants.map(calculateTotalPrice));
      dispatch(setHighestTotalPrice(highestPrice));
    }
  }, [restaurants, dispatch]);

  const getPrice = (item, mode, mealType) => {
    const priceField = `${mode}_${mealType}_price`;
    const correctedField = Object.keys(item).find(
      (key) => key.toLowerCase() === priceField.toLowerCase()
    );
    return correctedField ? item[correctedField] : "N/A";
  };

  useEffect(() => {
    if (!restaurants || restaurants.length === 0) return;
  
    const filtered = restaurants.filter((item) => {
      // Check if all DMC prices are 0 or less
      const hasValidDmcPrice = item.dmc_breakfast_price > 0 || item.dmc_lunch_price > 0 || item.dmc_dinner_price > 0;
      // Check if all TRAVCLICKS prices are 0 or less
      const hasValidTravClicksPrice = item.travClicks_breakfast_price > 0 || item.travClicks_lunch_price > 0 || item.travClicks_dinner_price > 0;
      
      // If both DMC and TRAVCLICKS prices are invalid, filter out the card completely
      if (!hasValidDmcPrice && !hasValidTravClicksPrice) {
        return false;
      }

      // Get the current price mode from filters
      const priceMode = filters.priceMode || ["dmc", "marketplace"];
      
      // If checkbox is checked (showDmcOnly is true), only show items with valid DMC prices
      if (priceMode.length === 1 && priceMode[0] === "dmc") {
        return hasValidDmcPrice;
      }
      
      // If both price modes are checked but neither has valid prices, don't show the card
      if (!hasValidDmcPrice && !hasValidTravClicksPrice) {
        return false;
      }

      // Calculate total price for price range filter
      const totalPrice = calculateTotalPrice(item);
      if (totalPrice < filters.priceRange.min || totalPrice > filters.priceRange.max) {
        return false;
      }

      // Apply cuisine filter
      if (filters.cuisines && filters.cuisines.length > 0) {
        const itemCuisine = Array.isArray(item.cuisine) ? item.cuisine : [item.cuisine];
        const hasMatchingCuisine = filters.cuisines.some(cuisine => 
          itemCuisine.some(itemC => itemC.toLowerCase() === cuisine.toLowerCase())
        );
        if (!hasMatchingCuisine) {
          return false;
        }
      }

      return true;
    });

    // Sort the filtered restaurants
    const sorted = [...filtered].sort((a, b) => {
      const priceA = calculateTotalPrice(a);
      const priceB = calculateTotalPrice(b);
      return sortOrder === "asc" ? priceA - priceB : priceB - priceA;
    });
  
    setSortedRestaurants(sorted);

    // Modified logic for setting default modes
    const defaultModes = {};
    const priceMode = filters.priceMode || ["dmc", "marketplace"];
    
    sorted.forEach((item) => {
      const hasValidDmcPrice = item.dmc_breakfast_price > 0 || item.dmc_lunch_price > 0 || item.dmc_dinner_price > 0;
      const hasValidTravClicksPrice = item.travClicks_breakfast_price > 0 || item.travClicks_lunch_price > 0 || item.travClicks_dinner_price > 0;

      let mode;
      if (priceMode.length === 1) {
        // Single mode selection
        if (priceMode[0] === "dmc" && hasValidDmcPrice) {
          mode = "dmc";
        } else if (priceMode[0] === "marketplace" && hasValidTravClicksPrice) {
          mode = "travclicks";
        }
      } else {
        // Default mode selection
        mode = hasValidDmcPrice ? "dmc" : (hasValidTravClicksPrice ? "travclicks" : null);
      }

      if (mode) {
        defaultModes[item.id] = { mode };
        dispatch(
          updateModeMap({
            restaurantId: item.id,
            mode: mode,
            prices: {
              breakfast: getPrice(item, mode, "breakfast"),
              lunch: getPrice(item, mode, "lunch"),
              dinner: getPrice(item, mode, "dinner"),
            },
          })
        );
      }
    });

    setSelectedModes(defaultModes);
  }, [restaurants, filters, sortOrder, dispatch]);

  const handleSort = (order) => {
    setSortOrder(order);
  };

  const handleModeChange = (restaurantId, mode) => {
    const restaurant = restaurants.find((item) => item.id === restaurantId);
    if (!restaurant) return;

    // Update local state
    setSelectedModes(prev => ({
      ...prev,
      [restaurantId]: { mode }
    }));

    // Update Redux store
    dispatch(
      updateModeMap({
        restaurantId,
        mode,
        prices: {
          breakfast: getPrice(restaurant, mode, "breakfast"),
          lunch: getPrice(restaurant, mode, "lunch"),
          dinner: getPrice(restaurant, mode, "dinner"),
        },
      })
    );
    
    // Add this line to update the bookingMode in Redux
    dispatch(setBookingMode(mode));
  };

  const handleAttractionClick = (event, item) => {
    event.preventDefault();
    
    // Check if only TravClicks prices are available
    const hasValidDmcPrice = item.dmc_breakfast_price > 0 || item.dmc_lunch_price > 0 || item.dmc_dinner_price > 0;
    const hasValidTravClicksPrice = item.travClicks_breakfast_price > 0 || item.travClicks_lunch_price > 0 || item.travClicks_dinner_price > 0;
    
    // Set default mode to 'travclicks' if only TravClicks prices are available
    let currentMode;
    if (!hasValidDmcPrice && hasValidTravClicksPrice) {
      currentMode = "travclicks";
    } else {
      currentMode = modeMap[item.id]?.mode || "dmc";
    }
    
    // Add this line to update the bookingMode in Redux
    dispatch(setBookingMode(currentMode));
    
    // The slice will automatically use the selected DMC ID from Redux
    // console.log('Selected mode:', currentMode);

    dispatch(
      fetchRestaurantsDetails({ 
        restaurantId: item.id, 
        price_mode: currentMode
        // dmc_id will be automatically handled by the slice using Redux state
      })
    );
    
    navigate(`/dashboard/db-dashboard/restaurants-details/${item.id}`, {
      state: { restaurants: item },
    });
  };

  // Add effect to handle automatic mode switching when DMC checkbox is clicked
  useEffect(() => {
    const priceMode = filters.priceMode || ["dmc", "marketplace"];
    const showDmcOnly = priceMode.length === 1 && priceMode[0] === "dmc";

    if (showDmcOnly) {
      // When DMC checkbox is checked, switch all restaurants to DMC mode
      sortedRestaurants.forEach((restaurant) => {
        if (restaurant.dmc_breakfast_price > 0 || restaurant.dmc_lunch_price > 0 || restaurant.dmc_dinner_price > 0) {
          handleModeChange(restaurant.id, "dmc");
        }
      });
    }
  }, [filters.priceMode]);

  // Add this useEffect to set initial bookingMode when components loads
  useEffect(() => {
    // If there are sorted restaurants, set the bookingMode based on the first item's mode
    if (sortedRestaurants.length > 0) {
      const firstItem = sortedRestaurants[0];
      const itemId = firstItem.id;
      const mode = selectedModes[itemId]?.mode || modeMap[itemId]?.mode || "dmc";
      dispatch(setBookingMode(mode));
    }
  }, [sortedRestaurants, selectedModes, modeMap, dispatch]);

  if (!restaurants || restaurants.length === 0) {
    // Show skeleton loader instead of the message when searching/loading
    if (isLoading || restaurantStatus === 'loading') {
      return (
        <>
          <style>{skeletonStyles}</style>
          <TopHeaderFilter onSort={handleSort} />
          <br />
          {/* Show skeleton loading */}
          {Array(5).fill(null).map((_, index) => (
            <RestaurantSkeleton key={index} />
          ))}
        </>
      );
    }
    
    return (
      <div
        className="no-hotels-message"
        style={{ textAlign: "center", marginTop: "2rem" }}
      >
        <div className="MuiBox-root css-t4lhjn">
          <div className="MuiBox-root css-d0j5jx">
            <img
              src="/icons/restaurant.png"
              alt="Restaurant Icon"
              className="your-icon-class"
              style={{ width: "200px", height: "200px" }}
            />
          </div>
          <h5 className="MuiTypography-root MuiTypography-h5 css-hu3rhi-MuiTypography-root">
            {filters.searchParams?.location 
              ? `No restaurants found in ${filters.searchParams.location.address}. Please try a different location.`
              : "Please provide restaurants location and date of journey and search..."}
          </h5>
        </div>
      </div>
    );
  }

  return (
    <>
      <style>{skeletonStyles}</style>
      <TopHeaderFilter onSort={handleSort} />
      <br />
      
      {isLoading ? (
        Array(5).fill(null).map((_, index) => (
          <RestaurantSkeleton key={index} />
        ))
      ) : sortedRestaurants.length > 0 ? (
        <>
          {sortedRestaurants.map((item) => {
            const currentMode = selectedModes[item.id]?.mode || modeMap[item.id]?.mode || "dmc";
            const priceMode = filters.priceMode || ["dmc", "marketplace"];
            const showDmcOnly = priceMode.length === 1 && priceMode[0] === "dmc";

            // Check if all DMC prices are 0 or less
            const hasValidDmcPrice = item.dmc_breakfast_price > 0 || item.dmc_lunch_price > 0 || item.dmc_dinner_price > 0;
            // Check if all TRAVCLICKS prices are 0 or less
            const hasValidTravClicksPrice = item.travClicks_breakfast_price > 0 || item.travClicks_lunch_price > 0 || item.travClicks_dinner_price > 0;

            // Skip rendering this restaurant card if no valid prices for either option
            if (!hasValidDmcPrice && (!hasValidTravClicksPrice || bookingType === 'enquiry')) {
              return null;
            }

            // Determine if we should show TravClicks mode based on bookingType
            const showTravClicks = bookingType !== 'enquiry' && hasValidTravClicksPrice;

            return (
              <div className="col-12" key={item?.id}>
                <div className="border-top-light pt-30">
                  <div className="row x-gap-20 y-gap-20">
                    <div className="col-md-auto">
                      <div className="cardImage ratio ratio-1:1 w-250 md:w-1/1 rounded-4">
                        <div className="cardImage__content custom_inside-slider">
                          <Swiper
                            className="mySwiper"
                            modules={[Pagination, Navigation]}
                            pagination={{ clickable: true }}
                            navigation={true}
                          >
                            <img
                              className="rounded-4 col-12 js-lazy"
                              src={item?.image}
                              alt={item?.restaurant_name}
                              style={{ height: '200px', objectFit: 'cover' }}
                            />
                          </Swiper>
                        </div>
                      </div>
                    </div>

                    <div className="col-md">
                      <h3 className="text-18 lh-16 fw-500">
                        {item?.restaurant_name}
                      </h3>
                      <p className="text-14 lh-14 mt-5">
                        {item?.city}, {item?.country}
                      </p>
                      <div className="text-14 lh-15 fw-500 mt-20">
                        {Array.isArray(item?.cuisine)
                          ? item.cuisine.join(", ")
                          : item.cuisine}
                      </div>
                    </div>

                    <div className="col-md-auto text-right md:text-left">
                      <div className="mt-20 p-15">
                        <Box sx={{ mt: 1, fontSize: "14px" }}>
                          <FormControl component="fieldset">
                            <RadioGroup
                              row
                              value={currentMode}
                              onChange={(e) => handleModeChange(item.id, e.target.value)}
                            >
                              {showDmcOnly ? (
                                // Show only DMC if checkbox is checked and DMC price is valid
                                hasValidDmcPrice && (
                                  <Box
                                    key="dmc"
                                    onClick={(e) => {
                                      e.preventDefault();
                                      e.stopPropagation();
                                      handleModeChange(item.id, "dmc");
                                    }}
                                    sx={{
                                      textAlign: "left",
                                      border: "2px solid #ccc",
                                      borderRadius: "12px",
                                      p: 1,
                                      m: 1,
                                      width: "160px",
                                      height: "165px", 
                                      bgcolor: currentMode === "dmc" ? "#e6f2ff" : "#fff",
                                      transform: currentMode === "dmc" ? "scale(1.05)" : "scale(1)",
                                      cursor: "pointer",
                                      "&:hover": {
                                        bgcolor: "#f5f5f5"
                                      },
                                      position: "relative",
                                      zIndex: 1,
                                      display: "flex",
                                      flexDirection: "column"
                                    }}
                                  >
                                    <Box
                                      sx={{
                                        width: "100%",
                                        display: "flex",
                                        alignItems: "center",
                                        cursor: "pointer",
                                        mb: 0.5
                                      }}
                                      onClick={(e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        handleModeChange(item.id, "dmc");
                                      }}
                                    >
                                      <Radio
                                        checked={currentMode === "dmc"}
                                        onChange={(e) => {
                                          e.preventDefault();
                                          e.stopPropagation();
                                          handleModeChange(item.id, "dmc");
                                        }}
                                        value="dmc"
                                        sx={{ p: 0, mr: 1 }}
                                      />
                                      <Box sx={{ 
                                        display: 'flex', 
                                        alignItems: 'center',
                                        fontSize: '0.7rem',
                                        maxWidth: '120px',
                                        overflow: 'hidden'
                                      }}>
                                        {dmcLogo && (
                                          <Avatar
                                            src={dmcLogo}
                                            alt={`${dmcCompanyName} Logo`}
                                            sx={{ 
                                              width: 14,
                                              height: 14,
                                              marginRight: '4px',
                                              flexShrink: 0
                                            }}
                                          />
                                        )}
                                        <Typography 
                                          sx={{
                                            whiteSpace: 'nowrap',
                                            overflow: 'hidden',
                                            textOverflow: 'ellipsis',
                                            fontSize: '0.7rem'
                                          }}
                                        >
                                          {`${dmcCompanyName}'s Mode`}
                                        </Typography>
                                      </Box>
                                    </Box>
                                    
                                    {/* Compact price display with PriceHide check */}
                                    {PriceHide === "0" && (
                                      <Box sx={{ display: 'flex', flexDirection: 'column', mt: 0 }}>
                                        {["breakfast", "lunch", "dinner"].map((mealType) => {
                                          const price = getPrice(item, "dmc", mealType);
                                          if (price <= 0) return null;
                                          
                                          // Calculate converted prices
                                          const convertedPrice = Math.ceil(Number(price) * exchangeRate);
                                          const usdPrice = Math.ceil(Number(price) * usdExchangeRate);
                                          const sgdPrice = Number(price);
                                          
                                          return (
                                            <Box
                                              key={mealType}
                                              sx={{
                                                mb: 0.5,
                                                pb: 0.5,
                                                borderBottom: mealType !== "dinner" ? "1px dashed rgba(0,0,0,0.1)" : "none"
                                              }}
                                              onClick={(e) => {
                                                e.preventDefault();
                                                e.stopPropagation();
                                                handleModeChange(item.id, "dmc");
                                              }}
                                            >
                                              <Typography 
                                                variant="caption" 
                                                sx={{ 
                                                  fontWeight: 'bold', 
                                                  fontSize: '0.65rem', 
                                                  display: 'block',
                                                  lineHeight: 1.1
                                                }}
                                              >
                                                {mealType.charAt(0).toUpperCase() + mealType.slice(1)}:
                                              </Typography>
                                              <Typography 
                                                variant="body2" 
                                                sx={{ 
                                                  fontWeight: "700", 
                                                  fontSize: '0.7rem',
                                                  lineHeight: 1.1
                                                }}
                                              >
                                                {currencyCode} {convertedPrice}
                                              </Typography>
                                              <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                                                {currencyCode !== "USD" && (
                                                  <Typography 
                                                    variant="body2" 
                                                    sx={{ 
                                                      fontSize: '0.6rem',
                                                      lineHeight: 1.1
                                                    }}
                                                  >
                                                    {usdCurrencyCode} {usdPrice}
                                                  </Typography>
                                                )}
                                                {currencyCode !== "SGD" && (
                                                  <Typography 
                                                    variant="body2" 
                                                    sx={{ 
                                                      fontSize: '0.6rem',
                                                      lineHeight: 1.1
                                                    }}
                                                  >
                                                    SGD {sgdPrice}
                                                  </Typography>
                                                )}
                                              </Box>
                                            </Box>
                                          );
                                        })}
                                      </Box>
                                    )}
                                  </Box>
                                )
                              ) : (
                                // Show modes based on bookingType and valid prices
                                <>
                                  {hasValidDmcPrice && (
                                    <Box
                                      key="dmc"
                                      onClick={(e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        handleModeChange(item.id, "dmc");
                                      }}
                                      sx={{
                                        textAlign: "left",
                                        border: "2px solid #ccc",
                                        borderRadius: "12px",
                                        p: 1,
                                        m: 1,
                                        width: "160px",
                                        height: "165px", 
                                        bgcolor: currentMode === "dmc" ? "#e6f2ff" : "#fff",
                                        transform: currentMode === "dmc" ? "scale(1.05)" : "scale(1)",
                                        cursor: "pointer",
                                        "&:hover": {
                                          bgcolor: "#f5f5f5"
                                        },
                                        position: "relative",
                                        zIndex: 1,
                                        display: "flex",
                                        flexDirection: "column"
                                      }}
                                    >
                                      <Box
                                        sx={{
                                          width: "100%",
                                          display: "flex",
                                          alignItems: "center",
                                          cursor: "pointer",
                                          mb: 0.5
                                        }}
                                        onClick={(e) => {
                                          e.preventDefault();
                                          e.stopPropagation();
                                          handleModeChange(item.id, "dmc");
                                        }}
                                      >
                                        <Radio
                                          checked={currentMode === "dmc"}
                                          onChange={(e) => {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            handleModeChange(item.id, "dmc");
                                          }}
                                          value="dmc"
                                          sx={{ p: 0, mr: 1 }}
                                        />
                                        <Box sx={{ 
                                          display: 'flex', 
                                          alignItems: 'center',
                                          fontSize: '0.7rem',
                                          maxWidth: '120px',
                                          overflow: 'hidden'
                                        }}>
                                          {dmcLogo && (
                                            <Avatar
                                              src={dmcLogo}
                                              alt={`${dmcCompanyName} Logo`}
                                              sx={{ 
                                                width: 14,
                                                height: 14,
                                                marginRight: '4px',
                                                flexShrink: 0
                                              }}
                                            />
                                          )}
                                          <Typography 
                                            sx={{
                                              whiteSpace: 'nowrap',
                                              overflow: 'hidden',
                                              textOverflow: 'ellipsis',
                                              fontSize: '0.7rem'
                                            }}
                                          >
                                            {`${dmcCompanyName}'s Mode`}
                                          </Typography>
                                        </Box>
                                      </Box>
                                      
                                      {/* Compact price display with PriceHide check */}
                                      {PriceHide === "0" && (
                                        <Box sx={{ display: 'flex', flexDirection: 'column', mt: 0 }}>
                                          {["breakfast", "lunch", "dinner"].map((mealType) => {
                                            const price = getPrice(item, "dmc", mealType);
                                            if (price <= 0) return null;
                                            
                                            // Calculate converted prices
                                            const convertedPrice = Math.ceil(Number(price) * exchangeRate);
                                            const usdPrice = Math.ceil(Number(price) * usdExchangeRate);
                                            const sgdPrice = Number(price);
                                            
                                            return (
                                              <Box
                                                key={mealType}
                                                sx={{
                                                  mb: 0.5,
                                                  pb: 0.5,
                                                  borderBottom: mealType !== "dinner" ? "1px dashed rgba(0,0,0,0.1)" : "none"
                                                }}
                                                onClick={(e) => {
                                                  e.preventDefault();
                                                  e.stopPropagation();
                                                  handleModeChange(item.id, "dmc");
                                                }}
                                              >
                                                <Typography 
                                                  variant="caption" 
                                                  sx={{ 
                                                    fontWeight: 'bold', 
                                                    fontSize: '0.65rem', 
                                                    display: 'block',
                                                    lineHeight: 1.1
                                                  }}
                                                >
                                                  {mealType.charAt(0).toUpperCase() + mealType.slice(1)}:
                                                </Typography>
                                                <Typography 
                                                  variant="body2" 
                                                  sx={{ 
                                                    fontWeight: "700", 
                                                    fontSize: '0.7rem',
                                                    lineHeight: 1.1
                                                  }}
                                                >
                                                  {currencyCode} {convertedPrice}
                                                </Typography>
                                                <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                                                  {currencyCode !== "USD" && (
                                                    <Typography 
                                                      variant="body2" 
                                                      sx={{ 
                                                        fontSize: '0.6rem',
                                                        lineHeight: 1.1
                                                      }}
                                                    >
                                                      {usdCurrencyCode} {usdPrice}
                                                    </Typography>
                                                  )}
                                                  {currencyCode !== "SGD" && (
                                                    <Typography 
                                                      variant="body2" 
                                                      sx={{ 
                                                        fontSize: '0.6rem',
                                                        lineHeight: 1.1
                                                      }}
                                                    >
                                                      SGD {sgdPrice}
                                                    </Typography>
                                                  )}
                                                </Box>
                                              </Box>
                                            );
                                          })}
                                        </Box>
                                      )}
                                    </Box>
                                  )}
                                  {/* Only show TravClicks if bookingType is not 'enquiry' */}
                                  {hasValidTravClicksPrice && !showDmcOnly && bookingType !== 'enquiry' && (
                                    <Box
                                      key="travclicks"
                                      onClick={(e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        handleModeChange(item.id, "travclicks");
                                      }}
                                      sx={{
                                        textAlign: "left",
                                        border: "2px solid #ccc",
                                        borderRadius: "12px",
                                        p: 1,
                                        m: 1,
                                        width: "160px",
                                        minHeight: "165px", // Reset to original height
                                        bgcolor: currentMode === "travclicks" ? "#e6f2ff" : "#fff",
                                        transform: currentMode === "travclicks" ? "scale(1.05)" : "scale(1)",
                                        cursor: "pointer",
                                        "&:hover": {
                                          bgcolor: "#f5f5f5"
                                        },
                                        position: "relative",
                                        zIndex: 1,
                                        display: "flex",
                                        flexDirection: "column"
                                      }}
                                    >
                                      <Box
                                        sx={{
                                          width: "100%",
                                          display: "flex",
                                          alignItems: "center",
                                          cursor: "pointer",
                                          mb: 0.5
                                        }}
                                        onClick={(e) => {
                                          e.preventDefault();
                                          e.stopPropagation();
                                          handleModeChange(item.id, "travclicks");
                                        }}
                                      >
                                        <Radio
                                          checked={currentMode === "travclicks"}
                                          onChange={(e) => {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            handleModeChange(item.id, "travclicks");
                                          }}
                                          value="travclicks"
                                          sx={{ p: 0, mr: 1 }}
                                        />
                                        <Typography sx={{ fontSize: '0.7rem' }}>Travclicks</Typography>
                                      </Box>
                                      
                                      {/* Compact price display with PriceHide check */}
                                      {PriceHide === "0" && (
                                        <Box sx={{ display: 'flex', flexDirection: 'column', mt: 0 }}>
                                          {["breakfast", "lunch", "dinner"].map((mealType) => {
                                            const price = getPrice(item, "travclicks", mealType);
                                            if (price <= 0) return null;
                                            
                                            // Calculate converted prices
                                            const convertedPrice = Math.ceil(Number(price) * exchangeRate);
                                            const usdPrice = Math.ceil(Number(price) * usdExchangeRate);
                                            const sgdPrice = Number(price);
                                            
                                            return (
                                              <Box
                                                key={mealType}
                                                sx={{
                                                  mb: 0.5,
                                                  pb: 0.5,
                                                  borderBottom: mealType !== "dinner" ? "1px dashed rgba(0,0,0,0.1)" : "none"
                                                }}
                                                onClick={(e) => {
                                                  e.preventDefault();
                                                  e.stopPropagation();
                                                  handleModeChange(item.id, "travclicks");
                                                }}
                                              >
                                                <Typography 
                                                  variant="caption" 
                                                  sx={{ 
                                                    fontWeight: 'bold', 
                                                    fontSize: '0.65rem', 
                                                    display: 'block',
                                                    lineHeight: 1.1
                                                  }}
                                                >
                                                  {mealType.charAt(0).toUpperCase() + mealType.slice(1)}:
                                                </Typography>
                                                <Typography 
                                                  variant="body2" 
                                                  sx={{ 
                                                    fontWeight: "700", 
                                                    fontSize: '0.7rem',
                                                    lineHeight: 1.1
                                                  }}
                                                >
                                                  {currencyCode} {convertedPrice}
                                                </Typography>
                                                <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                                                  {currencyCode !== "USD" && (
                                                    <Typography 
                                                      variant="body2" 
                                                      sx={{ 
                                                        fontSize: '0.6rem',
                                                        lineHeight: 1.1
                                                      }}
                                                    >
                                                      {usdCurrencyCode} {usdPrice}
                                                    </Typography>
                                                  )}
                                                  {currencyCode !== "SGD" && (
                                                    <Typography 
                                                      variant="body2" 
                                                      sx={{ 
                                                        fontSize: '0.6rem',
                                                        lineHeight: 1.1
                                                      }}
                                                    >
                                                      SGD {sgdPrice}
                                                    </Typography>
                                                  )}
                                                </Box>
                                              </Box>
                                            );
                                          })}
                                        </Box>
                                      )}
                                    </Box>
                                  )}
                                </>
                              )}
                            </RadioGroup>
                          </FormControl>
                        </Box>

                        {/* Add warning message here, only when Travclicks mode is selected */}
                        {currentMode === "travclicks" && hasValidTravClicksPrice && !showDmcOnly && bookingType !== 'enquiry' && (
                          <Typography variant="caption" sx={{ color: 'red', fontSize: '0.75rem', mt: 1, mb: 1, display: 'block' }}>
                            This marketplace product isn't eligible for DMC enquiry.
                          </Typography>
                        )}

                        <Button
                          variant="contained"
                          onClick={(e) => handleAttractionClick(e, item)}
                          sx={{ mt: 2 }}
                        >
                          View Detail
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            );
          })}
        </>
      ) : (
        // Show a proper "no results" message after loading is complete
        <div
          className="no-hotels-message"
          style={{ textAlign: "center", marginTop: "2rem" }}
        >
          <div className="MuiBox-root css-t4lhjn">
            <div className="MuiBox-root css-d0j5jx">
              <img
                src="/icons/restaurant.png"
                alt="Restaurant Icon"
                className="your-icon-class"
                style={{ width: "200px", height: "200px" }}
              />
            </div>
            <h5 className="MuiTypography-root MuiTypography-h5 css-hu3rhi-MuiTypography-root">
              {bookingType === 'enquiry' 
                ? "No restaurants available for enquiry. Please try a different selection."
                : filters.searchParams?.location 
                  ? `No restaurants found in ${filters.searchParams.location.address}. Please try a different location.`
                  : "No restaurants found. Please try a different search."}
            </h5>
          </div>
        </div>
      )}
    </>
  );
};

export default TourProperties;
