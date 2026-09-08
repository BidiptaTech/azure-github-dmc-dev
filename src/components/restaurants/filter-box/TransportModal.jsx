import React, { useState } from "react";
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  Typography,
  Box,
  Radio,
  RadioGroup,
  FormControlLabel,
  FormControl,
  Divider,
  Card,
  CardContent,
  Grid,
} from "@mui/material";
import { styled } from "@mui/material/styles";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import LocalTaxiIcon from "@mui/icons-material/LocalTaxi";
import AirportShuttleIcon from "@mui/icons-material/AirportShuttle";
import GroupIcon from "@mui/icons-material/Group";
import AttachMoneyIcon from "@mui/icons-material/AttachMoney";
import InfoIcon from "@mui/icons-material/Info";
import CloseIcon from "@mui/icons-material/Close";

const StyledDialog = styled(Dialog)(({ theme }) => ({
  "& .MuiDialog-paper": {
    borderRadius: "16px",
    boxShadow: "0 10px 40px rgba(0, 0, 0, 0.15)",
    background: "rgba(255, 255, 255, 0.95)",
  },
}));

const StyledCard = styled(Card)(({ selected }) => ({
  borderRadius: "12px",
  boxShadow: "0 4px 12px rgba(0, 0, 0, 0.05)",
  cursor: "pointer",
  transition: "all 0.3s ease",
  border: selected ? "2px solid #3554D1" : "1px solid rgba(230, 235, 245, 0.8)",
  transform: selected ? "scale(1.02)" : "scale(1)",
  backgroundColor: selected ? "rgba(53, 84, 209, 0.04)" : "white",
  position: "relative",
  "&:hover": {
    boxShadow: "0 8px 16px rgba(0, 0, 0, 0.1)",
    transform: "translateY(-4px)",
    borderColor: selected ? "#3554D1" : "rgba(53, 84, 209, 0.3)",
    "&:after": {
      opacity: 1
    }
  },
  "&:after": {
    content: selected ? '""' : '"Click to select"',
    position: "absolute",
    bottom: "8px",
    right: "8px",
    padding: "4px 8px",
    backgroundColor: "rgba(53, 84, 209, 0.7)",
    color: "white",
    borderRadius: "4px",
    fontSize: "11px",
    fontWeight: "bold",
    opacity: 0,
    transition: "opacity 0.2s ease",
    pointerEvents: "none"
  }
}));

const VehicleIcon = ({ type }) => {
  switch (type.toLowerCase()) {
    case "luxury":
      return <DirectionsCarIcon sx={{ fontSize: 28, color: "#3554D1" }} />;
    case "salon car":
      return <LocalTaxiIcon sx={{ fontSize: 28, color: "#3554D1" }} />;
    case "combi van":
      return <AirportShuttleIcon sx={{ fontSize: 28, color: "#3554D1" }} />;
    default:
      return <DirectionsCarIcon sx={{ fontSize: 28, color: "#3554D1" }} />;
  }
};

const TransportModal = ({
  open,
  onClose,
  vehicles,
  onTransportSelected,
  guestCount = 0,
  priceHide = "0"
}) => {
  const [needTransport, setNeedTransport] = useState(null);
  const [transportType, setTransportType] = useState(null);
  const [selectedVehicle, setSelectedVehicle] = useState(null);

  const handleNeedTransportChange = (event) => {
    setNeedTransport(event.target.value === "yes");
    if (event.target.value === "no") {
      // If no transport needed, pass null to parent
      onTransportSelected(null);
    }
  };

  const handleTransportTypeChange = (event) => {
    setTransportType(event.target.value);
  };

  const handleVehicleSelect = (vehicle) => {
    setSelectedVehicle(vehicle);
    
    const transportData = {
      vehicle_id: vehicle.vehicle_id,
      vehicle_name: vehicle.vehicle_name,
      vehicle_type: vehicle.vehicle_type,
      price: transportType === "shared" 
        ? parseFloat(vehicle.restaurant_shared_transport_price) 
        : parseFloat(vehicle.restaurant_private_transport_price),
      transport_type: transportType
    };
    
    onTransportSelected(transportData);
  };

  const handleClose = () => {
    // Reset state if dialog closed without completion
    if (selectedVehicle === null && needTransport === true) {
      setNeedTransport(null);
    }
    onClose();
  };

  const getCapacityColor = (capacity, guests) => {
    const ratio = guests / capacity;
    if (ratio >= 0.9) return '#e74c3c'; // Red - nearly full
    if (ratio >= 0.7) return '#f39c12'; // Orange - getting close
    return '#2ecc71'; // Green - plenty of space
  };

  const getCapacityText = (capacity, guests) => {
    const remaining = capacity - guests;
    if (remaining === 0) return 'Exact capacity';
    return `${remaining} extra seat${remaining !== 1 ? 's' : ''}`;
  };

  const filteredVehicles = vehicles?.filter(vehicle => {
    // For both transport types, first check if the vehicle has enough capacity
    if (vehicle.seating_capacity < guestCount) {
      return false; // Skip vehicles with insufficient capacity
    }
    
    // Then filter by sharable property
    if (transportType === "shared") {
      return vehicle.sharable === 1;
    } else if (transportType === "private") {
      return vehicle.sharable === 2;
    }
    
    return true;
  });

  return (
    <StyledDialog
      open={open}
      onClose={handleClose}
      maxWidth="md"
      fullWidth
      aria-labelledby="transport-dialog-title"
    >
      {needTransport === null ? (
        // Initial Dialog - Do you need transport?
        <>
          <DialogTitle id="transport-dialog-title" sx={{ padding: "24px 32px" }}>
            <Box display="flex" alignItems="center" justifyContent="space-between">
              <Box display="flex" alignItems="center" gap={2}>
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
                  }}
                >
                  <DirectionsCarIcon sx={{ color: '#ffffff', fontSize: 28 }} />
                </Box>
                <Typography variant="h5" component="h2" sx={{ fontWeight: 700 }}>
                  Do you need transportation?
                </Typography>
              </Box>
              <Button 
                onClick={handleClose}
                sx={{ 
                  minWidth: 'auto', 
                  p: 1,
                  color: '#64748B',
                  '&:hover': {
                    backgroundColor: 'rgba(100, 116, 139, 0.1)',
                  },
                }}
              >
                <CloseIcon />
              </Button>
            </Box>
          </DialogTitle>
          <DialogContent sx={{ padding: "24px 32px" }}>
            <Typography variant="body1" color="text.secondary" sx={{ mb: 3 }}>
              Would you like to add transportation to your restaurant booking?
            </Typography>
            <FormControl component="fieldset">
              <RadioGroup
                aria-label="need-transport"
                name="need-transport"
                value={needTransport === null ? "" : needTransport ? "yes" : "no"}
                onChange={handleNeedTransportChange}
              >
                <FormControlLabel 
                  value="yes" 
                  control={
                    <Radio sx={{
                      color: '#a3b1d1',
                      '&.Mui-checked': {
                        color: '#3554D1',
                      },
                    }} />
                  } 
                  label="Yes, I need transportation" 
                  sx={{ 
                    mb: 1,
                    p: 1,
                    borderRadius: '8px',
                    '&:hover': {
                      backgroundColor: 'rgba(53, 84, 209, 0.04)',
                    },
                  }}
                />
                <FormControlLabel 
                  value="no" 
                  control={
                    <Radio sx={{
                      color: '#a3b1d1',
                      '&.Mui-checked': {
                        color: '#3554D1',
                      },
                    }} />
                  } 
                  label="No, I don't need transportation" 
                  sx={{ 
                    p: 1,
                    borderRadius: '8px',
                    '&:hover': {
                      backgroundColor: 'rgba(53, 84, 209, 0.04)',
                    },
                  }}
                />
              </RadioGroup>
            </FormControl>
          </DialogContent>
          <DialogActions sx={{ padding: "16px 32px", justifyContent: "flex-end" }}>
            <Button
              onClick={handleClose}
              sx={{
                padding: '10px 24px',
                borderRadius: '10px',
                textTransform: 'none',
                backgroundColor: 'transparent',
                color: '#64748B',
                border: '1px solid #E2E8F0',
                fontWeight: 600,
                '&:hover': {
                  backgroundColor: '#F8FAFC',
                  borderColor: '#CBD5E1',
                },
              }}
            >
              Cancel
            </Button>
            <Button
              onClick={() => {
                if (needTransport === false) {
                  // If user doesn't need transport, close the dialog
                  handleClose();
                } else if (needTransport === true) {
                  // Move to transport type selection
                  setTransportType(null);
                }
              }}
              disabled={needTransport === null}
              sx={{
                padding: '10px 28px',
                borderRadius: '10px',
                textTransform: 'none',
                backgroundColor: '#3554D1',
                color: 'white',
                fontWeight: 600,
                boxShadow: '0 4px 12px rgba(53, 84, 209, 0.15)',
                '&:hover': {
                  backgroundColor: '#2A44B0',
                  boxShadow: '0 6px 16px rgba(53, 84, 209, 0.25)',
                },
                '&.Mui-disabled': {
                  backgroundColor: '#E2E8F0',
                  color: '#94A3B8',
                  boxShadow: 'none',
                },
              }}
            >
              Continue
            </Button>
          </DialogActions>
        </>
      ) : needTransport && transportType === null ? (
        // Transport Type Selection
        <>
          <DialogTitle id="transport-type-dialog-title" sx={{ padding: "24px 32px" }}>
            <Box display="flex" alignItems="center" justifyContent="space-between">
              <Box display="flex" alignItems="center" gap={2}>
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
                  }}
                >
                  <DirectionsCarIcon sx={{ color: '#ffffff', fontSize: 28 }} />
                </Box>
                <Typography variant="h5" component="h2" sx={{ fontWeight: 700 }}>
                  Select Transport Type
                </Typography>
              </Box>
              <Button 
                onClick={handleClose}
                sx={{ 
                  minWidth: 'auto', 
                  p: 1,
                  color: '#64748B',
                  '&:hover': {
                    backgroundColor: 'rgba(100, 116, 139, 0.1)',
                  },
                }}
              >
                <CloseIcon />
              </Button>
            </Box>
          </DialogTitle>
          <DialogContent sx={{ padding: "24px 32px" }}>
            <Typography variant="body1" color="text.secondary" sx={{ mb: 3 }}>
              Would you prefer shared or private transportation for your restaurant visit?
            </Typography>
            <FormControl component="fieldset">
              <RadioGroup
                aria-label="transport-type"
                name="transport-type"
                value={transportType || ""}
                onChange={handleTransportTypeChange}
              >
                <FormControlLabel 
                  value="shared" 
                  control={
                    <Radio sx={{
                      color: '#a3b1d1',
                      '&.Mui-checked': {
                        color: '#3554D1',
                      },
                    }} />
                  } 
                  label={
                    <Box>
                      <Typography variant="body1" sx={{ fontWeight: 600 }}>Shared Transport</Typography>
                      <Typography variant="body2" color="text.secondary">
                        Share your ride with other guests at a lower cost. Only vehicles designated for sharing will be available.
                      </Typography>
                    </Box>
                  }
                  sx={{ 
                    mb: 2,
                    p: 2,
                    borderRadius: '8px',
                    border: '1px solid rgba(230, 235, 245, 0.8)',
                    '&:hover': {
                      backgroundColor: 'rgba(53, 84, 209, 0.04)',
                      border: '1px solid rgba(53, 84, 209, 0.3)',
                    },
                  }}
                />
                <FormControlLabel 
                  value="private" 
                  control={
                    <Radio sx={{
                      color: '#a3b1d1',
                      '&.Mui-checked': {
                        color: '#3554D1',
                      },
                    }} />
                  } 
                  label={
                    <Box>
                      <Typography variant="body1" sx={{ fontWeight: 600 }}>Private Transport</Typography>
                      <Typography variant="body2" color="text.secondary">
                        Exclusive transport for your party only. Only vehicles designated for private hire with sufficient capacity will be shown.
                      </Typography>
                    </Box>
                  }
                  sx={{ 
                    p: 2,
                    borderRadius: '8px',
                    border: '1px solid rgba(230, 235, 245, 0.8)',
                    '&:hover': {
                      backgroundColor: 'rgba(53, 84, 209, 0.04)',
                      border: '1px solid rgba(53, 84, 209, 0.3)',
                    },
                  }}
                />
              </RadioGroup>
            </FormControl>
          </DialogContent>
          <DialogActions sx={{ padding: "16px 32px", justifyContent: "space-between" }}>
            <Button
              onClick={() => setNeedTransport(null)}
              sx={{
                padding: '10px 24px',
                borderRadius: '10px',
                textTransform: 'none',
                backgroundColor: 'transparent',
                color: '#64748B',
                border: '1px solid #E2E8F0',
                fontWeight: 600,
                '&:hover': {
                  backgroundColor: '#F8FAFC',
                  borderColor: '#CBD5E1',
                },
              }}
            >
              Back
            </Button>
            <Button
              onClick={() => {}}
              disabled={!transportType}
              sx={{
                padding: '10px 28px',
                borderRadius: '10px',
                textTransform: 'none',
                backgroundColor: '#3554D1',
                color: 'white',
                fontWeight: 600,
                boxShadow: '0 4px 12px rgba(53, 84, 209, 0.15)',
                '&:hover': {
                  backgroundColor: '#2A44B0',
                  boxShadow: '0 6px 16px rgba(53, 84, 209, 0.25)',
                },
                '&.Mui-disabled': {
                  backgroundColor: '#E2E8F0',
                  color: '#94A3B8',
                  boxShadow: 'none',
                },
              }}
            >
              Continue
            </Button>
          </DialogActions>
        </>
      ) : needTransport && transportType ? (
        // Vehicle Selection
        <>
          <DialogTitle id="vehicle-selection-dialog-title" sx={{ padding: "24px 32px" }}>
            <Box display="flex" alignItems="center" justifyContent="space-between">
              <Box display="flex" alignItems="center" gap={2}>
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
                  }}
                >
                  <VehicleIcon type={selectedVehicle?.vehicle_type || "Default"} />
                </Box>
                <Box>
                  <Typography variant="h5" component="h2" sx={{ fontWeight: 700 }}>
                    Select Vehicle
                  </Typography>
                  <Typography variant="body2" color="text.secondary">
                    For {guestCount} {guestCount === 1 ? 'passenger' : 'passengers'} • {transportType === "shared" ? "Shared" : "Private"} transport
                  </Typography>
                </Box>
              </Box>
              <Button 
                onClick={handleClose}
                sx={{ 
                  minWidth: 'auto', 
                  p: 1,
                  color: '#64748B',
                  '&:hover': {
                    backgroundColor: 'rgba(100, 116, 139, 0.1)',
                  },
                }}
              >
                <CloseIcon />
              </Button>
            </Box>
          </DialogTitle>
          <DialogContent sx={{ padding: "24px 32px" }}>
            <Typography variant="body1" color="text.secondary" sx={{ mb: 1 }}>
              Available vehicles for {transportType === "shared" ? "shared" : "private"} transport:
            </Typography>
            <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
              {transportType === "shared" 
                ? "Shared transportation allows you to share costs with other passengers. Select from available vehicles that support sharing." 
                : "Private transportation provides exclusive use of the vehicle for your party only. These vehicles are designated for private hire with enough seating capacity."}
            </Typography>
            <Box 
              sx={{ 
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
                backgroundColor: 'rgba(53, 84, 209, 0.08)',
                padding: '12px 16px',
                borderRadius: '8px',
                marginBottom: '20px'
              }}
            >
              <GroupIcon sx={{ fontSize: 20, color: '#3554D1' }} />
              <Typography variant="body2" sx={{ fontWeight: 500, color: '#3554D1' }}>
                Only showing vehicles with capacity for {guestCount} {guestCount === 1 ? 'passenger' : 'passengers'}
              </Typography>
            </Box>
            
            {filteredVehicles?.length > 0 ? (
              <Grid container spacing={2} sx={{ mt: 2 }}>
                {filteredVehicles.map((vehicle) => (
                  <Grid item xs={12} sm={6} key={vehicle.vehicle_id}>
                    <StyledCard 
                      selected={selectedVehicle?.vehicle_id === vehicle.vehicle_id}
                      onClick={() => handleVehicleSelect(vehicle)}
                    >
                      <CardContent sx={{ p: 3 }}>
                        <Box display="flex" justifyContent="space-between" alignItems="flex-start" mb={2}>
                          <Box display="flex" alignItems="center" gap={2}>
                            <Box 
                              sx={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                width: '42px',
                                height: '42px',
                                borderRadius: '10px',
                                backgroundColor: 'rgba(53, 84, 209, 0.1)',
                              }}
                            >
                              <VehicleIcon type={vehicle.vehicle_type} />
                            </Box>
                            <Box>
                              <Typography variant="h6" sx={{ fontWeight: 600 }}>
                                {vehicle.vehicle_name}
                              </Typography>
                              <Typography variant="body2" color="text.secondary">
                                {vehicle.vehicle_type}
                              </Typography>
                            </Box>
                          </Box>
                          <Typography 
                            variant="body1" 
                            sx={{ 
                              fontWeight: 700, 
                              color: '#3554D1',
                              backgroundColor: 'rgba(53, 84, 209, 0.08)',
                              padding: '6px 12px',
                              borderRadius: '8px',
                              display: 'flex',
                              alignItems: 'center',
                              gap: '4px'
                            }}
                          >
                            <AttachMoneyIcon sx={{ fontSize: 18 }} />
                            S$ {transportType === "shared" 
                              ? parseFloat(vehicle.restaurant_shared_transport_price).toFixed(2) 
                              : parseFloat(vehicle.restaurant_private_transport_price).toFixed(2)}
                          </Typography>
                        </Box>
                        
                        <Divider sx={{ mb: 2 }} />
                        
                        <Box display="flex" flexWrap="wrap" gap={2}>
                          <Box 
                            sx={{ 
                              display: 'flex', 
                              alignItems: 'center', 
                              gap: '6px',
                              backgroundColor: 'rgba(100, 116, 139, 0.08)',
                              padding: '4px 10px',
                              borderRadius: '6px'
                            }}
                          >
                            <GroupIcon sx={{ fontSize: 18, color: '#64748B' }} />
                            <Typography variant="body2" color="text.secondary">
                              Seats {vehicle.seating_capacity}
                            </Typography>
                          </Box>
                          <Box 
                            sx={{ 
                              display: 'flex', 
                              alignItems: 'center',
                              gap: '6px',
                              backgroundColor: vehicle.sharable === 1 
                                ? 'rgba(46, 204, 113, 0.1)' 
                                : 'rgba(231, 76, 60, 0.1)',
                              padding: '4px 10px',
                              borderRadius: '6px'
                            }}
                          >
                            <InfoIcon sx={{ 
                              fontSize: 18, 
                              color: vehicle.sharable === 1 ? '#2ecc71' : '#e74c3c' 
                            }} />
                            <Typography 
                              variant="body2" 
                              sx={{ 
                                color: vehicle.sharable === 1 ? '#2ecc71' : '#e74c3c',
                                fontWeight: 500
                              }}
                            >
                              {vehicle.sharable === 1 ? 'Shared Transport' : 'Private Transport'}
                            </Typography>
                          </Box>
                          <Box 
                            sx={{ 
                              display: 'flex', 
                              alignItems: 'center',
                              gap: '6px',
                              backgroundColor: `${getCapacityColor(vehicle.seating_capacity, guestCount)}20`,
                              padding: '4px 10px',
                              borderRadius: '6px'
                            }}
                          >
                            <InfoIcon sx={{ 
                              fontSize: 18, 
                              color: getCapacityColor(vehicle.seating_capacity, guestCount)
                            }} />
                            <Typography 
                              variant="body2" 
                              sx={{ 
                                color: getCapacityColor(vehicle.seating_capacity, guestCount),
                                fontWeight: 500
                              }}
                            >
                              {getCapacityText(vehicle.seating_capacity, guestCount)}
                            </Typography>
                          </Box>
                        </Box>
                      </CardContent>
                    </StyledCard>
                  </Grid>
                ))}
              </Grid>
            ) : (
              <Box 
                sx={{ 
                  textAlign: 'center', 
                  py: 4,
                  backgroundColor: 'rgba(241, 245, 249, 0.5)',
                  borderRadius: '10px',
                  border: '1px dashed #CBD5E1'
                }}
              >
                <Typography variant="body1" color="text.secondary" sx={{ mb: 2 }}>
                  No vehicles available for {transportType} transport with sufficient capacity for {guestCount} {guestCount === 1 ? 'passenger' : 'passengers'}.
                </Typography>
                <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                  Please try a different transport type or adjust your party size.
                </Typography>
                <Button
                  onClick={() => setTransportType(null)}
                  sx={{
                    mt: 2,
                    color: '#3554D1',
                    textTransform: 'none',
                    fontWeight: 600,
                  }}
                >
                  Try a different transport type
                </Button>
              </Box>
            )}
          </DialogContent>
          <DialogActions sx={{ padding: "16px 32px", justifyContent: "space-between" }}>
            <Button
              onClick={() => setTransportType(null)}
              sx={{
                padding: '10px 24px',
                borderRadius: '10px',
                textTransform: 'none',
                backgroundColor: 'transparent',
                color: '#64748B',
                border: '1px solid #E2E8F0',
                fontWeight: 600,
                '&:hover': {
                  backgroundColor: '#F8FAFC',
                  borderColor: '#CBD5E1',
                },
              }}
            >
              Back
            </Button>
            <Button
              onClick={handleClose}
              disabled={!selectedVehicle}
              sx={{
                padding: '10px 28px',
                borderRadius: '10px',
                textTransform: 'none',
                backgroundColor: '#3554D1',
                color: 'white',
                fontWeight: 600,
                boxShadow: '0 4px 12px rgba(53, 84, 209, 0.15)',
                '&:hover': {
                  backgroundColor: '#2A44B0',
                  boxShadow: '0 6px 16px rgba(53, 84, 209, 0.25)',
                },
                '&.Mui-disabled': {
                  backgroundColor: '#E2E8F0',
                  color: '#94A3B8',
                  boxShadow: 'none',
                },
              }}
            >
              Confirm Selection
            </Button>
          </DialogActions>
        </>
      ) : null}
    </StyledDialog>
  );
};

export default TransportModal; 