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
import { useDispatch } from 'react-redux';
import { useState } from 'react';
import { fetchVehicleDetails } from '../../../slice/localtour/Localslice';
import Passenger from './Passenger';
import HourlyPackage from './HourlyPackage';
import PeopleIcon from '@mui/icons-material/People';
import LocalOfferIcon from '@mui/icons-material/LocalOffer';

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
  const hasPrivatePrice = vehicles.prices.privatePrice > 0;
  const hasSharablePrice = vehicles.prices.sharablePrice > 0;
  
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
      {/* Mount the component directly without Box wrapping */}
      <div style={{ isolation: 'isolate'}}>
        <FormControl fullWidth>
          <InputLabel 
            id="price-mode-label"
            sx={{
              transform: 'translate(14px, 16px) scale(1)',
              '&.Mui-focused, &.MuiFormLabel-filled': {
                transform: 'translate(14px, -9px) scale(0.75)',
                color: '#ff9800',
                fontSize: '0.8rem',
                mt: 0.5
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
      </div>
    </Grid>
  ); 
};

const VehicleListDropdown = ({ 
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
  onAddMore = null,
  PointToPoint,
}) => {
  const vehicles = useSelector((state) => state.localtour.vehicles || []);
  const portZoneType = useSelector((state) => state.localtour.portZoneType);
  const dispatch = useDispatch();
  const tourDetails = useSelector((state) => state.hotels?.tourdetails);
  console.log("tourDetailsdd", tourDetails);
  const searchParams = useSelector((state) => state.tourPackages.searchCriteria);
  console.log("searchParamsvehi", searchParams);
  // Use cached vehicles if provided, otherwise use Redux vehicles
  const vehiclesToUse = cachedVehicles && cachedVehicles.length > 0 ? cachedVehicles : vehicles;
  
  // Find the currently selected vehicle from our list
  const selectedVehicleObj = vehiclesToUse.find(v => v.id === selectedVehicle) || null;

  // Use optional chaining for safe access to nested properties
  const adultsMax = searchParams?.guests?.adults  || 1;
  const childrenMax = searchParams?.guests?.children || 0;

  // Get initial passenger counts from preloaded booking or parent component
  const [adults, setAdults] = useState(preloadedBooking?.adults || adultsMax);
  const [children, setChildren] = useState(preloadedBooking?.children || childrenMax);
  const [seatingCapacity, setSeatingCapacity] = useState(0);
  const [data, setData] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);
  const tourStatus = useSelector((state) => state.tourPackages.tourStatus);
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
  
  // Add null check for data and data.prices
  const Price = data && data.prices 
    ? (pricemode === "Sharable"
      ? data.prices.sharablePrice * totalGuests
      : data.prices.privatePrice)
    : 0;
  
  // Pass price to parent component when it changes
  useEffect(() => {
    if (onPriceChange && Price > 0) {
      onPriceChange(Price);
    }
  }, [Price, pricemode, totalGuests]); // Removed onPriceChange to prevent loops
  
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
      console.log("Point To Point - Initializing with preloaded booking data:", preloadedBooking);
      
      // Set up mock data structure for preloaded booking
      const mockData = {
        prices: {
          privatePrice: preloadedBooking.priceMode === "Private" ? preloadedBooking.price : 0,
          sharablePrice: preloadedBooking.priceMode === "Sharable" ? preloadedBooking.price : 0
        }
      };
      
      setData(mockData);
      setSeatingCapacity(0); // Will be updated if needed
      
      // Trigger price change to parent
      if (onPriceChange) {
        onPriceChange(preloadedBooking.price);
      }
    }
  }, [preloadedBooking?.vehicleId, preloadedBooking?.price, preloadedBooking?.priceMode]); // More specific dependencies

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
          size="small"
          fullWidth
        />
      </div>
    );
  }

  return (
    <Grid container spacing={1} sx={{ mt: 0, p: 2 }}>
      {/* Vehicle Selection Column */}
      <Grid item xs={12} md={4} sx={{mt: 0}}>
     
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
              size="small"
            />
          </div>
        
      </Grid>

      {/* Passenger Selection Column */}
      <Grid item xs={12} md={4}>
     
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
      <Grid item xs={12} md={4}>
    
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
                  p: 0.5, 
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




