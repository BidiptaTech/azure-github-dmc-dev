import Header1 from "@/components/header/header-11";
import DefaultFooter from "@/components/footer/default";
import { useEffect, useState } from "react";
import { Outlet, useLocation } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import BasicTabs from "./components/TabStatus";
import SalesManagerDashboard from "./components/SalesManagerDashboard";
import DashboardCard from "./components/DashboardCard";
import ChartMain from "./components/ChartMain";
import RecentBooking from "./components/RercentBooking";
import { Grid, Container, Box, Tab, Tabs, Stack, Typography, Avatar, Chip, Card, CardContent, Fade, Grow } from "@mui/material";
import {
  Dashboard as DashboardIcon,
  Search,
  EventNote,
  AssessmentOutlined,
  MoreHoriz,
  EmailOutlined,
  CardGiftcardOutlined,
  SupervisorAccount,
  ManageAccounts,
  Person,
  TrendingUp,
  Business,
  AccountBalance,
  BarChart,
  Settings,
  WorkOutline,
  Diamond,
  EmojiEvents,
  Star,
  Badge,
} from "@mui/icons-material";
import EnquiryList from "./components/EnquiryList";
import { fetchEnquiries } from "@/slice/enquiries/enquiryListSlice";
import PreDefinePackages from "./PreDefine-Packages";
import { 
  DashboardEntrance, 
  AnimatedBox, 
  StaggeredContainer, 
  AnimatedGrid 
} from "@/components/dashboard/DashboardAnimations";

// Custom Tab Panel component
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

const DashboardLayout = () => {
  const location = useLocation();
  const [mainTabValue, setMainTabValue] = useState(0);
   const dispatch = useDispatch();
  
      useEffect(()=>{
       
        
           dispatch(fetchEnquiries())
       
    },[dispatch])
  // Get user role from Redux state
  const { userRole } = useSelector((state) => state.auth);

  // Ensure dashboard content appears ONLY on the exact dashboard route
  const isDashboardPage = location.pathname === "/dashboard/db-dashboard";

  const handleMainTabChange = (event, newValue) => {
    setMainTabValue(newValue);
  };

  // Render appropriate dashboard based on user role
  const renderDashboardContent = () => {
    // If the role is 'Manager' or 'Sales Manager', render the Sales Manager dashboard
    if (userRole === "Sales Head(DMC)" || userRole === "Sales Manager (DMC)"||userRole === "Assistant Manager (DMC)" ) {
      return <SalesManagerDashboard />;
    }
    
    // Default to Agent dashboard for any other role
    return (
      <>
        <AnimatedBox direction="down" delay={600}>
          <Box
            className="dashboard-tabs"
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
              <Tab 
                icon={<CardGiftcardOutlined />} 
                label="Fixed Itinerary Packages" 
                iconPosition="start" 
                {...a11yProps(2)} 
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
        </AnimatedBox>

        <TabPanel value={mainTabValue} index={0} sx={{ p: 0 }}>
          {/* Insert your booking details content here */}
          <AnimatedBox direction="up" delay={800}>
            <BasicTabs />
          </AnimatedBox>
        </TabPanel>
        
        <TabPanel value={mainTabValue} index={1} sx={{ p: 0 }}>
          {/* Insert your booking enquiries content here */}
          <AnimatedBox direction="up" delay={800}>
            <Box sx={{ p: 3, backgroundColor: "white", borderRadius: "0 0 12px 12px" }}>
              <EnquiryList />
            </Box>
          </AnimatedBox>
        </TabPanel>
        
        <TabPanel value={mainTabValue} index={2} sx={{ p: 0 }}>
          {/* Insert your predefine packages content here */}
          <AnimatedBox direction="up" delay={800}>
            <Box sx={{ p: 3, backgroundColor: "white", borderRadius: "0 0 12px 12px" }}>
              <PreDefinePackages />
            </Box>
          </AnimatedBox>
        </TabPanel>
        
        {/* <TabPanel value={mainTabValue} index={1}>
          <Paper
            elevation={0}
            sx={{
              p: 3,
              backgroundColor: "white",
              borderRadius: "0 0 12px 12px",
            }}
          >
            <Box
              sx={{
                display: "flex",
                flexDirection: "column",
                gap: 2,
              }}
            >
              <ChartMain />
            </Box>
          </Paper>
        </TabPanel>

        <TabPanel value={mainTabValue} index={2}>
          <Paper
            elevation={0}
            sx={{
              p: 3,
              backgroundColor: "white",
              borderRadius: "0 0 12px 12px",
            }}
          >
            <Box
              sx={{
                display: "flex",
                flexDirection: "column",
                gap: 2,
              }}
            >
              <RercentBooking />
            </Box>
          </Paper>
        </TabPanel> */}
      </>
    );
  };

  return (
    <>
      <div className="header-margin"></div>
      <Header1 />
      <main>
        <Outlet /> {/* This renders the nested routes */}
        {isDashboardPage && (
          <DashboardEntrance>
            <div
              className="dashboard"
              style={{
                backgroundColor: "#f9fafb",
                minHeight: "calc(100vh - 200px)",
                marginTop: "20px",
                padding: "0 20px",
              }}
            >
              <Container maxWidth="xxl">
                <div className="dashboard__main" style={{ padding: "30px 0" }}>
                  <AnimatedBox direction="left" delay={200}>
                    <Card
                      elevation={0}
                      sx={{
                        background: 
                          userRole === "Sales Head(DMC)" 
                            ? "linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%)" // Red gradient for Sales Head
                            : userRole === "Sales Manager (DMC)" 
                              ? "linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%)" // Teal gradient for Sales Manager
                              : userRole === "Assistant Manager (DMC)" 
                                ? "linear-gradient(135deg, #feca57 0%, #ff9ff3 100%)" // Yellow-Pink gradient for Assistant Manager
                                : "linear-gradient(135deg, #667eea 0%, #764ba2 100%)", // Default blue-purple gradient for Agent
                        borderRadius: "16px",
                        marginBottom: "30px",
                        overflow: "hidden",
                        position: "relative",
                        transition: "all 0.3s ease-in-out",
                        "&::before": {
                          content: '""',
                          position: "absolute",
                          top: 0,
                          left: 0,
                          right: 0,
                          bottom: 0,
                          background: "rgba(255, 255, 255, 0.1)",
                          backdropFilter: "blur(10px)",
                        },
                        "&:hover": {
                          transform: "translateY(-2px)",
                          boxShadow: "0 8px 25px rgba(0,0,0,0.15)",
                        }
                      }}
                    >
                      <CardContent sx={{ p: 4, position: "relative", zIndex: 1 }}>
                        <Stack direction="row" justifyContent="space-between" alignItems="center">
                          <Stack direction="row" spacing={3} alignItems="center">
                            <Grow in={true} timeout={800}>
                              <Avatar
                                sx={{
                                  width: 64,
                                  height: 64,
                                  background: "rgba(255, 255, 255, 0.2)",
                                  backdropFilter: "blur(10px)",
                                  border: "2px solid rgba(255, 255, 255, 0.3)",
                                }}
                              >
                                {userRole === "Sales Head(DMC)" ? (
                                  <SupervisorAccount sx={{ fontSize: 32, color: "white" }} />
                                ) : userRole === "Sales Manager (DMC)" ? (
                                  <ManageAccounts sx={{ fontSize: 32, color: "white" }} />
                                ) : userRole === "Assistant Manager (DMC)" ? (
                                  <Business sx={{ fontSize: 32, color: "white" }} />
                                ) : (
                                  <Person sx={{ fontSize: 32, color: "white" }} />
                                )}
                              </Avatar>
                            </Grow>
                            
                            <Fade in={true} timeout={1000}>
                              <Stack spacing={1}>
                                <Stack direction="row" alignItems="center" spacing={2}>
                                  <Stack direction="row" alignItems="center" spacing={1.5}>
                                    {userRole === "Sales Head(DMC)" ? (
                                      <AccountBalance sx={{ fontSize: 32, color: "white" }} />
                                    ) : userRole === "Sales Manager (DMC)" ? (
                                      <BarChart sx={{ fontSize: 32, color: "white" }} />
                                    ) : userRole === "Assistant Manager (DMC)" ? (
                                      <Settings sx={{ fontSize: 32, color: "white" }} />
                                    ) : (
                                      <WorkOutline sx={{ fontSize: 32, color: "white" }} />
                                    )}
                                    <Typography
                                      variant="h4"
                                      component="h1"
                                      sx={{
                                        fontWeight: 700,
                                        color: "white",
                                        fontSize: { xs: "1.5rem", sm: "2rem" },
                                        textShadow: "0 2px 4px rgba(0,0,0,0.1)",
                                      }}
                                    >
                                      {userRole === "Sales Head(DMC)" 
                                        ? "Executive Dashboard" 
                                        : userRole === "Sales Manager (DMC)" 
                                          ? "Manager Dashboard" 
                                          : userRole === "Assistant Manager (DMC)" 
                                            ? "Assistant Dashboard" 
                                            : "Agent Dashboard"}
                                    </Typography>
                                  </Stack>
                                  
                                  <Chip
                                    label={
                                      userRole === "Sales Head(DMC)" 
                                        ? "Sales Head" 
                                        : userRole === "Sales Manager (DMC)" 
                                          ? "Sales Manager" 
                                          : userRole === "Assistant Manager (DMC)" 
                                            ? "Assistant Manager" 
                                            : "Agent"
                                    }
                                    size="small"
                                    sx={{
                                      background: 
                                        userRole === "Sales Head(DMC)" 
                                          ? "rgba(255, 255, 255, 0.25)" // Slightly more opaque for red bg
                                          : userRole === "Sales Manager (DMC)" 
                                            ? "rgba(255, 255, 255, 0.22)" // Balanced for teal bg
                                            : userRole === "Assistant Manager (DMC)" 
                                              ? "rgba(255, 255, 255, 0.28)" // More opaque for yellow bg
                                              : "rgba(255, 255, 255, 0.2)", // Default for blue bg
                                      color: "white",
                                      fontWeight: 600,
                                      fontSize: "0.75rem",
                                      border: 
                                        userRole === "Sales Head(DMC)" 
                                          ? "1px solid rgba(255, 255, 255, 0.4)" // Stronger border for red
                                          : userRole === "Sales Manager (DMC)" 
                                            ? "1px solid rgba(255, 255, 255, 0.35)" // Medium border for teal
                                            : userRole === "Assistant Manager (DMC)" 
                                              ? "1px solid rgba(255, 255, 255, 0.45)" // Strongest border for yellow
                                              : "1px solid rgba(255, 255, 255, 0.3)", // Default border
                                      backdropFilter: "blur(10px)",
                                      textShadow: "0 1px 2px rgba(0,0,0,0.1)",
                                    }}
                                  />
                                </Stack>
                                
                                <Stack direction="row" alignItems="center" spacing={1}>
                                  <TrendingUp sx={{ fontSize: 16, color: "rgba(255, 255, 255, 0.8)" }} />
                                  <Typography
                                    variant="body1"
                                    sx={{
                                      color: "rgba(255, 255, 255, 0.9)",
                                      fontSize: "0.95rem",
                                      fontWeight: 400,
                                    }}
                                  >
                                    {userRole === "Sales Head(DMC)" 
                                      ? "Executive overview and strategic management"
                                      : userRole === "Sales Manager (DMC)" 
                                        ? "Team performance monitoring and sales analytics"
                                        : userRole === "Assistant Manager (DMC)" 
                                          ? "Operational management and team coordination"
                                          : "Booking management and client services"}
                                  </Typography>
                                </Stack>
                              </Stack>
                            </Fade>
                          </Stack>
                          
                          <Fade in={true} timeout={1200}>
                            <Stack direction="row" spacing={2} alignItems="center">
                              <Box
                                sx={{
                                  background: "rgba(255, 255, 255, 0.1)",
                                  backdropFilter: "blur(10px)",
                                  borderRadius: "12px",
                                  px: 3,
                                  py: 1.5,
                                  border: "1px solid rgba(255, 255, 255, 0.2)",
                                }}
                              >
                                <Stack direction="row" alignItems="center" spacing={1}>
                                  {userRole === "Sales Head(DMC)" ? (
                                    <Diamond sx={{ fontSize: 18, color: "white" }} />
                                  ) : userRole === "Sales Manager (DMC)" ? (
                                    <EmojiEvents sx={{ fontSize: 18, color: "white" }} />
                                  ) : userRole === "Assistant Manager (DMC)" ? (
                                    <Star sx={{ fontSize: 18, color: "white" }} />
                                  ) : (
                                    <Badge sx={{ fontSize: 18, color: "white" }} />
                                  )}
                                  <Typography
                                    variant="body2"
                                    sx={{
                                      color: "white",
                                      fontWeight: 600,
                                      fontSize: "0.9rem",
                                      textShadow: "0 1px 2px rgba(0,0,0,0.1)",
                                    }}
                                  >
                                    {userRole === "Sales Head(DMC)" 
                                      ? "Executive Level" 
                                      : userRole === "Sales Manager (DMC)" 
                                        ? "Management Level" 
                                        : userRole === "Assistant Manager (DMC)" 
                                          ? "Supervisor Level" 
                                          : "Agent Level"}
                                  </Typography>
                                </Stack>
                              </Box>
                            </Stack>
                          </Fade>
                        </Stack>
                      </CardContent>
                    </Card>
                  </AnimatedBox>

                  <AnimatedBox direction="up" delay={400}>
                    {/* Render dashboard content based on role */}
                    {renderDashboardContent()}
                  </AnimatedBox>
                </div>
              </Container>
            </div>
          </DashboardEntrance>
        )}
      </main>
      <DefaultFooter />
    </>
  );
};

export default DashboardLayout;
