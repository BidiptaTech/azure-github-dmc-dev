import React, { useState, useCallback, useEffect } from "react";
import DateSearch from "../DateSearch";
import GuestSearch from "./GuestSearch";
import CitySearch from "./CitySearch";
import Snackbar from "@mui/material/Snackbar";
import MuiAlert from "@mui/material/Alert";
import { useDispatch, useSelector } from "react-redux";
import { resetHotels } from "../../../slice/hotel/hotelSlice";
import moment from "moment";
import { clearUserInfo } from "../../../slice/common/customerInfo";
import { clearAttractions } from "../../../slice/attractions/attractionSlice";
import { clearRestaurants } from "../../../slice/restaurant/RestaurantsSlice";
import { resetguide } from "../../../slice/tourguide/guideslice";
import { resetVehicles } from "../../../slice/port/pickupDropSlice";
import { resetVehicles1 } from "../../../slice/localtour/Localslice";
import {
  setSelectedCity,
  setCityWiseDates,
} from "@/slice/common/commonSlice";
import {
  setSearchLocation,
  setCheckIn,
  setCheckOut,
  setGuest,
  clearServiceDetails,
  clearSpecificService,
} from "../../../slice/common/EnquirySlice";
import { clearEnquiryList } from "../../../slice/common/enquiryListSlice";
import {
  clearSelectedDmcs,
  clearSelectedDmc,
} from "../../../slice/dmc/dmcSlice";
import {
  clearSelectedCities,
  addSelectedCity,
} from "@/slice/common/citiesSlice";

const Alert = React.forwardRef(function Alert(props, ref) {
  return <MuiAlert elevation={6} ref={ref} variant="filled" {...props} />;
});

const formatDateValue = (date) => {
  if (!date) return "";
  if (typeof date.format === "function") return date.format("DD/MM/YYYY");
  if (date instanceof Date) return moment(date).format("DD/MM/YYYY");
  if (typeof date === "string") return date;
  return moment(date).format("DD/MM/YYYY");
};

const MainFilterSearchBox = ({ onNext }) => {
  const dispatch = useDispatch();
  const selectedCities = useSelector(
    (state) => state.cities?.selectedCities || []
  );
  const [selectedDates, setSelectedDates] = useState([]);
  const [cityDates, setCityDates] = useState({});
  const [tripType, setTripType] = useState("single");
  const [guestCounts, setGuestCounts] = useState({
    Adults: 1,
    Children: 0,
    Infants: 0,
    maleCount: 0,
    femaleCount: 0,
    ages: [],
  });
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState("");
  const [snackbarSeverity, setSnackbarSeverity] = useState("error");

  const hasTotalDates =
    Array.isArray(selectedDates) && selectedDates.length === 2;

  useEffect(() => {
    dispatch(setSelectedCity(null));
    dispatch(clearUserInfo());
    dispatch(clearSelectedDmc());
    dispatch(clearSelectedCities());
  }, [dispatch]);

  useEffect(() => {
    if (tripType === "single" && selectedCities.length > 1) {
      const keep = selectedCities[0];
      dispatch(clearSelectedCities());
      if (keep) dispatch(addSelectedCity(keep));
    }
  }, [tripType, selectedCities, dispatch]);

  useEffect(() => {
    if (selectedCities.length === 1 && hasTotalDates) {
      const onlyCityId = selectedCities[0].city_id;
      setCityDates({ [onlyCityId]: selectedDates });
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
  }, [selectedCities, selectedDates, hasTotalDates]);

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
    const prevFilled = Array.isArray(prevDates) && prevDates.length === 2;

    const blockedRanges = selectedCities
      .slice(0, cityIndex)
      .map((city) => cityDates[city.city_id])
      .filter((range) => Array.isArray(range) && range.length === 2);

    return {
      minDate: prevFilled ? prevDates[1] : totalMin,
      maxDate: totalMax,
      blockedRanges,
      disabled: !hasTotalDates || !prevFilled,
    };
  };

  const handleCityDateChange = (cityId, dates) => {
    setCityDates((prev) => ({ ...prev, [cityId]: dates }));
  };

  const handleDateChange = (dates) => {
    setSelectedDates(dates);
  };

  const handleGuestChange = (updatedGuestCounts) => {
    setGuestCounts(updatedGuestCounts);
  };

  const handleCitySelect = () => {
    dispatch(clearEnquiryList());
  };

  const validateForm = () => {
    if (!selectedCities.length) {
      setSnackbarMessage("Please select at least one city.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (!hasTotalDates) {
      setSnackbarMessage("Please select both check-in and check-out dates.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (selectedCities.length > 1 && !allCityDatesFilled) {
      setSnackbarMessage("Please set stay dates for every city.");
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

    const maleCount = guestCounts.maleCount || 0;
    const femaleCount = guestCounts.femaleCount || 0;

    if (maleCount === 0 && femaleCount === 0) {
      setSnackbarMessage("Please specify male and female counts for adults.");
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (maleCount + femaleCount !== guestCounts.Adults) {
      setSnackbarMessage(
        "Male and female count must equal the total adult count."
      );
      setSnackbarSeverity("error");
      setOpenSnackbar(true);
      return false;
    }

    if (
      guestCounts.Children > 0 &&
      !guestCounts.ages.every((age) => String(age).trim() !== "")
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

  const handleSearch = useCallback(
    async (e) => {
      e.preventDefault();
      if (!validateForm()) return;

      const formattedCheckIn = formatDateValue(selectedDates[0]);
      const formattedCheckOut = formatDateValue(selectedDates[1]);

      const cityNames = selectedCities.map((c) => c.city).filter(Boolean);
      const countryNames = [
        ...new Set(selectedCities.map((c) => c.country).filter(Boolean)),
      ];

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

      const firstCity = selectedCities[0];

      dispatch(
        setSearchLocation({
          country: countryNames.join(", "),
          city: cityNames.join(", "),
          cities: selectedCities.map((c) => ({
            city: c.city,
            country: c.country,
            city_id: c.city_id,
            country_code: c.country_code,
          })),
          cityWiseDates: cityWiseDatesPayload,
        })
      );

      dispatch(setCityWiseDates(cityWiseDatesPayload));
      dispatch(setCheckIn(formattedCheckIn));
      dispatch(setCheckOut(formattedCheckOut));
      dispatch(resetHotels());

      dispatch(
        setSelectedCity({
          countryCode: firstCity?.country_code || null,
          countryName: firstCity?.country || countryNames[0] || "",
          cityCode: firstCity?.city_id || null,
          // Downstream service fetches still use the primary city
          cityName: firstCity?.city || "",
          combinedCode: firstCity?.city_id || null,
        })
      );

      dispatch(clearUserInfo());
      dispatch(clearServiceDetails());
      dispatch(clearSpecificService());
      dispatch(clearAttractions());
      dispatch(resetguide());
      dispatch(resetVehicles());
      dispatch(resetVehicles1());
      dispatch(clearRestaurants());
      dispatch(clearSelectedDmcs());
      dispatch(clearSelectedDmc());
      dispatch(clearEnquiryList());

      const maleCount = guestCounts.maleCount || 0;
      const femaleCount = guestCounts.femaleCount || 0;
      const genders = [
        ...Array(maleCount).fill("Male"),
        ...Array(femaleCount).fill("Female"),
      ];

      dispatch(
        setGuest({
          adults: guestCounts.Adults.toString(),
          children: guestCounts.Children.toString(),
          infant: guestCounts.Infants.toString(),
          adultGenders: genders,
          childrenAges: guestCounts.ages || [],
          maleCount,
          femaleCount,
        })
      );

      onNext();
    },
    [
      selectedDates,
      selectedCities,
      cityDates,
      guestCounts,
      dispatch,
      onNext,
      allCityDatesFilled,
      hasTotalDates,
    ]
  );

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

  const checkInDisplay = formatGoibiboDate(hasTotalDates ? selectedDates[0] : null);
  const checkOutDisplay = formatGoibiboDate(hasTotalDates ? selectedDates[1] : null);

  const canSearchSingle = selectedCities.length === 1 && hasTotalDates;
  const canSearchMulti = showItineraryPlanner && allCityDatesFilled;
  const canSearch =
    (selectedCities.length <= 1 || tripType === "single"
      ? canSearchSingle
      : canSearchMulti) && guestCounts.Adults > 0;

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
                  name="enquiryTripType"
                  checked={tripType === opt.id}
                  onChange={() => {
                    setTripType(opt.id);
                    if (opt.id === "single") {
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
            Quick enquiry — destinations, dates &amp; pax to DMCs
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
            <CitySearch
              onCitySelect={handleCitySelect}
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
                  Split the enquiry period across cities in order
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
                        <div
                          style={{ fontSize: 15, fontWeight: 700, color: "#0f172a" }}
                        >
                          {city.city}
                        </div>
                        <div style={{ fontSize: 12, color: "#64748b" }}>
                          {city.country}
                        </div>
                        {index < selectedCities.length - 1 && (
                          <div
                            style={{ fontSize: 11, color: "#94a3b8", marginTop: 6 }}
                          >
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
                            handleCityDateChange(city.city_id, dates)
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
                        <div
                          style={{ fontSize: 11, color: "#94a3b8", marginTop: 6 }}
                        >
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
