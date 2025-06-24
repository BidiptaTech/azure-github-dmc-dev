import React, { useState } from 'react';
import { Container, Typography, Box, Grid, Paper, Button } from '@mui/material';
import { useSelector } from 'react-redux';
import { Link } from 'react-router-dom';
import SearchForm from './common/SearchForm';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import TravelExploreIcon from '@mui/icons-material/TravelExplore';


// Import service components
import Itinerary from './common/Itinerary';

// Import icons
import ViewTimelineIcon from '@mui/icons-material/ViewTimeline';

export default function TourPackages() {
  const [currentStep, setCurrentStep] = useState(1);

  const handleNext = () => {
    setCurrentStep(currentStep + 1);
  };

  return (
    <Container maxWidth="lg" sx={{ py: 5 }}>
      <Box 
        sx={{ 
          display: 'flex', 
          alignItems: 'center', 
          justifyContent: 'space-between',
          mt: 6, 
          mb: 4 
        }}
      >
        <Button 
          component={Link}
          to="/dashboard/db-dashboard/tour-packages"
          variant="contained"
          startIcon={<ArrowBackIcon />}
          sx={{ 
            bgcolor: 'primary.main', 
            '&:hover': { bgcolor: 'primary.dark' },
            borderRadius: '8px',
            boxShadow: '0 4px 10px rgba(0,0,0,0.15)'
          }}
        >
          Back
        </Button>
        
        <Typography
          variant="h3"
          component="h1"
          align="center"
          sx={{ flex: 1 }}
        >
          Tour Packages
        </Typography>
        
        <Box sx={{ width: '140px' }}></Box> {/* Spacer to balance the layout */}
      </Box>


      <Box sx={{ mb: 5 }}>
        <Paper
          elevation={0}
          sx={{
            borderRadius: 3,
            overflow: 'hidden',
            backgroundColor: 'transparent'
          }}
        >
          <Box
            sx={{
              background: 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)',
              p: 2,
              position: 'relative'
            }}
          >
            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
              <TravelExploreIcon sx={{ color: 'white', fontSize: 24 }} />
              <Box>
                <Typography 
                  variant="h6" 
                  component="div" 
                  sx={{ 
                    color: 'white', 
                    fontWeight: 600,
                    fontSize: '1.1rem'
                  }}
                >
                  Create Tour Packages
                </Typography>
                <Typography 
                  variant="caption" 
                  component="div" 
                  sx={{ 
                    color: 'rgba(255,255,255,0.9)', 
                    fontWeight: 400
                  }}
                >
                  Search and customize your packages
                </Typography>
              </Box>
            </Box>
          </Box>

          <Box sx={{ p: 2 }}>
            <SearchForm onNext={handleNext} />
          </Box>
        </Paper>
      </Box>

      {/* Render services when search is completed */}
      {currentStep > 1 && (
        <Box sx={{ mt: 4 }}>
          {/* Service content area */}
          <Box sx={{ py: 2 }}>
            <Itinerary />
          </Box>
        </Box>
      )}
    </Container>
  );
} 