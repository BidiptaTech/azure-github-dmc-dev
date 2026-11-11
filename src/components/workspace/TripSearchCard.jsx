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
  const [cityLegs, setCityLegs] = useState(() => {
    const a = defaultArrival();
    const b = defaultDeparture();
    const c = (() => {
      const d = new Date(b);
      d.setDate(d.getDate() + 3);
      return d.toISOString().slice(0, 10);
    })();
    return [
      { key: "leg-1", destination: "Singapore", arrival: a, departure: b, travellers: "2" },
      { key: "leg-2", destination: "Kuala Lumpur", arrival: b, departure: c, travellers: "2" },
    ];
  });
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

    const legs =
      mode === "multi"
        ? cityLegs
        : [
            {
              destination,
              arrival,
              departure,
              travellers,
            },
          ];

    for (const leg of legs) {
      if (!leg.arrival || !leg.departure) {
        swal({
          title: "Missing dates",
          text: "Please select arrival and departure dates for each city.",
          icon: "warning",
          button: "OK",
        });
        return;
      }
      if (moment(leg.departure).isSameOrBefore(moment(leg.arrival), "day")) {
        swal({
          title: "Invalid dates",
          text: "Departure must be after arrival for each city.",
          icon: "warning",
          button: "OK",
        });
        return;
      }
    }

    setSubmitting(true);

    try {
      const adults = Number(legs[0].travellers || travellers) || 1;
      const checkIn = moment(legs[0].arrival).format("DD/MM/YYYY");
      const checkOut = moment(legs[legs.length - 1].departure).format(
        "DD/MM/YYYY"
      );

      const resolvedLegs = [];
      for (const leg of legs) {
        resolvedLegs.push({
          ...(await resolveCity(leg.destination)),
          arrival: leg.arrival,
          departure: leg.departure,
        });
      }

      const destinationLocations = resolvedLegs.map((r) => ({
        city: r.city,
        country: r.country || "",
      }));
      const destinationCountries = [
        ...new Set(resolvedLegs.map((r) => r.country).filter(Boolean)),
      ];
      const cityWiseDatesPayload = resolvedLegs.map((r) => ({
        city: r.city,
        checkIn: moment(r.arrival).format("DD/MM/YYYY"),
        checkOut: moment(r.departure).format("DD/MM/YYYY"),
      }));

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
      resolvedLegs.forEach((resolved) => {
        if (resolved.city_id) dispatch(addSelectedCity(resolved));
      });

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

      const primary = resolvedLegs[0];

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
        country: primary.country || "",
        searchLocation: primary.country_code
          ? [primary.country_code]
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
        location: resolvedLegs.map((r) => r.city).join(","),
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

      {mode === "multi" ? (
        <form className="partner-multicity" onSubmit={handleSubmit}>
          {cityLegs.map((leg, index) => (
            <div className="partner-searchbar partner-searchbar--leg" key={leg.key}>
              <div className="partner-field">
                <label htmlFor={`pw-city-${index}`}>City {index + 1}</label>
                <select
                  id={`pw-city-${index}`}
                  value={leg.destination}
                  onChange={(e) => {
                    const value = e.target.value;
                    setCityLegs((prev) =>
                      prev.map((row, i) =>
                        i === index ? { ...row, destination: value } : row
                      )
                    );
                  }}
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
                <label htmlFor={`pw-arrival-${index}`}>Arrival</label>
                <input
                  id={`pw-arrival-${index}`}
                  type="date"
                  required
                  value={leg.arrival}
                  min={index > 0 ? cityLegs[index - 1].departure : undefined}
                  onChange={(e) => {
                    const value = e.target.value;
                    setCityLegs((prev) => {
                      const next = prev.map((row, i) =>
                        i === index ? { ...row, arrival: value } : row
                      );
                      if (
                        next[index + 1] &&
                        moment(next[index].departure).isSameOrBefore(
                          moment(value),
                          "day"
                        )
                      ) {
                        const dep = moment(value).add(3, "days").format("YYYY-MM-DD");
                        next[index] = { ...next[index], departure: dep };
                        next[index + 1] = {
                          ...next[index + 1],
                          arrival: dep,
                        };
                      }
                      return next;
                    });
                  }}
                  disabled={submitting}
                />
              </div>
              <div className="partner-field">
                <label htmlFor={`pw-departure-${index}`}>Departure</label>
                <input
                  id={`pw-departure-${index}`}
                  type="date"
                  required
                  value={leg.departure}
                  min={leg.arrival}
                  onChange={(e) => {
                    const value = e.target.value;
                    setCityLegs((prev) => {
                      const next = prev.map((row, i) =>
                        i === index ? { ...row, departure: value } : row
                      );
                      if (next[index + 1]) {
                        next[index + 1] = {
                          ...next[index + 1],
                          arrival: value,
                        };
                      }
                      return next;
                    });
                  }}
                  disabled={submitting}
                />
              </div>
              <button
                type="button"
                className="partner-btn partner-btn--secondary"
                aria-label={`Remove city ${index + 1}`}
                disabled={cityLegs.length <= 2 || submitting}
                onClick={() => {
                  if (cityLegs.length <= 2) return;
                  setCityLegs((prev) => prev.filter((_, i) => i !== index));
                }}
              >
                ×
              </button>
            </div>
          ))}
          <div className="partner-searchbar partner-searchbar--leg partner-searchbar--travellers">
            <div className="partner-field">
              <label htmlFor="pw-travellers-multi">Travellers</label>
              <select
                id="pw-travellers-multi"
                value={travellers}
                onChange={(e) => {
                  const value = e.target.value;
                  setTravellers(value);
                  setCityLegs((prev) =>
                    prev.map((row) => ({ ...row, travellers: value }))
                  );
                }}
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
              type="button"
              className="partner-btn partner-btn--secondary"
              disabled={submitting}
              onClick={() => {
                const last = cityLegs[cityLegs.length - 1];
                const nextDep = moment(last.departure)
                  .add(3, "days")
                  .format("YYYY-MM-DD");
                setCityLegs((prev) => [
                  ...prev,
                  {
                    key: `leg-${Date.now()}`,
                    destination: DESTINATIONS[prev.length % DESTINATIONS.length],
                    arrival: last.departure,
                    departure: nextDep,
                    travellers,
                  },
                ]);
              }}
            >
              + Add city
            </button>
            <button
              type="submit"
              className="partner-btn partner-btn--primary"
              disabled={submitting}
            >
              {submitting ? "Searching…" : "Build multi-city trip →"}
            </button>
          </div>
        </form>
      ) : (
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
      )}

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
