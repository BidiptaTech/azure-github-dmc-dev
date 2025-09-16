import { useState, useRef, useEffect, useMemo } from "react";
import { useSelector } from "react-redux";

const LocationSearch = ({ onLocationSelect, initialValue = null }) => {
  const [searchValue, setSearchValue] = useState("");
  const [selectedItem, setSelectedItem] = useState(null);
  const inputRef = useRef(null);
  
  // Get selected DMC data from Redux store
  const selectedDmcData = useSelector((state) => state.dmc.selectedDmcData);
  
  // Default countries if user_country isn't available
  const defaultCountries = [
    {
      name: "India",
      code: "IN",
    },
    {
      name: "Singapore",
      code: "SG",
    },
    {
      name: "Malaysia",
      code: "MY",
    },
    {
      name: "Thailand",
      code: "TH",
    },
    {
      name: "Indonesia",
      code: "ID",
    },
  ];

  // Get country from selected DMC data - memoize to prevent recreating on every render
  const locationSearchContent = useMemo(() => {
    if (selectedDmcData && selectedDmcData.location && selectedDmcData.location !== 'Auto-selected') {
      return [{ 
        name: selectedDmcData.location, // Full country name
        code: selectedDmcData.location, // Use full name as code for database
        key: 'selected-dmc-country' 
      }];
    } else {
      return defaultCountries;
    }
  }, [selectedDmcData]); // Only recalculate when selectedDmcData changes

  // Auto-select DMC country when selectedDmcData changes
  useEffect(() => {
    if (selectedDmcData && selectedDmcData.location && selectedDmcData.location !== 'Auto-selected') {
      const dmcCountry = {
        name: selectedDmcData.location, // Full country name
        code: selectedDmcData.location, // Use full name as code for database
        key: 'selected-dmc-country'
      };
      
      // Check if the country has actually changed to avoid unnecessary updates
      const currentCountry = selectedItem?.name;
      if (currentCountry !== dmcCountry.name) {
        console.log('🌍 LocationSearch - Auto-selecting country:', dmcCountry.name);
        setSearchValue(dmcCountry.name);
        setSelectedItem(dmcCountry);
        
        // Call the onLocationSelect callback
        if (onLocationSelect) {
          onLocationSelect(dmcCountry);
        }
      }
    } else if (!selectedDmcData && selectedItem) {
      // If DMC data is cleared, clear the selection
      console.log('🌍 LocationSearch - Clearing selection due to no DMC data');
      setSelectedItem(null);
      setSearchValue("");
      if (onLocationSelect) {
        onLocationSelect(null);
      }
    }
  }, [selectedDmcData, onLocationSelect]); // Removed selectedItem from dependencies

  // Set initial value if provided
  useEffect(() => {
    if (initialValue && !selectedItem) {
      const foundCountry = locationSearchContent.find(
        country => country.name === initialValue || country.code === initialValue
      );
      
      if (foundCountry) {
        setSelectedItem(foundCountry);
        setSearchValue(foundCountry.name);
      } else if (typeof initialValue === 'string') {
        // If we can't find the country but have a string, use it as is
        setSelectedItem({ name: initialValue });
        setSearchValue(initialValue);
      }
    }
  }, [initialValue, locationSearchContent, selectedItem]);

  // Handle country selection when user types and presses Enter or loses focus
  useEffect(() => {
    if (searchValue && searchValue.trim().length > 0) {
      // Check if the typed value matches any available country
      const matchedCountry = locationSearchContent.find(country => 
        country.name.toLowerCase() === searchValue.toLowerCase()
      );
      
      if (matchedCountry) {
        setSelectedItem(matchedCountry);
        if (onLocationSelect) {
          onLocationSelect(matchedCountry);
        }
      }
    }
  }, [searchValue, locationSearchContent, onLocationSelect]);



  const handleInputChange = (e) => {
    setSearchValue(e.target.value);
    
    // If user starts typing, clear selection to allow new search
    if (selectedItem && e.target.value !== selectedItem.name) {
      setSelectedItem(null);
    }
  };

  const handleInputFocus = () => {
    // No dropdown needed - just focus the input
  };

  const handleClearSelection = (e) => {
    e.stopPropagation();
    setSelectedItem(null);
    setSearchValue("");
    
    // Inform parent component about cleared selection
    if (onLocationSelect) {
      onLocationSelect(null);
    }
    
    // Focus the input
    if (inputRef.current) {
      inputRef.current.focus();
    }
  };

  // Handle keyboard navigation
  const handleKeyDown = (e) => {
    // If a location is already selected, only allow Escape or Backspace to clear
    if (selectedItem) {
      if (e.key === "Escape" || e.key === "Backspace") {
        handleClearSelection(e);
      }
      return;
    }
    
    // Handle Enter key to confirm selection
    if (e.key === "Enter") {
      e.preventDefault();
      if (searchValue && searchValue.trim().length > 0) {
        // Check if the typed value matches any available country
        const matchedCountry = locationSearchContent.find(country => 
          country.name.toLowerCase() === searchValue.toLowerCase()
        );
        
        if (matchedCountry) {
          setSelectedItem(matchedCountry);
          if (onLocationSelect) {
            onLocationSelect(matchedCountry);
          }
        }
      }
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
              type="search"
              placeholder="Where are you going?"
              className="js-search js-dd-focus pr-30"
              value={searchValue}
              onChange={handleInputChange}
              onFocus={handleInputFocus}
              onKeyDown={handleKeyDown}
              readOnly={false}
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
          </div>
        </div>
      </div>
      
    </>
  );
};

export default LocationSearch;
