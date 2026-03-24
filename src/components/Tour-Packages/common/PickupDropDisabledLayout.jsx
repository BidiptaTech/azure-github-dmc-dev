import React from 'react';
import {
  Grid,
  Box,
  Typography,
  Paper,
  Card,
  CardContent,
  RadioGroup,
  FormControlLabel,
  Radio,
  styled,
  Chip
} from '@mui/material';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
import FlightLandIcon from '@mui/icons-material/FlightLand';
import FlightTakeoffIcon from '@mui/icons-material/FlightTakeoff';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import InfoOutlinedIcon from '@mui/icons-material/InfoOutlined';
import DisabledStateLayout from './DisabledStateLayout';

// Styled components
const ServiceCard = styled(Card)(({ theme, selected }) => ({
  cursor: 'pointer',
  transition: 'all 0.3s ease',
  border: selected ? '2px solid #3b82f6' : '2px solid #e5e7eb',
  backgroundColor: selected ? 'rgba(59, 130, 246, 0.05)' : 'white',
  '&:hover': {
    borderColor: '#3b82f6',
    boxShadow: '0 4px 12px rgba(59, 130, 246, 0.15)',
    transform: 'translateY(-2px)',
  }
}));

const ServiceIcon = styled(Box)(({ theme, selected }) => ({
  width: 48,
  height: 48,
  borderRadius: '50%',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  backgroundColor: selected ? '#3b82f6' : '#f3f4f6',
  color: selected ? 'white' : '#6b7280',
  marginBottom: theme.spacing(1)
}));

const PickupDropDisabledLayout = ({ 
  selectedService, 
  onServiceChange,
  services = [
    {
      id: 'entry_port',
      name: 'Entry Port',
      description: 'Airport to Hotel Transfer',
      icon: FlightLandIcon
    },
    {
      id: 'exit_port', 
      name: 'Exit Port',
      description: 'Hotel to Airport Transfer',
      icon: FlightTakeoffIcon
    },
    {
      id: 'local_transport',
      name: 'Local Transport',
      description: 'Point to Point Transfer',
      icon: DirectionsCarIcon
    }
  ]
}) => {
  return (
    <Box sx={{ p: 3, backgroundColor: '#f8fafc', minHeight: '100vh' }}>
      {/* Header */}
      <Box sx={{ mb: 4, textAlign: 'center' }}>
        <Typography variant="h4" fontWeight="700" color="primary.main" gutterBottom>
          Transportation Services
        </Typography>
        <Typography variant="body1" color="text.secondary">
          Select a service type to configure your transportation needs
        </Typography>
      </Box>

      {/* Service Selection Cards */}
      <Grid container spacing={3} sx={{ mb: 4 }}>
        {services.map((service) => {
          const IconComponent = service.icon;
          const isSelected = selectedService === service.id;
          
          return (
            <Grid item xs={12} md={4} key={service.id}>
              <ServiceCard 
                selected={isSelected}
                onClick={() => onServiceChange(service.id)}
              >
                <CardContent sx={{ textAlign: 'center', p: 3 }}>
                  <ServiceIcon selected={isSelected}>
                    <IconComponent sx={{ fontSize: 24 }} />
                  </ServiceIcon>
                  
                  <Typography variant="h6" fontWeight="600" gutterBottom>
                    {service.name}
                  </Typography>
                  
                  <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                    {service.description}
                  </Typography>
                  
                  {isSelected && (
                    <Chip 
                      label="Selected" 
                      color="primary" 
                      size="small"
                      sx={{ fontWeight: 600 }}
                    />
                  )}
                </CardContent>
              </ServiceCard>
            </Grid>
          );
        })}
      </Grid>

      {/* Service Configuration Section */}
      <Paper elevation={2} sx={{ borderRadius: 3, overflow: 'hidden' }}>
        {/* Section Header */}
        <Box sx={{ 
          p: 3, 
          backgroundColor: selectedService ? '#3b82f6' : '#e5e7eb',
          color: selectedService ? 'white' : '#6b7280',
          textAlign: 'center'
        }}>
          <Typography variant="h5" fontWeight="600">
            Service Configuration
          </Typography>
          <Typography variant="body2" sx={{ mt: 1, opacity: 0.9 }}>
            {selectedService 
              ? `Configure your ${services.find(s => s.id === selectedService)?.name} details`
              : 'Please select a service type above to continue'
            }
          </Typography>
        </Box>

        {/* Configuration Content */}
        <Box sx={{ p: 3 }}>
          {selectedService ? (
            // Show enabled form fields when service is selected
            <Box>
              <Box sx={{ 
                display: 'flex', 
                alignItems: 'center', 
                gap: 2, 
                mb: 3,
                p: 2,
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                borderRadius: 2,
                border: '1px solid rgba(34, 197, 94, 0.3)'
              }}>
                <InfoOutlinedIcon sx={{ fontSize: 24, color: '#22c55e' }} />
                <Typography 
                  variant="body1" 
                  sx={{ 
                    color: '#166534',
                    fontWeight: 500
                  }}
                >
                  Great! Now you can configure your {services.find(s => s.id === selectedService)?.name} details below.
                </Typography>
              </Box>

              {/* Enabled form fields would go here */}
              <Grid container spacing={3}>
                <Grid item xs={12} md={6}>
                  <Box sx={{ 
                    p: 3, 
                    border: '2px solid #22c55e', 
                    borderRadius: 2,
                    backgroundColor: 'rgba(34, 197, 94, 0.05)'
                  }}>
                    <Typography variant="h6" color="success.main" gutterBottom>
                      Location Fields Enabled
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      You can now select pickup and dropoff locations
                    </Typography>
                  </Box>
                </Grid>
                
                <Grid item xs={12} md={3}>
                  <Box sx={{ 
                    p: 3, 
                    border: '2px solid #22c55e', 
                    borderRadius: 2,
                    backgroundColor: 'rgba(34, 197, 94, 0.05)'
                  }}>
                    <Typography variant="h6" color="success.main" gutterBottom>
                      Time Selection Enabled
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Choose your preferred time
                    </Typography>
                  </Box>
                </Grid>
                
                <Grid item xs={12} md={3}>
                  <Box sx={{ 
                    p: 3, 
                    border: '2px solid #22c55e', 
                    borderRadius: 2,
                    backgroundColor: 'rgba(34, 197, 94, 0.05)'
                  }}>
                    <Typography variant="h6" color="success.main" gutterBottom>
                      Date Selection Enabled
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Pick your travel date
                    </Typography>
                  </Box>
                </Grid>
              </Grid>
            </Box>
          ) : (
            // Show disabled layout when no service is selected
            <DisabledStateLayout 
              message="Please select a transportation service above to configure location, time, and date settings"
              showLocationFields={true}
              showTimeField={true}
              showDateField={true}
            />
          )}
        </Box>
      </Paper>

      {/* Help Section */}
      <Paper elevation={1} sx={{ mt: 4, p: 3, backgroundColor: '#fef3c7', border: '1px solid #fbbf24' }}>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
          <InfoOutlinedIcon sx={{ color: '#d97706' }} />
          <Box>
            <Typography variant="subtitle1" fontWeight="600" color="#92400e">
              How it works:
            </Typography>
            <Typography variant="body2" color="#92400e" sx={{ mt: 1 }}>
              1. Select a transportation service type from the cards above
              <br />
              2. Configure your pickup/dropoff locations, time, and date
              <br />
              3. Choose your preferred vehicle and complete the booking
            </Typography>
          </Box>
        </Box>
      </Paper>
    </Box>
  );
};

export default PickupDropDisabledLayout; 