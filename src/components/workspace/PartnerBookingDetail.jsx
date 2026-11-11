import CategoriesAccordion from "@/components/dashboard/dashboard/db-dashboard/components/TourDetails";
import PartnerNegotiation from "@/components/workspace/PartnerNegotiation";
import {
  canAddMoreServices,
  canCancelTour,
  canUpdateTourPlan,
} from "@/utils/tourBookingActions";

function money(value) {
  const n = Number(value);
  if (!Number.isFinite(n)) return "—";
  return `SGD ${n.toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
}

export default function PartnerBookingDetail({
  tour,
  badge,
  reference,
  title,
  meta,
  totalLabel,
  paidLabel,
  outstanding,
  outstandingLabel,
  markupAmount,
  setMarkupAmount,
  discountAmount,
  setDiscountAmount,
  userRole,
  onBack,
  onPrint,
  onAddServices,
  onUpdatePlan,
  onCancel,
  actionBusy,
  loading,
}) {
  const dueDate =
    tour?.balance_due_date || tour?.due_date || tour?.payment_due_date || null;
  const dmcName = tour?.dmc_company_name || tour?.dmc_name || null;
  const paymentStatus = tour?.payment_status || null;
  const showAdd = canAddMoreServices(tour);
  const showUpdate = canUpdateTourPlan(tour);
  const showCancel = canCancelTour(tour);
  const hasChanges = showAdd || showUpdate || showCancel;

  return (
    <div className="partner-booking-detail">
      <button type="button" className="partner-back" onClick={onBack}>
        ← All bookings
      </button>

      <div className="partner-booking-detail__head">
        <div>
          <div className="partner-kicker">{reference}</div>
          <h1 className="partner-title">{title}</h1>
          <p className="partner-lede">{meta}</p>
        </div>
        <div className="partner-booking-detail__badges">
          <span
            className={`partner-badge${
              badge?.tone === "amber"
                ? " partner-badge--amber"
                : badge?.tone === "muted"
                  ? " partner-badge--muted"
                  : ""
            }`}
          >
            {badge?.label || "Confirmed"}
          </span>
          {outstanding > 0 ? (
            <span className="partner-badge partner-badge--amber">
              Payment outstanding
            </span>
          ) : null}
        </div>
      </div>

      <div className="partner-builder">
        <section>
          <div className="partner-panel" id="partner-itinerary-services">
            <div className="partner-kicker">What is booked</div>
            <h2>View details</h2>
            <p className="partner-section-help">
              {loading
                ? "Loading hotels, transfers, attractions, guides and restaurants…"
                : "Open a service to see travellers, dates and rooming. Use the actions on each row to view or cancel that service."}
            </p>
            <div className="partner-summary-item">
              <div>
                <h3>All services</h3>
                <p>Everything already on this booking.</p>
              </div>
              <strong>{totalLabel || "—"}</strong>
            </div>
            <CategoriesAccordion embedded />
          </div>

          {hasChanges ? (
            <div className="partner-panel partner-booking-detail__history">
              <div className="partner-kicker">Change the trip</div>
              <h2>Booking actions</h2>
              <p className="partner-section-help">
                These change the itinerary. They do not change the client quotation or the negotiation.
              </p>
              <div className="partner-service-tabs">
                {showUpdate ? (
                  <button
                    type="button"
                    className="partner-btn"
                    disabled={actionBusy}
                    onClick={onUpdatePlan}
                  >
                    Update tour plan
                  </button>
                ) : null}
                {showAdd ? (
                  <button
                    type="button"
                    className="partner-btn"
                    disabled={actionBusy}
                    onClick={onAddServices}
                  >
                    Add more services
                  </button>
                ) : null}
                {showCancel ? (
                  <button
                    type="button"
                    className="partner-btn"
                    disabled={actionBusy}
                    onClick={onCancel}
                  >
                    Cancel booking
                  </button>
                ) : null}
              </div>
            </div>
          ) : null}
        </section>

        <aside className="partner-booking-detail__stack">
          <div className="partner-panel">
            <div className="partner-kicker">Client copy</div>
            <h2>Quotation</h2>
            <p className="partner-section-help">
              Add a markup or discount, then print or download. These amounts are for the client copy only.
            </p>
            <div className="partner-booking-detail__adjust">
              <label htmlFor="partner-markup">
                Markup amount (SGD)
                <input
                  id="partner-markup"
                  type="number"
                  min="0"
                  value={markupAmount}
                  onChange={(event) => {
                    const value = Number(event.target.value);
                    setMarkupAmount(value >= 0 ? value : 0);
                  }}
                />
              </label>
              <label htmlFor="partner-discount">
                Discount amount (SGD)
                <input
                  id="partner-discount"
                  type="number"
                  min="0"
                  value={discountAmount}
                  onChange={(event) => {
                    const value = Number(event.target.value);
                    setDiscountAmount(value >= 0 ? value : 0);
                  }}
                />
              </label>
            </div>
            <button
              type="button"
              className="partner-btn partner-btn--primary partner-btn--block"
              onClick={onPrint}
            >
              Print / Download PDF
            </button>
          </div>

          <PartnerNegotiation tour={tour} userRole={userRole} />

          <div className="partner-panel partner-summary">
            <div className="partner-kicker">Money</div>
            <h2>Settlement summary</h2>
            <div className="partner-summary-item">
              <span>Booking total</span>
              <strong>{totalLabel || "—"}</strong>
            </div>
            <div className="partner-summary-item">
              <span>Cash / prepaid paid</span>
              <strong>{paidLabel || money(0)}</strong>
            </div>
            <div className="partner-summary-item">
              <span>Unallocated balance</span>
              <strong>{outstandingLabel || money(0)}</strong>
            </div>
            <p className="partner-booking-detail__note">
              {paymentStatus ? (
                <>
                  Payment status: {paymentStatus}
                  <br />
                </>
              ) : null}
              {dueDate ? (
                <>
                  Balance due: {dueDate}
                  <br />
                </>
              ) : null}
              {dmcName ? <>DMC: {dmcName}</> : null}
            </p>
          </div>
        </aside>
      </div>
    </div>
  );
}
