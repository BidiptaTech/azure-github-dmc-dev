import React, { useState } from 'react';
import { Box, Typography, Paper } from '@mui/material';
import ResponsiveTabs from './ResponsiveTabs';
import {
  EventNote,
  EmailOutlined,
  CardGiftcardOutlined,
  AssessmentOutlined,
  MoreHoriz,
} from '@mui/icons-material';

const ResponsiveTabsDemo = () => {
  const [activeTab, setActiveTab] = useState(0);

  const tabs = [
    {
      icon: <EventNote />,
      label: "Bookings Details"
    },
    {
      icon: <EmailOutlined />,
      label: "Quick Enquiries"
    },
    {
      icon: <CardGiftcardOutlined />,
      label: "Fixed Itinerary Packages"
    },
    {
      icon: <AssessmentOutlined />,
      label: "Analytics"
    },
    {
      icon: <MoreHoriz />,
      label: "More Options"
    }
  ];

  const handleTabChange = (event, newValue) => {
    setActiveTab(newValue);
  };

  const tabContents = [
    <Box key="tab-0" sx={{ p: 3 }}>
      <Typography variant="h6" gutterBottom>
        Bookings Details Content
      </Typography>
      <Typography variant="body1">
        This is the content for the Bookings Details tab. 
        On mobile and tablet devices, this tab system will collapse into a dropdown menu.
      </Typography>
    </Box>,
    <Box key="tab-1" sx={{ p: 3 }}>
      <Typography variant="h6" gutterBottom>
        Quick Enquiries Content
      </Typography>
      <Typography variant="body1">
        This is the content for the Quick Enquiries tab.
        The responsive design ensures optimal viewing on all screen sizes.
      </Typography>
    </Box>,
    <Box key="tab-2" sx={{ p: 3 }}>
      <Typography variant="h6" gutterBottom>
        Fixed Itinerary Packages Content
      </Typography>
      <Typography variant="body1">
        This is the content for the Fixed Itinerary Packages tab.
        Users can easily switch between tabs using the dropdown on smaller screens.
      </Typography>
    </Box>,
    <Box key="tab-3" sx={{ p: 3 }}>
      <Typography variant="h6" gutterBottom>
        Analytics Content
      </Typography>
      <Typography variant="body1">
        This is the content for the Analytics tab.
        The responsive tabs adapt to different screen sizes automatically.
      </Typography>
    </Box>,
    <Box key="tab-4" sx={{ p: 3 }}>
      <Typography variant="h6" gutterBottom>
        More Options Content
      </Typography>
      <Typography variant="body1">
        This is the content for the More Options tab.
        All tabs are accessible through the dropdown menu on mobile devices.
      </Typography>
    </Box>
  ];

  return (
    <Paper elevation={2} sx={{ borderRadius: 2, overflow: 'hidden' }}>
      <ResponsiveTabs
        tabs={tabs}
        value={activeTab}
        onChange={handleTabChange}
        sx={{
          backgroundColor: "white",
        }}
      >
        {tabContents}
      </ResponsiveTabs>
    </Paper>
  );
};

export default ResponsiveTabsDemo;
