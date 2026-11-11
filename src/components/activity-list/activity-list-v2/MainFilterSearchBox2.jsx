import React, { useState, useEffect, useCallback, useMemo, useRef } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  selectCityWiseDates,
  setSelectedCity as setCommonSelectedCity,
} from "@/slice/common/commonSlice";
import {
  buildCityChipItems,
  buildLocationFromCityName,
  findCityDatesEntry,
  normalizeYmd,
} from "@/utils/cityWiseDates";
import { toCityOnly } from "@/utils/locationFormat";
import LocationSearch from "./PortLocation";
import { clearTriggerSearch } from "@/slice/common/stepsSlice";
import {
  setentrypickup,
  setentrydropoff,
  setexitpickup,
  setexitdropoff,
  setpickupdate,
  setentrytime,
  setexittime,
  setSelectionType,
  setPickupPlaceid,
  setDropoffPlaceid,
  resetVehicles,
  setSelectedPort,
  fetchPortCity,
  fetchLocalZone,
  setPicktype,
  setDroptype,
  fetchZoneVehicles,
  setPortZoneType,
  setPickupPlaceid1,
  setDropoffPlaceid1,
} from "@/slice/port/pickupDropSlice";
import { Typography } from "@mui/material";
import DateSearch1 from "../common/DateSearch1";
import SearchBar from "../activity-list-v2/PortLocation2";
import DateSearch2 from "../common/DateSearch2";
import Pickuptime from "@/components/activity-single/filter-box1/Pickuptime";
import Pickuptime1 from "@/components/activity-single/filter-box2/Pickuptime1";
import PortCity from "./PortCity";
import CityServiceChips from "@/components/common/CityServiceChips";

const MainFilterSearchBox2 = ({ Location }) => {
  console.log('🔍 Port MainFilterSearchBox2 - Component rendered/mounted');
  const dispatch = useDispatch();

  // State for storing the pickup and dropoff locations
  const [pickUpLocation, setPickUpLocation] = useState("");
  const [dropOffLocation, setDropOffLocation] = useState("");
  const [exitpickUpLocation, setexitPickUpLocation] = useState("");
  const [exitdropOffLocation, setexitDropOffLocation] = useState("");
  const [selectedDate, setSelectedDate] = useState("");
  const [selectedDate1, setSelectedDate1] = useState("");
  //const [selectedPort, setSelectedPort] = useState("Entry Port"); // Default selection
  const selectedPort = useSelector((state) => state.pickupDrop.selectedPort);
  const TourId = useSelector((state) => state.hotels.id);
  const tourDestination = useSelector(
    (state) => state.hotels.tourdetails?.destination
  );
  console.log(tourDestination, "tourDestination");
  const searchLocation = useSelector((state) => state.bookings?.searchLocation);
  console.log(searchLocation, "searchLocation");
  const userCountry = useSelector((state) => state.auth?.user_country);
  const cityWiseDates = useSelector(selectCityWiseDates);
  const cityList = useSelector((state) => state.city?.city || []);
  const hasCityWiseDates =
    Array.isArray(cityWiseDates) && cityWiseDates.length > 0;
  const hasAutoSelectedCity = useRef(false);

  // Check port city API status
  const portCityStatus = useSelector(
    (state) => state.pickupDrop.portCityStatus
  );
  const portCityData = useSelector((state) => state.pickupDrop.portCityData);

  // Check local zone API status
  const localZoneStatus = useSelector(
    (state) => state.pickupDrop.localZoneStatus
  );

  const [pickUpLatLng, setPickupLatLng] = useState(""); // New state for pick-up place_id
  const [dropOffLatLng, setDropoffLatLng] = useState(""); // New state for drop-off place_id
  const [entryytime, setentryytime] = useState("");
  const [entryytime1, setentryytime1] = useState("");

  // Add state for validation
  const [validationTriggered, setValidationTriggered] = useState(false);
  const [cityError, setCityError] = useState(false);
  const [type, setType] = useState("");
  const [pickdropType, setPickdropType] = useState("");
 

  const [id, setId] = useState("");
  // Track if locations were selected from autocomplete
  const [pickupFromAutocomplete, setPickupFromAutocomplete] = useState(false);
  const [dropoffFromAutocomplete, setDropoffFromAutocomplete] = useState(false);
  const [exitPickupFromAutocomplete, setExitPickupFromAutocomplete] =
    useState(false);
  const [exitDropoffFromAutocomplete, setExitDropoffFromAutocomplete] =
    useState(false);
  const [listType, setlistType] = useState("");
  const [time, setTime] = useState(false);
  const [time1, setTime1] = useState(false);
  const [pickid, setpickId] = useState("");
 
  const [dropid, setdropId] = useState("");

  // State for selected city
  const [selectedCity, setSelectedCity] = useState(null);

  const country = useMemo(() => {
    const cityName = selectedCity?.name || selectedCity?.city || "";
    if (cityName && Array.isArray(tourDestination)) {
      const match = tourDestination.find(
        (entry) =>
          entry?.city &&
          String(entry.city).toLowerCase() === String(cityName).toLowerCase()
      );
      if (match?.country) return match.country;
    }

    if (typeof tourDestination === "string" && tourDestination) {
      return tourDestination;
    }

    if (Array.isArray(searchLocation)) {
      return searchLocation
        .map((loc) => {
          const match = (userCountry || []).find(
            (c) =>
              c?.code === loc ||
              c?.code?.toLowerCase() === String(loc).toLowerCase() ||
              c?.name === loc
          );
          return match?.name || loc;
        })
        .join(", ");
    }

    return "";
  }, [selectedCity, tourDestination, searchLocation, userCountry]);

  // Sequential enabling states
  const [isCityEnabled, setIsCityEnabled] = useState(false);
  const [isPickupLocationEnabled, setIsPickupLocationEnabled] = useState(false);
  const [isDropoffLocationEnabled, setIsDropoffLocationEnabled] =
    useState(false);
  const [isSearchButtonEnabled, setIsSearchButtonEnabled] = useState(false);

  useEffect(() => {
    if (selectedPort === "Entry Port") {
      setlistType("port");
    } else {
      setlistType("hotel");
    }
  }, [selectedPort, setlistType]);

  // Enable City field when Port Type is selected
  useEffect(() => {
    setIsCityEnabled(!!selectedPort);

    // Reset dependent fields when port type changes
    if (!selectedPort) {
      setSelectedCity(null);
      setIsPickupLocationEnabled(false);
      setIsDropoffLocationEnabled(false);
    }
  }, [selectedPort]);

  const clearLocationFields = useCallback(() => {
    setPickUpLocation("");
    setexitPickUpLocation("");
    setDropOffLocation("");
    setexitDropOffLocation("");
    setPickupFromAutocomplete(false);
    setDropoffFromAutocomplete(false);
    setExitPickupFromAutocomplete(false);
    setExitDropoffFromAutocomplete(false);
    setIsDropoffLocationEnabled(false);
    setpickId("");
    setdropId("");
    setId("");
    setType("");
    setPickdropType("");
  }, []);

  const applyCitySelection = useCallback(
    (location) => {
      if (!location) {
        setSelectedCity(null);
        return;
      }

      setSelectedCity(location);
      setCityError(false);
      dispatch(
        setCommonSelectedCity({
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
    [dispatch]
  );

  // Effect to call fetchPortCity when a city is selected
  useEffect(() => {
    if (selectedCity && listType) {
      

      dispatch(
        fetchPortCity({
          country: country,
          city: selectedCity.name,
          type: listType,
        })
      )
        .then((result) => {
         
          if (result.error) {
            console.error("API Error:", result.error);
            setIsPickupLocationEnabled(false);
          } else {
            // Enable pickup location after successful city API call
            setIsPickupLocationEnabled(true);
          }
        })
        .catch((error) => {
          console.error("Error dispatching fetchPortCity:", error);
          setIsPickupLocationEnabled(false);
        });
    } else {
     
      
      setIsPickupLocationEnabled(false);
    }
  }, [selectedCity, TourId, dispatch]);

  useEffect(() => {
    if (selectedPort === "Entry Port") {
      if (pickUpLocation && type && id) {
        // Ensure TourId is passed correctly as a number
        dispatch(
          fetchLocalZone({
            id: id,
            type: type,
          })
        )
          .then((result) => {
            
            if (result.error) {
              console.error("API Error:", result.error);
              setIsDropoffLocationEnabled(false);
            } else {
              // Enable dropoff location after successful pickup API call
              setIsDropoffLocationEnabled(true);
            }
          })
          .catch((error) => {
            console.error("Error dispatching fetchLocalzone:", error);
            setIsDropoffLocationEnabled(false);
          });
      } else {
       
        
        setIsDropoffLocationEnabled(false);
      }
    } else {
      if (exitpickUpLocation && type && id) {
        // Ensure TourId is passed correctly as a number
        dispatch(
          fetchLocalZone({
            id: id,
            type: type,
          })
        )
          .then((result) => {
           
            if (result.error) {
              console.error("API Error:", result.error);
              setIsDropoffLocationEnabled(false);
            } else {
              // Enable dropoff location after successful pickup API call
              setIsDropoffLocationEnabled(true);
            }
          })
          .catch((error) => {
            console.error("Error dispatching fetchLocalzone:", error);
            setIsDropoffLocationEnabled(false);
          });
      } else {
        
        setIsDropoffLocationEnabled(false);
      }
    }
  }, [id, type, pickUpLocation, exitpickUpLocation, dispatch]);

  // Effect to handle port city API response
  useEffect(() => {
   

    // Update pickup location enabled state based on API response
    if (portCityStatus === "succeeded" && portCityData) {
      setIsPickupLocationEnabled(true);
    }
  }, [portCityStatus, portCityData]);

  // Effect to handle local zone API response
  useEffect(() => {
   

    // Update dropoff location enabled state based on API response
    if (localZoneStatus === "succeeded") {
      setIsDropoffLocationEnabled(true);
    }
  }, [localZoneStatus]);

  // Effect to update search button state
  useEffect(() => {
    // Check if all required fields are filled
    const isEntryModeComplete =
      selectedPort === "Entry Port" &&
      !!selectedCity &&
      !!pickUpLocation &&
      pickupFromAutocomplete &&
      !!exitpickUpLocation &&
      exitPickupFromAutocomplete;

    const isExitModeComplete =
      selectedPort === "Exit Port" &&
      !!selectedCity &&
      !!exitpickUpLocation &&
      exitPickupFromAutocomplete &&
      !!pickUpLocation &&
      pickupFromAutocomplete;

    setIsSearchButtonEnabled(isEntryModeComplete || isExitModeComplete);
  }, [
    selectedPort,
    selectedCity,
    pickUpLocation,
    exitpickUpLocation,
    pickupFromAutocomplete,
    exitPickupFromAutocomplete,
  ]);

  // Handler for the button search click event
  const buttonsearch = useCallback(() => {
    console.log('🔍 Port MainFilterSearchBox2 - buttonsearch called');
    console.log('🔍 Port MainFilterSearchBox2 - selectedPort:', selectedPort);
    console.log('🔍 Port MainFilterSearchBox2 - selectedCity:', selectedCity);
    console.log('🔍 Port MainFilterSearchBox2 - pickUpLocation:', pickUpLocation);
    console.log('🔍 Port MainFilterSearchBox2 - exitpickUpLocation:', exitpickUpLocation);
    
    // Set validation triggered to true when search button is clicked
    setValidationTriggered(true);

    // Check if city is selected
    if (!selectedCity) {
      setCityError(true);
      console.log('🔍 Port MainFilterSearchBox2 - City not selected, returning');
      return;
    }

    // Check if all required fields are filled
    if (!isSearchButtonEnabled) {
      // Display validation errors but don't proceed with API call
      console.log('🔍 Port MainFilterSearchBox2 - Search button not enabled, returning');
      return;
    }

    dispatch(setPortZoneType("zone"));

    if (selectedPort === "Entry Port") {
      dispatch(setentrypickup(pickUpLocation));
      dispatch(setentrydropoff(exitpickUpLocation));
      dispatch(setpickupdate(selectedDate));
      dispatch(setentrytime(entryytime));
      dispatch(setSelectionType(selectedPort));
      dispatch(setPickupPlaceid(pickid));
      dispatch(setDropoffPlaceid(dropid));
      dispatch(setPicktype("port"));
      dispatch(setDroptype(pickdropType));

      // Only fetch vehicles if both locations are valid
      if (pickid && dropid) {
        console.log('🔍 Port MainFilterSearchBox2 - Fetching zone vehicles for Entry Port');
        setTimeout(() => {
          dispatch(fetchZoneVehicles({ start: 0, limit: 5 }));
        }, 500);
      } else {
        console.log('🔍 Port MainFilterSearchBox2 - Entry Port search skipped - pickid:', pickid, 'dropid:', dropid);
      }
    } else if (selectedPort === "Exit Port") {
      dispatch(setexitpickup(exitpickUpLocation));
      dispatch(setexitdropoff(pickUpLocation));
      dispatch(setexittime(entryytime1));
      //dispatch(setexittime(entryytime1));
      dispatch(setpickupdate(selectedDate1));
      dispatch(setSelectionType(selectedPort));
      dispatch(setPickupPlaceid1(pickid));
      dispatch(setDropoffPlaceid1(dropid));
      dispatch(setPicktype(pickdropType));
      dispatch(setDroptype("port"));

      // Only fetch vehicles if both locations are valid
      if (pickid && dropid) {
        console.log('🔍 Port MainFilterSearchBox2 - Fetching zone vehicles for Exit Port');
        setTimeout(() => {
          dispatch(fetchZoneVehicles({ start: 0, limit: 5 }));
        }, 500);
      } else {
        console.log('🔍 Port MainFilterSearchBox2 - Exit Port search skipped - pickid:', pickid, 'dropid:', dropid);
      }
    } else {
      console.log('🔍 Port MainFilterSearchBox2 - No port selected');
    }
  }, [
    selectedPort,
    selectedCity,
    pickUpLocation,
    exitpickUpLocation,
    selectedDate,
    selectedDate1,
    entryytime,
    entryytime1,
    pickid,
    dropid,
    pickdropType,
    isSearchButtonEnabled,
    dispatch
  ]);

  // Listen for triggerSearch from Redux and call buttonsearch when port step is triggered
  const searchTrigger = useSelector((state) => {
    const trigger = state.steps.triggerSearch;
    console.log('🔍 Port MainFilterSearchBox2 - Selector called, searchTrigger:', trigger);
    return trigger;
  });
  
  useEffect(() => {
    console.log('🔍 Port MainFilterSearchBox2 - useEffect triggered, searchTrigger:', searchTrigger);
    console.log('🔍 Port MainFilterSearchBox2 - Component is mounted and listening for triggers');
    
    if (searchTrigger === 'port') {
      console.log('🔍 Port MainFilterSearchBox2 - Trigger search received for port, calling buttonsearch');
      buttonsearch();
      // Clear the trigger after handling
      dispatch(clearTriggerSearch());
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searchTrigger, dispatch]);

  const formatDateForInput = useCallback((value) => normalizeYmd(value) || "", []);

  const cityPickupDate = useMemo(() => {
    if (!selectedCity?.name) return "";
    const entry = findCityDatesEntry(cityWiseDates, selectedCity.name);
    if (!entry) return "";

    if (selectedPort === "Entry Port" && entry.checkIn) {
      return formatDateForInput(entry.checkIn);
    }
    if (selectedPort === "Exit Port" && entry.checkOut) {
      return formatDateForInput(entry.checkOut);
    }
    return "";
  }, [selectedCity, selectedPort, cityWiseDates, formatDateForInput]);

  useEffect(() => {
    if (!selectedCity?.name || !cityPickupDate) return;

    if (selectedPort === "Entry Port") {
      setSelectedDate(cityPickupDate);
    } else if (selectedPort === "Exit Port") {
      setSelectedDate1(cityPickupDate);
    }
  }, [selectedCity, selectedPort, cityPickupDate]);

  // Handle location selection from PortCity / city chips
  const handleCitySelect = (city) => {
    if (!city) {
      setSelectedCity(null);
      return;
    }

    const nextName = toCityOnly(city.name || city.city || city);
    const currentName = toCityOnly(selectedCity?.name || selectedCity?.city);
    if (nextName && currentName && nextName !== currentName) {
      clearLocationFields();
    }

    applyCitySelection(
      city.name
        ? city
        : buildLocationFromCityName(city.city || city, cityList)
    );
  };

  const handleCityChipClick = (entry) => {
    if (!isCityEnabled) return;
    const location = buildLocationFromCityName(entry.city, cityList);
    if (location) {
      handleCitySelect(location);
    }
  };

  useEffect(() => {
    hasAutoSelectedCity.current = false;
  }, [selectedPort]);

  useEffect(() => {
    if (!hasCityWiseDates || !isCityEnabled) return;
    if (hasAutoSelectedCity.current) return;

    if (selectedCity) {
      hasAutoSelectedCity.current = true;
      return;
    }

    const pending = cityWiseDates[0];
    const location = buildLocationFromCityName(pending?.city, cityList);
    if (location) {
      hasAutoSelectedCity.current = true;
      applyCitySelection(location);
    }
  }, [
    hasCityWiseDates,
    isCityEnabled,
    selectedCity,
    cityWiseDates,
    cityList,
    applyCitySelection,
  ]);

  const activeCityName = useMemo(
    () => toCityOnly(selectedCity?.name || selectedCity?.city),
    [selectedCity]
  );

  const cityChipItems = useMemo(
    () =>
      buildCityChipItems({
        cityWiseDates,
        activeCityName,
        bookings: [],
      }),
    [cityWiseDates, activeCityName]
  );

  const cityPills =
    cityChipItems.length > 0 ? (
      <div
        className={!isCityEnabled ? "pw-city-chips-disabled" : undefined}
        style={!isCityEnabled ? { opacity: 0.55, pointerEvents: "none" } : undefined}
      >
        <CityServiceChips
          items={cityChipItems}
          onCitySelect={handleCityChipClick}
          variant="workspace"
        />
        {cityError ? (
          <div className="text-12 text-red-1 mt-5 mb-5">Please select a city</div>
        ) : null}
      </div>
    ) : null;

  return (
    <>
      {cityPills}
      <div className="mainSearch -col-2 bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-4 mt-10">
        <div
          className={`single-row-container${
            hasCityWiseDates ? " single-row-container--chips" : ""
          }`}
        >
          {/* Port Selection */}
          <div className="search-item port-selection">
            <label className="text-15 fw-500 ls-2 lh-16">
              Select Port Type:
            </label>
            <select
              className="form-control"
              value={selectedPort}
              onChange={(e) => {
                clearLocationFields();
                dispatch(setSelectedPort(e.target.value));
                dispatch(resetVehicles()); // Reset vehicles when port type changes
                setValidationTriggered(false); // Reset validation when port type changes
                setCityError(false); // Reset city error
                setSelectedCity(null); // Reset selected city when port type changes
                setSelectedDate("");
                setSelectedDate1("");
                setTime(false);
                setTime1(false);
                hasAutoSelectedCity.current = false;
              }}
            >
              {selectedPort === "" && (
                <option value="" disabled>
                  Select the Port..
                </option>
              )}
              <option value="Entry Port">Entry Port</option>
              <option value="Exit Port">Exit Port</option>
            </select>
          </div>

          {/* City Selection Section — chips when cityWiseDates exist, else PortCity */}
          {!hasCityWiseDates && (
            <div className="search-item city-selection">
              <h4
                className={`text-15 fw-500 ls-2 lh-16 ${
                  !isCityEnabled ? "text-gray-400" : ""
                }`}
              >
                City
              </h4>
              <PortCity
                key={`city-select-${selectedPort}`}
                onLocationSelect={handleCitySelect}
                hasError={cityError}
                setError={setCityError}
                disabled={!isCityEnabled}
              />
            </div>
          )}

          {/* Pick Up Location */}
          <div className="search-item location-search pickup-location">
            <h4
              className={`text-15 fw-500 ls-2 lh-16 ${
                !isPickupLocationEnabled ? "text-gray-400" : ""
              }`}
            >
              Pick Up Location
            </h4>
            {selectedPort === "Entry Port" ? (
              <LocationSearch
                pickUpLocation={pickUpLocation}
                setPickUpLocation={setPickUpLocation}
                setType={setType}
                setId={setId}
                setpickId={setpickId}
                setdropId={setdropId}
                validationTriggered={validationTriggered}
                setPickupFromAutocomplete={setPickupFromAutocomplete}
                setDropoffFromAutocomplete={setDropoffFromAutocomplete}
                disabled={!isPickupLocationEnabled}
              />
            ) : selectedPort === "Exit Port" ? (
              <SearchBar
                exitpickUpLocation={exitpickUpLocation}
                setexitPickUpLocation={setexitPickUpLocation}
                setType={setType}
                setPickdropType={setPickdropType}
                setId={setId}
                setpickId={setpickId}
                setdropId={setdropId}
                validationTriggered={validationTriggered}
                setPickupFromAutocomplete={setExitPickupFromAutocomplete}
                setDropoffFromAutocomplete={setExitDropoffFromAutocomplete}
                disabled={!isPickupLocationEnabled}
              />
            ) : (
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
                disabled={true}
              />
            )}
          </div>

          {/* Drop Off Location */}
          <div className="search-item location-search dropoff-location">
            <h4
              className={`text-15 fw-500 ls-2 lh-16 ${
                !isDropoffLocationEnabled ? "text-gray-400" : ""
              }`}
            >
              Drop Off Location
            </h4>
            {selectedPort === "Entry Port" ? (
              <SearchBar
                exitpickUpLocation={exitpickUpLocation}
                setexitPickUpLocation={setexitPickUpLocation}
                setType={setType}
                setId={setId}
                setpickId={setpickId}
                setdropId={setdropId}
                setPickdropType={setPickdropType}
                //setDroptype={setDroptype}
                validationTriggered={validationTriggered}
                setPickupFromAutocomplete={setExitPickupFromAutocomplete}
                setDropoffFromAutocomplete={setExitDropoffFromAutocomplete}
                disabled={!isDropoffLocationEnabled}
              />
            ) : selectedPort === "Exit Port" ? (
              <LocationSearch
                pickUpLocation={pickUpLocation}
                setPickUpLocation={setPickUpLocation}
                setType={setType}
                setId={setId}
                setpickId={setpickId}
                setdropId={setdropId}
                validationTriggered={validationTriggered}
                setPickupFromAutocomplete={setPickupFromAutocomplete}
                setDropoffFromAutocomplete={setDropoffFromAutocomplete}
                disabled={!isDropoffLocationEnabled}
              />
            ) : (
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
                disabled={true}
              />
            )}
          </div>

          {/* Time Selection */}
          <div className="search-item time-selection">
            {selectedPort === "Entry Port" ? (
              <>
              <Typography variant="subtitle2" component="h4">
                Pick Up Time
              </Typography>
              <Pickuptime
                entryytime={entryytime}
                setentryytime={setentryytime}
                setTime={setTime}
                disabled={!isDropoffLocationEnabled}
              />
              </>
            ) : (
              <>
              <Typography variant="subtitle2" component="h4">
                Exit Time
              </Typography>
              <Pickuptime1
                entryytime={entryytime1}
                setentryytime={setentryytime1}
                setTime={setTime1}
                disabled={!isDropoffLocationEnabled}
              />
              </>
            )}
          </div>

          {/* Date Selection */}
          <div className="search-item date-selection">
            <h4
              className={`text-15 fw-500 ls-2 lh-16 ${
                !isDropoffLocationEnabled ? "text-gray-400" : ""
              }`}
            >
              {selectedPort === "Entry Port" ? "Pick Up Date" : "Exit Date"}
            </h4>
            {selectedPort === "Entry Port" ? (
              <DateSearch1
                selectedDate={selectedDate}
                setSelectedDate={setSelectedDate}
                cityDate={cityPickupDate}
                disabled={!isDropoffLocationEnabled}
              />
            ) : (
              <DateSearch2
                selectedDate1={selectedDate1}
                setSelectedDate1={setSelectedDate1}
                cityDate={cityPickupDate}
                disabled={!isDropoffLocationEnabled}
              />
            )}
          </div>

          {/* Search Button */}
          <div className="search-item search-button">
            <button
              className={`mainSearch__submit button -dark-1 px-35 rounded-4 ${
                isSearchButtonEnabled
                  ? "bg-blue-1 text-white"
                  : "bg-blue-1/50 text-white/80"
              }`}
              onClick={buttonsearch}
              //disabled={!isSearchButtonEnabled}
            >
              <i className="icon-search text-20 mr-10" />
              Search
            </button>
          </div>
        </div>
      </div>

      <style jsx>{`
        .single-row-container {
          display: grid;
          grid-template-columns:
            minmax(120px, 140px)
            minmax(120px, 1fr)
            minmax(150px, 1.4fr)
            minmax(150px, 1.4fr)
            minmax(130px, 160px)
            minmax(130px, 150px)
            minmax(110px, 130px);
          width: 100%;
          gap: 12px;
          align-items: end;
        }

        .single-row-container--chips {
          grid-template-columns:
            minmax(120px, 140px)
            minmax(150px, 1.4fr)
            minmax(150px, 1.4fr)
            minmax(130px, 160px)
            minmax(130px, 150px)
            minmax(110px, 130px);
        }

        .search-item {
          min-width: 0;
          width: 100%;
          display: flex;
          flex-direction: column;
          justify-content: flex-end;
          overflow: visible;
        }

        .city-selection,
        .pickup-location,
        .dropoff-location {
          min-width: 0;
        }

        .city-selection :global(.searchMenu-loc),
        .pickup-location :global(.searchMenu-loc),
        .dropoff-location :global(.searchMenu-loc) {
          width: 100%;
          min-width: 0;
          padding: 0 !important;
          margin: 0 !important;
        }

        .city-selection :global(input),
        .pickup-location :global(input),
        .dropoff-location :global(input) {
          width: 100% !important;
          min-width: 0 !important;
          max-width: 100% !important;
        }

        .search-button button {
          width: 100%;
          height: 42px;
        }

        .time-selection :global(.MuiFormControl-root),
        .date-selection :global(.MuiFormControl-root),
        .date-selection :global(.MuiTextField-root) {
          width: 100% !important;
          max-width: 100% !important;
          min-width: 0 !important;
        }

        .time-selection :global(.MuiInputBase-root),
        .date-selection :global(.MuiInputBase-root) {
          width: 100% !important;
          height: 42px !important;
          min-height: 42px !important;
          max-height: 42px !important;
        }

        /* Responsive styles */
        @media (max-width: 1400px) {
          .single-row-container {
            grid-template-columns:
              minmax(110px, 120px)
              minmax(110px, 1fr)
              minmax(130px, 1.2fr)
              minmax(130px, 1.2fr)
              minmax(120px, 140px)
              minmax(120px, 140px)
              minmax(100px, 120px);
            gap: 10px;
          }

          .single-row-container--chips {
            grid-template-columns:
              minmax(110px, 120px)
              minmax(130px, 1.2fr)
              minmax(130px, 1.2fr)
              minmax(120px, 140px)
              minmax(120px, 140px)
              minmax(100px, 120px);
          }
        }

        @media (max-width: 1200px) {
          .single-row-container {
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            align-items: stretch;
          }

          .pickup-location,
          .dropoff-location {
            grid-column: span 2;
          }

          .search-button {
            grid-column: span 2;
            margin-top: 8px;
            align-items: center;
          }

          .search-button button {
            max-width: 300px;
          }
        }

        @media (max-width: 900px) {
          .single-row-container {
            grid-template-columns: 1fr;
            gap: 14px;
          }

          .city-selection,
          .pickup-location,
          .dropoff-location,
          .search-button {
            grid-column: span 1;
          }

          .search-button button {
            max-width: 100%;
          }
        }

        @media (max-width: 480px) {
          .mainSearch {
            padding: 10px !important;
          }

          .single-row-container {
            gap: 10px;
          }
        }
      `}</style>
    </>
  );
};

export default MainFilterSearchBox2;
