import React, { useState } from 'react';
import {
  Box,
  Typography,
  FormControl,
  RadioGroup,
  FormControlLabel,
  Radio,
  Paper,
  Divider,
  Button
} from '@mui/material';
import PickupDropDisabledLayout from './PickupDropDisabledLayout';
import DisabledStateLayout from './DisabledStateLayout';

const DisabledStateDemo = () => {
  const [selectedService, setSelectedService] = useState('');
  const [demoType, setDemoType] = useState('pickup_drop');

  const handleServiceChange = (serviceId) => {
    setSelectedService(serviceId);
  };

  const resetDemo = () => {
    setSelectedService('');
  };

  return (
    <Box sx={{ p: 4, backgroundColor: '#f5f5f5', minHeight: '100vh' }}>
      {/* Demo Controls */}
      <Paper elevation={3} sx={{ p: 3, mb: 4, borderRadius: 2 }}>
        <Typography variant="h5" fontWeight="bold" gutterBottom color="primary">
          Disabled State Layout Demo
        </Typography>
        
        <Typography variant="body1" color="text.secondary" paragraph>
          This demo shows how the disabled state layout works when no radio button/service is selected.
        </Typography>

        <Box sx={{ display: 'flex', gap: 3, alignItems: 'center', flexWrap: 'wrap' }}>
          <FormControl>
            <Typography variant="subtitle2" sx={{ mb: 1 }}>Demo Type:</Typography>
            <RadioGroup
              row
              value={demoType}
              onChange={(e) => setDemoType(e.target.value)}
            >
              <FormControlLabel 
                value="pickup_drop" 
                control={<Radio />} 
                label="Pickup/Drop Layout" 
              />
              <FormControlLabel 
                value="simple" 
                control={<Radio />} 
                label="Simple Layout" 
              />
            </RadioGroup>
          </FormControl>

          <Button 
            variant="outlined" 
            onClick={resetDemo}
            sx={{ ml: 'auto' }}
          >
            Reset Demo
          </Button>
        </Box>

        <Divider sx={{ my: 2 }} />
        
        <Typography variant="body2" color="primary.main">
          <strong>Current State:</strong> {selectedService ? `${selectedService} selected` : 'No service selected (disabled state)'}
        </Typography>
      </Paper>

      {/* Demo Content */}
      {demoType === 'pickup_drop' ? (
        <PickupDropDisabledLayout 
          selectedService={selectedService}
          onServiceChange={handleServiceChange}
        />
      ) : (
        <Paper elevation={2} sx={{ p: 3, borderRadius: 2 }}>
          <Typography variant="h6" gutterBottom>
            Simple Disabled State Layout
          </Typography>
          
          <Box sx={{ mb: 3 }}>
            <FormControl>
              <RadioGroup
                row
                value={selectedService}
                onChange={(e) => setSelectedService(e.target.value)}
              >
                <FormControlLabel 
                  value="service1" 
                  control={<Radio />} 
                  label="Service 1" 
                />
                <FormControlLabel 
                  value="service2" 
                  control={<Radio />} 
                  label="Service 2" 
                />
                <FormControlLabel 
                  value="service3" 
                  control={<Radio />} 
                  label="Service 3" 
                />
              </RadioGroup>
            </FormControl>
          </Box>

          {selectedService ? (
            <Box sx={{ 
              p: 3, 
              backgroundColor: 'success.light', 
              borderRadius: 2,
              textAlign: 'center'
            }}>
              <Typography variant="h6" color="success.dark">
                ✅ Service Selected: {selectedService}
              </Typography>
              <Typography variant="body2" color="success.dark">
                Form fields would be enabled here
              </Typography>
            </Box>
          ) : (
            <DisabledStateLayout 
              message="Please select a service from the radio buttons above to enable the form fields"
              showLocationFields={true}
              showTimeField={true}
              showDateField={true}
            />
          )}
        </Paper>
      )}

      {/* Instructions */}
      <Paper elevation={1} sx={{ mt: 4, p: 3, backgroundColor: '#e3f2fd', borderRadius: 2 }}>
        <Typography variant="h6" color="primary.main" gutterBottom>
          How to Use:
        </Typography>
        <Box component="ul" sx={{ pl: 2, color: 'text.secondary' }}>
          <li>When no radio button is selected, all form fields are disabled with visual feedback</li>
          <li>The disabled state shows a clear message explaining why fields are disabled</li>
          <li>Once a service is selected, the fields become enabled</li>
          <li>The layout is responsive and maintains proper alignment</li>
          <li>Visual cues (icons, colors, styling) indicate the disabled state clearly</li>
        </Box>
      </Paper>
    </Box>
  );
};

export default DisabledStateDemo; 