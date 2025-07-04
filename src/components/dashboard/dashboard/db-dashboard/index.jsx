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
import { Grid, Container, Box, Tab, Tabs } from "@mui/material";
import {
  Dashboard as DashboardIcon,
  Notifications,
  Search,
  EventNote,
  AssessmentOutlined,
  MoreHoriz,
  EmailOutlined,
  CardGiftcardOutlined,
} from "@mui/icons-material";
import EnquiryList from "./components/EnquiryList";
import { fetchEnquiries } from "@/slice/enquiries/enquiryListSlice";
import PreDefinePackages from "./PreDefine-Packages";

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
        
        <TabPanel value={mainTabValue} index={2} sx={{ p: 0 }}>
          {/* Insert your predefine packages content here */}
          <Box sx={{ p: 3, backgroundColor: "white", borderRadius: "0 0 12px 12px" }}>
            <PreDefinePackages />
          </Box>
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
                <div
                  className="dashboard__header"
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    alignItems: "center",
                    marginBottom: "30px",
                  }}
                >
                  <div className="dashboard__title">
                    <div
                      style={{
                        display: "flex",
                        alignItems: "center",
                        gap: "12px",
                      }}
                    >
                      <DashboardIcon sx={{ color: "#4361ee", fontSize: 32 }} />
                      <h1
                        style={{
                          fontSize: "28px",
                          fontWeight: "700",
                          margin: "0",
                          color: "#333",
                        }}
                      >
                        {userRole === "Sales Head(DMC)" 
                          ? "Sales Head Dashboard" 
                          : userRole === "Sales Manager (DMC)" 
                            ? "Sales Manager Dashboard" 
                            : userRole === "Assistant Manager (DMC)" 
                              ? "Assistant Manager Dashboard" 
                              : "Dashboard"}
                      </h1>
                    </div>
                    <p
                      style={{
                        margin: "5px 0 0 44px",
                        color: "#666",
                        fontSize: "15px",
                      }}
                    >
                      {userRole === "Sales Manager (DMC)" || userRole === "Sales Head(DMC)"||userRole =="Assistant Manager (DMC)"
                        ? "Welcome back! Here's your team's performance at a glance"
                        : "Welcome back! Here's an overview of your bookings"}
                    </p>
                  </div>

                  {/* <div
                    style={{
                      display: "flex",
                      gap: "12px",
                    }}
                  >
                    <div
                      style={{
                        backgroundColor: "white",
                        borderRadius: "8px",
                        display: "flex",
                        alignItems: "center",
                        padding: "8px 12px",
                        boxShadow: "0 2px 8px rgba(0,0,0,0.05)",
                      }}
                    >
                      <Search sx={{ color: "#888", marginRight: "8px" }} />
                      <input
                        type="text"
                        placeholder="Search..."
                        style={{
                          border: "none",
                          outline: "none",
                          fontSize: "15px",
                          minWidth: "180px",
                        }}
                      />
                    </div>
                    <div
                      style={{
                        backgroundColor: "white",
                        borderRadius: "8px",
                        width: "40px",
                        height: "40px",
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "center",
                        boxShadow: "0 2px 8px rgba(0,0,0,0.05)",
                        cursor: "pointer",
                        position: "relative",
                      }}
                    >
                      <Notifications
                        sx={{ color: "#4361ee", fontSize: "22px" }}
                      />
                      <div
                        style={{
                          position: "absolute",
                          top: "-5px",
                          right: "-5px",
                          backgroundColor: "#FF5C5C",
                          color: "white",
                          borderRadius: "50%",
                          width: "18px",
                          height: "18px",
                          fontSize: "11px",
                          display: "flex",
                          alignItems: "center",
                          justifyContent: "center",
                          fontWeight: "500",
                        }}
                      >
                        3
                      </div>
                    </div>
                  </div> */}
                </div>

                {/* Render dashboard content based on role */}
                {renderDashboardContent()}
              </div>
            </Container>
          </div>
        )}
      </main>
      <DefaultFooter />
    </>
  );
};

export default DashboardLayout;
