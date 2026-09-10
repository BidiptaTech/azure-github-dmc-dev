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
import { clearSelectedCities, addSelectedCity } from "@/slice/common/citiesSlice";
import { clearBookingFlow } from "@/utils/clearBookingFlow";
import { clearViewDetails } from "@/slice/common/ViewDetails";
import { clearCart } from "@/slice/cart/carSlice";
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
  const [tripType, setTripType] = useState("single"); // single | multi

  const formatGoibiboDate = (date) => {
    if (!date) return { primary: "Select date", secondary: "Tap to choose" };
    try {
      const m =
        typeof date.format === "function"
          ? moment(date.format("YYYY-MM-DD"))
          : moment(date);
      return {
        primary: m.format("DD MMM'YY"),
        secondary: m.format("dddd"),
      };
    } catch {
      return { primary: "Select date", secondary: "Tap to choose" };
    }
  };

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

  const checkInDisplay = hasTotalDates
    ? formatGoibiboDate(selectedDates[0])
    : { primary: "Select date", secondary: "Check-in" };
  const checkOutDisplay = hasTotalDates
    ? formatGoibiboDate(selectedDates[1])
    : { primary: "Select date", secondary: "Check-out" };

  useEffect(() => {
    if (tripType === "single" && selectedCities.length > 1) {
      const keep = selectedCities[0];
      dispatch(clearSelectedCities());
      if (keep) dispatch(addSelectedCity(keep));
    }
  }, [tripType, selectedCities, dispatch]);

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
    hasTotalDates &&
    selectedCities.length > 0 &&
    (selectedCities.length === 1 ||
      selectedCities.every(
        (city) =>
          Array.isArray(cityDates[city.city_id]) &&
          cityDates[city.city_id].length === 2
      ));

  const showItineraryPlanner =
    (tripType === "multi" || selectedCities.length > 1) &&
    selectedCities.length > 1 &&
    hasTotalDates;

  useEffect(() => {
    if (showItineraryPlanner) {
      setShowCityDateRows(true);
    }
  }, [showItineraryPlanner]);

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
    // Single city: use total trip dates automatically — no city-wise panel
    if (selectedCities.length === 1 && hasTotalDates) {
      const onlyCityId = selectedCities[0].city_id;
      setCityDates({ [onlyCityId]: selectedDates });
      setShowCityDateRows(false);
      return;
    }

    setCityDates((prev) => {
      const next = {};
      selectedCities.forEach((city) => {
        if (prev[city.city_id]) {
          next[city.city_id] = prev[city.city_id];
        }
      });
      return next;
    });
    if (selectedCities.length <= 1) {
      setShowCityDateRows(false);
    }
  }, [selectedCities, selectedDates, hasTotalDates]);

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

    if (selectedCities.length > 1 && !allCityDatesFilled) {
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
      const range =
        selectedCities.length === 1
          ? selectedDates
          : cityDates[city.city_id] || [];
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
    // New search starts a fresh tour — only one trip allowed in cart
    dispatch(clearCart());

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

  const canSearchSingle = selectedCities.length === 1 && hasTotalDates;
  const canSearchMulti = showItineraryPlanner && allCityDatesFilled;
  const canSearch =
    selectedCities.length <= 1 || tripType === "single"
      ? canSearchSingle
      : canSearchMulti;

  const formatRangeLabel = (range) => {
    if (!Array.isArray(range) || range.length !== 2) return "Select dates";
    const a =
      typeof range[0]?.format === "function"
        ? range[0].format("DD MMM")
        : formatDateValue(range[0]);
    const b =
      typeof range[1]?.format === "function"
        ? range[1].format("DD MMM")
        : formatDateValue(range[1]);
    return `${a} → ${b}`;
  };

  return (
    <div className="position-relative mt-30 md:mt-20 js-tabs-content hero-search-reserve">
      <div
        className="bg-white"
        style={{
          position: "relative",
          zIndex: 50,
          borderRadius: 16,
          boxShadow: "0 12px 40px rgba(15, 23, 42, 0.16)",
          border: "1px solid rgba(15, 23, 42, 0.06)",
          padding: "18px 18px 14px",
          maxWidth: 1100,
          margin: "0 auto",
        }}
      >
        <div
          className="d-flex items-center justify-between flex-wrap"
          style={{ gap: 12, marginBottom: 14 }}
        >
          <div className="d-flex items-center flex-wrap" style={{ gap: 18 }}>
            {[
              { id: "single", label: "Single City" },
              { id: "multi", label: "Multi City" },
            ].map((opt) => (
              <label
                key={opt.id}
                className="d-flex items-center cursor-pointer"
                style={{ gap: 8, fontSize: 14, fontWeight: 600, color: "#0f172a" }}
              >
                <input
                  type="radio"
                  name="tripType"
                  checked={tripType === opt.id}
                  onChange={() => {
                    setTripType(opt.id);
                    if (opt.id === "single") {
                      setShowCityDateRows(false);
                      if (selectedCities.length > 1) {
                        const keep = selectedCities[0];
                        dispatch(clearSelectedCities());
                        if (keep) dispatch(addSelectedCity(keep));
                      }
                    }
                  }}
                  style={{ accentColor: "#3554D1", width: 16, height: 16 }}
                />
                {opt.label}
              </label>
            ))}
          </div>
          <div style={{ fontSize: 13, color: "#64748b", fontWeight: 500 }}>
            Book hotels, transfers & experiences for your clients
          </div>
        </div>

        <div
          className="d-flex flex-wrap"
          style={{
            border: "1px solid #e2e8f0",
            borderRadius: 12,
            overflow: "visible",
            background: "#fff",
          }}
        >
          <div
            style={{
              flex: "1.4 1 220px",
              minWidth: 200,
              borderRight: "1px solid #e2e8f0",
            }}
          >
            <LocationSearch
              onLocationSelect={handleLocationSelect}
              multiSelect={tripType === "multi"}
            />
          </div>

          <div
            className="searchMenu-date js-form-dd js-calendar position-relative cursor-pointer d-flex"
            style={{
              flex: "2 1 320px",
              minWidth: 280,
              borderRight: "1px solid #e2e8f0",
            }}
            onClick={(e) => {
              const input = e.currentTarget.querySelector("input");
              if (input) input.focus();
            }}
          >
            <div style={{ flex: 1, padding: "16px 18px" }}>
              <div
                style={{
                  fontSize: "11px",
                  fontWeight: 600,
                  letterSpacing: "0.04em",
                  textTransform: "uppercase",
                  color: "#64748b",
                  marginBottom: 4,
                }}
              >
                Check-in
              </div>
              <div
                style={{
                  fontSize: 22,
                  fontWeight: 700,
                  color: "#0f172a",
                  lineHeight: 1.2,
                }}
              >
                {checkInDisplay.primary}
              </div>
              <div style={{ fontSize: 12, color: "#64748b", marginTop: 2 }}>
                {checkInDisplay.secondary}
              </div>
            </div>
            <div
              style={{
                width: 1,
                background: "#e2e8f0",
                alignSelf: "stretch",
              }}
            />
            <div style={{ flex: 1, padding: "16px 18px" }}>
              <div
                style={{
                  fontSize: "11px",
                  fontWeight: 600,
                  letterSpacing: "0.04em",
                  textTransform: "uppercase",
                  color: "#64748b",
                  marginBottom: 4,
                }}
              >
                Check-out
              </div>
              <div
                style={{
                  fontSize: 22,
                  fontWeight: 700,
                  color: hasTotalDates ? "#0f172a" : "#94a3b8",
                  lineHeight: 1.2,
                }}
              >
                {checkOutDisplay.primary}
              </div>
              <div style={{ fontSize: 12, color: "#64748b", marginTop: 2 }}>
                {checkOutDisplay.secondary}
              </div>
            </div>
            <div style={{ position: "absolute", inset: 0, opacity: 0 }}>
              <DateSearch
                onDateChange={handleDateChange}
                value={selectedDates.length === 2 ? selectedDates : undefined}
                notifyOnMount
              />
            </div>
          </div>

          <div style={{ flex: "1 1 180px", minWidth: 160 }}>
            <GuestSearch
              onGuestChange={handleGuestChange}
              guestCounts={guestCounts}
            />
          </div>
        </div>

        {showItineraryPlanner && (
          <div
            style={{
              marginTop: 16,
              border: "1px solid #e2e8f0",
              borderRadius: 12,
              background: "#f8fafc",
              overflow: "hidden",
            }}
          >
            <div
              className="d-flex items-center justify-between flex-wrap"
              style={{
                gap: 8,
                padding: "12px 16px",
                borderBottom: "1px solid #e2e8f0",
                background: "#fff",
              }}
            >
              <div>
                <div style={{ fontSize: 14, fontWeight: 700, color: "#0f172a" }}>
                  City itinerary
                </div>
                <div style={{ fontSize: 12, color: "#64748b", marginTop: 2 }}>
                  Split the tour period across cities in order
                  {hasTotalDates && (
                    <span style={{ fontWeight: 600, color: "#3554D1" }}>
                      {" "}
                      (
                      {typeof selectedDates[0]?.format === "function"
                        ? selectedDates[0].format("DD MMM")
                        : ""}
                      {" – "}
                      {typeof selectedDates[1]?.format === "function"
                        ? selectedDates[1].format("DD MMM")
                        : ""}
                      )
                    </span>
                  )}
                </div>
              </div>
              <div
                style={{
                  fontSize: 12,
                  fontWeight: 600,
                  color: allCityDatesFilled ? "#15803d" : "#b45309",
                  background: allCityDatesFilled
                    ? "rgba(22, 163, 74, 0.1)"
                    : "rgba(245, 158, 11, 0.12)",
                  borderRadius: 999,
                  padding: "4px 10px",
                }}
              >
                {allCityDatesFilled
                  ? "Ready to search"
                  : `${selectedCities.filter((c) => cityDates[c.city_id]?.length === 2).length}/${selectedCities.length} cities dated`}
              </div>
            </div>

            <div style={{ padding: "8px 0" }}>
              {selectedCities.map((city, index) => {
                const constraints = getCityDateConstraints(index);
                const filled =
                  Array.isArray(cityDates[city.city_id]) &&
                  cityDates[city.city_id].length === 2;
                return (
                  <div
                    key={city.city_id}
                    className="d-flex items-stretch flex-wrap"
                    style={{
                      gap: 12,
                      padding: "14px 16px",
                      borderTop: index === 0 ? "none" : "1px solid #e2e8f0",
                    }}
                  >
                    <div
                      className="d-flex items-start"
                      style={{ gap: 12, flex: "1 1 180px", minWidth: 160 }}
                    >
                      <div
                        style={{
                          width: 28,
                          height: 28,
                          borderRadius: "50%",
                          background: filled ? "#3554D1" : "#cbd5e1",
                          color: "#fff",
                          fontSize: 12,
                          fontWeight: 700,
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          flexShrink: 0,
                          marginTop: 2,
                        }}
                      >
                        {index + 1}
                      </div>
                      <div>
                        <div style={{ fontSize: 15, fontWeight: 700, color: "#0f172a" }}>
                          {city.city}
                        </div>
                        <div style={{ fontSize: 12, color: "#64748b" }}>
                          {city.country}
                        </div>
                        {index < selectedCities.length - 1 && (
                          <div style={{ fontSize: 11, color: "#94a3b8", marginTop: 6 }}>
                            then → {selectedCities[index + 1]?.city}
                          </div>
                        )}
                      </div>
                    </div>

                    <div
                      className="searchMenu-date js-form-dd js-calendar"
                      style={{ flex: "1.4 1 240px", minWidth: 220 }}
                    >
                      <div
                        style={{
                          fontSize: 11,
                          fontWeight: 600,
                          letterSpacing: "0.04em",
                          textTransform: "uppercase",
                          color: "#64748b",
                          marginBottom: 6,
                        }}
                      >
                        Stay dates
                      </div>
                      <div
                        style={{
                          background: "#fff",
                          border: "1px solid #e2e8f0",
                          borderRadius: 10,
                          padding: "8px 12px",
                          opacity: constraints.disabled ? 0.55 : 1,
                        }}
                      >
                        <div
                          style={{
                            fontSize: 13,
                            fontWeight: 600,
                            color: filled ? "#0f172a" : "#94a3b8",
                            marginBottom: 4,
                          }}
                        >
                          {formatRangeLabel(cityDates[city.city_id])}
                        </div>
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
                      </div>
                      {constraints.disabled && index > 0 && (
                        <div style={{ fontSize: 11, color: "#94a3b8", marginTop: 6 }}>
                          Complete previous city dates first
                        </div>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {(tripType === "multi" || selectedCities.length > 1) &&
          selectedCities.length > 1 &&
          !hasTotalDates && (
            <div
              style={{
                marginTop: 14,
                padding: "12px 14px",
                borderRadius: 10,
                background: "rgba(53, 84, 209, 0.06)",
                color: "#3554D1",
                fontSize: 13,
                fontWeight: 500,
              }}
            >
              Select total trip check-in and check-out to allocate dates per city.
            </div>
          )}

        <div className="d-flex justify-center" style={{ marginTop: 12 }}>
          <button
            type="button"
            onClick={handleSearch}
            disabled={!canSearch}
            style={{
              minWidth: 220,
              height: 52,
              borderRadius: 28,
              border: "none",
              fontSize: 18,
              fontWeight: 800,
              letterSpacing: "0.04em",
              color: "#fff",
              background: !canSearch ? "#94a3b8" : "#3554D1",
              cursor: !canSearch ? "not-allowed" : "pointer",
              boxShadow: !canSearch
                ? "none"
                : "0 10px 24px rgba(53, 84, 209, 0.35)",
            }}
          >
            SEARCH
          </button>
        </div>
      </div>

      <Snackbar
        open={openSnackbar}
        autoHideDuration={6000}
        onClose={handleCloseSnackbar}
        anchorOrigin={{ vertical: "top", horizontal: "center" }}
        sx={{ top: { xs: 16, sm: 24 } }}
      >
        <Alert
          onClose={handleCloseSnackbar}
          severity={snackbarSeverity}
          variant="filled"
          sx={{ width: "100%", boxShadow: "0 8px 24px rgba(15, 23, 42, 0.18)" }}
        >
          {snackbarMessage}
        </Alert>
      </Snackbar>
    </div>
  );
};

export default MainFilterSearchBox;
