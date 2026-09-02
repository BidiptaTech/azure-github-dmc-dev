import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import DateSearch from "../DateSearch";
import GuestSearch from "./GuestSearch";
import LocationSearch from "./LocationSearch";
import Snackbar from "@mui/material/Snackbar";
import MuiAlert from "@mui/material/Alert";
import { useDispatch, useSelector } from "react-redux";
import {
  setCheckIn,
  setCheckOut,
  setGuest,
} from "../../../slice/common/BookingSlice";
import {
  resetHotels,
  setId,
  updateSearchState,
  settourdetails,
} from "../../../slice/hotel/hotelSlice";
import {
  resetSteps,
} from "../../../slice/common/stepsSlice";
import {
  setHaveBooking,
  setSelectedCity,
  setCityWiseDates,
} from "../../../slice/common/commonSlice";
import moment from "moment";
import { clearUserInfo } from "../../../slice/common/customerInfo";
import { clearAttractions, setIsFromMainSearch } from "../../../slice/attractions/attractionSlice";
import {
  clearRestaurants,
  setIsFromMainSearch as setRestaurantFromMainSearch,
} from "../../../slice/restaurant/RestaurantsSlice";
import { resetguide } from "../../../slice/tourguide/guideslice";
import { resetVehicles } from "../../../slice/port/pickupDropSlice";
import { resetVehicles1 } from "../../../slice/localtour/Localslice";
import { resetAllServiceResponses } from "../../../slice/common/stepperButtonSlice";
import { setCity } from "../../../slice/common/citySlice";
import { clearSelectedDmc, fetchDMCsByCountry } from "@/slice/dmc/dmcSlice";
import { clearSelectedCities } from "@/slice/common/citiesSlice";
import { clearBookingFlow } from "@/utils/clearBookingFlow";
import { clearViewDetails } from "@/slice/common/ViewDetails";
import swal from "sweetalert";

// Create a reusable alert component
const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

const MainFilterSearchBox = () => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const selectedCities = useSelector(
    (state) => state.cities?.selectedCities || []
  );
  const [selectedLocation, setSelectedLocation] = useState(null);
  const [selectedDates, setSelectedDates] = useState([]);
  const [cityDates, setCityDates] = useState({});
  const [showCityDateRows, setShowCityDateRows] = useState(false);
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

  const formatDateValue = (date) => {
    if (!date) return "";
    if (typeof date.format === "function") return date.format("DD/MM/YYYY");
    if (date instanceof Date) return moment(date).format("DD/MM/YYYY");
    if (typeof date === "string") return date;
    return moment(date).format("DD/MM/YYYY");
  };

  const handleLocationSelect = (location) => {
    console.log("Selected Location:", location);
    setSelectedLocation(location);
  };

  const handleDateChange = (dates) => {
    // Keep calendar/parent stable while user is still picking check-out
    if (!Array.isArray(dates) || dates.length !== 2) return;

    setSelectedDates(dates);
    setCityDates((prev) => {
      if (!Object.keys(prev).length) return prev;
      return {};
    });
  };

  const handleCityDateChange = (cityId, dates, cityIndex) => {
    // Ignore partial range while picking check-out
    if (!Array.isArray(dates) || dates.length !== 2) return;

    setCityDates((prev) => {
      const next = { ...prev, [cityId]: dates };
      selectedCities.forEach((city, idx) => {
        if (idx > cityIndex) {
          delete next[city.city_id];
        }
      });
      return next;
    });
  };

  const hasTotalDates =
    Array.isArray(selectedDates) && selectedDates.length === 2;

  const handleAddCities = () => {
    if (!selectedCities.length) {
      setSnackbarMessage("Please select at least one city.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return;
    }
    if (!hasTotalDates) {
      setSnackbarMessage("Please select total check-in and check-out dates.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return;
    }
    setShowCityDateRows(true);
    setSelectedLocation(selectedCities[0]?.country || selectedLocation);
  };

  const getCityDateConstraints = (cityIndex) => {
    const totalMin = hasTotalDates ? selectedDates[0] : undefined;
    const totalMax = hasTotalDates ? selectedDates[1] : undefined;

    if (cityIndex === 0) {
      return {
        minDate: totalMin,
        maxDate: totalMax,
        blockedRanges: [],
        disabled: !hasTotalDates,
      };
    }

    const prevCity = selectedCities[cityIndex - 1];
    const prevDates = prevCity ? cityDates[prevCity.city_id] : null;
    const prevFilled =
      Array.isArray(prevDates) && prevDates.length === 2;

    const blockedRanges = selectedCities
      .slice(0, cityIndex)
      .map((city) => cityDates[city.city_id])
      .filter((range) => Array.isArray(range) && range.length === 2);

    return {
      // Next city can start on previous city's check-out day
      minDate: prevFilled ? prevDates[1] : totalMin,
      maxDate: totalMax,
      blockedRanges,
      disabled: !hasTotalDates || !prevFilled,
    };
  };

  const allCityDatesFilled =
    showCityDateRows &&
    hasTotalDates &&
    selectedCities.length > 0 &&
    selectedCities.every(
      (city) =>
        Array.isArray(cityDates[city.city_id]) &&
        cityDates[city.city_id].length === 2
    );

  const handleGuestChange = (updatedGuestCounts) => {
    setGuestCounts(updatedGuestCounts);
  };

  // Clear previous booking/search state when search box mounts
  useEffect(() => {
    dispatch(setSelectedCity(null));
    dispatch(clearViewDetails());
    dispatch(clearUserInfo());
    dispatch(setHaveBooking(false));
    dispatch(clearSelectedDmc());
    dispatch(clearSelectedCities());
  }, [dispatch]);

  useEffect(() => {
    setCityDates((prev) => {
      const next = {};
      selectedCities.forEach((city) => {
        if (prev[city.city_id]) {
          next[city.city_id] = prev[city.city_id];
        }
      });
      return next;
    });
    if (!selectedCities.length) {
      setShowCityDateRows(false);
    }
  }, [selectedCities]);

  const validateForm = () => {
    if (!selectedCities.length) {
      setSnackbarMessage("Please select at least one city.");
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

    if (!allCityDatesFilled) {
      setSnackbarMessage("Please select check-in and check-out dates for each city.");
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

    // Multiple city names for DMC API and URL params
    const destinationName = selectedCities
      .map((city) => city.city)
      .filter(Boolean);

    // City + country pairs for search/tour state
    const destinationLocations = selectedCities.map((city) => ({
      city: city.city,
      country: city.country || "",
    }));

    const destinationCountries = [
      ...new Set(
        selectedCities.map((city) => city.country).filter(Boolean)
      ),
    ];

    // Check if selected cities share a bookable DMC on the same tour
    const dmcResult = await dispatch(fetchDMCsByCountry(destinationCountries));
    if (fetchDMCsByCountry.fulfilled.match(dmcResult)) {
      const payload = dmcResult.payload;
      // API may return [] or { data: [] }
      const dmcList = Array.isArray(payload)
        ? payload
        : Array.isArray(payload?.data)
          ? payload.data
          : [];

      if (dmcList.length === 0) {
        swal({
          title: "Unavailable",
          text: "Selected Cities are not available for booking on a same tour",
          icon: "warning",
          button: "OK",
        });
        
        return;
      }
    } else {
      swal({
        title: "Unavailable",
        text: "Selected Cities are not available for booking on a same tour",
        icon: "warning",
        button: "OK",
      });
      
      return;
    }

    // City-wise check-in / check-out as array of key-value objects
    const cityWiseDatesPayload = selectedCities.map((city) => {
      const range = cityDates[city.city_id] || [];
      return {
        city: city.city,
        checkIn: formatDateValue(range[0]),
        checkOut: formatDateValue(range[1]),
      };
    });
    dispatch(setCityWiseDates(cityWiseDatesPayload));
    
    // Step 2: Dispatch Redux actions to update the search state
    // Total trip check-in / check-out (unchanged)
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
    
    // Reset local step tracking for new search — replaces any previous tour session
    dispatch(resetSteps());
    clearBookingFlow(dispatch);

    // Re-apply city-wise dates after clearBookingFlow (it resets common booking state)
    dispatch(setCityWiseDates(cityWiseDatesPayload));

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

    // Update hotel search state so the hotel list has cities and total dates
    const ucheckInYmd = moment(formattedCheckIn, "DD/MM/YYYY").format("YYYY-MM-DD");
    const ucheckOutYmd = moment(formattedCheckOut, "DD/MM/YYYY").format("YYYY-MM-DD");
    dispatch(
      updateSearchState({
        location: destinationLocations,
        cityWiseDates: cityWiseDatesPayload,
        ucheckIn: ucheckInYmd,
        ucheckOut: ucheckOutYmd,
        guests: {
          adults: guestCounts.Adults,
          children: guestCounts.Children,
          infant: guestCounts.Infants,
        },
      })
    );

    // Provide basic tour details for hotel UI that previously relied on API response
    dispatch(
      settourdetails({
        destination: destinationLocations,
        cityWiseDates: cityWiseDatesPayload,
        adult: guestCounts.Adults,
        child: guestCounts.Children,
        infant: guestCounts.Infants,
        CheckInTime: formattedCheckIn,
        CheckOutTime: formattedCheckOut,
        tour_id: null,
      })
    );
    dispatch(setId(0));

    // Set selected cities into city slice for service APIs
    dispatch(
      setCity({
        cities: destinationName,
        country: selectedCities[0]?.country || "",
      })
    );
    dispatch(setSelectedCity(null));

    // Step 4: Navigate to the hotel search results page without creating a tour
    const searchParams = new URLSearchParams({
      location: destinationName.join(","),
      dates: [formattedCheckIn, formattedCheckOut].join(","),
      guests: JSON.stringify(guestCounts),
    });

    // Use placeholder id since router expects an :id param; actual tour will be created on booking/enquiry
    navigate(`/dashboard/db-dashboard/view-hotel-search/0?${searchParams}`);
  };

  return (
    <div className="position-relative mt-30 md:mt-20 js-tabs-content">
      <div
        className="mainSearch -w-900 -col-city-date-guest bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-100"
        style={{ position: "relative", zIndex: 30 }}
      >
        <div className="button-grid items-center">
          <LocationSearch onLocationSelect={handleLocationSelect} />

          <div className="searchMenu-date px-30 lg:py-20 lg:px-0 js-form-dd js-calendar">
            <div>
              <h4 className="text-15 fw-500 ls-2 lh-16">
                Check in - Check out
              </h4>
              <DateSearch
                onDateChange={handleDateChange}
                value={selectedDates.length === 2 ? selectedDates : undefined}
                notifyOnMount
              />
            </div>
          </div>

          <GuestSearch
            onGuestChange={handleGuestChange}
            guestCounts={guestCounts}
          />

          <div className="button-item">
            <button
              type="button"
              className="mainSearch__submit button -dark-1 h-60 px-35 col-12 rounded-100 bg-blue-1 text-white"
              onClick={handleAddCities}
            >
              <i className="icon-plus text-20 mr-10" />
              Add
            </button>
          </div>
        </div>
      </div>

      {showCityDateRows && selectedCities.length > 0 && (
        <div
          className="mainSearch -w-900 bg-white px-20 py-20 mt-15 rounded-24"
          style={{ position: "relative", zIndex: 30 }}
        >
          <div className="text-14 text-light-1 mb-15">
            Set city-wise dates within your total trip dates
            {hasTotalDates ? (
              <span className="fw-500 text-dark-1">
                {" "}
                (
                {typeof selectedDates[0]?.format === "function"
                  ? selectedDates[0].format("MMM DD")
                  : ""}
                {" - "}
                {typeof selectedDates[1]?.format === "function"
                  ? selectedDates[1].format("MMM DD")
                  : ""}
                )
              </span>
            ) : null}
          </div>
          {selectedCities.map((city, index) => {
            const isLastRow = index === selectedCities.length - 1;
            const constraints = getCityDateConstraints(index);
            return (
              <div
                key={city.city_id}
                className={`d-flex items-center y-gap-10 flex-wrap ${
                  index > 0 ? "border-top-light pt-15 mt-15" : ""
                }`}
              >
                <div className="col-md-4 col-12 px-10">
                  <h4 className="text-15 fw-500 ls-2 lh-16">City</h4>
                  <div className="text-15 text-dark-1 fw-500">{city.city}</div>
                  <div className="text-13 text-light-1">{city.country}</div>
                </div>

                <div className="col-md-5 col-12 px-10 searchMenu-date js-form-dd js-calendar">
                  <h4 className="text-15 fw-500 ls-2 lh-16">
                    Check in - Check out
                  </h4>
                  <DateSearch
                    key={city.city_id}
                    onDateChange={(dates) =>
                      handleCityDateChange(city.city_id, dates, index)
                    }
                    minDate={constraints.minDate}
                    maxDate={constraints.maxDate}
                    blockedRanges={constraints.blockedRanges}
                    value={
                      cityDates[city.city_id]?.length === 2
                        ? cityDates[city.city_id]
                        : undefined
                    }
                    disabled={constraints.disabled}
                    notifyOnMount={false}
                    calendarPosition="bottom-left"
                  />
                  {constraints.disabled && index > 0 && (
                    <div className="text-12 text-light-1 mt-5">
                      Select previous city dates first
                    </div>
                  )}
                </div>

                {isLastRow && (
                  <div className="col-md-3 col-12 px-10 button-item">
                    <button
                      type="button"
                      className={`mainSearch__submit button -dark-1 h-60 px-35 col-12 rounded-100 ${
                        allCityDatesFilled
                          ? "bg-blue-1 text-white"
                          : "bg-light-2 text-light-1"
                      }`}
                      onClick={handleSearch}
                      disabled={!allCityDatesFilled}
                    >
                      <i className="icon-search text-20 mr-10" />
                      Search
                    </button>
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}
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
