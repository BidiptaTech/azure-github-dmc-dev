import React, { useEffect, useRef, useState } from "react";
import { useSelector, useDispatch } from "react-redux";

const SearchZone = ({
  currentbooking,
  picktype,
  setdroptype,
  dropOffLocation,
  setDropOffLocation,
  setPickupLatLng,
  setDropoffLatLng,
  setPickUpLocation,
  validationTriggered,
}) => {
  const dispatch = useDispatch();
  const autocompletePickUpRef = useRef(null);
  const autocompleteDropOffRef = useRef(null);
  const [isPickupValid, setIsPickupValid] = useState(true);
  const [isDropoffValid, setIsDropoffValid] = useState(true);
  const [showDropdown, setShowDropdown] = useState(false);
  const selectedPort = useSelector((state) => state.localtour.selectedPort);
  const zone = useSelector((state) => state.localtour.zone);
  const [displayValue, setDisplayValue] = useState("");
  console.log("zone", zone);
  useEffect(() => {
    if (picktype === "hotel") {
      setPickUpLocation(currentbooking?.hotelDetails?.hotel_id);
      setPickupLatLng(currentbooking?.hotelDetails?.hotel_name);
    } else if (picktype === "attraction") {
      setPickUpLocation(currentbooking?.service_details?.attraction_id);
      setPickupLatLng(currentbooking?.service_details?.name);
    } else if (picktype === "restaurant") {
      setPickUpLocation(currentbooking?.service_details?.restaurant_id);
      setPickupLatLng(currentbooking?.service_details?.name);
    }
  });

  // useEffect(() => {
  //   if (!window.google || !window.google.maps || !window.google.maps.places) {
  //     console.error("Google Maps API not loaded.");
  //     return;
  //   }

  //   const initializeAutocomplete = (
  //     inputId,
  //     ref,
  //     setLocation,
  //     setLatLng,
  //     setIsValid,
  //     setParentValid
  //   ) => {
  //     const inputElement = document.getElementById(inputId);
  //     if (!inputElement) return;

  //     ref.current = new window.google.maps.places.Autocomplete(inputElement, {
  //       types: [], // Allow all locations
  //       componentRestrictions: {
  //         country: Array.isArray(Location) ? Location : [Location],
  //       },
  //     });

  //     ref.current.addListener("place_changed", () => {
  //       const place = ref.current.getPlace();
  //       if (place && place.geometry && place.geometry.location) {
  //         const lat = place.geometry.location.lat();
  //         const lng = place.geometry.location.lng();

  //         // Extract only the main name (highlighted) or first part of the address
  //         let formattedLocation =
  //           place.name ||
  //           place.address_components?.[0]?.long_name ||
  //           "Unknown Location";

  //         setLocation(formattedLocation);
  //         setLatLng({ lat, lng });
  //         setIsValid(true); // Mark as selected from autocomplete
  //         setParentValid(true); // Update parent state
  //       }
  //     });
  //   };

  //   initializeAutocomplete(
  //     "pick-up-input",
  //     autocompletePickUpRef,
  //     setPickUpLocation,
  //     setPickupLatLng,
  //     setIsPickupValid,
  //     setPickupFromAutocomplete
  //   );
  //   initializeAutocomplete(
  //     "drop-off-input",
  //     autocompleteDropOffRef,
  //     setDropOffLocation,
  //     setDropoffLatLng,
  //     setIsDropoffValid,
  //     setDropoffFromAutocomplete
  //   );

  //   return () => {
  //     if (autocompletePickUpRef.current)
  //       window.google.maps.event.clearInstanceListeners(
  //         autocompletePickUpRef.current
  //       );
  //     if (autocompleteDropOffRef.current)
  //       window.google.maps.event.clearInstanceListeners(
  //         autocompleteDropOffRef.current
  //       );
  //   };
  // }, [
  //   Location,
  //   setPickUpLocation,
  //   setDropOffLocation,
  //   setPickupLatLng,
  //   setDropoffLatLng,
  //   setPickupFromAutocomplete,
  //   setDropoffFromAutocomplete,
  // ]);

  // const handlePickupChange = (e) => {
  //   setPickUpLocation(e.target.value);
  //   setIsPickupValid(false);
  //   setPickupFromAutocomplete(false);
  // };

  const [searchTerm, setSearchTerm] = useState("");

  const handleDropoffChange = (e) => {
    const value = e.target.value;
    setDisplayValue(value);
    setDropOffLocation(value);
    setSearchTerm(value.toLowerCase());
    setIsDropoffValid(false);
    setShowDropdown(true);
    console.log("Dropdown should show:", showDropdown, "Zone data:", zone);
  };

  // Filter function to search across all options
  const filterItems = (items, term) => {
    if (!term) return items;
    return items.filter((item) => item.name.toLowerCase().includes(term));
  };

  const handleLocationSelect = (name, id, type) => {
    setDropOffLocation(id);
    setDropoffLatLng(name);
    setdroptype(type);
    setDisplayValue(name);
    console.log(
      "Selected drop-off location:",
      name,
      "with ID:",
      id,
      "Type:",
      type
    );
    setIsDropoffValid(true);
    setShowDropdown(false);
  };

  // Add this function to close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (
        event.target.id !== "drop-off-input" &&
        !event.target.closest(".location-dropdown")
      ) {
        setShowDropdown(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  return (
    <div className="searchMenu-loc pr-10 pl-10 lg:py-20 lg:px-0 js-form-dd js-liverSearch">
      <div className="d-flex justify-between">
        {/* Pick-up Location */}
        <div className="flex-1 mr-10">
          <div>
            <div className="d-flex">
              <i className="icon-location-2 text-20 text-light-1 mt-5"></i>
              <div className="ml-10 flex-grow-1">
                <h4 className="text-15 fw-500 ls-2 lh-16 mb-15">
                  Pick Up Location
                </h4>
                <div className="text-15 text-light-1 ls-2 lh-20">
                  <input
                    id="pick-up-input"
                    autoComplete="off"
                    type="search"
                    placeholder="Where is your pick up?"
                    className="js-search js-dd-focus w-full pac-item"
                    value={
                      (picktype === "hotel" &&
                        currentbooking?.hotelDetails?.hotel_name) ||
                      (picktype === "attraction" &&
                        currentbooking?.service_details?.name) ||
                      (picktype === "restaurant" &&
                        currentbooking?.service_details?.name) ||
                      ""
                    }
                    disabled
                  />
                  {/* {showPickupError && (
                    <div className="text-red-500 mt-5 text-14">
                      *Select location from dropdown
                    </div>
                  )} */}
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
                <div className="text-15 text-light-1 ls-2 lh-16 position-relative">
                  <input
                    id="drop-off-input"
                    autoComplete="off"
                    type="search"
                    placeholder="Where is your drop off?"
                    className="js-search js-dd-focus w-full pac-item"
                    value={displayValue}
                    onChange={handleDropoffChange}
                    onClick={() => {
                      setShowDropdown(true);
                      console.log("Input clicked, showing dropdown");
                    }}
                    disabled={picktype === ""}
                  />

                  {/* {!isDropoffValid && validationTriggered && (
                    <div className="text-red-500 mt-5 text-14">
                      *Please select a drop-off location
                    </div>
                  )} */}

                  {showDropdown && zone && zone.data && (
                    <div className="location-dropdown">
                      {zone.data.hotels && zone.data.hotels.length > 0 && (
                        <div className="location-category">
                          <div className="category-heading">Hotels</div>
                          {filterItems(zone.data.hotels, searchTerm).map(
                            (hotel) => (
                              <div
                                key={`hotel-${hotel.hotel_unique_id}`}
                                className="location-item"
                                onClick={() =>
                                  handleLocationSelect(
                                    hotel.name,
                                    hotel.hotel_unique_id,
                                    "hotel"
                                  )
                                }
                              >
                                {hotel.name}
                              </div>
                            )
                          )}
                          {filterItems(zone.data.hotels, searchTerm).length ===
                            0 &&
                            searchTerm && (
                              <div className="no-results">
                                No matching hotels
                              </div>
                            )}
                        </div>
                      )}

                      {zone.data.attractions &&
                        zone.data.attractions.length > 0 && (
                          <div className="location-category">
                            <div className="category-heading">Attractions</div>
                            {filterItems(zone.data.attractions, searchTerm).map(
                              (attraction) => (
                                <div
                                  key={`attraction-${attraction.attraction_id}`}
                                  className="location-item"
                                  onClick={() =>
                                    handleLocationSelect(
                                      attraction.name,
                                      attraction.attraction_id,
                                      "attraction"
                                    )
                                  }
                                >
                                  {attraction.name}
                                </div>
                              )
                            )}
                            {filterItems(zone.data.attractions, searchTerm)
                              .length === 0 &&
                              searchTerm && (
                                <div className="no-results">
                                  No matching attractions
                                </div>
                              )}
                          </div>
                        )}

                      {zone.data.restaurants &&
                        zone.data.restaurants.length > 0 && (
                          <div className="location-category">
                            <div className="category-heading">Restaurants</div>
                            {filterItems(zone.data.restaurants, searchTerm).map(
                              (restaurant) => (
                                <div
                                  key={`restaurant-${restaurant.restaurant_id}`}
                                  className="location-item"
                                  onClick={() =>
                                    handleLocationSelect(
                                      restaurant.name,
                                      restaurant.restaurant_id,
                                      "restaurant"
                                    )
                                  }
                                >
                                  {restaurant.name}
                                </div>
                              )
                            )}
                            {filterItems(zone.data.restaurants, searchTerm)
                              .length === 0 &&
                              searchTerm && (
                                <div className="no-results">
                                  No matching restaurants
                                </div>
                              )}
                          </div>
                        )}
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* CSS Fixes for Google Autocomplete and Custom Dropdown */}
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

          /* Custom dropdown styles */
          .position-relative {
            position: relative !important;
          }

          .location-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            max-height: 300px;
            overflow-y: auto;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 10001;
            display: block !important; /* Force display */
          }

          .location-category {
            margin-bottom: 10px;
          }

          .category-heading {
            padding: 8px 12px;
            font-weight: 600;
            background-color: #f5f5f5;
            color: #333;
            border-bottom: 1px solid #eee;
          }

          .location-item {
            padding: 8px 12px;
            cursor: pointer;
            transition: background-color 0.2s;
          }

          .location-item:hover {
            background-color: #f0f7ff;
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
        `}
      </style>
    </div>
  );
};

export default SearchZone;
