import React, { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import { fetchEnquiryList } from "@/slice/common/enquiryListSlice";
import { styled } from "@mui/material/styles";
import {
  Box,
  TextField,
  Typography,
  Paper,
  List,
  ListItem,
  CircularProgress,
  Chip,
  InputLabel
} from "@mui/material";
import LocationOnIcon from "@mui/icons-material/LocationOn";
import MapIcon from "@mui/icons-material/Map";

// Styled components
const SearchContainer = styled(Box)(({ theme }) => ({
  position: "relative",
  marginBottom: theme.spacing(2),
}));

const DropdownContainer = styled(Paper)(({ theme }) => ({
  position: "absolute",
  width: "100%",
  maxHeight: 250,
  overflowY: "auto",
  zIndex: 20,
  marginTop: theme.spacing(0.5),
  boxShadow: theme.shadows[3],
}));

const PortOption = styled(ListItem)(({ theme }) => ({
  padding: theme.spacing(1.5, 2),
  cursor: "pointer",
  transition: "background-color 0.2s",
  borderBottom: `1px solid ${theme.palette.divider}`,
  display: "flex",
  alignItems: "flex-start",
  "&:hover": {
    backgroundColor: theme.palette.action.hover,
  },
  "&:last-child": {
    borderBottom: "none",
  },
}));

const PortInfo = styled(Box)({
  flex: 1,
});

const SelectedPort = styled(Box)(({ theme }) => ({
  marginTop: theme.spacing(1),
  padding: theme.spacing(1, 1.5),
  backgroundColor: theme.palette.grey[50],
  borderRadius: theme.shape.borderRadius,
  borderLeft: `3px solid ${theme.palette.primary.main}`,
  fontSize: 13,
}));

const CountBadge = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.grey[200],
  marginLeft: theme.spacing(1),
  height: 20,
  fontSize: 12,
}));

const PortAddressSearch = ({ onSelect }) => {
  const dispatch = useDispatch();
  const [searchTerm, setSearchTerm] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedPort, setSelectedPort] = useState(null);
  const [showOthersInput, setShowOthersInput] = useState(false);
  const [otherPortName, setOtherPortName] = useState("");
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Get ports from Redux store
  const { ports = [], loading, error } = useSelector(state => state.enquiryList || { ports: [], loading: false });
  const { searchLocation } = useSelector(state => state.enquiry || { searchLocation: {} });

  // Manual fetch button for testing
  const handleManualFetch = () => {
    if (searchLocation && searchLocation.country && searchLocation.city) {
      dispatch(fetchEnquiryList({
        country: searchLocation.country,
        city: searchLocation.city
      }));
    }
  };

  // Filter ports based on search term - with safety checks
  const filteredPorts = ports ? ports.filter((port) =>
    port && (port.port_name || port.name) && 
    (port.port_name?.toLowerCase().includes(searchTerm.toLowerCase()) || 
     port.name?.toLowerCase().includes(searchTerm.toLowerCase()))
  ) : [];

  // Handle clicking outside to close dropdown
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (
        dropdownRef.current &&
        !dropdownRef.current.contains(event.target) &&
        !inputRef.current.contains(event.target)
      ) {
        setIsDropdownOpen(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  // Handle port selection
  const handlePortSelect = (port) => {
    if (port === "others") {
      setShowOthersInput(true);
      setSelectedPort(null);
    } else {
      setSelectedPort(port);
      setShowOthersInput(false);
      // Use port_name or name, whichever is available
      setSearchTerm(port.port_name || port.name || "");
      if (onSelect) onSelect(port);
    }
    setIsDropdownOpen(false);
  };

  // Handle other port name submission
  const handleOtherPortSubmit = () => {
    if (otherPortName.trim()) {
      const customPort = { port_id: "custom", port_name: otherPortName.trim(), name: otherPortName.trim() };
      setSelectedPort(customPort);
      setSearchTerm(otherPortName.trim());
      if (onSelect) onSelect(customPort);
      setShowOthersInput(false);
    }
  };

  return (
    <SearchContainer>
      <Box sx={{ mb: 2 }}>
        <InputLabel sx={{ mb: 1, fontSize: 16, fontWeight: 500, color: 'text.primary' }}>
          Port Address (Pick Up Location) {ports.length > 0 && (
            <CountBadge label={ports.length} size="small" variant="outlined" />
          )}
        </InputLabel>

        <TextField
          inputRef={inputRef}
          fullWidth
          variant="outlined"
          placeholder="Search for port address..."
          value={searchTerm}
          onChange={(e) => {
            setSearchTerm(e.target.value);
            setIsDropdownOpen(true);
          }}
          onFocus={() => setIsDropdownOpen(true)}
        />
      </Box>

      {isDropdownOpen && (
        <DropdownContainer ref={dropdownRef}>
          <List disablePadding>
            {loading ? (
              <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', p: 2 }}>
                <CircularProgress size={20} sx={{ mr: 1 }} />
                <Typography color="text.secondary">Loading ports...</Typography>
              </Box>
            ) : error ? (
              <Box sx={{ p: 2, color: 'error.main', textAlign: 'center', borderLeft: 3, borderColor: 'error.main', bgcolor: 'error.light', opacity: 0.05 }}>
                <Typography>Error: {error}</Typography>
              </Box>
            ) : filteredPorts.length > 0 ? (
              filteredPorts.map((port) => (
                <PortOption
                  key={port.port_id || `port-${Math.random()}`}
                  onClick={() => handlePortSelect(port)}
                >
                  <PortInfo>
                    <Typography variant="subtitle2" fontWeight={500}>{port.port_name || port.name}</Typography>
                    {port.type && (
                      <Box sx={{ display: 'flex', alignItems: 'center', fontSize: 12, color: 'text.secondary', mb: 0.5 }}>
                        <LocationOnIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                        {port.type}
                      </Box>
                    )}
                    {port.distance && (
                      <Box sx={{ display: 'flex', alignItems: 'center', fontSize: 12, color: 'text.secondary' }}>
                        <MapIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                        {port.distance} km
                      </Box>
                    )}
                  </PortInfo>
                </PortOption>
              ))
            ) : (
              <Box sx={{ p: 2, color: 'text.secondary', textAlign: 'center', fontStyle: 'italic' }}>
                <Typography>
                  {ports.length > 0 
                    ? "No ports match your search" 
                    : "No ports found for this location"}
                </Typography>
              </Box>
            )}
          </List>
        </DropdownContainer>
      )}
      
      {selectedPort && (
        <SelectedPort>
          <Typography component="span" color="primary" fontWeight={500} sx={{ mr: 0.5 }}>Selected:</Typography>
          {selectedPort.port_name || selectedPort.name}
          {(selectedPort.address || selectedPort.type) && (
            <Chip
              icon={<LocationOnIcon fontSize="small" />}
              label={selectedPort.address || selectedPort.type}
              size="small"
              variant="outlined"
              sx={{ ml: 1, fontSize: 12, bgcolor: 'grey.100', height: 24 }}
            />
          )}
        </SelectedPort>
      )}
    </SearchContainer>
  );
};

export default PortAddressSearch;
