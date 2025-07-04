import React, { useState, useEffect } from "react";
import { useDispatch } from "react-redux";
import LocationSearch from "./LocationSearch";
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
  setDropoffPlaceid1,
  resetVehicles,
  setSelectedPort,
  setPortZoneType,
  setPickupPlaceid1,
} from "@/slice/port/pickupDropSlice";
import DateSearch1 from "../common/DateSearch1";
import SearchBar from "../activity-list-v2/LocationSearch1";
import DateSearch2 from "../common/DateSearch2";
import Pickuptime from "@/components/activity-single/filter-box1/Pickuptime";
import Pickuptime1 from "@/components/activity-single/filter-box2/Pickuptime1";
import { useSelector } from "react-redux";
import { Typography } from "@mui/material";

const MainFilterSearchBox = ({ Location }) => {
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

  const [pickUpLatLng, setPickupLatLng] = useState(""); // New state for pick-up place_id
  console.log("pickUpLatLng", pickUpLatLng);
  const [dropOffLatLng, setDropoffLatLng] = useState(""); // New state for drop-off place_id
  console.log("dropOffLatLng", dropOffLatLng);
  const [entryytime, setentryytime] = useState("");
  const [entryytime1, setentryytime1] = useState("");

  // Add state for validation
  const [validationTriggered, setValidationTriggered] = useState(false);

  // Track if locations were selected from autocomplete
  const [pickupFromAutocomplete, setPickupFromAutocomplete] = useState(false);
  const [dropoffFromAutocomplete, setDropoffFromAutocomplete] = useState(false);
  const [exitPickupFromAutocomplete, setExitPickupFromAutocomplete] =
    useState(false);
  const [exitDropoffFromAutocomplete, setExitDropoffFromAutocomplete] =
    useState(false);

  const [time, setTime] = useState(false);
  const [time1, setTime1] = useState(false);
  // Handler for the button search click event
  const buttonsearch = () => {
    // Set validation triggered to true when search button is clicked
    setValidationTriggered(true);
    dispatch(setPortZoneType(""));
    if (selectedPort === "Entry Port") {
      // Only proceed if both locations are selected from autocomplete
      const locationsValid = pickupFromAutocomplete && dropoffFromAutocomplete;

      dispatch(setSelectionType("Entry Port"));
      dispatch(setentrypickup(pickUpLocation));
      dispatch(setentrydropoff(dropOffLocation));
      dispatch(setpickupdate(selectedDate));
      dispatch(setentrytime(entryytime));
      dispatch(setSelectionType(selectedPort));
      dispatch(setPickupPlaceid(pickUpLatLng));
      dispatch(setDropoffPlaceid(dropOffLatLng));

      // Only fetch vehicles if both locations are valid
      if (locationsValid && time) {
        setTimeout(() => {
          dispatch(fetchVehicles());
        }, 500);
      }
    } else if (selectedPort === "Exit Port") {
      // Only proceed if both locations are selected from autocomplete
      const locationsValid =
        exitPickupFromAutocomplete && exitDropoffFromAutocomplete;

      dispatch(setexitpickup(exitpickUpLocation));
      dispatch(setexitdropoff(exitdropOffLocation));
      dispatch(setentrytime(entryytime1));
      dispatch(setexittime(entryytime1));
      dispatch(setpickupdate(selectedDate1));
      dispatch(setSelectionType(selectedPort));
      // Convert the coordinate objects to strings before dispatching to Redux
      dispatch(setPickupPlaceid1(pickUpLatLng));
      dispatch(setDropoffPlaceid1(dropOffLatLng));

      // Only fetch vehicles if both locations are valid
      if (locationsValid && time1) {
        setTimeout(() => {
          dispatch(fetchVehicles());
        }, 500);
      }
    }
  };

  return (
    <>
      <div className="mainSearch -col-2 bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-4 mt-30">
        <div className="button-grid-v2 items-center">
          {/* First Section - Port Selection */}
          <div className="port-selection-wrapper">
            <label className="text-15 fw-500 ls-2 lh-16">
              Select Port Type:
            </label>
            <select
              className="form-control"
              value={selectedPort}
              onChange={(e) => {
                dispatch(setSelectedPort(e.target.value));
                dispatch(resetVehicles()); // Reset vehicles when port type changes
                setValidationTriggered(false); // Reset validation when port type changes
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

          {/* Second Section - Location Search */}
          <div className="location-search-wrapper">
            {selectedPort === "Entry Port" ? (
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
            ) : selectedPort === "Exit Port" ? (
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
            ) : (
              <>
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
              </>
            )}
          </div>

          {/* Third Section - Time Selection */}
          <div className="time-selection-wrapper">
            {selectedPort === "Entry Port" ? (
              <>
              <Typography variant="subtitle2" fontWeight={600} sx={{ mb: 1, color: '#000' }}>
        Pick Up Time
      </Typography>
              <Pickuptime
                entryytime={entryytime}
                setentryytime={setentryytime}
                setTime={setTime}
              />
              </>
            ) : (
              <>
              <Typography variant="subtitle2" fontWeight={600} sx={{ mb: 1, color: '#000' }}>
        Exit Time
      </Typography>
              <Pickuptime1
                entryytime={entryytime1}
                setentryytime={setentryytime1}
                setTime={setTime1}
              />
              </>
            )}
          </div>

          {/* Fourth Section - Date Selection */}
          <div className="date-selection-wrapper">
            <h4 className="text-15 fw-500 ls-2 lh-16 mt-15">
              {selectedPort === "Entry Port" ? "Pick Up Date" : "Exit Date"}
            </h4>
            {selectedPort === "Entry Port" ? (
              <DateSearch1
                selectedDate={selectedDate}
                setSelectedDate={setSelectedDate}
              />
            ) : (
              <DateSearch2
                selectedDate1={selectedDate1}
                setSelectedDate1={setSelectedDate1}
              />
            )}
          </div>

          {/* Fifth Section - Search Button */}
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
      </div>

      <style jsx>{`
        .button-grid-v2 {
          display: grid;
          grid-template-columns: 160px 4fr 1.93fr 140px 1fr;
          gap: 5px;
          align-items: center;
          width: 100%;
          max-width: 1730px;
        }

        .port-selection-wrapper,
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
          .button-grid-v2 {
            grid-template-columns: 1fr 1fr;
            gap: 20px;
          }

          .location-search-wrapper {
            grid-column: span 2;
            width: 100%;
          }

          .search-button-wrapper {
            grid-column: span 2;
            text-align: center;
          }
        }

        @media (max-width: 767px) {
          .button-grid-v2 {
            grid-template-columns: 1fr;
            gap: 15px;
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

export default MainFilterSearchBox;
