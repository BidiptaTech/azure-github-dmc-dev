import React, { useState } from 'react';
import { Container, Typography, Box, Grid, Paper, Button } from '@mui/material';
import { useSelector } from 'react-redux';
import { Link } from 'react-router-dom';
import SearchForm from './common/SearchForm';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';

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
              backgroundImage: 'linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url("https://source.unsplash.com/random/1200x400/?travel,landscape")',
              backgroundSize: 'cover',
              backgroundPosition: 'center',
              minHeight: '300px',
              display: 'flex',
              flexDirection: 'column',
              justifyContent: 'center',
              alignItems: 'center',
              p: 4,
              mb: 3
            }}
          >
            <Typography variant="h2" component="div" align="center" sx={{ color: 'white', mb: 2 }}>
              Find Your Dream Tour Package
            </Typography>
            <Typography variant="h6" component="div" align="center" sx={{ color: 'white' }}>
              Discover the world with our curated tour packages
            </Typography>
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