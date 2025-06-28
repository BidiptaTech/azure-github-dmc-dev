import React, { useState, useEffect, useRef } from "react";
import { useSelector } from "react-redux";
import { styled } from "@mui/material/styles";
import {
  Box,
  TextField,
  Typography,
  Paper,
  List,
  ListItem,
  Button,
  Chip,
  InputLabel,
  CircularProgress
} from "@mui/material";
import AddIcon from "@mui/icons-material/Add";

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

const DriverOption = styled(ListItem)(({ theme }) => ({
  padding: theme.spacing(1.5, 2),
  cursor: "pointer",
  transition: "background-color 0.2s",
  borderBottom: `1px solid ${theme.palette.divider}`,
  display: "flex",
  justifyContent: "space-between",
  alignItems: "center",
  "&:hover": {
    backgroundColor: theme.palette.action.hover,
  },
  "&:last-child": {
    borderBottom: "none",
  },
}));

const OthersOption = styled(ListItem)(({ theme }) => ({
  padding: theme.spacing(1.5, 2),
  cursor: "pointer",
  display: "flex",
  alignItems: "center",
  color: theme.palette.primary.main,
  fontWeight: 500,
  "&:hover": {
    backgroundColor: theme.palette.action.hover,
  },
}));

const OthersInputContainer = styled(Box)(({ theme }) => ({
  marginTop: theme.spacing(1),
  display: "flex",
  gap: theme.spacing(1),
}));

const PhoneChip = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.grey[200],
  fontSize: 12,
}));

const DriverSearch = ({ onSelect }) => {
  const [searchTerm, setSearchTerm] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedDriver, setSelectedDriver] = useState(null);
  const [showOthersInput, setShowOthersInput] = useState(false);
  const [otherDriverName, setOtherDriverName] = useState("");
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Get drivers from Redux store
  const { drivers = [], loading, error } = useSelector(state => state.enquiryList || { drivers: [], loading: false });

  // Filter drivers based on search term
  const filteredDrivers = drivers ? drivers.filter((driver) =>
    driver.name.toLowerCase().includes(searchTerm.toLowerCase())
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

  // Handle driver selection
  const handleDriverSelect = (driver) => {
    if (driver === "others") {
      setShowOthersInput(true);
      setSelectedDriver(null);
    } else {
      setSelectedDriver(driver);
      setShowOthersInput(false);
      setSearchTerm(driver.name);
      if (onSelect) onSelect(driver);
    }
    setIsDropdownOpen(false);
  };

  // Handle other driver name submission
  const handleOtherDriverSubmit = () => {
    if (otherDriverName.trim()) {
      const customDriver = { driver_id: "custom", name: otherDriverName.trim() };
      setSelectedDriver(customDriver);
      setSearchTerm(otherDriverName.trim());
      if (onSelect) onSelect(customDriver);
      setShowOthersInput(false);
    }
  };

  return (
    <SearchContainer>
      <Box sx={{ mb: 2 }}>
        <InputLabel sx={{ mb: 1, fontSize: 16, fontWeight: 500, color: 'text.primary' }}>
          Drivers
        </InputLabel>
        
        <TextField
          inputRef={inputRef}
          fullWidth
          variant="outlined"
          placeholder="Search for drivers..."
          value={searchTerm}
          onChange={(e) => {
            setSearchTerm(e.target.value);
            setIsDropdownOpen(true);
          }}
          onFocus={() => setIsDropdownOpen(true)}
        />

        {isDropdownOpen && (
          <DropdownContainer ref={dropdownRef}>
            <List disablePadding>
              {loading ? (
                <Box sx={{ p: 2, display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
                  <CircularProgress size={20} sx={{ mr: 1 }} />
                  <Typography color="text.secondary">Loading drivers...</Typography>
                </Box>
              ) : filteredDrivers.length > 0 ? (
                filteredDrivers.map((driver) => (
                  <DriverOption
                    key={driver.driver_id}
                    onClick={() => handleDriverSelect(driver)}
                  >
                    <Typography variant="body1">{driver.name}</Typography>
                    {driver.phone && (
                      <PhoneChip size="small" label={driver.phone} />
                    )}
                  </DriverOption>
                ))
              ) : (
                <Box sx={{ p: 2, color: 'text.secondary', textAlign: 'center', fontStyle: 'italic' }}>
                  <Typography>No drivers found</Typography>
                </Box>
              )}
              <OthersOption onClick={() => handleDriverSelect("others")}>
                <AddIcon fontSize="small" sx={{ mr: 1 }} />
                <Typography variant="body2">Add Other Driver</Typography>
              </OthersOption>
            </List>
          </DropdownContainer>
        )}

        {showOthersInput && (
          <OthersInputContainer>
            <TextField
              fullWidth
              size="small"
              placeholder="Enter driver name..."
              value={otherDriverName}
              onChange={(e) => setOtherDriverName(e.target.value)}
              autoFocus
            />
            <Button 
              variant="contained" 
              color="primary" 
              onClick={handleOtherDriverSubmit}
            >
              Add
            </Button>
          </OthersInputContainer>
        )}
      </Box>
    </SearchContainer>
  );
};

export default DriverSearch; 