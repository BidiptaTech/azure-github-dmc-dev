import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import DateSearch from "../DateSearch";
import GuestSearch from "./GuestSearch";
import LocationSearch from "./LocationSearch";
import MultiCityLegs, { defaultMultiLegs } from "./MultiCityLegs";
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
import { appPath } from "@/utils/appPaths";
import StayDatesPerCity from "@/components/booking/StayDatesPerCity";
import {
  syncStaysFromCities,
  toCityWiseDatesPayload,
  staysAllDated,
} from "@/utils/cityWiseDates";
import swal from "sweetalert";

const toDdMmYyyy = (date) => {
  if (!date) return "";
  try {
    if (typeof date?.format === "function") {
      const iso = date.format("YYYY-MM-DD");
      if (iso && moment(iso, "YYYY-MM-DD", true).isValid()) {
        return moment(iso, "YYYY-MM-DD").format("DD/MM/YYYY");
      }
      const direct = date.format("DD/MM/YYYY");
      if (direct) return direct;
    }
    if (date instanceof Date) return moment(date).format("DD/MM/YYYY");
    if (typeof date === "string") {
      const parsed = moment(date, ["DD/MM/YYYY", "YYYY-MM-DD", moment.ISO_8601], true);
      return parsed.isValid() ? parsed.format("DD/MM/YYYY") : date;
    }
    return moment(date).format("DD/MM/YYYY");
  } catch {
    return moment(date).format("DD/MM/YYYY");
  }
};

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
  console.log(selectedCities, "selectedCities");
  const [selectedLocation, setSelectedLocation] = useState(null);
  const [selectedDates, setSelectedDates] = useState([]);
  const [cityDates, setCityDates] = useState({});
  const [cityStays, setCityStays] = useState([]);
  const [guestCounts, setGuestCounts] = useState({
    Adults: 1,
    Children: 0,
    Infants: 0,
    maleCount: 1,
    femaleCount: 0,
    genders: [""], // Store gender selections for adults
    ages: [], // Store age selections for children
  });
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const [snackbarSeverity, setSnackbarSeverity] = useState("error");
  const [guestModalSignal, setGuestModalSignal] = useState(0);
  const [tripType, setTripType] = useState("single"); // single | multi
  const [multiLegs, setMultiLegs] = useState(() => defaultMultiLegs());

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

  const formatDateValue = (date) => toDdMmYyyy(date);

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

  const handleCityStaysChange = (nextStays) => {
    setCityStays(nextStays);
    const nextCityDates = {};
    (nextStays || []).forEach((stay) => {
      if (!stay.city_id || !stay.checkIn || !stay.checkOut) return;
      // Last dated occurrence wins for legacy city_id map; legs sync by index
      nextCityDates[stay.city_id] = [
        moment(stay.checkIn, "DD/MM/YYYY").toDate(),
        moment(stay.checkOut, "DD/MM/YYYY").toDate(),
      ];
    });
    setCityDates(nextCityDates);

    // Keep MultiCityLegs in sync by itinerary order (supports Batam → Singapore revisit)
    if (tripType === "multi") {
      setMultiLegs((prev) => {
        let stayIdx = 0;
        return (prev || []).map((leg) => {
          if (!leg.city?.city_id) return leg;
          const match = nextStays[stayIdx];
          stayIdx += 1;
          if (!match?.checkIn || !match?.checkOut) return leg;
          if (
            match.city_id &&
            String(match.city_id) !== String(leg.city.city_id)
          ) {
            return leg;
          }
          return {
            ...leg,
            dates: [
              moment(match.checkIn, "DD/MM/YYYY").toDate(),
              moment(match.checkOut, "DD/MM/YYYY").toDate(),
            ],
          };
        });
      });
    }
  };

  // Multi-city legs → stay rows (preserves Return stays / revisits)
  useEffect(() => {
    if (tripType !== "multi") return;
    const cities = (multiLegs || []).map((leg) => leg.city).filter(Boolean);
    if (!cities.length) {
      setCityStays([]);
      return;
    }
    setCityStays((prev) => {
      const synced = syncStaysFromCities(cities, prev);
      const filledLegs = (multiLegs || []).filter((leg) => leg.city?.city_id);
      return synced.map((stay, index) => {
        const leg = filledLegs[index];
        if (!leg || !Array.isArray(leg.dates) || leg.dates.length !== 2) {
          return stay;
        }
        return {
          ...stay,
          checkIn: formatDateValue(leg.dates[0]),
          checkOut: formatDateValue(leg.dates[1]),
        };
      });
    });
  }, [tripType, multiLegs]);

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

    setSelectedLocation(selectedCities[0]?.country || selectedLocation);
  };

  const allCityDatesFilled =
    hasTotalDates &&
    selectedCities.length > 0 &&
    (selectedCities.length === 1 ||
      (cityStays.length > 0
        ? staysAllDated(cityStays)
        : selectedCities.every(
            (city) =>
              Array.isArray(cityDates[city.city_id]) &&
              cityDates[city.city_id].length === 2
          )));

  const showItineraryPlanner =
    (tripType === "multi" || selectedCities.length > 1) &&
    selectedCities.length > 1 &&
    hasTotalDates;

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
      setCityStays(
        syncStaysFromCities(selectedCities, []).map((stay) => ({
          ...stay,
          checkIn: formatDateValue(selectedDates[0]),
          checkOut: formatDateValue(selectedDates[1]),
        }))
      );
      return;
    }

    setCityStays((prev) => syncStaysFromCities(selectedCities, prev));
    setCityDates((prev) => {
      const next = {};
      selectedCities.forEach((city) => {
        if (prev[city.city_id]) {
          next[city.city_id] = prev[city.city_id];
        }
      });
      return next;
    });
  }, [selectedCities, selectedDates, hasTotalDates]);

  const validateForm = (opts = {}) => {
    const cities = opts.cities || selectedCities;
    const dates = opts.selectedDates || selectedDates;
    const perCityDates = opts.cityDates || cityDates;

    if (!cities.length) {
      setSnackbarMessage("Please select at least one city.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (!dates || dates.length === 0) {
      setSnackbarMessage("Please select check-in and check-out dates.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    // Validate that both check-in and check-out dates are selected
    if (dates.length !== 2) {
      setSnackbarMessage("Please select both check-in and check-out dates.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    const staysReady =
      cities.length <= 1 ||
      (cityStays.length > 0
        ? staysAllDated(cityStays)
        : cities.every(
            (city) =>
              Array.isArray(perCityDates[city.city_id]) &&
              perCityDates[city.city_id].length === 2
          ));

    if (cities.length > 1 && !staysReady) {
      setSnackbarMessage(
        "Please set Stay from / until for every city stay (including Return stays)."
      );
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
      setGuestModalSignal((n) => n + 1);
      return false;
    }
    
    if (maleCount + femaleCount !== guestCounts.Adults) {
      setSnackbarMessage("Male and female count must equal the total adult count.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      setGuestModalSignal((n) => n + 1);
      return false;
    }

    if (
      guestCounts.Children > 0 &&
      !(guestCounts.ages || []).every((age) => String(age ?? "").trim() !== "")
    ) {
      setSnackbarMessage("Please provide an age for all children.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      setGuestModalSignal((n) => n + 1);
      return false;
    }

    return true;
  };

  const handleCloseSnackbar = () => {
    setOpenSnackbar(false);
  };

  const handleSearch = async (e, overrides = null) => {
    e?.preventDefault?.();

    const citiesForSearch = overrides?.cities || selectedCities;
    const datesForSearch = overrides?.selectedDates || selectedDates;
    const cityDatesForSearch = overrides?.cityDates || cityDates;

    if (
      !validateForm({
        cities: citiesForSearch,
        selectedDates: datesForSearch,
        cityDates: cityDatesForSearch,
      })
    ) {
      return;
    }

    // Format Dates - Handle DateObject / moment / Date safely
    const formattedCheckIn = toDdMmYyyy(datesForSearch?.[0]);
    const formattedCheckOut = toDdMmYyyy(datesForSearch?.[1]);
    if (!formattedCheckIn || !formattedCheckOut) {
      setSnackbarMessage("Please select both check-in and check-out dates.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return;
    }

    try {

    // Multiple city names for DMC API and URL params
    const destinationName = citiesForSearch
      .map((city) => city.city)
      .filter(Boolean);
 console.log(destinationName, "destinationName");
    // City + country pairs for search/tour state
    const destinationLocations = citiesForSearch.map((city) => ({
      city: city.city,
      country: city.country || "",
    }));
    console.log(destinationLocations, "destinationLocations");
    const destinationCountries = [
      ...new Set(
        citiesForSearch.map((city) => city.country).filter(Boolean)
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

    // City-wise check-in / check-out (supports Return stays)
    const cityWiseDatesPayload =
      citiesForSearch.length === 1
        ? [
            {
              city: citiesForSearch[0].city,
              checkIn: formatDateValue(datesForSearch[0]),
              checkOut: formatDateValue(datesForSearch[1]),
            },
          ]
        : cityStays.length > 0
          ? toCityWiseDatesPayload(cityStays)
          : citiesForSearch.map((city) => {
              const range = cityDatesForSearch[city.city_id] || [];
              return {
                city: city.city,
                checkIn: formatDateValue(range[0]),
                checkOut: formatDateValue(range[1]),
              };
            });
    dispatch(setCityWiseDates(cityWiseDatesPayload));

    // Keep Redux selectedCities aligned with the search payload
    dispatch(clearSelectedCities());
    citiesForSearch.forEach((city) => dispatch(addSelectedCity(city)));
    
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
        maleCount: guestCounts.maleCount,
        femaleCount: guestCounts.femaleCount,
        childrenAges: guestCounts.ages,
        CheckInTime: formattedCheckIn,
        CheckOutTime: formattedCheckOut,
        tour_id: null,
        city_type: tripType, // "single" | "multi"
      })
    );
    dispatch(setId(0));

    // Set selected cities into city slice for service APIs ("City, Country" per city)
    dispatch(
      setCity({
        cities: destinationLocations,
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
    navigate(`${appPath("view-hotel-search")}/0?${searchParams}`);
    } catch (err) {
      console.error("Search failed:", err);
      setSnackbarMessage("Something went wrong starting the search. Please try again.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
    }
  };

  const canSearchSingle = selectedCities.length === 1 && hasTotalDates;
  const canSearchMulti = showItineraryPlanner && allCityDatesFilled;
  const canSearch =
    selectedCities.length <= 1 || tripType === "single"
      ? canSearchSingle
      : canSearchMulti;

  const syncMultiLegsToSearchState = () => {
    const cities = multiLegs.map((leg) => leg.city).filter(Boolean);
    const nextCityDates = {};
    multiLegs.forEach((leg) => {
      if (leg.city?.city_id && Array.isArray(leg.dates) && leg.dates.length === 2) {
        nextCityDates[leg.city.city_id] = leg.dates;
      }
    });

    const firstDates = multiLegs[0]?.dates;
    const lastDates = multiLegs[multiLegs.length - 1]?.dates;
    if (
      Array.isArray(firstDates) &&
      firstDates.length === 2 &&
      Array.isArray(lastDates) &&
      lastDates.length === 2
    ) {
      setSelectedDates([firstDates[0], lastDates[1]]);
    }

    setCityDates(nextCityDates);
    setCityStays(
      syncStaysFromCities(cities, []).map((stay) => {
        const range = stay.city_id ? nextCityDates[stay.city_id] : null;
        if (!Array.isArray(range) || range.length !== 2) return stay;
        return {
          ...stay,
          checkIn: formatDateValue(range[0]),
          checkOut: formatDateValue(range[1]),
        };
      })
    );
    dispatch(clearSelectedCities());
    cities.forEach((city) => dispatch(addSelectedCity(city)));
    if (cities[0]?.country) {
      setSelectedLocation(cities[0].country);
    }
    return { cities, nextCityDates };
  };

  const setTripTypeMode = (id) => {
    setTripType(id);
    if (id === "single") {
      if (selectedCities.length > 1) {
        const keep = selectedCities[0];
        dispatch(clearSelectedCities());
        if (keep) dispatch(addSelectedCity(keep));
      }
      return;
    }

    // Multi-city: open two stacked search rows (demo behaviour)
    setMultiLegs((prev) => {
      const base =
        Array.isArray(prev) && prev.length >= 2
          ? prev
          : defaultMultiLegs(selectedDates);
      if (selectedCities[0] && !base[0].city) {
        return base.map((leg, idx) =>
          idx === 0 ? { ...leg, city: selectedCities[0] } : leg
        );
      }
      return base;
    });
  };

  const multiLegsReady =
    tripType === "multi" &&
    multiLegs.length >= 2 &&
    multiLegs.every(
      (leg) =>
        leg.city?.city_id &&
        Array.isArray(leg.dates) &&
        leg.dates.length === 2
    ) &&
    guestCounts.Adults > 0;
  const canSearchMultiLegs =
    multiLegsReady &&
    (cityStays.length === 0 || staysAllDated(cityStays));

  const handleMultiSearch = async (e) => {
    e?.preventDefault?.();
    if (!canSearchMultiLegs) {
      setSnackbarMessage(
        cityStays.length > 0 && !staysAllDated(cityStays)
          ? "Please set Stay from / until for every city stay (including Return stays)."
          : "Please complete city and dates for each stop."
      );
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return;
    }

    const { cities, nextCityDates } = syncMultiLegsToSearchState();
    const tripDates = [
      multiLegs[0].dates[0],
      multiLegs[multiLegs.length - 1].dates[1],
    ];

    await handleSearch(
      { preventDefault() {} },
      {
        cities,
        selectedDates: tripDates,
        cityDates: nextCityDates,
      }
    );
  };

  return (
    <div
      className="position-relative js-tabs-content hero-search-reserve"
      style={{ paddingBottom: 0 }}
    >
      <div
        className="hero-trip-seg"
        role="group"
        aria-label="Trip type"
        style={{
          display: "flex",
          gap: 8,
          marginBottom: 10,
          position: "relative",
          zIndex: 2,
        }}
      >
        {[
          { id: "single", label: "Single city" },
          { id: "multi", label: "Multi-city" },
        ].map((opt) => (
          <button
            key={opt.id}
            type="button"
            className={`hero-trip-seg__btn${tripType === opt.id ? " is-active" : ""}`}
            onClick={() => setTripTypeMode(opt.id)}
            style={{
              border:
                tripType === opt.id
                  ? "1px solid transparent"
                  : "1px solid rgba(229, 234, 243, 0.45)",
              background: tripType === opt.id ? "#e9efff" : "#fff",
              color: tripType === opt.id ? "#234adc" : "#192944",
              borderRadius: 9,
              padding: "10px 16px",
              fontSize: 13,
              fontWeight: 600,
              cursor: "pointer",
            }}
          >
            {opt.label}
          </button>
        ))}
      </div>

      {tripType === "multi" ? (
        <div>
          <MultiCityLegs
            legs={multiLegs}
            onLegsChange={setMultiLegs}
            guestCounts={guestCounts}
            onGuestChange={handleGuestChange}
            onSearch={handleMultiSearch}
            canSearch={canSearchMultiLegs}
            guestOpenSignal={guestModalSignal}
          />
          {multiLegs.filter((leg) => leg.city?.city_id).length >= 2 && (
            <div
              style={{
                marginTop: 12,
                border: "1px solid #e2e8f0",
                borderRadius: 12,
                background: "#fff",
                overflow: "hidden",
                padding: 12,
              }}
            >
              <StayDatesPerCity
                stays={
                  cityStays.length
                    ? cityStays
                    : syncStaysFromCities(
                        multiLegs.map((leg) => leg.city).filter(Boolean),
                        []
                      )
                }
                onChange={handleCityStaysChange}
              />
            </div>
          )}
        </div>
      ) : (
      <div
        className="bg-white hero-search-bar"
        style={{
          position: "relative",
          zIndex: 50,
          borderRadius: 12,
          boxShadow: "none",
          border: "0",
          padding: "8px",
          maxWidth: "none",
          width: "100%",
          margin: "0",
          display: "grid",
          gridTemplateColumns: "1.4fr 1fr 1fr 0.8fr auto",
          alignItems: "stretch",
          gap: 5,
        }}
      >
        <div
          style={{
            minWidth: 0,
            borderRight: "1px solid #e2e8f0",
          }}
        >
          <LocationSearch
            onLocationSelect={handleLocationSelect}
            multiSelect={tripType === "multi"}
          />
        </div>

        <div
          className="searchMenu-date js-form-dd js-calendar position-relative cursor-pointer"
          style={{
            gridColumn: "span 2",
            minWidth: 0,
            display: "grid",
            gridTemplateColumns: "1fr 1fr",
            gap: 5,
            borderRight: "1px solid #e2e8f0",
          }}
          onClick={(e) => {
            const input = e.currentTarget.querySelector("input");
            if (input) input.focus();
          }}
        >
          <div style={{ padding: "8px 14px", borderRight: "1px solid #e2e8f0" }}>
            <div
              style={{
                fontSize: "11px",
                fontWeight: 600,
                letterSpacing: "0.04em",
                textTransform: "uppercase",
                color: "#64748b",
                marginBottom: 2,
              }}
            >
              Check-in
            </div>
            <div
              style={{
                fontSize: 18,
                fontWeight: 700,
                color: "#0f172a",
                lineHeight: 1.2,
              }}
            >
              {checkInDisplay.primary}
            </div>
            <div style={{ fontSize: 11, color: "#64748b", marginTop: 1 }}>
              {checkInDisplay.secondary}
            </div>
          </div>
          <div style={{ padding: "8px 14px" }}>
            <div
              style={{
                fontSize: "11px",
                fontWeight: 600,
                letterSpacing: "0.04em",
                textTransform: "uppercase",
                color: "#64748b",
                marginBottom: 2,
              }}
            >
              Check-out
            </div>
            <div
              style={{
                fontSize: 18,
                fontWeight: 700,
                color: hasTotalDates ? "#0f172a" : "#94a3b8",
                lineHeight: 1.2,
              }}
            >
              {checkOutDisplay.primary}
            </div>
            <div style={{ fontSize: 11, color: "#64748b", marginTop: 1 }}>
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

        <div style={{ minWidth: 0, borderRight: "1px solid #e2e8f0" }}>
          <GuestSearch
            onGuestChange={handleGuestChange}
            guestCounts={guestCounts}
            openSignal={guestModalSignal}
          />
        </div>

        <div
          className="hero-search-bar__cta"
          style={{
            display: "flex",
            alignItems: "center",
            padding: "4px",
          }}
        >
          <button
            type="button"
            onClick={handleSearch}
            disabled={!canSearch}
            style={{
              minWidth: 170,
              height: "100%",
              minHeight: 52,
              borderRadius: 9,
              border: "none",
              fontSize: 13,
              fontWeight: 700,
              letterSpacing: "0",
              color: "#fff",
              background: !canSearch ? "#94a3b8" : "#234adc",
              cursor: !canSearch ? "not-allowed" : "pointer",
              boxShadow: "none",
              padding: "0 18px",
              whiteSpace: "nowrap",
            }}
          >
            Find stays &amp; services →
          </button>
        </div>

        {showItineraryPlanner && (
          <div
            style={{
              gridColumn: "1 / -1",
              marginTop: 8,
              border: "1px solid #e2e8f0",
              borderRadius: 12,
              background: "#f8fafc",
              overflow: "hidden",
            }}
          >
            <div style={{ padding: 12 }}>
              <StayDatesPerCity
                stays={
                  cityStays.length
                    ? cityStays
                    : syncStaysFromCities(selectedCities, [])
                }
                onChange={handleCityStaysChange}
              />
            </div>
          </div>
        )}

        {(tripType === "multi" || selectedCities.length > 1) &&
          selectedCities.length > 1 &&
          !hasTotalDates && (
            <div
              style={{
                gridColumn: "1 / -1",
                marginTop: 8,
                padding: "10px 12px",
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
      </div>
      )}

      <div className="hero-room-options">
        <button
          type="button"
          className="hero-room-options__btn"
          onClick={() => {
            const guestToggle = document.querySelector(
              ".hero-search-reserve .searchMenu-guests .guest-search-trigger"
            );
            if (guestToggle) guestToggle.click();
          }}
        >
          Room &amp; traveller options · 1 room(s) ·{" "}
          {guestCounts.Children || 0} children · {guestCounts.Infants || 0}{" "}
          infants ↓
        </button>
      </div>

      <Snackbar
        open={openSnackbar}
        autoHideDuration={6000}
        onClose={handleCloseSnackbar}
        anchorOrigin={{ vertical: "top", horizontal: "center" }}
        sx={{ top: { xs: 16, sm: 24 }, zIndex: 20000 }}
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
