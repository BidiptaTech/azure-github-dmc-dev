import React from "react";
import CombinedSearchLocationZone from "./CombinedSearchLocationZone";

const SearchLocationZone = ({ Location, portType, portType1 }) => {
  // This component now simply forwards the props to the CombinedSearchLocationZone component
  return <CombinedSearchLocationZone Location={Location} portType={portType} portType1={portType1} />;
};

export default SearchLocationZone; 