import React, { useEffect, useCallback, useRef } from 'react';
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
import { useDispatch } from 'react-redux';
import { useState } from 'react';
import { fetchVehicleDetails } from '../../../slice/localtour/Localslice';
import Passenger from './Passenger';
import HourlyPackage from './HourlyPackage';

// Custom styled tooltip
const CustomTooltip = styled(({ className, ...props }) => (
  <Tooltip {...props} classes={{ popper: className }} />
))(({ theme }) => ({
  '& .MuiTooltip-tooltip': {
    backgroundColor: 'white',
    color: 'rgba(0, 0, 0, 0.87)',
    maxWidth: 400,
    border: '1px solid #dadde9',
    borderRadius: '12px',
    padding: 0,
    boxShadow: theme.shadows[3]
  },
}));

// Tooltip content component
const TooltipContent = ({ vehicle }) => {
  return (
    <Box>
      {/* Header Image Section */}
      <Box sx={{ position: 'relative', width: '100%', height: 200 }}>
        <Box
          component="img"
          src={vehicle.image}
          alt={vehicle.vehicle_name}
          sx={{
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            borderTopLeftRadius: '12px',
            borderTopRightRadius: '12px',
          }}
        />
      </Box>

      {/* Content Section */}
      <Box sx={{ p: 2 }}>
        {/* Title and Location */}
        <Typography variant="h6" gutterBottom sx={{ fontWeight: 'bold', fontSize: '1.1rem' }}>
          {vehicle.vehicle_name}
        </Typography>
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
          <LocationOnIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 0.5 }} />
          <Typography variant="body2" color="text.secondary">
            {vehicle.city}, {vehicle.country}
          </Typography>
        </Box>

        {/* Vehicle Details */}
        <Box sx={{ mb: 2 }}>
          <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500 }}>
            Vehicle Details
          </Typography>
          <Stack spacing={1.5}>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <DirectionsCarIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 1 }} />
              <Typography variant="body2">
                <strong>Type:</strong> {vehicle.vehicle_type}
              </Typography>
            </Box>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <DirectionsCarIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 1 }} />
              <Typography variant="body2">
                <strong>Model:</strong> {vehicle.vehicle_model}
              </Typography>
            </Box>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <DirectionsCarIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 1 }} />
              <Typography variant="body2">
                <strong>Year:</strong> {vehicle.model_year}
              </Typography>
            </Box>
            <Box sx={{ display: 'flex', alignItems: 'center' }}>
              <EventSeatIcon sx={{ fontSize: 18, color: 'text.secondary', mr: 1 }} />
              <Typography variant="body2">
                <strong>Seating Capacity:</strong> {vehicle.seating_capacity}
              </Typography>
            </Box>
          </Stack>
        </Box>

                  {/* Pricing Section */}
          <Box sx={{ mt: 2 }}>
            <Typography variant="subtitle2" gutterBottom sx={{ fontWeight: 500 }}>
              Pricing Details
            </Typography>
            <Grid container spacing={2}>
              {/* Zone Prices */}
              <Grid item xs={12}>
                <Paper 
                  variant="outlined" 
                  sx={{ 
                    p: 1.5,
                    bgcolor: 'rgba(25, 118, 210, 0.02)',
                    borderColor: 'rgba(25, 118, 210, 0.1)'
                  }}
                >
                  <Typography variant="subtitle2" gutterBottom sx={{ color: 'primary.main', fontWeight: 500 }}>
                    Zone Transfer Prices
                  </Typography>
                  <Stack spacing={0.5}>
                    <Typography variant="body2">Private: ${vehicle.private_price || 'N/A'}</Typography>
                    <Typography variant="body2">Shared: ${vehicle.shared_price || 'N/A'}</Typography>
                  </Stack>
                </Paper>
              </Grid>
            </Grid>

          {/* Tax Information */}
          {vehicle.tax_percentage && (
            <Typography 
              variant="caption" 
              sx={{ 
                display: 'block',
                mt: 1,
                color: 'text.secondary',
                fontStyle: 'italic'
              }}
            >
              *Prices are subject to {vehicle.tax_percentage}% tax
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
  if (!vehicles) return null;
  
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
  
  // Set default mode on first render only
  useEffect(() => {
    if (!pricemode) {
      setpricemode("Private");
    }
  }, []); // Empty dependency array - only run once
  
  return (
    <Grid item xs={12} sm={6} md={12}>
      {/* Mount the component directly without Box wrapping */}
      <div style={{ isolation: 'isolate' }}>
        <FormControl fullWidth>
          <InputLabel id="price-mode-label">Price Mode</InputLabel>
          <Select
            labelId="price-mode-label"
            id="price-mode-select"
            value={pricemode || "Private"} // Provide a default value
            label="Price Mode"
            open={open}
            onOpen={handleOpen}
            onClose={handleClose}
            onChange={handleChange}
            onClick={(e) => e.stopPropagation()}
            onMouseDown={(e) => e.stopPropagation()}
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
            <MenuItem value="Private" onClick={(e) => e.stopPropagation()}>Private</MenuItem>
            <MenuItem value="Sharable" onClick={(e) => e.stopPropagation()}>Sharable</MenuItem>
          </Select>
        </FormControl>
      </div>
    </Grid>
  ); 
};



const VehicleListDropdownZone = ({ 
  selectedVehicle, 
  onVehicleChange, 
  onPaxChange, 
  onPriceModeChange, 
  onPriceChange,
  sectionIndex, 
  isNewBooking,
  cachedVehicles,
  cachedVehicleName,
  isGridLayout = false,
  preloadedBooking = null,
  onBookingComplete // Add this prop
}) => {
  const vehicles = useSelector((state) => state.localtour.vehicles || []);
  console.log("vehicles55", vehicles);
  const portZoneType = useSelector((state) => state.localtour.zonetype);
  const dispatch = useDispatch();
  const tourDetails = useSelector((state) => state.hotels?.tourdetails);
  
  // Use cached vehicles if provided, otherwise use Redux vehicles
  const vehiclesToUse = cachedVehicles && cachedVehicles.length > 0 ? cachedVehicles : vehicles;
  
  // Find the currently selected vehicle from our list
  const selectedVehicleObj = vehiclesToUse.find(v => v.id === selectedVehicle) || null;

  // Use optional chaining for safe access to nested properties
  const adultsMax = tourDetails?.data?.adult || tourDetails?.adult || 1;
  const childrenMax = tourDetails?.data?.child || tourDetails?.child || 0;

  const [adults, setAdults] = useState(preloadedBooking?.adults || adultsMax);
  const [children, setChildren] = useState(preloadedBooking?.children || childrenMax);
  const [seatingCapacity, setSeatingCapacity] = useState(0);
  const [data, setData] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const [vehicleZoneData, setVehicleZoneData] = useState({
    to_zone_id: '',
    from_zone_id: ''
  });
  
  // Define totalGuests for price calculation
  const totalGuests = adults + children;
  
  // Update parent component with passenger info when it changes
  useEffect(() => {
    if (onPaxChange) {
      onPaxChange(adults, children);
    }
  }, [adults, children]); // Removed onPaxChange to prevent loops

  // Remove the zone update effect that's causing the infinite loop
  
  // Filter vehicles that have at least one pricing mode
  const filteredVehicles = vehiclesToUse.length > 0 ? vehiclesToUse : [];
  
  const handleVehicleClick = (vehicle) => {
    if (!vehicle || !onVehicleChange) return;
    
    // For zone vehicles, use dmc_id directly from the vehicle
    const mode = "dmc";
    const dmcId = vehicle.dmc_id;
  
    // Get zone information from the vehicle or set default values
    // For Local Transfer, we need to ensure zone IDs are always set
    const zoneId = vehicle.to_zone_id || vehicle.zone_id || '1'; // Default to zone 1 if not set
    const fromZoneId = vehicle.from_zone_id || '1'; // Default to zone 1 if not set
    
    // Create a complete data structure with all required fields
    const vehicleData = {
      ...vehicle,
      private_price: parseFloat(vehicle.private_price || 0),
      shared_price: parseFloat(vehicle.shared_price || 0),
      // Add prices object for compatibility with other components
      prices: {
        privatePrice: parseFloat(vehicle.private_price || 0),
        sharablePrice: parseFloat(vehicle.shared_price || 0)
      },
      to_zone_id: zoneId,
      from_zone_id: fromZoneId,
      zone_id: zoneId
    };
    
    // Pass zone information to parent component directly
    onVehicleChange(vehicle.id, mode, dmcId, vehicle.city, vehicle.country, zoneId, fromZoneId);
    
    // Store zone information in local state
    setVehicleZoneData({
      to_zone_id: zoneId,
      from_zone_id: fromZoneId
    });

    // Reset states
    setError(null);
    setIsLoading(true);

    // Set the vehicle data directly since we already have all the information
    setSeatingCapacity(vehicle.seating_capacity || 0);
    setData(vehicleData);
    setIsLoading(false);
  };

  const [pricemode, setpricemode] = useState(preloadedBooking?.priceMode || ""); // Set from preloaded data or default
  
  // Notify parent when price mode changes
  useEffect(() => {
    if (onPriceModeChange && pricemode) {
      onPriceModeChange(pricemode);
    }
  }, [pricemode]); // Removed onPriceModeChange to prevent loops
  
  // Calculate price based on the data structure
  const Price = data 
    ? (pricemode === "Sharable"
      ? parseFloat(data.shared_price || 0) * totalGuests
      : parseFloat(data.private_price || 0))
    : 0;
    
  // Force a minimum price if we have vehicle data but price is 0
  const effectivePrice = (selectedVehicle && data && Price === 0) ? 
    (pricemode === "Sharable" ? 50 * totalGuests : 100) : Price;
    
  console.log("Local Transfer - Price calculation", { 
    pricemode, 
    rawPrice: Price,
    effectivePrice,
    data,
    sharedPrice: data?.shared_price,
    privatePrice: data?.private_price,
    totalGuests
  });
  
  // Create a ref to track previous price
  const prevPriceRef = useRef(0);
  
  // Pass price to parent component when it changes
  useEffect(() => {
    if (onPriceChange && selectedVehicle && data) {
      // Only update price if it has changed
      const newPrice = effectivePrice;
      
      if (prevPriceRef.current !== newPrice) {
        console.log("Local Transfer - Updating price to parent:", newPrice);
        onPriceChange(newPrice);
        prevPriceRef.current = newPrice;
        
        // Check if booking is complete and notify parent only when price changes
        const isComplete = selectedVehicle && 
                          data && 
                          pricemode && 
                          (adults + children > 0) && 
                          newPrice > 0;
                          
        if (isComplete && onBookingComplete) {
          // Only call onBookingComplete once when all conditions are met
          onBookingComplete(true);
        }
      }
    }
  }, [selectedVehicle, data, pricemode, adults, children, effectivePrice, onPriceChange, onBookingComplete]);
  
  // Handle adult count change for this specific booking
  const handleAdultChange = (value) => {
    setAdults(value);
  };

  // Handle child count change for this specific booking
  const handleChildChange = (value) => {
    setChildren(value);
  };

  // Initialize data when preloaded booking is available - use a ref to prevent re-initialization
  const hasInitialized = useRef(false);
  
  useEffect(() => {
    // Only initialize once
    if (hasInitialized.current) return;
    
    if (preloadedBooking && preloadedBooking.vehicleId && preloadedBooking.price > 0) {
      console.log("Local Transfer - Initializing with preloaded booking data");
      
      // Ensure zone IDs are set
      const zoneId = preloadedBooking.to_zone_id || preloadedBooking.zoneId || '1';
      const fromZoneId = preloadedBooking.from_zone_id || '1';
      
      // Set up mock data structure for preloaded booking - Zone has simple price structure
      const mockData = {
        id: preloadedBooking.vehicleId,
        vehicle_name: preloadedBooking.vehicleName || '',
        image: preloadedBooking.vehicleImage || '',
        city: preloadedBooking.city || '',
        country: preloadedBooking.country || '',
        vehicle_type: preloadedBooking.vehicleType || '',
        vehicle_model: preloadedBooking.vehicleModel || '',
        dmc_id: preloadedBooking.dmcId || '',
        private_price: preloadedBooking.priceMode === "Private" ? preloadedBooking.price : 0,
        shared_price: preloadedBooking.priceMode === "Sharable" ? preloadedBooking.price : 0,
        to_zone_id: zoneId,
        from_zone_id: fromZoneId,
        zone_id: zoneId,
        // Add prices object for compatibility with other components
        prices: {
          privatePrice: preloadedBooking.priceMode === "Private" ? preloadedBooking.price : 0,
          sharablePrice: preloadedBooking.priceMode === "Sharable" ? preloadedBooking.price : 0
        }
      };
      
      setData(mockData);
      setSeatingCapacity(preloadedBooking.seatingCapacity || 0);
      
      // Set zone information
      setVehicleZoneData({
        to_zone_id: zoneId,
        from_zone_id: fromZoneId
      });
      
      // Trigger price change to parent
      if (onPriceChange) {
        onPriceChange(preloadedBooking.price);
      }
      
      // Mark as complete if it was already complete
      if (preloadedBooking.isComplete && onBookingComplete) {
        onBookingComplete(true);
      }
      
      // Mark as initialized
      hasInitialized.current = true;
    }
  }, [preloadedBooking]);

  // If it's grid layout, return just the autocomplete for the vehicle selection column
  if (isGridLayout) {
    return (
      <div style={{ isolation: 'isolate', width: '100%' }}>
        <Autocomplete
          key={`vehicle-autocomplete-${sectionIndex}`}
          value={selectedVehicleObj}
          onChange={(event, newValue) => {
            event.stopPropagation();
            handleVehicleClick(newValue);
          }}
          options={filteredVehicles}
          getOptionLabel={(option) => option.vehicle_name || cachedVehicleName || ''}
          renderOption={(props, option) => (
            <CustomTooltip
              key={`tooltip-${option.id}`}
              title={<TooltipContent vehicle={option} />}
              placement="right"
              arrow
            >
              <Box 
                key={`option-${option.id}`}
                component="li" 
                {...props} 
                onClick={(e) => {
                  e.stopPropagation();
                  props.onClick(e);
                }}
              >
                {option.vehicle_name}
                {/* Add a small indicator for available pricing modes */}
                <Box sx={{ ml: 'auto', display: 'flex', gap: 0.5 }}>
                  <Chip 
                    key={`vehicle-${option.id}`}
                    size="small" 
                    label={option.vehicle_type || "Vehicle"}
                    sx={{ 
                      height: 20,
                      fontSize: '0.7rem',
                      bgcolor: 'rgba(25, 118, 210, 0.08)',
                      color: 'primary.main'
                    }}
                  />
                </Box>
              </Box>
            </CustomTooltip>
          )}
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
      </div>
    );
  }

  return (
    <Grid container spacing={3} sx={{ mt: 2 }}>
      {/* Vehicle Selection Column */}
      <Grid item xs={12} md={4}>
     
          <Typography 
            variant="subtitle2" 
            fontWeight={600} 
            sx={{ 
              mb: 2, 
              display: 'flex', 
              alignItems: 'center', 
              gap: 1,
              color: '#ff6b6b'
            }}
          >
            <DirectionsCarIcon sx={{ fontSize: 20, color: '#ff6b6b' }} />
            Vehicle Selection
          </Typography>
          <div style={{ isolation: 'isolate' }}>
            <Autocomplete
              key={`vehicle-autocomplete-${sectionIndex}`}
              value={selectedVehicleObj}
              onChange={(event, newValue) => {
                event.stopPropagation();
                handleVehicleClick(newValue);
              }}
              options={filteredVehicles}
              getOptionLabel={(option) => option.vehicle_name || cachedVehicleName || ''}
              renderOption={(props, option) => (
                <CustomTooltip
                  key={`tooltip-${option.id}`}
                  title={<TooltipContent vehicle={option} />}
                  placement="right"
                  arrow
                >
                  <Box 
                    key={`option-${option.id}`}
                    component="li" 
                    {...props} 
                    onClick={(e) => {
                      e.stopPropagation();
                      props.onClick(e);
                    }}
                  >
                    {option.vehicle_name}
                    <Box sx={{ ml: 'auto', display: 'flex', gap: 0.5 }}>
                      <Chip 
                        key={`vehicle-${option.id}`}
                        size="small" 
                        label={option.vehicle_type || "Vehicle"}
                        sx={{ 
                          height: 20,
                          fontSize: '0.7rem',
                          bgcolor: 'rgba(25, 118, 210, 0.08)',
                          color: 'primary.main'
                        }}
                      />
                    </Box>
                  </Box>
                </CustomTooltip>
              )}
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
          </div>
       
      </Grid>

      {/* Passenger Selection Column */}
      <Grid item xs={12} md={4}>
      
          <Typography 
            variant="subtitle2" 
            fontWeight={600} 
            sx={{ 
              mb: 2, 
              display: 'flex', 
              alignItems: 'center', 
              gap: 1,
              color: selectedVehicleObj ? '#2196f3' : 'text.disabled'
            }}
          >
            <PeopleIcon sx={{ fontSize: 20 }} />
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
      <Grid item xs={12} md={4}>
   
          <Typography 
            variant="subtitle2" 
            fontWeight={600} 
            sx={{ 
              mb: 2, 
              display: 'flex', 
              alignItems: 'center', 
              gap: 1,
              color: data ? '#ff9800' : 'text.disabled'
            }}
          >
            <LocalOfferIcon sx={{ fontSize: 20 }} />
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
                  p: 3, 
                  textAlign: 'center',
                  bgcolor: 'grey.50',
                  borderStyle: 'dashed',
                  borderColor: 'action.disabled'
                }}
              >
                <Typography variant="body2" color="text.disabled" sx={{ fontStyle: 'italic' }}>
                  Select vehicle first
                </Typography>
              </Paper>
            )}
          </Box>
     
      </Grid>
    </Grid>
  );
};

export default VehicleListDropdownZone;




