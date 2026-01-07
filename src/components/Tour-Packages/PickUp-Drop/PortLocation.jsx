import React, { useState, useEffect, useCallback, useMemo } from "react";
import { useSelector } from "react-redux";
import {
  Autocomplete,
  TextField,
  Box,
  Typography,
  InputAdornment,
  alpha,
} from '@mui/material';
import { LocationOn } from '@mui/icons-material';

const PortLocation = ({
  pickUpLocation,
  setPickUpLocation,
  setType,
  setId,
  validationTriggered = false,
  setPickupFromAutocomplete,
  setpickId,
  setdropId,
  setDropoffFromAutocomplete,
  disabled = false,
  portType,
}) => {
  const SelectedPort = useSelector((state) => state.pickupDrop.selectedPort);

  const portCityData = useSelector((state) =>
    portType === "Entry Port"
      ? state.pickupDrop.portCityData
      : state.pickupDrop.zone.data?.ports
  );

  // Store the initial ports data in a ref to maintain it after selection
  const [initialPortsLoaded, setInitialPortsLoaded] = useState(false);
  const [cachedPorts, setCachedPorts] = useState([]);
  const [selectedItem, setSelectedItem] = useState(null);

  useEffect(() => {
    // If we have valid port data and haven't cached it yet
    if (
      portCityData &&
      Array.isArray(portCityData) &&
      portCityData.length > 0 &&
      !initialPortsLoaded
    ) {
      setCachedPorts(portCityData);
      setInitialPortsLoaded(true);
    } else if (
      portCityData?.data &&
      Array.isArray(portCityData.data) &&
      portCityData.data.length > 0 &&
      !initialPortsLoaded
    ) {
      setCachedPorts(portCityData.data);
      setInitialPortsLoaded(true);
    }
  }, [portCityData, initialPortsLoaded]);

  // Use cached ports if current ports array is empty
  const portsArray = Array.isArray(portCityData)
    ? portCityData.length > 0
      ? portCityData
      : cachedPorts
    : portCityData?.data && portCityData.data.length > 0
    ? portCityData.data
    : cachedPorts;

  // Create grouped options array with headers (following GuideListing pattern)
  const options = useMemo(() => {
    if (!portsArray || !Array.isArray(portsArray)) return [];
    
    const grouped = portsArray.reduce((acc, port) => {
      if (!port) return acc;
      const type = port.type || "Other";
      if (!acc[type]) acc[type] = [];
      acc[type].push({
        ...port,
        isHeader: false
      });
      return acc;
    }, {});

    const result = [];
    Object.entries(grouped).forEach(([type, ports]) => {
      // Add header
      result.push({ 
        id: `${type}-header`, 
        port_name: type, 
        type: 'header', 
        isHeader: true 
      });
      // Add ports
      result.push(...ports);
    });

    return result;
  }, [portsArray]);

  // Handle selection (following GuideListing pattern)
  const handleSelect = useCallback((event, newValue) => {
    if (!newValue || newValue.isHeader || disabled) {
      setSelectedItem(null);
      return;
    }

    setSelectedItem(newValue);
    setPickUpLocation(newValue.port_name);
    setType("port");
    setId(newValue.port_id);
    
    if (portType === "Entry Port") {
      setpickId(newValue.port_id);
    } else {
      setdropId(newValue.port_id);
    }
    
    setPickupFromAutocomplete(true);
  }, [disabled, portType, setPickUpLocation, setType, setId, setpickId, setdropId, setPickupFromAutocomplete]);

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
            bgcolor: alpha('#1976d2', 0.1),
            color: '#1976d2',
            fontWeight: 600,
            display: 'flex',
            alignItems: 'center',
            gap: 1,
            cursor: 'default !important',
            '&:hover': {
              bgcolor: alpha('#1976d2', 0.1) + ' !important',
            }
          }}
        >
          <Typography variant="subtitle2" sx={{ fontWeight: 600, color: '#1976d2' }}>
            {option.port_name}
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
        <Box sx={{ display: 'flex', alignItems: 'center', width: '100%', gap: 1, pl: 2 }}>
          <LocationOn sx={{ fontSize: '1rem', color: '#1976d2' }} />
          <Typography noWrap>{option.port_name}</Typography>
        </Box>
      </Box>
    );
  }, []);

  // Get option label (following GuideListing pattern)
  const getOptionLabel = useCallback((option) => {
    if (option?.isHeader) return '';
    return option?.port_name || '';
  }, []);

  // Check if option equals value (following GuideListing pattern)
  const isOptionEqualToValue = useCallback((option, value) => {
    if (!option || !value || option.isHeader || value.isHeader) return false;
    return option.port_id === value.port_id;
  }, []);

  // Get unique key for options (following GuideListing pattern)
  const getOptionKey = useCallback((option) => `${option.type}-${option.port_id || option.id}`, []);

  // Filter out headers from selectable options (following GuideListing pattern)
  const getOptionDisabled = useCallback((option) => option.isHeader, []);

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
        noOptionsText="No ports available"
        disabled={disabled || options.length === 0}
        renderOption={renderOption}
        sx={{
          '& .MuiInputBase-input': {
            fontSize: '0.75rem',
            height: '16px',
          },
        }}
        renderInput={(params) => (
          <TextField
            {...params}
            fullWidth
            placeholder={portType === "Entry Port" ? "Where is your pick up?" : "Where is your drop off"}
            disabled={disabled}
            error={validationTriggered && !pickUpLocation && !disabled}
            helperText={
              validationTriggered && !pickUpLocation && !disabled
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
                  '& .MuiInputBase-input': {
                    fontSize: '0.75rem',
                    height: '16px',
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

export default PortLocation;
