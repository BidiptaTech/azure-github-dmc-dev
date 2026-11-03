import { useEffect, useMemo, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { useNavigate } from "react-router-dom";
import dayjs from "dayjs";
import {
  Box,
  CircularProgress,
  IconButton,
  Modal,
  Tab,
  Tabs,
  Typography,
} from "@mui/material";
import CloseIcon from "@mui/icons-material/Close";
import { fetchEnquiries } from "@/slice/enquiries/enquiryListSlice";
import {
  convertEnquiresToTourId,
  resetConvertState,
} from "@/slice/enquiries/enquiryToTourSlice";
import {
  clearPackages,
  setPackageData,
} from "@/slice/tour-packages/tourPackageSlice";
import { resetPackages } from "@/slice/tour-packages/prePackagesSlice";
import {
  RenderHotelDetails,
  RenderAttractionDetails,
  RenderRestaurantDetails,
  RenderGuideDetails,
  RenderLocalTransferDetails,
  RenderPortDetails,
} from "@/components/dashboard/dashboard/db-dashboard/components/renderServices";

const MANAGER_ROLES = [
  "Sales Head(DMC)",
  "Sales Manager (DMC)",
  "Assistant Manager (DMC)",
];

const SERVICE_META = [
  { key: "hotel", label: "Hotel" },
  { key: "local_transfer", label: "Transfer" },
  { key: "port", label: "Port" },
  { key: "attraction", label: "Attraction" },
  { key: "restaurant", label: "Dining" },
  { key: "guide", label: "Guide" },
];

const modalStyle = {
  position: "absolute",
  top: "50%",
  left: "50%",
  transform: "translate(-50%, -50%)",
  width: { xs: "95%", sm: "90%", md: "80%" },
  maxWidth: 1000,
  maxHeight: "90vh",
  bgcolor: "background.paper",
  boxShadow: 24,
  borderRadius: 2,
  p: { xs: 2, sm: 3, md: 4 },
  overflow: "auto",
};

function statusLabel(enquiry) {
  const raw = (
    enquiry.status ||
    enquiry.enquiry_status ||
    enquiry.status_name ||
    ""
  ).toString();
  if (!raw) return "Open";
  return raw;
}

function statusTone(label) {
  const s = label.toLowerCase();
  if (s.includes("ready") || s.includes("propos") || s.includes("confirm")) {
    return "green";
  }
  if (s.includes("progress") || s.includes("pending") || s.includes("await")) {
    return "amber";
  }
  if (s.includes("cancel") || s.includes("reject")) return "muted";
  return "blue";
}

function guestLine(enquiry) {
  const adults = Number(enquiry.adult || 0);
  const children = Number(enquiry.child || 0);
  const infants = Number(enquiry.infant || 0);
  const total = adults + children + infants;
  const parts = [
    `${adults} adult${adults === 1 ? "" : "s"}`,
    children ? `${children} child${children === 1 ? "" : "ren"}` : null,
    infants ? `${infants} infant${infants === 1 ? "" : "s"}` : null,
  ].filter(Boolean);
  return `${total} travellers · ${parts.join(", ")}`;
}

function serviceSummary(enquiry) {
  return SERVICE_META.filter((s) => enquiry[s.key])
    .map((s) => s.label)
    .join(" · ");
}

export default function PartnerEnquiryList() {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { enquiries, status, error, stats } = useSelector(
    (state) => state.bookingEnquiry
  );
  const userRole = useSelector((state) => state.auth.userRole);
  const selectedAgent = useSelector((state) => state.editing?.agentId);

  const [searchId, setSearchId] = useState("");
  const [city, setCity] = useState("");
  const [dateRange, setDateRange] = useState("all");
  const [page, setPage] = useState(0);
  const rowsPerPage = 8;
  const [convertingId, setConvertingId] = useState(null);
  const [modalOpen, setModalOpen] = useState(false);
  const [selectedEnquiry, setSelectedEnquiry] = useState(null);
  const [selectedService, setSelectedService] = useState(null);
  const [tabValue, setTabValue] = useState(0);
  const [refreshing, setRefreshing] = useState(false);

  const canConvert = MANAGER_ROLES.includes(userRole);

  const refresh = async () => {
    setRefreshing(true);
    try {
      await dispatch(
        fetchEnquiries({
          ...(selectedAgent ? { agentId: selectedAgent } : {}),
          reset: true,
          start: 0,
          limit: 30,
        })
      ).unwrap();
    } catch {
      /* status/error from slice */
    } finally {
      setRefreshing(false);
    }
  };

  useEffect(() => {
    refresh();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedAgent]);

  const filtered = useMemo(() => {
    const list = Array.isArray(enquiries) ? enquiries : [];
    return list
      .filter((enquiry) => {
        if (searchId) {
          const term = searchId.toLowerCase();
          const displayId = (enquiry.display_id || "").toLowerCase();
          const multiId = (enquiry.multi_enq_id || "").toLowerCase();
          if (!displayId.includes(term) && !multiId.includes(term)) return false;
        }
        if (
          city &&
          !(enquiry.city || "").toLowerCase().includes(city.toLowerCase())
        ) {
          return false;
        }
        if (dateRange !== "all") {
          const today = dayjs();
          const checkIn = dayjs(enquiry.check_in_time);
          if (dateRange === "upcoming" && checkIn.isBefore(today, "day")) {
            return false;
          }
          if (dateRange === "past" && checkIn.isAfter(today, "day")) {
            return false;
          }
        }
        return true;
      })
      .sort(
        (a, b) =>
          dayjs(b.created_at).valueOf() - dayjs(a.created_at).valueOf()
      );
  }, [enquiries, searchId, city, dateRange]);

  const totalPages = Math.max(1, Math.ceil(filtered.length / rowsPerPage));
  const pageSafe = Math.min(page, totalPages - 1);
  const pageItems = filtered.slice(
    pageSafe * rowsPerPage,
    pageSafe * rowsPerPage + rowsPerPage
  );

  const openService = (enquiry, serviceKey) => {
    setSelectedEnquiry(enquiry);
    setSelectedService(serviceKey);
    setTabValue(0);
    setModalOpen(true);
  };

  const handleConvert = async (enquiry) => {
    setConvertingId(enquiry.enquiry_id || enquiry.id);
    dispatch(setPackageData(null));
    dispatch(clearPackages());
    dispatch(resetConvertState());
    try {
      await dispatch(
        convertEnquiresToTourId({
          agentId: enquiry.agent_id,
          enquiryID: enquiry.enquiry_id,
        })
      ).unwrap();
      dispatch(resetPackages());
      navigate("/app/tour-packages-page");
    } catch (err) {
      console.error("Conversion failed:", err);
    } finally {
      setConvertingId(null);
    }
  };

  if (status === "loading" && (!enquiries || enquiries.length === 0)) {
    return (
      <div className="partner-quotes-empty">
        <CircularProgress size={22} />
        <span>Loading quick enquiries…</span>
      </div>
    );
  }

  if (status === "failed") {
    return (
      <div className="partner-quotes-empty partner-quotes-empty--error">
        <span>{error || "Could not load enquiries."}</span>
        <button type="button" className="partner-btn" onClick={refresh}>
          Try again
        </button>
      </div>
    );
  }

  return (
    <div className="partner-enquiry">
      <div className="partner-summary-grid partner-enquiry__stats">
        <div className="partner-stat" style={{ cursor: "default" }}>
          <span className="partner-stat__label">Total enquiries</span>
          <strong className="partner-stat__value">{stats?.total || 0}</strong>
        </div>
        <div className="partner-stat" style={{ cursor: "default" }}>
          <span className="partner-stat__label">With hotel</span>
          <strong className="partner-stat__value">{stats?.withHotel || 0}</strong>
        </div>
        <div className="partner-stat" style={{ cursor: "default" }}>
          <span className="partner-stat__label">With transfers</span>
          <strong className="partner-stat__value">
            {(stats?.withPickup || 0) + (stats?.withPort || 0)}
          </strong>
        </div>
        <div className="partner-stat" style={{ cursor: "default" }}>
          <span className="partner-stat__label">With experiences</span>
          <strong className="partner-stat__value">
            {(stats?.withAttractions || 0) +
              (stats?.withRestaurants || 0) +
              (stats?.withGuides || 0)}
          </strong>
        </div>
      </div>

      <div className="partner-toolbar partner-enquiry__toolbar">
        <div className="partner-enquiry__filters">
          <div className="partner-field">
            <label htmlFor="pq-search">Enquiry ID</label>
            <input
              id="pq-search"
              type="search"
              placeholder="Search ID…"
              value={searchId}
              onChange={(e) => {
                setSearchId(e.target.value);
                setPage(0);
              }}
            />
          </div>
          <div className="partner-field">
            <label htmlFor="pq-city">City</label>
            <input
              id="pq-city"
              type="search"
              placeholder="Filter city…"
              value={city}
              onChange={(e) => {
                setCity(e.target.value);
                setPage(0);
              }}
            />
          </div>
          <div className="partner-field">
            <label htmlFor="pq-dates">Tour dates</label>
            <select
              id="pq-dates"
              value={dateRange}
              onChange={(e) => {
                setDateRange(e.target.value);
                setPage(0);
              }}
            >
              <option value="all">All</option>
              <option value="upcoming">Upcoming</option>
              <option value="past">Past</option>
            </select>
          </div>
        </div>
        <button
          type="button"
          className="partner-btn partner-btn--secondary"
          onClick={refresh}
          disabled={refreshing}
        >
          {refreshing ? "Refreshing…" : "Refresh"}
        </button>
      </div>

      {pageItems.length === 0 ? (
        <div className="partner-quotes-empty">
          <span>No quick enquiries match your filters.</span>
        </div>
      ) : (
        <div className="partner-list">
          {pageItems.map((enquiry) => {
            const label = statusLabel(enquiry);
            const tone = statusTone(label);
            const services = serviceSummary(enquiry);
            const price = enquiry.approx_price
              ? `SGD ${parseFloat(enquiry.approx_price).toLocaleString()}`
              : null;
            const title =
              enquiry.customer_name ||
              enquiry.display_id ||
              `Enquiry #${enquiry.enquiry_id || enquiry.id}`;
            const place = [enquiry.city, enquiry.country]
              .filter(Boolean)
              .join(", ");
            const dates =
              enquiry.check_in_time && enquiry.check_out_time
                ? `${dayjs(enquiry.check_in_time).format("DD MMM YYYY")} — ${dayjs(
                    enquiry.check_out_time
                  ).format("DD MMM YYYY")}`
                : null;

            return (
              <div
                className="partner-list-item partner-enquiry__item"
                key={enquiry.enquiry_id || enquiry.id || enquiry.display_id}
              >
                <div className="partner-enquiry__main">
                  <span
                    className={`partner-chip partner-chip--${tone}`}
                  >
                    {label}
                  </span>
                  <h3>{title}</h3>
                  <p>
                    {[
                      place,
                      dates,
                      guestLine(enquiry),
                      price,
                      services || "No services selected yet",
                    ]
                      .filter(Boolean)
                      .join(" · ")}
                  </p>
                  <div className="partner-enquiry__services">
                    {SERVICE_META.map((svc) => {
                      const active = Boolean(enquiry[svc.key]);
                      return (
                        <button
                          key={svc.key}
                          type="button"
                          className={`partner-enquiry__svc${
                            active ? " is-active" : ""
                          }`}
                          disabled={!active}
                          onClick={() => openService(enquiry, svc.key)}
                        >
                          {svc.label}
                        </button>
                      );
                    })}
                  </div>
                </div>
                <div className="partner-enquiry__actions">
                  {canConvert && (
                    <button
                      type="button"
                      className="partner-btn partner-btn--primary"
                      disabled={convertingId === (enquiry.enquiry_id || enquiry.id)}
                      onClick={() => handleConvert(enquiry)}
                    >
                      {convertingId === (enquiry.enquiry_id || enquiry.id)
                        ? "Creating…"
                        : "Create tour →"}
                    </button>
                  )}
                  <button
                    type="button"
                    className="partner-btn"
                    onClick={() => {
                      const first =
                        SERVICE_META.find((s) => enquiry[s.key])?.key || null;
                      if (first) openService(enquiry, first);
                    }}
                  >
                    Review proposal →
                  </button>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {filtered.length > rowsPerPage && (
        <div className="partner-enquiry__pager">
          <button
            type="button"
            className="partner-btn partner-btn--secondary"
            disabled={pageSafe <= 0}
            onClick={() => setPage((p) => Math.max(0, p - 1))}
          >
            ← Previous
          </button>
          <span>
            Page {pageSafe + 1} of {totalPages} · {filtered.length} enquiries
          </span>
          <button
            type="button"
            className="partner-btn partner-btn--secondary"
            disabled={pageSafe >= totalPages - 1}
            onClick={() => setPage((p) => Math.min(totalPages - 1, p + 1))}
          >
            Next →
          </button>
        </div>
      )}

      <Modal
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        aria-labelledby="partner-enquiry-service"
      >
        <Box sx={modalStyle}>
          <Box
            sx={{
              display: "flex",
              justifyContent: "space-between",
              alignItems: "center",
              mb: 2,
            }}
          >
            <Typography variant="h6" component="h2" fontWeight={600}>
              {selectedService === "hotel" && "Hotel details"}
              {selectedService === "attraction" && "Attraction details"}
              {selectedService === "restaurant" && "Restaurant details"}
              {selectedService === "guide" && "Guide details"}
              {selectedService === "local_transfer" && "Transfer details"}
              {selectedService === "port" && "Port details"}
            </Typography>
            <IconButton onClick={() => setModalOpen(false)} aria-label="Close">
              <CloseIcon />
            </IconButton>
          </Box>
          <Tabs
            value={tabValue}
            onChange={(_, v) => setTabValue(v)}
            sx={{ borderBottom: 1, borderColor: "divider", mb: 2 }}
          >
            <Tab label="Overview" />
            <Tab label="Details" />
          </Tabs>
          {selectedEnquiry && selectedService === "hotel" && (
            <RenderHotelDetails
              details={selectedEnquiry.hotel_details}
              tabValue={tabValue}
            />
          )}
          {selectedEnquiry && selectedService === "attraction" && (
            <RenderAttractionDetails
              details={selectedEnquiry.attraction_details}
              tabValue={tabValue}
            />
          )}
          {selectedEnquiry && selectedService === "restaurant" && (
            <RenderRestaurantDetails
              details={selectedEnquiry.restaurant_details}
              tabValue={tabValue}
            />
          )}
          {selectedEnquiry && selectedService === "guide" && (
            <RenderGuideDetails
              details={selectedEnquiry.guide_details}
              tabValue={tabValue}
            />
          )}
          {selectedEnquiry && selectedService === "local_transfer" && (
            <RenderLocalTransferDetails
              details={selectedEnquiry.local_transfer_details}
              tabValue={tabValue}
            />
          )}
          {selectedEnquiry && selectedService === "port" && (
            <RenderPortDetails enquiry={selectedEnquiry} tabValue={tabValue} />
          )}
        </Box>
      </Modal>
    </div>
  );
}
