import React, { useEffect, useState, useRef } from "react";
import { Swiper } from "swiper/react";
import { Navigation, Pagination } from "swiper";
import { useSelector, useDispatch } from "react-redux";
import { useNavigate, useLocation, useSearchParams } from "react-router-dom";
import {
  fetchAttractions,
  selectFilteredAttractions,
  fetchAttractionDetails,
  setSelectedModeData,
  selectFilters,
} from "../../../slice/attractions/attractionSlice";
import TopHeaderFilter from "@/components/tour-list/tour-list-v1/TopHeaderFilter";
import {
  Box,
  Button,
  FormControl,
  FormControlLabel,
  Radio,
  RadioGroup,
  Typography,
  Avatar,
} from "@mui/material";
import { setBookingMode } from "../../../slice/common/commonSlice";

const getOpeningTimes = (item) => {
  const times = [];
  if (item.morning_opening === 1) times.push("Morning");
  if (item.afternoon_opening === 1) times.push("Afternoon");
  if (item.evening_opening === 1) times.push("Evening");
  if (item.night_opening === 1) times.push("Night");
  return times;
};

// Create a Skeleton component
const AttractionSkeleton = () => (
  <div className="col-12">
    <div className="border-top-light pt-30">
      <div className="row x-gap-20 y-gap-20">
        <div className="col-md-auto">
          <div className="cardImage ratio ratio-1:1 w-250 md:w-1/1 rounded-4">
            <div
              style={{
                background: "#f0f0f0",
                height: "200px",
                animation: "pulse 1.5s infinite",
              }}
            />
          </div>
        </div>
        <div className="col-md">
          <div
            style={{
              background: "#f0f0f0",
              height: "24px",
              width: "70%",
              marginBottom: "10px",
              animation: "pulse 1.5s infinite",
            }}
          />
          <div
            style={{
              background: "#f0f0f0",
              height: "18px",
              width: "40%",
              marginBottom: "10px",
              animation: "pulse 1.5s infinite",
            }}
          />
          <div
            style={{
              background: "#f0f0f0",
              height: "18px",
              width: "60%",
              animation: "pulse 1.5s infinite",
            }}
          />
        </div>
        <div className="col-md-auto">
          <div
            style={{
              background: "#f0f0f0",
              height: "150px",
              width: "160px",
              marginBottom: "10px",
              animation: "pulse 1.5s infinite",
            }}
          />
          <div
            style={{
              background: "#f0f0f0",
              height: "40px",
              width: "160px",
              animation: "pulse 1.5s infinite",
            }}
          />
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
  const dmcLogo = useSelector((state) => state.auth.DmcLogo);
  const dmcName = useSelector((state) => state.auth.DmcName) || "DMC";
  const filters = useSelector(selectFilters);
  const attractions = useSelector(selectFilteredAttractions);
  const bookingType = useSelector((state) => state.common.bookingType);
  const bookingMode = useSelector((state) => state.common.bookingMode);
  const location = useLocation();
  const [searchParams] = useSearchParams();

  // Add status selector to detect when attraction data is loading
  const attractionStatus = useSelector((state) => state.attractions.status);

  const [sortedAttractions, setSortedAttractions] = useState([]);
  const [selectedModes, setSelectedModes] = useState({});
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 5;
  const [isLoading, setIsLoading] = useState(true);
  const [isSearching, setIsSearching] = useState(false);

  // Add these selectors at the top with other useSelector calls
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencySymbol = useSelector(
    (state) => state.auth.usdCurrencySymbol
  );
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  // Add PriceHide selector
  const PriceHide = useSelector((state) => state.auth.PriceHide);

  useEffect(() => {
    dispatch(fetchAttractions());
  }, [dispatch]);

  // Update the useEffect to handle search loading based on Redux state
  useEffect(() => {
    if (attractionStatus === "loading") {
      // Show skeleton loader when attractions are loading
      setIsLoading(true);
      setIsSearching(true);

      // Set a maximum timeout for the loading state (5 seconds)
      const maxLoadingTimer = setTimeout(() => {
        setIsLoading(false);
        setIsSearching(false);
      }, 5000); // Maximum 5 seconds of loading, then show "no results" if needed

      return () => clearTimeout(maxLoadingTimer);
    } else if (
      attractionStatus === "succeeded" ||
      attractionStatus === "failed"
    ) {
      // Hide loader after data is loaded or if the request failed
      const timer = setTimeout(() => {
        setIsLoading(false);
        setIsSearching(false);
      }, 1500); // 1.5 seconds delay

      return () => clearTimeout(timer);
    }
  }, [attractionStatus]);

  // Modify the useEffect for initial loading to only run once
  useEffect(() => {
    // Only run on initial mount, not when isSearching changes
    const timer = setTimeout(() => {
      if (attractionStatus !== "loading") {
        setIsLoading(false);
      }
    }, 2000); // 2 seconds delay for initial load

    return () => clearTimeout(timer);
  }, []); // Empty dependency array means this runs once on mount

  // Add this helper function to check if the item should be displayed
  const shouldDisplayItem = (item) => {
    if (bookingType === "enquiry") {
      // For enquiry, only show items with DMC prices
      return item.dmc_adult_price > 0;
    } else {
      // For normal booking, show items with either DMC or Travclicks prices
      return item.dmc_adult_price > 0 || item.travClicks_adult_price > 0;
    }
  };

  // Modify the useEffect for filtered attractions
  useEffect(() => {
    if (!attractions || attractions.length === 0) return;

    const filtered = attractions.filter((item) => {
      // First check if the item should be displayed based on booking type
      if (!shouldDisplayItem(item)) {
        return false;
      }

      // Rest of your existing filtering logic
      const price = item.dmc_adult_price ?? item.travClicks_adult_price ?? 0;
      const priceMode = filters.priceMode || ["dmc", "marketplace"];

      // Updated logic for price mode filtering
      let hasValidPrice;
      if (priceMode.length === 1 && priceMode[0] === "dmc") {
        // DMC only mode - only show items with DMC prices
        hasValidPrice = item.dmc_adult_price > 0;
      } else {
        // Both modes - show items with either price
        hasValidPrice =
          item.dmc_adult_price > 0 || item.travClicks_adult_price > 0;
      }

      const timeOfDayFilter = filters.timeOfDay || [];
      const matchesTimeOfDay =
        timeOfDayFilter.length === 0 ||
        timeOfDayFilter.some((time) => {
          switch (time) {
            case "morning_opening":
              return item.morning_opening === 1;
            case "afternoon_opening":
              return item.afternoon_opening === 1;
            case "evening_opening":
              return item.evening_opening === 1;
            case "night_opening":
              return item.night_opening === 1;
            default:
              return true;
          }
        });

      return (
        price >= filters.priceRange.min &&
        price <= filters.priceRange.max &&
        (filters.category === "All" || item.category === filters.category) &&
        hasValidPrice &&
        matchesTimeOfDay
      );
    });

    setSortedAttractions(filtered);

    // Set default mode based on priceMode and available prices
    const defaultModes = {};
    filtered.forEach((item) => {
      let mode = "dmc"; // Default fallback
      
      if (bookingType === "enquiry") {
        // For enquiry, always set to DMC mode if available
        if (item.dmc_adult_price > 0) {
          mode = "dmc";
        }
      } else {
        // For normal booking, prioritize DMC if available, otherwise use travclicks
        if (item.dmc_adult_price > 0) {
          mode = "dmc";
        } else if (item.travClicks_adult_price > 0) {
          mode = "travclicks";
        }
      }
      
      // Always set a mode for each item
      defaultModes[item.id] = { mode };
    });
    
    setSelectedModes(defaultModes);
    
    // Set global booking mode based on the first item's mode
    if (filtered.length > 0) {
      const firstItemMode = defaultModes[filtered[0].id]?.mode || "dmc";
      dispatch(setBookingMode(firstItemMode));
    }
  }, [attractions, filters, bookingType, dispatch]); // Added dispatch to dependencies

  const getPrice = (item, mode, type) => {
    if (mode === "travclicks") {
      return type === "adult"
        ? item.travClicks_adult_price ?? 0
        : type === "child"
        ? item.travClicks_child_price ?? 0
        : item.travClicks_senior_price ?? 0;
    }
    return type === "adult"
      ? item.dmc_adult_price ?? 0
      : type === "child"
      ? item.dmc_child_price ?? 0
      : item.dmc_senior_price ?? 0;
  };

  const handleModeChange = (attractionId, mode) => {
    // console.log(`Mode changed for attraction ${attractionId} to ${mode}`);

    setSelectedModes((prev) => ({ ...prev, [attractionId]: { mode } }));

    const item = sortedAttractions.find((item) => item.id === attractionId);
    const adultPrice = getPrice(item, mode, "adult");
    const childPrice = getPrice(item, mode, "child");
    const seniorPrice = getPrice(item, mode, "senior");

    dispatch(
      setSelectedModeData({
        attractionId,
        mode,
        adultPrice,
        childPrice,
        seniorPrice,
      })
    );

    // Ensure bookingMode is updated in Redux
    // console.log("Dispatching bookingMode to Redux:", mode);
    dispatch(setBookingMode(mode));
  };

  const handleAttractionClick = (event, item) => {
    event.preventDefault();
    
    // Get the current mode from selectedModes with proper fallback logic
    let currentMode = selectedModes[item.id]?.mode;
    
    // If no mode is set, determine based on available prices
    if (!currentMode) {
      if (bookingType === "enquiry") {
        // For enquiry, always use DMC if available
        currentMode = item.dmc_adult_price > 0 ? "dmc" : "dmc";
      } else {
        // For normal booking, prioritize DMC, fallback to travclicks
        currentMode = item.dmc_adult_price > 0 ? "dmc" : "travclicks";
      }
    }
    
    // Ensure the booking mode is updated in Redux
    dispatch(setBookingMode(currentMode));

    // The slice will automatically use the selected DMC ID from Redux
    console.log('Selected mode for attraction:', currentMode);

    dispatch(
      fetchAttractionDetails({
        attractionId: item.id,
        price_mode: currentMode
        // dmc_id will be automatically handled by the slice using Redux state
      })
    );

    navigate(`/dashboard/db-dashboard/tour-single/${item.id}`, {
      state: {
        attraction: item,
        mode: currentMode,
        // dmc_id will be handled by the slice using Redux state
      },
    });
  };

  const handleSort = (sortOrder) => {
    const sorted = [...sortedAttractions].sort((a, b) => {
      const priceA = a.dmc_adult_price ?? a.travClicks_adult_price ?? 0;
      const priceB = b.dmc_adult_price ?? b.travClicks_adult_price ?? 0;
      return sortOrder === "asc" ? priceA - priceB : priceB - priceA;
    });
    setSortedAttractions(sorted);
  };

  useEffect(() => {
    const isTravclicks =
      searchParams.get("source") === "travclicks" ||
      location.pathname.includes("travclicks");

    // console.log('Current location:', location);
    // console.log('Search params:', searchParams.toString());
    // console.log('Is Travclicks?', isTravclicks);
    // console.log('Current bookingMode:', bookingMode);

    if (isTravclicks) {
      dispatch(setBookingMode("travclicks"));
    }
  }, [dispatch, location, searchParams]);

  // Add a useEffect to ensure bookingMode is consistently set to the selected mode
  useEffect(() => {
    // Check if any attraction has been selected
    if (sortedAttractions.length > 0 && Object.keys(selectedModes).length > 0) {
      // Get the first attraction's ID and mode
      const firstAttractionId = sortedAttractions[0].id;
      const currentMode = selectedModes[firstAttractionId]?.mode;

      if (currentMode) {
        // Explicitly dispatch the mode to Redux
        // console.log("Setting booking mode to:", currentMode);
        dispatch(setBookingMode(currentMode));
      }
    }
  }, [sortedAttractions, selectedModes, dispatch]);

  if (!attractions || attractions.length === 0) {
    // Show the skeleton loader instead of the message when searching/loading
    if (isLoading || attractionStatus === "loading") {
      return (
        <>
          <style>{skeletonStyles}</style>
          <TopHeaderFilter onSort={handleSort} />
          <br />
          {/* Show skeleton loading */}
          {Array(5)
            .fill(null)
            .map((_, index) => (
              <AttractionSkeleton key={index} />
            ))}
        </>
      );
    }

    // Only show the message when not in loading state
    return (
      <div
        className="no-hotels-message"
        style={{ textAlign: "center", marginTop: "2rem" }}
      >
        <div className="MuiBox-root css-t4lhjn">
          <div className="MuiBox-root css-d0j5jx">
            <img
              src="/icons/attractions.jpg"
              alt="Travel Icon"
              className="your-icon-class"
              style={{ width: "200px", height: "200px" }}
            />
          </div>
          <h5 className="MuiTypography-root MuiTypography-h5 css-hu3rhi-MuiTypography-root">
            {bookingType === "enquiry"
              ? "No attractions available for enquiry. Please try a different selection."
              : filters.searchParams?.location
              ? `No attractions found in ${filters.searchParams.location.address}. Please try a different location.`
              : "Please provide attractions location and date of journey and search..."}
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
        // Show skeleton loading for initial load
        Array(5)
          .fill(null)
          .map((_, index) => <AttractionSkeleton key={index} />)
      ) : sortedAttractions.length > 0 ? (
        <>
          {sortedAttractions
            .filter(shouldDisplayItem) // Apply filter here
            .map((item) => {
              const currentMode = selectedModes[item.id]?.mode || "dmc";

              return (
                <div className="col-12" key={item.id}>
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
                                alt={item?.attraction_name}
                                style={{ height: "200px", objectFit: "cover" }}
                              />
                            </Swiper>
                          </div>
                        </div>
                      </div>

                      <div className="col-md">
                        <h3 className="text-18 lh-16 fw-500">
                          {item.attraction_name}
                        </h3>
                        <p className="text-14 lh-14 mt-5">
                          {item.city}, {item.country}
                        </p>
                        <p className="text-14 lh-14 mt-5 text-light-1">
                          {getOpeningTimes(item).join(", ")}
                        </p>
                      </div>

                      <div className="col-md-auto text-right md:text-left">
                        <Box sx={{ mt: 1, fontSize: "14px" }}>
                          <FormControl component="fieldset">
                            <RadioGroup
                              row
                              value={currentMode}
                              onChange={(e) =>
                                handleModeChange(item.id, e.target.value)
                              }
                            >
                              {bookingType === "enquiry" ? (
                                item.dmc_adult_price > 0 && (
                                  <Box
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
                                      width: "180px",
                                      minHeight: "180px",
                                      height: "auto",
                                      bgcolor:
                                        currentMode === "dmc"
                                          ? "#e6f2ff"
                                          : "#fff",
                                      transform:
                                        currentMode === "dmc"
                                          ? "scale(1.05)"
                                          : "scale(1)",
                                      cursor: "pointer",
                                      "&:hover": {
                                        bgcolor: "#f5f5f5",
                                      },
                                      position: "relative",
                                      zIndex: 1,
                                      display: "flex",
                                      flexDirection: "column",
                                      alignItems: "flex-start",
                                    }}
                                  >
                                    <Box
                                      sx={{
                                        width: "100%",
                                        display: "flex",
                                        alignItems: "center",
                                        cursor: "pointer",
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
                                      <Box
                                        sx={{
                                          display: "flex",
                                          alignItems: "center",
                                          fontSize: "0.8rem",
                                          maxWidth: "120px",
                                          overflow: "hidden",
                                        }}
                                      >
                                        {dmcLogo && (
                                          <Avatar
                                            src={dmcLogo}
                                            alt={`${dmcName} Logo`}
                                            sx={{
                                              width: 16,
                                              height: 16,
                                              marginRight: "4px",
                                              flexShrink: 0,
                                            }}
                                          />
                                        )}
                                        <Typography
                                          sx={{
                                            whiteSpace: "nowrap",
                                            overflow: "hidden",
                                            textOverflow: "ellipsis",
                                          }}
                                        >
                                          {`${dmcName}'s Mode`}
                                        </Typography>
                                      </Box>
                                    </Box>

                                    {PriceHide === "0" && (
                                      <Box
                                        sx={{
                                          display: "flex",
                                          justifyContent: "space-between",
                                          mt: 2,
                                          width: "100%",
                                        }}
                                      >
                                        <Box
                                          sx={{ flex: 1, textAlign: "center" }}
                                        >
                                          <Typography
                                            variant="caption"
                                            sx={{
                                              fontWeight: "bold",
                                              fontSize: "12px",
                                            }}
                                          >
                                            Adult
                                          </Typography>
                                          <Typography
                                            variant="body2"
                                            display="block"
                                            sx={{
                                              fontSize: "14px",
                                              fontWeight: "bold",
                                              color: "#3554d1",
                                            }}
                                          >
                                            {currencyCode}{" "}
                                            {Math.ceil(
                                              item.dmc_adult_price *
                                                exchangeRate
                                            )}
                                          </Typography>
                                          {currencyCode !== "USD" && (
                                            <Typography
                                              variant="caption"
                                              display="block"
                                              sx={{ fontSize: "12px" }}
                                            >
                                              USD{" "}
                                              {Math.ceil(
                                                item.dmc_adult_price *
                                                  usdExchangeRate
                                              )}
                                            </Typography>
                                          )}
                                          {currencyCode !== "SGD" && (
                                            <Typography
                                              variant="caption"
                                              display="block"
                                              sx={{ fontSize: "12px" }}
                                            >
                                              SGD{" "}
                                              {Math.ceil(item.dmc_adult_price)}
                                            </Typography>
                                          )}
                                        </Box>

                                        <Box
                                          sx={{ flex: 1, textAlign: "center" }}
                                        >
                                          <Typography
                                            variant="caption"
                                            sx={{
                                              fontWeight: "bold",
                                              fontSize: "12px",
                                            }}
                                          >
                                            Child
                                          </Typography>
                                          <Typography
                                            variant="body2"
                                            display="block"
                                            sx={{
                                              fontSize: "14px",
                                              fontWeight: "bold",
                                              color: "#3554d1",
                                            }}
                                          >
                                            {currencyCode}{" "}
                                            {Math.ceil(
                                              item.dmc_child_price *
                                                exchangeRate
                                            )}
                                          </Typography>
                                          {currencyCode !== "USD" && (
                                            <Typography
                                              variant="caption"
                                              display="block"
                                              sx={{ fontSize: "12px" }}
                                            >
                                              USD{" "}
                                              {Math.ceil(
                                                item.dmc_child_price *
                                                  usdExchangeRate
                                              )}
                                            </Typography>
                                          )}
                                          {currencyCode !== "SGD" && (
                                            <Typography
                                              variant="caption"
                                              display="block"
                                              sx={{ fontSize: "12px" }}
                                            >
                                              SGD{" "}
                                              {Math.ceil(item.dmc_child_price)}
                                            </Typography>
                                          )}
                                        </Box>

                                        <Box
                                          sx={{ flex: 1, textAlign: "center" }}
                                        >
                                          <Typography
                                            variant="caption"
                                            sx={{
                                              fontWeight: "bold",
                                              fontSize: "12px",
                                            }}
                                          >
                                            Senior
                                          </Typography>
                                          <Typography
                                            variant="body2"
                                            display="block"
                                            sx={{
                                              fontSize: "14px",
                                              fontWeight: "bold",
                                              color: "#3554d1",
                                            }}
                                          >
                                            {currencyCode}{" "}
                                            {Math.ceil(
                                              item.dmc_senior_price *
                                                exchangeRate
                                            )}
                                          </Typography>
                                          {currencyCode !== "USD" && (
                                            <Typography
                                              variant="caption"
                                              display="block"
                                              sx={{ fontSize: "12px" }}
                                            >
                                              USD{" "}
                                              {Math.ceil(
                                                item.dmc_senior_price *
                                                  usdExchangeRate
                                              )}
                                            </Typography>
                                          )}
                                          {currencyCode !== "SGD" && (
                                            <Typography
                                              variant="caption"
                                              display="block"
                                              sx={{ fontSize: "12px" }}
                                            >
                                              SGD{" "}
                                              {Math.ceil(item.dmc_senior_price)}
                                            </Typography>
                                          )}
                                        </Box>
                                      </Box>
                                    )}
                                  </Box>
                                )
                              ) : (
                                <>
                                  {item.dmc_adult_price > 0 && (
                                    <Box
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
                                        width: "180px",
                                        minHeight: "180px",
                                        height: "auto",
                                        bgcolor:
                                          currentMode === "dmc"
                                            ? "#e6f2ff"
                                            : "#fff",
                                        transform:
                                          currentMode === "dmc"
                                            ? "scale(1.05)"
                                            : "scale(1)",
                                        cursor: "pointer",
                                        "&:hover": {
                                          bgcolor: "#f5f5f5",
                                        },
                                        position: "relative",
                                        zIndex: 1,
                                        display: "flex",
                                        flexDirection: "column",
                                        alignItems: "flex-start",
                                      }}
                                    >
                                      <Box
                                        sx={{
                                          width: "100%",
                                          display: "flex",
                                          alignItems: "center",
                                          cursor: "pointer",
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
                                        <Box
                                          sx={{
                                            display: "flex",
                                            alignItems: "center",
                                            fontSize: "0.8rem",
                                            maxWidth: "120px",
                                            overflow: "hidden",
                                          }}
                                        >
                                          {dmcLogo && (
                                            <Avatar
                                              src={dmcLogo}
                                              alt={`${dmcName} Logo`}
                                              sx={{
                                                width: 16,
                                                height: 16,
                                                marginRight: "4px",
                                                flexShrink: 0,
                                              }}
                                            />
                                          )}
                                          <Typography
                                            sx={{
                                              whiteSpace: "nowrap",
                                              overflow: "hidden",
                                              textOverflow: "ellipsis",
                                            }}
                                          >
                                            {`${dmcName}'s Mode`}
                                          </Typography>
                                        </Box>
                                      </Box>

                                      {PriceHide === "0" && (
                                        <Box
                                          sx={{
                                            display: "flex",
                                            justifyContent: "space-between",
                                            mt: 2,
                                            width: "100%",
                                          }}
                                        >
                                          <Box
                                            sx={{
                                              flex: 1,
                                              textAlign: "center",
                                            }}
                                          >
                                            <Typography
                                              variant="caption"
                                              sx={{
                                                fontWeight: "bold",
                                                fontSize: "12px",
                                              }}
                                            >
                                              Adult
                                            </Typography>
                                            <Typography
                                              variant="body2"
                                              display="block"
                                              sx={{
                                                fontSize: "14px",
                                                fontWeight: "bold",
                                                color: "#3554d1",
                                              }}
                                            >
                                              {currencyCode}{" "}
                                              {Math.ceil(
                                                item.dmc_adult_price *
                                                  exchangeRate
                                              )}
                                            </Typography>
                                            {currencyCode !== "USD" && (
                                              <Typography
                                                variant="caption"
                                                display="block"
                                                sx={{ fontSize: "12px" }}
                                              >
                                                USD{" "}
                                                {Math.ceil(
                                                  item.dmc_adult_price *
                                                    usdExchangeRate
                                                )}
                                              </Typography>
                                            )}
                                            {currencyCode !== "SGD" && (
                                              <Typography
                                                variant="caption"
                                                display="block"
                                                sx={{ fontSize: "12px" }}
                                              >
                                                SGD{" "}
                                                {Math.ceil(
                                                  item.dmc_adult_price
                                                )}
                                              </Typography>
                                            )}
                                          </Box>

                                          <Box
                                            sx={{
                                              flex: 1,
                                              textAlign: "center",
                                            }}
                                          >
                                            <Typography
                                              variant="caption"
                                              sx={{
                                                fontWeight: "bold",
                                                fontSize: "12px",
                                              }}
                                            >
                                              Child
                                            </Typography>
                                            <Typography
                                              variant="body2"
                                              display="block"
                                              sx={{
                                                fontSize: "14px",
                                                fontWeight: "bold",
                                                color: "#3554d1",
                                              }}
                                            >
                                              {currencyCode}{" "}
                                              {Math.ceil(
                                                item.dmc_child_price *
                                                  exchangeRate
                                              )}
                                            </Typography>
                                            {currencyCode !== "USD" && (
                                              <Typography
                                                variant="caption"
                                                display="block"
                                                sx={{ fontSize: "12px" }}
                                              >
                                                USD{" "}
                                                {Math.ceil(
                                                  item.dmc_child_price *
                                                    usdExchangeRate
                                                )}
                                              </Typography>
                                            )}
                                            {currencyCode !== "SGD" && (
                                              <Typography
                                                variant="caption"
                                                display="block"
                                                sx={{ fontSize: "12px" }}
                                              >
                                                SGD{" "}
                                                {Math.ceil(
                                                  item.dmc_child_price
                                                )}
                                              </Typography>
                                            )}
                                          </Box>

                                          <Box
                                            sx={{
                                              flex: 1,
                                              textAlign: "center",
                                            }}
                                          >
                                            <Typography
                                              variant="caption"
                                              sx={{
                                                fontWeight: "bold",
                                                fontSize: "12px",
                                              }}
                                            >
                                              Senior
                                            </Typography>
                                            <Typography
                                              variant="body2"
                                              display="block"
                                              sx={{
                                                fontSize: "14px",
                                                fontWeight: "bold",
                                                color: "#3554d1",
                                              }}
                                            >
                                              {currencyCode}{" "}
                                              {Math.ceil(
                                                item.dmc_senior_price *
                                                  exchangeRate
                                              )}
                                            </Typography>
                                            {currencyCode !== "USD" && (
                                              <Typography
                                                variant="caption"
                                                display="block"
                                                sx={{ fontSize: "12px" }}
                                              >
                                                USD{" "}
                                                {Math.ceil(
                                                  item.dmc_senior_price *
                                                    usdExchangeRate
                                                )}
                                              </Typography>
                                            )}
                                            {currencyCode !== "SGD" && (
                                              <Typography
                                                variant="caption"
                                                display="block"
                                                sx={{ fontSize: "12px" }}
                                              >
                                                SGD{" "}
                                                {Math.ceil(
                                                  item.dmc_senior_price
                                                )}
                                              </Typography>
                                            )}
                                          </Box>
                                        </Box>
                                      )}
                                    </Box>
                                  )}

                                  {/* Only show Travclicks if not in DMC-only mode */}
                                  {item.travClicks_adult_price > 0 &&
                                    !(
                                      filters.priceMode?.length === 1 &&
                                      filters.priceMode[0] === "dmc"
                                    ) &&
                                    bookingType !== "enquiry" && (
                                      <Box
                                        key="travclicks"
                                        onClick={(e) => {
                                          e.preventDefault();
                                          e.stopPropagation();
                                          handleModeChange(
                                            item.id,
                                            "travclicks"
                                          );
                                        }}
                                        sx={{
                                          textAlign: "left",
                                          border: "2px solid #ccc",
                                          borderRadius: "12px",
                                          p: 1,
                                          m: 1,
                                          width: "180px",
                                          minHeight: "180px",
                                          height: "auto",
                                          bgcolor:
                                            currentMode === "travclicks"
                                              ? "#e6f2ff"
                                              : "#fff",
                                          transform:
                                            currentMode === "travclicks"
                                              ? "scale(1.05)"
                                              : "scale(1)",
                                          cursor: "pointer",
                                          "&:hover": {
                                            bgcolor: "#f5f5f5",
                                          },
                                          position: "relative",
                                          zIndex: 1,
                                          display: "flex",
                                          flexDirection: "column",
                                          alignItems: "flex-start",
                                        }}
                                      >
                                        <Box
                                          sx={{
                                            width: "100%",
                                            display: "flex",
                                            alignItems: "center",
                                            cursor: "pointer",
                                          }}
                                          onClick={(e) => {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            handleModeChange(
                                              item.id,
                                              "travclicks"
                                            );
                                          }}
                                        >
                                          <Radio
                                            checked={
                                              currentMode === "travclicks"
                                            }
                                            onChange={(e) => {
                                              e.preventDefault();
                                              e.stopPropagation();
                                              handleModeChange(
                                                item.id,
                                                "travclicks"
                                              );
                                            }}
                                            value="travclicks"
                                            sx={{ p: 0, mr: 1 }}
                                          />
                                          <Typography>Travclicks</Typography>
                                        </Box>
                                        {PriceHide === "0" && (
                                          <Box
                                            sx={{
                                              display: "flex",
                                              justifyContent: "space-between",
                                              mt: 2,
                                              width: "100%",
                                            }}
                                          >
                                            <Box
                                              sx={{
                                                flex: 1,
                                                textAlign: "center",
                                              }}
                                            >
                                              <Typography
                                                variant="caption"
                                                sx={{
                                                  fontWeight: "bold",
                                                  fontSize: "12px",
                                                }}
                                              >
                                                Adult
                                              </Typography>
                                              <Typography
                                                variant="body2"
                                                display="block"
                                                sx={{
                                                  fontSize: "14px",
                                                  fontWeight: "bold",
                                                  color: "#3554d1",
                                                }}
                                              >
                                                {currencyCode}{" "}
                                                {Math.ceil(
                                                  item.travClicks_adult_price *
                                                    exchangeRate
                                                )}
                                              </Typography>
                                              {currencyCode !== "USD" && (
                                                <Typography
                                                  variant="caption"
                                                  display="block"
                                                  sx={{ fontSize: "12px" }}
                                                >
                                                  USD{" "}
                                                  {Math.ceil(
                                                    item.travClicks_adult_price *
                                                      usdExchangeRate
                                                  )}
                                                </Typography>
                                              )}
                                              {currencyCode !== "SGD" && (
                                                <Typography
                                                  variant="caption"
                                                  display="block"
                                                  sx={{ fontSize: "12px" }}
                                                >
                                                  SGD{" "}
                                                  {Math.ceil(
                                                    item.travClicks_adult_price
                                                  )}
                                                </Typography>
                                              )}
                                            </Box>

                                            <Box
                                              sx={{
                                                flex: 1,
                                                textAlign: "center",
                                              }}
                                            >
                                              <Typography
                                                variant="caption"
                                                sx={{
                                                  fontWeight: "bold",
                                                  fontSize: "12px",
                                                }}
                                              >
                                                Child
                                              </Typography>
                                              <Typography
                                                variant="body2"
                                                display="block"
                                                sx={{
                                                  fontSize: "14px",
                                                  fontWeight: "bold",
                                                  color: "#3554d1",
                                                }}
                                              >
                                                {currencyCode}{" "}
                                                {Math.ceil(
                                                  item.travClicks_child_price *
                                                    exchangeRate
                                                )}
                                              </Typography>
                                              {currencyCode !== "USD" && (
                                                <Typography
                                                  variant="caption"
                                                  display="block"
                                                  sx={{ fontSize: "12px" }}
                                                >
                                                  USD{" "}
                                                  {Math.ceil(
                                                    item.travClicks_child_price *
                                                      usdExchangeRate
                                                  )}
                                                </Typography>
                                              )}
                                              {currencyCode !== "SGD" && (
                                                <Typography
                                                  variant="caption"
                                                  display="block"
                                                  sx={{ fontSize: "12px" }}
                                                >
                                                  SGD{" "}
                                                  {Math.ceil(
                                                    item.travClicks_child_price
                                                  )}
                                                </Typography>
                                              )}
                                            </Box>

                                            <Box
                                              sx={{
                                                flex: 1,
                                                textAlign: "center",
                                              }}
                                            >
                                              <Typography
                                                variant="caption"
                                                sx={{
                                                  fontWeight: "bold",
                                                  fontSize: "12px",
                                                }}
                                              >
                                                Senior
                                              </Typography>
                                              <Typography
                                                variant="body2"
                                                display="block"
                                                sx={{
                                                  fontSize: "14px",
                                                  fontWeight: "bold",
                                                  color: "#3554d1",
                                                }}
                                              >
                                                {currencyCode}{" "}
                                                {Math.ceil(
                                                  item.travClicks_senior_price *
                                                    exchangeRate
                                                )}
                                              </Typography>
                                              {currencyCode !== "USD" && (
                                                <Typography
                                                  variant="caption"
                                                  display="block"
                                                  sx={{ fontSize: "12px" }}
                                                >
                                                  USD{" "}
                                                  {Math.ceil(
                                                    item.travClicks_senior_price *
                                                      usdExchangeRate
                                                  )}
                                                </Typography>
                                              )}
                                              {currencyCode !== "SGD" && (
                                                <Typography
                                                  variant="caption"
                                                  display="block"
                                                  sx={{ fontSize: "12px" }}
                                                >
                                                  SGD{" "}
                                                  {Math.ceil(
                                                    item.travClicks_senior_price
                                                  )}
                                                </Typography>
                                              )}
                                            </Box>
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
                        {currentMode === "travclicks" &&
                          item.travClicks_adult_price > 0 &&
                          !(
                            filters.priceMode?.length === 1 &&
                            filters.priceMode[0] === "dmc"
                          ) &&
                          bookingType !== "enquiry" && (
                            <Typography
                              variant="caption"
                              sx={{
                                color: "red",
                                fontSize: "0.75rem",
                                mt: 1,
                                mb: 1,
                                display: "block",
                              }}
                            >
                              This marketplace product isn't eligible for DMC
                              enquiry.
                            </Typography>
                          )}

                        <Button
                          variant="contained"
                          color="primary"
                          onClick={(e) => handleAttractionClick(e, item)}
                          className="button -md -dark-1 bg-blue-1 text-white mt-20 mb-10"
                        >
                          View Detail
                        </Button>
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
                src="/icons/attractions.jpg"
                alt="Travel Icon"
                className="your-icon-class"
                style={{ width: "200px", height: "200px" }}
              />
            </div>
            <h5 className="MuiTypography-root MuiTypography-h5 css-hu3rhi-MuiTypography-root">
              {bookingType === "enquiry"
                ? "No attractions available for enquiry. Please try a different selection."
                : filters.searchParams?.location
                ? `No attractions found in ${filters.searchParams.location.address}. Please try a different location.`
                : "No attractions found. Please try a different search."}
            </h5>
          </div>
        </div>
      )}
    </>
  );
};

export default TourProperties;
