import { useEffect } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { useDispatch } from "react-redux";
import Hero1 from "@/components/hero/hero-1";
import TourPackages from "@/components/Tour-Packages";
import { setSearchCriteria } from "@/slice/tour-packages/tourPackageSlice";
import { QUOTES_ENQUIRY_PATH } from "@/utils/appPaths";

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
      <div className="partner-plan-builder partner-plan-hero">
        <TourPackages mode="plan" />
      </div>
    );
  }

  return (
    <div>
      <div className="partner-plan-hero">
        <Hero1 />
      </div>

      <section className="partner-quote-cta partner-quote-cta--block">
        <h2 className="partner-quote-cta__title">Prefer a tailor-made proposal?</h2>
        <p className="partner-quote-cta__sub">
          Share your brief and let your DMC create the itinerary.
        </p>
        <button
          type="button"
          className="partner-btn partner-btn--primary"
          onClick={() => navigate(QUOTES_ENQUIRY_PATH)}
        >
          Request a Custom Quote →
        </button>
      </section>
    </div>
  );
}
