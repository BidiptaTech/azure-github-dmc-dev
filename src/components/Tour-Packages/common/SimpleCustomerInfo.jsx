import React, { useState, useEffect, useRef } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { setAllServices } from '@/slice/tour-packages/tourPackageSlice';
import {
  TextField,
  Paper,
  Typography,
  Box,
  Button,
  Grid,
  InputAdornment,
} from '@mui/material';

const SimpleCustomerInfo = () => {
  const dispatch = useDispatch();
  const allServices = useSelector(state => state.tourPackages?.AllServices || []);
  console.log("allServicesssd", allServices);
  
  // Form state with the same mandatory fields as the original component
  const [form, setForm] = useState({
    fullName: '',
    email: '',
    phone: '',
    address1: '',
    address2: '',
    state: '',
    zip: '',
    specialRequests: '',
    countryCode: '+1', // Default country code
  });

  // Track validation state
  const [errors, setErrors] = useState({});
  const [touched, setTouched] = useState({});
  const [formValid, setFormValid] = useState(false);
  
  // Use refs to track the previous values and prevent infinite loops
  const prevFormRef = useRef({});
  const formUpdatedRef = useRef(false);
  
  // Function to validate a single field
  const validateField = (field, value) => {
    let error = '';

    switch (field) {
      case 'fullName':
        if (!value.trim()) error = 'Full Name is required';
        break;
      case 'email':
        if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value))
          error = 'Enter a valid email';
        break;
      case 'phone':
        if (!/^\d{10,15}$/.test(value))
          error = 'Enter a valid phone number (10-15 digits)';
        break;
      case 'address1':
        if (!value.trim()) error = 'Address is required';
        break;
      case 'state':
        if (!value.trim()) error = 'State is required';
        break;
      case 'zip':
        if (!/^\d{5,8}$/.test(value)) error = 'Enter a valid ZIP code';
        break;
      default:
        break;
    }

    return error;
  };

  // Function to validate the entire form
  const validateForm = () => {
    const requiredFields = [
      'fullName',
      'email',
      'phone',
      'address1',
      'state',
      'zip',
    ];
    let newErrors = {};

    requiredFields.forEach((field) => {
      const error = validateField(field, form[field]);
      if (error) newErrors[field] = error;
    });

    setErrors(newErrors);
    
    // Form is valid if there are no errors
    return Object.keys(newErrors).length === 0;
  };

  // Function to handle input change
  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prevForm) => ({
      ...prevForm,
      [name]: value,
    }));
    
    // Mark that the form has been updated
    formUpdatedRef.current = true;

    if (touched[name]) {
      setErrors((prevErrors) => ({
        ...prevErrors,
        [name]: validateField(name, value),
      }));
    }
  };

  // Function to handle blur event (field loses focus)
  const handleBlur = (e) => {
    const { name, value } = e.target;
    setTouched((prevTouched) => ({ ...prevTouched, [name]: true }));

    setErrors((prevErrors) => ({
      ...prevErrors,
      [name]: validateField(name, value),
    }));
  };
  
  // Check if form has changed since last update
  const hasFormChanged = () => {
    return JSON.stringify(form) !== JSON.stringify(prevFormRef.current);
  };

  // Effect to update services when form changes
  useEffect(() => {
    // Only proceed if the form has actually been updated by user input
    if (!formUpdatedRef.current) {
      return;
    }
    
    const isValid = validateForm();
    
    // Only proceed if form is valid, has been touched, and has changed since last update
    if (isValid && Object.values(touched).some(t => t) && hasFormChanged()) {
      // Get customer info 
      const customerInfo = {
        ...form
      };
      
      // Remember the current form state to avoid redundant updates
      prevFormRef.current = {...form};
      
      // Create a new array with customer info embedded in each service type
      if (allServices.length > 0) {
        const updatedServices = allServices.map(service => {
          if (Array.isArray(service.data)) {
            // If service has a data array, update each item in the data array
            return {
              ...service,
              data: service.data.map(item => ({
                ...item,
                ...customerInfo
              }))
            };
          }
          return service;
        });
        
        // Reset the form updated flag before dispatching
        formUpdatedRef.current = false;
        
        // Dispatch updated services to Redux
        dispatch(setAllServices(updatedServices));
      }
      
      setFormValid(true);
    } else {
      setFormValid(false);
    }
  }, [form, touched]);

  return (
    <Paper elevation={3} sx={{ p: 3, mb: 4, borderRadius: 2 }}>
      <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
        <Typography variant="h5">Customer Information</Typography>
      </Box>
      
      <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
        Please provide your contact information below. Fields marked with * are required.
      </Typography>
      
      <Grid container spacing={2}>
        <Grid item xs={12}>
          <TextField
            label="Full Name *"
            name="fullName"
            fullWidth
            variant="outlined"
            value={form.fullName}
            onChange={handleChange}
            onBlur={handleBlur}
            error={!!errors.fullName && touched.fullName}
            helperText={touched.fullName && errors.fullName}
          />
        </Grid>

        <Grid item xs={12} md={6}>
          <TextField
            label="Email *"
            name="email"
            fullWidth
            variant="outlined"
            value={form.email}
            onChange={handleChange}
            onBlur={handleBlur}
            error={!!errors.email && touched.email}
            helperText={touched.email && errors.email}
          />
        </Grid>

        <Grid item xs={12} md={6}>
          <TextField
            label="Phone Number *"
            name="phone"
            fullWidth
            variant="outlined"
            value={form.phone}
            onChange={handleChange}
            onBlur={handleBlur}
            error={!!errors.phone && touched.phone}
            helperText={touched.phone && errors.phone}
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <span>{form.countryCode}</span>
                </InputAdornment>
              ),
            }}
          />
        </Grid>

        <Grid item xs={12}>
          <TextField
            label="Address Line 1 *"
            name="address1"
            fullWidth
            variant="outlined"
            value={form.address1}
            onChange={handleChange}
            onBlur={handleBlur}
            error={!!errors.address1 && touched.address1}
            helperText={touched.address1 && errors.address1}
          />
        </Grid>

        <Grid item xs={12}>
          <TextField
            label="Address Line 2"
            name="address2"
            fullWidth
            variant="outlined"
            value={form.address2}
            onChange={handleChange}
          />
        </Grid>

        <Grid item xs={12} md={6}>
          <TextField
            label="State/Province/Region *"
            name="state"
            fullWidth
            variant="outlined"
            value={form.state}
            onChange={handleChange}
            onBlur={handleBlur}
            error={!!errors.state && touched.state}
            helperText={touched.state && errors.state}
          />
        </Grid>

        <Grid item xs={12} md={6}>
          <TextField
            label="ZIP Code/Postal Code *"
            name="zip"
            fullWidth
            variant="outlined"
            value={form.zip}
            onChange={handleChange}
            onBlur={handleBlur}
            error={!!errors.zip && touched.zip}
            helperText={touched.zip && errors.zip}
          />
        </Grid>

        <Grid item xs={12}>
          <TextField
            label="Special Requests"
            name="specialRequests"
            fullWidth
            multiline
            rows={4}
            variant="outlined"
            value={form.specialRequests}
            onChange={handleChange}
          />
        </Grid>
      </Grid>
      
      <Box sx={{ mt: 2, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <Typography 
          variant="body2" 
          color={formValid ? "success.main" : "text.secondary"}
        >
          {formValid ? "✓ Information saved" : "Fill in all required fields to save automatically"}
        </Typography>
      </Box>
    </Paper>
  );
};

export default SimpleCustomerInfo; 