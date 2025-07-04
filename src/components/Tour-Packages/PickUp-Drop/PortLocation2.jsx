import React, { useEffect, useRef, useState, useCallback, useMemo } from "react";
import { useSelector } from "react-redux";
import {
  Autocomplete,
  TextField,
  Box,
  Typography,
  InputAdornment,
  alpha,
  Chip,
} from "@mui/material";
import { 
  LocationOn,
  Business,
  Attractions,
  Restaurant,
} from "@mui/icons-material";

const PortLocation2 = ({
  exitpickUpLocation,
  setexitPickUpLocation,
  setType,
  setPickdropType,
  setId,
  setpickId,
  setdropId,
  validationTriggered = false,
  setPickupFromAutocomplete,
  setDropoffFromAutocomplete,
  disabled = false,
  portType,
}) => {
  const [selectedItem, setSelectedItem] = useState(null);
  const SelectedPort = useSelector((state) => state.pickupDrop.selectedPort);
  const shotels = useSelector((state) => state.pickupDrop.portCityData?.data);
  const zones = useSelector((state) => state.pickupDrop.zone?.data);

  // Add caching mechanism similar to PortLocation
  const [initialHotelsLoaded, setInitialHotelsLoaded] = useState(false);
  const [cachedHotels, setCachedHotels] = useState([]);

  // Cache hotels when first loaded
  useEffect(() => {
    // For Exit Port
    if (
      portType === "Exit Port" &&
      shotels &&
      Array.isArray(shotels) &&
      shotels.length > 0 &&
      !initialHotelsLoaded
    ) {
      setCachedHotels(shotels);
      setInitialHotelsLoaded(true);
    }
    // For Entry Port
    else if (
      portType === "Entry Port" &&
      zones &&
      zones.hotels &&
      Array.isArray(zones.hotels) &&
      zones.hotels.length > 0 &&
      !initialHotelsLoaded
    ) {
      setCachedHotels(zones);
      setInitialHotelsLoaded(true);
    }
  }, [shotels, zones, SelectedPort, initialHotelsLoaded]);

  // Use the cached hotels if the current hotel array is empty
  const zonesData =
    portType === "Exit Port"
      ? shotels &&
        (shotels.hotels || shotels.attractions || shotels.restaurants)
        ? shotels
        : cachedHotels
      : zones && (zones.hotels || zones.attractions || zones.restaurants)
      ? zones
      : cachedHotels;

  // Create grouped options array with headers (following GuideListing pattern)
  const options = useMemo(() => {
    if (!zonesData) return [];
    
    const result = [];

    // Add Hotels section
    if (zonesData.hotels && Array.isArray(zonesData.hotels) && zonesData.hotels.length > 0) {
      result.push({ 
        id: `hotel-header`, 
        name: 'Hotels', 
        type: 'header', 
        isHeader: true 
      });
      zonesData.hotels.forEach(hotel => {
        result.push({
          ...hotel,
          type: 'hotel',
          isHeader: false,
          unique_id: hotel.hotel_unique_id || hotel.id
        });
      });
    }

    // Add Attractions section
    if (zonesData.attractions && Array.isArray(zonesData.attractions) && zonesData.attractions.length > 0) {
      result.push({ 
        id: `attraction-header`, 
        name: 'Attractions', 
        type: 'header', 
        isHeader: true 
      });
      zonesData.attractions.forEach(attraction => {
        result.push({
          ...attraction,
          type: 'attraction',
          isHeader: false,
          unique_id: attraction.attraction_id || attraction.id
        });
      });
    }

    // Add Restaurants section
    if (zonesData.restaurants && Array.isArray(zonesData.restaurants) && zonesData.restaurants.length > 0) {
      result.push({ 
        id: `restaurant-header`, 
        name: 'Restaurants', 
        type: 'header', 
        isHeader: true 
      });
      zonesData.restaurants.forEach(restaurant => {
        result.push({
          ...restaurant,
          type: 'restaurant',
          isHeader: false,
          unique_id: restaurant.restaurant_id || restaurant.id
        });
      });
    }

    return result;
  }, [zonesData]);

  // Handle selection (following GuideListing pattern)
  const handleSelect = useCallback((event, newValue) => {
    if (!newValue || newValue.isHeader || disabled) {
      setSelectedItem(null);
      return;
    }

    setSelectedItem(newValue);
    setexitPickUpLocation(newValue.name);
    setPickupFromAutocomplete(true);
    setType(newValue.type);
    setPickdropType(newValue.type);

    // Set ID based on the selected item type
    let itemId = newValue.unique_id;
    setId(itemId);

    if (portType === "Exit Port") {
      setpickId(itemId);
    } else {
      setdropId(itemId);
    }
  }, [disabled, portType, setexitPickUpLocation, setPickupFromAutocomplete, setType, setPickdropType, setId, setpickId, setdropId]);

  // Get icon for each type
  const getTypeIcon = (type) => {
    switch (type) {
      case 'hotel':
        return <Business sx={{ fontSize: '1rem', color: '#1976d2' }} />;
      case 'attraction':
        return <Attractions sx={{ fontSize: '1rem', color: '#ed6c02' }} />;
      case 'restaurant':
        return <Restaurant sx={{ fontSize: '1rem', color: '#2e7d32' }} />;
      default:
        return <LocationOn sx={{ fontSize: '1rem', color: '#1976d2' }} />;
    }
  };

  // Get color for each type
  const getTypeColor = (type) => {
    switch (type) {
      case 'hotel':
        return '#1976d2';
      case 'attraction':
        return '#ed6c02';
      case 'restaurant':
        return '#2e7d32';
      default:
        return '#1976d2';
    }
  };

  // Render option component (following GuideListing pattern)
  const renderOption = useCallback((props, option) => {
    // Render header
    if (option.isHeader) {
      return (
        <Box 
          component="li" 
          {...props}
          sx={{
            p: 2,
            bgcolor: alpha(getTypeColor(option.type === 'Hotels' ? 'hotel' : option.type === 'Attractions' ? 'attraction' : 'restaurant'), 0.1),
            color: getTypeColor(option.type === 'Hotels' ? 'hotel' : option.type === 'Attractions' ? 'attraction' : 'restaurant'),
            fontWeight: 600,
            display: 'flex',
            alignItems: 'center',
            gap: 1,
            cursor: 'default !important',
            '&:hover': {
              bgcolor: alpha(getTypeColor(option.type === 'Hotels' ? 'hotel' : option.type === 'Attractions' ? 'attraction' : 'restaurant'), 0.1) + ' !important',
            }
          }}
        >
          {getTypeIcon(option.type === 'Hotels' ? 'hotel' : option.type === 'Attractions' ? 'attraction' : 'restaurant')}
          <Typography variant="subtitle2" sx={{ fontWeight: 600, color: getTypeColor(option.type === 'Hotels' ? 'hotel' : option.type === 'Attractions' ? 'attraction' : 'restaurant') }}>
            {option.name}
          </Typography>
        </Box>
      );
    }

    // Render regular option
    return (
      <Box 
        component="li" 
        {...props}
        sx={{
          '&:hover': {
            bgcolor: alpha('#000', 0.05),
          },
          borderBottom: `1px solid ${alpha('#000', 0.05)}`,
        }}
      >
        <Box sx={{ display: 'flex', alignItems: 'center', width: '100%', gap: 1, pl: 2, justifyContent: 'space-between' }}>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, flex: 1 }}>
            {getTypeIcon(option.type)}
            <Typography noWrap sx={{ flex: 1 }}>{option.name}</Typography>
          </Box>
          <Chip 
            size="small" 
            label={option.type}
            sx={{ 
              height: 20,
              fontSize: '0.7rem',
              bgcolor: alpha(getTypeColor(option.type), 0.08),
              color: getTypeColor(option.type),
            }}
          />
        </Box>
      </Box>
    );
  }, []);

  // Get option label (following GuideListing pattern)
  const getOptionLabel = useCallback((option) => {
    if (option?.isHeader) return '';
    return option?.name || '';
  }, []);

  // Check if option equals value (following GuideListing pattern)
  const isOptionEqualToValue = useCallback((option, value) => {
    if (!option || !value || option.isHeader || value.isHeader) return false;
    return option.unique_id === value.unique_id;
  }, []);

  // Get unique key for options (following GuideListing pattern)
  const getOptionKey = useCallback((option) => `${option.type}-${option.unique_id || option.id}`, []);

  // Filter out headers from selectable options (following GuideListing pattern)
  const getOptionDisabled = useCallback((option) => option.isHeader, []);

  // Reset initialHotelsLoaded when port type changes
  useEffect(() => {
    setInitialHotelsLoaded(false);
  }, [portType]);

  return (
    <Box sx={{ width: '100%', position: 'relative' }}>
      <Autocomplete
        value={selectedItem}
        onChange={handleSelect}
        options={options}
        getOptionLabel={getOptionLabel}
        getOptionKey={getOptionKey}
        isOptionEqualToValue={isOptionEqualToValue}
        getOptionDisabled={getOptionDisabled}
        noOptionsText="No locations available"
        disabled={disabled || options.length === 0}
        renderOption={renderOption}
        renderInput={(params) => (
          <TextField
            {...params}
            fullWidth
            placeholder={
              portType === "Entry Port"
                ? "Where is your drop off?"
                : "Where is your pick up?"
            }
            disabled={disabled}
            error={validationTriggered && !exitpickUpLocation && !disabled}
            helperText={
              validationTriggered && !exitpickUpLocation && !disabled
                ? "*Select location from dropdown"
                : ""
            }
            InputProps={{
              ...params.InputProps,
              startAdornment: (
                <InputAdornment position="start">
                  <LocationOn 
                    sx={{ 
                      color: disabled ? 'action.disabled' : '#1976d2',
                      fontSize: 20 
                    }} 
                  />
                </InputAdornment>
              ),
              sx: {
                backgroundColor: disabled ? 'action.hover' : 'background.paper',
                '& .MuiOutlinedInput-root': {
                  '& fieldset': {
                    borderColor: disabled ? 'action.disabled' : 'divider',
                  },
                },
              }
            }}
          />
        )}
        ListboxProps={{
          style: {
            maxHeight: '300px'
          }
        }}
        slotProps={{
          popper: {
            sx: {
              zIndex: 999999
            }
          }
        }}
        forcePopupIcon={false}
      />
    </Box>
  );
};

export default PortLocation2;
