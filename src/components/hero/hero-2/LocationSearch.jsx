import { useState, useEffect, useRef, useMemo, useCallback } from "react";
import { useSelector } from 'react-redux';

const SearchBar = ({ onLocationSelect, onCountrySelect }) => {
  const [searchValueCountry, setSearchValueCountry] = useState("");
  const [selectedCountry, setSelectedCountry] = useState(null);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [countrySuggestions, setCountrySuggestions] = useState([]);
  const [isCountryDropdownOpen, setIsCountryDropdownOpen] = useState(false);
  const countryListRef = useRef(null);
  const countryInputRef = useRef(null);
  
  // Get global countries from auth state (same as SearchLocationModal)
  const global_countries = useSelector((state) => state.auth.global_countries);
  
  // Get selected DMC data from Redux store (same pattern as hero-1/LocationSearch)
  const selectedCountries = useSelector((state) => state.dmc.selectedCountries);
  const selectedDmcData = selectedCountries && selectedCountries.length > 0 ? selectedCountries[0] : null;
  console.log('selectedDmcData', selectedDmcData);

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

  // Get countries from auth state (same pattern as SearchLocationModal)
  const availableCountries = useMemo(() => {
    return global_countries && Array.isArray(global_countries)
      ? global_countries.map((country, index) => ({
          name: country.name,
          country_code: country.country_code,
          code: country.code,    
          key: `country-${index}`
        }))
      : defaultCountries;
  }, [global_countries]);

  // Notify parent when country is selected
  const notifyCountrySelect = useCallback((country) => {
    if (onCountrySelect) {
      onCountrySelect(country);
    }
    // Also call onLocationSelect for backward compatibility
    if (onLocationSelect) {
      onLocationSelect({
        country: country.name,
        countryCode: country.code || country.country_code,
        city: null,
        cityCode: null
      });
    }
  }, [onCountrySelect, onLocationSelect]);

  // Filter and suggest countries based on search input
  useEffect(() => {
    if (searchValueCountry && !selectedCountry) {
      const filtered = availableCountries.filter((country) =>
        country.name.toLowerCase().includes(searchValueCountry.toLowerCase())
      );
      setCountrySuggestions(filtered);
      setIsCountryDropdownOpen(true);
    } else {
      setCountrySuggestions(availableCountries.slice(0, 5));
      setIsCountryDropdownOpen(false);
    }
  }, [searchValueCountry, selectedCountry, availableCountries]);

  // Ensure highlighted item is visible in scroll
  useEffect(() => {
    if (countryListRef.current && highlightedIndex !== -1 && isCountryDropdownOpen) {
      const activeItem = countryListRef.current.children[highlightedIndex];
      if (activeItem) {
        activeItem.scrollIntoView({ block: "nearest" });
      }
    }
  }, [highlightedIndex, isCountryDropdownOpen]);

  // Close dropdown when clicking outside
  useEffect(() => {
    function handleClickOutside(event) {
      // Close country dropdown if click is outside
      if (countryListRef.current && 
          !countryListRef.current.contains(event.target) && 
          !countryInputRef.current.contains(event.target)) {
        setIsCountryDropdownOpen(false);
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

    setSearchValueCountry(country.name);
    setSelectedCountry(country);
    setIsCountryDropdownOpen(false);
    
    // Notify parent component about country selection
    notifyCountrySelect(country);
  };

  const handleCountryInputChange = (e) => {
    // If a country is already selected, prevent typing
    if (selectedCountry) return;
    
    setSearchValueCountry(e.target.value);
    setHighlightedIndex(-1);
  };

  const handleCountryInputFocus = () => {
    if (!selectedCountry) {
      setIsCountryDropdownOpen(true);
    }
  };

  const handleClearCountrySelection = (e) => {
    e.stopPropagation();
    setSelectedCountry(null);
    setSearchValueCountry("");
    setIsCountryDropdownOpen(true);
    
    // Inform parent of cleared selection
    if (onLocationSelect) {
      onLocationSelect(null);
    }
    if (onCountrySelect) {
      onCountrySelect(null);
    }
    
    // Focus the country input
    if (countryInputRef.current) {
      countryInputRef.current.focus();
    }
  };

  const handleCountryKeyDown = (e) => {
    // If a country is already selected, only allow Escape or Backspace to clear
    if (selectedCountry) {
      if (e.key === "Escape" || e.key === "Backspace") {
        handleClearCountrySelection(e);
      }
      return;
    }
    
    if (!countrySuggestions.length) return;

    if (e.key === "ArrowDown") {
      setHighlightedIndex((prev) =>
        prev < countrySuggestions.length - 1 ? prev + 1 : 0
      );
    } else if (e.key === "ArrowUp") {
      setHighlightedIndex((prev) =>
        prev > 0 ? prev - 1 : countrySuggestions.length - 1
      );
    } else if (e.key === "Enter" && highlightedIndex !== -1) {
      e.preventDefault();
      handleCountrySelect(countrySuggestions[highlightedIndex], e);
    }
  };


  return (
    <div className="text-15 text-light-1 ls-2 lh-16 position-relative">
        <input
          ref={countryInputRef}
          type="text"
          placeholder="Select a country"
          className="js-search js-dd-focus border-0 h-50"
          value={searchValueCountry}
          onChange={handleCountryInputChange}
          onFocus={handleCountryInputFocus}
          onKeyDown={handleCountryKeyDown}
          readOnly={selectedCountry !== null}
        />
        
        {selectedCountry && (
          <button 
            className="position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent cursor-pointer" 
            onClick={handleClearCountrySelection}
            type="button"
          >
            <i className="icon-close text-12" />
          </button>
        )}
        
        {isCountryDropdownOpen && (
          <div className="shadow-2 dropdown-menu min-width-200 show">
            <div className="px-20 py-20 bg-white rounded-4">
              {countrySuggestions.length > 0 ? (
                <ul 
                  className="y-gap-5 js-results" 
                  ref={countryListRef}
                  style={{
                    maxHeight: '200px',
                    overflowY: 'auto',
                    scrollbarWidth: 'thin',
                    scrollbarColor: '#ccc #f1f1f1'
                  }}
                >
                  {countrySuggestions.map((country, index) => (
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
              ) : (
                <div className="text-center py-20">
                  <div className="text-14 text-light-1 lh-16">
                    {searchValueCountry ? "No country found" : "No results found"}
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
