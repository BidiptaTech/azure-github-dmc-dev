import { useMemo } from "react";
import { useNavigate } from "react-router-dom";
import { useSelector } from "react-redux";
import moment from "moment";
import ServiceTabs from "@/components/workspace/ServiceTabs";
import TripSummary from "@/components/workspace/TripSummary";
import { appPath } from "@/utils/appPaths";
import { formatDestinationLabel } from "@/utils/applyTourSearchToRedux";

function formatTripDates(checkIn, checkOut) {
  const a = moment(checkIn, ["DD/MM/YYYY", "YYYY-MM-DD"]);
  const b = moment(checkOut, ["DD/MM/YYYY", "YYYY-MM-DD"]);
  if (!a.isValid() || !b.isValid()) return "";
  const nights = Math.max(0, b.diff(a, "days"));
  return `${a.format("YYYY-MM-DD")} — ${b.format("YYYY-MM-DD")} · ${nights} night${
    nights === 1 ? "" : "s"
  }`;
}

/**
 * Shared chrome for Hotels / Ports / Attractions / Guides / Dining / Transfers.
 * Pass the service content (filters + results) as children.
 */
export default function TripWorkspaceShell({
  children,
  filters = null,
  hideSummary = false,
  resultsClassName = "",
}) {
  const navigate = useNavigate();
  const tourdetails = useSelector((state) => state.hotels?.tourdetails);
  const searchState = useSelector((state) => state.hotels?.searchState);
  const dmcCompanyName = useSelector(
    (state) =>
      state.dmc?.selectedDmcData?.company_name || state.auth?.dmcCompanyName
  );

  const destination = useMemo(
    () =>
      formatDestinationLabel(
        tourdetails?.destination || searchState?.location || "your"
      ),
    [tourdetails, searchState]
  );

  const checkIn = tourdetails?.CheckInTime || searchState?.ucheckIn;
  const checkOut = tourdetails?.CheckOutTime || searchState?.ucheckOut;
  const adults = tourdetails?.adult || searchState?.guests?.adults || 2;
  const childCount = tourdetails?.child || searchState?.guests?.children || 0;
  const dateLine = formatTripDates(checkIn, checkOut);
  const paxLine = [
    dateLine,
    `${adults} adult${adults === 1 ? "" : "s"}`,
    childCount ? `${childCount} child${childCount === 1 ? "" : "ren"}` : null,
    "1 room(s)",
  ]
    .filter(Boolean)
    .join(" · ");

  return (
    <>
      <div className="partner-hero-row">
        <div>
          <div className="partner-kicker">Trip workspace</div>
          <h1 className="partner-title">
            Build a better {destination === "your" ? "trip" : destination}{" "}
            journey.
          </h1>
          <p className="partner-lede">
            All your services. One itinerary. Clear pricing from the start.
          </p>
        </div>
        <button
          type="button"
          className="partner-btn partner-btn--secondary"
          onClick={() => navigate(appPath("booking-enquiry"))}
        >
          Ask DMC to plan it
        </button>
      </div>

      <div className="pw-tripbar">
        <div className="pw-tripbar__meta">
          <strong>{destination === "your" ? "Your trip" : destination}</strong>
          <span>{paxLine || "Set dates and travellers to continue"}</span>
        </div>
        <div className="pw-tripbar__actions">
          <button
            type="button"
            className="partner-btn partner-btn--secondary"
            onClick={() => navigate(appPath("plan"))}
          >
            Edit search
          </button>
          <button
            type="button"
            className="partner-btn partner-btn--secondary"
            onClick={() => navigate(appPath("cart"))}
          >
            Save itinerary
          </button>
        </div>
      </div>

      <div className="pw-partner-row">
        <div>
          <label htmlFor="pw-dmc-partner">Destination management partner</label>
          <select id="pw-dmc-partner" defaultValue="selected">
            <option value="selected">
              {dmcCompanyName || "Travclicks Demo DMC · Credit enabled"}
            </option>
          </select>
        </div>
        <span className="partner-badge">Live inventory</span>
      </div>

      <ServiceTabs />

      <div className="pw-builder">
        <section>
          {filters ? (
            <div className="pw-hotel-layout">
              <aside className="pw-filters">{filters}</aside>
              <div className={`pw-results ${resultsClassName}`.trim()}>
                {children}
              </div>
            </div>
          ) : (
            <div className={`pw-results ${resultsClassName}`.trim()}>
              {children}
            </div>
          )}
        </section>
        {!hideSummary && <TripSummary />}
      </div>
    </>
  );
}
