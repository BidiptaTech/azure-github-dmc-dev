import { setSelectedCity } from "@/slice/common/commonSlice";
import { useState, useEffect, useRef, useMemo, useCallback } from "react";
import { useSelector } from "react-redux";
import { useDispatch } from "react-redux";

const SearchBar = ({
  setPickUpLocation,
  pickUpLocation,
  controlledCity = null,
  onCitySelect = null,
  hasError = false,
  setError = null,
}) => {
  const [selectedItem, setSelectedItem] = useState(null);
  const [searchValue, setSearchValue] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [validationError, setValidationError] = useState(false);
  const [hasUserInteracted, setHasUserInteracted] = useState(false);
  const initialSelectionRef = useRef(false);
  const lastControlledNameRef = useRef(null);
  const dropdownRef = useRef(null);
  const dispatch = useDispatch();
  const citySlice = useSelector((state) => state.common.selectedCity);

  const cityData = useSelector((state) => state.city.city);

  const transformedCityData = useMemo(() => {
    return (Array.isArray(cityData) ? cityData : [])
      .map((city, index) => {
        const address =
          typeof city === "string"
            ? city
            : city?.address || city?.name || city?.city || "";
        if (!address) return null;
        return {
          id: index + 1,
          name: String(address).split(",")[0].trim(),
          address: String(address),
        };
      })
      .filter(Boolean);
  }, [cityData]);

  const filteredCities = useMemo(() => {
    return transformedCityData.filter((item) =>
      item.name.toLowerCase().includes((searchValue || "").toString().toLowerCase())
    );
  }, [transformedCityData, searchValue]);

  const selectCity = useCallback(
    (item) => {
      setSelectedItem(item);
      setSearchValue(item.name);
      setIsDropdownOpen(false);
      setPickUpLocation(item.address);
      dispatch(
        setSelectedCity({
          id: item.id,
          name: item.name,
          address: item.address,
          city: item.name,
          country: item.address.split(",").slice(-1)[0].trim(),
        })
      );
      setValidationError(false);
      if (setError) setError(false);
      setHasUserInteracted(true);
      if (onCitySelect) {
        onCitySelect(item);
      }
    },
    [dispatch, setPickUpLocation, onCitySelect, setError]
  );

  const handleOptionClick = useCallback(
    (item) => {
      selectCity(item);
    },
    [selectCity]
  );

  const handleInputChange = useCallback(
    (e) => {
      const value = e.target.value;
      setSearchValue(value);
      setSelectedItem(null);
      setIsDropdownOpen(true);
      setPickUpLocation("");
      setValidationError(false);
      setHasUserInteracted(true);
      if (setError) setError(false);
    },
    [setPickUpLocation, setError]
  );

  useEffect(() => {
    if (!controlledCity) return;

    const nextName =
      typeof controlledCity === "string"
        ? String(controlledCity).split(",")[0].trim()
        : String(
            controlledCity?.name ||
              controlledCity?.address ||
              controlledCity?.city ||
              ""
          )
            .split(",")[0]
            .trim();

    if (!nextName) return;
    if (lastControlledNameRef.current === nextName.toLowerCase()) return;

    lastControlledNameRef.current = nextName.toLowerCase();
    initialSelectionRef.current = true;

    const matchingCity = transformedCityData.find(
      (city) =>
        city.name.toLowerCase() === nextName.toLowerCase() ||
        city.address.toLowerCase().includes(nextName.toLowerCase())
    );

    const nextItem =
      matchingCity ||
      (typeof controlledCity === "object"
        ? {
            id: controlledCity.id || 0,
            name: nextName,
            address: controlledCity.address || nextName,
          }
        : { id: 0, name: nextName, address: nextName });

    setSelectedItem(nextItem);
    setSearchValue(nextItem.name);
    setIsDropdownOpen(false);
    setHasUserInteracted(false);
    setValidationError(false);
    if (setError) setError(false);
  }, [controlledCity, transformedCityData, setError]);

  useEffect(() => {
    if (controlledCity) return;

    if (
      !hasUserInteracted &&
      citySlice &&
      !initialSelectionRef.current &&
      transformedCityData.length > 0
    ) {
      let cityToUse = null;

      if (typeof citySlice === "string") {
        const matchingCity = transformedCityData.find(
          (city) => city.name.toLowerCase() === citySlice.toLowerCase()
        );
        if (matchingCity) {
          cityToUse = matchingCity;
        }
      } else if (citySlice.name) {
        cityToUse = {
          id: citySlice.id || Math.random(),
          name: citySlice.name.split(",")[0].trim(),
          address: citySlice.address || citySlice.name,
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
  }, [
    citySlice,
    transformedCityData,
    setPickUpLocation,
    hasUserInteracted,
    controlledCity,
  ]);

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
            className={`js-search js-dd-focus ${hasError ? "border-red-500" : ""}`}
            value={searchValue}
            onChange={handleInputChange}
            onClick={() => setIsDropdownOpen(true)}
          />
          {(hasError || validationError) && (
            <div className="text-red-1 text-14 mt-5">
              Please select a location from the dropdown
            </div>
          )}
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
    </div>
  );
};

export default SearchBar;
