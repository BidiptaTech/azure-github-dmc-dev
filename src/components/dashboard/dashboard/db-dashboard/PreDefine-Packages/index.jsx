import { useState } from "react";
import { Box, Tab, Tabs, Typography, Paper } from "@mui/material";

// Tab Panel component
function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`package-tabpanel-${index}`}
      aria-labelledby={`package-tab-${index}`}
      {...other}
      style={{ padding: "20px 0" }}
    >
      {value === index && children}
    </div>
  );
}

function a11yProps(index) {
  return {
    id: `package-tab-${index}`,
    "aria-controls": `package-tabpanel-${index}`,
  };
}

const PreDefinePackages = () => {
  const [tabValue, setTabValue] = useState(0);

  const handleTabChange = (event, newValue) => {
    setTabValue(newValue);
  };

  return (
    <Box sx={{ width: "100%" }}>
      <Box sx={{ borderBottom: 1, borderColor: "divider", mb: 2 }}>
        <Tabs
          value={tabValue}
          onChange={handleTabChange}
          aria-label="package tabs"
          indicatorColor="primary"
          textColor="primary"
        >
          <Tab label="Ongoing" {...a11yProps(0)} />
          <Tab label="Upcoming" {...a11yProps(1)} />
          <Tab label="Past" {...a11yProps(2)} />
        </Tabs>
      </Box>
      
      <TabPanel value={tabValue} index={0}>
        <Paper elevation={0} sx={{ p: 2 }}>
          <Typography variant="h6" gutterBottom>
            Ongoing Packages
          </Typography>
          {/* Add your ongoing packages content here */}
          <Typography>
            No ongoing packages available at the moment.
          </Typography>
        </Paper>
      </TabPanel>
      
      <TabPanel value={tabValue} index={1}>
        <Paper elevation={0} sx={{ p: 2 }}>
          <Typography variant="h6" gutterBottom>
            Upcoming Packages
          </Typography>
          {/* Add your upcoming packages content here */}
          <Typography>
            No upcoming packages available at the moment.
          </Typography>
        </Paper>
      </TabPanel>
      
      <TabPanel value={tabValue} index={2}>
        <Paper elevation={0} sx={{ p: 2 }}>
          <Typography variant="h6" gutterBottom>
            Past Packages
          </Typography>
          {/* Add your past packages content here */}
          <Typography>
            No past packages available at the moment.
          </Typography>
        </Paper>
      </TabPanel>
    </Box>
  );
};

export default PreDefinePackages;
