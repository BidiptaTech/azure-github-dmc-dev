import React, { useEffect, useState } from "react";
import { useSelector, useDispatch } from "react-redux";
import { fetchLists } from "@/slice/common/TourlistSlice";
import { fetchAgentList } from '@/slice/common/agentListSlice';

import { 
  Container, 
  Box, 
  Typography,
  Paper,
  FormControl,
  InputLabel,
  Select,
  MenuItem
} from "@mui/material";
import { Grid, Tab, Tabs } from "@mui/material";
import {
    EventNote,
  EmailOutlined
} from "@mui/icons-material";
import BasicTabs from "./TabStatus";

import EnquiryList from "./EnquiryList";
import { fetchEnquiries } from "@/slice/enquiries/enquiryListSlice";

const SalesManagerDashboard = () => {
  const dispatch = useDispatch();
    const [mainTabValue, setMainTabValue] = useState(0);

      const handleMainTabChange = (event, newValue) => {
    setMainTabValue(newValue);
  };
    const {agents,error} =useSelector((state)=>state.agentList)
    // console.log(agents,"agentlist");
    
      const [selectedAgent, setSelectedAgent] = useState('');

        useEffect(()=>{
     
         dispatch(fetchAgentList())
            dispatch(fetchEnquiries())
     
  },[dispatch])
   const handleChange = (event) => {
      const agentId =event.target.value
      setSelectedAgent(agentId);
      dispatch(fetchEnquiries(agentId))
      dispatch(fetchLists(agentId))
      console.log('Selected agent:', event.target.value);
    };
  // useEffect(() => {
  //   dispatch(fetchLists());

  // }, [dispatch]);
  function TabPanel(props) {
    const { children, value, index, ...other } = props;
  
    return (
      <div
        role="tabpanel"
        hidden={value !== index}
        id={`dashboard-tabpanel-${index}`}
        aria-labelledby={`dashboard-tab-${index}`}
        {...other}
        style={{ padding: "20px 0" }}
      >
        {value === index && <Box>{children}</Box>}
      </div>
    );
  }
function a11yProps(index) {
  return {
    id: `dashboard-tab-${index}`,
    "aria-controls": `dashboard-tabpanel-${index}`,
  };
}


  return (
    <Container maxWidth="xl">
     <Box
  sx={{
    backgroundColor: "white",
    borderRadius: "12px 12px 0 0",
    boxShadow: "0 2px 10px rgba(0,0,0,0.05)",
    padding: "20px",
    mb: 2,
    display: "flex",
    justifyContent: "space-between",
    alignItems: "center",
    flexWrap: "wrap",
    gap: 2,
  }}
>
  {/* Left Section with Icon + Title */}
  <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
    <EmailOutlined color="primary" sx={{ fontSize: 28 }} />
    <Typography variant="h5" component="h1" fontWeight="bold">
      Booking Enquiries
    </Typography>
  </Box>

  {/* Right Section: Select Agent Dropdown */}
  <FormControl
    variant="outlined"
    size="small"
    sx={{ minWidth: 220 }}
  >
    <InputLabel id="agent-select-label">Select Agent</InputLabel>
    <Select
      labelId="agent-select-label"
      id="agent-select"
      value={selectedAgent}
      onChange={handleChange}
      label="Select Agent"
    >
      {/* Default Option */}
      <MenuItem value="">
        All Agents
      </MenuItem>

      {/* Agent List */}
      {agents.map((agent) => (
        <MenuItem key={agent.id} value={agent.agent_id}>
          {agent.name}
        </MenuItem>
      ))}
    </Select>
  </FormControl>
</Box>


       <Box
          sx={{
            borderBottom: 1,
            borderColor: "divider",
            backgroundColor: "white",
            borderRadius: "12px 12px 0 0",
            boxShadow: "0 2px 10px rgba(0,0,0,0.05)",
            padding: "0 20px",
          }}
        >
          <Tabs
            value={mainTabValue}
            onChange={handleMainTabChange}
            aria-label="dashboard tabs"
            indicatorColor="primary"
            textColor="primary"
            sx={{
              "& .MuiTab-root": {
                textTransform: "none",
                fontWeight: 500,
                fontSize: "15px",
                minHeight: "56px",
                display: "flex",
                flexDirection: "row",
                alignItems: "center",
                gap: "8px",
              },
            }}
          >
            <Tab
              icon={<EventNote />}
              label="Bookings Details"
              iconPosition="start"
              {...a11yProps(0)}
            />
            <Tab 
              icon={<EmailOutlined />} 
              label="Booking Enquiries" 
              iconPosition="start" 
              {...a11yProps(1)} 
            />
            {/* <Tab 
              icon={<AssessmentOutlined />} 
              label="Booking Overview" 
              iconPosition="start" 
              {...a11yProps(1)} 
            />
            <Tab 
              icon={<MoreHoriz />} 
              label="Recent Bookings" 
              iconPosition="start" 
              {...a11yProps(2)} 
            /> */}
          </Tabs>
        </Box>

        <TabPanel value={mainTabValue} index={0} sx={{ p: 0 }}>
          {/* Insert your booking details content here */}
          <BasicTabs />
        </TabPanel>
        
        <TabPanel value={mainTabValue} index={1} sx={{ p: 0 }}>
          {/* Insert your booking enquiries content here */}
          <Box sx={{ p: 3, backgroundColor: "white", borderRadius: "0 0 12px 12px" }}>
            <EnquiryList />
          </Box>
        </TabPanel>
    </Container>
  );
};

export default SalesManagerDashboard; 