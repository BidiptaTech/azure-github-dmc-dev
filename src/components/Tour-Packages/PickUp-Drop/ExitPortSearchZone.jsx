import React, { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  fetchVehicles,
  setexitpickup,
  setexitdropoff,
  setpickupdate,
  setexittime,
  setSelectionType,
  setPickupPlaceid,
  setDropoffPlaceid,
  resetVehicles,
  fetchPortCity,
  fetchLocalZone,
  setPicktype,
  setDroptype,
  fetchZoneVehicles,
  setPortZoneType,
} from "@/slice/port/pickupDropSlice";
import PortCity from "./PortCity";
import SearchBar from "./PortLocation2";
import LocationSearch from "./PortLocation";
import DateSearch2 from "@/components/activity-list/common/DateSearch2";
import Pickuptime1 from "@/components/activity-single/filter-box2/Pickuptime1";

const ExitPortSearchZone = ({ Location, portType}) => {
  const dispatch = useDispatch();

  // State for storing the pickup and dropoff locations
  const [pickUpLocation, setPickUpLocation] = useState("");
  const [exitpickUpLocation, setexitPickUpLocation] = useState("");
  const [selectedDate1, setSelectedDate1] = useState("");
  const [entryytime1, setentryytime1] = useState("");

  const TourId = useSelector((state) => state.hotels.id);
  console.log("TourId", TourId);

  // Check port city API status
  const portCityStatus = useSelector(
    (state) => state.pickupDrop.portCityStatus
  );
  const portCityData = useSelector((state) => state.pickupDrop.portCityData);

  // Check local zone API status
  const localZoneStatus = useSelector(
    (state) => state.pickupDrop.localZoneStatus
  );

  // Add state for validation
  const [validationTriggered, setValidationTriggered] = useState(false);
  const [cityError, setCityError] = useState(false);
  const [type, setType] = useState("");
  const [pickdropType, setPickdropType] = useState("");

  const [id, setId] = useState("");
  // Track if locations were selected from autocomplete
  const [pickupFromAutocomplete, setPickupFromAutocomplete] = useState(false);
  const [exitPickupFromAutocomplete, setExitPickupFromAutocomplete] = useState(false);
  const [time1, setTime1] = useState(false);
  const [pickid, setpickId] = useState("");
  const [dropid, setdropId] = useState("");

  // State for selected city
  const [selectedCity, setSelectedCity] = useState(null);

  // Sequential enabling states
  const [isCityEnabled, setIsCityEnabled] = useState(true);
  const [isPickupLocationEnabled, setIsPickupLocationEnabled] = useState(false);
  const [isDropoffLocationEnabled, setIsDropoffLocationEnabled] = useState(false);
  const [isSearchButtonEnabled, setIsSearchButtonEnabled] = useState(false);

  // Effect to call fetchPortCity when a city is selected
  useEffect(() => {
    if (selectedCity && TourId) {
      console.log("Selected city:", selectedCity);
      console.log("Tour ID:", TourId, "Type:", typeof TourId);

      // Ensure TourId is passed correctly as a number
      dispatch(
        fetchPortCity({
          city: selectedCity.name,
          tourId: parseInt(TourId),
          type: "hotel",
        })
      )
        .then((result) => {
          console.log("fetchPortCity dispatch result:", result);
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
      console.log("Not calling fetchPortCity, missing:", {
        hasCity: !!selectedCity,
        hasTourId: !!TourId,
        tourIdValue: TourId,
      });
      setIsPickupLocationEnabled(false);
    }
  }, [selectedCity, TourId, dispatch]);

  // Call fetchLocalZone when pickup location is selected for Exit Port
  useEffect(() => {
    if (exitpickUpLocation && type && id) {
      dispatch(
        fetchLocalZone({
          id: id,
          type: type,
        })
      )
        .then((result) => {
          console.log("fetchlocalzone dispatch result:", result);
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
      console.log(
        "Not calling fetchLocalZone, missing:",
        exitpickUpLocation,
        type,
        id
      );
      setIsDropoffLocationEnabled(false);
    }
  }, [id, type, exitpickUpLocation, dispatch]);

  // Effect to handle port city API response
  useEffect(() => {
    console.log("Port city status:", portCityStatus);
    console.log("Port city data:", portCityData);

    // Update pickup location enabled state based on API response
    if (portCityStatus === "succeeded" && portCityData) {
      setIsPickupLocationEnabled(true);
    }
  }, [portCityStatus, portCityData]);

  // Effect to handle local zone API response
  useEffect(() => {
    console.log("Local zone status:", localZoneStatus);

    // Update dropoff location enabled state based on API response
    if (localZoneStatus === "succeeded") {
      setIsDropoffLocationEnabled(true);
    }
  }, [localZoneStatus]);

  // Effect to update search button state for Exit Port
  useEffect(() => {
    // Check if all required fields are filled for Exit Port
    const isExitModeComplete =
      !!selectedCity &&
      !!exitpickUpLocation &&
      exitPickupFromAutocomplete &&
      !!pickUpLocation &&
      pickupFromAutocomplete;

    setIsSearchButtonEnabled(isExitModeComplete);
  }, [
    selectedCity,
    exitpickUpLocation,
    pickUpLocation,
    exitPickupFromAutocomplete,
    pickupFromAutocomplete,
  ]);

  // Handler for the button search click event
  const buttonsearch = () => {
    // Set validation triggered to true when search button is clicked
    setValidationTriggered(true);

    // Check if city is selected
    if (!selectedCity) {
      setCityError(true);
      return;
    }

    // Check if all required fields are filled
    if (!isSearchButtonEnabled) {
      // Display validation errors but don't proceed with API call
      return;
    }

    dispatch(setPortZoneType("zone"));
    dispatch(setexitpickup(exitpickUpLocation));
    dispatch(setexitdropoff(pickUpLocation));
    dispatch(setexittime(entryytime1));
    dispatch(setpickupdate(selectedDate1));
    dispatch(setSelectionType("Exit Port"));
    dispatch(setPickupPlaceid(pickid));
    dispatch(setDropoffPlaceid(dropid));
    dispatch(setPicktype(pickdropType));
    dispatch(setDroptype("port"));

    // Only fetch vehicles if both locations are valid
    if (pickid && dropid) {
      setTimeout(() => {
        dispatch(fetchZoneVehicles());
      }, 500);
    }
  };

  // Handle location selection from PortCity
  const handleCitySelect = (city) => {
    console.log("City selected:", city);
    setSelectedCity(city);
    if (city) {
      setCityError(false);
    }
  };

  return (
    <>
      <div className="exit-port-zone-container">
        <h4 className="text-18 fw-500 mb-10">Exit Port Services</h4>
        <div className="search-row">
          {/* City Selection Section */}
          <div className="search-item location-search city-selection">
            <h4 className={`text-15 fw-500 ls-2 lh-16 mb-20 ml-10`}>
              City
            </h4>
            <PortCity
              onLocationSelect={handleCitySelect}
              hasError={cityError}
              setError={setCityError}
              disabled={!isCityEnabled}
            />
          </div>

          {/* Pick Up Location */}
          <div className="search-item location-search pickup-location">
            <h4
              className={`text-15 fw-500 ls-2 lh-16 mb-20 ml-10 ${
                !isPickupLocationEnabled ? "text-gray-400" : ""
              }`}
            >
              Pick Up Location
            </h4>
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
              setDropoffFromAutocomplete={setExitPickupFromAutocomplete}
              disabled={!isPickupLocationEnabled}
              portType={portType}
            />
          </div>

          {/* Drop Off Location */}
          <div className="search-item location-search dropoff-location">
            <h4
              className={`text-15 fw-500 ls-2 lh-16 mb-20 ml-10 ${
                !isDropoffLocationEnabled ? "text-gray-400" : ""
              }`}
            >
              Drop Off Location
            </h4>
            <LocationSearch
              pickUpLocation={pickUpLocation}
              setPickUpLocation={setPickUpLocation}
              setType={setType}
              portType={portType}
              setId={setId}
              setpickId={setpickId}
              setdropId={setdropId}
              validationTriggered={validationTriggered}
              setPickupFromAutocomplete={setPickupFromAutocomplete}
              setDropoffFromAutocomplete={setPickupFromAutocomplete}
              disabled={!isDropoffLocationEnabled}
            />
          </div>

          {/* Time Selection */}
          <div className="search-item time-selection">
            <Pickuptime1
              entryytime={entryytime1}
              setentryytime={setentryytime1}
              setTime={setTime1}
              disabled={!isDropoffLocationEnabled}
            />
          </div>

          {/* Date Selection */}
          <div className="search-item date-selection">
            <h4
              className={`text-15 fw-500 ls-2 lh-16 mt-15 ${
                !isDropoffLocationEnabled ? "text-gray-400" : ""
              }`}
            >
              Exit Date
            </h4>
            <DateSearch2
              selectedDate1={selectedDate1}
              setSelectedDate1={setSelectedDate1}
              disabled={!isDropoffLocationEnabled}
            />
          </div>

          {/* Search Button */}
          <div className="search-item search-button">
            <button
              className={`mainSearch__submit button -dark-1 py-15 px-35 rounded-4 ${
                isSearchButtonEnabled
                  ? "bg-blue-1 text-white"
                  : "bg-blue-1/50 text-white/80"
              }`}
              onClick={buttonsearch}
            >
              <i className="icon-search text-20 mr-10" />
              Search
            </button>
          </div>
        </div>
      </div>

      <style jsx>{`
        .exit-port-zone-container {
          width: 100%;
          padding-bottom: 20px;
          margin-bottom: 20px;
        }
        
        .search-row {
          display: flex;
          flex-wrap: wrap;
          gap: 15px;
          align-items: flex-end;
        }
        
        .search-item {
          flex: 1;
          min-width: 150px;
        }

        .city-selection {
          flex: 1.5;
        }
        
        .location-search {
          flex: 2;
        }
        
        .time-selection, 
        .date-selection {
          min-width: 120px;
          flex: 1;
        }
        
        .search-button {
          min-width: 120px;
          display: flex;
          align-items: flex-end;
        }
        
        .search-button button {
          width: 100%;
          height: 50px;
        }
        
        @media (max-width: 991px) {
          .search-row {
            flex-direction: column;
          }
          
          .search-item {
            width: 100%;
          }
        }
      `}</style>
    </>
  );
};

export default ExitPortSearchZone; 