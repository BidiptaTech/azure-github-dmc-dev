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
  LocationOn,
  ZoomIn,
  ZoomOut,
  RotateLeft,
  RotateRight,
  CropFree,
  AdminPanelSettings,
  Engineering,
  SupportAgent,
  Dashboard,
  Analytics,
  Monitor,
  MilitaryTech,
  WorkspacePremium,
  Verified,
} from "@mui/icons-material";
import EnquiryList from "./components/EnquiryList";
import { fetchEnquiries } from "@/slice/enquiries/enquiryListSlice";
import { logoutUser, updateProfileData } from "@/slice/common/authSlices";
import { updateProfile, resetProfileState } from "@/slice/common/profileSlice";
import { clearSelectedDmc } from "@/slice/dmc/dmcSlice";
import { BASE_URL } from "@/services/api";
import PreDefinePackages from "./PreDefine-Packages";
import {
  DashboardEntrance,
  AnimatedBox,
  StaggeredContainer,
  AnimatedGrid
} from "@/components/dashboard/DashboardAnimations";
import { setAgentId as setAgentIdEdit } from "@/slice/common/EditSlice";

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

  // Utility function to convert profile picture path to full URL
  const getProfilePictureUrl = (profilePicturePath) => {
    if (!profilePicturePath) return "";
    if (profilePicturePath.startsWith('http')) return profilePicturePath;

    // Remove leading slash and backslashes, handle escaped slashes
    const cleanPath = profilePicturePath.replace(/^\\?\/+/, '').replace(/\\/g, '');

    // Try different URL constructions based on the API structure
    const baseUrl = BASE_URL.replace('/api/v1', '');
    const fullUrl = `${baseUrl}/${cleanPath}`;

    // console.log('Profile picture URL construction:');
    // console.log('Original path:', profilePicturePath);
    // console.log('Clean path:', cleanPath);
    // console.log('Base URL:', baseUrl);
    // console.log('Full URL:', fullUrl);
    // console.log('Current profilePicture from auth state:', profilePicture);

    return fullUrl;
  };

  // Agent profile dropdown state
  const [profileAnchorEl, setProfileAnchorEl] = useState(null);
  const isProfileMenuOpen = Boolean(profileAnchorEl);

  // Profile modal state
  const [profileModalOpen, setProfileModalOpen] = useState(false);
  const [passwordChangeMode, setPasswordChangeMode] = useState(false);
  const [phoneEditMode, setPhoneEditMode] = useState(false);
  const [phoneNumber, setPhoneNumber] = useState('');
  const [addressEditMode, setAddressEditMode] = useState(false);
  const [addressInput, setAddressInput] = useState('');
  const [selectedProfileImage, setSelectedProfileImage] = useState(null);
  const [previewImage, setPreviewImage] = useState(null);
  const [showImageAdjustment, setShowImageAdjustment] = useState(false);
  const [adjustedImage, setAdjustedImage] = useState(null);
  const [imageScale, setImageScale] = useState(1);
  const [imageRotation, setImageRotation] = useState(0);
  const [imageOffsetX, setImageOffsetX] = useState(0);
  const [imageOffsetY, setImageOffsetY] = useState(0);
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
    setPhoneNumber(phoneNo || ''); // Initialize with existing phone number or empty
    setAddressInput(agent_address || ''); // Initialize with existing address or empty
    handleProfileClose();
  };

  const handleCloseProfileModal = () => {
    setProfileModalOpen(false);
    setPasswordChangeMode(false);
    setPhoneEditMode(false);
    setPhoneNumber('');
    setAddressEditMode(false);
    setAddressInput('');
    setSelectedProfileImage(null);
    setPreviewImage(null);
    setShowImageAdjustment(false);
    setAdjustedImage(null);
    setImageScale(1);
    setImageRotation(0);
    setImageOffsetX(0);
    setImageOffsetY(0);
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

  const handleSavePassword = async () => {
    if (passwordData.newPassword !== passwordData.confirmPassword) {
      alert('New passwords do not match!');
      return;
    }

    if (!passwordData.currentPassword || !passwordData.newPassword) {
      alert('Please fill in all password fields!');
      return;
    }

    await dispatch(updateProfile({
      old_password: passwordData.currentPassword,
      new_password: passwordData.newPassword,
      confirm_password: passwordData.confirmPassword
    }));

    // Reset form after API call
    setPasswordData({
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    });
    setPasswordChangeMode(false);
  };

  const handleSavePhoneNumber = async () => {
    if (!phoneNumber.trim()) {
      alert('Please enter a valid phone number!');
      return;
    }

    await dispatch(updateProfile({
      phone_no: phoneNumber
    }));

    setPhoneEditMode(false);
  };

  const handleSaveAddress = async () => {
    if (!addressInput.trim()) {
      alert('Please enter a valid address!');
      return;
    }

    await dispatch(updateProfile({
      agent_address: addressInput
    }));

    setAddressEditMode(false);
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
        setImageOffsetX(0);
        setImageOffsetY(0);
      };
      reader.readAsDataURL(file);
    }
  };

  const applyImageAdjustments = () => {
    const canvas = canvasRef.current;
    const ctx = canvas.getContext('2d');
    const img = new Image();

    img.onload = () => {
      // Set canvas size for the final circular crop
      canvas.width = 200;
      canvas.height = 200;

      // Clear canvas
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      // Create circular clipping path
      ctx.save();
      ctx.beginPath();
      ctx.arc(canvas.width / 2, canvas.height / 2, canvas.width / 2, 0, Math.PI * 2);
      ctx.clip();

      // Apply transformations
      ctx.translate(canvas.width / 2 + imageOffsetX, canvas.height / 2 + imageOffsetY);
      ctx.rotate((imageRotation * Math.PI) / 180);
      ctx.scale(imageScale, imageScale);

      // Calculate image dimensions to fit properly
      const aspectRatio = img.width / img.height;
      let drawWidth, drawHeight;

      if (aspectRatio > 1) {
        // Landscape image
        drawHeight = 200;
        drawWidth = drawHeight * aspectRatio;
      } else {
        // Portrait image
        drawWidth = 200;
        drawHeight = drawWidth / aspectRatio;
      }

      // Draw image centered
      ctx.drawImage(img, -drawWidth / 2, -drawHeight / 2, drawWidth, drawHeight);
      ctx.restore();

      // Get adjusted image data
      const adjustedDataUrl = canvas.toDataURL('image/jpeg', 0.9);
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
    setImageOffsetX(0);
    setImageOffsetY(0);
  };

  const handleUploadProfileImage = async () => {
    const imageToUpload = adjustedImage || previewImage;
    // console.log('=== IMAGE UPLOAD DEBUG ===');
    // console.log('imageToUpload:', imageToUpload);
    // console.log('selectedProfileImage:', selectedProfileImage);
    // console.log('adjustedImage:', adjustedImage);
    // console.log('canvasRef.current:', canvasRef.current);

    if (imageToUpload && selectedProfileImage) {
      try {
        // Convert canvas to blob if we have adjustedImage and canvas is available
        if (adjustedImage && canvasRef.current) {
          // console.log('Using canvas adjusted image');
          const canvas = canvasRef.current;
          canvas.toBlob(async (blob) => {
            // console.log('Canvas blob created:', blob);
            const file = new File([blob], 'profile-image.jpg', { type: 'image/jpeg' });
            // console.log('File created from canvas:', file);
            const result = await dispatch(updateProfile({ image: file }));
            // console.log('Canvas upload result:', result);
          }, 'image/jpeg', 0.9);
        } else {
          // Use original selected file
          // console.log('Using original selected file:', selectedProfileImage);
          const result = await dispatch(updateProfile({ image: selectedProfileImage }));
          // console.log('Original file upload result:', result);
        }

        // Don't reset state immediately - let the success handler do it
        // console.log('Image upload initiated successfully');
      } catch (error) {
        // console.error('Error during image upload:', error);
        // Reset state on error only
        setSelectedProfileImage(null);
        setPreviewImage(null);
        setAdjustedImage(null);
        setImageScale(1);
        setImageRotation(0);
        setImageOffsetX(0);
        setImageOffsetY(0);
      }
    } else {
      // console.log('No image to upload - missing required data');
    }
  };

  const handleLogout = () => {
    dispatch(logoutUser());
    dispatch(setAgentIdEdit(null));
    dispatch(clearSelectedDmc()); // Clear DMC selection on logout
    handleProfileClose();
  };

  // Get user data from Redux state
  const { userRole, Username, Email, profilePicture, phoneNo, agentId, agent_address } = useSelector((state) => state.auth);
  const { loading: profileLoading, success: profileSuccess, error: profileError, data: profileData } = useSelector((state) => state.profile);

  useEffect(() => {


    dispatch(fetchEnquiries())

  }, [dispatch])

  // Handle profile update success/error
  useEffect(() => {
    if (profileSuccess && profileData) {
      alert('Profile updated successfully!');
      // console.log('Profile update successful! Response:', profileData);

      // Update auth state with new profile data
      if (profileData.data) {
        // console.log('API Response Fields:');
        // console.log('agent_image:', profileData.data.agent_image);
        // console.log('image:', profileData.data.image);

        const updateData = {};
        if (profileData.data.phone) {
          updateData.phone = profileData.data.phone;
          setPhoneNumber(profileData.data.phone); // Update local state too
        }
        if (profileData.data.agent_address) {
          updateData.agent_address = profileData.data.agent_address;
          setAddressInput(profileData.data.agent_address); // Update local state too
        }
        // Prefer personal profile `image`; fallback to `agent_image` if needed
        if (profileData.data.image) {
          updateData.image = profileData.data.image;
        } else if (profileData.data.agent_image) {
          updateData.image = profileData.data.agent_image;
        }

        // console.log('Updating auth state with:', updateData);
        dispatch(updateProfileData(updateData));
      }

      // Reset image upload state when successful
      setSelectedProfileImage(null);
      setPreviewImage(null);
      setAdjustedImage(null);
      setImageScale(1);
      setImageRotation(0);
      setImageOffsetX(0);
      setImageOffsetY(0);

       // Close the profile modal after successful image/profile update
       setProfileModalOpen(false);

      dispatch(resetProfileState());
    }
    if (profileError) {
      alert(`Error updating profile: ${profileError}`);
      console.error('Profile update error:', profileError);
      dispatch(resetProfileState());
    }
  }, [profileSuccess, profileError, profileData, dispatch]);

  // Ensure dashboard content appears ONLY on the exact dashboard route
  const isDashboardPage = location.pathname === "/dashboard/db-dashboard";

  const handleMainTabChange = (event, newValue) => {
    setMainTabValue(newValue);
  };

  // Render appropriate dashboard based on user role
  const renderDashboardContent = () => {
    // If the role is 'Manager' or 'Sales Manager', render the Sales Manager dashboard
    if (userRole === "Sales Head(DMC)" || userRole === "Sales Manager (DMC)" || userRole === "Assistant Manager (DMC)" || userRole === "Operational Head(DMC)" || userRole === "DMC Operational Manager" || userRole === "DMC Assistant Operational Manager") {
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
                label="Quick Enquiries"
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
                                : userRole === "Operational Head(DMC)"
                                  ? "linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%)" // Purple gradient for Operational Head
                                  : userRole === "DMC Operational Manager"
                                    ? "linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)" // Dark red gradient for DMC Operational Manager
                                                                    : userRole === "DMC Assistant Operational Manager"
                                  ? "linear-gradient(135deg, #3498db 0%, #2980b9 100%)" // Blue gradient for DMC Assistant Operational Manager
                                      : "linear-gradient(135deg, #667eea 0%, #764ba2 100%)", // Default blue-purple gradient for Agent
                        borderRadius: "16px",
                        marginBottom: "30px",
                        overflow: "visible",
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
                                ) : userRole === "Operational Head(DMC)" ? (
                                  <AdminPanelSettings sx={{ fontSize: 32, color: "white" }} />
                                ) : userRole === "DMC Operational Manager" ? (
                                  <Engineering sx={{ fontSize: 32, color: "white" }} />
                                ) : userRole === "DMC Assistant Operational Manager" ? (
                                  <SupportAgent sx={{ fontSize: 32, color: "white" }} />
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
                                    ) : userRole === "Operational Head(DMC)" ? (
                                      <Dashboard sx={{ fontSize: 32, color: "white" }} />
                                    ) : userRole === "DMC Operational Manager" ? (
                                      <Analytics sx={{ fontSize: 32, color: "white" }} />
                                    ) : userRole === "DMC Assistant Operational Manager" ? (
                                      <Monitor sx={{ fontSize: 32, color: "white" }} />
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
                                            : userRole === "Operational Head(DMC)"
                                              ? "Operational Dashboard"
                                              : userRole === "DMC Operational Manager"
                                                ? "Operational Manager Dashboard"
                                                : userRole === "DMC Assistant Operational Manager"
                                                  ? "Assistant Operational Dashboard"
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
                                            : userRole === "Operational Head(DMC)"
                                              ? "Operational Head"
                                              : userRole === "DMC Operational Manager"
                                                ? "Operational Manager"
                                                : userRole === "DMC Assistant Operational Manager"
                                                  ? "Assistant Operational Manager"
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
                                              : userRole === "Operational Head(DMC)"
                                                ? "rgba(255, 255, 255, 0.26)" // Balanced for purple bg
                                                : userRole === "DMC Operational Manager"
                                                  ? "rgba(255, 255, 255, 0.24)" // Balanced for dark red bg
                                                  : userRole === "DMC Assistant Operational Manager"
                                                    ? "rgba(255, 255, 255, 0.23)" // Balanced for blue bg
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
                                              : userRole === "Operational Head(DMC)"
                                                ? "1px solid rgba(255, 255, 255, 0.38)" // Medium-strong border for purple
                                                : userRole === "DMC Operational Manager"
                                                  ? "1px solid rgba(255, 255, 255, 0.42)" // Strong border for dark red
                                                  : userRole === "DMC Assistant Operational Manager"
                                                    ? "1px solid rgba(255, 255, 255, 0.36)" // Medium border for blue
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
                                          : userRole === "Operational Head(DMC)"
                                            ? "Operational strategy and executive oversight"
                                            : userRole === "DMC Operational Manager"
                                              ? "Operational performance and team management"
                                              : userRole === "DMC Assistant Operational Manager"
                                                ? "Operational coordination and support management"
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
                                  ) : userRole === "Operational Head(DMC)" ? (
                                    <MilitaryTech sx={{ fontSize: 18, color: "white" }} />
                                  ) : userRole === "DMC Operational Manager" ? (
                                    <WorkspacePremium sx={{ fontSize: 18, color: "white" }} />
                                                                     ) : userRole === "DMC Assistant Operational Manager" ? (
                                     <Verified sx={{ fontSize: 18, color: "white" }} />
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
                                          : userRole === "Operational Head(DMC)"
                                            ? "Executive Level"
                                            : userRole === "DMC Operational Manager"
                                              ? "Management Level"
                                              : userRole === "DMC Assistant Operational Manager"
                                                ? "Supervisor Level"
                                                : "Agent Level"}
                                  </Typography>
                                </Stack>
                              </Box>

                              {/* Agent Profile Button - Only for Agent */}
                              {userRole !== "Sales Head(DMC)" && userRole !== "Sales Manager (DMC)" && userRole !== "Assistant Manager (DMC)" && userRole !== "Operational Head(DMC)" && userRole !== "DMC Operational Manager" && userRole !== "DMC Assistant Operational Manager" && (
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
                                        padding: "4px",
                                        transition: "all 0.2s ease",
                                        "&:hover": {
                                          background: "rgba(255, 255, 255, 0.25)",
                                        }
                                      }}
                                    >
                                      <Avatar
                                        src={adjustedImage || getProfilePictureUrl(profilePicture) || ""}
                                        sx={{
                                          width: 32,
                                          height: 32,
                                          fontSize: "1rem",
                                          backgroundColor: "rgba(255, 255, 255, 0.2)",
                                          color: "white",
                                          animation: "profilePulse 3s ease-in-out infinite",
                                          transition: "all 0.3s ease",
                                          "&:hover": {
                                            transform: "scale(1.15) rotate(5deg)",
                                            boxShadow: "0 0 20px rgba(255, 255, 255, 0.4)",
                                          },
                                          "@keyframes profilePulse": {
                                            "0%": {
                                              boxShadow: "0 0 0 0 rgba(255, 255, 255, 0.3)",
                                            },
                                            "50%": {
                                              boxShadow: "0 0 0 8px rgba(255, 255, 255, 0.1)",
                                            },
                                            "100%": {
                                              boxShadow: "0 0 0 0 rgba(255, 255, 255, 0)",
                                            },
                                          },
                                        }}
                                      >
                                        {!adjustedImage && !profilePicture && (
                                          <AccountCircleOutlined
                                            sx={{
                                              fontSize: 24,
                                              animation: "iconGlow 2s ease-in-out infinite alternate",
                                              "@keyframes iconGlow": {
                                                "0%": {
                                                  filter: "brightness(1)",
                                                },
                                                "100%": {
                                                  filter: "brightness(1.3)",
                                                },
                                              },
                                            }}
                                          />
                                        )}
                                      </Avatar>
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
                                    <Box
                                      sx={{
                                        px: 3,
                                        py: 0.5,
                                        background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                                        color: "white",
                                        borderRadius: "12px 12px 0 0",
                                        position: "relative",
                                        overflow: "hidden",
                                        "&::before": {
                                          content: '""',
                                          position: "absolute",
                                          top: 0,
                                          left: 0,
                                          right: 0,
                                          bottom: 0,
                                          background: "rgba(255, 255, 255, 0.1)",
                                          backdropFilter: "blur(10px)",
                                        }
                                      }}
                                    >
                                      <Box sx={{ position: "relative", zIndex: 1 }}>
                                        <Stack direction="row" alignItems="center" spacing={1.5} sx={{ mb: 1 }}>
                                          <AccountCircleOutlined sx={{ fontSize: 24, color: "white" }} />
                                          <Typography variant="h6" fontWeight="bold" sx={{ color: "white" }}>
                                            Agent Profile
                                          </Typography>
                                        </Stack>
                                        <Stack direction="row" alignItems="center" spacing={1}>
                                          <SettingsOutlined sx={{ fontSize: 16, color: "rgba(255, 255, 255, 0.8)" }} />
                                          <Typography
                                            variant="body2"
                                            sx={{
                                              color: "rgba(255, 255, 255, 0.9)",
                                              fontSize: "0.85rem"
                                            }}
                                          >
                                            Manage your account settings
                                          </Typography>
                                        </Stack>
                                      </Box>
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
                        animation: profileModalOpen ? "slideInUp 0.4s ease-out" : "none",
                        "@keyframes slideInUp": {
                          "0%": {
                            opacity: 0,
                            transform: "translateY(30px) scale(0.95)",
                          },
                          "100%": {
                            opacity: 1,
                            transform: "translateY(0) scale(1)",
                          },
                        },
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
                      <Stack direction="row" alignItems="center" spacing={2}>
                        <AccountCircleOutlined sx={{ fontSize: 28, color: "white" }} />
                        <Typography variant="h5" fontWeight="bold">
                          Agent Profile
                        </Typography>
                      </Stack>
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
                              src={adjustedImage || previewImage || getProfilePictureUrl(profilePicture) || ""}
                              sx={{
                                width: 120,
                                height: 120,
                                border: "4px solid #667eea",
                                fontSize: "3rem",
                                background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                                animation: "pulseGlow 2s ease-in-out infinite alternate",
                                "@keyframes pulseGlow": {
                                  "0%": {
                                    boxShadow: "0 0 20px rgba(102, 126, 234, 0.3)",
                                  },
                                  "100%": {
                                    boxShadow: "0 0 30px rgba(102, 126, 234, 0.6)",
                                  },
                                },
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
                                "&:hover": {
                                  background: "#f5f5f5",
                                  transform: "scale(1.1) rotate(5deg)",
                                  transition: "all 0.2s ease",
                                },
                                transition: "all 0.2s ease",
                                animation: "bounce 2s ease-in-out infinite",
                                "@keyframes bounce": {
                                  "0%, 20%, 50%, 80%, 100%": {
                                    transform: "translateY(0)",
                                  },
                                  "40%": {
                                    transform: "translateY(-3px)",
                                  },
                                  "60%": {
                                    transform: "translateY(-1px)",
                                  },
                                },
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
                                disabled={profileLoading}
                                sx={{
                                  borderRadius: "20px",
                                  background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                                  "&:hover": {
                                    background: "linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%)",
                                    transform: "translateY(-2px)",
                                    boxShadow: "0 4px 12px rgba(102, 126, 234, 0.4)",
                                  },
                                  transition: "all 0.3s ease",
                                  animation: "slideInUp 0.6s ease-out",
                                  "@keyframes slideInUp": {
                                    "0%": {
                                      opacity: 0,
                                      transform: "translateY(20px)",
                                    },
                                    "100%": {
                                      opacity: 1,
                                      transform: "translateY(0)",
                                    },
                                  },
                                }}
                              >
                                {profileLoading ? 'Uploading...' : 'Upload Image'}
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
                                  setImageOffsetX(0);
                                  setImageOffsetY(0);
                                }}
                                sx={{
                                  borderRadius: "20px",
                                  transition: "all 0.3s ease",
                                  animation: "slideInUp 0.6s ease-out 0.1s both",
                                  "@keyframes slideInUp": {
                                    "0%": {
                                      opacity: 0,
                                      transform: "translateY(20px)",
                                    },
                                    "100%": {
                                      opacity: 1,
                                      transform: "translateY(0)",
                                    },
                                  },
                                }}
                              >
                                Cancel
                              </Button>
                            </Stack>
                          )}

                          {/* <Typography variant="h6" fontWeight="bold">
                             {Username || "Agent Name"}
                           </Typography> */}
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
                                transition: "all 0.3s ease",
                                "&:hover": {
                                  boxShadow: "0 2px 8px rgba(102, 126, 234, 0.1)",
                                  transform: "translateY(-1px)",
                                },
                              },
                              animation: "fadeInUp 0.8s ease-out",
                              "@keyframes fadeInUp": {
                                "0%": {
                                  opacity: 0,
                                  transform: "translateY(15px)",
                                },
                                "100%": {
                                  opacity: 1,
                                  transform: "translateY(0)",
                                },
                              },
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
                            value={phoneEditMode ? phoneNumber : (phoneNo || "Not provided")}
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
                                        disabled={profileLoading || !phoneNumber.trim()}
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

                          {/* <TextField
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
                           /> */}

                          <TextField
                            label="Address"
                            value={addressEditMode ? addressInput : (agent_address || "Not provided")}
                            onChange={(e) => setAddressInput(e.target.value)}
                            InputProps={{
                              startAdornment: (
                                <InputAdornment position="start">
                                  <LocationOn sx={{ color: "primary.main" }} />
                                </InputAdornment>
                              ),
                              endAdornment: (
                                <InputAdornment position="end">
                                  {addressEditMode ? (
                                    <Stack direction="row" spacing={1}>
                                      <IconButton
                                        size="small"
                                        onClick={handleSaveAddress}
                                        disabled={profileLoading || !addressInput.trim()}
                                        sx={{ color: "success.main" }}
                                      >
                                        <Check />
                                      </IconButton>
                                      <IconButton
                                        size="small"
                                        onClick={() => {
                                          setAddressEditMode(false);
                                          setAddressInput('');
                                        }}
                                        sx={{ color: "error.main" }}
                                      >
                                        <Close />
                                      </IconButton>
                                    </Stack>
                                  ) : (
                                    <IconButton
                                      size="small"
                                      onClick={() => setAddressEditMode(true)}
                                      sx={{ color: "primary.main" }}
                                    >
                                      <EditOutlined />
                                    </IconButton>
                                  )}
                                </InputAdornment>
                              ),
                              readOnly: !addressEditMode,
                            }}
                            variant="outlined"
                            fullWidth
                            placeholder={addressEditMode ? "Enter your address" : ""}
                            multiline
                            rows={addressEditMode ? 2 : 1}
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
                            <Stack direction="row" alignItems="center" spacing={1.5}>
                              <Lock sx={{ fontSize: 24, color: "primary.main" }} />
                              <Typography variant="h6" fontWeight="bold">
                                Security
                              </Typography>
                            </Stack>
                            <Button
                              variant={passwordChangeMode ? "outlined" : "contained"}
                              startIcon={<Lock />}
                              onClick={() => setPasswordChangeMode(!passwordChangeMode)}
                              sx={{
                                borderRadius: "8px",
                                transition: "all 0.3s ease",
                                "&:hover": {
                                  transform: "translateY(-2px)",
                                  boxShadow: "0 4px 12px rgba(0,0,0,0.15)",
                                },
                                animation: "pulse 2s ease-in-out infinite",
                                "@keyframes pulse": {
                                  "0%": {
                                    boxShadow: "0 0 0 0 rgba(102, 126, 234, 0.4)",
                                  },
                                  "70%": {
                                    boxShadow: "0 0 0 10px rgba(102, 126, 234, 0)",
                                  },
                                  "100%": {
                                    boxShadow: "0 0 0 0 rgba(102, 126, 234, 0)",
                                  },
                                },
                              }}
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
                                disabled={profileLoading || !passwordData.currentPassword || !passwordData.newPassword || !passwordData.confirmPassword}
                                sx={{
                                  borderRadius: "8px",
                                  py: 1.5,
                                  background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                                  "&:hover": {
                                    background: "linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%)",
                                  }
                                }}
                              >
                                {profileLoading ? 'Saving...' : 'Save New Password'}
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
                        <Grid item xs={12} md={7}>
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
                                width: 300,
                                height: 300,
                                border: "2px dashed #ccc",
                                borderRadius: "12px",
                                display: "flex",
                                alignItems: "center",
                                justifyContent: "center",
                                overflow: "hidden",
                                backgroundColor: "white",
                                position: "relative",
                              }}
                            >
                              {previewImage && (
                                <img
                                  src={previewImage}
                                  alt="Preview"
                                  style={{
                                    maxWidth: "100%",
                                    maxHeight: "100%",
                                    objectFit: "contain",
                                    transform: `translate(${imageOffsetX}px, ${imageOffsetY}px) scale(${imageScale}) rotate(${imageRotation}deg)`,
                                    transition: "transform 0.3s ease",
                                  }}
                                />
                              )}

                              {/* Crop overlay circle */}
                              <Box
                                sx={{
                                  position: "absolute",
                                  top: "50%",
                                  left: "50%",
                                  transform: "translate(-50%, -50%)",
                                  width: 200,
                                  height: 200,
                                  borderRadius: "50%",
                                  border: "2px solid #667eea",
                                  backgroundColor: "rgba(102, 126, 234, 0.1)",
                                  pointerEvents: "none",
                                  zIndex: 1,
                                }}
                              />

                              {/* Crop guide text */}
                              <Typography
                                variant="caption"
                                sx={{
                                  position: "absolute",
                                  bottom: 8,
                                  left: "50%",
                                  transform: "translateX(-50%)",
                                  backgroundColor: "rgba(0,0,0,0.7)",
                                  color: "white",
                                  px: 1,
                                  py: 0.5,
                                  borderRadius: 1,
                                  fontSize: "0.7rem",
                                  zIndex: 2,
                                }}
                              >
                                Blue circle shows crop area
                              </Typography>
                            </Box>
                          </Paper>
                        </Grid>

                        {/* Controls */}
                        <Grid item xs={12} md={5}>
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

                            {/* Position Controls */}
                            <Box>
                              <Typography variant="body1" fontWeight="bold" sx={{ mb: 2 }}>
                                Position
                              </Typography>

                              {/* Horizontal Position */}
                              <Box sx={{ mb: 2 }}>
                                <Typography variant="body2" sx={{ mb: 1 }}>
                                  Horizontal
                                </Typography>
                                <Slider
                                  value={imageOffsetX}
                                  onChange={(_, value) => setImageOffsetX(value)}
                                  min={-100}
                                  max={100}
                                  step={5}
                                  valueLabelDisplay="on"
                                  sx={{ color: "#667eea" }}
                                />
                              </Box>

                              {/* Vertical Position */}
                              <Box>
                                <Typography variant="body2" sx={{ mb: 1 }}>
                                  Vertical
                                </Typography>
                                <Slider
                                  value={imageOffsetY}
                                  onChange={(_, value) => setImageOffsetY(value)}
                                  min={-100}
                                  max={100}
                                  step={5}
                                  valueLabelDisplay="on"
                                  sx={{ color: "#667eea" }}
                                />
                              </Box>
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
                                setImageOffsetX(0);
                                setImageOffsetY(0);
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
