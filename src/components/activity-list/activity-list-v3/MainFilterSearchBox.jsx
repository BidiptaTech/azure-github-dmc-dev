import DateSearch from "../common/DateSearch";
import React, { useState, useEffect } from "react";
import { useDispatch } from "react-redux";
import GuestSearch from "../common/GuestSearch";
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
  setHourlyCity,
} from "@/slice/localtour/Localslice";
import SearchBar1 from "./LocationSearch1";
import DateSearch1 from "./DateSearch1";
import DateSearch2 from "./DateSearch2";
import Pickuptime from "@/components/activity-single/filter-box3/Pickuptime";
import Pickuptime1 from "@/components/activity-single/filter-box4/Pickuptime1";
import { useSelector } from "react-redux";
import SearchZone from "./LocationZoneSearch";
import Pickuptimezone from "./Pickuptimezone";
import DateSearchZone from "./DateSearchZone";
import { triggerSearch, clearTriggerSearch } from "@/slice/common/stepsSlice";
import { selectCart } from "@/slice/cart/carSlice";
import CityLocation from "./CityLocation";

const MainFilterSearchBox = ({ Location }) => {
  const dispatch = useDispatch();
  console.log('🔍 Location MainFilterSearchBox12', Location);
  // State for storing the pickup and dropoff locations
  const [pickUpLocation, setPickUpLocation] = useState("");
  const [pickUpZone, setPickUpZone] = useState("");
  const [dropOffLocation, setDropOffLocation] = useState("");
  const [dropOffzone, setDropOffZone] = useState("");
  const [exitpickUpLocation, setexitPickUpLocation] = useState("");
  //const [exitdropOffLocation, setexitDropOffLocation] = useState("");
  const [selectedDate, setSelectedDate] = useState("");
  const [selectedDate1, setSelectedDate1] = useState("");
  const [selectedDateZone, setSelectedDateZone] = useState("");
  // Hourly: selected city name (from CityLocation)
  const [hourlyCityName, setHourlyCityName] = useState("");
  const [hourlyCityAddress, setHourlyCityAddress] = useState("");
  const [hourlyCityError, setHourlyCityError] = useState(false);
  //const [selectedPort, setSelectedPort] = useState("Point To Point"); // Default selection
  const selectedPort = useSelector((state) => state.localtour.selectedPort);
  console.log("selectedPort2", selectedPort);
  const [pickUpLatLng, setPickupLatLng] = useState(""); // New state for pick-up place_id
  const [dropOffLatLng, setDropoffLatLng] = useState(""); // New state for drop-off place_id
  const [entryytime, setentryytime] = useState("");
  const [entryytime1, setentryytime1] = useState("");
  const [entryytimezone, setentryytimezone] = useState("");
  const rawZoneOn = useSelector((state) => state.auth.zone_on);
  const zone_on = rawZoneOn !== null ? Number(rawZoneOn) : null;
  // Add state for validation
  const [validationTriggered, setValidationTriggered] = useState(false);

  // Track if locations were selected from autocomplete
  const [pickupFromAutocomplete, setPickupFromAutocomplete] = useState(false);
  const [dropoffFromAutocomplete, setDropoffFromAutocomplete] = useState(false);
  const [exitPickupFromAutocomplete, setExitPickupFromAutocomplete] =
    useState(false);

  const currentbooking = useSelector((state) => state.localtour.selectbooking);
  console.log("currentbooking23", currentbooking);
  const picktype = useSelector((state) => state.localtour.picktype);
  const [droptype, setdroptype] = useState("");
  // Track if time is selected
  const [time, setTime] = useState(false);
  const [time1, setTime1] = useState(false);
  const [timezone, setTimezone] = useState(false);
  const viewDetails = useSelector((state) => state.viewDetails.bookings);
  const cart = useSelector(selectCart);

  const hasCartLocalTransferServices = Array.isArray(cart)
    ? cart.some(
        (trip) =>
          Array.isArray(trip?.bookings) &&
          trip.bookings.some((booking) => {
            const type = String(booking?.type || "").toLowerCase();
            return (
              type === "hotel" ||
              type === "attraction" ||
              type === "attraction_package" ||
              type === "restaurant"
            );
          })
      )
    : false;

  const hasViewDetailsLocalTransferServices = Boolean(
    viewDetails &&
      (viewDetails.hotel?.length > 0 ||
        viewDetails.attraction?.length > 0 ||
        viewDetails.attraction_package?.length > 0 ||
        viewDetails.restaurant?.length > 0)
  );

  const showLocalTransferOption =
    zone_on === 1 &&
    (hasViewDetailsLocalTransferServices || hasCartLocalTransferServices);

  const buttonsearch = () => {
    // Set validation triggered to true when search button is clicked
    setValidationTriggered(true);

    if (selectedPort === "Point To Point") {
      // Only proceed if both locations are selected from autocomplete
      const locationsValid = pickupFromAutocomplete && dropoffFromAutocomplete;

      dispatch(setentrypickup(pickUpLocation));
      dispatch(setentrydropoff(dropOffLocation));
      dispatch(setentrytime(entryytime));
      dispatch(setpickdate(selectedDate));
      dispatch(setSelectionType(selectedPort));
      dispatch(setPickupPlaceid(pickUpLatLng));
      dispatch(setDropoffPlaceid(dropOffLatLng));
      dispatch(setZonetype(""));

      // Only fetch vehicles if locations and time are valid
      if (locationsValid && time) {
        setTimeout(() => {
          dispatch(fetchVehicles({ start: 0, limit: 5 }));
        }, 500);
      }
    } else if (selectedPort === "Hourly") {
      // Only proceed if pickup location is selected from autocomplete
      const locationValid = exitPickupFromAutocomplete;
      const cityName =
        hourlyCityName ||
        String(hourlyCityAddress || "")
          .split(",")[0]
          .trim();
      const cityValid = Boolean(cityName);

      dispatch(setHourlyCity(cityName));
      dispatch(setexitpickup(exitpickUpLocation));
      dispatch(setpickdate(selectedDate1));
      dispatch(setentrytime(entryytime1));
      dispatch(setentrytime1(entryytime1));
      dispatch(setSelectionType(selectedPort));
      dispatch(setPickupPlaceid(pickUpLatLng));
      dispatch(setDropoffPlaceid(null));
      dispatch(setZonetype(""));

      // Only fetch vehicles if city, location and time are valid
      if (cityValid && locationValid && time1) {
        setTimeout(() => {
          dispatch(
            fetchVehicles({ start: 0, limit: 5, city: cityName })
          );
        }, 500);
      }
    } else if (selectedPort === "Local Transfer") {
      // Only proceed if both locations are selected from autocomplete
      dispatch(setPickupZoneid(pickUpZone));
      dispatch(setDropoffZoneid(dropOffzone));
      dispatch(setentrypickup(pickUpLatLng));
      dispatch(setentrydropoff(dropOffLatLng));
      dispatch(setSelectionType(selectedPort));
      dispatch(setDroptype(droptype));
      dispatch(setentrytime(entryytimezone));
      dispatch(setpickdate(selectedDateZone));
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
          dispatch(fetchZoneVehicles({ start: 0, limit: 5 }));
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

  // Listen for triggerSearch from Redux and call buttonsearch when travel step is triggered
  const searchTrigger = useSelector((state) => state.steps.triggerSearch);
  
  useEffect(() => {
    if (searchTrigger === 'travel') {
      console.log('🔍 Travel MainFilterSearchBox - Trigger search received, calling buttonsearch');
      buttonsearch();
      // Clear the trigger after handling
      dispatch(clearTriggerSearch());
    }
  }, [searchTrigger, dispatch]);

  return (
    <>
      <div className="mainSearch -col-2 bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-4 mt-30">
        <div className="button-grid-v2 items-center">
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
                setHourlyCityName("");
                setHourlyCityAddress("");
                setHourlyCityError(false);
              }}
            >
              {selectedPort === "" && (
                <option value="" disabled>
                  Select the type..
                </option>
              )}
              {/* <option value="Point To Point">Point To Point</option> */}
              <option value="Hourly">Hourly</option>
              {showLocalTransferOption && (
                <option value="Local Transfer">Local Transfer</option>
              )}
            </select>
          </div>

          {/* City field — Hourly only, own column before pickup */}
          {selectedPort === "Hourly" && (
            <div className="hourly-city-wrapper">
              <CityLocation
                label="City"
                setPickUpLocation={(address) => {
                  setHourlyCityAddress(address || "");
                  const name = String(address || "")
                    .split(",")[0]
                    .trim();
                  setHourlyCityName(name);
                  setHourlyCityError(false);
                }}
                pickUpLocation={hourlyCityAddress}
                onCitySelect={(item) => {
                  const name = item?.name || "";
                  setHourlyCityName(name);
                  setHourlyCityAddress(item?.address || name);
                  setHourlyCityError(false);
                }}
                hasError={
                  validationTriggered &&
                  !hourlyCityName &&
                  !String(hourlyCityAddress || "").trim()
                }
                setError={setHourlyCityError}
              />
            </div>
          )}

          {/* Second Section - Location Search */}
          <div className="location-search-wrapper">
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
              />
            ) : selectedPort === "Local Transfer" ? (
              <SearchZone
                currentbooking={currentbooking}
                picktype={picktype}
                setdroptype={setdroptype}
                setPickUpLocation={setPickUpZone}
                setPickupLatLng={setPickupLatLng}
                setDropoffLatLng={setDropoffLatLng}
                dropOffLocation={dropOffLatLng}
                validationTriggered={validationTriggered}
                setDropOffLocation={setDropOffZone}
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
            {selectedPort === "Point To Point" ? (
              <Pickuptime
                entryytime={entryytime}
                setentryytime={setentryytime}
                setTime={setTime}
              />
            ) : selectedPort === "Local Transfer" ? (
              <Pickuptimezone
                entryytime={entryytimezone}
                setentryytime={setentryytimezone}
                setTime={setTimezone}
              />
            ) : (
              <Pickuptime1
                entryytime={entryytime1}
                setentryytime={setentryytime1}
                setTime={setTime1}
              />
            )}
          </div>

          {/* Fourth Section - Date Selection */}
          <div className="date-selection-wrapper">
            <h4 className="text-15 fw-500 ls-2 lh-16 mt-15">
              {selectedPort === "Point To Point" ? "Pick Up Date" : "Exit Date"}
            </h4>
            {selectedPort === "Point To Point" ? (
              <DateSearch1
                selectedDate={selectedDate}
                setSelectedDate={(date) => {
                  console.log("Selected Pickup Date:", date);
                  setSelectedDate(date);
                }}
              />
            ) : selectedPort === "Local Transfer" ? (
              <DateSearchZone
                selectedDate1={selectedDateZone}
                setSelectedDate1={setSelectedDateZone}
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
          grid-template-columns: ${
            selectedPort === "Hourly"
              ? "160px minmax(140px, 180px) minmax(220px, 2.2fr) minmax(170px, 1.1fr) minmax(150px, 1fr) minmax(120px, 1fr)"
              : "160px minmax(280px, 3fr) minmax(180px, 1.2fr) minmax(150px, 1fr) minmax(130px, 1fr)"
          };
          gap: 12px;
          align-items: center;
          width: 100%;
          max-width: 1730px;
        }

        .port-selection-wrapper {
          width: 100%;
          max-width: 160px;
        }

        .port-selection-wrapper select {
          width: 100%;
          padding: 8px;
        }

        .hourly-city-wrapper {
          width: 100%;
          min-width: 0;
          position: relative;
          align-self: center;
        }

        .hourly-city-wrapper :global(.searchMenu-loc) {
          padding-left: 10px !important;
          padding-right: 10px !important;
        }

        .hourly-city-wrapper :global(input.js-search) {
          width: 100%;
          max-width: 100%;
        }

        .location-search-wrapper,
        .time-selection-wrapper,
        .date-selection-wrapper,
        .search-button-wrapper {
          width: 100%;
          min-width: 0;
          box-sizing: border-box;
        }

        .time-selection-wrapper,
        .date-selection-wrapper {
          overflow: hidden;
          padding-right: 4px;
        }

        .time-selection-wrapper :global(.MuiFormControl-root),
        .time-selection-wrapper :global(.MuiInputBase-root) {
          width: 100% !important;
          min-width: 0 !important;
          max-width: 100% !important;
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

          .port-selection-wrapper {
            max-width: 100%;
          }

          .hourly-city-wrapper {
            grid-column: span 1;
          }

          .location-search-wrapper {
            grid-column: span 1;
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

          .hourly-city-wrapper,
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
