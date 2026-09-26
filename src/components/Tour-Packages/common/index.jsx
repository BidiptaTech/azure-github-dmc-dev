import React, { useState } from 'react';
import { 
  Box, 
  Typography, 
  Tabs, 
  Tab, 
  Paper, 
  Container, 
} from '@mui/material';
import { 
  Hotel, 
  Attractions, 
  Restaurant, 
  Tour, 
  LocalTaxi, 
  DirectionsBus 
} from '@mui/icons-material';

// Import individual components
import HotelComponent from '../hotel';
import AttractionComponent from '../attraction';
import RestaurantComponent from '../Restaurants';
import GuideComponent from '../Guide';
import PickupDropComponent from '../PickUp-Drop';
import LocalTransportComponent from '../Local-Transport';
import SearchForm from './SearchForm';

function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`tabpanel-${index}`}
      aria-labelledby={`tab-${index}`}
      {...other}
    >
      {value === index && (
        <Box sx={{ p: 3 }}>
          {children}
        </Box>
      )}
    </div>
  );
}

export default function TourPackages() {
  const [tabValue, setTabValue] = useState(0);
  const [searchParams, setSearchParams] = useState(null);

  const handleChange = (event, newValue) => {
    setTabValue(newValue);
  };

  const handleSearch = (params) => {
    console.log('Search params:', params);
    setSearchParams(params);
    // In a real application, you would use these params to fetch data
  };

  return (
    <Container maxWidth="lg" sx={{ py: 4 }}>
      <Typography variant="h3" component="h1" gutterBottom align="center" sx={{ fontWeight: 'bold', mb: 4 }}>
        Tour Package Components
      </Typography>
      
      {/* Search Form */}
      <Box sx={{ mb: 4 }}>
        <SearchForm onSearch={handleSearch} />
      </Box>
      
      <Paper elevation={3} sx={{ mb: 4 }}>
        <Tabs 
          value={tabValue} 
          onChange={handleChange} 
          variant="scrollable" 
          scrollButtons="auto"
          sx={{ 
            borderBottom: 1, 
            borderColor: 'divider',
            '& .MuiTab-root': { fontSize: '1rem', py: 2 }
          }}
        >
          <Tab icon={<Hotel />} label="Hotels" iconPosition="start" />
          <Tab icon={<Attractions />} label="Attractions" iconPosition="start" />
          <Tab icon={<Restaurant />} label="Restaurants" iconPosition="start" />
          <Tab icon={<Tour />} label="Tour Guides" iconPosition="start" />
          <Tab icon={<LocalTaxi />} label="Pickup & Drop" iconPosition="start" />
          <Tab icon={<DirectionsBus />} label="Local Transport" iconPosition="start" />
        </Tabs>

        {/* Hotels Tab */}
        <TabPanel value={tabValue} index={0}>
          <HotelComponent searchParams={searchParams} />
        </TabPanel>

        {/* Attractions Tab */}
        <TabPanel value={tabValue} index={1}>
          <AttractionComponent />
        </TabPanel>

        {/* Restaurants Tab */}
        <TabPanel value={tabValue} index={2}>
          <RestaurantComponent />
        </TabPanel>

        {/* Tour Guides Tab */}
        <TabPanel value={tabValue} index={3}>
          <GuideComponent />
        </TabPanel>

        {/* Pickup & Drop Tab */}
        <TabPanel value={tabValue} index={4}>
          <PickupDropComponent />
        </TabPanel>

        {/* Local Transport Tab */}
        <TabPanel value={tabValue} index={5}>
          <LocalTransportComponent />
        </TabPanel>
      </Paper>
    </Container>
  );
}
