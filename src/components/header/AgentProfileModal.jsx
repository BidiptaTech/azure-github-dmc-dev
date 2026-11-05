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

  const fieldSx = {
    "& .MuiOutlinedInput-root": {
      borderRadius: "10px",
      backgroundColor: "#fff",
      "& fieldset": { borderColor: "var(--pw-line, #e5eaf3)" },
      "&:hover fieldset": { borderColor: "#c9d7ff" },
      "&.Mui-focused fieldset": { borderColor: "var(--pw-blue, #234adc)" },
    },
    "& .MuiInputLabel-root.Mui-focused": {
      color: "var(--pw-blue, #234adc)",
    },
  };

  const iconColor = { color: "var(--pw-blue, #234adc)" };

  return (
    <>
      <Dialog
        open={open}
        onClose={handleClose}
        maxWidth="xs"
        className="partner-profile-dialog"
        sx={{
          "& .MuiDialog-container": {
            alignItems: "flex-start",
            justifyContent: "flex-end",
          },
        }}
        PaperProps={{
          className: "partner-profile-paper",
          elevation: 0,
          sx: {
            m: 0,
            mt: "72px",
            mr: { xs: "12px", sm: "20px" },
            width: { xs: "calc(100% - 24px)", sm: 420 },
            maxHeight: "calc(100vh - 92px)",
            borderRadius: "14px",
            overflow: "hidden",
            border: "1px solid var(--pw-line, #e5eaf3)",
            boxShadow: "0 16px 40px rgba(15, 27, 45, 0.14)",
          },
        }}
      >
        <DialogTitle className="partner-profile-header" sx={{ p: 0 }}>
          <Stack
            direction="row"
            alignItems="center"
            justifyContent="space-between"
            sx={{ px: 2, py: 1.5, borderBottom: "1px solid var(--pw-line, #e5eaf3)" }}
          >
            <Stack direction="row" alignItems="center" spacing={1}>
              <AccountCircleOutlined sx={{ fontSize: 20, color: "var(--pw-blue, #234adc)" }} />
              <Box>
                <Typography sx={{ fontSize: 15, fontWeight: 700, color: "var(--pw-ink, #192944)", lineHeight: 1.2 }}>
                  Your profile
                </Typography>
                <Typography sx={{ fontSize: 12, color: "var(--pw-muted, #748097)" }}>
                  Account details and security
                </Typography>
              </Box>
            </Stack>
            <IconButton onClick={handleClose} size="small" sx={{ color: "var(--pw-muted, #748097)" }}>
              <Close fontSize="small" />
            </IconButton>
          </Stack>
        </DialogTitle>

        <DialogContent sx={{ p: 0 }}>
          <Box
            sx={{
              px: 2,
              py: 2,
              "& .MuiInputBase-input": { py: "10px" },
              "& .MuiInputLabel-root": { fontSize: "0.85rem" },
            }}
          >
            <Stack alignItems="center" spacing={1} sx={{ mb: 2.5 }}>
              <Avatar
                src={adjustedImage || previewImage || getProfilePictureUrl(profilePicture) || ""}
                sx={{
                  width: 72,
                  height: 72,
                  border: "2px solid #dbe4ff",
                  fontSize: "1.5rem",
                  fontWeight: 700,
                  bgcolor: "var(--pw-avatar, #eaf0ff)",
                  color: "var(--pw-blue, #234adc)",
                }}
              >
                {(!adjustedImage && !previewImage && !profilePicture && Username?.charAt(0)?.toUpperCase()) || "A"}
              </Avatar>

              <input
                ref={fileInputRef}
                type="file"
                accept="image/*"
                onChange={handleProfileImageChange}
                style={{ display: "none" }}
              />

              {(selectedProfileImage || adjustedImage) && !showImageAdjustment ? (
                <Stack direction="row" spacing={1}>
                  <Button
                    className="partner-btn partner-btn--primary"
                    size="small"
                    onClick={handleUploadProfileImage}
                    disabled={profileLoading}
                    sx={{
                      textTransform: "none",
                      borderRadius: "8px",
                      bgcolor: "var(--pw-blue, #234adc)",
                      "&:hover": { bgcolor: "#1b3cc0" },
                    }}
                  >
                    {profileLoading ? "Uploading…" : "Save photo"}
                  </Button>
                  <Button
                    size="small"
                    variant="outlined"
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
                      textTransform: "none",
                      borderRadius: "8px",
                      borderColor: "var(--pw-line, #e5eaf3)",
                      color: "var(--pw-ink, #192944)",
                    }}
                  >
                    Cancel
                  </Button>
                </Stack>
              ) : (
                <Button
                  size="small"
                  onClick={handleCameraIconClick}
                  sx={{
                    textTransform: "none",
                    fontWeight: 700,
                    color: "var(--pw-blue, #234adc)",
                    px: 1,
                  }}
                >
                  Change photo
                </Button>
              )}

              <Chip
                label={userRole || "Agent"}
                size="small"
                sx={{
                  fontWeight: 700,
                  bgcolor: "var(--pw-soft, #edf1ff)",
                  color: "var(--pw-blue, #234adc)",
                  borderRadius: "6px",
                }}
              />
            </Stack>

            <Stack spacing={1.5}>
              <TextField
                size="small"
                label="Full name"
                value={Username || "N/A"}
                InputProps={{
                  startAdornment: (
                    <InputAdornment position="start">
                      <Person sx={iconColor} />
                    </InputAdornment>
                  ),
                  readOnly: true,
                }}
                variant="outlined"
                fullWidth
                sx={fieldSx}
              />

              <TextField
                size="small"
                label="Email"
                value={Email || "N/A"}
                InputProps={{
                  startAdornment: (
                    <InputAdornment position="start">
                      <EmailOutlined sx={iconColor} />
                    </InputAdornment>
                  ),
                  readOnly: true,
                }}
                variant="outlined"
                fullWidth
                sx={fieldSx}
              />

              <TextField
                size="small"
                label="Phone number"
                value={phoneEditMode ? phoneNumber : phoneNo || "Not provided"}
                onChange={(e) => setPhoneNumber(e.target.value)}
                InputProps={{
                  startAdornment: (
                    <InputAdornment position="start">
                      <Phone sx={iconColor} />
                    </InputAdornment>
                  ),
                  endAdornment: (
                    <InputAdornment position="end">
                      {phoneEditMode ? (
                        <Stack direction="row" spacing={0.5}>
                          <IconButton
                            size="small"
                            onClick={handleSavePhoneNumber}
                            disabled={profileLoading || !phoneNumber.trim()}
                            sx={{ color: "#20815f" }}
                          >
                            <Check fontSize="small" />
                          </IconButton>
                          <IconButton
                            size="small"
                            onClick={() => {
                              setPhoneEditMode(false);
                              setPhoneNumber(phoneNo || "");
                            }}
                            sx={{ color: "#b42318" }}
                          >
                            <Close fontSize="small" />
                          </IconButton>
                        </Stack>
                      ) : (
                        <IconButton
                          size="small"
                          onClick={() => setPhoneEditMode(true)}
                          sx={iconColor}
                        >
                          <EditOutlined fontSize="small" />
                        </IconButton>
                      )}
                    </InputAdornment>
                  ),
                  readOnly: !phoneEditMode,
                }}
                variant="outlined"
                fullWidth
                placeholder={phoneEditMode ? "Enter phone number" : ""}
                sx={fieldSx}
              />

              <TextField
                size="small"
                label="Address"
                value={addressEditMode ? addressInput : agent_address || "Not provided"}
                onChange={(e) => setAddressInput(e.target.value)}
                InputProps={{
                  startAdornment: (
                    <InputAdornment position="start">
                      <LocationOn sx={iconColor} />
                    </InputAdornment>
                  ),
                  endAdornment: (
                    <InputAdornment position="end">
                      {addressEditMode ? (
                        <Stack direction="row" spacing={0.5}>
                          <IconButton
                            size="small"
                            onClick={handleSaveAddress}
                            disabled={profileLoading || !addressInput.trim()}
                            sx={{ color: "#20815f" }}
                          >
                            <Check fontSize="small" />
                          </IconButton>
                          <IconButton
                            size="small"
                            onClick={() => {
                              setAddressEditMode(false);
                              setAddressInput(agent_address || "");
                            }}
                            sx={{ color: "#b42318" }}
                          >
                            <Close fontSize="small" />
                          </IconButton>
                        </Stack>
                      ) : (
                        <IconButton
                          size="small"
                          onClick={() => setAddressEditMode(true)}
                          sx={iconColor}
                        >
                          <EditOutlined fontSize="small" />
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
                sx={fieldSx}
              />
            </Stack>

            <Box
              sx={{
                mt: 2.5,
                pt: 2,
                borderTop: "1px solid var(--pw-line, #e5eaf3)",
              }}
            >
              <Stack
                direction="row"
                alignItems="center"
                justifyContent="space-between"
                sx={{ mb: passwordChangeMode ? 2 : 0 }}
              >
                <Stack direction="row" alignItems="center" spacing={1}>
                  <Lock sx={{ fontSize: 18, color: "var(--pw-blue, #234adc)" }} />
                  <Typography sx={{ fontSize: 14, fontWeight: 700, color: "var(--pw-ink, #192944)" }}>
                    Security
                  </Typography>
                </Stack>
                <Button
                  size="small"
                  variant={passwordChangeMode ? "outlined" : "contained"}
                  onClick={() => setPasswordChangeMode(!passwordChangeMode)}
                  sx={{
                    textTransform: "none",
                    borderRadius: "8px",
                    fontWeight: 700,
                    ...(passwordChangeMode
                      ? {
                          borderColor: "var(--pw-line, #e5eaf3)",
                          color: "var(--pw-ink, #192944)",
                        }
                      : {
                          bgcolor: "var(--pw-blue, #234adc)",
                          "&:hover": { bgcolor: "#1b3cc0" },
                        }),
                  }}
                >
                  {passwordChangeMode ? "Cancel" : "Change password"}
                </Button>
              </Stack>

              {passwordChangeMode && (
                <Stack spacing={1.5}>
                  <TextField
                    size="small"
                    label="Current password"
                    type={showCurrentPassword ? "text" : "password"}
                    value={passwordData.currentPassword}
                    onChange={(e) => handlePasswordChange("currentPassword", e.target.value)}
                    autoComplete="new-password"
                    name="manual-current-password"
                    InputProps={{
                      startAdornment: (
                        <InputAdornment position="start">
                          <Lock sx={iconColor} />
                        </InputAdornment>
                      ),
                      endAdornment: (
                        <InputAdornment position="end">
                          <IconButton
                            onClick={() => setShowCurrentPassword(!showCurrentPassword)}
                            edge="end"
                            size="small"
                          >
                            {showCurrentPassword ? <VisibilityOff /> : <Visibility />}
                          </IconButton>
                        </InputAdornment>
                      ),
                    }}
                    variant="outlined"
                    fullWidth
                    sx={fieldSx}
                  />

                  <TextField
                    size="small"
                    label="New password"
                    type={showNewPassword ? "text" : "password"}
                    value={passwordData.newPassword}
                    onChange={(e) => handlePasswordChange("newPassword", e.target.value)}
                    autoComplete="new-password"
                    name="manual-new-password"
                    InputProps={{
                      startAdornment: (
                        <InputAdornment position="start">
                          <Lock sx={iconColor} />
                        </InputAdornment>
                      ),
                      endAdornment: (
                        <InputAdornment position="end">
                          <IconButton
                            onClick={() => setShowNewPassword(!showNewPassword)}
                            edge="end"
                            size="small"
                          >
                            {showNewPassword ? <VisibilityOff /> : <Visibility />}
                          </IconButton>
                        </InputAdornment>
                      ),
                    }}
                    variant="outlined"
                    fullWidth
                    sx={fieldSx}
                  />

                  <TextField
                    size="small"
                    label="Confirm new password"
                    type={showConfirmPassword ? "text" : "password"}
                    value={passwordData.confirmPassword}
                    onChange={(e) => handlePasswordChange("confirmPassword", e.target.value)}
                    autoComplete="new-password"
                    name="manual-confirm-password"
                    InputProps={{
                      startAdornment: (
                        <InputAdornment position="start">
                          <Lock sx={iconColor} />
                        </InputAdornment>
                      ),
                      endAdornment: (
                        <InputAdornment position="end">
                          <IconButton
                            onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                            edge="end"
                            size="small"
                          >
                            {showConfirmPassword ? <VisibilityOff /> : <Visibility />}
                          </IconButton>
                        </InputAdornment>
                      ),
                    }}
                    variant="outlined"
                    fullWidth
                    sx={fieldSx}
                  />

                  <Button
                    variant="contained"
                    onClick={handleSavePassword}
                    disabled={
                      profileLoading ||
                      !passwordData.currentPassword ||
                      !passwordData.newPassword ||
                      !passwordData.confirmPassword
                    }
                    sx={{
                      textTransform: "none",
                      borderRadius: "8px",
                      py: 1.1,
                      fontWeight: 700,
                      bgcolor: "var(--pw-blue, #234adc)",
                      "&:hover": { bgcolor: "#1b3cc0" },
                    }}
                  >
                    {profileLoading ? "Saving…" : "Save new password"}
                  </Button>
                </Stack>
              )}
            </Box>
          </Box>
        </DialogContent>
      </Dialog>

      <Dialog
        open={showImageAdjustment}
        onClose={handleImageAdjustmentCancel}
        maxWidth="md"
        fullWidth
        PaperProps={{
          elevation: 0,
          sx: {
            borderRadius: "14px",
            overflow: "hidden",
            border: "1px solid var(--pw-line, #e5eaf3)",
            boxShadow: "0 16px 40px rgba(15, 27, 45, 0.14)",
          },
        }}
      >
        <DialogTitle
          sx={{
            px: 2.5,
            py: 1.75,
            borderBottom: "1px solid var(--pw-line, #e5eaf3)",
            display: "flex",
            alignItems: "center",
            justifyContent: "space-between",
          }}
        >
          <Box>
            <Typography sx={{ fontSize: 16, fontWeight: 700, color: "var(--pw-ink, #192944)" }}>
              Adjust profile picture
            </Typography>
            <Typography sx={{ fontSize: 12, color: "var(--pw-muted, #748097)" }}>
              Scale, rotate and position before saving
            </Typography>
          </Box>
          <IconButton onClick={handleImageAdjustmentCancel} size="small" sx={{ color: "var(--pw-muted, #748097)" }}>
            <Close fontSize="small" />
          </IconButton>
        </DialogTitle>

        <DialogContent sx={{ p: 3 }}>
          <Grid container spacing={3}>
            <Grid item xs={12} md={7}>
              <Paper
                elevation={0}
                sx={{
                  p: 2,
                  display: "flex",
                  flexDirection: "column",
                  alignItems: "center",
                  backgroundColor: "var(--pw-bg, #f5f7fb)",
                  borderRadius: "12px",
                  border: "1px solid var(--pw-line, #e5eaf3)",
                }}
              >
                <Typography sx={{ fontWeight: 700, mb: 1.5, color: "var(--pw-ink, #192944)" }}>
                  Preview
                </Typography>
                <Box
                  sx={{
                    width: 300,
                    height: 300,
                    border: "1px dashed #c9d7ff",
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
                        transition: "transform 0.2s ease",
                      }}
                    />
                  )}

                  <Box
                    sx={{
                      position: "absolute",
                      top: "50%",
                      left: "50%",
                      transform: "translate(-50%, -50%)",
                      width: 200,
                      height: 200,
                      borderRadius: "50%",
                      border: "2px solid var(--pw-blue, #234adc)",
                      backgroundColor: "rgba(35, 74, 220, 0.08)",
                      pointerEvents: "none",
                      zIndex: 1,
                    }}
                  />

                  <Typography
                    variant="caption"
                    sx={{
                      position: "absolute",
                      bottom: 8,
                      left: "50%",
                      transform: "translateX(-50%)",
                      backgroundColor: "rgba(25, 41, 68, 0.8)",
                      color: "white",
                      px: 1,
                      py: 0.5,
                      borderRadius: 1,
                      fontSize: "0.7rem",
                      zIndex: 2,
                      whiteSpace: "nowrap",
                    }}
                  >
                    Circle shows crop area
                  </Typography>
                </Box>
              </Paper>
            </Grid>

            <Grid item xs={12} md={5}>
              <Stack spacing={3}>
                <Box>
                  <Stack direction="row" alignItems="center" spacing={1.5} sx={{ mb: 1.5 }}>
                    <ZoomOut fontSize="small" />
                    <Typography sx={{ fontWeight: 700 }}>Scale</Typography>
                    <ZoomIn fontSize="small" />
                  </Stack>
                  <Slider
                    value={imageScale}
                    onChange={(_, value) => setImageScale(value)}
                    min={0.5}
                    max={2}
                    step={0.1}
                    valueLabelDisplay="auto"
                    sx={{ color: "var(--pw-blue, #234adc)" }}
                  />
                </Box>

                <Box>
                  <Stack direction="row" alignItems="center" spacing={1.5} sx={{ mb: 1.5 }}>
                    <RotateLeft fontSize="small" />
                    <Typography sx={{ fontWeight: 700 }}>Rotation</Typography>
                    <RotateRight fontSize="small" />
                  </Stack>
                  <Slider
                    value={imageRotation}
                    onChange={(_, value) => setImageRotation(value)}
                    min={-180}
                    max={180}
                    step={15}
                    valueLabelDisplay="auto"
                    sx={{ color: "var(--pw-blue, #234adc)" }}
                  />
                </Box>

                <Box>
                  <Typography sx={{ fontWeight: 700, mb: 1.5 }}>Position</Typography>
                  <Box sx={{ mb: 2 }}>
                    <Typography variant="body2" sx={{ mb: 1, color: "var(--pw-muted, #748097)" }}>
                      Horizontal
                    </Typography>
                    <Slider
                      value={imageOffsetX}
                      onChange={(_, value) => setImageOffsetX(value)}
                      min={-100}
                      max={100}
                      step={5}
                      valueLabelDisplay="auto"
                      sx={{ color: "var(--pw-blue, #234adc)" }}
                    />
                  </Box>
                  <Box>
                    <Typography variant="body2" sx={{ mb: 1, color: "var(--pw-muted, #748097)" }}>
                      Vertical
                    </Typography>
                    <Slider
                      value={imageOffsetY}
                      onChange={(_, value) => setImageOffsetY(value)}
                      min={-100}
                      max={100}
                      step={5}
                      valueLabelDisplay="auto"
                      sx={{ color: "var(--pw-blue, #234adc)" }}
                    />
                  </Box>
                </Box>

                <Stack direction="row" spacing={1.5} justifyContent="center">
                  <IconButton
                    onClick={() => setImageRotation((prev) => prev - 90)}
                    sx={{
                      backgroundColor: "var(--pw-bg, #f5f7fb)",
                      border: "1px solid var(--pw-line, #e5eaf3)",
                      "&:hover": { backgroundColor: "#eef3ff" },
                    }}
                  >
                    <RotateLeft />
                  </IconButton>
                  <IconButton
                    onClick={() => setImageRotation((prev) => prev + 90)}
                    sx={{
                      backgroundColor: "var(--pw-bg, #f5f7fb)",
                      border: "1px solid var(--pw-line, #e5eaf3)",
                      "&:hover": { backgroundColor: "#eef3ff" },
                    }}
                  >
                    <RotateRight />
                  </IconButton>
                </Stack>

                <Button
                  variant="outlined"
                  onClick={() => {
                    setImageScale(1);
                    setImageRotation(0);
                    setImageOffsetX(0);
                    setImageOffsetY(0);
                  }}
                  sx={{
                    textTransform: "none",
                    borderRadius: "8px",
                    borderColor: "var(--pw-line, #e5eaf3)",
                    color: "var(--pw-ink, #192944)",
                    fontWeight: 700,
                  }}
                >
                  Reset
                </Button>
              </Stack>
            </Grid>
          </Grid>

          <canvas ref={canvasRef} style={{ display: "none" }} />
        </DialogContent>

        <DialogActions
          sx={{
            px: 3,
            py: 2,
            borderTop: "1px solid var(--pw-line, #e5eaf3)",
            gap: 1,
          }}
        >
          <Button
            variant="outlined"
            onClick={handleImageAdjustmentCancel}
            sx={{
              textTransform: "none",
              borderRadius: "8px",
              borderColor: "var(--pw-line, #e5eaf3)",
              color: "var(--pw-ink, #192944)",
              fontWeight: 700,
            }}
          >
            Cancel
          </Button>
          <Button
            variant="contained"
            onClick={handleImageAdjustmentSave}
            sx={{
              textTransform: "none",
              borderRadius: "8px",
              fontWeight: 700,
              bgcolor: "var(--pw-blue, #234adc)",
              "&:hover": { bgcolor: "#1b3cc0" },
            }}
          >
            Apply changes
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export default AgentProfileModal;
