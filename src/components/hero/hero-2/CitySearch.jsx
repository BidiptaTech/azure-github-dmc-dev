import { useState, useEffect, useRef, useMemo, useCallback } from "react";
import { useSelector, useDispatch } from 'react-redux';
import { fetchCitiesByCountry, clearCities } from "../../../slice/common/citiesSlice";

const CitySearch = ({ selectedCountry, onCitySelect }) => {
  const [searchValueCity, setSearchValueCity] = useState("");
  const [selectedCity, setSelectedCity] = useState(null);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [citySuggestions, setCitySuggestions] = useState([]);
  const [isCityDropdownOpen, setIsCityDropdownOpen] = useState(false);
  const [hasFetchedCities, setHasFetchedCities] = useState(false);
  const [citiesFetchedForCountry, setCitiesFetchedForCountry] = useState(null);
  const cityListRef = useRef(null);
  const cityInputRef = useRef(null);
  
  // Initialize dispatch
  const dispatch = useDispatch();
  
  // Get cities from Redux store
  const { cities: cityList, loading: isLoadingCities } = useSelector((state) => state.cities);

  // Clear cities when country changes or is cleared
  useEffect(() => {
    if (!selectedCountry) {
      dispatch(clearCities());
      setHasFetchedCities(false);
      setCitiesFetchedForCountry(null);
      setSearchValueCity("");
      setSelectedCity(null);
      setCitySuggestions([]);
      setIsCityDropdownOpen(false);
    }
  }, [selectedCountry, dispatch]);

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

  // Close dropdown when clicking outside
  useEffect(() => {
    function handleClickOutside(event) {
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

  const handleCitySelect = (city, event) => {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    setSearchValueCity(city.name);
    setSelectedCity(city);
    setIsCityDropdownOpen(false);
    
    // Call the callback to notify parent component
    if (onCitySelect && selectedCountry) {
      onCitySelect({
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

  const handleCityInputChange = (e) => {
    // If a city is already selected, prevent typing
    if (selectedCity) return;
    
    setSearchValueCity(e.target.value);
    setHighlightedIndex(-1);
  };

  const handleCityInputFocus = () => {
    if (selectedCountry && !selectedCity && formattedCities.length > 0) {
      setIsCityDropdownOpen(true);
    }
  };

  const handleClearCitySelection = (e) => {
    e.stopPropagation();
    setSelectedCity(null);
    setSearchValueCity("");
    setIsCityDropdownOpen(true);
    
    // Inform parent of cleared city selection
    if (onCitySelect && selectedCountry) {
      onCitySelect({
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
      <style dangerouslySetInnerHTML={{__html: `
        .city-search-input-placeholder-red::placeholder {
          color: #dc3545 !important;
          opacity: 1;
        }
        .city-search-input-placeholder-red:-ms-input-placeholder {
          color: #dc3545 !important;
        }
        .city-search-input-placeholder-red::-ms-input-placeholder {
          color: #dc3545 !important;
        }
      `}} />
      <div className="text-13 text-light-1 ls-2 lh-16 position-relative">
        <input
          ref={cityInputRef}
          type="text"
          placeholder={
            !selectedCountry 
              ? "Select a country first *" 
              : hasFetchedCities && formattedCities.length === 0 
                ? "No cities available for this country" 
                : "Select a city"
          }
          className={`js-search js-dd-focus border-0 h-50 ${!selectedCountry ? 'city-search-input-placeholder-red' : ''}`}
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
    </>
  );
};

export default CitySearch;
