import { useMemo } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import moment from "moment";
import { appPath } from "@/utils/appPaths";
import {
  removeFromCart,
  selectCart,
  selectCartItemCount,
} from "@/slice/cart/carSlice";
import { formatDestinationLabel } from "@/utils/applyTourSearchToRedux";

const SERVICE_TABS = [
  {
    key: "hotels",
    match: /view-hotel-search|hotel-details|hotel-checkout/i,
    types: ["hotel"],
    emptyLabel: "hotel",
  },
  {
    key: "ports",
    match: /pickupdrop/i,
    types: ["entryport", "exitport"],
    emptyLabel: "port",
  },
  {
    key: "attractions",
    match: /attractions|tour-single|attraction-/i,
    types: ["attraction", "attraction_package"],
    emptyLabel: "attraction",
  },
  {
    key: "guides",
    match: /tourguide|activity-single/i,
    types: ["guide"],
    emptyLabel: "guide",
  },
  {
    key: "restaurants",
    match: /restaurants/i,
    types: ["restaurant"],
    emptyLabel: "restaurant",
  },
  {
    key: "transfers",
    match: /localtransfer/i,
    types: [
      "travelpoint",
      "travel_point",
      "travelhourly",
      "travel_hourly",
      "travelpointzone",
      "travel_zone",
      "local_transport",
      "zone",
    ],
    emptyLabel: "transfer",
  },
];

function nightsBetween(checkIn, checkOut) {
  if (!checkIn || !checkOut) return 0;
  const a = moment(checkIn, ["DD/MM/YYYY", "YYYY-MM-DD"]);
  const b = moment(checkOut, ["DD/MM/YYYY", "YYYY-MM-DD"]);
  if (!a.isValid() || !b.isValid()) return 0;
  return Math.max(0, b.diff(a, "days"));
}

function getActiveServiceTab(pathname) {
  return (
    SERVICE_TABS.find((tab) => tab.match.test(pathname)) || SERVICE_TABS[0]
  );
}

function getBookingName(booking) {
  const type = String(booking?.type || "").toLowerCase();
  if (type === "hotel") return booking.hotel_name || "Hotel";
  if (type === "attraction" || type === "attraction_package") {
    return booking.AttractionName || booking.attraction_name || "Attraction";
  }
  if (type === "restaurant") {
    return booking.restaurantName || booking.restaurant_name || "Restaurant";
  }
  if (type === "guide") return booking.guide_name || "Guide";
  if (type === "entryport" || type === "exitport") {
    return booking.vehicles_name || booking.port_name || "Port transfer";
  }
  return (
    booking.vehicles_name ||
    booking.hotel_name ||
    booking.AttractionName ||
    booking.restaurantName ||
    booking.guide_name ||
    "Service"
  );
}

function getBookingPrice(booking) {
  const raw =
    booking?.totalPrice ??
    booking?.total_price ??
    booking?.price ??
    booking?.amount ??
    0;
  return Math.max(0, Number(raw) || 0);
}

function getBookingCurrency(booking, fallback = "SGD") {
  return (
    booking?.currency ||
    booking?.bookingArray?.[0]?.currency ||
    booking?.bookingArray?.[0]?.booking?.currency ||
    fallback
  );
}

function formatMoney(amount, currency = "SGD") {
  const value = Number(amount) || 0;
  return `${currency} ${value.toFixed(2)}`;
}

function getBookingMeta(booking) {
  const type = String(booking?.type || "").toLowerCase();
  const parts = [];

  if (type === "hotel") {
    const nights =
      Number(booking.nights) ||
      nightsBetween(booking.check_in, booking.check_out);
    const rooms = Number(booking.roomCount) || 1;
    if (nights) parts.push(`${nights} night${nights === 1 ? "" : "s"}`);
    parts.push(`${rooms} room(s)`);
    return parts.join(" · ");
  }

  const adults = Number(
    booking.adults ?? booking.adultCount ?? booking.adult ?? 0
  );
  const children = Number(
    booking.children ?? booking.childCount ?? booking.child ?? 0
  );
  if (adults) parts.push(`${adults} adult${adults === 1 ? "" : "s"}`);
  if (children) {
    parts.push(`${children} child${children === 1 ? "" : "ren"}`);
  }
  if (booking.bookingDate) parts.push(String(booking.bookingDate));
  return parts.join(" · ");
}

export default function TripSummary() {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { pathname } = useLocation();
  const tourdetails = useSelector((state) => state.hotels?.tourdetails);
  const searchState = useSelector((state) => state.hotels?.searchState);
  const cart = useSelector(selectCart);
  const cartCount = useSelector(selectCartItemCount);
  const activeTab = useMemo(() => getActiveServiceTab(pathname), [pathname]);

  const destination = formatDestinationLabel(
    tourdetails?.destination || searchState?.location || "Trip"
  );

  const checkIn = tourdetails?.CheckInTime || searchState?.ucheckIn;
  const checkOut = tourdetails?.CheckOutTime || searchState?.ucheckOut;
  const nights = nightsBetween(checkIn, checkOut);
  const adults = tourdetails?.adult || searchState?.guests?.adults || 1;
  const children = tourdetails?.child || searchState?.guests?.children || 0;
  const rooms = Number(searchState?.rooms) || 1;

  const meta = useMemo(() => {
    const parts = [];
    if (nights) parts.push(`${nights} night${nights === 1 ? "" : "s"}`);
    parts.push(`${adults} adult${adults === 1 ? "" : "s"}`);
    if (children) parts.push(`${children} child${children === 1 ? "" : "ren"}`);
    parts.push(`${rooms} room(s)`);
    return parts.join(" · ");
  }, [nights, adults, children, rooms]);

  const activeBookings = useMemo(() => {
    const typeSet = new Set(activeTab.types.map((t) => t.toLowerCase()));
    const rows = [];
    (cart || []).forEach((trip) => {
      (trip.bookings || []).forEach((booking) => {
        const type = String(booking?.type || "").toLowerCase();
        if (!typeSet.has(type)) return;
        rows.push({
          ...booking,
          tripId: trip.tripId,
        });
      });
    });
    return rows;
  }, [cart, activeTab]);

  const activeCount = activeBookings.length;
  const serviceLabel = `${activeTab.emptyLabel}${activeCount === 1 ? "" : "s"}`;

  const currency = useMemo(() => {
    const fromBooking = activeBookings.find((b) => getBookingCurrency(b, null));
    return fromBooking ? getBookingCurrency(fromBooking) : "SGD";
  }, [activeBookings]);

  const totalAmount = useMemo(
    () =>
      activeBookings.reduce(
        (sum, booking) => sum + getBookingPrice(booking),
        0
      ),
    [activeBookings]
  );

  const handleRemove = (booking) => {
    if (!booking?.tripId || !booking?.cartItemId) return;
    dispatch(
      removeFromCart({
        tripId: booking.tripId,
        cartItemId: booking.cartItemId,
      })
    );
  };

  return (
    <aside className="pw-summary">
      <div className="partner-toolbar" style={{ marginBottom: 0 }}>
        <div className="pw-summary__eyebrow">Your trip</div>
        <div className="pw-summary__badges">
          <span className="partner-badge">
            {activeCount} {serviceLabel}
          </span>
          <span className="partner-badge">
            {cartCount || 0} total
          </span>
        </div>
      </div>
      <h2>{destination} itinerary</h2>
      <p>{meta || "Add dates and travellers to begin."}</p>

      {!activeBookings.length ? (
        <div className="pw-summary__empty">
          {cartCount
            ? `No ${activeTab.emptyLabel}s in this trip yet.`
            : "Add a hotel or service to begin."}
        </div>
      ) : (
        <div className="pw-summary-services">
          {activeBookings.map((booking) => {
            const itemMeta = getBookingMeta(booking);
            return (
              <div key={booking.cartItemId} className="pw-summary-service">
                <div className="pw-summary-service__row">
                  <strong className="pw-summary-service__name">
                    {getBookingName(booking)}
                  </strong>
                  <strong className="pw-summary-service__price">
                    {formatMoney(
                      getBookingPrice(booking),
                      getBookingCurrency(booking, currency)
                    )}
                  </strong>
                </div>
                {itemMeta ? (
                  <div className="pw-summary-service__meta">{itemMeta}</div>
                ) : null}
                <button
                  type="button"
                  className="pw-summary-service__remove"
                  onClick={() => handleRemove(booking)}
                >
                  Remove
                </button>
              </div>
            );
          })}
        </div>
      )}

      <div className="pw-summary-total">
        <div className="pw-summary-total__row">
          <span>Total</span>
          <strong>
            {activeBookings.length ? formatMoney(totalAmount, currency) : "—"}
          </strong>
        </div>
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
