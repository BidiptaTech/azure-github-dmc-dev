import { useState, useRef, useEffect, useMemo } from "react";
import { useSelector } from "react-redux";

const LocationSearch = ({ onLocationSelect }) => {
  const [searchValue, setSearchValue] = useState("");
  const [selectedItem, setSelectedItem] = useState(null);
  const [isDropdownVisible, setIsDropdownVisible] = useState(false);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [suggestions, setSuggestions] = useState([]);
  
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);
  
  // Get user_country from Redux state
  const user_country = useSelector((state) => state.auth.user_country);
  
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

  // Process available countries from user_country - memoize to prevent recreating on every render
  const locationSearchContent = useMemo(() => {
    if (user_country && Array.isArray(user_country)) {
      return user_country.map((country, index) => ({
        name: country.name,
        code: country.code,
        key: `country-${index}`
      }));
    } else if (user_country && typeof user_country === 'string') {
      return user_country.split(',').map((country, index) => ({ 
          name: country.trim(),
          code: country.trim().substring(0, 2).toUpperCase(), 
          key: `country-${index}`
        }));
    } else {
      return defaultCountries;
    }
  }, [user_country]); // Only recalculate when user_country changes

  // Filter suggestions based on search input
  useEffect(() => {
    if (searchValue && !selectedItem) {
      const filtered = locationSearchContent.filter(country => 
        country.name.toLowerCase().includes(searchValue.toLowerCase())
      );
      setSuggestions(filtered);
      setIsDropdownVisible(filtered.length > 0);
    } else if (!selectedItem && isDropdownVisible) {
      // Only show initial suggestions if dropdown is already visible (user has interacted)
      const initialSuggestions = locationSearchContent.slice(0, 5);
      setSuggestions(initialSuggestions);
    } else {
      setIsDropdownVisible(false);
    }
  }, [searchValue, selectedItem, locationSearchContent, isDropdownVisible]);

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
    setSearchValue(item.name);
    setSelectedItem(item);
    setIsDropdownVisible(false);
    
    // Call the onLocationSelect callback if provided
    if (onLocationSelect) {
      onLocationSelect(item);
    }
    
    // Remove focus
    if (inputRef.current) {
      inputRef.current.blur();
    }
  };

  const handleInputChange = (e) => {
    // If a country is already selected, prevent typing
    if (selectedItem) return;
    
    setSearchValue(e.target.value);
    setHighlightedIndex(-1);
  };

  const handleInputFocus = () => {
    if (!selectedItem) {
      const initialSuggestions = locationSearchContent.slice(0, 5);
      setSuggestions(initialSuggestions);
      setIsDropdownVisible(true);
    }
  };

  const handleClearSelection = (e) => {
    e.stopPropagation();
    setSelectedItem(null);
    setSearchValue("");
    setIsDropdownVisible(true);
    
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
        <div
          onClick={() => !selectedItem && setIsDropdownVisible(!isDropdownVisible)}
        >
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
              readOnly={selectedItem !== null}
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
              {suggestions.length > 0 ? (
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
                      </div>
                    </div>
                  </li>
                ))
              ) : (
                <li className="d-block col-12 text-left rounded-4 px-20 py-15">
                  <div className="text-15 lh-12">No locations found</div>
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

export default LocationSearch;
