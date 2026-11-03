import React from "react";
import { useSelector } from "react-redux";

const Field = ({ label, primary, secondary }) => (
  <div className="enquiry-summary-field">
    <div className="enquiry-summary-label">{label}</div>
    <div className="enquiry-summary-primary">{primary || "—"}</div>
    {secondary ? (
      <div className="enquiry-summary-secondary">{secondary}</div>
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
    <div className="enquiry-summary-bar-wrap">
      <div className="enquiry-summary-bar">
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
