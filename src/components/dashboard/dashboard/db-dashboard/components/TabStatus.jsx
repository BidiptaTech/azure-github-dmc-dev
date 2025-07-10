import * as React from "react";
import { useState } from "react";
import { useSelector } from "react-redux";
import {
  Box,
  Card,
  Tabs,
  Tab,
  Badge,
} from "@mui/material";
import {
  DonutLarge,
  Upcoming as UpcomingIcon,
  History,
} from "@mui/icons-material";
import Pending from "./Pending";
import Upcoming from "./Upcoming";
import Completed from "./Completed";
import Deleted from "./Deleted";

// TabPanel component for accessibility
function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`simple-tabpanel-${index}`}
      aria-labelledby={`simple-tab-${index}`}
      {...other}
    >
      {value === index && <Box sx={{ p: 0 }}>{children}</Box>}
    </div>
  );
}

function a11yProps(index) {
  return {
    id: `simple-tab-${index}`,
    'aria-controls': `simple-tabpanel-${index}`,
  };
}

export default function TabStatus() {
  const [tabValue, setTabValue] = useState(0);

  // Get actual data from Redux store
  const { upcomingTours = [] } = useSelector((state) => state.lists);
  const { pendingTours = [] } = useSelector((state) => state.lists);
  const { lists = [] } = useSelector((state) => state.lists);

  const handleTabChange = (event, newValue) => {
    setTabValue(newValue);
  };

  // Calculate actual counts from the data
  const tabCounts = {
    ongoing: upcomingTours.length,
    upcoming: pendingTours.length,
    past: lists.length
  };

  return (
    <Box sx={{ width: "100%" }}>
      <Card sx={{ mb: 3, overflow: 'hidden' }}>
        <Tabs
          value={tabValue}
          onChange={handleTabChange}
          aria-label="status tabs"
          indicatorColor="primary"
          textColor="primary"
          variant="fullWidth"
          sx={{
            background: 'linear-gradient(to right, #f5f7fa, #f9fcff)',
            '& .MuiTab-root': {
              fontWeight: 600,
              py: 2.5,
              textTransform: 'none',
              fontSize: '0.95rem',
              minHeight: '64px',
              transition: 'all 0.2s',
              '&:hover': {
                backgroundColor: 'rgba(67, 97, 238, 0.04)',
              },
              '&.Mui-selected': {
                color: '#4361ee',
                fontWeight: 700,
              }
            }
          }}
        >
          <Tab
            icon={<DonutLarge />}
            label={
              <Box sx={{ position: 'relative', pr: 3 }}>
                Ongoing
                <Badge
                  badgeContent={tabCounts.ongoing}
                  color="primary"
                  sx={{
                    position: 'absolute',
                    top: 0,
                    right: 14,
                    '& .MuiBadge-badge': {
                      fontSize: '10px',
                      height: '20px',
                      minWidth: '20px'
                    }
                  }}
                />
              </Box>
            }
            iconPosition="start"
            {...a11yProps(0)}
          />
          <Tab
            icon={<UpcomingIcon />}
            label={
              <Box sx={{ position: 'relative', pr: 3 }}>
                Upcoming
                <Badge
                  badgeContent={tabCounts.upcoming}
                  color="primary"
                  sx={{
                    position: 'absolute',
                    top: 0,
                    right: 14,
                    '& .MuiBadge-badge': {
                      fontSize: '10px',
                      height: '20px',
                      minWidth: '20px'
                    }
                  }}
                />
              </Box>
            }
            iconPosition="start"
            {...a11yProps(1)}
          />
          <Tab
            icon={<History />}
            label={
              <Box sx={{ position: 'relative', pr: 3 }}>
                Past
                <Badge
                  badgeContent={tabCounts.past}
                  color="primary"
                  sx={{
                    position: 'absolute',
                    top: 0,
                    right: 14,
                    '& .MuiBadge-badge': {
                      fontSize: '10px',
                      height: '20px',
                      minWidth: '20px'
                    }
                  }}
                />
              </Box>
            }
            iconPosition="start"
            {...a11yProps(2)}
          />
        </Tabs>
      </Card>

      <TabPanel value={tabValue} index={0}>
        <Card sx={{ mb: 3, p: 3, boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
          <Upcoming />
        </Card>
      </TabPanel>

      <TabPanel value={tabValue} index={1}>
        <Card sx={{ mb: 3, p: 3, boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
          <Pending />
        </Card>
      </TabPanel>

      <TabPanel value={tabValue} index={2}>
        <Card sx={{ mb: 3, p: 3, boxShadow: '0 2px 10px rgba(0,0,0,0.05)' }}>
          <Deleted />
        </Card>
      </TabPanel>
    </Box>
  );
}
