import { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import {
  fetchCityCountry,
  addSelectedCity,
  removeSelectedCity,
  clearSelectedCities,
} from "@/slice/common/citiesSlice";
import { setSearchLocation } from "@/slice/common/BookingSlice";

const SearchBar = ({ onLocationSelect, multiSelect = false }) => {
  const [searchValue, setSearchValue] = useState("");
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [suggestions, setSuggestions] = useState([]);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [isEditing, setIsEditing] = useState(false);
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

  useEffect(() => {
    setIsEditing(false);
    setSearchValue("");
    setIsDropdownOpen(false);
  }, [multiSelect]);

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

    const countryCodes = [
      ...new Set(
        (cities || [])
          .map((city) => city?.country_code)
          .filter(Boolean)
      ),
    ];

    dispatch(setSearchLocation(countryCodes));
  };

  const handleOptionClick = (item, event) => {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    if (!multiSelect) {
      dispatch(clearSelectedCities());
      dispatch(addSelectedCity(item));
      notifyParentLocation([item]);
      setHighlightedIndex(-1);
      setSearchValue("");
      setIsDropdownOpen(false);
      setSuggestions([]);
      setIsEditing(false);
      return;
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

  const primaryCity = selectedCities[0];

  const countryLabel =
    primaryCity?.country &&
    primaryCity.country.toLowerCase() !== primaryCity.city?.toLowerCase()
      ? primaryCity.country
      : "";

  // Single city: show clean summary until user clicks "Change city"
  const showSingleSummary =
    !multiSelect && Boolean(primaryCity) && !searchValue && !isEditing;

  const startSingleEdit = () => {
    setIsEditing(true);
    setSearchValue("");
    setTimeout(() => inputRef.current?.focus(), 0);
  };

  return (
    <div
      className="searchMenu-loc px-20 lg:py-15 lg:px-0"
      style={{ minWidth: 0, position: "relative" }}
    >
      <div>
        <div
          style={{
            fontSize: "11px",
            fontWeight: 600,
            letterSpacing: "0.04em",
            textTransform: "uppercase",
            color: "#64748b",
            marginBottom: 4,
          }}
        >
          Destination
        </div>

        {/* ——— Single city: one clear selection, no chips / add-another ——— */}
        {showSingleSummary ? (
          <div className="cursor-pointer" onClick={startSingleEdit}>
            <div
              style={{
                fontSize: "22px",
                fontWeight: 700,
                color: "#0f172a",
                lineHeight: 1.2,
                overflow: "hidden",
                textOverflow: "ellipsis",
                whiteSpace: "nowrap",
              }}
            >
              {primaryCity.city}
            </div>
            {countryLabel ? (
              <div
                style={{
                  fontSize: "12px",
                  color: "#64748b",
                  marginTop: 3,
                  overflow: "hidden",
                  textOverflow: "ellipsis",
                  whiteSpace: "nowrap",
                }}
              >
                {countryLabel}
              </div>
            ) : null}
            <div
              style={{
                marginTop: countryLabel ? 6 : 8,
                fontSize: 12,
                fontWeight: 600,
                color: "#3554D1",
              }}
            >
              Change city
            </div>
          </div>
        ) : !multiSelect ? (
          <div className="text-15 text-light-1 ls-2 lh-16 position-relative">
            <input
              ref={inputRef}
              autoComplete="off"
              type="search"
              placeholder="Enter city name"
              className="js-search js-dd-focus pr-30"
              style={{
                fontSize: "20px",
                fontWeight: 700,
                color: "#0f172a",
                width: "100%",
                border: "none",
                outline: "none",
                background: "transparent",
                padding: 0,
              }}
              value={searchValue}
              onChange={handleInputChange}
              onKeyDown={handleKeyDown}
              onFocus={handleInputFocus}
              onBlur={() => {
                setTimeout(() => {
                  if (primaryCity && !searchValue) {
                    setIsEditing(false);
                    setIsDropdownOpen(false);
                  }
                }, 150);
              }}
            />
            {isEditing && primaryCity && (
              <button
                type="button"
                onMouseDown={(e) => {
                  e.preventDefault();
                  setIsEditing(false);
                  setSearchValue("");
                  setIsDropdownOpen(false);
                }}
                style={{
                  marginTop: 6,
                  border: "none",
                  background: "transparent",
                  padding: 0,
                  fontSize: 12,
                  fontWeight: 600,
                  color: "#64748b",
                  cursor: "pointer",
                }}
              >
                Cancel
              </button>
            )}
          </div>
        ) : (
          /* ——— Multi city: chips + compact add field (no repeated title) ——— */
          <>
            {selectedCities.length > 0 && (
              <div className="d-flex flex-wrap gap-1 mb-8 position-relative">
                {selectedCities.slice(0, 2).map((city) => (
                  <div
                    key={city.city_id}
                    className="d-inline-flex align-items-center rounded-4 px-10 py-5"
                    style={{
                      fontSize: "12px",
                      background: "#eef1ff",
                      color: "#3554d1",
                      fontWeight: 600,
                    }}
                  >
                    <span>{city.city}</span>
                    <button
                      type="button"
                      className="border-0 bg-transparent cursor-pointer ms-5"
                      onClick={(e) => handleRemoveCity(city.city_id, e)}
                      style={{ padding: "0 1px", color: "#64748b" }}
                      aria-label={`Remove ${city.city}`}
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
                      className="d-inline-flex align-items-center border-0 rounded-4 px-10 py-5 more-cities-trigger cursor-pointer"
                      style={{
                        fontSize: "11px",
                        background: showMoreCitiesTooltip ? "#3554d1" : "#f1f5f9",
                        color: showMoreCitiesTooltip ? "#fff" : "#475569",
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
                              background:
                                "linear-gradient(90deg, #3554d1 0%, #4c6fff 100%)",
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
                                    {city.country &&
                                    city.country.toLowerCase() !==
                                      city.city?.toLowerCase() ? (
                                      <div
                                        style={{
                                          fontSize: "11px",
                                          color: "#6b7280",
                                          marginTop: "2px",
                                        }}
                                      >
                                        {city.country}
                                      </div>
                                    ) : null}
                                  </div>
                                  <button
                                    type="button"
                                    className="border-0 rounded-100 cursor-pointer d-flex align-items-center justify-content-center"
                                    onClick={(e) =>
                                      handleRemoveCity(city.city_id, e)
                                    }
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

            <div className="text-15 text-light-1 ls-2 lh-16 position-relative">
              <input
                ref={inputRef}
                autoComplete="off"
                type="search"
                placeholder={
                  selectedCities.length > 0
                    ? "Add another city..."
                    : "Enter city name"
                }
                className="js-search js-dd-focus pr-30"
                style={{
                  fontSize: selectedCities.length > 0 ? "14px" : "20px",
                  fontWeight: selectedCities.length > 0 ? 500 : 700,
                  color: "#0f172a",
                  width: "100%",
                  border: "none",
                  outline: "none",
                  background: "transparent",
                  padding: 0,
                }}
                value={searchValue}
                onChange={handleInputChange}
                onKeyDown={handleKeyDown}
                onFocus={handleInputFocus}
              />
            </div>
          </>
        )}
      </div>

      {isDropdownOpen && (
        <div
          className="show"
          style={{
            position: "absolute",
            left: 12,
            right: 12,
            top: "calc(100% + 6px)",
            zIndex: 10050,
            background: "#fff",
            border: "1px solid #e2e8f0",
            borderRadius: 12,
            boxShadow: "0 12px 28px rgba(15, 23, 42, 0.12)",
            overflow: "hidden",
            maxWidth: 360,
          }}
        >
          {suggestions.length > 0 ? (
            <ul
              className="js-results"
              ref={listRef}
              style={{
                listStyle: "none",
                margin: 0,
                padding: 6,
                maxHeight: 240,
                overflowY: "auto",
              }}
            >
              {suggestions.map((item, index) => {
                const isSelected = isCitySelected(item.city_id);
                const active = highlightedIndex === index;
                return (
                  <li
                    className="js-search-option"
                    key={item.city_id || `city-${index}`}
                    role="button"
                    onClick={(e) => handleOptionClick(item, e)}
                    style={{
                      cursor: "pointer",
                      display: "flex",
                      alignItems: "center",
                      gap: 10,
                      padding: "10px 12px",
                      borderRadius: 8,
                      background: active || isSelected ? "#f1f5f9" : "transparent",
                      marginBottom: 2,
                    }}
                  >
                    <i
                      className="icon-location-2"
                      style={{ fontSize: 16, color: "#64748b", flexShrink: 0 }}
                    />
                    <div style={{ minWidth: 0, flex: 1 }}>
                      <div
                        className="js-search-option-target"
                        style={{
                          fontSize: 14,
                          fontWeight: 600,
                          color: "#0f172a",
                          lineHeight: 1.25,
                        }}
                      >
                        {item.city}
                      </div>
                      <div
                        style={{
                          fontSize: 12,
                          color: "#64748b",
                          marginTop: 2,
                        }}
                      >
                        {item.country}
                      </div>
                    </div>
                    {multiSelect && isSelected && (
                      <i
                        className="icon-check"
                        style={{ fontSize: 14, color: "#3554D1" }}
                      />
                    )}
                    {!multiSelect && (
                      <span
                        style={{
                          fontSize: 11,
                          fontWeight: 600,
                          color: "#3554D1",
                        }}
                      >
                        Select
                      </span>
                    )}
                  </li>
                );
              })}
            </ul>
          ) : (
            <div style={{ padding: "14px 16px", fontSize: 13, color: "#64748b" }}>
              {searchValue && searchValue.length >= 3
                ? "No cities found"
                : searchValue
                ? "Type at least 3 characters"
                : "No results found"}
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default SearchBar;
