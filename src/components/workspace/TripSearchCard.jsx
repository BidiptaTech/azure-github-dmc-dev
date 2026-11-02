import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useDispatch } from "react-redux";
import moment from "moment";
import swal from "sweetalert";
import { appPath } from "@/utils/appPaths";
import { applyTourSearchToRedux } from "@/utils/applyTourSearchToRedux";
import { clearBookingFlow } from "@/utils/clearBookingFlow";
import { fetchCityCountry, clearSelectedCities, addSelectedCity } from "@/slice/common/citiesSlice";
import { fetchDMCsByCountry, clearSelectedDmc } from "@/slice/dmc/dmcSlice";
import { resetHotels } from "@/slice/hotel/hotelSlice";
import {
  clearAttractions,
  setIsFromMainSearch,
} from "@/slice/attractions/attractionSlice";
import {
  clearRestaurants,
  setIsFromMainSearch as setRestaurantFromMainSearch,
} from "@/slice/restaurant/RestaurantsSlice";
import { resetguide } from "@/slice/tourguide/guideslice";
import { resetVehicles } from "@/slice/port/pickupDropSlice";
import { resetVehicles1 } from "@/slice/localtour/Localslice";
import { resetAllServiceResponses } from "@/slice/common/stepperButtonSlice";
import { resetSteps } from "@/slice/common/stepsSlice";
import { clearCart } from "@/slice/cart/carSlice";
import { clearUserInfo } from "@/slice/common/customerInfo";
import { clearViewDetails } from "@/slice/common/ViewDetails";
import { setHaveBooking, setSelectedCity } from "@/slice/common/commonSlice";

const DESTINATIONS = ["Singapore", "Batam", "Kuala Lumpur", "Bangkok"];

/** Fallback country when city-country API does not resolve a match */
const DESTINATION_COUNTRY = {
  Singapore: "Singapore",
  Batam: "Indonesia",
  "Kuala Lumpur": "Malaysia",
  Bangkok: "Thailand",
};

const defaultArrival = () => {
  const d = new Date();
  d.setDate(d.getDate() + 14);
  return d.toISOString().slice(0, 10);
};

const defaultDeparture = () => {
  const d = new Date();
  d.setDate(d.getDate() + 17);
  return d.toISOString().slice(0, 10);
};

export default function TripSearchCard({
  title = "Where are we taking them?",
  subtitle = "Find stays, add experiences and build a complete trip with your DMC.",
  kicker = "Your next client journey",
  showModeToggle = true,
  showRoomOptions = true,
}) {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const [mode, setMode] = useState("single");
  const [destination, setDestination] = useState("Singapore");
  const [arrival, setArrival] = useState(defaultArrival);
  const [departure, setDeparture] = useState(defaultDeparture);
  const [travellers, setTravellers] = useState("2");
  const [rooms] = useState(1);
  const [children] = useState(0);
  const [infants] = useState(0);
  const [submitting, setSubmitting] = useState(false);

  const resolveCity = async (cityName) => {
    try {
      const result = await dispatch(fetchCityCountry(cityName));
      if (fetchCityCountry.fulfilled.match(result)) {
        const list = Array.isArray(result.payload) ? result.payload : [];
        const exact =
          list.find(
            (c) =>
              String(c.city || "").toLowerCase() === cityName.toLowerCase()
          ) || list[0];
        if (exact) {
          return {
            city: exact.city || cityName,
            country: exact.country || DESTINATION_COUNTRY[cityName] || "",
            city_id: exact.city_id,
            country_code: exact.country_code,
          };
        }
      }
    } catch {
      /* fall through */
    }
    return {
      city: cityName,
      country: DESTINATION_COUNTRY[cityName] || "",
    };
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (submitting) return;

    if (!arrival || !departure) {
      swal({
        title: "Missing dates",
        text: "Please select arrival and departure dates.",
        icon: "warning",
        button: "OK",
      });
      return;
    }

    if (moment(departure).isSameOrBefore(moment(arrival), "day")) {
      swal({
        title: "Invalid dates",
        text: "Departure must be after arrival.",
        icon: "warning",
        button: "OK",
      });
      return;
    }

    setSubmitting(true);

    try {
      const adults = Number(travellers) || 1;
      const checkIn = moment(arrival).format("DD/MM/YYYY");
      const checkOut = moment(departure).format("DD/MM/YYYY");

      const resolved = await resolveCity(destination);
      const destinationLocations = [
        { city: resolved.city, country: resolved.country || "" },
      ];
      const destinationCountries = resolved.country
        ? [resolved.country]
        : [];
      const cityWiseDatesPayload = [
        {
          city: resolved.city,
          checkIn,
          checkOut,
        },
      ];

      // Same DMC availability check as MainFilterSearchBox
      if (destinationCountries.length) {
        const dmcResult = await dispatch(
          fetchDMCsByCountry(destinationCountries)
        );
        if (fetchDMCsByCountry.fulfilled.match(dmcResult)) {
          const payload = dmcResult.payload;
          const dmcList = Array.isArray(payload)
            ? payload
            : Array.isArray(payload?.data)
              ? payload.data
              : [];
          if (dmcList.length === 0) {
            await swal({
              title: "Unavailable",
              text: "Selected Cities are not available for booking on a same tour",
              icon: "warning",
              button: "OK",
            });
            return;
          }
        } else {
          await swal({
            title: "Unavailable",
            text: "Selected Cities are not available for booking on a same tour",
            icon: "warning",
            button: "OK",
          });
          return;
        }
      }

      // Mirror MainFilterSearchBox prep / clears
      dispatch(setSelectedCity(null));
      dispatch(clearViewDetails());
      dispatch(clearUserInfo());
      dispatch(setHaveBooking(false));
      dispatch(clearSelectedDmc());
      dispatch(clearSelectedCities());
      if (resolved.city_id) {
        dispatch(addSelectedCity(resolved));
      }

      dispatch(resetHotels());
      dispatch(clearAttractions());
      dispatch(setIsFromMainSearch(true));
      dispatch(resetguide());
      dispatch(resetVehicles());
      dispatch(resetVehicles1());
      dispatch(clearRestaurants());
      dispatch(setRestaurantFromMainSearch(true));
      dispatch(resetAllServiceResponses());
      dispatch(resetSteps());
      clearBookingFlow(dispatch);
      dispatch(clearCart());

      // Same Redux search fields as existing hotel search
      applyTourSearchToRedux(dispatch, {
        destination: destinationLocations,
        cityWiseDates: cityWiseDatesPayload,
        checkIn,
        checkOut,
        adults,
        children,
        infants,
        city_type: mode === "multi" ? "multi" : "single",
        country: resolved.country || "",
        searchLocation: resolved.country_code
          ? [resolved.country_code]
          : undefined,
      });

      const guestCounts = {
        Adults: adults,
        Children: children,
        Infants: infants,
        genders: Array(adults).fill(""),
        ages: [],
      };

      const searchParams = new URLSearchParams({
        location: resolved.city,
        dates: [checkIn, checkOut].join(","),
        guests: JSON.stringify(guestCounts),
      });

      // Hotel list / results (existing details flow starts here)
      navigate(`${appPath("view-hotel-search")}/0?${searchParams}`);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <section className="partner-trip-hero">
      <div className="partner-trip-hero__kicker">{kicker}</div>
      <h2 className="partner-trip-hero__title">{title}</h2>
      <p className="partner-trip-hero__sub">{subtitle}</p>

      {showModeToggle && (
        <div className="partner-seg" role="group" aria-label="Trip type">
          <button
            type="button"
            className={`partner-seg__btn${mode === "single" ? " is-active" : ""}`}
            onClick={() => setMode("single")}
          >
            Single city
          </button>
          <button
            type="button"
            className={`partner-seg__btn${mode === "multi" ? " is-active" : ""}`}
            onClick={() => setMode("multi")}
          >
            Multi-city
          </button>
        </div>
      )}

      <form className="partner-searchbar" onSubmit={handleSubmit}>
        <div className="partner-field">
          <label htmlFor="pw-destination">Destination</label>
          <select
            id="pw-destination"
            value={destination}
            onChange={(e) => setDestination(e.target.value)}
            disabled={submitting}
          >
            {DESTINATIONS.map((city) => (
              <option key={city} value={city}>
                {city}
              </option>
            ))}
          </select>
        </div>
        <div className="partner-field">
          <label htmlFor="pw-arrival">Arrival</label>
          <input
            id="pw-arrival"
            type="date"
            required
            value={arrival}
            onChange={(e) => setArrival(e.target.value)}
            disabled={submitting}
          />
        </div>
        <div className="partner-field">
          <label htmlFor="pw-departure">Departure</label>
          <input
            id="pw-departure"
            type="date"
            required
            value={departure}
            onChange={(e) => setDeparture(e.target.value)}
            disabled={submitting}
          />
        </div>
        <div className="partner-field">
          <label htmlFor="pw-travellers">Travellers</label>
          <select
            id="pw-travellers"
            value={travellers}
            onChange={(e) => setTravellers(e.target.value)}
            disabled={submitting}
          >
            {[1, 2, 3, 4, 5, 6, 8, 10].map((n) => (
              <option key={n} value={n}>
                {n} adults
              </option>
            ))}
          </select>
        </div>
        <button
          type="submit"
          className="partner-btn partner-btn--primary"
          disabled={submitting}
        >
          {submitting ? "Searching…" : "Find stays & services →"}
        </button>
      </form>

      {showRoomOptions && (
        <div className="partner-trip-hero__options">
          <button type="button">
            Room &amp; traveller options · {rooms} room(s) · {children} children
            · {infants} infants ↓
          </button>
        </div>
      )}
    </section>
  );
}
