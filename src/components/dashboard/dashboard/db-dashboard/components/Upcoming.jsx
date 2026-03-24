import React, { useState, useEffect, useMemo, useRef } from "react";
import "./ResponsiveTable.css";

import { useSelector, useDispatch } from "react-redux";
import { useNavigate } from "react-router-dom";
import { BASE_URL } from "@/services/api";
import { resetguide } from "@/slice/tourguide/guideslice";
import { resetVehicles1 } from "@/slice/localtour/Localslice";
import { resetVehicles } from "@/slice/port/pickupDropSlice";
import Box from "@mui/material/Box";
import { blue } from "@mui/material/colors";

import { Modal } from "antd";
import CategoriesAccordion from "./TourDetails";
import swal from "sweetalert";
import { fetchViewDetails } from "../../../../././../slice/common/ViewDetails";
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
  updateStepStatus,
  setType,
} from "@/slice/common/stepsSlice";
import { setTourIdd } from "@/slice/common/authSlices";
import { fetchEditid, setTourId1, deleteTour } from "@/slice/common/EditSlice";
import Snackbar from "@mui/material/Snackbar";
import MuiAlert from "@mui/material/Alert";
import { setDateService } from "@/slice/common/dateServicesSlice";
import { fetchLists, setTourType } from "@/slice/common/TourlistSlice";
import Pagination from "../../common/Pagination";
import { setBookingType, setHaveBooking } from "../../../../../slice/common/commonSlice";
import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";

dayjs.extend(customParseFormat);
import { Button, InputAdornment, TextField, Typography, IconButton, Tooltip } from "@mui/material";
import CustomPaymentTooltip from "./CustomTooltip";
import { Visibility, Edit, AttachMoney } from "@mui/icons-material";
import { toast } from "react-toastify";
import Cookies from "js-cookie";
import axios from "axios";
import {
  setUserInfo as customerInfoSetUserInfo,
  resetBookingState,
  clearUserInfo,
} from "@/slice/common/customerInfo";
import TourDetailsModal from "./TourDetailsModal";
import EnquiryModal from "./EnquiryModal";
import PrintModal from "./PrintModal";

const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

// Color functions for booking status styling
const getBackgroundColor = (tour_status) => {
  if (tour_status?.toLowerCase().startsWith("cancel")) {
    return "rgba(200, 50, 40, 0.06)"; // Professional light darker red
  }
  
  switch (tour_status) {
    case "Confirmed":
      return "rgba(33, 150, 243, 0.06)"; // Professional light blue
    case "Definite":
      return "rgba(76, 175, 80, 0.06)"; // Professional light green
    case "Actual":
      return "rgba(60, 140, 65, 0.06)"; // Professional light green
    case "Refund - Pending":
      return "rgba(255, 103, 2, 0.66)"; // Professional light deep orange
    case "Refunded":
      return "rgba(0, 136, 7, 0.66)"; // Professional light deep orange
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
    case "On Hold":
      return "#000000"; // Professional light orange
    default:
      return "#a9a9a9";
  }
};


// Text color function for booking status styling
const getTextColor = (tour_status) => {
  if (tour_status?.toLowerCase().startsWith("cancel")) {
    return "#F44336"; // Red
  }
  
  switch (tour_status) {
    case "Confirmed":
      return "#2196F3"; // Blue
    case "Definite":
      return "#4CAF50"; // Green
    case "Actual":
      return "#4CAF50"; // Green
    case "Pending":
      return "#F44336"; // Red
    case "Refund - Pending":
      return "#FFFFFF"; // Deep Orange
    case "Refunded":
      return "#FFFFFF"; // Deep Orange
    case "Tentative":
      return "#7E57C2"; // Violet
    case "New Enquiry":
      return "#FF9800"; // Orange
    case "Prospect":
      return "#FF5722"; // Deep Orange
    case "Closed":
      return "#000000"; // Black
    case "On Hold":
      return "#FFFFFF"; // Black
    default:
      return "#ffffff"; // Default gray
  }
};

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
const countryMap = {
  in: "India",
  SG: "Singapore",
};

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

// Truncate long customer names with word-aware cutoff
const truncateName = (name, maxLength = 10) => {
  if (!name) return "N/A";
  const trimmed = String(name).trim();
  if (trimmed.length <= maxLength) return trimmed;
  const slice = trimmed.slice(0, maxLength);
  const lastSpaceIndex = slice.lastIndexOf(" ");
  const base = lastSpaceIndex > 0 ? slice.slice(0, lastSpaceIndex) : slice;
  return `${base.trim()}...`;
};

export default function Pending({ filters = {} }) {
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(5);
  const [isModalVisible, setIsModalVisible] = useState(false);
  const [isPrintModalVisible, setIsPrintModalVisible] = useState(false);

  const [isEnquiryModalVisible, setIsEnquiryModalVisible] = useState(false);

  // Get the view details from the Redux store BEFORE using it in useState
  const { bookings = {}, status: viewDetailsStatus } = useSelector(
    (state) => state.viewDetails
  );
  
  // Extract price_hide from the fetched bookings data
  const priceHideFromBookings = bookings?.tour?.price_hide;
  
  // Debug logging
  // console.log("priceHideFromBookings:", priceHideFromBookings);
  // console.log("bookings.tour:", bookings?.tour);
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
        ...(bookings.travel_hourly || bookings.data?.travel_hourly || []),
        ...(bookings.local_transport || bookings.data?.local_transport || []),
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
  const contentRef = useRef(null);

  // Updated states for markup and discount (flat amounts instead of percentages)
  const [markupAmount, setMarkupAmount] = useState(0);
  const [discountAmount, setDiscountAmount] = useState(0);
  const [modifiedPriceData, setModifiedPriceData] = useState(null);
  const [bookingType1, setBookingType1] = useState(null);
  const [displayId, setDisplayId] = useState(null);
  const [pricehide, setPricehide] = useState(null);
  const { DmcName, DmcLogo } = useSelector((state) => state.auth);
  // Add these state declarations at the top of the component where other state variables are defined
  const [enquiryHistory, setEnquiryHistory] = useState([]);
  const [loadingEnquiryHistory, setLoadingEnquiryHistory] = useState(false);
  const userRole = useSelector((state) => state.auth.userRole);
  const sgdTax = useSelector((state) => state.auth.sgdTax);

  // Add a new state to track the current action type
  const [enquiryActionType, setEnquiryActionType] = useState("enquiry");

  // Add a new state to track if an enquiry has been processed (accepted or cancelled)
  const [enquiryProcessed, setEnquiryProcessed] = useState(false);

  // Add a new state to track which tour ID has an active enquiry
  const [tourWithProcessedEnquiry, setTourWithProcessedEnquiry] =
    useState(null);
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
      //console.error("Error checking enquiry status:", error);
      return false;
    }
  };
  const navigate = useNavigate(); // Initialize navigate
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const [snackbarSeverity, setSnackbarSeverity] = useState("info");
  //const { currentStep } = useSelector((state) => state.steps);
  const { upcomingTours = [], status } = useSelector((state) => state.lists);

  const dispatch = useDispatch();
  const location = useLocation(); // Get the current location (route)
  const [order, setOrder] = useState("asc"); // State to track sort order
  const [sortColumn, setSortColumn] = useState("id");
  const [tourId, setTId] = useState(null);

  // Trigger API call when navigating to Dashboard
  useEffect(() => {
    // Trigger fetchLists only when navigating to "/dashboard"
    if (location.pathname === "/dashboard/db-dashboard") {
      dispatch(setTourType("ongoing"));
      dispatch(fetchLists({ reset: true, type: "ongoing" }));
      
      //console.log('Initial data fetch with type: ongoing');
    }
  }, [location.pathname, dispatch]); // Depend on the pathname // Dependency on location.pathname

  // if (status === "loading") return <p>Loading...</p>;
  // if (status === "failed") return <p>Error: {error}</p>;
  const [sortedLists, setSortedLists] = useState([]); // State for sorted data
  const totalPages = Math.ceil(sortedLists.length / rowsPerPage);
  // Filter function
  const filterData = (data) => {
    if (!filters || Object.keys(filters).length === 0) return data;
    
    return data.filter(item => {
      // Booking ID filter
      if (filters.searchId && !item.display_id?.toLowerCase().includes(filters.searchId.toLowerCase())) {
        return false;
      }
      
      // Customer name filter
      if (filters.customerName && !item.customer_name?.toLowerCase().includes(filters.customerName.toLowerCase())) {
        return false;
      }
      
      // Country filter
      if (filters.country) {
        const destination = item.destination || '';
        const countryMatch = destination.toLowerCase().includes(filters.country.toLowerCase());
        if (!countryMatch) return false;
      }
      
      
      // Check-in date filter
      if (filters.checkInDate) {
        const checkInDate = item.check_in_time;
        if (checkInDate) {
          const [day, month, year] = checkInDate.split('/');
          const itemDate = new Date(`${year}-${month}-${day}`);
          const filterDate = new Date(filters.checkInDate);
          if (itemDate.toDateString() !== filterDate.toDateString()) {
            return false;
          }
        }
      }
      
      // Check-out date filter
      if (filters.checkOutDate) {
        const checkOutDate = item.check_out_time;
        if (checkOutDate) {
          const [day, month, year] = checkOutDate.split('/');
          const itemDate = new Date(`${year}-${month}-${day}`);
          const filterDate = new Date(filters.checkOutDate);
          if (itemDate.toDateString() !== filterDate.toDateString()) {
            return false;
          }
        }
      }
      
      // Status filter
      if (filters.status) {
        if (filters.status === "Cancel") {
          // For "Cancel" status, check if tour_status starts with "cancel" (case insensitive)
          if (!item.tour_status?.toLowerCase().startsWith("cancel")) {
            return false;
          }
        } else {
          // For other statuses, exact match
          if (item.tour_status !== filters.status) {
            return false;
          }
        }
      }
      
      
      return true;
    });
  };

  // Update sortedLists when lists or filters change
  useEffect(() => {
    const newLists = Array.isArray(upcomingTours) ? upcomingTours : [];
    const filteredLists = filterData(newLists);
    setSortedLists(filteredLists);
    
    console.log('Lists updated in Upcoming.jsx:', {
      listsLength: newLists.length,
      filteredLength: filteredLists.length,
      totalPages: Math.ceil(filteredLists.length / rowsPerPage),
      currentPage: page + 1
    });
  }, [upcomingTours, rowsPerPage, page, filters]);
  
  // Auto-fetch more data when reaching the last page
  useEffect(() => {
    if (page > 0 && sortedLists.length > 0) {
      const currentPageEnd = (page + 1) * rowsPerPage;
      const dataAvailable = sortedLists.length;
      
      // If we're on the last page or near the end of available data, fetch more
      if (page + 1 === totalPages || currentPageEnd >= dataAvailable - rowsPerPage) {
        const nextStart = Math.ceil(dataAvailable / 30) * 30; // Align to the next 30-item chunk
        
        // console.log('Auto-fetching more data in Upcoming:', {
        //   currentPage: page + 1,
        //   totalPages,
        //   dataAvailable,
        //   nextStart,
        //   type: "ongoing"
        // });
        
        // Make sure we're using the correct type
        const currentType = "ongoing"; // For Upcoming.jsx, we always use "ongoing"
        dispatch(fetchLists({ start: nextStart, limit: 30, type: currentType, reset: false }));
      }
    }
  }, [page, sortedLists.length, rowsPerPage, dispatch, totalPages]);

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
        label: "Attractions",
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

  const handleSort = () => {  
    const sortedData = [...sortedLists].sort((a, b) => {
      const numA = parseInt(a.display_id.match(/\d+$/)[0], 10);
      const numB = parseInt(b.display_id.match(/\d+$/)[0], 10);

      return order === "asc" ? numA - numB : numB - numA;
    });

    setSortedLists(sortedData);
    setOrder(order === "asc" ? "desc" : "asc");
  };

  // New function to handle enquiry directly from the list
  const handleDirectEnquiry = async (list) => {
    try {
      //console.log("Direct enquiry started for tour:", list.id);

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
        ...(bookings.travel_hourly || bookings.data?.travel_hourly || []),
        ...(bookings.local_transport || bookings.data?.local_transport || []),
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
            setSnackbarMessage("This enquiry has already been processed (Booked or Cancelled)");
            setSnackbarSeverity("info");
            setOpenSnackbar(true);
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
    //  console.log("Opening enquiry modal directly");
      setIsEnquiryModalVisible(true);
    } catch (error) {
      //console.error("Error preparing enquiry:", error);
      setSnackbarMessage("Could not prepare the enquiry. Please try again.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
    }
  };

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



  const handleCloseSnackbar = (event, reason) => {
    if (reason === "clickaway") {
      return;
    }
    setOpenSnackbar(false);
  };

  const handleEdit = (list) => {
    // Reverse mapping from country names to country codes
    const reverseCountryMap = Object.fromEntries(
      Object.entries(countryMap).map(([code, name]) => [name, code])
    );

    setSnackbarMessage("Submitting Id...");
    setSnackbarSeverity("info");
    setOpenSnackbar(true);
    dispatch(setHaveBooking(false));
    // Dispatch necessary actions
    dispatch(setId(list.id));
    dispatch(setTourId(list.id));
    dispatch(setTourId1(list.id));
    dispatch(setTourIdd(list.id));
    dispatch(resetBookingState());
    dispatch(clearUserInfo());
    dispatch(resetVehicles());
    dispatch(resetVehicles1());
    dispatch(resetguide());

    // Trigger fetchViewDetails just like handleViewDetails
    dispatch(fetchViewDetails({ tour_id: list.id }));

    dispatch(fetchEditid())
      .unwrap()
      .then((response) => {
        //console.log("Full Response Data:", response);

        // Extract data from the nested structure
        const data = response.data;
        const bookingType = data.bookingType || "booking";
        dispatch(setBookingType(bookingType));

        // Handle customer info if it exists
        // FIX: Check if customerInfo is an object (not an array)
        if (data.customerInfo && typeof data.customerInfo === "object") {
          //console.log("Processing customer info:", data.customerInfo);
          if(data.customerInfo.fullName) {
            dispatch(setHaveBooking(true));
          }
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
          //console.error("Tour ID or destination not found in response.");
          throw new Error("Invalid response data.");
        }

        //console.log("date service", data.service?.date_service);
        if (data.service?.date_service) {
          dispatch(setDateService(data.service.date_service));
        }

        //console.log("Step:", data.step);
        if (data.step) {
          dispatch(updateStepStatus({ key: data.step, status: 2 }));
          dispatch(setType(null));

          const matchedStep = steps.find((step) => step.key === data.step);
          if (matchedStep) {
            //console.log("Navigating to:", matchedStep.path);
            navigate(matchedStep.path, { state: { tourDetails: data } });
            dispatch(statusUpdate()).unwrap();
          } else {
            console.warn(`No step found for key: ${data.step}`);
          }
        }

        // console.log(
        //   "CheckInTime:",
        //   data.CheckInTime,
        //   "CheckOutTime:",
        //   data.CheckOutTime
        // );
        if (data.CheckInTime && data.CheckOutTime) {
          dispatch(setCheckIn(formatDate1(data.CheckInTime)));
          dispatch(setCheckOut(formatDate1(data.CheckOutTime)));
        } else {
          console.warn("Check-in or Check-out time is missing.");
        }

        dispatch(setTourId(id));

        if (destination) {
          //console.log("Destination (Raw):", destination);

          const destinationArray = Array.isArray(destination)
            ? destination
            : destination.split(",").map((code) => code.trim());

          //console.log("Formatted destination array:", destinationArray);

          const countryCodeArray = destinationArray
            .map((name) => reverseCountryMap[name])
            .filter(Boolean);

          //console.log("Country code array:", countryCodeArray);

          dispatch(setSearchLocation(countryCodeArray));

          const formattedCheckIn = data.CheckInTime
            ? dayjs(data.CheckInTime, "DD/MM/YYYY").format("YYYY-MM-DD")
            : null;
          const formattedCheckOut = data.CheckOutTime
            ? dayjs(data.CheckOutTime, "DD/MM/YYYY").format("YYYY-MM-DD")
            : null;

          dispatch(
            updateSearchState({
              location: destinationArray,
              ucheckIn: formattedCheckIn,
              ucheckOut: formattedCheckOut,
            })
          );

          dispatch(resetHotels());
        } else {
          console.warn("Destination is missing");
        }

        //   console.log("Setting tour details:", {
        //   ...data,
        //   country: destination,
        //   service_details: {
        //     ...data.service,
        //     country: destination,
        //   },
        // });

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
        //console.error("Error fetching booking:", error);
        setSnackbarMessage(
          error?.message || "Something went wrong. Please try again."
        );
        setSnackbarSeverity("error");
        setOpenSnackbar(true);
      });
  };

  const handleViewDetails = async ({
    id: tourId,
    booking_type,
    display_id,
  }) => {
    setBookingType1(booking_type);
    setDisplayId(display_id);
    dispatch(fetchViewDetails({ tour_id: tourId }));
    setIsModalVisible(true);
    // Store the current tour ID
    setTId(tourId);

    // Log the booking data for debugging
    //console.log("Fetching tour details for:", tourId);

    // Reset enquiry processed state when viewing a new tour
    setEnquiryProcessed(false);

    // Check if this tour has a status of 2 or 3
    const isProcessed = await checkEnquiryStatus(tourId);
    setEnquiryStatusProcessed(isProcessed);
    // Add a timeout to log bookings data after it's loaded
    setTimeout(() => {
      //console.log("BOOKINGS DATA:", bookings);
    }, 2000);
  };

  const handleDelete = async (tourId) => {
    //console.log("Received tourId:", tourId); // ✅ Should print actual ID

    if (!tourId) {
      //console.error("❌ tourId is missing!");
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
        //console.error("❌ Error deleting tour:", err);
        swal("Error!", "Failed to delete the tour.", "error");
      }
    } else {
      swal("Cancelled", "Your tour is safe!", "info");
    }
    dispatch(fetchLists({ reset: true }));
  };

  const handleCloseModal = () => {
    setIsModalVisible(false);
  };

  const handlePrint = () => {
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
    const travelHourly =
      modifiedData.travel_hourly || modifiedData.data?.travel_hourly || [];
    const attractions =
      modifiedData.attraction || modifiedData.data?.attraction || [];
    const guides = modifiedData.guide || modifiedData.data?.guide || [];
    const restaurants =
      modifiedData.restaurant || modifiedData.data?.restaurant || [];
    const localTransports =
      modifiedData.local_transport || modifiedData.data?.local_transport || [];

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
    
    // Add travel_hourly to total price
    travelHourly.forEach((travel) => {
      if (travel.totalPrice) {
        totalOriginalPrice += parseFloat(travel.totalPrice);
      }
    });
    
    // Add local_transport to total price
    localTransports.forEach((transport) => {
      if (transport.totalPrice) {
        totalOriginalPrice += parseFloat(transport.totalPrice);
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
      ...travelHourly,
      ...attractions,
      ...guides,
      ...restaurants,
      ...localTransports,
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
    
    // Process travel hourly
    travelHourly.forEach((travel) => {
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
    
    // Process local transport
    localTransports.forEach((transport) => {
      if (transport.totalPrice) {
        // Store original price
        transport.basePrice = transport.totalPrice;

        // Use the pre-calculated markup amount that ensures total matches exactly
        const transportMarkupAmount = transport.calculatedMarkup || 0;

        // Apply markup to get the new base price
        const markedUpPrice =
          parseFloat(transport.totalPrice) + transportMarkupAmount;
        transport.markedUpPrice = markedUpPrice.toString();

        // Use the pre-calculated discount amount that ensures total matches exactly
        const transportDiscountAmount = transport.calculatedDiscount || 0;

        // Apply discount on the marked-up price
        transport.totalPrice = (
          markedUpPrice - transportDiscountAmount
        ).toString();

        // Store markup and discount amounts
        transport.markupAmount = transportMarkupAmount.toString();
        if (discount > 0) {
          transport.discountAmount = transportDiscountAmount.toString();
        }

        // Remove the temporary calculation properties
        delete transport.calculatedMarkup;
        delete transport.calculatedDiscount;
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
        ...(bookings.travel_hourly || bookings.data?.travel_hourly || []),
        ...(bookings.local_transport || bookings.data?.local_transport || []),
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
          `Tour_Details_${bookings.display_id || bookings.data?.display_id || "booking"
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
    // Reset modified data when canceling
    setModifiedPriceData(null);
  };

  const handleOpenEnquiryModal = async () => {
    // Add debug logging
    //    console.log("handleOpenEnquiryModal called with tourId:", tourId);

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
    //console.log("Current tourId state:", tourId);
    //console.log("Current bookings data:", bookings);

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

      //console.log("Using tour_id for enquiry:", currentTourId);

      if (!currentTourId) {
        //console.error("No tour_id available for fetching enquiry history");
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

      //console.log("Enquiry history response:", response.data);

      if (response.data && response.data.data) {
        // For single object response, convert to array
        const historyData = Array.isArray(response.data.data)
          ? response.data.data
          : [response.data.data];
        setEnquiryHistory(historyData);
        setEnquiryAmount(historyData[0].current_price);
        //console.log("History data:", historyData[0].current_price);

        // Check if there's an entry with status 2 (Booked) or 3 (Cancel)
        const hasStatusBookedOrCancel = historyData.some(
          (item) =>
            item.status === "2" ||
            item.status === "3" ||
            item.status === 2 ||
            item.status === 3
        );

        //console.log("Has status booked or cancel?", hasStatusBookedOrCancel);

        // If the status is 2 or 3, close the modal and don't show it
        if (hasStatusBookedOrCancel) {
          setSnackbarMessage("This enquiry has already been processed (Booked or Cancelled)");
          setSnackbarSeverity("info");
          setOpenSnackbar(true);
          setLoadingEnquiryHistory(false);
          return;
        }

        // Check if any entry in enquiryHistory has assigned as "agent"
        const isAssignedToAgent = historyData.some(
          (item) => item.assigned && item.assigned === "Agent"
        );
        //console.log("Enquiry history data:", historyData);
        console.log("Is assigned to agent?", isAssignedToAgent);

        // Check if any entry has assigned as "OM" or other value
        const assignedValue =
          historyData.length > 0 ? historyData[0].assigned : "none";
        //console.log("Assigned value found:", assignedValue);
        console.log("Is assigned OM?", assignedValue === "OM");

        // Set assigned state based on what we found
        if (
          assignedValue === "OM" ||
          (assignedValue && assignedValue !== "Agent")
        ) {
          //console.log("Setting assigned to a non-Agent value:", assignedValue);
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
      //console.error("Error fetching enquiry history:", error);
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
        setSnackbarMessage("Please enter a comment for the enquiry");
        setSnackbarSeverity("error");
        setOpenSnackbar(true);
        return;
      }

      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");

      if (!authToken || !AgentId) {
        setSnackbarMessage("Authorization failed. Please login again.");
        setSnackbarSeverity("error");
        setOpenSnackbar(true);
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
      const travelHourly =
        bookings.travel_hourly || bookings.data?.travel_hourly || [];
      const localTransports =
        bookings.local_transport || bookings.data?.local_transport || [];

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
      
      // Add travel hourly prices
      travelHourly.forEach((travel) => {
        if (travel.totalPrice) {
          actualPrice += parseFloat(travel.totalPrice);
        }
      });
      
      // Add local transport prices
      localTransports.forEach((transport) => {
        if (transport.totalPrice) {
          actualPrice += parseFloat(transport.totalPrice);
        }
      });

      // Use Math.ceil for the prices
      actualPrice = Math.ceil(actualPrice);
      const enquiryPrice = Math.ceil(enquiryAmount);

      // Get tour_id from bookings data
      const tour_id = bookings.id || bookings.data?.id || tourId;

      if (!tour_id) {
        setSnackbarMessage("Tour ID is missing. Cannot submit enquiry.");
        setSnackbarSeverity("error");
        setOpenSnackbar(true);
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

      //console.log(`Submitting ${type} with data:`, requestData);

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
            successMessage = "Enquiry accepted successfully with amount: " + response.data.actual_amount;
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

        setSnackbarMessage(successMessage);
        setSnackbarSeverity("success");
        setOpenSnackbar(true);

        // Close the modal for all types using the handleCloseEnquiryModal
        handleCloseEnquiryModal();

        // Refresh the list
        dispatch(fetchLists({ reset: true }));
      } else {
        setSnackbarMessage(response.data?.message || "Operation failed. Please try again.");
        setSnackbarSeverity("error");
        setOpenSnackbar(true);
      }
    } catch (error) {
      //    console.error(`Error during ${type} operation:`, error);
      setSnackbarMessage("Something went wrong. Please try again later.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
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
      const travelHourly =
        bookings.travel_hourly || bookings.data?.travel_hourly || [];
      const attractions =
        bookings.attraction || bookings.data?.attraction || [];
      const guides = bookings.guide || bookings.data?.guide || [];
      const restaurants =
        bookings.restaurant || bookings.data?.restaurant || [];
      const localTransports =
        bookings.local_transport || bookings.data?.local_transport || [];

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
      
      // Add travel hourly prices
      travelHourly.forEach((travel) => {
        if (travel.totalPrice) {
          calculatedTotalPrice += parseFloat(travel.totalPrice);
        }
      });
      
      // Add local transport prices
      localTransports.forEach((transport) => {
        if (transport.totalPrice) {
          calculatedTotalPrice += parseFloat(transport.totalPrice);
        }
      });

      setTotalPrice(Math.ceil(calculatedTotalPrice));
    }
  }, [bookings]);

  const user_country = useSelector((state) => {
    const countryData = state.auth.user_country;
    if (Array.isArray(countryData)) return countryData;
    if (typeof countryData === "string" && countryData.includes("[object Object]")) {
      const actualCountries = state.auth.user_country;
      if (Array.isArray(actualCountries)) return actualCountries;
    }
    if (typeof countryData === "object" && countryData !== null) return [countryData];
    return [];
  });

  const countryMappings = useMemo(() => {
    const nameToCode = {};
    const codeToName = {};
    if (user_country && Array.isArray(user_country)) {
      user_country.forEach((country) => {
        if (country && country.name && country.code) {
          nameToCode[country.name] = country.code;
          nameToCode[country.name.toLowerCase()] = country.code;
          codeToName[country.code] = country.name;
          codeToName[country.code.toLowerCase()] = country.name;
        }
      });
    }
    return { nameToCode, codeToName };
  }, [user_country]);

  return (
    // <Box sx={{ width: "100%" }}>
    <>
      {/* <div className="col-auto d-flex justify-content-end">
        <div className="row x-gap-20 y-gap-20">
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
      <div
        className="dashboard"
        style={{ display: "flex", flexDirection: "column" }}
      >
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
              <div className="responsive-table-container">
                <table className="table-3 -border-bottom col-12 responsive-table">
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
                          e.currentTarget.querySelector("i").style.transform = "rotate(90deg)";
                        }}
                        onMouseLeave={(e) => {
                          e.currentTarget.style.backgroundColor = "#f5f7fc";
                          e.currentTarget.querySelector("i").style.transform = "rotate(0deg)";
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
                        onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = "#e6eafb")}
                        onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = "#f5f7fc")}
                        title={`Sort by Booking ID (${sortColumn === "id" ? (order === "asc" ? "ascending" : "descending") : "click to sort"})`}
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
                              className={`icon-up-down text-blue-1 mr-5 ${order === "asc" ? "rotate-180" : ""
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
                          e.currentTarget.querySelector("i").style.transform = "scale(1.2)";
                        }}
                        onMouseLeave={(e) => {
                          e.currentTarget.style.backgroundColor = "#f5f7fc";
                          e.currentTarget.querySelector("i").style.transform = "scale(1)";
                        }}
                        title={`Sort by Start Date (${sortColumn === "startDate" ? (order === "asc" ? "ascending" : "descending") : "click to sort"})`}
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
                              className={`icon-up-down text-blue-1 mr-5 ${order === "asc" ? "rotate-180" : ""
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
                          e.currentTarget.querySelector("i").style.transform = "scale(1.2)";
                        }}
                        onMouseLeave={(e) => {
                          e.currentTarget.style.backgroundColor = "#f5f7fc";
                          e.currentTarget.querySelector("i").style.transform = "scale(1)";
                        }}
                        title={`Sort by End Date (${sortColumn === "endDate" ? (order === "asc" ? "ascending" : "descending") : "click to sort"})`}
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
                              className={`icon-up-down text-blue-1 mr-5 ${order === "asc" ? "rotate-180" : ""
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
                          e.currentTarget.querySelector("i").style.transform = "rotate(15deg)";
                        }}
                        onMouseLeave={(e) => {
                          e.currentTarget.style.backgroundColor = "#f5f7fc";
                          e.currentTarget.querySelector("i").style.transform = "rotate(0deg)";
                        }}
                        title={`Sort by Pax Count (${sortColumn === "pax" ? (order === "asc" ? "ascending" : "descending") : "click to sort"})`}
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
                              className={`icon-arrow-${order === "asc" ? "down" : "up"
                                }`}
                              style={{ fontSize: "12px", opacity: 0.7 }}
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
                          width: "120px",
                          minWidth: "120px",
                          maxWidth: "120px",
                        }}
                        onClick={() => handleColumnSort("destination")}
                        onMouseEnter={(e) => {
                          e.currentTarget.style.backgroundColor = "#e6eafb";
                          e.currentTarget.querySelector("i").style.transform = "translateY(-3px)";
                        }}
                        onMouseLeave={(e) => {
                          e.currentTarget.style.backgroundColor = "#f5f7fc";
                          e.currentTarget.querySelector("i").style.transform = "translateY(0)";
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
                          whiteSpace: "nowrap",
                          transition: "background-color 0.3s ease",
                          width: "130px",
                          minWidth: "130px",
                          maxWidth: "130px",
                        }}
                        onMouseEnter={(e) => {
                          e.currentTarget.style.backgroundColor = "#e6eafb";
                          e.currentTarget.querySelector("i").style.transform = "scale(1.2)";
                        }}
                        onMouseLeave={(e) => {
                          e.currentTarget.style.backgroundColor = "#f5f7fc";
                          e.currentTarget.querySelector("i").style.transform = "scale(1)";
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
                          width: "100px",
                          minWidth: "100px",
                          maxWidth: "100px",
                        }}
                        onClick={() => handleColumnSort("status")}
                        onMouseEnter={(e) => {
                          e.currentTarget.style.backgroundColor = "#e6eafb";
                          e.currentTarget.querySelector("i").style.transform = "scale(1.2)";
                        }}
                        onMouseLeave={(e) => {
                          e.currentTarget.style.backgroundColor = "#f5f7fc";
                          e.currentTarget.querySelector("i").style.transform = "scale(1)";
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
                          width: "140px",
                          minWidth: "140px",
                          maxWidth: "140px",
                        }}
                        onMouseEnter={(e) => {
                          e.currentTarget.style.backgroundColor = "#e6eafb";
                          e.currentTarget.querySelector("i").style.transform = "scale(1.2)";
                        }}
                        onMouseLeave={(e) => {
                          e.currentTarget.style.backgroundColor = "#f5f7fc";
                          e.currentTarget.querySelector("i").style.transform = "scale(1)";
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
                          Payment Details
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
                            <Skeleton
                              variant="rectangular"
                              width={140}
                              height={80}
                            />
                          </td>
                          <td className="actions-column" style={{ whiteSpace: "nowrap" }}>
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
                                gap: "4px",
                                flexWrap: "nowrap",
                                alignItems: "center",
                              }}
                            >
                              {/* View button - always visible */}
                              <Tooltip title="View Details" arrow>
                                <IconButton
                                  size="small"
                                  onClick={() => handleViewDetails(list)}
                                  sx={{
                                    color: "#4361ee",
                                    width: "28px",
                                    height: "28px",
                                    padding: "4px",
                                    "&:hover": {
                                      color: "#2847C7",
                                    },
                                  }}
                                >
                                  <Visibility sx={{ fontSize: "14px" }} />
                                </IconButton>
                              </Tooltip>

                              {/* Show other buttons only if status doesn't start with "Cancel" */}
                              {(!list.tour_status?.toLowerCase().startsWith("cancel") && !list.tour_status?.toLowerCase().startsWith("refund") && !list.tour_status?.toLowerCase().startsWith("refunded"))&& !list.tour_status?.toLowerCase().startsWith("auto cancel") && (
                                <>
                                  {/* Only render Edit button if editOff is not 1 */}
                                
                                    <Tooltip title="Add More Services" arrow>
                                      <IconButton
                                        size="small"
                                        onClick={() => handleEdit(list)}
                                        sx={{
                                          color: "#2e7d32",
                                          width: "20px",
                                          height: "20px",
                                          padding: "2px",
                                          "&:hover": {
                                            color: "#1b5e20",
                                          },
                                        }}
                                      >
                                        <Edit sx={{ fontSize: "14px" }} />
                                      </IconButton>
                                    </Tooltip>
                                  

                                  {/* Negotiate button for enquiry type and Agent role */}
                                  {list.booking_type === "enquiry" && userRole === "Agent" && (
                                    <Tooltip title="Negotiate" arrow>
                                      <IconButton
                                        size="small"
                                        onClick={() => handleDirectEnquiry(list)}
                                        sx={{
                                          color: "#7b1fa2",
                                          width: "20px",
                                          height: "20px",
                                          padding: "2px",
                                          "&:hover": {
                                            color: "#4a148c",
                                          },
                                        }}
                                      >
                                        <AttachMoney sx={{ fontSize: "14px" }} />
                                      </IconButton>
                                    </Tooltip>
                                  )}
                                </>
                              )}
                            </div>
                            {list.dmc_company_name && (
                              <Tooltip
                                title={list.dmc_company_name}
                                arrow
                                placement="bottom"
                              >
                                <div
                                  style={{
                                    fontSize: "10px",
                                    fontWeight: "600",
                                    overflow: "hidden",
                                    textOverflow: "ellipsis",
                                    whiteSpace: "nowrap",
                                    maxWidth: "80px",
                                    marginTop: "10px",
                                    textAlign: "center",
                                    backgroundColor: "#3554D1",
                                    color: "#fff",
                                    padding: "5px 10px",
                                    borderRadius: "50px",
                                    cursor: "pointer",
                                    display: "inline-block",
                                  }}
                                >
                                  {list.dmc_company_name}
                                </div>
                              </Tooltip>
                            )}
                          </td>
                          <td className="booking-id-column">
                            <div
                              style={{
                                backgroundColor: "rgba(53, 84, 209, 0.1)",
                                padding: "4px 6px",
                                borderRadius: "8px",
                                fontSize: "10px",
                                color: "#3554D1",
                                fontWeight: "600",
                                display: "inline-flex",
                                flexDirection: "column",
                                alignItems: "center",
                                whiteSpace: "nowrap",
                              }}
                            >
                              <span>{list.display_id}</span>
                              {list.order_from?.trim() && (
                                <span
                                  style={{
                                    backgroundColor: "#3554D1",
                                    color: "#fff",
                                    padding: "2px 8px",
                                    borderRadius: "50px",
                                    fontSize: "10px",
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
                          <td className="date-column">
                            <Tooltip title={formatDateTooltip(list.check_in_time)} arrow placement="top">
                              <div
                                style={{
                                  backgroundColor: "rgba(76, 175, 80, 0.1)",
                                  padding: "4px 6px",
                                  borderRadius: "8px",
                                  fontSize: "12px",
                                  color: "#4CAF50",
                                  fontWeight: "600",
                                  display: "inline-flex",
                                  alignItems: "center",
                                  justifyContent: "center",
                                  whiteSpace: "nowrap",
                                  cursor: "pointer",
                                }}
                              >
                                <span style={{ fontWeight: "600", fontSize: "12px" }}>{formatDate(list.check_in_time)}</span>
                              </div>
                            </Tooltip>
                          </td>
                          <td className="date-column">
                            <Tooltip title={formatDateTooltip(list.check_out_time)} arrow placement="top">
                              <div
                                style={{
                                  backgroundColor: "rgba(244, 67, 54, 0.1)",
                                  padding: "4px 6px",
                                  borderRadius: "8px",
                                  fontSize: "12px",
                                  color: "#F44336",
                                  fontWeight: "600",
                                  display: "inline-flex",
                                  alignItems: "center",
                                  justifyContent: "center",
                                  whiteSpace: "nowrap",
                                  cursor: "pointer",
                                }}
                              >
                                <span style={{ fontWeight: "600", fontSize: "12px" }}>{formatDate(list.check_out_time)}</span>
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
                                  fontSize: "12px",
                                  color: "#FF9800",
                                  whiteSpace: "nowrap",
                                }}
                              >
                                {list.total_pax}
                              </span>
                            </div>
                          </td>
                          <td className="destination-column">
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
                                }}
                              >
                                {list.destination ? list.destination.split(",").map((code) => {
                                  const trimmedCode = code.trim();
                                  // First try to get the name from codeToName mapping
                                  const countryName = countryMappings.codeToName[trimmedCode];
                                  if (countryName) {
                                    return countryName;
                                  }
                                  // If not found in codeToName, check if it's already a name
                                  if (countryMappings.nameToCode[trimmedCode]) {
                                    return trimmedCode;
                                  }
                                  // If neither found, return the original code
                                  return trimmedCode;
                                }).join(", ") : "N/A"}
                              </span>
                            </div>
                          </td>
                          <td className="customer-column">
                            <div
                              style={{
                                display: "flex",
                                alignItems: "center",
                                gap: "8px",
                                maxWidth: "150px",
                              }}
                            >
                              <i
                                className="icon-customer"
                                style={{ fontSize: "18px", color: "#F44336" }}
                              ></i>
                              <Tooltip title={list.customer_name || "N/A"} placement="top" arrow>
                                <span
                                  style={{
                                    overflow: "hidden",
                                    textOverflow: "ellipsis",
                                    whiteSpace: "nowrap",
                                    display: "inline-block",
                                    maxWidth: "110px",
                                  }}
                                >
                                  {truncateName(list.customer_name, 10)}
                                </span>
                              </Tooltip>
                            </div>
                          </td>
                          <td className="booking-id-column"
                          style={{
                            minWidth: "200px",
                            maxWidth: "200px",
                          }}
                          >
                            <div
                              style={{
                                display: "flex",
                                alignItems: "center",
                                justifyContent: "center",
                                gap: "0px",
                                backgroundColor: getBackgroundColor(list.tour_status),
                                padding: "4px 8px",
                                borderRadius: "16px",
                                whiteSpace: "nowrap",
                                width: "",
                              }}
                            >
                              <i
                                className={(() => {
                                  // const status = list.status
                                  //   ? list.status.toLowerCase()
                                  //   : "";
                                  // if (status.includes("pending"))
                                  //   return "icon-clock";
                                  // if (
                                  //   status.includes("accept") ||
                                  //   status.includes("approved")
                                  // )
                                  //   return "icon-check";
                                  // if (
                                  //   status.includes("reject") ||
                                  //   status.includes("cancel")
                                  // )
                                  //   return "icon-close";
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
                                }}
                              >
                                {list.tour_status || "Pending"}
                              </span>
                            </div>
                          </td>
                          <td className="payment-column">
                            <CustomPaymentTooltip list={list}>
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
                                {/* Original Amount (from API, no tax) */}
                                <div
                                  style={{
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: "space-between",
                                    marginBottom: "4px",
                                    padding: "3px 6px",
                                    backgroundColor: "rgba(255, 255, 255, 0.8)",
                                    borderRadius: "6px",
                                    border: "1px solid rgba(0, 0, 0, 0.1)",
                                  }}
                                >
                                  <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
                                    <i className="icon-tag" style={{ fontSize: "14px", color: "#1976d2" }}></i>
                                    <span style={{ fontSize: "9px", color: "#1976d2", fontWeight: "600" }}>
                                      Original
                                    </span>
                                  </div>
                                  <span style={{ fontSize: "9px", color: "#1976d2", fontWeight: "700" }}>
                                    {(() => {
                                      const original = Math.ceil(
                                        list.tour_total_price != null
                                          ? Number(list.tour_total_price)
                                          : Number(list.finalAmount || 0) + Number(list.discountAmount || 0)
                                      );
                                      return `SGD ${original}`;
                                    })()}
                                  </span>
                                </div>

                                {/* Discount Amount */}
                                {list.discountAmount > 0 && (
                                  <div
                                    style={{
                                      display: "flex",
                                      alignItems: "center",
                                      justifyContent: "space-between",
                                      marginBottom: "4px",
                                      padding: "3px 6px",
                                      backgroundColor: "rgba(229, 57, 53, 0.1)",
                                      borderRadius: "6px",
                                      border: "1px solid rgba(229, 57, 53, 0.2)",
                                    }}
                                  >
                                    <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
                                      <i className="icon-usd" style={{ fontSize: "14px", color: "#e53935" }}></i>
                                      <span style={{ fontSize: "9px", color: "#e53935", fontWeight: "600" }}>
                                        Discount
                                      </span>
                                    </div>
                                    <span style={{ fontSize: "9px", color: "#e53935", fontWeight: "700" }}>
                                      -SGD {Math.ceil(list.discountAmount)}
                                    </span>
                                  </div>
                                )}

                                {/* Final Amount (API final + tax) */}
                                <div
                                  style={{
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: "space-between",
                                    marginBottom: "4px",
                                    padding: "3px 6px",
                                    backgroundColor: "rgba(255, 255, 255, 0.8)",
                                    borderRadius: "6px",
                                    border: "1px solid rgba(0, 0, 0, 0.1)",
                                  }}
                                >
                                  <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
                                    <i className="icon-wallet" style={{ fontSize: "14px", color: "#4CAF50" }}></i>
                                    <span style={{ fontSize: "9px", color: "#4CAF50", fontWeight: "600" }}>
                                      Final
                                    </span>
                                  </div>
                                  <span style={{ fontSize: "9px", color: "#4CAF50", fontWeight: "700" }}>
                                    {(() => {
                                      const baseFinal = Number(list.finalAmountWithTax || 0);
                                      const withTax = Math.ceil(baseFinal );
                                      return `SGD ${withTax}`;
                                    })()}
                                  </span>
                                </div>

                                <div
                                  style={{
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: "space-between",
                                    marginBottom: "6px",
                                    padding: "4px 8px",
                                    backgroundColor: "rgba(76, 175, 80, 0.1)",
                                    borderRadius: "6px",
                                    border: "1px solid rgba(76, 175, 80, 0.2)",
                                  }}
                                >
                                  <div style={{ display: "flex", alignItems: "center", gap: "4px" }}>
                                    <i className="icon-wallet" style={{ fontSize: "10px", color: "#4CAF50" }}></i>
                                    <span style={{ fontSize: "10px", color: "#4CAF50", fontWeight: "600" }}>
                                      Total(Tax Included)
                                    </span>
                                  </div>
                                  <span style={{ fontSize: "10px", color: "#4CAF50", fontWeight: "700" }}>{(() => {
                                      // Base amount - check first
                                      const baseFinalAmount = Number(list.finalAmountWithTax || 0);
                                      
                                      // If finalAmountWithTax is 0, return 0 (don't calculate taxes)
                                      if (!baseFinalAmount || baseFinalAmount <= 0) {
                                        return `SGD 0`;
                                      }

                                      // Helper function to safely convert to number
                                      const safeNumber = (value) => {
                                        const num = Number(value);
                                        return isNaN(num) ? 0 : num;
                                      };

                                      // Helper function to calculate nights
                                      const parseDate = (dateStr) => {
                                        if (!dateStr) return null;
                                        const parsed = dayjs(dateStr, ["DD/MM/YYYY", "DD-MM-YYYY", "YYYY-MM-DD", "YYYY/MM/DD"], true);
                                        if (parsed.isValid()) {
                                          return parsed;
                                        }
                                        const fallback = dayjs(dateStr);
                                        return fallback.isValid() ? fallback : null;
                                      };

                                      const calculateNights = (checkIn, checkOut) => {
                                        const inDate = parseDate(checkIn);
                                        const outDate = parseDate(checkOut);
                                        if (!inDate || !outDate) return 0;
                                        const nights = outDate.diff(inDate, 'day');
                                        return Math.max(nights, 0);
                                      };

                                      // Parse taxes from JSON string
                                      let taxes = [];
                                      try {
                                        if (list.taxes && typeof list.taxes === 'string') {
                                          taxes = JSON.parse(list.taxes);
                                        } else if (Array.isArray(list.taxes)) {
                                          taxes = list.taxes;
                                        }
                                      } catch (e) {
                                        console.error('Error parsing taxes for tour:', list.display_id, e);
                                        return `SGD ${Math.ceil(baseFinalAmount)}`;
                                      }

                                      if (!taxes || taxes.length === 0) {
                                        return `SGD ${Math.ceil(baseFinalAmount)}`;
                                      }

                                      // Initialize with base amount
                                      let total = baseFinalAmount;
                                      const totalPax = safeNumber(list.total_pax);
                                      const nights = calculateNights(list.check_in_time, list.check_out_time);

                                      // Store calculated amounts for each tax (for cascading)
                                      const taxCalculations = {};

                                      // Step 1: Process taxes where calculate_on = "total"
                                      const totalTaxes = taxes.filter(tax => 
                                        tax.calculate_on && tax.calculate_on.toLowerCase() === 'total'
                                      );

                                      const effectiveNights = nights > 0 ? nights : 1;

                                      totalTaxes.forEach(tax => {
                                        let taxAmount = 0;
                                        const taxValue = safeNumber(tax.tax_value);

                                        if (tax.tax_type === 'percentage') {
                                          // Calculate percentage tax on BASE amount (not running total)
                                          taxAmount = (baseFinalAmount * taxValue) / 100;
                                        } else if (tax.tax_type === 'fixed') {
                                          // Calculate fixed tax based on if_fixed type
                                          switch (tax.if_fixed) {
                                            case 'person':
                                              taxAmount = totalPax * taxValue;
                                              break;
                                            case 'person_day':
                                              taxAmount = totalPax * effectiveNights * taxValue;
                                              break;
                                            case 'per_day':
                                              taxAmount = effectiveNights * taxValue;
                                              break;
                                            case 'per_tour':
                                            case 'person_tour':
                                              taxAmount = taxValue;
                                              break;
                                            default:
                                              taxAmount = taxValue;
                                          }
                                        }

                                        // Validate taxAmount is not NaN
                                        if (isNaN(taxAmount)) {
                                          taxAmount = 0;
                                        }

                                        // Round the tax amount for consistency between display and calculation
                                        const roundedTaxAmount = Math.ceil(taxAmount);
                                        total += roundedTaxAmount;

                                        // Store the total after this tax (for potential cascading)
                                        taxCalculations[tax.tax_id] = {
                                          amount: total,
                                          taxAmount: roundedTaxAmount
                                        };
                                      });

                                      // Step 2: Process cascading taxes (where calculate_on != "total")
                                      const cascadingTaxes = taxes.filter(tax => 
                                        tax.calculate_on && tax.calculate_on.toLowerCase() !== 'total'
                                      );

                                      cascadingTaxes.forEach(tax => {
                                        let taxAmount = 0;
                                        const taxValue = safeNumber(tax.tax_value);

                                        if (tax.tax_type === 'percentage') {
                                          // For percentage taxes, calculate on the previous tax's total amount
                                          // Convert calculate_on to number to match tax_id keys in taxCalculations
                                          const calculateOnKey = typeof tax.calculate_on === 'string' && !isNaN(parseInt(tax.calculate_on)) 
                                            ? parseInt(tax.calculate_on) 
                                            : tax.calculate_on;
                                          const baseCalc = taxCalculations[calculateOnKey];
                                          const baseAmount = baseCalc ? baseCalc.amount : total;
                                          taxAmount = (baseAmount * taxValue) / 100;
                                        } else if (tax.tax_type === 'fixed') {
                                          // For fixed taxes, use if_fixed rules (don't use previous tax's amount)
                                          switch (tax.if_fixed) {
                                            case 'person':
                                            case 'per_person':
                                              taxAmount = totalPax * taxValue;
                                              break;
                                            case 'per_person_per_day':
                                              taxAmount = totalPax * effectiveNights * taxValue;
                                              break;
                                            case 'per_tour_per_day':
                                              taxAmount = effectiveNights * taxValue;
                                              break;
                                            case 'per_tour':
                                            case 'person_tour':
                                              taxAmount = taxValue;
                                              break;
                                            default:
                                              taxAmount = taxValue;
                                          }
                                        }

                                        // Validate taxAmount is not NaN
                                        if (isNaN(taxAmount)) {
                                          taxAmount = 0;
                                        }

                                        // Round the tax amount for consistency between display and calculation
                                        const roundedTaxAmount = Math.ceil(taxAmount);
                                        total += roundedTaxAmount;

                                        // Store the total after this tax
                                        taxCalculations[tax.tax_id] = {
                                          amount: total,
                                          taxAmount: roundedTaxAmount
                                        };
                                      });

                                      // Final validation - if total is NaN, fallback to baseFinalAmount
                                      if (isNaN(total) || !isFinite(total)) {
                                        console.error('Total became NaN for tour:', list.display_id);
                                        return `SGD ${Math.ceil(baseFinalAmount)}`;
                                      }

                                      return `SGD ${Math.ceil(total)}`;
                                    })()}
                                  </span>
                                </div>

                                {/* Payment Status */}
                                <div
                                  style={{
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: "center",
                                    gap: "4px",
                                    padding: "3px 6px",
                                    backgroundColor:
                                      list.payment_status === "Not Paid"
                                        ? "rgba(253, 236, 234, 0.9)"
                                        : list.payment_status === "Partially Paid"
                                          ? "rgba(227, 242, 253, 0.9)"
                                          : list.payment_status === "Completely Paid"
                                            ? "rgba(232, 245, 233, 0.9)"
                                            : "rgba(224, 224, 224, 0.9)",
                                    borderRadius: "6px",
                                    border: `1px solid ${list.payment_status === "Not Paid"
                                        ? "rgba(211, 47, 47, 0.3)"
                                        : list.payment_status === "Partially Paid"
                                          ? "rgba(25, 118, 210, 0.3)"
                                          : list.payment_status === "Completely Paid"
                                            ? "rgba(56, 142, 60, 0.3)"
                                            : "rgba(97, 97, 97, 0.3)"
                                      }`,
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
                                      fontWeight: "600",
                                      fontSize: "9px",
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

                                {/* Due Amount Warning */}
                                {(() => {
                                  const baseFinalAmount = Number(list.finalAmountWithTax || 0);
                                  if (!baseFinalAmount || baseFinalAmount <= 0) return false;

                                  const safeNumber = (value) => { const n = Number(value); return isNaN(n) ? 0 : n; };
                                  const parseDate = (dateStr) => {
                                    if (!dateStr) return null;
                                    const parsed = dayjs(dateStr, ["DD/MM/YYYY", "DD-MM-YYYY", "YYYY-MM-DD", "YYYY/MM/DD"], true);
                                    if (parsed.isValid()) {
                                      return parsed;
                                    }
                                    const fallback = dayjs(dateStr);
                                    return fallback.isValid() ? fallback : null;
                                  };
                                  const calculateNights = (checkIn, checkOut) => {
                                    const inDate = parseDate(checkIn);
                                    const outDate = parseDate(checkOut);
                                    if (!inDate || !outDate) return 0;
                                    const nights = outDate.diff(inDate, 'day');
                                    return Math.max(nights,0);
                                  };
                                  let taxes = [];
                                  try { if (list.taxes && typeof list.taxes==='string') taxes = JSON.parse(list.taxes); else if (Array.isArray(list.taxes)) taxes = list.taxes; } catch { taxes = []; }
                                  let total = baseFinalAmount;
                                  const totalPax = safeNumber(list.total_pax);
                                  const nights = calculateNights(list.check_in_time, list.check_out_time);
                                  const effectiveNights = nights > 0 ? nights : 1;
                                  const taxCalculations = {};
                                  (taxes||[]).filter(t=>t.calculate_on&&t.calculate_on.toLowerCase()==='total').forEach(t=>{
                                    let a=0; const v=safeNumber(t.tax_value);
                                    if(t.tax_type==='percentage') a=(baseFinalAmount*v)/100; else { switch(t.if_fixed){case 'person': a=totalPax*v; break; case 'person_day': a=totalPax*effectiveNights*v; break; case 'per_day': a=effectiveNights*v; break; case 'per_tour': case 'person_tour': a=v; break; default: a=v; } }
                                    const r=Math.ceil(isNaN(a)?0:a); total+=r; taxCalculations[t.tax_id]={amount:total,taxAmount:r};
                                  });
                                  (taxes||[]).filter(t=>t.calculate_on&&t.calculate_on.toLowerCase()!=='total').forEach(t=>{
                                    let a=0; const v=safeNumber(t.tax_value);
                                    if(t.tax_type==='percentage') {
                                      const calculateOnKey = typeof t.calculate_on === 'string' && !isNaN(parseInt(t.calculate_on)) ? parseInt(t.calculate_on) : t.calculate_on;
                                      const baseCalc=taxCalculations[calculateOnKey]; const b=baseCalc?baseCalc.amount:total;
                                      a=(b*v)/100;
                                    } else {
                                      switch(t.if_fixed){case 'person': case 'per_person': a=totalPax*v; break; case 'per_person_per_day': a=totalPax*effectiveNights*v; break; case 'per_tour_per_day': a=effectiveNights*v; break; case 'per_tour': case 'person_tour': a=v; break; default: a=v; }
                                    }
                                    const r=Math.ceil(isNaN(a)?0:a); total+=r; taxCalculations[t.tax_id]={amount:total,taxAmount:r};
                                  });
                                  const dueNow = Math.max(total - safeNumber(list.paidAmount), 0);
                                  return dueNow > 0;
                                })() && (
                                  <div
                                    style={{
                                      display: "flex",
                                      alignItems: "center",
                                      justifyContent: "center",
                                      gap: "4px",
                                      marginTop: "4px",
                                      padding: "3px 6px",
                                      backgroundColor: "rgba(229, 57, 53, 0.1)",
                                      borderRadius: "6px",
                                      border: "1px solid rgba(229, 57, 53, 0.2)",
                                    }}
                                  >
                                    <i className="icon-alert-circle" style={{ fontSize: "8px", color: "#e53935" }}></i>
                                    <span style={{ fontSize: "8px", color: "#e53935", fontWeight: "600" }}>
                                      {(() => {
                                        const safeNumber = (value) => { const n = Number(value); return isNaN(n) ? 0 : n; };
                                        const calculateNights = (checkIn, checkOut) => { if(!checkIn||!checkOut) return 0; try{ const i=dayjs(checkIn); const o=dayjs(checkOut); const d=o.diff(i,'day'); return Math.max(d,0);}catch{return 0;} };
                                        const baseFinalAmount = Number(list.finalAmountWithTax || 0);
                                        let taxes=[]; try{ if(list.taxes && typeof list.taxes==='string') taxes=JSON.parse(list.taxes); else if(Array.isArray(list.taxes)) taxes=list.taxes; }catch{ taxes=[]; }
                                        let total=baseFinalAmount; const totalPax=safeNumber(list.total_pax); const nights=calculateNights(list.check_in_time, list.check_out_time); const taxCalculations={};
                                        (taxes||[]).filter(t=>t.calculate_on&&t.calculate_on.toLowerCase()==='total').forEach(t=>{ let a=0; const v=safeNumber(t.tax_value); if(t.tax_type==='percentage') a=(baseFinalAmount*v)/100; else { switch(t.if_fixed){case 'person': a=totalPax*v; break; case 'person_day': a=totalPax*nights*v; break; case 'per_day': a=nights*v; break; case 'per_tour': case 'person_tour': a=v; break; default: a=v; } } const r=Math.ceil(isNaN(a)?0:a); total+=r; taxCalculations[t.tax_id]={amount:total,taxAmount:r}; });
                                        (taxes||[]).filter(t=>t.calculate_on&&t.calculate_on.toLowerCase()!=='total').forEach(t=>{ let a=0; const v=safeNumber(t.tax_value); if(t.tax_type==='percentage') { const calculateOnKey = typeof t.calculate_on === 'string' && !isNaN(parseInt(t.calculate_on)) ? parseInt(t.calculate_on) : t.calculate_on; const baseCalc=taxCalculations[calculateOnKey]; const b=baseCalc?baseCalc.amount:total; a=(b*v)/100; } else { switch(t.if_fixed){case 'person': case 'per_person': a=totalPax*v; break; case 'per_person_per_day': a=totalPax*nights*v; break; case 'per_tour_per_day': a=nights*v; break; case 'per_tour': case 'person_tour': a=v; break; default: a=v; } } const r=Math.ceil(isNaN(a)?0:a); total+=r; taxCalculations[t.tax_id]={amount:total,taxAmount:r}; });
                                        const dueNow=Math.max(total - safeNumber(list.paidAmount),0); return `Due: SGD ${Math.ceil(dueNow)}`; })()}
                                    </span>
                                  </div>
                                )}
                              </div>
                            </CustomPaymentTooltip>
                          </td>
                          <td className="date-column" style={{ display: "flex", alignItems: "center", justifyContent: "center", height: "100%" }}>
                            <span style={{ fontSize: "11px" }}>
                              {list.created_at ? dayjs(list.created_at).format("DD MMM YYYY, HH:mm") : "-"}
                            </span>
                          </td>
                        </tr>
                      ))
                    ) : (
                      <tr>
                        <td
                          colSpan="11"
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
            justifyContent: "center", // Center horizontally
            alignItems: "center", // Center vertically
            padding: "10px 0",
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
          displayId={displayId}
          bookings={bookings}
          modifiedPriceData={modifiedPriceData}
          markupAmount={markupAmount}
          discountAmount={discountAmount}
          totalPrice={totalPrice}
          tourId={tourId}
          pricehide={priceHideFromBookings}
        />
        {/* </Box> */}
      </div>
    </>
  );
}
