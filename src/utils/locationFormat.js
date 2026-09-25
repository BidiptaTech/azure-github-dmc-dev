/**
 * Hotel /location API expects city only (not "City, Country").
 */
export function toCityOnly(value) {
  if (!value) return "";
  if (typeof value === "object") {
    const preferred =
      value.name || value.city || value.address || value.location || "";
    return toCityOnly(preferred);
  }
  const raw = String(value).trim();
  if (!raw) return "";
  return raw.split(",")[0].trim();
}

/**
 * Normalize destination to [{ city, country }, ...]
 */
export function normalizeDestinationLocations(items) {
  if (!Array.isArray(items)) return [];
  return items
    .map((item) => {
      if (item && typeof item === "object" && (item.city || item.country)) {
        return {
          city: toCityOnly(item.city || item.name || ""),
          country: String(item.country || "").trim(),
        };
      }
      const city = toCityOnly(item);
      return city ? { city, country: "" } : null;
    })
    .filter(Boolean);
}

/**
 * Extract comma-separated city names for hotel /location API.
 */
export function extractCityNamesFromLocation(location) {
  if (!location) return "";
  if (Array.isArray(location)) {
    return location
      .map((item) =>
        item && typeof item === "object" && item.city
          ? toCityOnly(item.city)
          : toCityOnly(item)
      )
      .filter(Boolean)
      .join(",");
  }
  if (typeof location === "object" && location.city) {
    return toCityOnly(location.city);
  }
  return toCityOnly(location);
}

/**
 * Format destination for display or booking payloads.
 */
export function formatDestinationDisplay(location) {
  if (!location) return "";
  if (Array.isArray(location)) {
    return location
      .map((item) => {
        if (item && typeof item === "object" && item.city) {
          return item.country ? `${item.city}, ${item.country}` : item.city;
        }
        return toCityOnly(item);
      })
      .filter(Boolean)
      .join(", ");
  }
  if (typeof location === "object" && location.city) {
    return location.country
      ? `${location.city}, ${location.country}`
      : location.city;
  }
  return String(location);
}

/**
 * Extract unique country names for DMC API from destination payload.
 * Supports [{ city, country }, ...], string[], or a single string.
 */
export function extractCountryNamesFromDestination(destination) {
  if (!destination) return [];

  if (Array.isArray(destination)) {
    return [
      ...new Set(
        destination
          .map((item) => {
            if (item && typeof item === "object" && item.country) {
              return String(item.country).trim();
            }
            if (typeof item === "string") {
              return item.trim();
            }
            return "";
          })
          .filter(Boolean)
      ),
    ];
  }

  if (typeof destination === "object" && destination.country) {
    const country = String(destination.country).trim();
    return country ? [country] : [];
  }

  if (typeof destination === "string") {
    const value = destination.trim();
    return value ? [value] : [];
  }

  return [];
}

/**
 * Resolve country name for a selected city from tour destination payload.
 * Supports [{ city, country }, ...], legacy string[], or a single string.
 */
export function getCountryForCityFromDestination(destination, cityName) {
  const needle = toCityOnly(cityName).toLowerCase();

  if (Array.isArray(destination)) {
    if (needle) {
      const match = destination.find((entry) => {
        if (entry && typeof entry === "object" && entry.city) {
          return toCityOnly(entry.city).toLowerCase() === needle;
        }
        return toCityOnly(entry).toLowerCase() === needle;
      });
      if (match && typeof match === "object" && match.country) {
        return String(match.country).trim();
      }
    }

    const firstWithCountry = destination.find(
      (entry) => entry && typeof entry === "object" && entry.country
    );
    if (firstWithCountry?.country) {
      return String(firstWithCountry.country).trim();
    }

    const firstString = destination.find((entry) => typeof entry === "string");
    if (firstString) return String(firstString).trim();
  }

  if (typeof destination === "object" && destination?.country) {
    return String(destination.country).trim();
  }

  if (typeof destination === "string") {
    return destination.trim();
  }

  return "";
}

/**
 * Normalize city list entries to "City, Country" strings for service APIs.
 */
export function normalizeCityList(payload, countryName = "") {
  if (!Array.isArray(payload)) return [];
  const country = typeof countryName === "string" ? countryName.trim() : "";

  return payload
    .map((c) => {
      if (typeof c === "string") {
        const value = c.trim();
        if (!value) return "";
        if (country && !value.includes(",")) return `${value}, ${country}`;
        return value;
      }
      if (c && typeof c === "object") {
        const cityPart = String(c.address || c.name || c.city || "").trim();
        if (!cityPart) return "";
        if (cityPart.includes(",")) return cityPart;
        const countryPart = String(
          c.country || c.country_name || country || ""
        ).trim();
        return countryPart ? `${cityPart}, ${countryPart}` : cityPart;
      }
      return "";
    })
    .filter(Boolean);
}

/**
 * Resolve country code/name/object to a country display name.
 */
export function resolveCountryName(value, userCountry = []) {
  if (!value) return "";

  if (Array.isArray(value)) {
    return value
      .map((item) => resolveCountryName(item, userCountry))
      .filter(Boolean)
      .join(", ");
  }

  if (typeof value === "object") {
    return (
      value.name ||
      value.label ||
      value.country ||
      value.destination ||
      ""
    );
  }

  const raw = String(value).trim();
  if (!raw) return "";

  const list = Array.isArray(userCountry) ? userCountry : [];
  const match = list.find((country) => {
    if (!country) return false;
    const code = String(country.code || "").toLowerCase();
    const name = String(country.name || country.label || "").toLowerCase();
    const needle = raw.toLowerCase();
    return code === needle || name === needle;
  });

  return match?.name || match?.label || raw;
}

/**
 * Parse city/country for service list APIs.
 * Accepts "City, Country", "City, (Country)", plain city, or city objects.
 */
export function parseCityCountry(city, country) {
  let cityName = city;
  let countryName = country;

  if (city && typeof city === "object") {
    cityName = city.address || city.name || city.city || "";
    countryName =
      country || city.country || city.country_name || countryName || "";
  }

  if (typeof cityName === "string" && cityName.includes(",")) {
    const parts = cityName.split(",").map((part) => part.trim());
    cityName = parts[0];
    if (parts[1]) {
      const fromAddress = parts[1].replace(/[()]/g, "").trim();
      // Prefer country embedded in "City, Country" over weak fallbacks
      if (
        fromAddress &&
        (!countryName ||
          countryName === cityName ||
          countryName === "null" ||
          countryName === "undefined")
      ) {
        countryName = fromAddress;
      } else if (!countryName) {
        countryName = fromAddress;
      }
    }
  }

  if (typeof cityName !== "string") {
    cityName = cityName != null ? String(cityName) : "";
  }

  if (typeof countryName !== "string") {
    countryName = countryName != null ? String(countryName) : "";
  }

  countryName = countryName.trim();
  if (
    !countryName ||
    countryName === "null" ||
    countryName === "undefined" ||
    countryName === "[object Object]"
  ) {
    countryName = "";
  }

  if (!countryName && cityName) {
    countryName = cityName;
  }

  return {
    cityName: cityName.trim(),
    countryName: countryName.trim(),
  };
}
