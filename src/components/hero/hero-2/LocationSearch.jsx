import { useState, useEffect, useRef, useMemo } from "react";
import { useSelector, useDispatch } from 'react-redux';
import axios from "axios";
import Cookies from "js-cookie";
import { BASE_URL, endpoints } from "@/services/api";

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
  const [cityList, setCityList] = useState([]);
  const [isLoadingCities, setIsLoadingCities] = useState(false);
  const countryListRef = useRef(null);
  const cityListRef = useRef(null);
  const countryInputRef = useRef(null);
  const cityInputRef = useRef(null);
  
  // Get user_country from Redux state
  const user_country = useSelector((state) => state.auth.user_country);
  
  // Default countries if user_country isn't available
  const defaultCountries = [
    { name: "India", code: "in" },
    { name: "Singapore", code: "SG" },
    // More countries can be added here
  ];

  // Process available countries from user_country
  const availableCountries = useMemo(() => {
    return user_country && typeof user_country === 'string'
      ? user_country.split(',').map((country, index) => ({ 
          name: country.trim(),
          code: country.trim().toLowerCase(), 
          key: `country-${index}`
        }))
      : defaultCountries;
  }, [user_country]);

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

  // Fetch cities when a country is selected
  const fetchCities = async (country) => {
    setIsLoadingCities(true);
    try {
      // Make API call to fetch cities for the selected country
      const response = await endpoints.getCities(country.name);
      
      // Log the raw API response for debugging
      console.log('Raw API response:', response.data);
      
      let formattedCities = [];
      
      // Check for the exact format shown in the user's example
      if (response.data && Array.isArray(response.data.cities)) {
        // Handle the array of city strings format
        formattedCities = response.data.cities.map((cityName, index) => ({
          name: cityName,
          code: `${country.code}-${cityName.replace(/\s+/g, '')}`,
          key: `city-${index}`
        }));
      } else if (response.data && Array.isArray(response.data)) {
        // Handle direct array format (as shown in the message)
        formattedCities = response.data.map((cityName, index) => ({
          name: cityName,
          code: `${country.code}-${cityName.replace(/\s+/g, '')}`,
          key: `city-${index}`
        }));
      } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
        // Handle nested data array format
        formattedCities = response.data.data.map((city, index) => {
          const cityName = typeof city === 'string' ? city : city.name;
          return {
            name: cityName,
            code: city.id || `${country.code}-${cityName.replace(/\s+/g, '')}`,
            key: `city-${index}`
          };
        });
      } else {
        console.error("Unexpected cities data format:", response.data);
      }
      
      console.log('Formatted cities:', formattedCities);
      setCityList(formattedCities);
      setCitySuggestions(formattedCities);
    } catch (error) {
      console.error("Error fetching cities:", error);
      setCityList([]);
      setCitySuggestions([]);
    } finally {
      setIsLoadingCities(false);
    }
  };

  // Filter and suggest cities based on selected country and search input
  useEffect(() => {
    if (selectedCountry) {
      if (cityList.length === 0 && !isLoadingCities) {
        // Fetch cities for this country if we don't have them yet
        fetchCities(selectedCountry);
      } else if (searchValueCity) {
        const filtered = cityList.filter((city) =>
          city.name.toLowerCase().includes(searchValueCity.toLowerCase())
        );
        setCitySuggestions(filtered);
        setIsCityDropdownOpen(filtered.length > 0);
      } else {
        setCitySuggestions(cityList);
        setIsCityDropdownOpen(cityList.length > 0);
      }
    } else {
      setCitySuggestions([]);
      setIsCityDropdownOpen(false);
    }
  }, [searchValueCity, selectedCountry, cityList, isLoadingCities]);

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
    
    // Clear any previously selected city
    setSearchValueCity("");
    setSelectedCity(null);
    setCityList([]);
    
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
    
    // Call the callback to notify parent component
    if (onLocationSelect && selectedCountry) {
      onLocationSelect({
        country: selectedCountry.name,
        countryCode: selectedCountry.code,
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
    if (selectedCountry && !selectedCity && cityList.length > 0) {
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
    setCityList([]);
    
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
    
    // Inform parent of cleared city selection
    if (onLocationSelect && selectedCountry) {
      onLocationSelect({
        country: selectedCountry.name,
        countryCode: selectedCountry.code,
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
            
            {selectedCountry && (
              <button 
                className="position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent cursor-pointer" 
                onClick={handleClearCountrySelection}
                type="button"
              >
                <i className="icon-close text-12" />
              </button>
            )}
            
            {isCountryDropdownOpen && countrySuggestions.length > 0 && (
              <div className="shadow-2 dropdown-menu min-width-200 show">
                <div className="px-20 py-20 bg-white rounded-4">
                  <ul className="y-gap-5 js-results" ref={countryListRef}>
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
                </div>
              </div>
            )}
          </div>
        </div>
        
        <div className="w-50">
          <div className="text-10 text-light-1 ls-2 lh-16 position-relative">
            <input
              ref={cityInputRef}
              type="text"
              placeholder={selectedCountry ? "Select a city" : "First select a country"}
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
            
            {isCityDropdownOpen && citySuggestions.length > 0 && (
              <div className="shadow-2 dropdown-menu min-width-200 show">
                <div className="px-20 py-20 bg-white rounded-4">
                  <ul className="y-gap-5 js-results" ref={cityListRef}>
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
                </div>
              </div>
            )}
            
            {selectedCountry && !isLoadingCities && cityList.length === 0 && (
              <div className="text-danger text-12 mt-5">
                No cities available for this country.
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
};

export default SearchBar;
