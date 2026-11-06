import { useState } from "react";
import { useDispatch } from "react-redux";
import axios from "axios";
import Cookies from "js-cookie";
import { Alert, Snackbar } from "@mui/material";
import EnquiryModal from "@/components/dashboard/dashboard/db-dashboard/components/EnquiryModal";
import { fetchLists } from "@/slice/common/TourlistSlice";
import { BASE_URL } from "@/services/api";
import { canNegotiate } from "@/utils/tourBookingActions";

function buildCountryEnquiryAmounts(historyData = []) {
  const byCountry = {};
  (Array.isArray(historyData) ? historyData : []).forEach((item) => {
    const country = String(item?.country || "").trim();
    if (!country) return;
    const current = parseFloat(item.current_price);
    const actual = parseFloat(item.actual_price);
    const value = Number.isFinite(current)
      ? current
      : Number.isFinite(actual)
        ? actual
        : 0;
    byCountry[country] = Math.ceil(value);
  });
  return byCountry;
}

export default function PartnerNegotiation({ tour, userRole }) {
  const dispatch = useDispatch();
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [history, setHistory] = useState([]);
  const [assigned, setAssigned] = useState(null);
  const [amounts, setAmounts] = useState({});
  const [comment, setComment] = useState("");
  const [commentError, setCommentError] = useState(false);
  const [notice, setNotice] = useState(null);

  const allowed = canNegotiate(tour, userRole);
  const isEnquiry = tour?.booking_type === "enquiry";

  const notify = (message, severity = "info") => {
    setNotice({ message, severity });
  };

  const close = () => {
    setOpen(false);
    setComment("");
    setCommentError(false);
    setAssigned(null);
    setAmounts({});
  };

  const openNegotiation = async () => {
    if (!tour?.id || loading) return;
    setLoading(true);
    try {
      const authToken = Cookies.get("authToken");
      const AgentId = Cookies.get("AgentId");
      if (!authToken || !AgentId) {
        notify("Please sign in again to negotiate this booking.", "error");
        return;
      }

      const response = await axios.get(`${BASE_URL}/enquiry-status`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          "Content-Type": "application/json",
          "agent-id": AgentId,
        },
        params: { tour_id: tour.id },
      });

      const raw = response.data?.data;
      const historyData = raw
        ? Array.isArray(raw)
          ? raw
          : [raw]
        : [];

      if (historyData.length) {
        const processed = historyData.some(
          (item) =>
            item.status === "2" ||
            item.status === "3" ||
            item.status === 2 ||
            item.status === 3
        );
        if (processed) {
          notify(
            "This enquiry has already been processed (Booked or Cancelled).",
            "info"
          );
          return;
        }

        const assignedValue = historyData[0].assigned || null;
        if (
          assignedValue === "OM" ||
          (assignedValue && assignedValue !== "Agent")
        ) {
          setAssigned(assignedValue);
        } else {
          const isAssignedToAgent = historyData.some(
            (item) => item.assigned === "Agent"
          );
          setAssigned(isAssignedToAgent ? "Agent" : null);
        }
        setHistory(historyData);
        setAmounts(buildCountryEnquiryAmounts(historyData));
      } else {
        setHistory([]);
        setAssigned(null);
        setAmounts({});
      }

      setOpen(true);
    } catch {
      notify("Could not open negotiation. Please try again.", "error");
    } finally {
      setLoading(false);
    }
  };

  const submitEnquiry = async (type) => {
    if (type === "enquiry" && !comment.trim()) {
      setCommentError(true);
      notify("Please enter a comment for the enquiry.", "error");
      return;
    }

    const authToken = Cookies.get("authToken");
    const AgentId = Cookies.get("AgentId");
    if (!authToken || !AgentId) {
      notify("Please sign in again to submit this negotiation.", "error");
      return;
    }

    const countryMap = {};
    history.forEach((item) => {
      const country = String(item?.country || "").trim();
      if (!country) return;
      const actual = parseFloat(item.actual_price);
      const current = parseFloat(item.current_price);
      countryMap[country] = {
        country,
        currency: String(item?.currency || "").trim() || "SGD",
        actual_price: Number.isFinite(actual)
          ? Math.ceil(actual)
          : Number.isFinite(current)
            ? Math.ceil(current)
            : 0,
        current_price: Number.isFinite(current)
          ? Math.ceil(current)
          : Number.isFinite(actual)
            ? Math.ceil(actual)
            : 0,
      };
    });

    const country_prices = Object.values(countryMap).map((item) => {
      const negotiated = parseFloat(amounts[item.country]);
      const enquiryPrice = Number.isFinite(negotiated)
        ? Math.ceil(negotiated)
        : item.current_price;
      return {
        country: item.country,
        currency: item.currency,
        actual_price: String(item.actual_price),
        enquiry_price: String(enquiryPrice),
      };
    });

    if (!country_prices.length) {
      notify("No country price data found for this enquiry.", "error");
      return;
    }

    const exceeded = country_prices.find((item) => {
      const max = parseFloat(
        countryMap[item.country]?.current_price ||
          countryMap[item.country]?.actual_price ||
          0
      );
      return parseFloat(item.enquiry_price) > max;
    });
    if (exceeded) {
      notify(
        `Negotiated amount for ${exceeded.country} cannot exceed its current price.`,
        "error"
      );
      return;
    }

    const actualPrice = country_prices.reduce(
      (sum, item) => sum + (parseFloat(item.actual_price) || 0),
      0
    );
    const enquiryPrice = country_prices.reduce(
      (sum, item) => sum + (parseFloat(item.enquiry_price) || 0),
      0
    );

    try {
      const response = await axios.post(
        `${BASE_URL}/update-enquiry`,
        {
          tour_id: tour.id,
          comment,
          total_price: Math.ceil(actualPrice).toString(),
          enquiry_price: Math.ceil(enquiryPrice).toString(),
          country_prices,
          type,
        },
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
            "Content-Type": "application/json",
            "agent-id": AgentId,
          },
        }
      );

      if (response.data?.success) {
        const success =
          type === "accept"
            ? "Enquiry accepted."
            : type === "cancel"
              ? "Enquiry cancelled."
              : "Enquiry submitted.";
        notify(success, "success");
        close();
        dispatch(fetchLists({ reset: true }));
      } else {
        notify(response.data?.message || "Negotiation could not be saved.", "error");
      }
    } catch {
      notify("Something went wrong. Please try again.", "error");
    }
  };

  return (
    <div className="partner-panel">
      <div className="partner-kicker">Price discussion</div>
      <h2>Negotiation</h2>
      <p className="partner-section-help">
        {allowed
          ? "Discuss the country-wise price with your DMC. Accept, revise, or cancel the enquiry from here."
          : isEnquiry
            ? "This enquiry is not open for you to negotiate right now."
            : "Negotiation is only for enquiry bookings. Confirmed bookings are managed from View details and Quotation."}
      </p>
      {allowed ? (
        <button
          type="button"
          className="partner-btn partner-btn--block"
          disabled={loading}
          onClick={openNegotiation}
        >
          {loading ? "Opening negotiation…" : "Open negotiation"}
        </button>
      ) : null}

      <EnquiryModal
        isEnquiryModalVisible={open}
        handleCloseEnquiryModal={close}
        enquiryHistory={history}
        loadingEnquiryHistory={loading}
        assigned={assigned}
        countryEnquiryAmounts={amounts}
        handleCountryEnquiryAmountChange={(country, value) =>
          setAmounts((prev) => ({ ...prev, [country]: value }))
        }
        enquiryComment={comment}
        setEnquiryComment={setComment}
        commentError={commentError}
        setCommentError={setCommentError}
        submitEnquiry={submitEnquiry}
        handleEnquirySubmit={() => submitEnquiry("enquiry")}
      />

      <Snackbar
        open={Boolean(notice)}
        autoHideDuration={4000}
        onClose={() => setNotice(null)}
        anchorOrigin={{ vertical: "top", horizontal: "center" }}
      >
        <Alert
          severity={notice?.severity || "info"}
          onClose={() => setNotice(null)}
          variant="filled"
        >
          {notice?.message}
        </Alert>
      </Snackbar>
    </div>
  );
}
