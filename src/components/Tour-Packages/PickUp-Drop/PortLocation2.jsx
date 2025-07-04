import React, { useEffect, useRef, useState } from "react";
import { useSelector } from "react-redux";
import {
  TextField,
  Box,
  Paper,
  List,
  ListItem,
  ListItemText,
  Typography,
  Divider,
  InputAdornment,
  Fade,
} from "@mui/material";
import { LocationOn } from "@mui/icons-material";

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
  const autocompletePickUpRef = useRef(null);
  const autocompleteDropOffRef = useRef(null);
  const [isPickupValid, setIsPickupValid] = useState(true);
  const [isDropoffValid, setIsDropoffValid] = useState(true);
  const SelectedPort = useSelector((state) => state.pickupDrop.selectedPort);
  const shotels = useSelector((state) => state.pickupDrop.portCityData?.data);
  console.log("hotelsss", shotels);
  const zones = useSelector((state) => state.pickupDrop.zone?.data);
  console.log("zones", zones);

  // Add caching mechanism similar to PortLocation
  const [initialHotelsLoaded, setInitialHotelsLoaded] = useState(false);
  const [cachedHotels, setCachedHotels] = useState([]);
  console.log("portType", portType);
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

  const [showDropdown, setShowDropdown] = useState(false);
  const [searchText, setSearchText] = useState("");

  // Filter function to search across all options
  const filterItems = (items, term) => {
    if (!term) return items;
    if (!items) return [];
    return items.filter(
      (item) =>
        item &&
        item.name &&
        item.name.toLowerCase().includes((term || "").toLowerCase())
    );
  };

  const handleSelect = (item, type) => {
    if (disabled) return; // Don't process if disabled

    setexitPickUpLocation(item.name);
    setSearchText(item.name);
    setShowDropdown(false);
    setPickupFromAutocomplete(true);
    setType(type);
    setPickdropType(type);

    // Set ID based on the selected item type and port type
    let itemId;
    if (type === "hotel") {
      itemId = item.hotel_unique_id || item.id;
    } else if (type === "attraction") {
      itemId = item.attraction_id || item.id;
    } else if (type === "restaurant") {
      itemId = item.restaurant_id || item.id;
    }

    setId(itemId);

    if (portType === "Exit Port") {
      setpickId(itemId);
    } else {
      setdropId(itemId);
    }
  };

  // Reset initialHotelsLoaded when port type changes
  useEffect(() => {
    setInitialHotelsLoaded(false);
  }, [portType]);

  // Add useEffect to handle editing after selection
  useEffect(() => {
    // Only trigger when user is modifying a selection
    if (exitpickUpLocation && searchText !== exitpickUpLocation) {
      // Reset selection state when user starts editing
      setPickupFromAutocomplete(false);
      // Keep dropdown open to show options
      setShowDropdown(true);
    }
  }, [searchText, exitpickUpLocation]);

  // Add this function to close dropdown when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (
        event.target.id !== "pick-up-input" &&
        !event.target.closest(".location-dropdown")
      ) {
        setShowDropdown(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  return (
    <Box sx={{ position: 'relative', width: '100%' }}>
      <TextField
        id="pick-up-input"
        fullWidth
        variant="outlined"
        size="small"
        placeholder={
          portType === "Entry Port"
            ? "Where is your drop off?"
            : "Where is your pick up?"
        }
        value={searchText}
        disabled={
          disabled ||
          !portType ||
          (portType !== "Entry Port" && portType !== "Exit Port")
        }
        error={validationTriggered && !exitpickUpLocation && !disabled}
        helperText={
          validationTriggered && !exitpickUpLocation && !disabled
            ? "*Select location from dropdown"
            : ""
        }
        InputProps={{
          startAdornment: (
            <InputAdornment position="start">
              <LocationOn 
                sx={{ 
                  color: disabled ? 'action.disabled' : 'action.active',
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
        onChange={(e) => {
          if (disabled) return;

          const newValue = e.target.value;
          setSearchText(newValue);
          setShowDropdown(true);

          // If we had a selection but now we're editing it
          if (exitpickUpLocation && newValue !== exitpickUpLocation) {
            setPickupFromAutocomplete(false);
            // Don't clear the exitpickUpLocation yet - only when selection is confirmed
          }
        }}
        onFocus={() => {
          if (disabled) return;

          setShowDropdown(true);
          // If empty on focus, show all options
          if (!searchText && zonesData) {
            console.log("Showing all options on focus");
          }
        }}
      />

      {/* Material UI Dropdown */}
      <Fade in={showDropdown && !disabled}>
        <Paper
          elevation={8}
          sx={{
            position: 'absolute',
            top: '100%',
            left: 0,
            right: 0,
            maxHeight: '280px',
            overflowY: 'auto',
            zIndex: 15000, // Very high z-index
            mt: 0.5,
            borderRadius: 2,
            border: '1px solid',
            borderColor: 'divider',
            /* Custom scrollbar styles */
            '&::-webkit-scrollbar': {
              width: '8px',
            },
            '&::-webkit-scrollbar-track': {
              background: '#f1f1f1',
              borderRadius: '4px',
            },
            '&::-webkit-scrollbar-thumb': {
              background: '#bbb',
              borderRadius: '4px',
              transition: 'background 0.3s ease',
              '&:hover': {
                background: '#888',
              },
            },
            /* Firefox scrollbar */
            scrollbarWidth: 'thin',
            scrollbarColor: '#bbb #f1f1f1',
          }}
          className="location-dropdown"
        >
          {portType === "Exit Port" ? (
            // For Exit Port, show categorized dropdown just like Entry Port
            <>
              {zonesData &&
                zonesData.hotels &&
                zonesData.hotels.length > 0 && (
                  <Box>
                    <Typography
                      variant="subtitle2"
                      sx={{
                        px: 2,
                        py: 1,
                        backgroundColor: 'grey.50',
                        fontWeight: 600,
                        color: 'text.primary',
                        borderBottom: '1px solid',
                        borderColor: 'divider',
                      }}
                    >
                      Hotel
                    </Typography>
                    <List dense sx={{ py: 0 }}>
                      {filterItems(zonesData.hotels, searchText).map(
                        (hotel) => (
                          <ListItem
                            key={`hotel-${hotel.hotel_unique_id || hotel.id}`}
                            button
                            onClick={() => handleSelect(hotel, "hotel")}
                            sx={{
                              '&:hover': {
                                backgroundColor: 'primary.light',
                                color: 'primary.contrastText',
                              },
                              transition: 'all 0.2s ease',
                            }}
                          >
                            <ListItemText 
                              primary={hotel.name}
                              primaryTypographyProps={{
                                variant: 'body2',
                                fontWeight: 500,
                              }}
                            />
                          </ListItem>
                        )
                      )}
                      {filterItems(zonesData.hotels, searchText).length === 0 &&
                        searchText && (
                          <ListItem>
                            <ListItemText 
                              primary="No matching hotels"
                              primaryTypographyProps={{
                                variant: 'body2',
                                color: 'text.secondary',
                                fontStyle: 'italic',
                              }}
                            />
                          </ListItem>
                        )}
                    </List>
                  </Box>
                )}

              {zonesData &&
                zonesData.attractions &&
                zonesData.attractions.length > 0 && (
                  <Box>
                    <Divider />
                    <Typography
                      variant="subtitle2"
                      sx={{
                        px: 2,
                        py: 1,
                        backgroundColor: 'grey.50',
                        fontWeight: 600,
                        color: 'text.primary',
                        borderBottom: '1px solid',
                        borderColor: 'divider',
                      }}
                    >
                      Attraction
                    </Typography>
                    <List dense sx={{ py: 0 }}>
                      {filterItems(zonesData.attractions, searchText).map(
                        (attraction) => (
                          <ListItem
                            key={`attraction-${attraction.attraction_id || attraction.id}`}
                            button
                            onClick={() => handleSelect(attraction, "attraction")}
                            sx={{
                              '&:hover': {
                                backgroundColor: 'primary.light',
                                color: 'primary.contrastText',
                              },
                              transition: 'all 0.2s ease',
                            }}
                          >
                            <ListItemText 
                              primary={attraction.name}
                              primaryTypographyProps={{
                                variant: 'body2',
                                fontWeight: 500,
                              }}
                            />
                          </ListItem>
                        )
                      )}
                      {filterItems(zonesData.attractions, searchText).length === 0 &&
                        searchText && (
                          <ListItem>
                            <ListItemText 
                              primary="No matching attractions"
                              primaryTypographyProps={{
                                variant: 'body2',
                                color: 'text.secondary',
                                fontStyle: 'italic',
                              }}
                            />
                          </ListItem>
                        )}
                    </List>
                  </Box>
                )}

              {zonesData &&
                zonesData.restaurants &&
                zonesData.restaurants.length > 0 && (
                  <Box>
                    <Divider />
                    <Typography
                      variant="subtitle2"
                      sx={{
                        px: 2,
                        py: 1,
                        backgroundColor: 'grey.50',
                        fontWeight: 600,
                        color: 'text.primary',
                        borderBottom: '1px solid',
                        borderColor: 'divider',
                      }}
                    >
                      Restaurant
                    </Typography>
                    <List dense sx={{ py: 0 }}>
                      {filterItems(zonesData.restaurants, searchText).map(
                        (restaurant) => (
                          <ListItem
                            key={`restaurant-${restaurant.restaurant_id || restaurant.id}`}
                            button
                            onClick={() => handleSelect(restaurant, "restaurant")}
                            sx={{
                              '&:hover': {
                                backgroundColor: 'primary.light',
                                color: 'primary.contrastText',
                              },
                              transition: 'all 0.2s ease',
                            }}
                          >
                            <ListItemText 
                              primary={restaurant.name}
                              primaryTypographyProps={{
                                variant: 'body2',
                                fontWeight: 500,
                              }}
                            />
                          </ListItem>
                        )
                      )}
                      {filterItems(zonesData.restaurants, searchText).length === 0 &&
                        searchText && (
                          <ListItem>
                            <ListItemText 
                              primary="No matching restaurants"
                              primaryTypographyProps={{
                                variant: 'body2',
                                color: 'text.secondary',
                                fontStyle: 'italic',
                              }}
                            />
                          </ListItem>
                        )}
                    </List>
                  </Box>
                )}

              {(!zonesData ||
                ((!zonesData.hotels || zonesData.hotels.length === 0) &&
                  (!zonesData.attractions ||
                    zonesData.attractions.length === 0) &&
                  (!zonesData.restaurants ||
                    zonesData.restaurants.length === 0))) && (
                <ListItem>
                  <ListItemText 
                    primary={
                      initialHotelsLoaded && cachedHotels.length > 0
                        ? "Type to search..."
                        : "No options found"
                    }
                    primaryTypographyProps={{
                      variant: 'body2',
                      color: 'text.secondary',
                      fontStyle: 'italic',
                      textAlign: 'center',
                    }}
                  />
                </ListItem>
              )}
            </>
          ) : (
            // For Entry Port, keep the existing categorized dropdown
            <>
              {zonesData &&
                zonesData.hotels &&
                zonesData.hotels.length > 0 && (
                  <Box>
                    <Typography
                      variant="subtitle2"
                      sx={{
                        px: 2,
                        py: 1,
                        backgroundColor: 'grey.50',
                        fontWeight: 600,
                        color: 'text.primary',
                        borderBottom: '1px solid',
                        borderColor: 'divider',
                      }}
                    >
                      Hotel
                    </Typography>
                    <List dense sx={{ py: 0 }}>
                      {filterItems(zonesData.hotels, searchText).map(
                        (hotel) => (
                          <ListItem
                            key={`hotel-${hotel.hotel_unique_id || hotel.id}`}
                            button
                            onClick={() => handleSelect(hotel, "hotel")}
                            sx={{
                              '&:hover': {
                                backgroundColor: 'primary.light',
                                color: 'primary.contrastText',
                              },
                              transition: 'all 0.2s ease',
                            }}
                          >
                            <ListItemText 
                              primary={hotel.name}
                              primaryTypographyProps={{
                                variant: 'body2',
                                fontWeight: 500,
                              }}
                            />
                          </ListItem>
                        )
                      )}
                      {filterItems(zonesData.hotels, searchText).length === 0 &&
                        searchText && (
                          <ListItem>
                            <ListItemText 
                              primary="No matching hotels"
                              primaryTypographyProps={{
                                variant: 'body2',
                                color: 'text.secondary',
                                fontStyle: 'italic',
                              }}
                            />
                          </ListItem>
                        )}
                    </List>
                  </Box>
                )}

              {zonesData &&
                zonesData.attractions &&
                zonesData.attractions.length > 0 && (
                  <Box>
                    <Divider />
                    <Typography
                      variant="subtitle2"
                      sx={{
                        px: 2,
                        py: 1,
                        backgroundColor: 'grey.50',
                        fontWeight: 600,
                        color: 'text.primary',
                        borderBottom: '1px solid',
                        borderColor: 'divider',
                      }}
                    >
                      Attraction
                    </Typography>
                    <List dense sx={{ py: 0 }}>
                      {filterItems(zonesData.attractions, searchText).map(
                        (attraction) => (
                          <ListItem
                            key={`attraction-${attraction.attraction_id || attraction.id}`}
                            button
                            onClick={() => handleSelect(attraction, "attraction")}
                            sx={{
                              '&:hover': {
                                backgroundColor: 'primary.light',
                                color: 'primary.contrastText',
                              },
                              transition: 'all 0.2s ease',
                            }}
                          >
                            <ListItemText 
                              primary={attraction.name}
                              primaryTypographyProps={{
                                variant: 'body2',
                                fontWeight: 500,
                              }}
                            />
                          </ListItem>
                        )
                      )}
                      {filterItems(zonesData.attractions, searchText).length === 0 &&
                        searchText && (
                          <ListItem>
                            <ListItemText 
                              primary="No matching attractions"
                              primaryTypographyProps={{
                                variant: 'body2',
                                color: 'text.secondary',
                                fontStyle: 'italic',
                              }}
                            />
                          </ListItem>
                        )}
                    </List>
                  </Box>
                )}

              {zonesData &&
                zonesData.restaurants &&
                zonesData.restaurants.length > 0 && (
                  <Box>
                    <Divider />
                    <Typography
                      variant="subtitle2"
                      sx={{
                        px: 2,
                        py: 1,
                        backgroundColor: 'grey.50',
                        fontWeight: 600,
                        color: 'text.primary',
                        borderBottom: '1px solid',
                        borderColor: 'divider',
                      }}
                    >
                      Restaurant
                    </Typography>
                    <List dense sx={{ py: 0 }}>
                      {filterItems(zonesData.restaurants, searchText).map(
                        (restaurant) => (
                          <ListItem
                            key={`restaurant-${restaurant.restaurant_id || restaurant.id}`}
                            button
                            onClick={() => handleSelect(restaurant, "restaurant")}
                            sx={{
                              '&:hover': {
                                backgroundColor: 'primary.light',
                                color: 'primary.contrastText',
                              },
                              transition: 'all 0.2s ease',
                            }}
                          >
                            <ListItemText 
                              primary={restaurant.name}
                              primaryTypographyProps={{
                                variant: 'body2',
                                fontWeight: 500,
                              }}
                            />
                          </ListItem>
                        )
                      )}
                      {filterItems(zonesData.restaurants, searchText).length === 0 &&
                        searchText && (
                          <ListItem>
                            <ListItemText 
                              primary="No matching restaurants"
                              primaryTypographyProps={{
                                variant: 'body2',
                                color: 'text.secondary',
                                fontStyle: 'italic',
                              }}
                            />
                          </ListItem>
                        )}
                    </List>
                  </Box>
                )}

              {(!zonesData ||
                ((!zonesData.hotels || zonesData.hotels.length === 0) &&
                  (!zonesData.attractions ||
                    zonesData.attractions.length === 0) &&
                  (!zonesData.restaurants ||
                    zonesData.restaurants.length === 0))) && (
                <ListItem>
                  <ListItemText 
                    primary={
                      initialHotelsLoaded && cachedHotels.length > 0
                        ? "Type to search..."
                        : "No options found"
                    }
                    primaryTypographyProps={{
                      variant: 'body2',
                      color: 'text.secondary',
                      fontStyle: 'italic',
                      textAlign: 'center',
                    }}
                  />
                </ListItem>
              )}
            </>
          )}
        </Paper>
      </Fade>
    </Box>
  );
};

export default PortLocation2;
