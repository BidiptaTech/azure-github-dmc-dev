import React, { useState, useEffect, useRef } from "react";
import { useSelector, useDispatch } from "react-redux";
import { fetchEnquiryList } from "../../../slice/common/enquiryListSlice";
import { styled } from "@mui/material/styles";
import {
  Box,
  TextField,
  Typography,
  Paper,
  List,
  ListItem,
  Chip,
  InputLabel,
  CircularProgress
} from "@mui/material";
import DirectionsCarIcon from "@mui/icons-material/DirectionsCar";
import PersonIcon from "@mui/icons-material/Person";
import CloseIcon from "@mui/icons-material/Close";

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

const CarOption = styled(ListItem)(({ theme }) => ({
  padding: theme.spacing(1.5, 2),
  cursor: "pointer",
  transition: "background-color 0.2s",
  borderBottom: `1px solid ${theme.palette.divider}`,
  display: "flex",
  justifyContent: "space-between",
  alignItems: "flex-start",
  gap: theme.spacing(1),
  "&:hover": {
    backgroundColor: theme.palette.action.hover,
  },
  "&:last-child": {
    borderBottom: "none",
  },
}));

const CarInfo = styled(Box)({
  flex: 1,
});

const CarImage = styled(Box)(({ theme }) => ({
  width: 60,
  height: 45,
  flexShrink: 0,
  borderRadius: theme.shape.borderRadius,
  overflow: "hidden",
  "& img": {
    width: "100%",
    height: "100%",
    objectFit: "cover",
  },
}));

const SelectedCarsContainer = styled(Box)(({ theme }) => ({
  display: "flex",
  flexWrap: "wrap",
  gap: theme.spacing(1),
  marginTop: theme.spacing(1),
}));

const CarChip = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.grey[200],
  "& .MuiChip-deleteIcon:hover": {
    color: theme.palette.error.main,
  },
}));

const CountBadge = styled(Chip)(({ theme }) => ({
  backgroundColor: theme.palette.grey[200],
  marginLeft: theme.spacing(1),
  height: 20,
  fontSize: 12,
}));

const PreferredCarsSearch = ({ onSelect }) => {
  const dispatch = useDispatch();
  const [searchTerm, setSearchTerm] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [selectedCars, setSelectedCars] = useState([]);
  const [showOthersInput, setShowOthersInput] = useState(false);
  const [otherCarName, setOtherCarName] = useState("");
  const dropdownRef = useRef(null);
  const inputRef = useRef(null);

  // Get vehicles from Redux store
  const { vehicles = [], loading, error } = useSelector(state => state.enquiryList || { vehicles: [], loading: false });
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

  // Filter vehicles based on search term with safety checks
  const filteredCars = vehicles ? vehicles.filter((car) =>
    car && (car.vehicle_name || car.name) && 
    ((car.vehicle_name && car.vehicle_name.toLowerCase().includes(searchTerm.toLowerCase())) || 
     (car.name && car.name.toLowerCase().includes(searchTerm.toLowerCase())))
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

  // Handle car selection
  const handleCarSelect = (car) => {
    if (car === "others") {
      setShowOthersInput(true);
    } else {
      // Check if car is already selected
      const carId = car.driver_id || car.vehicle_id;
      if (!selectedCars.some(selectedCar => (selectedCar.driver_id === carId || selectedCar.vehicle_id === carId))) {
        const updatedCars = [...selectedCars, car];
        setSelectedCars(updatedCars);
        if (onSelect) onSelect(updatedCars);
      }
    }
    setSearchTerm("");
    setIsDropdownOpen(false);
  };

  // Handle other car name submission
  const handleOtherCarSubmit = () => {
    if (otherCarName.trim()) {
      const customCar = { 
        driver_id: `custom-${Date.now()}`, 
        vehicle_id: `custom-${Date.now()}`,
        vehicle_name: otherCarName.trim(),
        name: otherCarName.trim() 
      };
      const updatedCars = [...selectedCars, customCar];
      setSelectedCars(updatedCars);
      if (onSelect) onSelect(updatedCars);
      setOtherCarName("");
      setShowOthersInput(false);
    }
  };

  // Remove a car from selection
  const handleRemoveCar = (carId) => {
    const updatedCars = selectedCars.filter(car => 
      car.driver_id !== carId && car.vehicle_id !== carId
    );
    setSelectedCars(updatedCars);
    if (onSelect) onSelect(updatedCars);
  };

  return (
    <SearchContainer>
      <Box sx={{ mb: 2 }}>
        <InputLabel sx={{ mb: 1, fontSize: 16, fontWeight: 500, color: 'text.primary' }}>
          Preferred Cars {vehicles.length > 0 && (
            <CountBadge label={vehicles.length} size="small" variant="outlined" />
          )}
        </InputLabel>
        
        <TextField
          inputRef={inputRef}
          fullWidth
          variant="outlined"
          placeholder="Search for preferred cars..."
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
                <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', p: 2 }}>
                  <CircularProgress size={20} sx={{ mr: 1 }} />
                  <Typography color="text.secondary">Loading vehicles...</Typography>
                </Box>
              ) : error ? (
                <Box sx={{ p: 2, color: 'error.main', textAlign: 'center', borderLeft: 3, borderColor: 'error.main', bgcolor: 'error.light', opacity: 0.05 }}>
                  <Typography>Error: {error}</Typography>
                </Box>
              ) : filteredCars.length > 0 ? (
                filteredCars.map((car) => (
                  <CarOption
                    key={car.driver_id || car.vehicle_id || `car-${Math.random()}`}
                    onClick={() => handleCarSelect(car)}
                  >
                    <CarInfo>
                      <Typography variant="subtitle2" fontWeight={500}>{car.vehicle_name || car.name}</Typography>
                      {car.vehicle_type && (
                        <Box sx={{ display: 'flex', alignItems: 'center', fontSize: 12, color: 'text.secondary', mb: 0.5 }}>
                          <DirectionsCarIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                          {car.vehicle_type}
                        </Box>
                      )}
                      {car.seating_capacity && (
                        <Box sx={{ display: 'flex', alignItems: 'center', fontSize: 12, color: 'text.secondary' }}>
                          <PersonIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                          Capacity: {car.seating_capacity}
                        </Box>
                      )}
                    </CarInfo>
                    {car.image && (
                      <CarImage>
                        <img src={car.image} alt={car.vehicle_name || car.name} />
                      </CarImage>
                    )}
                  </CarOption>
                ))
              ) : (
                <Box sx={{ p: 2, color: 'text.secondary', textAlign: 'center', fontStyle: 'italic' }}>
                  <Typography>
                    {vehicles.length > 0 
                      ? "No vehicles match your search" 
                      : "No vehicles found for this location"}
                  </Typography>
                </Box>
              )}
            </List>
          </DropdownContainer>
        )}
        
        {selectedCars.length > 0 && (
          <SelectedCarsContainer>
            {selectedCars.map(car => (
              <CarChip
                key={car.driver_id || car.vehicle_id || `selected-${Math.random()}`}
                label={car.vehicle_name || car.name}
                onDelete={() => handleRemoveCar(car.driver_id || car.vehicle_id)}
                deleteIcon={<CloseIcon fontSize="small" />}
              />
            ))}
          </SelectedCarsContainer>
        )}
      </Box>
    </SearchContainer>
  );
};

export default PreferredCarsSearch;