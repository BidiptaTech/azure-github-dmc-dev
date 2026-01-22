import { setSelectedCity } from "@/slice/common/commonSlice";
import { useState, useEffect, useRef, useMemo, useCallback } from "react";
import { useSelector } from "react-redux";
import { useDispatch } from "react-redux";

const SearchBar = ({ setPickUpLocation, pickUpLocation }) => {
  const [selectedItem, setSelectedItem] = useState(null);
  const [searchValue, setSearchValue] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [validationError, setValidationError] = useState(false);
  const [hasUserInteracted, setHasUserInteracted] = useState(false);
  const initialSelectionRef = useRef(false);
  const dropdownRef = useRef(null);
  const dispatch = useDispatch();
  const citySlice = useSelector((state) => state.common.selectedCity);

  // Access city data from Redux store
  const cityData = useSelector((state) => state.city.city);

  // Transform city data into expected format - memoize to prevent recreation on every render
  const transformedCityData = useMemo(() => {
    return cityData.map((city, index) => ({
      id: index + 1,
      name: city.split(",")[0].trim(), // Extract and clean city name
      address: city, // Full address
    }));
  }, [cityData]);

  // Filter cities based on user input - memoize to prevent recreation on every render
  const filteredCities = useMemo(() => {
    return transformedCityData.filter((item) =>
      item.name.toLowerCase().includes((searchValue || "").toString().toLowerCase())
    );
  }, [transformedCityData, searchValue]);

  // Handle option click with useCallback to maintain reference stability
  const handleOptionClick = useCallback((item) => {
    setSelectedItem(item);
    setSearchValue(item.name);
    setIsDropdownOpen(false);
    setPickUpLocation(item.address);
    dispatch(setSelectedCity({
      id: item.id,
      name: item.name,
      address: item.address,
      city: item.name,
      country: item.address.split(",").slice(-1)[0].trim()
    }));
    setValidationError(false);
    setHasUserInteracted(true);
  }, [dispatch, setPickUpLocation]);

  // Handle input change with useCallback
  const handleInputChange = useCallback((e) => {
    const value = e.target.value;
    setSearchValue(value);
    setSelectedItem(null);
    setIsDropdownOpen(true);
    setPickUpLocation("");
    setValidationError(false);
    setHasUserInteracted(true);
  }, [setPickUpLocation]);

  // Handle initial city selection from Redux store
  useEffect(() => {
    if (!hasUserInteracted && citySlice && !initialSelectionRef.current && transformedCityData.length > 0) {
      let cityToUse = null;

      if (typeof citySlice === 'string') {
        // If citySlice is a string, find matching city
        const matchingCity = transformedCityData.find(
          city => city.name.toLowerCase() === citySlice.toLowerCase()
        );
        if (matchingCity) {
          cityToUse = matchingCity;
        }
      } else if (citySlice.name) {
        // If citySlice has name property, create city object
        cityToUse = {
          id: citySlice.id || Math.random(),
          name: citySlice.name.split(",")[0].trim(),
          address: citySlice.address || citySlice.name
        };
      }

      if (cityToUse) {
        setSelectedItem(cityToUse);
        setSearchValue(cityToUse.name);
        setPickUpLocation(cityToUse.address);
        setValidationError(false);
        initialSelectionRef.current = true;
      }
    }
  }, [citySlice, transformedCityData, setPickUpLocation, hasUserInteracted]);

  // Handle clicks outside the dropdown
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsDropdownOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  return (
    <div className="searchMenu-loc px-30 lg:py-20 lg:px-0 js-form-dd js-liverSearch">
      <div>
        <h4 className="text-15 fw-500 ls-2 lh-16">Location</h4>
        <div className="text-15 text-light-1 ls-2 lh-16">
          <input
            autoComplete="off"
            type="search"
            placeholder="Where are you going?"
            className="js-search js-dd-focus"
            value={searchValue}
            onChange={handleInputChange}
            onClick={() => setIsDropdownOpen(true)}
          />
        </div>
      </div>

      {isDropdownOpen && filteredCities.length > 0 && (
        <div
          className="shadow-2 border-light rounded-4 bg-white position-absolute z-2 w-100 mt-2"
          ref={dropdownRef}
        >
          {filteredCities.map((item) => (
            <div
              key={item.id}
              className="px-20 py-10 cursor-pointer"
              onClick={() => handleOptionClick(item)}
            >
              <div className="text-15 lh-12 fw-500">{item.name}</div>
              <div className="text-14 text-light-1">{item.address}</div>
            </div>
          ))}
        </div>
      )}

      {validationError && (
        <div className="text-red-1 text-14 mt-5">
          Please select a location from the dropdown
        </div>
      )}
    </div>
  );
};

export default SearchBar;
