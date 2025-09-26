import Header11 from "@/components/header/header-11";
import DefaultFooter from "@/components/footer/default";
import MainFilterSearchBox from "@/components/activity-list/activity-list-v3/MainFilterSearchBox";
import TopHeaderFilter from "@/components/activity-list/activity-list-v3/TopHeaderFilter";
import TourProperties from "@/components/activity-list/activity-list-v3/ActivityProperties";
import DropdownSelelctBar from "@/components/activity-list/common/DropdownSelelctBar";
import MapPropertyFinder from "@/components/activity-list/common/MapPropertyFinder";
import Pagination from "@/components/activity-list/common/Pagination";
import Sidebar from "@/components/activity-list/activity-list-v3/Sidebar";
import ActivityProperties1 from "@/components/activity-list/activity-list-v3/ActivityProperties";
import { useState, useEffect, useMemo } from "react";
import React from "react";

// Add this function to format time from 24h to 12h format
const formatTime = (timeString) => {
  if (!timeString) return "";
  const timeParts = timeString.split(":");
  if (timeParts.length < 2) return timeString;

  const hours = parseInt(timeParts[0], 10);
  const minutes = timeParts[1];
  const period = hours >= 12 ? "PM" : "AM";
  const displayHours = hours % 12 || 12;

  return `${displayHours}:${minutes} ${period}`;
};
import { useSelector, useDispatch } from "react-redux";

import {
  setSelectedVehicle,
  setMode,
  fetchVehicleDetails,
  setSelectedPort,
  fetchVehicles,
  fetchZoneVehicles,
} from "@/slice/localtour/Localslice";

import MetaComponent from "@/components/common/MetaComponent";
import CustomStepper from "@/components/common/sub_common/CustomStepper";
import TourStatus from "@/components/common/sub_common/TourStatus";
import { FaAngleUp, FaAngleDown } from "react-icons/fa";
import { fetchViewDetails } from "@/slice/common/ViewDetails";
import {
  fetchLocalZone,
  setPicktype,
  setSelectbooking,
} from "@/slice/localtour/Localslice";
// Import Material UI icons
import {
  MdHotel,
  MdAttractions,
  MdRestaurant,
  MdAirportShuttle,
  MdDirectionsBoat,
  MdDirectionsCar,
  MdAccessTime,
  MdTour,
  MdArrowForward,
} from "react-icons/md";

// Import Material UI icons
import CalendarMonthIcon from "@mui/icons-material/CalendarMonth";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import HotelIcon from "@mui/icons-material/Hotel";
import PersonIcon from "@mui/icons-material/Person";
import ConfirmationNumberIcon from "@mui/icons-material/ConfirmationNumber";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import FastfoodIcon from "@mui/icons-material/Fastfood";
import RoomServiceIcon from "@mui/icons-material/RoomService";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import AirportShuttleIcon from "@mui/icons-material/AirportShuttle";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import TourIcon from "@mui/icons-material/Tour";

const metadata = {
  title:
    "Local Tour Vehicle List || Travclicks - Travel Technology Transformed",
  description: "Travclicks - Travel Technology Transformed",
};

// Add this function before the ActivityListPage3 component
const getRowBackgroundColor = (serviceType) => {
  switch (serviceType) {
    case "Hotel":
      return "rgba(33, 150, 243, 0.06)"; // Professional light blue
    case "Attraction":
      return "rgba(76, 175, 80, 0.06)"; // Professional light green
    case "Attraction Package":
      return "rgba(175, 76, 114, 0.06)"; // Professional light green
    case "Restaurant":
      return "rgba(244, 67, 54, 0.06)"; // Professional light red
    case "Entry Port":
      return "rgba(126, 87, 194, 0.06)"; // Professional light violet
    case "Exit Port":
      return "rgba(255, 152, 0, 0.06)"; // Professional light orange
    case "Travel Point":
      return "rgba(0, 188, 212, 0.06)"; // Professional light cyan
    case "Travel Hourly":
      return "rgba(255, 87, 34, 0.06)"; // Professional light deep orange
    case "Travel Zone":
      return "rgba(156, 39, 176, 0.06)"; // Professional light purple for zone
    case "Guide":
      return "rgba(171, 71, 188, 0.06)"; // Professional light purple
    default:
      return "transparent";
  }
};

// Add border color function
const getRowBorderColor = (serviceType) => {
  switch (serviceType) {
    case "Hotel":
      return "#2196F3"; // Blue
    case "Attraction":
      return "#4CAF50"; // Green
    case "Attraction Package":
      return "#7E57C2"; // Green
    case "Restaurant":
      return "#F44336"; // Red
    case "Entry Port":
      return "#7E57C2"; // Violet
    case "Exit Port":
      return "#FF9800"; // Orange
    case "Travel Point":
      return "#00BCD4"; // Cyan
    case "Travel Hourly":
      return "#FF5722"; // Deep Orange
    case "Travel Zone":
      return "#9C27B0"; // Purple
    case "Guide":
      return "#AB47BC"; // Purple
    default:
      return "#CCCCCC"; // Default gray
  }
};

// Add this function to map service types to their respective icons
const getServiceTypeIcon = (serviceType) => {
  switch (serviceType) {
    case "Hotel":
      return <HotelIcon className="text-blue-1" style={{ fontSize: 22 }} />;
    case "Attraction":
      return (
        <ConfirmationNumberIcon
          className="text-green-1"
          style={{ fontSize: 22 }}
        />
      );
    case "Attraction Package":
      return (
        <ConfirmationNumberIcon
          className="text-purple-1"
          style={{ fontSize: 22 }}
        />
      );
    case "Restaurant":
      return <RestaurantIcon className="text-red-1" style={{ fontSize: 22 }} />;
    case "Entry Port":
      return (
        <AirportShuttleIcon
          className="text-purple-1"
          style={{ fontSize: 22 }}
        />
      );
    case "Exit Port":
      return (
        <AirportShuttleIcon
          className="text-orange-1"
          style={{ fontSize: 22 }}
        />
      );
    case "Guide":
      return <TourIcon className="text-purple-2" style={{ fontSize: 22 }} />;
    case "Travel Point":
      return (
        <DirectionsCarIcon className="text-cyan-1" style={{ fontSize: 22 }} />
      );
    case "Travel Hourly":
      return (
        <DirectionsCarIcon
          className="text-deep-orange-1"
          style={{ fontSize: 22 }}
        />
      );
    case "Travel Zone":
      return (
        <MdDirectionsBoat className="text-purple-2" style={{ fontSize: 22 }} />
      );
    default:
      return (
        <AccessTimeIcon className="text-gray-1" style={{ fontSize: 22 }} />
      );
  }
};

const ActivityListPage3 = () => {
  const dispatch = useDispatch();
  //const vehicles = useSelector((state) => state.pickupDrop.vehicles);
  const vehicles = useSelector((state) => state.localtour.vehicles);
  console.log("vehicles69", vehicles);
  const status = useSelector((state) => state.localtour.status);
  const PriceHide = useSelector((state) => state.auth.PriceHide);


  // Add this to check if vehicles is an array or an error object
  const isVehiclesArray = Array.isArray(vehicles);
  const vehiclesError = !isVehiclesArray && vehicles?.message;

  const Location = useSelector((state) => state.bookings.searchLocation);
  console.log("countryNames", Location);
  const mode = useSelector((state) => state.localtour.mode);
  const priceMode = useSelector((state) => state.localtour.pricemode);
  const exchangeRate = useSelector((state) => state.auth.exchangeRate);
  const bookingType = useSelector((state) => state.common.bookingType);
  const id = useSelector((state) => state.hotels.id);
  const [currentPage, setCurrentPage] = useState(1);
  const itemsPerPage = 5; // Adjust as needed
  const [hasMore, setHasMore] = useState(true);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  //const [sortOrder, setSortOrder] = useState("asc");
  const [sortedVehicles, setSortedVehicles] = useState([]);
  //const [filteredVehicles, setFilteredVehicles] = useState([]);
  const [priceRange, setPriceRange] = useState({ min: 0, max: 500 });
  const [sortOrder, setSortOrder] = useState("asc");
  // const [selectedModes, setSelectedModes] = useState({});
  const [isFilterVisible, setIsFilterVisible] = useState(false);
  const selectedPort = useSelector((state) => state.localtour.selectedPort);
  const viewDetails = useSelector((state) => state.viewDetails.bookings);
  console.log("viewDetails", viewDetails);

  // Function to check if the "Add Transfer" button should be displayed
  const shouldShowAddTransferButton = () => {
    // SIMPLIFIED CONDITION - just check if travel_point exists and has items
    const hasTravelPoint =
      ((viewDetails?.travel_point && viewDetails.travel_point.length > 0) ||
        (viewDetails?.guide && viewDetails.guide.length > 0) ||
        (viewDetails?.travel_hourly && viewDetails.travel_hourly.length > 0) ||
        (viewDetails?.entry_port && viewDetails.entry_port.length > 0) ||
        (viewDetails?.exit_port && viewDetails.exit_port.length > 0) ||
        (viewDetails?.local_transport && viewDetails.local_transport.length > 0)) &&
      (!viewDetails?.hotel || viewDetails?.hotel?.length === 0) &&
      (!viewDetails?.attraction || viewDetails?.attraction?.length === 0) &&
      (!viewDetails?.attraction_package || viewDetails?.attraction_package?.length === 0) &&
      (!viewDetails?.restaurant || viewDetails?.restaurant?.length === 0);

    // console.log("DEBUG - Simplified Button Check:", {
    //   hasTravelPoint,
    //   travel_point: viewDetails?.travel_point,
    //   "travel_point length": viewDetails?.travel_point?.length
    // });

    // Initially just return if we have travel_point data
    return hasTravelPoint;

    // Original complete condition (commented out for now)
    /*
    // Check if we have any travel-related data
    const hasTravelData = 
      (viewDetails?.travel_point && viewDetails.travel_point.length > 0) || 
      (viewDetails?.guide && viewDetails.guide.length > 0) || 
      (viewDetails?.travel_hourly && viewDetails.travel_hourly.length > 0) || 
      (viewDetails?.entry_port && viewDetails.entry_port.length > 0) || 
      (viewDetails?.exit_port && viewDetails.exit_port.length > 0);
    
    // Check if we don't have any hotel, attraction, or restaurant data
    const hasNoHotelData = !viewDetails?.hotel || viewDetails.hotel.length === 0;
    const hasNoAttractionData = !viewDetails?.attraction || viewDetails.attraction.length === 0;
    const hasNoRestaurantData = !viewDetails?.restaurant || viewDetails.restaurant.length === 0;
    
    // Log all the conditions for debugging
    console.log("DEBUG - Button Conditions:", {
      hasTravelData,
      hasNoHotelData,
      hasNoAttractionData,
      hasNoRestaurantData,
      travel_point: viewDetails?.travel_point
    });
    
    return hasTravelData && hasNoHotelData && hasNoAttractionData && hasNoRestaurantData;
    */
  };

  // Store the result in a variable for use in JSX
  const showAddTransferButton = shouldShowAddTransferButton();

  console.log("viewDetails", viewDetails);
  const [showBookingTable, setShowBookingTable] = useState(true);
  const [selectedBooking, setSelectedBooking] = useState(null);
  const zoneType = useSelector((state) => state.localtour.zonetype);

  useEffect(() => {
    dispatch(fetchViewDetails({ tour_id: id }));
  }, [id]);
  useEffect(() => {
    if (
      viewDetails === null ||
      (Array.isArray(viewDetails) && viewDetails.length === 0)
    ) {
      setShowBookingTable(false);
    } else {
      setShowBookingTable(true);
    }
  }, [viewDetails]);

  useEffect(() => {});
  // Function to organize bookings by date
  const getBookingsByDate = () => {
    if (!viewDetails) return {};

    const bookingsByDate = {};

    // Process hotel bookings
    if (viewDetails.entry_port) {
      viewDetails.entry_port.forEach((entryport) => {
        const date = entryport.bookingDate;
        if (!bookingsByDate[date]) bookingsByDate[date] = [];
        bookingsByDate[date].push({
          ...entryport,
          serviceType: "Entry Port",
          serviceName: entryport.vehicles_name || "Unknown Entry Port", // ✅ Use the correct field for serviceName
          serviceImage: entryport.image || "",
          price: entryport.totalPrice, // ✅ Use the correct field for price
        });
      });
    }

    if (viewDetails.hotel) {
      viewDetails.hotel.forEach((hotel) => {
        if (Array.isArray(hotel.bookingDate) && hotel.bookingDate.length >= 2) {
          // Get check-in and check-out dates
          const checkInDate = new Date(hotel.bookingDate[0]);
          const checkOutDate = new Date(hotel.bookingDate[1]);

          // Generate all dates between check-in and check-out (excluding check-out)
          const currentDate = new Date(checkInDate);
          while (currentDate < checkOutDate) {
            const dateString = currentDate.toISOString().split("T")[0]; // Format as YYYY-MM-DD

            if (!bookingsByDate[dateString]) bookingsByDate[dateString] = [];
            bookingsByDate[dateString].push({
              ...hotel,
              serviceType: "Hotel",
              serviceName: hotel.hotelDetails?.hotel_name || "Unknown Hotel",
              serviceImage: hotel.hotelDetails?.image || "",
              price: hotel.totalPrice,
            });

            // Move to next day
            currentDate.setDate(currentDate.getDate() + 1);
          }
        }
      });
    }

    // Process attraction bookings
    if (viewDetails.attraction) {
      viewDetails.attraction.forEach((attraction) => {
        const date = attraction.bookingDate;
        if (!bookingsByDate[date]) bookingsByDate[date] = [];
        bookingsByDate[date].push({
          ...attraction,
          serviceType: "Attraction",
          serviceName: attraction.AttractionName || "Unknown Attraction",
          serviceImage: attraction.service_details?.master_image || "",
          price: attraction.totalPrice,
        });
      });
    }
    if (viewDetails.attraction_package) {
      viewDetails.attraction_package.forEach((attraction_package) => {
        const date = attraction_package.bookingDate;
        if (!bookingsByDate[date]) bookingsByDate[date] = [];
        bookingsByDate[date].push({
          ...attraction_package,
          serviceType: "Attraction Package",
          serviceName: attraction_package.AttractionName || "Unknown Attraction Package",
          serviceImage: attraction_package.service_details?.master_image || "",
          price: attraction_package.totalPrice,
        });
      });
    }
    // Process restaurant bookings
    if (viewDetails.restaurant) {
      viewDetails.restaurant.forEach((restaurant) => {
        const date = restaurant.bookingDate;
        if (!bookingsByDate[date]) bookingsByDate[date] = [];
        bookingsByDate[date].push({
          ...restaurant,
          serviceType: "Restaurant",
          serviceName: restaurant.restaurantName || "Unknown Restaurant",
          serviceImage: restaurant.service_details?.master_image || "",
          price: restaurant.totalPrice, // ✅ Use the correct field for price
        });
      });
    }

    // Process transfer bookings

    // Process transfer bookings
    if (viewDetails.guide) {
      viewDetails.guide.forEach((guide) => {
        const date = guide.bookingDate;
        if (!bookingsByDate[date]) bookingsByDate[date] = [];
        bookingsByDate[date].push({
          ...guide,
          serviceType: "Guide",
          serviceName: guide.guide_name || "Unknown Guide", // ✅ Use the correct field for serviceName
          serviceImage: guide.image || "",
          price: guide.totalPrice, // ✅ Use the correct field for price
        });
      });
    }

    if (viewDetails.travel_point) {
      viewDetails.travel_point.forEach((travel_point) => {
        const date = travel_point.bookingDate;
        if (!bookingsByDate[date]) bookingsByDate[date] = [];
        bookingsByDate[date].push({
          ...travel_point,
          serviceType: "Travel Point", // ✅ Use the correct field for serviceName
          serviceName: travel_point.vehicles_name || "Unknown Travel Point", // ✅ Use the correct field for serviceName
          serviceImage: travel_point.image || "",
          price: travel_point.totalPrice, // ✅ Use the correct field for price
        });
      });
    }
    // Process transfer bookings
    if (viewDetails.travel_hourly) {
      viewDetails.travel_hourly.forEach((travel_hourly) => {
        const date = travel_hourly.bookingDate;
        if (!bookingsByDate[date]) bookingsByDate[date] = [];
        bookingsByDate[date].push({
          ...travel_hourly,
          serviceType: "Travel Hourly", // ✅ Use the correct field for serviceName
          serviceName: travel_hourly.vehicles_name || "Unknown Travel Hourly", // ✅ Use the correct field for serviceName
          serviceImage: travel_hourly.image || "",
          price: travel_hourly.totalPrice, // ✅ Use the correct field for price
        });
      });
    }

    // Process zone bookings - moved outside travel_hourly block
    if (viewDetails.local_transport && viewDetails.local_transport.length > 0) {
      viewDetails.local_transport.forEach((local_transport) => {
        const date = local_transport.bookingDate;
        if (!bookingsByDate[date]) bookingsByDate[date] = [];
        bookingsByDate[date].push({
          ...local_transport,
          serviceType: "Travel Zone", // ✅ Use the correct field for serviceName
          serviceName: local_transport.vehicles_name || "Unknown Travel Zone", // ✅ Use the correct field for serviceName
          serviceImage: local_transport.image || "",
          price: local_transport.totalPrice, // ✅ Use the correct field for price
        });
      });
    }
    if (viewDetails.exit_port) {
      viewDetails.exit_port.forEach((exitport) => {
        const date = exitport.bookingDate;
        if (!bookingsByDate[date]) bookingsByDate[date] = [];
        bookingsByDate[date].push({
          ...exitport,
          serviceType: "Exit Port",
          serviceName: exitport.vehicles_name || "Unknown Exit Port", // ✅ Use the correct field for serviceName
          serviceImage: exitport.image || "",
          price: exitport.totalPrice, // ✅ Use the correct field for price
        });
      });
    }

    // Add more service types as needed (restaurant, etc.)

    return bookingsByDate;
  };

  const handleBookTransfer = (booking, id, type) => {
    dispatch(fetchLocalZone({ id, type }));
    dispatch(setSelectbooking(booking));
    dispatch(setPicktype(type));
    setShowBookingTable(false);
  };

  useEffect(() => {
    // ✅ Ensure all vehicles get "dmc" as default unless priceMode is "marketplace"
    const defaultMode =
      priceMode === "checked"
        ? "dmc"
        : "travclicks" || priceMode === "non-checked"
        ? "dmc"
        : "travclicks";

    const initialModes = vehicles.reduce((acc, vehicle) => {
      acc[vehicle.id] = { mode: defaultMode, dmcId: vehicle.dmc_id };
      return acc;
    }, {});

    dispatch(setMode(initialModes)); // ✅ Set mode for all vehicles
  }, [vehicles, priceMode, dispatch]);

  // Memoized list of prices from vehicles
  const getPriceField = (vehicle) => {
    const vehicleMode = mode[vehicle.id]?.mode || "dmc"; // Default mode
    return vehicleMode === "dmc"
      ? vehicle.dmc_sharable_price
        ? parseFloat(vehicle.dmc_sharable_price)
        : vehicle.dmc_private_price
        ? parseFloat(vehicle.dmc_private_price)
        : 0
      : vehicle.trav_sharable_price
      ? parseFloat(vehicle.trav_sharable_price)
      : vehicle.trav_private_price
      ? parseFloat(vehicle.trav_private_price)
      : 0;
  };

  const hourlyPrices = useMemo(
    () =>
      isVehiclesArray ? vehicles.map((vehicle) => getPriceField(vehicle)) : [],
    [vehicles, mode, isVehiclesArray] // ✅ Use Redux mode instead of selectedModes
  );

  // Update sortedVehicles when vehicles change
  useEffect(() => {
    setSortedVehicles(isVehiclesArray ? [...vehicles] : []); // Initialize sorted guides
  }, [vehicles, isVehiclesArray]);

  // Sorting function
  const handleSort = () => {
    const sorted = [...vehicles]
      .map((vehicle) => ({
        ...vehicle,
        price: getPriceField(vehicle), // Use dynamic price field
      }))
      .sort((a, b) =>
        sortOrder === "asc" ? a.price - b.price : b.price - a.price
      );

    setSortedVehicles(sorted);
    setSortOrder(sortOrder === "asc" ? "desc" : "asc");
  };

  // Update price range when vehicles change
  useEffect(() => {
    if (hourlyPrices.length > 0) {
      setPriceRange({
        min: Math.min(...hourlyPrices),
        max: Math.max(...hourlyPrices),
      });
    }
  }, [hourlyPrices]);

  // Filter guides based on selected price range
  const filteredVehicles = sortedVehicles.filter((vehicle) => {
    const rate = getPriceField(vehicle);
    return rate >= priceRange.min && rate <= priceRange.max;
  });

  // Ensure filtering happens *after* priceRange updates
  // useEffect(() => {
  //   if (priceRange.min !== undefined && priceRange.max !== undefined) {
  //     console.log("Before filtering - sortedVehicles:", sortedVehicles);
  //     console.log("Current price range:", priceRange);

  //     const newFilteredVehicles = sortedVehicles.filter(
  //       (vehicle) =>
  //         vehicle.dmcDayPrice >= priceRange.min &&
  //         vehicle.dmcDayPrice <= priceRange.max
  //     );

  //     setFilteredVehicles(newFilteredVehicles);
  //     console.log("After filtering - filteredVehicles:", newFilteredVehicles);
  //   }
  // }, [sortedVehicles, priceRange]); // Trigger when sortedVehicles updates
  // Runs when either `sortedVehicles` or `priceRange` changes

  useEffect(() => {
    console.log("Updated Price Range:", priceRange);
  }, [priceRange]);
  useEffect(() => {
    console.log("Updated filteredVehicles:", filteredVehicles);
  }, [filteredVehicles]);

  // Function to handle page change
  const handlePageChange = (page) => {
    setCurrentPage(page);
  };

  // Function to handle guide selection
  const handleVehicleClick = (vehicles, navigate) => {
    const vehicleMode = mode[vehicles.id]?.mode || "dmc"; // ✅ Get correct mode
    const dmcId = mode[vehicles.id]?.dmcId || vehicles.dmc_id; // ✅ Get correct dmcId
    dispatch(setSelectedVehicle({ id: vehicles.id, mode: vehicleMode, dmcId })); // ✅ Store only selected vehicle's ID & mode
    dispatch(
      fetchVehicleDetails({
        city: vehicles.city,
        country: vehicles.country,
        type: zoneType,
      })
    )
      .unwrap() // Unwrap the promise to handle the payload directly
      .then((data) => {
        navigate(`/dashboard/db-dashboard/activity-single-2`, {
          state: { vehicles: data },
        });
      });
  };

  // Function to handle mode change and update Redux
  const handleModeChange = (id, newMode, dmcId) => {
    dispatch(setMode({ [id]: { mode: newMode, dmcId } })); // ✅ Update Redux mode for specific vehicle
  };

  // Reset current page when vehicles are cleared (new search)
  useEffect(() => {
    if (vehicles.length === 0) {
      setCurrentPage(1);
      setHasMore(true);
    }
  }, [vehicles.length]);

  // Scroll detection for infinite scroll
  useEffect(() => {
    const handleScroll = () => {
      if (
        window.innerHeight + document.documentElement.scrollTop >=
        document.documentElement.offsetHeight - 1000 && // Load more when 1000px from bottom
        !isLoadingMore &&
        hasMore &&
        status !== "loading" &&
        vehicles.length > 0 // Only load more if we have vehicles
      ) {
        setCurrentPage(prev => prev + 1);
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, [isLoadingMore, hasMore, status, vehicles.length]);

  // Load more effect for infinite scroll
  useEffect(() => {
    if (currentPage > 1 && vehicles.length > 0) {
      setIsLoadingMore(true);
      // Dispatch action to fetch more vehicles based on selection type
      const start = (currentPage - 1) * itemsPerPage;
      
      if (selectedPort === "Local Transfer") {
        // For zone vehicles, use fetchZoneVehicles
        dispatch(fetchZoneVehicles({ start, limit: itemsPerPage })).then((result) => {
          setIsLoadingMore(false);
          // Check if we have more data based on the response
          if (result.payload && Array.isArray(result.payload)) {
            if (result.payload.length < itemsPerPage) {
              setHasMore(false);
            }
          } else {
            setHasMore(false);
          }
        }).catch(() => {
          setIsLoadingMore(false);
          setHasMore(false);
        });
      } else {
        // For regular vehicles, use fetchVehicles
        dispatch(fetchVehicles({ start, limit: itemsPerPage })).then((result) => {
          setIsLoadingMore(false);
          // Check if we have more data based on the response
          if (result.payload && Array.isArray(result.payload)) {
            if (result.payload.length < itemsPerPage) {
              setHasMore(false);
            }
          } else {
            setHasMore(false);
          }
        }).catch(() => {
          setIsLoadingMore(false);
          setHasMore(false);
        });
      }
    }
  }, [currentPage, vehicles.length, itemsPerPage, dispatch, selectedPort]);

  const displayedVehicles = filteredVehicles.filter((vehicle) => {
    const dmcPrice = vehicle.dmc_sharable_price
      ? parseFloat(vehicle.dmc_sharable_price) * exchangeRate
      : vehicle.dmc_private_price
      ? parseFloat(vehicle.dmc_private_price) * exchangeRate
      : 0;
    const travClicksPrice = vehicle.trav_sharable_price
      ? parseFloat(vehicle.trav_sharable_price) * exchangeRate
      : vehicle.trav_private_price
      ? parseFloat(vehicle.trav_private_price) * exchangeRate
      : 0;

    if (priceMode === "checked" && dmcPrice <= 0) {
      return false;
    }

    if (
      (dmcPrice === 0 && travClicksPrice === 0) ||
      (bookingType === "enquiry" && dmcPrice === 0)
    ) {
      return false;
    }

    return true;
  });

  const displayedVehicleCount = displayedVehicles.length;

  return (
    <>
      <MetaComponent meta={metadata} />
      {/* End Page Title */}
      <div className="header-margin">
        <CustomStepper />
        <TourStatus />
      </div>

      {showBookingTable && (  viewDetails?.attraction?.length > 0 || viewDetails?.attraction_package?.length > 0 || viewDetails?.restaurant?.length > 0 || viewDetails?.hotel?.length > 0 || viewDetails?.guide?.length > 0 || viewDetails?.travel_point?.length > 0 || viewDetails?.travel_hourly?.length > 0 || viewDetails?.entry_port?.length > 0 || viewDetails?.exit_port?.length > 0) ? (
        <section className="layout-pt-md layout-pb-md">
          <div className="container-xxl">
            {/* Add debug panel */}
            {/* <div className="row mb-20">
              <div className="col-12">
                <div className="border border-danger rounded-4 p-20 bg-light-2">
                  <h3 className="text-18 fw-500 text-danger mb-10">Debug Information</h3>
                  <div className="text-14">
                    <p><strong>viewDetails.travel_point:</strong> {JSON.stringify(viewDetails?.travel_point)}</p>
                    <p><strong>travel_point length:</strong> {viewDetails?.travel_point?.length || 0}</p>
                    <p><strong>Has hotel:</strong> {Boolean(!viewDetails?.hotel || viewDetails?.hotel?.length === 0).toString()}</p>
                    <p><strong>Has attraction:</strong> {Boolean(!viewDetails?.attraction || viewDetails?.attraction?.length === 0).toString()}</p>
                    <p><strong>Has restaurant:</strong> {Boolean(!viewDetails?.restaurant || viewDetails?.restaurant?.length === 0).toString()}</p>
                    <p><strong>Button should show:</strong> {Boolean(
                      (viewDetails?.travel_point?.length > 0 || 
                       viewDetails?.guide?.length > 0 || 
                       viewDetails?.travel_hourly?.length > 0 || 
                       viewDetails?.entry_port?.length > 0 || 
                       viewDetails?.exit_port?.length > 0) && 
                      (!viewDetails?.hotel || viewDetails?.hotel?.length === 0) && 
                      (!viewDetails?.attraction || viewDetails?.attraction?.length === 0) && 
                      (!viewDetails?.restaurant || viewDetails?.restaurant?.length === 0)
                    ).toString()}</p>
                  </div>
                </div>
              </div>
            </div> */}
            {/* End debug panel */}

            <div className="row">
              <div className="col-12">
                <div className="text-center mb-30">
                  <h2 className="text-30 fw-600">Your Bookings</h2>
                </div>

                {Object.entries(getBookingsByDate()).map(([date, bookings]) => (
                  <div key={date} className="mb-40">
                    <h3 className="text-22 fw-500 mb-20">
                      Bookings for{" "}
                      {new Date(date).toLocaleDateString("en-US", {
                        weekday: "long",
                        year: "numeric",
                        month: "long",
                        day: "numeric",
                      })}
                    </h3>
                    <div className="overflow-auto">
                      <table className="table1 table-bordered table-hover shadow-sm">
                        <thead className="bg-blue-1 text-white">
                          <tr>
                            <th className="px-20 py-15">
                              <div className="d-flex items-center">
                                <HotelIcon
                                  style={{ fontSize: 20, marginRight: 8 }}
                                />
                                Service Type
                              </div>
                            </th>
                            <th className="px-20 py-15">
                              <div className="d-flex items-center">
                                <TourIcon
                                  style={{ fontSize: 20, marginRight: 8 }}
                                />
                                Name
                              </div>
                            </th>
                            <th className="px-20 py-15">
                              <div className="d-flex items-center">
                                <RestaurantIcon
                                  style={{ fontSize: 20, marginRight: 8 }}
                                />
                                Details
                              </div>
                            </th>
                            <th className="px-20 py-15">
                              <div className="d-flex items-center">
                                <AccessTimeIcon
                                  style={{ fontSize: 20, marginRight: 8 }}
                                />
                                Time
                              </div>
                            </th>
                            <th className="px-20 py-15">
                              <div className="d-flex items-center">
                                <FastfoodIcon
                                  style={{ fontSize: 20, marginRight: 8 }}
                                />
                                Price
                              </div>
                            </th>
                            <th className="px-20 py-15">
                              <div className="d-flex items-center">
                                <DirectionsCarIcon
                                  style={{ fontSize: 20, marginRight: 8 }}
                                />
                                Transport
                              </div>
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          {bookings.map((booking, index) => (
                            <tr
                              key={index}
                              className="border-bottom-light booking-row"
                              data-service-type={booking.serviceType}
                              style={{
                                backgroundColor: getRowBackgroundColor(
                                  booking.serviceType
                                ),
                                borderLeft: `4px solid ${getRowBorderColor(
                                  booking.serviceType
                                )}`,
                              }}
                            >
                              <td className="px-20 py-15">
                                <div className="d-flex items-center">
                                  {getServiceTypeIcon(booking.serviceType)}
                                  <div className="ml-10 fw-500">
                                    {booking.serviceType}
                                  </div>
                                </div>
                              </td>
                              <td className="px-20 py-15">
                                <div className="d-flex items-center">
                                  {booking.serviceImage && (
                                    <img
                                      src={booking.serviceImage}
                                      alt={booking.serviceName}
                                      className="size-40 rounded-4 object-cover mr-10"
                                    />
                                  )}
                                  <span className="fw-500">
                                    {booking.serviceName}
                                  </span>
                                </div>
                              </td>
                              <td className="px-20 py-15">
                                {booking.serviceType === "Hotel" && (
                                  <div className="d-flex flex-column">
                                    <div className="d-flex items-center mb-10">
                                      <CalendarMonthIcon
                                        className="text-blue-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Check-in:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.bookingDate[0]}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center">
                                      <CalendarMonthIcon
                                        className="text-blue-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Check-out:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.bookingDate[1]}
                                      </span>
                                    </div>
                                  </div>
                                )}
                                {booking.serviceType === "Attraction" && (
                                  <div className="d-flex flex-column">
                                    <div className="d-flex items-center mb-10">
                                      <PersonIcon
                                        className="text-green-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Guests:
                                      </span>
                                      <span className="text-15">
                                        Adults: {booking.adultCount}, Children:{" "}
                                        {booking.childCount}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center">
                                      <ConfirmationNumberIcon
                                        className="text-green-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Ticket:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.ticketName}
                                      </span>
                                    </div>
                                  </div>
                                )}
                                {booking.serviceType === "Attraction Package" && (
                                  <div className="d-flex flex-column">
                                    <div className="d-flex items-center mb-10">
                                      <PersonIcon
                                        className="text-green-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Guests:
                                      </span>
                                      <span className="text-15">
                                        Adults: {booking.adultCount}, Children:{" "}
                                        {booking.childCount}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center">
                                      <ConfirmationNumberIcon
                                        className="text-green-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Ticket:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.ticketName}
                                      </span>
                                    </div>
                                  </div>
                                )}
                                {booking.serviceType === "Restaurant" && (
                                  <div className="d-flex flex-column">
                                    <div className="d-flex items-center mb-10">
                                      <PersonIcon
                                        className="text-red-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Guests:
                                      </span>
                                      <span className="text-15">
                                        Adults: {booking.adultCount}, Children:{" "}
                                        {booking.childCount}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center mb-10">
                                      <RestaurantIcon
                                        className="text-red-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Meal Type:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.mealType || "N/A"}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center">
                                      <RoomServiceIcon
                                        className="text-red-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Description:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.mealSpecificType || "N/A"}
                                      </span>
                                    </div>
                                  </div>
                                )}
                                {booking.serviceType === "Entry Port" && (
                                  <div className="d-flex flex-column">
                                    <div className="d-flex items-center mb-10">
                                      <LocationOnIcon
                                        className="text-purple-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Pick Up:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.entrypickup}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center mb-10">
                                      <LocationOnIcon
                                        className="text-purple-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Drop Off:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.entrydropoff}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center mb-10">
                                      <AirportShuttleIcon
                                        className="text-purple-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">Type:</span>{" "}
                                      <span className="text-15">
                                        {booking.type}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center">
                                      <PersonIcon
                                        className="text-purple-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Guests:
                                      </span>
                                      <span className="text-15">
                                        Adults: {booking.adults}, Children:{" "}
                                        {booking.children}
                                      </span>
                                    </div>
                                  </div>
                                )}
                                {booking.serviceType === "Exit Port" && (
                                  <div className="d-flex flex-column">
                                    <div className="d-flex items-center mb-10">
                                      <LocationOnIcon
                                        className="text-orange-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Pick Up:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.exitpickup}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center mb-10">
                                      <LocationOnIcon
                                        className="text-orange-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Drop Off:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.exitdropoff}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center mb-10">
                                      <AirportShuttleIcon
                                        className="text-orange-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">Type:</span>{" "}
                                      <span className="text-15">
                                        {booking.type}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center">
                                      <PersonIcon
                                        className="text-orange-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Guests:
                                      </span>
                                      <span className="text-15">
                                        Adults: {booking.adults}, Children:{" "}
                                        {booking.children}
                                      </span>
                                    </div>
                                  </div>
                                )}
                                {booking.serviceType === "Travel Point" && (
                                  <div className="d-flex flex-column">
                                    <div className="d-flex items-center mb-10">
                                      <LocationOnIcon
                                        className="text-cyan-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Pick Up:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.entrypickup}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center mb-10">
                                      <LocationOnIcon
                                        className="text-cyan-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Drop Off:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.entrydropoff}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center">
                                      <DirectionsCarIcon
                                        className="text-cyan-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">Type:</span>{" "}
                                      <span className="text-15">
                                        {booking.type}
                                      </span>
                                    </div>
                                  </div>
                                )}
                                {booking.serviceType === "Travel Hourly" && (
                                  <div className="d-flex flex-column">
                                    <div className="d-flex items-center mb-10">
                                      <LocationOnIcon
                                        className="text-deep-orange-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Pick Up:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.entrypickup}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center">
                                      <DirectionsCarIcon
                                        className="text-deep-orange-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">Type:</span>{" "}
                                      <span className="text-15">
                                        {booking.type}
                                      </span>
                                    </div>
                                  </div>
                                )}
                                {booking.serviceType === "Travel Zone" && (
                                  <div className="d-flex flex-column">
                                    <div className="d-flex items-center mb-10">
                                      <LocationOnIcon
                                        className="text-purple-2 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Pick Up:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.entrypickup}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center mb-10">
                                      <LocationOnIcon
                                        className="text-cyan-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Drop Off:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.entrydropoff}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center">
                                      <DirectionsCarIcon
                                        className="text-cyan-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">Type:</span>{" "}
                                      <span className="text-15">
                                        {booking.type}
                                      </span>
                                    </div>
                                  </div>
                                )}

                                {booking.serviceType === "Guide" && (
                                  <div className="d-flex flex-column">
                                    <div className="d-flex items-center mb-10">
                                      <LocationOnIcon
                                        className="text-purple-2 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">
                                        Pick Up:
                                      </span>{" "}
                                      <span className="text-15">
                                        {booking.entrypickup}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center">
                                      <TourIcon
                                        className="text-purple-2 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="fw-500 mr-5">Type:</span>{" "}
                                      <span className="text-15">
                                        {booking.type}
                                      </span>
                                    </div>
                                  </div>
                                )}
                              </td>
                              <td className="px-20 py-15">
                                {booking.serviceType === "Hotel" && (
                                  <div className="d-flex flex-column">
                                    <div className="d-flex items-center mb-10">
                                      <AccessTimeIcon
                                        className="text-blue-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="text-15">
                                        {/* { {booking.ent}{" "} */}
                                        Check In Time:{" "}
                                        {formatTime(
                                          booking.hotelDetails?.checkInTime
                                        )}
                                      </span>
                                    </div>
                                    <div className="d-flex items-center">
                                      <AccessTimeIcon
                                        className="text-blue-1 mr-10"
                                        style={{ fontSize: 20 }}
                                      />
                                      <span className="text-15">
                                        Check Out Time:{" "}
                                        {formatTime(
                                          booking.hotelDetails?.checkOutTime
                                        )}
                                      </span>
                                    </div>
                                  </div>
                                )}
                                {booking.serviceType === "Attraction" && (
                                  <div>
                                    {booking.visitTime &&
                                      booking.visitTime
                                        .split("-")
                                        .map((time, i) => {
                                          // Convert 24hr format to AM/PM
                                          const [hours, minutes] =
                                            time.split(":");
                                          const h = parseInt(hours, 10);
                                          const ampm = h >= 12 ? "PM" : "AM";
                                          const hour = h % 12 || 12; // Convert 0 to 12 for 12 AM
                                          return (
                                            <div
                                              className="d-flex items-center mt-10"
                                              key={i}
                                            >
                                              <AccessTimeIcon
                                                className="text-green-1 mr-10"
                                                style={{ fontSize: 20 }}
                                              />
                                              <span className="text-15">
                                                {i === 0 ? "From: " : "To: "}
                                                {hour}:{minutes} {ampm}
                                              </span>
                                            </div>
                                          );
                                        })}
                                  </div>
                                )}
                                {booking.serviceType === "Attraction Package" && (
                                 <div>
                                 {booking.visitTime &&
                                   booking.visitTime
                                     .split("-")
                                     .map((time, i) => {
                                       // Convert 24hr format to AM/PM
                                       const [hours, minutes] =
                                         time.split(":");
                                       const h = parseInt(hours, 10);
                                       const ampm = h >= 12 ? "PM" : "AM";
                                       const hour = h % 12 || 12; // Convert 0 to 12 for 12 AM
                                       return (
                                         <div
                                           className="d-flex items-center mt-10"
                                           key={i}
                                         >
                                           <AccessTimeIcon
                                             className="text-green-1 mr-10"
                                             style={{ fontSize: 20 }}
                                           />
                                           <span className="text-15">
                                             {i === 0 ? "From: " : "To: "}
                                             {hour}:{minutes} {ampm}
                                           </span>
                                         </div>
                                       );
                                     })}
                               </div>
                                )}
                                {booking.serviceType === "Restaurant" && (
                                  <div className="d-flex items-center">
                                    <AccessTimeIcon
                                      className="text-red-1 mr-10"
                                      style={{ fontSize: 20 }}
                                    />
                                    <span className="text-15">
                                      {booking.visitTime}
                                    </span>
                                  </div>
                                )}
                                {booking.serviceType === "Entry Port" && (
                                  <div className="d-flex items-center">
                                    <AccessTimeIcon
                                      className="text-purple-1 mr-10"
                                      style={{ fontSize: 20 }}
                                    />
                                    <span className="text-15">
                                      {booking.entrytime}
                                    </span>
                                  </div>
                                )}
                                {booking.serviceType === "Exit Port" && (
                                  <div className="d-flex items-center">
                                    <AccessTimeIcon
                                      className="text-orange-1 mr-10"
                                      style={{ fontSize: 20 }}
                                    />
                                    <span className="text-15">
                                      {booking.entrytime}
                                    </span>
                                  </div>
                                )}
                                {booking.serviceType === "Travel Point" && (
                                  <div className="d-flex items-center">
                                    <AccessTimeIcon
                                      className="text-cyan-1 mr-10"
                                      style={{ fontSize: 20 }}
                                    />
                                    <span className="text-15">
                                      {booking.entrytime}
                                    </span>
                                  </div>
                                )}
                                {booking.serviceType === "Travel Hourly" && (
                                  <div className="d-flex items-center">
                                    <AccessTimeIcon
                                      className="text-deep-orange-1 mr-10"
                                      style={{ fontSize: 20 }}
                                    />
                                    <span className="text-15">
                                      {booking.entrytime}
                                    </span>
                                  </div>
                                )}
                                {booking.serviceType === "Travel Zone" && (
                                  <div className="d-flex items-center">
                                    <AccessTimeIcon
                                      className="text-cyan-1 mr-10"
                                      style={{ fontSize: 20 }}
                                    />
                                    <span className="text-15">
                                      {booking.entrytime}
                                    </span>
                                  </div>
                                )}
                                {booking.serviceType === "Guide" && (
                                  <div className="d-flex items-center">
                                    <AccessTimeIcon
                                      className="text-purple-2 mr-10"
                                      style={{ fontSize: 20 }}
                                    />
                                    <span className="text-15">
                                      {booking.entrytime}
                                    </span>
                                  </div>
                                )}
                              </td>
                              <td className="px-20 py-15">
                                <div className="fw-600 text-blue-1 d-flex justify-center align-center">
                                  <span
                                    className="text-18"
                                    style={{
                                      padding: "4px 12px",
                                      borderRadius: "20px",
                                      background: "rgba(53, 84, 209, 0.1)",
                                      display: "inline-flex",
                                      alignItems: "center",
                                      justifyContent: "center",
                                    }}
                                  >
                                    {PriceHide === "0" ? `$${booking.price}` : "Price Hidden"}
                                  </span>
                                </div>
                              </td>
                              <td className="px-20 py-15">
                                {(booking.serviceType === "Hotel" ||
                                  booking.serviceType === "Attraction" ||
                                  booking.serviceType === "Attraction Package" ||
                                  booking.serviceType === "Restaurant") && (
                                  <button
                                    className="button -md -dark-1 bg-blue-1 text-white d-flex items-center justify-center"
                                    onClick={() => {
                                      if (booking.serviceType === "Hotel") {
                                        handleBookTransfer(
                                          booking,
                                          booking.hotelDetails.hotel_id,
                                          "hotel"
                                        );
                                        dispatch(setSelectedPort("Local Transfer"));
                                      } else if (
                                        booking.serviceType === "Attraction"
                                      ) {
                                        handleBookTransfer(
                                          booking,
                                          booking.service_details.attraction_id,
                                          "attraction"
                                        );
                                        dispatch(setSelectedPort("Local Transfer"));
                                      } else if (
                                        booking.serviceType === "Attraction Package"
                                      ) {
                                        handleBookTransfer(
                                          booking,
                                          booking.package_attraction_id,
                                          "attraction_package"
                                        );
                                        dispatch(setSelectedPort("Local Transfer"));
                                      } else if (
                                        booking.serviceType === "Restaurant"
                                      ) {
                                        handleBookTransfer(
                                          booking,
                                          booking.service_details.restaurant_id,
                                          "restaurant"
                                        );
                                        dispatch(setSelectedPort("Local Transfer"));
                                      }
                                    }}
                                    style={{
                                      borderRadius: "6px",
                                      boxShadow: "0 2px 4px rgba(0,0,0,0.15)",
                                      transition: "all 0.2s ease",
                                    }}
                                  >
                                    <DirectionsCarIcon
                                      style={{ marginRight: 8, fontSize: 18 }}
                                    />
                                    Book Transfer
                                  </button>
                                )}
                              </td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>
                ))}
              </div>
              <div className="d-flex justify-center mt-20">
                {/* Always render this for debugging */}

                {/* Regular button with condition */}
                {showAddTransferButton && (
                  <button
                    className="button -md -dark-1 bg-blue-1 text-white d-flex items-center justify-center"
                    onClick={() => {
                      setShowBookingTable(false);
                    }}
                    style={{
                      borderRadius: "6px",
                      boxShadow: "0 2px 4px rgba(0,0,0,0.15)",
                      transition: "all 0.2s ease",
                    }}
                  >
                    <DirectionsCarIcon
                      style={{ marginRight: 8, fontSize: 18 }}
                    />
                    Add Transfer
                  </button>
                )}

                {/* Debug button that's always visible */}
              </div>
            </div>
          </div>
        </section>
      ) : (
        <>
          {/* header top margin */}
          {/* <Header11 /> */}
          {/* End Header 1 */}
          <section className="pt-40 pb-40 bg-light-2">
            <div
              className={
                selectedPort === "Hourly" ? "container" : "container-xxl padding-left-right-5"
              }
            >
              <div className="row" style={{ padding: "20px" }}>
                <div className="col-12">
                  <div className="text-center">
                    <h1 className="text-30 fw-600">Local Tour</h1>
                  </div>
                  {/* End text-center */}
                  <MainFilterSearchBox Location={Location} />
                </div>
                {/* End col-12 */}
              </div>
            </div>
          </section>
          <section className="layout-pt-md layout-pb-lg">
            <div className="container-xxl padding-left-right-5`">
              <div className="row y-gap-30" style={{ padding: "20px" }}>
                <div className="col-xl-3  d-xl-block">
                  <aside className="sidebar y-gap-40 xl:d-none">
                    <Sidebar
                      hourlyPrices={hourlyPrices}
                      priceRange={priceRange}
                      setPriceRange={setPriceRange}
                    />
                  </aside>
                </div>

                {/* Mobile Filter Section */}
                <div className="col-12 d-xl-none">
                  <div className="filter-mobile-section">
                    <button
                      className="filter-button"
                      onClick={() => setIsFilterVisible(!isFilterVisible)}
                      aria-expanded={isFilterVisible}
                    >
                      <div className="button-content">
                        <div className="left-content">
                          <i className="icon-filter text-20" />
                          <span>Filter</span>
                        </div>
                        <div className="arrow-icon">
                          {isFilterVisible ? (
                            <FaAngleUp className="arrow-icon-svg" />
                          ) : (
                            <FaAngleDown className="arrow-icon-svg" />
                          )}
                        </div>
                      </div>
                    </button>

                    {/* Collapsible Filter Content */}
                    <div
                      className={`filter-content ${
                        isFilterVisible ? "show" : ""
                      }`}
                    >
                      <aside className="sidebar y-gap-40 mb-30">
                        <Sidebar
                          hourlyPrices={hourlyPrices}
                          priceRange={priceRange}
                          setPriceRange={setPriceRange}
                        />
                      </aside>
                    </div>
                  </div>
                </div>
                {/* End col */}

                <div className="col-xl-9 ">
                  <TopHeaderFilter
                    Location={Location}
                    vehicles={vehicles}
                    handleSort={handleSort}
                    sortOrder={sortOrder}
                    filteredCount={displayedVehicleCount}
                  />
                  <div className="mt-30"></div>
                  {/* End mt--30 */}
                  <div className="row y-gap-30" style={{ marginTop: "0px", paddingLeft: "2rem" }}>
                    <ActivityProperties1
                      vehicles={filteredVehicles}
                      status={status}
                      onVehicleClick={handleVehicleClick}
                      selectedModes={mode} // ✅ Pass selected modes
                      setSelectedModes={handleModeChange} // ✅ Allow child to update mode
                      priceMode={priceMode}
                      hasMore={hasMore}
                      isLoadingMore={isLoadingMore}
                    />
                  </div>
                  {/* End .row */}
                  {/* <Pagination /> */}
                </div>
                {/* End .col for right content */}
              </div>
              {/* End .row */}
            </div>
            {/* End .container */}
          </section>
          {/* End halfMap content */}
          {/* <DefaultFooter /> */}
        </>
      )}

      <style jsx>{`
        .filter-mobile-section {
          position: relative;
          margin-bottom: 20px;
        }

        .filter-button {
          width: 100%;
          background-color: #3554d1;
          border: none;
          border-radius: 4px;
          padding: 15px 20px;
          cursor: pointer;
          transition: all 0.3s ease;
        }

        .filter-button:hover {
          background-color: #284bc1;
        }

        .button-content {
          display: flex;
          justify-content: space-between;
          align-items: center;
        }

        .left-content {
          display: flex;
          align-items: center;
          gap: 10px;
        }

        .left-content i {
          color: white;
        }

        .left-content span {
          color: white;
          font-size: 16px;
          font-weight: 500;
        }

        .arrow-icon {
          color: white;
          display: flex;
          align-items: center;
        }

        .arrow-icon-svg {
          width: 20px;
          height: 20px;
          color: white;
          transition: transform 0.3s ease;
        }

        .filter-content {
          max-height: 0;
          overflow: hidden;
          transition: max-height 0.3s ease-out;
          background-color: white;
          border-radius: 4px;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-content.show {
          max-height: 2000px;
          transition: max-height 0.5s ease-in;
          padding: 20px;
          margin-top: 15px;
        }

        @media (max-width: 1199px) {
          .sidebar {
            margin-bottom: 30px;
          }
        }

        /* Additional styles for the booking table */
        .table1 {
          width: 100%;
          border-collapse: separate;
          border-spacing: 0;
          border: 1px solid #e5e5e5;
          border-radius: 8px;
          overflow: hidden;
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .table th,
        .table td {
          border: 1px solid #e5e5e5;
        }

        .table th {
          font-weight: 600;
          text-transform: uppercase;
          letter-spacing: 0.5px;
        }

        .booking-row {
          transition: all 0.2s ease;
        }

        .booking-row:hover {
          transform: translateY(-2px);
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .bg-blue-1 {
          background-color: #3554d1;
        }

        .text-white {
          color: white;
        }

        .text-blue-1 {
          color: #3554d1;
        }

        .text-green-1 {
          color: #4caf50;
        }

        .text-red-1 {
          color: #f44336;
        }

        .text-purple-1 {
          color: #7e57c2;
        }

        .text-orange-1 {
          color: #ff9800;
        }

        .text-cyan-1 {
          color: #00bcd4;
        }

        .text-deep-orange-1 {
          color: #ff5722;
        }

        .text-purple-2 {
          color: #ab47bc;
        }

        .border-bottom-light {
          border-bottom: 1px solid #e5e5e5;
        }

        .size-40 {
          width: 40px;
          height: 40px;
        }

        .rounded-4 {
          border-radius: 4px;
        }

        .flex-center {
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .object-cover {
          object-fit: cover;
        }

        .text-15 {
          font-size: 15px;
        }

        .text-16 {
          font-size: 16px;
        }

        .text-18 {
          font-size: 18px;
        }

        .mr-10 {
          margin-right: 10px;
        }

        .mb-10 {
          margin-bottom: 10px;
        }
      `}</style>
    </>
  );
};

export default ActivityListPage3;
