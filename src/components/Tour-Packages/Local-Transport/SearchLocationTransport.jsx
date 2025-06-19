import React, { useState, useEffect, useRef } from "react";
import { useDispatch } from "react-redux";

import LocationSearch from "./LocationSearch";
import {
  fetchVehicles,
  setentrypickup,
  setentrydropoff,
  setexitpickup,
  setentrytime,
  setentrytime1,
  //setexitdropoff,
  setpickupdate,
  setexitpickupdate,
  setSelectionType,
  setPickupPlaceid,
  setDropoffPlaceid,
  setpickdate,
  resetVehicles1,
  setSelectedPort,
  setDroptype,
  fetchZoneVehicles,
  setDropoffZoneid,
  setPickupZoneid,
  setZonetype,
} from "@/slice/localtour/Localslice";
import SearchBar1 from "./LocationSearch1";
import DateSearch1 from "@/components/activity-list/activity-list-v3/DateSearch1";
import DateSearch2 from "@/components/activity-list/activity-list-v3/DateSearch2";
import Pickuptime from "@/components/activity-single/filter-box3/Pickuptime";
import Pickuptime1 from "@/components/activity-single/filter-box4/Pickuptime1";
import { useSelector } from "react-redux";
import SearchZone from "@/components/Tour-Packages/Local-Transport/LocationZoneSearch";
import Pickuptimezone from "@/components/activity-list/activity-list-v3/Pickuptimezone";
import DateSearchZone from "@/components/activity-list/activity-list-v3/DateSearchZone";

const SearchLocationTransport = ({ Location }) => {
  const dispatch = useDispatch();

  // Get values from Redux store to persist state
  const reduxPickUpLocation = useSelector((state) => state.localtour.entrypickup || "");
  const reduxDropOffLocation = useSelector((state) => state.localtour.entrydropoff || "");
  const reduxExitPickUpLocation = useSelector((state) => state.localtour.exitpickup || "");
  const reduxPickupDate = useSelector((state) => state.localtour.pickupdate || "");
  const reduxPickupDate1 = useSelector((state) => state.localtour.exitpickupdate || "");
  const reduxPickUpLatLng = useSelector((state) => state.localtour.PickupPlaceid || "");
  const reduxDropOffLatLng = useSelector((state) => state.localtour.DropoffPlaceid || "");
  const reduxEntryTime = useSelector((state) => state.localtour.entrytime || "");
  const reduxEntryTime1 = useSelector((state) => state.localtour.entrytime1 || "");
  const reduxEntryTimeZone = useSelector((state) => state.localtour.entrytimezone || "");
  const reduxPickUpZone = useSelector((state) => state.localtour.PickupZoneid || "");
  const reduxDropOffZone = useSelector((state) => state.localtour.DropoffZoneid || "");

  // State for storing the pickup and dropoff locations
  const [pickUpLocation, setPickUpLocation] = useState(reduxPickUpLocation);
  const [pickUpZone, setPickUpZone] = useState(reduxPickUpZone);
  const [dropOffLocation, setDropOffLocation] = useState(reduxDropOffLocation);
  const [dropOffzone, setDropOffZone] = useState(reduxDropOffZone);
  const [exitpickUpLocation, setexitPickUpLocation] = useState(reduxExitPickUpLocation);
  const [selectedDate, setSelectedDate] = useState(reduxPickupDate);
  const [selectedDate1, setSelectedDate1] = useState(reduxPickupDate1);
  const [selectedDateZone, setSelectedDateZone] = useState(reduxPickupDate || "");
  const selectedPort = useSelector((state) => state.localtour.selectedPort);
  const [pickUpLatLng, setPickupLatLng] = useState(reduxPickUpLatLng);
  const [dropOffLatLng, setDropoffLatLng] = useState(reduxDropOffLatLng);
  const [entryytime, setentryytime] = useState(reduxEntryTime);
  const [entryytime1, setentryytime1] = useState(reduxEntryTime1);
  const [entryytimezone, setentryytimezone] = useState(reduxEntryTimeZone);
  const zone_on = useSelector((state) => state.auth.zone_on);

  // Log Redux date values when component mounts
  useEffect(() => {
    console.log("Initial Redux date values:", { 
      reduxPickupDate, 
      reduxPickupDate1,
      selectedDate,
      selectedDate1,
      selectedDateZone
    });
  }, []);

  // Add state for validation
  const [validationTriggered, setValidationTriggered] = useState(false);

  // Track if locations were selected from autocomplete
  const [pickupFromAutocomplete, setPickupFromAutocomplete] = useState(false);
  const [dropoffFromAutocomplete, setDropoffFromAutocomplete] = useState(false);
  const [exitPickupFromAutocomplete, setExitPickupFromAutocomplete] =
    useState(false);

  const currentbooking = useSelector((state) => state.localtour.selectbooking);
  const picktype = useSelector((state) => state.localtour.picktype);
  const reduxDropType = useSelector((state) => state.localtour.droptype || "");
  const [droptype, setdroptype] = useState(reduxDropType);
  // Track if time is selected
  const [time, setTime] = useState(!!reduxEntryTime);
  const [time1, setTime1] = useState(!!reduxEntryTime1);
  const [timezone, setTimezone] = useState(!!reduxEntryTimeZone);
  const viewDetails = useSelector((state) => state.viewDetails.bookings);

  // Add imports for useRef
  const prevPickUpLocationRef = useRef(reduxPickUpLocation);
  const prevDropOffLocationRef = useRef(reduxDropOffLocation);
  const prevPickUpLatLngRef = useRef(reduxPickUpLatLng);
  const prevDropOffLatLngRef = useRef(reduxDropOffLatLng);
  const prevExitPickUpLocationRef = useRef(reduxExitPickUpLocation);
  const prevSelectedDateRef = useRef(reduxPickupDate);
  const prevSelectedDate1Ref = useRef(reduxPickupDate1);
  const prevSelectedDateZoneRef = useRef(reduxPickupDate || "");
  
  // Sync Redux state with local state when the component mounts or Redux values change
  useEffect(() => {
    // Use refs to track if we're in the middle of a sync operation
    const isInitialSync = !pickUpLocation && !dropOffLocation;
    
    if (reduxPickUpLocation && (isInitialSync || reduxPickUpLocation !== pickUpLocation)) {
      console.log("Syncing from Redux: pickUpLocation", reduxPickUpLocation);
      setPickUpLocation(reduxPickUpLocation);
    }
    
    if (reduxDropOffLocation && (isInitialSync || reduxDropOffLocation !== dropOffLocation)) {
      console.log("Syncing from Redux: dropOffLocation", reduxDropOffLocation);
      setDropOffLocation(reduxDropOffLocation);
    }

    // Also sync lat/lng values from Redux
    if (reduxPickUpLatLng && Object.keys(reduxPickUpLatLng).length > 0 && 
        (!pickUpLatLng || 
          pickUpLatLng.lat !== reduxPickUpLatLng.lat || 
          pickUpLatLng.lng !== reduxPickUpLatLng.lng)) {
      console.log("Syncing from Redux: pickUpLatLng", reduxPickUpLatLng);
      setPickupLatLng(reduxPickUpLatLng);
    }
    
    if (reduxDropOffLatLng && Object.keys(reduxDropOffLatLng).length > 0 && 
        (!dropOffLatLng || 
          dropOffLatLng.lat !== reduxDropOffLatLng.lat || 
          dropOffLatLng.lng !== reduxDropOffLatLng.lng)) {
      console.log("Syncing from Redux: dropOffLatLng", reduxDropOffLatLng);
      setDropoffLatLng(reduxDropOffLatLng);
    }
  }, [reduxPickUpLocation, reduxDropOffLocation, reduxPickUpLatLng, reduxDropOffLatLng]);

  useEffect(() => {
    const isInitialSync = !exitpickUpLocation;
    if(exitpickUpLocation && (isInitialSync || exitpickUpLocation !== reduxExitPickUpLocation)){
      console.log("Syncing from Redux: exitpickUpLocation", exitpickUpLocation);
      setexitPickUpLocation(reduxExitPickUpLocation);
    }
  }, [reduxExitPickUpLocation]);

  // Sync date values from Redux
  useEffect(() => {
    if (reduxPickupDate && reduxPickupDate !== prevSelectedDateRef.current) {
      console.log("Syncing from Redux: pickupDate", reduxPickupDate);
      setSelectedDate(reduxPickupDate);
      prevSelectedDateRef.current = reduxPickupDate;
    }
  }, [reduxPickupDate]);

  useEffect(() => {
    if (reduxPickupDate1 && reduxPickupDate1 !== prevSelectedDate1Ref.current) {
      console.log("Syncing from Redux: exitPickupDate", reduxPickupDate1);
      setSelectedDate1(reduxPickupDate1);
      prevSelectedDate1Ref.current = reduxPickupDate1;
    }
  }, [reduxPickupDate1]);

  // Update Redux state when local state changes
  useEffect(() => {
    // Only dispatch if the value has actually changed from what's in Redux
    if (pickUpLocation && pickUpLocation !== prevPickUpLocationRef.current) {
      prevPickUpLocationRef.current = pickUpLocation;
      dispatch(setentrypickup(pickUpLocation));
    }
  }, [pickUpLocation, dispatch]);
  
  useEffect(() => {
    if (exitpickUpLocation && exitpickUpLocation !== prevExitPickUpLocationRef.current) {
      prevExitPickUpLocationRef.current = exitpickUpLocation;
      dispatch(setexitpickup(exitpickUpLocation));
    }
  }, [exitpickUpLocation, dispatch]);
  
  useEffect(() => {
    // Only dispatch if the value has actually changed from what's in Redux
    if (dropOffLocation && dropOffLocation !== prevDropOffLocationRef.current) {
      prevDropOffLocationRef.current = dropOffLocation;
      dispatch(setentrydropoff(dropOffLocation));
    }
  }, [dropOffLocation, dispatch]);

  // Update Redux state when date values change
  useEffect(() => {
    if (selectedDate && selectedDate !== prevSelectedDateRef.current) {
      prevSelectedDateRef.current = selectedDate;
      console.log("Dispatching pickupdate to Redux:", selectedDate);
      dispatch(setpickdate(selectedDate));
    }
  }, [selectedDate, dispatch]);

  useEffect(() => {
    if (selectedDate1 && selectedDate1 !== prevSelectedDate1Ref.current) {
      prevSelectedDate1Ref.current = selectedDate1;
      console.log("Dispatching exitpickupdate to Redux:", selectedDate1);
      dispatch(setexitpickupdate(selectedDate1));
    }
  }, [selectedDate1, dispatch]);

  useEffect(() => {
    if (selectedDateZone && selectedDateZone !== prevSelectedDateZoneRef.current) {
      prevSelectedDateZoneRef.current = selectedDateZone;
      console.log("Dispatching zone date to Redux:", selectedDateZone);
      dispatch(setpickdate(selectedDateZone));
    }
  }, [selectedDateZone, dispatch]);
  
  // Update Redux state when lat/lng values change
  useEffect(() => {
    if (pickUpLatLng && Object.keys(pickUpLatLng).length > 0 && 
        (!prevPickUpLatLngRef.current ||
         prevPickUpLatLngRef.current.lat !== pickUpLatLng.lat ||
         prevPickUpLatLngRef.current.lng !== pickUpLatLng.lng)) {
      
      prevPickUpLatLngRef.current = pickUpLatLng;
      const pickUpLatLng1 = {
        lat: pickUpLatLng.lat,
        lng: pickUpLatLng.lng
      }
      dispatch(setPickupPlaceid(pickUpLatLng1));
    }
  }, [pickUpLatLng, dispatch]);
  
  useEffect(() => {
    if (dropOffLatLng && Object.keys(dropOffLatLng).length > 0 && 
        (!prevDropOffLatLngRef.current ||
         prevDropOffLatLngRef.current.lat !== dropOffLatLng.lat ||
         prevDropOffLatLngRef.current.lng !== dropOffLatLng.lng)) {
      
      prevDropOffLatLngRef.current = dropOffLatLng;
      const dropOffLatLng1 = {
        lat: dropOffLatLng.lat,
        lng: dropOffLatLng.lng
      }
      dispatch(setDropoffPlaceid(dropOffLatLng1));
    }
  }, [dropOffLatLng, dispatch]);
  
  // Custom handler for pickup location change that updates both name and latlng in Redux
  const handleLocationChange = () => {
    // Only update Redux if we have valid data that differs from what's already in Redux
    if (pickUpLocation && pickUpLatLng && 
        (pickUpLocation !== prevPickUpLocationRef.current || 
         !prevPickUpLatLngRef.current || 
         prevPickUpLatLngRef.current.lat !== pickUpLatLng.lat || 
         prevPickUpLatLngRef.current.lng !== pickUpLatLng.lng)) {
      
      prevPickUpLocationRef.current = pickUpLocation;
      prevPickUpLatLngRef.current = pickUpLatLng;
      
      dispatch(setentrypickup(pickUpLocation));

      const pickUpLatLng1 = {
        lat: pickUpLatLng.lat,
        lng: pickUpLatLng.lng
      } 
      dispatch(setPickupPlaceid(pickUpLatLng1));
    }
    if (exitpickUpLocation && pickUpLatLng && 
        (exitpickUpLocation !== prevExitPickUpLocationRef.current || 
         !prevExitPickUpLocationRef.current || 
         prevExitPickUpLocationRef.current.lat !== pickUpLatLng.lat || 
         prevExitPickUpLocationRef.current.lng !== pickUpLatLng.lng)) {
      prevExitPickUpLocationRef.current = exitpickUpLocation;
      dispatch(setexitpickup(exitpickUpLocation));
    }
    
    if (dropOffLocation && dropOffLatLng && 
        (dropOffLocation !== prevDropOffLocationRef.current || 
         !prevDropOffLatLngRef.current ||
         prevDropOffLatLngRef.current.lat !== dropOffLatLng.lat || 
         prevDropOffLatLngRef.current.lng !== dropOffLatLng.lng)) {
      
      prevDropOffLocationRef.current = dropOffLocation;
      prevDropOffLatLngRef.current = dropOffLatLng;
      
      dispatch(setentrydropoff(dropOffLocation));
      const dropOffLatLng1 = {
        lat: dropOffLatLng.lat,
        lng: dropOffLatLng.lng
      }
      dispatch(setDropoffPlaceid(dropOffLatLng1));
    }
  };
  
  // Ensure location values are persisted when Pickuptime changes
  const handleTimeSelection = (value) => {
    // Use conditional statements to update only the relevant time state
    if(selectedPort === "Point To Point"){
      setentryytime(value);
    }
    else if(selectedPort === "Hourly"){
      setentryytime1(value);
    }
    else if(selectedPort === "Local Transfer"){
      setentryytimezone(value);
    }
    
    // No need to manually trigger handleLocationChange here
    // Let the state update naturally when the time is selected
  };
  
  const handleDateSelection = (date) => {
    // Check if date is a Moment object and convert to string
    if (date && date._isAMomentObject) {
      return date.format('YYYY-MM-DD');
    }
    return date;
  };
  
  const buttonsearch = () => {
    // Set validation triggered to true when search button is clicked
    setValidationTriggered(true);

    if (selectedPort === "Point To Point") {
      // Only proceed if both locations are selected from autocomplete
      const locationsValid = pickupFromAutocomplete && dropoffFromAutocomplete;
      
      console.log("Search conditions:", { 
        pickupFromAutocomplete, 
        dropoffFromAutocomplete,
        locationsValid,
        time,
        "Will call fetchVehicles": locationsValid && time
      });

      // Ensure the date is properly formatted
      const formattedDate = handleDateSelection(selectedDate);
      console.log("Point To Point search with date:", formattedDate);
      
      dispatch(setentrypickup(pickUpLocation));
      dispatch(setentrydropoff(dropOffLocation));
      dispatch(setentrytime(entryytime));
      dispatch(setpickdate(formattedDate));
      dispatch(setSelectionType(selectedPort));
      
      // Check if pickUpLatLng and dropOffLatLng have valid values
      if (!pickUpLatLng || !pickUpLatLng.lat || !pickUpLatLng.lng) {
        console.error("Invalid pickup location coordinates. Please select a location from the dropdown.");
        // Force pickupFromAutocomplete to false to show validation message
        setPickupFromAutocomplete(false);
        return;
      }
      
      if (!dropOffLatLng || !dropOffLatLng.lat || !dropOffLatLng.lng) {
        console.error("Invalid dropoff location coordinates. Please select a location from the dropdown.");
        // Force dropoffFromAutocomplete to false to show validation message
        setDropoffFromAutocomplete(false);
        return;
      }
      
      // If we have valid coordinates, consider the locations as valid from autocomplete
      setPickupFromAutocomplete(true);
      setDropoffFromAutocomplete(true);
      
      console.log("Search button clicked - lat/lng values:", {
        pickUpLatLng,
        dropOffLatLng
      });
      
      const pickUpLatLng1 = {
        lat: pickUpLatLng.lat,
        lng: pickUpLatLng.lng
      };
      
      const dropOffLatLng1 = {
        lat: dropOffLatLng.lat,
        lng: dropOffLatLng.lng
      };
      
      dispatch(setPickupPlaceid(pickUpLatLng1));
      dispatch(setDropoffPlaceid(dropOffLatLng1));
      dispatch(setZonetype(""));

      // Check if time is selected and valid
      if (!entryytime) {
        console.error("Please select a pickup time");
        setTime(false);
        return;
      } else {
        setTime(true);
      }

      // Only fetch vehicles if locations and time are valid
      if (pickUpLatLng && dropOffLatLng && entryytime) {
        console.log("All conditions met, fetching vehicles...");
        setTimeout(() => {
          dispatch(fetchVehicles());
        }, 500);
      } else {
        console.error("Cannot fetch vehicles: missing required fields", {
          pickUpLatLng: !!pickUpLatLng,
          dropOffLatLng: !!dropOffLatLng,
          entryytime: !!entryytime
        });
      }
    } else if (selectedPort === "Hourly") {
      // Only proceed if pickup location is selected from autocomplete
      const locationValid = exitPickupFromAutocomplete;

      // Ensure the date is properly formatted
      const formattedDate = handleDateSelection(selectedDate1);
      console.log("Hourly search with date:", formattedDate);

      console.log("Hourly Search conditions:", { 
        exitPickupFromAutocomplete,
        locationValid,
        time1,
        pickUpLatLng,
        "Will call fetchVehicles": locationValid && time1
      });

      dispatch(setexitpickup(exitpickUpLocation));
      dispatch(setpickdate(formattedDate));
      dispatch(setexitpickupdate(formattedDate));
      dispatch(setentrytime(entryytime1));
      dispatch(setentrytime1(entryytime1));
      dispatch(setSelectionType(selectedPort));
      
      // Check if pickUpLatLng has valid values
      if (!pickUpLatLng || !pickUpLatLng.lat || !pickUpLatLng.lng) {
        console.error("Invalid pickup location coordinates for Hourly mode. Please select a location from the dropdown.");
        setExitPickupFromAutocomplete(false);
        return;
      }
      
      // If we have valid coordinates, consider the location as valid from autocomplete
      setExitPickupFromAutocomplete(true);
      
      const pickUpLatLng1 = {
        lat: pickUpLatLng.lat,
        lng: pickUpLatLng.lng
      };
      
      dispatch(setPickupPlaceid(pickUpLatLng1));
      dispatch(setDropoffPlaceid(null));
      dispatch(setZonetype(""));

      // Check if time is selected and valid
      if (!entryytime1) {
        console.error("Please select a pickup time for Hourly mode");
        setTime1(false);
        return;
      } else {
        setTime1(true);
      }

      // Only fetch vehicles if location and time are valid
      if (pickUpLatLng && pickUpLatLng.lat && pickUpLatLng.lng && entryytime1) {
        console.log("All Hourly conditions met, fetching vehicles...");
        setTimeout(() => {
          dispatch(fetchVehicles());
        }, 500);
      } else {
        console.error("Cannot fetch vehicles for Hourly mode: missing required fields", {
          pickUpLatLng: !!pickUpLatLng,
          "pickUpLatLng.lat": pickUpLatLng?.lat,
          "pickUpLatLng.lng": pickUpLatLng?.lng,
          entryytime1: !!entryytime1
        });
      }
    } else if (selectedPort === "Local Transfer") {
      // Only proceed if both locations are selected from autocomplete
      // Ensure the date is properly formatted
      const formattedDate = handleDateSelection(selectedDateZone);
      console.log("Local Transfer search with date:", formattedDate);
      
      dispatch(setPickupZoneid(pickUpZone));
      dispatch(setDropoffZoneid(dropOffzone));
      dispatch(setentrypickup(pickUpLatLng));
      dispatch(setentrydropoff(dropOffLatLng));
      dispatch(setSelectionType(selectedPort));
      dispatch(setDroptype(droptype));
      dispatch(setentrytime(entryytimezone));
      dispatch(setpickdate(formattedDate));
      dispatch(setZonetype("zone"));

      // Check if we have valid values for the API call
      if (
        pickUpZone &&
        dropOffzone &&
        entryytimezone &&
        selectedDateZone &&
        droptype
      ) {
        console.log("Local Transfer search with droptype:", droptype);
        setTimeout(() => {
          dispatch(fetchZoneVehicles());
        }, 500);
      } else {
        console.log("Missing required fields for Local Transfer search:", {
          pickUpLocation,
          dropOffLocation,
          entryytimezone,
          selectedDateZone,
          droptype,
        });
      }
    }
  };

  return (
    <>
      <div className="mainSearch -col-2 bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-4 mt-30">
        <div className={`button-grid-v2 items-center ${selectedPort === "Point To Point" ? "point-to-point-layout" : ""}`}>
          {/* First Section - Port Selection */}
          <div className="port-selection-wrapper">
            <label className="text-15 fw-500 ls-2 lh-16">
              Select Journey Type:
            </label>
            <select
              className="form-control"
              value={selectedPort}
              onChange={(e) => {
                dispatch(setSelectedPort(e.target.value));
                dispatch(resetVehicles1()); // Reset vehicles when port type changes
                setValidationTriggered(false); // Reset validation when port type changes
              }}
            >
              {selectedPort === "" && (
                <option value="" disabled>
                  Select the type..
                </option>
              )}
              <option value="Point To Point">Point To Point</option>
              <option value="Hourly">Hourly</option>
              {(zone_on === 1 && 
                // viewDetails && 
                // (viewDetails.hotel?.length > 0 || 
                //  viewDetails.attraction?.length > 0 || 
                //  viewDetails.restaurant?.length > 0)) && (
                <option value="Local Transfer">Local Transfer</option>
              )}
            </select>
          </div>

          {/* Second Section - Location Search */}
          <div className={`location-search-wrapper ${selectedPort === "Point To Point" ? "location-full-width" : ""}`}>
            {selectedPort === "Point To Point" ? (
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
                pickupFromAutocomplete={pickupFromAutocomplete}
                dropoffFromAutocomplete={dropoffFromAutocomplete}
              />
            ) : selectedPort === "Hourly" ? (
              <SearchBar1
                exitpickUpLocation={exitpickUpLocation}
                setexitPickUpLocation={setexitPickUpLocation}
                pickUpLatLng={pickUpLatLng}
                setPickupLatLng={setPickupLatLng}
                Location={Location}
                validationTriggered={validationTriggered}
                setPickupFromAutocomplete={setExitPickupFromAutocomplete}
                pickupFromAutocomplete={exitPickupFromAutocomplete}
              />
            ) : selectedPort === "Local Transfer" ? (
              <SearchZone
                currentbooking={currentbooking}
                picktype={picktype}
                setdroptype={setdroptype}
                droptype={droptype}
                setPickUpLocation={setPickUpZone}
                setPickupLatLng={setPickupLatLng}
                setDropoffLatLng={setDropoffLatLng}
                dropOffLocation={dropOffLatLng}
                validationTriggered={validationTriggered}
                setDropOffLocation={setDropOffZone}
              />
            ) : (
              <div className="text-center p-20">
                <p>Please select a journey type first</p>
              </div>
            )}
          </div>

          {/* Third Section - Time Selection */}
          <div className="time-selection-wrapper">
            {selectedPort === "Point To Point" ? (
              <Pickuptime
                entryytime={entryytime}
                setentryytime={handleTimeSelection}
                setTime={setTime}
              />
            ) : selectedPort === "Hourly" ? (
              <Pickuptime1
                entryytime={entryytime1}
                setentryytime={handleTimeSelection}
                setTime={setTime1}
              />
            ) : (
              <Pickuptimezone
                entryytime={entryytimezone}
                setentryytime={setentryytimezone}
                setTime={setTimezone}
              />
            )}
          </div>

          {/* Fourth Section - Date Selection */}
          <div className={`date-selection-wrapper ${selectedPort === "Point To Point" ? "date-selection-wrapper-point-to-point" : ""}`}>
            <h4 className="text-15 fw-500 ls-2 lh-16 mt-15">
              {selectedPort === "Point To Point" ? "Pick Up Date" : "Exit Date"}
            </h4>
            {selectedPort === "Point To Point" ? (
              <DateSearch1
                selectedDate={selectedDate}
                setSelectedDate={(date) => {
                  console.log("Selected Pickup Date:", date);
                  // Check if date is a moment object and convert to string if needed
                  if (date && date._isAMomentObject) {
                    const formattedDate = date.format('YYYY-MM-DD');
                    console.log("Formatted date:", formattedDate);
                    setSelectedDate(formattedDate);
                    // Directly update Redux to ensure it's set immediately
                    dispatch(setpickdate(formattedDate));
                  } else {
                    setSelectedDate(date);
                    dispatch(setpickdate(date));
                  }
                  // Also ensure location data is preserved when date changes
                  handleLocationChange();
                }}
              />
            ) : selectedPort === "Local Transfer" ? (
              <DateSearchZone
                selectedDate1={selectedDateZone}
                setSelectedDate1={(date) => {
                  // Check if date is a moment object and convert to string
                  if (date && date._isAMomentObject) {
                    const formattedDate = date.format('YYYY-MM-DD');
                    console.log("Formatted zone date:", formattedDate);
                    setSelectedDateZone(formattedDate);
                    // Directly update Redux
                    dispatch(setpickdate(formattedDate));
                  } else {
                    setSelectedDateZone(date);
                    dispatch(setpickdate(date));
                  }
                  handleLocationChange();
                }}
              />
            ) : (
              <DateSearch2
                selectedDate1={selectedDate1}
                setSelectedDate1={(date) => {
                  // Check if date is a moment object and convert to string
                  if (date && date._isAMomentObject) {
                    const formattedDate = date.format('YYYY-MM-DD');
                    console.log("Formatted exit date:", formattedDate);
                    setSelectedDate1(formattedDate);
                    // Directly update Redux
                    dispatch(setexitpickupdate(formattedDate));
                    dispatch(setpickdate(formattedDate));
                  } else {
                    setSelectedDate1(date);
                    dispatch(setexitpickupdate(date));
                    dispatch(setpickdate(date));
                  }
                  handleLocationChange();
                }}
              />
            )}
          </div>

          {/* Fifth Section - Search Button */}
          <div className={`search-button-wrapper ${selectedPort === "Point To Point" ? "search-button-full-width" : ""}`}>
            <button
              className="mainSearch__submit button -dark-1 py-15 px-35 rounded-4 bg-blue-1 text-white"
              onClick={buttonsearch}
            >
              <i className="icon-search text-20 mr-10" />
              Search
            </button>
          </div>
        </div>
      </div>

      <style jsx>{`
        .button-grid-v2 {
          display: grid;
          grid-template-columns:
            160px minmax(300px, ${selectedPort === "Hourly" ? "3fr" : "4fr"})
            ${selectedPort === "Hourly" ? "2fr" : "1fr"} minmax(140px, 1fr) minmax(120px, 1fr);
          gap: 5px;
          align-items: center;
          width: 100%;
          max-width: 1730px;
        }

        /* Special layout for Point to Point */
        .point-to-point-layout {
          grid-template-columns: 160px 1fr;
          grid-template-rows: auto auto auto;
          gap: 15px;
        }

        .point-to-point-layout .port-selection-wrapper {
          grid-row: 1;
          grid-column: 1;
        }

        .point-to-point-layout .location-full-width {
          grid-row: 1;
          grid-column: 2;
        }

        .point-to-point-layout .time-selection-wrapper {
          grid-row: 2;
          grid-column: 1;
        }

        .point-to-point-layout .date-selection-wrapper {
          grid-row: 2;
          grid-column: 2;
        }

        .point-to-point-layout .search-button-full-width {
          grid-row: 2;
          grid-column: 1 / span 2;
          display: flex;
          justify-content: center;
          margin-top: 10px;
        }
        .point-to-point-layout .date-selection-wrapper{
          width: 20%;
        }

        .port-selection-wrapper {
          width: 100%;
          max-width: 160px;
        }

        .port-selection-wrapper select {
          width: 100%;
          padding: 8px;
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
          .button-grid-v2:not(.point-to-point-layout) {
            grid-template-columns: 1fr 1fr;
            gap: 20px;
          }

          .port-selection-wrapper {
            max-width: 100%;
          }

          .button-grid-v2:not(.point-to-point-layout) .location-search-wrapper {
            grid-column: span 2;
          }

          .button-grid-v2:not(.point-to-point-layout) .search-button-wrapper {
            grid-column: span 2;
            text-align: center;
          }
        }

        @media (max-width: 767px) {
          .button-grid-v2, .point-to-point-layout {
            grid-template-columns: 1fr !important;
            grid-template-rows: auto !important;
            gap: 15px;
          }

          .point-to-point-layout .port-selection-wrapper,
          .point-to-point-layout .location-full-width,
          .point-to-point-layout .time-selection-wrapper,
          .point-to-point-layout .date-selection-wrapper,
          .point-to-point-layout .search-button-full-width {
            grid-column: 1 !important;
            grid-row: auto !important;
          }

          .location-search-wrapper,
          .search-button-wrapper {
            grid-column: span 1;
          }

          .port-selection-wrapper,
          .time-selection-wrapper,
          .date-selection-wrapper {
            margin-bottom: 10px;
          }

          .search-button-wrapper button {
            max-width: 100%;
          }
        }

        @media (max-width: 480px) {
          .mainSearch {
            padding: 10px !important;
          }

          .button-grid-v2 {
            gap: 10px;
          }

          .text-15 {
            font-size: 14px;
          }
        }
      `}</style>
    </>
  );
}; 

export default SearchLocationTransport;
