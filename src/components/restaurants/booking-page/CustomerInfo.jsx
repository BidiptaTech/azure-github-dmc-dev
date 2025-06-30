import BookingDetails from "./sidebar/BookingDetails";
import { useSelector, useDispatch } from "react-redux";
import { useState, useEffect, useRef, forwardRef, useImperativeHandle } from "react";
import {
  TextField,
  Grid,
  Typography,
  Box,
  Select,
  MenuItem,
  InputAdornment,
  FormControl,
} from "@mui/material";
import {
  selectUserInfo,
  setUserInfo,
  setBookingResponse,
} from "../../../slice/common/customerInfo";
import React from "react";
import { useNavigate } from "react-router-dom";
import { setDateService } from "../../../slice/common/dateServicesSlice";
import {
  createBooking,
  setRestaurantsService,
} from "../../../slice/restaurant/RestaurantsSlice";
import Cookies from "js-cookie";
import { toast } from "react-toastify";
import { createSelector } from '@reduxjs/toolkit';
import { setBookingType, setIsNavigating } from '../../../slice/common/commonSlice';

// Add country codes constant
const countryCodes = [
  { code: "US", name: "United States", dialCode: "+1" },
  { code: "GB", name: "United Kingdom", dialCode: "+44" },
  { code: "IN", name: "India", dialCode: "+91" },
  { code: "AU", name: "Australia", dialCode: "+61" },
  { code: "SG", name: "Singapore", dialCode: "+65" },
  // Add more country codes as needed
];



const CustomerInfo = forwardRef(function CustomerInfo(props, ref) {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const [errors, setErrors] = useState({});
  const [touched, setTouched] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isEnquiring, setIsEnquiring] = useState(false);

  // State for selected country
  const [selectedCountry, setSelectedCountry] = useState(null);

  // Get user country from Redux state
  const user_country = useSelector((state) => state.auth?.user_country || []);
  
  // Get country code and phone validation values from Redux
  const countryCodeFromRedux = useSelector((state) => state.auth.countryCode);
  const dialMaxLength = useSelector((state) => state.auth.dialMaxLength) || 15;
  const dialMinLength = useSelector((state) => state.auth.dialMinLength) || 8;
  
  // console.log("Restaurant countryCode from Redux:", countryCodeFromRedux);
  // console.log("Restaurant dialMaxLength from Redux:", dialMaxLength);
  // console.log("Restaurant dialMinLength from Redux:", dialMinLength);

  const searchLocation = useSelector((state) => state.bookings.searchLocation);
  // console.log("Restaurant searchLocation:", searchLocation);
  
  const restaurantBookings = useSelector((state) => state.restaurants?.restaurantBookings || []);
  // console.log('restaurantBookings',restaurantBookings);
  const selectedRestaurant = useSelector((state) => state.restaurants?.selectedRestaurant || []);
  // console.log('selectedRestaurant',selectedRestaurant);
  const tourdetails = useSelector((state) => state.hotels.tourdetails);

  const [formData, setFormData] = useState({
    fullName: "",
    email: "",
    phone: "",
    countryCode: "+91",
    address1: "",
    address2: "",
    state: "",
    zip: "",
    specialRequests: "",
  });

  // Add this to get the bookingMode from Redux
  const bookingMode = useSelector((state) => state.common.bookingMode);

  // Initialize selected country from user_country array
  useEffect(() => {
    if (user_country && user_country.length > 0) {
      // Set default to first country in the array
      const defaultCountry = user_country[0];
      setSelectedCountry(defaultCountry);
      setFormData(prevForm => ({
        ...prevForm,
        countryCode: defaultCountry.country_code,
      }));
    }
  }, [user_country]);

  // Handler for country selection
  const handleCountryChange = (event) => {
    const selectedCountryCode = event.target.value;
    const country = user_country.find(c => c.code === selectedCountryCode);
    
    if (country) {
      setSelectedCountry(country);
      setFormData(prevForm => ({
        ...prevForm,
        countryCode: country.country_code,
      }));
      
      // Re-validate phone if it's already touched
      if (touched.phone && formData.phone) {
        const phoneError = validateField('phone', formData.phone);
        setErrors(prev => ({
          ...prev,
          phone: phoneError
        }));
      }
    }
  };

  const validateField = (name, value) => {
    let error = "";
    
    // Get dynamic min/max length from selectedCountry if available
    const phoneMinLength = selectedCountry?.contact_min_length || dialMinLength;
    const phoneMaxLength = selectedCountry?.contact_max_length || dialMaxLength;
    
    switch (name) {
      case "fullName":
        if (!value.trim()) {
          error = "Full name is required";
        } else if (value.length < 2) {
          error = "Name must be at least 2 characters";
        }
        break;

      case "email":
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!value) {
          error = "Email is required";
        } else if (!emailRegex.test(value)) {
          error = "Please enter a valid email";
        }
        break;

      case "phone":
        const phoneRegex = new RegExp(`^\\d{${phoneMinLength},${phoneMaxLength}}$`);
        if (!value) {
          error = "Phone number is required";
        } else if (!phoneRegex.test(value)) {
          error = `Enter a valid phone number (${phoneMinLength}-${phoneMaxLength} digits)`;
        }
        break;

      case "address1":
        if (!value.trim()) {
          error = "Address line 1 is required";
        } else if (value.trim().length < 5) {
          error = "Address must be at least 5 characters";
        }
        break;

      // State and ZIP validation removed - these fields are now optional
      
      default:
        break;
    }
    return error;
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    const updatedForm = { ...formData, [name]: value };
    setFormData(updatedForm);

    if (touched[name]) {
      const error = validateField(name, value);
      setErrors((prev) => ({ ...prev, [name]: error }));
    }
  };

  const handleBlur = (e) => {
    const { name, value } = e.target;
    setTouched((prev) => ({ ...prev, [name]: true }));
    const error = validateField(name, value);
    setErrors((prev) => ({ ...prev, [name]: error }));
  };

  const validate = () => {
    let newErrors = {};
    Object.keys(formData).forEach((key) => {
      const error = validateField(key, formData[key]);
      if (error) newErrors[key] = error;
    });

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async () => {
    try {
      // Set loading state
      setIsSubmitting(true);

      // Validate the form first
      const isValid = validate();

      if (!isValid) {
        // Get list of fields with validation errors
        const errorFields = Object.entries(errors)
          .filter(([_, value]) => value)
          .map(([field, message]) => ({
            field: field.charAt(0).toUpperCase() + field.slice(1),
            message,
          }));

        // Show detailed error toast
        toast.error(
          <div>
            <div>Please fill in the following required fields:</div>
            {errorFields.map(({ field, message }, index) => (
              <div key={index}>
                • {field}: {message}
              </div>
            ))}
          </div>,
          { position: "top-center", autoClose: 5000 }
        );
        setIsSubmitting(false); // Reset loading state
        return;
      }

      // Set navigation state to true immediately before making the API call
      dispatch(setIsNavigating(true));

      // Update form data with bookingType if parent component provided onFormChange
      if (props.onFormChange) {
        props.onFormChange({
          ...formData,
          bookingType: "booking",
        });
      }

      // Calculate total price including transport if available
      // IMPORTANT: Use the exact price from restaurantBookings without adding markups
      const mealPrice = parseFloat(restaurantBookings?.[0]?.data?.[0]?.totalPrice || 0);
      // console.log('mealPrice', mealPrice);
      
      // Calculate transport price based on type
      let transportPrice = 0;
      const transport = restaurantBookings?.[0]?.data?.[0]?.transport;
      
      if (transport) {
        const adultCount = restaurantBookings?.[0]?.data?.[0]?.adultCount || 0;
        const childCount = restaurantBookings?.[0]?.data?.[0]?.childCount || 0;
        const totalPax = adultCount + childCount;
        
        // If shared transport, multiply price by total pax
        if (transport.transport_type === 'shared') {
          transportPrice = parseFloat(transport.price) * totalPax;
        } else {
          // For private transport, use price as is
          transportPrice = parseFloat(transport.price);
        }
      }
      
      // Ensure we're using the exact values without any hidden markups
      const totalPrice = parseFloat(mealPrice) + parseFloat(transportPrice);
      // console.log('finalTotalPrice', totalPrice);

      const bookingDetails = {
        agent_id: Cookies.get("AgentId") || "0",
        data: [{
          ...formData,
          bookingDate: restaurantBookings?.[0]?.data?.[0]?.bookingDate,
          visitTime: restaurantBookings?.[0]?.data?.[0]?.visitTime,
          adultCount: restaurantBookings?.[0]?.data?.[0]?.adultCount,
          childCount: restaurantBookings?.[0]?.data?.[0]?.childCount || 0,
          restaurantId: restaurantBookings?.[0]?.data?.[0]?.restaurantId || selectedRestaurant.id,
          restaurantName: restaurantBookings?.[0]?.data?.[0]?.restaurantName,
          mealType: restaurantBookings?.[0]?.data?.[0]?.mealType,
          mealSpecificType: restaurantBookings?.[0]?.data?.[0]?.mealSpecificType,
          MealDescription: restaurantBookings?.[0]?.data?.[0]?.MealDescription,
          totalPrice: totalPrice,
          mealPrice: mealPrice,
          transport: restaurantBookings?.[0]?.data?.[0]?.transport || null,
          transportPrice: transportPrice,
          priceTypes: restaurantBookings?.[0]?.data?.[0]?.priceTypes,
          dmc_id: selectedRestaurant?.meals?.[0]?.dmc_id,
          bookingType: "booking"
        }],
        tour_id: parseInt(tourdetails?.tour_id, 10) || 0,
        type: "restaurant",
        bookingType: "booking"
      };

      // console.log("Sending booking data:", JSON.stringify(bookingDetails, null, 2));
      const response = await dispatch(createBooking(bookingDetails)).unwrap();
      
      if (response?.service?.date_service) {
        // Set all necessary data in Redux
        dispatch(setDateService(response.service.date_service));
        dispatch(setRestaurantsService(response.service.data));
        dispatch(setUserInfo(response.service.data));
        dispatch(setBookingResponse(response));
        dispatch(setBookingType(response?.order?.bookingType));
        
        // Navigate to thank you page after a short delay to ensure Redux updates
        setTimeout(() => {
          navigate("/dashboard/db-dashboard/restaurants-thank-you", {
            state: { 
              bookingResponse: response,
              from: 'booking'
            }
          });
          
          // Reset navigation state after navigation completes
          setTimeout(() => {
            dispatch(setIsNavigating(false));
          }, 500);
        }, 100);
      } else {
        dispatch(setIsNavigating(false)); // Reset navigation state on error
        throw new Error("Invalid response received from the server.");
      }
    } catch (error) {
      console.error("Error during submission:", error);
      dispatch(setIsNavigating(false)); // Reset navigation state on error
      toast.error(
        error.message || "Something went wrong. Please try again later.",
        {
          position: "top-center",
          autoClose: 3000,
        }
      );
    } finally {
      // Reset loading state if needed (though we navigate away on success)
      setIsSubmitting(false);
    }
  };

  const handleEnquirySubmit = async () => {
    try {
      // Set loading state
      setIsEnquiring(true);
      
      // console.log("Starting enquiry submission...");
      
      // Validate the form first
      const isValid = validate();

      if (!isValid) {
        // Get list of fields with validation errors
        const errorFields = Object.entries(errors)
          .filter(([_, value]) => value)
          .map(([field, message]) => ({
            field: field.charAt(0).toUpperCase() + field.slice(1),
            message,
          }));

        // Show detailed error toast
        toast.error(
          <div>
            <div>Please fill in the following required fields:</div>
            {errorFields.map(({ field, message }, index) => (
              <div key={index}>
                • {field}: {message}
              </div>
            ))}
          </div>,
          { position: "top-center", autoClose: 5000 }
        );
        setIsEnquiring(false); // Reset loading state
        return;
      }

      // Update form data with bookingType if parent component provided onFormChange
      if (props.onFormChange) {
        props.onFormChange({
          ...formData,
          bookingType: "enquiry",
        });
      }

      // Set navigation state to true before making the API call
      dispatch(setIsNavigating(true));

      // Calculate total price including transport if available
      // IMPORTANT: Use the exact price from restaurantBookings without adding markups
      const mealPrice = parseFloat(restaurantBookings?.[0]?.data?.[0]?.totalPrice || 0);
      // console.log('Enquiry mealPrice', mealPrice);
      
      // Calculate transport price based on type
      let transportPrice = 0;
      const transport = restaurantBookings?.[0]?.data?.[0]?.transport;
      
      if (transport) {
        const adultCount = restaurantBookings?.[0]?.data?.[0]?.adultCount || 0;
        const childCount = restaurantBookings?.[0]?.data?.[0]?.childCount || 0;
        const totalPax = adultCount + childCount;
        
        // If shared transport, multiply price by total pax
        if (transport.transport_type === 'shared') {
          transportPrice = parseFloat(transport.price) * totalPax;
        } else {
          // For private transport, use price as is
          transportPrice = parseFloat(transport.price);
        }
      }
      
      // Ensure we're using the exact values without any hidden markups
      const totalPrice = parseFloat(mealPrice) + parseFloat(transportPrice);
      // console.log('Enquiry finalTotalPrice', totalPrice);

      const enquiryDetails = {
        agent_id: Cookies.get("AgentId") || "0",
        data: [{
          ...formData,
          bookingDate: restaurantBookings?.[0]?.data?.[0]?.bookingDate,
          visitTime: restaurantBookings?.[0]?.data?.[0]?.visitTime,
          adultCount: restaurantBookings?.[0]?.data?.[0]?.adultCount,
          childCount: restaurantBookings?.[0]?.data?.[0]?.childCount || 0,
          restaurantId: restaurantBookings?.[0]?.data?.[0]?.restaurantId || selectedRestaurant.id,
          restaurantName: restaurantBookings?.[0]?.data?.[0]?.restaurantName,
          mealType: restaurantBookings?.[0]?.data?.[0]?.mealType,
          mealSpecificType: restaurantBookings?.[0]?.data?.[0]?.mealSpecificType,
          MealDescription: restaurantBookings?.[0]?.data?.[0]?.MealDescription,
          totalPrice: totalPrice,
          mealPrice: mealPrice,
          transport: restaurantBookings?.[0]?.data?.[0]?.transport || null,
          transportPrice: transportPrice,
          priceTypes: restaurantBookings?.[0]?.data?.[0]?.priceTypes,
          dmc_id: selectedRestaurant?.meals?.[0]?.dmc_id,
          bookingType: "enquiry",
        }],
        tour_id: parseInt(tourdetails?.tour_id, 10) || 0,
        type: "restaurant",
        bookingType: "enquiry",
      };

      // console.log("Sending enquiry data:", JSON.stringify(enquiryDetails, null, 2));
      const response = await dispatch(createBooking(enquiryDetails)).unwrap();
      // console.log('Enquiry response:', response);
      
      if (response?.service?.date_service) {
        // Set all necessary data in Redux
        dispatch(setDateService(response.service.date_service));
        dispatch(setRestaurantsService(response.service.data));
        dispatch(setUserInfo(response.service.data));
        dispatch(setBookingResponse(response));
        dispatch(setBookingType(response?.order?.bookingType));
        
        // Show success message
        toast.success("Enquiry submitted successfully!", {
          position: "top-center",
          autoClose: 3000,
        });
        
        // Navigate to thank you page after a short delay to ensure Redux updates
        setTimeout(() => {
          navigate("/dashboard/db-dashboard/restaurants-thank-you", {
            state: { 
              bookingResponse: response,
              from: 'enquiry'
            }
          });
          
          // Reset navigation state after navigation completes
          setTimeout(() => {
            dispatch(setIsNavigating(false));
          }, 500);
        }, 100);
      } else {
        dispatch(setIsNavigating(false)); // Reset navigation state on error
        throw new Error("Invalid response received from the server.");
      }
    } catch (error) {
      console.error("Error during enquiry submission:", error);
      dispatch(setIsNavigating(false)); // Reset navigation state on error
      toast.error(
        error.message || "Something went wrong. Please try again later.",
        {
          position: "top-center",
          autoClose: 3000,
        }
      );
    } finally {
      // Reset loading state if needed (though we navigate away on success)
      setIsEnquiring(false);
    }
  };

  // Add useImperativeHandle for ref functionality
  useImperativeHandle(ref, () => ({
    isFormValid: () => {
      const requiredFields = [
        "fullName",
        "email",
        "phone",
        "address1",
      ];
      const newErrors = {};
      let isValid = true;

      requiredFields.forEach((field) => {
        const error = validateField(field, formData[field]);
        if (error) {
          newErrors[field] = error;
          isValid = false;
        }
      });

      setErrors(newErrors);
      setTouched(
        requiredFields.reduce((acc, field) => ({ ...acc, [field]: true }), {})
      );
      return isValid;
    },
  }));

  return (
    <Grid container spacing={3} sx={{ mt: 4, mb: 0 }}>
      <Grid item xs={12} md={7}>
        <Typography variant="h5" sx={{ fontWeight: 500 }}>
          Let us know who you are
        </Typography>
        <Box sx={{ mt: 2, mb: 0 }}>
          <Grid container spacing={2}>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Full Name"
                name="fullName"
                value={formData.fullName}
                onChange={handleChange}
                onBlur={handleBlur}
                error={!!errors.fullName}
                helperText={errors.fullName}
                required
              />
            </Grid>

            <Grid item xs={12} md={6}>
              <TextField
                fullWidth
                label="Email"
                name="email"
                type="email"
                value={formData.email}
                onChange={handleChange}
                onBlur={handleBlur}
                error={!!errors.email}
                helperText={errors.email}
                required
              />
            </Grid>

            <Grid item xs={12} md={6}>
              <TextField
                fullWidth
                label="Phone Number"
                name="phone"
                value={formData.phone}
                onChange={handleChange}
                onBlur={handleBlur}
                error={!!errors.phone}
                helperText={errors.phone}
                required
                InputProps={{
                  startAdornment: (
                    <InputAdornment position="start">
                      {user_country && user_country.length > 0 ? (
                        <FormControl 
                          variant="standard" 
                          sx={{ 
                            minWidth: 80,
                            '& .MuiInput-underline:before': { display: 'none' },
                            '& .MuiInput-underline:after': { display: 'none' },
                            '& .MuiInput-underline:hover:not(.Mui-disabled):before': { display: 'none' }
                          }}
                        >
                          <Select
                            value={selectedCountry?.code || ''}
                            onChange={handleCountryChange}
                            disableUnderline
                            sx={{
                              fontSize: '0.875rem',
                              '& .MuiSelect-select': {
                                paddingRight: '20px !important',
                                paddingLeft: 0,
                                paddingTop: 0,
                                paddingBottom: 0,
                                display: 'flex',
                                alignItems: 'center'
                              },
                              '& .MuiSvgIcon-root': {
                                fontSize: '1rem'
                              }
                            }}
                          >
                            {user_country.map((country) => (
                              <MenuItem key={country.code} value={country.code}>
                                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                                  <span style={{ fontWeight: 'bold' }}>{country.country_code}</span>
                                  <span style={{ fontSize: '0.75rem', color: '#666' }}>({country.code})</span>
                                </Box>
                              </MenuItem>
                            ))}
                          </Select>
                        </FormControl>
                      ) : (
                        formData.countryCode
                      )}
                    </InputAdornment>
                  ),
                }}
              />
            </Grid>

            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Address line 1"
                name="address1"
                value={formData.address1}
                onChange={handleChange}
                onBlur={handleBlur}
                error={!!errors.address1}
                helperText={errors.address1}
                required
              />
            </Grid>

            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Address line 2"
                name="address2"
                value={formData.address2}
                onChange={handleChange}
                onBlur={handleBlur}
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
                error={!!errors.state}
                helperText={errors.state}
              />
            </Grid>

            <Grid item xs={12} md={6}>
              <TextField
                fullWidth
                label="ZIP code/Postal code"
                name="zip"
                value={formData.zip}
                onChange={handleChange}
                onBlur={handleBlur}
                error={!!errors.zip}
                helperText={errors.zip}
              />
            </Grid>

            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Special Requests"
                name="specialRequests"
                value={formData.specialRequests}
                onChange={handleChange}
                multiline
                rows={4}
              />
            </Grid>
          </Grid>
        </Box>
        <Box
          sx={{
            position: "relative",
            top: { xs: "0px", md: "0px" },
            display: "flex",
            justifyContent: "flex-start",
            gap: "15px",
            mt: 2,
          }}
        >
          <button
            className="button h-60 px-24 -dark-1 bg-blue-1 text-white"
            onClick={handleSubmit}
            disabled={isSubmitting}
          >
            {isSubmitting ? 'Booking...' : 'Book Now'} <div className="icon-arrow-top-right ml-15" />
          </button>

          {/* Only show the enquiry button if mode is not travclicks */}
          {bookingMode !== 'travclicks' && (
            <button
              className="button h-60 px-24 -dark-1 bg-blue-1 text-white"
              onClick={handleEnquirySubmit}
              style={{ backgroundColor: '#3554D1' }}
              disabled={isEnquiring}
            >
              {isEnquiring ? 'Enquiring...' : 'Make an Enquiry'}
            </button>
          )}
        </Box>

       
      </Grid>

      <Grid item xs={12} md={5}>
        <Box sx={{ mt: 2, mb: 0 }}>
          <BookingDetails />
        </Box>
      </Grid>
    </Grid>
  );
});

// Add display name for debugging
CustomerInfo.displayName = 'CustomerInfo';

export default CustomerInfo;
