import React from "react";
import EntryPortSearchZone from "./EntryPortSearchZone";
import ExitPortSearchZone from "./ExitPortSearchZone";


const CombinedSearchLocationZone = ({ Location, portType, portType1 }) => {
  // Determine which components to show based on portType and portType1
  const showEntryPort = portType === "Entry Port";
  const showExitPort = portType1 === "Exit Port";

  return (
    <div className="mainSearch -col-2 bg-white px-10 py-10 lg:px-20 lg:pt-5 lg:pb-20 rounded-4 mt-30">
      <div className="combined-search-container">
        {/* Render Entry Port component if portType is "Entry Port" */}
        {showEntryPort && <EntryPortSearchZone Location={Location} portType={portType}/>}
        
        {/* Render Exit Port component if portType1 is "Exit Port" */}
        {showExitPort && <ExitPortSearchZone Location={Location} portType={portType1}/>}
      </div>

      <style jsx>{`
        .combined-search-container {
          width: 100%;
        }
      `}</style>
    </div>
  );
};

export default CombinedSearchLocationZone; 