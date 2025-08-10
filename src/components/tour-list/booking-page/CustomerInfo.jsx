import BookingDetails from "./sidebar/BookingDetails";
import { useState, forwardRef, useImperativeHandle, useEffect, useRef } from "react";
import {
  TextField,
  Grid,
  Typography,
  Box,
  Select,
  MenuItem,
  InputAdornment,
  FormControl,
  InputLabel,
  OutlinedInput,
  FormHelperText,
  NativeSelect,
} from "@mui/material";
import { useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux"; 
import {
  selectUserInfo,
  setUserInfo,
  setBookingResponse,
} from "../../../slice/common/customerInfo";
import { setDateService } from "../../../slice/common/dateServicesSlice";
import {
  createBooking,
  setAttractionService,
} from "../../../slice/attractions/attractionSlice";
import Cookies from "js-cookie";
import { toast } from "react-toastify";
import { setBookingType, setBookingMode, setIsNavigating } from '../../../slice/common/commonSlice';

// Add this near the top with other constants
const countryCodes = [
  { code: "US", name: "United States", dialCode: "+1" },
  { code: "GB", name: "United Kingdom", dialCode: "+44" },
  { code: "IN", name: "India", dialCode: "+91" },
  { code: "AU", name: "Australia", dialCode: "+61" },
  { code: "SG", name: "Singapore", dialCode: "+65" },
  // Add more country codes as needed
];

const CustomerInfo = forwardRef((props, ref) => {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const [errors, setErrors] = useState({});
  const [touched, setTouched] = useState({});

  // State for selected country
  const [selectedCountry, setSelectedCountry] = useState(null);

  // Get user country from Redux state
  const user_country = useSelector((state) => state.auth?.user_country || []);

  // Get the bookingMode from Redux
  const bookingMode = useSelector((state) => state.common.bookingMode);
  // console.log('bookingMode bookingMode bookingMode',bookingMode);
  
  // Get the mode from the attraction booking data
  const attractionBookingMode = useSelector((state) => 
    state.attractions?.attractionBookings?.[0]?.data?.[0]?.mode
  );
  // console.log('attractionBookingMode attractionBookingMode attractionBookingMode',attractionBookingMode);
  
  // Get country code and phone validation values from Redux
  const countryCodeFromRedux = useSelector((state) => state.auth.countryCode);
  const dialMaxLength = useSelector((state) => state.auth.dialMaxLength) || 15;
  const dialMinLength = useSelector((state) => state.auth.dialMinLength) || 8;
  
  // console.log("Attraction countryCode from Redux:", countryCodeFromRedux);
  // console.log("Attraction dialMaxLength from Redux:", dialMaxLength);
  // console.log("Attraction dialMinLength from Redux:", dialMinLength);

  // Log the current modes for debugging
  // console.log("Current Redux bookingMode:", bookingMode);
  // console.log("Current attraction mode from booking data:", attractionBookingMode);
  
  // Add an effect to ensure the mode is consistently set
  useEffect(() => {
    // If we have attraction booking data but no bookingMode set, 
    // or if they're inconsistent, update Redux
    if (attractionBookingMode && attractionBookingMode !== bookingMode) {
      // console.log(`Updating bookingMode from ${bookingMode} to ${attractionBookingMode}`);
      dispatch(setBookingMode(attractionBookingMode));
    }
  }, [attractionBookingMode, bookingMode, dispatch]);

  const searchLocation = useSelector((state) => state.bookings.searchLocation);
  // console.log("Attraction searchLocation:", searchLocation);
  
  const attractionBookings = useSelector((state) => state.attractions?.attractionBookings || []);
    // console.log('attractionBookings attractionBookings attractionBookings',attractionBookings);
    const attractionDetails = useSelector((state) => state.attractions.attractionDetails);
  //  console.log('attractionDetails attractionDetails attractionDetails............',attractionDetails?.ticket_prices?.[0]?.ticket_id);
  
  const tourdetails = useSelector((state) => state.hotels.tourdetails);

  // Keep local form state only
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

  // Add these new state variables at the top of the component
  const [showEnquiry, setShowEnquiry] = useState(false);
  const [enquiryAmount, setEnquiryAmount] = useState(() => {
    return attractionBookings?.[0]?.data?.[0]?.totalPrice || '';
  });
  const [enquiryComment, setEnquiryComment] = useState('');
  const [commentError, setCommentError] = useState(false);

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
          error = "Address is required";
        } else if (value.length < 5) {
          error = "Address must be at least 5 characters";
        }
        break;

      // State and ZIP validation removed - these fields are now optional
      
      default:
        break;
    }
    return error;
  };

  // Handle input changes
  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));

    if (touched[name]) {
      const error = validateField(name, value);
      setErrors(prev => ({ ...prev, [name]: error }));
    }
  };

  // Add useEffect to pass form data to parent
  useEffect(() => {
    props.onFormChange(formData);
  }, [formData, props.onFormChange]);

  const handleBlur = (e) => {
    const { name, value } = e.target;
    setTouched((prev) => ({ ...prev, [name]: true }));
    const error = validateField(name, value);
    setErrors((prev) => ({ ...prev, [name]: error }));
  };

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

  const validate = () => {
    let newErrors = {};
    Object.keys(formData).forEach((key) => {
      const error = validateField(key, formData[key]);
      if (error) newErrors[key] = error;
    });

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  useEffect(() => {
    if (props.validateForm) {
      props.validateForm(() => validate);
    }
  }, [props.validateForm, formData]);

  // Modify handleSubmit function
  const handleSubmit = async () => {
    try {
      // Set booking loading state to true
      setIsBookingLoading(true);
      
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
        setIsBookingLoading(false); // Reset loading state
        return;
      }

      // Set navigation state to true immediately before making the API call
      dispatch(setIsNavigating(true));

      // Update form data with bookingType
      if (props.onFormChange) {
        props.onFormChange({
          ...formData,
          bookingType: "booking",
        });
      }

      const isPackageBooking = attractionBookings?.[0]?.data?.[0]?.package_type === 1;
      
      // Get package details if it's a package booking
      const packageDetails = isPackageBooking ? {
        package_id: attractionBookings?.[0]?.data?.[0]?.package_attraction_id,
        package_name: attractionBookings?.[0]?.data?.[0]?.ticketName,
        package_attractions: attractionBookings?.[0]?.data?.[0]?.packageDetails?.attractions || [],
        package_description: attractionBookings?.[0]?.data?.[0]?.packageDetails?.description || "",
        package_adult_price: attractionBookings?.[0]?.data?.[0]?.ticket_details?.adult_price || 0,
        package_child_price: attractionBookings?.[0]?.data?.[0]?.ticket_details?.child_price || 0,
        package_senior_price: attractionBookings?.[0]?.data?.[0]?.ticket_details?.senior_price || 0,
        package_total_attractions: attractionBookings?.[0]?.data?.[0]?.packageDetails?.attractions?.length || 0
      } : null;
      
     
      
      const bookingDetails = {
        agent_id: Cookies.get("AgentId") || "0",
        data: [{
          ...formData,
          bookingDate: attractionBookings?.[0]?.data?.[0]?.bookingDate,
          visitTime: attractionBookings?.[0]?.data?.[0]?.visitTime,
          adultCount: attractionBookings?.[0]?.data?.[0]?.adultCount,
          childCount: attractionBookings?.[0]?.data?.[0]?.childCount || 0,
          seniorCount: attractionBookings?.[0]?.data?.[0]?.seniorCount || 0,
          AttractionId: attractionBookings?.[0]?.data?.[0]?.AttractionId || attractionDetails.id || 0,
          AttractionName: attractionBookings?.[0]?.data?.[0]?.AttractionName,
          ticketId:  attractionBookings?.[0]?.data?.[0]?.ticketId,
          ticketName: attractionBookings?.[0]?.data?.[0]?.ticketName,
          ticket_details: attractionBookings?.[0]?.data?.[0]?.ticket_details || {
            adult_price: attractionBookings?.[0]?.data?.[0]?.dmc_adult_price || 0,
            child_price: attractionBookings?.[0]?.data?.[0]?.dmc_child_price || 0,
            senior_price: attractionBookings?.[0]?.data?.[0]?.dmc_senior_price || 0,
            description: attractionBookings?.[0]?.data?.[0]?.description || "",
            
          },
          transport: attractionBookings?.[0]?.data?.[0]?.transport || null,
          Selection: (() => {
            const transport = attractionBookings?.[0]?.data?.[0]?.transport;
            if (!transport) return "withoutTransport";
            return transport.type === "private" ? "withPrivate" : "withShare";
          })(),
          mode: attractionBookingMode?.prices?.mode || attractionDetails?.prices?.mode,
          totalPrice: attractionBookings?.[0]?.data?.[0]?.totalPrice,
          nri : attractionBookings?.[0]?.data?.[0]?.ticket_details?.nri || "residential",
          bookingType: "booking",
          package_type: attractionBookings?.[0]?.data?.[0]?.package_type || 0,
          package_attraction_id: attractionDetails?.packages?.[0]?.package_attraction_id || null,
          ...(isPackageBooking && packageDetails && { package_details: packageDetails })
        }],
        tour_id: parseInt(tourdetails?.tour_id, 10) || 0,
        type: isPackageBooking ? "attraction_package" : "attraction",
        type: isPackageBooking ? "attraction_package" : "attraction",
        type: isPackageBooking ? "attraction_package" : "attraction",
        bookingType: "booking"
      };

      const response = await dispatch(createBooking(bookingDetails)).unwrap();
      
      if (response?.service?.date_service) {
        // Set all necessary data in Redux
        dispatch(setDateService(response.service.date_service));
        dispatch(setAttractionService(response.service.data));
        dispatch(setUserInfo(formData));
        dispatch(setBookingResponse(response));
        dispatch(setBookingType(response?.order?.bookingType));
        
        // Navigate to thank you page after a short delay to ensure Redux updates
        setTimeout(() => {
          navigate("/dashboard/db-dashboard/attraction-thank-you", {
            state: { 
              bookingResponse: response,
              from: 'booking'
            }
          });
          
          // Reset navigation state after navigation completes
          setTimeout(() => {
            dispatch(setIsNavigating(false));
            setIsBookingLoading(false); // Reset loading state
          }, 500);
        }, 100);
      } else {
        dispatch(setIsNavigating(false)); // Reset navigation state on error
        setIsBookingLoading(false); // Reset loading state
        throw new Error("Invalid response received from the server.");
      }
    } catch (error) {
      console.error("Error during submission:", error);
      dispatch(setIsNavigating(false)); // Reset navigation state on error
      setIsBookingLoading(false); // Reset loading state
      toast.error(
        error.message || "Something went wrong. Please try again later.",
        {
          position: "top-center",
          autoClose: 3000,
        }
      );
    }
  };

  // Add the handleAmountChange function
  // const handleAmountChange = (e) => {
  //   const newValue = parseFloat(e.target.value);
  //   const totalPrice = attractionBookings?.[0]?.data?.[0]?.totalPrice || 0;
    
  //   if (newValue <= totalPrice && newValue > 0) {
  //     setEnquiryAmount(newValue);
  //   }
  // };

  // Modify handleEnquirySubmit function
  const handleEnquirySubmit = async () => {
    try {
      // console.log("Starting enquiry submission...");
      
      // Set enquiry loading state to true
      setIsEnquiryLoading(true);
      
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
        setIsEnquiryLoading(false); // Reset loading state
        return;
      }

      // Update form data with bookingType
      if (props.onFormChange) {
        props.onFormChange({
          ...formData,
          bookingType: "enquiry",
        });
      }

      // Set navigation state to true before making the API call
      dispatch(setIsNavigating(true));

      const isPackageBooking = attractionBookings?.[0]?.data?.[0]?.package_type === 1;
      
      // Get package details if it's a package booking
      const packageDetails = isPackageBooking ? {
        package_id: attractionBookings?.[0]?.data?.[0]?.package_attraction_id,
        package_name: attractionBookings?.[0]?.data?.[0]?.ticketName,
        package_attractions: attractionBookings?.[0]?.data?.[0]?.packageDetails?.attractions || [],
        package_description: attractionBookings?.[0]?.data?.[0]?.packageDetails?.description || "",
        package_adult_price: attractionBookings?.[0]?.data?.[0]?.ticket_details?.adult_price || 0,
        package_child_price: attractionBookings?.[0]?.data?.[0]?.ticket_details?.child_price || 0,
        package_senior_price: attractionBookings?.[0]?.data?.[0]?.ticket_details?.senior_price || 0,
        package_total_attractions: attractionBookings?.[0]?.data?.[0]?.packageDetails?.attractions?.length || 0
      } : null;
      
      const enquiryDetails = {
        agent_id: Cookies.get("AgentId") || "0",
        data: [{
          ...formData,
          bookingDate: attractionBookings?.[0]?.data?.[0]?.bookingDate,
          visitTime: attractionBookings?.[0]?.data?.[0]?.visitTime,
          adultCount: attractionBookings?.[0]?.data?.[0]?.adultCount,
          childCount: attractionBookings?.[0]?.data?.[0]?.childCount || 0,
          seniorCount: attractionBookings?.[0]?.data?.[0]?.seniorCount || 0,
          AttractionId: attractionBookings?.[0]?.data?.[0]?.AttractionId || attractionDetails.id || 0,
          AttractionName: attractionBookings?.[0]?.data?.[0]?.AttractionName,
          ticketId:  attractionBookings?.[0]?.data?.[0]?.ticketId,
          ticketName: attractionBookings?.[0]?.data?.[0]?.ticketName,
          ticket_details: attractionBookings?.[0]?.data?.[0]?.ticket_details || {
            adult_price: attractionBookings?.[0]?.data?.[0]?.dmc_adult_price || 0,
            child_price: attractionBookings?.[0]?.data?.[0]?.dmc_child_price || 0,
            senior_price: attractionBookings?.[0]?.data?.[0]?.dmc_senior_price || 0,
            description: attractionBookings?.[0]?.data?.[0]?.description || "",
            nri : attractionBookings?.[0]?.data?.[0]?.ticket_details?.nri || "residential",
          },
          transport: attractionBookings?.[0]?.data?.[0]?.transport || null,
          Selection: (() => {
            const transport = attractionBookings?.[0]?.data?.[0]?.transport;
            if (!transport) return "withoutTransport";
            return transport.type === "private" ? "withPrivate" : "withShare";
          })(),
          mode: attractionBookingMode?.prices?.mode || attractionDetails?.prices?.mode,
          totalPrice: attractionBookings?.[0]?.data?.[0]?.totalPrice,
          nri : attractionBookings?.[0]?.data?.[0]?.ticket_details?.nri || "residential",
          price: attractionBookings?.[0]?.data?.[0]?.totalPrice,
          prices: { 
            price: attractionBookings?.[0]?.data?.[0]?.totalPrice 
          },
          bookingType: "enquiry",
          package_type: attractionBookings?.[0]?.data?.[0]?.package_type || 0,
          package_attraction_id: attractionDetails?.packages?.[0]?.package_attraction_id || null,
          ...(isPackageBooking && packageDetails && { package_details: packageDetails })
        }],
        tour_id: parseInt(tourdetails?.tour_id, 10) || 0,
        type: isPackageBooking ? "attraction_package" : "attraction",
        type: isPackageBooking ? "attraction_package" : "attraction",
        type: isPackageBooking ? "attraction_package" : "attraction",
        bookingType: "enquiry",
      };

      // Force a direct field for PHP's $price variable
      enquiryDetails.price = enquiryDetails.data[0].totalPrice;

      const response = await dispatch(createBooking(enquiryDetails)).unwrap();
      
      if (response?.service?.date_service) {
        // Set all necessary data in Redux
        dispatch(setDateService(response.service.date_service));
        dispatch(setAttractionService(response.service.data));
        dispatch(setUserInfo(formData));
        dispatch(setBookingResponse(response));
        dispatch(setBookingType(response?.order?.bookingType));
        
        // Show success message
        toast.success("Enquiry submitted successfully!", {
          position: "top-center",
          autoClose: 3000,
        });
        
        setShowEnquiry(false);
        setEnquiryComment('');
        
        // Navigate to thank you page after a short delay to ensure Redux updates
        setTimeout(() => {
          navigate("/dashboard/db-dashboard/attraction-thank-you", {
            state: { 
              bookingResponse: response,
              from: 'enquiry'
            }
          });
          
          // Reset navigation state after navigation completes
          setTimeout(() => {
            dispatch(setIsNavigating(false));
            setIsEnquiryLoading(false); // Reset loading state
          }, 500);
        }, 100);
      } else {
        dispatch(setIsNavigating(false)); // Reset navigation state on error
        setIsEnquiryLoading(false); // Reset loading state
        throw new Error("Invalid response received from the server.");
      }
    } catch (error) {
      console.error("Error during enquiry submission:", error);
      dispatch(setIsNavigating(false)); // Reset navigation state on error
      setIsEnquiryLoading(false); // Reset loading state
      toast.error(
        error.message || "Something went wrong. Please try again later.",
        {
          position: "top-center",
          autoClose: 3000,
        }
      );
    }
  };

  const [isBookingLoading, setIsBookingLoading] = useState(false);
  const [isEnquiryLoading, setIsEnquiryLoading] = useState(false);

  return (
    <>
      <Grid container spacing={4}>
        <Grid item xs={12} lg={7}>
          <Box sx={{ pt: 3, px: 3, pb: 1 }}>
            <Typography variant="h5" sx={{ mb: 4 }}>
              Let us know who you are
            </Typography>

            <Grid container spacing={2}>
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
                  error={touched.email && Boolean(errors.email)}
                  helperText={touched.email && errors.email}
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
                  error={touched.phone && Boolean(errors.phone)}
                  helperText={touched.phone && errors.phone}
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
                  label="Address Line 1"
                  name="address1"
                  value={formData.address1}
                  onChange={handleChange}
                  onBlur={handleBlur}
                  error={touched.address1 && Boolean(errors.address1)}
                  helperText={touched.address1 && errors.address1}
                  required
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
          
          <Box sx={{
            position: "relative",
            top: { xs: "0px", md: "0px" },
            display: "flex",
            justifyContent: "flex-start",
            gap: "15px",
            mt: 2,
            left: '22px'
          }}>
            <button
              className="button h-60 px-24 -dark-1 bg-blue-1 text-white"
              onClick={handleSubmit}
              disabled={isBookingLoading || isEnquiryLoading}
              style={{ 
                cursor: isBookingLoading ? 'not-allowed' : 'pointer',
                opacity: isBookingLoading ? 0.7 : 1
              }}
            >
              {isBookingLoading ? (
                <>Booking... <span className="loading-dots"><span>.</span><span>.</span><span>.</span></span></>
              ) : (
                <>Book Now <div className="icon-arrow-top-right ml-15" /></>
              )}
            </button>

            {/* Show the enquiry button only if the mode is not travclicks */}
            {bookingMode !== 'travclicks' && (
              <button
                className="button h-60 px-24 -dark-1 bg-blue-1 text-white"
                onClick={handleEnquirySubmit}
                disabled={isBookingLoading || isEnquiryLoading}
                style={{ 
                  backgroundColor: '#3554D1',
                  cursor: isEnquiryLoading ? 'not-allowed' : 'pointer',
                  opacity: isEnquiryLoading ? 0.7 : 1
                }}
              >
                {isEnquiryLoading ? (
                  <>Sending Enquiry... <span className="loading-dots"><span>.</span><span>.</span><span>.</span></span></>
                ) : (
                  "Make an Enquiry"
                )}
              </button>
            )}
          </Box>

        
        </Grid>

        <Grid item xs={12} lg={5}>
          <Box sx={{ pt: 3, px: 3, pb: 1 }}>
            <BookingDetails />
          </Box>
        </Grid>
      </Grid>
    </>
  );
});

export default CustomerInfo;


