import { useEffect, useRef, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import moment from "moment";
import { fetchCityCountry } from "@/slice/common/citiesSlice";
import GuestSearch from "./GuestSearch";
import DateSearch from "../DateSearch";

export function createEmptyLeg(overrides = {}) {
  return {
    key: `leg-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
    city: null,
    dates: [],
    ...overrides,
  };
}

export function defaultMultiLegs(fromDates = []) {
  const hasRange = Array.isArray(fromDates) && fromDates.length === 2;
  const start = hasRange ? moment(fromDates[0]) : moment().add(14, "days");
  const mid = hasRange ? moment(fromDates[1]) : moment(start).add(3, "days");
  const end = moment(mid).add(3, "days");

  return [
    createEmptyLeg({ dates: [start, mid] }),
    createEmptyLeg({ dates: [mid, end] }),
  ];
}

function formatGoibiboDate(date) {
  if (!date) return { primary: "Select date", secondary: "Tap to choose" };
  try {
    const m =
      typeof date.format === "function"
        ? moment(date.format("YYYY-MM-DD"))
        : moment(date);
    return {
      primary: m.format("DD MMM'YY"),
      secondary: m.format("dddd"),
    };
  } catch {
    return { primary: "Select date", secondary: "Tap to choose" };
  }
}

function LegCityField({ label, city, usedCityIds, onSelect }) {
  const dispatch = useDispatch();
  const [searchValue, setSearchValue] = useState("");
  const [open, setOpen] = useState(false);
  const [highlightedIndex, setHighlightedIndex] = useState(-1);
  const wrapRef = useRef(null);
  const { cityCountryResults = [], loading } = useSelector(
    (state) => state.cities || {}
  );

  useEffect(() => {
    if (!open) return undefined;
    const onDoc = (e) => {
      if (wrapRef.current && !wrapRef.current.contains(e.target)) {
        setOpen(false);
      }
    };
    document.addEventListener("mousedown", onDoc);
    return () => document.removeEventListener("mousedown", onDoc);
  }, [open]);

  useEffect(() => {
    if (searchValue.length >= 3) {
      dispatch(fetchCityCountry(searchValue.substring(0, 3)));
    }
  }, [searchValue, dispatch]);

  const suggestions = (cityCountryResults || []).filter(
    (item) =>
      !usedCityIds.includes(item.city_id) || item.city_id === city?.city_id
  );

  const pick = (item) => {
    onSelect(item);
    setSearchValue("");
    setOpen(false);
    setHighlightedIndex(-1);
  };

  return (
    <div ref={wrapRef} style={{ position: "relative", minWidth: 0 }}>
      <div style={{ padding: "8px 14px" }}>
        <div
          style={{
            fontSize: 11,
            fontWeight: 600,
            letterSpacing: "0.04em",
            textTransform: "uppercase",
            color: "#64748b",
            marginBottom: 2,
          }}
        >
          {label}
        </div>
        {city && !open ? (
          <button
            type="button"
            onClick={() => {
              setOpen(true);
              setSearchValue("");
            }}
            style={{
              border: 0,
              background: "transparent",
              padding: 0,
              textAlign: "left",
              cursor: "pointer",
              width: "100%",
            }}
          >
            <div
              style={{
                fontSize: 16,
                fontWeight: 700,
                color: "#0f172a",
                lineHeight: 1.2,
              }}
            >
              {city.city}
            </div>
            <div style={{ fontSize: 11, color: "#64748b", marginTop: 1 }}>
              {city.country || "Change city"}
            </div>
          </button>
        ) : (
          <input
            type="text"
            value={searchValue}
            autoFocus={open && Boolean(city)}
            placeholder="City or country"
            onChange={(e) => {
              setSearchValue(e.target.value);
              setOpen(true);
              setHighlightedIndex(-1);
            }}
            onFocus={() => setOpen(true)}
            onKeyDown={(e) => {
              if (!open || !suggestions.length) return;
              if (e.key === "ArrowDown") {
                e.preventDefault();
                setHighlightedIndex((i) =>
                  Math.min(i + 1, suggestions.length - 1)
                );
              } else if (e.key === "ArrowUp") {
                e.preventDefault();
                setHighlightedIndex((i) => Math.max(i - 1, 0));
              } else if (e.key === "Enter" && highlightedIndex >= 0) {
                e.preventDefault();
                pick(suggestions[highlightedIndex]);
              } else if (e.key === "Escape") {
                setOpen(false);
              }
            }}
            style={{
              width: "100%",
              border: 0,
              outline: "none",
              fontSize: 16,
              fontWeight: 700,
              color: "#0f172a",
              background: "transparent",
              padding: 0,
            }}
          />
        )}
      </div>

      {open && searchValue.length >= 3 && (
        <div
          style={{
            position: "absolute",
            left: 8,
            right: 8,
            top: "100%",
            zIndex: 80,
            background: "#fff",
            border: "1px solid #e2e8f0",
            borderRadius: 10,
            boxShadow: "0 12px 28px rgba(15, 23, 42, 0.12)",
            maxHeight: 220,
            overflowY: "auto",
          }}
        >
          {loading && (
            <div style={{ padding: 12, fontSize: 13, color: "#64748b" }}>
              Searching…
            </div>
          )}
          {!loading && suggestions.length === 0 && (
            <div style={{ padding: 12, fontSize: 13, color: "#64748b" }}>
              No cities found
            </div>
          )}
          {suggestions.map((item, idx) => (
            <button
              key={item.city_id || `${item.city}-${idx}`}
              type="button"
              onMouseDown={(e) => e.preventDefault()}
              onClick={() => pick(item)}
              style={{
                display: "block",
                width: "100%",
                textAlign: "left",
                border: 0,
                background: idx === highlightedIndex ? "#eef3ff" : "#fff",
                padding: "10px 12px",
                cursor: "pointer",
              }}
            >
              <div style={{ fontSize: 14, fontWeight: 600, color: "#0f172a" }}>
                {item.city}
              </div>
              <div style={{ fontSize: 12, color: "#64748b" }}>
                {item.country}
              </div>
            </button>
          ))}
        </div>
      )}
    </div>
  );
}

/** Same check-in / check-out DateSearch UI as single-city search. */
function LegDateRange({ dates, minDate, disabled, onChange }) {
  const hasDates = Array.isArray(dates) && dates.length === 2;
  const checkInDisplay = formatGoibiboDate(hasDates ? dates[0] : null);
  const checkOutDisplay = formatGoibiboDate(hasDates ? dates[1] : null);

  return (
    <div
      className="searchMenu-date js-form-dd js-calendar position-relative cursor-pointer"
      style={{
        gridColumn: "span 2",
        minWidth: 0,
        display: "grid",
        gridTemplateColumns: "1fr 1fr",
        gap: 5,
        borderRight: "1px solid #e2e8f0",
        opacity: disabled ? 0.55 : 1,
        pointerEvents: disabled ? "none" : "auto",
      }}
      onClick={(e) => {
        if (disabled) return;
        const input = e.currentTarget.querySelector("input");
        if (input) input.focus();
      }}
    >
      <div style={{ padding: "8px 14px", borderRight: "1px solid #e2e8f0" }}>
        <div
          style={{
            fontSize: "11px",
            fontWeight: 600,
            letterSpacing: "0.04em",
            textTransform: "uppercase",
            color: "#64748b",
            marginBottom: 2,
          }}
        >
          Check-in
        </div>
        <div
          style={{
            fontSize: 18,
            fontWeight: 700,
            color: "#0f172a",
            lineHeight: 1.2,
          }}
        >
          {checkInDisplay.primary}
        </div>
        <div style={{ fontSize: 11, color: "#64748b", marginTop: 1 }}>
          {checkInDisplay.secondary}
        </div>
      </div>
      <div style={{ padding: "8px 14px" }}>
        <div
          style={{
            fontSize: "11px",
            fontWeight: 600,
            letterSpacing: "0.04em",
            textTransform: "uppercase",
            color: "#64748b",
            marginBottom: 2,
          }}
        >
          Check-out
        </div>
        <div
          style={{
            fontSize: 18,
            fontWeight: 700,
            color: hasDates ? "#0f172a" : "#94a3b8",
            lineHeight: 1.2,
          }}
        >
          {checkOutDisplay.primary}
        </div>
        <div style={{ fontSize: 11, color: "#64748b", marginTop: 1 }}>
          {checkOutDisplay.secondary}
        </div>
      </div>
      <div style={{ position: "absolute", inset: 0, opacity: 0 }}>
        <DateSearch
          onDateChange={(next) => {
            if (!Array.isArray(next) || next.length !== 2) return;
            onChange(next);
          }}
          value={hasDates ? dates : undefined}
          minDate={minDate}
          disabled={disabled}
          notifyOnMount={false}
          calendarPosition="bottom-left"
        />
      </div>
    </div>
  );
}

/**
 * Demo-style multi-city search: stacked City 1 / City 2 rows with + Add city.
 */
export default function MultiCityLegs({
  legs,
  onLegsChange,
  guestCounts,
  onGuestChange,
  onSearch,
  canSearch,
  ctaLabel = "Build multi-city trip →",
  guestOpenSignal = 0,
}) {
  const usedCityIds = legs.map((leg) => leg.city?.city_id).filter(Boolean);

  const updateLeg = (index, patch) => {
    const next = legs.map((leg, i) => (i === index ? { ...leg, ...patch } : leg));

    if (patch.dates && Array.isArray(patch.dates) && patch.dates.length === 2) {
      const checkout = patch.dates[1];
      if (next[index + 1]) {
        const nextDates = next[index + 1].dates;
        const nextCheckout =
          Array.isArray(nextDates) && nextDates.length === 2
            ? nextDates[1]
            : moment(checkout).add(3, "days");
        const safeCheckout = moment(nextCheckout).isAfter(moment(checkout))
          ? nextCheckout
          : moment(checkout).add(3, "days");
        next[index + 1] = {
          ...next[index + 1],
          dates: [checkout, safeCheckout],
        };
      }
    }

    onLegsChange(next);
  };

  const addCity = () => {
    const last = legs[legs.length - 1];
    const lastOut =
      Array.isArray(last?.dates) && last.dates.length === 2
        ? last.dates[1]
        : moment().add(14, "days");
    onLegsChange([
      ...legs,
      createEmptyLeg({
        dates: [lastOut, moment(lastOut).add(3, "days")],
      }),
    ]);
  };

  const removeCity = (index) => {
    if (legs.length <= 2) return;
    onLegsChange(legs.filter((_, i) => i !== index));
  };

  return (
    <div style={{ display: "grid", gap: 8 }}>
      {legs.map((leg, index) => {
        const prevCheckout =
          index > 0 &&
          Array.isArray(legs[index - 1].dates) &&
          legs[index - 1].dates.length === 2
            ? legs[index - 1].dates[1]
            : undefined;

        return (
          <div
            key={leg.key}
            className="bg-white hero-search-bar"
            style={{
              position: "relative",
              zIndex: 40 - index,
              borderRadius: 12,
              padding: 8,
              display: "grid",
              gridTemplateColumns: "1.5fr 1fr 1fr auto",
              alignItems: "stretch",
              gap: 5,
            }}
          >
            <div style={{ minWidth: 0, borderRight: "1px solid #e2e8f0" }}>
              <LegCityField
                label={`City ${index + 1}`}
                city={leg.city}
                usedCityIds={usedCityIds}
                onSelect={(city) => updateLeg(index, { city })}
              />
            </div>

            <LegDateRange
              dates={leg.dates}
              minDate={prevCheckout}
              disabled={index > 0 && !prevCheckout}
              onChange={(dates) => updateLeg(index, { dates })}
            />

            <div
              style={{
                display: "flex",
                alignItems: "center",
                justifyContent: "center",
                padding: "0 6px",
              }}
            >
              <button
                type="button"
                aria-label={`Remove city ${index + 1}`}
                disabled={legs.length <= 2}
                onClick={() => removeCity(index)}
                style={{
                  width: 36,
                  height: 36,
                  borderRadius: 8,
                  border: "1px solid #e2e8f0",
                  background: legs.length <= 2 ? "#f8fafc" : "#fff",
                  color: legs.length <= 2 ? "#94a3b8" : "#64748b",
                  cursor: legs.length <= 2 ? "not-allowed" : "pointer",
                  fontSize: 18,
                  lineHeight: 1,
                }}
              >
                ×
              </button>
            </div>
          </div>
        );
      })}

      <div
        className="bg-white hero-search-bar"
        style={{
          borderRadius: 12,
          padding: 8,
          display: "grid",
          gridTemplateColumns: "minmax(180px, 0.9fr) auto 1fr auto",
          alignItems: "stretch",
          gap: 8,
        }}
      >
        <div style={{ minWidth: 0, borderRight: "1px solid #e2e8f0" }}>
          <GuestSearch
            onGuestChange={onGuestChange}
            guestCounts={guestCounts}
            openSignal={guestOpenSignal}
          />
        </div>

        <button
          type="button"
          onClick={addCity}
          style={{
            alignSelf: "center",
            border: "1px dashed #c5d0e6",
            background: "#f8fafc",
            color: "#234adc",
            borderRadius: 9,
            padding: "12px 16px",
            fontSize: 13,
            fontWeight: 700,
            cursor: "pointer",
            whiteSpace: "nowrap",
          }}
        >
          + Add city
        </button>

        <div />

        <button
          type="button"
          onClick={onSearch}
          disabled={!canSearch}
          style={{
            minWidth: 210,
            minHeight: 52,
            borderRadius: 9,
            border: "none",
            fontSize: 13,
            fontWeight: 700,
            color: "#fff",
            background: !canSearch ? "#94a3b8" : "#234adc",
            cursor: !canSearch ? "not-allowed" : "pointer",
            padding: "0 18px",
            justifySelf: "end",
          }}
        >
          {ctaLabel}
        </button>
      </div>
    </div>
  );
}
