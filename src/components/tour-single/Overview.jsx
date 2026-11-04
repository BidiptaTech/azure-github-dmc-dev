
import React, { useState } from "react";
import { useSelector } from "react-redux";
import { FaRegFileAlt, FaStickyNote, FaGavel } from "react-icons/fa";

const MAX_WORDS = 100;

const Section = ({ title, icon: Icon, content, color }) => {
  const [expanded, setExpanded] = useState(false);

  const plainText = content?.replace(/<[^>]+>/g, "") || "";
  const words = plainText.trim().split(/\s+/);
  const shouldTruncate = words.length > MAX_WORDS;

  const truncatedText = shouldTruncate
    ? words.slice(0, MAX_WORDS).join(" ") + "..."
    : plainText;

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
      className={`bg-white shadow-md rounded-2xl ${compact ? "p-4" : "p-6"}`}
    >
      <h3
        className={`${
          compact ? "text-xl mb-4" : "text-2xl mb-6"
        } font-semibold border-b pb-3`}
      >
        Overview
      </h3>

      <Section
        title="Description"
        icon={FaRegFileAlt}
        content={attractionDetails?.description}
        color="text-blue-600"
      />
      <Section
        title="Remarks"
        icon={FaStickyNote}
        content={attractionDetails?.remarks}
        color="text-yellow-600"
      />
      <Section
        title="Terms & Conditions"
        icon={FaGavel}
        content={attractionDetails?.terms_conditions}
        color="text-red-600"
      />
    </div>
  );
};

export default Overview;
