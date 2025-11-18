import { useState, useEffect } from "react";
import { useSelector, useDispatch } from "react-redux";
import DateSearch from "../common/DateSearch";
import GuestSearch from "../common/GuestSearch";
import LocationSearch from "../common/LocationSearch";
import {
  setSearchParams,
  fetchAttractions,
} from "../../../slice/attractions/attractionSlice";
import { setSelectedCity } from "@/slice/common/commonSlice";
import { triggerSearch, clearTriggerSearch } from "@/slice/common/stepsSlice";

const MainFilterSearchBox = () => {
  console.log('🔍 Attraction MainFilterSearchBox - Component rendered/mounted');
  const dispatch = useDispatch();

  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  // console.log('tourdetails tourdetails tourdetails',tourdetails?.tour_id);

  // Add error state
  const [locationError, setLocationError] = useState(false);

  // Default values
  const defaultAdults = tourdetails?.adult > 0 ? tourdetails.adult : 1;
  const defaultChildren = tourdetails?.child || 0;

  // States
  const [selectedLocation, setSelectedLocation] = useState(null);
  const [selectedDate, setSelectedDate] = useState(null);
  const [guestCounts, setGuestCounts] = useState({
    Adults: defaultAdults,
    Children: defaultChildren,
  });

  useEffect(() => {
    setGuestCounts({
      Adults: defaultAdults,
      Children: defaultChildren,
    });
  }, [tourdetails]);

  // Handlers
  const handleLocationSelect = (location) => setSelectedLocation(location);
  const handleDateSelect = (date) => setSelectedDate(date);

  const handleSearch = () => {
    if (!selectedLocation) {
      setLocationError(true);
      return;
    }

    const searchData = {
      location: selectedLocation,
      date: selectedDate ? selectedDate.format("YYYY-MM-DD") : null,
      selectedDate: selectedDate ? selectedDate.format("YYYY-MM-DD") : null,
      adults: guestCounts.Adults,
      children: guestCounts.Children,
      tour_id: tourdetails?.tour_id,
    };

    dispatch(setSelectedCity(selectedLocation));

    dispatch(setSearchParams(searchData));
    // Clear previous attractions before new search
    dispatch({ type: "attractions/clearAttractions" });
    
    // Reset pagination to first page for new search
    // Note: This will be handled by the TourProperties component

    dispatch(
      fetchAttractions({
        city: selectedLocation?.address,
        date: selectedDate ? selectedDate.format("YYYY-MM-DD") : null,
        adults: guestCounts.Adults,
        children: guestCounts.Children,
        tour_id: tourdetails?.tour_id,
        selectedDate: selectedDate,
        start: 0,
        limit: 5,
      })
    );
  };

  // Listen for triggerSearch from Redux and call handleSearch when attraction step is triggered
  const searchTrigger = useSelector((state) => {
    const trigger = state.steps.triggerSearch;
    console.log('🔍 Attraction MainFilterSearchBox - Selector called, searchTrigger:', trigger);
    return trigger;
  });
  
  useEffect(() => {
    console.log('🔍 Attraction MainFilterSearchBox - useEffect triggered, searchTrigger:', searchTrigger);
    console.log('🔍 Attraction MainFilterSearchBox - Component is mounted and listening for triggers');
    
    if (searchTrigger === 'attraction') {
      console.log('🔍 Attraction MainFilterSearchBox - Trigger search received for attraction, calling handleSearch');
      handleSearch();
      // Clear the trigger after handling
      dispatch(clearTriggerSearch());
    }
  }, [searchTrigger, dispatch]);

  return (
    <div className="mainSearch -col-3-big bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-4 mt-30">
      <div className="button-grid items-center">
        <LocationSearch
          onLocationSelect={handleLocationSelect}
          hasError={locationError}
          setError={setLocationError}
        />

        {/* Check-in / Check-out */}
        <div className="searchMenu-date px-30 lg:py-20 sm:px-20 js-form-dd js-calendar">
          <div className="d-flex">
            <i className="icon-calendar-2 text-20 text-light-1 mt-5"></i>
            <div className="ml-10">
              <h4 className="text-15 fw-500 ls-2 lh-16">Select Date</h4>
              <DateSearch onDateSelect={handleDateSelect} />
            </div>
          </div>
        </div>

        {/* Guest selection */}
        <GuestSearch
          guestCounts={guestCounts}
          setGuestCounts={setGuestCounts}
        />

        {/* Search Button */}
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
  );
};

export default MainFilterSearchBox;
