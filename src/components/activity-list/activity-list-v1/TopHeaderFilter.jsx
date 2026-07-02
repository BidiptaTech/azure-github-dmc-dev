import { useEffect, useState } from "react";

// Country mapping
const countryMap = {
  in: "India",
  SG: "Singapore",
};

const TopHeaderFilter = ({
  guides,
  Location,
  handleSort,
  sortOrder,
  filteredCount,
}) => {
  const [sortedGuides, setSortedGuides] = useState([]);

  useEffect(() => {
    console.log("Updated guides:", guides);
    console.log("Updated Location:", Location);
    setSortedGuides([...guides]); // Initialize sorted guides
  }, [guides, Location]);

  // Map Location array to country names
  const mappedCountries = Location?.map((loc) => countryMap[loc] || loc).join(
    ", "
  );

  return (
    <>
      <div className="row y-gap-10 items-center justify-between">
        <div className="col-auto">
          <div className="text-18">
            <span className="fw-500">{filteredCount} Guides</span> in{" "}
            {mappedCountries || "Unknown Location"}
          </div>
        </div>

        {/* Sort Button */}
        <div className="col-auto">
          <div className="row x-gap-20 y-gap-20">
            <div className="col-auto">
              <button
                className="button -blue-1 h-40 px-20 rounded-100 bg-blue-1-05 text-15 text-blue-1 d-flex align-items-center"
                onClick={handleSort}
              >
                <i
                  className={`icon-up-down text-14 mr-10 ${
                    sortOrder === "asc" ? "rotate-180" : ""
                  }`}
                />
                Sort by Price
              </button>
            </div>
          </div>
        </div>
      </div>
    </>
  );
};

export default TopHeaderFilter;
