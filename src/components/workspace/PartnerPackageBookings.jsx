import { useCallback, useEffect, useMemo, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { CircularProgress } from "@mui/material";
import dayjs from "dayjs";
import {
  fetchPackageBookingLists,
  cancelPackageBooking,
  setBookingListsStatus,
  resetBookingListsPagination,
} from "@/slice/tour-packages/prePackagesSlice";
import BookingViewModal from "@/components/dashboard/dashboard/db-dashboard/PreDefine-Packages/BookingViewModal";

const STATUS_TABS = [
  { id: "ongoing", label: "Ongoing" },
  { id: "upcoming", label: "Upcoming" },
  { id: "past", label: "Past" },
];

function parseMaybeJson(value) {
  if (!value) return {};
  if (typeof value === "object") return value;
  try {
    return JSON.parse(value);
  } catch {
    return {};
  }
}

function statusLabel(status) {
  switch (Number(status)) {
    case 1:
      return "Confirm";
    case 2:
      return "Definite";
    case 3:
      return "Actual";
    case 4:
      return "Cancelled";
    case 5:
      return "Refund pending";
    case 6:
      return "Refunded";
    case 7:
      return "Cancel confirm";
    case 8:
      return "Completed";
    default:
      return "Pending";
  }
}

function statusTone(status) {
  switch (Number(status)) {
    case 1:
    case 8:
      return "green";
    case 2:
    case 3:
      return "blue";
    case 5:
      return "amber";
    case 4:
    case 6:
    case 7:
      return "muted";
    default:
      return "blue";
  }
}

function formatDate(value) {
  if (!value) return null;
  const d = dayjs(value);
  return d.isValid() ? d.format("DD MMM YYYY") : null;
}

function normalizeBooking(booking) {
  const bookingDetails = parseMaybeJson(booking.booking_details);
  const travelDates = parseMaybeJson(booking.travel_dates);
  const userInfo = parseMaybeJson(booking.user_info);
  const packageInfo = parseMaybeJson(booking.package);

  const checkIn =
    travelDates?.check_in ||
    bookingDetails?.travel_dates?.check_in ||
    booking.start_date ||
    booking.check_in ||
    null;
  const checkOut =
    travelDates?.check_out ||
    bookingDetails?.travel_dates?.check_out ||
    booking.end_date ||
    booking.check_out ||
    null;

  const adults =
    Number(bookingDetails?.adult_count || bookingDetails?.adults || 0) ||
    Number(booking.adults || 0);
  const children =
    Number(bookingDetails?.child_count || bookingDetails?.children || 0) ||
    Number(booking.children || 0);
  const male = Number(bookingDetails?.male_count || 0);
  const female = Number(bookingDetails?.female_count || 0);
  const pax =
    Number(booking.total_pax || 0) ||
    (male || female ? male + female : adults + children) ||
    0;

  const destination =
    packageInfo?.destination ||
    booking.destination ||
    booking.location ||
    [booking.city, booking.country].filter(Boolean).join(", ") ||
    "Destination TBC";

  const customerName =
    userInfo?.fullName ||
    booking.customer_name ||
    booking.name ||
    "Guest";

  const amount =
    booking.total_amount ||
    bookingDetails?.total_price ||
    bookingDetails?.price ||
    booking.price ||
    packageInfo?.price ||
    null;

  const paymentStatus =
    booking.payment_status ||
    bookingDetails?.payment_status ||
    "Unpaid";

  return {
    ...booking,
    bookingId: booking.booking_id || booking.id,
    booking_id: booking.booking_id || booking.id,
    customerName,
    destination,
    checkIn,
    checkOut,
    pax,
    amount,
    paymentStatus,
    status: booking.status ?? 0,
    booking_details: bookingDetails,
    travel_dates: travelDates,
    user_info: userInfo,
    package: packageInfo,
  };
}

export default function PartnerPackageBookings() {
  const dispatch = useDispatch();
  const {
    bookingLists,
    bookingListsLoading,
    bookingListsError,
    bookingListsPagination,
    bookingListsStatus,
  } = useSelector((state) => state.prePackages);

  const [tab, setTab] = useState("ongoing");
  const [searchId, setSearchId] = useState("");
  const [destination, setDestination] = useState("");
  const [refreshing, setRefreshing] = useState(false);
  const [viewOpen, setViewOpen] = useState(false);
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [cancellingId, setCancellingId] = useState(null);

  const load = useCallback(
    async (status = tab, start = 0) => {
      if (start === 0) {
        dispatch(resetBookingListsPagination());
        dispatch(setBookingListsStatus(status));
      }
      await dispatch(
        fetchPackageBookingLists({
          start,
          limit: 10,
          status,
        })
      ).unwrap();
    },
    [dispatch, tab]
  );

  useEffect(() => {
    setRefreshing(true);
    load(tab, 0)
      .catch(() => {})
      .finally(() => setRefreshing(false));
  }, [tab, load]);

  const items = useMemo(() => {
    const list = Array.isArray(bookingLists)
      ? bookingLists.map(normalizeBooking)
      : [];
    return list.filter((booking) => {
      if (searchId) {
        const term = searchId.toLowerCase();
        const id = String(booking.bookingId || "").toLowerCase();
        if (!id.includes(term)) return false;
      }
      if (destination) {
        const dest = String(booking.destination || "").toLowerCase();
        if (!dest.includes(destination.toLowerCase())) return false;
      }
      return true;
    });
  }, [bookingLists, searchId, destination]);

  const stats = useMemo(() => {
    const total = bookingListsPagination?.total || items.length;
    return {
      total,
      shown: items.length,
      status: tab,
      hasMore: Boolean(bookingListsPagination?.hasMore),
    };
  }, [bookingListsPagination, items.length, tab]);

  const openView = (booking) => {
    setSelectedBooking(booking);
    setViewOpen(true);
  };

  const handleCancel = async (booking) => {
    const id = booking.booking_id || booking.bookingId;
    if (!id) return;
    if (![1, 2].includes(Number(booking.status))) return;
    setCancellingId(id);
    try {
      await dispatch(cancelPackageBooking(id)).unwrap();
      await load(tab, 0);
    } catch {
      /* slice holds error */
    } finally {
      setCancellingId(null);
    }
  };

  const loadMore = async () => {
    if (!bookingListsPagination?.hasMore || bookingListsLoading) return;
    const nextStart =
      (bookingListsPagination.start || 0) + (bookingListsPagination.limit || 10);
    try {
      await load(bookingListsStatus || tab, nextStart);
    } catch {
      /* ignore */
    }
  };

  if (bookingListsLoading && (!bookingLists || bookingLists.length === 0)) {
    return (
      <div className="partner-quotes-empty">
        <CircularProgress size={22} />
        <span>Loading package bookings…</span>
      </div>
    );
  }

  if (bookingListsError && (!bookingLists || bookingLists.length === 0)) {
    return (
      <div className="partner-quotes-empty partner-quotes-empty--error">
        <span>{bookingListsError || "Could not load package bookings."}</span>
        <button
          type="button"
          className="partner-btn"
          onClick={() => load(tab, 0)}
        >
          Try again
        </button>
      </div>
    );
  }

  return (
    <div className="partner-enquiry partner-package-bookings">
      <div className="partner-summary-grid partner-enquiry__stats">
        <div className="partner-stat" style={{ cursor: "default" }}>
          <span className="partner-stat__label">Showing</span>
          <strong className="partner-stat__value">{stats.shown}</strong>
        </div>
        <div className="partner-stat" style={{ cursor: "default" }}>
          <span className="partner-stat__label">Status tab</span>
          <strong
            className="partner-stat__value"
            style={{ fontSize: 18, textTransform: "capitalize" }}
          >
            {tab}
          </strong>
        </div>
        <div className="partner-stat" style={{ cursor: "default" }}>
          <span className="partner-stat__label">Loaded</span>
          <strong className="partner-stat__value">
            {Array.isArray(bookingLists) ? bookingLists.length : 0}
          </strong>
        </div>
        <div className="partner-stat" style={{ cursor: "default" }}>
          <span className="partner-stat__label">More available</span>
          <strong className="partner-stat__value">
            {stats.hasMore ? "Yes" : "No"}
          </strong>
        </div>
      </div>

      <div className="partner-seg" style={{ marginBottom: 16 }}>
        {STATUS_TABS.map((item) => (
          <button
            key={item.id}
            type="button"
            className={`partner-seg__btn${tab === item.id ? " is-active" : ""}`}
            onClick={() => setTab(item.id)}
          >
            {item.label}
          </button>
        ))}
      </div>

      <div className="partner-toolbar partner-enquiry__toolbar">
        <div className="partner-enquiry__filters">
          <div className="partner-field">
            <label htmlFor="ppb-id">Booking ID</label>
            <input
              id="ppb-id"
              type="search"
              placeholder="Search ID…"
              value={searchId}
              onChange={(e) => setSearchId(e.target.value)}
            />
          </div>
          <div className="partner-field">
            <label htmlFor="ppb-dest">Destination</label>
            <input
              id="ppb-dest"
              type="search"
              placeholder="Filter destination…"
              value={destination}
              onChange={(e) => setDestination(e.target.value)}
            />
          </div>
        </div>
        <button
          type="button"
          className="partner-btn partner-btn--secondary"
          disabled={refreshing || bookingListsLoading}
          onClick={() => {
            setRefreshing(true);
            load(tab, 0)
              .catch(() => {})
              .finally(() => setRefreshing(false));
          }}
        >
          {refreshing ? "Refreshing…" : "Refresh"}
        </button>
      </div>

      {items.length === 0 ? (
        <div className="partner-quotes-empty">
          <span>No {tab} package bookings match your filters.</span>
        </div>
      ) : (
        <div className="partner-list">
          {items.map((booking) => {
            const label = statusLabel(booking.status);
            const tone = statusTone(booking.status);
            const dates =
              formatDate(booking.checkIn) && formatDate(booking.checkOut)
                ? `${formatDate(booking.checkIn)} — ${formatDate(booking.checkOut)}`
                : formatDate(booking.checkIn) || formatDate(booking.checkOut);
            const price = booking.amount
              ? `SGD ${Number(booking.amount).toLocaleString()}`
              : null;
            const canCancel = [1, 2].includes(Number(booking.status));

            return (
              <div
                className="partner-list-item partner-enquiry__item"
                key={booking.bookingId}
              >
                <div className="partner-enquiry__main">
                  <span className={`partner-chip partner-chip--${tone}`}>
                    {label}
                  </span>
                  <h3>
                    {booking.customerName}
                    <span
                      style={{
                        marginLeft: 8,
                        fontWeight: 500,
                        fontSize: 13,
                        color: "var(--pw-muted)",
                      }}
                    >
                      #{booking.bookingId}
                    </span>
                  </h3>
                  <p>
                    {[
                      booking.destination,
                      dates,
                      booking.pax
                        ? `${booking.pax} traveller${booking.pax === 1 ? "" : "s"}`
                        : null,
                      price,
                      booking.paymentStatus
                        ? `Payment: ${booking.paymentStatus}`
                        : null,
                    ]
                      .filter(Boolean)
                      .join(" · ")}
                  </p>
                </div>
                <div className="partner-enquiry__actions">
                  <button
                    type="button"
                    className="partner-btn partner-btn--primary"
                    onClick={() => openView(booking)}
                  >
                    View package →
                  </button>
                  {canCancel ? (
                    <button
                      type="button"
                      className="partner-btn"
                      disabled={cancellingId === booking.bookingId}
                      onClick={() => handleCancel(booking)}
                    >
                      {cancellingId === booking.bookingId
                        ? "Cancelling…"
                        : "Cancel booking"}
                    </button>
                  ) : null}
                </div>
              </div>
            );
          })}
        </div>
      )}

      {stats.hasMore ? (
        <div className="partner-enquiry__pager">
          <span>
            Showing {Array.isArray(bookingLists) ? bookingLists.length : 0}{" "}
            bookings
          </span>
          <button
            type="button"
            className="partner-btn partner-btn--secondary"
            disabled={bookingListsLoading}
            onClick={loadMore}
          >
            {bookingListsLoading ? "Loading…" : "Load more →"}
          </button>
        </div>
      ) : null}

      <BookingViewModal
        open={viewOpen}
        onClose={() => {
          setViewOpen(false);
          setSelectedBooking(null);
        }}
        bookingData={selectedBooking}
      />
    </div>
  );
}
