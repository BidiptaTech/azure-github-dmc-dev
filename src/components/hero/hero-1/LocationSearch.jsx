import { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import {
  fetchCityCountry,
  addSelectedCity,
  removeSelectedCity,
} from "@/slice/common/citiesSlice";
import { setSearchLocation } from "@/slice/common/BookingSlice";

const SearchBar = ({ onLocationSelect }) => {
  const [searchValue, setSearchValue] = useState("");
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [suggestions, setSuggestions] = useState([]);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [showMoreCitiesTooltip, setShowMoreCitiesTooltip] = useState(false);
  const listRef = useRef(null);
  const inputRef = useRef(null);
  const moreCitiesRef = useRef(null);
  const closeTooltipTimerRef = useRef(null);
  const dispatch = useDispatch();

  const selectedCountries = useSelector((state) => state.dmc.selectedCountries);
  const selectedDmcData =
    selectedCountries && selectedCountries.length > 0
      ? selectedCountries[0]
      : null;

  const {
    cityCountryResults,
    loading: cityCountryLoading,
    selectedCities,
  } = useSelector((state) => state.cities || {
    cityCountryResults: [],
    loading: false,
    selectedCities: [],
  });

  // Call fetchCityCountry when user types 3 or more characters
  useEffect(() => {
    if (searchValue && searchValue.length >= 3) {
      const firstThreeChars = searchValue.substring(0, 3);
      dispatch(fetchCityCountry(firstThreeChars));
    }
  }, [searchValue, dispatch]);

  // Filter and suggest results based on search input
  useEffect(() => {
    if (searchValue) {
      if (
        searchValue.length >= 3 &&
        cityCountryResults &&
        cityCountryResults.length > 0
      ) {
        setSuggestions(cityCountryResults);
        setIsDropdownOpen(true);
      } else if (searchValue.length < 3) {
        setSuggestions([]);
        setIsDropdownOpen(false);
      }
    } else {
      setSuggestions([]);
      setIsDropdownOpen(false);
    }
  }, [searchValue, cityCountryResults]);

  // Hide overflow panel when there are no overflow cities left
  useEffect(() => {
    if (selectedCities.length <= 2) {
      setShowMoreCitiesTooltip(false);
    }
  }, [selectedCities.length]);

  useEffect(() => {
    return () => {
      if (closeTooltipTimerRef.current) {
        clearTimeout(closeTooltipTimerRef.current);
      }
    };
  }, []);

  const openMoreCitiesPanel = () => {
    if (closeTooltipTimerRef.current) {
      clearTimeout(closeTooltipTimerRef.current);
      closeTooltipTimerRef.current = null;
    }
    setShowMoreCitiesTooltip(true);
  };

  const scheduleCloseMoreCitiesPanel = () => {
    if (closeTooltipTimerRef.current) {
      clearTimeout(closeTooltipTimerRef.current);
    }
    closeTooltipTimerRef.current = setTimeout(() => {
      setShowMoreCitiesTooltip(false);
      closeTooltipTimerRef.current = null;
    }, 200);
  };

  const notifyParentLocation = (cities) => {
    if (!onLocationSelect) return;
    const nextCity = cities?.[0];
    onLocationSelect(nextCity?.country || null);
    if (nextCity?.country_code) {
      dispatch(setSearchLocation(nextCity.country_code));
    }
  };

  const handleOptionClick = (item, event) => {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    const isSelected = selectedCities.some(
      (selected) => selected.city_id === item.city_id
    );

    if (isSelected) {
      dispatch(removeSelectedCity(item.city_id));
      notifyParentLocation(
        selectedCities.filter((city) => city.city_id !== item.city_id)
      );
    } else {
      dispatch(addSelectedCity(item));
      notifyParentLocation([item, ...selectedCities]);
    }

    setHighlightedIndex(-1);
    setSearchValue("");
  };

  const handleInputChange = (e) => {
    setSearchValue(e.target.value);
    setHighlightedIndex(-1);
  };

  const handleInputFocus = () => {
    if (suggestions.length > 0) {
      setIsDropdownOpen(true);
    }
  };

  const handleRemoveCity = (cityId, e) => {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    // Keep overflow panel open while removing from it
    openMoreCitiesPanel();
    dispatch(removeSelectedCity(cityId));
    notifyParentLocation(
      selectedCities.filter((city) => city.city_id !== cityId)
    );
    if (inputRef.current) {
      inputRef.current.focus();
    }
  };

  const handleKeyDown = (e) => {
    if (!suggestions.length) return;

    if (e.key === "ArrowDown") {
      e.preventDefault();
      setHighlightedIndex((prev) =>
        prev < suggestions.length - 1 ? prev + 1 : 0
      );
      setIsDropdownOpen(true);
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      setHighlightedIndex((prev) =>
        prev > 0 ? prev - 1 : suggestions.length - 1
      );
      setIsDropdownOpen(true);
    } else if (e.key === "Enter" && highlightedIndex !== -1) {
      e.preventDefault();
      handleOptionClick(suggestions[highlightedIndex]);
    } else if (e.key === "Escape") {
      setIsDropdownOpen(false);
      setHighlightedIndex(-1);
      setShowMoreCitiesTooltip(false);
    }
  };

  useEffect(() => {
    if (listRef.current && highlightedIndex !== -1) {
      const activeItem = listRef.current.children[highlightedIndex];
      if (activeItem) {
        activeItem.scrollIntoView({ block: "nearest" });
      }
    }
  }, [highlightedIndex]);

  // Close dropdown / overflow panel when clicking outside
  useEffect(() => {
    function handleClickOutside(event) {
      if (
        listRef.current &&
        !listRef.current.contains(event.target) &&
        !event.target.classList.contains("js-search")
      ) {
        setIsDropdownOpen(false);
      }
      if (
        moreCitiesRef.current &&
        !moreCitiesRef.current.contains(event.target)
      ) {
        setShowMoreCitiesTooltip(false);
      }
    }

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  const isCitySelected = (cityId) => {
    return selectedCities.some((city) => city.city_id === cityId);
  };

  const overflowCities = selectedCities.slice(2);

  return (
    <div className="searchMenu-loc px-30 lg:py-20 lg:px-0">
      <div>
        <h4 className="text-15 fw-500 ls-2 lh-16">City</h4>
        <div className="text-15 text-light-1 ls-2 lh-16 position-relative">
          <input
            ref={inputRef}
            autoComplete="off"
            type="search"
            placeholder="Search cities (type 3+ characters)"
            className="js-search js-dd-focus pr-30 text-xs"
            style={{ fontSize: "11.5px" }}
            value={searchValue}
            onChange={handleInputChange}
            onKeyDown={handleKeyDown}
            onFocus={handleInputFocus}
          />
        </div>

        {selectedCities.length > 0 && (
          <div className="d-flex flex-wrap gap-1 mt-5 position-relative">
            {selectedCities.slice(0, 2).map((city) => (
              <div
                key={city.city_id}
                className="d-inline-flex align-items-center bg-light-2 rounded-4 px-10 py-12"
                style={{ fontSize: "10px" }}
              >
                <span className="fw-500">{city.city}</span>
                <button
                  type="button"
                  className="border-0 bg-transparent cursor-pointer ms-5"
                  onClick={(e) => handleRemoveCity(city.city_id, e)}
                  style={{ padding: "0 1px" }}
                >
                  <i className="icon-close text-10" />
                </button>
              </div>
            ))}

            {overflowCities.length > 0 && (
              <div
                ref={moreCitiesRef}
                className="position-relative d-inline-flex"
                onMouseEnter={openMoreCitiesPanel}
                onMouseLeave={scheduleCloseMoreCitiesPanel}
              >
                <button
                  type="button"
                  className="d-inline-flex align-items-center border-0 rounded-4 px-10 py-12 more-cities-trigger cursor-pointer"
                  style={{
                    fontSize: "10px",
                    background: showMoreCitiesTooltip ? "#3554d1" : "#eef1ff",
                    color: showMoreCitiesTooltip ? "#fff" : "#3554d1",
                    fontWeight: 600,
                  }}
                  onClick={(e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    setShowMoreCitiesTooltip((prev) => !prev);
                  }}
                  aria-expanded={showMoreCitiesTooltip}
                >
                  +{overflowCities.length} more
                </button>

                {showMoreCitiesTooltip && (
                  <div
                    className="position-absolute"
                    style={{
                      top: "calc(100% + 8px)",
                      left: 0,
                      minWidth: "240px",
                      maxWidth: "300px",
                      zIndex: 10050,
                    }}
                    onMouseEnter={openMoreCitiesPanel}
                    onMouseLeave={scheduleCloseMoreCitiesPanel}
                  >
                    {/* Bridge so cursor can move from chip → panel without closing */}
                    <div
                      style={{
                        position: "absolute",
                        top: "-10px",
                        left: 0,
                        right: 0,
                        height: "10px",
                      }}
                    />
                    <div
                      className="bg-white rounded-8"
                      style={{
                        border: "1px solid #e4e7f1",
                        boxShadow:
                          "0 12px 28px rgba(17, 24, 39, 0.14), 0 2px 6px rgba(17, 24, 39, 0.06)",
                        overflow: "hidden",
                      }}
                    >
                      <div
                        className="d-flex align-items-center justify-content-between px-15 py-12"
                        style={{
                          background: "linear-gradient(90deg, #3554d1 0%, #4c6fff 100%)",
                          color: "#fff",
                        }}
                      >
                        <span className="text-13 fw-600">
                          More cities ({overflowCities.length})
                        </span>
                        <button
                          type="button"
                          className="border-0 bg-transparent text-white cursor-pointer"
                          onClick={(e) => {
                            e.stopPropagation();
                            setShowMoreCitiesTooltip(false);
                          }}
                          style={{ lineHeight: 1, padding: 0 }}
                          aria-label="Close"
                        >
                          <i className="icon-close text-12" />
                        </button>
                      </div>

                      <div
                        className="p-10"
                        style={{ maxHeight: "220px", overflowY: "auto" }}
                      >
                        <div className="d-flex flex-column gap-2">
                          {overflowCities.map((city) => (
                            <div
                              key={city.city_id}
                              className="d-flex align-items-center justify-content-between rounded-4 px-12 py-10"
                              style={{
                                background: "#f7f8fc",
                                border: "1px solid #eef0f6",
                              }}
                            >
                              <div className="flex-grow-1 pe-10">
                                <div
                                  className="fw-600"
                                  style={{ fontSize: "12px", color: "#1a1a1a" }}
                                >
                                  {city.city}
                                </div>
                                <div
                                  style={{
                                    fontSize: "11px",
                                    color: "#6b7280",
                                    marginTop: "2px",
                                  }}
                                >
                                  {city.country}
                                </div>
                              </div>
                              <button
                                type="button"
                                className="border-0 rounded-100 cursor-pointer d-flex align-items-center justify-content-center"
                                onClick={(e) => handleRemoveCity(city.city_id, e)}
                                style={{
                                  width: "24px",
                                  height: "24px",
                                  background: "#fff",
                                  border: "1px solid #dde1ec",
                                  color: "#6b7280",
                                  flexShrink: 0,
                                }}
                                aria-label={`Remove ${city.city}`}
                              >
                                <i className="icon-close text-10" />
                              </button>
                            </div>
                          ))}
                        </div>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            )}
          </div>
        )}
      </div>

      {isDropdownOpen && (
        <div
          className="shadow-2 dropdown-menu min-width-400 show"
          style={{ zIndex: 10040 }}
        >
          <div className="bg-white px-20 py-20 sm:px-0 sm:py-15 rounded-4">
            {suggestions.length > 0 ? (
              <div
                className="max-height-300 overflow-y-auto"
                style={{ maxHeight: "300px" }}
              >
                <ul className="y-gap-5 js-results" ref={listRef}>
                  {suggestions.map((item, index) => {
                    const isSelected = isCitySelected(item.city_id);
                    return (
                      <li
                        className={`-link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option mb-1 ${
                          isSelected
                            ? "active bg-light-2"
                            : highlightedIndex === index
                            ? "highlighted bg-light-1"
                            : ""
                        }`}
                        key={item.city_id || `city-${index}`}
                        role="button"
                        onClick={(e) => handleOptionClick(item, e)}
                        style={{ cursor: "pointer" }}
                      >
                        <div className="d-flex align-items-center">
                          <div className="me-10" style={{ minWidth: "20px" }}>
                            {isSelected ? (
                              <i className="icon-check text-16 text-primary" />
                            ) : (
                              <div
                                style={{
                                  width: "16px",
                                  height: "16px",
                                  border: "2px solid #ddd",
                                  borderRadius: "3px",
                                }}
                              />
                            )}
                          </div>
                          <div className="icon-location-2 text-light-1 text-20 pt-4" />
                          <div className="ml-10 flex-grow-1">
                            <div
                              className="text-15 lh-16 fw-500 js-search-option-target"
                              style={{ color: "#1a1a1a" }}
                            >
                              {item.city}
                            </div>
                            <div
                              className="text-13 lh-14 text-light-1"
                              style={{ color: "#999", marginTop: "2px" }}
                            >
                              {item.country}
                            </div>
                          </div>
                        </div>
                      </li>
                    );
                  })}
                </ul>
              </div>
            ) : (
              <div className="text-center py-20">
                <div className="text-14 text-light-1 lh-16">
                  {searchValue && searchValue.length >= 3
                    ? "No cities found"
                    : searchValue
                    ? "Type at least 3 characters to search"
                    : "No results found"}
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
