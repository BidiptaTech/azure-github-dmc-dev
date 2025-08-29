import { Link } from "react-router-dom";
import BookingDetails from "./sidebar/BookingDetails";
import {
  useState,
  forwardRef,
  useImperativeHandle,
  useEffect,
  useRef,
} from "react";
import {
  TextField,
  Grid,
  Box,
  Typography,
  FormControl,
  Select,
  MenuItem,
  InputAdornment,
} from "@mui/material";
import { useSelector, useDispatch } from "react-redux";
import {
  setUserInfo,
  setBookingResponse,
} from "../../slice/common/customerInfo";
import { toast } from "react-toastify";
import Cookies from "js-cookie";
import { useNavigate } from "react-router-dom";

const CustomerInfo = forwardRef(
  (
    {
      roomDetails,
      hotelDetails,
      tourDetails,
      totalPrice,
      responseData,
      onFormChange,
      priceMode,
      handleSubmit: parentHandleSubmit,
      handleEnquirySubmit,
      isEnquirySubmitting,
      isSubmitting,
    },
    ref
  ) => {
    const dispatch = useDispatch();
    const searchLocation = useSelector(
      (state) => state.bookings.searchLocation
    );
    const navigate = useNavigate();

    // Get user_country from auth slice (similar to activity-single)
    const user_country = useSelector((state) => state.auth.user_country);
    console.log("user_country", user_country);

    // State for selected country and dynamic validation (like activity-single)
    const [selectedCountry, setSelectedCountry] = useState(null);
    const [dialMinLength, setDialMinLength] = useState(8);
    const [dialMaxLength, setDialMaxLength] = useState(15);

    // Add this to get existing user info from Redux store
    const existingUserInfo = useSelector(
      (state) => state.customerInfo?.userInfo
    );

    const [form, setForm] = useState(() => {
      // First try to get data from Redux store
      if (existingUserInfo && Object.keys(existingUserInfo).length > 0) {
        return existingUserInfo;
      }

      // Then try localStorage
      const localStorageData = localStorage.getItem("lastHotelUserInfo");
      const parsedLocalData = localStorageData
        ? JSON.parse(localStorageData)
        : null;

      // Use stored data or default empty values
      return (
        parsedLocalData || {
          fullName: "",
          email: "",
          phone: "",
          countryCode: "+1",
          address1: "",
          address2: "",
          state: "",
          zip: "",
          specialRequests: "",
        }
      );
    });

    const [errors, setErrors] = useState({});
    const [touched, setTouched] = useState({});
    const [isAutofilled, setIsAutofilled] = useState(false);

    // Add state for enquiry form visibility
    const [showEnquiry, setShowEnquiry] = useState(false);
    const [commentError, setCommentError] = useState(false);
    


    // Get currency information from Redux store
    const currencySymbol = useSelector((state) => state.auth.currencySymbol);
    const currencyCode = useSelector((state) => state.auth.currencyCode);
    const exchangeRate = useSelector((state) => state.auth.exchangeRate);
    const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate);
    const usdCurrencySymbol = useSelector(
      (state) => state.auth.usdCurrencySymbol
    );
    const usdCurrencyCode = useSelector((state) => state.auth.usdCurrencyCode);
    const mode = useSelector((state) => state.category.priceMode);

    // Initialize selected country from user_country array (like activity-single)
    useEffect(() => {
      if (user_country && user_country.length > 0) {
        // Set default to first country in the array
        const defaultCountry = user_country[0];
        setSelectedCountry(defaultCountry);
        setDialMinLength(defaultCountry.contact_min_length);
        setDialMaxLength(defaultCountry.contact_max_length);
        setForm((prevForm) => {
          const updatedForm = {
            ...prevForm,
            countryCode: defaultCountry.country_code,
          };
          
          // Also update localStorage
          localStorage.setItem(
            "lastHotelUserInfo",
            JSON.stringify(updatedForm)
          );
          
          return updatedForm;
        });
      }
    }, [user_country]);

    // Handler for country selection (like activity-single)
    const handleCountryChange = (event) => {
      const selectedCountryCode = event.target.value;
      const country = user_country.find(c => c.code === selectedCountryCode);
      
      if (country) {
        setSelectedCountry(country);
        setDialMinLength(country.contact_min_length);
        setDialMaxLength(country.contact_max_length);
        const updatedForm = {
          ...form,
          countryCode: country.country_code,
        };
        setForm(updatedForm);
        
        // Update localStorage
        localStorage.setItem("lastHotelUserInfo", JSON.stringify(updatedForm));
        
        // Update parent component
        onFormChange(updatedForm);
        
        // Re-validate phone if it's already touched
        if (touched.phone && form.phone) {
          const phoneError = validateField('phone', form.phone);
          setErrors((prevErrors) => ({
            ...prevErrors,
            phone: phoneError,
          }));
        }
      }
    };

    // Add this effect to automatically set country code based on search location
    useEffect(() => {
      if (searchLocation && searchLocation[0] && user_country) {
        const country = user_country.find(
          (c) => c.code.toLowerCase() === searchLocation[0].toLowerCase()
        );
        if (country) {
          setSelectedCountry(country);
          setDialMinLength(country.contact_min_length);
          setDialMaxLength(country.contact_max_length);
          const updatedForm = {
            ...form,
            countryCode: country.country_code,
          };
          setForm(updatedForm);
          // Also update localStorage
          localStorage.setItem(
            "lastHotelUserInfo",
            JSON.stringify(updatedForm)
          );
        }
      }
    }, [searchLocation, user_country]);

    const validateField = (name, value) => {
      let error = "";
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
          // Use dynamic dial lengths from selected country (like activity-single)
          const phoneRegex = new RegExp(`^\\d{${dialMinLength},${dialMaxLength}}$`);
          if (!value) {
            error = "Phone number is required";
          } else if (!phoneRegex.test(value)) {
            error = `Please enter a valid phone number (${dialMinLength}-${dialMaxLength} digits)`;
          }
          break;

        case "address1":
          if (!value.trim()) {
            error = "Address is required";
          } else if (value.length < 5) {
            error = "Address must be at least 5 characters";
          }
          break;

        case "state":
          if (!value.trim()) {
            error = "State is required";
          }
          break;

        case "zip":
          // Less strict ZIP validation, similar to activity-single component
          if (value && !/^\d{5,8}$/.test(value)) {
            error = "Please enter a valid ZIP code (5-8 digits)";
          }
          break;

        default:
          break;
      }
      return error;
    };

    const handleChange = (e) => {
      const { name, value } = e.target;
      const updatedForm = { ...form, [name]: value };
      setForm(updatedForm);

      if (touched[name]) {
        const error = validateField(name, value);
        setErrors((prev) => ({ ...prev, [name]: error }));
      }

      // Save to localStorage whenever form changes
      localStorage.setItem("lastHotelUserInfo", JSON.stringify(updatedForm));

      onFormChange(updatedForm);
    };

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
          "state",
          "zip",
        ];
        const newErrors = {};
        let isValid = true;

        requiredFields.forEach((field) => {
          const error = validateField(field, form[field]);
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

    const handleSubmit = async () => {
      // Prevent multiple submissions
      if (isSubmitting) {
        return;
      }

      const isValid = ref.current.isFormValid();
      if (!isValid) {
        toast.error("Please fill in all required fields correctly.", {
          position: "top-center",
        });
        return;
      }

      try {
        // First dispatch user info to Redux
        dispatch(
          setUserInfo({
            ...form,
            phone: `${form.countryCode}${form.phone}`,
          })
        );

        // Simply call the parent's handleSubmit function
        // The parent component (index.jsx) already has all the necessary data
        await parentHandleSubmit();
      } catch (error) {
        console.error("Error during submission:", error);
        toast.error("Something went wrong. Please try again later.", {
          position: "top-center",
          autoClose: 3000,
        });
      }
    };

    return (
      <>
        <Grid container spacing={4}>
          <Grid item xs={12} lg={6}>
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
                    value={form.fullName}
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
                    value={form.email}
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
                    value={form.phone}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    error={touched.phone && Boolean(errors.phone)}
                    helperText={touched.phone && errors.phone}
                    required
                    InputProps={{
                      startAdornment: (
                        <InputAdornment position="start">
                          <FormControl 
                            variant="standard" 
                            sx={{ 
                              minWidth: 80,
                              marginRight: 1,
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
                              {user_country && user_country.map((country) => (
                                <MenuItem key={country.code} value={country.code}>
                                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                                    <span style={{ fontWeight: 'bold' }}>{country.country_code}</span>
                                    <span style={{ fontSize: '0.75rem', color: '#666' }}>({country.code})</span>
                                  </Box>
                                </MenuItem>
                              ))}
                            </Select>
                          </FormControl>
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
                    value={form.address1}
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
                    label="Address line 2"
                    name="address2"
                    value={form.address2}
                    onChange={handleChange}
                    onBlur={handleBlur}
                  />
                </Grid>

                <Grid item xs={12} md={6}>
                  <TextField
                    fullWidth
                    label="State/Province/Region"
                    name="state"
                    value={form.state}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    error={touched.state && Boolean(errors.state)}
                    helperText={touched.state && errors.state}
                    required
                  />
                </Grid>

                <Grid item xs={12} md={6}>
                  <TextField
                    fullWidth
                    label="ZIP code/Postal code"
                    name="zip"
                    value={form.zip}
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
                    value={form.specialRequests}
                    onChange={handleChange}
                    multiline
                    rows={4}
                  />
                </Grid>

                <Grid item xs={12}>
                  <Box
                    sx={{
                      display: "flex",
                      justifyContent: "flex-start",
                      gap: "15px",
                      mt: 2,
                    }}
                  >
                                         <button
                       className="button h-60 px-24 -dark-1 bg-blue-1 text-white"
                       onClick={handleSubmit}
                       disabled={isSubmitting || isEnquirySubmitting}
                       style={{ opacity: (isSubmitting || isEnquirySubmitting) ? 0.6 : 1, cursor: (isSubmitting || isEnquirySubmitting) ? 'not-allowed' : 'pointer' }}
                     >
                       {isSubmitting ? 'Processing...' : 'Book Now'}
                       <div className="icon-arrow-top-right ml-15" />
                     </button>
                     {(mode === "dmc" || (Array.isArray(mode) && mode[0] === "dmc")) && (
                       <button
                         className="button h-60 px-24 -dark-1 bg-blue-1 text-white"
                         onClick={() => handleEnquirySubmit()}
                         disabled={isSubmitting || isEnquirySubmitting}
                         style={{ 
                           backgroundColor: "#3554D1",
                           opacity: (isSubmitting || isEnquirySubmitting) ? 0.6 : 1, 
                           cursor: (isSubmitting || isEnquirySubmitting) ? 'not-allowed' : 'pointer' 
                         }}
                       >
                         {isEnquirySubmitting ? 'Processing...' : 'Make an Enquiry'}
                       </button>
                     )}
                  </Box>
                </Grid>
              </Grid>
            </Box>
          </Grid>

          <Grid item xs={12} lg={6}>
            <Box sx={{ pt: 3, px: 3, pb: 1 }}>
              <BookingDetails
                rooms={roomDetails}
                hotelDetails={hotelDetails}
                tourDetails={tourDetails}
                totalPrice={totalPrice}
                responseData={responseData}
              />
            </Box>
          </Grid>
        </Grid>
      </>
    );
  }
);

export default CustomerInfo;
