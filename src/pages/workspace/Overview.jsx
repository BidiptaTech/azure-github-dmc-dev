import { useEffect, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import Hero1 from "@/components/hero/hero-1";
import SalesManagerDashboard from "@/components/dashboard/dashboard/db-dashboard/components/SalesManagerDashboard";
import { fetchEnquiries } from "@/slice/enquiries/enquiryListSlice";
import { fetchLists } from "@/slice/common/TourlistSlice";
import {
  appPath,
  QUOTES_ENQUIRY_PATH,
  PACKAGES_SEARCH_PATH,
} from "@/utils/appPaths";

const MANAGER_ROLES = [
  "Sales Head(DMC)",
  "Sales Manager (DMC)",
  "Assistant Manager (DMC)",
  "Operational Head(DMC)",
  "DMC Operational Manager",
  "DMC Assistant Operational Manager",
];

function greetingForNow() {
  const h = new Date().getHours();
  if (h < 12) return "Good morning";
  if (h < 17) return "Good afternoon";
  return "Good evening";
}

export default function Overview() {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const username = useSelector((state) => state.auth.Username);
  const userRole = useSelector((state) => state.auth.userRole);
  const { upcomingTours = [], pendingTours = [] } = useSelector(
    (state) => state.lists
  );
  const enquiries = useSelector(
    (state) => state.bookingEnquiry?.enquiries || []
  );

  const isManager = MANAGER_ROLES.includes(userRole);

  useEffect(() => {
    if (isManager) return;
    dispatch(fetchEnquiries({ reset: true, start: 0, limit: 30 }));
    dispatch(fetchLists({ reset: true, type: "upcoming" }));
    dispatch(fetchLists({ reset: true, type: "ongoing" }));
  }, [dispatch, isManager]);

  const firstName = useMemo(() => {
    const raw = (username || "there").trim();
    return raw.split(" ")[0] || "there";
  }, [username]);

  const upcomingCount = Array.isArray(upcomingTours) ? upcomingTours.length : 0;
  const pendingCount = Array.isArray(pendingTours) ? pendingTours.length : 0;
  const quoteCount = Array.isArray(enquiries) ? enquiries.length : 0;

  if (isManager) {
    return <SalesManagerDashboard />;
  }

  return (
    <div>
      <div className="partner-hero-row">
        <div>
          <div className="partner-kicker">Your travel desk</div>
          <h1 className="partner-title">
            {greetingForNow()}, {firstName}.
          </h1>
          <p className="partner-lede">
            A clearer view of your bookings. An easier way to plan the next one.
          </p>
        </div>
        <section className="partner-quote-cta">
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
            Request a Custom Quote ↗
          </button>
        </section>
      </div>

      <div className="partner-plan-hero partner-plan-hero--overview">
        <Hero1 showPageIntro={false} />
      </div>

      <div className="partner-summary-grid">
        <button
          type="button"
          className="partner-stat"
          onClick={() => navigate(appPath("bookings"))}
        >
          <span className="partner-stat__label">Upcoming trips</span>
          <strong className="partner-stat__value">{upcomingCount}</strong>
          <span className="partner-stat__hint">Your next departures</span>
        </button>
        <button
          type="button"
          className="partner-stat"
          onClick={() => navigate(appPath("quotes"))}
        >
          <span className="partner-stat__label">Quotes awaiting you</span>
          <strong className="partner-stat__value">{quoteCount}</strong>
          <span className="partner-stat__hint">Ready for your review</span>
        </button>
        <button
          type="button"
          className="partner-stat"
          onClick={() => navigate(appPath("bookings"))}
        >
          <span className="partner-stat__label">Needs attention</span>
          <strong className="partner-stat__value">{pendingCount}</strong>
          <span className="partner-stat__hint">
            <i className="partner-stat__dot" />
            One payment · One confirmation
          </span>
        </button>
        <button
          type="button"
          className="partner-stat"
          onClick={() => navigate(appPath("packages"))}
        >
          <span className="partner-stat__label">Saved itineraries</span>
          <strong className="partner-stat__value">0</strong>
          <span className="partner-stat__hint">Continue where you left off</span>
        </button>
      </div>

      <section className="partner-packages-banner">
        <div className="partner-packages-banner__copy">
          <h2>Ready-made journeys, made flexible.</h2>
          <p>Explore fixed itineraries or build every service yourself.</p>
        </div>
        <button
          type="button"
          className="partner-packages-banner__btn"
          onClick={() => navigate(PACKAGES_SEARCH_PATH)}
        >
          Explore packages →
        </button>
      </section>
    </div>
  );
}
