import { useState, useRef, useEffect, useMemo } from "react";
import { useSelector, useDispatch } from 'react-redux';
import { fetchCitiesByCountry, clearCities } from "../../../slice/common/citiesSlice";

const LocationSearch = ({ onLocationSelect, initialValue = null }) => {
  const dispatch = useDispatch();
  const [searchValue, setSearchValue] = useState("");
  const [selectedItem, setSelectedItem] = useState(null);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [suggestions, setSuggestions] = useState([]);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const inputRef = useRef(null);
  const listRef = useRef(null);
  
  // Get global countries from auth state (same as hero-2/LocationSearch)
  const global_countries = useSelector((state) => state.auth.global_countries);
  
  // Get selected DMC data from Redux store
  const selectedDmcData = useSelector((state) => state.dmc.selectedDmcData);
  
  // Default countries if global_countries isn't available
  const defaultCountries = [
    { name: "India", code: "in" },
    { name: "Singapore", code: "SG" },
    { name: "UAE", code: "AE" },
    { name: "Thailand", code: "TH" },
    { name: "Switzerland", code: "CH" },
    { name: "Maldives", code: "MV" },
    { name: "Japan", code: "JP" },
    { name: "France", code: "FR" },
    { name: "Italy", code: "IT" },
    { name: "Spain", code: "ES" },
  ];

  // Get countries from auth state (same pattern as hero-2/LocationSearch)
  const availableCountries = useMemo(() => {
    if (selectedDmcData && selectedDmcData.location && selectedDmcData.location !== 'Auto-selected') {
      return [{ 
        name: selectedDmcData.location,
        code: selectedDmcData.location,
        key: 'selected-dmc-country' 
      }];
    }
    return global_countries && Array.isArray(global_countries)
      ? global_countries.map((country, index) => ({
          name: country.name,
          country_code: country.country_code,
          code: country.code,    
          key: `country-${index}`
        }))
      : defaultCountries;
  }, [global_countries, selectedDmcData]);

  // Filter and suggest countries based on search input (same as hero-2)
  useEffect(() => {
    if (searchValue && !selectedItem) {
      const filtered = availableCountries.filter((country) =>
        country.name.toLowerCase().includes(searchValue.toLowerCase())
      );
      setSuggestions(filtered);
      setIsDropdownOpen(true);
    } else if (selectedItem) {
      // When item is selected, keep dropdown closed and show no suggestions
      setSuggestions([]);
      setIsDropdownOpen(false);
    } else {
      setSuggestions(availableCountries.slice(0, 5));
      setIsDropdownOpen(false);
    }
  }, [searchValue, selectedItem, availableCountries]);

  // Auto-select DMC country when selectedDmcData changes
  useEffect(() => {
    if (selectedDmcData && selectedDmcData.location && selectedDmcData.location !== 'Auto-selected') {
      const dmcCountry = {
        name: selectedDmcData.location,
        code: selectedDmcData.location,
        key: 'selected-dmc-country'
      };
      
      const currentCountry = selectedItem?.name;
      if (currentCountry !== dmcCountry.name) {
        setSearchValue(dmcCountry.name);
        setSelectedItem(dmcCountry);
        setIsDropdownOpen(false);
        
        if (onLocationSelect) {
          onLocationSelect(dmcCountry);
        }
      }
    } else if (!selectedDmcData && selectedItem && selectedItem.key === 'selected-dmc-country') {
      // Only clear if it was auto-selected from DMC data
      setSelectedItem(null);
      setSearchValue("");
      setIsDropdownOpen(true);
      dispatch(clearCities());
      if (onLocationSelect) {
        onLocationSelect(null);
      }
    }
  }, [selectedDmcData, selectedItem, onLocationSelect, dispatch]);

  // Set initial value if provided
  useEffect(() => {
    if (initialValue && !selectedItem) {
      const foundCountry = availableCountries.find(
        country => country.name === initialValue || country.code === initialValue
      );
      
      if (foundCountry) {
        setSelectedItem(foundCountry);
        setSearchValue(foundCountry.name);
        setIsDropdownOpen(false);
      } else if (typeof initialValue === 'string') {
        setSelectedItem({ name: initialValue });
        setSearchValue(initialValue);
        setIsDropdownOpen(false);
      }
    }
  }, [initialValue, availableCountries, selectedItem]);



  // Ensure highlighted item is visible in scroll
  useEffect(() => {
    if (listRef.current && highlightedIndex !== -1 && isDropdownOpen) {
      const activeItem = listRef.current.children[highlightedIndex];
      if (activeItem) {
        activeItem.scrollIntoView({ block: "nearest" });
      }
    }
  }, [highlightedIndex, isDropdownOpen]);

  // Close dropdown when clicking outside
  useEffect(() => {
    function handleClickOutside(event) {
      if (listRef.current && 
          !listRef.current.contains(event.target) && 
          !inputRef.current.contains(event.target)) {
        setIsDropdownOpen(false);
      }
    }

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  const handleCountrySelect = (country, event) => {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    console.log('Country selected:', country);

    // Update all state synchronously (React will batch these)
    setSelectedItem(country);
    setSearchValue(country.name);
    setIsDropdownOpen(false);
    setHighlightedIndex(-1);
    
    // Clear cities from Redux store when country changes
    dispatch(clearCities());
    
    // Call the callback to notify parent component
    if (onLocationSelect) {
      onLocationSelect(country);
    }
  };

  const handleInputChange = (e) => {
    // If a country is already selected, prevent typing (same as hero-2)
    if (selectedItem) return;
    
    setSearchValue(e.target.value);
    setHighlightedIndex(-1);
  };

  const handleInputFocus = () => {
    // Always allow focus so Escape/Backspace work even when selected
    if (!selectedItem) {
      setIsDropdownOpen(true);
    }
  };

  const handleClearSelection = (e) => {
    e.stopPropagation();
    setSelectedItem(null);
    setSearchValue("");
    setIsDropdownOpen(true);
    
    // Clear cities from Redux store
    dispatch(clearCities());
    
    // Inform parent component about cleared selection
    if (onLocationSelect) {
      onLocationSelect(null);
    }
    
    // Focus the input
    if (inputRef.current) {
      inputRef.current.focus();
    }
  };

  // Handle keyboard navigation (same as hero-2)
  const handleKeyDown = (e) => {
    // If a country is already selected, only allow Escape or Backspace to clear
    if (selectedItem) {
      if (e.key === "Escape" || e.key === "Backspace") {
        e.preventDefault();
        handleClearSelection(e);
      }
      return;
    }
    
    if (!suggestions.length) return;

    if (e.key === "ArrowDown") {
      setHighlightedIndex((prev) =>
        prev < suggestions.length - 1 ? prev + 1 : 0
      );
    } else if (e.key === "ArrowUp") {
      setHighlightedIndex((prev) =>
        prev > 0 ? prev - 1 : suggestions.length - 1
      );
    } else if (e.key === "Enter" && highlightedIndex !== -1) {
      e.preventDefault();
      handleCountrySelect(suggestions[highlightedIndex], e);
    }
  };

  return (
    <>
      <div className="searchMenu-loc px-30 lg:py-20 lg:px-0 js-form-dd js-liverSearch">
        <div>
          <h4 className="text-15 fw-500 ls-2 lh-16">Country</h4>
          <div className="text-15 text-light-1 ls-2 lh-16 position-relative">
            <input
              ref={inputRef}
              autoComplete="off"
              type="text"
              placeholder="Where are you going?"
              className="js-search js-dd-focus pr-30"
              value={searchValue}
              onChange={handleInputChange}
              onFocus={handleInputFocus}
              onKeyDown={handleKeyDown}
              readOnly={selectedItem !== null}
            />
            
            {selectedItem && (
              <button 
                className="position-absolute end-0 top-50 translate-middle-y pe-3 border-0 bg-transparent cursor-pointer" 
                onClick={handleClearSelection}
                type="button"
                style={{ zIndex: 10 }}
              >
                <i className="icon-close text-10" />
              </button>
            )}
            
            {isDropdownOpen && suggestions.length > 0 && (
              <div className="shadow-2 dropdown-menu min-width-200 show">
                <div className="px-20 py-20 bg-white rounded-4">
                  <ul 
                    className="y-gap-5 js-results" 
                    ref={listRef}
                    style={{
                      maxHeight: '200px',
                      overflowY: 'auto',
                      scrollbarWidth: 'thin',
                      scrollbarColor: '#ccc #f1f1f1'
                    }}
                  >
                    {suggestions.map((country, index) => (
                      <li
                        className={`-link d-block col-12 text-left rounded-4 px-10 py-10 js-search-option ${
                          highlightedIndex === index ? "bg-light-2" : ""
                        }`}
                        key={country.key || `country-${index}`}
                        role="button"
                        onClick={(e) => handleCountrySelect(country, e)}
                      >
                        <div className="d-flex align-items-center">
                          <div className="icon-location-2 text-light-1 text-16 mr-5" />
                          <div>
                            <div className="text-14 lh-12 fw-500 js-search-option-target">
                              {country.name}
                            </div>
                          </div>
                        </div>
                      </li>
                    ))}
                  </ul>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
      
    </>
  );
};

export default LocationSearch;
