import React, { useMemo, useState } from 'react';
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
  Stepper,
  Step,
  StepLabel,
  StepContent,
  IconButton,
  TextField,
  Checkbox,
  FormControlLabel,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Tabs,
  Tab,
  CircularProgress,
  Snackbar,
  Alert
} from '@mui/material';
import { useSelector, useDispatch } from 'react-redux';
import moment from 'moment';
import HotelIcon from '@mui/icons-material/Hotel';
import RestaurantIcon from '@mui/icons-material/Restaurant';
import AttractionsIcon from '@mui/icons-material/Attractions';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import PersonIcon from '@mui/icons-material/Person';
import AddIcon from '@mui/icons-material/Add';
import EditIcon from '@mui/icons-material/Edit';
import EventIcon from '@mui/icons-material/Event';
import AirportShuttleIcon from '@mui/icons-material/AirportShuttle';
import CommentIcon from '@mui/icons-material/Comment';
import RoomIcon from '@mui/icons-material/Room';
import ContentCopyIcon from '@mui/icons-material/ContentCopy';
import DeleteIcon from '@mui/icons-material/Delete';
import ShoppingCartIcon from '@mui/icons-material/ShoppingCart';
import { BookPackageEnquiry } from '../../../slice/tour-packages/tourPackageSlice';

// Import all service components
import HotelComponent from '../../Tour-Packages/hotel';
import AttractionComponent from '../../Tour-Packages/attraction';
import RestaurantComponent from '../../Tour-Packages/Restaurants';
import GuideComponent from '../../Tour-Packages/Guide';
import TransportComponent from '../../Tour-Packages/Local-Transport';
import PickupDropComponent from '../../Tour-Packages/PickUp-Drop';
import SimpleCustomerInfo from './SimpleCustomerInfo';
import ServicesSummaryModal from './ServicesSummaryModal';

export default function Itinerary() {
  // Get data from Redux store
  const { searchCriteria } = useSelector((state) => state.tourPackages);
  const { hotels } = useSelector((state) => state.hotels);
  const selectedCity = useSelector(state => state.common?.selectedCity?.cityName);
  const selectedCountry = useSelector(state => state.common?.selectedCity?.countryName);
  const [portType,setPortType] = useState("Entry Port");
  const [portType1,setPortType1] = useState("Exit Port");
  const dispatch = useDispatch();
  
  // State for active service tab
  const [activeServiceTab, setActiveServiceTab] = useState(0);
  
  // State for summary modal
  const [summaryModalOpen, setSummaryModalOpen] = useState(false);
  
  // State for snackbar
  const [openSnackbar, setOpenSnackbar] = useState(false);
  const [snackbarMessage, setSnackbarMessage] = useState('');
  const [snackbarSeverity, setSnackbarSeverity] = useState('success');
  const { loading, error, packageEnquiryId } = useSelector((state) => state.tourPackages);
  
  // Generate dates array from the selected date range
  const dates = useMemo(() => {
    if (!searchCriteria?.checkIn || !searchCriteria?.checkOut) return [];
    
    const startDate = moment(searchCriteria.checkIn, 'DD/MM/YYYY');
    const endDate = moment(searchCriteria.checkOut, 'DD/MM/YYYY');
    const dayDiff = endDate.diff(startDate, 'days');

    // Generate an array of all dates in the range
    const datesArray = [];
    for (let i = 0; i <= dayDiff; i++) {
      datesArray.push(moment(startDate).add(i, 'days'));
    }
    return datesArray;
  }, [searchCriteria]);
  
  // Mock hotel data for the top section
  const mockHotel = {
    name: "Hotel Boss",
    location: "Little India • 3 Star",
    image: "https://source.unsplash.com/random/600x400/?hotel"
  };

  // If no dates available, show a message
  if (dates.length === 0) {
    return (
      <Paper sx={{ p: 4, textAlign: 'center' }}>
        <Typography variant="h5">
          Please select travel dates to view your itinerary
        </Typography>
      </Paper>
    );
  }

  // Function to handle booking
  const handleBookPackage = () => {
    dispatch(BookPackageEnquiry())
      .unwrap()
      .then((result) => {
        setSnackbarMessage('Package booked successfully!');
        setSnackbarSeverity('success');
        setOpenSnackbar(true);
      })
      .catch((error) => {
        setSnackbarMessage(error?.message || 'Failed to book package. Please try again.');
        setSnackbarSeverity('error');
        setOpenSnackbar(true);
      });
  };
  
  const handleCloseSnackbar = () => {
    setOpenSnackbar(false);
  };

  return (
    <Box>
      {/* Top Level Hotel Section */}
      <Paper elevation={3} sx={{ p: 3, mb: 4, borderRadius: 2 }}>
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
          <Typography variant="h5">Hotels</Typography>
        </Box>
        
        {/* <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
          Please add hotels details (if included in package) with services provided for each hotels and the selling cost price.
        </Typography>
        
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
          <Typography variant="body2" color="text.secondary" sx={{ mr: 1 }}>
            Tip: To speed up the process of adding multiple hotels, use
          </Typography>
          <Button size="small" variant="text" color="primary">Next Night</Button>
          <Typography variant="body2" color="text.secondary" sx={{ mx: 1 }}>or</Typography>
          <Button size="small" variant="text" color="primary">Duplicate</Button>
          <Typography variant="body2" color="text.secondary">actions.</Typography>
        </Box> */}
        
        {/* Hotel Component */}
        <HotelComponent />

      </Paper>

      
      
      {/* Transports and Activities Section */}
      <Paper elevation={3} sx={{ p: 3, mb: 4, borderRadius: 2 }}>
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
          <Typography variant="h5">Transports and Other Services</Typography>
        </Box>
        
        
        {/* Day by Day Transportation and Activities */}
        {dates.map((date, index) => (
          <Box key={index} sx={{ mb: 4 }}>
            {/* Day Header */}
            <Box 
              sx={{ 
                bgcolor: 'primary.main', 
                color: 'white', 
                p: 1.5, 
                borderRadius: 1,
                mb: 2
              }}
            >
              <Typography variant="h6">
                Day {index + 1}, {date.format('Do MMMM')}, {date.format('dddd')}
              </Typography>
            </Box>

            {/* First Day - Place Port Component at the beginning */}
            {index === 0 && (
  <Box sx={{ mb: 2 }}>
    <Paper elevation={2} sx={{ p: 2, borderLeft: '4px solid #1976d2' }}>
      {/* <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 1 }}>Arrival</Typography> */}
      <PickupDropComponent portType={portType} setPortType={() => setPortType("Entry Port")} />
    </Paper>
  </Box>
)}


            {/* Attraction Component */}
            <Box sx={{ mb: 2 }}>
              <Paper elevation={1} sx={{ p: 0, borderLeft: '4px solid #f44336' }}>
                
                <AttractionComponent />
              </Paper>
            </Box>

            {/* Guide Component */}
            <Box sx={{ mb: 2 }}>
              <Paper elevation={2} sx={{ p: 2, borderLeft: '4px solid #2196f3' }}>
                {/* <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 1 }}>Guide</Typography> */}
                <GuideComponent />
              </Paper>
            </Box>

            {/* Restaurant Component */}
            <Box sx={{ mb: 2 }}>
              <Paper elevation={2} sx={{ p: 2, borderLeft: '4px solid #ff9800' }}>
                <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 1 }}>Restaurant</Typography>
                <RestaurantComponent />
              </Paper>
            </Box>

            {/* Transport Component */}
            <Box sx={{ mb: 2 }}>
              <Paper elevation={2} sx={{ p: 2, borderLeft: '4px solid #2196f3' }}>
                <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 1 }}>Transport</Typography>
                <TransportComponent dayIndex={index} />
              </Paper>
            </Box>

            {/* Last Day - Place Port Component at the end */}
            {index === dates.length - 1 && (
  <Box sx={{ mb: 2 }}>
    <Paper elevation={2} sx={{ p: 2, borderLeft: '4px solid #1976d2' }}>
      {/* <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 1 }}>Departure</Typography> */}
      <PickupDropComponent portType1={portType1} setPortType1={() => setPortType1("Exit Port")} />
    </Paper>
  </Box>
)}
            
            {index < dates.length - 1 && <Divider sx={{ my: 3 }} />}
          </Box>
        ))}
      </Paper>
      
      {/* Customer Information Section - Added just before Trip Summary */}
      <SimpleCustomerInfo />
      
      {/* Summary section */}
      <Paper elevation={3} sx={{ mt: 4, p: 3 }}>
        <Typography variant="h6" gutterBottom>Trip Summary</Typography>
        <Grid container spacing={2}>
          <Grid item xs={12} md={8}>
            <Typography variant="body1">
              {dates.length} day{dates.length !== 1 ? 's' : ''} in {selectedCity}, {selectedCountry}
            </Typography>
            <Typography variant="body2" color="text.secondary">
              {searchCriteria.guests?.adults || 1} Adult{parseInt(searchCriteria.guests?.adults) !== 1 ? 's' : ''} •
              {searchCriteria.guests?.children > 0 ? ` ${searchCriteria.guests.children} Child${parseInt(searchCriteria.guests.children) !== 1 ? 'ren' : ''} •` : ''}
              {dates.length - 1} Night{dates.length - 1 !== 1 ? 's' : ''}
            </Typography>
          </Grid>
          <Grid item xs={12} md={4} sx={{ display: 'flex', justifyContent: 'flex-end', alignItems: 'center', gap: 2 }}>
            <Button 
              variant="outlined" 
              color="primary" 
              size="large"
              onClick={() => setSummaryModalOpen(true)}
            >
              View Summary
            </Button>
            <Button variant="contained" color="primary" size="large">
              Save Itinerary
            </Button>
          </Grid>
        </Grid>
      </Paper>
      
      {/* Book Package Button Section - Improved */}
      <Paper 
        elevation={3} 
        sx={{ 
          mt: 4, 
          p: 4, 
          display: 'flex', 
          flexDirection: 'column', 
          alignItems: 'center', 
          borderRadius: 2,
          background: 'linear-gradient(to right, rgba(255,255,255,0.95), rgba(245,245,255,0.95))',
          boxShadow: '0 8px 32px rgba(0,0,0,0.1)'
        }}
      >
        <Typography variant="h5" gutterBottom sx={{ mb: 2, fontWeight: 600 }}>
          Ready to book this amazing package?
        </Typography>
        <Button 
          variant="contained" 
          color="primary" 
          size="large" 
          disabled={loading}
          startIcon={loading ? null : <ShoppingCartIcon />}
          onClick={handleBookPackage}
          sx={{
            py: 1.5,
            px: 4,
            fontSize: '1.1rem',
            fontWeight: 600,
            borderRadius: '50px',
            background: 'linear-gradient(45deg, #3554D1 30%, #5672E9 90%)',
            boxShadow: '0 10px 20px rgba(53, 84, 209, 0.3)',
            transition: 'all 0.3s ease',
            textTransform: 'none',
            '&:hover': {
              transform: 'translateY(-3px)',
              boxShadow: '0 15px 30px rgba(53, 84, 209, 0.4)',
            },
            minWidth: 200,
          }}
        >
          {loading ? <CircularProgress size={24} color="inherit" /> : 'Book Package Now'}
        </Button>
        {packageEnquiryId && (
          <Typography variant="body1" sx={{ mt: 2, color: 'success.main', fontWeight: 500 }}>
            Booking ID: {packageEnquiryId}
          </Typography>
        )}
      </Paper>
      
      {/* Snackbar for notifications */}
      <Snackbar 
        open={openSnackbar} 
        autoHideDuration={6000} 
        onClose={handleCloseSnackbar}
        anchorOrigin={{ vertical: 'top', horizontal: 'center' }}
      >
        <Alert 
          onClose={handleCloseSnackbar} 
          severity={snackbarSeverity} 
          sx={{ 
            width: '100%',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            borderRadius: 2
          }}
        >
          {snackbarMessage}
        </Alert>
      </Snackbar>
      
      {/* Services Summary Modal */}
      <ServicesSummaryModal 
        open={summaryModalOpen} 
        onClose={() => setSummaryModalOpen(false)} 
      />
    </Box>
  );
} 