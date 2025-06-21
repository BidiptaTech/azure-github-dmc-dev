import React, { useEffect, useState } from 'react';
import { 
  Typography, 
  Container 
} from '@mui/material';
import { useSelector, useDispatch } from 'react-redux';
import SearchLocation from './SearchLocation';
import VehicleListDropdown from './vehiclelistdropdown';
import VehicleListDropdown1 from './vehiclelistdropdown1';
import { setSelectedVehicle, setSelectedVehicle1 } from '../../../slice/port/pickupDropSlice';
import CombinedSearchLocationZone from './CombinedSearchLocationZone';

export default function PickupDropComponent({portType, setPortType, portType1, setPortType1}) {
  // Add useSelector to access AllServices
  const allServices = useSelector((state) => state.tourPackages.AllServices);
  
  // Add a log to see when the component renders and what services exist
  useEffect(() => {
    console.log("%c PickupDropComponent Rendered", "background: #6a89cc; color: white; padding: 4px;");
    console.log("Current services in Redux:", allServices);
  }, [allServices]);
  
  // Make sure we're selecting from the correct slice in Redux store
  const Location = useSelector((state) => {
    console.log("Redux State:", state.bookings); // Log entire state to debug
    return state.bookings?.searchLocation || {};
  });
  
  // State to track multiple exit vehicles
  const [exitVehicles, setExitVehicles] = useState([]);
  // State to track multiple entry vehicles
  const [entryVehicles, setEntryVehicles] = useState([]);
  
  // Use useEffect to ensure console log runs after component mounts
  useEffect(() => {
    console.log("Locationstart in useEffect:", Location);
  }, [Location]);
  
  console.log("Locationstart outside useEffect:", Location);
  
  // Determine if we're showing entry port, exit port, or both
  const showEntryPort = portType === "Entry Port";
  const showExitPort = portType1 === "Exit Port";
  
  // Create the appropriate header based on which ports are being shown
  let headerText = "Pickup & Drop Services";
  
  if (showEntryPort && !showExitPort) {
    headerText = "Entry Port Services";
  } else if (portType === "Exit Port") {
    headerText = "Exit Port Services";
  }
  
  const dispatch = useDispatch();
  // Extract all selectors to the top level
  const vehicles = useSelector(state => state.pickupDrop.vehicles);
  const vehicles1 = useSelector(state => state.pickupDrop.vehicles1);
  const selectedVehicleId = useSelector(state => state.pickupDrop.selectedVehicle?.id);
  const selectedVehicleId1 = useSelector(state => state.pickupDrop.selectedVehicle1?.id);
  const hasVehicles = vehicles && vehicles.length > 0;
  const hasVehicles1 = vehicles1 && vehicles1.length > 0;
  const zone_on = useSelector((state) => state.auth.zone_on);
  
  // Updated handler for entry vehicle changes that tracks all selections
  const handleVehicleChange = (vehicleId, mode, dmcId, city, country, bookingIndex = 0) => {
    // Always update Redux with the latest selection for main booking
    dispatch(setSelectedVehicle({id: vehicleId, mode, dmcId, city, country}));
    
    // Also track in local state for supporting multiple bookings
    const vehicleInfo = {
      id: vehicleId,
      mode,
      dmcId,
      city,
      country,
      bookingIndex // Store which booking this belongs to
    };
    
    // Update local state of multiple bookings
    setEntryVehicles(prevVehicles => {
      // Try to find if we already have a booking at this index
      const existingIndex = prevVehicles.findIndex(v => v.bookingIndex === bookingIndex);
      
      if (existingIndex >= 0) {
        // Update existing vehicle at this booking index
        const updatedVehicles = [...prevVehicles];
        updatedVehicles[existingIndex] = vehicleInfo;
        return updatedVehicles;
      } else {
        // Add new vehicle for this booking index
        return [...prevVehicles, vehicleInfo];
      }
    });
    
    console.log(`Entry vehicle selected for booking #${bookingIndex}:`, {vehicleId, mode, dmcId, city, country});
  };
  
  // Updated handler for exit vehicle changes that tracks all selections
  const handleVehicleChange1 = (vehicleId, mode, dmcId, city, country, bookingIndex = 0) => {
    // Always update Redux with the latest selection for main booking
    dispatch(setSelectedVehicle1({id: vehicleId, mode, dmcId, city, country}));
    
    // Also track in local state for supporting multiple bookings
    const vehicleInfo = {
      id: vehicleId,
      mode,
      dmcId,
      city,
      country,
      bookingIndex // Store which booking this belongs to
    };
    
    // Update local state of multiple bookings
    setExitVehicles(prevVehicles => {
      // Try to find if we already have a booking at this index
      const existingIndex = prevVehicles.findIndex(v => v.bookingIndex === bookingIndex);
      
      if (existingIndex >= 0) {
        // Update existing vehicle at this booking index
        const updatedVehicles = [...prevVehicles];
        updatedVehicles[existingIndex] = vehicleInfo;
        return updatedVehicles;
      } else {
        // Add new vehicle for this booking index
        return [...prevVehicles, vehicleInfo];
      }
    });
    
    console.log(`Vehicle selected for booking #${bookingIndex}:`, {vehicleId, mode, dmcId, city, country});
  };
  
  return (
    <Container>
      <Typography variant="h5" gutterBottom>{headerText}</Typography>
      {zone_on ? (
        <CombinedSearchLocationZone 
          Location={Location}
          portType={portType}
          portType1={portType1}
        />
      ) : (
        <SearchLocation 
          Location={Location} 
          portType={portType} 
          portType1={portType1} 
        />
      )}
      
      {/* Show first vehicle dropdown for Entry Port */}
      {showEntryPort && hasVehicles && (
        <VehicleListDropdown 
          selectedVehicle={selectedVehicleId} 
          onVehicleChange={handleVehicleChange}
          entryVehicles={entryVehicles}
        />
      )}
      
      {/* Show second vehicle dropdown for Exit Port */}
      {showExitPort && hasVehicles1 && (
        <VehicleListDropdown1
          selectedVehicle={selectedVehicleId1}
          onVehicleChange={handleVehicleChange1}
          exitVehicles={exitVehicles}
        />
      )}
    </Container>
  );
} 