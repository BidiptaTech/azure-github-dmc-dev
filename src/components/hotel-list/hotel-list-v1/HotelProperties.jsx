import React, { useState, useEffect, useCallback } from "react";
import { useDispatch, useSelector } from "react-redux";
import { Link, useNavigate } from "react-router-dom";
import {
  FormControl,
  FormControlLabel,
  FormLabel,
  Radio,
  RadioGroup,
  Skeleton,
} from "@mui/material";
import {
  setHotelDetails,
  setHotelImages,
} from "@/slice/hotel/HotelDetailsSlice";
import {
  setId,
  fetchRoomData,
  settourid,
} from "@/slice/hotel/HotelAvailabilitySlice";
import HotelCardImages from "./HotelCardImages";
import {
  Card,
  CardContent,
  CardMedia,
  Typography,
  Button,
  Grid,
  Rating,
  Box,
  Chip,
  Tooltip,
  Avatar,
} from "@mui/material";
import CheckIcon from "@mui/icons-material/Check";

import SortBy from "./sortBy";
import { fetchHotels } from "@/slice/hotel/hotelSlice";
import InfiniteScroll from "react-infinite-scroll-component";
import { debounce } from "lodash";
//import state from "sweetalert/typings/modules/state";
import { Swiper, SwiperSlide } from "swiper/react";
import { Navigation, Pagination } from "swiper";
import { setPriceMode, setPriceModeId } from "@/slice/hotel/CategorySlice";
import travClikImage from "../../../../public/Images/hotel/travclick.jpg";
import { selectSelectedDmcLogo, selectSelectedDmcCompanyName } from "../../../slice/dmc/dmcSlice"; // Import DMC slice selectors

export default function HotelProperties() {
  const amenities = ["Breakfast", "WiFi", "Parking", "Swimming Pool"];

  // const { state } = useLocation();
  const dispatch = useDispatch();
  const navigate = useNavigate();

  // Get currency information from Redux store
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
  const usdCurrencySymbol = useSelector(
    (state) => state.auth.usdCurrencySymbol
  );
  const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
  // Get DMC logo and company name from DMC slice instead of auth slice
  const dmcLogo = useSelector(selectSelectedDmcLogo);
  const dmcCompanyName = useSelector(selectSelectedDmcCompanyName) || 'DMC';
  // Add PriceHide selector
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  //console.log(DmcName,"dmcName");

  // Get DMC logo from Redux store

  // const location = state.destination;
  const limit = 5; // Number of items to load initially and on each fetch
  // const updatedLocation = useSelector(
  //   (state) => state.hotels.searchState.location
  // );
  const sortOption = useSelector((state) => state.category.sortOption);
  const filters = useSelector((state) => state.category) || {};
  const tour_id = useSelector((state) => state.hotels.id);
  const priceMode = useSelector((state) => state.category.priceMode);
  const globalPriceMode = useSelector((state) => state.category.priceMode);
  const bookingType = useSelector((state) => state.common.bookingType);

  const [loading, setLoading] = useState(false);
  const { hotels, hasMore, status, start } = useSelector(
    (state) => state.hotels
  );
  //console.log(hotels,"hotels");
  const [selectedPriceModes, setSelectedPriceModes] = useState({});

  // Initialize selected price modes based on availability
  useEffect(() => {
    // Set default price modes for each hotel based on availability and global price mode
    const initialPriceModes = {};
    hotels.forEach((hotel) => {
      // Check if we're in DMC-only mode
      const isDmcOnlyMode =
        Array.isArray(globalPriceMode) &&
        globalPriceMode.length === 1 &&
        globalPriceMode[0] === "dmc";

      // If global price mode is DMC-only, only show DMC prices
      if (isDmcOnlyMode && hotel.dmc_price > 0) {
        initialPriceModes[hotel.id] = "dmc";
      }
      // If global price mode includes both, show both prices if available
      else if (!isDmcOnlyMode) {
        if (hotel.dmc_price > 0) {
          initialPriceModes[hotel.id] = "dmc";
        } else if (hotel.travclicks_price > 0) {
          initialPriceModes[hotel.id] = "travclicks";
        }
      }
    });
    setSelectedPriceModes(initialPriceModes);
  }, [hotels, globalPriceMode]);

  // Add a new effect to handle global price mode changes
  useEffect(() => {
    // Update selected price modes when global price mode changes
    const updatedPriceModes = {};
    hotels.forEach((hotel) => {
      // Check if we're in DMC-only mode
      const isDmcOnlyMode =
        Array.isArray(globalPriceMode) &&
        globalPriceMode.length === 1 &&
        globalPriceMode[0] === "dmc";

      if (isDmcOnlyMode && hotel.dmc_price > 0) {
        updatedPriceModes[hotel.id] = "dmc";
      } else if (!isDmcOnlyMode) {
        if (hotel.dmc_price > 0) {
          updatedPriceModes[hotel.id] = "dmc";
        } else if (hotel.travclicks_price > 0) {
          updatedPriceModes[hotel.id] = "travclicks";
        }
      }
    });
    setSelectedPriceModes(updatedPriceModes);
  }, [globalPriceMode, hotels]);

  const handlePriceModeChange = (id, mode) => {
    setSelectedPriceModes((prev) => ({
      ...prev,
      [id]: mode, // Store mode with hotel ID as key
    }));
  };

  const fetchData = useCallback(() => {
    if (status !== "loading" && hasMore && !loading) {
      setLoading(true);

      const isDataFetched = hotels.some((hotel) => hotel.start === start);

      // Ensure data has not been fetched and prevent fetching if the limit has been reached
      if (!isDataFetched && hotels.length < start + limit) {
        dispatch(fetchHotels({ start, limit }))
          .then(() => {
            setLoading(false);
            dispatch({
              type: "hotels",
              payload: { start: start + limit }, // Properly increment the start value
            });
          })
          .catch((error) => {
            setLoading(false);
            //console.error("API Error:", error); // Log errors to track issues
          });
        // } else {
        //   setLoading(false);
        // }
      }
    }
  }, [status, hasMore, loading, start, hotels, limit, dispatch]);

  // Infinite scroll logic
  const handleScroll = debounce(() => {
    const displayedItems = hotels.length; // Total currently fetched items
    const triggerThreshold = Math.floor(displayedItems / 2); // Half of displayed items
    // const heightThreshold = window.innerHeight + window.scrollY;
    // const earlyTriggerOffset = window.innerHeight * 0.6; // 30% of viewport height
    const remainingItems = hasMore ? displayedItems % limit : 0; // Remaining items to load

    // Trigger fetch when halfway through displayed items or nearing the end
    if (
      (displayedItems >= triggerThreshold && !loading) ||
      (remainingItems <= limit && !loading)
      // (heightThreshold >=
      //   document.documentElement.scrollHeight - earlyTriggerOffset &&
      //   !loading &&
      //   hasMore)
    ) {
      fetchData(); // Fetch more data
    }
  }, [hotels.length, loading, hasMore, fetchData, limit]);

  useEffect(() => {
    if (status === "succeeded") {
      handleScroll();
      // .then(() => {
      //   setLoading(false);
      //   dispatch({
      //     type: "hotels",
      //     payload: { start: start + limit },
      //   });
      // })
      // .catch((error) => {
      //   console.error("Error in fetchHotels:", error);
      // });
    }
    // } else {
    //   setLoading(false);
    // }
  }, [dispatch, status, handleScroll]);

  useEffect(() => {
    if (status === "loading") {
      const timer = setTimeout(() => {
        setLoading(false); // Skeleton will show for 2 seconds
      }, 1000); // Adjust the time as needed

      // Cleanup the timer when the component is unmounted or the status changes
      return () => clearTimeout(timer);
    }
  }, [status]);
  // useEffect(() => {
  //   // console.log("hasMore:", hasMore, "start:", start, "limit:", limit);
  // }, [hasMore, start, limit]);

  useEffect(() => {
    // Apply filters when they change
    console.log("Filters changed:", filters);
  }, [filters]);

  // const filterHotels = (hotels) => {
  //   let filteredHotels = hotels || [];

  //   // Price Range Filter
  //   if (
  //     filters.priceRange &&
  //     Array.isArray(filters.priceRange) &&
  //     filters.priceRange.length === 2
  //   ) {
  //     const [minPrice, maxPrice] = filters.priceRange;
  //     console.log(`Filtering hotels by price range: ${minPrice} - ${maxPrice}`);

  //     filteredHotels = filteredHotels.filter((hotel) => {
  //       // Get the appropriate price based on price mode
  //       let hotelPrice = 0;

  //       // Use the DMC price if available and selected
  //       if (globalPriceMode === "dmc" && hotel.dmc_price > 0) {
  //         hotelPrice = parseFloat(hotel.dmc_price) * exchangeRate;
  //       }
  //       // Otherwise use the lower of the two available prices
  //       else {
  //         const dmcPrice =
  //           hotel.dmc_price > 0
  //             ? parseFloat(hotel.dmc_price) * exchangeRate
  //             : Number.MAX_VALUE;
  //         const travclicksPrice =
  //           hotel.travclicks_price > 0
  //             ? parseFloat(hotel.travclicks_price) * exchangeRate
  //             : Number.MAX_VALUE;
  //         hotelPrice = Math.min(dmcPrice, travclicksPrice);
  //       }

  //       return hotelPrice >= minPrice && hotelPrice <= maxPrice;
  //     });
  //   }

  //   // Star Rating Filter
  //   if (filters.star1 || filters.star2 || filters.star3) {
  //     filteredHotels = filteredHotels.filter((hotel) => {
  //       const rating =
  //         parseFloat((hotel?.category || "").replace(/[^\d.]/g, "")) || 0;
  //       const inStarRating1 = filters.star1 && rating >= 3 && rating < 4;
  //       const inStarRating2 = filters.star2 && rating >= 4 && rating < 5;
  //       const inStarRating3 = filters.star3 && rating === 5;
  //       return inStarRating1 || inStarRating2 || inStarRating3;
  //     });
  //   }

  //   return filteredHotels;
  // };
  //console.log(filteredHotels,"filteredHotels");

  console.log(hotels, "hotels");

  const filteredHotels = hotels.filter((hotel) => {
    // Remove hotels where both prices are not available
    if (
      (hotel.dmc_price <= 0 && hotel.travclicks_price <= 0) ||
      (bookingType === "enquiry" && hotel.dmc_price <= 0)
    ) {
      return false; // Filter out hotels with no prices
    }

    // Check if we're in DMC-only mode
    const isDmcOnlyMode =
      Array.isArray(globalPriceMode) &&
      globalPriceMode.length === 1 &&
      globalPriceMode[0] === "dmc";

    // Price mode filter
    let hasValidPrice = false;
    // If DMC checkbox is checked (globalPriceMode is DMC-only)
    if (isDmcOnlyMode) {
      // Show the hotel only if DMC price is available
      hasValidPrice = hotel.dmc_price > 0; // Only show hotels with DMC price
    } else {
      // If DMC checkbox is unchecked (globalPriceMode includes both)
      // Show hotels with either DMC or Travclick price > 0
      hasValidPrice = hotel.dmc_price > 0 || hotel.travclicks_price > 0; // Show hotels with either price
    }

    // If hotel doesn't pass the price mode filter, return false immediately
    if (!hasValidPrice) return false;

    // Price Range Filter
    if (
      filters.priceRange &&
      typeof filters.priceRange === "object" &&
      "min" in filters.priceRange &&
      "max" in filters.priceRange
    ) {
      // Get the appropriate price based on selected mode and availability
      let hotelPrice = 0;
      if (isDmcOnlyMode && hotel.dmc_price > 0) {
        // Use DMC price when in DMC-only mode
        hotelPrice = parseFloat(hotel.dmc_price);
      } else {
        // Otherwise use the lower of available prices (or whichever is available)
        const dmcPrice =
          hotel.dmc_price > 0 ? parseFloat(hotel.dmc_price) : Number.MAX_VALUE;
        const travclicksPrice =
          hotel.travclicks_price > 0
            ? parseFloat(hotel.travclicks_price)
            : Number.MAX_VALUE;
        hotelPrice = Math.min(dmcPrice, travclicksPrice);
      }

      // Apply exchange rate if needed
      const convertedPrice = hotelPrice * exchangeRate;

      // Check if price is within range
      if (
        convertedPrice < filters.priceRange.min ||
        convertedPrice > filters.priceRange.max
      ) {
        return false; // Filter out hotels outside price range
      }
    }

    // If hotel passes all filters, return true
    return true;
  });

  // const sortHotels = (hotels) => {
  //   const sortedHotels = [...hotels];

  //   if (
  //     sortOption === "Price - Low to High" ||
  //     sortOption === "Price - High to Low"
  //   ) {
  //     // Sort based on price, taking into account the selected price mode
  //     sortedHotels.sort((a, b) => {
  //       // Calculate price based on price mode
  //       const getPriceValue = (hotel) => {
  //         if (globalPriceMode === "dmc" && hotel.dmc_price > 0) {
  //           // Use DMC price if that mode is selected and available
  //           return parseFloat(hotel.dmc_price) * exchangeRate;
  //         } else {
  //           // Otherwise use the lower of available prices
  //           const dmcPrice =
  //             hotel.dmc_price > 0
  //               ? parseFloat(hotel.dmc_price) * exchangeRate
  //               : Number.MAX_VALUE;
  //           const travclicksPrice =
  //             hotel.travclicks_price > 0
  //               ? parseFloat(hotel.travclicks_price) * exchangeRate
  //               : Number.MAX_VALUE;
  //           return Math.min(dmcPrice, travclicksPrice);
  //         }
  //       };

  //       const priceA = getPriceValue(a);
  //       const priceB = getPriceValue(b);

  //       return sortOption === "Price - Low to High"
  //         ? priceA - priceB
  //         : priceB - priceA;
  //     });
  //   }

  //   return sortedHotels;
  // };

  // const sortedFilteredHotels = sortHotels(filteredHotels);
  //console.log("sorthotel", sortedFilteredHotels);

  // const totalItems = sortedFilteredHotels.length;
  // const totalPages = Math.ceil(totalItems / itemsPerPage);
  // const paginatedHotels = sortedFilteredHotels.slice(
  //   (currentPage - 1) * itemsPerPage,
  //   currentPage * itemsPerPage
  // );

  const handleViewDetails = (id) => {
    const selectedHotel = hotels.find((hotel) => hotel.id === id);

    if (selectedHotel) {
      let selectedMode = "dmc"; // Default mode
      let priceModeId = null;

      // First check if user has explicitly selected a price mode for this hotel
      if (selectedPriceModes[id]) {
        // For DMC mode, use "dmc", for travclicks use "travclick"
        selectedMode = selectedPriceModes[id] === "dmc" ? "dmc" : "travclick";

        // Set the appropriate price mode ID based on the selected mode
        if (selectedMode === "dmc") {
          priceModeId = selectedHotel.dmc_id;
        } else {
          priceModeId = selectedHotel.travclicks_id;
        }
      }
      // If no explicit selection, use DMC if available, otherwise travclicks
      else {
        if (selectedHotel.dmc_price > 0) {
          selectedMode = "dmc";
        } else if (selectedHotel.travclicks_price > 0) {
          selectedMode = "travclick"; // Changed from "marketplace" to "travclick"
          priceModeId = selectedHotel.travclicks_id;
        }
      }

      // Ensure we have a valid price mode ID
      if (!priceModeId) {
        console.error("No valid price mode ID found for hotel:", selectedHotel);
        // Fallback to any available ID
        priceModeId = selectedHotel.dmc_id || selectedHotel.travclicks_id;
      }

      // Log the selected hotel data before dispatching
      // console.log("Selected hotel for details:", {
      //   id: selectedHotel.id,
      //   hotel_id: selectedHotel.hotel_id,
      //   hotel_name: selectedHotel.hotel_name,
      //   selectedMode: selectedMode,
      //   priceModeId: priceModeId,
      //   full_hotel: selectedHotel
      // });

      dispatch(setHotelDetails(selectedHotel));
      dispatch(setHotelImages(selectedHotel.images));
      dispatch(setId(id));
      dispatch(settourid(tour_id));

      // Convert the selected mode to a string for consistency - don't use arrays
      dispatch(setPriceMode(selectedMode));
      dispatch(setPriceModeId(priceModeId));

      dispatch(
        fetchRoomData({
          id,
          tour_id,
          priceMode: selectedMode, // Keep this as string for API compatibility
          priceModeId: priceModeId,
        })
      );

      navigate("/dashboard/db-dashboard/hotel-details", {
        state: {
          hotel: selectedHotel,
          priceMode: selectedMode, // Keep this as string for hotel details page compatibility
          priceModeId: priceModeId,
        },
      });
    }
  };

  return (
    <>
      {/* <SortBy /> */}
      <InfiniteScroll
        dataLength={filteredHotels.length}
        next={status === "succeeded" ? handleScroll : null}
        hasMore={status === "succeeded" ? hasMore : false}
        style={{ display: "flex", flexDirection: "column", overflow: "hidden" }}
      >
        {status === "loading" ? ( // Show skeletons while loading
          Array.from({ length: 4 }).map((_, index) => (
            <Card key={index} className="hotel-card" sx={{ p: 2 }}>
              <Grid container spacing={2} justifyContent="space-between">
                <Grid item xs={12} sm={12} md={4}>
                  <Skeleton
                    variant="rectangular"
                    height={150}
                    sx={{ borderRadius: "10px" }}
                  />
                </Grid>
                <Grid item xs={12} sm={12} md={4}>
                  <CardContent>
                    <Skeleton variant="text" width="60%" />
                    <Skeleton variant="text" width="40%" />
                    <Skeleton variant="text" width="90%" />
                  </CardContent>
                </Grid>
                <Grid item xs={12} sm={12} md={4}>
                  <CardContent>
                    <Skeleton variant="text" width="80%" />
                    <Skeleton variant="text" width="60%" />
                    <Skeleton
                      variant="rectangular"
                      width="100px"
                      height="36px"
                    />
                  </CardContent>
                </Grid>
              </Grid>
            </Card>
          ))
        ) : filteredHotels.length === 0 && !loading ? ( // Show message if no hotels match
          <div
            className="no-hotels-message"
            style={{ textAlign: "center", marginTop: "2rem" }}
          >
            <Box
              sx={{
                display: "flex",
                flexDirection: "column",
                alignItems: "center",
                justifyContent: "center",
                padding: "3rem",
                border: "1px solid #e0e0e0",
                borderRadius: "16px",
                backgroundColor: "#ffffff",
                boxShadow: "0 8px 32px rgba(0, 0, 0, 0.1)",
                maxWidth: "600px",
                margin: "0 auto",
                position: "relative",
                overflow: "hidden",
                "&::before": {
                  content: '""',
                  position: "absolute",
                  top: 0,
                  left: 0,
                  right: 0,
                  bottom: 0,
                  background:
                    "linear-gradient(45deg, #f3f4f6 25%, transparent 25%, transparent 50%, #f3f4f6 50%, #f3f4f6 75%, transparent 75%, transparent)",
                  backgroundSize: "40px 40px",
                  opacity: 0.1,
                  animation: "shimmer 2s infinite linear",
                },
                "@keyframes shimmer": {
                  "0%": {
                    backgroundPosition: "0 0",
                  },
                  "100%": {
                    backgroundPosition: "40px 0",
                  },
                },
              }}
            >
              {/* Animated Hotel Icon */}
              <Box
                sx={{
                  position: "relative",
                  width: "120px",
                  height: "120px",
                  marginBottom: "2rem",
                  animation: "float 3s ease-in-out infinite",
                  "@keyframes float": {
                    "0%": {
                      transform: "translateY(0px)",
                    },
                    "50%": {
                      transform: "translateY(-20px)",
                    },
                    "100%": {
                      transform: "translateY(0px)",
                    },
                  },
                }}
              >
                <Box
                  sx={{
                    position: "absolute",
                    top: "50%",
                    left: "50%",
                    transform: "translate(-50%, -50%)",
                    fontSize: "4rem",
                    color: "#3554D1",
                    filter: "drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1))",
                  }}
                >
                  🏨
                </Box>
                {/* Decorative circles */}
                <Box
                  sx={{
                    position: "absolute",
                    top: "50%",
                    left: "50%",
                    transform: "translate(-50%, -50%)",
                    width: "100%",
                    height: "100%",
                    border: "2px solid #3554D1",
                    borderRadius: "50%",
                    opacity: 0.2,
                    animation: "pulse 2s ease-in-out infinite",
                    "@keyframes pulse": {
                      "0%": {
                        transform: "translate(-50%, -50%) scale(1)",
                        opacity: 0.2,
                      },
                      "50%": {
                        transform: "translate(-50%, -50%) scale(1.2)",
                        opacity: 0.1,
                      },
                      "100%": {
                        transform: "translate(-50%, -50%) scale(1)",
                        opacity: 0.2,
                      },
                    },
                  }}
                />
              </Box>

              {/* Main Content */}
              <Typography
                variant="h4"
                sx={{
                  fontWeight: "bold",
                  color: "#1a1a1a",
                  marginBottom: "1rem",
                  animation: "fadeInUp 0.6s ease-out",
                  "@keyframes fadeInUp": {
                    "0%": {
                      opacity: 0,
                      transform: "translateY(20px)",
                    },
                    "100%": {
                      opacity: 1,
                      transform: "translateY(0)",
                    },
                  },
                }}
              >
                No Hotels Found
              </Typography>

              <Typography
                variant="body1"
                sx={{
                  color: "#666",
                  marginBottom: "2rem",
                  maxWidth: "400px",
                  animation: "fadeInUp 0.6s ease-out 0.2s both",
                }}
              >
                We couldn't find any hotels matching your search criteria. Try
                adjusting your filters or search parameters to find what you're
                looking for.
              </Typography>

              {/* Action Buttons */}
              <Box
                sx={{
                  display: "flex",
                  gap: "1rem",
                  animation: "fadeInUp 0.6s ease-out 0.4s both",
                }}
              >
                <button
                  className="button -dark-1 py-10 px-20 rounded-1 bg-blue-1 text-white"
                  onClick={() => window.location.reload()}
                  style={{
                    transition: "all 0.3s ease",
                    border: "none",
                    cursor: "pointer",
                    "&:hover": {
                      transform: "translateY(-2px)",
                      boxShadow: "0 4px 12px rgba(53, 84, 209, 0.2)",
                    },
                  }}
                >
                  Reset Search
                </button>
                <button
                  className="button -dark-1 py-10 px-20 rounded-1 bg-white text-blue-1 border-blue-1"
                  onClick={() => {
                    // Add your filter reset logic here
                    dispatch({ type: "category/resetFilters" });
                  }}
                  style={{
                    transition: "all 0.3s ease",
                    border: "1px solid #3554D1",
                    cursor: "pointer",
                    "&:hover": {
                      transform: "translateY(-2px)",
                      boxShadow: "0 4px 12px rgba(53, 84, 209, 0.1)",
                    },
                  }}
                >
                  Clear Filters
                </button>
              </Box>

              {/* Decorative Elements */}
              <Box
                sx={{
                  position: "absolute",
                  top: "20px",
                  right: "20px",
                  width: "60px",
                  height: "60px",
                  background: "linear-gradient(45deg, #3554D1, #4a6ee0)",
                  borderRadius: "50%",
                  opacity: 0.1,
                  animation: "rotate 10s linear infinite",
                  "@keyframes rotate": {
                    "0%": {
                      transform: "rotate(0deg)",
                    },
                    "100%": {
                      transform: "rotate(360deg)",
                    },
                  },
                }}
              />
              <Box
                sx={{
                  position: "absolute",
                  bottom: "20px",
                  left: "20px",
                  width: "40px",
                  height: "40px",
                  background: "linear-gradient(45deg, #4a6ee0, #3554D1)",
                  borderRadius: "50%",
                  opacity: 0.1,
                  animation: "rotate 8s linear infinite reverse",
                }}
              />
            </Box>
          </div>
        ) : (
          filteredHotels.slice(0, 7).map((item, index) => {
            const isSelected = selectedPriceModes.hasOwnProperty(item.id);
            const selectedMode = isSelected
              ? selectedPriceModes[item.id]
              : "dmc"; // Default to "dmc" if not selected

            // Check if price modes are valid (non-null and greater than 0)
            const isDmcAvailable = item?.dmc_price && item.dmc_price > 0;
            const isTravclicksAvailable =
              item?.travclicks_price && item.travclicks_price > 0;

            // Calculate prices with exchange rates
            const dmcPrice = isDmcAvailable
              ? parseFloat(item.dmc_price) * exchangeRate
              : 0;
            const travClicksPrice = isTravclicksAvailable
              ? parseFloat(item.travclicks_price) * exchangeRate
              : 0;
            const usddmcPrice = isDmcAvailable
              ? parseFloat(item.dmc_price) * usdExchangeRate
              : 0;
            const usdtravClicksPrice = isTravclicksAvailable
              ? parseFloat(item.travclicks_price) * usdExchangeRate
              : 0;

            // For display purposes
            const displayTravClicksPrice = travClicksPrice;
            const displayUsdTravClicksPrice = usdtravClicksPrice;

            return (
              <div className="col-12" key={item.id}>
                <div className="border-top-light pt-30">
                  <div className="row x-gap-20 y-gap-20">
                    <div className="col-md-auto">
                      <div className="cardImage ratio ratio-1:1 w-250 md:w-1/1 rounded-4">
                        <div className="cardImage__content">
                          <div className="cardImage-slider rounded-4 custom_inside-slider">
                            <Swiper
                              className="mySwiper"
                              modules={[Pagination, Navigation]}
                              pagination={{ clickable: true }}
                              navigation={true}
                            >
                              {item?.room_image?.length > 0 ? (
                                item.site_image.map((slide, i) => (
                                  <SwiperSlide key={i}>
                                    <img
                                      className="rounded-4 col-12 js-lazy"
                                      src={slide}
                                      alt="image"
                                      style={{
                                        objectFit: "cover",
                                        height: "220px",
                                        width: "100%",
                                      }}
                                    />
                                  </SwiperSlide>
                                ))
                              ) : (
                                <SwiperSlide>
                                  <img
                                    src={travClikImage}
                                    onError={(e) => {
                                      e.target.onerror = null;
                                      e.target.src = "travclick.png";
                                    }}
                                    alt="Image"
                                    style={{
                                      objectFit: "cover",
                                      height: "220px",
                                      width: "100%",
                                    }}
                                  />
                                </SwiperSlide>
                              )}
                            </Swiper>
                          </div>
                        </div>
                        <div className="cardImage__wishlist">
                          <button className="button -blue-1 bg-white size-30 rounded-full shadow-2">
                            <i className="icon-heart text-12"></i>
                          </button>
                        </div>
                      </div>
                    </div>

                    <div className="col-md">
                      <div className="d-flex justify-content-between">
                        <h3 className="text-18 lh-16 fw-500">
                          {item?.hotel_name}
                        </h3>
                        {/* <div className="d-flex align-items-center">
                          <div className="bg-blue-1 text-white px-3 py-1 rounded-pill me-2" style={{ fontSize: "12px" }}>
                            {parseFloat((item?.category || "").replace(/[^\d]/g, "")) || 0}-Star
                          </div>
                          <Rating
                            value={parseFloat((item?.category || "").replace(/[^\d]/g, "")) || 0}
                            readOnly
                            precision={0.5}
                            size="small"
                          />
                        </div> */}
                      </div>

                      <div className="row x-gap-10 y-gap-10 items-center pt-10">
                        <div className="col-auto">
                          <div className="d-flex items-center">
                            <i className="icon-location-2 text-14 text-blue-1 mr-5"></i>
                            <p className="text-14">{item?.location}</p>
                          </div>
                        </div>
                      </div>

                      {/* Static Facilities */}
                      <div className="row x-gap-10 y-gap-10 pt-10">
                        <div className="col-12">
                          <div className="d-flex items-center flex-wrap gap-2">
                            <div className="border-light rounded-100 py-3 px-10 text-12 lh-14">
                              <i className="icon-wifi text-blue-1 mr-5"></i>
                              WiFi
                            </div>
                            <div className="border-light rounded-100 py-3 px-10 text-12 lh-14">
                              <i className="icon-coffee text-blue-1 mr-5"></i>
                              Breakfast
                            </div>
                            <div className="border-light rounded-100 py-3 px-10 text-12 lh-14">
                              <i className="icon-parking text-blue-1 mr-5"></i>
                              Parking
                            </div>
                            <div className="border-light rounded-100 py-3 px-10 text-12 lh-14">
                              <i className="icon-pool text-blue-1 mr-5"></i>
                              Pool
                            </div>
                          </div>
                        </div>
                      </div>

                      {/* Categories */}
                      <div className="mt-10">
                        <div className="d-flex flex-wrap gap-2">
                          <Chip
                            label="Family Friendly"
                            size="small"
                            color="primary"
                            variant="outlined"
                            icon={<CheckIcon fontSize="small" />}
                            sx={{
                              height: "24px",
                              "& .MuiChip-label": { fontSize: "11px", px: 1 },
                              "& .MuiChip-icon": { fontSize: "14px" },
                            }}
                          />
                          <Chip
                            label="Business"
                            size="small"
                            color="primary"
                            variant="outlined"
                            icon={<CheckIcon fontSize="small" />}
                            sx={{
                              height: "24px",
                              "& .MuiChip-label": { fontSize: "11px", px: 1 },
                              "& .MuiChip-icon": { fontSize: "14px" },
                            }}
                          />
                        </div>
                      </div>
                    </div>

                    <div className="col-md-auto text-right md:text-left">
                      {/* Price Display - Improved */}
                      <div className="d-flex flex-column align-items-end md:align-items-start">
                        {(isDmcAvailable || isTravclicksAvailable) && (
                          <Box sx={{ mt: 1, fontSize: "14px", width: "100%" }}>
                            <Box
                              sx={{
                                display: "flex",
                                flexDirection: "row",
                                flexWrap: "wrap",
                                width: "100%",
                                justifyContent: "center",
                                mt: 1,
                                gap: 0.5,
                              }}
                            >
                              <FormControl
                                component="fieldset"
                                key={item.id}
                                sx={{ width: "100%" }}
                              >
                                <RadioGroup
                                  row
                                  value={
                                    selectedPriceModes[item.id] ||
                                    (isDmcAvailable ? "dmc" : "travclicks")
                                  }
                                  onChange={(e) =>
                                    handlePriceModeChange(
                                      item.id,
                                      e.target.value
                                    )
                                  }
                                  sx={{ justifyContent: "center" }}
                                >
                                  {/* Show DMC mode if dmc_price is greater than 0 */}
                                  {item?.dmc_price > 0 && (
                                    <Box
                                      onClick={(e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        handlePriceModeChange(item.id, "dmc");
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
                                          selectedPriceModes[item.id] === "dmc"
                                            ? "#e6f2ff"
                                            : "#fff",
                                        transform:
                                          selectedPriceModes[item.id] === "dmc"
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
                                          handlePriceModeChange(item.id, "dmc");
                                        }}
                                      >
                                        <Radio
                                          checked={selectedPriceModes[item.id] === "dmc"}
                                          onChange={(e) => {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            handlePriceModeChange(item.id, "dmc");
                                          }}
                                          value="dmc"
                                          sx={{ p: 0, mr: 1, size: "small" }}
                                          size="small"
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
                                              alt={`${dmcCompanyName} Logo`}
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
                                            {`${dmcCompanyName}'s Mode`}
                                          </Typography>
                                        </Box>
                                      </Box>
                                      {PriceHide === "0" && (
                                        <Box
                                          sx={{
                                            fontSize: "12px",
                                            color: "#333",
                                            pl: 3,
                                            pb: 0.5,
                                          }}
                                        >
                                          <Typography
                                            variant="body2"
                                            sx={{
                                              fontWeight: "600",
                                              fontSize: "14px",
                                              color: "#3554D1",
                                            }}
                                          >
                                            {currencyCode} {dmcPrice.toFixed(2)}
                                          </Typography>
                                          <Typography
                                            variant="caption"
                                            color="text.secondary"
                                            sx={{
                                              display: "block",
                                              fontSize: "11px",
                                            }}
                                          >
                                            {usdCurrencyCode}{" "}
                                            {usddmcPrice.toFixed(2)}
                                          </Typography>
                                          <Typography
                                            variant="caption"
                                            color="text.secondary"
                                            sx={{
                                              display: "block",
                                              fontSize: "11px",
                                            }}
                                          >
                                            SGD {item?.dmc_price.toFixed(2)}
                                          </Typography>
                                        </Box>
                                      )}
                                    </Box>
                                  )}

                                  {/* Only show Travclicks if not in DMC-only mode */}
                                  {item?.travclicks_price > 0 &&
                                    !(
                                      Array.isArray(globalPriceMode) &&
                                      globalPriceMode.length === 1 &&
                                      globalPriceMode[0] === "dmc"
                                    ) &&
                                    (bookingType === "booking" ||
                                      bookingType === "null") && (
                                      <Box
                                        onClick={(e) => {
                                          e.preventDefault();
                                          e.stopPropagation();
                                          handlePriceModeChange(item.id, "travclicks");
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
                                            selectedPriceModes[item.id] ===
                                            "travclicks"
                                              ? "#e6f2ff"
                                              : "#fff",
                                          transform:
                                            selectedPriceModes[item.id] ===
                                            "travclicks"
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
                                            handlePriceModeChange(item.id, "travclicks");
                                          }}
                                        >
                                          <Radio
                                            checked={selectedPriceModes[item.id] === "travclicks"}
                                            onChange={(e) => {
                                              e.preventDefault();
                                              e.stopPropagation();
                                              handlePriceModeChange(item.id, "travclicks");
                                            }}
                                            value="travclicks"
                                            sx={{ p: 0, mr: 1, size: "small" }}
                                            size="small"
                                          />
                                          <Typography>Travclicks</Typography>
                                        </Box>
                                        {PriceHide === "0" && (
                                          <Box
                                            sx={{
                                              fontSize: "12px",
                                              color: "#333",
                                              pl: 3,
                                              pb: 0.5,
                                            }}
                                          >
                                            <Typography
                                              variant="body2"
                                              sx={{
                                                fontWeight: "600",
                                                fontSize: "14px",
                                                color: "#3554D1",
                                              }}
                                            >
                                              {currencyCode}{" "}
                                              {displayTravClicksPrice.toFixed(2)}
                                            </Typography>
                                            <Typography
                                              variant="caption"
                                              color="text.secondary"
                                              sx={{
                                                display: "block",
                                                fontSize: "11px",
                                              }}
                                            >
                                              {usdCurrencyCode}{" "}
                                              {displayUsdTravClicksPrice.toFixed(
                                                2
                                              )}
                                            </Typography>
                                            <Typography
                                              variant="caption"
                                              color="text.secondary"
                                              sx={{
                                                display: "block",
                                                fontSize: "11px",
                                              }}
                                            >
                                              SGD{" "}
                                              {item?.travclicks_price.toFixed(2)}
                                            </Typography>
                                          </Box>
                                        )}
                                      </Box>
                                    )}
                                </RadioGroup>
                              </FormControl>
                            </Box>
                          </Box>
                        )}

                        {/* Add warning message here, only when Travclicks mode is selected */}
                        {selectedPriceModes[item.id] === "travclicks" && 
                          item?.travclicks_price > 0 && 
                          (bookingType === "booking" || bookingType === "null") && (
                          <Typography variant="caption" sx={{ color: 'red', fontSize: '0.75rem', mt: 1, mb: 1, display: 'block' }}>
                            This marketplace product isn't eligible for DMC enquiry.
                          </Typography>
                        )}

                        {/* Call to Action Button */}
                        <Button
                          variant="contained"
                          color="primary"
                          onClick={() => handleViewDetails(item.id)}
                          className="button -md -dark-1 bg-blue-1 text-white py-10 w-100"
                          sx={{
                            borderRadius: "8px",
                            padding: "8px 15px",
                            textTransform: "none",
                            fontWeight: "600",
                            boxShadow: "0 4px 10px rgba(53, 84, 209, 0.25)",
                            "&:hover": {
                              boxShadow: "0 6px 15px rgba(53, 84, 209, 0.35)",
                              transform: "translateY(-2px)",
                            },
                            transition: "all 0.3s ease",
                            mt: 1.5,
                            fontSize: "14px",
                          }}
                        >
                          See Availability
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            );
          })
        )}
        {/* Second skeleton loading state (infinite scroll) */}
        {loading &&
          hasMore &&
          Array.from({ length: 4 }).map((_, index) => (
            <Card
              key={`scroll-skeleton-${index}`}
              className="hotel-card"
              sx={{
                p: 2,
                width: "100%",
                display: "flex",
                flexDirection: "column",
              }}
            >
              <Grid
                container
                spacing={2}
                justifyContent="space-between"
                sx={{ width: "100%", height: "100%" }}
              >
                <Grid item xs={12} sm={12} md={4} sx={{ display: "flex" }}>
                  <Skeleton
                    variant="rectangular"
                    sx={{
                      borderRadius: "10px",
                      width: "100%",
                      height: 150,
                      flex: 1,
                    }}
                  />
                </Grid>
                <Grid
                  item
                  xs={12}
                  sm={12}
                  md={4}
                  sx={{ display: "flex", flexDirection: "column" }}
                >
                  <CardContent
                    sx={{
                      width: "100%",
                      height: "100%",
                      display: "flex",
                      flexDirection: "column",
                      flex: 1,
                    }}
                  >
                    <Skeleton variant="text" width="60%" />
                    <Skeleton variant="text" width="40%" />
                    <Skeleton variant="text" width="90%" />
                    <Skeleton variant="text" width="70%" sx={{ mt: 1 }} />
                    <Skeleton variant="text" width="80%" />
                  </CardContent>
                </Grid>
                <Grid
                  item
                  xs={12}
                  sm={12}
                  md={4}
                  sx={{ display: "flex", flexDirection: "column" }}
                >
                  <CardContent
                    sx={{
                      width: "100%",
                      height: "100%",
                      display: "flex",
                      flexDirection: "column",
                      flex: 1,
                    }}
                  >
                    <Skeleton variant="text" width="80%" />
                    <Skeleton variant="text" width="60%" />
                    <Skeleton
                      variant="rectangular"
                      width="100%"
                      height="80px"
                      sx={{ mt: 1, borderRadius: "8px" }}
                    />
                    <Skeleton
                      variant="rectangular"
                      width="100%"
                      height="36px"
                      sx={{ mt: 2, borderRadius: "8px" }}
                    />
                  </CardContent>
                </Grid>
              </Grid>
            </Card>
          ))}
        {!hasMore && <p>No more hotels available.</p>}
      </InfiniteScroll>
    </>
  );
}
