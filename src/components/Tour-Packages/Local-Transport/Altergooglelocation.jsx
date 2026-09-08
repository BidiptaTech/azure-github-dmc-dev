import React, { useEffect, useRef, useState } from "react";
import { useSelector } from "react-redux";

const AlternateSearchBar = ({
  pickupAltLocation,
  setPickupAltLocation,
  dropoffAltLocation,
  setDropoffAltLocation,
  setPickupAltLatLng,
  setDropoffAltLatLng,
  Location,
  validationOn = false,
  setPickupAltFromAutocomplete,
  setDropoffAltFromAutocomplete,
}) => {
  const pickupRef = useRef(null);
  const dropoffRef = useRef(null);
  const [pickupValid, setPickupValid] = useState(true);
  const [dropoffValid, setDropoffValid] = useState(true);
  const selectedPort = useSelector((state) => state.localtour.selectedPort);

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
        types: [], // Allow all locations
        componentRestrictions: {
          country: Array.isArray(Location) ? Location : [Location],
        },
      });

      ref.current.addListener("place_changed", () => {
        const place = ref.current.getPlace();
        if (place && place.geometry && place.geometry.location) {
          const lat = place.geometry.location.lat();
          const lng = place.geometry.location.lng();

          // Extract only the main name (highlighted) or first part of the address
          let formattedLocation =
            place.name ||
            place.address_components?.[0]?.long_name ||
            "Unknown Location";

          setLocation(formattedLocation);
          setLatLng({ lat, lng });
          setIsValid(true); // Mark as selected from autocomplete
          setParentValid(true); // Update parent state
        }
      });
    };

    initializeAutocomplete(
      "alt-pickup-input",
      pickupRef,
      setPickupAltLocation,
      setPickupAltLatLng,
      setPickupValid,
      setPickupAltFromAutocomplete
    );

    initializeAutocomplete(
      "alt-dropoff-input",
      dropoffRef,
      setDropoffAltLocation,
      setDropoffAltLatLng,
      setDropoffValid,
      setDropoffAltFromAutocomplete
    );

    return () => {
      if (pickupRef.current) {
        window.google.maps.event.clearInstanceListeners(pickupRef.current);
      }
      if (dropoffRef.current) {
        window.google.maps.event.clearInstanceListeners(dropoffRef.current);
      }
    };
  }, [
    Location,
    setPickupAltLocation,
    setDropoffAltLocation,
    setPickupAltLatLng,
    setDropoffAltLatLng,
    setPickupAltFromAutocomplete,
    setDropoffAltFromAutocomplete,
  ]);

  const handlePickupAltChange = (e) => {
    setPickupAltLocation(e.target.value);
    setPickupValid(false);
    setPickupAltFromAutocomplete(false);
  };

  const handleDropoffAltChange = (e) => {
    setDropoffAltLocation(e.target.value);
    setDropoffValid(false);
    setDropoffAltFromAutocomplete(false);
  };

  const showPickupError = validationOn && pickupAltLocation && !pickupValid;
  const showDropoffError = validationOn && dropoffAltLocation && !dropoffValid;

  return (
    <div className="searchMenu-loc pr-10 pl-10 lg:py-20 lg:px-0">
      <div className="d-flex justify-between">
        {/* Pickup Field */}
        <div className="flex-1 mr-10">
          <div className="d-flex">
            <i className="icon-location-2 text-20 text-light-1 mt-5"></i>
            <div className="ml-10 flex-grow-1">
              <h4 className="text-15 fw-500 ls-2 lh-16 mb-15">
                Alternate Pick Up Location
              </h4>
              <input
                id="alt-pickup-input"
                autoComplete="off"
                type="search"
                placeholder="Where is your pick up?"
                className="js-search js-dd-focus w-full pac-item"
                value={pickupAltLocation}
                onChange={handlePickupAltChange}
                disabled={
                  !selectedPort ||
                  (selectedPort !== "Point To Point" && selectedPort !== "Hourly")
                }
              />
              {showPickupError && (
                <div className="text-red-500 mt-5 text-14">
                  *Select location from dropdown
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Dropoff Field */}
        <div className="flex-1 ml-10">
          <div className="d-flex">
            <i className="icon-location-2 text-20 text-light-1 mt-5"></i>
            <div className="ml-10 flex-grow-1">
              <h4 className="text-15 fw-500 ls-2 lh-16 mb-15">
                Alternate Drop Off Location
              </h4>
              <input
                id="alt-dropoff-input"
                autoComplete="off"
                type="search"
                placeholder="Where is your drop off?"
                className="js-search js-dd-focus w-full pac-item"
                value={dropoffAltLocation}
                onChange={handleDropoffAltChange}
                disabled={
                  !selectedPort ||
                  (selectedPort !== "Point To Point" && selectedPort !== "Hourly")
                }
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

      {/* CSS Fixes for Google Autocomplete */}
      <style jsx>{`
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
            width: 300px !important;
          }
        }

        @media (max-width: 768px) {
          .pac-item {
            width: 200px !important;
          }
        }

        @media (max-width: 480px) {
          .pac-item {
            width: 80px !important;
          }
        }
      `}</style>
    </div>
  );
};

export default AlternateSearchBar;
