import React, { useEffect, useRef, useState } from "react";
import { useSelector } from "react-redux";

const ExitSearchInput = ({
  originLocation,
  setOriginLocation,
  setOriginLatLng,
  Location,
  validationActive = false,
  markFromAutocomplete,
}) => {
  const originRef = useRef(null);
  const [originValid, setOriginValid] = useState(true);
  const selectedPortType = useSelector((state) => state.localtour.selectedPort);

  useEffect(() => {
    if (!window.google || !window.google.maps || !window.google.maps.places) {
      console.error("Google Maps API not loaded.");
      return;
    }

    const setupAutocomplete = (
      inputFieldId,
      autocompleteRef,
      updateLocation,
      updateLatLng,
      updateValidity,
      notifyParent
    ) => {
      const element = document.getElementById(inputFieldId);
      if (!element) return;

      autocompleteRef.current = new window.google.maps.places.Autocomplete(element, {
        types: [],
        componentRestrictions: {
          country: Array.isArray(Location) ? Location : [Location],
        },
      });

      autocompleteRef.current.addListener("place_changed", () => {
        const placeInfo = autocompleteRef.current.getPlace();
        if (placeInfo && placeInfo.geometry?.location) {
          const latitude = placeInfo.geometry.location.lat();
          const longitude = placeInfo.geometry.location.lng();

          const locationLabel =
            placeInfo.name ||
            placeInfo.address_components?.[0]?.long_name ||
            "Unknown";

          updateLocation(locationLabel);
          updateLatLng({ lat: latitude, lng: longitude });
          updateValidity(true);
          notifyParent(true);
        }
      });
    };

    setupAutocomplete(
      "origin-location-input",
      originRef,
      setOriginLocation,
      setOriginLatLng,
      setOriginValid,
      markFromAutocomplete
    );

    return () => {
      if (originRef.current)
        window.google.maps.event.clearInstanceListeners(originRef.current);
    };
}, [Location, setOriginLocation, setOriginLatLng, markFromAutocomplete]);

  const onOriginInputChange = (e) => {
    setOriginLocation(e.target.value);
    setOriginValid(false);
    markFromAutocomplete(false);
  };

  const shouldShowError = validationActive && originLocation && !originValid;

  return (
    <div className="searchMenu-loc pr-10 pl-10 lg:py-20 lg:px-0 js-form-dd js-liverSearch">
      <div className="d-flex justify-between">
        <div className="flex-1 mr-10">
          <div className="d-flex">
            <i className="icon-location-2 text-20 text-light-1 mt-5"></i>
            <div className="ml-10 flex-grow-1">
              <h4 className="text-15 fw-500 ls-2 lh-16 mb-15">Pick Up Location</h4>
              <div className="text-15 text-light-1 ls-2 lh-20">
                <input
                  id="origin-location-input"
                  autoComplete="off"
                  type="search"
                  placeholder="Where is your pick up?"
                  className="js-search js-dd-focus w-full pac-item"
                  value={originLocation}
                  onChange={onOriginInputChange}
                  disabled={
                    !selectedPortType ||
                    (selectedPortType !== "Point To Point" && selectedPortType !== "Hourly")
                  }
                />
                {shouldShowError && (
                  <div className="text-red-500 mt-5 text-14">
                    *Select location from dropdown
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>

      <style>
        {`
          .pac-container {
            z-index: 10000 !important;
            background-color: #fff !important;
            border: 1px solid #ccc !important;
            width: 100% !important;
            min-width: 200px !important;
            max-width: 250px !important;
          }
          .pac-item {
            font-size: 15px !important;
            font-weight: 520 !important;
            color: #000 !important;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: ellipsis !important;
            width: 350px !important;
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
          @media (max-width: 1730px) { .pac-item { width: 250px !important; } }
          @media (max-width: 1400px) { .pac-item { width: 200px !important; } }
          @media (max-width: 1200px) { .pac-item { width: 300px !important; } }
          @media (max-width: 1024px) { .pac-item { width: 300px !important; } }
          @media (max-width: 768px) { .pac-item { width: 200px !important; } }
          @media (max-width: 480px) { .pac-item { width: 80px !important; } }
        `}
      </style>
    </div>
  );
};

export default ExitSearchInput;
