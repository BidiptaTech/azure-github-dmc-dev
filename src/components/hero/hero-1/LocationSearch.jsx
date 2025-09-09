import { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from 'react-redux';

const SearchBar = ({ onLocationSelect }) => {
  const [searchValue, setSearchValue] = useState("");
  const [selectedItem, setSelectedItem] = useState(null);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [suggestions, setSuggestions] = useState([]);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const listRef = useRef(null);
  const inputRef = useRef(null);
  const user_country = useSelector((state) => state.auth.user_country);
  console.log('user_country123',user_country);
  
  const defaultCountries = [
    { name: "India", code: "in" },
    { name: "Singapore", code: "SG" },
    // You can add more locations here
  ];

  const locationSearchContent = user_country && Array.isArray(user_country)
    ? user_country.map((country, index) => ({
        name: country.name,
        code: country.code,
        key: `country-${index}`
      }))
    : defaultCountries;
  
  // Add more detailed logging
  useEffect(() => {
    console.log('User country from Redux:', user_country);
    console.log('Location content:', locationSearchContent);
  }, [user_country, locationSearchContent]);

  // Filter and suggest results based on search input
  useEffect(() => {
    if (searchValue && !selectedItem) {  // Only filter and show dropdown if no item is selected
      const filtered = locationSearchContent.filter((item) =>
        item.name.toLowerCase().includes(searchValue.toLowerCase())
      );
      setSuggestions(filtered);
      setIsDropdownOpen(true);
    } else {
      setSuggestions(locationSearchContent.slice(0, 5));
      setIsDropdownOpen(false);
    }
  }, [searchValue, selectedItem]);  // Add selectedItem to dependencies

  const handleOptionClick = (item, event) => {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    setSearchValue(item.name);
    setSelectedItem(item);
    setHighlightedIndex(-1);
    setIsDropdownOpen(false);
    
    // Call the onLocationSelect callback immediately
    if (onLocationSelect) {
      onLocationSelect(item);
    }
    
    // Remove focus from input after selection
    if (inputRef.current) {
      inputRef.current.blur();
    }
  };

  // Add a new function to handle input change
  const handleInputChange = (e) => {
    // If a location is already selected, prevent changing the input
    if (selectedItem) {
      return;
    }
    
    setSearchValue(e.target.value);
    setSelectedItem(null);
    setHighlightedIndex(-1);
  };
  
  // Handle input focus
  const handleInputFocus = () => {
    // If no item is selected yet, open dropdown
    if (!selectedItem) {
      setIsDropdownOpen(true);
    }
  };
  
  // Clear selected location
  const handleClearSelection = (e) => {
    e.stopPropagation();
    setSelectedItem(null);
    setSearchValue("");
    setIsDropdownOpen(true);
    
    // Inform parent component about cleared selection
    if (onLocationSelect) {
      onLocationSelect(null);
    }
    
    // Focus the input after clearing
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
      e.preventDefault(); // Prevent form submission
      handleOptionClick(suggestions[highlightedIndex]);
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
    }

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  return (
    <div className="searchMenu-loc px-30 lg:py-20 lg:px-0">
      <div>
        <h4 className="text-15 fw-500 ls-2 lh-16">Country</h4>
        <div className="text-15 text-light-1 ls-2 lh-16 position-relative">
          <input
            ref={inputRef}
            autoComplete="off"
            type="search"
            placeholder="Select a country"
            className="js-search js-dd-focus pr-30 text-xs"
            style={{ fontSize: "11.5px" }}
            value={searchValue}
            onChange={handleInputChange}
            onKeyDown={handleKeyDown}
            onFocus={handleInputFocus}
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

      {isDropdownOpen && suggestions.length > 0 && (
        <div className="shadow-2 dropdown-menu min-width-400 show">
          <div className="bg-white px-20 py-20 sm:px-0 sm:py-15 rounded-4">
            <ul className="y-gap-5 js-results" ref={listRef}>
              {suggestions.map((item, index) => (
                <li
                  className={`-link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option mb-1 ${
                    selectedItem && selectedItem.code === item.code
                      ? "active"
                      : highlightedIndex === index
                      ? "highlighted" // Highlight active selection
                      : ""
                  }`}
                  key={item.key || item.code || `country-${index}`}
                  role="button"
                  onClick={(e) => handleOptionClick(item, e)}
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
              ))}
            </ul>
          </div>
        </div>
      )}
    </div>
  );
};

export default SearchBar;
