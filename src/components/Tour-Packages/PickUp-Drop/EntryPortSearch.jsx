import React, { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import LocationSearch from "./LocationSearch";
import {
  fetchVehicles,
  setentrypickup,
  setentrydropoff,
  setpickupdate,
  setentrytime,
  setSelectionType,
  setPickupPlaceid,
  setDropoffPlaceid,
  setPortZoneType,
} from "@/slice/port/pickupDropSlice";
import DateSearch1 from "@/components/activity-list/common/DateSearch1";
import Pickuptime from "@/components/activity-single/filter-box1/Pickuptime";

const EntryPortSearch = ({ Location }) => {
  const dispatch = useDispatch();
  
  // Get values from Redux store to persist state
  const reduxPickUpLocation = useSelector((state) => state.pickupDrop.entrypickup || "");
  const reduxDropOffLocation = useSelector((state) => state.pickupDrop.entrydropoff || "");
  const reduxPickupDate = useSelector((state) => state.pickupDrop.pickupdate || "");
  const reduxEntryTime = useSelector((state) => state.pickupDrop.entrytime || "");
  const reduxPickUpLatLng = useSelector((state) => state.pickupDrop.PickupPlaceid || "");
  const reduxDropOffLatLng = useSelector((state) => state.pickupDrop.DropoffPlaceid || "");
  
  // State for storing the pickup and dropoff locations
  const [pickUpLocation, setPickUpLocation] = useState(reduxPickUpLocation);
  const [dropOffLocation, setDropOffLocation] = useState(reduxDropOffLocation);
  const [selectedDate, setSelectedDate] = useState(reduxPickupDate);
  const [pickUpLatLng, setPickupLatLng] = useState(reduxPickUpLatLng);
  const [dropOffLatLng, setDropoffLatLng] = useState(reduxDropOffLatLng);
  const [entryytime, setentryytime] = useState(reduxEntryTime);
  const [validationTriggered, setValidationTriggered] = useState(false);
  const [pickupFromAutocomplete, setPickupFromAutocomplete] = useState(false);
  const [dropoffFromAutocomplete, setDropoffFromAutocomplete] = useState(false);
  const [time, setTime] = useState(false);

  // Update Redux store whenever local state changes
  useEffect(() => {
    if (pickUpLocation) {
      dispatch(setentrypickup(pickUpLocation));
    }
  }, [pickUpLocation, dispatch]);

  useEffect(() => {
    if (dropOffLocation) {
      dispatch(setentrydropoff(dropOffLocation));
    }
  }, [dropOffLocation, dispatch]);

  useEffect(() => {
    if (selectedDate) {
      dispatch(setpickupdate(selectedDate));
    }
  }, [selectedDate, dispatch]);

  useEffect(() => {
    if (entryytime) {
      dispatch(setentrytime(entryytime));
    }
  }, [entryytime, dispatch]);

  useEffect(() => {
    if (pickUpLatLng) {
      dispatch(setPickupPlaceid(pickUpLatLng));
    }
  }, [pickUpLatLng, dispatch]);

  useEffect(() => {
    if (dropOffLatLng) {
      dispatch(setDropoffPlaceid(dropOffLatLng));
    }
  }, [dropOffLatLng, dispatch]);

  // Log Location prop to debug
  useEffect(() => {
    console.log("Entry Port Location:", Location);
  }, [Location]);

  // Handler for the button search click event
  const buttonsearch = () => {
    // Set validation triggered to true when search button is clicked
    setValidationTriggered(true);
    dispatch(setPortZoneType(""));
    
    // Only proceed if both locations are selected from autocomplete
    const locationsValid = pickupFromAutocomplete && dropoffFromAutocomplete;

    dispatch(setentrypickup(pickUpLocation));
    dispatch(setentrydropoff(dropOffLocation));
    dispatch(setpickupdate(selectedDate));
    dispatch(setentrytime(entryytime));
    dispatch(setSelectionType("Entry Port"));
    dispatch(setPickupPlaceid(pickUpLatLng));
    dispatch(setDropoffPlaceid(dropOffLatLng));

    // Only fetch vehicles if both locations are valid
    if (locationsValid && time) {
      setTimeout(() => {
        dispatch(fetchVehicles());
      }, 500);
    }
  };

  return (
    <div className="entry-port-search-grid">
      {/* Location Search */}
      <div className="location-search-wrapper">
        <LocationSearch
          pickUpLocation={pickUpLocation}
          setPickUpLocation={setPickUpLocation}
          dropOffLocation={dropOffLocation}
          setDropOffLocation={setDropOffLocation}
          pickUpLatLng={pickUpLatLng}
          setPickupLatLng={setPickupLatLng}
          dropOffLatLng={dropOffLatLng}
          setDropoffLatLng={setDropoffLatLng}
          Location={Location}
          validationTriggered={validationTriggered}
          setPickupFromAutocomplete={setPickupFromAutocomplete}
          setDropoffFromAutocomplete={setDropoffFromAutocomplete}
        />
      </div>

      {/* Time Selection */}
      <div className="time-selection-wrapper">
        <Pickuptime
          entryytime={entryytime}
          setentryytime={setentryytime}
          setTime={setTime}
        />
      </div>

      {/* Date Selection */}
      <div className="date-selection-wrapper">
        <h4 className="text-15 fw-500 ls-2 lh-16 mt-15">Pick Up Date</h4>
        <DateSearch1
          selectedDate={selectedDate}
          setSelectedDate={setSelectedDate}
        />
      </div>

      {/* Search Button */}
      <div className="search-button-wrapper">
        <button
          className="mainSearch__submit button -dark-1 py-15 px-35 rounded-4 bg-blue-1 text-white"
          onClick={buttonsearch}
        >
          <i className="icon-search text-20 mr-10" />
          Search
        </button>
      </div>

      <style jsx>{`
        .entry-port-search-grid {
          display: grid;
          grid-template-columns: 4fr 1.93fr 140px 1fr;
          gap: 5px;
          width: 100%;
        }

        .location-search-wrapper,
        .time-selection-wrapper,
        .date-selection-wrapper,
        .search-button-wrapper {
          width: 100%;
        }

        .search-button-wrapper button {
          width: 100%;
          max-width: 250px;
        }

        @media (max-width: 1199px) {
          .entry-port-search-grid {
            grid-template-columns: 1fr 1fr;
            gap: 20px;
          }

          .location-search-wrapper {
            grid-column: span 2;
          }

          .search-button-wrapper {
            grid-column: span 2;
            text-align: center;
          }
        }

        @media (max-width: 767px) {
          .entry-port-search-grid {
            grid-template-columns: 1fr;
            gap: 15px;
          }

          .time-selection-wrapper,
          .date-selection-wrapper {
            margin-bottom: 10px;
          }

          .search-button-wrapper button {
            max-width: 100%;
          }
        }
      `}</style>
    </div>
  );
};

export default EntryPortSearch; 