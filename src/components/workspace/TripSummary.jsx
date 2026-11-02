import { useMemo } from "react";
import { useNavigate } from "react-router-dom";
import { useSelector } from "react-redux";
import moment from "moment";
import { appPath } from "@/utils/appPaths";
import { selectCartItemCount } from "@/slice/cart/carSlice";
import { formatDestinationLabel } from "@/utils/applyTourSearchToRedux";

function nightsBetween(checkIn, checkOut) {
  if (!checkIn || !checkOut) return 0;
  const a = moment(checkIn, ["DD/MM/YYYY", "YYYY-MM-DD"]);
  const b = moment(checkOut, ["DD/MM/YYYY", "YYYY-MM-DD"]);
  if (!a.isValid() || !b.isValid()) return 0;
  return Math.max(0, b.diff(a, "days"));
}

export default function TripSummary() {
  const navigate = useNavigate();
  const tourdetails = useSelector((state) => state.hotels?.tourdetails);
  const searchState = useSelector((state) => state.hotels?.searchState);
  const cartCount = useSelector(selectCartItemCount);

  const destination = formatDestinationLabel(
    tourdetails?.destination || searchState?.location || "Trip"
  );

  const checkIn = tourdetails?.CheckInTime || searchState?.ucheckIn;
  const checkOut = tourdetails?.CheckOutTime || searchState?.ucheckOut;
  const nights = nightsBetween(checkIn, checkOut);
  const adults = tourdetails?.adult || searchState?.guests?.adults || 1;
  const children = tourdetails?.child || searchState?.guests?.children || 0;

  const meta = useMemo(() => {
    const parts = [];
    if (nights) parts.push(`${nights} night${nights === 1 ? "" : "s"}`);
    parts.push(`${adults} adult${adults === 1 ? "" : "s"}`);
    if (children) parts.push(`${children} child${children === 1 ? "" : "ren"}`);
    return parts.join(" · ");
  }, [nights, adults, children]);

  return (
    <aside className="pw-summary">
      <div className="partner-toolbar" style={{ marginBottom: 0 }}>
        <div className="pw-summary__eyebrow">Your trip</div>
        <span className="partner-badge">
          {cartCount || 0} service{(cartCount || 0) === 1 ? "" : "s"}
        </span>
      </div>
      <h2>{destination} itinerary</h2>
      <p>{meta || "Add dates and travellers to begin."}</p>

      {!cartCount ? (
        <div className="pw-summary__empty">
          Add a hotel or service to begin.
        </div>
      ) : (
        <div className="pw-summary-item">
          <span>Items in cart</span>
          <strong>{cartCount}</strong>
        </div>
      )}

      <div className="pw-summary-item">
        <span>Net services</span>
        <strong>—</strong>
      </div>

      <button
        type="button"
        className="partner-btn partner-btn--primary"
        disabled={!cartCount}
        onClick={() => navigate(appPath("cart"))}
      >
        Continue to review →
      </button>
      <button
        type="button"
        className="partner-btn partner-btn--secondary"
        onClick={() => navigate(appPath("cart"))}
      >
        Save as draft
      </button>
    </aside>
  );
}
