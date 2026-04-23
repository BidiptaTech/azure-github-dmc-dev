import { useState, useRef, useEffect, useMemo } from "react";
import { useSelector, useDispatch } from 'react-redux';
import { setCity } from '@/slice/common/citySlice';
import axios from "axios";
import Cookies from "js-cookie";
import { BASE_URL, endpoints } from "@/services/api";

const CitySearch = ({ selectedCountry, onCitySelect, initialValue = null }) => {
  const dispatch = useDispatch();
  const [searchValue, setSearchValue] = useState("");
  const [selectedItem, setSelectedItem] = useState(null);
  const [isDropdownVisible, setIsDropdownVisible] = useState(false);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [suggestions, setSuggestions] = useState([]);
  const [loading, setLoading] = useState(false);
  const [cityList, setCityList] = useState([]);
  const [error, setError] = useState(null);
  
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Fetch cities when a country is selected
  const fetchCities = async (country) => {
    setLoading(true);
    setError(null);
    
    try {
      // Check if auth token exists
      const authToken = Cookies.get("authToken");
      if (!authToken) {
        setError("Authentication required. Please login again.");
        setLoading(false);
        return;
      }
      
      // Use the endpoints.getCities function which already handles authentication
      const response = await endpoints.getCities(country.name);
      
     
      
      let formattedCities = [];
      
      // Check for the expected format
      if (response.data && Array.isArray(response.data.cities)) {
        // Handle the array of city strings format
        formattedCities = response.data.cities.map((cityName, index) => ({
          name: cityName.split(",")[0],
          address: cityName,
          code: `${country.code}-${cityName.split(",")[0].replace(/\s+/g, '').substring(0, 3).toUpperCase()}`,
          key: `city-${index}`
        }));
        
        // Store cities in Redux for other components to use
        dispatch(setCity(response.data.cities));
        
        // Set initial value if provided after city list is loaded
        if (initialValue && !selectedItem) {
          const foundCity = formattedCities.find(
            city => city.name === initialValue || city.code === initialValue
          );
          
          if (foundCity) {
            setSelectedItem(foundCity);
            setSearchValue(foundCity.name);
            if (onCitySelect) onCitySelect(foundCity);
          } else if (typeof initialValue === 'string') {
            // If we can't find the city but have a string, use it as is
            const newCity = {
              name: initialValue,
              address: initialValue,
              code: `${country.code}-${initialValue.replace(/\s+/g, '').substring(0, 3).toUpperCase()}`
            };
            setSelectedItem(newCity);
            setSearchValue(initialValue);
            if (onCitySelect) onCitySelect(newCity);
          }
        }
      } else {
        console.error("Unexpected cities data format:", response.data);
        setError("Invalid response format from server");
      }
      
     
      setCityList(formattedCities);
      setSuggestions(formattedCities.slice(0, 5));
      // Don't show dropdown by default - only when user types
      setIsDropdownVisible(false);
    } catch (error) {
      console.error("Error fetching cities:", error);
      
      // Handle specific error types
      if (error.response) {
        if (error.response.status === 401) {
          setError("Authentication failed. Please login again.");
        } else if (error.response.status === 404) {
          setError("No cities found for this country.");
        } else {
          setError(`Server error: ${error.response.status}`);
        }
      } else if (error.request) {
        setError("No response from server. Check your connection.");
      } else {
        setError("Failed to fetch cities. Please try again.");
      }
      
      setCityList([]);
      setSuggestions([]);
    } finally {
      setLoading(false);
    }
  };

  // Reset selected city when country changes and fetch cities
  useEffect(() => {
    if (selectedCountry) {
      // Don't reset if we have an initialValue to preserve
      if (!initialValue) {
        setSearchValue("");
        setSelectedItem(null);
      }
      setSuggestions([]);
      
      // Fetch cities for the selected country
      fetchCities(selectedCountry);
    } else {
      setSuggestions([]);
      setSelectedItem(null);
      setSearchValue("");
      setCityList([]);
    }
  }, [selectedCountry]);

  // Filter suggestions based on search input
  useEffect(() => {
    // Don't show dropdown if a city is already selected
    if (selectedItem) {
      setIsDropdownVisible(false);
      return;
    }
    
    if (cityList.length > 0) {
      if (searchValue && searchValue.trim().length > 0) {
        const filtered = cityList.filter(city => 
          city.name.toLowerCase().includes(searchValue.toLowerCase())
        );
        setSuggestions(filtered);
        setIsDropdownVisible(filtered.length > 0);
      } else {
        // Show first 5 cities when search is empty
        const initialSuggestions = cityList.slice(0, 5);
        setSuggestions(initialSuggestions);
        setIsDropdownVisible(false);
      }
    } else {
      setSuggestions([]);
      setIsDropdownVisible(false);
    }
  }, [cityList, searchValue, selectedItem]);

  // Ensure highlighted item is visible in scroll
  useEffect(() => {
    if (dropdownRef.current && highlightedIndex !== -1 && isDropdownVisible) {
      const activeItem = dropdownRef.current.children[highlightedIndex];
      if (activeItem) {
        activeItem.scrollIntoView({ block: "nearest" });
      }
    }
  }, [highlightedIndex, isDropdownVisible]);

  // Close dropdown when clicking outside
  useEffect(() => {
    function handleClickOutside(event) {
      if (
        dropdownRef.current && 
        !dropdownRef.current.contains(event.target) && 
        inputRef.current && 
        !inputRef.current.contains(event.target)
      ) {
        setIsDropdownVisible(false);
      }
    }

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  const handleOptionClick = (item) => {
   
    
    // Close dropdown immediately
    setIsDropdownVisible(false);
    
    // Set the selected city
    setSearchValue(item.name);
    setSelectedItem(item);
    
    // Call the onCitySelect callback if provided
    if (onCitySelect) {
     
      onCitySelect(item);
    } else {
      alert('🏙️ CitySearch - No onCitySelect callback provided');
    }
    
    // Remove focus to prevent any further interactions
    if (inputRef.current) {
      inputRef.current.blur();
    }
    
    // Ensure dropdown stays closed with a small delay
    setTimeout(() => {
      setIsDropdownVisible(false);
    }, 100);
  };

  const handleInputChange = (e) => {
    setSearchValue(e.target.value);
    setHighlightedIndex(-1);
    
    // If user starts typing, clear selection to allow new search
    if (selectedItem && e.target.value !== selectedItem.name) {
      setSelectedItem(null);
    }
  };

  const handleInputFocus = () => {
    // Don't show dropdown on focus - only when user types
  };

  const handleClearSelection = (e) => {
    e.stopPropagation();
    setSelectedItem(null);
    setSearchValue("");
    setIsDropdownVisible(true);
    
    // Inform parent component about cleared selection
    if (onCitySelect) {
      onCitySelect(null);
    }
    
    // Focus the input
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
      setHighlightedIndex((prev) =>
        prev < suggestions.length - 1 ? prev + 1 : 0
      );
    } else if (e.key === "ArrowUp") {
      setHighlightedIndex((prev) =>
        prev > 0 ? prev - 1 : suggestions.length - 1
      );
    } else if (e.key === "Enter" && highlightedIndex !== -1) {
      e.preventDefault();
      handleOptionClick(suggestions[highlightedIndex]);
    }
  };

  return (
    <>
      <div className="searchMenu-loc px-30 lg:py-20 lg:px-0 js-form-dd js-liverSearch">
        <div>
          <h4 className="text-15 fw-500 ls-2 lh-16">City</h4>
          <div className="text-15 text-light-1 ls-2 lh-16 position-relative">
            <input
              ref={inputRef}
              autoComplete="off"
              type="search"
              placeholder={selectedCountry ? "Select a city" : "Select a country first"}
              className="js-search js-dd-focus pr-30"
              value={searchValue}
              onChange={handleInputChange}
              onFocus={handleInputFocus}
              onKeyDown={handleKeyDown}
              readOnly={!selectedCountry}
              disabled={!selectedCountry || loading}
            />
            {selectedItem && (
              <button 
                className="position-absolute end-0 top-50 translate-middle-y pe-3 border-0 bg-transparent cursor-pointer" 
                onClick={handleClearSelection}
                type="button"
              >
                <i className="icon-close text-10" />
              </button>
            )}
            {loading && (
              <div className="position-absolute end-0 top-50 translate-middle-y pe-3">
                <div className="spinner-border spinner-border-sm text-primary" role="status">
                  <span className="visually-hidden">Loading...</span>
                </div>
              </div>
            )}
          </div>
          
          {error && (
            <div className="text-danger text-12 mt-5">
              {error}
            </div>
          )}
          
          {selectedCountry && !loading && cityList.length === 0 && !error && (
            <div className="text-danger text-12 mt-5">
              No cities available for this country.
            </div>
          )}
        </div>

        <div 
          className={`shadow-2 dropdown-menu min-width-400 ${isDropdownVisible ? 'show' : ''}`}
          style={{
            position: 'absolute',
            zIndex: 9999,
            display: isDropdownVisible ? 'block' : 'none',
            top: '100%',
            left: 0,
          }}
        >
          <div className="bg-white px-20 py-20 sm:px-0 sm:py-15 rounded-4">
            <ul className="y-gap-5 js-results" ref={dropdownRef} tabIndex="-1">
              {loading ? (
                <li className="d-block col-12 text-left rounded-4 px-20 py-15">
                  <div className="d-flex align-items-center">
                    <div className="spinner-border spinner-border-sm text-primary me-3" role="status">
                      <span className="visually-hidden">Loading...</span>
                    </div>
                    <div className="text-15 lh-12">Loading cities...</div>
                  </div>
                </li>
              ) : suggestions.length > 0 ? (
                suggestions.map((item, index) => (
                  <li
                    className={`-link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option mb-1 ${
                      highlightedIndex === index ? "highlighted" : ""
                    }`}
                    key={item.key || item.code || index}
                    role="button"
                    onClick={() => handleOptionClick(item)}
                  >
                    <div className="d-flex">
                      <div className="icon-location-2 text-light-1 text-20 pt-4" />
                      <div className="ml-10">
                        <div className="text-15 lh-12 fw-500 js-search-option-target">
                          {item.name}
                        </div>
                        {item.address && item.address !== item.name && (
                          <div className="text-14 lh-12 text-light-1 mt-5">{item.address}</div>
                        )}
                      </div>
                    </div>
                  </li>
                ))
              ) : selectedCountry ? (
                <li className="d-block col-12 text-left rounded-4 px-20 py-15">
                  <div className="text-15 lh-12">No cities found</div>
                </li>
              ) : (
                <li className="d-block col-12 text-left rounded-4 px-20 py-15">
                  <div className="text-15 lh-12">Select a country first</div>
                </li>
              )}
            </ul>
          </div>
        </div>
      </div>
      
      <style jsx>{`
        .highlighted {
          background-color: rgba(53, 84, 209, 0.05);
        }
      `}</style>
    </>
  );
};

export default CitySearch; 