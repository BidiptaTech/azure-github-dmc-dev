import { useEffect, useRef, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import {
  Box,
  Stack,
  Typography,
  Avatar,
  Chip,
  Button,
  IconButton,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  InputAdornment,
  Slider,
  Paper,
  Grid,
} from "@mui/material";
import {
  AccountCircleOutlined,
  EditOutlined,
  Close,
  Visibility,
  VisibilityOff,
  Lock,
  Phone,
  Check,
  LocationOn,
  Person,
  EmailOutlined,
  ZoomIn,
  ZoomOut,
  RotateLeft,
  RotateRight,
} from "@mui/icons-material";
import { updateProfileData } from "@/slice/common/authSlices";
import { updateProfile, resetProfileState } from "@/slice/common/profileSlice";
import { BASE_URL } from "@/services/api";

const getProfilePictureUrl = (profilePicturePath) => {
  if (!profilePicturePath) return "";
  if (profilePicturePath.startsWith("http")) return profilePicturePath;
  const cleanPath = profilePicturePath.replace(/^\\?\/+/, "").replace(/\\/g, "");
  const baseUrl = BASE_URL.replace("/api/v1", "");
  return `${baseUrl}/${cleanPath}`;
};

const AgentProfileModal = ({ open, onClose }) => {
  const dispatch = useDispatch();
  const { userRole, Username, Email, profilePicture, phoneNo, agent_address } = useSelector((state) => state.auth);
  const { loading: profileLoading, success: profileSuccess, error: profileError, data: profileData } = useSelector((state) => state.profile);

  const [passwordChangeMode, setPasswordChangeMode] = useState(false);
  const [phoneEditMode, setPhoneEditMode] = useState(false);
  const [phoneNumber, setPhoneNumber] = useState("");
  const [addressEditMode, setAddressEditMode] = useState(false);
  const [addressInput, setAddressInput] = useState("");
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
    currentPassword: "",
    newPassword: "",
    confirmPassword: "",
  });

  const fileInputRef = useRef(null);
  const canvasRef = useRef(null);

  const resetForm = () => {
    setPasswordChangeMode(false);
    setPhoneEditMode(false);
    setPhoneNumber("");
    setAddressEditMode(false);
    setAddressInput("");
    setSelectedProfileImage(null);
    setPreviewImage(null);
    setShowImageAdjustment(false);
    setAdjustedImage(null);
    setImageScale(1);
    setImageRotation(0);
    setImageOffsetX(0);
    setImageOffsetY(0);
    setPasswordData({ currentPassword: "", newPassword: "", confirmPassword: "" });
  };

  const handleClose = () => {
    resetForm();
    onClose?.();
  };

  useEffect(() => {
    if (open) {
      setPhoneNumber(phoneNo || "");
      setAddressInput(agent_address || "");
    }
  }, [open, phoneNo, agent_address]);

  useEffect(() => {
    if (!open) return;
    if (profileSuccess && profileData) {
      alert("Profile updated successfully!");
      if (profileData.data) {
        const updateData = {};
        if (profileData.data.phone) {
          updateData.phone = profileData.data.phone;
          setPhoneNumber(profileData.data.phone);
        }
        if (profileData.data.agent_address) {
          updateData.agent_address = profileData.data.agent_address;
          setAddressInput(profileData.data.agent_address);
        }
        if (profileData.data.image) {
          updateData.image = profileData.data.image;
        } else if (profileData.data.agent_image) {
          updateData.image = profileData.data.agent_image;
        }
        dispatch(updateProfileData(updateData));
      }
      setSelectedProfileImage(null);
      setPreviewImage(null);
      setAdjustedImage(null);
      setImageScale(1);
      setImageRotation(0);
      setImageOffsetX(0);
      setImageOffsetY(0);
      onClose?.();
      dispatch(resetProfileState());
    }
    if (profileError) {
      alert(`Error updating profile: ${profileError}`);
      dispatch(resetProfileState());
    }
  }, [open, profileSuccess, profileError, profileData, dispatch]);

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

  return (
    <>
                  {/* Profile Modal */}
                  <Dialog
                    open={open}
                    onClose={handleClose}
                    maxWidth="xs"
                    sx={{
                      "& .MuiDialog-container": {
                        alignItems: "flex-start",
                        justifyContent: "flex-end",
                      },
                    }}
                    PaperProps={{
                      sx: {
                        m: 0,
                        mt: "80px",
                        mr: { xs: "12px", sm: "24px" },
                        width: { xs: "calc(100% - 24px)", sm: 420 },
                        maxHeight: "calc(100vh - 100px)",
                        borderRadius: "16px",
                        overflow: "hidden",
                        animation: open ? "profileDrop 0.28s ease-out" : "none",
                        "@keyframes profileDrop": {
                          "0%": {
                            opacity: 0,
                            transform: "translateY(-10px)",
                          },
                          "100%": {
                            opacity: 1,
                            transform: "translateY(0)",
                          },
                        },
                      }
                    }}
                  >
                    <DialogTitle
                      sx={{
                        background: "linear-gradient(135deg, #0f1f4b 0%, #1e3a8a 55%, #243b7a 100%)",
                        color: "white",
                        px: 2,
                        py: 1.25,
                        display: "flex",
                        alignItems: "center",
                        justifyContent: "space-between",
                      }}
                    >
                      <Stack direction="row" alignItems="center" spacing={1}>
                        <AccountCircleOutlined sx={{ fontSize: 20, color: "white" }} />
                        <Typography variant="subtitle1" fontWeight="bold" sx={{ fontSize: "1rem" }}>
                          Agent Profile
                        </Typography>
                      </Stack>
                      <IconButton
                        onClick={handleClose}
                        sx={{ color: "white" }}
                      >
                        <Close />
                      </IconButton>
                    </DialogTitle>

                    <DialogContent sx={{ p: 0 }}>
                      <Box sx={{ px: 2, py: 1.5, "& .MuiInputBase-input": { py: "8.5px" }, "& .MuiInputLabel-root": { fontSize: "0.85rem" } }}>
                        {/* Profile Picture Section */}
                        <Stack alignItems="center" spacing={0.75} sx={{ mb: 1.5 }}>
                          <Box sx={{ position: "relative" }}>
                            <Avatar
                              src={adjustedImage || previewImage || getProfilePictureUrl(profilePicture) || ""}
                              sx={{
                                width: 64,
                                height: 64,
                                border: "2px solid #3554d1",
                                fontSize: "1.4rem",
                                background: "linear-gradient(135deg, #0f1f4b 0%, #1e3a8a 55%, #243b7a 100%)",
                                animation: "pulseGlow 2s ease-in-out infinite alternate",
                                "@keyframes pulseGlow": {
                                  "0%": {
                                    boxShadow: "0 0 20px rgba(53, 84, 209, 0.3)",
                                  },
                                  "100%": {
                                    boxShadow: "0 0 30px rgba(53, 84, 209, 0.6)",
                                  },
                                },
                              }}
                            >
                              {!adjustedImage && !previewImage && !profilePicture && Username?.charAt(0)?.toUpperCase() || "A"}
                            </Avatar>
                            {/* <IconButton
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
                              <CameraAlt sx={{ fontSize: 20, color: "#3554d1" }} />
                            </IconButton> */}

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
                                  background: "linear-gradient(135deg, #0f1f4b 0%, #1e3a8a 55%, #243b7a 100%)",
                                  "&:hover": {
                                    background: "linear-gradient(135deg, #1e3a8a 0%, #3554d1 100%)",
                                    transform: "translateY(-2px)",
                                    boxShadow: "0 4px 12px rgba(53, 84, 209, 0.4)",
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
                        <Stack spacing={1.25}>
                          <TextField
                            size="small"
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
                                  boxShadow: "0 2px 8px rgba(53, 84, 209, 0.1)",
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
                                    boxShadow: "0 0 0 0 rgba(53, 84, 209, 0.4)",
                                  },
                                  "70%": {
                                    boxShadow: "0 0 0 10px rgba(53, 84, 209, 0)",
                                  },
                                  "100%": {
                                    boxShadow: "0 0 0 0 rgba(53, 84, 209, 0)",
                                  },
                                },
                              }}
                            >
                              {passwordChangeMode ? "Cancel" : "Change Password"}
                            </Button>
                          </Stack>

                          {passwordChangeMode && (
                            <Stack spacing={1.25}>
                              <TextField
                                label="Current Password"
                                type={showCurrentPassword ? "text" : "password"}
                                value={passwordData.currentPassword}
                                onChange={(e) => handlePasswordChange('currentPassword', e.target.value)}
                                autoComplete="new-password"
                                name="manual-current-password"
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
                                autoComplete="new-password"
                                name="manual-new-password"
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
                                autoComplete="new-password"
                                name="manual-confirm-password"
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
                                  background: "linear-gradient(135deg, #0f1f4b 0%, #1e3a8a 55%, #243b7a 100%)",
                                  "&:hover": {
                                    background: "linear-gradient(135deg, #1e3a8a 0%, #3554d1 100%)",
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
                        background: "linear-gradient(135deg, #0f1f4b 0%, #1e3a8a 55%, #243b7a 100%)",
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
                                  border: "2px solid #3554d1",
                                  backgroundColor: "rgba(53, 84, 209, 0.1)",
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
                                sx={{ color: "#3554d1" }}
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
                                sx={{ color: "#3554d1" }}
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
                                  sx={{ color: "#3554d1" }}
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
                                  sx={{ color: "#3554d1" }}
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
                          background: "linear-gradient(135deg, #0f1f4b 0%, #1e3a8a 55%, #243b7a 100%)",
                          "&:hover": {
                            background: "linear-gradient(135deg, #1e3a8a 0%, #3554d1 100%)",
                          }
                        }}
                      >
                        Apply Changes
                      </Button>
                    </DialogActions>
                  </Dialog>
    </>
  );
};

export default AgentProfileModal;
