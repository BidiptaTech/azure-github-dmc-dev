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
import ResponsiveTabs from "./components/ResponsiveTabs";
import { Grid, Container, Box, Tab, Tabs, Stack, Typography, Avatar, Chip, Card, CardContent, Fade, Grow, Menu, MenuItem, Button, Tooltip, IconButton, Divider, Dialog, DialogTitle, DialogContent, DialogActions, TextField, InputAdornment, Slider, Paper } from "@mui/material";
import {
  Dashboard as DashboardIcon,
  Search,
  EventNote,
  AssessmentOutlined,
  MoreHoriz,
  EmailOutlined,
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
import { clearBookingFlow } from "@/utils/clearBookingFlow";

const BOOKING_FLOW_SEGMENTS = [
  "view-hotel-search",
  "hotel-details",
  "hotel-checkout",
  "thank-you",
  "attractions",
  "restaurants",
  "pickupdrop",
  "localtransfer",
  "tourguide",
  "tour-single",
  "restaurants-details",
  "activity-single",
  "restaurants-checkout",
  "attraction-checkout",
  "restaurants-thank-you",
  "attraction-thank-you",
  "CheckOut",
  "ThankYou",
  "updatebooking",
  "cart",
  "cart-checkout",
];


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
    window.dispatchEvent(new Event("open-agent-profile"));
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


  // Ensure dashboard content appears ONLY on the exact dashboard route
  const isDashboardPage = location.pathname === "/dashboard/db-dashboard";

  // Book Tour / Quick Enquiry hero pages: allow expanded search to grow past viewport
  const isBookTourHeroPage =
    location.pathname.includes("/home_1") ||
    location.pathname.includes("/home_2");

  const isAgentDashboard =
    userRole !== "Sales Head(DMC)" &&
    userRole !== "Sales Manager (DMC)" &&
    userRole !== "Assistant Manager (DMC)" &&
    userRole !== "Operational Head(DMC)" &&
    userRole !== "DMC Operational Manager" &&
    userRole !== "DMC Assistant Operational Manager";

  // Leaving ProtectedRoutetour / booking module → clear session + Redux
  useEffect(() => {
    const path = location.pathname || "";
    if (!path.startsWith("/dashboard/db-dashboard")) return;

    const stillInBookingFlow = BOOKING_FLOW_SEGMENTS.some((segment) =>
      path.includes(segment)
    );
    if (stillInBookingFlow) return;

    clearBookingFlow(dispatch);
  }, [location.pathname, dispatch]);

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
        icon: <WorkOutline />,
        label: "Fixed Itinerary Packages"
      }
    ];

    const tabContents = [
      <AnimatedBox key="tab-0" direction="up" delay={800}>
        <BasicTabs />
      </AnimatedBox>,
      <AnimatedBox key="tab-1" direction="up" delay={800}>
        <Box sx={{ p: 3, backgroundColor: "white", borderRadius: "0 0 12px 12px" }}>
          <EnquiryList />
        </Box>
      </AnimatedBox>,
      <AnimatedBox key="tab-2" direction="up" delay={800}>
        <Box sx={{ p: 3, backgroundColor: "white", borderRadius: "0 0 12px 12px" }}>
          <PreDefinePackages />
        </Box>
      </AnimatedBox>
    ];

    return (
      <>
        <AnimatedBox direction="down" delay={600}>
          <ResponsiveTabs
            tabs={tabs}
            value={mainTabValue}
            onChange={handleMainTabChange}
            sx={{
              backgroundColor: "white",
              borderRadius: "14px",
              border: "1px solid #e8eef8",
              boxShadow: "0 1px 2px rgba(15, 23, 42, 0.04)",
              px: 1,
              "& .MuiTabs-indicator": {
                backgroundColor: "#3554d1",
                height: 2,
              },
              "& .Mui-selected": {
                color: "#3554d1 !important",
                fontWeight: 700,
              },
            }}
          >
            {tabContents}
          </ResponsiveTabs>
        </AnimatedBox>
      </>
    );
  };

  return (
    <>
      <div className="header-margin"></div>
      <Header1 onViewProfile={isAgentDashboard ? handleViewProfile : undefined} />
      <main className={isBookTourHeroPage ? "main-search-hero" : undefined}>
        <Outlet /> {/* This renders the nested routes */}
        {isDashboardPage && (
          <DashboardEntrance>
            <div
              className="dashboard"
              style={{
                backgroundColor: "#f9fafb",
                minHeight: "calc(100vh - 200px)",
                marginTop: "44px",
                padding: "0 12px",
              }}
            >
              <Container maxWidth="xxl">
                <div className="dashboard__main" style={{ padding: "8px 0" }}>
                  {isAgentDashboard && (
                    <>
                    <Card
                      elevation={0}
                      sx={{
                        mb: 1,
                        borderRadius: "12px",
                        border: "1px solid #e6eef8",
                        background: "#fff",
                        overflow: "hidden",
                      }}
                    >
                      <CardContent sx={{ py: 1.25, px: { xs: 1.5, md: 2 }, "&:last-child": { pb: 1.25 } }}>
                        <Stack direction="row" spacing={1.25} alignItems="center">
                          <Avatar sx={{ width: 36, height: 36, bgcolor: "#eef3ff", color: "#3554d1" }}>
                            <WorkOutline sx={{ fontSize: 18 }} />
                          </Avatar>
                          <Box>
                            <Stack direction="row" spacing={0.75} alignItems="center">
                              <Typography
                                component="h1"
                                sx={{ fontWeight: 700, fontSize: "1rem", color: "#0f172a", lineHeight: 1.2 }}
                              >
                                Agent Dashboard
                              </Typography>
                              <Chip
                                label="Agent"
                                size="small"
                                sx={{
                                  height: 20,
                                  bgcolor: "#eef3ff",
                                  color: "#3554d1",
                                  fontWeight: 700,
                                  fontSize: "0.68rem",
                                }}
                              />
                            </Stack>
                            <Typography sx={{ color: "#64748b", fontSize: "0.78rem", lineHeight: 1.3 }}>
                              Booking management and client services
                            </Typography>
                          </Box>
                        </Stack>
                      </CardContent>
                    </Card>
                    </>
                  )}
                  <Box sx={{ display: isAgentDashboard ? "none" : "block" }}>
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
                      <CardContent sx={{ p: { xs: 2, sm: 3, md: 4 }, position: "relative", zIndex: 1 }}>
                        <Stack 
                          direction={{ xs: "column", sm: "row" }} 
                          justifyContent="space-between" 
                          alignItems={{ xs: "flex-start", sm: "center" }}
                          spacing={{ xs: 2, sm: 0 }}
                        >
                          <Stack direction={{ xs: "column", sm: "row" }} spacing={{ xs: 2, sm: 3 }} alignItems={{ xs: "flex-start", sm: "center" }}>
                            <Grow in={true} timeout={800}>
                              <Avatar
                                sx={{
                                  width: { xs: 48, sm: 56, md: 64 },
                                  height: { xs: 48, sm: 56, md: 64 },
                                  background: "rgba(255, 255, 255, 0.2)",
                                  backdropFilter: "blur(10px)",
                                  border: "2px solid rgba(255, 255, 255, 0.3)",
                                }}
                              >
                                {userRole === "Sales Head(DMC)" ? (
                                  <SupervisorAccount sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                ) : userRole === "Sales Manager (DMC)" ? (
                                  <ManageAccounts sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                ) : userRole === "Assistant Manager (DMC)" ? (
                                  <Business sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                ) : userRole === "Operational Head(DMC)" ? (
                                  <AdminPanelSettings sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                ) : userRole === "DMC Operational Manager" ? (
                                  <Engineering sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                ) : userRole === "DMC Assistant Operational Manager" ? (
                                  <SupportAgent sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                ) : (
                                  <Person sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                )}
                              </Avatar>
                            </Grow>

                            <Fade in={true} timeout={1000}>
                              <Stack spacing={1} sx={{ width: { xs: "100%", sm: "auto" } }}>
                                <Stack direction="row" alignItems="center" spacing={{ xs: 1, sm: 1.5 }} sx={{ flexWrap: "wrap" }}>
                                  {userRole === "Sales Head(DMC)" ? (
                                    <AccountBalance sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                  ) : userRole === "Sales Manager (DMC)" ? (
                                    <BarChart sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                  ) : userRole === "Assistant Manager (DMC)" ? (
                                    <Settings sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                  ) : userRole === "Operational Head(DMC)" ? (
                                    <Dashboard sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                  ) : userRole === "DMC Operational Manager" ? (
                                    <Analytics sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                  ) : userRole === "DMC Assistant Operational Manager" ? (
                                    <Monitor sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                  ) : (
                                    <WorkOutline sx={{ fontSize: { xs: 24, sm: 28, md: 32 }, color: "white" }} />
                                  )}
                                  <Typography
                                    variant="h4"
                                    component="h1"
                                    sx={{
                                      fontWeight: 700,
                                      color: "white",
                                      fontSize: { xs: "1.2rem", sm: "1.5rem", md: "2rem" },
                                      textShadow: "0 2px 4px rgba(0,0,0,0.1)",
                                      lineHeight: 1.2,
                                      wordBreak: "break-word",
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
                                      fontSize: { xs: "0.65rem", sm: "0.7rem", md: "0.75rem" },
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

                                <Stack direction="row" alignItems="center" spacing={1} sx={{ width: "100%" }}>
                                  <TrendingUp sx={{ fontSize: { xs: 14, sm: 16 }, color: "rgba(255, 255, 255, 0.8)" }} />
                                  <Typography
                                    variant="body1"
                                    sx={{
                                      color: "rgba(255, 255, 255, 0.9)",
                                      fontSize: { xs: "0.8rem", sm: "0.9rem", md: "0.95rem" },
                                      fontWeight: 400,
                                      lineHeight: 1.3,
                                      wordBreak: "break-word",
                                      overflow: "hidden",
                                      textOverflow: "ellipsis",
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
                            <Stack 
                              direction={{ xs: "column", sm: "row" }} 
                              spacing={{ xs: 1.5, sm: 2 }} 
                              alignItems={{ xs: "flex-start", sm: "center" }}
                              sx={{ width: { xs: "100%", sm: "auto" } }}
                            >
                              <Box
                                sx={{
                                  background: "rgba(255, 255, 255, 0.1)",
                                  backdropFilter: "blur(10px)",
                                  borderRadius: "12px",
                                  px: { xs: 2, sm: 3 },
                                  py: { xs: 1, sm: 1.5 },
                                  border: "1px solid rgba(255, 255, 255, 0.2)",
                                  width: { xs: "100%", sm: "auto" },
                                }}
                              >
                                <Stack direction="row" alignItems="center" spacing={1} sx={{ width: "100%" }}>
                                  {userRole === "Sales Head(DMC)" ? (
                                    <Diamond sx={{ fontSize: { xs: 16, sm: 18 }, color: "white" }} />
                                  ) : userRole === "Sales Manager (DMC)" ? (
                                    <EmojiEvents sx={{ fontSize: { xs: 16, sm: 18 }, color: "white" }} />
                                  ) : userRole === "Assistant Manager (DMC)" ? (
                                    <Star sx={{ fontSize: { xs: 16, sm: 18 }, color: "white" }} />
                                  ) : userRole === "Operational Head(DMC)" ? (
                                    <MilitaryTech sx={{ fontSize: { xs: 16, sm: 18 }, color: "white" }} />
                                  ) : userRole === "DMC Operational Manager" ? (
                                    <WorkspacePremium sx={{ fontSize: { xs: 16, sm: 18 }, color: "white" }} />
                                                                     ) : userRole === "DMC Assistant Operational Manager" ? (
                                     <Verified sx={{ fontSize: { xs: 16, sm: 18 }, color: "white" }} />
                                  ) : (
                                    <Badge sx={{ fontSize: { xs: 16, sm: 18 }, color: "white" }} />
                                  )}
                                  <Typography
                                    variant="body2"
                                    sx={{
                                      color: "white",
                                      fontWeight: 600,
                                      fontSize: { xs: "0.8rem", sm: "0.85rem", md: "0.9rem" },
                                      textShadow: "0 1px 2px rgba(0,0,0,0.1)",
                                      wordBreak: "break-word",
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
                                        padding: { xs: "4px 8px", sm: "4px" },
                                        transition: "all 0.2s ease",
                                        width: { xs: "auto", sm: "auto" },
                                        minWidth: { xs: "auto", sm: "auto" },
                                        justifyContent: { xs: "center", sm: "center" },
                                        borderRadius: { xs: "20px", sm: "50%" },
                                        "&:hover": {
                                          background: "rgba(255, 255, 255, 0.25)",
                                        }
                                      }}
                                    >
                                      <Stack direction="row" alignItems="center" spacing={1} sx={{ width: "100%" }}>
                                        <Avatar
                                          src={adjustedImage || getProfilePictureUrl(profilePicture) || ""}
                                          sx={{
                                            width: { xs: 32, sm: 36 },
                                            height: { xs: 32, sm: 36 },
                                            fontSize: { xs: "1rem", sm: "1.1rem" },
                                            backgroundColor: "rgba(255, 255, 255, 0.2)",
                                            color: "white",
                                            border: "2px solid rgba(255, 255, 255, 0.3)",
                                            borderRadius: "50%",
                                            overflow: "hidden",
                                            objectFit: "cover",
                                            animation: "profilePulse 3s ease-in-out infinite",
                                            transition: "all 0.3s ease",
                                            "&:hover": {
                                              transform: "scale(1.05)",
                                              boxShadow: "0 0 20px rgba(255, 255, 255, 0.4)",
                                            },
                                            "& img": {
                                              width: "100%",
                                              height: "100%",
                                              objectFit: "cover",
                                              borderRadius: "50%",
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
                                                fontSize: { xs: 20, sm: 24 },
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
                                        <Typography
                                          variant="body2"
                                          sx={{
                                            color: "white",
                                            fontSize: { xs: "0.75rem", sm: "0.9rem" },
                                            fontWeight: 500,
                                            display: { xs: "block", sm: "none" },
                                            whiteSpace: "nowrap",
                                          }}
                                        >
                                          Profile
                                        </Typography>
                                      </Stack>
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
                  </Box>

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
