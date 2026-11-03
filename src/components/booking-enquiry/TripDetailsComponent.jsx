import React, { useState, useEffect } from "react";
import { useSelector, useDispatch } from "react-redux";
import { updateServiceDetails } from "@/slice/common/EnquirySlice";

const formatDate = (dateString) => {
  if (!dateString) return { primary: "Not specified", secondary: "" };
  const parts = String(dateString).split("/");
  if (parts.length === 3) {
    const months = [
      "Jan", "Feb", "Mar", "Apr", "May", "Jun",
      "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
    ];
    const day = parts[0];
    const month = months[parseInt(parts[1], 10) - 1] || parts[1];
    const year = parts[2];
    try {
      const d = new Date(
        parseInt(parts[2], 10),
        parseInt(parts[1], 10) - 1,
        parseInt(parts[0], 10)
      );
      const weekday = d.toLocaleDateString("en-US", { weekday: "short" });
      return { primary: `${day} ${month} ${year}`, secondary: weekday };
    } catch {
      return { primary: `${day} ${month} ${year}`, secondary: "" };
    }
  }
  return { primary: String(dateString), secondary: "" };
};

const Field = ({ label, primary, secondary }) => (
  <div className="enquiry-summary-field">
    <div className="enquiry-summary-label">{label}</div>
    <div className="enquiry-summary-primary">{primary || "—"}</div>
    {secondary ? (
      <div className="enquiry-summary-secondary">{secondary}</div>
    ) : null}
  </div>
);

const TripDetailsComponent = ({ mode = "view" }) => {
  const dispatch = useDispatch();
  const bookingDetails = useSelector((state) => state.enquiry);

  const [countryValue, setCountryValue] = useState("");
  const [cityValue, setCityValue] = useState("");

  useEffect(() => {
    if (bookingDetails.searchLocation) {
      setCountryValue(bookingDetails.searchLocation.country || "");
      setCityValue(bookingDetails.searchLocation.city || "");
    }
  }, [bookingDetails.searchLocation]);

  const handleCountryChange = (e) => {
    if (mode !== "edit") return;
    setCountryValue(e.target.value);
    if (bookingDetails.searchLocation) {
      dispatch(
        updateServiceDetails({
          service: "searchLocation",
          data: {
            ...bookingDetails.searchLocation,
            country: e.target.value,
          },
        })
      );
    }
  };

  const handleCityChange = (e) => {
    if (mode !== "edit") return;
    setCityValue(e.target.value);
    if (bookingDetails.searchLocation) {
      dispatch(
        updateServiceDetails({
          service: "searchLocation",
          data: {
            ...bookingDetails.searchLocation,
            city: e.target.value,
          },
        })
      );
    }
  };

  const checkIn = formatDate(bookingDetails.checkIn);
  const checkOut = formatDate(bookingDetails.checkOut);
  const guests = bookingDetails.guests || {};
  const genderAdults =
    (parseInt(guests.maleCount, 10) || 0) +
    (parseInt(guests.femaleCount, 10) || 0);
  const adults = genderAdults || parseInt(guests.adults, 10) || 0;
  const children = parseInt(guests.children, 10) || 0;
  const infants = parseInt(guests.infant, 10) || 0;
  const total = adults + children + infants;
  const guestPrimary = `${total || 1} Traveller${total === 1 ? "" : "s"}`;
  const guestSecondary = [
    adults ? `${adults} Adult${adults === 1 ? "" : "s"}` : null,
    children ? `${children} Child` : null,
    infants ? `${infants} Infant` : null,
  ]
    .filter(Boolean)
    .join(" · ");

  if (mode === "edit") {
    return (
      <div className="enquiry-summary-bar-wrap">
        <div className="enquiry-summary-bar">
          <div className="partner-card__title" style={{ marginBottom: 12 }}>
            Trip details
          </div>
          <div className="enquiry-summary-fields">
            <div className="partner-field">
              <label htmlFor="trip-country">Country</label>
              <input
                id="trip-country"
                value={countryValue}
                onChange={handleCountryChange}
                placeholder="Country"
              />
            </div>
            <div className="partner-field">
              <label htmlFor="trip-city">City</label>
              <input
                id="trip-city"
                value={cityValue}
                onChange={handleCityChange}
                placeholder="City"
              />
            </div>
            <Field label="Check-in" primary={checkIn.primary} secondary={checkIn.secondary} />
            <Field label="Check-out" primary={checkOut.primary} secondary={checkOut.secondary} />
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="enquiry-summary-bar-wrap">
      <div className="enquiry-summary-bar">
        <div className="enquiry-summary-fields">
          <Field
            label="Country"
            primary={countryValue || bookingDetails.searchLocation?.country || "—"}
          />
          <Field
            label="City"
            primary={cityValue || bookingDetails.searchLocation?.city || "—"}
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

export default TripDetailsComponent;
