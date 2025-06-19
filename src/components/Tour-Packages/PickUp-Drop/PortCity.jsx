import { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
// import { fetchPortCity } from "@/slice/port/pickupDropSlice";
// import { selectSelectedCity } from "@/slice/common/commonSlice";

const PortCity = ({ onLocationSelect, hasError, setError, disabled = false }) => {
  const dispatch = useDispatch();
  const selectedCityFromSlice = useSelector(
    (state) => state.common.selectedCity
  );
  const [hasUserInteracted, setHasUserInteracted] = useState(false);
  const [citySelectedManually, setCitySelectedManually] = useState(false);
  const initialSelectionRef = useRef(false);

  const [selectedItem, setSelectedItem] = useState(null);
  const [searchValue, setSearchValue] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [highlightedIndex, setHighlightedIndex] = useState(-1); // Track highlighted item
  const dropdownRef = useRef(null);

  const cityData = useSelector((state) => state.city.city);
  console.log("cityData", cityData);

  // Check if cityData is available
  useEffect(() => {
    console.log("City data available:", Array.isArray(cityData));
    console.log("City data length:", cityData?.length || 0);
    if (cityData?.length > 0) {
      console.log("Sample city:", cityData[0]);
    }
  }, [cityData]);

  const transformedCityData =
    cityData && Array.isArray(cityData)
      ? cityData.map((city, index) => ({
          id: index + 1,
          name: city.split(",")[0],
          address: city,
          country: city.split(",").length > 1 ? city.split(",")[1].trim() : "",
        }))
      : [];

  // With key-based remounting, we don't need extra effects for port type changes
  const filteredCities = transformedCityData.filter(
    (item) =>
      item &&
      item.name &&
      item.name
        .toLowerCase()
        .includes((searchValue || "").toString().toLowerCase())
  );

  const handleOptionClick = (item) => {
    if (disabled) return; // Don't process clicks if disabled
    
    console.log("City option clicked:", item);
    // Only update state if the selection is actually changing
    if (!selectedItem || selectedItem.id !== item.id) {
      setSelectedItem(item);
      setSearchValue(item.name);
      onLocationSelect(item);
      if (setError) setError(false);

      // Let the parent component handle the API call by passing the selected city
    }

    setIsDropdownOpen(false);
    setHighlightedIndex(-1);
    setHasUserInteracted(true);
    setCitySelectedManually(true);
  };

  const handleInputChange = (e) => {
    if (disabled) return; // Don't process changes if disabled
    
    const value = e.target.value;
    setSearchValue(value);
    setHasUserInteracted(true);

    // Only reset the selection if the text completely changes
    if (selectedItem && value !== selectedItem.name) {
      // Don't immediately clear the selection, just mark it as not from autocomplete
      setIsDropdownOpen(true);
      setCitySelectedManually(false);
    }

    setHighlightedIndex(-1);

    // Only check for city matches if we have some input
    if (value.trim() !== "") {
      const matchesAnyCity = transformedCityData.some((city) =>
        city.name.toLowerCase().includes(value.toLowerCase())
      );

      // Only notify parent if we completely cleared the input
      if (!matchesAnyCity && value.trim() === "") {
        setSelectedItem(null);
        onLocationSelect(null);
      }
    }

    if (setError) setError(false);
  };

  // Effect to handle auto-fill from Redux store
  // useEffect(() => {
  //   if (!hasUserInteracted && !citySelectedManually && selectedCityFromSlice && !initialSelectionRef.current) {
  //     console.log("Auto-fill from Redux store:", selectedCityFromSlice);

  //     const cityName = typeof selectedCityFromSlice === 'object' && selectedCityFromSlice.name
  //       ? selectedCityFromSlice.name
  //       : selectedCityFromSlice;

  //     const matchingCity = transformedCityData.find(
  //       city => city.name.toLowerCase() === (cityName || '').toString().toLowerCase()
  //     );

  //     if (matchingCity) {
  //       console.log("Found matching city for auto-fill:", matchingCity);
  //       initialSelectionRef.current = true; // Mark that we've done the initial selection
  //       setSelectedItem(matchingCity);
  //       setSearchValue(matchingCity.name);
  //       setIsDropdownOpen(false);
  //       if (selectedItem?.id !== matchingCity.id) {
  //         onLocationSelect(matchingCity);
  //         if (setError) setError(false);

  //         // Let the parent component handle the API call by passing the selected city
  //       }
  //     }
  //   }
  // }, [selectedCityFromSlice, transformedCityData, hasUserInteracted, citySelectedManually, selectedItem]);

  // Effect to clear selection when no matching cities
  useEffect(() => {
    if (searchValue && isDropdownOpen && filteredCities.length === 0) {
      if (selectedItem) {
        // Only update if we're actually changing the selection
        setSelectedItem(null);
        onLocationSelect(null);
      }
    }
  }, [searchValue, filteredCities.length, isDropdownOpen, selectedItem]);

  const handleKeyDown = (e) => {
    if (disabled || !isDropdownOpen || filteredCities.length === 0) return;

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

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsDropdownOpen(false);
        setHighlightedIndex(-1);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  return (
    <div
      className={`searchMenu-loc pl-10 lg:py-20 lg:px-0 js-form-dd js-liverSearch ${
        hasError ? "has-error" : ""
      } ${disabled ? "disabled-input" : ""}`}
      ref={dropdownRef}
    >
      <div>
        <div className="d-flex relative">
          <i className={`icon-location-2 text-20 ${disabled ? "text-gray-400" : "text-light-1"} `}></i>
          <div className="ml-5 flex-grow-1 w-full" style={{ minWidth: "160px" }}>
            <input
              autoComplete="off"
              type="search"
              placeholder="Search for a city"
              className={`js-search js-dd-focus w-full text-15 ${disabled ? "bg-gray-100 border-gray-200 text-gray-400" : "border-gray-300"} rounded  ${
                hasError ? "border-red-500" : ""
              }`}
              style={{ minWidth: "160px" }}
              value={searchValue}
              onChange={handleInputChange}
              onFocus={() => !disabled && setIsDropdownOpen(true)}
              onKeyDown={handleKeyDown}
              disabled={disabled}
            />
          </div>
        </div>
      </div>

      {isDropdownOpen && searchValue && !disabled && (
        <div
          className="absolute bg-white border rounded shadow mt-2 z-50 w-full"
          style={{ maxHeight: "300px", overflowY: "auto", minWidth: "250px" }}
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

      <style jsx>{`
        .border-red-500 {
          border-color: #ef4444;
        }
        .disabled-input {
          opacity: 0.8;
          cursor: not-allowed;
        }
      `}</style>
    </div>
  );
};

export default PortCity;
