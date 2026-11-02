import { useEffect, useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import TripSearchCard from "@/components/workspace/TripSearchCard";
import SalesManagerDashboard from "@/components/dashboard/dashboard/db-dashboard/components/SalesManagerDashboard";
import { fetchEnquiries } from "@/slice/enquiries/enquiryListSlice";
import { fetchLists } from "@/slice/common/TourlistSlice";
import { appPath } from "@/utils/appPaths";

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

function tourTitle(tour, idx) {
  return (
    tour.customer_name ||
    tour.tour_name ||
    tour.destination ||
    `Trip #${tour.id || idx + 1}`
  );
}

function tourMeta(tour) {
  return (
    [
      tour.destination || tour.country,
      tour.check_in || tour.checkIn,
      tour.pax || tour.travellers
        ? `${tour.pax || tour.travellers} travellers`
        : null,
    ]
      .filter(Boolean)
      .join(" · ") || "Open in My bookings for details"
  );
}

function statusBadge(tour) {
  const raw = (tour.status || tour.booking_status || "").toLowerCase();
  if (raw.includes("await") || raw.includes("pending") || raw.includes("confirm")) {
    return { label: "Awaiting confirmation", amber: true };
  }
  if (raw.includes("complete") || raw.includes("past")) {
    return { label: "Completed", amber: false };
  }
  return { label: "Confirmed", amber: false };
}

export default function Overview() {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const username = useSelector((state) => state.auth.Username);
  const userRole = useSelector((state) => state.auth.userRole);
  const {
    upcomingTours = [],
    pendingTours = [],
    lists = [],
  } = useSelector((state) => state.lists);
  const enquiries = useSelector(
    (state) => state.bookingEnquiry?.enquiries || []
  );
  const [bookingFilter, setBookingFilter] = useState("Upcoming");

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

  const filteredBookings = useMemo(() => {
    if (bookingFilter === "In progress") {
      return (Array.isArray(pendingTours) ? pendingTours : []).slice(0, 5);
    }
    if (bookingFilter === "Completed") {
      return (Array.isArray(lists) ? lists : []).slice(0, 5);
    }
    return (Array.isArray(upcomingTours) ? upcomingTours : []).slice(0, 5);
  }, [bookingFilter, upcomingTours, pendingTours, lists]);

  const nextSteps = useMemo(() => {
    const fromEnquiries = (Array.isArray(enquiries) ? enquiries : [])
      .slice(0, 2)
      .map((e, idx) => ({
        id: e.id || e.enquiry_id || `enq-${idx}`,
        badge: e.status || "Quote",
        amber: true,
        title: e.customer_name || e.title || e.destination || "Enquiry",
        detail: e.destination || e.status || "Your DMC has shared a proposal.",
        action: "Review quote →",
        to: appPath("quotes"),
      }));

    const paymentDue =
      pendingCount > 0
        ? [
            {
              id: "payment-due",
              badge: "Payment due",
              amber: true,
              title: tourTitle(pendingTours[0] || {}, 0),
              detail: "A booking still needs payment or confirmation.",
              action: "Review booking →",
              to: appPath("bookings"),
            },
          ]
        : [];

    const steps = [...paymentDue, ...fromEnquiries].slice(0, 3);
    if (steps.length) return steps;

    return [
      {
        id: "need-hand",
        badge: "New request",
        amber: false,
        title: "Need a hand?",
        detail: "Send your requirements to a DMC.",
        action: "Request a quote →",
        to: appPath("booking-enquiry"),
      },
    ];
  }, [enquiries, pendingCount, pendingTours]);

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
        <button
          type="button"
          className="partner-btn partner-btn--secondary"
          onClick={() => navigate(appPath("booking-enquiry"))}
        >
          Request a custom quote ↗
        </button>
      </div>

      <TripSearchCard />

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
          onClick={() => navigate(appPath("plan"))}
        >
          <span className="partner-stat__label">Saved itineraries</span>
          <strong className="partner-stat__value">0</strong>
          <span className="partner-stat__hint">Continue where you left off</span>
        </button>
      </div>

      <div className="partner-columns">
        <section className="partner-panel">
          <div className="partner-toolbar">
            <h2>Your bookings</h2>
            <button
              type="button"
              className="partner-link"
              onClick={() => navigate(appPath("bookings"))}
            >
              View all →
            </button>
          </div>

          <div className="partner-tabs">
            {["Upcoming", "In progress", "Completed"].map((tab) => (
              <button
                key={tab}
                type="button"
                className={`partner-tabs__btn${
                  bookingFilter === tab ? " is-active" : ""
                }`}
                onClick={() => setBookingFilter(tab)}
              >
                {tab}
              </button>
            ))}
          </div>

          {filteredBookings.length === 0 ? (
            <div style={{ padding: "28px 0", color: "var(--pw-muted)" }}>
              No {bookingFilter.toLowerCase()} bookings yet.{" "}
              <button
                type="button"
                className="partner-link"
                onClick={() => navigate(appPath("plan"))}
              >
                Plan a trip →
              </button>
            </div>
          ) : (
            filteredBookings.map((tour, idx) => {
              const badge = statusBadge(tour);
              return (
                <div className="partner-booking" key={tour.id || idx}>
                  <div>
                    <h3>{tourTitle(tour, idx)}</h3>
                    <div className="partner-sub">{tourMeta(tour)}</div>
                    {tour.id ? (
                      <div className="partner-sub">TC-{tour.id}</div>
                    ) : null}
                  </div>
                  <div>
                    <span
                      className={`partner-badge${
                        badge.amber ? " partner-badge--amber" : ""
                      }`}
                    >
                      {badge.label}
                    </span>
                  </div>
                  <div>
                    <strong>
                      {tour.total_amount ||
                        tour.grand_total ||
                        tour.amount ||
                        "—"}
                    </strong>
                    {(tour.balance || tour.unallocated) && (
                      <div className="partner-sub">
                        {tour.balance || tour.unallocated} unallocated
                      </div>
                    )}
                  </div>
                  <button
                    type="button"
                    className="partner-link"
                    onClick={() => navigate(appPath("bookings"))}
                  >
                    View booking →
                  </button>
                </div>
              );
            })
          )}
        </section>

        <aside className="partner-panel">
          <h2>Next steps</h2>
          {nextSteps.map((step) => (
            <div className="partner-task" key={step.id}>
              <span
                className={`partner-badge${
                  step.amber ? " partner-badge--amber" : ""
                }`}
              >
                {step.badge}
              </span>
              <h3>{step.title}</h3>
              <p>{step.detail}</p>
              <button
                type="button"
                className="partner-link"
                onClick={() => navigate(step.to)}
              >
                {step.action}
              </button>
            </div>
          ))}
        </aside>
      </div>

      <section className="partner-panel partner-packages-cta">
        <h2>Ready-made journeys, made flexible.</h2>
        <p className="partner-card__sub">
          Explore fixed itineraries or build every service yourself.
        </p>
        <button
          type="button"
          className="partner-btn partner-btn--primary"
          onClick={() => navigate(appPath("packages"))}
        >
          Explore packages →
        </button>
      </section>
    </div>
  );
}
