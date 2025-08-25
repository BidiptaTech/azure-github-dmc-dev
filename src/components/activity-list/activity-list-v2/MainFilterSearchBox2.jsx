import React, { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
import LocationSearch from "./PortLocation";
import {
  fetchVehicles,
  setentrypickup,
  setentrydropoff,
  setexitpickup,
  setexitdropoff,
  setpickupdate,
  setentrytime,
  setexittime,
  //setexitpickupdate,
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

const MainFilterSearchBox2 = ({ Location }) => {
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

  const [pickUpLatLng, setPickupLatLng] = useState(""); // New state for pick-up place_id
  const [dropOffLatLng, setDropoffLatLng] = useState(""); // New state for drop-off place_id
  const [entryytime, setentryytime] = useState("");
  const [entryytime1, setentryytime1] = useState("");

  // Add state for validation
  const [validationTriggered, setValidationTriggered] = useState(false);
  const [cityError, setCityError] = useState(false);
  const [type, setType] = useState("");
  const [pickdropType, setPickdropType] = useState("");
  console.log("pickdropType", pickdropType);

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
  console.log("pickiddd", pickid);
  const [dropid, setdropId] = useState("");
  console.log("dropiddd", dropid);
  // State for selected city
  const [selectedCity, setSelectedCity] = useState(null);

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
    if (!isCityEnabled) {
      setSelectedCity(null);
      setIsPickupLocationEnabled(false);
      setIsDropoffLocationEnabled(false);
    }
  }, [selectedPort]);

  // Effect to call fetchPortCity when a city is selected
  useEffect(() => {
    if (selectedCity && TourId && listType) {
      console.log("Selected city:", selectedCity);
      console.log("Tour ID:", TourId, "Type:", typeof TourId);

      // Ensure TourId is passed correctly as a number
      dispatch(
        fetchPortCity({
          city: selectedCity.name,
          tourId: parseInt(TourId),
          type: listType,
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
          "Not calling fetchPortCity, missing:",
          pickUpLocation,
          type,
          id
        );
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
          "Not calling fetchPortCity, missing:",
          pickUpLocation,
          type,
          id
        );
        setIsDropoffLocationEnabled(false);
      }
    }
  }, [id, type, pickUpLocation, exitpickUpLocation, dispatch]);

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
        setTimeout(() => {
          dispatch(fetchZoneVehicles({ start: 0, limit: 5 }));
        }, 500);
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
        setTimeout(() => {
          dispatch(fetchZoneVehicles({ start: 0, limit: 5 }));
        }, 500);
      }
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
      <div className="mainSearch -col-2 bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-4 mt-10">
        <div className="single-row-container">
          {/* Port Selection */}
          <div className="search-item port-selection mb-10">
            <label className="text-15 fw-500 ls-2 lh-16 mb-10">
              Select Port Type:
            </label>
            <select
              className="form-control"
              value={selectedPort}
              onChange={(e) => {
                setPickUpLocation("");
                setexitPickUpLocation("");
                setDropOffLocation("");
                setexitDropOffLocation("");
                dispatch(setSelectedPort(e.target.value));
                dispatch(resetVehicles()); // Reset vehicles when port type changes
                setValidationTriggered(false); // Reset validation when port type changes
                setCityError(false); // Reset city error
                setSelectedCity(null); // Reset selected city when port type changes
                // Reset other state affecting location validation
                setPickupFromAutocomplete(false);
                setDropoffFromAutocomplete(false);
                setExitPickupFromAutocomplete(false);
                setExitDropoffFromAutocomplete(false);
                setTime(false);
                setTime1(false);
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

          {/* City Selection Section */}
          <div className="search-item location-search pickup-location">
            <h4
              className={`text-15 fw-500 ls-2 lh-16 mb-20 ml-10 ${
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

          {/* Pick Up Location */}
          <div className="search-item location-search pickup-location">
            <h4
              className={`text-15 fw-500 ls-2 lh-16 mb-20 ml-10 ${
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
              className={`text-15 fw-500 ls-2 lh-16 mb-20 ml-10 ${
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
          <div className="search-item time-selection mb-10">
            {selectedPort === "Entry Port" ? (
              <>
              <Typography variant="subtitle2" fontWeight={100} sx={{ mb: 1, color: '#000' }}>
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
              <Typography variant="subtitle2" fontWeight={100} sx={{ mb: 1, color: '#000' }}>
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
          <div className="search-item date-selection mb-10">
            <h4
              className={`text-15 fw-500 ls-2 lh-16 mt-15 ${
                !isDropoffLocationEnabled ? "text-gray-400" : ""
              }`}
            >
              {selectedPort === "Entry Port" ? "Pick Up Date" : "Exit Date"}
            </h4>
            {selectedPort === "Entry Port" ? (
              <DateSearch1
                selectedDate={selectedDate}
                setSelectedDate={setSelectedDate}
                disabled={!isDropoffLocationEnabled}
              />
            ) : (
              <DateSearch2
                selectedDate1={selectedDate1}
                setSelectedDate1={setSelectedDate1}
                disabled={!isDropoffLocationEnabled}
              />
            )}
          </div>

          {/* Search Button */}
          <div className="search-item search-button mb-15">
            <button
              className={`mainSearch__submit button -dark-1 py-15 px-35 rounded-4 ${
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
          grid-template-columns: 140px 1fr 2fr 2fr 2fr 1fr 140px;
          flex-wrap: nowrap;
          width: 100%;
          gap: 5px;
          align-items: flex-end;
        }

        .search-item {
          flex-shrink: 1;
        }

        .port-selection {
          width: 140px;
        }

        .city-selection {
          width: 100px;
        }

        .location-search {
          width: 200px;
          flex-grow: 1;
          margin-bottom: 15px;
        }

        .pickup-location,
        .dropoff-location {
          min-width: 200px;
        }

        .time-selection {
          width: 140px;
        }

        .date-selection {
          width: 160px;
        }

        .search-button {
          width: 140px;
          display: flex;
          align-items: flex-end;
        }

        .search-button button {
          width: 100%;
          height: 50px;
        }

        /* Responsive styles */
        @media (max-width: 1400px) {
          .single-row-container {
            grid-template-columns: 120px 1fr 1.5fr 1.5fr 1.5fr 1fr 120px;
            gap: 8px;
          }

          .port-selection {
            width: 120px;
          }

          .time-selection {
            width: 120px;
          }

          .date-selection {
            width: 140px;
          }

          .search-button {
            width: 120px;
          }
        }

        @media (max-width: 1200px) {
          .single-row-container {
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: stretch;
          }

          .port-selection {
            width: 100%;
            grid-column: span 1;
          }

          .city-selection {
            width: 100%;
            grid-column: span 1;
          }

          .location-search {
            width: 100%;
            grid-column: span 2;
            min-width: 100%;
          }

          .pickup-location,
          .dropoff-location {
            min-width: 100%;
          }

          .time-selection {
            width: 100%;
            grid-column: span 1;
          }

          .date-selection {
            width: 100%;
            grid-column: span 1;
          }

          .search-button {
            width: 100%;
            grid-column: span 2;
            margin-top: 15px;
            justify-content: center;
          }

          .search-button button {
            max-width: 300px;
          }
        }

        @media (max-width: 900px) {
          .single-row-container {
            grid-template-columns: 1fr;
            gap: 15px;
          }

          .port-selection,
          .city-selection,
          .location-search,
          .time-selection,
          .date-selection,
          .search-button {
            grid-column: span 1;
            width: 100%;
          }

          .location-search {
            margin-bottom: 10px;
          }

          .search-button {
            margin-top: 10px;
          }
        }

        @media (max-width: 767px) {
          .single-row-container {
            gap: 15px;
          }

          .search-item {
            margin-bottom: 10px;
          }

          .location-search {
            margin-bottom: 8px;
          }

          .search-button button {
            max-width: 100%;
            height: 45px;
            font-size: 14px;
          }
        }

        @media (max-width: 480px) {
          .mainSearch {
            padding: 10px !important;
          }

          .single-row-container {
            gap: 10px;
          }

          .text-15 {
            font-size: 14px;
          }

          .search-button button {
            height: 40px;
            font-size: 13px;
            padding: 8px 20px;
          }

          .port-selection select,
          .location-search input,
          .time-selection input,
          .date-selection input {
            font-size: 14px;
            padding: 8px 12px;
          }
        }

        @media (max-width: 360px) {
          .mainSearch {
            padding: 8px !important;
          }

          .single-row-container {
            gap: 8px;
          }

          .search-button button {
            height: 38px;
            font-size: 12px;
            padding: 6px 16px;
          }
        }
      `}</style>
    </>
  );
};

export default MainFilterSearchBox2;
