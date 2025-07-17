import Header1 from "@/components/header/header-11";
import DefaultFooter from "@/components/footer/default";
import { useEffect, useState, useRef } from "react";
import { Outlet, useLocation } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import BasicTabs from "./components/TabStatus";
import SalesManagerDashboard from "./components/SalesManagerDashboard";
import DashboardCard from "./components/DashboardCard";
import ChartMain from "./components/ChartMain";
import RecentBooking from "./components/RercentBooking";
import { Grid, Container, Box, Tab, Tabs, Stack, Typography, Avatar, Chip, Card, CardContent, Fade, Grow, Menu, MenuItem, Button, Tooltip, IconButton, Divider, Dialog, DialogTitle, DialogContent, DialogActions, TextField, InputAdornment, Slider, Paper } from "@mui/material";
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
  KeyboardArrowDown,
  AccountCircleOutlined,
  EditOutlined,
  LogoutOutlined,
  SettingsOutlined,
  NotificationsOutlined,
  Close,
  CameraAlt,
  Visibility,
  VisibilityOff,
  Lock,
  Phone,
  Badge as BadgeIcon,
  Check,
  ZoomIn,
  ZoomOut,
  RotateLeft,
  RotateRight,
  CropFree,
} from "@mui/icons-material";
import EnquiryList from "./components/EnquiryList";
import { fetchEnquiries } from "@/slice/enquiries/enquiryListSlice";
import { logoutUser } from "@/slice/common/authSlices";
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
  
  // Agent profile dropdown state
  const [profileAnchorEl, setProfileAnchorEl] = useState(null);
  const isProfileMenuOpen = Boolean(profileAnchorEl);
  
  // Profile modal state
  const [profileModalOpen, setProfileModalOpen] = useState(false);
  const [passwordChangeMode, setPasswordChangeMode] = useState(false);
  const [phoneEditMode, setPhoneEditMode] = useState(false);
  const [phoneNumber, setPhoneNumber] = useState('');
  const [selectedProfileImage, setSelectedProfileImage] = useState(null);
  const [previewImage, setPreviewImage] = useState(null);
  const [showImageAdjustment, setShowImageAdjustment] = useState(false);
  const [adjustedImage, setAdjustedImage] = useState(null);
  const [imageScale, setImageScale] = useState(1);
  const [imageRotation, setImageRotation] = useState(0);
  const [showCurrentPassword, setShowCurrentPassword] = useState(false);
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [passwordData, setPasswordData] = useState({
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  });
  
  // File input ref for profile picture
  const fileInputRef = useRef(null);
  const canvasRef = useRef(null);
  
  const handleProfileClick = (event) => {
    setProfileAnchorEl(event.currentTarget);
  };
  
  const handleProfileClose = () => {
    setProfileAnchorEl(null);
  };
  
  const handleViewProfile = () => {
    setProfileModalOpen(true);
    setPhoneNumber(''); // Initialize with empty or existing phone number
    handleProfileClose();
  };
  
  const handleCloseProfileModal = () => {
    setProfileModalOpen(false);
    setPasswordChangeMode(false);
    setPhoneEditMode(false);
    setPhoneNumber('');
    setSelectedProfileImage(null);
    setPreviewImage(null);
    setShowImageAdjustment(false);
    setAdjustedImage(null);
    setImageScale(1);
    setImageRotation(0);
    setPasswordData({
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    });
  };
  
  const handlePasswordChange = (field, value) => {
    setPasswordData(prev => ({
      ...prev,
      [field]: value
    }));
  };
  
  const handleSavePassword = () => {
    // Add password change logic here
    console.log('Password change requested:', passwordData);
    // Reset form after successful change
    setPasswordData({
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    });
    setPasswordChangeMode(false);
  };
  
  const handleSavePhoneNumber = () => {
    // Add phone number save logic here
    console.log('Phone number update requested:', phoneNumber);
    // You can dispatch an action to update phone number in the backend
    // For now, we'll just close the edit mode
    setPhoneEditMode(false);
  };
  
  const handleCameraIconClick = () => {
    fileInputRef.current?.click();
  };
  
  const handleProfileImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
      // Validate file type
      const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
      if (!allowedTypes.includes(file.type)) {
        alert('Please select a valid image file (JPEG, PNG, or GIF)');
        return;
      }
      
      // Validate file size (max 5MB)
      const maxSize = 5 * 1024 * 1024; // 5MB in bytes
      if (file.size > maxSize) {
        alert('Please select an image smaller than 5MB');
        return;
      }
      
      setSelectedProfileImage(file);
      
      // Create preview URL and show adjustment modal
      const reader = new FileReader();
      reader.onload = (e) => {
        setPreviewImage(e.target.result);
        setShowImageAdjustment(true);
        setImageScale(1);
        setImageRotation(0);
      };
      reader.readAsDataURL(file);
    }
  };
  
  const applyImageAdjustments = () => {
    const canvas = canvasRef.current;
    const ctx = canvas.getContext('2d');
    const img = new Image();
    
    img.onload = () => {
      // Set canvas size
      canvas.width = 200;
      canvas.height = 200;
      
      // Clear canvas
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      
      // Apply transformations
      ctx.save();
      ctx.translate(canvas.width / 2, canvas.height / 2);
      ctx.rotate((imageRotation * Math.PI) / 180);
      ctx.scale(imageScale, imageScale);
      
      // Draw image centered
      ctx.drawImage(img, -img.width / 2, -img.height / 2, img.width, img.height);
      ctx.restore();
      
      // Get adjusted image data
      const adjustedDataUrl = canvas.toDataURL('image/jpeg', 0.8);
      setAdjustedImage(adjustedDataUrl);
    };
    
    img.src = previewImage;
  };
  
  const handleImageAdjustmentSave = () => {
    applyImageAdjustments();
    setShowImageAdjustment(false);
  };
  
  const handleImageAdjustmentCancel = () => {
    setShowImageAdjustment(false);
    setSelectedProfileImage(null);
    setPreviewImage(null);
    setAdjustedImage(null);
    setImageScale(1);
    setImageRotation(0);
  };
  
  const handleUploadProfileImage = () => {
    const imageToUpload = adjustedImage || previewImage;
    if (imageToUpload) {
      // Add API call to upload image here
      console.log('Uploading profile image:', imageToUpload);
      
      // For now, just update the preview and close
      // In a real app, you would:
      // 1. Upload to your backend/cloud storage
      // 2. Update the profilePicture in Redux state
      // 3. Update the database
      
      alert('Profile image uploaded successfully!');
      setSelectedProfileImage(null);
      setPreviewImage(null);
      setAdjustedImage(null);
      setImageScale(1);
      setImageRotation(0);
    }
  };
  
  const handleLogout = () => {
    dispatch(logoutUser());
    handleProfileClose();
  };
  
      useEffect(()=>{
       
        
           dispatch(fetchEnquiries())
       
    },[dispatch])
  // Get user data from Redux state
  const { userRole, Username, Email, profilePicture, agentId } = useSelector((state) => state.auth);

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
                              
                              {/* Agent Profile Button - Only for Agent */}
                              {userRole !== "Sales Head(DMC)" && userRole !== "Sales Manager (DMC)" && userRole !== "Assistant Manager (DMC)" && (
                                <>
                                  <Tooltip title="Agent Profile">
                                    <IconButton
                                      onClick={handleProfileClick}
                                      aria-controls={isProfileMenuOpen ? "agent-profile-menu" : undefined}
                                      aria-haspopup="true"
                                      aria-expanded={isProfileMenuOpen ? "true" : undefined}
                                      sx={{
                                        background: "rgba(255, 255, 255, 0.15)",
                                        backdropFilter: "blur(10px)",
                                        border: "1px solid rgba(255, 255, 255, 0.25)",
                                        color: "white",
                                        padding: "8px",
                                        transition: "all 0.2s ease",
                                        "&:hover": {
                                          background: "rgba(255, 255, 255, 0.25)",
                                        }
                                      }}
                                    >
                                      <AccountCircleOutlined sx={{ fontSize: 24 }} />
                                    </IconButton>
                                  </Tooltip>
                                  
                                  {/* Agent Profile Menu */}
                                  <Menu
                                    id="agent-profile-menu"
                                    anchorEl={profileAnchorEl}
                                    open={isProfileMenuOpen}
                                    onClose={handleProfileClose}
                                    MenuListProps={{
                                      "aria-labelledby": "agent-profile-button",
                                    }}
                                    PaperProps={{
                                      elevation: 3,
                                      sx: {
                                        minWidth: 220,
                                        borderRadius: "10px",
                                        mt: 1.5,
                                        overflow: "visible",
                                        filter: "drop-shadow(0px 2px 8px rgba(0,0,0,0.15))",
                                        "&:before": {
                                          content: '""',
                                          display: "block",
                                          position: "absolute",
                                          top: 0,
                                          right: 14,
                                          width: 10,
                                          height: 10,
                                          bgcolor: "background.paper",
                                          transform: "translateY(-50%) rotate(45deg)",
                                          zIndex: 0,
                                        },
                                      },
                                    }}
                                    transformOrigin={{ horizontal: "right", vertical: "top" }}
                                    anchorOrigin={{ horizontal: "right", vertical: "bottom" }}
                                  >
                                    <Box sx={{ px: 2, py: 2 }}>
                                      <Typography variant="subtitle1" fontWeight="bold">
                                        Agent Profile
                                      </Typography>
                                      <Typography variant="body2" color="text.secondary" sx={{ mb: 1 }}>
                                        Manage your account settings
                                      </Typography>
                                    </Box>
                                    
                                    <Divider />
                                    
                                    <MenuItem onClick={handleViewProfile} sx={{ py: 1.5 }}>
                                      <AccountCircleOutlined sx={{ mr: 1.5, fontSize: 20, color: "primary.main" }} />
                                      <Typography>View Profile</Typography>
                                    </MenuItem>
                                    
                                    <Divider />
                                    
                                    <MenuItem onClick={handleLogout} sx={{ py: 1.5, color: "error.main" }}>
                                      <LogoutOutlined sx={{ mr: 1.5, fontSize: 20 }} />
                                      <Typography>Logout</Typography>
                                    </MenuItem>
                                  </Menu>
                                </>
                              )}
                            </Stack>
                          </Fade>
                        </Stack>
                      </CardContent>
                    </Card>
                  </AnimatedBox>

                  {/* Profile Modal */}
                  <Dialog
                    open={profileModalOpen}
                    onClose={handleCloseProfileModal}
                    maxWidth="sm"
                    fullWidth
                    PaperProps={{
                      sx: {
                        borderRadius: "16px",
                        overflow: "hidden",
                      }
                    }}
                  >
                    <DialogTitle
                      sx={{
                        background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                        color: "white",
                        p: 3,
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "space-between",
                      }}
                    >
                      <Typography variant="h5" fontWeight="bold">
                        Agent Profile
                      </Typography>
                      <IconButton
                        onClick={handleCloseProfileModal}
                        sx={{ color: "white" }}
                      >
                        <Close />
                      </IconButton>
                    </DialogTitle>
                    
                    <DialogContent sx={{ p: 0 }}>
                      <Box sx={{ p: 4 }}>
                                                 {/* Profile Picture Section */}
                         <Stack alignItems="center" spacing={2} sx={{ mb: 4 }}>
                           <Box sx={{ position: "relative" }}>
                             <Avatar
                               src={adjustedImage || previewImage || profilePicture || ""}
                               sx={{
                                 width: 120,
                                 height: 120,
                                 border: "4px solid #667eea",
                                 fontSize: "3rem",
                                 background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                               }}
                             >
                               {!adjustedImage && !previewImage && !profilePicture && Username?.charAt(0)?.toUpperCase() || "A"}
                             </Avatar>
                             <IconButton
                               onClick={handleCameraIconClick}
                               sx={{
                                 position: "absolute",
                                 bottom: 0,
                                 right: 0,
                                 background: "white",
                                 boxShadow: "0 2px 8px rgba(0,0,0,0.15)",
                                 "&:hover": { background: "#f5f5f5" },
                               }}
                             >
                               <CameraAlt sx={{ fontSize: 20, color: "#667eea" }} />
                             </IconButton>
                             
                             {/* Hidden file input */}
                             <input
                               ref={fileInputRef}
                               type="file"
                               accept="image/*"
                               onChange={handleProfileImageChange}
                               style={{ display: 'none' }}
                             />
                           </Box>
                           
                           {/* Show upload button if image is selected */}
                           {(selectedProfileImage || adjustedImage) && !showImageAdjustment && (
                             <Stack direction="row" spacing={2}>
                               <Button
                                 variant="contained"
                                 size="small"
                                 onClick={handleUploadProfileImage}
                                 sx={{
                                   borderRadius: "20px",
                                   background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                                   "&:hover": {
                                     background: "linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%)",
                                   }
                                 }}
                               >
                                 Upload Image
                               </Button>
                               <Button
                                 variant="outlined"
                                 size="small"
                                 onClick={() => {
                                   setSelectedProfileImage(null);
                                   setPreviewImage(null);
                                   setAdjustedImage(null);
                                   setImageScale(1);
                                   setImageRotation(0);
                                 }}
                                 sx={{ borderRadius: "20px" }}
                               >
                                 Cancel
                               </Button>
                             </Stack>
                           )}
                           
                           <Typography variant="h6" fontWeight="bold">
                             {Username || "Agent Name"}
                           </Typography>
                           <Chip
                             label={userRole || "Agent"}
                             color="primary"
                             size="small"
                             sx={{ fontWeight: 600 }}
                           />
                         </Stack>
                        
                                                 {/* Profile Information */}
                         <Stack spacing={3}>
                           <TextField
                             label="Full Name"
                             value={Username || "N/A"}
                             InputProps={{
                               startAdornment: (
                                 <InputAdornment position="start">
                                   <Person sx={{ color: "primary.main" }} />
                                 </InputAdornment>
                               ),
                               readOnly: true,
                             }}
                             variant="outlined"
                             fullWidth
                             sx={{
                               "& .MuiOutlinedInput-root": {
                                 borderRadius: "10px",
                               }
                             }}
                           />
                           
                           <TextField
                             label="Email"
                             value={Email || "N/A"}
                             InputProps={{
                               startAdornment: (
                                 <InputAdornment position="start">
                                   <EmailOutlined sx={{ color: "primary.main" }} />
                                 </InputAdornment>
                               ),
                               readOnly: true,
                             }}
                             variant="outlined"
                             fullWidth
                             sx={{
                               "& .MuiOutlinedInput-root": {
                                 borderRadius: "10px",
                               }
                             }}
                           />
                           
                           <TextField
                             label="Phone Number"
                             value={phoneEditMode ? phoneNumber : (phoneNumber || "Not provided")}
                             onChange={(e) => setPhoneNumber(e.target.value)}
                             InputProps={{
                               startAdornment: (
                                 <InputAdornment position="start">
                                   <Phone sx={{ color: "primary.main" }} />
                                 </InputAdornment>
                               ),
                               endAdornment: (
                                 <InputAdornment position="end">
                                   {phoneEditMode ? (
                                     <Stack direction="row" spacing={1}>
                                       <IconButton
                                         size="small"
                                         onClick={handleSavePhoneNumber}
                                         disabled={!phoneNumber.trim()}
                                         sx={{ color: "success.main" }}
                                       >
                                         <Check />
                                       </IconButton>
                                       <IconButton
                                         size="small"
                                         onClick={() => {
                                           setPhoneEditMode(false);
                                           setPhoneNumber('');
                                         }}
                                         sx={{ color: "error.main" }}
                                       >
                                         <Close />
                                       </IconButton>
                                     </Stack>
                                   ) : (
                                     <IconButton
                                       size="small"
                                       onClick={() => setPhoneEditMode(true)}
                                       sx={{ color: "primary.main" }}
                                     >
                                       <EditOutlined />
                                     </IconButton>
                                   )}
                                 </InputAdornment>
                               ),
                               readOnly: !phoneEditMode,
                             }}
                             variant="outlined"
                             fullWidth
                             placeholder={phoneEditMode ? "Enter phone number" : ""}
                             sx={{
                               "& .MuiOutlinedInput-root": {
                                 borderRadius: "10px",
                               }
                             }}
                           />
                           
                           <TextField
                             label="Agent ID"
                             value={agentId || "N/A"}
                             InputProps={{
                               startAdornment: (
                                 <InputAdornment position="start">
                                   <BadgeIcon sx={{ color: "primary.main" }} />
                                 </InputAdornment>
                               ),
                               readOnly: true,
                             }}
                             variant="outlined"
                             fullWidth
                             sx={{
                               "& .MuiOutlinedInput-root": {
                                 borderRadius: "10px",
                               }
                             }}
                           />
                         </Stack>
                        
                        {/* Password Change Section */}
                        <Box sx={{ mt: 4 }}>
                          <Stack direction="row" alignItems="center" justifyContent="space-between" sx={{ mb: 3 }}>
                            <Typography variant="h6" fontWeight="bold">
                              Security
                            </Typography>
                            <Button
                              variant={passwordChangeMode ? "outlined" : "contained"}
                              startIcon={<Lock />}
                              onClick={() => setPasswordChangeMode(!passwordChangeMode)}
                              sx={{ borderRadius: "8px" }}
                            >
                              {passwordChangeMode ? "Cancel" : "Change Password"}
                            </Button>
                          </Stack>
                          
                          {passwordChangeMode && (
                            <Stack spacing={3}>
                              <TextField
                                label="Current Password"
                                type={showCurrentPassword ? "text" : "password"}
                                value={passwordData.currentPassword}
                                onChange={(e) => handlePasswordChange('currentPassword', e.target.value)}
                                InputProps={{
                                  startAdornment: (
                                    <InputAdornment position="start">
                                      <Lock sx={{ color: "primary.main" }} />
                                    </InputAdornment>
                                  ),
                                  endAdornment: (
                                    <InputAdornment position="end">
                                      <IconButton
                                        onClick={() => setShowCurrentPassword(!showCurrentPassword)}
                                        edge="end"
                                      >
                                        {showCurrentPassword ? <VisibilityOff /> : <Visibility />}
                                      </IconButton>
                                    </InputAdornment>
                                  ),
                                }}
                                variant="outlined"
                                fullWidth
                                sx={{
                                  "& .MuiOutlinedInput-root": {
                                    borderRadius: "10px",
                                  }
                                }}
                              />
                              
                              <TextField
                                label="New Password"
                                type={showNewPassword ? "text" : "password"}
                                value={passwordData.newPassword}
                                onChange={(e) => handlePasswordChange('newPassword', e.target.value)}
                                InputProps={{
                                  startAdornment: (
                                    <InputAdornment position="start">
                                      <Lock sx={{ color: "primary.main" }} />
                                    </InputAdornment>
                                  ),
                                  endAdornment: (
                                    <InputAdornment position="end">
                                      <IconButton
                                        onClick={() => setShowNewPassword(!showNewPassword)}
                                        edge="end"
                                      >
                                        {showNewPassword ? <VisibilityOff /> : <Visibility />}
                                      </IconButton>
                                    </InputAdornment>
                                  ),
                                }}
                                variant="outlined"
                                fullWidth
                                sx={{
                                  "& .MuiOutlinedInput-root": {
                                    borderRadius: "10px",
                                  }
                                }}
                              />
                              
                              <TextField
                                label="Confirm New Password"
                                type={showConfirmPassword ? "text" : "password"}
                                value={passwordData.confirmPassword}
                                onChange={(e) => handlePasswordChange('confirmPassword', e.target.value)}
                                InputProps={{
                                  startAdornment: (
                                    <InputAdornment position="start">
                                      <Lock sx={{ color: "primary.main" }} />
                                    </InputAdornment>
                                  ),
                                  endAdornment: (
                                    <InputAdornment position="end">
                                      <IconButton
                                        onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                        edge="end"
                                      >
                                        {showConfirmPassword ? <VisibilityOff /> : <Visibility />}
                                      </IconButton>
                                    </InputAdornment>
                                  ),
                                }}
                                variant="outlined"
                                fullWidth
                                sx={{
                                  "& .MuiOutlinedInput-root": {
                                    borderRadius: "10px",
                                  }
                                }}
                              />
                              
                              <Button
                                variant="contained"
                                onClick={handleSavePassword}
                                disabled={!passwordData.currentPassword || !passwordData.newPassword || !passwordData.confirmPassword}
                                sx={{
                                  borderRadius: "8px",
                                  py: 1.5,
                                  background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                                  "&:hover": {
                                    background: "linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%)",
                                  }
                                }}
                              >
                                Save New Password
                              </Button>
                            </Stack>
                          )}
                        </Box>
                      </Box>
                                         </DialogContent>
                   </Dialog>
                   
                   {/* Image Adjustment Modal */}
                   <Dialog
                     open={showImageAdjustment}
                     onClose={handleImageAdjustmentCancel}
                     maxWidth="md"
                     fullWidth
                     PaperProps={{
                       sx: {
                         borderRadius: "16px",
                         overflow: "hidden",
                       }
                     }}
                   >
                     <DialogTitle
                       sx={{
                         background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                         color: "white",
                         p: 3,
                         display: "flex",
                         alignItems: "center",
                         justifyContent: "space-between",
                       }}
                     >
                       <Typography variant="h6" fontWeight="bold">
                         Adjust Profile Picture
                       </Typography>
                       <IconButton
                         onClick={handleImageAdjustmentCancel}
                         sx={{ color: "white" }}
                       >
                         <Close />
                       </IconButton>
                     </DialogTitle>
                     
                     <DialogContent sx={{ p: 4 }}>
                       <Grid container spacing={3}>
                         {/* Image Preview */}
                         <Grid item xs={12} md={6}>
                           <Paper
                             elevation={3}
                             sx={{
                               p: 2,
                               display: "flex",
                               flexDirection: "column",
                               alignItems: "center",
                               backgroundColor: "#f5f5f5",
                               borderRadius: "12px",
                             }}
                           >
                             <Typography variant="subtitle1" gutterBottom>
                               Preview
                             </Typography>
                             <Box
                               sx={{
                                 width: 200,
                                 height: 200,
                                 border: "2px dashed #ccc",
                                 borderRadius: "50%",
                                 display: "flex",
                                 alignItems: "center",
                                 justifyContent: "center",
                                 overflow: "hidden",
                                 backgroundColor: "white",
                               }}
                             >
                               {previewImage && (
                                 <img
                                   src={previewImage}
                                   alt="Preview"
                                   style={{
                                     width: "100%",
                                     height: "100%",
                                     objectFit: "cover",
                                     transform: `scale(${imageScale}) rotate(${imageRotation}deg)`,
                                     transition: "transform 0.3s ease",
                                   }}
                                 />
                               )}
                             </Box>
                           </Paper>
                         </Grid>
                         
                         {/* Controls */}
                         <Grid item xs={12} md={6}>
                           <Stack spacing={4}>
                             {/* Scale Control */}
                             <Box>
                               <Stack direction="row" alignItems="center" spacing={2} sx={{ mb: 2 }}>
                                 <ZoomOut />
                                 <Typography variant="body1" fontWeight="bold">
                                   Scale
                                 </Typography>
                                 <ZoomIn />
                               </Stack>
                               <Slider
                                 value={imageScale}
                                 onChange={(_, value) => setImageScale(value)}
                                 min={0.5}
                                 max={2}
                                 step={0.1}
                                 valueLabelDisplay="on"
                                 sx={{ color: "#667eea" }}
                               />
                             </Box>
                             
                             {/* Rotation Control */}
                             <Box>
                               <Stack direction="row" alignItems="center" spacing={2} sx={{ mb: 2 }}>
                                 <RotateLeft />
                                 <Typography variant="body1" fontWeight="bold">
                                   Rotation
                                 </Typography>
                                 <RotateRight />
                               </Stack>
                               <Slider
                                 value={imageRotation}
                                 onChange={(_, value) => setImageRotation(value)}
                                 min={-180}
                                 max={180}
                                 step={15}
                                 valueLabelDisplay="on"
                                 sx={{ color: "#667eea" }}
                               />
                             </Box>
                             
                             {/* Quick Rotation Buttons */}
                             <Stack direction="row" spacing={2} justifyContent="center">
                               <IconButton
                                 onClick={() => setImageRotation(prev => prev - 90)}
                                 sx={{
                                   backgroundColor: "#f0f0f0",
                                   "&:hover": { backgroundColor: "#e0e0e0" },
                                 }}
                               >
                                 <RotateLeft />
                               </IconButton>
                               <IconButton
                                 onClick={() => setImageRotation(prev => prev + 90)}
                                 sx={{
                                   backgroundColor: "#f0f0f0",
                                   "&:hover": { backgroundColor: "#e0e0e0" },
                                 }}
                               >
                                 <RotateRight />
                               </IconButton>
                             </Stack>
                             
                             {/* Reset Button */}
                             <Button
                               variant="outlined"
                               onClick={() => {
                                 setImageScale(1);
                                 setImageRotation(0);
                               }}
                               sx={{ borderRadius: "8px" }}
                             >
                               Reset
                             </Button>
                           </Stack>
                         </Grid>
                       </Grid>
                       
                       {/* Hidden Canvas for processing */}
                       <canvas
                         ref={canvasRef}
                         style={{ display: "none" }}
                       />
                     </DialogContent>
                     
                     <DialogActions sx={{ p: 3 }}>
                       <Button
                         variant="outlined"
                         onClick={handleImageAdjustmentCancel}
                         sx={{ borderRadius: "8px" }}
                       >
                         Cancel
                       </Button>
                       <Button
                         variant="contained"
                         onClick={handleImageAdjustmentSave}
                         sx={{
                           borderRadius: "8px",
                           background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                           "&:hover": {
                             background: "linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%)",
                           }
                         }}
                       >
                         Apply Changes
                       </Button>
                     </DialogActions>
                   </Dialog>

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
