import React, { useState, useEffect, useCallback } from "react";
import { useDispatch } from "react-redux";
import DateSearch from "../common/DateSearch";
import LocationSearch from "./LocationSearch";
import {
  fetchGuides,
  setentrypickup,
  // setentrydropoff,
  setpickupdate,
  // setPickupPlaceid,
  // setDropoffPlaceid,
  resetguide,
} from "@/slice/tourguide/guideslice";
import moment from "moment";

const MainFilterSearchBox = ({ Location }) => {
  const dispatch = useDispatch();
  // useEffect(() => {
  //   dispatch(resetguide());
  // }, [dispatch]);

  // State for storing the pickup and dropoff locations
  const [pickUpLocation, setPickUpLocation] = useState("");
  // const [dropOffLocation, setDropOffLocation] = useState("");
  // const [pickUpPlaceId, setpickUpPlaceId] = useState(""); // New state for pick-up place_id
  // const [dropOffPlaceId, setdropOffPlaceId] = useState(""); // New state for drop-off place_id
  const [selectedDate, setSelectedDate] = useState("");
  const [locationError, setLocationError] = useState(false); // Add this state for tracking validation errors
  // const Location = Location;
  // Handler for the button search click event
  const buttonsearch = useCallback(() => {
    // Validate that we have both location and date
    if (!pickUpLocation || pickUpLocation.trim() === '') {
      setLocationError(true);
      console.log("Please select a valid location from the dropdown");
      return;
    }

    if (!selectedDate) {
      console.log("Please select a date");
      return;
    }

    setLocationError(false);
    
    // Format the date to match the API requirements
    const formattedDate = moment(selectedDate).format("YYYY-MM-DD");

    // Update Redux state
    dispatch(setentrypickup(pickUpLocation));
    dispatch(setpickupdate(formattedDate));

    // Call the fetchGuides API with the required parameters
    dispatch(fetchGuides({
      city: pickUpLocation,
      date: formattedDate
    }));
  }, [dispatch, pickUpLocation, selectedDate]);

  return (
    <>
      <div className="mainSearch -col-2 bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-4 mt-30">
        <div className="button-grid items-center">
          {/* LocationSearch will update the pickUpLocation and dropOffLocation */}
          <LocationSearch
            pickUpLocation={pickUpLocation}
            setPickUpLocation={setPickUpLocation}
            // pickUpPlaceId={pickUpPlaceId}
            // setPickupPlaceid={setpickUpPlaceId}
            // dropOffLocation={dropOffLocation}
            // setDropOffLocation={setDropOffLocation}
            // dropOffPlaceId={dropOffPlaceId}
            // setDropoffPlaceid={setdropOffPlaceId}
            // Location={Location}
          />
          {/* End Location */}

          <div className="searchMenu-date px-30 lg:py-20 lg:px-0 js-form-dd js-calendar">
            <div className="d-flex">
              {/* Pick Up Date Section */}
              <div className="ml-10">
                <h4
                  className="text-15 fw-500 ls-2 lh-16"
                  style={{ marginTop: "10px" }}
                >
                  Pick Up Date
                </h4>
                <DateSearch
                  selectedDate={selectedDate}
                  setSelectedDate={setSelectedDate}
                />
              </div>
            </div>
          </div>

          {/* End check-in-out */}

          <div className="button-item">
            <button
              className="mainSearch__submit button -dark-1 py-20 px-40 col-12 rounded-4 bg-blue-1 text-white"
              onClick={buttonsearch}
            >
              <i className="icon-search text-20 mr-10" />
              Search
            </button>
          </div>
          {/* End search button_item */}
        </div>
      </div>
    </>
  );
};

export default MainFilterSearchBox;
