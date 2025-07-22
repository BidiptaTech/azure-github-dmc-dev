import React, { useState } from "react";
import MultiSelectDropdown from "../common/MultiSelectDropdown";

const MultiSelectExample = () => {
  const [selectedStars, setSelectedStars] = useState([]);

  const starOptions = [
    { value: "3", label: "3 Star" },
    { value: "4", label: "4 Star" },
    { value: "5", label: "5 Star" },
  ];

  const amenityOptions = [
    { value: "wifi", label: "WiFi" },
    { value: "pool", label: "Swimming Pool" },
    { value: "spa", label: "Spa" },
    { value: "gym", label: "Gym" },
    { value: "restaurant", label: "Restaurant" },
  ];

  const handleStarChange = (values) => {
    setSelectedStars(values);
    console.log("Selected stars:", values);
  };

  return (
    <div className="container mt-40 mb-40">
      <h2 className="text-22 fw-500 mb-20">Multi-Select Dropdown Examples</h2>

      <div className="row y-gap-20">
        <div className="col-md-6">
          <MultiSelectDropdown
            label="Star Category"
            placeholder="Select Star Categories"
            options={starOptions}
            onChange={handleStarChange}
          />
          <div className="mt-10">
            Selected values: {selectedStars.join(", ")}
          </div>
        </div>

        <div className="col-md-6">
          <MultiSelectDropdown
            label="Hotel Amenities"
            placeholder="Select Amenities"
            options={amenityOptions}
            onChange={(values) => console.log("Selected amenities:", values)}
          />
        </div>
      </div>
    </div>
  );
};

export default MultiSelectExample;
