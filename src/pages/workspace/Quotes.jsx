import { useEffect, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { fetchEnquiries } from "@/slice/enquiries/enquiryListSlice";

const DEMO_QUOTES = [
  {
    id: "demo-bali",
    title: "Bali family getaway",
    detail: "Your DMC has shared a proposal.",
    status: "Proposal ready",
  },
  {
    id: "demo-sg",
    title: "Singapore school visit",
    detail: "Awaiting DMC response.",
    status: "In progress",
  },
  {
    id: "demo-my",
    title: "Malaysia discovery",
    detail: "Draft brief saved.",
    status: "Draft",
  },
];

export default function Quotes() {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const enquiries = useSelector((state) => state.bookingEnquiry?.enquiries || []);

  useEffect(() => {
    dispatch(fetchEnquiries());
  }, [dispatch]);

  const items = useMemo(() => {
    const live = (Array.isArray(enquiries) ? enquiries : []).map((e, idx) => ({
      id: e.id || e.enquiry_id || `live-${idx}`,
      title: e.customer_name || e.title || e.destination || `Enquiry #${idx + 1}`,
      detail: e.status || e.destination || "Awaiting review",
      status: e.status || "Open",
    }));
    return live.length ? live : DEMO_QUOTES;
  }, [enquiries]);

  return (
    <div>
      <div className="partner-hero">
        <div>
          <div className="partner-kicker">Quotes &amp; requests</div>
          <h1 className="partner-title">From first brief to confirmed trip.</h1>
          <p className="partner-lede">
            Compare proposals, request changes and continue with payment.
          </p>
        </div>
        <button
          type="button"
          className="partner-btn partner-btn--primary"
          onClick={() => navigate("/app/booking-enquiry")}
        >
          + New enquiry
        </button>
      </div>

      <section className="partner-card">
        <div className="partner-list">
          {items.map((item) => (
            <div className="partner-list-item" key={item.id}>
              <div>
                <span className="partner-chip">{item.status}</span>
                <h3>{item.title}</h3>
                <p>{item.detail}</p>
              </div>
              <button
                type="button"
                className="partner-btn"
                onClick={() => navigate("/app/booking-enquiry")}
              >
                Review proposal →
              </button>
            </div>
          ))}
        </div>
      </section>
    </div>
  );
}
