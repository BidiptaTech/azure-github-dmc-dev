import { useEffect, useRef, useState } from "react";
import { DatePicker } from "antd";
import dayjs from "dayjs";
import { useDispatch, useSelector } from "react-redux";
// import {
//   fetchHotels,
//   updateSearchState,
//   resetHotels,
// } from "@/slice/hotel/hotelSlice";
import { useLocation, useNavigate } from "react-router-dom";
// import { useSelector } from "react-redux";

const SearchBar = ({ onLocationSelect, hasError, setError, controlledCity = null }) => {
  const selectedCityFromSlice = useSelector((state)=> state.common.selectedCity);
  const initialSelectionRef = useRef(false); // Track if initial selection has been applied
  const lastControlledNameRef = useRef(null);

  const [selectedItem, setSelectedItem] = useState(null);
  const [searchValue, setSearchValue] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [highlightedIndex, setHighlightedIndex] = useState(-1); // Track highlighted item
  const [userInteracted, setUserInteracted] = useState(false);

  const dropdownRef = useRef(null);

  // Access current location from Redux store
  const { searchState } = useSelector((state) => state.hotels);
  
  // Access city data from Redux store
  const cityData = useSelector((state) => state.city.city);
  //console.log("City Data:", cityData);

  // Transform city data into expected format (API/session may return strings or objects)
  const transformedCityData = (Array.isArray(cityData) ? cityData : [])
    .map((city, index) => {
      const address =
        typeof city === "string"
          ? city
          : city?.address || city?.name || city?.city || "";
      if (!address) return null;
      return {
        id: index + 1,
        name: String(address).split(",")[0].trim(),
        address: String(address),
      };
    })
    .filter(Boolean);

  // Filter cities based on user input
  const filteredCities = transformedCityData.filter((item) =>
    item.name.toLowerCase().includes((searchValue || "").toString().toLowerCase())
  );

  const handleInputChange = (e) => {
    setUserInteracted(true);
    const value = e.target.value;
    setSearchValue(value);
    
    // Only reset selection if we have one
    if (selectedItem) {
      setSelectedItem(null);
      onLocationSelect(null);
    }
    
    setIsDropdownOpen(true);
    if (setError) setError(false);
  };
  
  const handleOptionClick = (item) => {
    setUserInteracted(true);
    
    // Only update if selection is changing
    if (!selectedItem || selectedItem.id !== item.id) {
      setSelectedItem(item);
      setSearchValue(item.name);
      onLocationSelect(item);
      if (setError) setError(false);
    }
    
    setIsDropdownOpen(false);
  };
  
  const handleKeyDown = (e) => {
    if (!isDropdownOpen || filteredCities.length === 0) return;

    if (e.key === "ArrowDown") {
      setHighlightedIndex((prevIndex) =>
        prevIndex < filteredCities.length - 1 ? prevIndex + 1 : 0
      );
    } else if (e.key === "ArrowUp") {
      setHighlightedIndex((prevIndex) =>
        prevIndex > 0 ? prevIndex - 1 : filteredCities.length - 1
      );
    } else if (e.key === "Enter" && highlightedIndex >= 0) {
      handleOptionClick(filteredCities[highlightedIndex]);
    }
  };
  
  // Sync when parent drives selection (city chips / auto-select from cityWiseDates)
  useEffect(() => {
    if (!controlledCity) return;

    const nextName =
      typeof controlledCity === "string"
        ? String(controlledCity).split(",")[0].trim()
        : String(
            controlledCity?.name ||
              controlledCity?.address ||
              controlledCity?.city ||
              ""
          )
            .split(",")[0]
            .trim();

    if (!nextName) return;
    if (lastControlledNameRef.current === nextName.toLowerCase()) return;

    lastControlledNameRef.current = nextName.toLowerCase();
    initialSelectionRef.current = true;

    const matchingCity = transformedCityData.find(
      (city) =>
        city.name.toLowerCase() === nextName.toLowerCase() ||
        city.address.toLowerCase().includes(nextName.toLowerCase())
    );

    const nextItem =
      matchingCity ||
      (typeof controlledCity === "object"
        ? {
            id: controlledCity.id || 0,
            name: nextName,
            address: controlledCity.address || nextName,
          }
        : { id: 0, name: nextName, address: nextName });

    setSelectedItem(nextItem);
    setSearchValue(nextItem.name);
    setIsDropdownOpen(false);
    setUserInteracted(false);
    if (setError) setError(false);
  }, [controlledCity, transformedCityData, setError]);

  useEffect(() => {
    // Skip Redux hydrate when parent already controls the city field
    if (controlledCity) return;

    if (selectedCityFromSlice && !userInteracted && !initialSelectionRef.current) {
      const cityName =
        typeof selectedCityFromSlice === "string"
          ? selectedCityFromSlice
          : selectedCityFromSlice?.name ||
            selectedCityFromSlice?.address ||
            selectedCityFromSlice?.city ||
            "";
      
      const matchingCity = transformedCityData.find(
        city => city.name.toLowerCase() === (cityName || '').toString().toLowerCase() ||
          city.address.toLowerCase() === (cityName || '').toString().toLowerCase()
      );
      
      if (matchingCity) {
        initialSelectionRef.current = true; // Mark initial selection as done
        setSelectedItem(matchingCity);
        setSearchValue(matchingCity.name);
        setIsDropdownOpen(false);
        if (setError) setError(false);
        
        // Only call onLocationSelect if we have a selection different from current
        if (!selectedItem || selectedItem.id !== matchingCity.id) {
          onLocationSelect(matchingCity);
        }
      } else if (cityName) {
        // After refresh, city list may load later — still show the saved city name
        const fallback = {
          id: 0,
          name: String(cityName).split(",")[0].trim(),
          address: String(cityName),
        };
        initialSelectionRef.current = true;
        setSelectedItem(fallback);
        setSearchValue(fallback.name);
        setIsDropdownOpen(false);
        if (setError) setError(false);
        onLocationSelect(fallback);
      }
    }
  // Removed onLocationSelect from dependencies to prevent loops
  }, [selectedCityFromSlice, userInteracted, transformedCityData, selectedItem, controlledCity]);
  
  // Close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsDropdownOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);
 

  return (
    <div className="searchMenu-loc pl-20 lg:py-20 lg:px-0 js-form-dd js-liverSearch" ref={dropdownRef}>
      <div>
        <div className="d-flex">
          <i className="icon-location-2 text-20 text-light-1 mt-5"></i>
          <div className="ml-10 flex-grow-1">
            <h4 className="text-15 fw-500 ls-2 lh-16">City</h4>
            <div className="text-15 text-light-1 ls-2 lh-16">
              <input
                autoComplete="off"
                type="search"
                placeholder="Where are you going?"
                className={`js-search js-dd-focus ${hasError ? 'border-red-500' : ''}`}
                value={searchValue}
                onChange={handleInputChange}
                onFocus={() => setIsDropdownOpen(true)}
                onKeyDown={handleKeyDown}
              />
              {hasError && (
                <div style={{ color: '#dc2626', fontSize: '12px', marginTop: '4px' }}>
                  Please select a city
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {isDropdownOpen && searchValue && (
        <div
          className="shadow-2 dropdown-menu min-width-400 show"
          style={{ maxHeight: "300px", overflowY: "auto" }}
        >
          <div className="bg-white px-20 py-20 sm:px-0 sm:py-15 rounded-4">
            <ul className="y-gap-5 js-results">
              {filteredCities.length > 0 ? (
                filteredCities.map((item, index) => (
                  <li
                    className={`-link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option mb-1 ${
                      highlightedIndex === index ? "active" : ""
                    }`}
                    key={item.id}
                    role="button"
                    onClick={() => handleOptionClick(item)}
                  >
                    <div className="d-flex">
                      <div className="icon-location-2 text-light-1 text-20 pt-4" />
                      <div className="ml-10">
                        <div className="text-15 lh-12 fw-500 js-search-option-target">
                          {item.name}
                        </div>
                        <div className="text-14 lh-12 text-light-1 mt-5">
                          {item.address}
                        </div>
                      </div>
                    </div>
                  </li>
                ))
              ) : (
                <li className="text-center">No matching locations</li>
              )}
            </ul>
          </div>
        </div>
      )}
    </div>
  );
};

export default SearchBar;
