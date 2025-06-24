import React, { useState, useEffect } from 'react';
import { 
  Dialog, 
  DialogTitle, 
  DialogContent, 
  DialogActions,
  TextField, 
  Grid, 
  Box, 
  Typography, 
  Button,
  InputAdornment,
  IconButton,
  Avatar,
  Divider,
  Paper,
  Chip,
  CircularProgress,
  Alert,
  Snackbar,
  Select,
  MenuItem,
  FormControl,
  InputLabel
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import PersonIcon from '@mui/icons-material/Person';
import EmailIcon from '@mui/icons-material/Email';
import PhoneIcon from '@mui/icons-material/Phone';
import HomeIcon from '@mui/icons-material/Home';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import MarkunreadMailboxIcon from '@mui/icons-material/MarkunreadMailbox';
import NoteIcon from '@mui/icons-material/Note';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import LockIcon from '@mui/icons-material/Lock';
import FlightTakeoffIcon from '@mui/icons-material/FlightTakeoff';
import { useDispatch, useSelector } from 'react-redux';
import { bookPackage, resetBookingStatus } from '../../../slice/tour-packages/prePackagesSlice';

// Add country codes array
const countryCodes = [
  { code: "US", name: "United States", dialCode: "+1" },
  { code: "GB", name: "United Kingdom", dialCode: "+44" },
  { code: "IN", name: "India", dialCode: "+91" },
  { code: "AU", name: "Australia", dialCode: "+61" },
  { code: "SG", name: "Singapore", dialCode: "+65" },
  { code: "AE", name: "UAE", dialCode: "+971" },
  { code: "MY", name: "Malaysia", dialCode: "+60" },
  { code: "TH", name: "Thailand", dialCode: "+66" },
  // Add more country codes as needed
];

const UserInfo = ({ open, onClose, onSubmit, bookingData }) => {
  const dispatch = useDispatch();
  const { bookingLoading, bookingSuccess, bookingError } = useSelector(state => state.prePackages);
  
  // Get country code from Redux state (adjust the path based on your Redux structure)
  const countryCodeFromRedux = useSelector((state) => state.auth?.countryCode);
  const dialMaxLength = useSelector((state) => state.auth?.dialMaxLength) || 15;
  const dialMinLength = useSelector((state) => state.auth?.dialMinLength) || 8;
  
  const [snackbar, setSnackbar] = useState({
    open: false,
    message: '',
    severity: 'success'
  });
  
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    phone: '',
    countryCode: '+65',
    address1: '',
    address2: '',
    state: '',
    zip: '',
    specialRequests: ''
  });

  // Set the initial country code based on Redux state
  useEffect(() => {
    const selectedCountry = countryCodes.find(
      (country) => country.code === countryCodeFromRedux
    );
    
    if (selectedCountry) {
      setFormData((prevForm) => ({
        ...prevForm,
        countryCode: selectedCountry.dialCode,
      }));
    }
  }, [countryCodeFromRedux]);

  const [errors, setErrors] = useState({});
  const [touched, setTouched] = useState({});
  
  // Reset booking status when component unmounts
  useEffect(() => {
    return () => {
      dispatch(resetBookingStatus());
    };
  }, [dispatch]);
  
  // Handle booking success
  useEffect(() => {
    if (bookingSuccess) {
      setSnackbar({
        open: true,
        message: 'Booking successful!',
        severity: 'success'
      });
      
      // Close the modal after successful booking with a slight delay
      setTimeout(() => {
        onSubmit({ ...bookingData, user_info: formData });
        onClose();
      }, 1500);
    }
  }, [bookingSuccess, bookingData, formData, onSubmit, onClose]);
  
  // Handle booking error
  useEffect(() => {
    if (bookingError) {
      setSnackbar({
        open: true,
        message: bookingError || 'Failed to book package. Please try again.',
        severity: 'error'
      });
    }
  }, [bookingError]);

  const validateField = (name, value) => {
    let error = '';
    switch (name) {
      case 'fullName':
        error = !value ? 'Full name is required' : '';
        break;
      case 'email':
        error = !value ? 'Email is required' : 
                !/\S+@\S+\.\S+/.test(value) ? 'Email is invalid' : '';
        break;
      case 'phone':
        const phoneRegex = new RegExp(`^\\d{${dialMinLength},${dialMaxLength}}$`);
        if (!value) {
          error = 'Phone number is required';
        } else if (!phoneRegex.test(value)) {
          error = `Enter a valid phone number (${dialMinLength}-${dialMaxLength} digits)`;
        }
        break;
      case 'address1':
        error = !value ? 'Address is required' : '';
        break;
      default:
        break;
    }
    return error;
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    
    if (touched[name]) {
      setErrors(prev => ({ 
        ...prev, 
        [name]: validateField(name, value) 
      }));
    }
  };

  const handleBlur = (e) => {
    const { name, value } = e.target;
    setTouched(prev => ({ ...prev, [name]: true }));
    setErrors(prev => ({ 
      ...prev, 
      [name]: validateField(name, value) 
    }));
  };

  const validateForm = () => {
    const newErrors = {};
    const newTouched = {};
    
    // Validate required fields
    Object.keys(formData).forEach(key => {
      newTouched[key] = true;
      const error = validateField(key, formData[key]);
      if (error) {
        newErrors[key] = error;
      }
    });
    
    setErrors(newErrors);
    setTouched(newTouched);
    
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = () => {
    if (validateForm()) {
      // Create the final booking data with user info
      const finalBookingData = {
        ...bookingData,
        user_info: {
          ...formData
        }
      };
      
      // Dispatch the booking action
      dispatch(bookPackage(finalBookingData));
      
      // Log the complete booking data to console
      console.log('Complete booking data:', JSON.stringify(finalBookingData, null, 2));
    }
  };
  
  const handleCloseSnackbar = () => {
    setSnackbar(prev => ({ ...prev, open: false }));
  };

  return (
    <>
      <Dialog 
        open={open} 
        onClose={!bookingLoading ? onClose : undefined} 
        maxWidth="md" 
        fullWidth
        PaperProps={{
          sx: {
            borderRadius: '16px',
            overflow: 'hidden'
          }
        }}
      >
        <DialogTitle 
          sx={{
            background: 'linear-gradient(45deg, #3f51b5 30%, #2196f3 90%)',
            color: 'white',
            p: 3
          }}
        >
          <Box display="flex" justifyContent="space-between" alignItems="center">
            <Box display="flex" alignItems="center" gap={2}>
              <Avatar sx={{ bgcolor: 'white' }}>
                <FlightTakeoffIcon color="primary" />
              </Avatar>
              <Box>
                <Typography variant="h5" fontWeight="bold">Complete Your Booking</Typography>
                <Typography variant="subtitle2">Please fill in your details to proceed</Typography>
              </Box>
            </Box>
            <IconButton 
              aria-label="close" 
              onClick={onClose} 
              disabled={bookingLoading}
              sx={{ color: 'white' }}
            >
              <CloseIcon />
            </IconButton>
          </Box>
        </DialogTitle>
        
        <DialogContent dividers sx={{ p: 0 }}>
          <Box 
            sx={{ 
              p: 3,
              backgroundColor: '#f8f9fa',
              borderBottom: '1px solid #eee'
            }}
          >
            {bookingError && (
              <Alert severity="error" sx={{ mb: 2 }}>
                {bookingError}
              </Alert>
            )}
            
            <Box display="flex" alignItems="center" mb={2}>
              <Chip 
                icon={<CheckCircleIcon />} 
                label="Secure Booking" 
                color="primary" 
                size="small"
                sx={{ mr: 1 }}
              />
              <Typography variant="caption" color="text.secondary">
                <LockIcon sx={{ fontSize: 12, mr: 0.5, verticalAlign: 'middle' }} />
                Your information is secure
              </Typography>
            </Box>

            <Paper elevation={0} sx={{ p: 3, borderRadius: '12px' }}>
              <Typography variant="h6" fontWeight="bold" sx={{ mb: 3, color: '#3f51b5' }}>
                <PersonIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
                Personal Information
              </Typography>

              <Grid container spacing={3}>
                <Grid item xs={12}>
                  <TextField
                    fullWidth
                    label="Full Name"
                    name="fullName"
                    value={formData.fullName}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    error={touched.fullName && Boolean(errors.fullName)}
                    helperText={touched.fullName && errors.fullName}
                    required
                    variant="outlined"
                    InputProps={{
                      startAdornment: (
                        <InputAdornment position="start">
                          <PersonIcon color="primary" />
                        </InputAdornment>
                      ),
                    }}
                  />
                </Grid>

                <Grid item xs={12} md={6}>
                  <TextField
                    fullWidth
                    label="Email Address"
                    name="email"
                    type="email"
                    value={formData.email}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    error={touched.email && Boolean(errors.email)}
                    helperText={touched.email && errors.email}
                    required
                    variant="outlined"
                    InputProps={{
                      startAdornment: (
                        <InputAdornment position="start">
                          <EmailIcon color="primary" />
                        </InputAdornment>
                      ),
                    }}
                  />
                </Grid>

                <Grid item xs={12} md={6}>
                  <Box display="flex" alignItems="flex-start" gap={1}>
                    <FormControl variant="outlined" sx={{ minWidth: 120 }}>
                      <InputLabel id="country-code-label">Code</InputLabel>
                      <Select
                        labelId="country-code-label"
                        id="country-code-select"
                        value={formData.countryCode}
                        onChange={(e) => setFormData(prev => ({ ...prev, countryCode: e.target.value }))}
                        label="Code"
                        size="medium"
                      >
                        {countryCodes.map((country) => (
                          <MenuItem key={country.code} value={country.dialCode}>
                            {country.dialCode} ({country.name})
                          </MenuItem>
                        ))}
                      </Select>
                    </FormControl>
                    <TextField
                      fullWidth
                      label="Phone Number"
                      name="phone"
                      value={formData.phone}
                      onChange={handleChange}
                      onBlur={handleBlur}
                      error={touched.phone && Boolean(errors.phone)}
                      helperText={touched.phone && errors.phone}
                      required
                      variant="outlined"
                      InputProps={{
                        startAdornment: (
                          <InputAdornment position="start">
                            <PhoneIcon color="primary" />
                          </InputAdornment>
                        ),
                      }}
                    />
                  </Box>
                </Grid>
              </Grid>
            </Paper>

            <Paper elevation={0} sx={{ p: 3, borderRadius: '12px', mt: 3 }}>
              <Typography variant="h6" fontWeight="bold" sx={{ mb: 3, color: '#3f51b5' }}>
                <HomeIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
                Address Details
              </Typography>

              <Grid container spacing={3}>
                <Grid item xs={12}>
                  <TextField
                    fullWidth
                    label="Address Line 1"
                    name="address1"
                    value={formData.address1}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    error={touched.address1 && Boolean(errors.address1)}
                    helperText={touched.address1 && errors.address1}
                    required
                    variant="outlined"
                    InputProps={{
                      startAdornment: (
                        <InputAdornment position="start">
                          <HomeIcon color="primary" />
                        </InputAdornment>
                      ),
                    }}
                  />
                </Grid>

                <Grid item xs={12}>
                  <TextField
                    fullWidth
                    label="Address Line 2"
                    name="address2"
                    value={formData.address2}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    variant="outlined"
                    InputProps={{
                      startAdornment: (
                        <InputAdornment position="start">
                          <HomeIcon color="primary" />
                        </InputAdornment>
                      ),
                    }}
                  />
                </Grid>

                <Grid item xs={12} md={6}>
                  <TextField
                    fullWidth
                    label="State/Province/Region"
                    name="state"
                    value={formData.state}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    error={touched.state && Boolean(errors.state)}
                    helperText={touched.state && errors.state}
                    variant="outlined"
                    InputProps={{
                      startAdornment: (
                        <InputAdornment position="start">
                          <LocationOnIcon color="primary" />
                        </InputAdornment>
                      ),
                    }}
                  />
                </Grid>

                <Grid item xs={12} md={6}>
                  <TextField
                    fullWidth
                    label="ZIP Code/Postal Code"
                    name="zip"
                    value={formData.zip}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    error={touched.zip && Boolean(errors.zip)}
                    helperText={touched.zip && errors.zip}
                    variant="outlined"
                    InputProps={{
                      startAdornment: (
                        <InputAdornment position="start">
                          <MarkunreadMailboxIcon color="primary" />
                        </InputAdornment>
                      ),
                    }}
                  />
                </Grid>
              </Grid>
            </Paper>

            <Paper elevation={0} sx={{ p: 3, borderRadius: '12px', mt: 3 }}>
              <Typography variant="h6" fontWeight="bold" sx={{ mb: 3, color: '#3f51b5' }}>
                <NoteIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
                Additional Information
              </Typography>

              <Grid container spacing={3}>
                <Grid item xs={12}>
                  <TextField
                    fullWidth
                    label="Special Requests"
                    name="specialRequests"
                    value={formData.specialRequests}
                    onChange={handleChange}
                    multiline
                    rows={4}
                    variant="outlined"
                    placeholder="Any special requirements or requests for your trip"
                    InputProps={{
                      startAdornment: (
                        <InputAdornment position="start" sx={{ alignSelf: 'flex-start', mt: 1.5 }}>
                          <NoteIcon color="primary" />
                        </InputAdornment>
                      ),
                    }}
                  />
                </Grid>
              </Grid>
            </Paper>
          </Box>
        </DialogContent>
        
        <DialogActions sx={{ p: 3, bgcolor: '#f8f9fa', justifyContent: 'space-between' }}>
          <Button 
            onClick={onClose} 
            variant="outlined"
            color="primary"
            disabled={bookingLoading}
            startIcon={<CloseIcon />}
            sx={{ px: 3 }}
          >
            Cancel
          </Button>
          <Button 
            onClick={handleSubmit} 
            variant="contained" 
            color="primary"
            disabled={bookingLoading}
            sx={{ 
              px: 4, 
              py: 1.2,
              background: 'linear-gradient(45deg, #3f51b5 30%, #2196f3 90%)',
              boxShadow: '0 3px 5px 2px rgba(33, 150, 243, .3)',
            }}
            endIcon={bookingLoading ? <CircularProgress size={20} color="inherit" /> : <CheckCircleIcon />}
          >
            {bookingLoading ? 'Processing...' : 'Complete Booking'}
          </Button>
        </DialogActions>
      </Dialog>
      
      <Snackbar
        open={snackbar.open}
        autoHideDuration={6000}
        onClose={handleCloseSnackbar}
        anchorOrigin={{ vertical: 'top', horizontal: 'center' }}
      >
        <Alert onClose={handleCloseSnackbar} severity={snackbar.severity} sx={{ width: '100%' }}>
          {snackbar.message}
        </Alert>
      </Snackbar>
    </>
  );
};

export default UserInfo;
