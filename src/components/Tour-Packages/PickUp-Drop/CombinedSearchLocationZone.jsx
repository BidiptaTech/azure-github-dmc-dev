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
    <Container maxWidth="xl" sx={{ py: 1, position: 'relative' }}>
      {/* Header Card with Gradient Background */}
      <Card 
        elevation={3}
        sx={{
          borderRadius: 2,
          background: 'linear-gradient(135deg, #3b82f6 0%, #1e40af 100%)',
          color: 'white',
          mb: 0.1,  
          mx: 'auto',
        }}
      >
        <CardContent sx={{ 
          py: { xs: 1, sm: 0.8, md: 0.1 },
          px: { xs: 1.5, sm: 2, md: 2 },
          height: { xs: 'auto', sm: '52px' },
          minHeight: { xs: '60px', sm: '52px' }
        }}>
          <Box 
            display="flex" 
            alignItems="center" 
            justifyContent="space-between"
            flexDirection={{ xs: 'column', sm: 'row' }}
            gap={{ xs: 1, sm: 0 }}
          >
            <Box 
              display="flex" 
              alignItems="center"
              flexDirection={{ xs: 'column', sm: 'row' }}
              textAlign={{ xs: 'center', sm: 'left' }}
              gap={{ xs: 1, sm: 0 }}
            >
              <DirectionsCarIcon sx={{ 
                mr: { xs: 0, sm: 1.5 }, 
                mb: { xs: 0.5, sm: 0 },
                fontSize: { xs: 28, sm: 24 }, 
                color: '#FFD700' 
              }} />
              <Box>
                <Typography 
                  variant="h6" 
                  fontWeight="600" 
                  sx={{ 
                    color: 'white', 
                    fontSize: { xs: '0.85rem', sm: '1rem', md: '1rem' },
                    lineHeight: 1.2
                  }}
                >
                  Port Transport Services (Zone)
                </Typography>
                <Typography 
                  variant="body2" 
                  sx={{ 
                    color: 'rgba(255, 255, 255, 0.8)', 
                    fontSize: { xs: '0.65rem', sm: '0.75rem', md: '0.75rem' },
                    lineHeight: 1.3,
                    display: { xs: 'none', sm: 'block' }
                  }}
                >
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
                border: '1px solid rgba(255, 255, 255, 0.3)',
                fontSize: { xs: '0.7rem', sm: '0.7rem' },
                height: { xs: '28px', sm: '20px' },
                minWidth: { xs: '80px', sm: 'auto' },
                mt: { xs: 0.5, sm: 0 }
              }}
            />
          </Box>
        </CardContent>
      </Card>

      {/* Port Services Content */}
      <Box sx={{ display: 'flex', flexDirection: 'column', gap: 1.5 }}>
        {/* Entry Port Services */}
        {showEntryPort && (
          <Card 
            elevation={1}
            sx={{ 
              borderRadius: 2,
              border: `1px solid ${alpha('#3b82f6', 0.2)}`,
              transition: 'all 0.3s ease',
              '&:hover': {
                boxShadow: `0 4px 12px ${alpha('#3b82f6', 0.15)}`,
                transform: 'translateY(-1px)',
              }
            }}
          >
            <CardContent sx={{ p: 0 }}>
              {/* Entry Port Header */}
              <Box sx={{ 
                p: 1.5,
                bgcolor: alpha('#3b82f6', 0.05),
                borderBottom: `1px solid ${alpha('#3b82f6', 0.1)}`,
                display: 'flex',
                alignItems: 'center',
                gap: 1.5
              }}>
                <FlightLandIcon sx={{ color: '#3b82f6', fontSize: 20 }} />
                <Typography variant="subtitle1" fontWeight={600} sx={{ color: '#3b82f6', fontSize: '0.9rem' }}>
                  Arrival Port Services (Zone)
                </Typography>
                <Chip 
                  label="Arrival"
                  size="small"
                  sx={{ 
                    bgcolor: '#3b82f6',
                    color: 'white',
                    fontWeight: 600,
                    fontSize: '0.7rem',
                    height: '20px'
                  }}
                />
              </Box>

              {/* Entry Port Content */}
              <Box sx={{ p: 1.5 }}>
                <EntryPortSearchZone Location={Location} portType={portType} />
              </Box>
            </CardContent>
          </Card>
        )}
        
        {/* Divider between services if both are present */}
        {showEntryPort && showExitPort && (
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5, my: 0.5 }}>
            <Divider sx={{ flex: 1, borderColor: alpha('#3b82f6', 0.2) }} />
            <Typography 
              variant="body2" 
              sx={{ 
                color: alpha('#3b82f6', 0.7),
                fontWeight: 500,
                px: 1.5,
                fontSize: '0.8rem'
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
            elevation={1}
            sx={{ 
              borderRadius: 2,
              border: `1px solid ${alpha('#3b82f6', 0.2)}`,
              transition: 'all 0.3s ease',
              '&:hover': {
                boxShadow: `0 4px 12px ${alpha('#3b82f6', 0.15)}`,
                transform: 'translateY(-1px)',
              }
            }}
          >
            <CardContent sx={{ p: 0 }}>
              {/* Exit Port Header */}
              <Box sx={{ 
                p: 1.5,
                bgcolor: alpha('#3b82f6', 0.05),
                borderBottom: `1px solid ${alpha('#3b82f6', 0.1)}`,
                display: 'flex',
                alignItems: 'center',
                gap: 1.5
              }}>
                <FlightTakeoffIcon sx={{ color: '#3b82f6', fontSize: 20 }} />
                <Typography variant="subtitle1" fontWeight={600} sx={{ color: '#3b82f6', fontSize: '0.9rem' }}>
                  Departure Port Services (Zone)
                </Typography>
                <Chip 
                  label="Departure"
                  size="small"
                  sx={{ 
                    bgcolor: '#3b82f6',
                    color: 'white',
                    fontWeight: 600,
                    fontSize: '0.7rem',
                    height: '20px'
                  }}
                />
              </Box>

              {/* Exit Port Content */}
              <Box sx={{ p: 1.5 }}>
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
              borderRadius: 2,
              border: `1px dashed ${alpha('#3b82f6', 0.3)}`,
              bgcolor: alpha('#3b82f6', 0.02),
            }}
          >
            <CardContent sx={{ py: 2.5, textAlign: 'center' }}>
              <DirectionsCarIcon sx={{ fontSize: 36, color: alpha('#3b82f6', 0.4), mb: 1.5 }} />
              <Typography variant="subtitle1" color={alpha('#3b82f6', 0.7)} fontWeight={500} sx={{ fontSize: '0.9rem' }}>
                No Port Services Selected
              </Typography>
              <Typography variant="body2" color={alpha('#3b82f6', 0.5)} sx={{ fontSize: '0.75rem' }}>
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