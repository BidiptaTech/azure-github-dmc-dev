import { useState, useEffect, useRef } from "react";
import {
  TextField,
  InputAdornment,
  Button,
  Grid,
  Box,
  Typography,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
} from "@mui/material";
import { toast } from "react-toastify";
import BookingDetails from "./sidebar/BookingDetails";
import {
  guideslice,
  setbookingtype,
  setData,
} from "@/slice/tourguide/guideslice";
import {
  Localtourslice,
  setpointdata,
  sethourlydata,
  setbookingtype3,
} from "@/slice/localtour/Localslice";
import {
  submitPickupDrop,
  setentrydata,
  setexitdata,
  setResponse,
  setbookingtype1,
} from "@/slice/port/pickupDropSlice";
import { setDateService } from "@/slice/common/dateServicesSlice";
import { setBookingType } from "@/slice/common/commonSlice";
import { useSelector } from "react-redux";
import {
  selectUserInfo,
  setUserInfo,
  setBookingResponse,
} from "../../../slice/common/customerInfo";
import { setIsNavigating } from "@/slice/common/commonSlice";

const countryCodes = [
  { code: "US", name: "United States", dialCode: "+1" },
  { code: "GB", name: "United Kingdom", dialCode: "+44" },
  { code: "IN", name: "India", dialCode: "+91" },
  { code: "AU", name: "Australia", dialCode: "+61" },
  { code: "SG", name: "Singapore", dialCode: "+65" },
  // Add more country codes as needed
];

const CustomerInfo = ({
  image,
  bookingDetails,
  onFormChange,
  setFormValid,
  formData,
  enquiryAmount: propEnquiryAmount,
  formValid,
  dispatch,
  navigate,
  type,
  totalPrice,
}) => {
  const [form, setForm] = useState({
    fullName: "",
    email: "",
    phone: "",
    address1: "",
    address2: "",
    state: "",
    zip: "",
    specialRequests: "",
    countryCode: "+1", // Default to the first country code
  });

  // State for selected country and dynamic validation
  const [selectedCountry, setSelectedCountry] = useState(null);
  const [dialMinLength, setDialMinLength] = useState(8);
  const [dialMaxLength, setDialMaxLength] = useState(15);

  const countryCodeFromRedux = useSelector((state) => state.auth.countryCode);
  // const dialMaxLength = useSelector((state) => state.auth.dialMaxLength);
  // const dialMinLength = useSelector((state) => state.auth.dialMinLength);
  const user_country = useSelector((state) => state.auth.user_country);
  console.log("user_country", user_country);
  console.log("countryCode from Redux:", countryCodeFromRedux);
  // console.log("dialMaxLength from Redux:", dialMaxLength);
  // console.log("dialMinLength from Redux:", dialMinLength);
  const mode =
    type === "guide"
      ? useSelector((state) => state.tourguide.selectedGuide?.mode)
      : type === "entryport" || type === "exitport"
      ? useSelector((state) => state.pickupDrop.selectedVehicle?.mode)
      : type === "travelpoint" ||
        type === "travelhourly" ||
        type === "travelpointzone"
      ? useSelector((state) => state.localtour.selectedVehicle?.mode)
      : "dmc";
  console.log("mode88", mode);
  // Set the initial country code based on Redux state
  // Initialize selected country from user_country array
  useEffect(() => {
    if (user_country && user_country.length > 0) {
      // Set default to first country in the array
      const defaultCountry = user_country[0];
      setSelectedCountry(defaultCountry);
      setDialMinLength(defaultCountry.contact_min_length);
      setDialMaxLength(defaultCountry.contact_max_length);
      setForm((prevForm) => ({
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
      setDialMinLength(country.contact_min_length);
      setDialMaxLength(country.contact_max_length);
      setForm((prevForm) => ({
        ...prevForm,
        countryCode: country.country_code,
      }));
      
      // Update parent component
      onFormChange({ 
        ...form, 
        countryCode: country.country_code 
      });
      
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

  // Ensure hooks are called in the same order
  const entryport = useSelector((state) => state.pickupDrop.entryport[0] || {});
  console.log("entryport", entryport);
  const exitport = useSelector((state) => state.pickupDrop.exitport?.[0] || {});
  console.log("exitport", exitport);
  const travelPoint = useSelector(
    (state) => state.localtour.pointtopoint?.[0] || {}
  );
  console.log("travelPoint", travelPoint);
  const travelHourly = useSelector((state) => state.localtour.hourly[0] || {});
  console.log("travelHourly", travelHourly);
  const attraction = useSelector(
    (state) => state.attractions.services[0] || {}
  );
  console.log("attraction", attraction);
  const restaurant = useSelector(
    (state) => state.restaurants.services[0] || {}
  );
  console.log("restaurant", restaurant);
  const guide = useSelector((state) => state.tourguide.bookedguide[0] || {});
  console.log("guide", guide);
  const hotel = useSelector((state) => state.hotels.submitHotels[0] || {});
  console.log("hotel", hotel);

  const [errors, setErrors] = useState({});
  const [touched, setTouched] = useState({});
  const [showEnquiry, setShowEnquiry] = useState(false);
  const [commentError, setCommentError] = useState(false);
  const [isAutofilled, setIsAutofilled] = useState(false);

  // Refs to store previous values for logging
  const prevEntryportRef = useRef();
  const prevExitportRef = useRef();
  const prevAttractionRef = useRef();
  const prevRestaurantRef = useRef();
  const prevGuideRef = useRef();
  const prevHotelRef = useRef();
  const prevTravelPointRef = useRef();
  const prevTravelHourlyRef = useRef();

  // Initialize state with the prop value
  const [enquiryAmount, setEnquiryAmount] = useState(propEnquiryAmount);
  const [enquiryComment, setEnquiryComment] = useState("");
  // Add state for tracking button loading
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isEnquiring, setIsEnquiring] = useState(false);

  // Add an effect to update the enquiryAmount state when the prop changes
  // useEffect(() => {
  //   if (propEnquiryAmount) {
  //     setEnquiryAmount(propEnquiryAmount);

  //     // Update the form with enquiry amount
  //     setForm((prevForm) => ({
  //       ...prevForm,
  //       enquiryAmount: propEnquiryAmount,
  //     }));

  //     // Update parent component
  //     const updatedForm = {
  //       ...form,
  //       enquiryAmount: propEnquiryAmount,
  //     };
  //     onFormChange(updatedForm);
  //   }
  // }, [propEnquiryAmount]);

  // Function to validate a single field
  const validateField = (field, value) => {
    let error = "";

    switch (field) {
      case "fullName":
        if (!value.trim()) error = "Full Name is required";
        break;
      case "email":
        if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value))
          error = "Enter a valid email";
        break;
      case "phone":
        const phoneRegex = new RegExp(
          `^\\d{${dialMinLength},${dialMaxLength}}$`
        );
        if (!phoneRegex.test(value))
          error = `Enter a valid phone number (${dialMinLength}-${dialMaxLength} digits)`;
        break;
      case "address1":
        if (!value.trim()) error = "Address is required";
        break;
      // case "state":
      //   if (!value.trim()) error = "State is required";
      //   break;
      // case "zip":
      //   if (!/^\d{5,8}$/.test(value)) error = "Enter a valid ZIP code";
      //   break;
      default:
        break;
    }

    return error;
  };

  // Function to validate the whole form
  const validateForm = () => {
    return new Promise((resolve) => {
      const requiredFields = [
        "fullName",
        "email",
        "phone",
        "address1",
        "state",
        "zip",
      ];
      let newErrors = {};

      requiredFields.forEach((field) => {
        const error = validateField(field, form[field]);
        if (error) newErrors[field] = error;
      });

      setErrors(newErrors);

      // Form is valid if there are no errors
      const isFormValid = Object.keys(newErrors).length === 0;
      setFormValid(isFormValid);

      // Resolve with validation result
      resolve(isFormValid);
    });
  };

  // Function to handle input change
  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((prevForm) => ({
      ...prevForm,
      [name]: value,
    }));
    onFormChange({ ...form, [name]: value }); // Update parent component

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

  // Run validation only when the user modifies inputs
  // useEffect(() => {
  //   validateForm();
  // }, [form]);

  // Effect to autofill form values if any data source has data
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
  //       setForm((prevForm) => {
  //         const updatedForm = {
  //           ...prevForm,
  //           fullName: filledData.fullName || prevForm.fullName,
  //           email: filledData.email || prevForm.email,
  //           phone: filledData.phone || prevForm.phone,
  //           address1: filledData.address1 || prevForm.address1,
  //           address2: filledData.address2 || prevForm.address2,
  //           state: filledData.state || prevForm.state,
  //           zip: filledData.zip || prevForm.zip,
  //           countryCode: prevForm.countryCode, // Keep existing country code
  //           specialRequests: prevForm.specialRequests, // Keep existing special requests
  //         };

  //         // Call onFormChange with updated data
  //         onFormChange(updatedForm);

  //         return updatedForm;
  //       });

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
  // ]);

  // **NEW EFFECT**: Run validation after autofill updates form
  // useEffect(() => {
  //   if (isAutofilled) {
  //     validateForm().then((isValid) => {
  //       if (!isValid) {
  //         toast.error("Please fill in all required fields", {
  //           position: "top-center",
  //           autoClose: 5000,
  //         });
  //       }
  //     });
  //   }
  // }, [form, isAutofilled]);

  // Run validation when critical form fields change
  // useEffect(() => {
  //   validateForm().then((isValid) => {
  //     console.log("Form validation result:", isValid);
  //   });
  // }, [
  //   form.fullName,
  //   form.email,
  //   form.phone,
  //   form.address1,
  //   form.state,
  //   form.zip,
  // ]);

  // Run effect when any data source changes

  // Effect to log changes
  // useEffect(() => {
  //   if (entryport !== prevEntryportRef.current) {
  //     console.log("Entryport changed:", entryport);
  //     prevEntryportRef.current = entryport; // Update ref
  //   }
  //   if (exitport !== prevExitportRef.current) {
  //     console.log("Exitport changed:", exitport);
  //     prevExitportRef.current = exitport; // Update ref
  //   }
  //   if (attraction !== prevAttractionRef.current) {
  //     console.log("Attraction changed:", attraction);
  //     prevAttractionRef.current = attraction; // Update ref
  //   }
  //   if (restaurant !== prevRestaurantRef.current) {
  //     console.log("Restaurant changed:", restaurant);
  //     prevRestaurantRef.current = restaurant; // Update ref
  //   }
  //   if (guide !== prevGuideRef.current) {
  //     console.log("Guide changed:", guide);
  //     prevGuideRef.current = guide; // Update ref
  //   }
  //   if (hotel !== prevHotelRef.current) {
  //     console.log("Hotel changed:", hotel);
  //     prevHotelRef.current = hotel; // Update ref
  //   }
  //   if (travelPoint !== prevTravelPointRef.current) {
  //     console.log("Travel Point changed:", travelPoint);
  //     prevTravelPointRef.current = travelPoint; // Update ref
  //   }
  //   if (travelHourly !== prevTravelHourlyRef.current) {
  //     console.log("Travel Hourly changed:", travelHourly);
  //     prevTravelHourlyRef.current = travelHourly; // Update ref
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

  // New handleSubmit function
  const handleSubmit = async () => {
    try {
      // Validate the form and wait for the result
      const isValid = await validateForm();
      if (!isValid) {
        // Get list of fields with validation errors
        const errorFields = Object.entries(errors)
          .filter(([_, value]) => value)
          .map(([field, message]) => ({
            field: field.charAt(0).toUpperCase() + field.slice(1),
            message,
          }));

        // Show error toast
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
        return;
      }
      onFormChange({
        ...form,
        bookingType: "booking",
      });

      // Set navigation state to true before making the API call
      dispatch(setIsNavigating(true));

      let response = null;
      dispatch(setbookingtype("booking"));
      dispatch(setbookingtype3("booking"));
      dispatch(setbookingtype1("booking"));
      setIsSubmitting(true);
      // Determine the appropriate action based on `type`
      if (type === "guide") {
        response = await dispatch(guideslice()).unwrap();
      } else if (type === "entryport") {
        response = await dispatch(
          submitPickupDrop({ selectedType: "entry" })
        ).unwrap();
      } else if (type === "exitport") {
        response = await dispatch(
          submitPickupDrop({ selectedType: "exit" })
        ).unwrap();
      } else if (type === "travelpoint") {
        response = await dispatch(
          Localtourslice({ selectedType: "travelpoint" })
        ).unwrap();
      } else if (type === "travelhourly") {
        response = await dispatch(
          Localtourslice({ selectedType: "travelhourly" })
        ).unwrap();
      } else if (type === "travelpointzone") {
        response = await dispatch(
          Localtourslice({ selectedType: "travelpointzone" })
        ).unwrap();
      } else {
        console.error("Invalid booking type:", type);
        toast.error("Invalid booking type. Please try again.", {
          position: "top-center",
          autoClose: 3000,
        });
        setIsSubmitting(false);
        dispatch(setIsNavigating(false)); // Reset navigation state on error
        return;
      }

      // console.log("API Response:", response);

      // Dispatch the response only if it's valid
      if (response) {
        dispatch(setResponse(response));
        dispatch(setBookingType(response.order?.bookingType));

        // If the response contains a service date, update Redux state
        if (response?.service?.date_service) {
          dispatch(setDateService(response.service.date_service));
          // toast.success(response.message, { position: "top-center", autoClose: 3000 });
        }

        // Store user info and booking response - with safety checks
        if (response?.service?.data) {
          // Ensure data is in the expected format (array)
          const userData = Array.isArray(response.service.data) 
            ? response.service.data 
            : [response.service.data];
          
          dispatch(setUserInfo(userData));
        } else {
          console.warn("Response service data is missing or invalid:", response);
          dispatch(setUserInfo([])); // Set empty array as fallback
        }
        
        dispatch(setBookingResponse(response));

        // Navigate to the Thank You page after a short delay to ensure Redux updates
        setTimeout(() => {
          navigate("/dashboard/db-dashboard/ThankYou");
          // Reset navigation state after navigation completes
          setTimeout(() => {
            dispatch(setIsNavigating(false));
          }, 500);
        }, 500);
        setIsSubmitting(false);
      } else {
        dispatch(setIsNavigating(false)); // Reset navigation state on error
        throw new Error("Invalid response received from the server.");
      }
    } catch (error) {
      console.error("Error during submission:", error);
      dispatch(setIsNavigating(false)); // Reset navigation state on error
      setIsSubmitting(false);
      // Handle errors and show user-friendly messages
      toast.error(
        error.message || "Something went wrong. Please try again later.",
        {
          position: "top-center",
          autoClose: 3000,
        }
      );
    }
  };
  const handleEnquirySubmit = async () => {
    try {
      console.log("Starting enquiry submission...");

      // Set form data for enquiry
      onFormChange({
        ...form,
        bookingType: "enquiry",
      });

      // Set navigation state to true before making the API call
      dispatch(setIsNavigating(true));

      // Set booking types
      dispatch(setbookingtype("enquiry"));
      dispatch(setbookingtype3("enquiry"));
      dispatch(setbookingtype1("enquiry"));
      setIsEnquiring(true);
      let response = null;

      // Dispatch the appropriate action based on type
      try {
        console.log(`Processing ${type} type submission`);

        if (type === "guide") {
          response = await dispatch(guideslice()).unwrap();
        } else if (type === "entryport") {
          response = await dispatch(
            submitPickupDrop({ selectedType: "entry" })
          ).unwrap();
        } else if (type === "exitport") {
          response = await dispatch(
            submitPickupDrop({ selectedType: "exit" })
          ).unwrap();
        } else if (type === "travelpoint") {
          response = await dispatch(
            Localtourslice({ selectedType: "travelpoint" })
          ).unwrap();
        } else if (type === "travelhourly") {
          response = await dispatch(
            Localtourslice({ selectedType: "travelhourly" })
          ).unwrap();
        } else if (type === "travelpointzone") {
          response = await dispatch(
            Localtourslice({ selectedType: "travelpointzone" })
          ).unwrap();
        } else {
          console.error("Invalid booking type:", type);
          toast.error("Invalid booking type. Please try again.", {
            position: "top-center",
            autoClose: 3000,
          });
          dispatch(setIsNavigating(false)); // Reset navigation state on error
          setIsEnquiring(false);
          return;
        }

        console.log("API Response received:", response);
      } catch (dispatchError) {
        console.error("Error dispatching action:", dispatchError);
        dispatch(setIsNavigating(false)); // Reset navigation state on error
        toast.error(`Failed to submit ${type}. Please try again.`, {
          position: "top-center",
          autoClose: 3000,
        });
        setIsEnquiring(false);
        return;
      }

      // Check if we have a valid response
      if (!response) {
        dispatch(setIsNavigating(false)); // Reset navigation state on error
        throw new Error("No response received from the server");
      }

      console.log("Processing API Response:", response);

      // Set response in Redux
      dispatch(setResponse(response));

      // Set booking type
      if (response.order && response.order.bookingType) {
        dispatch(setBookingType(response.order.bookingType));
      }

      // Set date service if available
      if (response.service && response.service.date_service) {
        dispatch(setDateService(response.service.date_service));
      }

      // Handle user info data safely
      if (response.service && response.service.data) {
        try {
          // Check if data is an array or object
          const userData = Array.isArray(response.service.data)
            ? response.service.data[0]
            : response.service.data;

          // Make sure userData is an object before dispatching
          if (userData && typeof userData === "object") {
            dispatch(setUserInfo(userData));
          } else {
            console.warn("Invalid user data format:", userData);
          }
        } catch (userInfoError) {
          console.error("Error processing user info:", userInfoError);
        }
      }

      // Set booking response
      dispatch(setBookingResponse(response));
      setIsEnquiring(false);
      // Show success message
      toast.success("Enquiry submitted successfully!", {
        position: "top-center",
        autoClose: 3000,
      });

      console.log("Navigating to Thank You page...");
      // Navigate to the Thank You page
      setTimeout(() => {
        navigate("/dashboard/db-dashboard/ThankYou");
        // Reset navigation state after navigation completes
        setTimeout(() => {
          dispatch(setIsNavigating(false));
        }, 500);
      }, 500);
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
      setIsEnquiring(false);
    }
  };
  // Update the handleEnquiryAmountChange function
  // const handleEnquiryAmountChange = (e) => {
  //   const newValue = parseFloat(e.target.value);
  //   if (newValue <= propEnquiryAmount && newValue > 0) {
  //     setEnquiryAmount(newValue);

  //     // Update form state
  //     const updatedForm = {
  //       ...form,
  //       enquiryAmount: newValue,
  //     };
  //     setForm(updatedForm);

  //     // Update parent component
  //     onFormChange(updatedForm);
  //   }
  // };

  // Update the handleEnquiryCommentChange function
  // const handleEnquiryCommentChange = (e) => {
  //   const newValue = e.target.value;
  //   setEnquiryComment(newValue);

  //   // Update the form with the new enquiry comment
  //   const updatedForm = {
  //     ...form,
  //     enquiryComment: newValue,
  //   };

  //   setForm(updatedForm);
  //   onFormChange(updatedForm); // Update parent component with form + enquiry data

  //   // If there's a separate handler for the parent component
  //   // if (typeof onEnquiryCommentChange === "function") {
  //   //   onEnquiryCommentChange(newValue);
  //   // }

  //   setCommentError(false);
  // };

  return (
    <>
      <div className="col-xl-7 col-lg-8 mt-0 align-self-start">
        <h2 className="text-22 fw-500 mt-40 md:mt-24">
          Let us know who you are
        </h2>
        <div className="row x-gap-20 y-gap-20 pt-20">
          <div className="col-12">
            <TextField
              label="Full Name"
              name="fullName"
              fullWidth
              variant="outlined"
              value={form.fullName}
              onChange={handleChange}
              onBlur={handleBlur}
              error={!!errors.fullName && touched.fullName}
              helperText={touched.fullName && errors.fullName}
            />
          </div>

          <div className="col-md-6">
            <TextField
              label="Email"
              name="email"
              fullWidth
              variant="outlined"
              value={form.email}
              onChange={handleChange}
              onBlur={handleBlur}
              error={!!errors.email && touched.email}
              helperText={touched.email && errors.email}
            />
          </div>

          <div className="col-md-6">
            <TextField
              label="Phone Number"
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
          </div>

          <div className="col-12">
            <TextField
              label="Address Line 1"
              name="address1"
              fullWidth
              variant="outlined"
              value={form.address1}
              onChange={handleChange}
              onBlur={handleBlur}
              error={!!errors.address1 && touched.address1}
              helperText={touched.address1 && errors.address1}
            />
          </div>

          <div className="col-12">
            <TextField
              label="Address Line 2"
              name="address2"
              fullWidth
              variant="outlined"
              value={form.address2}
              onChange={handleChange}
            />
          </div>

          <div className="col-md-6">
            <TextField
              label="State/Province/Region"
              name="state"
              fullWidth
              variant="outlined"
              value={form.state}
              onChange={handleChange}
              onBlur={handleBlur}
              error={!!errors.state && touched.state}
              helperText={touched.state && errors.state}
            />
          </div>

          <div className="col-md-6">
            <TextField
              label="ZIP Code/Postal Code"
              name="zip"
              fullWidth
              variant="outlined"
              value={form.zip}
              onChange={handleChange}
              onBlur={handleBlur}
              error={!!errors.zip && touched.zip}
              helperText={touched.zip && errors.zip}
            />
          </div>

          <div className="col-12">
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
          </div>

          <div className="col-12">
            <Box
              sx={{
                display: "flex",
                justifyContent: "flex-start",
                gap: "10px",
                mt: 3,
                mb: 2,
              }}
            >
              {/* Submit button - show for all modes */}
              <button
                className="button h-60 px-24 -dark-1 bg-blue-1 text-white"
                onClick={handleSubmit}
                disabled={isSubmitting}
              >
                {isSubmitting ? "Booking..." : "Book Now"}
                <div className="icon-arrow-top-right ml-15" />
              </button>

              {/* Make an Enquiry button - only show if mode is "dmc" */}
              {mode === "dmc" && (
                <button
                  className="button h-60 px-24 -dark-1 bg-blue-1 text-white"
                  onClick={() => {
                    handleEnquirySubmit();
                  }}
                  disabled={isEnquiring}
                >
                  {isEnquiring ? "Enquiring..." : "Make an Enquiry"}
                </button>
              )}
            </Box>
          </div>
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
                    <Typography sx={{ mb: 1 }}>Negotiated Amount</Typography>
                    <TextField
                      fullWidth
                      type="number"
                      value={enquiryAmount}
                      onChange={(e) => handleEnquiryAmountChange(e)}
                      InputProps={{
                        inputProps: {
                          max: bookingDetails[0]?.totalPrice,
                          min: 1,
                        },
                      }}
                      helperText={`Maximum amount: $${bookingDetails[0]?.totalPrice}`}
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
                      helperText={commentError ? "Comment is required" : ""}
                      placeholder="Enter your comment"
                    />
                  </Grid>
                  <Grid item xs={12}>
                    <Box sx={{ display: "flex", justifyContent: "flex-end" }}>
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
        </div>
      </div>

      {/* Booking Details Sidebar */}
      <div className="col-xl-5 col-lg-4 mt-30">
        <div className="booking-sidebar">
          <BookingDetails image={image} bookingDetails={bookingDetails} />
        </div>
      </div>
    </>
  );
};

export default CustomerInfo;
