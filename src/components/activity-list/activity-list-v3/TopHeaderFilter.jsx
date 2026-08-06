import { useEffect, useState } from "react";

const countryMap = {
  in: "India",
  SG: "Singapore",
};

const TopHeaderFilter = ({
  Location = [],
  handleSort,
  sortOrder,
  vehicles = [],
  filteredCount,
}) => {
  const [sortedVehicles, setSortedVehicles] = useState([]);

  useEffect(() => {
    console.log("Updated vehicles:", vehicles);
    console.log("Updated Location:", Location);
    setSortedVehicles([...vehicles]); // Initialize sorted vehicles
  }, [vehicles, Location]);

  // Ensure Location is an array before calling map()
  const mappedCountries = Array.isArray(Location)
    ? Location.map((loc) => countryMap[loc] || loc).join(", ")
    : "Unknown Location";

  return (
    <>
      <div className="row y-gap-10 items-center justify-between">
        <div className="col-auto">
          <div className="text-18">
            <span className="fw-500">{filteredCount} Vehicles</span> in{" "}
            {mappedCountries}
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
