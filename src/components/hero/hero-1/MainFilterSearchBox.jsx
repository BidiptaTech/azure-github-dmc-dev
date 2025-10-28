import React, { useState, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import DateSearch from "../DateSearch";
import GuestSearch from "./GuestSearch";
import LocationSearch from "./LocationSearch";
import Snackbar from "@mui/material/Snackbar";
import MuiAlert from "@mui/material/Alert";
// import { fetchBookingid } from "../../../slice/common/BookingSlice"; //bookingslice
import { useDispatch, useSelector } from "react-redux";
import {
  setSearchLocation,
  setCheckIn,
  setCheckOut,
  setGuest,
  fetchBookingid,
} from "../../../slice/common/BookingSlice"; //bookingSlice
// import { settourdetails } from "../HotelSlices/hotelSlice"; //hotelSlice
import {
  resetHotels,
  setId,
  updateSearchState,
  settourdetails,
} from "../../../slice/hotel/hotelSlice"; //hotelSlice
import {
  setTourId,
  statusUpdate,
  updateStepStatus,
  setType,
} from "../../../slice/common/stepsSlice";
import { setBookingType, setHaveBooking } from "../../../slice/common/commonSlice";
import moment from "moment";
import { clearUserInfo } from "../../../slice/common/customerInfo"; // Add this import
import { clearAttractions, setIsFromMainSearch } from "../../../slice/attractions/attractionSlice";
import { fetchAttractions } from "../../../slice/attractions/attractionSlice";
import {
  fetchRestaurants,
  clearRestaurants,
  setIsFromMainSearch as setRestaurantFromMainSearch,
} from "../../../slice/restaurant/RestaurantsSlice";
import { resetguide } from "../../../slice/tourguide/guideslice";
import { resetVehicles } from "../../../slice/port/pickupDropSlice";
import { resetVehicles1 } from "../../../slice/localtour/Localslice";
import {setSelectedCity} from "@/slice/common/commonSlice";
import { resetAllServiceResponses } from "../../../slice/common/stepperButtonSlice";

// Create a reusable alert component
const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

const MainFilterSearchBox = () => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const user_country = useSelector((state) => state.auth.user_country);
  console.log('user_country',user_country)
  const [selectedLocation, setSelectedLocation] = useState(null);
  const [selectedDates, setSelectedDates] = useState([]);
  const [guestCounts, setGuestCounts] = useState({
    Adults: 1,
    Children: 0,
    Infants: 0,
    genders: [""], // Store gender selections for adults
    ages: [""], // Store age selections for children
  });
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const [snackbarSeverity, setSnackbarSeverity] = useState("error");

  // Create mapping for country codes to names
  const countryCodeToName = useMemo(() => {
    const mapping = {};
    if (user_country && Array.isArray(user_country)) {
      user_country.forEach(country => {
        if (country && country.name && country.code) {
          mapping[country.code] = country.name;
          mapping[country.code.toLowerCase()] = country.name;
        }
      });
    }
    return mapping;
  }, [user_country]);

  const handleLocationSelect = (location) => {
    console.log("Selected Location:", location);
    setSelectedLocation(location?.code);
  };

  const handleDateChange = (dates) => {
    setSelectedDates(dates);
  };

  const handleGuestChange = (updatedGuestCounts) => {
    setGuestCounts(updatedGuestCounts);
  };

  const validateForm = () => {
    if (!selectedLocation) {
      setSnackbarMessage("Please select a country.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (selectedDates.length === 0) {
      setSnackbarMessage("Please select check-in and check-out dates.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Validate that both check-in and check-out dates are selected
    if (selectedDates.length !== 2) {
      setSnackbarMessage("Please select both check-in and check-out dates.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (guestCounts.Adults <= 0) {
      setSnackbarMessage("At least one adult must be selected.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Check that male + female counts equal the adult count and neither is zero
    const maleCount = guestCounts.maleCount || 0;
    const femaleCount = guestCounts.femaleCount || 0;
    
    if (maleCount === 0 && femaleCount === 0) {
      setSnackbarMessage("Please specify male and female counts for adults.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }
    
    if (maleCount + femaleCount !== guestCounts.Adults) {
      setSnackbarMessage("Male and female count must equal the total adult count.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (
      guestCounts.Children > 0 &&
      !guestCounts.ages.every((age) => age.trim() !== "")
    ) {
      setSnackbarMessage("Please provide an age for all children.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    return true;
  };

  const handleCloseSnackbar = () => {
    setOpenSnackbar(false);
  };

  const handleSearch = async (e) => {
    e.preventDefault();

    if (!validateForm()) return;

    // Clear previous selected city
    dispatch(setSelectedCity(null));

    // Clear previous customer info when starting new search
    dispatch(clearUserInfo());

    // Format Dates - Handle different date formats safely
    let formattedCheckIn, formattedCheckOut;
    
    if (selectedDates && selectedDates.length >= 2) {
      // Check if selectedDates are DateObjects from react-multi-date-picker
      if (typeof selectedDates[0].format === 'function') {
        formattedCheckIn = selectedDates[0].format("DD/MM/YYYY");
        formattedCheckOut = selectedDates[1].format("DD/MM/YYYY");
      } 
      // Check if they're moment objects
      else if (selectedDates[0].format && typeof selectedDates[0].format === 'function') {
        formattedCheckIn = moment(selectedDates[0]).format("DD/MM/YYYY");
        formattedCheckOut = moment(selectedDates[1]).format("DD/MM/YYYY");
      }
      // If they're Date objects
      else if (selectedDates[0] instanceof Date) {
        formattedCheckIn = moment(selectedDates[0]).format("DD/MM/YYYY");
        formattedCheckOut = moment(selectedDates[1]).format("DD/MM/YYYY");
      }
      // If they're strings
      else if (typeof selectedDates[0] === 'string') {
        formattedCheckIn = selectedDates[0];
        formattedCheckOut = selectedDates[1];
      }
      else {
        console.error("Unknown date format:", selectedDates);
        return;
      }
    } else {
      console.error("Invalid dates selected:", selectedDates);
      return;
    }

    // Convert country code to full name for destination
    const destinationName = countryCodeToName[selectedLocation] || selectedLocation;
    
    // Step 2: Dispatch Redux actions to update the search state
    dispatch(setSearchLocation(selectedLocation));
    dispatch(setCheckIn(formattedCheckIn));
    dispatch(setCheckOut(formattedCheckOut));
    dispatch(resetHotels());

    // Clear any existing attractions data
    dispatch(clearAttractions());
    dispatch(setIsFromMainSearch(true));
    dispatch(resetguide());
    dispatch(resetVehicles());
    dispatch(resetVehicles1());

    // Clear any existing restaurants data
    dispatch(clearRestaurants());
    dispatch(setRestaurantFromMainSearch(true));

    // Reset stepper button state for new search
    dispatch(resetAllServiceResponses());

    // Create genders array based on male and female counts
    const maleCount = guestCounts.maleCount || 0;
    const femaleCount = guestCounts.femaleCount || 0;
    const genders = [
      ...Array(maleCount).fill("Male"),
      ...Array(femaleCount).fill("Female")
    ];

    // Dispatch guest details
    dispatch(
      setGuest({
        adults: guestCounts.Adults,
        children: guestCounts.Children,
        infant: guestCounts.Infants,
        adultGenders: genders,
        childrenAges: guestCounts.ages,
      })
    );

    // Add this line to ensure attractions aren't loaded
    // dispatch(
    //   fetchAttractions({
    //     city: selectedLocation,
    //     adults: guestCounts.Adults,
    //     children: guestCounts.Children,
    //     fromMainSearch: true,
    //     start: 0,
    //     limit: 5,
    //   })
    // );

    // Add this line to ensure restaurants aren't loaded
    // dispatch(
    //   fetchRestaurants({
    //     city: selectedLocation,
    //     date: formattedCheckIn,
    //     adults: guestCounts.Adults,
    //     children: guestCounts.Children,
    //     fromMainSearch: true,
    //   })
    // );

    // Step 4: Fetch Booking ID
    dispatch(fetchBookingid({
      destination: destinationName, // Use full country name in payload
      check_in: formattedCheckIn,
      check_out: formattedCheckOut,
      adult: guestCounts.Adults,
      child: guestCounts.Children,
      infant: guestCounts.Infants,
      male: guestCounts.maleCount || 0,
      female: guestCounts.femaleCount || 0,
      children_ages: guestCounts.ages.join(',')
    }))
      .unwrap()
      .then((data) => {
        const id = data?.tour_id || data?.data?.tour_id;
        
        // Ensure we're using the full country name for destination
        const destination = destinationName; // Use the full country name from our mapping
        console.log('destination destination', destination);

        if (!id || !destination) {
          console.error("Tour ID or destination not found in response:", data);
          throw new Error("Invalid response data.");
        }
        dispatch(setHaveBooking(false));
        // Step 5: Update state with API response
        dispatch(updateSearchState({ location: destination })); // Update location with full name
        dispatch(settourdetails({
          ...data,
          destination: destination // Override the destination with full name
        }));
        dispatch(setId(id));
        dispatch(setTourId(id));
        dispatch(setBookingType("null"));

        // Step 6: Create search query params
        const searchParams = new URLSearchParams({
          location: selectedLocation, // Keep using code in URL
          dates: [formattedCheckIn, formattedCheckOut].join(","),
          guests: JSON.stringify(guestCounts),
        });

        dispatch(setType(" "));
        dispatch(updateStepStatus({ key: "hotel", status: 2 }));
        dispatch(statusUpdate()).unwrap();

        // Step 7: Navigate to the hotel search results page
        navigate(
          `/dashboard/db-dashboard/view-hotel-search/${id}?${searchParams}`
        );
      })
      .catch((error) => {
        console.error("Error fetching booking ID:", error);
        setSnackbarMessage(
          "Failed to fetch booking details. Please try again."
        );
        setSnackbarSeverity("error");
        setOpenSnackbar(true);
      });
  };

  return (
    <div className="position-relative mt-30 md:mt-20 js-tabs-content">
      <div className="mainSearch -w-900 bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-100">
        <div className="button-grid items-center">
          <LocationSearch onLocationSelect={handleLocationSelect} />

          <div className="searchMenu-date px-30 lg:py-20 lg:px-0 js-form-dd js-calendar">
            <div>
              <h4 className="text-15 fw-500 ls-2 lh-16">
                Check in - Check out
              </h4>
              <DateSearch onDateChange={handleDateChange} />
            </div>
          </div>

          <GuestSearch
            onGuestChange={handleGuestChange}
            guestCounts={guestCounts}
          />

          <div className="button-item">
            <button
              className="mainSearch__submit button -dark-1 h-60 px-35 col-12 rounded-100 bg-blue-1 text-white"
              onClick={handleSearch}
            >
              <i className="icon-search text-20 mr-10" />
              Search
            </button>
          </div>
        </div>
      </div>
      <Snackbar
        open={openSnackbar}
        autoHideDuration={6000}
        onClose={handleCloseSnackbar}
      >
        <Alert onClose={handleCloseSnackbar} severity={snackbarSeverity}>
          {snackbarMessage}
        </Alert>
      </Snackbar>
    </div>
  );
};

export default MainFilterSearchBox;
