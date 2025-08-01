import React, { useState, useEffect, useMemo, useRef } from "react";

import { fetchViewDetails } from "../../../../././../slice/common/ViewDetails";
import { useSelector, useDispatch } from "react-redux";
import { useNavigate } from "react-router-dom";
import { BASE_URL } from "@/services/api";
import { resetguide } from "@/slice/tourguide/guideslice";
import { resetVehicles1 } from "@/slice/localtour/Localslice";
import { resetVehicles } from "@/slice/port/pickupDropSlice";
import Box from "@mui/material/Box";
// import { blue } from "@mui/material/colors";

import { Modal } from "antd";
import CategoriesAccordion from "./TourDetails";
import swal from "sweetalert";
import jsPDF from "jspdf";
import html2canvas from "html2canvas";
import { saveAs } from "file-saver";

import { useLocation } from "react-router-dom"; // Import useLocation hook
import Skeleton from "@mui/material/Skeleton"; // Import Skeleton
import {
  setCheckIn,
  setCheckOut,
  setSearchLocation,
} from "@/slice/common/BookingSlice";
import {
  resetHotels,
  setId,
  updateSearchState,
  settourdetails,
} from "@/slice/hotel/hotelSlice";
import {
  setTourId,
  statusUpdate,
  setType,
  updateStepStatus,
} from "@/slice/common/stepsSlice";
import { setTourIdd } from "@/slice/common/authSlices";
import { fetchEditid, setTourId1, deleteTour } from "@/slice/common/EditSlice";
import Snackbar from "@mui/material/Snackbar";
import MuiAlert from "@mui/material/Alert";
import { setDateService } from "@/slice/common/dateServicesSlice";
import { fetchLists } from "@/slice/common/TourlistSlice";
import Pagination from "../../common/Pagination";
import dayjs from "dayjs";
import { Button, InputAdornment, TextField, Typography, Tooltip, IconButton } from "@mui/material";
import { Visibility, Edit, AttachMoney, Update } from "@mui/icons-material";
import { toast } from "react-toastify";
import Cookies from "js-cookie";
import axios from "axios";
import { Table, Empty } from "antd";
import CheckCircleOutlinedIcon from "@mui/icons-material/CheckCircleOutlined";
import CancelOutlinedIcon from "@mui/icons-material/CancelOutlined";
import QuestionAnswerIcon from "@mui/icons-material/QuestionAnswer";
import {
  setUserInfo as customerInfoSetUserInfo,
  resetBookingState,
  clearUserInfo,
} from "@/slice/common/customerInfo";
import { setBookingType } from "../../../../../slice/common/commonSlice";
import HotelIcon from "@mui/icons-material/Hotel";
import LocalShippingIcon from "@mui/icons-material/LocalShipping";
import TourIcon from "@mui/icons-material/Tour";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import AccountCircleIcon from "@mui/icons-material/AccountCircle";
import SummarizeIcon from "@mui/icons-material/Summarize";
import SingleBedIcon from "@mui/icons-material/SingleBed";
import RestaurantIcon from "@mui/icons-material/Restaurant";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import CalendarTodayIcon from "@mui/icons-material/CalendarToday";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import StraightenIcon from "@mui/icons-material/Straighten";
import AttachMoneyIcon from "@mui/icons-material/AttachMoney";
import PeopleIcon from "@mui/icons-material/People";
import WorkIcon from "@mui/icons-material/Work";
import SchoolIcon from "@mui/icons-material/School";
import FlightTakeoffIcon from "@mui/icons-material/FlightTakeoff";
import FlightLandIcon from "@mui/icons-material/FlightLand";
import BadgeIcon from "@mui/icons-material/Badge";
import AttractionsIcon from "@mui/icons-material/Attractions";
import BrunchDiningIcon from "@mui/icons-material/BrunchDining";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import TimerIcon from "@mui/icons-material/Timer";
import EventAvailableIcon from "@mui/icons-material/EventAvailable";
import PersonIcon from "@mui/icons-material/Person";
import CribIcon from "@mui/icons-material/Crib";
import Avatar from "@mui/material/Avatar";
import DoNotDisturbIcon from "@mui/icons-material/DoNotDisturb";
import ArrowUpwardIcon from "@mui/icons-material/ArrowUpward";
import ArrowDownwardIcon from "@mui/icons-material/ArrowDownward";
import TourDetailsModal from "./TourDetailsModal";
import EnquiryModal from "./EnquiryModal";
import PrintModal from "./PrintModal";
import { setSelectedCity } from "@/slice/common/commonSlice";
import { clearAttractions } from "@/slice/attractions/attractionSlice";
import { clearRestaurants } from "@/slice/restaurant/RestaurantsSlice";
import { UpdateCustomPackage, clearAllServices } from "@/slice/tour-packages/tourPackageSlice";

// Color functions for booking status styling
const getBackgroundColor = (tour_status) => {
  switch (tour_status) {
    case "Confirmed":
      return "rgba(33, 150, 243, 0.06)"; // Professional light blue
    case "Definite":
      return "rgba(76, 175, 80, 0.06)"; // Professional light green
    case "Actual":
      return "rgba(60, 140, 65, 0.06)"; // Professional light green
    case "Pending":
      return "rgba(244, 67, 54, 0.06)"; // Professional light red
    case "Tentative":
      return "rgba(126, 87, 194, 0.06)"; // Professional light violet
    case "New Enquiry":
      return "rgba(255, 152, 0, 0.06)"; // Professional light orange
    case "Prospect":
      return "rgba(255, 87, 34, 0.06)"; // Professional light deep orange
    case "Closed":
      return "rgb(237,237,237)"; // Professional light gray
    case "Cancelled":
      return "rgba(200, 50, 40, 0.06)"; // Professional light darker red
    default:
      return "transparent";
  }
};

// Text color function for booking status styling
const getTextColor = (tour_status) => {
  switch (tour_status) {
    case "Confirmed":
      return "#2196F3"; // Blue
    case "Definite":
      return "#4CAF50"; // Green
    case "Actual":
      return "#4CAF50"; // Green
    case "Pending":
      return "#F44336"; // Red
    case "Cancelled":
      return "#F44336"; // Red
    case "Tentative":
      return "#7E57C2"; // Violet
    case "New Enquiry":
      return "#FF9800"; // Orange
    case "Prospect":
      return "#FF5722"; // Deep Orange
    case "Closed":
      return "#000000"; // Black
    default:
      return "#CCCCCC"; // Default gray
  }
};

const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

// const StyledTableCell = styled(TableCell)(({ theme }) => ({
//   [`&.${tableCellClasses.head}`]: {
//     backgroundColor: blue[500],
//     color: theme.palette.common.white,
//   },
//   [`&.${tableCellClasses.body}`]: {
//     fontSize: 14,
//   },
// }));

// const StyledTableRow = styled(TableRow)(({ theme }) => ({
//   "&:nth-of-type(odd)": {
//     backgroundColor: "white", // Set odd rows background to white
//   },
//   "&:nth-of-type(even)": {
//     backgroundColor: theme.palette.action.hover, // Optional: alternate with a hover color or another color
//   },
//   "&:last-child td, &:last-child th": {
//     border: 0,
//   },
// }));

// Country mapping
// const countryMap = {
//   in: "India",
//   SG: "Singapore",
// };

const formatDate = (dateString) => {
  // Parse the date in 'DD/MM/YYYY' format
  const [day, month, year] = dateString.split("/");

  // Create a new Date object from the parsed values (month is zero-indexed in JS Date)
  const date = new Date(`${year}-${month}-${day}`);

  // Get the day, month (abbreviated), and year for compact display
  const dayFormatted = String(date.getDate()).padStart(2, "0");
  const monthFormatted = date.toLocaleString("en-us", { month: "short" }); // Abbreviated month name
  const yearFormatted = String(date.getFullYear()).slice(-2); // Last 2 digits of year

  return `${dayFormatted} ${monthFormatted} ${yearFormatted}`;
};

const formatDateTooltip = (dateString) => {
  // Parse the date in 'DD/MM/YYYY' format
  const [day, month, year] = dateString.split("/");

  // Create a new Date object from the parsed values (month is zero-indexed in JS Date)
  const date = new Date(`${year}-${month}-${day}`);

  // Get the day of the week (e.g., "Friday")
  const dayOfWeek = date.toLocaleString("en-us", { weekday: "short" });

  // Get the day, month (abbreviated), and year
  const dayFormatted = String(date.getDate()).padStart(2, "0");
  const monthFormatted = date.toLocaleString("en-us", { month: "short" }); // Abbreviated month name
  const yearFormatted = date.getFullYear();

  return `${dayOfWeek}, ${dayFormatted} ${monthFormatted} ${yearFormatted}`;
};

const formatDate1 = (dateString) => {
  // Split the input date string by "/"
  const [day, month, year] = dateString.split("/");

  // Ensure the day and month are two digits
  const formattedDay = String(day).padStart(2, "0");
  const formattedMonth = String(month).padStart(2, "0");
  const formattedYear = String(year);

  // Return the formatted date in the same format
  return `${formattedDay}/${formattedMonth}/${formattedYear}`;
};

export default function Pending() {
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(5);
  const [isModalVisible, setIsModalVisible] = useState(false);
  const [isPrintModalVisible, setIsPrintModalVisible] = useState(false);
  const userRole = useSelector((state) => state.auth.userRole);
  const [isEnquiryModalVisible, setIsEnquiryModalVisible] = useState(false);

  // Get the view details from the Redux store BEFORE using it in useState
  const { bookings = {}, status: viewDetailsStatus } = useSelector(
    (state) => state.viewDetails
  );
  const { pendingTours = [], status } = useSelector((state) => state.lists);

  // Get user_country from auth slice
  const user_country = useSelector((state) => {
    const countryData = state.auth.user_country;
    console.log("Raw user_country data:", countryData);

    // If it's already an array, return it
    if (Array.isArray(countryData)) {
      return countryData;
    }

    // If it's a string containing [object Object]
    if (
      typeof countryData === "string" &&
      countryData.includes("[object Object]")
    ) {
      // Get the actual country data from the auth slice
      const actualCountries = state.auth.user_country;
      if (Array.isArray(actualCountries)) {
        return actualCountries;
      }
    }

    // If it's a single object, wrap it in an array
    if (typeof countryData === "object" && countryData !== null) {
      return [countryData];
    }

    // Default to empty array if we can't process the data
    return [];
  });

  console.log("Processed user_country:", user_country);

  // Create bidirectional mapping for country names and codes
  const countryMappings = useMemo(() => {
    const nameToCode = {};
    const codeToName = {};

    if (user_country && Array.isArray(user_country)) {
      user_country.forEach((country) => {
        if (country && country.name && country.code) {
          // Store both original case and lowercase versions for names
          nameToCode[country.name] = country.code;
          nameToCode[country.name.toLowerCase()] = country.code;
          // Store code to name mapping
          codeToName[country.code] = country.name;
          codeToName[country.code.toLowerCase()] = country.name;
        }
      });
    }
    console.log("Created country mappings:", { nameToCode, codeToName });
    return { nameToCode, codeToName };
  }, [user_country]);

  const [enquiryAmount, setEnquiryAmount] = useState(() => {
    // Initialize with current total price if data is available
    if (bookings && Object.keys(bookings).length > 0) {
      let totalPrice = 0;

      // Calculate total from all services
      const services = [
        ...(bookings.hotel || bookings.data?.hotel || []),
        ...(bookings.entry_port || bookings.data?.entry_port || []),
        ...(bookings.exit_port || bookings.data?.exit_port || []),
        ...(bookings.attraction || bookings.data?.attraction || []),
        ...(bookings.guide || bookings.data?.guide || []),
        ...(bookings.restaurant || bookings.data?.restaurant || []),
        ...(bookings.travel_point || bookings.data?.travel_point || []),
      ];

      services.forEach((item) => {
        if (item.totalPrice) {
          totalPrice += parseFloat(item.totalPrice);
        }
      });

      return Math.ceil(totalPrice) || 0;
    }

    return 0;
  });
  const [enquiryComment, setEnquiryComment] = useState("");
  const [commentError, setCommentError] = useState(false);
  const navigate = useNavigate(); // Initialize navigate
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const [snackbarSeverity, setSnackbarSeverity] = useState("info");
  const [tourId, setTId] = useState(null);
  const contentRef = useRef(null);

  // Updated states for markup and discount (flat amounts instead of percentages)
  const [markupAmount, setMarkupAmount] = useState(0);
  const [discountAmount, setDiscountAmount] = useState(0);
  const [modifiedPriceData, setModifiedPriceData] = useState(null);
  const [bookingType1, setBookingType1] = useState(null);
  const [displayId, setDisplayId] = useState(null);

  //const { currentStep } = useSelector((state) => state.steps);
  const { DmcName, DmcLogo } = useSelector((state) => state.auth);

  const dispatch = useDispatch();
  const location = useLocation(); // Get the current location (route)
  const [order, setOrder] = useState("asc"); // State to track sort order

  // Add these state declarations at the top of the component where other state variables are defined
  const [enquiryHistory, setEnquiryHistory] = useState([]);
  const [loadingEnquiryHistory, setLoadingEnquiryHistory] = useState(false);

  // Add a new state to track the current action type
  const [enquiryActionType, setEnquiryActionType] = useState("enquiry");

  // Add a new state to track if an enquiry has been processed (accepted or cancelled)
  const [enquiryProcessed, setEnquiryProcessed] = useState(false);

  // Add a new state to track which tour ID has an active enquiry
  const [tourWithProcessedEnquiry, setTourWithProcessedEnquiry] =
    useState(null);

  // Add state to track which column is being sorted
  const [sortColumn, setSortColumn] = useState("id");

  // Add state to track if assigned to agent
  const [assigned, setAssigned] = useState(null);

  // Add a new state to track if the enquiry is already processed with status 2 or 3
  const [enquiryStatusProcessed, setEnquiryStatusProcessed] = useState(false);

  // Add a function to check if a tour has a processed enquiry
  const hasTourProcessedEnquiry = (tourId) => {
    return tourWithProcessedEnquiry === tourId;
  };

  // Add a function to check if the current tour has a processed status
  const checkEnquiryStatus = async (tourId) => {
    try {
      if (!tourId) return false;

      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken || !AgentId) {
        return false;
      }

      const response = await axios.get(`${BASE_URL}/enquiry-status`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          "Content-Type": "application/json",
          "agent-id": AgentId,
        },
        params: {
          tour_id: tourId,
        },
      });

      if (response.data && response.data.data) {
        const historyData = Array.isArray(response.data.data)
          ? response.data.data
          : [response.data.data];

        // Check if there's an entry with status 2 (Booked) or 3 (Cancel)
        const hasStatusBookedOrCancel = historyData.some(
          (item) =>
            item.status === "2" ||
            item.status === "3" ||
            item.status === 2 ||
            item.status === 3
        );

        return hasStatusBookedOrCancel;
      }

      return false;
    } catch (error) {
      console.error("Error checking enquiry status:", error);
      return false;
    }
  };

  // Trigger API call when navigating to Dashboard
  useEffect(() => {
    // Trigger fetchLists only when navigating to "/dashboard"
    if (location.pathname === "/dashboard/db-dashboard") {
      dispatch(fetchLists());
    }
  }, [location.pathname, dispatch]); // Depend on the pathname // Dependency on location.pathname

  // if (status === "loading") return <p>Loading...</p>;
  // if (status === "failed") return <p>Error: {error}</p>;
  const [sortedLists, setSortedLists] = useState([]); // State for sorted data
  // Update sortedLists when lists change
  useEffect(() => {
    setSortedLists(Array.isArray(pendingTours) ? pendingTours : []); // Safely check if lists is an array
  }, [pendingTours]);

  const handleSort = () => {
    const sortedData = [...sortedLists].sort((a, b) => {
      const numA = parseInt(a.display_id.match(/\d+$/)[0], 10);
      const numB = parseInt(b.display_id.match(/\d+$/)[0], 10);

      return order === "asc" ? numA - numB : numB - numA;
    });

    setSortedLists(sortedData);
    setOrder(order === "asc" ? "desc" : "asc");
  };

  // Add new function to handle column sorting
  const handleColumnSort = (column) => {
    // Clone current list to avoid mutation
    const sortedData = [...sortedLists];

    // If clicking on the same column, just toggle the order
    // Otherwise, set to ascending first
    const newOrder =
      column === sortColumn ? (order === "asc" ? "desc" : "asc") : "asc";

    // Sort based on column
    switch (column) {
      case "id":
        sortedData.sort((a, b) => {
          const numA = parseInt(a.display_id.match(/\d+$/)[0], 10);
          const numB = parseInt(b.display_id.match(/\d+$/)[0], 10);
          return newOrder === "asc" ? numA - numB : numB - numA;
        });
        break;
      case "startDate":
        sortedData.sort((a, b) => {
          // Convert date strings to Date objects for comparison
          const [dayA, monthA, yearA] = a.check_in_time.split("/");
          const [dayB, monthB, yearB] = b.check_in_time.split("/");
          const dateA = new Date(yearA, monthA - 1, dayA);
          const dateB = new Date(yearB, monthB - 1, dayB);
          return newOrder === "asc" ? dateA - dateB : dateB - dateA;
        });
        break;
      case "endDate":
        sortedData.sort((a, b) => {
          // Convert date strings to Date objects for comparison
          const [dayA, monthA, yearA] = a.check_out_time.split("/");
          const [dayB, monthB, yearB] = b.check_out_time.split("/");
          const dateA = new Date(yearA, monthA - 1, dayA);
          const dateB = new Date(yearB, monthB - 1, dayB);
          return newOrder === "asc" ? dateA - dateB : dateB - dateA;
        });
        break;
      case "pax":
        sortedData.sort((a, b) => {
          return newOrder === "asc"
            ? parseInt(a.total_pax) - parseInt(b.total_pax)
            : parseInt(b.total_pax) - parseInt(a.total_pax);
        });
        break;
      case "destination":
        sortedData.sort((a, b) => {
          const destA = a.destination ? a.destination.split(",")[0].trim() : "";
          const destB = b.destination ? b.destination.split(",")[0].trim() : "";
          return newOrder === "asc"
            ? destA.localeCompare(destB)
            : destB.localeCompare(destA);
        });
        break;
      case "Booking Type":
        sortedData.sort((a, b) => {
          const statusA = a.booking_type || "Booking";
          const statusB = b.booking_type || "Booking";
          return newOrder === "asc"
            ? statusA.localeCompare(statusB)
            : statusB.localeCompare(statusA);
        });
        break;
      default:
        // No sorting
        break;
    }

    // Update state with sorted data
    setSortedLists(sortedData);
    setOrder(newOrder);
    setSortColumn(column);
  };

  // const handleChangePage = (event, newPage) => {
  //   setPage(newPage);
  // };

  // const handleChangeRowsPerPage = (event) => {
  //   setRowsPerPage(parseInt(event.target.value, 10));
  //   setPage(0);
  // };

  const paginatedLists = sortedLists.slice(
    page * rowsPerPage,
    page * rowsPerPage + rowsPerPage
  );

  const steps = useMemo(
    () => [
      {
        label: "Hotels",
        path: `/dashboard/db-dashboard/view-hotel-search/${tourId}`,
        key: "hotel",
      },
      {
        label: "Pickup/Drop(Entry & Exit Port)",
        path: "/dashboard/db-dashboard/pickupdrop",
        key: "port",
      },
      {
        label: "Attractions & Experiences",
        path: "/dashboard/db-dashboard/attractions",
        key: "attraction",
      },
      {
        label: "Tour Guide",
        path: "/dashboard/db-dashboard/tourguide",
        key: "guide",
      },
      {
        label: "Restaurants",
        path: "/dashboard/db-dashboard/restaurants",
        key: "restaurent",
      },
      {
        label: "Local Transfer",
        path: "/dashboard/db-dashboard/localtransfer",
        key: "travel",
      },
    ],
    [tourId] // Recalculate only when tourId changes
  );
  // Dependency array: only recalculate if 'id' changes

  // Calculate total pages
  const totalPages = Math.ceil(sortedLists.length / rowsPerPage);

  const handleCloseSnackbar = (event, reason) => {
    if (reason === "clickaway") {
      return;
    }
    setOpenSnackbar(false);
  };

  const handleEdit = (list) => {
    setSnackbarMessage("Submitting Id...");
    setSnackbarSeverity("info");
    setOpenSnackbar(true);

    // Clear attractions and restaurants data first
    dispatch(clearAttractions());
    dispatch(clearRestaurants());

    // Dispatch necessary actions
    dispatch(setId(list.id));
    dispatch(setTourId(list.id));
    dispatch(setTourId1(list.id));
    dispatch(setTourIdd(list.id));
    dispatch(resetBookingState());
    dispatch(clearUserInfo());

    // Format dates before dispatching
    const formattedCheckIn = formatDate1(list.check_in_time);
    const formattedCheckOut = formatDate1(list.check_out_time);

    // Set dates in Redux store
    dispatch(setCheckIn(formattedCheckIn));
    dispatch(setCheckOut(formattedCheckOut));

    // Trigger fetchViewDetails just like handleViewDetails
    dispatch(fetchViewDetails({ tour_id: list.id }));
    dispatch(resetguide());
    dispatch(resetVehicles());
    dispatch(resetVehicles1());

    dispatch(fetchEditid())
      .unwrap()
      .then((response) => {
        console.log("Full Response Data:", response);

        // Extract data from the nested structure
        const data = response.data;
        const bookingType = data.bookingType || "booking";
        dispatch(setBookingType(bookingType));

        // Handle customer info if it exists
        if (data.customerInfo && typeof data.customerInfo === "object") {
          console.log("Processing customer info:", data.customerInfo);

          // Directly use the customerInfo object
          dispatch(
            customerInfoSetUserInfo({
              fullName: data.customerInfo.fullName || "",
              email: data.customerInfo.email || "",
              phone: data.customerInfo.phone || "",
              address1: data.customerInfo.address1 || "",
              address2: data.customerInfo.address2 || "",
              state: data.customerInfo.state || "",
              zip: data.customerInfo.zip || "",
              specialRequests: data.customerInfo.specialRequest || "",
            })
          );
        } else {
          console.warn(
            "Customer info not found or has incorrect format:",
            data.customerInfo
          );
        }

        const id = data.tour_id;
        setTId(id);
        const destination = data.destination;

        if (!id || !destination) {
          console.error("Tour ID or destination not found in response.");
          throw new Error("Invalid response data.");
        }

        console.log("date service", data.service?.date_service);
        if (data.service?.date_service) {
          dispatch(setDateService(data.service.date_service));
        }

        console.log("Step:", data.step);
        if (data.step) {
          dispatch(updateStepStatus({ key: data.step, status: 2 }));
          dispatch(setType(null));

          const matchedStep = steps.find((step) => step.key === data.step);
          if (matchedStep) {
            console.log("Navigating to:", matchedStep.path);
            navigate(matchedStep.path, { state: { tourDetails: data } });
            dispatch(statusUpdate()).unwrap();
          } else {
            console.warn(`No step found for key: ${data.step}`);
          }
        }

        dispatch(setTourId(id));

        if (destination) {
          console.log("Destination (Raw):", destination);

          // Convert destination names to country codes using the mapping
          const destinationArray = Array.isArray(destination)
            ? destination
            : destination.split(",").map((name) => name.trim());

          console.log("Formatted destination array:", destinationArray);

          // Map destinations to country codes for search
          const countryCodeArray = destinationArray
            .map((item) => {
              // First try to get code directly (in case item is already a code)
              let code = countryMappings.nameToCode[item];

              // If not found, check if it's a code and get the name
              if (!code) {
                const name = countryMappings.codeToName[item];
                if (name) {
                  code = countryMappings.nameToCode[name];
                }
              }

              console.log(`Mapping ${item} to code:`, code);
              return code;
            })
            .filter(Boolean);

          console.log("Country code array:", countryCodeArray);

          // Use country codes for search
          dispatch(setSearchLocation(countryCodeArray));

          // Convert destination to full country names for display
          const destinationNames = destinationArray
            .map((item) => {
              // If item is a code, get the name
              if (countryMappings.codeToName[item]) {
                return countryMappings.codeToName[item];
              }
              // If item is already a name, return it
              return item;
            })
            .filter(Boolean);

          // Pass country names for city selection
          dispatch(
            updateSearchState({
              location: destinationNames,
              ucheckIn: formattedCheckIn,
              ucheckOut: formattedCheckOut,
            })
          );

          // Set selected city with country name
          if (destinationNames.length > 0) {
            dispatch(setSelectedCity(destinationNames[0]));
          }

          dispatch(resetHotels());
        } else {
          console.warn("Destination is missing");
        }

        console.log("Setting tour details:", {
          ...data,
          country: destination,
          service_details: {
            ...data.service,
            country: destination,
          },
        });

        dispatch(
          settourdetails({
            ...data,
            country: destination,
            service_details: {
              ...data.service,
              country: destination,
            },
          })
        );

        setSnackbarMessage("Search submitted successfully!");
        setSnackbarSeverity("success");
        setOpenSnackbar(true);
      })
      .catch((error) => {
        console.error("Error fetching booking:", error);
        setSnackbarMessage(
          error?.message || "Something went wrong. Please try again."
        );
        setSnackbarSeverity("error");
        setOpenSnackbar(true);
      });
  };

  const handleUpdate = async ({ id: tourId }) => {
    setSnackbarMessage("Submitting Id...");
    setSnackbarSeverity("info");
    setOpenSnackbar(true);
    dispatch(clearAllServices());
    dispatch(clearAttractions());
    dispatch(clearRestaurants());
    dispatch(resetVehicles());
    dispatch(resetVehicles1()); 
    dispatch(resetguide());
    dispatch(UpdateCustomPackage({ tour_id: tourId }))
    .unwrap()
    .then((response) => {
      navigate("/dashboard/tour-packages");
      console.log("Full Response Data:", response);
    })
    .catch((error) => {
      console.error("Error fetching booking:", error);
    });
  };

  // Update the handleViewDetails function to properly initialize and persist data
  const handleViewDetails = async ({
    id: tourId,
    booking_type,
    display_id,
  }) => {
    // Fetch tour details
    console.log("booking_type", booking_type);
    console.log("display_id", display_id);
    setBookingType1(booking_type);
    setDisplayId(display_id);

    // Preserve any existing modifiedPriceData if it exists and is for the same tour
    const currentModifiedPriceData = modifiedPriceData;
    const isForSameTour =
      currentModifiedPriceData &&
      (currentModifiedPriceData.id === tourId ||
        currentModifiedPriceData.tour_id === tourId ||
        (currentModifiedPriceData.data &&
          (currentModifiedPriceData.data.id === tourId ||
            currentModifiedPriceData.data.tour_id === tourId)));

    // Only reset modifiedPriceData if viewing a different tour
    if (!isForSameTour) {
      // Initialize with sensible defaults before data loads
      setMarkupAmount(0);
      setDiscountAmount(0);
      setModifiedPriceData(null);
    }

    // Dispatch the API call to get tour details
    await dispatch(fetchViewDetails({ tour_id: tourId }));

    // Show the modal
    setIsModalVisible(true);

    // Store the current tour ID
    setTId(tourId);

    // Log the booking data for debugging
    console.log("Fetching tour details for:", tourId);

    // Reset enquiry processed state when viewing a new tour
    setEnquiryProcessed(false);

    // Check if this tour has a status of 2 or 3
    const isProcessed = await checkEnquiryStatus(tourId);
    setEnquiryStatusProcessed(isProcessed);

    // Add a timeout to log bookings data after it's loaded
    setTimeout(() => {
      console.log("BOOKINGS DATA:", bookings);

      // If we have valid bookings data and no modifiedPriceData yet, update prices
      if (
        bookings &&
        Object.keys(bookings).length > 0 &&
        (!modifiedPriceData || !isForSameTour)
      ) {
        console.log("Initializing modifiedPriceData from bookings");
        updatePrices(markupAmount, discountAmount);
      }
    }, 1000);
  };

  const handleDelete = async (tourId) => {
    console.log("Received tourId:", tourId); // ✅ Should print actual ID

    if (!tourId) {
      console.error("❌ tourId is missing!");
      return;
    }

    const willDelete = await swal({
      title: "Are you sure to delete this tour?",
      icon: "warning",
      buttons: ["Cancel", "Delete"],
      dangerMode: true,
    });

    if (willDelete) {
      try {
        await dispatch(deleteTour(tourId)).unwrap(); // Pass tourId here ✅
        swal("Deleted!", "Your tour has been deleted!", "success");
      } catch (err) {
        console.error("❌ Error deleting tour:", err);
        swal("Error!", "Failed to delete the tour.", "error");
      }
    } else {
      swal("Cancelled", "Your tour is safe!", "info");
    }
    dispatch(fetchLists());
  };

  const handleCloseModal = () => {
    setIsModalVisible(false);
  };

  const handlePrint = () => {
    // Ensure prices are updated before showing the modal
    if (bookings && Object.keys(bookings).length > 0) {
      // Make sure modifiedPriceData is updated with current markup/discount
      updatePrices(markupAmount, discountAmount);
      console.log(
        "Updated modifiedPriceData before showing PrintModal:",
        modifiedPriceData
      );
    }

    // Show the modal
    setIsPrintModalVisible(true);
  };

  // Function to update prices based on markup and discount flat amounts
  const updatePrices = (markup, discount) => {
    // Deep clone the bookings object to avoid mutating the original
    const modifiedData = JSON.parse(JSON.stringify(bookings));

    // Calculate total original price
    let totalOriginalPrice = 0;

    // Get all services
    const hotels = modifiedData.hotel || modifiedData.data?.hotel || [];
    const entryPorts =
      modifiedData.entry_port || modifiedData.data?.entry_port || [];
    const exitPorts =
      modifiedData.exit_port || modifiedData.data?.exit_port || [];
    const travelPoints =
      modifiedData.travel_point || modifiedData.data?.travel_point || [];
    const attractions =
      modifiedData.attraction || modifiedData.data?.attraction || [];
    const guides = modifiedData.guide || modifiedData.data?.guide || [];
    const restaurants =
      modifiedData.restaurant || modifiedData.data?.restaurant || [];

    // Calculate total original price
    hotels.forEach((hotel) => {
      if (hotel.totalPrice) {
        totalOriginalPrice += parseFloat(hotel.totalPrice);
      }
    });

    entryPorts.forEach((port) => {
      if (port.totalPrice) {
        totalOriginalPrice += parseFloat(port.totalPrice);
      }
    });

    exitPorts.forEach((port) => {
      if (port.totalPrice) {
        totalOriginalPrice += parseFloat(port.totalPrice);
      }
    });

    travelPoints.forEach((travel) => {
      if (travel.totalPrice) {
        totalOriginalPrice += parseFloat(travel.totalPrice);
      }
    });

    attractions.forEach((attraction) => {
      if (attraction.totalPrice) {
        totalOriginalPrice += parseFloat(attraction.totalPrice);
      }
    });

    guides.forEach((guide) => {
      if (guide.totalPrice) {
        totalOriginalPrice += parseFloat(guide.totalPrice);
      }
    });

    restaurants.forEach((restaurant) => {
      if (restaurant.totalPrice) {
        totalOriginalPrice += parseFloat(restaurant.totalPrice);
      }
    });

    // If total price is 0, we can't calculate distribution ratios
    if (totalOriginalPrice === 0) {
      setModifiedPriceData(modifiedData);
      return;
    }

    // Calculate markup and discount percentages to apply proportionally
    const markupPercentage = (markup / totalOriginalPrice) * 100;
    const discountPercentage = (discount / totalOriginalPrice) * 100;

    // Calculate individual items with prices for distribution
    const items = [
      ...hotels,
      ...entryPorts,
      ...exitPorts,
      ...travelPoints,
      ...attractions,
      ...guides,
      ...restaurants,
    ].filter((item) => item.totalPrice);

    // Sort items by price to distribute remaining amounts to higher priced items first
    items.sort((a, b) => parseFloat(b.totalPrice) - parseFloat(a.totalPrice));

    // HANDLE MARKUP CALCULATION WITH EXACT TOTAL
    let totalAppliedMarkup = 0;

    // Calculate each item's proportional markup using Math.floor to avoid exceeding total
    items.forEach((item) => {
      item.calculatedMarkup = Math.floor(
        (parseFloat(item.totalPrice) / totalOriginalPrice) * markup
      );
      totalAppliedMarkup += item.calculatedMarkup;
    });

    // Distribute remaining markup cents to ensure exact total
    let remainingMarkupCents = markup - totalAppliedMarkup;

    // Distribute one by one to highest priced items first
    for (let i = 0; i < items.length && remainingMarkupCents > 0; i++) {
      items[i].calculatedMarkup += 1;
      remainingMarkupCents -= 1;
    }

    // HANDLE DISCOUNT CALCULATION WITH EXACT TOTAL
    let totalAppliedDiscount = 0;

    // Calculate each item's proportional discount using Math.floor to avoid exceeding total
    items.forEach((item) => {
      item.calculatedDiscount = Math.floor(
        (parseFloat(item.totalPrice) / totalOriginalPrice) * discount
      );
      totalAppliedDiscount += item.calculatedDiscount;
    });

    // Distribute remaining discount cents to ensure exact total
    let remainingDiscountCents = discount - totalAppliedDiscount;

    // Distribute one by one to highest priced items first (already sorted)
    for (let i = 0; i < items.length && remainingDiscountCents > 0; i++) {
      items[i].calculatedDiscount += 1;
      remainingDiscountCents -= 1;
    }

    // Process hotels
    hotels.forEach((hotel) => {
      if (hotel.totalPrice) {
        // Store original price
        hotel.basePrice = hotel.totalPrice;

        // Use the pre-calculated markup amount that ensures total matches exactly
        const hotelMarkupAmount = hotel.calculatedMarkup || 0;

        // Apply markup to get the new base price
        const markedUpPrice = parseFloat(hotel.totalPrice) + hotelMarkupAmount;
        hotel.markedUpPrice = markedUpPrice.toString();

        // Use the pre-calculated discount amount that ensures total matches exactly
        const hotelDiscountAmount = hotel.calculatedDiscount || 0;

        // Apply discount on the marked-up price
        hotel.totalPrice = (markedUpPrice - hotelDiscountAmount).toString();

        // Store markup and discount amounts
        hotel.markupAmount = hotelMarkupAmount.toString();
        if (discount > 0) {
          hotel.discountAmount = hotelDiscountAmount.toString();
        }

        // Remove the temporary calculation properties
        delete hotel.calculatedMarkup;
        delete hotel.calculatedDiscount;
      }
    });

    // Process entry ports
    entryPorts.forEach((port) => {
      if (port.totalPrice) {
        // Store original price
        port.basePrice = port.totalPrice;

        // Use the pre-calculated markup amount that ensures total matches exactly
        const portMarkupAmount = port.calculatedMarkup || 0;

        // Apply markup to get the new base price
        const markedUpPrice = parseFloat(port.totalPrice) + portMarkupAmount;
        port.markedUpPrice = markedUpPrice.toString();

        // Use the pre-calculated discount amount that ensures total matches exactly
        const portDiscountAmount = port.calculatedDiscount || 0;

        // Apply discount on the marked-up price
        port.totalPrice = (markedUpPrice - portDiscountAmount).toString();

        // Store markup and discount amounts
        port.markupAmount = portMarkupAmount.toString();
        if (discount > 0) {
          port.discountAmount = portDiscountAmount.toString();
        }

        // Remove the temporary calculation properties
        delete port.calculatedMarkup;
        delete port.calculatedDiscount;
      }
    });

    // Process exit ports
    exitPorts.forEach((port) => {
      if (port.totalPrice) {
        // Store original price
        port.basePrice = port.totalPrice;

        // Use the pre-calculated markup amount that ensures total matches exactly
        const portMarkupAmount = port.calculatedMarkup || 0;

        // Apply markup to get the new base price
        const markedUpPrice = parseFloat(port.totalPrice) + portMarkupAmount;
        port.markedUpPrice = markedUpPrice.toString();

        // Use the pre-calculated discount amount that ensures total matches exactly
        const portDiscountAmount = port.calculatedDiscount || 0;

        // Apply discount on the marked-up price
        port.totalPrice = (markedUpPrice - portDiscountAmount).toString();

        // Store markup and discount amounts
        port.markupAmount = portMarkupAmount.toString();
        if (discount > 0) {
          port.discountAmount = portDiscountAmount.toString();
        }

        // Remove the temporary calculation properties
        delete port.calculatedMarkup;
        delete port.calculatedDiscount;
      }
    });

    // Process travel points
    travelPoints.forEach((travel) => {
      if (travel.totalPrice) {
        // Store original price
        travel.basePrice = travel.totalPrice;

        // Use the pre-calculated markup amount that ensures total matches exactly
        const travelMarkupAmount = travel.calculatedMarkup || 0;

        // Apply markup to get the new base price
        const markedUpPrice =
          parseFloat(travel.totalPrice) + travelMarkupAmount;
        travel.markedUpPrice = markedUpPrice.toString();

        // Use the pre-calculated discount amount that ensures total matches exactly
        const travelDiscountAmount = travel.calculatedDiscount || 0;

        // Apply discount on the marked-up price
        travel.totalPrice = (markedUpPrice - travelDiscountAmount).toString();

        // Store markup and discount amounts
        travel.markupAmount = travelMarkupAmount.toString();
        if (discount > 0) {
          travel.discountAmount = travelDiscountAmount.toString();
        }

        // Remove the temporary calculation properties
        delete travel.calculatedMarkup;
        delete travel.calculatedDiscount;
      }
    });

    // Process attractions
    attractions.forEach((attraction) => {
      if (attraction.totalPrice) {
        // Store original price
        attraction.basePrice = attraction.totalPrice;

        // Use the pre-calculated markup amount that ensures total matches exactly
        const attractionMarkupAmount = attraction.calculatedMarkup || 0;

        // Apply markup to get the new base price
        const markedUpPrice =
          parseFloat(attraction.totalPrice) + attractionMarkupAmount;
        attraction.markedUpPrice = markedUpPrice.toString();

        // Use the pre-calculated discount amount that ensures total matches exactly
        const attractionDiscountAmount = attraction.calculatedDiscount || 0;

        // Apply discount on the marked-up price
        attraction.totalPrice = (
          markedUpPrice - attractionDiscountAmount
        ).toString();

        // Store markup and discount amounts
        attraction.markupAmount = attractionMarkupAmount.toString();
        if (discount > 0) {
          attraction.discountAmount = attractionDiscountAmount.toString();
        }

        // Remove the temporary calculation properties
        delete attraction.calculatedMarkup;
        delete attraction.calculatedDiscount;
      }
    });

    // Process guides
    guides.forEach((guide) => {
      if (guide.totalPrice) {
        // Store original price
        guide.basePrice = guide.totalPrice;

        // Use the pre-calculated markup amount that ensures total matches exactly
        const guideMarkupAmount = guide.calculatedMarkup || 0;

        // Apply markup to get the new base price
        const markedUpPrice = parseFloat(guide.totalPrice) + guideMarkupAmount;
        guide.markedUpPrice = markedUpPrice.toString();

        // Use the pre-calculated discount amount that ensures total matches exactly
        const guideDiscountAmount = guide.calculatedDiscount || 0;

        // Apply discount on the marked-up price
        guide.totalPrice = (markedUpPrice - guideDiscountAmount).toString();

        // Store markup and discount amounts
        guide.markupAmount = guideMarkupAmount.toString();
        if (discount > 0) {
          guide.discountAmount = guideDiscountAmount.toString();
        }

        // Remove the temporary calculation properties
        delete guide.calculatedMarkup;
        delete guide.calculatedDiscount;
      }
    });

    // Process restaurants
    restaurants.forEach((restaurant) => {
      if (restaurant.totalPrice) {
        // Store original price
        restaurant.basePrice = restaurant.totalPrice;

        // Use the pre-calculated markup amount that ensures total matches exactly
        const restaurantMarkupAmount = restaurant.calculatedMarkup || 0;

        // Apply markup to get the new base price
        const markedUpPrice =
          parseFloat(restaurant.totalPrice) + restaurantMarkupAmount;
        restaurant.markedUpPrice = markedUpPrice.toString();

        // Use the pre-calculated discount amount that ensures total matches exactly
        const restaurantDiscountAmount = restaurant.calculatedDiscount || 0;

        // Apply discount on the marked-up price
        restaurant.totalPrice = (
          markedUpPrice - restaurantDiscountAmount
        ).toString();

        // Store markup and discount amounts
        restaurant.markupAmount = restaurantMarkupAmount.toString();
        if (discount > 0) {
          restaurant.discountAmount = restaurantDiscountAmount.toString();
        }

        // Remove the temporary calculation properties
        delete restaurant.calculatedMarkup;
        delete restaurant.calculatedDiscount;
      }
    });

    // Store markup and discount as properties on the data object for reference
    modifiedData.markupAmount = markup;
    modifiedData.discountAmount = discount;
    modifiedData.markupPercentage = markupPercentage;
    modifiedData.discountPercentage = discountPercentage;
    modifiedData.lastUpdated = new Date().toISOString(); // Add timestamp

    // Make sure the tour ID is preserved
    if (tourId) {
      modifiedData.tour_id = tourId;
    }

    console.log("Setting modifiedPriceData:", modifiedData);

    // Update the state with modified data
    setModifiedPriceData(modifiedData);
  };

  // Effect to update prices when markup or discount changes
  useEffect(() => {
    if (Object.keys(bookings).length > 0) {
      updatePrices(markupAmount, discountAmount);
    }
  }, [bookings, markupAmount, discountAmount]);

  // Update enquiryAmount when booking data changes
  useEffect(() => {
    if (bookings && Object.keys(bookings).length > 0) {
      let totalPrice = 0;

      // Calculate total from all services
      const services = [
        ...(bookings.hotel || bookings.data?.hotel || []),
        ...(bookings.entry_port || bookings.data?.entry_port || []),
        ...(bookings.exit_port || bookings.data?.exit_port || []),
        ...(bookings.attraction || bookings.data?.attraction || []),
        ...(bookings.guide || bookings.data?.guide || []),
        ...(bookings.restaurant || bookings.data?.restaurant || []),
        ...(bookings.travel_point || bookings.data?.travel_point || []),
      ];

      services.forEach((item) => {
        if (item.totalPrice) {
          totalPrice += parseFloat(item.totalPrice);
        }
      });

      setEnquiryAmount(Math.ceil(totalPrice) || 0);
    }
  }, [bookings]);

  const handleDownloadPDF = () => {
    // Apply markup and discount before generating PDF
    updatePrices(markupAmount, discountAmount);

    const input = contentRef.current;

    if (!input) {
      setSnackbarMessage("Error: Content not ready for download");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return;
    }

    setSnackbarMessage("Generating PDF...");
    setSnackbarSeverity("info");
    setOpenSnackbar(true);

    // Setup PDF document
    const pdf = new jsPDF({
      orientation: "portrait",
      unit: "mm",
      format: "a4",
    });

    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();

    // Define margins
    const marginLeft = 10;
    const marginTop = 10;
    const contentWidth = pageWidth - marginLeft * 2;
    const contentHeight = pageHeight - marginTop * 2;

    // We'll split the content into sections and render each section separately
    // This is more reliable than trying to render the whole page at once
    const contentSections = [
      // First find all the main sections by their className or other selectors
      ...Array.from(input.children).filter((child) => child.tagName === "DIV"),
    ];

    if (contentSections.length === 0) {
      // If no sections found, try to render the whole thing
      contentSections.push(input);
    }

    // Create a promise for each section
    const sectionPromises = contentSections.map((section) => {
      return html2canvas(section, {
        scale: 1.8,
        useCORS: true,
        allowTaint: true,
        backgroundColor: "#FFFFFF",
      });
    });

    let currentPage = 0;

    // Process all sections sequentially
    Promise.all(sectionPromises)
      .then((canvases) => {
        // For each canvas (section)
        canvases.forEach((canvas, index) => {
          // If not the first section, add a new page
          if (index > 0) {
            pdf.addPage();
            currentPage++;
          }

          // Calculate image dimensions
          const imgWidth = pageWidth;
          const scaleFactor = imgWidth / canvas.width;
          const imgHeight = canvas.height * scaleFactor;

          // If this section fits on the current page
          if (imgHeight <= contentHeight) {
            // Simply add it to the page
            pdf.addImage(
              canvas.toDataURL("image/png", 1.0),
              "PNG",
              marginLeft,
              marginTop,
              contentWidth,
              imgHeight * (contentWidth / imgWidth),
              `section-${index}`,
              "FAST",
              0
            );
          } else {
            // This section is too large for one page, split it
            const pagesNeeded = Math.ceil(imgHeight / contentHeight);

            for (let pageNum = 0; pageNum < pagesNeeded; pageNum++) {
              if (pageNum > 0) {
                pdf.addPage();
                currentPage++;
              }

              // Create a cropped version of this section for the current page
              const tempCanvas = document.createElement("canvas");
              const tempCtx = tempCanvas.getContext("2d");

              // Height of the source piece to use
              const sourceHeight = Math.min(
                canvas.height / pagesNeeded,
                (contentHeight / imgHeight) * canvas.height
              );

              tempCanvas.width = canvas.width;
              tempCanvas.height = sourceHeight;

              // Draw the appropriate slice of the original canvas
              tempCtx.drawImage(
                canvas,
                0,
                pageNum * sourceHeight,
                canvas.width,
                sourceHeight,
                0,
                0,
                canvas.width,
                sourceHeight
              );

              // Add the image slice to the PDF
              pdf.addImage(
                tempCanvas.toDataURL("image/png", 1.0),
                "PNG",
                marginLeft,
                marginTop,
                contentWidth,
                pageHeight - marginTop * 2,
                `section-${index}-page-${pageNum}`,
                "FAST",
                0
              );
            }
          }
        });

        // Save the completed PDF
        pdf.save(
          `Tour_Details_${
            bookings.display_id || bookings.data?.display_id || "booking"
          }.pdf`
        );

        setSnackbarMessage("PDF downloaded successfully!");
        setSnackbarSeverity("success");
        setOpenSnackbar(true);
        setIsPrintModalVisible(false);

        // Reset modified data after PDF generation
        setModifiedPriceData(null);
      })
      .catch((error) => {
        console.error("Error generating PDF:", error);
        setSnackbarMessage("Error generating PDF");
        setSnackbarSeverity("error");
        setOpenSnackbar(true);
        // Reset modified data on error
        setModifiedPriceData(null);
      });
  };

  const handleCancelPrint = () => {
    setIsPrintModalVisible(false);
    // Don't reset modified data when canceling
    // setModifiedPriceData(null); <- Remove or comment this line
  };

  const handleOpenEnquiryModal = async () => {
    // Calculate total original price to set as default enquiry amount
    let totalOriginalPrice = 0;

    // Get all services
    const hotels = bookings.hotel || bookings.data?.hotel || [];
    const entryPorts = bookings.entry_port || bookings.data?.entry_port || [];
    const exitPorts = bookings.exit_port || bookings.data?.exit_port || [];
    const attractions = bookings.attraction || bookings.data?.attraction || [];
    const guides = bookings.guide || bookings.data?.guide || [];
    const restaurants = bookings.restaurant || bookings.data?.restaurant || [];
    const travelPoints =
      bookings.travel_point || bookings.data?.travel_point || [];

    // Calculate total original price - using a more efficient approach
    const allItems = [
      ...hotels,
      ...entryPorts,
      ...exitPorts,
      ...attractions,
      ...guides,
      ...restaurants,
      ...travelPoints,
    ];

    allItems.forEach((item) => {
      if (item.totalPrice) {
        totalOriginalPrice += parseFloat(item.totalPrice);
      }
    });

    // Set the enquiry amount to the current total price
    setEnquiryAmount(Math.ceil(totalOriginalPrice));

    // Log current state for debugging
    console.log("Current tourId state:", tourId);
    console.log("Current bookings data:", bookings);

    // Fetch enquiry history data
    try {
      setLoadingEnquiryHistory(true);
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken || !AgentId) {
        throw new Error("Authorization and AgentId are missing.");
      }

      // Try to get tour_id from multiple possible sources
      // First check if we have it in the component state (from handleViewDetails)
      // Then try to get it from various locations in the bookings object
      let currentTourId = tourId;

      if (!currentTourId) {
        currentTourId =
          bookings.id ||
          bookings.tour_id ||
          bookings.data?.id ||
          bookings.data?.tour_id;

        // Check hotel data for tour_id if it exists
        if (!currentTourId && hotels.length > 0 && hotels[0].tourDetails) {
          currentTourId = hotels[0].tourDetails.tour_id;
        }
      }

      console.log("Using tour_id for enquiry:", currentTourId);

      if (!currentTourId) {
        console.error("No tour_id available for fetching enquiry history");
        setEnquiryHistory([]);
        setLoadingEnquiryHistory(false);
        return;
      }

      const response = await axios.get(`${BASE_URL}/enquiry-status`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          "Content-Type": "application/json",
          "agent-id": AgentId,
        },
        params: {
          tour_id: currentTourId,
        },
      });

      console.log("Enquiry history response:", response.data);

      if (response.data && response.data.data) {
        // For single object response, convert to array
        const historyData = Array.isArray(response.data.data)
          ? response.data.data
          : [response.data.data];
        setEnquiryHistory(historyData);
        setEnquiryAmount(historyData[0].current_price);
        console.log("History data:", historyData[0].current_price);

        // Check if there's an entry with status 2 (Booked) or 3 (Cancel)
        const hasStatusBookedOrCancel = historyData.some(
          (item) =>
            item.status === "2" ||
            item.status === "3" ||
            item.status === 2 ||
            item.status === 3
        );

        console.log("Has status booked or cancel?", hasStatusBookedOrCancel);

        // If the status is 2 or 3, close the modal and don't show it
        if (hasStatusBookedOrCancel) {
          toast.info(
            "This enquiry has already been processed (Booked or Cancelled)"
          );
          setLoadingEnquiryHistory(false);
          return;
        }

        // Check if any entry in enquiryHistory has assigned as "agent"
        const isAssignedToAgent = historyData.some(
          (item) => item.assigned && item.assigned === "Agent"
        );
        console.log("Enquiry history data:", historyData);
        console.log("Is assigned to agent?", isAssignedToAgent);

        // Check if any entry has assigned as "OM" or other value
        const assignedValue =
          historyData.length > 0 ? historyData[0].assigned : "none";
        console.log("Assigned value found:", assignedValue);
        console.log("Is assigned OM?", assignedValue === "OM");

        // Set assigned state based on what we found
        if (
          assignedValue === "OM" ||
          (assignedValue && assignedValue !== "Agent")
        ) {
          console.log("Setting assigned to a non-Agent value:", assignedValue);
          setAssigned(assignedValue);
        } else {
          setAssigned(isAssignedToAgent ? "Agent" : null);
        }

        // Only show the modal if we don't have a booked or cancelled status
        setIsEnquiryModalVisible(true);
      } else {
        setEnquiryHistory([]);
        setAssigned(null);
        setIsEnquiryModalVisible(true);
      }
    } catch (error) {
      console.error("Error fetching enquiry history:", error);
      setEnquiryHistory([]);
      setIsEnquiryModalVisible(true);
    } finally {
      setLoadingEnquiryHistory(false);
    }
  };

  // Reset enquiryProcessed when closing the modal
  const handleCloseEnquiryModal = () => {
    setIsEnquiryModalVisible(false);
    setEnquiryComment("");
    setCommentError(false);
    setAssigned(null);
    // We don't reset enquiryProcessed here so the button stays hidden
  };

  const handleEnquiryAmountChange = (e) => {
    setEnquiryAmount(e.target.value);
  };

  // Create a shared function to submit enquiry with different types
  const submitEnquiry = async (type) => {
    try {
      setCommentError(false);

      // Comment is required only for 'enquiry' type
      if (type === "enquiry" && !enquiryComment.trim()) {
        setCommentError(true);
        toast.error("Please enter a comment for the enquiry");
        return;
      }

      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken || !AgentId) {
        toast.error("Authorization failed. Please login again.");
        return;
      }

      // Calculate actual price (original total price)
      let actualPrice = 0;

      // Get all services
      const hotels = bookings.hotel || bookings.data?.hotel || [];
      const entryPorts = bookings.entry_port || bookings.data?.entry_port || [];
      const exitPorts = bookings.exit_port || bookings.data?.exit_port || [];
      const attractions =
        bookings.attraction || bookings.data?.attraction || [];
      const guides = bookings.guide || bookings.data?.guide || [];
      const restaurants =
        bookings.restaurant || bookings.data?.restaurant || [];
      const travelPoints =
        bookings.travel_point || bookings.data?.travel_point || [];

      // Calculate total original price
      hotels.forEach((hotel) => {
        if (hotel.totalPrice) {
          actualPrice += parseFloat(hotel.totalPrice);
        }
      });

      entryPorts.forEach((port) => {
        if (port.totalPrice) {
          actualPrice += parseFloat(port.totalPrice);
        }
      });

      exitPorts.forEach((port) => {
        if (port.totalPrice) {
          actualPrice += parseFloat(port.totalPrice);
        }
      });

      attractions.forEach((attraction) => {
        if (attraction.totalPrice) {
          actualPrice += parseFloat(attraction.totalPrice);
        }
      });

      guides.forEach((guide) => {
        if (guide.totalPrice) {
          actualPrice += parseFloat(guide.totalPrice);
        }
      });

      restaurants.forEach((restaurant) => {
        if (restaurant.totalPrice) {
          actualPrice += parseFloat(restaurant.totalPrice);
        }
      });

      travelPoints.forEach((travelPoint) => {
        if (travelPoint.totalPrice) {
          actualPrice += parseFloat(travelPoint.totalPrice);
        }
      });

      // Use Math.ceil for the prices
      actualPrice = Math.ceil(actualPrice);
      const enquiryPrice = Math.ceil(enquiryAmount);

      // Get tour_id from bookings data
      const tour_id = bookings.id || bookings.data?.id || tourId;

      if (!tour_id) {
        toast.error("Tour ID is missing. Cannot submit enquiry.");
        return;
      }

      // Prepare data for API call
      const requestData = {
        tour_id: tour_id,
        comment: enquiryComment,
        total_price: actualPrice.toString(),
        enquiry_price: enquiryPrice.toString(),
        type: type, // Dynamic type based on action
      };

      console.log(`Submitting ${type} with data:`, requestData);

      // Make API call to the new endpoint, explicitly sending data in the request body
      const response = await axios({
        method: "post",
        url: `${BASE_URL}/update-enquiry`,
        data: requestData, // This ensures data is sent in the request body
        headers: {
          Authorization: `Bearer ${authToken}`,
          "Content-Type": "application/json",
          "agent-id": AgentId,
        },
      });

      if (response.data && response.data.success) {
        // Show success message based on action type
        let successMessage = "";

        switch (type) {
          case "accept":
            successMessage = "Enquiry accepted successfully!";
            // Store the tour ID that has a processed enquiry
            setTourWithProcessedEnquiry(tour_id);
            break;
          case "cancel":
            successMessage = "Enquiry cancelled successfully!";
            // Store the tour ID that has a processed enquiry
            setTourWithProcessedEnquiry(tour_id);
            break;
          default:
            successMessage = "Enquiry submitted successfully!";
        }

        toast.success(successMessage);

        // Close the modal for all types using the handleCloseEnquiryModal
        handleCloseEnquiryModal();

        // Refresh the list
        dispatch(fetchLists());
      } else {
        toast.error(
          response.data?.message || "Operation failed. Please try again."
        );
      }
    } catch (error) {
      console.error(`Error during ${type} operation:`, error);
      toast.error("Something went wrong. Please try again later.");
    }
  };

  // Update the handleEnquirySubmit function to use the shared function
  const handleEnquirySubmit = () => submitEnquiry("enquiry");

  const [totalPrice, setTotalPrice] = useState(0);

  // Calculate total price when bookings data changes
  useEffect(() => {
    // Calculate total original price
    let calculatedTotalPrice = 0;

    if (bookings) {
      // Get all services
      const hotels = bookings.hotel || bookings.data?.hotel || [];
      const entryPorts = bookings.entry_port || bookings.data?.entry_port || [];
      const exitPorts = bookings.exit_port || bookings.data?.exit_port || [];
      const travelPoints =
        bookings.travel_point || bookings.data?.travel_point || [];
      const attractions =
        bookings.attraction || bookings.data?.attraction || [];
      const guides = bookings.guide || bookings.data?.guide || [];
      const restaurants =
        bookings.restaurant || bookings.data?.restaurant || [];

      // Calculate total original price
      hotels.forEach((hotel) => {
        if (hotel.totalPrice) {
          calculatedTotalPrice += parseFloat(hotel.totalPrice);
        }
      });

      entryPorts.forEach((port) => {
        if (port.totalPrice) {
          calculatedTotalPrice += parseFloat(port.totalPrice);
        }
      });

      exitPorts.forEach((port) => {
        if (port.totalPrice) {
          calculatedTotalPrice += parseFloat(port.totalPrice);
        }
      });

      travelPoints.forEach((travel) => {
        if (travel.totalPrice) {
          calculatedTotalPrice += parseFloat(travel.totalPrice);
        }
      });

      attractions.forEach((attraction) => {
        if (attraction.totalPrice) {
          calculatedTotalPrice += parseFloat(attraction.totalPrice);
        }
      });

      guides.forEach((guide) => {
        if (guide.totalPrice) {
          calculatedTotalPrice += parseFloat(guide.totalPrice);
        }
      });

      restaurants.forEach((restaurant) => {
        if (restaurant.totalPrice) {
          calculatedTotalPrice += parseFloat(restaurant.totalPrice);
        }
      });

      setTotalPrice(Math.ceil(calculatedTotalPrice));
    }
  }, [bookings]);

  // New function to handle enquiry directly from the list
  const handleDirectEnquiry = async (list) => {
    try {
      console.log("Direct enquiry started for tour:", list.id);

      // Set necessary state variables
      setBookingType1(list.booking_type);
      setDisplayId(list.display_id);
      setTId(list.id);

      // Fetch the tour details
      await dispatch(fetchViewDetails({ tour_id: list.id })).unwrap();

      // Calculate the enquiry amount from the fetched data
      let totalPrice = 0;
      const services = [
        ...(bookings.hotel || bookings.data?.hotel || []),
        ...(bookings.entry_port || bookings.data?.entry_port || []),
        ...(bookings.exit_port || bookings.data?.exit_port || []),
        ...(bookings.attraction || bookings.data?.attraction || []),
        ...(bookings.guide || bookings.data?.guide || []),
        ...(bookings.restaurant || bookings.data?.restaurant || []),
        ...(bookings.travel_point || bookings.data?.travel_point || []),
      ];

      services.forEach((item) => {
        if (item.totalPrice) {
          totalPrice += parseFloat(item.totalPrice);
        }
      });

      setEnquiryAmount(Math.ceil(totalPrice) || 0);

      // Directly fetch enquiry history
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (authToken && AgentId) {
        const response = await axios.get(`${BASE_URL}/enquiry-status`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "Content-Type": "application/json",
            "agent-id": AgentId,
          },
          params: {
            tour_id: list.id,
          },
        });

        if (response.data && response.data.data) {
          const historyData = Array.isArray(response.data.data)
            ? response.data.data
            : [response.data.data];

          setEnquiryHistory(historyData);

          if (historyData.length > 0) {
            setEnquiryAmount(historyData[0].current_price);
          }

          // Check for processed status
          const hasProcessedStatus = historyData.some(
            (item) =>
              item.status === "2" ||
              item.status === "3" ||
              item.status === 2 ||
              item.status === 3
          );

          if (hasProcessedStatus) {
            toast.info(
              "This enquiry has already been processed (Booked or Cancelled)"
            );
            return;
          }

          // Set assignment status
          const assignedValue =
            historyData.length > 0 ? historyData[0].assigned : null;
          if (
            assignedValue === "OM" ||
            (assignedValue && assignedValue !== "Agent")
          ) {
            setAssigned(assignedValue);
          } else {
            const isAssignedToAgent = historyData.some(
              (item) => item.assigned && item.assigned === "Agent"
            );
            setAssigned(isAssignedToAgent ? "Agent" : null);
          }
        } else {
          setEnquiryHistory([]);
          setAssigned(null);
        }
      }

      // Finally, open the enquiry modal
      console.log("Opening enquiry modal directly");
      setIsEnquiryModalVisible(true);
    } catch (error) {
      console.error("Error preparing enquiry:", error);
      toast.error("Could not prepare the enquiry. Please try again.");
    }
  };

  return (
    <div
      className="dashboard"
      style={{ display: "flex", flexDirection: "column" }}
    >
      {/* <div style={{
       backgroundColor: '#f8f9fa',
        borderRadius: '12px',
        padding: '20px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.05)',
        marginBottom: '20px'
      }}>
        <div style={{
          display: 'flex',
          justifyContent: 'flex-end',
          alignItems: 'center',
          marginBottom: '15px'
        }}>
          <div className="col-auto">
            <button
              className="button -blue-1 h-40 px-20 rounded-100 bg-blue-1-05 text-15 text-blue-1 d-flex align-items-center"
              onClick={handleSort}
            >
              <i
                className={`icon-up-down text-14 mr-10 ${
                  order === "asc" ? "rotate-180" : ""
                }`}
              />
              Sort
            </button>
          </div>
        </div>

        
    
      </div> */}

      <div className="tabs__content pt-0 js-tabs-content">
        <div className="tabs__pane -tab-item-1 is-tab-el-active">
          <div
            style={{
              borderRadius: "12px",
              boxShadow: "0 4px 12px rgba(0,0,0,0.05)",
              overflow: "hidden",
              backgroundColor: "white",
              border: "1px solid #e0e6ed",
            }}
          >
            <div className="overflow-hidden">
              <table className="table-3 -border-bottom col-12" style={{ width: "100%", tableLayout: "fixed" }}>
                <thead className="bg-light-2">
                  <tr>
                    <th
                      style={{
                        backgroundColor: "#f5f7fc",
                        padding: "8px 12px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        whiteSpace: "nowrap",
                        transition: "background-color 0.3s ease",
                        width: "120px",
                        minWidth: "120px",
                        maxWidth: "120px",
                      }}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.backgroundColor = "#e6eafb";
                        e.currentTarget.querySelector("i").style.transform =
                          "rotate(90deg)";
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.backgroundColor = "#f5f7fc";
                        e.currentTarget.querySelector("i").style.transform =
                          "rotate(0deg)";
                      }}
                    >
                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          gap: "6px",
                          fontSize: "13px",
                          fontWeight: "600",
                          whiteSpace: "nowrap",
                        }}
                      >
                        <i
                          className="icon-menu"
                          style={{
                            fontSize: "14px",
                            color: "#3554D1",
                          }}
                        ></i>
                        Actions
                      </div>
                    </th>
                    <th
                      style={{
                        backgroundColor: "#f5f7fc",
                        padding: "8px 12px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        whiteSpace: "nowrap",
                        transition: "background-color 0.3s ease",
                        width: "100px",
                        minWidth: "100px",
                        maxWidth: "100px",
                      }}
                      onClick={() => handleColumnSort("id")}
                      onMouseEnter={(e) =>
                        (e.currentTarget.style.backgroundColor = "#e6eafb")
                      }
                      onMouseLeave={(e) =>
                        (e.currentTarget.style.backgroundColor = "#f5f7fc")
                      }
                      title={`Sort by Booking ID (${
                        sortColumn === "id"
                          ? order === "asc"
                            ? "ascending"
                            : "descending"
                          : "click to sort"
                      })`}
                    >
                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          gap: "6px",
                          fontSize: "13px",
                          fontWeight: "600",
                        }}
                      >
                        <i
                          className="icon-ticket"
                          style={{
                            fontSize: "14px",
                            color: "#3554D1",
                          }}
                        ></i>
                        Booking ID
                        {sortColumn === "id" && (
                          <i
                            className={`icon-up-down text-blue-1 mr-5 ${
                              order === "asc" ? "rotate-180" : ""
                            }`}
                            style={{
                              fontSize: "12px",
                              opacity: order === "asc" ? 1 : 0.7,
                              fontWeight: order === "asc" ? "normal" : "bold",
                            }}
                          ></i>
                        )}
                      </div>
                    </th>
                    <th
                      style={{
                        backgroundColor: "#f5f7fc",
                        padding: "8px 12px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
                        width: "90px",
                        minWidth: "90px",
                        maxWidth: "90px",
                      }}
                      onClick={() => handleColumnSort("startDate")}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.backgroundColor = "#e6eafb";
                        e.currentTarget.querySelector("i").style.transform =
                          "scale(1.2)";
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.backgroundColor = "#f5f7fc";
                        e.currentTarget.querySelector("i").style.transform =
                          "scale(1)";
                      }}
                      title={`Sort by Start Date (${
                        sortColumn === "startDate"
                          ? order === "asc"
                            ? "ascending"
                            : "descending"
                          : "click to sort"
                      })`}
                    >
                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          gap: "6px",
                          fontSize: "13px",
                          fontWeight: "600",
                        }}
                      >
                        <i
                          className="icon-calendar"
                          style={{
                            fontSize: "14px",
                            color: "#3554D1",
                          }}
                        ></i>
                        Start Date
                        {sortColumn === "startDate" && (
                          <i
                            className={`icon-up-down text-blue-1 mr-5 ${
                              order === "asc" ? "rotate-180" : ""
                            }`}
                            style={{
                              fontSize: "12px",
                              opacity: order === "asc" ? 1 : 0.7,
                              fontWeight: order === "asc" ? "normal" : "bold",
                            }}
                          ></i>
                        )}
                      </div>
                    </th>
                    <th
                      style={{
                        backgroundColor: "#f5f7fc",
                        padding: "8px 12px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
                        width: "90px",
                        minWidth: "90px",
                        maxWidth: "90px",
                      }}
                      onClick={() => handleColumnSort("endDate")}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.backgroundColor = "#e6eafb";
                        e.currentTarget.querySelector("i").style.transform =
                          "scale(1.2)";
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.backgroundColor = "#f5f7fc";
                        e.currentTarget.querySelector("i").style.transform =
                          "scale(1)";
                      }}
                      title={`Sort by End Date (${
                        sortColumn === "endDate"
                          ? order === "asc"
                            ? "ascending"
                            : "descending"
                          : "click to sort"
                      })`}
                    >
                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          gap: "6px",
                          fontSize: "13px",
                          fontWeight: "600",
                        }}
                      >
                        <i
                          className="icon-calendar-2"
                          style={{
                            fontSize: "14px",
                            color: "#3554D1",
                          }}
                        ></i>
                        End Date
                        {sortColumn === "endDate" && (
                          <i
                            className={`icon-up-down text-blue-1 mr-5 ${
                              order === "asc" ? "rotate-180" : ""
                            }`}
                            style={{
                              fontSize: "12px",
                              opacity: order === "asc" ? 1 : 0.7,
                              fontWeight: order === "asc" ? "normal" : "bold",
                            }}
                          ></i>
                        )}
                      </div>
                    </th>
                    <th
                      style={{
                        backgroundColor: "#f5f7fc",
                        padding: "6px 8px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
                        width: "60px",
                        minWidth: "60px",
                        maxWidth: "60px",
                        whiteSpace: "nowrap",
                        overflow: "hidden",
                        textOverflow: "ellipsis",
                      }}
                      onClick={() => handleColumnSort("pax")}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.backgroundColor = "#e6eafb";
                        e.currentTarget.querySelector("i").style.transform =
                          "rotate(15deg)";
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.backgroundColor = "#f5f7fc";
                        e.currentTarget.querySelector("i").style.transform =
                          "rotate(0deg)";
                      }}
                      title={`Sort by Pax Count (${
                        sortColumn === "pax"
                          ? order === "asc"
                            ? "ascending"
                            : "descending"
                          : "click to sort"
                      })`}
                    >
                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          gap: "6px",
                          fontSize: "13px",
                          fontWeight: "600",
                        }}
                      >
                        <i
                          className="icon-group"
                          style={{
                            fontSize: "14px",
                            color: "#3554D1",
                          }}
                        ></i>
                        Pax
                        {sortColumn === "pax" && (
                          <i
                            className={`icon-arrow-${
                              order === "asc" ? "down" : "up"
                            }`}
                            style={{
                              fontSize: "12px",
                              opacity: order === "asc" ? 1 : 0.7,
                              fontWeight: order === "asc" ? "bold" : "normal",
                            }}
                          ></i>
                        )}
                      </div>
                    </th>
                    <th
                      style={{
                        backgroundColor: "#f5f7fc",
                        padding: "8px 12px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
                        width: "150px",
                        minWidth: "150px",
                        maxWidth: "150px",
                      }}
                      onClick={() => handleColumnSort("destination")}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.backgroundColor = "#e6eafb";
                        e.currentTarget.querySelector("i").style.transform =
                          "translateY(-3px)";
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.backgroundColor = "#f5f7fc";
                        e.currentTarget.querySelector("i").style.transform =
                          "translateY(0)";
                      }}
                    >
                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          gap: "6px",
                          fontSize: "13px",
                          fontWeight: "600",
                        }}
                      >
                        <i
                          className="icon-destination"
                          style={{
                            fontSize: "14px",
                            color: "#3554D1",
                          }}
                        ></i>
                        Destination
                      </div>
                    </th>
                    <th
                      style={{
                        backgroundColor: "#f5f7fc",
                        padding: "8px 12px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
                        whiteSpace: "nowrap",
                        width: "130px",
                        minWidth: "130px",
                        maxWidth: "130px",
                      }}
                      // onClick={() => handleColumnSort("status")}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.backgroundColor = "#e6eafb";
                        e.currentTarget.querySelector("i").style.transform =
                          "scale(1.2)";
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.backgroundColor = "#f5f7fc";
                        e.currentTarget.querySelector("i").style.transform =
                          "scale(1)";
                      }}
                    >
                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          gap: "6px",
                          fontSize: "13px",
                          fontWeight: "600",
                        }}
                      >
                        <i
                          className="icon-customer"
                          style={{
                            fontSize: "14px",
                            color: "#3554D1",
                          }}
                        ></i>
                        Customer Name
                      </div>
                    </th>
                    <th
                      style={{
                        backgroundColor: "#f5f7fc",
                        padding: "8px 12px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
                        width: "120px",
                        minWidth: "120px",
                        maxWidth: "120px",
                      }}
                      onClick={() => handleColumnSort("status")}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.backgroundColor = "#e6eafb";
                        e.currentTarget.querySelector("i").style.transform =
                          "scale(1.2)";
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.backgroundColor = "#f5f7fc";
                        e.currentTarget.querySelector("i").style.transform =
                          "scale(1)";
                      }}
                    >
                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          gap: "6px",
                          fontSize: "13px",
                          fontWeight: "600",
                        }}
                      >
                        <i
                          className="icon-check"
                          style={{
                            fontSize: "14px",
                            color: "#3554D1",
                          }}
                        ></i>
                        Status
                      </div>
                    </th>
                    <th
                      style={{
                        backgroundColor: "#f5f7fc",
                        padding: "8px 12px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
                        whiteSpace: "nowrap",
                        width: "110px",
                        minWidth: "110px",
                        maxWidth: "110px",
                      }}
                      // onClick={() => handleColumnSort("status")}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.backgroundColor = "#e6eafb";
                        e.currentTarget.querySelector("i").style.transform =
                          "scale(1.2)";
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.backgroundColor = "#f5f7fc";
                        e.currentTarget.querySelector("i").style.transform =
                          "scale(1)";
                      }}
                    >
                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          gap: "6px",
                          fontSize: "13px",
                          fontWeight: "600",
                        }}
                      >
                        <i
                          className="icon-usd"
                          style={{
                            fontSize: "14px",
                            color: "#3554D1",
                          }}
                        ></i>
                        Payment
                      </div>
                    </th>
                    <th
                      style={{
                        backgroundColor: "#f5f7fc",
                        padding: "8px 12px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
                        whiteSpace: "nowrap",
                        width: "120px",
                        minWidth: "120px",
                        maxWidth: "120px",
                      }}
                      // onClick={() => handleColumnSort("status")}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.backgroundColor = "#e6eafb";
                        e.currentTarget.querySelector("i").style.transform =
                          "scale(1.2)";
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.backgroundColor = "#f5f7fc";
                        e.currentTarget.querySelector("i").style.transform =
                          "scale(1)";
                      }}
                    >
                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          gap: "6px",
                          fontSize: "13px",
                          fontWeight: "600",
                        }}
                      >
                        <i
                          className="icon-usd"
                          style={{
                            fontSize: "14px",
                            color: "#3554D1",
                          }}
                        ></i>
                        Payment Status
                      </div>
                    </th>
                    <th
                      style={{
                        backgroundColor: "#f5f7fc",
                        padding: "8px 12px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
                        whiteSpace: "nowrap",
                        width: "110px",
                        minWidth: "110px",
                        maxWidth: "110px",
                      }}
                    >
                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          gap: "6px",
                          fontSize: "13px",
                          fontWeight: "600",
                        }}
                      >
                        <i className="icon-calendar" style={{ fontSize: "14px", color: "#3554D1" }}></i>
                        Created
                      </div>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {/* Show skeleton loading while status is "loading" */}
                  {status === "loading" ? (
                    Array.from(new Array(rowsPerPage)).map((_, index) => (
                      <tr key={index}>
                        <td>
                          <Skeleton variant="text" width={100} />
                        </td>
                        <td>
                          <Skeleton variant="text" width={80} />
                        </td>
                        <td>
                          <Skeleton variant="text" width={80} />
                        </td>
                        <td>
                          <Skeleton variant="text" width={60} />
                        </td>
                        <td>
                          <Skeleton variant="text" width={60} />
                        </td>
                        <td>
                          <Skeleton variant="text" width={60} />
                        </td>
                        <td>
                          <Skeleton variant="text" width={60} />
                        </td>
                        <td>
                          <Skeleton variant="text" width={60} />
                        </td>
                        <td>
                          <Skeleton variant="text" width={120} />
                        </td>
                        <td>
                          <Skeleton
                            variant="rectangular"
                            width={130}
                            height={35}
                          />
                        </td>
                        <td style={{ padding: "8px 12px", width: "110px", minWidth: "110px", maxWidth: "110px", whiteSpace: "nowrap" }}>
                          <Skeleton variant="text" width={100} />
                        </td>
                      </tr>
                    ))
                  ) : paginatedLists.length > 0 ? (
                    paginatedLists.map((list, index) => (
                      <tr
                        key={index}
                        style={{
                          transition: "background-color 0.3s ease",
                          ":hover": {
                            backgroundColor: "#f8f9fa",
                          },
                        }}
                      >
                        <td
                          style={{
                            padding: "8px 12px",
                            minWidth: "120px",
                            maxWidth: "120px",
                            whiteSpace: "nowrap",
                          }}
                        >
                          <div
                            style={{
                              display: "flex",
                              gap: "8px",
                              flexWrap: "nowrap",
                              alignItems: "center",
                            }}
                          >
                            <Tooltip title="View Details" arrow>
                              <IconButton
                                size="small"
                                onClick={() => handleViewDetails(list)}
                                                                  sx={{
                                    color: "#4361ee",
                                    width: "28px",
                                    height: "28px",
                                    borderRadius: "6px",
                                    backgroundColor: "rgba(0, 0, 0, 0.04)",
                                    boxShadow: "0 2px 4px rgba(0, 0, 0, 0.1)",
                                    transition: "all 0.2s ease",
                                    "&:hover": {
                                      backgroundColor: "rgba(67, 97, 238, 0.12)",
                                      boxShadow: "0 4px 8px rgba(0, 0, 0, 0.15)",
                                      transform: "translateY(-1px)",
                                    },
                                  }}
                                >
                                  <Visibility sx={{ fontSize: "16px" }} />
                              </IconButton>
                            </Tooltip>

                            {/* Only render Edit button if editOff is not 1 */}
                            {list.editOff !== 1 && (
                              <Tooltip title="Add More Services" arrow>
                                <IconButton
                                  size="small"
                                  onClick={() => handleEdit(list)}
                                  sx={{
                                    color: "#2e7d32",
                                    width: "28px",
                                    height: "28px",
                                    borderRadius: "6px",
                                    backgroundColor: "rgba(0, 0, 0, 0.04)",
                                    boxShadow: "0 2px 4px rgba(0, 0, 0, 0.1)",
                                    transition: "all 0.2s ease",
                                    "&:hover": {
                                      backgroundColor: "rgba(46, 125, 50, 0.12)",
                                      boxShadow: "0 4px 8px rgba(0, 0, 0, 0.15)",
                                      transform: "translateY(-1px)",
                                    },
                                  }}
                                >
                                  <Edit sx={{ fontSize: "16px" }} />
                                </IconButton>
                              </Tooltip>
                            )}

                            {list.booking_type === "enquiry" &&
                              userRole === "Agent" && (
                                <Tooltip title="Negotiate" arrow>
                                  <IconButton
                                    size="small"
                                    onClick={() => handleDirectEnquiry(list)}
                                    sx={{
                                      color: "#7b1fa2",
                                      width: "28px",
                                      height: "28px",
                                      borderRadius: "6px",
                                      backgroundColor: "rgba(0, 0, 0, 0.04)",
                                      boxShadow: "0 2px 4px rgba(0, 0, 0, 0.1)",
                                      transition: "all 0.2s ease",
                                      "&:hover": {
                                        backgroundColor: "rgba(123, 31, 162, 0.12)",
                                        boxShadow: "0 4px 8px rgba(0, 0, 0, 0.15)",
                                        transform: "translateY(-1px)",
                                      },
                                    }}
                                  >
                                    <AttachMoney sx={{ fontSize: "16px" }} />
                                  </IconButton>
                                </Tooltip>
                              )}

                            <Tooltip title="Update Tour Plan" arrow>
                              <IconButton
                                size="small"
                                onClick={() => handleUpdate(list)}
                                sx={{
                                  color: "#f57c00",
                                  width: "28px",
                                  height: "28px",
                                  borderRadius: "6px",
                                  backgroundColor: "rgba(0, 0, 0, 0.04)",
                                  boxShadow: "0 2px 4px rgba(0, 0, 0, 0.1)",
                                  transition: "all 0.2s ease",
                                  "&:hover": {
                                    backgroundColor: "rgba(245, 124, 0, 0.12)",
                                    boxShadow: "0 4px 8px rgba(0, 0, 0, 0.15)",
                                    transform: "translateY(-1px)",
                                  },
                                }}
                              >
                                <Update sx={{ fontSize: "16px" }} />
                              </IconButton>
                            </Tooltip>
                          </div>
                          {list.dmc_company_name && (
                          <Tooltip 
                            title={list.dmc_company_name }
                            arrow
                            placement="top"
                          >
                            <div 
                              style={{ 
                                fontSize: "12px", 
                                fontWeight: "600",
                                overflow: "hidden",
                                textOverflow: "ellipsis",
                                whiteSpace: "nowrap",
                                maxWidth: "100%",
                                marginTop: "10px",
                                textAlign: "center",
                                backgroundColor: "#3554D1",
                                color: "#fff",
                                padding: "5px 10px",
                                borderRadius: "50px",
                                cursor: "pointer",
                              }}
                            >
                              {list.dmc_company_name }
                            </div>
                          </Tooltip>
                          )}
                        </td>
                        <td style={{ padding: "16px 20px", width: "100px", minWidth: "100px", maxWidth: "100px" }}>
                          <div
                            style={{
                              backgroundColor: "rgba(53, 84, 209, 0.1)",
                              padding: "6px 8px",
                              borderRadius: "12px",
                              fontSize: "11px",
                              color: "#3554D1",
                              fontWeight: "600",
                              display: "inline-flex",
                              flexDirection: "column",
                              alignItems: "center",
                              whiteSpace: "nowrap",
                            }}
                          >
                            {/* Display ID on top */}
                            <span>{list.display_id}</span>

                            {/* order_from below, if exists */}
                            {list.order_from?.trim() && (
                              <span
                                style={{
                                  backgroundColor: "#3554D1",
                                  color: "#fff",
                                  padding: "2px 8px",
                                  borderRadius: "50px",
                                  fontSize: "12px",
                                  marginTop: "4px",
                                }}
                              >
                                {list.order_from}
                              </span>
                            )}
                            {list.multi_enq_id && (
                              <div 
                              style={{ 
                               
                                textAlign: "center",
                                //backgroundColor: "#3554D1",
                                //color: "#fff",
                                color: "#3554D1",
                                padding: "5px 10px",
                                borderRadius: "50px",
                                cursor: "pointer",
                              }}
                            >
                              {list.multi_enq_id}
                            </div>
                            )}
                          </div>
                        </td>

                        <td style={{ padding: "16px 20px", width: "90px", minWidth: "90px", maxWidth: "90px" }}>
                          <Tooltip 
                            title={formatDateTooltip(list.check_in_time)}
                            arrow
                            placement="top"
                          >
                            <div
                              style={{
                                backgroundColor: "rgba(76, 175, 80, 0.1)",
                                padding: "6px 8px",
                                borderRadius: "12px",
                                fontSize: "15px",
                                color: "#4CAF50",
                                fontWeight: "600",
                                display: "inline-flex",
                                alignItems: "center",
                                justifyContent: "center",
                                whiteSpace: "nowrap",
                                cursor: "pointer",
                              }}
                            >
                              <span style={{ fontWeight: "600", fontSize: "15px" }}>{formatDate(list.check_in_time)}</span>
                            </div>
                          </Tooltip>
                        </td>
                        <td style={{ padding: "16px 20px", width: "90px", minWidth: "90px", maxWidth: "90px" }}>
                          <Tooltip 
                            title={formatDateTooltip(list.check_out_time)}
                            arrow
                            placement="top"
                          >
                            <div
                              style={{
                                backgroundColor: "rgba(244, 67, 54, 0.1)",
                                padding: "6px 8px",
                                borderRadius: "12px",
                                fontSize: "15px",
                                color: "#F44336",
                                fontWeight: "600",
                                display: "inline-flex",
                                alignItems: "center",
                                justifyContent: "center",
                                whiteSpace: "nowrap",
                                cursor: "pointer",
                              }}
                            >
                              <span style={{ fontWeight: "600", fontSize: "15px" }}>{formatDate(list.check_out_time)}</span>
                            </div>
                          </Tooltip>
                        </td>
                        <td
                          style={{
                            padding: "16px 10px",
                            whiteSpace: "nowrap",
                            width: "60px",
                            minWidth: "60px",
                            maxWidth: "60px",
                            textAlign: "center",
                          }}
                        >
                          <div
                            style={{
                              display: "inline-flex",
                              alignItems: "center",
                              justifyContent: "center",
                              gap: "4px",
                              backgroundColor: "rgba(255, 152, 0, 0.1)",
                              padding: "5px 8px",
                              borderRadius: "20px",
                              whiteSpace: "nowrap",
                              width: "fit-content",
                              margin: "0 auto",
                            }}
                          >
                            <i
                              className="icon-passenger"
                              style={{ fontSize: "18px", color: "#FF9800" }}
                            ></i>
                            <span
                              style={{
                                fontWeight: "600",
                                fontSize: "15px",
                                color: "#FF9800",
                                whiteSpace: "nowrap",
                              }}
                            >
                              {list.total_pax}
                            </span>
                          </div>
                        </td>

                        <td style={{ padding: "16px 20px", width: "150px", minWidth: "150px", maxWidth: "150px" }}>
                          <div
                            style={{
                              display: "flex",
                              alignItems: "center",
                              gap: "8px",
                            }}
                          >
                            <i
                              className="icon-destination"
                              style={{ fontSize: "18px", color: "#4CAF50" }}
                            ></i>
                            <span
                              style={{
                                fontWeight: "500",
                                color: "#4CAF50",
                                whiteSpace: "nowrap",
                              }}
                            >
                              {list.destination
                                ? list.destination
                                    .split(",")
                                    .map((code) => {
                                      const trimmedCode = code.trim();
                                      // First try to get the name from codeToName mapping
                                      const countryName =
                                        countryMappings.codeToName[trimmedCode];
                                      if (countryName) {
                                        return countryName;
                                      }
                                      // If not found in codeToName, check if it's already a name
                                      if (
                                        countryMappings.nameToCode[trimmedCode]
                                      ) {
                                        return trimmedCode;
                                      }
                                      // If neither found, return the original code
                                      return trimmedCode;
                                    })
                                    .join(", ")
                                : "N/A"}
                            </span>
                          </div>
                        </td>
                        <td style={{ padding: "16px 20px", width: "130px", minWidth: "130px", maxWidth: "130px" }}>
                          <div
                            style={{
                              display: "flex",
                              alignItems: "center",
                              gap: "8px",
                            }}
                          >
                            <i
                              className="icon-customer"
                              style={{ fontSize: "18px", color: "#F44336" }}
                            ></i>
                            <span style={{ overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap" }}>
                              {list.customer_name || "N/A"}
                            </span>
                          </div>
                        </td>
                        <td style={{ padding: "16px 20px", width: "120px", minWidth: "120px", maxWidth: "120px" }}>
                          <div
                            style={{
                              display: "flex",
                              alignItems: "center",
                              gap: "6px",
                              backgroundColor: getBackgroundColor(
                                list.tour_status
                              ),
                              padding: "4px 8px",
                              borderRadius: "16px",
                            }}
                          >
                            <i
                              className={(() => {
                                // const status = list.status
                                //   ? list.status.toLowerCase()
                                //   : "";
                                if (status.includes("pending"))
                                  return "icon-clock";
                                if (
                                  status.includes("accept") ||
                                  status.includes("approved")
                                )
                                  return "icon-check";
                                if (
                                  status.includes("reject") ||
                                  status.includes("cancel")
                                )
                                  return "icon-close";
                                return "icon-info-circle";
                              })()}
                              style={{
                                fontSize: "14px",
                                color: getTextColor(list.tour_status),
                              }}
                            ></i>
                            <span
                              style={{
                                fontWeight: "600",
                                color: getTextColor(list.tour_status),
                                fontSize: "11px",
                                whiteSpace: "nowrap",
                              }}
                            >
                              {" "}
                              {list.tour_status || "Pending"}
                            </span>
                          </div>
                        </td>
                        <td
                          style={{
                            padding: "8px 12px",
                            width: "110px",
                            minWidth: "110px",
                            maxWidth: "110px",
                          }}
                        >
                          <Tooltip 
                            title={`Original: SGD ${Math.ceil(list.finalAmount + list.discountAmount)} | Discount: SGD ${Math.ceil(list.discountAmount)} | Final: SGD ${Math.ceil(list.finalAmount)}`}
                            arrow
                            placement="top"
                          >
                            <div
                              style={{
                                borderRadius: "8px",
                                padding: "10px 12px",
                                backgroundColor: "rgba(225, 245, 255, 0.9)",
                                border: "1px solid rgba(33, 150, 243, 0.2)",
                                boxShadow: "0 2px 8px rgba(33, 150, 243, 0.15)",
                                transition: "all 0.3s ease",
                                cursor: "pointer",
                              }}
                              onMouseEnter={(e) => {
                                e.currentTarget.style.transform = "translateY(-2px)";
                                e.currentTarget.style.boxShadow = "0 4px 12px rgba(33, 150, 243, 0.25)";
                              }}
                              onMouseLeave={(e) => {
                                e.currentTarget.style.transform = "translateY(0)";
                                e.currentTarget.style.boxShadow = "0 2px 8px rgba(33, 150, 243, 0.15)";
                              }}
                            >
                              <div
                                style={{
                                  display: "flex",
                                  alignItems: "center",
                                  justifyContent: "space-between",
                                  marginBottom: "6px",
                                }}
                              >
                                <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
                                  <i className="icon-usd" style={{ fontSize: "12px", color: "#e53935" }}></i>
                                  <span style={{ fontSize: "10px", color: "#e53935", fontWeight: "600" }}>
                                    -{Math.ceil(list.discountAmount)}
                                  </span>
                                </div>
                                <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
                                  <i className="icon-tag" style={{ fontSize: "12px", color: "#1976d2" }}></i>
                                  <span style={{ fontSize: "10px", color: "#1976d2", fontWeight: "600" }}>
                                    {Math.ceil(list.finalAmount)}
                                  </span>
                                </div>
                              </div>
                              <div
                                style={{
                                  display: "flex",
                                  alignItems: "center",
                                  justifyContent: "center",
                                  gap: "6px",
                                  padding: "4px 8px",
                                  backgroundColor: "rgba(255, 255, 255, 0.7)",
                                  borderRadius: "6px",
                                  border: "1px solid rgba(33, 150, 243, 0.1)",
                                }}
                              >
                                <i className="icon-wallet" style={{ fontSize: "14px", color: "#1976d2" }}></i>
                                <span style={{ fontSize: "12px", fontWeight: "700", color: "#1976d2" }}>
                                  SGD {Math.ceil(list.finalAmount)}
                                </span>
                              </div>
                            </div>
                          </Tooltip>
                        </td>

                        <td style={{ padding: "8px 12px", width: "120px", minWidth: "120px", maxWidth: "120px" }}>
                          <Tooltip 
                            title={`Status: ${list.payment_status}${list.dueAmount > 0 ? ` | Due: SGD ${Math.ceil(list.dueAmount)}` : ''}`}
                            arrow
                            placement="top"
                          >
                            <div
                              style={{
                                borderRadius: "8px",
                                padding: "10px 12px",
                                backgroundColor:
                                  list.payment_status === "Not Paid"
                                    ? "rgba(253, 236, 234, 0.95)"
                                    : list.payment_status === "Partially Paid"
                                    ? "rgba(227, 242, 253, 0.95)"
                                    : list.payment_status === "Completely Paid"
                                    ? "rgba(232, 245, 233, 0.95)"
                                    : "rgba(224, 224, 224, 0.95)",
                                border: `1px solid ${
                                  list.payment_status === "Not Paid"
                                    ? "rgba(211, 47, 47, 0.3)"
                                    : list.payment_status === "Partially Paid"
                                    ? "rgba(25, 118, 210, 0.3)"
                                    : list.payment_status === "Completely Paid"
                                    ? "rgba(56, 142, 60, 0.3)"
                                    : "rgba(97, 97, 97, 0.3)"
                                }`,
                                boxShadow: "0 2px 8px rgba(0, 0, 0, 0.12)",
                                transition: "all 0.3s ease",
                                cursor: "pointer",
                              }}
                              onMouseEnter={(e) => {
                                e.currentTarget.style.transform = "translateY(-2px)";
                                e.currentTarget.style.boxShadow = "0 4px 12px rgba(0, 0, 0, 0.2)";
                              }}
                              onMouseLeave={(e) => {
                                e.currentTarget.style.transform = "translateY(0)";
                                e.currentTarget.style.boxShadow = "0 2px 8px rgba(0, 0, 0, 0.12)";
                              }}
                            >
                              {list.dueAmount > 0 && (
                                <div
                                  style={{
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: "center",
                                    gap: "4px",
                                    marginBottom: "6px",
                                    padding: "3px 8px",
                                    backgroundColor: "rgba(229, 57, 53, 0.1)",
                                    borderRadius: "12px",
                                    border: "1px solid rgba(229, 57, 53, 0.2)",
                                  }}
                                >
                                  <i className="icon-alert-circle" style={{ fontSize: "10px", color: "#e53935" }}></i>
                                  <span style={{ fontSize: "10px", color: "#e53935", fontWeight: "600" }}>
                                    Due: SGD {Math.ceil(list.dueAmount)}
                                  </span>
                                </div>
                              )}
                              <div
                                style={{
                                  display: "flex",
                                  alignItems: "center",
                                  justifyContent: "center",
                                  gap: "6px",
                                  padding: "4px 8px",
                                  backgroundColor: "rgba(255, 255, 255, 0.8)",
                                  borderRadius: "6px",
                                  border: "1px solid rgba(0, 0, 0, 0.1)",
                                }}
                              >
                                <i 
                                  className={
                                    list.payment_status === "Not Paid"
                                      ? "icon-x-circle"
                                      : list.payment_status === "Partially Paid"
                                      ? "icon-clock"
                                      : list.payment_status === "Completely Paid"
                                      ? "icon-check-circle"
                                      : "icon-help-circle"
                                  }
                                  style={{ 
                                    fontSize: "14px", 
                                    color:
                                      list.payment_status === "Not Paid"
                                        ? "#d32f2f"
                                        : list.payment_status === "Partially Paid"
                                        ? "#1976d2"
                                        : list.payment_status === "Completely Paid"
                                        ? "#388e3c"
                                        : "#616161"
                                  }}
                                ></i>
                                <span
                                  style={{
                                    fontWeight: "700",
                                    fontSize: "12px",
                                    color:
                                      list.payment_status === "Not Paid"
                                        ? "#d32f2f"
                                        : list.payment_status === "Partially Paid"
                                        ? "#1976d2"
                                        : list.payment_status === "Completely Paid"
                                        ? "#388e3c"
                                        : "#616161",
                                  }}
                                >
                                  {list.payment_status}
                                </span>
                              </div>
                            </div>
                          </Tooltip>
                        </td>
                        <td style={{ padding: "8px 12px", width: "110px", minWidth: "110px", maxWidth: "110px", whiteSpace: "nowrap", display: "flex", alignItems: "center", justifyContent: "center", height: "100%" }}>
                          <span style={{ fontSize: "11px" }}>
                            {list.created_at ? dayjs(list.created_at).format("DD MMM YYYY, HH:mm") : "-"}
                          </span>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td
                        colSpan="12"
                        style={{ textAlign: "center", padding: "40px 20px" }}
                      >
                        <div
                          style={{
                            display: "flex",
                            flexDirection: "column",
                            alignItems: "center",
                            gap: "15px",
                          }}
                        >
                          <div
                            style={{
                              width: "70px",
                              height: "70px",
                              borderRadius: "50%",
                              backgroundColor: "rgba(53, 84, 209, 0.1)",
                              display: "flex",
                              alignItems: "center",
                              justifyContent: "center",
                              fontSize: "30px",
                              color: "#3554D1",
                            }}
                          >
                            <i className="icon-info-circle"></i>
                          </div>
                          <h3 style={{ margin: "0", color: "#3554D1" }}>
                            No tours found
                          </h3>
                          <p style={{ margin: "0", color: "#697488" }}>
                            There are currently no tours to display
                          </p>
                        </div>
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <Box
        sx={{
          display: "flex",
          flexDirection: "column",
          justifyContent: "center", // Center horizontally
          alignItems: "center", // Center vertically
          padding: "20px 0",
          width: "100%", // Ensure it takes the full width
        }}
      >
        {/* Replace TablePagination with custom Pagination component */}
        <Pagination
          currentPage={page + 1} // Pass current page (1-based)
          setCurrentPage={(newPage) => setPage(newPage - 1)} // Update state to 0-based
          totalPages={totalPages}
        />
      </Box>

      <Snackbar
        open={openSnackbar}
        autoHideDuration={3000}
        onClose={handleCloseSnackbar}
        anchorOrigin={{ vertical: "top", horizontal: "center" }}
      >
        <Alert onClose={handleCloseSnackbar} severity={snackbarSeverity}>
          {snackbarMessage}
        </Alert>
      </Snackbar>

      <TourDetailsModal
        isModalVisible={isModalVisible}
        handleCloseModal={handleCloseModal}
        markupAmount={markupAmount}
        setMarkupAmount={setMarkupAmount}
        discountAmount={discountAmount}
        setDiscountAmount={setDiscountAmount}
        handlePrint={handlePrint}
        bookingType1={bookingType1}
        handleOpenEnquiryModal={handleOpenEnquiryModal}
      />

      {/* Enquiry Modal */}
      <EnquiryModal
        isEnquiryModalVisible={isEnquiryModalVisible}
        handleCloseEnquiryModal={handleCloseEnquiryModal}
        enquiryHistory={enquiryHistory}
        loadingEnquiryHistory={loadingEnquiryHistory}
        assigned={assigned}
        enquiryAmount={enquiryAmount}
        handleEnquiryAmountChange={handleEnquiryAmountChange}
        totalPrice={totalPrice}
        enquiryComment={enquiryComment}
        setEnquiryComment={setEnquiryComment}
        commentError={commentError}
        setCommentError={setCommentError}
        submitEnquiry={submitEnquiry}
        handleEnquirySubmit={handleEnquirySubmit}
      />

      {/* Print Modal */}
      <PrintModal
        isPrintModalVisible={isPrintModalVisible}
        handleCancelPrint={handleCancelPrint}
        handleDownloadPDF={handleDownloadPDF}
        contentRef={contentRef}
        viewDetailsStatus={viewDetailsStatus}
        DmcLogo={DmcLogo}
        DmcName={DmcName}
        displayId={displayId}
        bookings={modifiedPriceData || bookings}
        modifiedPriceData={modifiedPriceData}
        markupAmount={markupAmount}
        discountAmount={discountAmount}
        totalPrice={totalPrice}
        tourId={tourId}
      />
    </div>
  );
}
