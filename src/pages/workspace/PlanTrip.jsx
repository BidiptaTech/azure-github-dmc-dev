import { useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { useDispatch } from "react-redux";
import Hero1 from "@/components/hero/hero-1";
import TourPackages from "@/components/Tour-Packages";
import { setSearchCriteria } from "@/slice/tour-packages/tourPackageSlice";
import { appPath } from "@/utils/appPaths";

export default function PlanTrip() {
  const navigate = useNavigate();
  const location = useLocation();
  const dispatch = useDispatch();
  const startTrip = Boolean(location.state?.startTrip);
  const searchCriteria = location.state?.searchCriteria;

  useEffect(() => {
    if (!startTrip || !searchCriteria) return;

    dispatch(
      setSearchCriteria({
        destination: searchCriteria.destination || searchCriteria.country,
        checkIn: searchCriteria.checkIn || searchCriteria.arrival,
        checkOut: searchCriteria.checkOut || searchCriteria.departure,
        guests: searchCriteria.guests || {
          adults: searchCriteria.travellers || 2,
          children: searchCriteria.children || 0,
          infants: searchCriteria.infants || 0,
        },
      })
    );
  }, [startTrip, searchCriteria, dispatch]);

  if (startTrip) {
    return (
      <div>
        <div className="partner-hero-row" style={{ marginBottom: 8 }}>
          <div>
            <div className="partner-kicker">Plan a trip</div>
            <h1 className="partner-title">
              Build a better {searchCriteria?.destination || "trip"} journey.
            </h1>
            <p className="partner-lede">
              All your services. One itinerary. Clear pricing from the start.
            </p>
          </div>
          <button
            type="button"
            className="partner-btn partner-btn--secondary"
            onClick={() => navigate(appPath("plan"), { replace: true, state: {} })}
          >
            Edit search
          </button>
        </div>
        <TourPackages />
      </div>
    );
  }

  return (
    <div>
      <div className="partner-plan-hero">
        <Hero1 />
      </div>

      <section className="partner-card">
        <h2 className="partner-card__title">Prefer a tailor-made proposal?</h2>
        <p className="partner-card__sub">
          Share your brief and let a DMC create the itinerary.
        </p>
        <button
          type="button"
          className="partner-btn"
          onClick={() => navigate(appPath("booking-enquiry"))}
        >
          Request a custom quote →
        </button>
      </section>
    </div>
  );
}
