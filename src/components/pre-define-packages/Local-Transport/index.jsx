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
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  List,
  ListItem,
  ListItemIcon,
  ListItemText
} from '@mui/material';
import { useSelector, useDispatch } from 'react-redux';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import EventIcon from '@mui/icons-material/Event';
import PersonIcon from '@mui/icons-material/Person';
import AirlineSeatReclineNormalIcon from '@mui/icons-material/AirlineSeatReclineNormal';

export default function TransportComponent({ preDefinedData }) {
  const dispatch = useDispatch();
  const { searchCriteria } = useSelector((state) => state.tourPackages);
  
  // Mock transport data for the pre-defined package
  const mockTransport = {
    id: 1,
    type: "Private Car",
    model: "Toyota Camry or similar",
    image: "https://source.unsplash.com/random/600x400/?sedan",
    description: "Comfortable sedan with air conditioning and professional driver",
    capacity: 4,
    features: ["Air Conditioning", "Free WiFi", "Bottled Water", "Professional Driver"],
    pricePerDay: 80,
    schedule: [
      { day: 2, description: "Full day for Gardens by the Bay and Singapore Flyer", hours: 8 },
      { day: 3, description: "Full day for Sentosa Island tour", hours: 8 }
    ],
    inclusions: [
      "Professional driver",
      "Fuel and tolls",
      "Vehicle maintenance",
      "Bottled water"
    ]
  };

  // If we have pre-defined data, use it
  const transportData = preDefinedData?.transport || mockTransport;

  // Calculate the total value of transport
  const totalTransportValue = transportData.schedule.reduce((acc, day) => {
    const dayPrice = transportData.pricePerDay * (day.hours / 8); // Prorated based on hours
    return acc + dayPrice;
  }, 0);
  
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
        <Typography variant="h5">Local Transportation</Typography>
      </Box>
      
      <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
        Local transportation services included in this pre-defined package.
      </Typography>
      
      <Grid container spacing={3}>
        <Grid item xs={12} md={5}>
          <Card elevation={2}>
            <CardMedia
              component="img"
              height="200"
              image={transportData.image}
              alt={transportData.type}
            />
            <CardContent>
              <Typography variant="h6" gutterBottom>
                {transportData.type}
              </Typography>
              
              <Typography variant="body2" color="text.secondary" gutterBottom>
                {transportData.model}
              </Typography>
              
              <Typography variant="body2" paragraph>
                {transportData.description}
              </Typography>
              
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                <AirlineSeatReclineNormalIcon fontSize="small" sx={{ mr: 0.5 }} />
                <Typography variant="body2">
                  Capacity: {transportData.capacity} passengers
                </Typography>
              </Box>
              
              <Divider sx={{ my: 2 }} />
              
              <Typography variant="subtitle2" gutterBottom>
                Vehicle Features:
              </Typography>
              
              <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5, mb: 2 }}>
                {transportData.features.map((feature, index) => (
                  <Chip 
                    key={index} 
                    label={feature} 
                    size="small" 
                    variant="outlined" 
                  />
                ))}
              </Box>
              
              <Box sx={{ display: 'flex', justifyContent: 'flex-end' }}>
                <Typography variant="subtitle1" color="primary" fontWeight={500}>
                  ${transportData.pricePerDay} / day
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>
        
        <Grid item xs={12} md={7}>
          <Paper elevation={2} sx={{ p: 3, height: '100%' }}>
            <Typography variant="subtitle1" gutterBottom>
              Transport Schedule
            </Typography>
            
            <TableContainer component={Paper} variant="outlined" sx={{ mb: 3 }}>
              <Table size="small">
                <TableHead>
                  <TableRow>
                    <TableCell>Day</TableCell>
                    <TableCell>Activity</TableCell>
                    <TableCell align="center">Hours</TableCell>
                    <TableCell align="right">Value</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {transportData.schedule.map((day, index) => (
                    <TableRow key={index}>
                      <TableCell>Day {day.day}</TableCell>
                      <TableCell>{day.description}</TableCell>
                      <TableCell align="center">{day.hours} hrs</TableCell>
                      <TableCell align="right">${(transportData.pricePerDay * day.hours / 8).toFixed(2)}</TableCell>
                    </TableRow>
                  ))}
                  <TableRow>
                    <TableCell colSpan={3} align="right" sx={{ fontWeight: 'bold' }}>
                      Total Value:
                    </TableCell>
                    <TableCell align="right" sx={{ fontWeight: 'bold' }}>
                      ${totalTransportValue.toFixed(2)}
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </TableContainer>
            
            <Typography variant="subtitle1" gutterBottom>
              What's Included:
            </Typography>
            
            <List dense>
              {transportData.inclusions.map((inclusion, index) => (
                <ListItem key={index} disablePadding>
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <CheckCircleIcon color="primary" fontSize="small" />
                  </ListItemIcon>
                  <ListItemText primary={inclusion} />
                </ListItem>
              ))}
            </List>
            
            <Box sx={{ mt: 2 }}>
              <Typography variant="body2" color="text.secondary">
                Note: Your driver will wait at designated meeting points during tours. Additional hours 
                beyond the scheduled time may incur extra charges. Please confirm pick-up and drop-off 
                locations with your guide or driver the day before.
              </Typography>
            </Box>
          </Paper>
        </Grid>
      </Grid>
    </Box>
  );
}
