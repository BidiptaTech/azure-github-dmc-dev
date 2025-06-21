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
    <div className="entry-port-search-container">
      {/* First row - Location Search */}
      <div className="search-row1 location-row">
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
        <div className="time-selection-wrapper">
          {/* Time Selection */}
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
      </div>
      {/* Second row - Time, Date, Button */}
      <div className="search-row controls-row">
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
      </div>

      <style jsx>{`
        .entry-port-search-container {
          display: flex;
          flex-direction: column;
          gap: 15px;
          width: 100%;
        }
        
        .search-row1 {
          width: 100%;
          margin-left: -10px;
        }
        
        .location-row {
          display: flex;
          flex-direction: row;
          align-items: flex-end;
          flex-wrap: nowrap;
          gap: 10px;
        }
        
        .controls-row {
          /* Second row with controls */
          display: flex;
          flex-wrap: wrap;
          align-items: flex-end;
          gap: 15px;
        }
        
        .searchMenu-loc {
          width: 58%;
          flex-shrink: 0;
          margin-right: 0;
        }
        
        .time-selection-wrapper {
          width: 15%;
          flex-shrink: 0;
        }
        
        .date-selection-wrapper {
          width: 20%;
          flex-shrink: 0;
        }
        
        .search-button-wrapper {
          width: 20%;
          margin-left: auto;
        }

        .search-button-wrapper button {
          width: 100%;
          max-width: 160px;
        }

        @media (max-width: 1400px) {
          .searchMenu-loc {
            width: 50%;
          }
          
          .time-selection-wrapper {
            width: 20%;
          }
          
          .date-selection-wrapper {
            width: 25%;
          }
        }

        @media (max-width: 1199px) {
          .location-row {
            flex-wrap: wrap;
          }
          
          .searchMenu-loc {
            width: 100%;
            margin-bottom: 15px;
          }
          
          .time-selection-wrapper {
            width: 48%;
          }
          
          .date-selection-wrapper {
            width: 48%;
          }
          
          .controls-row {
            flex-direction: column;
            gap: 15px;
          }
          
          .time-selection-wrapper,
          .date-selection-wrapper,
          .search-button-wrapper {
            margin-left: 0;
          }
          
          .search-button-wrapper {
            text-align: center;
            width: 100%;
          }
          
          .search-button-wrapper button {
            max-width: 100%;
          }
        }

        @media (max-width: 767px) {
          .entry-port-search-container {
            gap: 10px;
          }
          
          .location-row {
            flex-direction: column;
            gap: 15px;
          }
          
          .time-selection-wrapper,
          .date-selection-wrapper {
            width: 100%;
          }
          
          .controls-row {
            gap: 10px;
          }
        }
      `}</style>
    </div>
  );
};

export default EntryPortSearch; 