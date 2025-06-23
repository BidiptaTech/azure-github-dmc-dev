import React, { useState } from 'react';
import { 
  Container, 
  Typography, 
  Box, 
  Paper, 
  Button
} from '@mui/material';
import { useSelector } from 'react-redux';
import { Link } from 'react-router-dom';
import SearchForm from './common/SearchForm';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import TravelExploreIcon from '@mui/icons-material/TravelExplore';

// Import service components
import Itinerary from './common/Itinerary';

export default function TourPackages() {
  const [currentStep, setCurrentStep] = useState(1);

  const handleNext = () => {
    setCurrentStep(currentStep + 1);
  };

  return (
    <Box sx={{ bgcolor: '#f8fafc', minHeight: '100vh' }}>
      <Container maxWidth="lg" sx={{ py: 2 }}>
        {/* Compact Header */}
        <Box 
          sx={{ 
            display: 'flex', 
            alignItems: 'center', 
            justifyContent: 'space-between',
            mb: 2,
            bgcolor: 'white',
            p: 1.5,
            borderRadius: 2,
            boxShadow: '0 1px 4px rgba(0,0,0,0.04)'
          }}
        >
          <Button 
            component={Link}
            to="/dashboard/db-dashboard/tour-packages"
            variant="outlined"
            startIcon={<ArrowBackIcon />}
            size="small"
            sx={{ 
              borderColor: '#e2e8f0',
              color: '#64748b',
              fontSize: '0.875rem',
              '&:hover': { 
                borderColor: '#cbd5e1',
                bgcolor: '#f8fafc'
              }
            }}
          >
            Back
          </Button>
          
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
            <TravelExploreIcon sx={{ color: '#3b82f6', fontSize: 24 }} />
            <Typography
              variant="h6"
              component="h1"
              sx={{ 
                mt: 1,
                fontWeight: 600,
                color: '#1e293b'
              }}
            >
              Tour Packages
            </Typography>
          </Box>
          
          <Box sx={{ width: '70px' }}></Box>
        </Box>

        {/* Main Search Section - Very Compact */}
        <Paper
          elevation={0}
          sx={{
            borderRadius: 2,
            overflow: 'visible',
            boxShadow: '0 2px 8px rgba(0, 0, 0, 0.05)',
            border: '1px solid #e2e8f0',
            mt: 2,
            position: 'relative',
            zIndex: 1
          }}
        >
          {/* Minimal Header */}
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

          {/* Search Form Section */}
          <Box sx={{ 
            p: 2, 
            bgcolor: 'white',
            overflow: 'visible',
            position: 'relative'
          }}>
            <SearchForm onNext={handleNext} />
          </Box>
        </Paper>

        {/* Results Section - Compact */}
        {currentStep > 1 && (
          <Box sx={{ mt: 2 }}>
            <Paper 
              sx={{ 
                borderRadius: 2,
                overflow: 'hidden',
                boxShadow: '0 2px 8px rgba(0, 0, 0, 0.05)',
                border: '1px solid #e2e8f0'
              }}
            >
              <Box 
                sx={{ 
                  p: 1.5, 
                  bgcolor: '#f8fafc',
                  borderBottom: '1px solid #e2e8f0'
                }}
              >
                <Typography 
                  variant="subtitle1" 
                  sx={{ 
                    fontWeight: 600, 
                    color: '#1e293b',
                    fontSize: '1rem'
                  }}
                >
                  Package Details
                </Typography>
              </Box>
              <Box sx={{ p: 1.5 }}>
                <Itinerary />
              </Box>
            </Paper>
          </Box>
        )}
      </Container>
    </Box>
  );
} 