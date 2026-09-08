import React, { useState, useEffect, useRef } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { setAllServices, setCustomerInfoValid } from '@/slice/tour-packages/tourPackageSlice';
import {
  TextField,
  Paper,
  Typography,
  Box,
  Button,
  Grid,
  InputAdornment,
  FormControl,
  Select,
  MenuItem,
  Chip,
  Alert,
  CircularProgress,
} from '@mui/material';
import { toast } from 'react-toastify';
import {
  MANUAL_COUNTRY_VALUE,
  fetchCountryDialCodes,
  sanitizeDialCode,
} from '@/utils/countryCodes';

const SimpleCustomerInfo = () => {
  const dispatch = useDispatch();
  const allServices = useSelector(state => state.tourPackages?.AllServices || []);
  console.log("allServicesssd", allServices);
  const customerInfo = useSelector(state => state.tourPackages?.packageData?.customer_info || {});
  console.log("customerInfo", customerInfo);
  
  // Check if customerInfo has data (form should be read-only)
  const hasCustomerInfo = customerInfo && Object.keys(customerInfo).length > 0 && 
    (customerInfo.fullName || customerInfo.email || customerInfo.phone);
  
  const searchLocation = useSelector((state) => state.bookings.searchLocation);

  // Countries from /get-country
  const [countries, setCountries] = useState([]);
  const [countriesLoading, setCountriesLoading] = useState(true);
  const [selectedCountry, setSelectedCountry] = useState(null);
  const [dialMinLength, setDialMinLength] = useState(8);
  const [dialMaxLength, setDialMaxLength] = useState(15);
  const [isManualCountryCode, setIsManualCountryCode] = useState(false);
  const countryManuallyOverriddenRef = useRef(false);
  const hasInitializedCountryRef = useRef(false);
  
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
  
  // Load countries from /get-country
  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      setCountriesLoading(true);
      try {
        const list = await fetchCountryDialCodes();
        if (!cancelled) setCountries(list);
      } catch (error) {
        console.error("Failed to fetch /get-country:", error);
        if (!cancelled) {
          setCountries([]);
          toast.error("Failed to load country codes");
        }
      } finally {
        if (!cancelled) setCountriesLoading(false);
      }
    };
    load();
    return () => {
      cancelled = true;
    };
  }, []);

  // Auto-populate form when customerInfo is available
  useEffect(() => {
    if (hasCustomerInfo) {
      console.log("Auto-populating form with customerInfo:", customerInfo);
      setForm(prevForm => ({
        ...prevForm,
        fullName: customerInfo.fullName || '',
        email: customerInfo.email || '',
        phone: customerInfo.phone || '',
        address1: customerInfo.address1 || '',
        address2: customerInfo.address2 || '',
        state: customerInfo.state || '',
        zip: customerInfo.zip || '',
        specialRequests: customerInfo.specialRequests || '',
        countryCode: customerInfo.countryCode || prevForm.countryCode,
      }));
      
      setFormValid(true);
      dispatch(setCustomerInfoValid(true));
      
      if (customerInfo.countryCode && countries.length) {
        const country = countries.find(c => c.country_code === customerInfo.countryCode);
        if (country) {
          setSelectedCountry(country);
          setIsManualCountryCode(false);
          setDialMinLength(country.contact_min_length || 8);
          setDialMaxLength(country.contact_max_length || 15);
        } else {
          setIsManualCountryCode(true);
          setSelectedCountry(null);
        }
      }
    }
  }, [customerInfo, hasCustomerInfo, countries, dispatch]);

  // Initialize selected country from /get-country list
  useEffect(() => {
    if (countryManuallyOverriddenRef.current || hasCustomerInfo) return;
    if (!countries.length || hasInitializedCountryRef.current) return;

    let defaultCountry = null;
    if (searchLocation?.[0]) {
      defaultCountry = countries.find(
        (c) =>
          String(c.code).toLowerCase() ===
          String(searchLocation[0]).toLowerCase()
      );
    }
    if (!defaultCountry) defaultCountry = countries[0];

    hasInitializedCountryRef.current = true;
    setSelectedCountry(defaultCountry);
    setIsManualCountryCode(false);
    setDialMinLength(defaultCountry.contact_min_length || 8);
    setDialMaxLength(defaultCountry.contact_max_length || 15);
    setForm((prevForm) => ({
      ...prevForm,
      countryCode: prevForm.countryCode || defaultCountry.country_code,
    }));
    formUpdatedRef.current = true;
  }, [countries, searchLocation, hasCustomerInfo]);

  // Handler for country selection (dropdown or Other)
  const handleCountryChange = (event) => {
    if (hasCustomerInfo) return;
    
    const selectedCountryCode = event.target.value;

    if (selectedCountryCode === MANUAL_COUNTRY_VALUE) {
      countryManuallyOverriddenRef.current = true;
      setIsManualCountryCode(true);
      setSelectedCountry(null);
      setDialMinLength(8);
      setDialMaxLength(15);
      setForm((prevForm) => ({
        ...prevForm,
        countryCode: prevForm.countryCode?.startsWith("+")
          ? prevForm.countryCode
          : "+",
      }));
      formUpdatedRef.current = true;
      return;
    }

    const country = countries.find(c => c.code === selectedCountryCode);
    
    if (country) {
      countryManuallyOverriddenRef.current = false;
      setIsManualCountryCode(false);
      setSelectedCountry(country);
      setDialMinLength(country.contact_min_length || 8);
      setDialMaxLength(country.contact_max_length || 15);
      setForm((prevForm) => ({
        ...prevForm,
        countryCode: country.country_code,
      }));
      formUpdatedRef.current = true;
      
      if (touched.phone && form.phone) {
        const phoneError = validateField('phone', form.phone);
        setErrors((prevErrors) => ({
          ...prevErrors,
          phone: phoneError,
        }));
      }
    }
  };

  const handleManualCountryCodeChange = (e) => {
    if (hasCustomerInfo) return;
    const value = sanitizeDialCode(e.target.value);
    countryManuallyOverriddenRef.current = true;
    setIsManualCountryCode(true);
    setForm((prev) => ({ ...prev, countryCode: value }));
    formUpdatedRef.current = true;
  };
  
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
        // Use dynamic dial lengths from selected country (same as CustomerInfo.jsx)
        const phoneRegex = new RegExp(`^\\d{${dialMinLength},${dialMaxLength}}$`);
        if (!value) {
          error = "Phone number is required";
        } else if (!phoneRegex.test(value)) {
          error = `Please enter a valid phone number (${dialMinLength}-${dialMaxLength} digits)`;
        }
        break;
      case 'address1':
        if (!value.trim()) error = 'Address is required';
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
    if (hasCustomerInfo) return; // Prevent changes when form is read-only
    
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
    if (hasCustomerInfo) return; // Prevent changes when form is read-only
    
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
    
    // Dispatch customer info validity to Redux
    dispatch(setCustomerInfoValid(isValid && Object.values(touched).some(t => t)));
    
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
  }, [form, touched, dispatch]);

  // Cleanup effect to clear customer info validation when component unmounts
  useEffect(() => {
    return () => {
      dispatch(setCustomerInfoValid(false));
    };
  }, [dispatch]);

  return (
    <Paper elevation={2} sx={{ p: 2, mb: 2, borderRadius: 1.5 }}>
      <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 1.5 }}>
        <Typography variant="h6" sx={{ fontSize: '1.1rem', fontWeight: 600 }}>Customer Information</Typography>
        {hasCustomerInfo && (
          <Chip 
            label="Auto-filled" 
            color="success" 
            variant="outlined" 
            size="small"
            sx={{ fontSize: '0.65rem', height: '20px' }}
          />
        )}
      </Box>
      
      {hasCustomerInfo ? (
        <Alert severity="info" sx={{ mb: 2, py: 0.5, '& .MuiAlert-message': { fontSize: '0.75rem' } }}>
          Your information has been automatically filled from your Tour Package Booking.
        </Alert>
      ) : (
        <Typography variant="body2" color="text.secondary" sx={{ mb: 2, fontSize: '0.75rem' }}>
          Please provide your contact information below. Fields marked with * are required.
        </Typography>
      )}
      
      <Grid container spacing={1.5}>
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
            disabled={hasCustomerInfo}
            size="small"
            InputProps={{
              readOnly: hasCustomerInfo,
              sx: { fontSize: '0.8rem' }
            }}
            InputLabelProps={{
              sx: { fontSize: '0.8rem' }
            }}
            FormHelperTextProps={{
              sx: { fontSize: '0.65rem', mx: 0 }
            }}
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
            disabled={hasCustomerInfo}
            size="small"
            InputProps={{
              readOnly: hasCustomerInfo,
              sx: { fontSize: '0.8rem' }
            }}
            InputLabelProps={{
              sx: { fontSize: '0.8rem' }
            }}
            FormHelperTextProps={{
              sx: { fontSize: '0.65rem', mx: 0 }
            }}
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
            disabled={hasCustomerInfo}
            size="small"
            InputProps={{
              readOnly: hasCustomerInfo,
              sx: { fontSize: '0.8rem' },
              startAdornment: (
                <InputAdornment position="start">
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5, mr: 0.5 }}>
                    <FormControl 
                      variant="standard" 
                      sx={{ 
                        minWidth: isManualCountryCode ? 64 : 70,
                        '& .MuiInput-underline:before': { display: 'none' },
                        '& .MuiInput-underline:after': { display: 'none' },
                        '& .MuiInput-underline:hover:not(.Mui-disabled):before': { display: 'none' }
                      }}
                    >
                      <Select
                        value={
                          isManualCountryCode
                            ? MANUAL_COUNTRY_VALUE
                            : selectedCountry?.code || ''
                        }
                        onChange={handleCountryChange}
                        disableUnderline
                        disabled={hasCustomerInfo || countriesLoading}
                        displayEmpty
                        size="small"
                        sx={{
                          fontSize: '0.75rem',
                          '& .MuiSelect-select': {
                            paddingRight: '16px !important',
                            paddingLeft: 0,
                            paddingTop: 0,
                            paddingBottom: 0,
                            display: 'flex',
                            alignItems: 'center'
                          },
                          '& .MuiSvgIcon-root': {
                            fontSize: '0.9rem'
                          }
                        }}
                      >
                        {countriesLoading && (
                          <MenuItem value="" disabled sx={{ fontSize: '0.75rem' }}>
                            <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                              <CircularProgress size={12} />
                              Loading...
                            </Box>
                          </MenuItem>
                        )}
                        {!countriesLoading &&
                          countries.map((country) => (
                            <MenuItem key={country.code} value={country.code} sx={{ fontSize: '0.75rem' }}>
                              <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                                <span style={{ fontWeight: 'bold' }}>{country.country_code}</span>
                                {country.name ? (
                                  <span style={{ fontSize: '0.65rem', color: '#666' }}>{country.name}</span>
                                ) : null}
                              </Box>
                            </MenuItem>
                          ))}
                        <MenuItem value={MANUAL_COUNTRY_VALUE} sx={{ fontSize: '0.75rem' }}>
                          <span style={{ fontWeight: 'bold' }}>Other</span>
                        </MenuItem>
                      </Select>
                    </FormControl>
                    {isManualCountryCode && !hasCustomerInfo && (
                      <TextField
                        variant="standard"
                        value={form.countryCode || '+'}
                        onChange={handleManualCountryCodeChange}
                        placeholder="+XX"
                        inputProps={{
                          'aria-label': 'Manual country dial code',
                          style: {
                            width: 44,
                            fontSize: '0.75rem',
                            fontWeight: 600,
                            padding: 0,
                          },
                        }}
                        InputProps={{ disableUnderline: true }}
                      />
                    )}
                  </Box>
                </InputAdornment>
              ),
            }}
            InputLabelProps={{
              sx: { fontSize: '0.8rem' }
            }}
            FormHelperTextProps={{
              sx: { fontSize: '0.65rem', mx: 0 }
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
            disabled={hasCustomerInfo}
            size="small"
            InputProps={{
              readOnly: hasCustomerInfo,
              sx: { fontSize: '0.8rem' }
            }}
            InputLabelProps={{
              sx: { fontSize: '0.8rem' }
            }}
            FormHelperTextProps={{
              sx: { fontSize: '0.65rem', mx: 0 }
            }}
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
            disabled={hasCustomerInfo}
            size="small"
            InputProps={{
              readOnly: hasCustomerInfo,
              sx: { fontSize: '0.8rem' }
            }}
            InputLabelProps={{
              sx: { fontSize: '0.8rem' }
            }}
          />
        </Grid>

        <Grid item xs={12} md={6}>
          <TextField
            label="State/Province/Region"
            name="state"
            fullWidth
            variant="outlined"
            value={form.state}
            onChange={handleChange}
            onBlur={handleBlur}
            disabled={hasCustomerInfo}
            size="small"
            InputProps={{
              readOnly: hasCustomerInfo,
              sx: { fontSize: '0.8rem' }
            }}
            InputLabelProps={{
              sx: { fontSize: '0.8rem' }
            }}
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
            disabled={hasCustomerInfo}
            size="small"
            InputProps={{
              readOnly: hasCustomerInfo,
              sx: { fontSize: '0.8rem' }
            }}
            InputLabelProps={{
              sx: { fontSize: '0.8rem' }
            }}
            FormHelperTextProps={{
              sx: { fontSize: '0.65rem', mx: 0 }
            }}
          />
        </Grid>

        <Grid item xs={12}>
          <TextField
            label="Special Requests"
            name="specialRequests"
            fullWidth
            multiline
            rows={3}
            variant="outlined"
            value={form.specialRequests}
            onChange={handleChange}
            disabled={hasCustomerInfo}
            size="small"
            InputProps={{
              readOnly: hasCustomerInfo,
              sx: { fontSize: '0.8rem' }
            }}
            InputLabelProps={{
              sx: { fontSize: '0.8rem' }
            }}
          />
        </Grid>
      </Grid>
      
      <Box sx={{ mt: 1.5, display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <Typography 
          variant="body2" 
          color={formValid ? "success.main" : "text.secondary"}
          sx={{ fontSize: '0.75rem' }}
        >
          {hasCustomerInfo 
            ? "✓ Information loaded from package data" 
            : (formValid ? "✓ Information saved" : "Fill in all required fields to save automatically")
          }
        </Typography>
      </Box>
    </Paper>
  );
};

export default SimpleCustomerInfo; 