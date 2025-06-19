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
  Tab
} from '@mui/material';
import { useSelector } from 'react-redux';
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

// Import all service components
import HotelComponent from '../../Tour-Packages/hotel';
import AttractionComponent from '../../Tour-Packages/attraction';
import RestaurantComponent from '../../Tour-Packages/Restaurants';
import GuideComponent from '../../Tour-Packages/Guide';
import TransportComponent from '../../Tour-Packages/Local-Transport';
import PickupDropComponent from '../../Tour-Packages/PickUp-Drop';

export default function Itinerary() {
  // Get data from Redux store
  const { searchCriteria } = useSelector((state) => state.tourPackages);
  const { hotels } = useSelector((state) => state.hotels);
  const selectedCity = useSelector(state => state.common?.selectedCity?.cityName);
  const selectedCountry = useSelector(state => state.common?.selectedCity?.countryName);
  const [portType,setPortType] = useState("Entry Port");
  const [portType1,setPortType1] = useState("Exit Port");
  
  // State for active service tab
  const [activeServiceTab, setActiveServiceTab] = useState(0);
  
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
        
        <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
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
        </Box>
        
        {/* Hotel Component */}
        <HotelComponent />
{/*         
        <Grid container spacing={2} sx={{ mb: 3 }}>
          <Grid item xs={12} md={4}>
            <Typography variant="subtitle1" fontWeight={500}>Stay Nights</Typography>
            <Box sx={{ mt: 2 }}>
              {dates.slice(0, -1).map((date, index) => (
                <FormControlLabel 
                  key={index} 
                  control={<Checkbox defaultChecked />} 
                  label={`${index + 1}st N (${date.format('DD MMM')})`} 
                  sx={{ display: 'block', mb: 0.5 }}
                />
              ))}
            </Box>
          </Grid>
          
          <Grid item xs={12} md={4}>
            <Typography variant="subtitle1" fontWeight={500}>Hotel</Typography>
            <Box sx={{ mt: 2, p: 1, border: '1px solid #e0e0e0', borderRadius: 1 }}>
              <Typography variant="body2">{mockHotel.name}</Typography>
              <Typography variant="caption" color="text.secondary">{mockHotel.location}</Typography>
            </Box>
          </Grid>
          
          <Grid item xs={12} md={4}>
            <Typography variant="subtitle1" fontWeight={500}>Meal Plan</Typography>
            <Box sx={{ mt: 2, p: 1, border: '1px solid #e0e0e0', borderRadius: 1 }}>
              <Typography variant="body2">Self Booked</Typography>
            </Box>
          </Grid>
        </Grid>
        
        <Grid container spacing={2} sx={{ mb: 3 }}>
          <Grid item xs={6} md={2}>
            <Typography variant="subtitle2">Pax/room</Typography>
            <TextField 
              fullWidth 
              size="small" 
              value="2" 
              variant="outlined" 
              sx={{ mt: 1 }}
              InputProps={{
                readOnly: true,
              }}
            />
          </Grid>
          
          <Grid item xs={6} md={2}>
            <Typography variant="subtitle2">No. of rooms</Typography>
            <TextField 
              fullWidth 
              size="small" 
              value="8" 
              variant="outlined" 
              sx={{ mt: 1 }}
              InputProps={{
                readOnly: true,
              }}
            />
          </Grid>
          
          <Grid item xs={6} sm={3} md={2}>
            <Typography variant="subtitle2">AWEB</Typography>
            <TextField 
              fullWidth 
              size="small" 
              value="0" 
              variant="outlined" 
              sx={{ mt: 1 }}
              InputProps={{
                readOnly: true,
              }}
            />
          </Grid>
          
          <Grid item xs={6} sm={3} md={2}>
            <Typography variant="subtitle2">CWEB</Typography>
            <TextField 
              fullWidth 
              size="small" 
              value="0" 
              variant="outlined" 
              sx={{ mt: 1 }}
              InputProps={{
                readOnly: true,
              }}
            />
          </Grid>
          
          <Grid item xs={6} sm={3} md={2}>
            <Typography variant="subtitle2">CNB</Typography>
            <TextField 
              fullWidth 
              size="small" 
              value="0" 
              variant="outlined" 
              sx={{ mt: 1 }}
              InputProps={{
                readOnly: true,
              }}
            />
          </Grid>
          
          <Grid item xs={6} sm={3} md={2}>
            <Typography variant="subtitle2">Comp Child</Typography>
            <Box sx={{ display: 'flex', alignItems: 'center', mt: 1 }}>
              <Typography variant="body2">Upto 5y</Typography>
              <Typography variant="caption" color="text.secondary" sx={{ ml: 1 }}>(OC)</Typography>
            </Box>
          </Grid>
        </Grid>
        
        <Box sx={{ display: 'flex', justifyContent: 'space-between' }}>
          <Button variant="outlined" color="primary" startIcon={<AddIcon />}>
            Add Similar Hotels
          </Button>
          
          <Box>
            <Button variant="outlined" color="primary" startIcon={<ContentCopyIcon />}>
              Duplicate
            </Button>
            <Button variant="outlined" color="error" startIcon={<DeleteIcon />} sx={{ ml: 1 }}>
              Remove
            </Button>
          </Box>
        </Box> */}
      </Paper>

      {/* Pickup & Drop Component */}
      {/* <Paper elevation={3} sx={{ p: 3, mb: 4, borderRadius: 2 }}>
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
          <Typography variant="h5"> Entry/Exit Port</Typography>
        </Box>
          <PickupDropComponent startDate={searchCriteria?.checkIn} endDate={searchCriteria?.checkOut} />
          </Paper> */}
      
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
      <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 1 }}>Entry Port</Typography>
      <PickupDropComponent portType={portType} setPortType={() => setPortType("Entry Port")} />
    </Paper>
  </Box>
)}


            {/* Attraction Component */}
            <Box sx={{ mb: 2 }}>
              <Paper elevation={2} sx={{ p: 2, borderLeft: '4px solid #f44336' }}>
                <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 1 }}>Attraction</Typography>
                <AttractionComponent />
              </Paper>
            </Box>

            {/* Guide Component */}
            <Box sx={{ mb: 2 }}>
              <Paper elevation={2} sx={{ p: 2, borderLeft: '4px solid #4caf50' }}>
                <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 1 }}>Guide</Typography>
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
                <TransportComponent />
              </Paper>
            </Box>

            {/* Last Day - Place Port Component at the end */}
            {index === dates.length - 1 && (
  <Box sx={{ mb: 2 }}>
    <Paper elevation={2} sx={{ p: 2, borderLeft: '4px solid #1976d2' }}>
      <Typography variant="subtitle1" fontWeight={500} sx={{ mb: 1 }}>Exit Port</Typography>
      <PickupDropComponent portType1={portType1} setPortType1={() => setPortType1("Exit Port")} />
    </Paper>
  </Box>
)}
            
            {index < dates.length - 1 && <Divider sx={{ my: 3 }} />}
          </Box>
        ))}
      </Paper>
      
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
          <Grid item xs={12} md={4} sx={{ display: 'flex', justifyContent: 'flex-end', alignItems: 'center' }}>
            <Button variant="contained" color="primary" size="large">
              Save Itinerary
            </Button>
          </Grid>
        </Grid>
      </Paper>
    </Box>
  );
} 