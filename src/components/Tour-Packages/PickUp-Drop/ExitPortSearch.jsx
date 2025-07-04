import React, { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import SearchBar from "./LocationSearch1";
import {
  fetchVehicles,
  setexitpickup,
  setexitdropoff,
  setpickupdate,
  setentrytime,
  setexittime,
  setSelectionType,
  setPickupPlaceid1,
  setDropoffPlaceid1,
  setPortZoneType,
} from "@/slice/port/pickupDropSlice";
import DateSearch2 from "@/components/activity-list/common/DateSearch2";
import Pickuptime1 from "@/components/activity-single/filter-box2/Pickuptime1";
import { Typography } from "@mui/material";

const ExitPortSearch = ({ Location }) => {
  const dispatch = useDispatch();
  
  // Log the location prop to debug
  console.log("ExitPortSearch Location prop:", Location);
  
  // Get values from Redux store to persist state
  const reduxExitPickUpLocation = useSelector((state) => state.pickupDrop.exitpickup || "");
  const reduxExitDropOffLocation = useSelector((state) => state.pickupDrop.exitdropoff || "");
  const reduxPickupDate = useSelector((state) => state.pickupDrop.pickupdate || "");
  const reduxExitTime = useSelector((state) => state.pickupDrop.exittime || "");
  const reduxPickUpLatLng = useSelector((state) => state.pickupDrop.PickupPlaceid1 || "");
  const reduxDropOffLatLng = useSelector((state) => state.pickupDrop.DropoffPlaceid1 || "");
  
  // Log Redux values for debugging
  console.log("Redux values in ExitPortSearch:", {
    reduxExitPickUpLocation,
    reduxExitDropOffLocation,
    reduxPickupDate,
    reduxExitTime,
    reduxPickUpLatLng,
    reduxDropOffLatLng
  });
  
  // State for storing the pickup and dropoff locations
  const [exitpickUpLocation, setexitPickUpLocation] = useState(reduxExitPickUpLocation);
  const [exitdropOffLocation, setexitDropOffLocation] = useState(reduxExitDropOffLocation);
  const [selectedDate1, setSelectedDate1] = useState(reduxPickupDate);
  const [pickUpLatLng, setPickupLatLng] = useState(reduxPickUpLatLng);
  const [dropOffLatLng, setDropoffLatLng] = useState(reduxDropOffLatLng);
  const [entryytime1, setentryytime1] = useState(reduxExitTime);
  const [validationTriggered, setValidationTriggered] = useState(false);
  const [exitPickupFromAutocomplete, setExitPickupFromAutocomplete] = useState(false);
  const [exitDropoffFromAutocomplete, setExitDropoffFromAutocomplete] = useState(false);
  const [time1, setTime1] = useState(false);

  // Update Redux store whenever local state changes
  useEffect(() => {
    if (exitpickUpLocation) {
      console.log("Updating exitpickup in Redux:", exitpickUpLocation);
      dispatch(setexitpickup(exitpickUpLocation));
    }
  }, [exitpickUpLocation, dispatch]);

  useEffect(() => {
    if (exitdropOffLocation) {
      console.log("Updating exitdropoff in Redux:", exitdropOffLocation);
      dispatch(setexitdropoff(exitdropOffLocation));
    }
  }, [exitdropOffLocation, dispatch]);

  useEffect(() => {
    if (selectedDate1) {
      dispatch(setpickupdate(selectedDate1));
    }
  }, [selectedDate1, dispatch]);

  useEffect(() => {
    if (entryytime1) {
      console.log("ExitPortSearch - Setting entry/exit time in Redux:", entryytime1);
      dispatch(setentrytime(entryytime1));
      dispatch(setexittime(entryytime1));
    }
  }, [entryytime1, dispatch]);

  useEffect(() => {
    if (pickUpLatLng) {
      console.log("Updating pickUpLatLng in Redux:", pickUpLatLng);
      dispatch(setPickupPlaceid1(pickUpLatLng));
    }
  }, [pickUpLatLng, dispatch]);

  useEffect(() => {
    if (dropOffLatLng) {
      console.log("Updating dropOffLatLng in Redux:", dropOffLatLng);
      dispatch(setDropoffPlaceid1(dropOffLatLng));
    }
  }, [dropOffLatLng, dispatch]);

  // Log Location prop to debug
  useEffect(() => {
    console.log("Exit Port Location:", Location);
  }, [Location]);

  // Monitor autocomplete state changes
  useEffect(() => {
    console.log("exitPickupFromAutocomplete changed:", exitPickupFromAutocomplete);
  }, [exitPickupFromAutocomplete]);
  
  useEffect(() => {
    console.log("exitDropoffFromAutocomplete changed:", exitDropoffFromAutocomplete);
  }, [exitDropoffFromAutocomplete]);
  
  // Set time1 to true whenever entryytime1 has a value
  useEffect(() => {
    if (entryytime1) {
      console.log("Setting time1 to true because entryytime1 has value:", entryytime1);
      setTime1(true);
    } else {
      setTime1(false);
    }
  }, [entryytime1]);

  // Handler for the button search click event
  const buttonsearch = () => {
    console.log("ExitPortSearch - Search button clicked");
    console.log("ExitPortSearch - Current state before search:", {
      exitpickUpLocation,
      exitdropOffLocation,
      selectedDate1,
      entryytime1,
      time1,
      exitPickupFromAutocomplete,
      exitDropoffFromAutocomplete,
      pickUpLatLng,
      dropOffLatLng
    });
    
    // IMPORTANT: Set selection type to Exit Port FIRST
    // This ensures the correct slice of the Redux store is updated
    dispatch(setSelectionType("Exit Port"));
    dispatch(setPortZoneType(""));
    
    // Set validation triggered to true when search button is clicked
    setValidationTriggered(true);
    
    // Update all Redux values
    dispatch(setexitpickup(exitpickUpLocation));
    dispatch(setexitdropoff(exitdropOffLocation));
    dispatch(setentrytime(entryytime1));
    dispatch(setexittime(entryytime1));
    dispatch(setpickupdate(selectedDate1));
    dispatch(setPickupPlaceid1(pickUpLatLng));
    dispatch(setDropoffPlaceid1(dropOffLatLng));
    
    // Only proceed if both locations are selected from autocomplete
    const locationsValid = exitPickupFromAutocomplete && exitDropoffFromAutocomplete;
    console.log("ExitPortSearch - Locations valid for search:", locationsValid, {
      exitPickupFromAutocomplete,
      exitDropoffFromAutocomplete
    });
    
    console.log("ExitPortSearch - Current time1 state:", time1, "Current entryytime1:", entryytime1);

    // Check if we have a time value but time1 state is false (possible state inconsistency)
    if (entryytime1 && !time1) {
      console.log("ExitPortSearch - Time value exists but time1 is false. Fixing state inconsistency.");
      setTime1(true);
    }

    // Only fetch vehicles if both locations are valid and time is selected
    if (locationsValid && (time1 || entryytime1)) {
      console.log("ExitPortSearch - All conditions met, fetching vehicles for Exit Port...");
      
      // Use setTimeout to ensure all Redux state updates have been processed
      setTimeout(() => {
        // Make sure selectionType is set to Exit Port before fetching
        dispatch(setSelectionType("Exit Port"));
        console.log("ExitPortSearch - Dispatching fetchVehicles with Exit Port selection type");
        dispatch(fetchVehicles());
      }, 300);
    } else {
      console.log("ExitPortSearch - Not fetching vehicles due to invalid data:", { 
        locationsValid, 
        time1, 
        exitPickupFromAutocomplete,
        exitDropoffFromAutocomplete,
        entryytime1
      });
      
      // If we have all required data but there might be a state inconsistency, try again
      if (locationsValid && entryytime1) {
        console.log("ExitPortSearch - We have all required data but possible state inconsistency. Trying again.");
        
        // Force set time1 to true
        setTime1(true);
        
        // Use setTimeout to ensure all state updates have been processed
        setTimeout(() => {
          console.log("ExitPortSearch - Retry: Dispatching fetchVehicles with Exit Port selection type");
          dispatch(setSelectionType("Exit Port"));
          dispatch(fetchVehicles());
        }, 500);
      }
    }
  };

  return (
    <div className="entry-port-search-container">
      {/* First row - Location Search */}
      <div className="search-row1 location-row">
        <SearchBar
          exitpickUpLocation={exitpickUpLocation}
          setexitPickUpLocation={setexitPickUpLocation}
          exitdropOffLocation={exitdropOffLocation}
          setexitDropOffLocation={setexitDropOffLocation}
          pickUpLatLng={pickUpLatLng}
          setPickupLatLng={setPickupLatLng}
          dropOffLatLng={dropOffLatLng}
          setDropoffLatLng={setDropoffLatLng}
          Location={Location}
          validationTriggered={validationTriggered}
          setPickupFromAutocomplete={setExitPickupFromAutocomplete}
          setDropoffFromAutocomplete={setExitDropoffFromAutocomplete}
        />
        <div className="time-selection-wrapper">
        <Typography variant="subtitle2" fontWeight={600} sx={{ mb: 1, color: 'text.primary' }}>
                Exit Time
              </Typography>
          <Pickuptime1
            entryytime={entryytime1}
            setentryytime={setentryytime1}
            setTime={setTime1}
          />
        </div>

        {/* Date Selection */}
        <div className="date-selection-wrapper">
          <h4 className="text-15 fw-500 ls-2 lh-16 mt-15">Exit Date</h4>
          <DateSearch2
            selectedDate1={selectedDate1}
            setSelectedDate1={setSelectedDate1}
          />
        </div>
      </div>

      {/* Search Button Row */}
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
          max-width: 250px;
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

export default ExitPortSearch; 