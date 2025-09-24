import React, { useState } from 'react';
import { Button, Box, Typography, Container, Paper, Grid } from '@mui/material';
import BookingViewModal from './BookingViewModal';

const ModalTestPage = () => {
  const [openModal, setOpenModal] = useState(false);
  const [showEnhanced, setShowEnhanced] = useState(false);
  
  // Sample basic booking data
  const sampleBasicBooking = {
    bookingId: 21,
    customerName: "sk Imran",
    destination: "Singapore",
    endDate: "Not specified",
    pax: 3,
    payment: 1500,
    paymentStatus: "Unpaid",
    startDate: "Jun 26, 2025",
    status: 1
  };

  // Sample enhanced booking data with API-style details
  const sampleEnhancedBooking = {
    booking_id: 1,
    package_id: 3,
    customerName: "sk Imran",
    destination: "Singapore",
    startDate: "Jul 01, 2025",
    endDate: "Jul 09, 2025",
    pax: 3,
    payment: 2400,
    paymentStatus: "Paid",
    status: 1,
    booking_details: `{
      "adult_count": 3,
      "child_count": 0,
      "male_count": 1,
      "female_count": 2,
      "total_price": 2400,
      "currency": "USD",
      "travel_dates": {
        "check_in": "2025-07-01",
        "check_out": "2025-07-09"
      }
    }`,
    travel_dates: `{
      "check_in": "2025-07-01",
      "check_out": "2025-07-09"
    }`,
    hotels: [
      {
        hotel_unique_id: "f8194e9.49080741",
        name: "Taj Hotel Kolkata",
        main_image: "https://stgdmcappdev.blob.core.windows.net/uploads/logo_1745728991_97kjTK.jpg",
        images: "[\"https://nawajstorage.s3.eu-north-1.amazonaws.com/uploads/logo_1745728992_cmA8F4.jpg\"]",
        address: "New Town",
        phone: "01234567890",
        email: "taj@gmail.com",
        latitude: "22.62206",
        longitude: "88.44739"
      }
    ],
    attractions: [
      {
        attraction_id: 4,
        name: "Marina Bay Sands",
        master_image: "https://stgdmcappdev.blob.core.windows.net/uploads/logo_1745845447_HUk4Sc.webp",
        additional_image: "[\"https://nawajstorage.s3.eu-north-1.amazonaws.com/uploads/logo_1745845444_Wciq2g.webp\"]",
        location: "Singapore",
        latitude: 1.2837562,
        longitude: 103.7767045
      }
    ],
    guides: [
      {
        guide_id: 8,
        name: "Kunal",
        image: "https://stgdmcappdev.blob.core.windows.net/uploads/logo_1747428130_y0DrRf.jpeg",
        contact_no: "74893300",
        email: "kunalxyz42@gmail.com",
        languages: [
          "Spanish",
          "Korean",
          "Chinese"
        ]
      }
    ],
    restaurants: [
      {
        restaurant_id: 1,
        name: "Rang Mahal",
        master_image: "https://stgdmcappdev.blob.core.windows.net/uploads/logo_1744729142_giB6Wx.jpeg",
        images: "[\"https://nawajstorage.s3.eu-north-1.amazonaws.com/uploads/logo_1744729141_4MU4w3.jpg\"]",
        city: "Singapore",
        latitude: 1.305817,
        longitude: 103.860307
      }
    ]
  };
  
  const handleOpenModal = () => {
    setOpenModal(true);
  };
  
  const handleCloseModal = () => {
    setOpenModal(false);
  };

  const toggleDataType = () => {
    setShowEnhanced(!showEnhanced);
  };

  // Get the current booking data based on selected type
  const currentBookingData = showEnhanced ? sampleEnhancedBooking : sampleBasicBooking;
  
  return (
    <Container maxWidth="md" sx={{ mt: 5 }}>
      <Paper elevation={2} sx={{ p: 4, borderRadius: '12px' }}>
        <Typography variant="h5" gutterBottom sx={{ fontWeight: 600 }}>
          Booking View Modal Test Page
        </Typography>
        
        <Typography paragraph>
          Test the booking view modal with two different data formats: basic and enhanced API data.
        </Typography>
        
        <Box sx={{ mt: 3, display: 'flex', flexDirection: 'column', gap: 2 }}>
          <Paper sx={{ p: 2, bgcolor: '#f8f9fa', borderRadius: '8px' }}>
            <Grid container spacing={2} alignItems="center">
              <Grid item xs={12} md={8}>
                <Typography variant="subtitle1" gutterBottom>
                  Current Mode: <strong>{showEnhanced ? 'Enhanced API Data' : 'Basic Data'}</strong>
                </Typography>
                <Typography variant="body2" color="text.secondary">
                  {showEnhanced 
                    ? 'Using complete API response with hotels, attractions, guides, and restaurants.' 
                    : 'Using simplified booking data with just basic fields.'}
                </Typography>
              </Grid>
              <Grid item xs={12} md={4}>
                <Button 
                  variant="outlined" 
                  color="primary"
                  onClick={toggleDataType}
                  fullWidth
                >
                  Switch to {showEnhanced ? 'Basic Data' : 'Enhanced API Data'}
                </Button>
              </Grid>
            </Grid>
          </Paper>
          
          <Box sx={{ p: 2, bgcolor: '#f5f5f5', borderRadius: '8px', maxHeight: '300px', overflow: 'auto' }}>
            <Typography variant="subtitle1" gutterBottom>
              Sample Booking Data:
            </Typography>
            <pre style={{ whiteSpace: 'pre-wrap', fontSize: '0.8rem' }}>
              {JSON.stringify(currentBookingData, null, 2)}
            </pre>
          </Box>
          
          <Button 
            variant="contained" 
            color="primary" 
            onClick={handleOpenModal}
            sx={{ alignSelf: 'flex-start', mt: 2 }}
          >
            Open Booking Modal
          </Button>
        </Box>
        
        {/* BookingViewModal component */}
        <BookingViewModal
          open={openModal}
          onClose={handleCloseModal}
          bookingData={currentBookingData}
        />
      </Paper>
    </Container>
  );
};

export default ModalTestPage; 