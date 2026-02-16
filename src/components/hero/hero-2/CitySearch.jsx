import { useState, useEffect, useRef, useMemo } from "react";
import { useSelector, useDispatch } from 'react-redux';
import { fetchCityCountry, addSelectedCity, removeSelectedCity } from '@/slice/common/citiesSlice';

const CitySearch = ({ onCitySelect }) => {
  const [searchValue, setSearchValue] = useState("");
  const [selectedItem, setSelectedItem] = useState(null); // Single city selection
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [suggestions, setSuggestions] = useState([]);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const listRef = useRef(null);
  const inputRef = useRef(null);
  const dispatch = useDispatch();
  
  // Get selected DMC data from Redux store
  const selectedCountries = useSelector((state) => state.dmc.selectedCountries);
  const selectedDmcData = selectedCountries && selectedCountries.length > 0 ? selectedCountries[0] : null;
  
  // Get city-country search results and selected cities from Redux
  const { cityCountryResults, loading: cityCountryLoading } = useSelector((state) => state.cities || { cityCountryResults: [], loading: false });
  
  // Get global countries to map country names to codes if needed
  const global_countries = useSelector((state) => state.auth.global_countries);
  
  // Create mapping for country names to codes (similar to hero-1)
  const countryNameToCode = useMemo(() => {
    const mapping = {};
    if (global_countries && Array.isArray(global_countries)) {
      global_countries.forEach(country => {
        if (country && country.name && (country.code || country.country_code)) {
          mapping[country.name.toLowerCase()] = country.code || country.country_code;
        }
      });
    }
    return mapping;
  }, [global_countries]);
  
  // Call fetchCityCountry when user types 3 or more characters
  useEffect(() => {
    if (searchValue && searchValue.length >= 3) {
      const firstThreeChars = searchValue.substring(0, 3);
      dispatch(fetchCityCountry(firstThreeChars));
    }
  }, [searchValue, dispatch]);
  
  // Filter and suggest results based on search input
  useEffect(() => {
    if (searchValue) {
      // If we have API results and search value is 3+ characters, use API results
      if (searchValue.length >= 3 && cityCountryResults && cityCountryResults.length > 0) {
        setSuggestions(cityCountryResults);
        setIsDropdownOpen(true);
      } else if (searchValue.length < 3) {
        // Clear suggestions if less than 3 characters
        setSuggestions([]);
        setIsDropdownOpen(false);
      }
    } else {
      // Clear suggestions when search value is empty
      setSuggestions([]);
      setIsDropdownOpen(false);
    }
  }, [searchValue, cityCountryResults]);

  const handleOptionClick = (item, event) => {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    // Single city selection
    setSelectedItem(item);
    setSearchValue(item.city);
    setHighlightedIndex(-1);
    setIsDropdownOpen(false);
    
    // Extract country from city data and notify parent
    // The item from fetchCityCountry has structure: { city, country, city_id, etc. }
    // Similar to hero-1 pattern where city selection provides country info
    if (onCitySelect) {
      const countryName = item.country;
      const countryCode = item.country_code || item.code || countryNameToCode[countryName?.toLowerCase()] || null;
      
      onCitySelect({
        country: countryName, // Extract country from city item
        countryCode: countryCode, // Extract country code from item or mapping
        city: item.city,
        cityCode: item.city_id || item.code || item.city_code || null // Use city_id, code, or city_code
      });
      dispatch(addSelectedCity(item));
    }
    
    // Remove focus from input after selection
    if (inputRef.current) {
      inputRef.current.blur();
    }
  };

  // Add a new function to handle input change
  const handleInputChange = (e) => {
    const newValue = e.target.value;
    // If a city is already selected and user is typing, clear selection
    if (selectedItem) {
      setSelectedItem(null);
      // Clear the city selection in parent
      if (onCitySelect) {
        onCitySelect(null);
        if (selectedItem.city_id) {
          dispatch(removeSelectedCity(selectedItem.city_id));
        }
      }
    }
    setSearchValue(newValue);
    setHighlightedIndex(-1);
  };
  
  // Handle input focus
  const handleInputFocus = () => {
    // Always open dropdown on focus if there are suggestions
    if (suggestions.length > 0) {
      setIsDropdownOpen(true);
    }
  };
  
  // Clear selected location (single select)
  const handleClearSelection = (e) => {
    if (e) {
      e.stopPropagation();
    }
    setSelectedItem(null);
    setSearchValue("");
    setIsDropdownOpen(true);
    
    // Inform parent component about cleared selection
    if (onCitySelect) {
      onCitySelect(null);
      if (selectedItem && selectedItem.city_id) {
        dispatch(removeSelectedCity(selectedItem.city_id));
      }
    }
    
    // Focus the input after clearing
    if (inputRef.current) {
      inputRef.current.focus();
    }
  };

  // Handle keyboard navigation
  const handleKeyDown = (e) => {
    // If a city is already selected, only allow Escape or Backspace to clear
    if (selectedItem) {
      if (e.key === "Escape" || e.key === "Backspace") {
        handleClearSelection(e);
      }
      return;
    }
    
    if (!suggestions.length) return;

    if (e.key === "ArrowDown") {
      e.preventDefault();
      setHighlightedIndex((prev) =>
        prev < suggestions.length - 1 ? prev + 1 : 0
      );
      setIsDropdownOpen(true);
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      setHighlightedIndex((prev) =>
        prev > 0 ? prev - 1 : suggestions.length - 1
      );
      setIsDropdownOpen(true);
    } else if (e.key === "Enter" && highlightedIndex !== -1) {
      e.preventDefault(); // Prevent form submission
      handleOptionClick(suggestions[highlightedIndex]);
    } else if (e.key === "Escape") {
      setIsDropdownOpen(false);
      setHighlightedIndex(-1);
    }
  };

  // Ensure highlighted item is visible
  useEffect(() => {
    if (listRef.current && highlightedIndex !== -1) {
      const activeItem = listRef.current.children[highlightedIndex];
      if (activeItem) {
        activeItem.scrollIntoView({ block: "nearest" });
      }
    }
  }, [highlightedIndex]);

  // Close dropdown when clicking outside
  useEffect(() => {
    function handleClickOutside(event) {
      if (listRef.current && !listRef.current.contains(event.target) && 
          !inputRef.current.contains(event.target) &&
          !event.target.classList.contains('js-search')) {
        setIsDropdownOpen(false);
      }
    }

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  // Check if a city is selected (single select)
  const isCitySelected = (cityId) => {
    return selectedItem && selectedItem.city_id === cityId;
  };

  // Get placeholder text - only show when field is empty
  const getPlaceholder = () => {
    return "Search cities (type 3+ characters)";
  };

  // Get input value - show "city, country" when selected, otherwise show search value
  const getInputValue = () => {
    if (selectedItem && selectedItem.city && selectedItem.country) {
      return `${selectedItem.city}, ${selectedItem.country}`;
    }
    return searchValue;
  };

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 position-relative">
      <input
        ref={inputRef}
        autoComplete="off"
        type="search"
        placeholder="search city"
        className="js-search js-dd-focus pr-30 text-xs border-0 h-50"
        style={{ fontSize: "11.5px" }}
        value={getInputValue()}
        onChange={handleInputChange}
        onKeyDown={handleKeyDown}
        onFocus={handleInputFocus}
        readOnly={selectedItem !== null}
      />
      
      {/* Clear button when city is selected */}
      {selectedItem && (
        <button
          type="button"
          className="position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent cursor-pointer"
          onClick={handleClearSelection}
          style={{ padding: "5px", zIndex: 10 }}
        >
          <i className="icon-close text-12" />
        </button>
      )}

      {isDropdownOpen && (
        <div className="shadow-2 dropdown-menu min-width-400 show">
          <div className="bg-white px-20 py-20 sm:px-0 sm:py-15 rounded-4">
            {suggestions.length > 0 ? (
              <div 
                className="max-height-300 overflow-y-auto"
                style={{ maxHeight: '300px' }}
              >
                <ul className="y-gap-5 js-results" ref={listRef}>
                  {suggestions.map((item, index) => {
                    const isSelected = isCitySelected(item.city_id);
                    return (
                      <li
                        className={`-link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option mb-1 ${
                          isSelected
                            ? "active bg-light-2"
                            : highlightedIndex === index
                            ? "highlighted bg-light-1"
                            : ""
                        }`}
                        key={item.city_id || `city-${index}`}
                        role="button"
                        onClick={(e) => handleOptionClick(item, e)}
                        style={{ cursor: "pointer" }}
                      >
                        <div className="d-flex align-items-center">
                          <div className="icon-location-2 text-light-1 text-20 pt-4" />
                          <div className="ml-10 flex-grow-1">
                            {/* City name - highlighted/bold */}
                            <div className="text-15 lh-16 fw-500 js-search-option-target" style={{ color: "#1a1a1a" }}>
                              {item.city}
                            </div>
                            {/* Country name - greyed out */}
                            <div className="text-13 lh-14 text-light-1" style={{ color: "#999", marginTop: "2px" }}>
                              {item.country}
                            </div>
                          </div>
                        </div>
                      </li>
                    );
                  })}
                </ul>
              </div>
            ) : (
              <div className="text-center py-20">
                <div className="text-14 text-light-1 lh-16">
                  {searchValue && searchValue.length >= 3
                    ? "No cities found"
                    : searchValue
                    ? "Type at least 3 characters to search"
                    : "No results found"}
                </div>
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default CitySearch;
