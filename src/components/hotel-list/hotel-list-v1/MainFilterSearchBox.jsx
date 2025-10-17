import DateSearch from "../common/DateSearch";
import GuestSearch from "../common/GuestSearch";
import LocationSearch from "../common/LocationSearch";
import { format, parse } from "date-fns";
import { useDispatch, useSelector } from "react-redux";
import {
  fetchHotels,
  updateSearchState,
  resetHotels,
} from "@/slice/hotel/hotelSlice";
import { useLocation, useNavigate } from "react-router-dom";
import { useEffect, useRef, useState } from "react";
import { DateObject } from "react-multi-date-picker";
import { setSelectedCity } from "@/slice/common/commonSlice";

const MainFilterSearchBox = () => {
  const dispatch = useDispatch();
  const { state } = useLocation();

  // Get data from hotelSlice tourdetails instead of BookingSlice
  const tourDetails = useSelector((state) => state.hotels.tourdetails);
  const { searchState } = useSelector((state) => state.hotels);
  const id = useSelector((state) => state.hotels.id);

  //console.log("Tour Details from hotelSlice:", tourDetails);

  // Get location from BookingSlice if needed
  const Location = useSelector((state) => state.bookings.searchLocation);

  const [selectedLocation1, setSelectedLocation1] = useState(""); // Input value
  const [selectedLocation, setSelectedLocation] = useState(null);
  const [showDropdown, setShowDropdown] = useState(false);
  const dropdownRef = useRef(null); //clicks outside the dropdown
  const previousSearchLocation = useRef(null);

  // Extract check-in and check-out dates from tourDetails
  const checkIn = tourDetails?.CheckInTime || tourDetails?.check_in_time;
  const checkOut = tourDetails?.CheckOutTime || tourDetails?.check_out_time;

  // Initialize dateRange with DateObject format instead of dayjs
  const [dateRange, setDateRange] = useState([
    checkIn ? new DateObject({ date: checkIn, format: "DD/MM/YYYY" }) : null,
    checkOut ? new DateObject({ date: checkOut, format: "DD/MM/YYYY" }) : null,
  ]);

  const start = 0;
  const limit = 4;

  // Extract guest information from tourDetails
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

  // Setting initial guests state from tourDetails
  const [guestsState, setGuests] = useState(guestsFromTourDetails);

  //navigate
  const navigate = useNavigate();
  const formattedDateRange = dateRange.map((date) =>
    date ? date.format("YYYY-MM-DD") : null
  );

  // Store the initial allowed range when the component mounts
  const [initialMinDate, setInitialMinDate] = useState(null);
  const [initialMaxDate, setInitialMaxDate] = useState(null);

  useEffect(() => {
    if (checkIn) {
      setInitialMinDate(new Date(parse(checkIn, "dd/MM/yyyy", new Date())));
    }
    if (checkOut) {
      setInitialMaxDate(new Date(parse(checkOut, "dd/MM/yyyy", new Date())));
    }
  }, [checkIn, checkOut]);

  const handleSelect = (dates) => {
    if (!dates || dates.length !== 2) return;

    const [startDate, endDate] = dates;

    // Format the selected dates to "YYYY-MM-DD"
    const formattedStartDate = startDate.format("YYYY-MM-DD");
    const formattedEndDate = endDate.format("YYYY-MM-DD");

    setDateRange([startDate, endDate]);
  };
  const handleLocationSelect = (location) => setSelectedLocation(location.name);
  // console.log(selectedLocation, "selceted location");

  // Add error state
  const [locationError, setLocationError] = useState(false);

  // Default values
  const defaultAdults = tourDetails?.adult > 0 ? tourDetails.adult : 1;
  const defaultChildren = tourDetails?.child || 0;

  const handleSearch = () => {
    dispatch(setSelectedCity(selectedLocation));
    dispatch(resetHotels());

    const parsedStartDate = dateRange[0]
      ? dateRange[0].format("YYYY-MM-DD")
      : null;
    const parsedEndDate = dateRange[1]
      ? dateRange[1].format("YYYY-MM-DD")
      : null;

    // Check if location is selected before search
    if (!selectedLocation) {
      setLocationError(true);
      return;
    }

    // console.log("Search with:", {
    //   location: selectedLocation,
    //   ucheckIn: parsedStartDate,
    //   ucheckOut: parsedEndDate,
    //   guests: guestsState,
    // });

    dispatch(
      updateSearchState({
        location: selectedLocation,
        ucheckIn: parsedStartDate,
        ucheckOut: parsedEndDate,
        guests: guestsState,
      })
    );

    dispatch(
      fetchHotels({
        location: selectedLocation.address, // Use the full address from the location object
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

  // Initialize searchState with data from tourDetails on component mount
  useEffect(() => {
    if (tourDetails && !previousSearchLocation.current) {
      // console.log("Initializing from tourDetails:", tourDetails);

      // Convert dates to the format expected by hotelSlice
      let formattedCheckIn = null;
      let formattedCheckOut = null;

      if (checkIn) {
        const checkInDate = new DateObject({
          date: checkIn,
          format: "DD/MM/YYYY",
        });
        formattedCheckIn = checkInDate.format("YYYY-MM-DD");
      }

      if (checkOut) {
        const checkOutDate = new DateObject({
          date: checkOut,
          format: "DD/MM/YYYY",
        });
        formattedCheckOut = checkOutDate.format("YYYY-MM-DD");
      }

      // Update hotelSlice searchState with data from tourDetails
      dispatch(
        updateSearchState({
          location: tourDetails.destination || searchState.location,
          ucheckIn: formattedCheckIn,
          ucheckOut: formattedCheckOut,
          guests: {
            adults: tourDetails.adult || 1,
            children: tourDetails.child || 0,
            infant: tourDetails.infant || 0,
          },
        })
      );

      // Mark that we've initialized
      previousSearchLocation.current = true;
    }
  }, [dispatch]); // Only run once on component mount

  return (
    <>
      <div className="mainSearch -col-3-big bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-4 mt-30">
        <div className="button-grid items-center">
          <LocationSearch
            onLocationSelect={handleLocationSelect}
            hasError={locationError}
            setError={setLocationError}
          />
          {/* End Location */}

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
            </div>
          </div>
          {/* End check-in-out */}

          <GuestSearch
            guests={guestsState}
            setGuests={setGuests}
            maxValues={maxValues}
            showDropdown={showDropdown}
            setShowDropdown={setShowDropdown}
            dropdownRef={dropdownRef}
          />
          {/* End guest */}

          <div className="button-item h-full">
            <button
              onClick={handleSearch}
              className="button -dark-1 py-15 px-40 h-full col-12 rounded-0 bg-blue-1 text-white"
            >
              <i className="icon-search text-20 mr-10" />
              Search
            </button>
          </div>
          {/* End search button_item */}
        </div>
      </div>
    </>
  );
};

export default MainFilterSearchBox;
