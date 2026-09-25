import React, { useState } from 'react';
import {
  Box,
  Typography,
  FormControl,
  RadioGroup,
  FormControlLabel,
  Radio,
  Grid,
  Paper,
  Button
} from '@mui/material';
import DisabledStateLayout from './DisabledStateLayout';
// Import your existing components
// import LocationSearch from '../Local-Transport/LocationSearch';
// import Pickuptime from '../../activity-single/filter-box/Pickuptime';
// import DateSearch1 from '../../activity-list/common/DateSearch1';

const IntegrationExample = () => {
  const [selectedPort, setSelectedPort] = useState('');
  const [pickUpLocation, setPickUpLocation] = useState('');
  const [dropOffLocation, setDropOffLocation] = useState('');
  const [selectedTime, setSelectedTime] = useState('');
  const [selectedDate, setSelectedDate] = useState('');

  const handlePortChange = (event) => {
    setSelectedPort(event.target.value);
    // Reset form values when changing service
    setPickUpLocation('');
    setDropOffLocation('');
    setSelectedTime('');
    setSelectedDate('');
  };

  const handleReset = () => {
    setSelectedPort('');
    setPickUpLocation('');
    setDropOffLocation('');
    setSelectedTime('');
    setSelectedDate('');
  };

  return (
    <Box sx={{ p: 3 }}>
      <Typography variant="h4" gutterBottom>
        Transportation Service Selection
      </Typography>

      {/* Service Type Selection */}
      <Paper elevation={2} sx={{ p: 3, mb: 3 }}>
        <Typography variant="h6" gutterBottom>
          Select Service Type:
        </Typography>
        
        <FormControl component="fieldset">
          <RadioGroup
            row
            aria-label="port-selection"
            name="port-selection"
            value={selectedPort}
            onChange={handlePortChange}
          >
            <FormControlLabel 
              value="Entry Port" 
              control={<Radio />} 
              label="Entry Port (Airport to Hotel)" 
            />
            <FormControlLabel 
              value="Exit Port" 
              control={<Radio />} 
              label="Exit Port (Hotel to Airport)" 
            />
            <FormControlLabel 
              value="Point To Point" 
              control={<Radio />} 
              label="Point to Point Transfer" 
            />
            <FormControlLabel 
              value="Local Transfer" 
              control={<Radio />} 
              label="Local Transfer" 
            />
          </RadioGroup>
        </FormControl>

        <Button 
          variant="outlined" 
          size="small" 
          onClick={handleReset}
          sx={{ ml: 2 }}
        >
          Reset
        </Button>
      </Paper>

      {/* Configuration Section */}
      <Paper elevation={2} sx={{ p: 3 }}>
        <Typography variant="h6" gutterBottom>
          Service Configuration:
        </Typography>

        {selectedPort ? (
          // Show enabled form when service is selected
          <Box>
            <Typography variant="body1" color="success.main" sx={{ mb: 2 }}>
              ✅ {selectedPort} selected - Form fields are now enabled
            </Typography>
            
            <Grid container spacing={3}>
              {/* Location Fields */}
              <Grid item xs={12} md={6}>
                <Typography variant="subtitle2" gutterBottom>
                  Pickup Location:
                </Typography>
                <Box sx={{ 
                  p: 2, 
                  border: '1px solid #4caf50', 
                  borderRadius: 1,
                  backgroundColor: 'rgba(76, 175, 80, 0.05)'
                }}>
                  {/* Replace with your actual LocationSearch component */}
                  <Typography variant="body2" color="success.dark">
                    🟢 LocationSearch Component (Enabled)
                  </Typography>
                  <Typography variant="caption" display="block">
                    Replace this with: &lt;LocationSearch disabled={'{false}'} /&gt;
                  </Typography>
                </Box>
              </Grid>

              <Grid item xs={12} md={6}>
                <Typography variant="subtitle2" gutterBottom>
                  Dropoff Location:
                </Typography>
                <Box sx={{ 
                  p: 2, 
                  border: '1px solid #4caf50', 
                  borderRadius: 1,
                  backgroundColor: 'rgba(76, 175, 80, 0.05)'
                }}>
                  <Typography variant="body2" color="success.dark">
                    🟢 LocationSearch Component (Enabled)
                  </Typography>
                  <Typography variant="caption" display="block">
                    Replace this with: &lt;LocationSearch disabled={'{false}'} /&gt;
                  </Typography>
                </Box>
              </Grid>

              {/* Time Selection */}
              <Grid item xs={12} md={6}>
                <Typography variant="subtitle2" gutterBottom>
                  Select Time:
                </Typography>
                <Box sx={{ 
                  p: 2, 
                  border: '1px solid #4caf50', 
                  borderRadius: 1,
                  backgroundColor: 'rgba(76, 175, 80, 0.05)'
                }}>
                  <Typography variant="body2" color="success.dark">
                    🟢 Pickuptime Component (Enabled)
                  </Typography>
                  <Typography variant="caption" display="block">
                    Replace this with: &lt;Pickuptime disabled={'{false}'} /&gt;
                  </Typography>
                </Box>
              </Grid>

              {/* Date Selection */}
              <Grid item xs={12} md={6}>
                <Typography variant="subtitle2" gutterBottom>
                  Select Date:
                </Typography>
                <Box sx={{ 
                  p: 2, 
                  border: '1px solid #4caf50', 
                  borderRadius: 1,
                  backgroundColor: 'rgba(76, 175, 80, 0.05)'
                }}>
                  <Typography variant="body2" color="success.dark">
                    🟢 DateSearch Component (Enabled)
                  </Typography>
                  <Typography variant="caption" display="block">
                    Replace this with: &lt;DateSearch1 disabled={'{false}'} /&gt;
                  </Typography>
                </Box>
              </Grid>
            </Grid>
          </Box>
        ) : (
          // Show disabled layout when no service is selected
          <DisabledStateLayout 
            message="Please select a service type above to configure pickup location, dropoff location, time, and date"
            showLocationFields={true}
            showTimeField={true}
            showDateField={true}
          />
        )}
      </Paper>

      {/* Integration Code Example */}
      <Paper elevation={1} sx={{ mt: 3, p: 3, backgroundColor: '#f5f5f5' }}>
        <Typography variant="h6" gutterBottom>
          Integration Code Example:
        </Typography>
        
        <Box component="pre" sx={{ 
          backgroundColor: '#263238', 
          color: '#fff', 
          p: 2, 
          borderRadius: 1,
          fontSize: '0.8rem',
          overflow: 'auto'
        }}>
{`// In your existing component:
import DisabledStateLayout from './Common/DisabledStateLayout';

const YourComponent = () => {
  const [selectedPort, setSelectedPort] = useState('');
  
  return (
    <Box>
      {/* Your radio button selection */}
      <RadioGroup value={selectedPort} onChange={handleChange}>
        <FormControlLabel value="Entry Port" control={<Radio />} label="Entry Port" />
        <FormControlLabel value="Exit Port" control={<Radio />} label="Exit Port" />
      </RadioGroup>
      
      {/* Conditional rendering */}
      {selectedPort ? (
        <Box>
          <LocationSearch disabled={false} />
          <Pickuptime disabled={false} />
          <DateSearch1 disabled={false} />
        </Box>
      ) : (
        <DisabledStateLayout 
          message="Please select a service type to continue"
          showLocationFields={true}
          showTimeField={true}
          showDateField={true}
        />
      )}
    </Box>
  );
};`}
        </Box>
      </Paper>
    </Box>
  );
};

export default IntegrationExample; 