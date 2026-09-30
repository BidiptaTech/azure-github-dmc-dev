import { toCityOnly } from "@/utils/locationFormat";
import { formatChipDates } from "@/utils/cityWiseDates";

const CityServiceChips = ({
  items = [],
  onCitySelect,
  label = "Cities:",
  variant = "default",
}) => {
  if (!items.length) return null;

  const isHero = variant === "hero";

  return (
    <div
      className={
        isHero
          ? "hotel-list-hero__pills"
          : "d-flex flex-wrap items-center gap-2 mt-20 mb-10"
      }
    >
      {!isHero && <span className="text-14 text-light-1 mr-5">{label}</span>}
      {items.map((entry) => (
        <button
          key={entry.city}
          type="button"
          onClick={() => onCitySelect(entry)}
          className={
            isHero
              ? `hotel-list-city-pill${entry.active ? " is-active" : ""}${
                  entry.booked ? " is-booked" : ""
                }`
              : "d-inline-flex align-items-center border-0 rounded-100 px-15 py-8 cursor-pointer"
          }
          style={
            isHero
              ? undefined
              : {
                  background: entry.active
                    ? "#3554d1"
                    : entry.booked
                      ? "#e8f5e9"
                      : "#f7f8fc",
                  color: entry.active
                    ? "#fff"
                    : entry.booked
                      ? "#2e7d32"
                      : "#1a1a1a",
                  border: entry.active
                    ? "1px solid #3554d1"
                    : entry.booked
                      ? "1px solid #a5d6a7"
                      : "1px solid #e4e7f1",
                  fontWeight: 600,
                  fontSize: "12px",
                  transition: "all 0.2s ease",
                }
          }
        >
          {isHero ? (
            <i className="icon-location-2 text-14" />
          ) : (
            entry.booked && (
              <i
                className="icon-check text-12 mr-5"
                style={{ color: entry.active ? "#fff" : "#2e7d32" }}
              />
            )
          )}
          <span>{toCityOnly(entry.city)}</span>
          <span
            className={isHero ? "hotel-list-city-pill__dates" : "ml-8"}
            style={
              isHero
                ? undefined
                : {
                    opacity: entry.active ? 0.9 : 0.65,
                    fontWeight: 500,
                  }
            }
          >
            {formatChipDates(entry)}
          </span>
        </button>
      ))}
    </div>
  );
};

export default CityServiceChips;
