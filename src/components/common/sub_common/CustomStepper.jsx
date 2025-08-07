import React, { useMemo, useState } from "react";
import {
  Stepper,
  Step,
  StepLabel,
  styled,
  StepConnector,
  Box,
  Typography,
  Grid,
  Button,
  Snackbar,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  stepConnectorClasses,
} from "@mui/material";
//import { Modal } from "antd";
//import hotelIcon from "../../icons/hotel.png";
import hotelIcon from "../../../../public/icons/hotel.png";
//import car from "../../icons/car.png";
import car from "../../../../public/icons/car.png";
//import attractionIcon from "../../icons/tourist-attraction.png";
import attractionIcon from "../../../../public/icons/tourist-attraction.png";
import ArrivalDeparture from "../../../../public/icons/ArrivalDeparture.png";
//import ArrivalDeparture from "../../icons/ArrivalDeparture.png";
import restaurantIcon from "../../../../public/icons/restaurant.png";
//import restaurantIcon from "../../icons/restaurant.png";
import guideIcon from "../../../../public/icons/tour-guide.png";
import MuiAlert from "@mui/material/Alert";
import { toast, ToastContainer } from "react-toastify";
import swal from "sweetalert";
import {
  // skipStep,
  // setCurrentStep,
  // settourId, //resetStepsStatus,
  // setstepname,
  // updateStepStatus,
  statusUpdate,
  // resetStepsStatus,
  //setTourId,
  updateStepStatus,
  setType,
  //resetSteps,
} from "@/slice/common/stepsSlice";
import { updateServiceResponse } from "@/slice/common/stepperButtonSlice";
import { useDispatch, useSelector } from "react-redux";
import { useNavigate } from "react-router-dom"; // Import useNavigate from React Router
import ArrowBackIcon from "@mui/icons-material/ArrowBack";
import ArrowForwardIcon from "@mui/icons-material/ArrowForward";
import SkipNextIcon from "@mui/icons-material/SkipNext";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import DoneAllIcon from "@mui/icons-material/DoneAll";
import PlayArrowIcon from "@mui/icons-material/PlayArrow";
import FastForwardIcon from "@mui/icons-material/FastForward";
import RocketLaunchIcon from "@mui/icons-material/RocketLaunch";
import AutoAwesomeIcon from "@mui/icons-material/AutoAwesome";
import TrendingFlatIcon from "@mui/icons-material/TrendingFlat";
import KeyboardDoubleArrowRightIcon from "@mui/icons-material/KeyboardDoubleArrowRight";
import FlightTakeoffIcon from "@mui/icons-material/FlightTakeoff";
import DirectionsRunIcon from "@mui/icons-material/DirectionsRun";
import SpeedIcon from "@mui/icons-material/Speed";
import BoltIcon from "@mui/icons-material/Bolt";
import NavigateNextIcon from "@mui/icons-material/NavigateNext";
import LaunchIcon from "@mui/icons-material/Launch";
import SendIcon from "@mui/icons-material/Send";
import TelegramIcon from "@mui/icons-material/Telegram";

// Create the Alert component using MuiAlert
const Alert = React.forwardRef(function Alert(props, ref) {
  return (
    <MuiAlert
      elevation={6}
      ref={ref}
      variant="filled"
      {...props}
      sx={{
        borderRadius: "8px",
        boxShadow: "0 10px 25px rgba(0,0,0,0.1)",
        fontWeight: 500,
        fontSize: "0.95rem",
        alignItems: "center",
        ...props.sx,
      }}
    />
  );
});

const LoadingDots = () => {
  return (
    <span className="loading-dots">
      <span className="dot"></span>
      <span className="dot"></span>
      <span className="dot"></span>
      <style jsx>{`
        .loading-dots {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          margin-left: 8px;
        }
        .dot {
          width: 8px;
          height: 8px;
          margin: 0 2px;
          background-color: white;
          border-radius: 50%;
          display: inline-block;
          animation: dot-flashing 1s infinite alternate;
        }
        .dot:nth-child(1) {
          animation-delay: 0s;
        }
        .dot:nth-child(2) {
          animation-delay: 0.3s;
        }
        .dot:nth-child(3) {
          animation-delay: 0.6s;
        }
        @keyframes dot-flashing {
          0% {
            opacity: 0.2;
            transform: scale(0.8);
          }
          100% {
            opacity: 1;
            transform: scale(1);
          }
        }
      `}</style>
    </span>
  );
};

export default function CustomStepper() {
  //const location = useLocation(); // Get current location
  const id = useSelector((state) => state.hotels.id);
  // const stepStatus = useSelector(
  //   (state) => state.steps.responses?.[0]?.data || {}
  // );
  const { currentStep, stepStatus1, active_status } = useSelector(
    (state) => state.steps
  );
  
  // Get stepper button visibility state
  const { buttonVisibility } = useSelector((state) => state.stepperButton);

  const steps = useMemo(
    () => [
      {
        label: "Hotels",
        path: `/dashboard/db-dashboard/view-hotel-search/${id}`,
        key: "hotel",
      },
      {
        label: "Entry/Exit Port",
        path: "/dashboard/db-dashboard/pickupdrop",
        key: "port",
      },
      {
        label: "Attractions & Experiences",
        path: "/dashboard/db-dashboard/attractions",
        key: "attraction",
      },
      {
        label: "Tour Guide",
        path: "/dashboard/db-dashboard/tourguide",
        key: "guide",
      },
      {
        label: "Restaurants",
        path: "/dashboard/db-dashboard/restaurants",
        key: "restaurent",
      },
      {
        label: "Local Transfer",
        path: "/dashboard/db-dashboard/localtransfer",
        key: "travel",
      },
    ],
    [id]
  ); // Dependency array: only recalculate if 'id' changes

  const stepIcons = {
    1: hotelIcon,
    2: ArrivalDeparture,
    3: attractionIcon,
    4: guideIcon,
    5: restaurantIcon,
    6: car,
  };
  // const level = [
  //   "hotel",
  //   "port",
  //   "attraction",
  //   "transfer",
  //   "guide",
  //   "restaurant",
  // ];

  const CustomConnector = styled(StepConnector)(({ theme }) => ({
    // Hide the default connector since we're using custom ones
    display: "none !important",
  }));

  const StepIcon = ({ status, icon }) => {
    // console.log(`Icon Key: ${icon}, Status: ${status}`); // Debugging log
    const isSkipped = status === 1;
    const isActive = status === 2;
    const isCompleted = status === 3;

    const borderColor = isSkipped
      ? "#ff5252"
      : isActive
      ? "#ffa726"
      : isCompleted
      ? "#4caf50"
      : "#e0e0e0";

    const backgroundColor = isSkipped
      ? "rgba(255, 82, 82, 0.1)"
      : isActive
      ? "rgba(255, 167, 38, 0.1)"
      : isCompleted
      ? "rgba(76, 175, 80, 0.1)"
      : "white";

    const iconContainerStyle = {
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
      borderRadius: "50%",
      width: "56px",
      height: "56px",
      border: `2.5px solid ${borderColor}`,
      transition: "all 0.3s cubic-bezier(0.4, 0, 0.2, 1)",
      backgroundColor,
      position: "relative",
      zIndex: 2,
      boxShadow:
        isSkipped || isActive || isCompleted
          ? `0 4px 10px rgba(0, 0, 0, 0.1), 0 0 0 4px rgba(${
              isSkipped
                ? "255, 82, 82"
                : isActive
                ? "255, 167, 38"
                : "76, 175, 80"
            }, 0.1)`
          : "none",
    };

    const iconStyle = {
      filter: status === 0 ? "grayscale(100%)" : "none",
      height: "32px",
      width: "32px",
      opacity: status === 0 ? 0.5 : 1,
      transition: "all 0.3s ease",
    };

    return (
      <div style={iconContainerStyle}>
        <img src={stepIcons[icon]} alt={`Step ${icon}`} style={iconStyle} />
      </div>
    );
  };

  // const [activeStep, setActiveStep] = React.useState(0); // Start with first step selected
  const [openSnackbar, setOpenSnackbar] = React.useState(false); // Snackbar state
  const [snackbarMessage, setSnackbarMessage] = React.useState(""); // Message for the Snackbar
  const [isTourCompleted, setIsTourCompleted] = React.useState(false); // Track if the tour is finished
  // const [skippedSteps, setSkippedSteps] = React.useState([]);
  const dispatch = useDispatch();
  const [isModalVisible, setIsModalVisible] = useState(false);
  const [isModalVisible1, setIsModalVisible1] = useState(false);
  const [isModalVisible2, setIsModalVisible2] = useState(false);
  const navigate = useNavigate(); // React Router navigation hook

  //const currentStep = useSelector((state) => state.steps.currentStep);
  //const response = useSelector((state) => state.steps.response);
  const formData = useSelector((state) => state.pickupDrop.formData);
  // console.log("snsd", formData);
  const formData1 = useSelector((state) => state.pickupDrop.formData1);
  // console.log("ndc", formData1);
  const travelPoint = useSelector((state) => state.localtour.pointtopoint);
  // console.log("pointtopoint", travelPoint);
  const travelHourly = useSelector((state) => state.localtour.hourly);
  // console.log("hourly", travelHourly);
  const guide = useSelector((state) => state.tourguide.bookedguide);
  // console.log("guidesss", guide);

  // console.log("Steps:", steps);
  // console.log("Current Step:", currentStep);

  const labelMapping = {
    entrypickup: "Pick Up Location",
    exitpickup: "Pick Up Location",
    entrydropoff: "Drop Off Location",
    pickupdate: "Date",
    exitpickupdate: "Date",
    entrytime: "Time",
    entrytime1: "Time",
    traveller0: "Traveller",
    traveller1: "Traveller",
    vehicletype: "Vehicle Type",
    vehicletype1: "Vehicle Type",
    hours: "Hourly Package",
  };
  const labelMapping1 = {
    guide_name: "Guide Name",
    entrypickup: "Pick Up Location",
    entrytime: "Time",
    adults: "Adult",
    children: "Children",
    hours: "Hourly Package",
    bookingDate: "Date",
    totalPrice: "Total Amount to be Paid",
  };

  const [isSkipping, setIsSkipping] = useState(false);
  const [isProgressing, setIsProgressing] = useState(false);
  const [isGoingBack, setIsGoingBack] = useState(false);

  const handleNext = async () => {
    // console.log("steps", currentStep);
    const currentStepKey = steps[currentStep].key;

    let shouldProceed = true;

    // Handle final step completion
    if (currentStepKey) {
      const willDelete = await swal({
        title: `Are you sure all of your ${steps[currentStep].label} booking is completed?`,
        icon: "warning",
        buttons: { cancel: "Cancel", confirm: "OK" },
        dangerMode: true,
      });
      if (!willDelete) return;

      setIsProgressing(true); // Start loading animation
      dispatch(updateStepStatus({ key: currentStepKey, status: 3 }));
    }

    if (!shouldProceed) return;

    dispatch(setType(null));
    dispatch(statusUpdate())
      .then(() => {
        const nextStepIndex = currentStep + 1;
        if (nextStepIndex < steps.length) {
          dispatch(
            updateStepStatus({ key: steps[nextStepIndex].key, status: 2 })
          );
          dispatch(setType(null));
          dispatch(statusUpdate())
            .then(() => {
              navigate(steps[nextStepIndex].path);
              setSnackbarMessage(`Step ${steps[currentStep].label} completed!`);
              setOpenSnackbar(true);
              setIsProgressing(false); // Stop loading animation on success
            })
            .catch(() => {
              setIsProgressing(false); // Stop loading animation on error
            });
        } else {
          // If last step, navigate to the dashboard
          dispatch(setType(null));
          dispatch(statusUpdate())
            .then(() => {
              navigate("/dashboard/db-dashboard");
              setIsTourCompleted(true);
              setSnackbarMessage("Tour completed!");
              setOpenSnackbar(true);
              setIsProgressing(false); // Stop loading animation on success
            })
            .catch(() => {
              setIsProgressing(false); // Stop loading animation on error
            });
        }
      })
      .catch(() => {
        setIsProgressing(false); // Stop loading animation on error
      });
  };

  const handleSkip = () => {
    //const currentStep = steps[currentIndex];
    //if (currentStep) {
    setIsSkipping(true); // Set loading state to true
    dispatch(updateStepStatus({ key: steps[currentStep].key, status: 1 })); // Mark current step as skipped (1)
    dispatch(setType(null));
    dispatch(statusUpdate())
      .then(() => {
        // Once statusUpdate is complete, proceed to next step
        const stepnext = currentStep + 1;
        const nextStep = steps[currentStep + 1];
        if (nextStep) {
          dispatch(updateStepStatus({ key: steps[stepnext].key, status: 2 })); // Activate next step (2)
          dispatch(setType(null));
          dispatch(statusUpdate())
            .then(() => {
              navigate(steps[stepnext].path);
              setIsSkipping(false); // Reset loading state
            })
            .catch(() => {
              setIsSkipping(false); // Reset loading state on error
            });
        } else {
          setIsSkipping(false); // Reset loading state if no next step
        }
      })
      .catch(() => {
        setIsSkipping(false); // Reset loading state on error
      });
  };

  const handleBack = () => {
    // console.log("stepStatus1:", stepStatus1);

    const prevStep = currentStep - 1;
    if (prevStep < 0) return; // Prevent going before first step

    const previousStep = steps[prevStep];
    if (previousStep) {
      // Get the previous step's status from stepStatus1
      const prevStatus = stepStatus1[previousStep.key] ?? null;

      // Set loading state
      setIsGoingBack(true);

      // Dispatch with the same status as before
      dispatch(updateStepStatus({ key: previousStep.key, status: prevStatus }));
      dispatch(setType("back"));
      dispatch(statusUpdate())
        .then(() => {
          navigate(previousStep.path);
          setIsGoingBack(false); // Stop loading animation on success
        })
        .catch(() => {
          setIsGoingBack(false); // Stop loading animation on error
        });
    }
  };

  const handleCloseSnackbar = () => {
    setOpenSnackbar(false); // Close Snackbar
  };

  // Determine which buttons to show based on current step and booking responses
  const getCurrentStepKey = () => {
    if (currentStep >= 0 && currentStep < steps.length) {
      return steps[currentStep].key;
    }
    return null;
  };

  const currentStepKey = getCurrentStepKey();
  const currentStepButtonVisibility = buttonVisibility[currentStepKey] || { showSkip: true, showNext: false };
  
  // Special handling for the last step (Local Transfer) - show only Finish button
  const isLastStep = currentStep === steps.length - 1;
  
  // For the last step, show only the Finish button (which uses the Next button logic)
  // For other steps, use the normal logic
  const shouldShowSkip = isLastStep ? false : (currentStepButtonVisibility.showSkip || (!currentStepButtonVisibility.showSkip && !currentStepButtonVisibility.showNext));
  const shouldShowNext = isLastStep ? true : currentStepButtonVisibility.showNext;

  return (
    <>
      <Box
        sx={{
          width: "100%",
          padding: { xs: "20px", sm: "30px" },
          borderRadius: "24px",
          background: "linear-gradient(145deg, #ffffff, #f8f9fa)",
          boxShadow:
            "rgba(50, 50, 93, 0.1) 0px 13px 27px -5px, rgba(0, 0, 0, 0.05) 0px 8px 16px -8px",
          mb: 4,
        }}
      >
        <Typography
          variant="h5"
          align="center"
          gutterBottom
          sx={{
            fontWeight: "700",
            color: "#2c3e50",
            mb: 4,
            position: "relative",
            letterSpacing: "0.5px",
            "&:after": {
              content: '""',
              position: "absolute",
              width: "80px",
              height: "4px",
              borderRadius: "4px",
              background: "linear-gradient(90deg, #4caf50, #2196f3)",
              bottom: "-12px",
              left: "50%",
              transform: "translateX(-50%)",
            },
          }}
        >
          Travel Planner
        </Typography>
        <div className="stepper-wrapper">
          <Stepper
            alternativeLabel
            connector={<CustomConnector />}
            sx={{ position: "relative" }}
          >
            {steps.map(({ label, path, key }, index) => {
              const status = parseInt(stepStatus1[key] || 0, 10);
              const isCompleted = status === 3;
              const isActive = status >= 2;

              const lastActiveStepIndex = steps.reduce(
                (lastIndex, step, stepIndex) => {
                  const stepStatus = parseInt(stepStatus1[step.key] || 0, 10);
                  return stepStatus >= 2 ? stepIndex : lastIndex;
                },
                -1
              );

              return (
                <Step
                  key={path}
                  completed={index <= lastActiveStepIndex}
                  active={index === lastActiveStepIndex}
                  sx={{
                    position: 'relative',
                    // Custom connector line for each step
                    '&:before': index > 0 ? {
                      content: '""',
                      position: 'absolute',
                      top: '28px',
                      left: 'calc(-50% + 28px)',
                      right: 'calc(50% + 28px)',
                      height: '4px',
                      background: parseInt(stepStatus1[steps[index - 1].key] || 0, 10) === 1 
                        ? 'linear-gradient(90deg, #ff5252 0%, #ff1744 100%)' 
                        : parseInt(stepStatus1[steps[index - 1].key] || 0, 10) === 3 
                        ? 'linear-gradient(90deg, #4caf50 0%, #2e7d32 100%)' 
                        : '#e0e0e0',
                      borderRadius: '4px',
                      zIndex: 1,
                      transition: 'all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1)',
                      boxShadow: parseInt(stepStatus1[steps[index - 1].key] || 0, 10) === 1 
                        ? '0 2px 8px rgba(255, 82, 82, 0.3)' 
                        : parseInt(stepStatus1[steps[index - 1].key] || 0, 10) === 3 
                        ? '0 2px 8px rgba(76, 175, 80, 0.3)' 
                        : 'none',
                      // Add animation for color changes
                      '&:hover': {
                        transform: 'scaleY(1.2)',
                      }
                    } : {}
                  }}
                >
                  <StepLabel
                    StepIconComponent={(props) => (
                      <StepIcon {...props} icon={index + 1} status={status} />
                    )}
                  >
                    {label}
                  </StepLabel>
                </Step>
              );
            })}
          </Stepper>
        </div>

        {/* Position buttons directly under their corresponding step icons */}
        <Box
          sx={{
            mt: 4,
            display: "grid",
            gridTemplateColumns: `repeat(${steps.length}, 1fr)`,
            gap: 1,
          }}
        >
          {/* First column: Back/Start button under Hotels */}
          <Box sx={{ display: "flex", justifyContent: "center" }}>
            <Button
              variant="contained"
              startIcon={
                !isGoingBack && <ArrowBackIcon sx={{ 
                  fontSize: "1.3rem",
                  filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
                }} />
              }
              onClick={handleBack}
              disabled={currentStep === 0 || isGoingBack}
              sx={{
                width: "auto",
                minWidth: isGoingBack ? "220px" : "120px",
                height: "50px",
                background:
                  currentStep === 0 || isGoingBack
                    ? "linear-gradient(135deg, #e0e0e0 0%, #d5d5d5 100%)"
                    : "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                color: "#fff",
                boxShadow:
                  currentStep === 0 || isGoingBack
                    ? "inset 0 2px 4px rgba(0,0,0,0.1)"
                    : "0 8px 32px rgba(102, 126, 234, 0.4), 0 4px 16px rgba(118, 75, 162, 0.3), inset 0 1px 0 rgba(255,255,255,0.2)",
                "&:hover": {
                  background:
                    currentStep === 0 || isGoingBack 
                      ? "linear-gradient(135deg, #e0e0e0 0%, #d5d5d5 100%)" 
                      : "linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%)",
                  boxShadow: currentStep === 0 || isGoingBack 
                    ? "inset 0 2px 4px rgba(0,0,0,0.1)"
                    : "0 12px 40px rgba(102, 126, 234, 0.5), 0 8px 24px rgba(118, 75, 162, 0.4), inset 0 1px 0 rgba(255,255,255,0.3)",
                  transform: currentStep === 0 || isGoingBack ? "none" : "translateY(-4px) scale(1.02)",
                },
                "&:active": {
                  transform: currentStep === 0 || isGoingBack ? "none" : "translateY(-2px) scale(1.01)",
                  boxShadow: currentStep === 0 || isGoingBack 
                    ? "inset 0 2px 4px rgba(0,0,0,0.1)"
                    : "0 6px 20px rgba(102, 126, 234, 0.4), 0 4px 12px rgba(118, 75, 162, 0.3)",
                },
                visibility: currentStep === 0 ? "hidden" : "visible",
                textTransform: "none",
                fontWeight: 700,
                px: 3,
                py: 1.5,
                borderRadius: "25px",
                transition: "all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)",
                fontSize: "1rem",
                border: "none",
                backdropFilter: "blur(20px)",
                letterSpacing: "0.8px",
                whiteSpace: "nowrap",
                position: "relative",
                overflow: "hidden",
                "&:before": {
                  content: '""',
                  position: "absolute",
                  top: 0,
                  left: "-100%",
                  width: "100%",
                  height: "100%",
                  background: "linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent)",
                  transition: "left 0.6s",
                },
                "&:hover:before": {
                  left: "100%",
                },
                "&:disabled": {
                  background: "linear-gradient(135deg, #e0e0e0 0%, #d5d5d5 100%)",
                  color: "#9e9e9e",
                  boxShadow: "inset 0 2px 4px rgba(0,0,0,0.1)",
                  "&:before": {
                    display: "none",
                  },
                },
              }}
            >
              {isGoingBack ? (
                <>
                  <TrendingFlatIcon sx={{ 
                    fontSize: "1.4rem", 
                    transform: "rotate(180deg)",
                    marginRight: "8px",
                    filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
                  }} />
                  Previous Service
                  <LoadingDots />
                </>
              ) : (
                <>
                  <AutoAwesomeIcon sx={{ 
                    fontSize: "1.1rem", 
                    marginRight: "4px",
                    filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
                  }} />
                  Back
                </>
              )}
            </Button>
          </Box>

          {/* Middle columns (empty spaces) */}
          {Array.from({ length: steps.length - 2 }).map((_, i) => (
            <Box key={i} />
          ))}

          {/* Last column: Skip and Next/Finish buttons placed together */}
          <Box
            sx={{
              gridColumn: steps.length,
              width: "100%",
              display: "flex",
              justifyContent: "center",
              alignItems: "center",
            }}
          >
            <Box
              sx={{
                display: "flex",
                flexDirection: "row",
                gap: 1.5,
                maxWidth: "95%",
              }}
            >
              {shouldShowSkip && (
                <Button
                  variant="contained"
                  startIcon={
                    !isSkipping && <FastForwardIcon sx={{ 
                      fontSize: "1.3rem",
                      filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
                    }} />
                  }
                  onClick={handleSkip}
                  disabled={currentStep === steps.length - 1 || isSkipping}
                sx={{
                  width: "auto",
                  minWidth: isSkipping ? "260px" : "120px",
                  height: "50px",
                  background:
                    currentStep === steps.length - 1 || isSkipping
                      ? "linear-gradient(135deg, #e0e0e0 0%, #d5d5d5 100%)"
                      : "linear-gradient(135deg, #ff9500 0%, #ff6b35 100%)",
                  color: "#fff",
                  boxShadow:
                    currentStep === steps.length - 1 || isSkipping
                      ? "inset 0 2px 4px rgba(0,0,0,0.1)"
                      : "0 8px 32px rgba(255, 149, 0, 0.4), 0 4px 16px rgba(255, 107, 53, 0.3), inset 0 1px 0 rgba(255,255,255,0.2)",
                  "&:hover": {
                    background:
                      currentStep === steps.length - 1 || isSkipping
                        ? "linear-gradient(135deg, #e0e0e0 0%, #d5d5d5 100%)"
                        : "linear-gradient(135deg, #e68900 0%, #e55a2b 100%)",
                    boxShadow: currentStep === steps.length - 1 || isSkipping
                      ? "inset 0 2px 4px rgba(0,0,0,0.1)"
                      : "0 12px 40px rgba(255, 149, 0, 0.5), 0 8px 24px rgba(255, 107, 53, 0.4), inset 0 1px 0 rgba(255,255,255,0.3)",
                    transform: currentStep === steps.length - 1 || isSkipping ? "none" : "translateY(-4px) scale(1.02)",
                  },
                  "&:active": {
                    transform: currentStep === steps.length - 1 || isSkipping ? "none" : "translateY(-2px) scale(1.01)",
                    boxShadow: currentStep === steps.length - 1 || isSkipping
                      ? "inset 0 2px 4px rgba(0,0,0,0.1)"
                      : "0 6px 20px rgba(255, 149, 0, 0.4), 0 4px 12px rgba(255, 107, 53, 0.3)",
                  },
                  textTransform: "none",
                  fontWeight: 700,
                  px: 3,
                  py: 1.5,
                  borderRadius: "25px",
                  fontSize: "1rem",
                  transition: "all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)",
                  border: "none",
                  backdropFilter: "blur(20px)",
                  letterSpacing: "0.8px",
                  whiteSpace: "nowrap",
                  position: "relative",
                  overflow: "hidden",
                  "&:before": {
                    content: '""',
                    position: "absolute",
                    top: 0,
                    left: "-100%",
                    width: "100%",
                    height: "100%",
                    background: "linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent)",
                    transition: "left 0.6s",
                  },
                  "&:hover:before": {
                    left: "100%",
                  },
                  "&:disabled": {
                    background: "linear-gradient(135deg, #e0e0e0 0%, #d5d5d5 100%)",
                    color: "#9e9e9e",
                    boxShadow: "inset 0 2px 4px rgba(0,0,0,0.1)",
                    "&:before": {
                      display: "none",
                    },
                  },
                }}
              >
                {isSkipping ? (
                  <>
                    <KeyboardDoubleArrowRightIcon sx={{ 
                      fontSize: "1.4rem", 
                      marginRight: "8px",
                      filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
                    }} />
                    Skipping Current Service
                    <LoadingDots />
                  </>
                ) : (
                  <>
                    <PlayArrowIcon sx={{ 
                      fontSize: "1.1rem", 
                      marginRight: "4px",
                      filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
                    }} />
                    Skip
                  </>
                )}
              </Button>
              )}

              {shouldShowNext && (
                <Button
                  variant="contained"
                  endIcon={
                    !isProgressing &&
                    (isTourCompleted ? (
                      <DoneAllIcon sx={{ 
                        fontSize: "1.3rem",
                        filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
                      }} />
                    ) : currentStep === steps.length - 1 ? (
                      <RocketLaunchIcon sx={{ 
                        fontSize: "1.3rem",
                        filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
                      }} />
                    ) : (
                      <SendIcon sx={{ 
                        fontSize: "1.3rem",
                        filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
                      }} />
                    ))
                  }
                  onClick={handleNext}
                  disabled={isTourCompleted || isProgressing}
                sx={{
                  width: "auto",
                  minWidth: isProgressing ? "220px" : "130px",
                  height: "50px",
                  background:
                    isTourCompleted || isProgressing
                      ? "linear-gradient(135deg, #e0e0e0 0%, #d5d5d5 100%)"
                      : currentStep === steps.length - 1
                      ? "linear-gradient(135deg, #4caf50 0%, #2e7d32 100%)"
                      : "linear-gradient(135deg, #2196f3 0%, #1565c0 100%)",
                  color: "#fff",
                  boxShadow:
                    isTourCompleted || isProgressing
                      ? "inset 0 2px 4px rgba(0,0,0,0.1)"
                      : currentStep === steps.length - 1
                      ? "0 8px 32px rgba(76, 175, 80, 0.4), 0 4px 16px rgba(46, 125, 50, 0.3), inset 0 1px 0 rgba(255,255,255,0.2)"
                      : "0 8px 32px rgba(33, 150, 243, 0.4), 0 4px 16px rgba(21, 101, 192, 0.3), inset 0 1px 0 rgba(255,255,255,0.2)",
                  "&:hover": {
                    background:
                      isTourCompleted || isProgressing
                        ? "linear-gradient(135deg, #e0e0e0 0%, #d5d5d5 100%)"
                        : currentStep === steps.length - 1
                        ? "linear-gradient(135deg, #43a047 0%, #1b5e20 100%)"
                        : "linear-gradient(135deg, #1976d2 0%, #0d47a1 100%)",
                    boxShadow: isTourCompleted || isProgressing
                      ? "inset 0 2px 4px rgba(0,0,0,0.1)"
                      : currentStep === steps.length - 1
                      ? "0 12px 40px rgba(76, 175, 80, 0.5), 0 8px 24px rgba(46, 125, 50, 0.4), inset 0 1px 0 rgba(255,255,255,0.3)"
                      : "0 12px 40px rgba(33, 150, 243, 0.5), 0 8px 24px rgba(21, 101, 192, 0.4), inset 0 1px 0 rgba(255,255,255,0.3)",
                    transform: isTourCompleted || isProgressing ? "none" : "translateY(-4px) scale(1.02)",
                  },
                  "&:active": {
                    transform: isTourCompleted || isProgressing ? "none" : "translateY(-2px) scale(1.01)",
                    boxShadow: isTourCompleted || isProgressing
                      ? "inset 0 2px 4px rgba(0,0,0,0.1)"
                      : currentStep === steps.length - 1
                      ? "0 6px 20px rgba(76, 175, 80, 0.4), 0 4px 12px rgba(46, 125, 50, 0.3)"
                      : "0 6px 20px rgba(33, 150, 243, 0.4), 0 4px 12px rgba(21, 101, 192, 0.3)",
                  },
                  textTransform: "none",
                  fontWeight: 700,
                  px: 3,
                  py: 1.5,
                  borderRadius: "25px",
                  fontSize: "1rem",
                  transition: "all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)",
                  border: "none",
                  backdropFilter: "blur(20px)",
                  letterSpacing: "0.8px",
                  whiteSpace: "nowrap",
                  position: "relative",
                  overflow: "hidden",
                  "&:before": {
                    content: '""',
                    position: "absolute",
                    top: 0,
                    left: "-100%",
                    width: "100%",
                    height: "100%",
                    background: "linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent)",
                    transition: "left 0.6s",
                  },
                  "&:hover:before": {
                    left: "100%",
                  },
                  "&:disabled": {
                    background: "linear-gradient(135deg, #e0e0e0 0%, #d5d5d5 100%)",
                    color: "#9e9e9e",
                    boxShadow: "inset 0 2px 4px rgba(0,0,0,0.1)",
                    "&:before": {
                      display: "none",
                    },
                  },
                }}
              >
                {isProgressing ? (
                  <>
                    <TrendingFlatIcon sx={{ 
                      fontSize: "1.4rem", 
                      marginRight: "8px",
                      filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
                    }} />
                    Next Service
                    <LoadingDots />
                  </>
                ) : isTourCompleted ? (
                  <>
                    <CheckCircleIcon sx={{ 
                      fontSize: "1.1rem", 
                      marginRight: "4px",
                      filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
                    }} />
                    Done
                  </>
                ) : currentStep === steps.length - 1 ? (
                  <>
                    <AutoAwesomeIcon sx={{ 
                      fontSize: "1.1rem", 
                      marginRight: "4px",
                      filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
                    }} />
                    Finish
                  </>
                ) : (
                  <>
                    <ArrowForwardIcon sx={{ 
                      fontSize: "1.1rem", 
                      marginRight: "4px",
                      filter: "drop-shadow(0 2px 4px rgba(0,0,0,0.2))"
                    }} />
                    Next
                  </>
                )}
              </Button>
              )}
            </Box>
          </Box>
        </Box>

        <Snackbar
          open={openSnackbar}
          autoHideDuration={4000}
          onClose={handleCloseSnackbar}
          anchorOrigin={{ vertical: "top", horizontal: "center" }}
          sx={{
            "& .MuiSnackbarContent-root": {
              borderRadius: "8px",
              minWidth: "300px",
            },
          }}
        >
          <Alert
            onClose={handleCloseSnackbar}
            severity="success"
            sx={{
              display: "flex",
              alignItems: "center",
              gap: 1,
              "& .MuiAlert-icon": {
                fontSize: "1.5rem",
                marginRight: "8px",
              },
            }}
          >
            {snackbarMessage}
          </Alert>
        </Snackbar>
        <ToastContainer
          position="top-right"
          autoClose={3000}
          hideProgressBar={false}
          newestOnTop
          closeOnClick
          rtl={false}
          pauseOnFocusLoss
          draggable
          pauseOnHover
          theme="light"
          style={{
            fontSize: "0.95rem",
            fontWeight: 500,
          }}
        />
      </Box>
      <style jsx>{`
        .stepper-wrapper {
          width: 100%;
          overflow-x: auto;
          -webkit-overflow-scrolling: touch;
          margin-bottom: 30px;
          background: linear-gradient(to right, #0dcaf033, #fff, #0dcaf033);
          border-radius: 16px;
          padding: 20px 10px;
          box-shadow: rgba(17, 17, 26, 0.05) 0px 4px 16px,
            rgba(17, 17, 26, 0.05) 0px 8px 32px;
          border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .stepper-wrapper::-webkit-scrollbar {
          height: 8px;
          border-radius: 4px;
        }

        .stepper-wrapper::-webkit-scrollbar-track {
          background: rgba(241, 241, 241, 0.5);
          border-radius: 4px;
          margin: 0 10px;
        }

        .stepper-wrapper::-webkit-scrollbar-thumb {
          background: rgba(189, 189, 189, 0.8);
          border-radius: 10px;
          border: 2px solid rgba(241, 241, 241, 0.5);
        }

        .stepper-wrapper::-webkit-scrollbar-thumb:hover {
          background: rgba(158, 158, 158, 0.9);
        }

        .stepper-wrapper :global(.MuiStepper-root) {
          min-width: max-content;
          padding: 25px 20px;
          display: flex;
          align-items: center;
          position: relative;
        }

        .stepper-wrapper :global(.MuiStep-root) {
          padding: 0 25px;
          flex: 1;
          position: relative;
        }

        .stepper-wrapper :global(.MuiStepConnector-root) {
          position: relative;
        }

        .stepper-wrapper :global(.MuiStepConnector-line) {
          margin-top: 0 !important;
          margin-bottom: 0 !important;
        }

        .stepper-wrapper :global(.MuiStepConnector-alternativeLabel) {
          top: 28px !important; /* Ensure connector is in the middle of icons */
        }

        .stepper-wrapper :global(.MuiStepLabel-root) {
          padding: 0 !important;
          display: flex;
          flex-direction: column;
          align-items: center;
        }

        .stepper-wrapper :global(.MuiStepLabel-iconContainer) {
          padding: 0 !important;
          margin-bottom: 12px !important;
        }

        .stepper-wrapper :global(.MuiStepLabel-label) {
          font-weight: 600 !important;
          color: #64748b !important;
          margin-top: 10px;
          font-size: 15px;
          text-align: center;
          transition: all 0.2s ease;
          white-space: nowrap;
          line-height: 1.4;
        }

        .stepper-wrapper :global(.MuiStepLabel-active) {
          color: #1976d2 !important;
          font-weight: 700 !important;
        }

        .stepper-wrapper :global(.MuiStepLabel-completed) {
          color: #4caf50 !important;
          font-weight: 700 !important;
        }

        .stepper-wrapper :global(.MuiStepConnector-root) {
          flex: 1;
          min-width: 60px;
        }

        /* Add animation for step transitions */
        .stepper-wrapper :global(.MuiStepLabel-iconContainer) {
          transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .stepper-wrapper
          :global(.MuiStepLabel-active .MuiStepLabel-iconContainer) {
          transform: scale(1.08);
        }

        .stepper-wrapper
          :global(.MuiStepLabel-completed .MuiStepLabel-iconContainer) {
          transform: scale(1.05);
        }

        /* Enhanced button animations */
        @keyframes buttonPulse {
          0% {
            box-shadow: 0 0 0 0 rgba(33, 150, 243, 0.4);
          }
          70% {
            box-shadow: 0 0 0 10px rgba(33, 150, 243, 0);
          }
          100% {
            box-shadow: 0 0 0 0 rgba(33, 150, 243, 0);
          }
        }

        @keyframes buttonShine {
          0% {
            transform: translateX(-100%);
          }
          100% {
            transform: translateX(100%);
          }
        }

        @keyframes buttonGlow {
          0%, 100% {
            filter: brightness(1) drop-shadow(0 0 10px rgba(33, 150, 243, 0.3));
          }
          50% {
            filter: brightness(1.1) drop-shadow(0 0 20px rgba(33, 150, 243, 0.6));
          }
        }

        /* Ripple effect for buttons */
        @keyframes ripple {
          0% {
            transform: scale(0);
            opacity: 1;
          }
          100% {
            transform: scale(4);
            opacity: 0;
          }
        }

        /* Connector line animations */
        @keyframes connectorSlide {
          0% {
            transform: scaleX(0);
            transform-origin: left;
          }
          100% {
            transform: scaleX(1);
            transform-origin: left;
          }
        }

        @keyframes connectorGlow {
          0%, 100% {
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
          }
          50% {
            box-shadow: 0 4px 16px rgba(76, 175, 80, 0.6);
          }
        }

        @keyframes connectorGlowRed {
          0%, 100% {
            box-shadow: 0 2px 8px rgba(255, 82, 82, 0.3);
          }
          50% {
            box-shadow: 0 4px 16px rgba(255, 82, 82, 0.6);
          }
        }

        /* Enhanced stepper connector styling */
        .stepper-wrapper :global(.MuiStep-root:before) {
          animation: connectorSlide 0.6s ease-out;
        }

        @media (max-width: 768px) {
          .stepper-wrapper {
            padding: 15px 5px;
          }

          .stepper-wrapper :global(.MuiStepLabel-label) {
            font-size: 13px;
          }

          .stepper-wrapper :global(.MuiStep-root) {
            padding: 0 15px;
          }
        }
      `}</style>
      ;
    </>
  );
}
