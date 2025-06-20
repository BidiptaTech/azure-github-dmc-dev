import React, { useState } from 'react';
import { 
  Paper, 
  Typography, 
  Box, 
  Stepper, 
  Step, 
  StepLabel, 
  StepContent, 
  Button, 
  Alert
} from '@mui/material';
import MapIcon from '@mui/icons-material/Map';

const PackageItinerary = ({ packageData }) => {
  const [activeStep, setActiveStep] = useState(0);
  
  // If there's no specific itinerary data, create a simple one based on duration
  const generateDefaultItinerary = (days) => {
    const itinerary = [];
    for (let i = 0; i < days; i++) {
      itinerary.push({
        title: `Day ${i + 1} - ${i === 0 ? 'Arrival' : i === days - 1 ? 'Departure' : 'Exploration'}`,
        description: `Day ${i + 1} of your ${packageData.destination} adventure.`
      });
    }
    return itinerary;
  };
  
  // Use provided itinerary or generate a default one based on duration
  const itinerary = packageData.itinerary || generateDefaultItinerary(packageData.duration_days);
  
  const handleNext = () => {
    setActiveStep((prevActiveStep) => prevActiveStep + 1);
  };

  const handleBack = () => {
    setActiveStep((prevActiveStep) => prevActiveStep - 1);
  };

  const handleReset = () => {
    setActiveStep(0);
  };
  
  if (!itinerary || itinerary.length === 0) {
    return (
      <Paper elevation={1} sx={{ p: 3, borderRadius: '12px' }}>
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
          <MapIcon sx={{ mr: 1 }} />
          <Typography variant="h5" fontWeight="bold">
            Itinerary
          </Typography>
        </Box>
        <Alert severity="info">
          No detailed itinerary is available for this package at the moment.
        </Alert>
      </Paper>
    );
  }
  
  return (
    <Paper elevation={1} sx={{ p: 3, borderRadius: '12px' }}>
      <Box sx={{ display: 'flex', alignItems: 'center', mb: 3 }}>
        <MapIcon sx={{ mr: 1 }} />
        <Typography variant="h5" fontWeight="bold">
          {packageData.duration_days}-Day Itinerary
        </Typography>
      </Box>
      
      <Stepper activeStep={activeStep} orientation="vertical">
        {itinerary.map((day, index) => (
          <Step key={index}>
            <StepLabel>
              <Typography variant="subtitle1" fontWeight="medium">
                {day.title || `Day ${index + 1}`}
              </Typography>
            </StepLabel>
            <StepContent>
              <Typography variant="body1" sx={{ mb: 2 }}>
                {day.description}
              </Typography>
              <Box sx={{ mb: 2 }}>
                <div>
                  <Button
                    variant="contained"
                    onClick={handleNext}
                    sx={{ mt: 1, mr: 1 }}
                  >
                    {index === itinerary.length - 1 ? 'Finish' : 'Continue'}
                  </Button>
                  <Button
                    disabled={index === 0}
                    onClick={handleBack}
                    sx={{ mt: 1, mr: 1 }}
                  >
                    Back
                  </Button>
                </div>
              </Box>
            </StepContent>
          </Step>
        ))}
      </Stepper>
      
      {activeStep === itinerary.length && (
        <Paper square elevation={0} sx={{ p: 3 }}>
          <Typography variant="body1" sx={{ mb: 2 }}>
            All steps completed - you've finished exploring the itinerary!
          </Typography>
          <Button onClick={handleReset} sx={{ mt: 1, mr: 1 }}>
            View Itinerary Again
          </Button>
        </Paper>
      )}
    </Paper>
  );
};

export default PackageItinerary; 