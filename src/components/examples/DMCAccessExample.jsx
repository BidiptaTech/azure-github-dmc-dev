import React, { useEffect } from 'react';
import { useSelector } from 'react-redux';
import { selectDmcId, selectSelectedDmcData } from '../../slice/dmc/dmcSlice';
import { Box, Typography, Paper, Chip } from '@mui/material';
import { Business as BusinessIcon } from '@mui/icons-material';

// Example component showing how to access selected DMC from any component
const DMCAccessExample = () => {
  // Access selected DMC ID from Redux store
  const dmcId = useSelector(selectDmcId);
  const selectedDmcData = useSelector(selectSelectedDmcData);

  // Console log when DMC ID changes
  useEffect(() => {
    console.log('🔍 DMC ID from Redux state:', dmcId === null ? 'null (dmcId was 0)' : dmcId);
    console.log('🔍 DMC Data from Redux state:', selectedDmcData);
  }, [dmcId, selectedDmcData]);

  return (
    <Box sx={{ p: 3 }}>
      <Typography variant="h6" gutterBottom>
        Selected DMC Information (Available from any component)
      </Typography>
      
      {dmcId !== null ? (
        <Paper elevation={2} sx={{ p: 3, borderRadius: 2 }}>
          <Box display="flex" alignItems="center" gap={2} mb={2}>
            <BusinessIcon sx={{ color: '#667eea' }} />
            <Typography variant="h6" color="#667eea">
              Selected DMC ID: {dmcId}
            </Typography>
          </Box>
          
          {selectedDmcData && (
            <Box>
              <Typography variant="subtitle1" gutterBottom>
                <strong>Company:</strong> {selectedDmcData.name}
              </Typography>
              
              <Typography variant="body2" gutterBottom>
                <strong>Location:</strong> {selectedDmcData.location}
              </Typography>
              
              <Typography variant="body2" gutterBottom>
                <strong>Rating:</strong> {selectedDmcData.rating}/5
              </Typography>
              
              <Chip 
                label={`DMC ID: ${dmcId}`} 
                size="small" 
                sx={{ 
                  backgroundColor: '#667eea', 
                  color: 'white',
                  mt: 1
                }} 
              />
            </Box>
          )}
        </Paper>
      ) : (
        <Paper elevation={2} sx={{ p: 3, borderRadius: 2, textAlign: 'center' }}>
          <Typography variant="body1" color="text.secondary">
            {dmcId === null ? 'DMC ID is null (original dmcId was 0)' : 'No DMC selected'}
          </Typography>
        </Paper>
      )}
    </Box>
  );
};

export default DMCAccessExample; 