import React, { useState, useEffect, useCallback, useMemo, useRef } from "react";
import { useDispatch, useSelector } from "react-redux";
import DateSearch from "../common/DateSearch";
import LocationSearch from "./LocationSearch";
import CityServiceChips from "@/components/common/CityServiceChips";
import {
  fetchGuides,
  setentrypickup,
  setpickupdate,
} from "@/slice/tourguide/guideslice";
import { setSelectedCity, selectCityWiseDates } from "@/slice/common/commonSlice";
import { clearTriggerSearch } from "@/slice/common/stepsSlice";
import { toCityOnly } from "@/utils/locationFormat";
import {
  buildCityChipItems,
  buildLocationFromCityName,
  getCityDateBounds,
  isCityServiceBooked,
  normalizeYmd,
} from "@/utils/cityWiseDates";

const MainFilterSearchBox = () => {
  const dispatch = useDispatch();

  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  const cityWiseDates = useSelector(selectCityWiseDates);
  const cityList = useSelector((state) => state.city?.city || []);
  const bookedGuides = useSelector((state) => state.tourguide.bookedguide || []);

  const [pickUpLocation, setPickUpLocation] = useState("");
  const [selectedDate, setSelectedDate] = useState("");
  const [dateBounds, setDateBounds] = useState({ minDate: null, maxDate: null });
  const [locationError, setLocationError] = useState(false);
  const [controlledCity, setControlledCity] = useState(null);
  const hasAutoSelectedCity = useRef(false);

  const tourCheckIn = tourdetails?.CheckInTime || tourdetails?.check_in_time;
  const tourCheckOut = tourdetails?.CheckOutTime || tourdetails?.check_out_time;
  const hasCityWiseDates =
    Array.isArray(cityWiseDates) && cityWiseDates.length > 0;

  const applyCityContext = useCallback(
    (location) => {
      if (!location) {
        setPickUpLocation("");
        setControlledCity(null);
        setSelectedDate("");
        return;
      }

      const cityName = location.name || location.address || location;
      const bounds = getCityDateBounds(
        cityWiseDates,
        cityName,
        tourCheckIn,
        tourCheckOut
      );
      const minYmd = normalizeYmd(bounds.checkIn);
      const maxYmd = normalizeYmd(bounds.checkOut);
      const defaultDate = minYmd || "";

      setPickUpLocation(location.address || location.name || "");
      setControlledCity(location);
      setLocationError(false);
      setDateBounds({ minDate: minYmd, maxDate: maxYmd });
      setSelectedDate(defaultDate);
      dispatch(
        setSelectedCity({
          id: location.id,
          name: location.name,
          address: location.address,
          city: location.name,
          country: String(location.address || "")
            .split(",")
            .slice(-1)[0]
            .trim(),
        })
      );
    },
    [cityWiseDates, tourCheckIn, tourCheckOut, dispatch]
  );

  useEffect(() => {
    if (hasCityWiseDates) return;
    const bounds = getCityDateBounds([], null, tourCheckIn, tourCheckOut);
    const minYmd = normalizeYmd(bounds.checkIn);
    const maxYmd = normalizeYmd(bounds.checkOut);
    setDateBounds({ minDate: minYmd, maxDate: maxYmd });
    if (minYmd && !selectedDate) {
      setSelectedDate(minYmd);
    }
  }, [hasCityWiseDates, tourCheckIn, tourCheckOut, selectedDate]);

  useEffect(() => {
    if (hasAutoSelectedCity.current) return;
    if (!hasCityWiseDates) return;
    if (pickUpLocation) {
      hasAutoSelectedCity.current = true;
      return;
    }

    const pending =
      cityWiseDates.find(
        (entry) => !isCityServiceBooked(entry, bookedGuides)
      ) || cityWiseDates[0];
    const location = buildLocationFromCityName(pending.city, cityList);
    if (location) {
      hasAutoSelectedCity.current = true;
      applyCityContext(location);
    }
  }, [
    hasCityWiseDates,
    cityWiseDates,
    bookedGuides,
    pickUpLocation,
    cityList,
    applyCityContext,
  ]);

  const handleCityChipClick = (entry) => {
    const location = buildLocationFromCityName(entry.city, cityList);
    if (location) {
      applyCityContext(location);
    }
  };

  const buttonsearch = useCallback(() => {
    if (!pickUpLocation || pickUpLocation.trim() === "") {
      setLocationError(true);
      return;
    }

    if (!selectedDate) {
      return;
    }

    setLocationError(false);

    dispatch(setentrypickup(pickUpLocation));
    dispatch(setpickupdate(selectedDate));

    dispatch(
      fetchGuides({
        city: pickUpLocation,
        date: selectedDate,
        start: 0,
        limit: 5,
      })
    );
  }, [dispatch, pickUpLocation, selectedDate]);

  const searchTrigger = useSelector((state) => state.steps.triggerSearch);

  useEffect(() => {
    if (searchTrigger === "guide") {
      buttonsearch();
      dispatch(clearTriggerSearch());
    }
  }, [searchTrigger, dispatch, buttonsearch]);

  const activeCityName = useMemo(
    () => toCityOnly(pickUpLocation || controlledCity),
    [pickUpLocation, controlledCity]
  );

  const cityChipItems = useMemo(
    () =>
      buildCityChipItems({
        cityWiseDates,
        activeCityName,
        bookings: bookedGuides,
      }),
    [cityWiseDates, activeCityName, bookedGuides]
  );

  return (
    <>
      <CityServiceChips
        items={cityChipItems}
        onCitySelect={handleCityChipClick}
      />

      <div className="mainSearch -col-2 bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-4 mt-30">
        <div className="button-grid items-center">
          <LocationSearch
            pickUpLocation={pickUpLocation}
            setPickUpLocation={setPickUpLocation}
            controlledCity={controlledCity}
            onCitySelect={applyCityContext}
            hasError={locationError}
            setError={setLocationError}
          />

          <div className="searchMenu-date px-30 lg:py-20 lg:px-0 js-form-dd js-calendar">
            <div className="d-flex">
              <div className="ml-10">
                <h4
                  className="text-15 fw-500 ls-2 lh-16"
                  style={{ marginTop: "10px" }}
                >
                  Pick Up Date
                </h4>
                <DateSearch
                  selectedDate={selectedDate}
                  setSelectedDate={setSelectedDate}
                  minDate={dateBounds.minDate}
                  maxDate={dateBounds.maxDate}
                  value={selectedDate}
                />
                {hasCityWiseDates && pickUpLocation && (
                  <div className="text-12 text-light-1 mt-5">
                    Dates within {toCityOnly(pickUpLocation)} stay
                  </div>
                )}
              </div>
            </div>
          </div>

          <div className="button-item">
            <button
              className="mainSearch__submit button -dark-1 py-20 px-40 col-12 rounded-4 bg-blue-1 text-white"
              onClick={buttonsearch}
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
