import { useState, useEffect, useCallback, useMemo, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import DateSearch from "../common/DateSearch";
import GuestSearch from "../common/GuestSearch";
import LocationSearch from "../common/LocationSearch";
import CityServiceChips from "@/components/common/CityServiceChips";
import {
  setSearchParams,
  fetchRestaurants,
} from "../../../slice/restaurant/RestaurantsSlice";
import { setSelectedCity, selectCityWiseDates } from "@/slice/common/commonSlice";
import { clearTriggerSearch } from "@/slice/common/stepsSlice";
import { toCityOnly, getCountryForCityFromDestination } from "@/utils/locationFormat";
import {
  buildCityChipItems,
  buildLocationFromCityName,
  getCityDateBounds,
  isCityServiceBooked,
} from "@/utils/cityWiseDates";

const MainFilterSearchBox = () => {
  const dispatch = useDispatch();

  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  const cityWiseDates = useSelector(selectCityWiseDates);
  const cityList = useSelector((state) => state.city?.city || []);
  const restaurantServices = useSelector((state) => state.restaurants.services || []);

  const [locationError, setLocationError] = useState(false);
  const hasAutoSelectedCity = useRef(false);

  const defaultAdults = tourdetails?.adult > 0 ? tourdetails.adult : 1;
  const defaultChildren = tourdetails?.child || 0;
  const tourCheckIn = tourdetails?.CheckInTime || tourdetails?.check_in_time;
  const tourCheckOut = tourdetails?.CheckOutTime || tourdetails?.check_out_time;
  const hasCityWiseDates =
    Array.isArray(cityWiseDates) && cityWiseDates.length > 0;

  const [selectedLocation, setSelectedLocation] = useState(null);
  const [selectedDate, setSelectedDate] = useState(null);
  const [dateBounds, setDateBounds] = useState({
    minDate: null,
    maxDate: null,
  });
  const [guestCounts, setGuestCounts] = useState({
    Adults: defaultAdults,
    Children: defaultChildren,
  });

  useEffect(() => {
    setGuestCounts({
      Adults: defaultAdults,
      Children: defaultChildren,
    });
  }, [defaultAdults, defaultChildren, tourdetails]);

  useEffect(() => {
    if (hasCityWiseDates) return;
    const bounds = getCityDateBounds(
      [],
      null,
      tourCheckIn,
      tourCheckOut
    );
    setDateBounds({
      minDate: bounds.checkIn,
      maxDate: bounds.checkOut,
    });
    if (bounds.checkIn && !selectedDate) {
      setSelectedDate(bounds.checkIn);
    }
  }, [hasCityWiseDates, tourCheckIn, tourCheckOut, selectedDate]);

  const applyCityContext = useCallback(
    (location) => {
      if (!location) {
        setSelectedLocation(null);
        setSelectedDate(null);
        return;
      }

      const cityName = location.name || location.address || location;
      const bounds = getCityDateBounds(
        cityWiseDates,
        cityName,
        tourCheckIn,
        tourCheckOut
      );

      setSelectedLocation(location);
      setLocationError(false);
      setDateBounds({
        minDate: bounds.checkIn,
        maxDate: bounds.checkOut,
      });
      setSelectedDate(bounds.checkIn);
      dispatch(setSelectedCity(location));
    },
    [cityWiseDates, tourCheckIn, tourCheckOut, dispatch]
  );

  const handleLocationSelect = (location) => {
    if (!location) {
      setSelectedLocation(null);
      return;
    }
    applyCityContext(location);
  };

  const handleDateSelect = (date) => setSelectedDate(date);

  const handleCityChipClick = (entry) => {
    const location = buildLocationFromCityName(entry.city, cityList);
    if (location) {
      applyCityContext(location);
    }
  };

  useEffect(() => {
    if (hasAutoSelectedCity.current) return;
    if (!hasCityWiseDates) return;
    if (selectedLocation) {
      hasAutoSelectedCity.current = true;
      return;
    }

    const pending =
      cityWiseDates.find(
        (entry) => !isCityServiceBooked(entry, restaurantServices)
      ) || cityWiseDates[0];
    const location = buildLocationFromCityName(pending.city, cityList);
    if (location) {
      hasAutoSelectedCity.current = true;
      applyCityContext(location);
    }
  }, [
    hasCityWiseDates,
    cityWiseDates,
    restaurantServices,
    selectedLocation,
    cityList,
    applyCityContext,
  ]);

  const handleSearch = () => {
    if (!selectedLocation) {
      setLocationError(true);
      return;
    }

    const formattedDate = selectedDate ? selectedDate.format("YYYY-MM-DD") : "";

    const searchData = {
      location: selectedLocation,
      date: formattedDate,
      adults: guestCounts.Adults,
      children: guestCounts.Children,
    };

    dispatch(setSelectedCity(selectedLocation.name || selectedLocation));
    dispatch(setSearchParams(searchData));
    dispatch({ type: "restaurants/clearRestaurants" });

    const selectedCityName =
      selectedLocation?.name ||
      selectedLocation?.address ||
      selectedLocation;
    const country =
      getCountryForCityFromDestination(
        tourdetails?.destination,
        selectedCityName
      ) ||
      tourdetails?.country ||
      "";
console.log("country", country);
    dispatch(
      fetchRestaurants({
        city: selectedLocation?.address || selectedLocation?.name,
        country,
        date: formattedDate,
        adults: guestCounts.Adults,
        children: guestCounts.Children,
        tour_id: tourdetails?.tour_id,
        start: 0,
        limit: 5,
      })
    );
  };

  const searchTrigger = useSelector((state) => state.steps.triggerSearch);

  useEffect(() => {
    if (searchTrigger === "restaurent") {
      handleSearch();
      dispatch(clearTriggerSearch());
    }
  }, [searchTrigger, dispatch]);

  const activeCityName = useMemo(
    () => toCityOnly(selectedLocation),
    [selectedLocation]
  );

  const cityChipItems = useMemo(
    () =>
      buildCityChipItems({
        cityWiseDates,
        activeCityName,
        bookings: restaurantServices,
      }),
    [cityWiseDates, activeCityName, restaurantServices]
  );

  return (
    <>
      <CityServiceChips
        items={cityChipItems}
        onCitySelect={handleCityChipClick}
      />

      <div className="mainSearch -col-3-big bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-4 mt-30">
        <div className="button-grid items-center">
          <LocationSearch
            onLocationSelect={handleLocationSelect}
            hasError={locationError}
            setError={setLocationError}
            controlledCity={selectedLocation}
          />

          <div className="searchMenu-date px-30 lg:py-20 sm:px-20 js-form-dd js-calendar">
            <div className="d-flex">
              <i className="icon-calendar-2 text-20 text-light-1 mt-5"></i>
              <div className="ml-10">
                <h4 className="text-15 fw-500 ls-2 lh-16">Select Date</h4>
                <DateSearch
                  onDateSelect={handleDateSelect}
                  minDate={dateBounds.minDate}
                  maxDate={dateBounds.maxDate}
                  value={selectedDate}
                />
                {hasCityWiseDates && selectedLocation && (
                  <div className="text-12 text-light-1 mt-5">
                    Dates within {toCityOnly(selectedLocation)} stay
                  </div>
                )}
              </div>
            </div>
          </div>

          <GuestSearch
            guestCounts={guestCounts}
            setGuestCounts={setGuestCounts}
          />

          <div className="button-item h-full">
            <button
              className="button -dark-1 py-15 px-40 h-full col-12 rounded-0 bg-blue-1 text-white"
              onClick={handleSearch}
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
