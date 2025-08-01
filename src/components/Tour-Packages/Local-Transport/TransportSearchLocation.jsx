import { useState, useEffect } from "react";
import { useDispatch, useSelector } from "react-redux";
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
  import AlternateSearchBar from "./Altergooglelocation";
  import AlternateSearchBar1 from "./Altergooglelocation1";
  import DateSearch1 from "@/components/activity-list/activity-list-v3/DateSearch1";
import DateSearch2 from "@/components/activity-list/activity-list-v3/DateSearch2";
import Pickuptime from "@/components/activity-single/filter-box3/Pickuptime";
import Pickuptime1 from "@/components/activity-single/filter-box4/Pickuptime1";
import SearchZone from "@/components/activity-list/activity-list-v3/LocationZoneSearch";
import Pickuptimezone from "@/components/activity-list/activity-list-v3/Pickuptimezone";
import DateSearchZone from "@/components/activity-list/activity-list-v3/DateSearchZone";

const AlternateFilterSearchBox = ({ Location }) => {
    const dispatch = useDispatch();
  
    const [startLocation, setStartLocation] = useState("");
    const [startZone, setStartZone] = useState("");
    const [endLocation, setEndLocation] = useState("");
    const [endZone, setEndZone] = useState("");
    const [hourlyStartLocation, setHourlyStartLocation] = useState("");
    const [dateA, setDateA] = useState("");
    const [dateB, setDateB] = useState("");
    const [zoneDate, setZoneDate] = useState("");
    const selectedPort = useSelector((state) => state.localtour.selectedPort);
    const [startCoords, setStartCoords] = useState("");
    const [endCoords, setEndCoords] = useState("");
    const [pickupTimeA, setPickupTimeA] = useState("");
    const [pickupTimeB, setPickupTimeB] = useState("");
    const [pickupTimeZone, setPickupTimeZone] = useState("");
    const zone_on = useSelector((state) => state.auth.zone_on);
  
    const [isValidationOn, setIsValidationOn] = useState(false);
    const [startAutoFilled, setStartAutoFilled] = useState(false);
    const [endAutoFilled, setEndAutoFilled] = useState(false);
    const [hourlyAutoFilled, setHourlyAutoFilled] = useState(false);
    const currentBooking = useSelector((state) => state.localtour.selectbooking);
    const pickupType = useSelector((state) => state.localtour.picktype);
    const [localDropType, setLocalDropType] = useState("");
    const [hasTimeA, setHasTimeA] = useState(false);
    const [hasTimeB, setHasTimeB] = useState(false);
    const [hasZoneTime, setHasZoneTime] = useState(false);
    const bookingDetails = useSelector((state) => state.viewDetails.bookings);
  
    // Add effect to preserve state when port type changes
    useEffect(() => {
      if (selectedPort === "Point To Point") {
        // Preserve Point To Point state
        if (startLocation && startCoords) {
          setStartAutoFilled(true);
        }
        if (endLocation && endCoords) {
          setEndAutoFilled(true);
        }
      } else if (selectedPort === "Hourly") {
        // Preserve Hourly state
        if (hourlyStartLocation && startCoords) {
          setHourlyAutoFilled(true);
        }
      }
    }, [selectedPort, startLocation, endLocation, hourlyStartLocation, startCoords, endCoords]);
  
    const triggerSearch = () => {
      setIsValidationOn(true);
  
      if (selectedPort === "Point To Point") {
        const valid = startAutoFilled && endAutoFilled;
  
        dispatch(setentrypickup(startLocation));
        dispatch(setentrydropoff(endLocation));
        dispatch(setentrytime(pickupTimeA));
        dispatch(setpickdate(dateA));
        dispatch(setSelectionType(selectedPort));
        dispatch(setPickupPlaceid(startCoords));
        dispatch(setDropoffPlaceid(endCoords));
        dispatch(setZonetype(""));
  
        if (valid && hasTimeA) {
          setTimeout(() => {
            dispatch(fetchVehicles());
          }, 500);
        }
      } else if (selectedPort === "Hourly") {
        const valid = hourlyAutoFilled;
  
        dispatch(setexitpickup(hourlyStartLocation));
        dispatch(setpickdate(dateB));
        dispatch(setentrytime(pickupTimeB));
        dispatch(setentrytime1(pickupTimeB));
        dispatch(setSelectionType(selectedPort));
        dispatch(setPickupPlaceid(startCoords));
        dispatch(setDropoffPlaceid(null));
        dispatch(setZonetype(""));
  
        if (valid && hasTimeB) {
          setTimeout(() => {
            dispatch(fetchVehicles());
          }, 500);
        }
      } else if (selectedPort === "Local Transfer") {
        dispatch(setPickupZoneid(startZone));
        dispatch(setDropoffZoneid(endZone));
        dispatch(setentrypickup(startCoords));
        dispatch(setentrydropoff(endCoords));
        dispatch(setSelectionType(selectedPort));
        dispatch(setDroptype(localDropType));
        dispatch(setentrytime(pickupTimeZone));
        dispatch(setpickdate(zoneDate));
        dispatch(setZonetype("zone"));
  
        if (startZone && endZone && pickupTimeZone && zoneDate && localDropType) {
          setTimeout(() => {
            dispatch(fetchZoneVehicles());
          }, 500);
        }
      }
    };

    // Add handler for port type change
    const handlePortChange = (e) => {
      const newPortType = e.target.value;
      dispatch(setSelectedPort(newPortType));
      dispatch(resetVehicles1());
      setIsValidationOn(false);
      
      // Preserve location state when switching between port types
      if (newPortType === "Point To Point") {
        if (startLocation && startCoords) {
          setStartAutoFilled(true);
        }
        if (endLocation && endCoords) {
          setEndAutoFilled(true);
        }
      } else if (newPortType === "Hourly") {
        if (hourlyStartLocation && startCoords) {
          setHourlyAutoFilled(true);
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
              Select Journey Type:
            </label>
            <select
              className="form-control"
              value={selectedPort}
              onChange={handlePortChange}
            >
              {selectedPort === "" && (
                <option value="" disabled>
                  Select the type..
                </option>
              )}
              <option value="Point To Point">Point To Point</option>
              <option value="Hourly">Hourly</option>
              {(zone_on === 1 && 
                bookingDetails && 
                (bookingDetails.hotel?.length > 0 || 
                 bookingDetails.attraction?.length > 0 || 
                 bookingDetails.restaurant?.length > 0)) && (
                <option value="Local Transfer">Local Transfer</option>
              )}
            </select>
          </div>

          {/* Second Section - Location Search */}
          <div className="location-search-wrapper">
  {selectedPort === "Point To Point" ? (
    <AlternateSearchBar
      pickupAltLocation={startLocation}
      setPickupAltLocation={setStartLocation}
      dropoffAltLocation={endLocation}
      setDropoffAltLocation={setEndLocation}
      setPickupAltLatLng={setStartCoords}
      setDropoffAltLatLng={setEndCoords}
      Location={Location}
      validationOn={isValidationOn}
      setPickupAltFromAutocomplete={setStartAutoFilled}
      setDropoffAltFromAutocomplete={setEndAutoFilled}
    />
  ) : selectedPort === "Hourly" ? (
    <AlternateSearchBar1
      originLocation={hourlyStartLocation}
      setOriginLocation={setHourlyStartLocation}
      setOriginLatLng={setStartCoords}
      Location={Location}
      validationActive={isValidationOn}
      markFromAutocomplete={setHourlyAutoFilled}
    />
  ) : selectedPort === "Local Transfer" ? (
    <SearchZone
      currentbooking={currentBooking}
      picktype={pickupType}
      setdroptype={setLocalDropType}
      droptype={localDropType}
      setPickUpLocation={setStartZone}
      setPickupLatLng={setStartCoords}
      setDropoffLatLng={setEndCoords}
      dropOffLocation={endCoords}
      validationTriggered={isValidationOn}
      setDropOffLocation={setEndZone}
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
              entryytime={pickupTimeA}
              setentryytime={setPickupTimeA}
              setTime={setHasTimeA}
            />
          ) : selectedPort === "Local Transfer" ? (
            <Pickuptimezone
              entryytime={pickupTimeZone}
              setentryytime={setPickupTimeZone}
              setTime={setHasZoneTime}
            />
          ) : (
            <Pickuptime1
              entryytime={pickupTimeB}
              setentryytime={setPickupTimeB}
              setTime={setHasTimeB}
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
              selectedDate={dateA}
              setSelectedDate={(date) => {
                console.log("Selected Pickup Date:", date);
                setDateA(date);
                // Also ensure location data is preserved when date changes
              }}
            />
          ) : selectedPort === "Local Transfer" ? (
            <DateSearchZone
              selectedDate1={zoneDate}
              setSelectedDate1={setZoneDate}
            />
          ) : (
            <DateSearch2
              selectedDate1={dateB}
              setSelectedDate1={setDateB}
            />
          )}
        </div>

        {/* Fifth Section - Search Button */}
        <div className="search-button-wrapper">
          <button
            className="mainSearch__submit button -dark-1 py-15 px-35 rounded-4 bg-blue-1 text-white"
            onClick={triggerSearch}
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
        .button-grid-v2 {
          grid-template-columns: 1fr 1fr;
          gap: 20px;
        }

        .port-selection-wrapper {
          max-width: 100%;
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
  
  export default AlternateFilterSearchBox;
  