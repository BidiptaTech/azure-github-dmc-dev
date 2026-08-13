import { toast } from "react-toastify";
import { endpoints } from "@/services/api";

export const MANUAL_COUNTRY_VALUE = "__OTHER__";

/** Normalize /get-country payload into UI-friendly country rows */
export function normalizeCountriesFromApi(payload) {
  let list = [];
  if (Array.isArray(payload)) list = payload;
  else if (Array.isArray(payload?.countries)) list = payload.countries;
  else if (Array.isArray(payload?.country)) list = payload.country;
  else if (Array.isArray(payload?.data)) list = payload.data;
  else if (Array.isArray(payload?.results)) list = payload.results;
  else if (Array.isArray(payload?.data?.countries)) list = payload.data.countries;

  return list
    .map((c, index) => {
      if (!c || typeof c !== "object") return null;
      const code = String(
        c.code ||
          c.country_iso ||
          c.iso2 ||
          c.iso_code ||
          c.cca2 ||
          c.countryCode ||
          ""
      ).toUpperCase();
      const name =
        c.name || c.country_name || c.country || code || `Country ${index + 1}`;
      let dial = String(
        c.country_code ||
          c.dial_code ||
          c.dialCode ||
          c.phone_code ||
          c.phonecode ||
          c.idd ||
          ""
      ).trim();
      if (dial && !dial.startsWith("+")) dial = `+${dial}`;

      if (!code && !dial) return null;

      return {
        code: code || `C${index}`,
        name,
        country_code: dial || "+1",
        contact_min_length:
          Number(c.contact_min_length || c.min_length || c.minLength) || 8,
        contact_max_length:
          Number(c.contact_max_length || c.max_length || c.maxLength) || 15,
      };
    })
    .filter(Boolean)
    .sort((a, b) => String(a.name).localeCompare(String(b.name)));
}

/** Fetch + normalize countries from /get-country */
export async function fetchCountryDialCodes({ showToastOnError = true } = {}) {
  const response = await endpoints.getCountries();
  const normalized = normalizeCountriesFromApi(response?.data);
  if (!normalized.length && showToastOnError) {
    toast.error("No countries returned from /get-country");
  }
  return normalized;
}

/** Keep dial code as +digits only */
export function sanitizeDialCode(raw) {
  let value = String(raw || "").trim();
  if (value && !value.startsWith("+")) {
    value = `+${value.replace(/^\+*/, "")}`;
  }
  value = value.replace(/[^\d+]/g, "");
  if (value.indexOf("+") > 0) {
    value = `+${value.replace(/\+/g, "")}`;
  }
  return value || "+";
}
