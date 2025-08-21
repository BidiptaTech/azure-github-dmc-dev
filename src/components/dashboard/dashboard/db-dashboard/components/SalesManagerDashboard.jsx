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
  MenuItem,
  Card,
  CardContent,
  Stack,
  Chip,
  Divider,
  Avatar,
  Fade,
  Grow
} from "@mui/material";
import { Grid, Tab, Tabs } from "@mui/material";
import {
    EventNote,
  EmailOutlined,
  CardGiftcardOutlined,
  People,
  FilterList,
  Analytics,
  TrendingUp,
  Assessment
} from "@mui/icons-material";
import BasicTabs from "./TabStatus";

import EnquiryList from "./EnquiryList";
import { fetchEnquiries } from "@/slice/enquiries/enquiryListSlice";
import PreDefinePackages from "../PreDefine-Packages";
import { setAgentId } from "@/slice/common/EditSlice";
import { AnimatedBox, StaggeredContainer } from "@/components/dashboard/DashboardAnimations";

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
            dispatch(fetchEnquiries({ reset: true, start: 0, limit: 30 }))
     
  },[dispatch])
   const handleChange = (event) => {
      const agentId =event.target.value
      setSelectedAgent(agentId);
      dispatch(fetchEnquiries({ agentId, reset: true, start: 0, limit: 30 }))
      dispatch(setAgentId(agentId))
      dispatch(fetchLists({ agentId, reset: true }))
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
      <StaggeredContainer>
        <AnimatedBox direction="left" delay={200}>
          <Card
            elevation={0}
            sx={{
              background: "white",
              borderRadius: "16px",
              mb: 3,
              overflow: "hidden",
              position: "relative",
              boxShadow: "0 4px 20px rgba(0,0,0,0.08)",
              border: "1px solid rgba(0,0,0,0.05)",
            }}
          >
            <CardContent sx={{ p: 4 }}>
              <Stack direction="row" justifyContent="space-between" alignItems="center" spacing={3}>
                <Fade in={true} timeout={800}>
                  <Stack direction="row" alignItems="center" spacing={3}>
                    <Avatar
                      sx={{
                        width: 56,
                        height: 56,
                        background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                        boxShadow: "0 4px 12px rgba(102, 126, 234, 0.3)",
                      }}
                    >
                      <Analytics sx={{ fontSize: 28, color: "white" }} />
                    </Avatar>
                    
                    <Stack spacing={0.5}>
                      <Typography
                        variant="h4"
                        component="h1"
                        sx={{
                          fontWeight: 700,
                          color: "text.primary",
                          fontSize: { xs: "1.5rem", sm: "1.8rem" },
                        }}
                      >
                        Sales Management Hub
                      </Typography>
                      <Stack direction="row" alignItems="center" spacing={1}>
                        <TrendingUp sx={{ fontSize: 16, color: "text.secondary" }} />
                        <Typography
                          variant="body2"
                          sx={{
                            color: "text.secondary",
                            fontSize: "0.9rem",
                          }}
                        >
                          Monitor team performance and analyze booking trends
                        </Typography>
                      </Stack>
                    </Stack>
                  </Stack>
                </Fade>

                <Grow in={true} timeout={1000}>
                  <Stack spacing={2} alignItems="flex-end">
                    <Stack direction="row" alignItems="center" spacing={2}>
                      <Stack direction="row" alignItems="center" spacing={1}>
                        <People sx={{ fontSize: 18, color: "text.secondary" }} />
                        <Typography
                          variant="body2"
                          sx={{
                            color: "text.primary",
                            fontSize: "0.85rem",
                            fontWeight: 500,
                          }}
                        >
                          Team Filter
                        </Typography>
                      </Stack>
                      <Chip
                        label="Active"
                        size="small"
                        sx={{
                          background: "rgba(76, 175, 80, 0.1)",
                          color: "rgb(76, 175, 80)",
                          border: "1px solid rgba(76, 175, 80, 0.2)",
                          fontSize: "0.75rem",
                        }}
                      />
                    </Stack>
                    
                    <Paper
                      elevation={0}
                      sx={{
                        background: "#f8f9fa",
                        borderRadius: "12px",
                        border: "1px solid rgba(0, 0, 0, 0.1)",
                        minWidth: 250,
                      }}
                    >
                      <FormControl
                        variant="outlined"
                        size="small"
                        fullWidth
                        sx={{
                          "& .MuiOutlinedInput-root": {
                            backgroundColor: "transparent",
                            "& fieldset": {
                              border: "none",
                            },
                            "&:hover fieldset": {
                              border: "none",
                            },
                            "&.Mui-focused fieldset": {
                              border: "none",
                            },
                          },
                          "& .MuiInputLabel-root": {
                            color: "text.secondary",
                            "&.Mui-focused": {
                              color: "primary.main",
                            },
                          },
                          "& .MuiSelect-select": {
                            color: "text.primary",
                            padding: "12px 16px",
                            display: "flex",
                            alignItems: "center",
                            gap: "8px",
                          },
                          "& .MuiSelect-icon": {
                            color: "text.secondary",
                          },
                        }}
                      >
                        <InputLabel id="agent-select-label">
                          <Stack direction="row" alignItems="center" spacing={1}>
                            <FilterList sx={{ fontSize: 16 }} />
                            <span>Select Agent</span>
                          </Stack>
                        </InputLabel>
                        <Select
                          labelId="agent-select-label"
                          id="agent-select"
                          value={selectedAgent}
                          onChange={handleChange}
                          label="Select Agent"
                          sx={{
                            "& .MuiMenuItem-root": {
                              fontSize: "0.9rem",
                            },
                          }}
                        >
                          <MenuItem value="">
                            <Stack direction="row" alignItems="center" spacing={1}>
                              <Assessment sx={{ fontSize: 16, color: "primary.main" }} />
                              <span>All Agents</span>
                            </Stack>
                          </MenuItem>
                          {agents.map((agent) => (
                            <MenuItem key={agent.id} value={agent.agent_id}>
                              <Stack direction="row" alignItems="center" spacing={1}>
                                <Avatar sx={{ width: 20, height: 20, fontSize: "0.7rem" }}>
                                  {agent.name.charAt(0).toUpperCase()}
                                </Avatar>
                                <span>{agent.name}</span>
                              </Stack>
                            </MenuItem>
                          ))}
                        </Select>
                      </FormControl>
                    </Paper>
                  </Stack>
                </Grow>
              </Stack>
            </CardContent>
          </Card>
        </AnimatedBox>




        <AnimatedBox direction="down" delay={400}>
          <Card
            elevation={0}
            sx={{
              backgroundColor: "white",
              borderRadius: "16px",
              boxShadow: "0 4px 20px rgba(0,0,0,0.08)",
              overflow: "hidden",
              border: "1px solid rgba(0,0,0,0.05)",
            }}
          >
            <CardContent sx={{ p: 0 }}>
              <Stack direction="row" alignItems="center" justifyContent="space-between" sx={{ px: 3, py: 2 }}>
                <Stack direction="row" alignItems="center" spacing={2}>
                  <Avatar
                    sx={{
                      width: 36,
                      height: 36,
                      background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                    }}
                  >
                    <Assessment sx={{ fontSize: 20, color: "white" }} />
                  </Avatar>
                  <Stack>
                    <Typography variant="h6" fontWeight="bold" color="text.primary">
                      Management Overview
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Detailed analytics and performance tracking
                    </Typography>
                  </Stack>
                </Stack>
                
                <Chip
                  label={`${agents.length} Agent${agents.length !== 1 ? 's' : ''}`}
                  size="small"
                  sx={{
                    backgroundColor: "rgba(102, 126, 234, 0.1)",
                    color: "rgb(102, 126, 234)",
                    fontWeight: 600,
                    "& .MuiChip-label": {
                      px: 2,
                    },
                  }}
                />
              </Stack>
              
              <Divider sx={{ borderColor: "rgba(0,0,0,0.05)" }} />
              
              <Box sx={{ px: 2 }}>
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
                      fontSize: "14px",
                      minHeight: "56px",
                      display: "flex",
                      flexDirection: "row",
                      alignItems: "center",
                      gap: "8px",
                      borderRadius: "8px",
                      mx: 0.5,
                      my: 1,
                      transition: "all 0.3s ease",
                      "&:hover": {
                        backgroundColor: "rgba(102, 126, 234, 0.05)",
                      },
                      "&.Mui-selected": {
                        backgroundColor: "rgba(102, 126, 234, 0.1)",
                        color: "rgb(102, 126, 234)",
                      },
                    },
                    "& .MuiTabs-indicator": {
                      height: 3,
                      borderRadius: "3px 3px 0 0",
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
                  <Tab 
                    icon={<CardGiftcardOutlined />} 
                    label="Fixed Itinerary Packages" 
                    iconPosition="start" 
                    {...a11yProps(2)} 
                  />
                </Tabs>
              </Box>
            </CardContent>
          </Card>
        </AnimatedBox>

        <AnimatedBox direction="up" delay={600}>
          <TabPanel value={mainTabValue} index={0} sx={{ p: 0 }}>
            {/* Insert your booking details content here */}
            <BasicTabs />
          </TabPanel>
        </AnimatedBox>
        
        <AnimatedBox direction="up" delay={600}>
          <TabPanel value={mainTabValue} index={1} sx={{ p: 0 }}>
            {/* Insert your booking enquiries content here */}
            <Box sx={{ p: 3, backgroundColor: "white", borderRadius: "0 0 12px 12px" }}>
              <EnquiryList />
            </Box>
          </TabPanel>
        </AnimatedBox>
        
        <AnimatedBox direction="up" delay={600}>
          <TabPanel value={mainTabValue} index={2} sx={{ p: 0 }}>
            {/* Insert your predefine packages content here */}
            <Box sx={{ p: 3, backgroundColor: "white", borderRadius: "0 0 12px 12px" }}>
              <PreDefinePackages />
            </Box>
          </TabPanel>
        </AnimatedBox>
      </StaggeredContainer>
    </Container>
  );
};

export default SalesManagerDashboard; 