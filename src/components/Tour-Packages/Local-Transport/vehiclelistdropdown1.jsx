import React, { useEffect, useRef } from 'react';
import { 
  Grid, 
  Autocomplete, 
  TextField, 
  Tooltip, 
  Box,
  Typography,
  Stack,
  Chip,
  Paper,
  styled,
  FormControl,
  FormLabel,
  Select,
  MenuItem,
  InputLabel
} from '@mui/material';
import { useSelector } from "react-redux";
import LocationOnIcon from '@mui/icons-material/LocationOn';
import DirectionsCarIcon from '@mui/icons-material/DirectionsCar';
import EventSeatIcon from '@mui/icons-material/EventSeat';
import PeopleIcon from '@mui/icons-material/People';
import LocalOfferIcon from '@mui/icons-material/LocalOffer';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import { useDispatch } from 'react-redux';
import { useState } from 'react';
import { fetchVehicleDetails } from '../../../slice/localtour/Localslice';
// Import our new simplified components instead
import SimplePassenger from './SimplePassenger';
import SimpleHourlyPackage from './SimpleHourlyPackage';
import HourlyPackage from './HourlyPackage';
import Passenger from './Passenger';

// Custom styled tooltip
const CustomTooltip = styled(({ className, ...props }) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
  '& .MuiTooltip-tooltip': {
    backgroundColor: 'white',
    color: 'rgba(0, 0, 0, 0.87)',
    maxWidth: 350,
    border: '1px solid #dadde9',
    borderRadius: '10px',
    padding: 0,
    boxShadow: theme.shadows[2]
  },
}));

// Tooltip content component
const TooltipContent = ({ vehicle }) => {
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  return (
    <Box>
      {/* Header Image Section */}
      <Box sx={{ position: 'relative', width: '100%', height: 160 }}>
        <Box
          component="img"
          src={vehicle.image}
          alt={vehicle.vehicle_name}
          sx={{
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            borderTopLeftRadius: '10px',
            borderTopRightRadius: '10px',
          }}
        />
      </Box>

      {/* Content Section */}
      <Box sx={{ p: 1.5 }}>
        {/* Title and Location */}
        <Typography variant="h6" gutterBottom sx={{ fontWeight: 'bold', fontSize: '1rem' }}>
          {vehicle.vehicle_name}
        </Typography>
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
          <LocationOnIcon sx={{ fontSize: 16, color: 'text.secondary', mr: 0.5 }} />
          <Typography variant="body2" color="text.secondary" sx={{ fontSize: '0.85rem' }}>
            {vehicle.city}, {vehicle.country}
          </Typography>
        </Box>

        {/* Vehicle Details */}
        <Box sx={{ mb: 1.5 }}>
          <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500, fontSize: '0.9rem' }}>
            Vehicle Details
          </Typography>
          <Stack spacing={1}>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <DirectionsCarIcon sx={{ fontSize: 16, color: 'text.secondary', mr: 0.8 }} />
              <Typography variant="body2" sx={{ fontSize: '0.8rem' }}>
                <strong>Type:</strong> {vehicle.vehicle_type}
              </Typography>
            </Box>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <DirectionsCarIcon sx={{ fontSize: 16, color: 'text.secondary', mr: 0.8 }} />
              <Typography variant="body2" sx={{ fontSize: '0.8rem' }}>
                <strong>Model:</strong> {vehicle.vehicle_model}
              </Typography>
            </Box>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <DirectionsCarIcon sx={{ fontSize: 16, color: 'text.secondary', mr: 0.8 }} />
              <Typography variant="body2" sx={{ fontSize: '0.8rem' }}>
                <strong>Year:</strong> {vehicle.model_year}
              </Typography>
            </Box>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <EventSeatIcon sx={{ fontSize: 16, color: 'text.secondary', mr: 0.8 }} />
              <Typography variant="body2" sx={{ fontSize: '0.8rem' }}>
                <strong>Seating Capacity:</strong> {vehicle.seating_capacity}
              </Typography>
            </Box>
          </Stack>
        </Box>

        {/* Pricing Section */}
        <Box sx={{ mt: 1.5 }}>
          <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500, fontSize: '0.9rem' }}>
            Pricing Details
          </Typography>
          {PriceHide !== "1" ? (
          <>
          <Grid container spacing={1.5}>
            {/* DMC Prices */}
            {(vehicle.dmc_private_price > 0 || vehicle.dmc_sharable_price > 0) && (
              <Grid item xs={6}>
                <Paper 
                  variant="outlined" 
                  sx={{ 
                    p: 1,
                    bgcolor: 'rgba(25, 118, 210, 0.02)',
                    borderColor: 'rgba(25, 118, 210, 0.1)'
                  }}
                >
                  <Typography variant="subtitle2" gutterBottom sx={{ color: 'primary.main', fontWeight: 500, fontSize: '0.8rem' }}>
                    DMC Prices
                  </Typography>
                  <Stack spacing={0.4}>
                    {vehicle.dmc_private_price > 0 && (
                      <Typography variant="body2" sx={{ fontSize: '0.75rem' }}>Private: ${vehicle.dmc_private_price}</Typography>
                    )}
                    {vehicle.dmc_sharable_price > 0 && (
                      <Typography variant="body2" sx={{ fontSize: '0.75rem' }}>Sharable: ${vehicle.dmc_sharable_price}</Typography>
                    )}
                  </Stack>
                </Paper>
              </Grid>
            )}

            {/* Travclicks Prices */}
            {(vehicle.trav_private_price > 0 || vehicle.trav_sharable_price > 0) && (
              <Grid item xs={6}>
                <Paper 
                  variant="outlined" 
                  sx={{ 
                    p: 1,
                    bgcolor: 'rgba(76, 175, 80, 0.02)',
                    borderColor: 'rgba(76, 175, 80, 0.1)'
                  }}
                >
                  <Typography variant="subtitle2" gutterBottom sx={{ color: '#2e7d32', fontWeight: 500, fontSize: '0.8rem' }}>
                    Travclicks Prices
                  </Typography>
                  <Stack spacing={0.4}>
                    {vehicle.trav_private_price > 0 && (
                      <Typography variant="body2" sx={{ fontSize: '0.75rem' }}>Private: ${vehicle.trav_private_price}</Typography>
                    )}
                    {vehicle.trav_sharable_price > 0 && (
                      <Typography variant="body2" sx={{ fontSize: '0.75rem' }}>Sharable: ${vehicle.trav_sharable_price}</Typography>
                    )}
                  </Stack>
                </Paper>
              </Grid>
            )}
          </Grid>
         
          {/* Tax Information */}
          {vehicle.tax_percentage && (
            <Typography 
              variant="caption" 
              sx={{ 
                display: 'block',
                mt: 0.8,
                color: 'text.secondary',
                fontStyle: 'italic',
                fontSize: '0.7rem'
              }}
            >
              *Prices are subject to {vehicle.tax_percentage}% tax
            </Typography>
          )}
          </>
        ):(
          <Typography variant="caption" gutterBottom sx={{ color: 'text.secondary', fontWeight: 500, fontSize: '0.75rem' }}>
            Pricing hidden
          </Typography>
        )}
        </Box>
      </Box>
    </Box>
  );
};

// Updated Mode component with dropdown instead of radio buttons
const Mode = ({ pricemode, setpricemode, vehicles }) => {
  // Return null if no vehicle data is available
  if (!vehicles || !vehicles.prices) return null;
  
  // Check which price modes are available
  const hasPrivatePrice = vehicles.prices.day_private_price > 0;
  const hasSharablePrice = vehicles.prices.day_sharable_price > 0;
  
  // If no pricing options available, return null
  if (!hasPrivatePrice && !hasSharablePrice) return null;
  
  // State to control dropdown open/close
  const [open, setOpen] = useState(false);
  
  const handleOpen = (e) => {
    e.stopPropagation();
    e.preventDefault();
    setOpen(true);
  };
  
  const handleClose = (e) => {
    if (e) {
      e.stopPropagation();
      e.preventDefault();
    }
    setOpen(false);
  };
  
  const handleChange = (e) => {
    e.stopPropagation();
    setpricemode(e.target.value);
  };
  
  // Set default mode on first render
  useEffect(() => {
    // If no mode is selected yet, set a default
    if (!pricemode) {
      if (hasPrivatePrice) {
        setpricemode("Private");
      } else if (hasSharablePrice) {
        setpricemode("Sharable");
      }
    }
  }, [vehicles, pricemode, hasPrivatePrice, hasSharablePrice]); // Removed setpricemode to prevent loops
  
  return (
    <Grid item xs={12} sm={6} md={12}>
      <Box onClick={(e) => e.stopPropagation()} onMouseDown={(e) => e.stopPropagation()}>
        <FormControl fullWidth>
          <InputLabel 
            id="price-mode-label"
            sx={{
              transform: 'translate(14px, 16px) scale(1)',
              '&.Mui-focused, &.MuiFormLabel-filled': {
                transform: 'translate(14px, -9px) scale(0.75)',
                color: '#ff9800'
              }
            }}
          >
            Price Mode
          </InputLabel>
          <Select
            labelId="price-mode-label"
            id="price-mode-select"
            value={pricemode}
            label="Price Mode"
            open={open}
            onOpen={handleOpen}
            onClose={handleClose}
            onChange={handleChange}
            onClick={(e) => e.stopPropagation()}
            onMouseDown={(e) => e.stopPropagation()}
            sx={{
              height: '42px',
              '& .MuiSelect-select': {
                height: '42px',
                paddingTop: '15px',
                paddingBottom: '0px'
              },
              '& .MuiOutlinedInput-notchedOutline': {
                borderColor: '#e0e0e0'
              },
              '&:hover .MuiOutlinedInput-notchedOutline': {
                borderColor: '#ff9800'
              },
              '&.Mui-focused .MuiOutlinedInput-notchedOutline': {
                borderColor: '#ff9800',
                borderWidth: '2px'
              }
            }}
            MenuProps={{
              disableScrollLock: true,
              slotProps: {
                paper: {
                  onMouseDown: (e) => e.stopPropagation(),
                  onClick: (e) => e.stopPropagation(),
                  style: { zIndex: 9999 }
                }
              }
            }}
          >
            {hasPrivatePrice && (
              <MenuItem value="Private" onClick={(e) => e.stopPropagation()}>Private</MenuItem>
            )}
            {hasSharablePrice && (
              <MenuItem value="Sharable" onClick={(e) => e.stopPropagation()}>Sharable</MenuItem>
            )}
          </Select>
        </FormControl>
      </Box>
    </Grid>
  ); 
};

const VehicleListDropdown = ({ 
  selectedVehicle, 
  onVehicleChange, 
  onPaxChange, 
  onPriceModeChange, 
  onHourChange, 
  onHourlyPriceChange,
  sectionIndex, 
  isNewBooking,
  cachedVehicles,
  cachedVehicleName,
  isGridLayout = false,
  preloadedBooking = null,
  onAddMore = null,
  Hourly,
}) => {
  const vehicles = useSelector((state) => state.localtour.vehicles || []);
  const portZoneType = useSelector((state) => state.localtour.portZoneType);
  const dispatch = useDispatch();
  const tourDetails = useSelector((state) => state.hotels?.tourdetails);
  const entryytime = useSelector((state) => state.localtour.entrytime);
  const selectedPort = useSelector((state) => state.localtour.selectedPort);

  // Use cached vehicles if provided, otherwise use Redux vehicles
  const vehiclesToUse = cachedVehicles && cachedVehicles.length > 0 ? cachedVehicles : vehicles;
  
  // Find the currently selected vehicle from our list
  const selectedVehicleObj = vehiclesToUse.find(v => v.id === selectedVehicle) || null;

  // Use optional chaining for safe access to nested properties
  const adultsMax = tourDetails?.data?.adult || tourDetails?.adult || 1;
  const childrenMax = tourDetails?.data?.child || tourDetails?.child || 0;
  const [selectedHours, setSelectedHours] = useState(preloadedBooking?.hours || 1);

  const [adults, setAdults] = useState(preloadedBooking?.adults || adultsMax);
  const [children, setChildren] = useState(preloadedBooking?.children || childrenMax);
  const [seatingCapacity, setSeatingCapacity] = useState(0);
  const [data, setData] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const [totalHourlyPrice, setTotalHourlyPrice] = useState(0);
  const [isNight, setIsNight] = useState(false);
  
  // Define totalGuests for price calculation
  const totalGuests = adults + children;
  
  // Update parent component with passenger info when it changes
  useEffect(() => {
    if (onPaxChange) {
      onPaxChange(adults, children);
    }
  }, [adults, children]); // Removed onPaxChange to prevent loops
  
  // Filter vehicles that have at least one pricing mode
  const filteredVehicles = vehiclesToUse.filter(vehicle => {
    const hasDmcPrice = vehicle.dmc_private_price > 0 || vehicle.dmc_sharable_price > 0;
    const hasTravclicksPrice = vehicle.trav_private_price > 0 || vehicle.trav_sharable_price > 0;
    return hasDmcPrice || hasTravclicksPrice;
  });
  
  const handleVehicleClick = (vehicle) => {
    if (!vehicle || !onVehicleChange) return;
  
    const hasDmcPrice = vehicle.dmc_private_price > 0 || vehicle.dmc_sharable_price > 0;
    const hasTravclicksPrice = vehicle.trav_private_price > 0 || vehicle.trav_sharable_price > 0;
  
    const mode = (hasDmcPrice && !hasTravclicksPrice) ? "dmc" : "travclicks";
    const dmcId = (hasDmcPrice && !hasTravclicksPrice) ? vehicle.dmc_id : vehicle.travclicks_dmc_id;
  
    onVehicleChange(vehicle.id, mode, dmcId, vehicle.city, vehicle.country);
  
    // Reset states
    setData(null);
    setError(null);
    setIsLoading(true);
  
    setTimeout(() => {
      dispatch(fetchVehicleDetails({ city: vehicle.city, country: vehicle.country, type: portZoneType }))
      .unwrap() // Unwrap the promise to handle the payload directly
      .then((data) => {
        console.log("data", data);
        setSeatingCapacity(data.seating_capacity || 0);
        setData(data);
        setIsLoading(false);
      })
      .catch((err) => {
        console.error("Error fetching vehicle details:", err);
        setError(err.message || "Failed to load vehicle details");
        setIsLoading(false);
      });
    }, 300); // 300ms delay
  };

  const [pricemode, setpricemode] = useState(preloadedBooking?.priceMode || ""); // Set from preloaded data or default
  
  // Notify parent when price mode changes
  useEffect(() => {
    if (onPriceModeChange && pricemode) {
      onPriceModeChange(pricemode);
    }
  }, [pricemode]); // Removed onPriceModeChange to prevent loops

  
  const calculateHourlyPrice = (hours = selectedHours) => {
    // Use the hours parameter instead of selectedHours
    let totalPrice = 0;
    let currentTime = entryytime || "09:00 AM"; // Using existing entryytime variable with fallback
    let hasNightHours = false; // Track if any night hours are found

    // Early return if data is not available
    if (!data || !data.prices) {
      return 0;
    }

    // Add safety checks for price values to prevent NaN/Infinity
    const safeParseFloat = (value) => {
      const parsed = parseFloat(value);
      return isNaN(parsed) || !isFinite(parsed) ? 0 : parsed;
    };

    // Set base prices based on price mode with safety checks
    const dayPrice = safeParseFloat(
      pricemode === "Sharable"
        ? (data.prices.day_sharable_price || 0) * totalGuests
        : (data.prices.day_private_price || 0)
    );
    
    const daybaseprice = safeParseFloat(
      pricemode === "Sharable"
        ? (data.prices.sharable_day_base_price || 0)
        : (data.prices.private_day_base_price || 0)
    );

    const nightPrice = safeParseFloat(
      pricemode === "Sharable"
        ? (data.prices.night_sharable_price || 0) * totalGuests
        : (data.prices.night_private_price || 0)
    );
    
    const nightbaseprice = safeParseFloat(
      pricemode === "Sharable"
        ? (data.prices.sharable_night_base_price || 0)
        : (data.prices.private_night_base_price || 0)
    );

    // Parse night start and end times with fallback values
    const startNightTime = (data.night_start_time || "20:00:00")
      .split(":")
      .slice(0, 2)
      .join(":");
    const endNightTime = (data.night_end_time || "06:00:00")
      .split(":")
      .slice(0, 2)
      .join(":");

    // Helper function to convert 12-hour format to 24-hour format
    const convertTo24Hour = (time12h) => {
      // Add null check and default value
      if (!time12h || typeof time12h !== 'string') {
        return "09:00"; // Default fallback time
      }

      const timeParts = time12h.split(" ");
      if (timeParts.length !== 2) {
        return "09:00"; // Default fallback if format is incorrect
      }

      const [time, period] = timeParts;
      const timeComponents = time.split(":");
      
      if (timeComponents.length !== 2) {
        return "09:00"; // Default fallback if time format is incorrect
      }

      let [hours, minutes] = timeComponents.map(Number);

      // Check if hours and minutes are valid numbers
      if (isNaN(hours) || isNaN(minutes)) {
        return "09:00"; // Default fallback for invalid numbers
      }

      if (period === "PM" && hours !== 12) {
        hours += 12;
      } else if (period === "AM" && hours === 12) {
        hours = 0;
      }

      return `${hours.toString().padStart(2, "0")}:${minutes
        .toString()
        .padStart(2, "0")}`;
    };

    // Convert initial time to 24-hour format
    let currentTime24h = convertTo24Hour(currentTime);

    // Helper function to add hours to time (in 24-hour format)
    const addHour = (time24h) => {
      const [hours, minutes] = time24h.split(":").map(Number);

      // Add 1 hour
      let newHours = hours + 1;

      // Handle overflow (24-hour format)
      if (newHours >= 24) {
        newHours = newHours - 24;
      }

      return `${newHours.toString().padStart(2, "0")}:${minutes
        .toString()
        .padStart(2, "0")}`;
    };

    // Helper function to check if a time is between night start and end
    const isNightTime = (time24h) => {
      // Handle case where night period crosses midnight
      if (startNightTime > endNightTime) {
        return time24h >= startNightTime || time24h < endNightTime;
      } else {
        return time24h >= startNightTime && time24h < endNightTime;
      }
    };

    // Loop through each hour and calculate price
    for (let i = 0; i < hours; i++) {
      // Check if current time is within night hours
      if (isNightTime(currentTime24h)) {
        const hourPrice = safeParseFloat(nightPrice) + safeParseFloat(nightbaseprice);
        totalPrice += hourPrice;
        hasNightHours = true; // Mark that we found a night hour
      } else {
        const hourPrice = safeParseFloat(dayPrice) + safeParseFloat(daybaseprice);
        totalPrice += hourPrice;
      }

      // Add 1 hour to current time for next iteration
      currentTime24h = addHour(currentTime24h);
    }

    // Set isNight based on whether any night hours were found
    setIsNight(hasNightHours);

    // Final safety check to prevent NaN/Infinity
    const finalPrice = safeParseFloat(totalPrice);
    return finalPrice;
  };
  
  useEffect(() => {
    if (data && data.prices && pricemode && selectedHours > 0) {
      const calculatedPrice = calculateHourlyPrice(selectedHours);
      // Add safety check to prevent setting NaN or Infinity
      if (calculatedPrice && isFinite(calculatedPrice) && calculatedPrice > 0) {
        setTotalHourlyPrice(calculatedPrice);
      } else {
        console.warn('Invalid calculated price:', calculatedPrice, 'for data:', data);
        setTotalHourlyPrice(0);
      }
    }
  }, [selectedHours, pricemode, entryytime, data, totalGuests]);

  // Pass the hourly price to parent component when it changes
  useEffect(() => {
    if (onHourlyPriceChange && totalHourlyPrice > 0 && isFinite(totalHourlyPrice)) {
      onHourlyPriceChange(totalHourlyPrice);
    }
  }, [totalHourlyPrice]); // Removed onHourlyPriceChange to prevent loops
  //console.log("totalHourlyPrice", totalHourlyPrice);
 
  const FinalPrice = totalHourlyPrice;

  // Handle adult count change for this specific booking
  const handleAdultChange = (value) => {
    setAdults(value);
  };

  // Handle child count change for this specific booking
  const handleChildChange = (value) => {
    setChildren(value);
  };

  // Initialize data when preloaded booking is available
  useEffect(() => {
    if (preloadedBooking && preloadedBooking.vehicleId && preloadedBooking.price > 0) {
      console.log("Hourly - Initializing with preloaded booking data:", preloadedBooking);
      
      // Calculate price per hour for the preloaded booking
      const pricePerHour = preloadedBooking.hours > 0 ? preloadedBooking.price / preloadedBooking.hours : preloadedBooking.price;
      
      // Set up mock data structure for preloaded booking - Hourly has different price structure
      const mockData = {
        prices: {
          day_private_price: preloadedBooking.priceMode === "Private" ? pricePerHour : 0,
          day_sharable_price: preloadedBooking.priceMode === "Sharable" ? pricePerHour : 0,
          night_private_price: preloadedBooking.priceMode === "Private" ? pricePerHour : 0,
          night_sharable_price: preloadedBooking.priceMode === "Sharable" ? pricePerHour : 0,
          private_day_base_price: 0,
          sharable_day_base_price: 0,
          sharable_night_base_price: 0,
          private_night_base_price: 0
        },
        night_start_time: "20:00:00",
        night_end_time: "06:00:00"
      };
      
      setData(mockData);
      setSeatingCapacity(0); // Will be updated if needed
      
      // Set the total price directly from preloaded booking
      const finalPrice = preloadedBooking.price || 0;
      setTotalHourlyPrice(finalPrice);
      
      // Trigger price change to parent
      if (onHourlyPriceChange) {
        onHourlyPriceChange(finalPrice);
      }
      
      // Trigger hour change to parent
      if (onHourChange && preloadedBooking.hours) {
        onHourChange(preloadedBooking.hours);
      }
    }
  }, [preloadedBooking?.vehicleId, preloadedBooking?.price, preloadedBooking?.priceMode, preloadedBooking?.hours]); // Added hours dependency

  // If it's grid layout, return just the autocomplete for the vehicle selection column
  if (isGridLayout) {
    return (
      <Box onClick={(e) => e.stopPropagation()} onMouseDown={(e) => e.stopPropagation()} sx={{ width: '100%' }}>
        <Autocomplete
          key={`vehicle-autocomplete-${sectionIndex}`}
          value={selectedVehicleObj}
          onChange={(event, newValue) => {
            event.stopPropagation();
            handleVehicleClick(newValue);
          }}
          options={filteredVehicles}
          getOptionLabel={(option) => option.vehicle_name || cachedVehicleName || ''}
          renderOption={(props, option) => {
            const { key, ...otherProps } = props;
            return (
              <CustomTooltip
                key={`tooltip-${option.id}`}
                title={<TooltipContent vehicle={option} />}
                placement="right"
                arrow
              >
                <Box 
                  key={`option-${option.id}`}
                  component="li" 
                  {...otherProps} 
                  onClick={(e) => {
                    e.stopPropagation();
                    props.onClick(e);
                  }}
                >
                  {option.vehicle_name}
                  {/* Add a small indicator for available pricing modes */}
                  <Box sx={{ ml: 'auto', display: 'flex', gap: 0.5 }}>
                    {(option.dmc_private_price > 0 || option.dmc_sharable_price > 0) && (
                      <Chip 
                        key={`dmc-${option.id}`}
                        size="small" 
                        label="DMC"
                        sx={{ 
                          height: 20,
                          fontSize: '0.7rem',
                          bgcolor: 'rgba(25, 118, 210, 0.08)',
                          color: 'primary.main'
                        }}
                      />
                    )}
                    {(option.trav_private_price > 0 || option.trav_sharable_price > 0) && (
                      <Chip 
                        key={`travclicks-${option.id}`}
                        size="small" 
                        label="Travclicks"
                        sx={{ 
                          height: 20,
                          fontSize: '0.7rem',
                          bgcolor: 'rgba(76, 175, 80, 0.08)',
                          color: '#2e7d32'
                        }}
                      />
                    )}
                  </Box>
                </Box>
              </CustomTooltip>
            );
          }}
          renderInput={(params) => (
            <TextField
              {...params}
              label=""
              placeholder="Search Vehicle"
              fullWidth
              size="small"
              // Display cached vehicle name if we have it but the vehicle isn't in the list
              {...(cachedVehicleName && !selectedVehicleObj && {
                InputProps: {
                  ...params.InputProps,
                  placeholder: cachedVehicleName
                }
              })}
            />
          )}
          componentsProps={{
            popper: {
              sx: { zIndex: 9999 },
              onClick: (e) => e.stopPropagation(),
              onMouseDown: (e) => e.stopPropagation()
            },
            paper: {
              onClick: (e) => e.stopPropagation(),
              onMouseDown: (e) => e.stopPropagation()
            }
          }}
          size="small"
        />
      </Box>
    );
  }

  return (
    <Grid container spacing={2} sx={{ mt: 0, p: 2 }}>
      {/* Vehicle Selection Column */}
      <Grid item xs={12} md={3} sx={{mt: 0}}>
    
          <Typography 
            variant="subtitle2" 
            fontWeight={600} 
            sx={{ 
              mb: 1.5, 
              display: 'flex', 
              alignItems: 'center', 
              gap: 0.8,
              color: '#ff6b6b',
              fontSize: '0.9rem'
            }}
          >
            <DirectionsCarIcon sx={{ fontSize: 18, color: '#ff6b6b' }} />
            Vehicle Selection
          </Typography>
          <Box onClick={(e) => e.stopPropagation()} onMouseDown={(e) => e.stopPropagation()}>
            <Autocomplete
              key={`vehicle-autocomplete-${sectionIndex}`}
              value={selectedVehicleObj}
              onChange={(event, newValue) => {
                event.stopPropagation();
                handleVehicleClick(newValue);
              }}
              options={filteredVehicles}
              getOptionLabel={(option) => option.vehicle_name || cachedVehicleName || ''}
              renderOption={(props, option) => {
                const { key, ...otherProps } = props;
                return (
                  <CustomTooltip
                    key={`tooltip-${option.id}`}
                    title={<TooltipContent vehicle={option} />}
                    placement="right"
                    arrow
                  >
                    <Box 
                      key={`option-${option.id}`}
                      component="li" 
                      {...otherProps} 
                      onClick={(e) => {
                        e.stopPropagation();
                        props.onClick(e);
                      }}
                    >
                      {option.vehicle_name}
                      <Box sx={{ ml: 'auto', display: 'flex', gap: 0.5 }}>
                        {(option.dmc_private_price > 0 || option.dmc_sharable_price > 0) && (
                          <Chip 
                            key={`dmc-${option.id}`}
                            size="small" 
                            label="DMC"
                            sx={{ 
                              height: 20,
                              fontSize: '0.7rem',
                              bgcolor: 'rgba(25, 118, 210, 0.08)',
                              color: 'primary.main'
                            }}
                          />
                        )}
                        {(option.trav_private_price > 0 || option.trav_sharable_price > 0) && (
                          <Chip 
                            key={`travclicks-${option.id}`}
                            size="small" 
                            label="Travclicks"
                            sx={{ 
                              height: 20,
                              fontSize: '0.7rem',
                              bgcolor: 'rgba(76, 175, 80, 0.08)',
                              color: '#2e7d32'
                            }}
                          />
                        )}
                      </Box>
                    </Box>
                  </CustomTooltip>
                );
              }}
              renderInput={(params) => (
                <TextField
                  {...params}
                  label=""
                  placeholder="Search & Select Vehicle"
                  fullWidth
                  size="small"
                  sx={{
                    '& .MuiOutlinedInput-root': {
                      borderRadius: 2,
                      '&:hover fieldset': {
                        borderColor: '#ff6b6b',
                      },
                      '&.Mui-focused fieldset': {
                        borderColor: '#ff6b6b',
                      }
                    }
                  }}
                  {...(cachedVehicleName && !selectedVehicleObj && {
                    InputProps: {
                      ...params.InputProps,
                      placeholder: cachedVehicleName
                    }
                  })}
                />
              )}
              componentsProps={{
                popper: {
                  sx: { zIndex: 9999 },
                  onClick: (e) => e.stopPropagation(),
                  onMouseDown: (e) => e.stopPropagation()
                },
                paper: {
                  onClick: (e) => e.stopPropagation(),
                  onMouseDown: (e) => e.stopPropagation()
                }
              }}
            />
          </Box>
        
      </Grid>

      {/* Passenger Selection Column */}
      <Grid item xs={12} md={3}>
    
          <Typography 
            variant="subtitle2" 
            fontWeight={600} 
            sx={{ 
              mb: 1.5, 
              display: 'flex', 
              alignItems: 'center', 
              gap: 0.8,
              color: selectedVehicleObj ? '#2196f3' : 'text.disabled',
              fontSize: '0.9rem'
            }}
          >
            <PeopleIcon sx={{ fontSize: 18 }} />
            Passengers
          </Typography>
          <Box sx={{ 
            pointerEvents: selectedVehicleObj ? 'auto' : 'none'
          }}>
            <Passenger 
              adultsMax={adultsMax} 
              childrenMax={childrenMax} 
              seatingCapacity={seatingCapacity} 
              initialAdults={adults}
              initialChildren={children}
              onAdultChange={handleAdultChange}
              onChildChange={handleChildChange}
            />
          </Box>
     
      </Grid>

      {/* Price Mode Column */}
      <Grid item xs={12} md={3}>
     
          <Typography 
            variant="subtitle2" 
            fontWeight={600} 
            sx={{ 
              mb: 1.5, 
              display: 'flex', 
              alignItems: 'center', 
              gap: 0.8,
              color: data ? '#ff9800' : 'text.disabled',
              fontSize: '0.9rem'
            }}
          >
            <LocalOfferIcon sx={{ fontSize: 18 }} />
            Price Mode
          </Typography>
          <Box sx={{ 
            pointerEvents: data ? 'auto' : 'none'
          }}>
            {data && <Mode pricemode={pricemode} setpricemode={setpricemode} vehicles={data}/>}
            {!data && (
              <Paper 
                variant="outlined" 
                sx={{ 
                  p: 2, 
                  textAlign: 'center',
                  bgcolor: 'grey.50',
                  borderStyle: 'dashed',
                  borderColor: 'action.disabled'
                }}
              >
                <Typography variant="body2" color="text.disabled" sx={{ fontStyle: 'italic', fontSize: '0.85rem' }}>
                  Select vehicle first
                </Typography>
              </Paper>
            )}
          </Box>
       
      </Grid>

      {/* Hours Selection Column */}
      <Grid item xs={12} md={3}>
      
          <Typography 
            variant="subtitle2" 
            fontWeight={600} 
            sx={{ 
              mb: 1.5, 
              display: 'flex', 
              alignItems: 'center', 
              gap: 0.8,
              color: data ? '#9c27b0' : 'text.disabled',
              fontSize: '0.9rem'
            }}
          >
            <AccessTimeIcon sx={{ fontSize: 18 }} />
            Hours Package
          </Typography>
          <Box sx={{ 
            pointerEvents: data ? 'auto' : 'none'
          }}>
            {data && (
              <HourlyPackage 
                hour={selectedHours}
                sethours={setSelectedHours}
                onHourChange={(selectedHour) => {
                  setSelectedHours(selectedHour);
                  if (onHourChange) {
                    onHourChange(selectedHour);
                  }
                  
                  if (data && data.prices) {
                    const calculatedPrice = calculateHourlyPrice(selectedHour);
                    setTotalHourlyPrice(calculatedPrice);
                  }
                }}
                availableHours={[1, 2, 4, 6, 8, 12]}
              />
            )}
            {!data && (
              <Paper 
                variant="outlined" 
                sx={{ 
                  p: 2, 
                  textAlign: 'center',
                  bgcolor: 'grey.50',
                  borderStyle: 'dashed',
                  borderColor: 'action.disabled'
                }}
              >
                <Typography variant="body2" color="text.disabled" sx={{ fontStyle: 'italic', fontSize: '0.85rem' }}>
                  Select vehicle first
                </Typography>
              </Paper>
            )}
          </Box>
       
      </Grid>
    </Grid>
  );
};

export default VehicleListDropdown;




