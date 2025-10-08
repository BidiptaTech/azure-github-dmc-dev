import { useState, useEffect, useRef } from "react";
import { useSelector } from "react-redux";

const SearchBar = ({ onLocationSelect, hasError, setError }) => {
  const [selectedItem, setSelectedItem] = useState(null);
  const [searchValue, setSearchValue] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [hasUserInteracted, setHasUserInteracted] = useState(false);
  const [citySelectedManually, setCitySelectedManually] = useState(false);

  const dropdownRef = useRef(null);
  const initialSelectionRef = useRef(false);

  const selectedCityFromSlice = useSelector((state) => state.common.selectedCity);
  const cityData = useSelector((state) => state.city.city);

  const transformedCityData = cityData.map((city, index) => ({
    id: index + 1,
    name: city.split(",")[0],
    address: city,
  }));

  const filteredCities = transformedCityData.filter((item) =>
    item.name.toLowerCase().includes((searchValue || "").toString().toLowerCase())
  );

  const handleOptionClick = (item) => {
    // Only update state if the selection is actually changing
    if (!selectedItem || selectedItem.id !== item.id) {
      setSelectedItem(item);
      setSearchValue(item.name);
      onLocationSelect(item);
      if (setError) setError(false);
    }
    
    setIsDropdownOpen(false);
    setHighlightedIndex(-1);
    setCitySelectedManually(true); // Mark user has selected manually
  };

  const handleInputChange = (e) => {
    const value = e.target.value;
    setSearchValue(value);
    setHasUserInteracted(true); // Mark user has typed
    
    // Only reset the selection if we actually have a selection
    if (selectedItem) {
      setSelectedItem(null);
      // Only notify parent when there's an actual change
      onLocationSelect(null);
    }
    
    setIsDropdownOpen(true);
    setCitySelectedManually(false); // Reset manual selection if typing again
    setHighlightedIndex(-1);

    // Only check for city matches if we have some input
    if (value.trim() !== "") {
      const matchesAnyCity = transformedCityData.some(city =>
        city.name.toLowerCase().includes(value.toLowerCase())
      );
      
      // Only notify parent if we need to clear a selection
      if (!matchesAnyCity && selectedItem) {
        onLocationSelect(null);
      }
    }

    if (setError) setError(false);
  };

  useEffect(() => {
    if (searchValue && isDropdownOpen && filteredCities.length === 0) {
      if (selectedItem) {
        setSelectedItem(null);
        onLocationSelect(null);
      }
    }
  }, [searchValue, filteredCities.length, isDropdownOpen, selectedItem]);

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

  useEffect(() => {
    if (!hasUserInteracted && !citySelectedManually && selectedCityFromSlice && !initialSelectionRef.current) {
      const cityName = typeof selectedCityFromSlice === 'object' && selectedCityFromSlice.name
        ? selectedCityFromSlice.name
        : selectedCityFromSlice;

      const matchingCity = transformedCityData.find(
        city => city.name.toLowerCase() === (cityName || '').toString().toLowerCase()
      );

      if (matchingCity) {
        initialSelectionRef.current = true; // Mark that we've done the initial selection
        setSelectedItem(matchingCity);
        setSearchValue(matchingCity.name);
        setIsDropdownOpen(false);
        if (selectedItem?.id !== matchingCity.id) {
          onLocationSelect(matchingCity);
          if (setError) setError(false);
        }
      }
    }
  }, [selectedCityFromSlice, transformedCityData, hasUserInteracted, citySelectedManually, selectedItem]);

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
                  Please select a City
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {isDropdownOpen && searchValue && (
        <div className="shadow-2 dropdown-menu min-width-400 show" style={{ maxHeight: "300px", overflowY: "auto" }}>
          <div className="bg-white px-20 py-20 sm:px-0 sm:py-15 rounded-4">
            <ul className="y-gap-5 js-results">
              {filteredCities.length > 0 ? (
                filteredCities.map((item, index) => (
                  <li
                    className={`-link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option mb-1 ${
                      index === highlightedIndex ? "bg-light active" : ""
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
                        <div className="text-14 lh-12 text-light-1 mt-5">{item.address}</div>
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
