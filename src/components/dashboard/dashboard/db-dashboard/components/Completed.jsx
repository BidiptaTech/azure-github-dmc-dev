import React, { useState, useEffect, useMemo } from "react";

import { useSelector, useDispatch } from "react-redux";
import { useNavigate } from "react-router-dom";

import Box from "@mui/material/Box";
import { blue } from "@mui/material/colors";

import { Modal } from "antd";
import CategoriesAccordion from "./TourDetails";
import swal from "sweetalert";

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
import { fetchEditid, setTourId1 } from "@/slice/common/EditSlice";
import Snackbar from "@mui/material/Snackbar";
import MuiAlert from "@mui/material/Alert";
import { setDateService } from "@/slice/common/dateServicesSlice";
import { fetchLists } from "@/slice/common/TourlistSlice";
import { fetchViewDetails } from "../../../../././../slice/common/ViewDetails";
import Pagination from "../../common/Pagination";
import { Tooltip } from "@mui/material";
import { setBookingType } from "../../../../../slice/common/commonSlice";
import {
  setUserInfo as customerInfoSetUserInfo,
  resetBookingState,
  clearUserInfo,
} from "@/slice/common/customerInfo";

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
const countryMap = {
  in: "India",
  SG: "Singapore",
};

const formatDate = (dateString) => {
  // Parse the date in 'DD/MM/YYYY' format
  const [day, month, year] = dateString.split("/");

  // Create a new Date object from the parsed values (month is zero-indexed in JS Date)
  const date = new Date(`${year}-${month}-${day}`);

  // Get the day of the week (e.g., "Friday")
  const dayOfWeek = date.toLocaleString("en-us", { weekday: "long" });

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

export default function Pending() {
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(5);
  const [isModalVisible, setIsModalVisible] = useState(false);
  const navigate = useNavigate(); // Initialize navigate
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const [snackbarSeverity, setSnackbarSeverity] = useState("info");
  //const { currentStep } = useSelector((state) => state.steps);
  const { completedTours = [], status } = useSelector((state) => state.lists);

  const dispatch = useDispatch();
  const location = useLocation(); // Get the current location (route)
  const [order, setOrder] = useState("asc"); // State to track sort order
  const [tourId, setTId] = useState(null);

  // Trigger API call when navigating to Dashboard
  useEffect(() => {
    // Trigger fetchLists only when navigating to "/dashboard"
    if (location.pathname === "/dashboard/db-dashboard") {
      dispatch(fetchLists({ reset: true }));
    }
  }, [location.pathname, dispatch]); // Depend on the pathname // Dependency on location.pathname

  // if (status === "loading") return <p>Loading...</p>;
  // if (status === "failed") return <p>Error: {error}</p>;
  const [sortedLists, setSortedLists] = useState([]); // State for sorted data
  const [sortColumn, setSortColumn] = useState("id");
  // Update sortedLists when lists change
  useEffect(() => {
    setSortedLists(Array.isArray(completedTours) ? completedTours : []); // Safely check if lists is an array
  }, [completedTours]);

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
        label: "Local Transfer",
        path: "/dashboard/db-dashboard/localtransfer",
        key: "travel",
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

  // Calculate total pages
  const totalPages = Math.ceil(sortedLists.length / rowsPerPage);

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

    // Dispatch necessary actions
    dispatch(setId(list.id));
    dispatch(setTourId(list.id));
    dispatch(setTourId1(list.id));
    dispatch(setTourIdd(list.id));
    dispatch(resetBookingState());
    dispatch(clearUserInfo());

    // Trigger fetchViewDetails just like handleViewDetails
    dispatch(fetchViewDetails({ tour_id: list.id }));

    dispatch(fetchEditid())
      .unwrap()
      .then((response) => {
        console.log("Full Response Data:", response);

        // Extract data from the nested structure
        const data = response.data;
        const bookingType = data.bookingType || "booking";
        dispatch(setBookingType(bookingType));

        // Handle customer info if it exists
        // FIX: Check if customerInfo is an object (not an array)
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

        console.log(
          "CheckInTime:",
          data.CheckInTime,
          "CheckOutTime:",
          data.CheckOutTime
        );
        if (data.CheckInTime && data.CheckOutTime) {
          dispatch(setCheckIn(formatDate1(data.CheckInTime)));
          dispatch(setCheckOut(formatDate1(data.CheckOutTime)));
        } else {
          console.warn("Check-in or Check-out time is missing.");
        }

        dispatch(setTourId(id));

        if (destination) {
          console.log("Destination (Raw):", destination);

          const destinationArray = Array.isArray(destination)
            ? destination
            : destination.split(",").map((code) => code.trim());

          console.log("Formatted destination array:", destinationArray);

          const countryCodeArray = destinationArray
            .map((name) => reverseCountryMap[name])
            .filter(Boolean);

          console.log("Country code array:", countryCodeArray);

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

  const handleViewDetails = ({ id: tourId }) => {
    dispatch(fetchViewDetails({ tour_id: tourId }));
    setIsModalVisible(true);
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
    dispatch(fetchLists({ reset: true }));
  };

  const handleCloseModal = () => {
    setIsModalVisible(false);
  };

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
            <div className="overflow-scroll scroll-bar-1">
              <table className="table-3 -border-bottom col-12">
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
                          gap: "8px",
                        }}
                      >
                        <i
                          className="icon-ticket"
                          style={{
                            fontSize: "16px",
                            transition: "transform 0.3s ease",
                          }}
                        ></i>
                        Booking ID
                        {sortColumn === "id" && (
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
                        whiteSpace: "nowrap",
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
                          gap: "8px",
                        }}
                      >
                        <i
                          className="icon-calendar"
                          style={{
                            fontSize: "16px",
                            transition: "transform 0.3s ease",
                          }}
                        ></i>
                        Start Date
                        {sortColumn === "startDate" && (
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
                          gap: "8px",
                        }}
                      >
                        <i
                          className="icon-calendar-2"
                          style={{
                            fontSize: "16px",
                            transition: "transform 0.3s ease",
                          }}
                        ></i>
                        End Date
                        {sortColumn === "endDate" && (
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
                          gap: "8px",
                        }}
                      >
                        <i
                          className="icon-passenger"
                          style={{
                            fontSize: "16px",
                            transition: "transform 0.3s ease",
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
                          gap: "8px",
                        }}
                      >
                        <i
                          className="icon-destination"
                          style={{
                            fontSize: "16px",
                            transition: "transform 0.3s ease",
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
                      // onClick={() => handleColumnSort("customer_name")}
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
                          gap: "8px",
                        }}
                      >
                        <i
                          className="icon-customer"
                          style={{
                            fontSize: "16px",
                            transition: "transform 0.3s ease",
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
                          gap: "8px",
                        }}
                      >
                        <i
                          className="icon-status"
                          style={{
                            fontSize: "16px",
                            transition: "transform 0.3s ease",
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
                          gap: "8px",
                        }}
                      >
                        <i
                          className="icon-settings"
                          style={{
                            fontSize: "16px",
                            transition: "transform 0.3s ease",
                          }}
                        ></i>
                        Actions
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
                          <Skeleton variant="text" width={120} />
                        </td>
                        <td>
                          <Skeleton
                            variant="rectangular"
                            width={130}
                            height={35}
                          />
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
                        <td style={{ padding: "16px 20px" }}>
                          <span
                            style={{
                              backgroundColor: "rgba(53, 84, 209, 0.1)",
                              padding: "4px 10px",
                              borderRadius: "100px",
                              fontSize: "14px",
                              color: "#3554D1",
                              whiteSpace: "nowrap",
                              fontWeight: "600",
                            }}
                          >
                            {list.display_id}
                          </span>
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
                        </td>
                        <td style={{ padding: "16px 20px", whiteSpace: "nowrap" }}>
                          {list.created_at ? dayjs(list.created_at).format("DD MMM YYYY, HH:mm") : "-"}
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
                              className="icon-calendar"
                              style={{ fontSize: "18px", color: "#4CAF50" }}
                            ></i>
                            <span style={{ whiteSpace: "nowrap" }}>
                              {formatDate(list.check_in_time)}
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
                              className="icon-calendar-2"
                              style={{ fontSize: "18px", color: "#F44336" }}
                            ></i>
                            <span style={{ whiteSpace: "nowrap" }}>
                              {formatDate(list.check_out_time)}
                            </span>
                          </div>
                        </td>
                        <td style={{ padding: "16px 20px" }}>
                          <div
                            style={{
                              display: "flex",
                              alignItems: "center",
                              gap: "8px",
                              backgroundColor: "rgba(255, 152, 0, 0.1)",
                              padding: "6px 12px",
                              borderRadius: "20px",
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
                              backgroundColor: "rgba(76, 175, 80, 0.1)",
                              padding: "6px 12px",
                              borderRadius: "20px",
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
                        <td style={{ padding: "16px 20px" }}>
                          <div
                            style={{
                              display: "flex",
                              alignItems: "center",
                              gap: "8px",
                              backgroundColor: (() => {
                                // const status = list.status
                                //   ? list.status.toLowerCase()
                                //   : "";
                                const bookingType = list.booking_type
                                  ? list.booking_type.toLowerCase()
                                  : "";

                                // First check status colors
                                // if (status.includes("pending"))
                                //   return "rgba(250, 173, 20, 0.1)"; // semi-transparent warning
                                // if (
                                //   status.includes("accept") ||
                                //   status.includes("approved")
                                // )
                                //   return "rgba(82, 196, 26, 0.1)"; // semi-transparent success
                                // if (
                                //   status.includes("reject") ||
                                //   status.includes("cancel")
                                // )
                                //   return "rgba(245, 34, 45, 0.1)"; // semi-transparent danger

                                // Then check booking type colors
                                if (bookingType === "booking")
                                  return "rgba(82, 196, 26, 0.1)"; // semi-transparent success
                                if (bookingType === "enquiry")
                                  return "rgba(24, 144, 255, 0.1)"; // semi-transparent primary
                                if (bookingType === "pending")
                                  return "rgba(158, 230, 42, 0.1)"; // semi-transparent warning

                                return "rgba(24, 144, 255, 0.1)"; // semi-transparent default primary
                              })(),
                              padding: "6px 12px",
                              borderRadius: "20px",
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
                                fontSize: "18px",
                                color: (() => {
                                  // const status = list.status
                                  //   ? list.status.toLowerCase()
                                  //   : "";
                                  const bookingType = list.booking_type
                                    ? list.booking_type.toLowerCase()
                                    : "";

                                  // // First check status colors
                                  // if (status.includes("pending"))
                                  //   return "#faad14"; // warning
                                  // if (
                                  //   status.includes("accept") ||
                                  //   status.includes("approved")
                                  // )
                                  //   return "#52c41a"; // success
                                  // if (
                                  //   status.includes("reject") ||
                                  //   status.includes("cancel")
                                  // )
                                  //   return "#f5222d"; // danger

                                  // Then check booking type colors
                                  if (bookingType === "booking")
                                    return "#52c41a"; // success
                                  if (bookingType === "enquiry")
                                    return "#1890ff"; // primary
                                  if (bookingType === "pending")
                                    return "rgba(158, 230, 42, 0.1)"; // warning

                                  return "rgba(250, 173, 20, 0.1)"; // warning
                                })(),
                              }}
                            ></i>
                            <span
                              style={{
                                fontWeight: "600",
                                color: (() => {
                                  // const status = list.status
                                  //   ? list.status.toLowerCase()
                                  //   : "";
                                  const bookingType = list.booking_type
                                    ? list.booking_type.toLowerCase()
                                    : "";

                                  // First check status colors
                                  // if (status.includes("pending"))
                                  //   return "#faad14"; // warning
                                  // if (
                                  //   status.includes("accept") ||
                                  //   status.includes("approved")
                                  // )
                                  //   return "#52c41a"; // success
                                  // if (
                                  //   status.includes("reject") ||
                                  //   status.includes("cancel")
                                  // )
                                  //   return "#f5222d"; // danger

                                  // Then check booking type colors
                                  if (bookingType === "booking")
                                    return "#52c41a"; // success
                                  if (bookingType === "enquiry")
                                    return "#1890ff"; // primary
                                  if (bookingType === "pending")
                                    return "#faad14"; // warning

                                  return "#1890ff"; // default primary
                                })(),
                              }}
                            >
                              {list.booking_type
                                ? list.booking_type.toUpperCase()
                                : "Pending"}
                            </span>
                          </div>
                        </td>
                        <td
                          style={{
                            padding: "12px 20px",
                            minWidth: "280px",
                            whiteSpace: "nowrap",
                          }}
                        >
                          <div
                            style={{
                              display: "flex",
                              gap: "10px",
                              flexWrap: "nowrap",
                            }}
                          >
                            <button
                              className="button h-40 px-20 text-13 fw-500 rounded-8"
                              style={{
                                backgroundColor: "#3554D1",
                                color: "white",
                                border: "none",
                                transition: "all 0.3s ease",
                                boxShadow: "0 2px 6px rgba(53, 84, 209, 0.2)",
                                display: "flex",
                                alignItems: "center",
                                gap: "8px",
                              }}
                              onMouseEnter={(e) => {
                                e.target.style.backgroundColor = "#2C46BA";
                                e.target.style.boxShadow =
                                  "0 4px 8px rgba(53, 84, 209, 0.3)";
                                e.target.querySelector("i").style.transform =
                                  "scale(1.2)";
                              }}
                              onMouseLeave={(e) => {
                                e.target.style.backgroundColor = "#3554D1";
                                e.target.style.boxShadow =
                                  "0 2px 6px rgba(53, 84, 209, 0.2)";
                                e.target.querySelector("i").style.transform =
                                  "scale(1)";
                              }}
                              onClick={() => handleViewDetails(list)}
                            >
                              <i
                                className="icon-eye"
                                style={{
                                  fontSize: "16px",
                                  transition: "transform 0.3s ease",
                                }}
                              ></i>
                              View
                            </button>
                            {/* Only render Edit button if editOff is not 1 */}
                            {list.editOff !== 1 && (
                              <button
                                className="button h-40 px-20 text-13 fw-500 rounded-8"
                                style={{
                                  backgroundColor: "#FF9800",
                                  color: "white",
                                  border: "none",
                                  transition: "all 0.3s ease",
                                  boxShadow: "0 2px 6px rgba(255, 152, 0, 0.2)",
                                  display: "flex",
                                  alignItems: "center",
                                  gap: "8px",
                                }}
                                onMouseEnter={(e) => {
                                  e.target.style.backgroundColor = "#F57C00";
                                  e.target.style.boxShadow =
                                    "0 4px 8px rgba(255, 152, 0, 0.3)";
                                  e.target.querySelector("i").style.transform =
                                    "rotate(15deg)";
                                }}
                                onMouseLeave={(e) => {
                                  e.target.style.backgroundColor = "#FF9800";
                                  e.target.style.boxShadow =
                                    "0 2px 6px rgba(255, 152, 0, 0.2)";
                                  e.target.querySelector("i").style.transform =
                                    "rotate(0deg)";
                                }}
                                onClick={() => handleEdit(list)}
                              >
                                <i
                                  className="icon-edit"
                                  style={{
                                    fontSize: "16px",
                                    transition: "transform 0.3s ease",
                                  }}
                                ></i>
                                Edit
                              </button>
                            )}
                            {/* <button
                              className="button h-40 px-20 text-13 fw-500 rounded-8"
                              style={{
                                backgroundColor: "#F44336",
                                color: "white",
                                border: "none",
                                transition: "all 0.3s ease",
                                boxShadow: "0 2px 6px rgba(244, 67, 54, 0.2)",
                                display: "flex",
                                alignItems: "center",
                                gap: "8px",
                              }}
                              onMouseEnter={(e) => {
                                e.target.style.backgroundColor = "#E53935";
                                e.target.style.boxShadow =
                                  "0 4px 8px rgba(244, 67, 54, 0.3)";
                                e.target.querySelector("i").style.transform =
                                  "scale(1.2)";
                              }}
                              onMouseLeave={(e) => {
                                e.target.style.backgroundColor = "#F44336";
                                e.target.style.boxShadow =
                                  "0 2px 6px rgba(244, 67, 54, 0.2)";
                                e.target.querySelector("i").style.transform =
                                  "scale(1)";
                              }}
                              onClick={() => handleDelete(list.id)}
                            >
                              <i
                                className="icon-trash-2"
                                style={{
                                  fontSize: "16px",
                                  transition: "transform 0.3s ease",
                                }}
                              ></i>
                              Delete
                            </button> */}
                          </div>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td
                        colSpan="6"
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
      <Modal
        title="Tour Details"
        open={isModalVisible}
        onCancel={handleCloseModal}
        footer={null}
        width="100vw"
        style={{ top: 0, height: "100vh", padding: 0 }}
        bodyStyle={{
          height: "calc(100vh - 55px)",
          overflowY: "auto",
          padding: 0,
        }}
        centered
      >
        <CategoriesAccordion />
      </Modal>
      {/* </Box> */}
    </>
  );
}
