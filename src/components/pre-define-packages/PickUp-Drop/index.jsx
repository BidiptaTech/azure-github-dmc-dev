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
import AirportShuttleIcon from '@mui/icons-material/AirportShuttle';
import FlightTakeoffIcon from '@mui/icons-material/FlightTakeoff';
import FlightLandIcon from '@mui/icons-material/FlightLand';
import HotelIcon from '@mui/icons-material/Hotel';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';

export default function PickupDropComponent({ preDefinedData }) {
  const dispatch = useDispatch();
  const { searchCriteria } = useSelector((state) => state.tourPackages);
  
  // Mock pickup-drop data for the pre-defined package
  const mockTransfers = {
    id: 1,
    vehicle: {
      type: "Private Van",
      model: "Toyota Hiace or similar",
      image: "https://source.unsplash.com/random/600x400/?van",
      capacity: 7,
      features: ["Air Conditioning", "Free WiFi", "Luggage Space", "Professional Driver"]
    },
    transfers: [
      {
        id: 1,
        type: "Arrival",
        from: "Changi International Airport",
        to: "Marina Bay Hotel",
        date: "Day 1",
        meetingPoint: "Arrival Hall with Name Board",
        time: "Based on your flight arrival",
        price: 45
      },
      {
        id: 2,
        type: "Departure",
        from: "Marina Bay Hotel",
        to: "Changi International Airport",
        date: "Day 4",
        meetingPoint: "Hotel Lobby",
        time: "3 hours before your flight departure",
        price: 45
      }
    ],
    inclusions: [
      "Meet and greet at airport (for arrival)",
      "Professional driver",
      "Air-conditioned vehicle",
      "Luggage assistance",
      "Direct transfer without stops"
    ]
  };

  // If we have pre-defined data, use it
  const transferData = preDefinedData?.transfers || mockTransfers;

  // Calculate total transfer value
  const totalTransferValue = transferData.transfers.reduce((acc, transfer) => {
    return acc + transfer.price;
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
          <AirportShuttleIcon />
        </Box>
        <Typography variant="h5">Airport Transfers</Typography>
      </Box>
      
      <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
        Airport pickup and drop-off services included in this pre-defined package.
      </Typography>
      
      <Grid container spacing={3}>
        <Grid item xs={12} md={5}>
          <Card elevation={2}>
            <CardMedia
              component="img"
              height="180"
              image={transferData.vehicle.image}
              alt={transferData.vehicle.type}
            />
            <CardContent>
              <Typography variant="h6" gutterBottom>
                {transferData.vehicle.type}
              </Typography>
              
              <Typography variant="body2" color="text.secondary" gutterBottom>
                {transferData.vehicle.model}
              </Typography>
              
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
                <Typography variant="body2" fontWeight={500} sx={{ mr: 1 }}>
                  Capacity:
                </Typography>
                <Typography variant="body2">
                  {transferData.vehicle.capacity} passengers
                </Typography>
              </Box>
              
              <Divider sx={{ my: 2 }} />
              
              <Typography variant="subtitle2" gutterBottom>
                Vehicle Features:
              </Typography>
              
              <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5, mb: 2 }}>
                {transferData.vehicle.features.map((feature, index) => (
                  <Chip 
                    key={index} 
                    label={feature} 
                    size="small" 
                    variant="outlined" 
                  />
                ))}
              </Box>
            </CardContent>
          </Card>
        </Grid>
        
        <Grid item xs={12} md={7}>
          <Paper elevation={2} sx={{ p: 3, height: '100%' }}>
            <Typography variant="subtitle1" gutterBottom>
              Transfer Details
            </Typography>
            
            <TableContainer component={Paper} variant="outlined" sx={{ mb: 3 }}>
              <Table size="small">
                <TableHead>
                  <TableRow>
                    <TableCell>Type</TableCell>
                    <TableCell>Route</TableCell>
                    <TableCell>Date</TableCell>
                    <TableCell align="right">Value</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {transferData.transfers.map((transfer) => (
                    <TableRow key={transfer.id}>
                      <TableCell>
                        <Box sx={{ display: 'flex', alignItems: 'center' }}>
                          {transfer.type === 'Arrival' ? (
                            <FlightLandIcon fontSize="small" sx={{ mr: 0.5 }} />
                          ) : (
                            <FlightTakeoffIcon fontSize="small" sx={{ mr: 0.5 }} />
                          )}
                          {transfer.type}
                        </Box>
                      </TableCell>
                      <TableCell>{transfer.from} → {transfer.to}</TableCell>
                      <TableCell>{transfer.date}</TableCell>
                      <TableCell align="right">${transfer.price.toFixed(2)}</TableCell>
                    </TableRow>
                  ))}
                  <TableRow>
                    <TableCell colSpan={3} align="right" sx={{ fontWeight: 'bold' }}>
                      Total Value:
                    </TableCell>
                    <TableCell align="right" sx={{ fontWeight: 'bold' }}>
                      ${totalTransferValue.toFixed(2)}
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </TableContainer>
            
            <Grid container spacing={2}>
              <Grid item xs={12} md={6}>
                <Typography variant="subtitle1" gutterBottom>
                  What's Included:
                </Typography>
                
                <List dense>
                  {transferData.inclusions.map((inclusion, index) => (
                    <ListItem key={index} disablePadding>
                      <ListItemIcon sx={{ minWidth: 36 }}>
                        <CheckCircleIcon color="primary" fontSize="small" />
                      </ListItemIcon>
                      <ListItemText primary={inclusion} />
                    </ListItem>
                  ))}
                </List>
              </Grid>
              
              <Grid item xs={12} md={6}>
                <Box sx={{ mb: 1.5 }}>
                  <Typography variant="subtitle2" gutterBottom>
                    Meeting Points:
                  </Typography>
                  
                  {transferData.transfers.map((transfer) => (
                    <Box key={transfer.id} sx={{ mb: 1.5 }}>
                      <Typography variant="body2" fontWeight={500}>
                        {transfer.type}:
                      </Typography>
                      <Typography variant="body2">
                        {transfer.meetingPoint}
                      </Typography>
                      <Typography variant="body2" color="text.secondary">
                        Time: {transfer.time}
                      </Typography>
                    </Box>
                  ))}
                </Box>
              </Grid>
            </Grid>
            
            <Box sx={{ mt: 2 }}>
              <Typography variant="body2" color="text.secondary">
                Note: For arrival transfers, your driver will wait up to 60 minutes after 
                the scheduled flight arrival. Please provide flight details when finalizing your booking.
              </Typography>
            </Box>
          </Paper>
        </Grid>
      </Grid>
    </Box>
  );
}
