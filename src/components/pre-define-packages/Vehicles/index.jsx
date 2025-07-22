import React, { useState } from 'react';
import { 
  Box, 
  Typography, 
  Paper, 
  Grid, 
  Card, 
  CardContent, 
  CardMedia, 
  Divider, 
  Stack,
  Chip,
  Button,
  List,
  ListItem,
  ListItemIcon,
  ListItemText,
  FormControlLabel,
  Checkbox
} from '@mui/material';
import { useSelector, useDispatch } from 'react-redux';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import AirlineSeatReclineNormalIcon from '@mui/icons-material/AirlineSeatReclineNormal';
import LocalGasStationIcon from '@mui/icons-material/LocalGasStation';
import SpeedIcon from '@mui/icons-material/Speed';

export default function VehicleComponent({ preDefinedData }) {
  const dispatch = useDispatch();
  const { searchCriteria } = useSelector((state) => state.tourPackages);
  
  // Mock vehicles data for the pre-defined package
  const mockVehicles = [
    {
      id: 1,
      type: "Economy",
      model: "Toyota Corolla or similar",
      image: "https://source.unsplash.com/random/600x400/?economy-car",
      description: "Fuel-efficient and comfortable for city driving",
      capacity: 4,
      luggage: 2,
      transmission: "Automatic",
      features: ["Air Conditioning", "Bluetooth", "USB Ports", "Power Windows"],
      pricePerDay: 55,
      included: ["Insurance", "Unlimited mileage", "24/7 roadside assistance"],
      fuelPolicy: "Full to Full"
    },
    {
      id: 2,
      type: "SUV",
      model: "Toyota RAV4 or similar",
      image: "https://source.unsplash.com/random/600x400/?suv-car",
      description: "Spacious and comfortable with ample luggage space",
      capacity: 5,
      luggage: 4,
      transmission: "Automatic",
      features: ["Air Conditioning", "Backup Camera", "Bluetooth", "Cruise Control"],
      pricePerDay: 75,
      included: ["Insurance", "Unlimited mileage", "24/7 roadside assistance"],
      fuelPolicy: "Full to Full"
    }
  ];

  // If we have pre-defined data, use it
  const vehicleData = preDefinedData?.vehicles || mockVehicles;
  
  return (
    <Box>
      <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
        <Box 
          sx={{ 
            bgcolor: 'primary.main', 
            color: 'white', 
            p: 0.8, 
            borderRadius: '50%', 
            mr: 1,
            display: 'flex'
          }}
        >
          <DirectionsCarIcon />
        </Box>
        <Typography variant="h5">Rental Vehicles</Typography>
      </Box>
      
      <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
        Self-drive rental vehicles included in this pre-defined package.
      </Typography>
      
      <Grid container spacing={3}>
        {vehicleData.map((vehicle) => (
          <Grid item xs={12} md={6} key={vehicle.id}>
            <Card elevation={2}>
              <Grid container>
                <Grid item xs={12} md={5}>
                  <CardMedia
                    component="img"
                    sx={{ height: '100%', minHeight: 200 }}
                    image={vehicle.image}
                    alt={vehicle.type}
                  />
                </Grid>
                <Grid item xs={12} md={7}>
                  <CardContent>
                    <Typography variant="h6" gutterBottom>
                      {vehicle.type}
                    </Typography>
                    
                    <Typography variant="body2" color="text.secondary" gutterBottom>
                      {vehicle.model}
                    </Typography>
                    
                    <Typography variant="body2" paragraph>
                      {vehicle.description}
                    </Typography>
                    
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        <AirlineSeatReclineNormalIcon fontSize="small" sx={{ mr: 0.5 }} />
                        <Typography variant="body2" sx={{ mr: 2 }}>
                          {vehicle.capacity} seats
                        </Typography>
                      </Box>
                      
                      <Typography variant="body2">
                        {vehicle.luggage} luggage
                      </Typography>
                    </Box>
                    
                    <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 1 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        <SpeedIcon fontSize="small" sx={{ mr: 0.5 }} />
                        <Typography variant="body2" sx={{ mr: 2 }}>
                          {vehicle.transmission}
                        </Typography>
                      </Box>
                      
                      <Box sx={{ display: 'flex', alignItems: 'center' }}>
                        <LocalGasStationIcon fontSize="small" sx={{ mr: 0.5 }} />
                        <Typography variant="body2">
                          {vehicle.fuelPolicy}
                        </Typography>
                      </Box>
                    </Box>
                    
                    <Divider sx={{ my: 1.5 }} />
                    
                    <Typography variant="subtitle2" gutterBottom>
                      Features:
                    </Typography>
                    
                    <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5, mb: 1.5 }}>
                      {vehicle.features.map((feature, index) => (
                        <Chip 
                          key={index} 
                          label={feature} 
                          size="small" 
                          variant="outlined" 
                        />
                      ))}
                    </Box>
                    
                    <Typography variant="subtitle2" gutterBottom>
                      Included in rate:
                    </Typography>
                    
                    <List dense disablePadding sx={{ mb: 1.5 }}>
                      {vehicle.included.map((item, index) => (
                        <ListItem key={index} disablePadding>
                          <ListItemIcon sx={{ minWidth: 28 }}>
                            <CheckCircleIcon color="primary" fontSize="small" />
                          </ListItemIcon>
                          <ListItemText primary={item} />
                        </ListItem>
                      ))}
                    </List>
                    
                    <Box sx={{ display: 'flex', justifyContent: 'flex-end' }}>
                      <Typography variant="subtitle1" color="primary" fontWeight={500}>
                        ${vehicle.pricePerDay} / day
                      </Typography>
                    </Box>
                  </CardContent>
                </Grid>
              </Grid>
            </Card>
          </Grid>
        ))}
      </Grid>
      
      <Paper variant="outlined" sx={{ p: 2, mt: 3 }}>
        <Typography variant="subtitle1" gutterBottom>
          Rental Information
        </Typography>
        
        <Typography variant="body2" color="text.secondary">
          All vehicles come with full insurance coverage and 24/7 roadside assistance. 
          Pickup and drop-off will be at your hotel or a designated location. 
          A valid driver's license and credit card are required for pickup. 
          Fuel policy is full-to-full, meaning you'll receive the vehicle with a full 
          tank and are expected to return it with a full tank.
        </Typography>
      </Paper>
    </Box>
  );
}
