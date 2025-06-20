import React, { useEffect, useRef, useState } from "react";
import { useSelector } from "react-redux";

const SearchBar = ({
  pickUpLocation,
  setPickUpLocation,
  dropOffLocation,
  setDropOffLocation,
  setPickupLatLng,
  setDropoffLatLng,
  Location,
  validationTriggered = false,
  setPickupFromAutocomplete,
  setDropoffFromAutocomplete,
}) => {
  const autocompletePickUpPackageRef = useRef(null);
  const autocompleteDropOffPackageRef = useRef(null);
  const [isPickupValid, setIsPickupValid] = useState(true);
  const [isDropoffValid, setIsDropoffValid] = useState(true);
  const SelectedPort = useSelector((state) => state.pickupDrop.selectedPort);
  

  useEffect(() => {
    if (!window.google || !window.google.maps || !window.google.maps.places) {
      console.error("Google Maps API not loaded.");
      return;
    }

    const initializeAutocomplete = (
      inputId,
      ref,
      setLocation,
      setLatLng,
      setIsValid,
      setParentValid
    ) => {
      const inputElement = document.getElementById(inputId);
      if (!inputElement) return;

      ref.current = new window.google.maps.places.Autocomplete(inputElement, {
        types: [], // Allow any location
        componentRestrictions: {
          country: Array.isArray(Location) ? Location : [Location],
        },
      });

      ref.current.addListener("place_changed", () => {
        const place = ref.current.getPlace();
        if (place && place.geometry && place.geometry.location) {
          const lat = place.geometry.location.lat();
          const lng = place.geometry.location.lng();

          // ✅ Use only the highlighted primary name (or first part of address)
          let formattedLocation =
            place.name || place.address_components?.[0]?.long_name || "";

          setLocation(formattedLocation);
          setLatLng({ lat, lng });
          setIsValid(true); // Mark as selected from autocomplete
          setParentValid(true); // Update parent state
        }
      });
    };

    initializeAutocomplete(
      "pick-up-input",
      autocompletePickUpPackageRef,
      setPickUpLocation,
      setPickupLatLng,
      setIsPickupValid,
      setPickupFromAutocomplete
    );
    initializeAutocomplete(
      "drop-off-input",
      autocompleteDropOffPackageRef,
      setDropOffLocation,
      setDropoffLatLng,
      setIsDropoffValid,
      setDropoffFromAutocomplete
    );

    return () => {
      if (autocompletePickUpPackageRef.current)
        window.google.maps.event.clearInstanceListeners(
          autocompletePickUpPackageRef.current
        );
      if (autocompleteDropOffPackageRef.current)
        window.google.maps.event.clearInstanceListeners(
          autocompleteDropOffPackageRef.current
        );
    };
  }, [
    Location,
    setPickUpLocation,
    setDropOffLocation,
    setPickupLatLng,
    setDropoffLatLng,
    setPickupFromAutocomplete,
    setDropoffFromAutocomplete,
  ]);

  const handlePickupChange = (e) => {
    setPickUpLocation(e.target.value);
    setIsPickupValid(false);
    setPickupFromAutocomplete(false);
  };

  const handleDropoffChange = (e) => {
    setDropOffLocation(e.target.value);
    setIsDropoffValid(false);
    setDropoffFromAutocomplete(false);
  };

  // Only show validation errors if validationTriggered is true and there's a value in the input
  const showPickupError =
    validationTriggered && pickUpLocation && !isPickupValid;
  const showDropoffError =
    validationTriggered && dropOffLocation && !isDropoffValid;

  return (
    <div className="searchMenu-loc pr-10 pl-10 lg:py-20 lg:px-0">
      <div className="d-flex justify-between">
        {/* Pick-up Location */}
        <div className="flex-1 mr-10">
          <div>
            <div className="d-flex ml-10">
              <i className="icon-location-2 text-20 text-light-1 mt-5"></i>
              <div className="ml-10 flex-grow-1">
                <h4 className="text-15 fw-500 ls-2 lh-16 mb-15">
                  Pick Up Location
                </h4>
                <div className="text-15 text-light-1 ls-2 lh-16">
                  <input
                    id="pick-up-input"
                    autoComplete="off"
                    type="search"
                    placeholder="Where is your pick up?"
                    className="js-search js-dd-focus w-full pac-item"
                    value={pickUpLocation}
                    onChange={handlePickupChange}
                    // disabled={
                    //   !SelectedPort ||
                    //   (SelectedPort !== "Entry Port" &&
                    //     SelectedPort !== "Exit Port")
                    // }
                  />
                  {showPickupError && (
                    <div className="text-red-500 mt-5 text-14">
                      *Select location from dropdown
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Drop-off Location */}
        <div className="flex-1 ml-10">
          <div>
            <div className="d-flex">
              <i className="icon-location-2 text-20 text-light-1 mt-5"></i>
              <div className="ml-10 flex-grow-1">
                <h4 className="text-15 fw-500 ls-2 lh-16 mb-15">
                  Drop Off Location
                </h4>
                <div className="text-15 text-light-1 ls-2 lh-16">
                  <input
                    id="drop-off-input"
                    autoComplete="off"
                    type="search"
                    placeholder="Where is your drop off?"
                    className="js-search js-dd-focus w-full pac-item"
                    value={dropOffLocation}
                    onChange={handleDropoffChange}
                    // disabled={
                    //   !SelectedPort ||
                    //   (SelectedPort !== "Entry Port" &&
                    //     SelectedPort !== "Exit Port")
                    // }
                  />
                  {showDropoffError && (
                    <div className="text-red-500 mt-5 text-14">
                      *Select location from dropdown
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* CSS Fixes for Google Autocomplete */}
      <style>
        {`
          .pac-container {
  z-index: 10000 !important;
  background-color: #fff !important;
   border: 1px solid #ccc !important;
  width: 100% !important; /* Expands dropdown width */
  min-width: 200px !important; /* Ensures it's not too small */
  max-width: 250px !important; /* Adjust as needed */
}

.pac-item {
  font-size: 15px !important;
  font-weight: 520 !important;
   color: #000 !important;
  // padding: 10px !important;
  white-space: normal !important;
  overflow: visible !important;
  text-overflow: ellipsis !important;
  width: 250px !important;
}

.pac-item-query {
  font-weight: bold !important;
  color: #000 !important;
}

.text-red-500 {
  color: #ef4444 !important;
}

.mt-5 {
  margin-top: 5px !important;
}

.text-14 {
  font-size: 14px !important;
}
  @media (max-width: 1730px) {
    .pac-item {
      width: 250px !important;
    }
  }
  @media (max-width: 1400px) {
    .pac-item {
      width: 200px !important;
    }
  }
  @media (max-width: 1200px) {
    .pac-item {
      width: 100px !important;
    }
  }
  @media (max-width: 1024px) {
    .pac-item {
      width: 100px !important;
    }
  }
  @media (max-width: 768px) {
    .pac-item {
      width: 80px !important;
    }
  }
  @media (max-width: 480px) {
    .pac-item {
      width: 80px !important;
    }
  }
        `}
      </style>
    </div>
  );
};

export default SearchBar;
