import DateSearch from "../common/DateSearch";
import GuestSearch from "../common/GuestSearch";
import LocationSearch from "../common/LocationSearch";
import { parse } from "date-fns";
import { useDispatch, useSelector } from "react-redux";
import {
  fetchHotels,
  updateSearchState,
  resetHotels,
} from "@/slice/hotel/hotelSlice";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { DateObject } from "react-multi-date-picker";
import {
  setSelectedCity,
  selectCityWiseDates,
} from "@/slice/common/commonSlice";
import { triggerSearch, clearTriggerSearch } from "@/slice/common/stepsSlice";
import { toCityOnly } from "@/utils/locationFormat";

const toDateObject = (value) => {
  if (!value) return null;
  if (value instanceof DateObject) return value;
  if (typeof value === "string" && value.includes("/")) {
    return new DateObject({ date: value, format: "DD/MM/YYYY" });
  }
  if (typeof value === "string" && value.includes("-")) {
    return new DateObject({ date: value, format: "YYYY-MM-DD" });
  }
  try {
    return new DateObject(value);
  } catch {
    return null;
  }
};

const normalizeYmd = (value) => {
  if (!value) return null;
  if (value instanceof DateObject) return value.format("YYYY-MM-DD");
  const raw = String(value).trim();
  if (raw.includes("/")) {
    const [day, month, year] = raw.split("/");
    return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
  }
  return raw;
};

const findCityDatesEntry = (cityWiseDates, cityName) => {
  if (!cityName || !Array.isArray(cityWiseDates) || !cityWiseDates.length) {
    return null;
  }
  const needle = toCityOnly(cityName).toLowerCase();
  return (
    cityWiseDates.find(
      (entry) => toCityOnly(entry.city).toLowerCase() === needle
    ) || null
  );
};

const isCityHotelBooked = (cityEntry, hotelBookings) => {
  if (!cityEntry || !Array.isArray(hotelBookings) || !hotelBookings.length) {
    return false;
  }

  const cityName = toCityOnly(cityEntry.city).toLowerCase();
  const cityIn = normalizeYmd(cityEntry.checkIn);
  const cityOut = normalizeYmd(cityEntry.checkOut);

  return hotelBookings.some((booking) => {
    const locationRaw =
      booking?.hotelDetails?.location ||
      booking?.hotelDetails?.city ||
      booking?.location ||
      booking?.city ||
      "";
    const locationText = Array.isArray(locationRaw)
      ? locationRaw.join(" ")
      : String(locationRaw);
    const locationMatch =
      cityName && locationText.toLowerCase().includes(cityName);

    const bookingDates = booking?.bookingDate;
    const bookingIn = Array.isArray(bookingDates)
      ? normalizeYmd(bookingDates[0])
      : normalizeYmd(booking?.checkIn || booking?.check_in);
    const bookingOut = Array.isArray(bookingDates)
      ? normalizeYmd(bookingDates[1])
      : normalizeYmd(booking?.checkOut || booking?.check_out);

    const dateMatch =
      cityIn &&
      bookingIn &&
      (bookingIn === cityIn ||
        (cityOut &&
          bookingOut &&
          bookingIn <= cityOut &&
          bookingOut >= cityIn));

    return locationMatch || dateMatch;
  });
};

const formatChipDates = (entry) => {
  if (!entry?.checkIn || !entry?.checkOut) return "";
  const start = toDateObject(entry.checkIn);
  const end = toDateObject(entry.checkOut);
  if (!start || !end) return "";
  return `${start.format("MMM DD")} – ${end.format("MMM DD")}`;
};

const MainFilterSearchBox = () => {
  const dispatch = useDispatch();

  const tourDetails = useSelector((state) => state.hotels.tourdetails);
  const hotelService = useSelector((state) => state.hotels.hotelService || []);
  const cityWiseDates = useSelector(selectCityWiseDates);
  const cityList = useSelector((state) => state.city?.city || []);

  const [selectedLocation, setSelectedLocation] = useState(null);
  const [showDropdown, setShowDropdown] = useState(false);
  const dropdownRef = useRef(null);
  const previousSearchLocation = useRef(null);
  const hasAutoSelectedCity = useRef(false);

  const checkIn = tourDetails?.CheckInTime || tourDetails?.check_in_time;
  const checkOut = tourDetails?.CheckOutTime || tourDetails?.check_out_time;
  const hasCityWiseDates =
    Array.isArray(cityWiseDates) && cityWiseDates.length > 0;

  const [dateRange, setDateRange] = useState([
    checkIn ? toDateObject(checkIn) : null,
    checkOut ? toDateObject(checkOut) : null,
  ]);

  const guestsFromTourDetails = {
    adults: tourDetails?.adult || 1,
    children: tourDetails?.child || 0,
    infant: tourDetails?.infant || 0,
  };

  const maxValues = {
    adults: tourDetails?.adult || 10,
    children: tourDetails?.child || 10,
    infant: tourDetails?.infant || 10,
  };

  const [guestsState, setGuests] = useState(guestsFromTourDetails);

  const formattedDateRange = dateRange.map((date) =>
    date ? date.format("YYYY-MM-DD") : null
  );

  const [initialMinDate, setInitialMinDate] = useState(null);
  const [initialMaxDate, setInitialMaxDate] = useState(null);
  const [locationError, setLocationError] = useState(false);

  const applyCityDates = useCallback(
    (cityName) => {
      const entry = findCityDatesEntry(cityWiseDates, cityName);
      if (entry?.checkIn && entry?.checkOut) {
        const start = toDateObject(entry.checkIn);
        const end = toDateObject(entry.checkOut);
        if (start && end) {
          setDateRange([start, end]);
          setInitialMinDate(
            new Date(parse(entry.checkIn, "dd/MM/yyyy", new Date()))
          );
          setInitialMaxDate(
            new Date(parse(entry.checkOut, "dd/MM/yyyy", new Date()))
          );
          return true;
        }
      }

      // Fallback to total trip dates when no city-wise entry
      if (checkIn && checkOut) {
        setDateRange([toDateObject(checkIn), toDateObject(checkOut)]);
        setInitialMinDate(new Date(parse(checkIn, "dd/MM/yyyy", new Date())));
        setInitialMaxDate(new Date(parse(checkOut, "dd/MM/yyyy", new Date())));
      }
      return false;
    },
    [cityWiseDates, checkIn, checkOut]
  );

  const buildLocationFromCityName = useCallback(
    (cityName) => {
      const name = toCityOnly(cityName);
      if (!name) return null;

      const list = Array.isArray(cityList) ? cityList : [];
      const match = list
        .map((city, index) => {
          const address =
            typeof city === "string"
              ? city
              : city?.address || city?.name || city?.city || "";
          if (!address) return null;
          return {
            id: index + 1,
            name: String(address).split(",")[0].trim(),
            address: String(address),
          };
        })
        .filter(Boolean)
        .find(
          (item) =>
            item.name.toLowerCase() === name.toLowerCase() ||
            item.address.toLowerCase().includes(name.toLowerCase())
        );

      return (
        match || {
          id: 0,
          name,
          address: String(cityName),
        }
      );
    },
    [cityList]
  );

  const selectCityForSearch = useCallback(
    (location) => {
      if (!location) {
        setSelectedLocation(null);
        return;
      }
      setSelectedLocation(location);
      setLocationError(false);
      applyCityDates(location.name || location.address || location);
      dispatch(setSelectedCity(location));
    },
    [applyCityDates, dispatch]
  );

  useEffect(() => {
    if (checkIn && !hasCityWiseDates) {
      setInitialMinDate(new Date(parse(checkIn, "dd/MM/yyyy", new Date())));
    }
    if (checkOut && !hasCityWiseDates) {
      setInitialMaxDate(new Date(parse(checkOut, "dd/MM/yyyy", new Date())));
    }
  }, [checkIn, checkOut, hasCityWiseDates]);

  // Auto-select first city (or first without hotel) when city-wise dates exist
  useEffect(() => {
    if (hasAutoSelectedCity.current) return;
    if (!hasCityWiseDates) return;
    if (selectedLocation) {
      hasAutoSelectedCity.current = true;
      return;
    }

    const pending =
      cityWiseDates.find((entry) => !isCityHotelBooked(entry, hotelService)) ||
      cityWiseDates[0];
    const location = buildLocationFromCityName(pending.city);
    if (location) {
      hasAutoSelectedCity.current = true;
      selectCityForSearch(location);
    }
  }, [
    hasCityWiseDates,
    cityWiseDates,
    hotelService,
    selectedLocation,
    buildLocationFromCityName,
    selectCityForSearch,
  ]);

  const handleSelect = (dates) => {
    if (!dates || dates.length !== 2) return;
    setDateRange([dates[0], dates[1]]);
  };

  const handleLocationSelect = (location) => {
    if (!location) {
      setSelectedLocation(null);
      return;
    }
    selectCityForSearch(location);
  };

  const handleCityChipClick = (entry) => {
    const location = buildLocationFromCityName(entry.city);
    if (location) {
      selectCityForSearch(location);
    }
  };

  const resolveLocationString = (loc) => {
    const cityOnly = toCityOnly(loc);
    return cityOnly || null;
  };

  const handleSearch = () => {
    const locationString = resolveLocationString(selectedLocation);
    dispatch(setSelectedCity(selectedLocation));
    dispatch(resetHotels());

    const parsedStartDate = dateRange[0]
      ? dateRange[0].format("YYYY-MM-DD")
      : null;
    const parsedEndDate = dateRange[1]
      ? dateRange[1].format("YYYY-MM-DD")
      : null;

    if (!locationString) {
      setLocationError(true);
      return;
    }

    dispatch(
      updateSearchState({
        location: locationString,
        ucheckIn: parsedStartDate,
        ucheckOut: parsedEndDate,
        guests: guestsState,
      })
    );

    dispatch(
      fetchHotels({
        location: locationString,
        ucheckIn: parsedStartDate,
        ucheckOut: parsedEndDate,
        start: 0,
        limit: 5,
        guests: guestsState,
      })
    );
  };

  const disableBeforeToday = (current) => {
    return current && (current < checkIn || current > checkOut);
  };

  // Sync search UI when tourDetails become available (including after refresh restore)
  useEffect(() => {
    if (!tourDetails) return;

    const nextCheckIn =
      tourDetails?.CheckInTime || tourDetails?.check_in_time || null;
    const nextCheckOut =
      tourDetails?.CheckOutTime || tourDetails?.check_out_time || null;

    // Prefer active city-wise dates when a city is already selected
    const activeCityName =
      selectedLocation?.name ||
      selectedLocation?.address ||
      (typeof selectedLocation === "string" ? selectedLocation : null);
    const cityEntry = findCityDatesEntry(cityWiseDates, activeCityName);

    if (cityEntry?.checkIn && cityEntry?.checkOut) {
      applyCityDates(activeCityName);
    } else if (nextCheckIn && nextCheckOut && !hasCityWiseDates) {
      setDateRange([
        toDateObject(nextCheckIn),
        toDateObject(nextCheckOut),
      ]);
      setInitialMinDate(
        new Date(parse(nextCheckIn, "dd/MM/yyyy", new Date()))
      );
      setInitialMaxDate(
        new Date(parse(nextCheckOut, "dd/MM/yyyy", new Date()))
      );
    }

    if (tourDetails.adult != null || tourDetails.child != null) {
      setGuests({
        adults: tourDetails.adult || 1,
        children: tourDetails.child || 0,
        infant: tourDetails.infant || 0,
      });
    }

    if (previousSearchLocation.current) return;

    const useCheckIn = cityEntry?.checkIn || nextCheckIn;
    const useCheckOut = cityEntry?.checkOut || nextCheckOut;

    let formattedCheckIn = null;
    let formattedCheckOut = null;

    if (useCheckIn) {
      formattedCheckIn = toDateObject(useCheckIn)?.format("YYYY-MM-DD");
    }
    if (useCheckOut) {
      formattedCheckOut = toDateObject(useCheckOut)?.format("YYYY-MM-DD");
    }

    dispatch(
      updateSearchState({
        ucheckIn: formattedCheckIn,
        ucheckOut: formattedCheckOut,
        guests: {
          adults: tourDetails.adult || 1,
          children: tourDetails.child || 0,
          infant: tourDetails.infant || 0,
        },
      })
    );

    previousSearchLocation.current = true;
  }, [
    dispatch,
    tourDetails,
    cityWiseDates,
    selectedLocation,
    hasCityWiseDates,
    applyCityDates,
  ]);

  const searchTrigger = useSelector((state) => state.steps.triggerSearch);

  useEffect(() => {
    if (searchTrigger === "hotel") {
      handleSearch();
      dispatch(clearTriggerSearch());
    }
  }, [searchTrigger, dispatch]);

  const activeCityName = useMemo(
    () => toCityOnly(selectedLocation).toLowerCase(),
    [selectedLocation]
  );

  const cityChipItems = useMemo(() => {
    if (!hasCityWiseDates) return [];
    return cityWiseDates.map((entry) => ({
      ...entry,
      booked: isCityHotelBooked(entry, hotelService),
      active: toCityOnly(entry.city).toLowerCase() === activeCityName,
    }));
  }, [hasCityWiseDates, cityWiseDates, hotelService, activeCityName]);

  return (
    <>
      {cityChipItems.length > 0 && (
        <div className="d-flex flex-wrap items-center gap-2 mt-20 mb-10">
          <span className="text-14 text-light-1 mr-5">Cities:</span>
          {cityChipItems.map((entry) => (
            <button
              key={entry.city}
              type="button"
              onClick={() => handleCityChipClick(entry)}
              className="d-inline-flex align-items-center border-0 rounded-100 px-15 py-8 cursor-pointer"
              style={{
                background: entry.active
                  ? "#3554d1"
                  : entry.booked
                    ? "#e8f5e9"
                    : "#f7f8fc",
                color: entry.active
                  ? "#fff"
                  : entry.booked
                    ? "#2e7d32"
                    : "#1a1a1a",
                border: entry.active
                  ? "1px solid #3554d1"
                  : entry.booked
                    ? "1px solid #a5d6a7"
                    : "1px solid #e4e7f1",
                fontWeight: 600,
                fontSize: "12px",
                transition: "all 0.2s ease",
              }}
            >
              {entry.booked && (
                <i
                  className="icon-check text-12 mr-5"
                  style={{ color: entry.active ? "#fff" : "#2e7d32" }}
                />
              )}
              <span>{toCityOnly(entry.city)}</span>
              <span
                className="ml-8"
                style={{
                  opacity: entry.active ? 0.9 : 0.65,
                  fontWeight: 500,
                }}
              >
                {formatChipDates(entry)}
              </span>
            </button>
          ))}
        </div>
      )}

      <div className="mainSearch -col-3-big bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-4 mt-30">
        <div className="button-grid items-center">
          <LocationSearch
            onLocationSelect={handleLocationSelect}
            hasError={locationError}
            setError={setLocationError}
            controlledCity={selectedLocation}
          />

          <div className="searchMenu-date px-30 lg:py-20  sm:px-20 js-form-dd js-calendar">
            <div>
              <h4 className="text-15 fw-500 ls-2 lh-16">
                Check in - Check out
              </h4>
              <DateSearch
                dateRange={dateRange}
                setDateRange={setDateRange}
                handleSelect={handleSelect}
                disableBeforeToday={disableBeforeToday}
                formattedDateRange={formattedDateRange}
                initialMinDate={initialMinDate}
                initialMaxDate={initialMaxDate}
              />
              {hasCityWiseDates && selectedLocation && (
                <div className="text-12 text-light-1 mt-5">
                  Dates locked to {toCityOnly(selectedLocation)} stay
                </div>
              )}
            </div>
          </div>

          <GuestSearch
            guests={guestsState}
            setGuests={setGuests}
            maxValues={maxValues}
            showDropdown={showDropdown}
            setShowDropdown={setShowDropdown}
            dropdownRef={dropdownRef}
          />

          <div className="button-item h-full">
            <button
              onClick={handleSearch}
              className="button -dark-1 py-15 px-40 h-full col-12 rounded-0 bg-blue-1 text-white"
            >
              <i className="icon-search text-20 mr-10" />
              Search
            </button>
          </div>
        </div>
      </div>
    </>
  );
};

export default MainFilterSearchBox;
