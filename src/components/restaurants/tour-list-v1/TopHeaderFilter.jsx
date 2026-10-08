import { useState } from "react";


const TopHeaderFilter = ({ onSort }) => {
  const [sortOrder, setSortOrder] = useState("asc");

  const handleSort = () => {
    const newOrder = sortOrder === "asc" ? "desc" : "asc";
    setSortOrder(newOrder);
    onSort(newOrder); // Notify parent component
  };

  return (
    <div className="row y-gap-10 items-center justify-between">
      <div className="col-auto">
        <div className="text-18">
          {/* <span className="fw-500">3,269 properties</span> in Europe */}
        </div>
      </div>

      <div className="col-auto">
        <div className="row x-gap-20 y-gap-20">
          <div className="col-auto">
            <button
              className="button -blue-1 h-40 px-20 rounded-100 bg-blue-1-05 text-15 text-blue-1"
              onClick={handleSort}
            >
              <i className="icon-up-down text-14 mr-10" />
              Sort by Price ({sortOrder === "asc" ? "Low to High" : "High to Low"})
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TopHeaderFilter;
