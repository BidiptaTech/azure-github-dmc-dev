import { useCallback, useEffect, useMemo, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { useNavigate } from "react-router-dom";
import {
  CircularProgress,
  Divider,
  IconButton,
  Menu,
  MenuItem,
} from "@mui/material";
import VisibilityOutlinedIcon from "@mui/icons-material/VisibilityOutlined";
import EditOutlinedIcon from "@mui/icons-material/EditOutlined";
import UpdateOutlinedIcon from "@mui/icons-material/UpdateOutlined";
import CancelOutlinedIcon from "@mui/icons-material/CancelOutlined";
import MoreVertIcon from "@mui/icons-material/MoreVert";
import KeyboardArrowDownIcon from "@mui/icons-material/KeyboardArrowDown";
import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
import { fetchLists, setTourType } from "@/slice/common/TourlistSlice";
import { fetchViewDetails } from "@/slice/common/ViewDetails";
import TourDetailsModal from "@/components/dashboard/dashboard/db-dashboard/components/TourDetailsModal";
import CustomPaymentTooltip from "@/components/dashboard/dashboard/db-dashboard/components/CustomTooltip";
import {
  canAddMoreServices,
  canCancelTour,
  canUpdateTourPlan,
  cancelTourBooking,
  startAddMoreServices,
  startUpdateTourPlan,
} from "@/utils/tourBookingActions";

dayjs.extend(customParseFormat);

const STATUS_TABS = [
  { id: "ongoing", label: "Ongoing", listKey: "upcomingTours" },
  { id: "upcoming", label: "Upcoming", listKey: "pendingTours" },
  { id: "past", label: "Past", listKey: "lists" },
];

const STATUS_FILTERS = [
  { id: "all", label: "All bookings" },
  { id: "due", label: "Payment outstanding" },
  { id: "confirmed", label: "Confirmed" },
  { id: "pending", label: "Pending DMC confirmation" },
];

function parseDate(value) {
  if (!value) return null;
  const formats = ["DD/MM/YYYY", "DD-MM-YYYY", "YYYY-MM-DD", "YYYY/MM/DD"];
  for (const format of formats) {
    const d = dayjs(value, format, true);
    if (d.isValid()) return d;
  }
  const fallback = dayjs(value);
  return fallback.isValid() ? fallback : null;
}

function formatDate(value) {
  const d = parseDate(value);
  return d ? d.format("YYYY-MM-DD") : null;
}

function formatMoney(value) {
  const n = Number(value);
  if (!Number.isFinite(n)) return null;
  return `SGD ${n.toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
}

function statusMeta(tour) {
  const raw = (tour.tour_status || tour.status || "").toString();
  const lower = raw.toLowerCase();
  if (
    lower.includes("await") ||
    lower.includes("pending") ||
    lower.includes("tentative") ||
    lower.includes("prospect") ||
    lower.includes("new enquiry")
  ) {
    return { label: raw || "Awaiting confirmation", tone: "amber" };
  }
  if (
    lower.includes("cancel") ||
    lower.includes("refund") ||
    lower.includes("closed")
  ) {
    return { label: raw || "Cancelled", tone: "muted" };
  }
  if (lower.includes("complete") || lower.includes("past")) {
    return { label: raw || "Completed", tone: "muted" };
  }
  if (
    lower.includes("confirm") ||
    lower.includes("definite") ||
    lower.includes("actual")
  ) {
    return { label: raw || "Confirmed", tone: "green" };
  }
  return { label: raw || "Confirmed", tone: "green" };
}

function totalAmount(tour) {
  const value =
    tour.finalAmountWithTax ??
    tour.tour_total_price ??
    tour.finalAmount ??
    tour.grand_total ??
    tour.total_amount ??
    null;
  return Number(value);
}

function outstandingAmount(tour) {
  const total = totalAmount(tour);
  if (!Number.isFinite(total) || total <= 0) return 0;
  const paid = Number(tour.paidAmount ?? tour.paid_amount ?? 0);
  const due = total - (Number.isFinite(paid) ? paid : 0);
  if (Number.isFinite(Number(tour.balance))) return Number(tour.balance);
  if (Number.isFinite(Number(tour.unallocated))) return Number(tour.unallocated);
  return Math.max(due, 0);
}

function matchesStatusFilter(tour, filterId) {
  if (filterId === "all") return true;
  const status = (tour.tour_status || "").toLowerCase();
  const payment = (tour.payment_status || "").toLowerCase();
  if (filterId === "due") {
    return (
      outstandingAmount(tour) > 0 ||
      payment.includes("not paid") ||
      payment.includes("partial")
    );
  }
  if (filterId === "confirmed") {
    return (
      status.includes("confirm") ||
      status.includes("definite") ||
      status.includes("actual")
    );
  }
  if (filterId === "pending") {
    return (
      status.includes("pending") ||
      status.includes("await") ||
      status.includes("tentative") ||
      status.includes("prospect") ||
      status.includes("new enquiry")
    );
  }
  return true;
}

function matchesSearch(tour, term) {
  if (!term) return true;
  const q = term.toLowerCase();
  const haystack = [
    tour.display_id,
    tour.id,
    tour.customer_name,
    tour.destination,
    tour.country,
    tour.dmc_company_name,
  ]
    .filter(Boolean)
    .join(" ")
    .toLowerCase();
  return haystack.includes(q);
}

export default function PartnerTourBookings({
  embedded = false,
  limit = null,
  showFilters = true,
} = {}) {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const {
    upcomingTours = [],
    pendingTours = [],
    lists = [],
    status,
    error,
  } = useSelector((state) => state.lists);
  const userCountry = useSelector((state) => state.auth.user_country);
  const [actionBusy, setActionBusy] = useState(false);

  const [tab, setTab] = useState("upcoming");
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");
  const [refreshing, setRefreshing] = useState(false);
  const [isModalVisible, setIsModalVisible] = useState(false);
  const [markupAmount, setMarkupAmount] = useState(0);
  const [discountAmount, setDiscountAmount] = useState(0);
  const [bookingType1, setBookingType1] = useState(null);
  const [menuAnchor, setMenuAnchor] = useState(null);
  const [menuTour, setMenuTour] = useState(null);

  const load = useCallback(
    async (type = tab) => {
      dispatch(setTourType(type));
      await dispatch(fetchLists({ reset: true, type })).unwrap();
    },
    [dispatch, tab]
  );

  useEffect(() => {
    setRefreshing(true);
    load(tab)
      .catch(() => {})
      .finally(() => setRefreshing(false));
  }, [tab, load]);

  const sourceList = useMemo(() => {
    const meta = STATUS_TABS.find((item) => item.id === tab);
    if (!meta) return [];
    if (meta.listKey === "upcomingTours") return upcomingTours;
    if (meta.listKey === "pendingTours") return pendingTours;
    return lists;
  }, [tab, upcomingTours, pendingTours, lists]);

  const items = useMemo(() => {
    const filtered = (Array.isArray(sourceList) ? sourceList : []).filter(
      (tour) =>
        matchesSearch(tour, search.trim()) &&
        matchesStatusFilter(tour, statusFilter)
    );
    if (limit != null && Number.isFinite(Number(limit))) {
      return filtered.slice(0, Number(limit));
    }
    return filtered;
  }, [sourceList, search, statusFilter, limit]);

  const closeActionMenu = () => {
    setMenuAnchor(null);
    setMenuTour(null);
  };

  const openActionMenu = (event, tour) => {
    setMenuAnchor(event.currentTarget);
    setMenuTour(tour);
  };

  const handleViewDetails = async (tour) => {
    closeActionMenu();
    setBookingType1(tour.booking_type || null);
    setMarkupAmount(0);
    setDiscountAmount(0);
    setIsModalVisible(true);
    try {
      await dispatch(fetchViewDetails({ tour_id: tour.id })).unwrap();
    } catch {
      /* ViewDetails slice holds error; modal still shows loading/empty */
    }
  };

  const handleAddMoreServices = async (tour) => {
    closeActionMenu();
    if (!canAddMoreServices(tour) || actionBusy) return;
    setActionBusy(true);
    try {
      await startAddMoreServices({
        tour,
        dispatch,
        navigate,
        userCountry,
      });
    } catch (err) {
      console.error("Add more services failed:", err);
    } finally {
      setActionBusy(false);
    }
  };

  const handleUpdateTourPlan = async (tour) => {
    closeActionMenu();
    if (!canUpdateTourPlan(tour) || actionBusy) return;
    setActionBusy(true);
    try {
      await startUpdateTourPlan({ tour, dispatch, navigate });
    } catch (err) {
      console.error("Update tour plan failed:", err);
    } finally {
      setActionBusy(false);
    }
  };

  const handleCancelTour = async (tour) => {
    closeActionMenu();
    if (!canCancelTour(tour) || actionBusy) return;
    setActionBusy(true);
    try {
      await cancelTourBooking({ tour, dispatch, listType: tab });
    } catch (err) {
      console.error("Cancel tour failed:", err);
    } finally {
      setActionBusy(false);
    }
  };

  const handleCloseModal = () => {
    setIsModalVisible(false);
    setBookingType1(null);
  };

  const handlePrint = () => {
    window.print();
  };

  if (status === "loading" && (!sourceList || sourceList.length === 0)) {
    return (
      <div className="partner-quotes-empty">
        <CircularProgress size={22} />
        <span>Loading bookings…</span>
      </div>
    );
  }

  if (status === "failed" && (!sourceList || sourceList.length === 0)) {
    return (
      <div className="partner-quotes-empty partner-quotes-empty--error">
        <span>{error || "Could not load bookings."}</span>
        <button type="button" className="partner-btn" onClick={() => load(tab)}>
          Try again
        </button>
      </div>
    );
  }

  return (
    <div
      className={`partner-tour-bookings${
        embedded ? " partner-tour-bookings--embedded" : ""
      }`}
    >
      <div className="partner-seg" style={{ marginBottom: showFilters ? 16 : 12 }}>
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

      {showFilters ? (
        <div className="partner-searchbar partner-tour-bookings__filters">
          <div className="partner-field">
            <label htmlFor="ptb-search">Booking, client or destination</label>
            <input
              id="ptb-search"
              type="search"
              placeholder="Search bookings"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>
          <div className="partner-field">
            <label htmlFor="ptb-status">Status</label>
            <select
              id="ptb-status"
              value={statusFilter}
              onChange={(e) => setStatusFilter(e.target.value)}
            >
              {STATUS_FILTERS.map((option) => (
                <option key={option.id} value={option.id}>
                  {option.label}
                </option>
              ))}
            </select>
          </div>
          <button
            type="button"
            className="partner-btn partner-btn--secondary"
            disabled={refreshing || status === "loading"}
            onClick={() => {
              setRefreshing(true);
              load(tab)
                .catch(() => {})
                .finally(() => setRefreshing(false));
            }}
          >
            {refreshing ? "Refreshing…" : "Refresh"}
          </button>
        </div>
      ) : null}

      {items.length === 0 ? (
        <div className="partner-quotes-empty">
          <span>No {tab} bookings match your filters.</span>
        </div>
      ) : (
        <div className="partner-table-scroll">
          <table className="partner-bookings-table">
            <thead>
              <tr>
                <th>Client &amp; reference</th>
                <th>Journey</th>
                <th>Service status</th>
                <th>Total / outstanding</th>
                <th aria-label="Actions" />
              </tr>
            </thead>
            <tbody>
              {items.map((tour) => {
                const badge = statusMeta(tour);
                const checkIn = formatDate(tour.check_in_time || tour.check_in);
                const checkOut = formatDate(
                  tour.check_out_time || tour.check_out
                );
                const dates =
                  checkIn && checkOut
                    ? `${checkIn} → ${checkOut}`
                    : checkIn || checkOut;
                const pax = Number(tour.total_pax || tour.pax || 0);
                const total = formatMoney(totalAmount(tour));
                const outstanding = outstandingAmount(tour);
                const outstandingLabel = formatMoney(outstanding);

                return (
                  <tr key={tour.id || tour.display_id}>
                    <td>
                      <strong>
                        {tour.customer_name ||
                          tour.tour_name ||
                          "Untitled booking"}
                      </strong>
                      <div className="partner-sub">
                        {tour.display_id || (tour.id ? `TC-${tour.id}` : "—")}
                      </div>
                    </td>
                    <td>
                      {tour.destination || tour.country || "Destination TBC"}
                      <div className="partner-sub">
                        {dates}
                        {dates && pax ? <br /> : null}
                        {pax
                          ? `${pax} traveller${pax === 1 ? "" : "s"}`
                          : null}
                      </div>
                    </td>
                    <td>
                      <span
                        className={`partner-badge${
                          badge.tone === "amber"
                            ? " partner-badge--amber"
                            : badge.tone === "muted"
                              ? " partner-badge--muted"
                              : ""
                        }`}
                      >
                        {badge.label}
                      </span>
                    </td>
                    <td>
                      <CustomPaymentTooltip list={tour}>
                        <div
                          className="partner-bookings-table__amount"
                          tabIndex={0}
                          aria-label="Price breakdown"
                        >
                          <strong>{total || "—"}</strong>
                          {outstandingLabel ? (
                            <div className="partner-sub">
                              {outstandingLabel} unallocated
                            </div>
                          ) : null}
                        </div>
                      </CustomPaymentTooltip>
                    </td>
                    <td>
                      <div className="partner-bookings-table__actions">
                        <button
                          type="button"
                          className="partner-link partner-bookings-table__manage"
                          onClick={(event) => openActionMenu(event, tour)}
                          disabled={actionBusy}
                        >
                          Manage
                          <KeyboardArrowDownIcon fontSize="inherit" />
                        </button>
                        <IconButton
                          size="small"
                          className="partner-bookings-table__more"
                          aria-label="More actions"
                          onClick={(event) => openActionMenu(event, tour)}
                          disabled={actionBusy}
                        >
                          <MoreVertIcon fontSize="small" />
                        </IconButton>
                      </div>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      )}

      <Menu
        anchorEl={menuAnchor}
        open={Boolean(menuAnchor) && Boolean(menuTour)}
        onClose={closeActionMenu}
        anchorOrigin={{ horizontal: "right", vertical: "bottom" }}
        transformOrigin={{ horizontal: "right", vertical: "top" }}
        PaperProps={{
          className: "partner-booking-actions-menu",
          elevation: 0,
        }}
      >
        <MenuItem
          className="partner-booking-actions-menu__item"
          onClick={() => menuTour && handleViewDetails(menuTour)}
        >
          <VisibilityOutlinedIcon fontSize="small" />
          View details
        </MenuItem>
        {menuTour && canAddMoreServices(menuTour) ? (
          <MenuItem
            className="partner-booking-actions-menu__item"
            onClick={() => handleAddMoreServices(menuTour)}
          >
            <EditOutlinedIcon fontSize="small" />
            Add more services
          </MenuItem>
        ) : null}
        {menuTour && canUpdateTourPlan(menuTour) ? (
          <MenuItem
            className="partner-booking-actions-menu__item"
            onClick={() => handleUpdateTourPlan(menuTour)}
          >
            <UpdateOutlinedIcon fontSize="small" />
            Update tour plan
          </MenuItem>
        ) : null}
        {menuTour && canCancelTour(menuTour) ? (
          <>
            <Divider className="partner-booking-actions-menu__divider" />
            <MenuItem
              className="partner-booking-actions-menu__item partner-booking-actions-menu__item--danger"
              onClick={() => handleCancelTour(menuTour)}
            >
              <CancelOutlinedIcon fontSize="small" />
              Cancel booking
            </MenuItem>
          </>
        ) : null}
      </Menu>

      <TourDetailsModal
        isModalVisible={isModalVisible}
        handleCloseModal={handleCloseModal}
        markupAmount={markupAmount}
        setMarkupAmount={setMarkupAmount}
        discountAmount={discountAmount}
        setDiscountAmount={setDiscountAmount}
        handlePrint={handlePrint}
        bookingType1={bookingType1}
        handleOpenEnquiryModal={() => {}}
      />
    </div>
  );
}
