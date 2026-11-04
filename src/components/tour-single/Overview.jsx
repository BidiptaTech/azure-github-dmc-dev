
import React, { useState } from "react";
import { useSelector } from "react-redux";
import { FaRegFileAlt, FaStickyNote, FaGavel } from "react-icons/fa";

const MAX_WORDS = 100;
const COMPACT_MAX_WORDS = 40;

const Section = ({ title, icon: Icon, content, color, compact = false }) => {
  const [expanded, setExpanded] = useState(false);
  const wordLimit = compact ? COMPACT_MAX_WORDS : MAX_WORDS;

  const plainText = content?.replace(/<[^>]+>/g, "") || "";
  const words = plainText.trim().split(/\s+/).filter(Boolean);
  const shouldTruncate = words.length > wordLimit;

  const truncatedText = shouldTruncate
    ? words.slice(0, wordLimit).join(" ") + "..."
    : plainText;

  if (compact) {
    return (
      <div style={{ display: "flex", alignItems: "flex-start", gap: 6, marginBottom: 6 }}>
        <Icon style={{ fontSize: 11, marginTop: 2, flexShrink: 0 }} className={color} />
        <div style={{ minWidth: 0, flex: 1 }}>
          <h4
            style={{
              fontSize: 11,
              fontWeight: 600,
              color: "#1f2937",
              lineHeight: 1.2,
              margin: 0,
            }}
          >
            {title}
          </h4>
          <p
            style={{
              fontSize: 10,
              lineHeight: 1.35,
              color: "#4b5563",
              margin: "2px 0 0",
            }}
            dangerouslySetInnerHTML={{
              __html:
                expanded || !shouldTruncate
                  ? content || `<i>No ${title.toLowerCase()} available.</i>`
                  : truncatedText,
            }}
          />
          {shouldTruncate && (
            <button
              type="button"
              style={{
                fontSize: 10,
                fontWeight: 500,
                color: "#2563eb",
                marginTop: 2,
                padding: 0,
                border: "none",
                background: "none",
                cursor: "pointer",
              }}
              onClick={() => setExpanded((prev) => !prev)}
            >
              {expanded ? "See less" : "See more"}
            </button>
          )}
        </div>
      </div>
    );
  }

  return (
    <div className="flex items-start gap-4 mb-6">
      <Icon className={`${color} text-xl mt-1`} />
      <div>
        <h4 className="text-lg font-medium text-gray-800">{title}</h4>
        <p
          className="text-gray-700 text-sm mt-1"
          dangerouslySetInnerHTML={{
            __html:
              expanded || !shouldTruncate
                ? content || `<i>No ${title.toLowerCase()} available.</i>`
                : truncatedText,
          }}
        />
        {shouldTruncate && (
          <button
            className="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 transition-colors duration-200 focus:outline-none focus:underline"
            onClick={() => setExpanded((prev) => !prev)}
          >
            {expanded ? "See less" : "See more"}
          </button>
        )}
      </div>
    </div>
  );
};

const Overview = ({ compact = false }) => {
  const attractionDetails = useSelector(
    (state) => state.attractions.attractionDetails
  );

  return (
    <div
      className={`bg-white shadow-md ${compact ? "rounded-lg" : "rounded-2xl"}`}
      style={
        compact
          ? { padding: "8px 10px" }
          : { padding: "24px" }
      }
    >
      <h3
        style={
          compact
            ? {
                fontSize: 12,
                fontWeight: 600,
                margin: "0 0 6px",
                paddingBottom: 4,
                borderBottom: "1px solid #e5e7eb",
                lineHeight: 1.2,
              }
            : undefined
        }
        className={
          compact
            ? undefined
            : "text-2xl mb-6 pb-3 font-semibold border-b"
        }
      >
        Overview
      </h3>

      <Section
        title="Description"
        icon={FaRegFileAlt}
        content={attractionDetails?.description}
        color="text-blue-600"
        compact={compact}
      />
      <Section
        title="Remarks"
        icon={FaStickyNote}
        content={attractionDetails?.remarks}
        color="text-yellow-600"
        compact={compact}
      />
      <Section
        title="Terms & Conditions"
        icon={FaGavel}
        content={attractionDetails?.terms_conditions}
        color="text-red-600"
        compact={compact}
      />
    </div>
  );
};

export default Overview;
