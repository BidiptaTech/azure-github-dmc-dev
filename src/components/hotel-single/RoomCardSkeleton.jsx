import React from "react";
import { Box, Skeleton, Paper } from "@mui/material";

const RoomCardSkeleton = () => {
  return (
    <Paper
      elevation={2}
      sx={{
        width: '100%',
        borderRadius: '16px',
        overflow: 'hidden',
        transition: 'transform 0.3s ease, box-shadow 0.3s ease',
      }}
    >
      {/* Header */}
      <Box
        sx={{
          background: 'linear-gradient(135deg, #3554D1 0%, #5073FB 100%)',
          padding: '10px 20px',
          color: 'white',
        }}
      >
        <Skeleton 
          variant="text" 
          width="60%" 
          height={32} 
          sx={{ bgcolor: 'rgba(255, 255, 255, 0.3)' }} 
        />
      </Box>

      {/* Content */}
      <Box sx={{ padding: '10px 20px 10px 20px' }}>
        <Box display="flex" justifyContent="space-between" alignItems="center">
          <Box>
            {/* Price */}
            <Box display="flex" alignItems="baseline" gap={1}>
              <Skeleton variant="text" width={120} height={36} />
              <Skeleton variant="text" width={40} height={20} />
            </Box>
            
            {/* Alternative prices */}
            <Skeleton variant="text" width={100} height={20} />
            <Skeleton variant="text" width={100} height={20} />
            
            {/* Tax info */}
            <Box display="flex" alignItems="center">
              <Skeleton variant="circular" width={20} height={20} sx={{ mr: 1 }} />
              <Skeleton variant="text" width={80} height={20} />
            </Box>
          </Box>

          {/* Button */}
          <Box sx={{ marginLeft: '5px' }}>
            <Skeleton variant="rounded" width={100} height={40} sx={{ borderRadius: '12px' }} />
          </Box>
        </Box>
      </Box>
    </Paper>
  );
};

export default RoomCardSkeleton; 