import React, { useState, useEffect, useCallback } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import DateSearch from "../DateSearch";
import GuestSearch from "./GuestSearch";
import LocationSearch from "./LocationSearch";
import Snackbar from "@mui/material/Snackbar";
import MuiAlert from "@mui/material/Alert";
import { useDispatch, useSelector } from "react-redux";
import {
  resetHotels,
  setId,
  updateSearchState,
  settourdetails,
} from "../../../slice/hotel/hotelSlice";
import {
  setTourId,
  statusUpdate,
  updateStepStatus,
  setType,
} from "../../../slice/common/stepsSlice";
import { setBookingType } from "../../../slice/common/commonSlice";
import moment from "moment";
import { clearUserInfo } from "../../../slice/common/customerInfo";
import { clearAttractions } from "../../../slice/attractions/attractionSlice";
import { fetchAttractions } from "../../../slice/attractions/attractionSlice";
import {
  fetchRestaurants,
  clearRestaurants,
} from "../../../slice/restaurant/RestaurantsSlice";
import { resetguide } from "../../../slice/tourguide/guideslice";
import { resetVehicles } from "../../../slice/port/pickupDropSlice";
import { resetVehicles1 } from "../../../slice/localtour/Localslice";
import { setSelectedCity } from "@/slice/common/commonSlice";
import { fetchBookingid, setSearchLocation, setCheckIn, setCheckOut, setGuest } from "../../../slice/common/EnquirySlice";
import { fetchEnquiryList, clearEnquiryList } from "../../../slice/common/enquiryListSlice";
import { store } from "../../../store/store";
import { selectSelectedDmcIds } from "../../../slice/dmc/dmcSlice";
import { clearServiceDetails, clearSpecificService } from "../../../slice/common/EnquirySlice"; //EnquirySlice

// Create a reusable alert component
const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

const MainFilterSearchBox = ({ onNext, clearDataOnNext = false }) => {
  const dispatch = useDispatch();
  const location = useLocation();
  const [locationData, setLocationData] = useState(null);
  const [selectedDates, setSelectedDates] = useState([]);
  const [guestCounts, setGuestCounts] = useState({
    Adults: 1,
    Children: 0,
    Infants: 0,
    maleCount: 0,
    femaleCount: 0,
    ages: [], // Store age selections for children
  });
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const [snackbarSeverity, setSnackbarSeverity] = useState("error");

  const handleLocationSelect = (location) => {
    // Store the complete location data including country and city
    setLocationData(location);
    
    // Clear previous enquiry list data
    // Note: We're not fetching enquiry list here anymore
    // It will be fetched only when the form is submitted
    dispatch(clearEnquiryList());
    
    console.log("Location selected:", location);
  };

  const handleDateChange = (dates) => {
    setSelectedDates(dates);
  };

  const handleGuestChange = (updatedGuestCounts) => {
    setGuestCounts(updatedGuestCounts);
  };

  const validateForm = () => {
    if (!locationData) {
      setSnackbarMessage("Please select a location.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Check if both country and city are selected
    if (!locationData.country) {
      setSnackbarMessage("Please select a country.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (!locationData.city) {
      setSnackbarMessage("Please select a city.");
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

  const handleSearch = useCallback(async (e) => {
    e.preventDefault();

    if (!validateForm()) return;

    // Clear previous selected city for the Redux store
    dispatch(setSelectedCity(null));

    // Clear previous customer info when starting new search
    dispatch(clearUserInfo());
    dispatch(clearServiceDetails());
    dispatch(clearSpecificService());
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

    // Get the country code
    const countryCode = locationData.countryCode;
    // Get the city code
    const cityCode = locationData.cityCode;
    // Get the properly formatted combined code directly from the location object
    const combinedCode = locationData.cityCode;
    
    console.log("Setting search location with:", [countryCode, combinedCode]);
    console.log("Location data:", locationData);
    
    // Set location data in the right format for EnquirySlice
    const locationPayload = {
      country: locationData.country,
      city: locationData.city
    };
    console.log("Dispatching location data to EnquirySlice:", locationPayload);
    
    // Dispatch the new location data
    dispatch(setSearchLocation(locationPayload));
    
    dispatch(setCheckIn(formattedCheckIn));
    dispatch(setCheckOut(formattedCheckOut));
    dispatch(resetHotels());

    // Set the selected city in Redux for other components to use
    dispatch(setSelectedCity({
      countryCode: locationData.countryCode,
      countryName: locationData.country,
      cityCode: locationData.cityCode,
      cityName: locationData.city,
      combinedCode: combinedCode
    }));

    // Clear any existing attractions data
    dispatch(clearAttractions());
    dispatch(clearServiceDetails());
    dispatch(clearSpecificService());
    dispatch(resetguide());
    dispatch(resetVehicles());
    dispatch(resetVehicles1());

    // Clear any existing restaurants data
    dispatch(clearRestaurants());

    // Create genders array based on male and female counts
    const maleCount = guestCounts.maleCount || 0;
    const femaleCount = guestCounts.femaleCount || 0;
    const genders = [
      ...Array(maleCount).fill("Male"),
      ...Array(femaleCount).fill("Female")
    ];

    // console.log("Guest data before dispatch:", {
    //   adults: guestCounts.Adults,
    //   children: guestCounts.Children,
    //   infants: guestCounts.Infants,
    //   maleCount,
    //   femaleCount,
    //   genders,
    //   ages: guestCounts.ages
    // });

    // Dispatch guest details to EnquirySlice
    // console.log("Dispatching guest details to EnquirySlice:", {
    //   adults: guestCounts.Adults.toString(),
    //   children: guestCounts.Children.toString(),
    //   infant: guestCounts.Infants.toString(),
    //   adultGenders: genders,
    //   childrenAges: guestCounts.ages || [],
    //   maleCount: maleCount,
    //   femaleCount: femaleCount
    // });
    
    dispatch(
      setGuest({
        adults: guestCounts.Adults.toString(),
        children: guestCounts.Children.toString(),
        infant: guestCounts.Infants.toString(),
        adultGenders: genders,
        childrenAges: guestCounts.ages || [],
        maleCount: maleCount,
        femaleCount: femaleCount
      })
    );

    // Add a small delay to ensure the guest data is set before making the API call
    setTimeout(() => {
      // Debug: Get current state from Redux to verify data
      const state = store.getState();
      console.log("Redux state before API call:", {
        searchLocation: state.enquiry.searchLocation,
        guests: state.enquiry.guests,
        checkIn: state.enquiry.checkIn,
        checkOut: state.enquiry.checkOut
      });
      
      // Step 4: Fetch Enquiry ID using the EnquirySlice's fetchBookingid
      dispatch(fetchBookingid())
        .unwrap()
        .then((data) => {
          console.log("Enquiry response:", data);
          // EnquirySlice response should have enquiry_id instead of tour_id
          const id = data?.multi_enq_id 
          
          const country = data?.country || data?.data?.country;
          const city = data?.city || data?.data?.city;

          if (!id) {
            console.error("Enquiry ID not found in response:", data);
            throw new Error("Invalid response data.");
          }

          // Step 5: Update state with API response
          dispatch(updateSearchState({ 
            location: country,
            cityName: city,
            countryName: country
          }));
          
          dispatch(settourdetails(data)); // Set full enquiry details
          dispatch(setId(id)); // Set the ID
          dispatch(setTourId(id));
          dispatch(setBookingType("enquiry")); // Set booking type to enquiry

   
          
          // Get the auth token to log
          const authToken = localStorage.getItem("token");
          console.log("Auth token available:", authToken ? "Yes" : "No");
          
        

          // Fetch the enquiry list data for hotels and other services
          // Use API response data if available, otherwise fall back to original locationData
          const fetchParams = {
            country: country || locationData.country,
            city: city || locationData.city
          };

          console.log("Fetching enquiry list with params:", fetchParams);
          console.log("Data sources:", {
            apiCountry: country,
            apiCity: city,
            locationDataCountry: locationData.country,
            locationDataCity: locationData.city,
            locationData: locationData
          });

          // Validate fetchParams before making the API call
          if (!fetchParams.country || !fetchParams.city) {
            console.error("Invalid fetchParams:", fetchParams);
            console.error("LocationData available:", !!locationData);
            if (locationData) {
              console.error("LocationData structure:", locationData);
            }
            setSnackbarMessage("Invalid location data. Please try again.");
            setSnackbarSeverity("error");
            setOpenSnackbar(true);
            return;
          }

          // Ensure we have string values
          if (typeof fetchParams.country !== 'string' || typeof fetchParams.city !== 'string') {
            console.error("Country or city is not a string:", fetchParams);
            setSnackbarMessage("Invalid location data format. Please try again.");
            setSnackbarSeverity("error");
            setOpenSnackbar(true);
            return;
          }

        
          
          dispatch(fetchEnquiryList(fetchParams));

          dispatch(setType("enquiry"));
          dispatch(updateStepStatus({ key: "hotel", status: 2 })); // Update step status
          dispatch(statusUpdate()).unwrap();

          // Move to next step instead of navigating
          onNext();
        })
        .catch((error) => {
          console.error("Error fetching enquiry ID:", error);
          setSnackbarMessage(
            "Failed to create enquiry. Please try again."
          );
          setSnackbarSeverity("error");
          setOpenSnackbar(true);
        });
    }, 300);
  }, [validateForm, selectedDates, locationData, guestCounts, location.state, dispatch, onNext]);


  return (
    <div className="js-tabs-content d-flex justify-center">
      <div className="mainSearch -w-900 bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-100 w-100">
        <div className="button-grid items-center">
          <div className="searchMenu-loc px-30 lg:py-20 lg:px-0 js-form-dd">
            <LocationSearch onLocationSelect={handleLocationSelect} />
          </div>

          <div className="searchMenu-date px-30 lg:py-20 lg:px-0 js-form-dd js-calendar">
            <div>
              <h4 className="text-15 fw-500 ls-2 lh-16">
                Check in - Check out
              </h4>
              <DateSearch onDateChange={handleDateChange} />
            </div>
          </div>

          <div className="searchMenu-guests px-30 lg:py-20 lg:px-0 js-form-dd js-form-counters">
            <GuestSearch
              onGuestChange={handleGuestChange}
              guestCounts={guestCounts}
            />
          </div>

          <div className="button-item">
            <button
              className="mainSearch__submit button -dark-1 h-60 px-35 col-12 rounded-100 bg-blue-1 text-white"
              onClick={handleSearch}
            >
              <i className="icon-search text-20 mr-10" />
              Submit
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
