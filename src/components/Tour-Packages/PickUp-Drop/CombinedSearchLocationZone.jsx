import React, { useEffect } from "react";
import { useDispatch } from "react-redux";
import {
  Box,
  Card,
  CardContent,
  Container,
  Typography,
  Chip,
  useTheme,
  alpha,
  Divider,
} from '@mui/material';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import FlightLandIcon from '@mui/icons-material/FlightLand';
import FlightTakeoffIcon from '@mui/icons-material/FlightTakeoff';
import EntryPortSearchZone from "./EntryPortSearchZone";
import ExitPortSearchZone from "./ExitPortSearchZone";

const CombinedSearchLocationZone = ({ Location, portType, portType1, entryPorts, exitPorts }) => {
  const theme = useTheme();
  const dispatch = useDispatch();
  console.log("entryPorts", entryPorts);
  console.log("exitPorts", exitPorts);
  // Check and log the Google Maps API availability
  useEffect(() => {
    const checkGoogleMapsApi = () => {
      if (!window.google) {
        console.error("Google Maps API not loaded at all!");
        return false;
      }
      
      if (!window.google.maps) {
        console.error("Google Maps core not loaded!");
        return false;
      }
      
      if (!window.google.maps.places) {
        console.error("Google Maps Places library not loaded!");
        return false;
      }
      
      console.log("Google Maps API (with Places) is fully loaded and available");
      return true;
    };
    
    // Check immediately and after a small delay to ensure API has time to load
    const isLoaded = checkGoogleMapsApi();
    
    if (!isLoaded) {
      // Try again after a delay in case API is still loading
      const timer = setTimeout(() => {
        checkGoogleMapsApi();
      }, 2000);
      
      return () => clearTimeout(timer);
    }
  }, []);

  // Determine which components to show based on portType and portType1
  const showEntryPort = portType === "Entry Port";
  const showExitPort = portType1 === "Exit Port";
  
  // Count active port types
  const activePortsCount = (portType === "Entry Port" ? 1 : 0) + (portType1 === "Exit Port" ? 1 : 0);

  return (
    <Container maxWidth="xl" sx={{ py: 2, position: 'relative' }}>
      {/* Header Card with Gradient Background */}
      <Card 
        elevation={3}
        sx={{
          borderRadius: 3,
          background: 'linear-gradient(135deg, #3b82f6 0%, #1e40af 100%)',
          color: 'white',
          mb: 3,
          mx: 'auto',
        }}
      >
        <CardContent sx={{ py: 1.5 }}>
          <Box display="flex" alignItems="center" justifyContent="space-between">
            <Box display="flex" alignItems="center">
              <DirectionsCarIcon sx={{ mr: 2, fontSize: 32, color: '#FFD700' }} />
              <Box>
                <Typography variant="h5" fontWeight="600" sx={{ color: 'white' }}>
                  Port Transport Services (Zone)
                </Typography>
                <Typography variant="body2" sx={{ color: 'rgba(255, 255, 255, 0.8)' }}>
                  Configure entry and exit port transportation services with zone pricing
                </Typography>
              </Box>
            </Box>
            <Chip 
              label={`${activePortsCount} Port${activePortsCount !== 1 ? 's' : ''} Active`}
              sx={{ 
                bgcolor: 'rgba(255, 255, 255, 0.2)',
                color: 'white',
                fontWeight: 600,
                border: '1px solid rgba(255, 255, 255, 0.3)'
              }}
            />
          </Box>
        </CardContent>
      </Card>

      {/* Port Services Content */}
      <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>
        {/* Entry Port Services */}
        {showEntryPort && (
          <Card 
            elevation={2}
            sx={{ 
              borderRadius: 3,
              border: `2px solid ${alpha('#3b82f6', 0.2)}`,
              transition: 'all 0.3s ease',
              '&:hover': {
                boxShadow: `0 8px 24px ${alpha('#3b82f6', 0.15)}`,
                transform: 'translateY(-2px)',
              }
            }}
          >
            <CardContent sx={{ p: 0 }}>
              {/* Entry Port Header */}
              <Box sx={{ 
                p: 2,
                bgcolor: alpha('#3b82f6', 0.05),
                borderBottom: `1px solid ${alpha('#3b82f6', 0.1)}`,
                display: 'flex',
                alignItems: 'center',
                gap: 2
              }}>
                <FlightLandIcon sx={{ color: '#3b82f6', fontSize: 24 }} />
                <Typography variant="h6" fontWeight={600} sx={{ color: '#3b82f6' }}>
                  Entry Port Services (Zone)
                </Typography>
                <Chip 
                  label="Arrival"
                  size="small"
                  sx={{ 
                    bgcolor: '#3b82f6',
                    color: 'white',
                    fontWeight: 600
                  }}
                />
              </Box>

              {/* Entry Port Content */}
              <Box sx={{ p: 2 }}>
                <EntryPortSearchZone Location={Location} portType={portType} />
              </Box>
            </CardContent>
          </Card>
        )}
        
        {/* Divider between services if both are present */}
        {showEntryPort && showExitPort && (
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 2, my: 1 }}>
            <Divider sx={{ flex: 1, borderColor: alpha('#3b82f6', 0.2) }} />
            <Typography 
              variant="body2" 
              sx={{ 
                color: alpha('#3b82f6', 0.7),
                fontWeight: 500,
                px: 2
              }}
            >
              Transport Services
            </Typography>
            <Divider sx={{ flex: 1, borderColor: alpha('#3b82f6', 0.2) }} />
          </Box>
        )}

        {/* Exit Port Services */}
        {showExitPort && (
          <Card 
            elevation={2}
            sx={{ 
              borderRadius: 3,
              border: `2px solid ${alpha('#3b82f6', 0.2)}`,
              transition: 'all 0.3s ease',
              '&:hover': {
                boxShadow: `0 8px 24px ${alpha('#3b82f6', 0.15)}`,
                transform: 'translateY(-2px)',
              }
            }}
          >
            <CardContent sx={{ p: 0 }}>
              {/* Exit Port Header */}
              <Box sx={{ 
                p: 2,
                bgcolor: alpha('#3b82f6', 0.05),
                borderBottom: `1px solid ${alpha('#3b82f6', 0.1)}`,
                display: 'flex',
                alignItems: 'center',
                gap: 2
              }}>
                <FlightTakeoffIcon sx={{ color: '#3b82f6', fontSize: 24 }} />
                <Typography variant="h6" fontWeight={600} sx={{ color: '#3b82f6' }}>
                  Exit Port Services (Zone)
                </Typography>
                <Chip 
                  label="Departure"
                  size="small"
                  sx={{ 
                    bgcolor: '#3b82f6',
                    color: 'white',
                    fontWeight: 600
                  }}
                />
              </Box>

              {/* Exit Port Content */}
              <Box sx={{ p: 2 }}>
                <ExitPortSearchZone Location={Location} portType={portType1} />
              </Box>
            </CardContent>
          </Card>
        )}

        {/* No Services Message */}
        {!showEntryPort && !showExitPort && (
          <Card 
            elevation={1}
            sx={{ 
              borderRadius: 3,
              border: `2px dashed ${alpha('#3b82f6', 0.3)}`,
              bgcolor: alpha('#3b82f6', 0.02),
            }}
          >
            <CardContent sx={{ py: 4, textAlign: 'center' }}>
              <DirectionsCarIcon sx={{ fontSize: 48, color: alpha('#3b82f6', 0.4), mb: 2 }} />
              <Typography variant="h6" color={alpha('#3b82f6', 0.7)} fontWeight={500}>
                No Port Services Selected
              </Typography>
              <Typography variant="body2" color={alpha('#3b82f6', 0.5)}>
                Please configure your port types to enable transport services
              </Typography>
            </CardContent>
          </Card>
        )}
      </Box>
    </Container>
  );
};

export default CombinedSearchLocationZone; 