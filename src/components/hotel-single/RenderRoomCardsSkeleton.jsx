import React from "react";
import { Box } from "@mui/material";
import { IoPersonSharp } from "react-icons/io5";
import RoomCardSkeleton from "./RoomCardSkeleton";

const RenderRoomCardsSkeleton = ({ count = 2, personCount = 1 }) => {
  return (
    <Box sx={{ width: '100%' }}>
      {/* Header with person count */}
      <Box
        display="flex"
        alignItems="center"
        gap={1}
        mt={2}
        mb={1}
        sx={{
          backgroundColor: '#e6f2ff',
          padding: '5px 5px',
          borderRadius: '8px',
          boxShadow: '0 2px 4px rgba(0,0,0,0.05)'
        }}
      >
        <Box display="flex" alignItems="center">
          {Array.from({ length: personCount }).map((_, iconIndex) => (
            <IoPersonSharp 
              key={iconIndex}
              size={20}
              style={{ 
                color: '#3554D1',
                marginRight: '2px'
              }}
            />
          ))}
        </Box>
      </Box>

      {/* Room Cards Grid */}
      <Box
        display="grid"
        gap={2}
      >
        {Array.from({ length: count }).map((_, index) => (
          <Box key={index} sx={{ width: '100%' }}>
            <RoomCardSkeleton />
          </Box>
        ))}
      </Box>
    </Box>
  );
};

export default RenderRoomCardsSkeleton; 