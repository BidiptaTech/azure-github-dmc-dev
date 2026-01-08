import React, { useState, useEffect } from 'react';
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
  TextField,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  IconButton,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow
} from '@mui/material';
import { useSelector, useDispatch } from 'react-redux';
import HotelIcon from '@mui/icons-material/Hotel';
import EditIcon from '@mui/icons-material/Edit';
import InfoIcon from '@mui/icons-material/Info';

export default function HotelComponent({ preDefinedData }) {
  const dispatch = useDispatch();
  const { searchCriteria } = useSelector((state) => state.tourPackages);
  const { hotels } = useSelector((state) => state.hotels);
  
  // Mock hotel data for the pre-defined package
  const mockHotel = {
    id: 1,
    name: "Marina Bay Hotel",
    location: "Marina Bay, Singapore",
    stars: 5,
    image: "https://source.unsplash.com/random/600x400/?hotel",
    description: "Luxury hotel with stunning views of Marina Bay",
    amenities: ["Free WiFi", "Swimming Pool", "Spa", "Fitness Center", "Restaurant"],
    roomType: "Deluxe Room",
    mealPlan: "Breakfast",
    checkIn: "14:00",
    checkOut: "12:00",
    pricePerNight: 299
  };

  // If we have pre-defined data, use it
  const hotelData = preDefinedData?.hotels || [mockHotel];
  
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
          <HotelIcon />
        </Box>
        <Typography variant="h5">Hotel Details</Typography>
      </Box>
      
      <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
        Hotel accommodations included in this pre-defined package.
      </Typography>
      
      <Grid container spacing={3}>
        {hotelData.map((hotel, index) => (
          <Grid item xs={12} md={6} key={hotel.id || index}>
            <Card elevation={2} sx={{ height: '100%' }}>
              <Grid container>
                <Grid item xs={12} md={5}>
                  <CardMedia
                    component="img"
                    sx={{ height: '100%', minHeight: 200 }}
                    image={hotel.image}
                    alt={hotel.name}
                  />
                </Grid>
                <Grid item xs={12} md={7}>
                  <CardContent>
                    <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
                      <Typography variant="h6" component="div">
                        {hotel.name}
                      </Typography>
                      <IconButton size="small">
                        <InfoIcon fontSize="small" />
                      </IconButton>
                    </Box>
                    
                    <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                      {hotel.location} • {hotel.stars} Star
                    </Typography>
                    
                    <Divider sx={{ my: 1 }} />
                    
                    <Box sx={{ mb: 1 }}>
                      <Typography variant="body2" component="span" fontWeight={500}>
                        Room Type:
                      </Typography>
                      <Typography variant="body2" component="span" sx={{ ml: 1 }}>
                        {hotel.roomType}
                      </Typography>
                    </Box>
                    
                    <Box sx={{ mb: 1 }}>
                      <Typography variant="body2" component="span" fontWeight={500}>
                        Meal Plan:
                      </Typography>
                      <Typography variant="body2" component="span" sx={{ ml: 1 }}>
                        {hotel.mealPlan}
                      </Typography>
                    </Box>
                    
                    <Box sx={{ mb: 1 }}>
                      <Typography variant="body2" component="span" fontWeight={500}>
                        Check-in/Check-out:
                      </Typography>
                      <Typography variant="body2" component="span" sx={{ ml: 1 }}>
                        {hotel.checkIn} / {hotel.checkOut}
                      </Typography>
                    </Box>
                    
                    <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5, mt: 1 }}>
                      {hotel.amenities && hotel.amenities.map((amenity, i) => (
                        <Chip 
                          key={i} 
                          label={amenity} 
                          size="small" 
                          variant="outlined" 
                        />
                      ))}
                    </Box>
                    
                    <Box sx={{ display: 'flex', justifyContent: 'flex-end', mt: 2 }}>
                      <Typography variant="subtitle1" color="primary" fontWeight={500}>
                        ${hotel.pricePerNight} / night
                      </Typography>
                    </Box>
                  </CardContent>
                </Grid>
              </Grid>
            </Card>
          </Grid>
        ))}
      </Grid>
      
      <Box sx={{ mt: 3 }}>
        <Typography variant="h6" gutterBottom>Stay Summary</Typography>
        <TableContainer component={Paper} variant="outlined">
          <Table size="small">
            <TableHead>
              <TableRow>
                <TableCell>Night</TableCell>
                <TableCell>Date</TableCell>
                <TableCell>Hotel</TableCell>
                <TableCell>Room Type</TableCell>
                <TableCell>Meal Plan</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {/* Iterate through each night of the stay */}
              {Array.from({ length: preDefinedData?.days - 1 || 3 }).map((_, index) => (
                <TableRow key={index}>
                  <TableCell>Night {index + 1}</TableCell>
                  <TableCell>{/* Date would be calculated from search criteria */}</TableCell>
                  <TableCell>{mockHotel.name}</TableCell>
                  <TableCell>{mockHotel.roomType}</TableCell>
                  <TableCell>{mockHotel.mealPlan}</TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </TableContainer>
      </Box>
    </Box>
  );
}
