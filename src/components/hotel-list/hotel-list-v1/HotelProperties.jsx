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
        console.log(`Fetching hotels with start=${start}, limit=${limit}, hasMore=${hasMore}`);
        
        dispatch(fetchHotels({ start, limit }))
          .unwrap() // Use unwrap to handle the promise correctly
          .then((data) => {
            setLoading(false);
            
            // Check if we got back any data
            const gotResults = Array.isArray(data) && data.length > 0;
            
            if (gotResults) {
              console.log(`Successfully fetched ${data.length} hotels`);
              // Only update start if we received data
              dispatch({
                type: "hotels/updatePaginationState",
                payload: { 
                  start: start + data.length,
                  // Set hasMore to false if we got fewer items than requested
                  hasMore: data.length === limit
                }
              });
            } else {
              console.log("No more hotels available, stopping pagination");
              dispatch({
                type: "hotels/updatePaginationState",
                payload: { hasMore: false }
              });
            }
          })
          .catch((error) => {
            setLoading(false);
            console.log("Error fetching hotels:", error.message);
            
            // If we get a 404, mark hasMore as false to prevent more requests
            if (error.message?.includes('404')) {
              console.log("Received 404 error - no more hotels available");
              dispatch({
                type: "hotels/updatePaginationState",
                payload: { hasMore: false }
              });
            }
          });
      } else {
        setLoading(false);
      }
    }
  }, [status, hasMore, loading, start, hotels, limit, dispatch]);

  // Infinite scroll logic
  const handleScroll = debounce(() => {
    // Don't do anything if we already know there are no more hotels
    if (!hasMore) {
      console.log("No more hotels to fetch, skipping scroll handler");
      return;
    }
    
    // Don't do anything if we're currently loading
    if (loading) {
      return;
    }
    
    const displayedItems = hotels.length; // Total currently fetched items
    
    // Only trigger fetch when we have some items and we're not already loading
    if (displayedItems > 0 && !loading) {
      console.log("Scroll triggered, fetching more hotels");
      fetchData(); // Fetch more data
    }
  }, [hotels.length, loading, hasMore, fetchData]);

  useEffect(() => {
    if (status === "succeeded" && hasMore) {
      // Only trigger scroll handler if we have more hotels to fetch
      console.log("Status changed to succeeded, checking for more hotels");
      handleScroll();
    }
  }, [dispatch, status, handleScroll, hasMore]);

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

  // Log all hotels to help debug filtering issues
  useEffect(() => {
    if (hotels && hotels.length > 0) {
      console.log("All hotels from API:", hotels.map(h => ({
        id: h.id,
        name: h.hotel_name,
        dmc_price: h.dmc_price,
        travclicks_price: h.travclicks_price
      })));
      
      // Check specifically for St. Regis
      const stRegis = hotels.find(h => 
        h.hotel_name && h.hotel_name.toLowerCase().includes('regis'));
      
      if (stRegis) {
        console.log("FOUND ST. REGIS:", {
          id: stRegis.id,
          name: stRegis.hotel_name,
          price: stRegis.dmc_price,
          location: stRegis.location
        });
      } else {
        console.log("ST. REGIS NOT FOUND IN API RESPONSE!");
      }
    }
  }, [hotels]);

  // Will log the filtered hotels count after filtering is done

  // TEMPORARY DEBUG VERSION - SHOW ALL HOTELS WITHOUT FILTERING
  console.log("*** DEBUG MODE: All hotels will be shown without price filtering ***");
  
  // Instead of filtering, just map all hotels to see them
  const allHotelsWithPrices = hotels.map(hotel => {
    const dmcPrice = typeof hotel.dmc_price === 'string' 
      ? parseFloat(hotel.dmc_price) 
      : (hotel.dmc_price || 0);
      
    const travclicksPrice = typeof hotel.travclicks_price === 'string'
      ? parseFloat(hotel.travclicks_price)
      : (hotel.travclicks_price || 0);
      
    console.log(`DEBUG: Hotel ${hotel.hotel_name}, DMC price: ${dmcPrice}, Travclicks price: ${travclicksPrice}`);
    
    return {
      ...hotel,
      parsed_dmc_price: dmcPrice,
      parsed_travclicks_price: travclicksPrice
    };
  });
  
  // This will show ALL hotels in the console for debugging
  console.log("All hotels before filtering:", allHotelsWithPrices);
  
  // For this debug version, don't filter at all - just show all hotels
  const filteredHotels = hotels.filter((hotel) => {
    // Show every hotel, regardless of price
    return true;
    
    /* Original filtering code - commented out for debugging
    console.log(`Filtering hotel: ${hotel.hotel_name}, DMC price: ${hotel.dmc_price}, Travclicks price: ${hotel.travclicks_price}`);
    
    // Make sure we have numerical prices to work with
    const dmcPrice = typeof hotel.dmc_price === 'string' 
      ? parseFloat(hotel.dmc_price) 
      : (hotel.dmc_price || 0);
      
    const travclicksPrice = typeof hotel.travclicks_price === 'string'
      ? parseFloat(hotel.travclicks_price)
      : (hotel.travclicks_price || 0);
      
    // Remove hotels where both prices are not available
    if (
      (dmcPrice <= 0 && travclicksPrice <= 0) ||
      (bookingType === "enquiry" && dmcPrice <= 0)
    ) {
      console.log(`Filtered out ${hotel.hotel_name}: No valid prices available`);
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
      hasValidPrice = dmcPrice > 0; // Only show hotels with DMC price
    } else {
      // If DMC checkbox is unchecked (globalPriceMode includes both)
      // Show hotels with either DMC or Travclick price > 0
      hasValidPrice = dmcPrice > 0 || travclicksPrice > 0; // Show hotels with either price
    }

    // If hotel doesn't pass the price mode filter, return false immediately
    if (!hasValidPrice) {
      console.log(`Filtered out ${hotel.hotel_name}: Failed price mode filter`);
      return false;
    }
    */

    // PRICE RANGE FILTER TEMPORARILY COMMENTED OUT TO DEBUG HOTEL DISPLAY
    // Commenting out all price range filtering to check if hotels appear
    console.log(`PRICE FILTER DISABLED - Hotel ${hotel.hotel_name} will be shown regardless of price`);
    
    /*
    // Price Range Filter
    if (
      filters.priceRange &&
      typeof filters.priceRange === "object" &&
      "min" in filters.priceRange &&
      "max" in filters.priceRange
    ) {
      // Get the appropriate price based on selected mode and availability
      let hotelPrice = 0;
      
      // First make sure we have a valid price to work with
      if (isDmcOnlyMode && hotel.dmc_price > 0) {
        // Use DMC price when in DMC-only mode
        hotelPrice = typeof hotel.dmc_price === 'string' 
          ? parseFloat(hotel.dmc_price) 
          : hotel.dmc_price;
      } else {
        // Otherwise use the lower of available prices (or whichever is available)
        const dmcPrice =
          hotel.dmc_price > 0 
            ? (typeof hotel.dmc_price === 'string' ? parseFloat(hotel.dmc_price) : hotel.dmc_price)
            : Number.MAX_VALUE;
            
        const travclicksPrice =
          hotel.travclicks_price > 0
            ? (typeof hotel.travclicks_price === 'string' ? parseFloat(hotel.travclicks_price) : hotel.travclicks_price)
            : Number.MAX_VALUE;
            
        hotelPrice = Math.min(dmcPrice, travclicksPrice);
      }

      // Apply exchange rate if needed
      const convertedPrice = hotelPrice * exchangeRate;
      
      // Log the price details for debugging
      console.log(`Hotel ${hotel.hotel_name} - Price: ${hotelPrice}, Converted: ${convertedPrice}, Range: ${filters.priceRange.min} - ${filters.priceRange.max}`);

      // Check if price is within range - use inclusive comparisons
      if (
        convertedPrice < filters.priceRange.min ||
        convertedPrice > filters.priceRange.max
      ) {
        console.log(`Hotel ${hotel.hotel_name} filtered out: price ${convertedPrice} outside range ${filters.priceRange.min} - ${filters.priceRange.max}`);
        return false; // Filter out hotels outside price range
      }
      
      // Log if the hotel passed the price filter
      console.log(`Hotel ${hotel.hotel_name} passed price filter`);
    }
    */

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
  
  // Log the final filtered hotels count
  useEffect(() => {
    console.log(`Final hotels count: ${hotels.length} total, ${filteredHotels.length} after filtering`);
    if (filteredHotels.length > 0) {
      console.log("Filtered hotels:", filteredHotels.map(h => ({
        name: h.hotel_name,
        dmc_price: h.dmc_price
      })));
    }
  }, [filteredHotels.length, hotels.length]);

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
          dmc_id: selectedHotel.dmc_id,
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
            <div className="MuiBox-root css-t4lhjn">
              <div className="MuiBox-root css-d0j5jx">
                <img
                  src="/icons/hotel1.jpg"
                  alt="Travel Icon"
                  className="your-icon-class"
                  style={{ width: "200px", height: "200px" }}
                />
              </div>
              <h5 className="MuiTypography-root MuiTypography-h5 css-hu3rhi-MuiTypography-root">
                {bookingType === "enquiry"
                  ? "No hotels available for enquiry. Please try a different selection."
                  : filters.searchParams?.location
                  ? `No hotels found in ${filters.searchParams.location.address}. Please try a different location.`
                  : "Please provide hotel location and date of journey and search..."}
              </h5>
            </div>
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
