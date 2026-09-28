import { useState, useEffect, useRef, useMemo } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  fetchCityCountry,
  clearCities,
} from "@/slice/common/citiesSlice";

const normalizeCityName = (value) =>
  String(value || "")
    .split(",")[0]
    .trim();

const toCityOption = (value, index = 0) => {
  if (!value) return null;
  if (typeof value === "object") {
    const name = normalizeCityName(
      value.city || value.name || value.address || ""
    );
    if (!name) return null;
    return {
      city: name,
      name,
      city_id: value.city_id || value.id || `local-${name.toLowerCase()}`,
      country: value.country || value.country_name || null,
      country_code: value.country_code || value.countryCode || null,
    };
  }
  const name = normalizeCityName(value);
  if (!name) return null;
  return {
    city: name,
    name,
    city_id: `local-${name.toLowerCase()}-${index}`,
    country: null,
    country_code: null,
  };
};

const LocationSearch = ({
  onLocationSelect,
  defaultDestination,
  defaultCity,
  defaultCities,
  destinationCountries,
}) => {
  const dispatch = useDispatch();
  const [searchValue, setSearchValue] = useState("");
  const [selectedCities, setSelectedCities] = useState([]);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const [suggestions, setSuggestions] = useState([]);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [initializedKey, setInitializedKey] = useState("");
  const listRef = useRef(null);
  const inputRef = useRef(null);
  const containerRef = useRef(null);

  const { cityCountryResults, loading: cityCountryLoading } = useSelector(
    (state) =>
      state.cities || {
        cityCountryResults: [],
        loading: false,
      }
  );

  const countryList = useMemo(() => {
    if (Array.isArray(destinationCountries)) {
      return destinationCountries
        .map((item) => String(item || "").trim())
        .filter(Boolean);
    }
    if (typeof destinationCountries === "string") {
      return destinationCountries
        .split(",")
        .map((part) => part.trim())
        .filter(Boolean);
    }
    return [];
  }, [destinationCountries]);

  const applyCountryByIndex = (option, index) => {
    if (!option) return null;
    const country = option.country || countryList[index] || null;
    return {
      ...option,
      country,
      country_code: option.country_code || null,
    };
  };

  const initialCities = useMemo(() => {
    const fromProp = Array.isArray(defaultCities)
      ? defaultCities
      : defaultCities
        ? [defaultCities]
        : [];

    let cities = [];

    if (fromProp.length > 0) {
      cities = fromProp
        .map((item, index) => applyCountryByIndex(toCityOption(item, index), index))
        .filter(Boolean);
    } else if (Array.isArray(defaultCity)) {
      cities = defaultCity
        .map((item, index) => applyCountryByIndex(toCityOption(item, index), index))
        .filter(Boolean);
    } else if (defaultCity) {
      cities = [applyCountryByIndex(toCityOption(defaultCity, 0), 0)].filter(
        Boolean
      );
    } else if (defaultDestination && typeof defaultDestination === "string") {
      // Only treat as cities when destinationCountries is separately available;
      // otherwise avoid mistaking country list for city list.
      cities = [];
    } else if (Array.isArray(defaultDestination)) {
      cities = defaultDestination
        .map((item, index) => applyCountryByIndex(toCityOption(item, index), index))
        .filter(Boolean);
    }

    return cities;
  }, [defaultCities, defaultCity, defaultDestination, countryList]);

  const notifyParent = (cities) => {
    if (!onLocationSelect) return;
    if (!cities || cities.length === 0) {
      onLocationSelect(null);
      return;
    }

    const cityNames = cities.map((c) => c.city || c.name).filter(Boolean);
    const countryNames = cities.map((c) => c.country).filter(Boolean);
    const primary = cities[0];

    onLocationSelect({
      cities,
      city: cityNames.join(", "),
      cityCode: primary?.city_id || null,
      country: countryNames.join(", ") || primary?.country || null,
      countryCode: primary?.country_code || null,
    });
  };

  // Seed selected cities from cityWiseDates / defaults once per unique set
  useEffect(() => {
    const key = initialCities.map((c) => c.city.toLowerCase()).join("|");
    if (!key || key === initializedKey) return;

    setSelectedCities(initialCities);
    setInitializedKey(key);
    notifyParent(initialCities);
  }, [initialCities, initializedKey]);

  useEffect(() => {
    if (searchValue && searchValue.length >= 3) {
      dispatch(fetchCityCountry(searchValue.substring(0, 3)));
    }
  }, [searchValue, dispatch]);

  useEffect(() => {
    if (searchValue && searchValue.length >= 3 && cityCountryResults?.length) {
      setSuggestions(cityCountryResults);
      setIsDropdownOpen(true);
    } else if (searchValue.length < 3) {
      setSuggestions([]);
      setIsDropdownOpen(false);
    }
  }, [searchValue, cityCountryResults]);

  useEffect(() => {
    if (listRef.current && highlightedIndex !== -1) {
      const activeItem = listRef.current.children[highlightedIndex];
      if (activeItem) {
        activeItem.scrollIntoView({ block: "nearest" });
      }
    }
  }, [highlightedIndex]);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (
        containerRef.current &&
        !containerRef.current.contains(event.target)
      ) {
        setIsDropdownOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  useEffect(() => {
    return () => {
      dispatch(clearCities());
    };
  }, [dispatch]);

  const isCitySelected = (cityId, cityName) =>
    selectedCities.some(
      (selected) =>
        selected.city_id === cityId ||
        (selected.city || selected.name || "").toLowerCase() ===
          (cityName || "").toLowerCase()
    );

  const handleOptionClick = (item, event) => {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    const option = toCityOption(item);
    if (!option) return;

    let nextCities;
    if (isCitySelected(option.city_id, option.city)) {
      nextCities = selectedCities.filter(
        (city) =>
          city.city_id !== option.city_id &&
          (city.city || city.name || "").toLowerCase() !==
            option.city.toLowerCase()
      );
    } else {
      nextCities = [
        ...selectedCities,
        applyCountryByIndex(option, selectedCities.length),
      ];
    }

    setSelectedCities(nextCities);
    notifyParent(nextCities);
    setSearchValue("");
    setHighlightedIndex(-1);
    setSuggestions([]);
    setIsDropdownOpen(false);
  };

  const handleRemoveCity = (cityId, cityName, event) => {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    const nextCities = selectedCities.filter(
      (city) =>
        city.city_id !== cityId &&
        (city.city || city.name || "").toLowerCase() !==
          (cityName || "").toLowerCase()
    );
    setSelectedCities(nextCities);
    notifyParent(nextCities);
    if (inputRef.current) inputRef.current.focus();
  };

  const handleInputChange = (e) => {
    setSearchValue(e.target.value);
    setHighlightedIndex(-1);
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
    }
  };

  return (
    <div
      ref={containerRef}
      className="location-search-container"
      style={{ width: "100%", marginBottom: 0, position: "relative" }}
    >
      <div
        className="d-flex flex-wrap items-center"
        style={{
          minHeight: 40,
          width: "100%",
          border: "1px solid #ddd",
          borderRadius: 4,
          padding: "6px 10px",
          background: "#fff",
          gap: 6,
          cursor: "text",
        }}
        onClick={() => inputRef.current?.focus()}
      >
        {selectedCities.map((city) => (
          <span
            key={city.city_id}
            className="d-inline-flex items-center"
            style={{
              fontSize: 12,
              fontWeight: 600,
              lineHeight: 1,
              padding: "5px 8px",
              borderRadius: 4,
              background: "#eef2ff",
              color: "#3554d1",
              border: "1px solid #c7d2fe",
              whiteSpace: "nowrap",
            }}
          >
            {city.city || city.name}
            <button
              type="button"
              aria-label={`Remove ${city.city || city.name}`}
              onClick={(e) =>
                handleRemoveCity(city.city_id, city.city || city.name, e)
              }
              style={{
                marginLeft: 6,
                border: "none",
                background: "transparent",
                color: "#64748b",
                cursor: "pointer",
                padding: 0,
                fontSize: 12,
                lineHeight: 1,
              }}
            >
              ×
            </button>
          </span>
        ))}

        <input
          ref={inputRef}
          type="text"
          placeholder={
            selectedCities.length > 0
              ? "Add another city"
              : "Search and select cities"
          }
          className="js-search js-dd-focus border-0"
          style={{
            flex: 1,
            minWidth: 120,
            height: 28,
            border: "none",
            outline: "none",
            fontSize: 14,
            background: "transparent",
          }}
          value={searchValue}
          onChange={handleInputChange}
          onFocus={() => {
            if (suggestions.length > 0) setIsDropdownOpen(true);
          }}
          onKeyDown={handleKeyDown}
        />
      </div>

      {isDropdownOpen && (suggestions.length > 0 || cityCountryLoading) && (
        <div
          className="shadow-2 dropdown-menu min-width-200 show"
          style={{
            boxShadow: "0 5px 20px rgba(0,0,0,0.15)",
            position: "absolute",
            zIndex: 1000,
            top: "100%",
            left: 0,
            width: "100%",
            minWidth: 200,
            display: "block",
            marginTop: 4,
          }}
        >
          <div
            className="px-20 py-20 bg-white rounded-4"
            style={{
              padding: 12,
              backgroundColor: "white",
              borderRadius: 4,
              maxHeight: 240,
              overflowY: "auto",
            }}
          >
            {cityCountryLoading && suggestions.length === 0 ? (
              <div style={{ fontSize: 13, color: "#64748b", padding: 8 }}>
                Searching cities...
              </div>
            ) : (
              <ul
                className="y-gap-5 js-results"
                ref={listRef}
                style={{
                  listStyleType: "none",
                  margin: 0,
                  padding: 0,
                }}
              >
                {suggestions.map((item, index) => {
                  const option = toCityOption(item, index);
                  const selected = isCitySelected(
                    option?.city_id,
                    option?.city
                  );
                  return (
                    <li
                      key={option?.city_id || `suggestion-${index}`}
                      role="button"
                      onClick={(e) => handleOptionClick(item, e)}
                      style={{
                        display: "block",
                        width: "100%",
                        textAlign: "left",
                        borderRadius: 4,
                        padding: "10px",
                        backgroundColor:
                          highlightedIndex === index
                            ? "#f5f5f5"
                            : selected
                              ? "#eef2ff"
                              : "transparent",
                        cursor: "pointer",
                        marginBottom: 4,
                      }}
                    >
                      <div
                        style={{
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "space-between",
                          gap: 8,
                        }}
                      >
                        <div>
                          <div
                            style={{
                              fontSize: 14,
                              fontWeight: 500,
                              color: "#0f172a",
                            }}
                          >
                            {option?.city}
                          </div>
                          {item?.country && (
                            <div
                              style={{
                                fontSize: 12,
                                color: "#64748b",
                                marginTop: 2,
                              }}
                            >
                              {item.country}
                            </div>
                          )}
                        </div>
                        {selected && (
                          <span
                            style={{
                              fontSize: 11,
                              fontWeight: 600,
                              color: "#3554d1",
                            }}
                          >
                            Selected
                          </span>
                        )}
                      </div>
                    </li>
                  );
                })}
              </ul>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

export default LocationSearch;
