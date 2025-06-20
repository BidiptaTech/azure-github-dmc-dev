import React, { useEffect, useRef, useState } from "react";
import { useSelector } from "react-redux";

const PortLocation2 = ({
  exitpickUpLocation,
  setexitPickUpLocation,
  setType,
  setPickdropType,
  setId,
  setpickId,
  setdropId,
  validationTriggered = false,
  setPickupFromAutocomplete,
  setDropoffFromAutocomplete,
  disabled = false,
  portType,
}) => {
  const autocompletePickUpRef = useRef(null);
  const autocompleteDropOffRef = useRef(null);
  const [isPickupValid, setIsPickupValid] = useState(true);
  const [isDropoffValid, setIsDropoffValid] = useState(true);
  const SelectedPort = useSelector((state) => state.pickupDrop.selectedPort);
  const shotels = useSelector((state) => state.pickupDrop.portCityData?.data);
  console.log("hotelsss", shotels);
  const zones = useSelector((state) => state.pickupDrop.zone?.data);
  console.log("zones", zones);

  // Add caching mechanism similar to PortLocation
  const [initialHotelsLoaded, setInitialHotelsLoaded] = useState(false);
  const [cachedHotels, setCachedHotels] = useState([]);
  console.log("portType", portType);
  // Cache hotels when first loaded
  useEffect(() => {
    // For Exit Port
    if (
      portType === "Exit Port" &&
      shotels &&
      Array.isArray(shotels) &&
      shotels.length > 0 &&
      !initialHotelsLoaded
    ) {
      setCachedHotels(shotels);
      setInitialHotelsLoaded(true);
    }
    // For Entry Port
    else if (
      portType === "Entry Port" &&
      zones &&
      zones.hotels &&
      Array.isArray(zones.hotels) &&
      zones.hotels.length > 0 &&
      !initialHotelsLoaded
    ) {
      setCachedHotels(zones);
      setInitialHotelsLoaded(true);
    }
  }, [shotels, zones, SelectedPort, initialHotelsLoaded]);

  // Use the cached hotels if the current hotel array is empty
  const zonesData =
    portType === "Exit Port"
      ? shotels &&
        (shotels.hotels || shotels.attractions || shotels.restaurants)
        ? shotels
        : cachedHotels
      : zones && (zones.hotels || zones.attractions || zones.restaurants)
      ? zones
      : cachedHotels;

  const [showDropdown, setShowDropdown] = useState(false);
  const [searchText, setSearchText] = useState("");

  // Filter function to search across all options
  const filterItems = (items, term) => {
    if (!term) return items;
    if (!items) return [];
    return items.filter(
      (item) =>
        item &&
        item.name &&
        item.name.toLowerCase().includes((term || "").toLowerCase())
    );
  };

  const handleSelect = (item, type) => {
    if (disabled) return; // Don't process if disabled

    setexitPickUpLocation(item.name);
    setSearchText(item.name);
    setShowDropdown(false);
    setPickupFromAutocomplete(true);
    setType(type);
    setPickdropType(type);

    // Set ID based on the selected item type and port type
    let itemId;
    if (type === "hotel") {
      itemId = item.hotel_unique_id || item.id;
    } else if (type === "attraction") {
      itemId = item.attraction_id || item.id;
    } else if (type === "restaurant") {
      itemId = item.restaurant_id || item.id;
    }

    setId(itemId);

    if (portType === "Exit Port") {
      setpickId(itemId);
    } else {
      setdropId(itemId);
    }
  };

  // Reset initialHotelsLoaded when port type changes
  useEffect(() => {
    setInitialHotelsLoaded(false);
  }, [portType]);

  // Add useEffect to handle editing after selection
  useEffect(() => {
    // Only trigger when user is modifying a selection
    if (exitpickUpLocation && searchText !== exitpickUpLocation) {
      // Reset selection state when user starts editing
      setPickupFromAutocomplete(false);
      // Keep dropdown open to show options
      setShowDropdown(true);
    }
  }, [searchText, exitpickUpLocation]);

  // Add this function to close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (
        event.target.id !== "pick-up-input" &&
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
    <div
      className={`searchMenu-loc pr-10 pl-10 lg:py-20 lg:px-0 relative ${
        disabled ? "disabled-input" : ""
      }`}
    >
      <div className="d-flex justify-between">
        <div className="flex-1 mr-10">
          <div className="d-flex relative">
            <i
              className={`icon-location-2 text-20 ${
                disabled ? "text-gray-400" : "text-light-1"
              } `}
            ></i>
            <div
              className="ml-5 flex-grow-1 w-full"
              style={{ minWidth: "160px" }}
            >
              <input
                id="pick-up-input"
                autoComplete="off"
                type="search"
                placeholder={
                  portType === "Entry Port"
                    ? "Where is your drop off?"
                    : "Where is your pick up?"
                }
                className={`js-search js-dd-focus w-full text-15 ${
                  disabled
                    ? "bg-gray-100 border-gray-200 text-gray-400"
                    : "border-gray-300"
                } rounded `}
                style={{ minWidth: "160px" }}
                value={searchText}
                onChange={(e) => {
                  if (disabled) return;

                  const newValue = e.target.value;
                  setSearchText(newValue);
                  setShowDropdown(true);

                  // If we had a selection but now we're editing it
                  if (exitpickUpLocation && newValue !== exitpickUpLocation) {
                    setPickupFromAutocomplete(false);
                    // Don't clear the exitpickUpLocation yet - only when selection is confirmed
                  }
                }}
                onFocus={() => {
                  if (disabled) return;

                  setShowDropdown(true);
                  // If empty on focus, show all options
                  if (!searchText && zonesData) {
                    console.log("Showing all options on focus");
                  }
                }}
                disabled={
                  disabled ||
                  !portType ||
                  (portType !== "Entry Port" && portType !== "Exit Port")
                }
              />
              {showDropdown && !disabled && (
                <div className="location-dropdown">
                  {portType === "Exit Port" ? (
                    // For Exit Port, show categorized dropdown just like Entry Port
                    <>
                      {zonesData &&
                        zonesData.hotels &&
                        zonesData.hotels.length > 0 && (
                          <div className="location-category">
                            <div className="category-heading">Hotel</div>
                            {filterItems(zonesData.hotels, searchText).map(
                              (hotel) => (
                                <div
                                  key={`hotel-${
                                    hotel.hotel_unique_id || hotel.id
                                  }`}
                                  className="location-item"
                                  onClick={() => handleSelect(hotel, "hotel")}
                                >
                                  {hotel.name}
                                </div>
                              )
                            )}
                            {filterItems(zonesData.hotels, searchText)
                              .length === 0 &&
                              searchText && (
                                <div className="no-results">
                                  No matching hotels
                                </div>
                              )}
                          </div>
                        )}

                      {zonesData &&
                        zonesData.attractions &&
                        zonesData.attractions.length > 0 && (
                          <div className="location-category">
                            <div className="category-heading">Attraction</div>
                            {filterItems(zonesData.attractions, searchText).map(
                              (attraction) => (
                                <div
                                  key={`attraction-${
                                    attraction.attraction_id || attraction.id
                                  }`}
                                  className="location-item"
                                  onClick={() =>
                                    handleSelect(attraction, "attraction")
                                  }
                                >
                                  {attraction.name}
                                </div>
                              )
                            )}
                            {filterItems(zonesData.attractions, searchText)
                              .length === 0 &&
                              searchText && (
                                <div className="no-results">
                                  No matching attractions
                                </div>
                              )}
                          </div>
                        )}

                      {zonesData &&
                        zonesData.restaurants &&
                        zonesData.restaurants.length > 0 && (
                          <div className="location-category">
                            <div className="category-heading">Restaurant</div>
                            {filterItems(zonesData.restaurants, searchText).map(
                              (restaurant) => (
                                <div
                                  key={`restaurant-${
                                    restaurant.restaurant_id || restaurant.id
                                  }`}
                                  className="location-item"
                                  onClick={() =>
                                    handleSelect(restaurant, "restaurant")
                                  }
                                >
                                  {restaurant.name}
                                </div>
                              )
                            )}
                            {filterItems(zonesData.restaurants, searchText)
                              .length === 0 &&
                              searchText && (
                                <div className="no-results">
                                  No matching restaurants
                                </div>
                              )}
                          </div>
                        )}

                      {(!zonesData ||
                        ((!zonesData.hotels || zonesData.hotels.length === 0) &&
                          (!zonesData.attractions ||
                            zonesData.attractions.length === 0) &&
                          (!zonesData.restaurants ||
                            zonesData.restaurants.length === 0))) && (
                        <div className="no-results">
                          {initialHotelsLoaded && cachedHotels.length > 0
                            ? "Type to search..."
                            : "No options found"}
                        </div>
                      )}
                    </>
                  ) : (
                    // For Entry Port, keep the existing categorized dropdown
                    <>
                      {zonesData &&
                        zonesData.hotels &&
                        zonesData.hotels.length > 0 && (
                          <div className="location-category">
                            <div className="category-heading">Hotel</div>
                            {filterItems(zonesData.hotels, searchText).map(
                              (hotel) => (
                                <div
                                  key={`hotel-${
                                    hotel.hotel_unique_id || hotel.id
                                  }`}
                                  className="location-item"
                                  onClick={() => handleSelect(hotel, "hotel")}
                                >
                                  {hotel.name}
                                </div>
                              )
                            )}
                            {filterItems(zonesData.hotels, searchText)
                              .length === 0 &&
                              searchText && (
                                <div className="no-results">
                                  No matching hotels
                                </div>
                              )}
                          </div>
                        )}

                      {zonesData &&
                        zonesData.attractions &&
                        zonesData.attractions.length > 0 && (
                          <div className="location-category">
                            <div className="category-heading">Attraction</div>
                            {filterItems(zonesData.attractions, searchText).map(
                              (attraction) => (
                                <div
                                  key={`attraction-${
                                    attraction.attraction_id || attraction.id
                                  }`}
                                  className="location-item"
                                  onClick={() =>
                                    handleSelect(attraction, "attraction")
                                  }
                                >
                                  {attraction.name}
                                </div>
                              )
                            )}
                            {filterItems(zonesData.attractions, searchText)
                              .length === 0 &&
                              searchText && (
                                <div className="no-results">
                                  No matching attractions
                                </div>
                              )}
                          </div>
                        )}

                      {zonesData &&
                        zonesData.restaurants &&
                        zonesData.restaurants.length > 0 && (
                          <div className="location-category">
                            <div className="category-heading">Restaurant</div>
                            {filterItems(zonesData.restaurants, searchText).map(
                              (restaurant) => (
                                <div
                                  key={`restaurant-${
                                    restaurant.restaurant_id || restaurant.id
                                  }`}
                                  className="location-item"
                                  onClick={() =>
                                    handleSelect(restaurant, "restaurant")
                                  }
                                >
                                  {restaurant.name}
                                </div>
                              )
                            )}
                            {filterItems(zonesData.restaurants, searchText)
                              .length === 0 &&
                              searchText && (
                                <div className="no-results">
                                  No matching restaurants
                                </div>
                              )}
                          </div>
                        )}

                      {(!zonesData ||
                        ((!zonesData.hotels || zonesData.hotels.length === 0) &&
                          (!zonesData.attractions ||
                            zonesData.attractions.length === 0) &&
                          (!zonesData.restaurants ||
                            zonesData.restaurants.length === 0))) && (
                        <div className="no-results">
                          {initialHotelsLoaded && cachedHotels.length > 0
                            ? "Type to search..."
                            : "No options found"}
                        </div>
                      )}
                    </>
                  )}
                </div>
              )}
              {validationTriggered && !exitpickUpLocation && !disabled && (
                <div className="text-red-500 mt-1 text-sm">
                  *Select location from dropdown
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* CSS Fixes for Google Autocomplete and Custom Dropdown */}
      <style jsx>{`
        .pac-container {
          z-index: 10000 !important;
          background-color: #fff !important;
          border: 1px solid #ccc !important;
          width: 100% !important;
          min-width: 200px !important;
        }

        .pac-item {
          font-size: 15px !important;
          font-weight: 500 !important;
          color: #000 !important;
          white-space: normal !important;
          overflow: visible !important;
        }

        .pac-item-query {
          font-weight: bold !important;
          color: #000 !important;
        }

        .disabled-input {
          opacity: 0.8;
          cursor: not-allowed;
        }

        /* Custom dropdown styles */
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

        .no-results {
          padding: 8px 12px;
          color: #888;
          font-style: italic;
        }
      `}</style>
    </div>
  );
};

export default PortLocation2;
