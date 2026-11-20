import React, { useMemo } from "react";
import DatePicker, { DateObject } from "react-multi-date-picker";
import {
  staysAllDated,
  toggleReturnStay,
  updateStayDates,
  toDateObject,
  getPreviousStayCity,
} from "@/utils/cityWiseDates";
import { toCityOnly } from "@/utils/locationFormat";
import "@/styles/stay-dates-per-city.scss";

const toDdMmYyyy = (value) => {
  const date = toDateObject(value);
  return date ? date.format("DD/MM/YYYY") : "";
};

const formatCityLabel = (stay) => {
  if (stay?.city) return stay.city;
  const name = stay?.cityName || "";
  const country = stay?.country;
  return country ? `${name} (${country})` : name;
};

const shortCity = (value) => toCityOnly(value) || value || "";

const StayDateField = ({
  label,
  value,
  onChange,
  locked,
  placeholder = "dd-mm-yyyy",
}) => {
  const dateValue = useMemo(() => toDateObject(value), [value]);

  return (
    <div className="stay-dates-row__field">
      <span className="stay-dates-row__label">{label}</span>
      <div className="stay-dates-row__date-box">
        <DatePicker
          value={dateValue || null}
          onChange={(date) => onChange(date)}
          format="DD-MM-YYYY"
          editable={false}
          disabled={locked}
          placeholder={placeholder}
          inputClass="stay-dates-input"
          containerClassName="stay-dates-datepicker"
          calendarPosition="bottom-left"
          portal
          portalTarget={typeof document !== "undefined" ? document.body : undefined}
          minDate={new DateObject()}
        />
      </div>
    </div>
  );
};

const StayDatesPerCity = ({
  stays = [],
  onChange,
  locked = false,
  className = "",
}) => {
  const list = Array.isArray(stays) ? stays : [];
  const allDated = staysAllDated(list);
  const datedCount = list.filter(
    (stay) => stay?.checkIn && stay?.checkOut
  ).length;

  const hasReturnEnabled = (stayKey) =>
    list.some((stay) => stay.isReturn && stay.sourceStayKey === stayKey);

  const canToggleReturn = (stay, index) => {
    if (stay.isReturn) return false;
    return list
      .slice(index + 1)
      .some((later) => {
        if (later.isReturn) return false;
        const laterName = shortCity(later.cityName || later.city);
        const stayName = shortCity(stay.cityName || stay.city);
        if (stay.city_id && later.city_id) {
          return String(stay.city_id) !== String(later.city_id);
        }
        return laterName && stayName && laterName !== stayName;
      });
  };

  const handleToggleReturn = (stayKey, enabled) => {
    if (locked || typeof onChange !== "function") return;
    onChange(toggleReturnStay(list, stayKey, enabled));
  };

  const patchStayDate = (stayKey, field, dateObj) => {
    if (locked || typeof onChange !== "function") return;
    const stay = list.find((item) => item.stayKey === stayKey);
    if (!stay) return;

    const nextValue = dateObj ? toDdMmYyyy(dateObj) : "";
    const checkIn = field === "checkIn" ? nextValue : stay.checkIn || "";
    const checkOut = field === "checkOut" ? nextValue : stay.checkOut || "";
    onChange(updateStayDates(list, stayKey, checkIn, checkOut));
  };

  if (!list.length) return null;

  return (
    <section
      className={`stay-dates-panel${locked ? " is-locked" : ""}${
        className ? ` ${className}` : ""
      }`}
      aria-label="Stay dates per city"
    >
      <div className="stay-dates-panel__header">
        <h3 className="stay-dates-panel__title">Stay dates (per city)</h3>
        <span
          className={`stay-dates-panel__badge${allDated ? " is-ready" : ""}`}
        >
          {allDated ? `${datedCount}/${list.length} dated` : "Dates required"}
        </span>
      </div>
      <p className="stay-dates-panel__hint">
        Each stay shows where you arrive from. Use Return to come back to a
        city after visiting another (e.g. Batam → Singapore) — not the same
        city twice in a row.
      </p>

      <div className="stay-dates-rows">
        {list.map((stay, index) => {
          const returnOn = hasReturnEnabled(stay.stayKey);
          const showReturnToggle = canToggleReturn(stay, index);
          const cityLabel = formatCityLabel(stay);
          const fromCity = getPreviousStayCity(list, stay.stayKey);
          const toCity = shortCity(stay.cityName || stay.city || cityLabel);

          return (
            <div className="stay-dates-row" key={stay.stayKey}>
              {index > 0 ? (
                <div className="stay-dates-row__route-wrap">
                  <span className="stay-dates-row__label">Travel leg</span>
                  <div
                    className={`stay-dates-row__route${
                      stay.isReturn ? " is-return" : ""
                    }`}
                  >
                    <span className="stay-dates-row__route-from">
                      {shortCity(fromCity) || "Previous"}
                    </span>
                    <span className="stay-dates-row__route-arrow" aria-hidden>
                      →
                    </span>
                    <span className="stay-dates-row__route-to">{toCity}</span>
                    {stay.isReturn && (
                      <span className="stay-dates-row__return-badge">
                        Return stay
                      </span>
                    )}
                  </div>
                </div>
              ) : (
                <div className="stay-dates-row__city-wrap">
                  <span className="stay-dates-row__label">Start city</span>
                  <div className="stay-dates-row__city is-primary">
                    <span>{cityLabel || toCity}</span>
                  </div>
                </div>
              )}

              <StayDateField
                label="Stay from"
                value={stay.checkIn}
                locked={locked}
                onChange={(date) =>
                  patchStayDate(stay.stayKey, "checkIn", date)
                }
              />

              <StayDateField
                label="Stay until"
                value={stay.checkOut}
                locked={locked}
                onChange={(date) =>
                  patchStayDate(stay.stayKey, "checkOut", date)
                }
              />

              <div className="stay-dates-row__return">
                <span className="stay-dates-row__label">Return later</span>
                {stay.isReturn ? (
                  <div className="stay-dates-row__return-meta">
                    <span className="stay-dates-row__return-text is-return-label">
                      {fromCity
                        ? `Back from ${shortCity(fromCity)}`
                        : "Return visit"}
                    </span>
                  </div>
                ) : showReturnToggle ? (
                  <div className="stay-dates-row__return-meta">
                    <button
                      type="button"
                      className={`stay-dates-switch${returnOn ? " is-on" : ""}`}
                      aria-pressed={returnOn}
                      aria-label={`Return to ${toCity} after later cities`}
                      disabled={locked}
                      onClick={() =>
                        handleToggleReturn(stay.stayKey, !returnOn)
                      }
                    >
                      <span className="stay-dates-switch__knob" />
                    </button>
                    <span className="stay-dates-row__return-text">
                      Come back after next cities
                    </span>
                  </div>
                ) : (
                  <div className="stay-dates-row__return-meta">
                    <span className="stay-dates-row__return-text">
                      Add another city first
                    </span>
                  </div>
                )}
              </div>
            </div>
          );
        })}
      </div>

      {!allDated && (
        <div className="stay-dates-panel__warning">
          Set a date range for every stay (including Return stays) before Hotel
          and other services unlock.
        </div>
      )}
    </section>
  );
};

export default StayDatesPerCity;
