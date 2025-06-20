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
const countryCodes = [
  { code: "US", name: "United States", dialCode: "+1" },
  { code: "AE", name: "United Arab Emirates", dialCode: "+971" },
  { code: "IN", name: "India", dialCode: "+91" },
  { code: "SG", name: "Singapore", dialCode: "+65" },
  { code: "VN", name: "Vietnam", dialCode: "+84" },
  { code: "MY", name: "Malaysia", dialCode: "+60" },
  { code: "PH", name: "Philippines", dialCode: "+63" },
  { code: "ID", name: "Indonesia", dialCode: "+62" },
  { code: "TH", name: "Thailand", dialCode: "+66" },
  { code: "HK", name: "Hong Kong", dialCode: "+852" },
  { code: "MO", name: "Macau", dialCode: "+853" },
  { code: "JP", name: "Japan", dialCode: "+81" },
  { code: "GB", name: "United Kingdom", dialCode: "+44" },
  { code: "AU", name: "Australia", dialCode: "+61" },
  // Add more country codes as needed
];

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
      // enquiryAmount,
      // enquiryComment,
      // onEnquiryAmountChange,
      // onEnquiryCommentChange,
    },
    ref
  ) => {
    const dispatch = useDispatch();
    const searchLocation = useSelector(
      (state) => state.bookings.searchLocation
    );
    const navigate = useNavigate();

    // Get country code and dial length details from Redux auth state
    const countryCodeFromRedux = useSelector((state) => state.auth.countryCode);
    const dialMaxLength = useSelector((state) => state.auth.dialMaxLength) || 15; // Default to 15 if not available
    const dialMinLength = useSelector((state) => state.auth.dialMinLength) || 7;  // Default to 7 if not available
    console.log("countryCode from Redux:", countryCodeFromRedux);
    console.log("dialMaxLength from Redux:", dialMaxLength);
    console.log("dialMinLength from Redux:", dialMinLength);

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

    // Get customer information from various Redux slices
    // const entryport = useSelector((state) => state.pickupDrop?.entryport?.[0] || {});
    // const exitport = useSelector((state) => state.pickupDrop?.exitport?.[0] || {});
    // const travelPoint = useSelector((state) => state.localtour?.pointtopoint?.[0] || {});
    // const travelHourly = useSelector((state) => state.localtour?.hourly?.[0] || {});
    // const attraction = useSelector((state) => state.attractions?.services?.[0] || {});
    // const restaurant = useSelector((state) => state.restaurants?.services?.[0] || {});
    // const guide = useSelector((state) => state.tourguide?.bookedguide?.[0] || {});
    // const hotel = useSelector((state) => state.hotels?.submitHotels?.[0] || {});

    // Refs to track previous values
    // const prevEntryportRef = useRef();
    // const prevExitportRef = useRef();
    // const prevAttractionRef = useRef();
    // const prevRestaurantRef = useRef();
    // const prevGuideRef = useRef();
    // const prevHotelRef = useRef();
    // const prevTravelPointRef = useRef();
    // const prevTravelHourlyRef = useRef();

    // Calculate converted prices
    // const convertedTotalPrice = totalPrice * (priceMode === "dmc" ? exchangeRate : usdExchangeRate);
    // const usdTotalPrice = totalPrice * usdExchangeRate;

    // Add this effect to automatically set country code based on search location
    useEffect(() => {
      if (searchLocation && searchLocation[0]) {
        const country = countryCodes.find(
          (c) => c.code.toLowerCase() === searchLocation[0].toLowerCase()
        );
        if (country) {
          setForm((prev) => ({ ...prev, countryCode: country.dialCode }));
          // Also update localStorage
          localStorage.setItem(
            "lastHotelUserInfo",
            JSON.stringify({
              ...form,
              countryCode: country.dialCode,
            })
          );
        }
      }
    }, [searchLocation]); // Add searchLocation as dependency

    // Also update the autofill effect to include countryCode
    // useEffect(() => {
    //   if (!isAutofilled) {
    //     const dataSources = [
    //       entryport,
    //       exitport,
    //       attraction,
    //       restaurant,
    //       guide,
    //       hotel,
    //       travelPoint,
    //       travelHourly,
    //     ];

    //     const filledData = dataSources.find(
    //       (data) => data && Object.keys(data).length > 0
    //     );

    //     if (filledData) {
    //       setForm((prevForm) => ({
    //         ...prevForm,
    //         fullName: filledData.fullName || prevForm.fullName,
    //         email: filledData.email || prevForm.email,
    //         phone: filledData.phone || prevForm.phone,
    //         countryCode: filledData.countryCode || prevForm.countryCode,
    //         address1: filledData.address1 || prevForm.address1,
    //         address2: filledData.address2 || prevForm.address2,
    //         state: filledData.state || prevForm.state,
    //         zip: filledData.zip || prevForm.zip,
    //         specialRequests: filledData.specialRequests || prevForm.specialRequests,
    //       }));
    //       setIsAutofilled(true);
    //     }
    //   }
    // }, [
    //   entryport,
    //   exitport,
    //   attraction,
    //   restaurant,
    //   guide,
    //   hotel,
    //   travelPoint,
    //   travelHourly,
    //   isAutofilled,
    // ]);

    // Effect to log changes for debugging
    // useEffect(() => {
    //   if (entryport !== prevEntryportRef.current) {
    //     console.log("Entryport changed:", entryport);
    //     prevEntryportRef.current = entryport;
    //   }
    //   if (exitport !== prevExitportRef.current) {
    //     console.log("Exitport changed:", exitport);
    //     prevExitportRef.current = exitport;
    //   }
    //   if (attraction !== prevAttractionRef.current) {
    //     console.log("Attraction changed:", attraction);
    //     prevAttractionRef.current = attraction;
    //   }
    //   if (restaurant !== prevRestaurantRef.current) {
    //     console.log("Restaurant changed:", restaurant);
    //     prevRestaurantRef.current = restaurant;
    //   }
    //   if (guide !== prevGuideRef.current) {
    //     console.log("Guide changed:", guide);
    //     prevGuideRef.current = guide;
    //   }
    //   if (hotel !== prevHotelRef.current) {
    //     console.log("Hotel changed:", hotel);
    //     prevHotelRef.current = hotel;
    //   }
    //   if (travelPoint !== prevTravelPointRef.current) {
    //     console.log("Travel Point changed:", travelPoint);
    //     prevTravelPointRef.current = travelPoint;
    //   }
    //   if (travelHourly !== prevTravelHourlyRef.current) {
    //     console.log("Travel Hourly changed:", travelHourly);
    //     prevTravelHourlyRef.current = travelHourly;
    //   }
    // }, [
    //   entryport,
    //   exitport,
    //   attraction,
    //   restaurant,
    //   guide,
    //   hotel,
    //   travelPoint,
    //   travelHourly,
    // ]);

    // Set the initial country code based on Redux state, similar to activity-single CustomerInfo
    useEffect(() => {
      const selectedCountry = countryCodes.find(
        (country) => country.code === countryCodeFromRedux
      );
      if (selectedCountry) {
        setForm((prevForm) => {
          const updatedForm = {
            ...prevForm,
            countryCode: selectedCountry.dialCode, // Set the dial code based on Redux state
          };
          
          // Also update localStorage
          localStorage.setItem(
            "lastHotelUserInfo",
            JSON.stringify(updatedForm)
          );
          
          return updatedForm;
        });
      }
    }, [countryCodeFromRedux]); // Run effect when countryCodeFromRedux changes

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
          // Use the dialMinLength and dialMaxLength from Redux for validation
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
        parentHandleSubmit();
      } catch (error) {
        console.error("Error during submission:", error);
        toast.error("Something went wrong. Please try again later.", {
          position: "top-center",
          autoClose: 3000,
        });
      }
    };

    // Add effect to update Redux store when form changes
    // useEffect(() => {
    //   if (form.fullName) {
    //     // Only update if form has some data
    //     dispatch(setUserInfo(form));
    //   }
    // }, [form, dispatch]);

    // Add handlers for enquiry input
    // const handleEnquiryAmountChange = (e) => {
    //   const newValue = parseFloat(e.target.value);
    //   if (newValue <= totalPrice && newValue > 0) {
    //     onEnquiryAmountChange(newValue);
    //   }
    // };

    // const handleEnquiryCommentChange = (e) => {
    //   onEnquiryCommentChange(e.target.value);
    //   setCommentError(false);
    // };

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
                         <span>{form.countryCode}</span>
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
                    >
                       Book Now
                      <div className="icon-arrow-top-right ml-15" />
                    </button>
                    {(mode === "dmc" || (Array.isArray(mode) && mode[0] === "dmc")) && (
                      <button
                        className="button h-60 px-24 -dark-1 bg-blue-1 text-white"
                        onClick={() => handleEnquirySubmit()}
                        style={{ backgroundColor: "#3554D1" }}
                      >
                        Make an Enquiry
                      </button>
                    )}
                  </Box>
                </Grid>

                {/* Enquiry Form */}
                {/* {showEnquiry && (
                  <Grid item xs={12}>
                    <Box
                      sx={{
                        mt: 3,
                        p: 3,
                        backgroundColor: "#f8faff",
                        borderRadius: "8px",
                        border: "1px solid rgba(53, 84, 209, 0.1)",
                      }}
                    >
                      <Typography variant="h6" sx={{ mb: 3, color: "#3554D1" }}>
                        Enquiry Details
                      </Typography>
                      <Grid container spacing={3}>
                        <Grid item xs={12} md={6}>
                          <Typography sx={{ mb: 1 }}>
                            Negotiated Amount
                          </Typography>
                          <TextField
                            fullWidth
                            type="number"
                            value={enquiryAmount}
                            onChange={handleEnquiryAmountChange}
                            InputProps={{
                              inputProps: {
                                max: totalPrice,
                                min: 1,
                              },
                            }}
                            helperText={`Maximum amount: $${totalPrice}`}
                          />
                        </Grid>
                        <Grid item xs={12}>
                          <Typography sx={{ mb: 1 }}>
                            Comment<span style={{ color: "red" }}> *</span>
                          </Typography>
                          <TextField
                            fullWidth
                            multiline
                            rows={4}
                            value={enquiryComment}
                            onChange={handleEnquiryCommentChange}
                            error={commentError}
                            helperText={
                              commentError ? "Comment is required" : ""
                            }
                            placeholder="Enter your comment"
                          />
                        </Grid>
                        <Grid item xs={12}>
                          <Box
                            sx={{ display: "flex", justifyContent: "flex-end" }}
                          >
                            <button
                              className="button h-50 px-24 -dark-1 bg-blue-1 text-white"
                              onClick={() => {
                                if (!enquiryComment.trim()) {
                                  setCommentError(true);
                                  toast.error("Please enter a comment");
                                  return;
                                }
                                handleEnquirySubmit();
                              }}
                            >
                              Submit Enquiry
                            </button>
                          </Box>
                        </Grid>
                      </Grid>
                    </Box>
                  </Grid>
                )} */}
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
