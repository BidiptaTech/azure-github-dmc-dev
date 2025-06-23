import React from "react";
import { Box, Container } from "@mui/material";
import EntryPortSearchZone from "./EntryPortSearchZone";
import ExitPortSearchZone from "./ExitPortSearchZone";

const CombinedSearchLocationZone = ({ Location, portType, portType1 }) => {
  // Determine which components to show based on portType and portType1
  const showEntryPort = portType === "Entry Port";
  const showExitPort = portType1 === "Exit Port";

  return (
    <Box sx={{ width: '100%', py: 0, px: 0 }}>
      {/* Render Entry Port component if portType is "Entry Port" */}
      {showEntryPort && (
        <EntryPortSearchZone 
          Location={Location} 
          portType={portType}
        />
      )}
      
      {/* Render Exit Port component if portType1 is "Exit Port" */}
      {showExitPort && (
        <ExitPortSearchZone 
          Location={Location} 
          portType={portType1}
        />
      )}
    </Box>
  );
};

export default CombinedSearchLocationZone; 