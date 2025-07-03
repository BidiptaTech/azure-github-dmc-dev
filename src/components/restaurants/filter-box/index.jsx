import { useState, useEffect } from "react";
import { useSelector, useDispatch } from "react-redux";
import { useLocation } from "react-router-dom";
import Cookies from "js-cookie";
import dayjs from "dayjs";
import {
  
  addRestaurantBooking,
  // updateModeMap,
} from "../../../slice/restaurant/RestaurantsSlice";
// import { setDateService } from "../../../slice/common/dateServicesSlice";
import GuestSearch from "./GuestSearch";
import DateSearch from "./DateSearch";
// import TransportModal from "./TransportModal";
import {  ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import { useNavigate } from "react-router-dom";
// import { useParams } from "react-router-dom";
// import { fetchRestaurantsDetails } from "@/slice/restaurant/RestaurantsSlice";
import isSameOrAfter from "dayjs/plugin/isSameOrAfter";
import {
  Modal,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  FormControlLabel,
  Radio,
  Checkbox,
  Tooltip,
  Select,
  FormControl,
  InputLabel,
  MenuItem,
  Box,
  Typography,
  IconButton,
  Paper,
  Fade,
} from "@mui/material";
import { styled } from "@mui/material/styles";
import DrinkIcon from '@mui/icons-material/LocalDrink';
import NoDrinksIcon from '@mui/icons-material/NoDrinks';
import CircleIcon from '@mui/icons-material/Circle';
import SquareIcon from '@mui/icons-material/Square';
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import NightsStayIcon from "@mui/icons-material/NightsStay";
import WbSunnyIcon from "@mui/icons-material/WbSunny";
import BreakfastDiningIcon from "@mui/icons-material/BreakfastDining";
import LunchDiningIcon from "@mui/icons-material/LunchDining";
import DinnerDiningIcon from "@mui/icons-material/DinnerDining";
import RestaurantMenuIcon from "@mui/icons-material/RestaurantMenu";
import LocalDiningIcon from "@mui/icons-material/LocalDining";
import BuffetIcon from "@mui/icons-material/Restaurant";
import SetMenuIcon from "@mui/icons-material/MenuBook";
import FastfoodIcon from "@mui/icons-material/Fastfood";
import CalendarMonthIcon from "@mui/icons-material/CalendarMonth";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import ExpandLessIcon from "@mui/icons-material/ExpandLess";
import CloseIcon from "@mui/icons-material/Close";
import MenuBookIcon from "@mui/icons-material/MenuBook";

// Styled components for custom styling of time slot selection
const StyledFormControl = styled(FormControl)(({ theme }) => ({
  backgroundColor: "rgba(255, 255, 255, 0.9)",
  borderRadius: "10px",
  boxShadow: "0 4px 10px rgba(0, 0, 0, 0.05)",
  transition: "all 0.3s ease",
  "&:hover": {
    boxShadow: "0 6px 15px rgba(0, 0, 0, 0.1)",
    transform: "translateY(-2px)",
  },
}));

const StyledSelect = styled(Select)(({ theme }) => ({
  "& .MuiSelect-select": {
    padding: "15px 14px",
    display: "flex",
    alignItems: "center",
    gap: "10px",
  },
  "& .MuiOutlinedInput-notchedOutline": {
    borderColor: "rgba(53, 84, 209, 0.2)",
  },
  "&:hover .MuiOutlinedInput-notchedOutline": {
    borderColor: "rgba(53, 84, 209, 0.5)",
    borderWidth: "2px",
  },
  "&.Mui-focused .MuiOutlinedInput-notchedOutline": {
    borderColor: "#3554D1",
  },
}));

const StyledMenuItem = styled(MenuItem)(
  ({ isNightTime, isDisabled, theme }) => ({
    padding: "10px 16px",
    display: "flex",
    alignItems: "center",
    backgroundColor: isNightTime
      ? "rgba(255, 235, 235, 0.85)"
      : "rgba(237, 242, 255, 0.85)",
    color: isNightTime ? "#9A3412" : "#1E3A8A",
    borderRadius: "6px",
    margin: "4px 8px",
    transition: "all 0.2s ease",
    opacity: isDisabled ? 0.5 : 1,

    "&:hover": {
      backgroundColor: isNightTime
        ? "rgba(255, 235, 235, 1)"
        : "rgba(237, 242, 255, 1)",
      transform: "translateY(-2px) scale(1.02)",
      boxShadow: "0 4px 8px rgba(0, 0, 0, 0.1)",
    },

    "&.Mui-selected": {
      backgroundColor: isNightTime
        ? "rgba(254, 215, 215, 1)"
        : "rgba(219, 234, 254, 1)",
      fontWeight: "bold",
    },

    "&.Mui-selected:hover": {
      backgroundColor: isNightTime
        ? "rgba(254, 202, 202, 1)"
        : "rgba(191, 219, 254, 1)",
    },
  })
);

const SurchargeText = styled(Typography)({
  fontSize: "10px",
  fontWeight: "500",
  color: "#B45309",
  marginTop: "2px",
});

const DayPriceText = styled(Typography)({
  fontSize: "10px",
  fontWeight: "500",
  color: "#1E40AF",
  marginTop: "2px",
});

const StyledInputLabel = styled(InputLabel)({
  color: "#64748B",
  "&.Mui-focused": {
    color: "#3554D1",
  },
});

dayjs.extend(isSameOrAfter);

// Add a new component for meal description with "show more" functionality
const MealDescription = ({ description, index, expandedDescriptions, toggleDescription, openDescriptionModal }) => {
  const truncateWords = (text, limit) => {
    if (!text) return "";
    const words = text.split(/\s+/);
    if (words.length <= limit) return text;
    return words.slice(0, limit).join(" ") + "...";
  };

  // Calculate word count
  const wordCount = description ? description.split(/\s+/).length : 0;
  const hasMoreContent = wordCount > 25;

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column' }}>
      <Typography sx={{ 
        fontWeight: 500,
        fontSize: '15px',
        color: '#1E293B'
      }}>
        {hasMoreContent ? (
          <>
            {expandedDescriptions[index] ? description : truncateWords(description, 25)}
            <Button
              onClick={(e) => {
                e.stopPropagation();
                e.preventDefault();
                // Instead of toggling inline, open the modal
                openDescriptionModal(description, index);
              }}
              sx={{
                minWidth: 'auto',
                padding: '2px 8px',
                fontSize: '12px',
                marginLeft: '4px',
                color: '#3554D1',
                textTransform: 'none',
                display: 'inline-flex',
                alignItems: 'center',
                '&:hover': {
                  backgroundColor: 'rgba(53, 84, 209, 0.04)',
                },
              }}
            >
              Show More <ExpandMoreIcon sx={{ fontSize: 16, ml: 0.5 }} />
            </Button>
          </>
        ) : (
          description || "No description available"
        )}
      </Typography>
    </Box>
  );
};

// New component for the description modal
const DescriptionModal = ({ open, onClose, description, mealType }) => {
  return (
    <Modal
      open={open}
      onClose={onClose}
      closeAfterTransition
      sx={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '20px',
        backdropFilter: 'blur(5px)',
        backgroundColor: 'rgba(0, 0, 0, 0.6)',
      }}
    >
      <Fade in={open}>
        <Paper
          elevation={24}
          sx={{
            width: '90%',
            maxWidth: '600px',
            maxHeight: '90vh',
            borderRadius: '16px',
            overflow: 'hidden',
            boxShadow: '0 10px 40px rgba(0, 0, 0, 0.2)',
            position: 'relative',
            animation: 'zoomIn 0.3s ease-out',
            '@keyframes zoomIn': {
              '0%': {
                opacity: 0,
                transform: 'scale(0.95)'
              },
              '100%': {
                opacity: 1,
                transform: 'scale(1)'
              }
            },
          }}
        >
          <Box sx={{ 
            position: 'relative',
            padding: '24px 32px',
            borderBottom: '1px solid rgba(230, 235, 245, 0.8)',
            background: 'linear-gradient(135deg, #f0f5ff 0%, #ffffff 100%)',
            display: 'flex',
            alignItems: 'center',
            gap: '16px',
          }}>
            {/* Decorative elements */}
            <Box sx={{
              position: 'absolute',
              top: 0,
              right: 0,
              width: '150px',
              height: '150px',
              background: 'radial-gradient(circle, rgba(53, 84, 209, 0.05) 0%, rgba(255, 255, 255, 0) 70%)',
              borderRadius: '50%',
              transform: 'translate(30%, -30%)',
              zIndex: 0
            }} />
            
            {/* Icon with background glow */}
            <Box 
              sx={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: '48px',
                height: '48px',
                borderRadius: '12px',
                background: 'linear-gradient(135deg, #3554D1 0%, #5E72E4 100%)',
                boxShadow: '0 4px 15px rgba(53, 84, 209, 0.25)',
                position: 'relative',
                zIndex: 1,
                animation: 'pulse 2s infinite',
                '@keyframes pulse': {
                  '0%': { boxShadow: '0 4px 15px rgba(53, 84, 209, 0.25)' },
                  '50%': { boxShadow: '0 4px 25px rgba(53, 84, 209, 0.4)' },
                  '100%': { boxShadow: '0 4px 15px rgba(53, 84, 209, 0.25)' }
                }
              }}
            >
              <MenuBookIcon sx={{ color: '#ffffff', fontSize: 28 }} />
            </Box>
            
            <Typography variant="h5" component="h2" sx={{ 
              margin: 0, 
              fontSize: '22px', 
              fontWeight: 700, 
              color: '#1a1a1a',
              flex: 1,
              zIndex: 1
            }}>
              Detailed Description
            </Typography>
            
            <IconButton 
              onClick={onClose}
              sx={{ 
                color: '#64748B', 
                '&:hover': { 
                  backgroundColor: 'rgba(100, 116, 139, 0.1)', 
                  color: '#334155' 
                },
                zIndex: 1
              }}
            >
              <CloseIcon />
            </IconButton>
          </Box>
          
          <Box sx={{ 
            position: 'relative',
            padding: '32px',
            maxHeight: 'calc(90vh - 180px)',
            overflow: 'auto',
            background: 'linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(245, 248, 255, 0.95) 100%)',
            '&::-webkit-scrollbar': {
              width: '8px',
            },
            '&::-webkit-scrollbar-track': {
              background: 'rgba(241, 245, 249, 0.8)',
              borderRadius: '4px',
            },
            '&::-webkit-scrollbar-thumb': {
              background: 'rgba(148, 163, 184, 0.6)',
              borderRadius: '4px',
              '&:hover': {
                background: 'rgba(100, 116, 139, 0.7)',
              },
            },
          }}>
            {/* Background decorative elements */}
            <Box sx={{
              position: 'absolute',
              top: '40%',
              right: '5%',
              width: '200px',
              height: '200px',
              background: 'radial-gradient(circle, rgba(53, 84, 209, 0.03) 0%, rgba(255, 255, 255, 0) 70%)',
              borderRadius: '50%',
              zIndex: 0
            }} />
            <Box sx={{
              position: 'absolute',
              bottom: '10%',
              left: '10%',
              width: '150px',
              height: '150px',
              background: 'radial-gradient(circle, rgba(53, 84, 209, 0.02) 0%, rgba(255, 255, 255, 0) 70%)',
              borderRadius: '50%',
              zIndex: 0
            }} />
            
            {/* Description text with elegant styling */}
            <Typography sx={{ 
              position: 'relative',
              zIndex: 1,
              fontSize: '16px',
              lineHeight: 1.8,
              color: '#334155',
              fontWeight: 400,
              whiteSpace: 'pre-line',
              '& p': {
                marginBottom: '16px'
              },
              '& strong': {
                color: '#1E293B',
                fontWeight: 600
              }
            }}>
              {description || "No description available"}
            </Typography>
          </Box>
          
          <Box sx={{
            padding: '16px 32px',
            borderTop: '1px solid rgba(230, 235, 245, 0.6)',
            background: 'linear-gradient(135deg, #f8faff 0%, #ffffff 100%)',
            display: 'flex',
            justifyContent: 'flex-end'
          }}>
            <Button
              onClick={onClose}
              sx={{
                padding: '10px 24px',
                borderRadius: '10px',
                textTransform: 'none',
                backgroundColor: '#3554D1',
                color: 'white',
                fontWeight: 600,
                boxShadow: '0 4px 12px rgba(53, 84, 209, 0.15)',
                transition: 'all 0.2s ease',
                '&:hover': {
                  backgroundColor: '#2A44B0',
                  boxShadow: '0 6px 16px rgba(53, 84, 209, 0.25)',
                  transform: 'translateY(-2px)'
                },
              }}
            >
              Close
            </Button>
          </Box>
        </Paper>
      </Fade>
    </Modal>
  );
};

const Index = () => {
  const navigate = useNavigate();
  const [open, setOpen] = useState(false);
  // const [selectedMealDescription, setSelectedMealDescription] = useState("");
  const dispatch = useDispatch();
  // const { id } = useParams();
  const AgentId = Cookies.get("AgentId") || "0";
  const location = useLocation();
  const restaurant = location.state?.restaurants || {};
  // console.log("restaurant restaurant", restaurant);

  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  const searchParams =
    useSelector((state) => state.restaurants.searchParams) || {};
  // console.log('aaasss',searchParams);
  const restaurantsDetails = useSelector(
    (state) => state.restaurants.selectedRestaurant
  );
//  console.log('restaurantsDetails restaurantsDetails restaurantsDetails',restaurantsDetails);

  // Add currency information and PriceHide selectors
  const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode) || 'SGD';
  const exchangeRate = useSelector((state) => state.auth.exchangeRate) || 1;
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate) || 1;
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  // console.log('PriceHide',PriceHide);

  const [selectedDate, setSelectedDate] = useState(null);
  const [selectedTime, setSelectedTime] = useState("");
  const [mealType, setMealType] = useState("none");
  const [specificMealType, setSpecificMealType] = useState("");

  // const [isModalOpen, setIsModalOpen] = useState(false);
  const [showMealOptions, setShowMealOptions] = useState(false);
  const [availableMealTypes, setAvailableMealTypes] = useState([]);
  const [timeSlots, setTimeSlots] = useState([]);
  const [selectedMealParts, setSelectedMealParts] = useState([]);
  const [confirmedMealParts, setConfirmedMealParts] = useState([]);
  const [isConfirmationDialogOpen, setIsConfirmationDialogOpen] =
    useState(false);
  const [isConfirmDisabled, setIsConfirmDisabled] = useState(true);
  const [isBookNowDisabled, setIsBookNowDisabled] = useState(true);
  const [timeSlotOpen, setTimeSlotOpen] = useState(false);
  const [mealTypeOpen, setMealTypeOpen] = useState(false);
  const [specificMealTypeOpen, setSpecificMealTypeOpen] = useState(false);
  const [guestSearchOpen, setGuestSearchOpen] = useState(false);

  const [selectedMealIndexes, setSelectedMealIndexes] = useState([]);
  const [selectedMealIndex, setSelectedMealIndex] = useState(null);
  const [priceTypes, setPriceTypes] = useState([]);

  // New state for description modal
  const [descriptionModalOpen, setDescriptionModalOpen] = useState(false);
  const [currentDescription, setCurrentDescription] = useState("");

  // Commenting out transport-related state variables
  /*
  const [isTransportModalOpen, setIsTransportModalOpen] = useState(false);
  const [selectedTransport, setSelectedTransport] = useState(null);
  */

  const [expandedDescriptions, setExpandedDescriptions] = useState({});

  // Function to open the description modal
  const openDescriptionModal = (description, index) => {
    setCurrentDescription(description);
    setDescriptionModalOpen(true);
  };

  useEffect(() => {
    if (restaurantsDetails?.meals?.length) {
      const types = [
        ...new Set(restaurantsDetails.meals.map((meal) => meal.price_type)),
      ];
      setPriceTypes(types);
    } else {
      setPriceTypes([]); // Prevent undefined issues
    }
  }, [restaurantsDetails]);

  useEffect(() => {
    setIsBookNowDisabled(
      !selectedDate || // Date must be selected
        !selectedTime || // Time slot must be selected
        mealType === "none" || // Meal type must be chosen
        !specificMealType || // Specific meal type must be chosen
        selectedMealParts.length === 0 || // At least one meal part must be available
        !selectedMealParts.some((part) => part.checked) // At least one checkbox must be checked
    );
  }, [
    selectedDate,
    selectedTime,
    mealType,
    specificMealType,
    selectedMealParts,
  ]);

  useEffect(() => {
    if (searchParams?.date) {
      setSelectedDate(searchParams.date);
    }
  }, [searchParams]);

  useEffect(() => {
    if (restaurantsDetails?.meals?.length > 0) {
      const meals = [];
      restaurantsDetails.meals.forEach((meal) => {
        if (meal.meal_period.toLowerCase().includes("breakfast"))
          meals.push("breakfast");
        if (meal.meal_period.toLowerCase().includes("lunch"))
          meals.push("lunch");
        if (meal.meal_period.toLowerCase().includes("dinner"))
          meals.push("dinner");
      });
      setAvailableMealTypes([...new Set(meals)]); // Remove duplicates
    }
  }, [restaurantsDetails]);

  const generateTimeSlots = (openTime, closeTime) => {
    if (!openTime || !closeTime) return [];

    const parseTime = (timeStr) => {
      const [hours, minutes] = timeStr.split(":").map(Number);
      return new Date(2000, 0, 1, hours, minutes); // Fixed date to avoid issues
    };

    const startTime = parseTime(openTime);
    const endTime = parseTime(closeTime);

    if (isNaN(startTime) || isNaN(endTime) || startTime >= endTime) {
      console.warn(`Invalid time range: ${openTime} - ${closeTime}`);
      return [];
    }

    const slots = [];
    let currentTime = new Date(startTime.getTime() + 30 * 60000); // Skip the opening time

    while (
      currentTime < endTime ||
      currentTime.getTime() === endTime.getTime()
    ) {
      const hours = currentTime.getHours();
      const minutes = currentTime.getMinutes();
      const formattedTime = `${hours % 12 === 0 ? 12 : hours % 12}:${minutes
        .toString()
        .padStart(2, "0")} ${hours >= 12 ? "PM" : "AM"}`;
      slots.push(formattedTime);
      currentTime = new Date(currentTime.getTime() + 30 * 60000);
    }

    return slots;
  };

  const handleMealTypeChange = (event) => {
    const selectedMeal = event.target.value;
    setMealType(selectedMeal);
    setSpecificMealType("");
    setShowMealOptions(selectedMeal !== "none");
    setSelectedTime(""); // Reset selected time when meal type changes
  };

  const handleTimeSlotChange = (e) => {
    setSelectedTime(e.target.value);
    // setIsTransportModalOpen(true); // Commenting out transport modal open
  };

  /*
  const handleTransportSelected = (transportData) => {
    setSelectedTransport(transportData);
    setIsTransportModalOpen(false);
  };
  */

  const handleBookNow = () => {
    const finalPrice = calculateTotalPrice();
    // console.log('finalPrice',finalPrice);
    // const transportPrice = selectedTransport ? selectedTransport.price : 0;
    // console.log('transportPrice',transportPrice);
    // const finalPrice = totalPrice + transportPrice;
    // console.log('finalPrice',finalPrice);
    // Filter and format meal descriptions based on meal type
    const formattedMealDescriptions = confirmedMealParts.map(part => {
      const baseDescription = {
        item_name: part.item_name,
        name: part.name,
        price: part.price,
        meal_id: part.meal_id,
        category: part.category,
        item_type: part.item_type
      };

      // Only add quantity for Set Menu and A la Carte
      if (["Set Menu", "A la carte"].includes(specificMealType)) {
        baseDescription.quantity = part.quantity || 1;
      }

      return baseDescription;
    });

    const bookingDetails = {
      agent_id: AgentId || 0,
      data: [
        {
          bookingDate: dayjs(selectedDate).format("YYYY-MM-DD"),
          visitTime: selectedTime,
          adultCount: searchParams?.adults,
          childCount: searchParams?.children,
          restaurantId: restaurant?.id,
          restaurantName: restaurant?.restaurant_name || "Unknown Restaurant",
          mealType,
          mealSpecificType: ["breakfast", "lunch", "dinner"].includes(mealType)
            ? specificMealType
            : null,
          MealDescription: formattedMealDescriptions,
          totalPrice: finalPrice,
          priceTypes,
          // transport: selectedTransport // Commenting out transport data
        },
      ],
      tour_id: parseInt(tourdetails?.tour_id) || 0,
      type: "restaurant",
    };

    // Dispatch booking details to Redux
    dispatch(addRestaurantBooking(bookingDetails));

    // Navigate to checkout page
    navigate("/dashboard/db-dashboard/restaurants-checkout");
  };
  
  const handleMealSelection = (e) => {
    const selectedMealName = e.target.value;
    setSpecificMealType(selectedMealName);
    setSelectedMealIndex(null); // Reset selected index
    setSelectedMealIndexes([]); // Reset selected indexes

    const selectedMeals = restaurantsDetails.meals.filter(
      (meal) =>
        meal.type === selectedMealName &&
        meal.meal_period.toLowerCase() === mealType.toLowerCase()
    );

    if (selectedMeals.length > 0) {
      const mealParts = selectedMeals.map((meal) => ({
        item_name: meal.name,
        name: meal.item_description,
        category: meal.category,
        item_type: meal.item_type,
        checked: false,
        price: meal.price,
        meal_id: meal.meal_id,
        quantity: 1,
        // Add adult and child prices for buffet type
        adult_price: meal.adult_price,
        child_price: meal.child_price,
        // Initialize counts for buffet
        adultCount: 0,
        childCount: 0
      }));

      setSelectedMealParts(mealParts);
      setIsConfirmDisabled(true);
      setOpen(true);
    } else {
      setSelectedMealParts([]);
      setOpen(false);
    }
  };

  // Add a debug effect to monitor specificMealType
  // useEffect(() => {
  //   console.log("Current specificMealType:", specificMealType);
  // }, [specificMealType]);

  const handleRadioChange = (index) => {
    setSelectedMealIndex(index); // Track selected radio index
    setSelectedMealIndexes([]); // Clear any checkbox selections

    setSelectedMealParts((prevParts) =>
      prevParts.map((part, i) => ({
        ...part,
        checked: i === index, // Only mark the selected one as checked
      }))
    );

    setIsConfirmDisabled(false);
  };

  // ✅ Handle confirm button click
  const handleConfirm = () => {
    let selectedItems;
    if (specificMealType === "A la carte") {
      selectedItems = selectedMealParts.filter((part) => part.checked);
    } else if (specificMealType === "Buffet") {
      // For Buffet, only include the selected item by radio button
      selectedItems = selectedMealIndex !== null 
        ? [selectedMealParts[selectedMealIndex]].map(part => ({
            ...part,
            adultCount: part.adultCount || searchParams?.adults || 0,
            childCount: part.childCount || searchParams?.children || 0
          }))
        : [];
    } else {
      // For Set menu, get the selected item by index
      selectedItems =
        selectedMealIndex !== null
          ? [selectedMealParts[selectedMealIndex]]
          : [];
    }

    setConfirmedMealParts(selectedItems);
    setOpen(false); // Close modal

    if (mealType !== "none") {
      const mealTimes = {
        breakfast: {
          open: restaurantsDetails?.opening_time_bf,
          close: restaurantsDetails?.closing_time_bf,
        },
        lunch: {
          open: restaurantsDetails?.opening_time_lunch,
          close: restaurantsDetails?.closing_time_lunch,
        },
        dinner: {
          open: restaurantsDetails?.opening_time_dinner,
          close: restaurantsDetails?.closing_time_dinner,
        },
      };

      setTimeSlots(
        generateTimeSlots(mealTimes[mealType]?.open, mealTimes[mealType]?.close)
      );
    }
  };
  useEffect(() => {
    // console.log("Total Price Updated:", calculateTotalPrice());
  }, [
    specificMealType,
    selectedMealIndexes,
    selectedMealIndex,
    selectedMealParts,
  ]);

  const handleQuantityChange = (index, change) => {
    setSelectedMealParts((prevParts) =>
      prevParts.map((part, i) => {
        if (i === index) {
          const newQuantity = Math.max(1, (part.quantity || 1) + change);
          return { ...part, quantity: newQuantity };
        }
        return part;
      })
    );
  };

 

  const calculateItemPrice = (part) => {
    // console.log('Part object:', part);
    // console.log('Specific Meal Type:', specificMealType);
    
    if (specificMealType === "Buffet") {
      const adultPrice = part.adult_price || part.price || 0;
      const childPrice = part.child_price || (adultPrice * 0.5);
      const adultCount = part.adultCount || searchParams?.adults || 0;
      const childCount = part.childCount || searchParams?.children || 0;
      
      // console.log('Buffet Calculation Details:', {
      //   adultPrice,
      //   childPrice,
      //   adultCount,
      //   childCount,
      //   total: (adultPrice * adultCount) + (childPrice * childCount)
      // });
      
      return (adultPrice * adultCount) + (childPrice * childCount);
    } else if (specificMealType === "A la carte") {
      // console.log('A la carte Calculation:', {
      //   price: part.price,
      //   quantity: part.quantity,
      //   total: Number(part.price || 0) * (part.quantity || 1)
      // });
      return Number(part.price || 0) * (part.quantity || 1);
    } else {
      // console.log('Set Menu Calculation:', {
      //   price: part.price,
      //   quantity: part.quantity,
      //   total: Number(part.price || 0) * (part.quantity || 1)
      // });
      return Number(part.price || 0) * (part.quantity || 1);
    }
  };

  const calculateTotalPrice = () => {
    if (specificMealType === "Buffet") {  
      return selectedMealIndex !== null 
        ? calculateItemPrice(selectedMealParts[selectedMealIndex])
        : 0;
    } else if (specificMealType === "A la carte") {
      return selectedMealParts
        .filter((_, index) => selectedMealIndexes.includes(index))
        .reduce((total, part) => total + calculateItemPrice(part), 0);
    } else {
      return calculateItemPrice(selectedMealParts[selectedMealIndex] || {});
    }
  };

  const handleCheckboxChange = (index) => {
    setSelectedMealIndex(null); // Clear any radio selection
    setSelectedMealIndexes((prev) => {
      const updatedIndexes = prev.includes(index)
        ? prev.filter((i) => i !== index)
        : [...prev, index];

      // Update checked status in selectedMealParts
      setSelectedMealParts((prevParts) =>
        prevParts.map((part, i) => ({
          ...part,
          checked: updatedIndexes.includes(i), // Mark parts as checked based on selected indexes
        }))
      );

      setIsConfirmDisabled(updatedIndexes.length === 0); // Disable Confirm if no checkboxes selected
      return updatedIndexes;
    });
  };

  // Update the condition in the render to be more explicit
  const showQuantityColumn = ["A la carte", "Set Menu"].includes(
    specificMealType
  );
  const showBuffetColumns = specificMealType === "Buffet";

  // Format price in different currencies
  const formatPrice = (price, type) => {
    if (!price) return "0.00";
    
    switch(type) {
      case "main":
        return `${currencyCode} ${formatCurrency(price * exchangeRate)}`;
      case "usd":
        return `USD ${formatCurrency(price * usdExchangeRate)}`;
      case "sgd":
        return `SGD ${formatCurrency(price)}`;
      default:
        return `SGD ${formatCurrency(price)}`;
    }
  };

  // Format currency with 2 decimal places and thousands separator
  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-SG', {
      style: 'decimal',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }).format(amount);
  };

  const toggleDescription = (index) => {
    // Fix the toggle function to properly update state
    setExpandedDescriptions(prev => {
      const updated = {...prev};
      updated[index] = !prev[index];
      return updated;
    });
  };

  return (
    <>
      <div className="col-12">
        <div className="searchMenu-date px-20 py-10 border-light rounded-4">
          <h4 className="text-15 fw-500 ls-2 lh-16">Date</h4>
          <div style={{ position: "relative" }}>
            <div 
              style={{ 
                display: "flex", 
                alignItems: "center",
                backgroundColor: "rgba(255, 255, 255, 0.9)",
                borderRadius: "10px",
                boxShadow: "0 4px 10px rgba(0, 0, 0, 0.05)",
                padding: "15px 14px",
                transition: "all 0.3s ease",
                cursor: "pointer",
              }}
              onMouseOver={(e) => {
                e.currentTarget.style.boxShadow = "0 6px 15px rgba(0, 0, 0, 0.1)";
                e.currentTarget.style.transform = "translateY(-2px)";
              }}
              onMouseOut={(e) => {
                e.currentTarget.style.boxShadow = "0 4px 10px rgba(0, 0, 0, 0.05)";
                e.currentTarget.style.transform = "translateY(0)";
              }}
            >
              <CalendarMonthIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />
              <Typography sx={{ fontWeight: 500 }}>
                {selectedDate 
                  ? (() => {
                      const date = dayjs(selectedDate);
                      const dayName = date.format('ddd');
                      const day = date.format('DD');
                      const month = date.format('MMM');
                      const year = `'${date.format('YY')}`;
                      return `${dayName}, ${day} ${month}${year}`;
                    })() 
                  : "Select Date"}
              </Typography>
            </div>
            <div style={{ position: "absolute", top: 0, left: 0, right: 0, bottom: 0, opacity: 0 }}>
              <DateSearch setSelectedDate={setSelectedDate} />
            </div>
          </div>
        </div>
      </div>

      <div className="col-12">
        <div className="searchMenu-date px-20 py-10 border-light rounded-4">
          <h4 className="text-15 fw-500 ls-2 lh-16">Meal Selection</h4>
          <StyledFormControl fullWidth>
            <StyledInputLabel 
              id="meal-type-label" 
              shrink={false}
              sx={{ display: "none" }}
            >
              Select a Meal Type
            </StyledInputLabel>
            <StyledSelect
              labelId="meal-type-label"
              id="meal-type-select"
              value={mealType}
              onChange={handleMealTypeChange}
              open={mealTypeOpen}
              onOpen={() => setMealTypeOpen(true)}
              onClose={() => setMealTypeOpen(false)}
              displayEmpty
              renderValue={(selected) => {
                if (selected === "none") {
                  return (
                    <div style={{ display: "flex", alignItems: "center" }}>
                      <RestaurantMenuIcon sx={{ mr: 2, fontSize: 20, color: "#64748B" }} />
                      <Typography sx={{ fontWeight: 500 }}>None</Typography>
                    </div>
                  );
                }
                if (!selected) {
                  return (
                    <div style={{ display: "flex", alignItems: "center" }}>
                      <LocalDiningIcon sx={{ mr: 2, fontSize: 20, color: "#64748B" }} />
                      <Typography sx={{ fontWeight: 500, color: "#64748B" }}>
                        Select a Meal Type
                      </Typography>
                    </div>
                  );
                }
                
                // For other meal types
                const mealIcon = selected === "breakfast" 
                  ? <BreakfastDiningIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />
                  : selected === "lunch" 
                  ? <LunchDiningIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />
                  : <DinnerDiningIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />;
                
                return (
                  <div style={{ display: "flex", alignItems: "center" }}>
                    {mealIcon}
                    <Typography sx={{ fontWeight: 500 }}>
                      {selected.charAt(0).toUpperCase() + selected.slice(1)}
                    </Typography>
                  </div>
                );
              }}
              MenuProps={{
                PaperProps: {
                  style: {
                    maxHeight: 350,
                    width: "auto",
                    borderRadius: "12px",
                    padding: "8px",
                    marginTop: "8px",
                    backgroundColor: "rgba(255, 255, 255, 0.95)",
                    boxShadow: "0 8px 20px rgba(0, 0, 0, 0.15)",
                  },
                },
                TransitionProps: {
                  style: {
                    transition: "all 0.2s ease",
                  },
                },
              }}
            >
              <StyledMenuItem 
                value="none"
                isNightTime={false}
              >
                <RestaurantMenuIcon sx={{ mr: 2, fontSize: 20, color: "#64748B" }} />
                <Typography variant="body1" sx={{ fontWeight: 500 }}>
                  None
                </Typography>
              </StyledMenuItem>
              {availableMealTypes.map((type, index) => {
                const mealIcon = type === "breakfast" 
                  ? <BreakfastDiningIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />
                  : type === "lunch" 
                  ? <LunchDiningIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />
                  : <DinnerDiningIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />;
                  
                return (
                  <StyledMenuItem
                    key={index}
                    value={type}
                    isNightTime={false}
                  >
                    {mealIcon}
                    <Typography variant="body1" sx={{ fontWeight: 500 }}>
                      {type.charAt(0).toUpperCase() + type.slice(1)}
                    </Typography>
                  </StyledMenuItem>
                );
              })}
            </StyledSelect>
          </StyledFormControl>
        </div>
      </div>

      {showMealOptions && (
        <div className="col-12">
          <div className="searchMenu-date px-20 py-10 border-light rounded-4">
            <h4 className="text-15 fw-500 ls-2 lh-16">
              Specific Meal Selection
            </h4>
            <StyledFormControl fullWidth>
              <StyledInputLabel 
                id="specific-meal-type-label" 
                shrink={false}
                sx={{ display: "none" }}
              >
                Select Meal Type
              </StyledInputLabel>
              <StyledSelect
                labelId="specific-meal-type-label"
                id="specific-meal-type-select"
                value={specificMealType}
                onChange={handleMealSelection}
                open={specificMealTypeOpen}
                onOpen={() => setSpecificMealTypeOpen(true)}
                onClose={() => setSpecificMealTypeOpen(false)}
                displayEmpty
                renderValue={(selected) => {
                  if (!selected) {
                    return (
                      <div style={{ display: "flex", alignItems: "center" }}>
                        <LocalDiningIcon sx={{ mr: 2, fontSize: 20, color: "#64748B" }} />
                        <Typography sx={{ fontWeight: 500, color: "#64748B" }}>
                          Select Meal Type
                        </Typography>
                      </div>
                    );
                  }
                  
                  // Choose the right icon based on meal type
                  let mealTypeIcon;
                  switch(selected.toLowerCase()) {
                    case 'buffet':
                      mealTypeIcon = <BuffetIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />;
                      break;
                    case 'set menu':
                      mealTypeIcon = <SetMenuIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />;
                      break;
                    case 'a la carte':
                      mealTypeIcon = <FastfoodIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />;
                      break;
                    default:
                      mealTypeIcon = <LocalDiningIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />;
                  }
                  
                  return (
                    <div style={{ display: "flex", alignItems: "center" }}>
                      {mealTypeIcon}
                      <Typography sx={{ fontWeight: 500 }}>
                        {selected}
                      </Typography>
                    </div>
                  );
                }}
                MenuProps={{
                  PaperProps: {
                    style: {
                      maxHeight: 350,
                      width: "auto",
                      borderRadius: "12px",
                      padding: "8px",
                      marginTop: "8px",
                      backgroundColor: "rgba(255, 255, 255, 0.95)",
                      boxShadow: "0 8px 20px rgba(0, 0, 0, 0.15)",
                    },
                  },
                  TransitionProps: {
                    style: {
                      transition: "all 0.2s ease",
                    },
                  },
                }}
              >
                <StyledMenuItem
                  value=""
                  isNightTime={false}
                >
                  <LocalDiningIcon sx={{ mr: 2, fontSize: 20, color: "#64748B" }} />
                  <Typography variant="body1" sx={{ fontWeight: 500 }}>
                    Select Meal Type
                  </Typography>
                </StyledMenuItem>
                
                {[
                  ...new Set(
                    restaurantsDetails.meals
                      .filter(
                        (meal) =>
                          meal.meal_period.toLowerCase() ===
                          mealType.toLowerCase()
                      )
                      .map((meal) => meal.type)
                  ),
                ].map((uniqueMealType, index) => {
                  // Choose the right icon based on meal type
                  let mealTypeIcon;
                  switch(uniqueMealType.toLowerCase()) {
                    case 'buffet':
                      mealTypeIcon = <BuffetIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />;
                      break;
                    case 'set menu':
                      mealTypeIcon = <SetMenuIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />;
                      break;
                    case 'a la carte':
                      mealTypeIcon = <FastfoodIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />;
                      break;
                    default:
                      mealTypeIcon = <LocalDiningIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />;
                  }
                  
                  return (
                    <StyledMenuItem
                      key={index}
                      value={uniqueMealType}
                      isNightTime={false}
                    >
                      {mealTypeIcon}
                      <Typography variant="body1" sx={{ fontWeight: 500 }}>
                        {uniqueMealType}
                      </Typography>
                    </StyledMenuItem>
                  );
                })}
              </StyledSelect>
            </StyledFormControl>
          </div>
        </div>
      )}

      {timeSlots.length > 0 && mealType !== "none" && (
        <div className="col-12">
          <div className="searchMenu-date px-20 py-10 border-light rounded-4">
            <h4 className="text-15 fw-500 ls-2 lh-16">Time Slot</h4>
            <StyledFormControl fullWidth>
              <StyledInputLabel 
                id="time-slot-label" 
                shrink={false}
                sx={{ display: "none" }}
              >
                Select Time Slot
              </StyledInputLabel>
              <StyledSelect
                labelId="time-slot-label"
                id="time-slot-select"
                value={selectedTime}
                onChange={handleTimeSlotChange}
                open={timeSlotOpen}
                onOpen={() => setTimeSlotOpen(true)}
                onClose={() => setTimeSlotOpen(false)}
                displayEmpty
                renderValue={(selected) => {
                  if (!selected) {
                    return (
                      <div style={{ display: "flex", alignItems: "center" }}>
                        <AccessTimeIcon sx={{ mr: 2, fontSize: 20, color: "#64748B" }} />
                        <Typography sx={{ fontWeight: 500, color: "#64748B" }}>
                          Select Time Slot
                        </Typography>
                      </div>
                    );
                  }
                  
                  // Parse the time to determine if it's night time
                  const timeComponents = selected.match(/(\d+):(\d+) (AM|PM)/);
                  if (!timeComponents) return selected;

                  let hour = parseInt(timeComponents[1], 10);
                  const period = timeComponents[3];
                  
                  // Convert to 24-hour format for checking night time
                  if (period === "PM" && hour !== 12) hour += 12;
                  if (period === "AM" && hour === 12) hour = 0;
                  
                  // Determine if it's night time
                  const isNight = hour >= 19 || hour < 6;
                  
                  return (
                    <div style={{ display: "flex", alignItems: "center" }}>
                      {isNight ? (
                        <NightsStayIcon sx={{ mr: 2, fontSize: 20, color: "#9A3412" }} />
                      ) : (
                        <WbSunnyIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />
                      )}
                      <Typography sx={{ fontWeight: 500 }}>
                        {selected}
                      </Typography>
                    </div>
                  );
                }}
                MenuProps={{
                  PaperProps: {
                    style: {
                      maxHeight: 350,
                      width: "auto",
                      borderRadius: "12px",
                      padding: "8px",
                      marginTop: "8px",
                      backgroundColor: "rgba(255, 255, 255, 0.95)",
                      boxShadow: "0 8px 20px rgba(0, 0, 0, 0.15)",
                    },
                  },
                  TransitionProps: {
                    style: {
                      transition: "all 0.2s ease",
                    },
                  },
                }}
              >
                {timeSlots.map((timeSlot, index) => {
                  // Parse the time to determine if it's night time (after 7 PM or before 6 AM)
                  const timeComponents = timeSlot.match(/(\d+):(\d+) (AM|PM)/);
                  if (!timeComponents) return null;

                  let hour = parseInt(timeComponents[1], 10);
                  const minutes = parseInt(timeComponents[2], 10);
                  const period = timeComponents[3];
                  
                  // Convert to 24-hour format
                  if (period === "PM" && hour !== 12) hour += 12;
                  if (period === "AM" && hour === 12) hour = 0;
                  
                  // Determine if it's night time
                  const isNight = hour >= 19 || hour < 6;

                  return (
                    <StyledMenuItem
                      key={index}
                      value={timeSlot}
                      isNightTime={isNight}
                    >
                      {isNight ? (
                        <NightsStayIcon
                          sx={{ mr: 2, fontSize: 20, color: "#9A3412" }}
                        />
                      ) : (
                        <WbSunnyIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />
                      )}
                      <Typography variant="body1" sx={{ fontWeight: 500 }}>
                        {timeSlot}
                      </Typography>
                    </StyledMenuItem>
                  );
                })}
              </StyledSelect>
            </StyledFormControl>
          </div>
        </div>
      )}

      <div className="col-12">
        <div 
          className="searchMenu-guests px-20 py-10 border-light rounded-4"
          style={{ 
            backgroundColor: "rgba(255, 255, 255, 0.9)",
            borderRadius: "10px",
            boxShadow: "0 4px 10px rgba(0, 0, 0, 0.05)",
            transition: "all 0.3s ease",
            position: "relative"
          }}
          onMouseOver={(e) => {
            e.currentTarget.style.boxShadow = "0 6px 15px rgba(0, 0, 0, 0.1)";
            e.currentTarget.style.transform = "translateY(-2px)";
          }}
          onMouseOut={(e) => {
            e.currentTarget.style.boxShadow = "0 4px 10px rgba(0, 0, 0, 0.05)";
            e.currentTarget.style.transform = "translateY(0)";
          }}
        >
          <GuestSearch />
        </div>
      </div>

      {!isBookNowDisabled && (
        <div className="col-12">
          <button
            className="button py-15 px-35 h-60 col-12 rounded-4 bg-blue-1 text-white"
            onClick={handleBookNow}
            style={{
              transition: "all 0.3s ease",
              boxShadow: "0 4px 10px rgba(53, 84, 209, 0.25)",
              fontWeight: "600",
              letterSpacing: "0.5px",
              marginTop: "15px"
            }}
            onMouseOver={(e) => {
              e.currentTarget.style.transform = "translateY(-2px)";
              e.currentTarget.style.boxShadow = "0 6px 15px rgba(53, 84, 209, 0.35)";
            }}
            onMouseOut={(e) => {
              e.currentTarget.style.transform = "translateY(0)";
              e.currentTarget.style.boxShadow = "0 4px 10px rgba(53, 84, 209, 0.25)";
            }}
          >
            Check Out
          </button>
          
          {/* Only display price information if PriceHide is "0" */}
          {PriceHide === "0" && calculateTotalPrice() > 0 && (
            <div className="mt-10 text-center">
              <div className="text-18 fw-600 text-blue-1">
                {formatPrice(calculateTotalPrice(), "main")}
              </div>
              {currencyCode !== 'USD' && (
                <div className="text-14 text-light-1">
                  {formatPrice(calculateTotalPrice(), "usd")}
                </div>
              )}
              {currencyCode !== 'SGD' && (
                <div className="text-14 text-light-1">
                  {formatPrice(calculateTotalPrice(), "sgd")}
                </div>
              )}
            </div>
          )}
          
          {/* Commenting out Transport Info 
          {selectedTransport && (
            <div
              className="mt-15 p-15 rounded-4"
              style={{
                backgroundColor: "rgba(53, 84, 209, 0.05)",
                border: "1px solid rgba(53, 84, 209, 0.2)",
                display: "flex",
                alignItems: "center",
                justifyContent: "space-between"
              }}
            >
              <div>
                <div className="d-flex align-items-center">
                  <div 
                    style={{ 
                      backgroundColor: "rgba(53, 84, 209, 0.1)", 
                      borderRadius: "8px",
                      width: "36px",
                      height: "36px",
                      display: "flex",
                      alignItems: "center",
                      justifyContent: "center",
                      marginRight: "10px"
                    }}
                  >
                    <DirectionsCarIcon sx={{ color: "#3554D1", fontSize: 20 }} />
                  </div>
                  <span className="fw-500">{selectedTransport.vehicle_name} ({selectedTransport.transport_type})</span>
                </div>
                {PriceHide === "0" && (
                  <div className="ml-45 text-14 text-light-1">
                    S$ {selectedTransport.price.toFixed(2)}
                  </div>
                )}
              </div>
              <button
                className="button py-10 px-15 bg-white text-14 text-blue-1 fw-500 border-light"
                onClick={() => setIsTransportModalOpen(true)}
              >
                Change
              </button>
            </div>
          )}
          */}
        </div>
      )}

      {/* Meal Description Modal */}
      <Modal 
        open={open} 
        onClose={() => setOpen(false)}
        sx={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          padding: '20px',
          height: '100vh',
          backdropFilter: 'blur(5px)',
          backgroundColor: 'rgba(0, 0, 0, 0.6)',
        }}
      >
        <Dialog
          open={open}
          onClose={() => setOpen(false)}
          maxWidth={false}
          fullWidth
          sx={{
            width: '80vw',
            maxWidth: '1000px',
            height: 'auto',
            maxHeight: '90vh',
            margin: 'auto',
            position: 'relative',
            transform: 'translateY(-30px)',
            opacity: 1,
            animation: 'fadeIn 0.3s ease-out',
            '@keyframes fadeIn': {
              '0%': {
                opacity: 0,
                transform: 'translateY(30px)'
              },
              '100%': {
                opacity: 1,
                transform: 'translateY(-30px)'
              }
            },
            '& .MuiDialog-paper': {
              margin: '0',
              borderRadius: '16px',
              boxShadow: '0 10px 40px rgba(0, 0, 0, 0.15)',
              overflow: 'hidden',
              border: '1px solid rgba(255, 255, 255, 0.18)',
              background: 'rgba(255, 255, 255, 0.95)',
            }
          }}
        >
          <DialogTitle 
            sx={{
              padding: '24px 32px',
              borderBottom: '1px solid rgba(230, 235, 245, 0.8)',
              background: 'linear-gradient(135deg, #f0f5ff 0%, #ffffff 100%)',
              borderTopLeftRadius: '16px',
              borderTopRightRadius: '16px',
              display: 'flex',
              alignItems: 'center',
              gap: '12px',
              position: 'relative',
              overflow: 'hidden'
            }}
          >
            {/* Decorative elements */}
            <Box sx={{
              position: 'absolute',
              top: 0,
              right: 0,
              width: '150px',
              height: '150px',
              background: 'radial-gradient(circle, rgba(53, 84, 209, 0.05) 0%, rgba(255, 255, 255, 0) 70%)',
              borderRadius: '50%',
              transform: 'translate(30%, -30%)',
              zIndex: 0
            }} />
            <Box sx={{
              position: 'absolute',
              bottom: 0,
              left: '10%',
              width: '100px',
              height: '100px',
              background: 'radial-gradient(circle, rgba(53, 84, 209, 0.03) 0%, rgba(255, 255, 255, 0) 70%)',
              borderRadius: '50%',
              transform: 'translate(0, 30%)',
              zIndex: 0
            }} />
            
            {/* Icon with background glow */}
            <Box 
              sx={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: '48px',
                height: '48px',
                borderRadius: '12px',
                background: 'linear-gradient(135deg, #3554D1 0%, #5E72E4 100%)',
                boxShadow: '0 4px 15px rgba(53, 84, 209, 0.25)',
                position: 'relative',
                zIndex: 1,
                animation: 'pulse 2s infinite',
                '@keyframes pulse': {
                  '0%': { boxShadow: '0 4px 15px rgba(53, 84, 209, 0.25)' },
                  '50%': { boxShadow: '0 4px 25px rgba(53, 84, 209, 0.4)' },
                  '100%': { boxShadow: '0 4px 15px rgba(53, 84, 209, 0.25)' }
                }
              }}
            >
              {specificMealType === "Buffet" ? (
                <BuffetIcon sx={{ color: '#ffffff', fontSize: 28 }} />
              ) : specificMealType === "Set Menu" ? (
                <SetMenuIcon sx={{ color: '#ffffff', fontSize: 28 }} />
              ) : specificMealType === "A la carte" ? (
                <FastfoodIcon sx={{ color: '#ffffff', fontSize: 28 }} />
              ) : (
                <RestaurantMenuIcon sx={{ color: '#ffffff', fontSize: 28 }} />
              )}
            </Box>
            
            <Box sx={{ zIndex: 1 }}>
              <Typography variant="h5" component="h2" sx={{ 
                margin: 0, 
                fontSize: '26px', 
                fontWeight: 700, 
                color: '#1a1a1a',
                marginBottom: '4px'
              }}>
                {specificMealType || "Meal Description"}
              </Typography>
              <Typography variant="body2" sx={{ 
                color: '#64748B',
                fontSize: '14px',
                fontWeight: 500
              }}>
                {mealType.charAt(0).toUpperCase() + mealType.slice(1)} • {searchParams?.adults || 0} Adult{searchParams?.adults !== 1 ? 's' : ''}{searchParams?.children > 0 ? ` • ${searchParams?.children} Child${searchParams?.children !== 1 ? 'ren' : ''}` : ''}
              </Typography>
            </Box>
          </DialogTitle>
          
          <DialogContent 
            sx={{
              padding: '32px',
              background: 'linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(245, 248, 255, 0.95) 100%)',
              position: 'relative',
              overflow: 'auto',
              maxHeight: 'calc(90vh - 200px)', // Add max height to enable scrolling
              '&::-webkit-scrollbar': {
                width: '8px',
              },
              '&::-webkit-scrollbar-track': {
                background: 'rgba(241, 245, 249, 0.8)',
                borderRadius: '4px',
              },
              '&::-webkit-scrollbar-thumb': {
                background: 'rgba(148, 163, 184, 0.6)',
                borderRadius: '4px',
                '&:hover': {
                  background: 'rgba(100, 116, 139, 0.7)',
                },
              },
            }}
          >
            {/* Background decorative elements */}
            <Box sx={{
              position: 'absolute',
              top: '40%',
              right: '5%',
              width: '200px',
              height: '200px',
              background: 'radial-gradient(circle, rgba(53, 84, 209, 0.03) 0%, rgba(255, 255, 255, 0) 70%)',
              borderRadius: '50%',
              zIndex: 0
            }} />
            <Box sx={{
              position: 'absolute',
              bottom: '10%',
              left: '10%',
              width: '150px',
              height: '150px',
              background: 'radial-gradient(circle, rgba(53, 84, 209, 0.02) 0%, rgba(255, 255, 255, 0) 70%)',
              borderRadius: '50%',
              zIndex: 0
            }} />
            
            {/* Table with enhanced styling */}
            <Box 
              sx={{ 
                position: 'relative', 
                zIndex: 1, 
                boxShadow: '0 4px 25px rgba(0, 0, 0, 0.05)',
                borderRadius: '16px',
                overflow: 'hidden',
                background: 'rgba(255, 255, 255, 0.9)'
              }}
            >
              <table style={{ 
                width: '100%', 
                borderCollapse: 'separate', 
                borderSpacing: '0',
                tableLayout: 'fixed' 
              }}>
                <thead>
                  <tr style={{ 
                    background: 'linear-gradient(135deg, #EBF2FF 0%, #F8FAFF 100%)'
                  }}>
                    <th style={{ 
                      padding: '18px 24px', 
                      textAlign: 'left', 
                      fontWeight: 600, 
                      color: '#334155',
                      fontSize: '14px',
                      letterSpacing: '0.5px',
                      textTransform: 'uppercase',
                      borderBottom: '1px solid rgba(230, 235, 245, 0.6)'
                    }}>
                      Item Name
                    </th>
                    <th style={{ 
                      padding: '18px 24px', 
                      textAlign: 'left', 
                      fontWeight: 600, 
                      color: '#334155',
                      fontSize: '14px',
                      letterSpacing: '0.5px',
                      textTransform: 'uppercase',
                      borderBottom: '1px solid rgba(230, 235, 245, 0.6)'
                    }}>
                      Menu List
                    </th>
                    {showQuantityColumn && (
                      <th style={{ 
                        padding: '18px 24px', 
                        textAlign: 'center', 
                        fontWeight: 600, 
                        color: '#334155',
                        fontSize: '14px',
                        letterSpacing: '0.5px',
                        textTransform: 'uppercase',
                        borderBottom: '1px solid rgba(230, 235, 245, 0.6)',
                        width: '140px'
                      }}>
                        Quantity
                      </th>
                    )}
                    {showBuffetColumns && (
                      <>
                        <th style={{ 
                          padding: '18px 24px', 
                          textAlign: 'center', 
                          fontWeight: 600, 
                          color: '#334155',
                          fontSize: '14px',
                          letterSpacing: '0.5px',
                          textTransform: 'uppercase',
                          borderBottom: '1px solid rgba(230, 235, 245, 0.6)',
                          width: '120px'
                        }}>
                          Adults
                        </th>
                        {searchParams?.children > 0 && (
                          <th style={{ 
                            padding: '18px 24px', 
                            textAlign: 'center', 
                            fontWeight: 600, 
                            color: '#334155',
                            fontSize: '14px',
                            letterSpacing: '0.5px',
                            textTransform: 'uppercase',
                            borderBottom: '1px solid rgba(230, 235, 245, 0.6)',
                            width: '120px'
                          }}>
                            Children
                          </th>
                        )}
                      </>
                    )}
                    <th style={{ 
                      padding: '18px 24px', 
                      textAlign: 'right', 
                      fontWeight: 600, 
                      color: '#334155',
                      fontSize: '14px',
                      letterSpacing: '0.5px',
                      textTransform: 'uppercase',
                      borderBottom: '1px solid rgba(230, 235, 245, 0.6)',
                      width: '140px'
                    }}>
                      Price
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {selectedMealParts.length > 0 ? (
                    selectedMealParts.map((part, index) => (
                      <tr 
                        key={index} 
                        style={{ 
                          borderBottom: '1px solid rgba(230, 235, 245, 0.6)',
                          backgroundColor: selectedMealIndex === index || selectedMealIndexes.includes(index) 
                            ? 'rgba(53, 84, 209, 0.04)' 
                            : index % 2 === 0 
                              ? 'rgba(255, 255, 255, 0.7)' 
                              : 'rgba(248, 250, 252, 0.7)',
                          transition: 'background-color 0.2s ease'
                        }}
                        onMouseEnter={(e) => {
                          e.currentTarget.style.backgroundColor = 'rgba(53, 84, 209, 0.06)';
                        }}
                        onMouseLeave={(e) => {
                          if (selectedMealIndex === index || selectedMealIndexes.includes(index)) {
                            e.currentTarget.style.backgroundColor = 'rgba(53, 84, 209, 0.04)';
                          } else {
                            e.currentTarget.style.backgroundColor = index % 2 === 0 
                              ? 'rgba(255, 255, 255, 0.7)' 
                              : 'rgba(248, 250, 252, 0.7)';
                          }
                        }}
                      >
                        <td style={{ padding: '20px 24px', color: '#334155' }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                            {specificMealType !== "Buffet" && (
                              <Box 
                                sx={{ 
                                  display: 'flex', 
                                  alignItems: 'center', 
                                  gap: '8px',
                                  minWidth: '64px'
                                }}
                              >
                                {/* Food Type Icon with enhanced styling */}
                                {part.item_type === 'Veg' ? (
                                  <Tooltip title="Vegetarian" arrow placement="top">
                                    <Box sx={{ 
                                      position: 'relative', 
                                      display: 'inline-flex',
                                      backgroundColor: 'rgba(46, 204, 113, 0.12)',
                                      padding: '6px',
                                      borderRadius: '8px'
                                    }}>
                                      <SquareIcon sx={{ color: '#2ecc71', fontSize: 24 }} />
                                      <CircleIcon sx={{ color: '#fff', fontSize: 12, position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)' }} />
                                    </Box>
                                  </Tooltip>
                                ) : part.item_type === 'Non Veg' ? (
                                  <Tooltip title="Non-Vegetarian" arrow placement="top">
                                    <Box sx={{ 
                                      position: 'relative', 
                                      display: 'inline-flex',
                                      backgroundColor: 'rgba(231, 76, 60, 0.12)',
                                      padding: '6px',
                                      borderRadius: '8px'
                                    }}>
                                      <SquareIcon sx={{ color: '#e74c3c', fontSize: 24 }} />
                                      <CircleIcon sx={{ color: '#fff', fontSize: 12, position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)' }} />
                                    </Box>
                                  </Tooltip>
                                ) : null}
                                
                                {/* Drink Type Icon with enhanced styling */}
                                {part.category === 'Alcoholic' ? (
                                  <Tooltip title="Alcoholic Beverage" arrow placement="top">
                                    <Box sx={{ 
                                      display: 'inline-flex',
                                      backgroundColor: 'rgba(231, 76, 60, 0.12)',
                                      padding: '6px',
                                      borderRadius: '8px'
                                    }}>
                                      <DrinkIcon sx={{ color: '#e74c3c', fontSize: 24 }} />
                                    </Box>
                                  </Tooltip>
                                ) : part.category === 'Non Alcoholic' ? (
                                  <Tooltip title="Non-Alcoholic Beverage" arrow placement="top">
                                    <Box sx={{ 
                                      display: 'inline-flex',
                                      backgroundColor: 'rgba(46, 204, 113, 0.12)',
                                      padding: '6px',
                                      borderRadius: '8px'
                                    }}>
                                      <NoDrinksIcon sx={{ color: '#2ecc71', fontSize: 24 }} />
                                    </Box>
                                  </Tooltip>
                                ) : null}
                              </Box>
                            )}
                            <Box sx={{ display: 'flex', flexDirection: 'column' }}>
                              <Typography sx={{ 
                                fontWeight: 600,
                                fontSize: '16px',
                                color: '#1E293B'
                              }}>
                                {part.item_name || "Unnamed Item"}
                              </Typography>
                              {part.category && (
                                <Typography sx={{
                                  fontSize: '13px',
                                  color: '#64748B',
                                  marginTop: '2px'
                                }}>
                                  {/* {part.category} */}
                                </Typography>
                              )}
                            </Box>
                          </div>
                        </td>
                        <td style={{ padding: '20px 24px' }}>
                          {specificMealType === "A la carte" ? (
                            <FormControlLabel
                              control={
                                <Checkbox
                                  checked={selectedMealIndexes.includes(index)}
                                  onChange={() => handleCheckboxChange(index)}
                                  sx={{
                                    color: '#a3b1d1',
                                    '&.Mui-checked': {
                                      color: '#3554D1',
                                    },
                                    '& .MuiSvgIcon-root': {
                                      fontSize: 24
                                    }
                                  }}
                                />
                              }
                              label={
                                <MealDescription 
                                  description={part.name}
                                  index={index}
                                  expandedDescriptions={expandedDescriptions}
                                  toggleDescription={toggleDescription}
                                  openDescriptionModal={openDescriptionModal}
                                />
                              }
                              sx={{ margin: 0 }}
                            />
                          ) : (
                            <FormControlLabel
                              control={
                                <Radio
                                  checked={selectedMealIndex === index}
                                  onChange={() => handleRadioChange(index)}
                                  sx={{
                                    color: '#a3b1d1',
                                    '&.Mui-checked': {
                                      color: '#3554D1',
                                    },
                                    '& .MuiSvgIcon-root': {
                                      fontSize: 24
                                    }
                                  }}
                                />
                              }
                              label={
                                <MealDescription 
                                  description={part.name}
                                  index={index}
                                  expandedDescriptions={expandedDescriptions}
                                  toggleDescription={toggleDescription}
                                  openDescriptionModal={openDescriptionModal}
                                />
                              }
                              sx={{ margin: 0 }}
                            />
                          )}
                        </td>
                        {showQuantityColumn && (
                          <td style={{ padding: '20px 24px', textAlign: 'center' }}>
                            <Box sx={{ 
                              display: 'flex', 
                              alignItems: 'center', 
                              justifyContent: 'center',
                              gap: '4px'
                            }}>
                              <Button
                                onClick={() => handleQuantityChange(index, -1)}
                                variant="outlined"
                                sx={{
                                  minWidth: '36px',
                                  width: '36px',
                                  height: '36px',
                                  padding: 0,
                                  borderRadius: '8px',
                                  border: '1px solid rgba(53, 84, 209, 0.3)',
                                  color: '#3554D1',
                                  '&:hover': {
                                    backgroundColor: 'rgba(53, 84, 209, 0.04)',
                                  },
                                }}
                              >
                                <Typography sx={{ fontSize: '18px', fontWeight: 600 }}>-</Typography>
                              </Button>
                              <Typography 
                                sx={{ 
                                  margin: '0 12px', 
                                  fontSize: '16px', 
                                  fontWeight: 600,
                                  color: '#3554D1',
                                  minWidth: '30px'
                                }}
                              >
                                {part.quantity || 1}
                              </Typography>
                              <Button
                                onClick={() => handleQuantityChange(index, 1)}
                                variant="outlined"
                                sx={{
                                  minWidth: '36px',
                                  width: '36px',
                                  height: '36px',
                                  padding: 0,
                                  borderRadius: '8px',
                                  border: '1px solid rgba(53, 84, 209, 0.3)',
                                  color: '#3554D1',
                                  '&:hover': {
                                    backgroundColor: 'rgba(53, 84, 209, 0.04)',
                                  },
                                }}
                              >
                                <Typography sx={{ fontSize: '18px', fontWeight: 600 }}>+</Typography>
                              </Button>
                            </Box>
                          </td>
                        )}
                        {showBuffetColumns && (
                          <>
                            <td style={{ 
                              padding: '20px 24px', 
                              textAlign: 'center', 
                              color: '#3554D1', 
                              fontWeight: 600,
                              fontSize: '16px'
                            }}>
                              {searchParams?.adults || 0}
                            </td>
                            {searchParams?.children > 0 && (
                              <td style={{ 
                                padding: '20px 24px', 
                                textAlign: 'center', 
                                color: '#3554D1', 
                                fontWeight: 600,
                                fontSize: '16px'
                              }}>
                                {searchParams?.children || 0}
                              </td>
                            )}
                          </>
                        )}
                        <td style={{ 
                          padding: '20px 24px', 
                          textAlign: 'right'
                        }}>
                          {part.price !== null && part.price !== undefined && PriceHide === "0" ? (
                            <Box sx={{ 
                              display: 'flex', 
                              flexDirection: 'column',
                              alignItems: 'flex-end'
                            }}>
                              <Typography sx={{ 
                                color: '#3554D1', 
                                fontWeight: 700,
                                fontSize: '18px'
                              }}>
                                S$ {calculateItemPrice(part).toFixed(2)}
                              </Typography>
                              <Typography sx={{ 
                                color: '#64748B',
                                fontSize: '12px',
                                fontWeight: 500
                              }}>
                                
                              </Typography>
                            </Box>
                          ) : part.price !== null && part.price !== undefined ? (
                            <Box sx={{ 
                              display: 'flex', 
                              flexDirection: 'column',
                              alignItems: 'flex-end'
                            }}>
                              <Typography sx={{ 
                                color: '#3554D1', 
                                fontWeight: 700,
                                fontSize: '18px'
                              }}>
                                Price Available
                              </Typography>
                            </Box>
                          ) : (
                            <Typography sx={{ color: '#94A3B8', fontStyle: 'italic' }}>
                              Price not available
                            </Typography>
                          )}
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td 
                        colSpan={showBuffetColumns ? (searchParams?.children > 0 ? 6 : 5) : (showQuantityColumn ? 4 : 3)} 
                        style={{ 
                          padding: '40px 24px', 
                          textAlign: 'center', 
                          color: '#64748B' 
                        }}
                      >
                        <Box sx={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '16px' }}>
                          <RestaurantMenuIcon sx={{ fontSize: 48, color: '#94A3B8', opacity: 0.6 }} />
                          <Typography sx={{ fontSize: '16px', fontWeight: 500 }}>
                            No meal options available
                          </Typography>
                        </Box>
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </Box>
          </DialogContent>
          
          <DialogActions 
            sx={{
              padding: '20px 32px',
              borderTop: '1px solid rgba(230, 235, 245, 0.6)',
              background: 'linear-gradient(135deg, #f8faff 0%, #ffffff 100%)',
              borderBottomLeftRadius: '16px',
              borderBottomRightRadius: '16px',
              display: 'flex',
              justifyContent: 'space-between'
            }}
          >
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              {!isConfirmDisabled && (
                <Typography sx={{ color: '#64748B', fontSize: '14px', fontWeight: 500 }}>
                  {specificMealType === "A la carte" 
                    ? `${selectedMealIndexes.length} item${selectedMealIndexes.length !== 1 ? 's' : ''} selected` 
                    : 'Ready to confirm'}
                </Typography>
              )}
            </Box>
            
            <Box sx={{ display: 'flex', gap: '12px' }}>
              <Button
                onClick={() => setOpen(false)}
                sx={{
                  padding: '10px 24px',
                  borderRadius: '10px',
                  textTransform: 'none',
                  backgroundColor: 'transparent',
                  color: '#64748B',
                  border: '1px solid #E2E8F0',
                  fontWeight: 600,
                  transition: 'all 0.2s ease',
                  '&:hover': {
                    backgroundColor: '#F8FAFC',
                    borderColor: '#CBD5E1',
                  },
                }}
              >
                Cancel
              </Button>
              <Button
                onClick={handleConfirm}
                disabled={isConfirmDisabled}
                sx={{
                  padding: '10px 28px',
                  borderRadius: '10px',
                  textTransform: 'none',
                  backgroundColor: '#3554D1',
                  color: 'white',
                  fontWeight: 600,
                  boxShadow: '0 4px 12px rgba(53, 84, 209, 0.15)',
                  transition: 'all 0.2s ease',
                  '&:hover': {
                    backgroundColor: '#2A44B0',
                    boxShadow: '0 6px 16px rgba(53, 84, 209, 0.25)',
                    transform: 'translateY(-2px)'
                  },
                  '&.Mui-disabled': {
                    backgroundColor: '#E2E8F0',
                    color: '#94A3B8',
                    boxShadow: 'none',
                  },
                }}
              >
                Confirm
              </Button>
            </Box>
          </DialogActions>
        </Dialog>
      </Modal>

      {/* New Description Modal */}
      <DescriptionModal 
        open={descriptionModalOpen} 
        onClose={() => setDescriptionModalOpen(false)}
        description={currentDescription}
        mealType={mealType}
      />

      {/* ... other modals and components ... */}

      <ToastContainer />
    </>
  );
};

export default Index;
