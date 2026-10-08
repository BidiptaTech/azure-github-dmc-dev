import React from "react";
import { useSelector } from "react-redux";

const Field = ({ label, primary, secondary }) => (
  <div
    className="enquiry-summary-field"
    style={{
      flex: "1 1 0",
      minWidth: 140,
      minHeight: 0,
      background: "#ffffff",
      borderRadius: 8,
      padding: "8px 12px",
      border: "1px solid rgba(15, 23, 42, 0.08)",
      boxShadow: "0 1px 3px rgba(15, 23, 42, 0.08)",
      boxSizing: "border-box",
      display: "flex",
      flexDirection: "column",
      justifyContent: "center",
      overflow: "visible",
    }}
  >
    <div
      className="enquiry-summary-label"
      style={{
        display: "block",
        fontSize: 11,
        fontWeight: 700,
        letterSpacing: "0.06em",
        textTransform: "uppercase",
        color: "#64748b",
        marginBottom: 2,
        lineHeight: "14px",
        minHeight: 0,
      }}
    >
      {label}
    </div>
    <div
      className="enquiry-summary-primary"
      style={{
        display: "block",
        fontSize: 15,
        fontWeight: 700,
        color: "#0f172a",
        lineHeight: "20px",
      }}
    >
      {primary || "—"}
    </div>
    {secondary ? (
      <div
        className="enquiry-summary-secondary"
        style={{
          display: "block",
          fontSize: 12,
          fontWeight: 500,
          color: "#64748b",
          marginTop: 1,
          lineHeight: "16px",
        }}
      >
        {secondary}
      </div>
    ) : null}
  </div>
);

const formatDisplayDate = (dateString) => {
  if (!dateString) return { primary: "Not set", secondary: "" };
  const parts = String(dateString).split("/");
  if (parts.length === 3) {
    const months = [
      "Jan", "Feb", "Mar", "Apr", "May", "Jun",
      "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
    ];
    const day = parts[0];
    const month = months[parseInt(parts[1], 10) - 1] || parts[1];
    const year = parts[2]?.slice(-2);
    try {
      const d = new Date(
        parseInt(parts[2], 10),
        parseInt(parts[1], 10) - 1,
        parseInt(parts[0], 10)
      );
      const weekday = d.toLocaleDateString("en-US", { weekday: "short" });
      return {
        primary: `${day} ${month}'${year}`,
        secondary: weekday,
      };
    } catch {
      return { primary: `${day} ${month}'${year}`, secondary: "" };
    }
  }
  return { primary: String(dateString), secondary: "" };
};

const EnquirySearchSummaryBar = () => {
  const bookingDetails = useSelector((state) => state.enquiry);
  const searchLocation = bookingDetails?.searchLocation || {};
  const citiesList = Array.isArray(searchLocation.cities)
    ? searchLocation.cities
    : [];

  const cityRaw = searchLocation.city || "";
  const countryRaw = searchLocation.country || "";

  const destinationPrimary =
    citiesList.length > 1
      ? citiesList.map((c) => c.city).filter(Boolean).join(", ")
      : cityRaw.split(",")[0]?.trim() || cityRaw || "—";

  const destinationSecondary =
    citiesList.length > 1
      ? [...new Set(citiesList.map((c) => c.country).filter(Boolean))].join(", ")
      : countryRaw &&
        countryRaw.toLowerCase() !== destinationPrimary.toLowerCase() &&
        !destinationPrimary.toLowerCase().includes(countryRaw.toLowerCase())
      ? countryRaw
      : "";

  const checkIn = formatDisplayDate(bookingDetails?.checkIn);
  const checkOut = formatDisplayDate(bookingDetails?.checkOut);
  const guests = bookingDetails?.guests || {};
  const adults = parseInt(guests.adults, 10) || 0;
  const children = parseInt(guests.children, 10) || 0;
  const infants = parseInt(guests.infant, 10) || 0;
  const totalPax = adults + children + infants;

  const guestPrimary = `${totalPax || 1} Traveller${totalPax === 1 ? "" : "s"}`;
  const guestSecondary = [
    adults ? `${adults} Adult${adults === 1 ? "" : "s"}` : null,
    children ? `${children} Child` : null,
    infants ? `${infants} Infant` : null,
  ]
    .filter(Boolean)
    .join(" · ");

  return (
    <div
      className="enquiry-summary-bar-wrap"
      style={{
        width: "100%",
        boxSizing: "border-box",
        padding: "0 0 8px",
        marginBottom: 0,
      }}
    >
      <div
        className="enquiry-summary-bar"
        style={{
          width: "100%",
          margin: 0,
          background: "linear-gradient(90deg, #3554D1 0%, #4c6fff 100%)",
          padding: "10px 12px",
          borderRadius: 12,
          boxSizing: "border-box",
          overflow: "visible",
          boxShadow: "0 8px 24px rgba(53, 84, 209, 0.22)",
        }}
      >
      <style>{`
        .enquiry-summary-bar {
          overflow: visible !important;
        }
        .enquiry-summary-bar .enquiry-summary-label {
          display: block !important;
          visibility: visible !important;
          opacity: 1 !important;
          height: auto !important;
          max-height: none !important;
          overflow: visible !important;
          font-size: 11px !important;
          font-weight: 700 !important;
          letter-spacing: 0.06em !important;
          text-transform: uppercase !important;
          color: #64748b !important;
          margin: 0 0 2px 0 !important;
          padding: 0 !important;
          line-height: 14px !important;
          min-height: 0 !important;
        }
        .enquiry-summary-bar .enquiry-summary-primary {
          display: block !important;
          font-size: 15px !important;
          font-weight: 700 !important;
          color: #0f172a !important;
          line-height: 20px !important;
        }
        .enquiry-summary-bar .enquiry-summary-secondary {
          display: block !important;
          font-size: 12px !important;
          font-weight: 500 !important;
          color: #64748b !important;
          margin-top: 1px !important;
          line-height: 16px !important;
        }
        .enquiry-summary-fields {
          display: flex;
          flex-wrap: wrap;
          align-items: stretch;
          gap: 8px;
        }
        .enquiry-summary-field {
          overflow: visible !important;
        }
        @media (min-width: 900px) {
          .enquiry-summary-fields {
            flex-wrap: nowrap;
          }
        }
        @media (max-width: 899px) {
          .enquiry-summary-bar-wrap {
            padding: 0 0 6px !important;
          }
          .enquiry-summary-bar {
            padding: 8px 10px !important;
            border-radius: 10px !important;
          }
          .enquiry-summary-field {
            min-width: calc(50% - 4px) !important;
          }
        }
      `}</style>

      <div className="enquiry-summary-fields">
        <Field
          label="Destination"
          primary={destinationPrimary}
          secondary={destinationSecondary}
        />
        <Field
          label="Check-in"
          primary={checkIn.primary}
          secondary={checkIn.secondary}
        />
        <Field
          label="Check-out"
          primary={checkOut.primary}
          secondary={checkOut.secondary}
        />
        <Field
          label="Travellers"
          primary={guestPrimary}
          secondary={guestSecondary || undefined}
        />
      </div>
      </div>
    </div>
  );
};

export default EnquirySearchSummaryBar;
