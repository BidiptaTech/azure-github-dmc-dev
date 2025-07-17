import React, { useState, useEffect, useMemo, useRef } from "react";

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
import { fetchLists } from "@/slice/common/TourlistSlice";
import Pagination from "../../common/Pagination";
import { setBookingType } from "../../../../../slice/common/commonSlice";
import dayjs from "dayjs";
import { Button, InputAdornment, TextField, Typography, Tooltip, IconButton } from "@mui/material";
import { Visibility } from "@mui/icons-material";
import { toast } from "react-toastify";
import Cookies from "js-cookie";
import axios from "axios";
import {
  setUserInfo as customerInfoSetUserInfo,
  resetBookingState,
  clearUserInfo,
} from "@/slice/common/customerInfo";

import PrintModal from "./PrintModal";
import TourDetailsModal1 from "./TourDetailsModal1";

const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

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

export default function Pending() {
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(5);
  const [isModalVisible, setIsModalVisible] = useState(false);
  const navigate = useNavigate(); // Initialize navigate
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const [snackbarSeverity, setSnackbarSeverity] = useState("info");
  //const { currentStep } = useSelector((state) => state.steps);
  const { lists = [], status } = useSelector((state) => state.lists);
  const { bookings = {}, status: viewDetailsStatus } = useSelector(
    (state) => state.viewDetails
  );

  const contentRef = useRef(null);

  // Updated states for markup and discount (flat amounts instead of percentages)
  const [markupAmount, setMarkupAmount] = useState(0);
  const [discountAmount, setDiscountAmount] = useState(0);
  const [bookingType1, setBookingType1] = useState(null);
  const [displayId, setDisplayId] = useState(null);
  const [modifiedPriceData, setModifiedPriceData] = useState(null);
  //const { currentStep } = useSelector((state) => state.steps);
  const { DmcName, DmcLogo } = useSelector((state) => state.auth);
  const dispatch = useDispatch();
  const location = useLocation(); // Get the current location (route)
  const [order, setOrder] = useState("asc"); // State to track sort order
  const [sortColumn, setSortColumn] = useState("id");
  const [isPrintModalVisible, setIsPrintModalVisible] = useState(false);

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
    setSortedLists(Array.isArray(lists) ? lists : []); // Safely check if lists is an array
  }, [lists]);

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

  const handleSort = () => {
    const sortedData = [...sortedLists].sort((a, b) => {
      if (order === "asc") {
        return a.id - b.id; // Ascending order
      } else {
        return b.id - a.id; // Descending order
      }
    });
    setSortedLists(sortedData);
    setOrder(order === "asc" ? "desc" : "asc"); // Toggle sort order
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

  // Calculate total pages
  const totalPages = Math.ceil(sortedLists.length / rowsPerPage);

  const handleCloseSnackbar = (event, reason) => {
    if (reason === "clickaway") {
      return;
    }
    setOpenSnackbar(false);
  };

  // const handleEdit = (list) => {
  //   console.log("Edit Details for:", list.id);
  //   console.log("Initial destination:", list.destination);

  //   // Create the reverse mapping from country names to country codes
  //   const reverseCountryMap = Object.fromEntries(
  //     Object.entries(countryMap).map(([code, name]) => [name, code])
  //   );

  //   // Set the snackbar state for an info message indicating submission
  //   setSnackbarMessage("Submitting Id...");
  //   setSnackbarSeverity("info");
  //   setOpenSnackbar(true);

  //   // Dispatch actions to update state
  //   dispatch(setId(list.id));

  //   dispatch(setTourId1(list.id));
  //   dispatch(setTourIdd(list.id));

  //   // Fetch the edit data
  //   dispatch(fetchEditid())
  //     .unwrap() // Unwrap the promise to handle the payload directly
  //     .then((data) => {
  //       const id = data.tour_id || data.data?.tour_id;
  //       const destination = data.destination || data.data?.destination;

  //       if (!id || !destination) {
  //         console.error("Tour ID or destination not found in response.");
  //         throw new Error("Invalid response data.");
  //       }
  //       console.log("date service", data?.service?.date_service);

  //       if (data?.service?.date_service) {
  //         dispatch(setDateService(data.service.date_service));
  //       }
  //       if (data?.step) {
  //         dispatch(updateStepStatus({ key: data.step, status: 2 }));
  //       }
  //       // Handle check-in and check-out times if available
  //       if (data.CheckInTime && data.CheckOutTime) {
  //         dispatch(setCheckIn(formatDate1(data.CheckInTime)));
  //         dispatch(setCheckOut(formatDate1(data.CheckOutTime)));
  //       } else {
  //         console.warn("Check-in or Check-out time is missing.");
  //       }
  //       dispatch(setTourId(id));
  //       // Handle destination array formatting
  //       if (destination) {
  //         const destinationArray = Array.isArray(destination)
  //           ? destination
  //           : destination.split(",").map((code) => code.trim());

  //         console.log("Formatted destination array:", destinationArray);

  //         // Map country names to country codes
  //         const countryCodeArray = destinationArray
  //           .map((name) => reverseCountryMap[name])
  //           .filter(Boolean);

  //         console.log("Country code array:", countryCodeArray);
  //         dispatch(statusUpdate()).unwrap();
  //         // Dispatch actions for search location and state
  //         dispatch(setSearchLocation(countryCodeArray));
  //         dispatch(updateSearchState({ location: destinationArray }));
  //         dispatch(resetHotels());
  //       } else {
  //         console.warn("Destination is missing");
  //       }

  //       // Set success snackbar message after the operation
  //       dispatch(settourdetails(data));
  //       navigate(`/dashboard/db-dashboard/view-hotel-search/${id}`, {
  //         state: { tourDetails: data },
  //       });

  //       // Success message for snackbar
  //       setSnackbarMessage("Search submitted successfully!");
  //       setSnackbarSeverity("success");
  //       setOpenSnackbar(true); // Show the snackbar
  //     })
  //     .catch((error) => {
  //       // Handle errors during the fetch request
  //       console.error("Error fetching booking:", error);
  //       setSnackbarMessage(
  //         error?.message || "Something went wrong. Please try again."
  //       );
  //       setSnackbarSeverity("error");
  //       setOpenSnackbar(true); // Show the snackbar for error
  //     });
  // };

  // Update handleViewDetails to also check the enquiry status
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
    dispatch(fetchViewDetails({ tour_id: tourId }));

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
    }, 2000);
  };

  const handleDelete = async (tourId) => {
    const willDelete = await swal({
      title: "Are you sure to delete this tour?",
      icon: "warning",
      dangerMode: true,
    });

    if (willDelete) {
      console.log("Delete tour with ID:", tourId);
      swal("Deleted!", "Your tour has been deleted!", "success");
    } else {
      swal("Cancelled", "Your tour is safe!", "info");
    }
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
  // useEffect(() => {
  //   if (bookings && Object.keys(bookings).length > 0) {
  //     let totalPrice = 0;

  //     // Calculate total from all services
  //     const services = [
  //       ...(bookings.hotel || bookings.data?.hotel || []),
  //       ...(bookings.entry_port || bookings.data?.entry_port || []),
  //       ...(bookings.exit_port || bookings.data?.exit_port || []),
  //       ...(bookings.attraction || bookings.data?.attraction || []),
  //       ...(bookings.guide || bookings.data?.guide || []),
  //       ...(bookings.restaurant || bookings.data?.restaurant || []),
  //       ...(bookings.travel_point || bookings.data?.travel_point || []),
  //     ];

  //     services.forEach((item) => {
  //       if (item.totalPrice) {
  //         totalPrice += parseFloat(item.totalPrice);
  //       }
  //     });

  //     setEnquiryAmount(Math.ceil(totalPrice) || 0);
  //   }
  // }, [bookings]);

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
    // Reset modified data when canceling
    setModifiedPriceData(null);
  };

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
                        padding: "14px 20px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
                        whiteSpace: "nowrap",
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
                        padding: "14px 20px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        whiteSpace: "nowrap",
                        transition: "background-color 0.3s ease",
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
                        padding: "14px 20px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
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
                        padding: "14px 20px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
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
                        padding: "14px 10px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
                        width: "80px", // Set a fixed narrow width for Pax column
                        minWidth: "80px",
                        maxWidth: "80px",
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
                            style={{ fontSize: "12px", opacity: 0.7 }}
                          ></i>
                        )}
                      </div>
                    </th>
                    <th
                      style={{
                        backgroundColor: "#f5f7fc",
                        padding: "14px 20px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
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
                        padding: "14px 20px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        whiteSpace: "nowrap",
                        transition: "background-color 0.3s ease",
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
                        padding: "14px 20px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
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
                        padding: "14px 20px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
                        whiteSpace: "nowrap",
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
                        padding: "14px 20px",
                        fontWeight: "600",
                        color: "#3554D1",
                        cursor: "pointer",
                        transition: "background-color 0.3s ease",
                        whiteSpace: "nowrap",
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
                        <td style={{ padding: "12px 20px", minWidth: "80px", whiteSpace: "nowrap" }}>
                          <Skeleton variant="text" width={80} />
                        </td>
                        <td style={{ padding: "16px 20px", width: "100px", minWidth: "100px", maxWidth: "100px" }}>
                          <Skeleton variant="text" width={80} />
                        </td>
                        <td style={{ padding: "16px 20px", width: "90px", minWidth: "90px", maxWidth: "90px" }}>
                          <Skeleton variant="text" width={80} />
                        </td>
                        <td style={{ padding: "16px 20px", width: "90px", minWidth: "90px", maxWidth: "90px" }}>
                          <Skeleton variant="text" width={80} />
                        </td>
                        <td style={{ padding: "16px 10px", width: "80px", minWidth: "80px", maxWidth: "80px", textAlign: "center" }}>
                          <Skeleton variant="text" width={60} />
                        </td>
                        <td style={{ padding: "16px 20px", width: "120px", minWidth: "120px", maxWidth: "120px" }}>
                          <Skeleton variant="text" width={100} />
                        </td>
                        <td style={{ padding: "16px 20px", width: "130px", minWidth: "130px", maxWidth: "130px" }}>
                          <Skeleton variant="text" width={100} />
                        </td>
                        <td style={{ padding: "16px 20px", width: "100px", minWidth: "100px", maxWidth: "100px" }}>
                          <Skeleton variant="text" width={80} />
                        </td>
                        <td style={{ padding: "8px 12px", width: "110px", minWidth: "110px", maxWidth: "110px" }}>
                          <Skeleton variant="text" width={80} />
                        </td>
                        <td style={{ padding: "8px 12px", width: "120px", minWidth: "120px", maxWidth: "120px" }}>
                          <Skeleton variant="text" width={100} />
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
                            padding: "12px 20px",
                            minWidth: "80px",
                            whiteSpace: "nowrap",
                          }}
                        >
                          <div
                            style={{
                              display: "flex",
                              gap: "8px",
                              flexWrap: "nowrap",
                              whiteSpace: "nowrap",
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
                            {/* {list.editOff !== 1 && (
                              <Tooltip title="Edit" arrow>
                                <IconButton
                                  size="small"
                                  onClick={() => handleEdit(list)}
                                  sx={{
                                    backgroundColor: "#FF9800",
                                    color: "white",
                                    width: "36px",
                                    height: "36px",
                                    boxShadow: "0 2px 6px rgba(255, 152, 0, 0.2)",
                                    transition: "all 0.3s ease",
                                    "&:hover": {
                                      backgroundColor: "#F57C00",
                                      boxShadow: "0 4px 8px rgba(255, 152, 0, 0.3)",
                                      transform: "translateY(-2px)",
                                    },
                                  }}
                                >
                                  <i
                                    className="icon-edit"
                                    style={{
                                      fontSize: "16px",
                                    }}
                                  ></i>
                                </IconButton>
                              </Tooltip>
                            )} */}
                            {/* <Tooltip title="Delete" arrow>
                              <IconButton
                                size="small"
                                onClick={() => handleDelete(list.id)}
                                sx={{
                                  backgroundColor: "#F44336",
                                  color: "white",
                                  width: "36px",
                                  height: "36px",
                                  boxShadow: "0 2px 6px rgba(244, 67, 54, 0.2)",
                                  transition: "all 0.3s ease",
                                  "&:hover": {
                                    backgroundColor: "#E53935",
                                    boxShadow: "0 4px 8px rgba(244, 67, 54, 0.3)",
                                    transform: "translateY(-2px)",
                                  },
                                }}
                              >
                                <i
                                  className="icon-trash-2"
                                  style={{
                                    fontSize: "16px",
                                  }}
                                ></i>
                              </IconButton>
                            </Tooltip> */}
                          </div>
                        </td>
                    <td style={{ padding: "16px 20px" }}>
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
  </div>
</td>
                        <td style={{ padding: "16px 20px" }}>
                          <Tooltip 
                            title={formatDateTooltip(list.check_in_time)}
                            arrow
                            placement="top"
                          >
                            <div
                              style={{
                                display: "flex",
                                alignItems: "center",
                                gap: "8px",
                                cursor: "pointer",
                              }}
                            >
                              <i
                                className="icon-calendar"
                                style={{ fontSize: "18px", color: "#4CAF50" }}
                              ></i>
                              <span style={{ whiteSpace: "nowrap" }}>
                                {formatDate(list.check_in_time)}
                              </span>
                            </div>
                          </Tooltip>
                        </td>
                        <td style={{ padding: "16px 20px" }}>
                          <Tooltip 
                            title={formatDateTooltip(list.check_out_time)}
                            arrow
                            placement="top"
                          >
                            <div
                              style={{
                                display: "flex",
                                alignItems: "center",
                                gap: "8px",
                                cursor: "pointer",
                              }}
                            >
                              <i
                                className="icon-calendar-2"
                                style={{ fontSize: "18px", color: "#F44336" }}
                              ></i>
                              <span style={{ whiteSpace: "nowrap" }}>
                                {formatDate(list.check_out_time)}
                              </span>
                            </div>
                          </Tooltip>
                        </td>
                        <td
                          style={{
                            padding: "16px 10px",
                            whiteSpace: "nowrap",
                            width: "80px",
                            minWidth: "80px",
                            maxWidth: "80px",
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
                        <td style={{ padding: "16px 20px" }}>
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
                              {list.destination
                                ? list.destination
                                    .split(",")
                                    .map(
                                      (code) =>
                                        countryMap[code.trim()] ||
                                        countryMap[code.trim().toLowerCase()] ||
                                        code.trim()
                                    )
                                    .join(", ")
                                : "N/A"}
                            </span>
                          </div>
                        </td>
                        <td style={{ padding: "16px 20px" }}>
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
                            <span>{list.customer_name || "N/A"}</span>
                          </div>
                        </td>
                        <td style={{ padding: "16px 20px" }}>
                        <div                            style={{                              display: "flex",                              alignItems: "center",                              gap: "6px",                              backgroundColor: getBackgroundColor(list.tour_status),
                              padding: "4px 8px",
                              borderRadius: "16px",
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
                              style={{                                fontSize: "14px",                                color: getTextColor(list.tour_status),
                              }}
                            ></i>
                            <span                              style={{                                fontWeight: "600",                                color: getTextColor(list.tour_status),
                                fontSize: "11px",
                              }}                            >                              {list.tour_status || "Pending"}
                            </span>
                          </div>
                        </td>
                        <td
                          style={{
                            padding: "8px 12px",
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

                        <td style={{ padding: "8px 12px" }}>
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
                        colSpan="13"
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
      <TourDetailsModal1
        isModalVisible={isModalVisible}
        handleCloseModal={handleCloseModal}
        markupAmount={markupAmount}
        setMarkupAmount={setMarkupAmount}
        discountAmount={discountAmount}
        setDiscountAmount={setDiscountAmount}
        handlePrint={handlePrint}
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
        bookings={bookings}
        modifiedPriceData={modifiedPriceData}
        markupAmount={markupAmount}
        discountAmount={discountAmount}
      />
      {/* </Box> */}
    </>
  );
}
