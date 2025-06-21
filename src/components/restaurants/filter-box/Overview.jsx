// import React from "react";
// import { useDispatch, useSelector } from "react-redux";
// import { useParams } from "react-router-dom";
// import { fetchRestaurantsDetails } from "@/slice/restaurant/RestaurantsSlice";

// const Overview = () => {
//   // Fetch attraction details from Redux
//   const restaurantsDetails = useSelector(
//     (state) => state.restaurants.selectedRestaurant
//   );

//   return (
//     <>
//       <div className="row x-gap-40 y-gap-40">
//         <div className="col-12">
//           <h3 className="text-22 fw-500">Overview</h3>
//           <p
//             className="text-dark-1 text-15 mt-20"
//             dangerouslySetInnerHTML={{
//               __html:
//                 restaurantsDetails?.description || "No description available.",
//             }}
//           />
//         </div>
//       </div>
//     </>
//   );
// };

// export default Overview;


import React, { useState } from "react";
import { useSelector } from "react-redux";
import { FaRegFileAlt, FaStickyNote, FaGavel } from "react-icons/fa";

const MAX_WORDS = 200;

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
            __html: expanded || !shouldTruncate
              ? content || `<i>No ${title.toLowerCase()} available.</i>`
              : truncatedText,
          }}
        />
        {shouldTruncate && (
          <button
            className="text-blue-600 text-sm mt-2 underline"
            onClick={() => setExpanded((prev) => !prev)}
          >
            {expanded ? "See less" : "See more"}
          </button>
        )}
      </div>
    </div>
  );
};

const Overview = () => {
  const restaurantsDetails = useSelector(
    (state) => state.restaurants.selectedRestaurant
  );

  return (
    <div className="bg-white shadow-md rounded-2xl p-6">
      <h3 className="text-2xl font-semibold mb-6 border-b pb-3">Overview</h3>

      <Section
        title="Description"
        icon={FaRegFileAlt}
        content={restaurantsDetails?.description}
        color="text-blue-600"
      />
      <Section
        title="Remarks"
        icon={FaStickyNote}
        content={restaurantsDetails?.remarks}
        color="text-yellow-600"
      />
      <Section
        title="Terms & Conditions"
        icon={FaGavel}
        content={restaurantsDetails?.terms_conditions}
        color="text-red-600"
      />
    </div>
  );
};

export default Overview;
