import React, { useEffect, useRef, useState, useCallback } from "react";

const SearchBar = ({
  exitpickUpLocation,
  setexitPickUpLocation,
  exitdropOffLocation,
  setexitDropOffLocation,
  setPickupLatLng,
  setDropoffLatLng,
  Location,
  validationTriggered = false,
  setPickupFromAutocomplete,
  setDropoffFromAutocomplete,
}) => {
  const autocompletePickUpPackageRef1 = useRef(null);
  const autocompleteDropOffPackageRef1 = useRef(null);
  const [isPickupValid, setIsPickupValid] = useState(true);
  const [isDropoffValid, setIsDropoffValid] = useState(true);
  const [isInitialized, setIsInitialized] = useState(false);
  const isMountedRef = useRef(true);

  // Define unique IDs for this component
  const pickupInputId = "exit-pick-up-input";
  const dropoffInputId = "exit-drop-off-input";

  // Debug logs
  console.log("LocationSearch1 Props:", {
    exitpickUpLocation,
    exitdropOffLocation,
    Location,
    validationTriggered,
  });

  // Cleanup function to clear autocomplete listeners
  const cleanupAutocomplete = useCallback(() => {
    if (autocompletePickUpPackageRef1.current) {
      window.google.maps.event.clearInstanceListeners(
        autocompletePickUpPackageRef1.current
      );
      autocompletePickUpPackageRef1.current = null;
    }
    if (autocompleteDropOffPackageRef1.current) {
      window.google.maps.event.clearInstanceListeners(
        autocompleteDropOffPackageRef1.current
      );
      autocompleteDropOffPackageRef1.current = null;
    }
  }, []);

  // Initialize autocomplete function with proper error handling
  const initializeAutocomplete1 = useCallback((
    inputId,
    ref,
    setLocation,
    setLatLng,
    setIsValid,
    setParentValid
  ) => {
    if (!isMountedRef.current) return;

    const inputElement = document.getElementById(inputId);
    if (!inputElement) {
      console.error(`Input element with ID ${inputId} not found`);
      return;
    }

    try {
      console.log(`Initializing autocomplete for ${inputId}`, {
        restrictCountry: Array.isArray(Location) ? Location : [Location],
      });
      
      ref.current = new window.google.maps.places.Autocomplete(inputElement, {
        types: [], // Allow all locations
        componentRestrictions: {
          country: Array.isArray(Location) ? Location : [Location],
        },
      });

      ref.current.addListener("place_changed", () => {
        if (!isMountedRef.current) return;

        console.log(`Place changed for ${inputId}`);
        const place = ref.current.getPlace();
        console.log("Place details:", place);
        
        if (place && place.geometry && place.geometry.location) {
          const lat = place.geometry.location.lat();
          const lng = place.geometry.location.lng();

          // Extract only the main name (highlighted) or first part of the address
          let formattedLocation =
            place.name ||
            place.address_components?.[0]?.long_name ||
            "Unknown Location";

          console.log(`Setting location for ${inputId}:`, formattedLocation);
          setLocation(formattedLocation);
          setLatLng({ lat, lng });
          setIsValid(true); // Mark as selected from autocomplete
          setParentValid(true); // Update parent state
        } else {
          console.error(`No geometry found for place from ${inputId}`);
        }
      });
    } catch (error) {
      console.error(`Error initializing autocomplete for ${inputId}:`, error);
    }
  }, [Location]);

  // Setup autocomplete with proper lifecycle management
  useEffect(() => {
    // Don't re-initialize if already done
    if (isInitialized) return;

    // Check if Google Maps API is loaded
    if (!window.google || !window.google.maps || !window.google.maps.places) {
      console.error("Google Maps API not loaded or places library missing.");
      return;
    }

    console.log("Google Maps API is available, setting up autocomplete");
    
    // Check if input elements exist before initializing
    const checkAndInitialize = () => {
      if (!isMountedRef.current) return;

      const pickupInput = document.getElementById(pickupInputId);
      const dropoffInput = document.getElementById(dropoffInputId);
      
      if (!pickupInput) {
        console.error(`Pickup input element not found with ID: ${pickupInputId}`);
        return;
      }
      
      if (!dropoffInput) {
        console.error(`Dropoff input element not found with ID: ${dropoffInputId}`);
        return;
      }

      // Initialize autocomplete for both inputs
      initializeAutocomplete1(
        pickupInputId,
        autocompletePickUpPackageRef1,
        setexitPickUpLocation,
        setPickupLatLng,
        setIsPickupValid,
        setPickupFromAutocomplete
      );
      
      initializeAutocomplete1(
        dropoffInputId,
        autocompleteDropOffPackageRef1,
        setexitDropOffLocation,
        setDropoffLatLng,
        setIsDropoffValid,
        setDropoffFromAutocomplete
      );

      setIsInitialized(true);
    };

    // Use multiple attempts with increasing delays to ensure DOM is ready
    const timeoutId = setTimeout(checkAndInitialize, 100);
    const timeoutId2 = setTimeout(() => {
      if (!isInitialized && isMountedRef.current) {
        checkAndInitialize();
      }
    }, 500);

    return () => {
      clearTimeout(timeoutId);
      clearTimeout(timeoutId2);
    };
  }, [initializeAutocomplete1, isInitialized, setexitPickUpLocation, setexitDropOffLocation, setPickupLatLng, setDropoffLatLng, setPickupFromAutocomplete, setDropoffFromAutocomplete]);

  // Component unmount cleanup
  useEffect(() => {
    return () => {
      isMountedRef.current = false;
      cleanupAutocomplete();
    };
  }, [cleanupAutocomplete]);

  const handlePickupChange = useCallback((e) => {
    setexitPickUpLocation(e.target.value);
    setIsPickupValid(false);
    setPickupFromAutocomplete(false);
  }, [setexitPickUpLocation, setPickupFromAutocomplete]);

  const handleDropoffChange = useCallback((e) => {
    setexitDropOffLocation(e.target.value);
    setIsDropoffValid(false);
    setDropoffFromAutocomplete(false);
  }, [setexitDropOffLocation, setDropoffFromAutocomplete]);

  // Only show validation errors if validationTriggered is true and there's a value in the input
  const showPickupError =
    validationTriggered && exitpickUpLocation && !isPickupValid;
  const showDropoffError =
    validationTriggered && exitdropOffLocation && !isDropoffValid;

  return (
    <div className="searchMenu-loc lg:py-10 lg:px-0 js-form-dd js-liverSearch">
      <div className="d-flex justify-between">
        {/* Pick-up Location */}
        <div className="flex-1">
          <div>
            <div className="d-flex ml-10">
              <i className="icon-location-2 text-20 text-light-1 mt-5"></i>
              <div className="ml-10 flex-grow-1">
                <h4 className="text-15 fw-500 ls-2 lh-16 mb-15">
                  Pick Up Location
                </h4>
                <div className="text-15 text-light-1 ls-2 lh-20">
                  <input
                    id={pickupInputId}
                    autoComplete="off"
                    type="search"
                    placeholder="Where is your pick up?"
                    className="js-search js-dd-focus w-full pac-item"
                    value={exitpickUpLocation}
                    onChange={handlePickupChange}
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
                    id={dropoffInputId}
                    autoComplete="off"
                    type="search"
                    placeholder="Where is your drop off?"
                    className="js-search js-dd-focus w-full pac-item"
                    value={exitdropOffLocation}
                    onChange={handleDropoffChange}
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

.searchMenu-loc {
  width: 100%;
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
