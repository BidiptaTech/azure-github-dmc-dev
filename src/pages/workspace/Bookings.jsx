import { useEffect, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import PartnerTourBookings from "@/components/workspace/PartnerTourBookings";
import { appPath } from "@/utils/appPaths";

export default function Bookings() {
  const navigate = useNavigate();
  const location = useLocation();
  const [seedTour, setSeedTour] = useState(
    () => location.state?.openTour || null
  );
  const [proposalReview, setProposalReview] = useState(() =>
    Boolean(location.state?.proposalReview)
  );
  const [detailOpen, setDetailOpen] = useState(Boolean(seedTour));

  useEffect(() => {
    if (!location.state?.proposalAccepted) return;
    setSeedTour(null);
    setProposalReview(false);
    setDetailOpen(false);
    navigate(appPath("bookings"), { replace: true, state: {} });
  }, [location.state, navigate]);

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
            onClick={() => navigate(appPath("plan"))}
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
          proposalReview={proposalReview}
          onDetailChange={setDetailOpen}
        />
      </section>
    </div>
  );
}
