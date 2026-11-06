import { useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import PartnerTourBookings from "@/components/workspace/PartnerTourBookings";
import { appPath } from "@/utils/appPaths";

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
          onDetailChange={setDetailOpen}
        />
      </section>
    </div>
  );
}
