import { useState, useEffect, useRef, useMemo, useCallback } from "react";
import { useSelector, useDispatch } from 'react-redux';
import { fetchCitiesByCountry, clearCities } from "../../../slice/common/citiesSlice";

const SearchBar = ({ onLocationSelect }) => {
  const [searchValueCountry, setSearchValueCountry] = useState("");
  const [searchValueCity, setSearchValueCity] = useState("");
  const [selectedCountry, setSelectedCountry] = useState(null);
  const [selectedCity, setSelectedCity] = useState(null);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [countrySuggestions, setCountrySuggestions] = useState([]);
  const [citySuggestions, setCitySuggestions] = useState([]);
  const [isCountryDropdownOpen, setIsCountryDropdownOpen] = useState(false);
  const [isCityDropdownOpen, setIsCityDropdownOpen] = useState(false);
  const [hasFetchedCities, setHasFetchedCities] = useState(false);
  const [citiesFetchedForCountry, setCitiesFetchedForCountry] = useState(null);
  const countryListRef = useRef(null);
  const cityListRef = useRef(null);
  const countryInputRef = useRef(null);
  const cityInputRef = useRef(null);
  
  // Initialize dispatch
  const dispatch = useDispatch();
  
  // Get global countries from auth state (same as SearchLocationModal)
  const global_countries = useSelector((state) => state.auth.global_countries);
  
  // Get cities from Redux store
  const { cities: cityList, loading: isLoadingCities } = useSelector((state) => state.cities);
  
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

  // Stable callback for parent notification
  const notifyParent = useCallback((locationData) => {
    if (onLocationSelect) {
      onLocationSelect(locationData);
    }
  }, [onLocationSelect]);

  // Clear cities when country changes
  useEffect(() => {
    if (!selectedCountry) {
      dispatch(clearCities());
      setHasFetchedCities(false);
      setCitiesFetchedForCountry(null);
    }
  }, [selectedCountry, dispatch]);

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

  // Fetch cities when a country is selected using Redux thunk
  const fetchCities = useCallback(async (country) => {
    // Handle both string and object parameters
    const countryName = typeof country === 'string' ? country : country.name;
    
    // Prevent multiple API calls for the same country
    if (citiesFetchedForCountry === countryName) {
      return;
    }
    
    setHasFetchedCities(false);
    
    try {
      console.log('Fetching cities for country using Redux:', countryName);
      
      // Dispatch the fetchCitiesByCountry thunk from citiesSlice
      const result = await dispatch(fetchCitiesByCountry(countryName));
      
      if (fetchCitiesByCountry.fulfilled.match(result)) {
        console.log('Cities fetched successfully:', result.payload);
        setHasFetchedCities(true);
        setCitiesFetchedForCountry(countryName);
      } else {
        console.error('Failed to fetch cities:', result.payload);
        setHasFetchedCities(true);
        setCitiesFetchedForCountry(countryName);
      }
    } catch (error) {
      console.error("Error fetching cities:", error);
      setHasFetchedCities(true);
      setCitiesFetchedForCountry(countryName);
    }
  }, [dispatch, citiesFetchedForCountry]);

  // Format cities from Redux store for UI consumption
  const formattedCities = useMemo(() => {
    if (!cityList || !Array.isArray(cityList)) {
      return [];
    }
    
    const countryCode = selectedCountry?.code || selectedCountry?.country_code || 'XX';
    
    return cityList.map((cityName, index) => ({
      name: typeof cityName === 'string' ? cityName : cityName.name || cityName,
      code: `${countryCode}-${(typeof cityName === 'string' ? cityName : cityName.name || cityName).replace(/\s+/g, '')}`,
      key: `city-${index}`
    }));
  }, [cityList, selectedCountry]);

  // Fetch cities when country is selected
  useEffect(() => {
    if (selectedCountry && !hasFetchedCities && !isLoadingCities && citiesFetchedForCountry !== selectedCountry.name) {
      fetchCities(selectedCountry);
    }
  }, [selectedCountry, hasFetchedCities, isLoadingCities, citiesFetchedForCountry, fetchCities]);

  // Filter and suggest cities based on search input and available cities
  useEffect(() => {
    if (selectedCountry) {
      if (searchValueCity) {
        const filtered = formattedCities.filter((city) =>
          city.name.toLowerCase().includes(searchValueCity.toLowerCase())
        );
        setCitySuggestions(filtered);
        setIsCityDropdownOpen(true);
      } else {
        setCitySuggestions(formattedCities);
        setIsCityDropdownOpen(formattedCities.length > 0);
      }
    } else {
      setCitySuggestions([]);
      setIsCityDropdownOpen(false);
    }
  }, [searchValueCity, selectedCountry, formattedCities]);

  // Ensure highlighted item is visible in scroll
  useEffect(() => {
    if (countryListRef.current && highlightedIndex !== -1 && isCountryDropdownOpen) {
      const activeItem = countryListRef.current.children[highlightedIndex];
      if (activeItem) {
        activeItem.scrollIntoView({ block: "nearest" });
      }
    }
  }, [highlightedIndex, isCountryDropdownOpen]);

  // Close dropdowns when clicking outside
  useEffect(() => {
    function handleClickOutside(event) {
      // Close country dropdown if click is outside
      if (countryListRef.current && 
          !countryListRef.current.contains(event.target) && 
          !countryInputRef.current.contains(event.target)) {
        setIsCountryDropdownOpen(false);
      }
      
      // Close city dropdown if click is outside
      if (cityListRef.current && 
          !cityListRef.current.contains(event.target) && 
          !cityInputRef.current.contains(event.target)) {
        setIsCityDropdownOpen(false);
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
    
    // Clear any previously selected city and reset city states
    setSearchValueCity("");
    setSelectedCity(null);
    setCitySuggestions([]);
    setHasFetchedCities(false);
    setCitiesFetchedForCountry(null);
    
    // Clear cities from Redux store
    dispatch(clearCities());
    
    // Put focus on city input
    if (cityInputRef.current) {
      cityInputRef.current.focus();
    }
  };

  const handleCitySelect = (city, event) => {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    setSearchValueCity(city.name);
    setSelectedCity(city);
    setIsCityDropdownOpen(false);
    
    // Call the callback to notify parent component (same structure as SearchLocationModal)
    if (onLocationSelect && selectedCountry) {
      onLocationSelect({
        country: selectedCountry.name,
        countryCode: selectedCountry.code || selectedCountry.country_code,
        city: city.name,
        cityCode: city.code
      });
    }
    
    // Remove focus
    if (cityInputRef.current) {
      cityInputRef.current.blur();
    }
  };

  const handleCountryInputChange = (e) => {
    // If a country is already selected, prevent typing
    if (selectedCountry) return;
    
    setSearchValueCountry(e.target.value);
    setHighlightedIndex(-1);
  };

  const handleCityInputChange = (e) => {
    // If a city is already selected, prevent typing
    if (selectedCity) return;
    
    setSearchValueCity(e.target.value);
    setHighlightedIndex(-1);
  };

  const handleCountryInputFocus = () => {
    if (!selectedCountry) {
      setIsCountryDropdownOpen(true);
    }
  };

  const handleCityInputFocus = () => {
    if (selectedCountry && !selectedCity && formattedCities.length > 0) {
      setIsCityDropdownOpen(true);
    }
  };

  const handleClearCountrySelection = (e) => {
    e.stopPropagation();
    setSelectedCountry(null);
    setSearchValueCountry("");
    setIsCountryDropdownOpen(true);
    
    // Also clear city selection since country changed
    setSelectedCity(null);
    setSearchValueCity("");
    setIsCityDropdownOpen(false);
    
    // Clear cities from Redux store
    dispatch(clearCities());
    
    // Inform parent of cleared selection
    if (onLocationSelect) {
      onLocationSelect(null);
    }
    
    // Focus the country input
    if (countryInputRef.current) {
      countryInputRef.current.focus();
    }
  };

  const handleClearCitySelection = (e) => {
    e.stopPropagation();
    setSelectedCity(null);
    setSearchValueCity("");
    setIsCityDropdownOpen(true);
    
    // Inform parent of cleared city selection (same structure as SearchLocationModal)
    if (onLocationSelect && selectedCountry) {
      onLocationSelect({
        country: selectedCountry.name,
        countryCode: selectedCountry.code || selectedCountry.country_code,
        city: null,
        cityCode: null
      });
    }
    
    // Focus the city input
    if (cityInputRef.current) {
      cityInputRef.current.focus();
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

  const handleCityKeyDown = (e) => {
    // If a city is already selected, only allow Escape or Backspace to clear
    if (selectedCity) {
      if (e.key === "Escape" || e.key === "Backspace") {
        handleClearCitySelection(e);
      }
      return;
    }
    
    if (!citySuggestions.length) return;

    if (e.key === "ArrowDown") {
      setHighlightedIndex((prev) =>
        prev < citySuggestions.length - 1 ? prev + 1 : 0
      );
    } else if (e.key === "ArrowUp") {
      setHighlightedIndex((prev) =>
        prev > 0 ? prev - 1 : citySuggestions.length - 1
      );
    } else if (e.key === "Enter" && highlightedIndex !== -1) {
      e.preventDefault();
      handleCitySelect(citySuggestions[highlightedIndex], e);
    }
  };

  return (
    <>
      <h4 className="text-15 fw-500 ls-2 lh-16">Location</h4>
      <div className="d-flex">
        <div className="mr-20 w-50">
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
            
            {/* {selectedCountry && (
              <button 
                className="position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent cursor-pointer" 
                onClick={handleClearCountrySelection}
                type="button"
              >
                <i className="icon-close text-12" />
              </button>
            )} */}
            
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
        </div>
        
        <div className="w-50">
          <div className="text-13 text-light-1 ls-2 lh-16 position-relative">
            <input
              ref={cityInputRef}
              type="text"
              placeholder={
                !selectedCountry 
                  ? "First select a country" 
                  : hasFetchedCities && formattedCities.length === 0 
                    ? "No cities available " 
                    : "Select a city available"
              }
              className="js-search js-dd-focus border-0 h-50"
              value={searchValueCity}
              onChange={handleCityInputChange}
              onFocus={handleCityInputFocus}
              onKeyDown={handleCityKeyDown}
              disabled={!selectedCountry}
              readOnly={selectedCity !== null}
            />
            
            {isLoadingCities && (
              <div className="position-absolute end-0 top-50 translate-middle-y">
                <div className="spinner-border spinner-border-sm text-primary" role="status">
                  <span className="visually-hidden">Loading...</span>
                </div>
              </div>
            )}
            
            {selectedCity && (
              <button 
                className="position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent cursor-pointer" 
                onClick={handleClearCitySelection}
                type="button"
              >
                <i className="icon-close text-12" />
              </button>
            )}
            
            {isCityDropdownOpen && (
              <div className="shadow-2 dropdown-menu min-width-200 show">
                <div className="px-20 py-20 bg-white rounded-4">
                  {citySuggestions.length > 0 ? (
                    <ul 
                      className="y-gap-5 js-results" 
                      ref={cityListRef}
                      style={{
                        maxHeight: '200px',
                        overflowY: 'auto',
                        scrollbarWidth: 'thin',
                        scrollbarColor: '#ccc #f1f1f1'
                      }}
                    >
                      {citySuggestions.map((city, index) => (
                        <li
                          className={`-link d-block col-12 text-left rounded-4 px-10 py-10 js-search-option ${
                            highlightedIndex === index ? "bg-light-2" : ""
                          }`}
                          key={city.key || `city-${index}`}
                          role="button"
                          onClick={(e) => handleCitySelect(city, e)}
                        >
                          <div className="d-flex align-items-center">
                            <div className="icon-location-2 text-light-1 text-16 mr-5" />
                            <div>
                              <div className="text-14 lh-12 fw-500 js-search-option-target">
                                {city.name}
                              </div>
                            </div>
                          </div>
                        </li>
                      ))}
                    </ul>
                  ) : (
                    <div className="text-center py-20">
                      <div className="text-14 text-light-1 lh-16">
                        {searchValueCity ? "No city found" : "No results found"}
                      </div>
                    </div>
                  )}
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
};

export default SearchBar;
