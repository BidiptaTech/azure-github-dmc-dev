import { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from 'react-redux';
import { fetchCityCountry, addSelectedCity, removeSelectedCity } from '@/slice/common/citiesSlice';

const SearchBar = ({ onLocationSelect }) => {
  const [searchValue, setSearchValue] = useState("");
  const [selectedItem, setSelectedItem] = useState(null); // Single city selection
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [suggestions, setSuggestions] = useState([]);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  // Multi-select related states - commented out for single select
  // const [showMoreCitiesTooltip, setShowMoreCitiesTooltip] = useState(false);
  const listRef = useRef(null);
  const inputRef = useRef(null);
  // const tooltipRef = useRef(null);
  const dispatch = useDispatch();
  // Get selected DMC data from Redux store
  const selectedCountries = useSelector((state) => state.dmc.selectedCountries);
  const selectedDmcData = selectedCountries && selectedCountries.length > 0 ? selectedCountries[0] : null;
  
  // Get city-country search results and selected cities from Redux
  const { cityCountryResults, loading: cityCountryLoading, selectedCities } = useSelector((state) => state.cities || { cityCountryResults: [], loading: false, selectedCities: [] });
  
  // Multi-select functionality - commented out for single select
  // Using selectedCities from Redux for multi-select (commented out)
  // For single select, using local state selectedItem instead
  
  const defaultCountries = [
    { name: "India", code: "in" },
    { name: "Singapore", code: "SG" },
    // You can add more locations here
  ];

  // Get country from selected DMC data - memoized to prevent infinite loops
  // const global_countries = useSelector((state) => state.auth.global_countries);
  // const locationSearchContent = useMemo(() => {
  //   const content = global_countries && Array.isArray(global_countries)
  //     ? global_countries.map((country, index) => ({
  //         name: country.name,
  //         country_code: country.country_code,
  //         code: country.code,    
  //         key: `country-${index}`
  //       }))
  //     : defaultCountries;
  //   return content;
  // }, [global_countries]);
  
  // Call fetchCityCountry when user types 3 or more characters
  useEffect(() => {
    if (searchValue && searchValue.length >= 3) {
      const firstThreeChars = searchValue.substring(0, 3);
      dispatch(fetchCityCountry(firstThreeChars));
    }
  }, [searchValue, dispatch]);
  
  // Update location content when selected DMC changes
  // useEffect(() => {
  //   // Auto-select the DMC's country if available
  //   if (selectedDmcData && selectedDmcData.name) {
  //     const dmcCountry = {
  //       name: selectedDmcData.name, // Full country name
  //       code: selectedDmcData.code, // Use full name as code for database
  //       key: 'selected-dmc-country'
  //     };
  //     setSearchValue(dmcCountry.name);
  //     setSelectedItem(dmcCountry);
  //     setIsDropdownOpen(false);
      
  //     // Call the onLocationSelect callback
  //     if (onLocationSelect) {
  //       onLocationSelect(dmcCountry);
  //     }
  //   }
  // }, [selectedDmcData, onLocationSelect]);

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
    
    // Pass the city's country name to onLocationSelect
    if (onLocationSelect) {
      onLocationSelect(item.country);
      dispatch(addSelectedCity(item));
    }
    
    // Remove focus from input after selection
    if (inputRef.current) {
      inputRef.current.blur();
    }

    // Multi-select functionality - commented out
    // // Check if city is already selected
    // const isSelected = selectedCities.some(
    //   (selected) => selected.city_id === item.city_id
    // );
    // if (isSelected) {
    //   // Remove from selection
    //   dispatch(removeSelectedCity(item.city_id));
    // } else {
    //   // Add to selection
    //   dispatch(addSelectedCity(item));
    //   onLocationSelect(item.country);
    // }
    // setHighlightedIndex(-1);
    // // Keep dropdown open for multi-select
    // // Don't clear search value to allow selecting multiple items
  };

  // Add a new function to handle input change
  const handleInputChange = (e) => {
    // If a city is already selected, prevent changing the input
    if (selectedItem) {
      return;
    }
    setSearchValue(e.target.value);
    setSelectedItem(null);
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
    if (onLocationSelect) {
      onLocationSelect(null);
      dispatch(removeSelectedCity(selectedItem.city_id));
    }
    
    // Focus the input after clearing
    if (inputRef.current) {
      inputRef.current.focus();
    }
  };

  // Multi-select: Remove a selected city - commented out
  // const handleRemoveCity = (cityId, e) => {
  //   if (e) {
  //     e.stopPropagation();
  //   }
  //   dispatch(removeSelectedCity(cityId));
  // };

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
          !event.target.classList.contains('js-search')) {
        setIsDropdownOpen(false);
      }
      // Multi-select: Close tooltip when clicking outside - commented out
      // if (tooltipRef.current && !tooltipRef.current.contains(event.target) &&
      //     !event.target.closest('.more-cities-trigger')) {
      //   setShowMoreCitiesTooltip(false);
      // }
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

  // Multi-select: Check if a city is selected - commented out
  // const isCitySelected = (cityId) => {
  //   return selectedCities.some((city) => city.city_id === cityId);
  // };

  return (
    <div className="searchMenu-loc px-30 lg:py-20 lg:px-0">
      <div>
        <h4 className="text-15 fw-500 ls-2 lh-16">City</h4>
        <div className="text-15 text-light-1 ls-2 lh-16 position-relative">
          <input
            ref={inputRef}
            autoComplete="off"
            type="search"
            placeholder="Search cities (type 3+ characters)"
            className="js-search js-dd-focus pr-30 text-xs"
            style={{ fontSize: "11.5px" }}
            value={searchValue}
            onChange={handleInputChange}
            onKeyDown={handleKeyDown}
            onFocus={handleInputFocus}
          />
        </div>
        
        {/* Display selected city (single select) */}
        {selectedItem && (
          <div className="d-flex flex-wrap gap-1 mt-5">
            <div
              className="d-inline-flex align-items-center bg-light-2 rounded-4 px-10 py-12"
              style={{ fontSize: "10px" }}
            >
              <span className="fw-500">{selectedItem.city}</span>
              <button
                type="button"
                className="border-0 bg-transparent cursor-pointer ms-5"
                onClick={handleClearSelection}
                style={{ padding: "0 1px" }}
              >
                <i className="icon-close text-10" />
              </button>
            </div>
          </div>
        )}

        {/* Multi-select: Display selected cities as chips - commented out */}
        {/* {selectedCities.length > 0 && (
          <div className="d-flex flex-wrap gap-1 mt-5 position-relative">
            {selectedCities.slice(0, 2).map((city) => (
              <div
                key={city.city_id}
                className="d-inline-flex align-items-center bg-light-2 rounded-4 px-10 py-12"
                style={{ fontSize: "10px" }}
              >
                <span className="fw-500">{city.city}</span>
                <button
                  type="button"
                  className="border-0 bg-transparent cursor-pointer ms-5"
                  onClick={(e) => handleRemoveCity(city.city_id, e)}
                  style={{ padding: "0 1px" }}
                >
                  <i className="icon-close text-10" />
                </button>
              </div>
            ))}
            {selectedCities.length > 2 && (
              <>
                <div
                  className="d-inline-flex align-items-center bg-light-2 rounded-4 px-10 py-12 more-cities-trigger cursor-pointer"
                  style={{ fontSize: "10px" }}
                  onMouseEnter={() => setShowMoreCitiesTooltip(true)}
                  onMouseLeave={() => setShowMoreCitiesTooltip(false)}
                  onClick={() => setShowMoreCitiesTooltip(!showMoreCitiesTooltip)}
                >
                  <span className="fw-500">+{selectedCities.length - 2} more</span>
                </div>
                {showMoreCitiesTooltip && (
                  <div
                    ref={tooltipRef}
                    className="position-absolute bg-white shadow-2 rounded-4 p-15"
                    style={{
                      top: "100%",
                      left: "0",
                      marginTop: "5px",
                      minWidth: "200px",
                      maxWidth: "300px",
                      zIndex: 1000,
                      maxHeight: "250px",
                      overflowY: "auto"
                    }}
                    onMouseEnter={() => setShowMoreCitiesTooltip(true)}
                    onMouseLeave={() => setShowMoreCitiesTooltip(false)}
                  >
                    <div className="text-13 fw-500 mb-10 pb-10 border-bottom">Remaining Cities:</div>
                    <div className="d-flex flex-column gap-2">
                      {selectedCities.slice(2).map((city) => (
                        <div
                          key={city.city_id}
                          className="d-flex align-items-center justify-content-between bg-light-1 rounded-4 px-10 py-8"
                          style={{ fontSize: "11px" }}
                        >
                          <div className="flex-grow-1">
                            <div className="fw-500">{city.city}</div>
                            <div className="text-light-1" style={{ fontSize: "10px" }}>{city.country}</div>
                          </div>
                          <button
                            type="button"
                            className="border-0 bg-transparent cursor-pointer ms-5"
                            onClick={(e) => {
                              e.stopPropagation();
                              handleRemoveCity(city.city_id, e);
                            }}
                            style={{ padding: "0 5px" }}
                          >
                            <i className="icon-close text-12" />
                          </button>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </>
            )}
          </div>
        )} */}
      </div>

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
                          {/* Multi-select: Checkbox indicator - commented out */}
                          {/* <div className="me-10" style={{ minWidth: "20px" }}>
                            {isSelected ? (
                              <i className="icon-check text-16 text-primary" />
                            ) : (
                              <div style={{ width: "16px", height: "16px", border: "2px solid #ddd", borderRadius: "3px" }} />
                            )}
                          </div> */}
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

export default SearchBar;
