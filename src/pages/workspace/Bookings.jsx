import { useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import PartnerTourBookings from "@/components/workspace/PartnerTourBookings";
import { appPath, QUOTES_ENQUIRY_PATH } from "@/utils/appPaths";

export default function Bookings() {
  const navigate = useNavigate();
  const location = useLocation();
  const [seedTour] = useState(() => location.state?.openTour || null);
  const [detailOpen, setDetailOpen] = useState(Boolean(seedTour));

  return (
    <div>
      {detailOpen ? null : (
        <div className="partner-hero">
          <div>
            <div className="partner-kicker">Booking management</div>
            <h1 className="partner-title">Every journey, under control.</h1>
            <p className="partner-lede">
              Manage services, traveller details, payments and trip documents.
            </p>
          </div>
          <button
            type="button"
            className="partner-btn partner-btn--primary"
            onClick={() => navigate(appPath())}
          >
            + New booking
          </button>
        </div>
      )}

      <section
        className={
          detailOpen ? undefined : "partner-card partner-tour-bookings-card"
        }
      >
        <PartnerTourBookings
          initialTour={seedTour}
          onDetailChange={setDetailOpen}
        />
      </section>

      {detailOpen ? null : (
        <section className="partner-quote-cta partner-quote-cta--block">
          <h2 className="partner-quote-cta__title">
            Prefer a tailor-made proposal?
          </h2>
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
      )}
    </div>
  );
}
