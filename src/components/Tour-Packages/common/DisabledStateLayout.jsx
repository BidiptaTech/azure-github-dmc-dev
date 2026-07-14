import React from 'react';
import {
  Grid,
  Box,
  Typography,
  TextField,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Paper,
  Card,
  CardContent,
  styled
} from '@mui/material';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
import BlockIcon from '@mui/icons-material/Block';

// Styled components for disabled state
const DisabledCard = styled(Card)(({ theme }) => ({
  backgroundColor: '#f8f9fa',
  border: '2px dashed #dee2e6',
  opacity: 0.6,
  '& *': {
    color: '#6c757d !important',
    cursor: 'not-allowed',
  }
}));

const DisabledTextField = styled(TextField)(({ theme }) => ({
  '& .MuiInputBase-root': {
    backgroundColor: '#f8f9fa',
    '& fieldset': {
      borderColor: '#dee2e6',
    },
  },
  '& .MuiInputBase-input': {
    color: '#6c757d',
    cursor: 'not-allowed',
  },
  '& .MuiInputLabel-root': {
    color: '#6c757d',
  }
}));

const DisabledFormControl = styled(FormControl)(({ theme }) => ({
  '& .MuiInputBase-root': {
    backgroundColor: '#f8f9fa',
    '& fieldset': {
      borderColor: '#dee2e6',
    },
  },
  '& .MuiSelect-select': {
    color: '#6c757d',
    cursor: 'not-allowed',
  },
  '& .MuiInputLabel-root': {
    color: '#6c757d',
  }
}));

const DisabledStateLayout = ({ 
  message = "Please select a service type to enable location, time, and date selection",
  showLocationFields = true,
  showTimeField = true,
  showDateField = true
}) => {
  return (
    <DisabledCard elevation={1}>
      <CardContent sx={{ p: 3 }}>
        {/* Header Message */}
        <Box sx={{ 
          display: 'flex', 
          alignItems: 'center', 
          gap: 2, 
          mb: 3,
          p: 2,
          backgroundColor: 'rgba(108, 117, 125, 0.1)',
          borderRadius: 2,
          border: '1px solid #dee2e6'
        }}>
          <BlockIcon sx={{ fontSize: 24, color: '#6c757d' }} />
          <Typography 
            variant="body1" 
            sx={{ 
              color: '#6c757d',
              fontWeight: 500,
              fontStyle: 'italic'
            }}
          >
            {message}
          </Typography>
        </Box>

        <Grid container spacing={3}>
          {/* Location Fields */}
          {showLocationFields && (
            <>
              {/* Pickup Location */}
              <Grid item xs={12} md={6}>
                <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                  <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                    <LocationOnIcon sx={{ mr: 1, color: '#6c757d', fontSize: 20 }} />
                    <Typography 
                      variant="subtitle2" 
                      fontWeight="600"
                      color="#6c757d"
                    >
                      Pickup Location
                    </Typography>
                  </Box>
                  <Box sx={{ minHeight: '56px', display: 'flex', alignItems: 'center' }}>
                    <DisabledTextField
                      fullWidth
                      placeholder="Select pickup location"
                      disabled
                      variant="outlined"
                      InputProps={{
                        startAdornment: <LocationOnIcon sx={{ mr: 1, color: '#6c757d' }} />,
                      }}
                    />
                  </Box>
                </Box>
              </Grid>

              {/* Dropoff Location */}
              <Grid item xs={12} md={6}>
                <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                  <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                    <LocationOnIcon sx={{ mr: 1, color: '#6c757d', fontSize: 20 }} />
                    <Typography 
                      variant="subtitle2" 
                      fontWeight="600"
                      color="#6c757d"
                    >
                      Dropoff Location
                    </Typography>
                  </Box>
                  <Box sx={{ minHeight: '56px', display: 'flex', alignItems: 'center' }}>
                    <DisabledTextField
                      fullWidth
                      placeholder="Select dropoff location"
                      disabled
                      variant="outlined"
                      InputProps={{
                        startAdornment: <LocationOnIcon sx={{ mr: 1, color: '#6c757d' }} />,
                      }}
                    />
                  </Box>
                </Box>
              </Grid>
            </>
          )}

          {/* Time Selection */}
          {showTimeField && (
            <Grid item xs={12} md={showLocationFields ? 6 : 4}>
              <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                  <AccessTimeIcon sx={{ mr: 1, color: '#6c757d', fontSize: 20 }} />
                  <Typography 
                    variant="subtitle2" 
                    fontWeight="600"
                    color="#6c757d"
                  >
                    Select Time
                  </Typography>
                </Box>
                <Box sx={{ minHeight: '56px', display: 'flex', alignItems: 'center' }}>
                  <DisabledFormControl fullWidth>
                    <InputLabel sx={{ color: '#6c757d' }}>Select time</InputLabel>
                    <Select
                      disabled
                      value=""
                      label="Select time"
                      startAdornment={<AccessTimeIcon sx={{ mr: 1, color: '#6c757d' }} />}
                    >
                      <MenuItem value="" disabled>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                          <AccessTimeIcon sx={{ color: '#6c757d' }} />
                          <Typography sx={{ color: '#6c757d' }}>Please select service first</Typography>
                        </Box>
                      </MenuItem>
                    </Select>
                  </DisabledFormControl>
                </Box>
              </Box>
            </Grid>
          )}

          {/* Date Selection */}
          {showDateField && (
            <Grid item xs={12} md={showLocationFields ? 6 : 4}>
              <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                  <CalendarTodayIcon sx={{ mr: 1, color: '#6c757d', fontSize: 20 }} />
                  <Typography 
                    variant="subtitle2" 
                    fontWeight="600"
                    color="#6c757d"
                  >
                    Select Date
                  </Typography>
                </Box>
                <Box sx={{ minHeight: '56px', display: 'flex', alignItems: 'center' }}>
                  <DisabledTextField
                    fullWidth
                    placeholder="Select date"
                    disabled
                    variant="outlined"
                    type="date"
                    InputProps={{
                      startAdornment: <CalendarTodayIcon sx={{ mr: 1, color: '#6c757d' }} />,
                    }}
                    InputLabelProps={{
                      shrink: true,
                    }}
                  />
                </Box>
              </Box>
            </Grid>
          )}
        </Grid>

        {/* Footer message */}
        <Box sx={{ 
          mt: 3,
          p: 2,
          backgroundColor: 'rgba(108, 117, 125, 0.05)',
          borderRadius: 1,
          textAlign: 'center'
        }}>
          <Typography 
            variant="caption" 
            sx={{ 
              color: '#6c757d',
              fontStyle: 'italic'
            }}
          >
            All fields will be enabled once you select a service option above
          </Typography>
        </Box>
      </CardContent>
    </DisabledCard>
  );
};

export default DisabledStateLayout; 