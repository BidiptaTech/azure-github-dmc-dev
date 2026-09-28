import React from 'react';
import { Box } from '@mui/material';
import { useSelector, useDispatch } from 'react-redux';
import SearchLocation from './SearchLocation';
import VehicleListDropdown from './vehiclelistdropdown';
import VehicleListDropdown1 from './vehiclelistdropdown1';
import { setSelectedVehicle, setSelectedVehicle1 } from '../../../slice/port/pickupDropSlice';
import CombinedSearchLocationZone from './CombinedSearchLocationZone';

/**
 * Entry / Exit port orchestrator.
 * mode: "entry" | "exit" — one instance per day (day 0 = entry, last day = exit).
 * Flow: package ports → local bookings in vehicle dropdown → single AllServices write path.
 */
export default function PickupDropComponent({
  mode = 'entry',
  entryPorts,
  exitPorts,
  tourDates = [],
  date
}) {
  const dispatch = useDispatch();
  const Location = useSelector((state) => state.bookings?.searchLocation || {});
  const vehicles = useSelector((state) => state.pickupDrop.vehicles);
  const vehicles1 = useSelector((state) => state.pickupDrop.vehicles1);
  const selectedVehicleId = useSelector((state) => state.pickupDrop.selectedVehicle?.id);
  const selectedVehicleId1 = useSelector((state) => state.pickupDrop.selectedVehicle1?.id);
  const zone_on = useSelector((state) => state.auth.zone_on);

  const isEntry = mode === 'entry';
  const isExit = mode === 'exit';

  // Keep SearchLocation API compatible (it still keys off these string flags)
  const portType = isEntry ? 'Entry Port' : undefined;
  const portType1 = isExit ? 'Exit Port' : undefined;

  const hasVehicles = vehicles && vehicles.length > 0;
  const hasVehicles1 = vehicles1 && vehicles1.length > 0;
  const hasEntryPack = Array.isArray(entryPorts) && entryPorts.length > 0;
  const hasExitPack = Array.isArray(exitPorts) && exitPorts.length > 0;

  const handleVehicleChange = (vehicleId, modeValue, dmcId, city, country) => {
    dispatch(setSelectedVehicle({ id: vehicleId, mode: modeValue, dmcId, city, country }));
  };

  const handleVehicleChange1 = (vehicleId, modeValue, dmcId, city, country) => {
    dispatch(setSelectedVehicle1({ id: vehicleId, mode: modeValue, dmcId, city, country }));
  };

  return (
    <Box sx={{ position: 'relative', zIndex: 1 }}>
      {zone_on ? (
        <CombinedSearchLocationZone
          Location={Location}
          portType={portType}
          portType1={portType1}
          entryPorts={isEntry ? entryPorts : null}
          exitPorts={isExit ? exitPorts : null}
        />
      ) : (
        <SearchLocation
          Location={Location}
          portType={portType}
          portType1={portType1}
          entryPorts={isEntry ? entryPorts : null}
          exitPorts={isExit ? exitPorts : null}
        />
      )}

      {isEntry && (hasVehicles || hasEntryPack) ? (
        <Box sx={{ mt: 1 }}>
          <VehicleListDropdown
            selectedVehicle={selectedVehicleId}
            onVehicleChange={handleVehicleChange}
            entryPorts={entryPorts}
            tourDates={tourDates}
            date={date}
          />
        </Box>
      ) : null}

      {isExit && (hasVehicles1 || hasExitPack) ? (
        <Box sx={{ mt: 1 }}>
          <VehicleListDropdown1
            selectedVehicle={selectedVehicleId1}
            onVehicleChange={handleVehicleChange1}
            exitPorts={exitPorts}
            tourDates={tourDates}
            date={date}
          />
        </Box>
      ) : null}
    </Box>
  );
}
