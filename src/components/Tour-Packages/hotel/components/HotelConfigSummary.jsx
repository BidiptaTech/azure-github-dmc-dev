import React, { useState, useEffect } from 'react';
import { 
  Box, Typography, Grid, Card, CardContent, Paper, Chip, Avatar,
  Accordion, AccordionSummary, AccordionDetails, IconButton, Alert, Button, Divider
} from '@mui/material';
import HotelIcon from '@mui/icons-material/Hotel';
import RestaurantMenuIcon from '@mui/icons-material/RestaurantMenu';
import PersonIcon from '@mui/icons-material/Person';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import DeleteIcon from '@mui/icons-material/Delete';
import WarningIcon from '@mui/icons-material/Warning';
import UnfoldLessIcon from '@mui/icons-material/UnfoldLess';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
import RoomIcon from '@mui/icons-material/Room';

/**
 * Hotel Configuration Summary component
 * @param {Object} props Component props
 * @param {Array} props.hotelConfigurations - Array of hotel configurations
 * @param {number} props.activeHotelIndex - Index of currently active hotel
 * @param {Function} props.selectHotelConfiguration - Function to select hotel configuration
 * @param {Function} props.removeHotelConfiguration - Function to remove hotel configuration
 * @param {Object} props.tourDateRange - Tour date range {startDate, endDate}
 * @returns {JSX.Element} Hotel configuration summary component
 */
const HotelConfigSummary = ({ 
  hotelConfigurations, 
  activeHotelIndex, 
  selectHotelConfiguration, 
  removeHotelConfiguration,
  tourDateRange 
}) => {
  const [expandedAccordions, setExpandedAccordions] = useState(new Set());

  // Group configurations by hotel ID
  const groupedConfigurations = React.useMemo(() => {
    const grouped = {};
    
    hotelConfigurations.forEach((config, index) => {
      if (config.hotelId) {
        if (!grouped[config.hotelId]) {
          grouped[config.hotelId] = {
            hotelDetails: config.hotelDetails || {},
            configurations: []
          };
        }
        grouped[config.hotelId].configurations.push({
          ...config,
          originalIndex: index
        });
      }
    });
    
    return grouped;
  }, [hotelConfigurations]);

  // Update expanded accordions when activeHotelIndex changes
  useEffect(() => {
    if (activeHotelIndex !== null && activeHotelIndex !== undefined) {
      const activeConfig = hotelConfigurations[activeHotelIndex];
      if (activeConfig && activeConfig.hotelId) {
        setExpandedAccordions(prev => new Set([...prev, activeConfig.hotelId]));
      }
    }
  }, [activeHotelIndex, hotelConfigurations]);

  if (Object.keys(groupedConfigurations).length === 0) {
    return null;
  }

  // Function to check if hotel overall dates are within tour date range
  const isHotelDateInRange = (overallCheckIn, overallCheckOut) => {
    console.log('Hotel-level date validation:', {
      overallCheckIn,
      overallCheckOut,
      tourDateRange
    });

    if (!tourDateRange || !tourDateRange.startDate || !tourDateRange.endDate) {
      console.log('No tour date range provided, returning valid');
      return { valid: true, message: '' };
    }

    if (!overallCheckIn || !overallCheckOut) {
      console.log('No hotel dates provided, returning valid');
      return { valid: true, message: '' };
    }

    // Parse dates from the formatted strings
    const hotelCheckIn = new Date(overallCheckIn);
    const hotelCheckOut = new Date(overallCheckOut);
    const tourStart = new Date(tourDateRange.startDate);
    const tourEnd = new Date(tourDateRange.endDate);

    console.log('Parsed hotel-level dates:', {
      hotelCheckIn,
      hotelCheckOut,
      tourStart,
      tourEnd
    });

    // Reset time to compare only dates
    hotelCheckIn.setHours(0, 0, 0, 0);
    hotelCheckOut.setHours(0, 0, 0, 0);
    tourStart.setHours(0, 0, 0, 0);
    tourEnd.setHours(0, 0, 0, 0);

    console.log('Normalized hotel-level dates:', {
      hotelCheckIn,
      hotelCheckOut,
      tourStart,
      tourEnd
    });

    if (hotelCheckIn < tourStart) {
      const message = `Hotel check-in date (${hotelCheckIn.toLocaleDateString()}) is before tour start date (${tourStart.toLocaleDateString()}).`;
      console.log('Hotel date validation failed:', message);
      return { 
        valid: false, 
        message 
      };
    }

    if (hotelCheckOut > tourEnd) {
      const message = `Hotel check-out date (${hotelCheckOut.toLocaleDateString()}) is after tour end date (${tourEnd.toLocaleDateString()}).`;
      console.log('Hotel date validation failed:', message);
      return { 
        valid: false, 
        message 
      };
    }

    if (hotelCheckIn > tourEnd || hotelCheckOut < tourStart) {
      const message = `Hotel dates (${hotelCheckIn.toLocaleDateString()} - ${hotelCheckOut.toLocaleDateString()}) are completely outside the tour date range.`;
      console.log('Hotel date validation failed:', message);
      return { 
        valid: false, 
        message 
      };
    }

    console.log('Hotel date validation passed');
    return { valid: true, message: '' };
  };

  // Handle accordion toggle
  const handleAccordionChange = (hotelId) => {
    const newExpanded = new Set(expandedAccordions);
    if (newExpanded.has(hotelId)) {
      newExpanded.delete(hotelId);
    } else {
      newExpanded.add(hotelId);
    }
    setExpandedAccordions(newExpanded);
  };

  // Handle collapse all accordions
  const handleCollapseAll = () => {
    setExpandedAccordions(new Set());
  };

  // Format date for display
  const formatDate = (dateString) => {
    if (!dateString) return 'Not set';
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  // Get hotel summary statistics
  const getHotelSummary = (configurations) => {
    const totalNights = configurations.reduce((sum, config) => sum + (config.nights || 0), 0);
    const totalGuests = configurations.reduce((sum, config) => sum + (config.selectedGuests || 0), 0);
    const totalMealPlanCost = configurations.reduce((sum, config) => {
      const mealPlanCost = (config.guestMealPlans || []).reduce((mealSum, mealPlanData) => {
        const price = mealPlanData?.price || 0;
        return mealSum + price;
      }, 0);
      return sum + mealPlanCost;
    }, 0);
    
    const dateRanges = configurations
      .filter(config => config.checkInDate && config.checkOutDate)
      .map(config => ({
        checkIn: formatDate(config.checkInDate),
        checkOut: formatDate(config.checkOutDate),
        checkInDate: new Date(config.checkInDate),
        checkOutDate: new Date(config.checkOutDate)
      }));
    
    // Calculate overall hotel date range
    let overallCheckIn = null;
    let overallCheckOut = null;
    
    if (dateRanges.length > 0) {
      // Find earliest check-in and latest check-out
      overallCheckIn = new Date(Math.min(...dateRanges.map(range => range.checkInDate.getTime())));
      overallCheckOut = new Date(Math.max(...dateRanges.map(range => range.checkOutDate.getTime())));
    }
    
    return { 
      totalNights, 
      totalGuests, 
      totalMealPlanCost, 
      dateRanges,
      overallCheckIn: overallCheckIn ? formatDate(overallCheckIn.toISOString().split('T')[0]) : null,
      overallCheckOut: overallCheckOut ? formatDate(overallCheckOut.toISOString().split('T')[0]) : null
    };
  };
  
  return (
    <Box sx={{ mb: 3 }}>
      {/* Header with title and controls */}
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
        <Typography variant="h6">
          {Object.keys(groupedConfigurations).length === 1 ? 'Hotel Selection Summary' : 'Your Hotel Selections'}
        </Typography>
        
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
          {/* Tour Date Range Display */}
          {tourDateRange && tourDateRange.startDate && tourDateRange.endDate && (
            <Box sx={{ display: 'flex', alignItems: 'center', bgcolor: 'primary.light', px: 2, py: 1, borderRadius: 1 }}>
              <CalendarTodayIcon sx={{ color: 'primary.main', mr: 1, fontSize: 18 }} />
              <Typography variant="body2" color="primary.main" fontWeight={600}>
                Tour: {formatDate(tourDateRange.startDate)} - {formatDate(tourDateRange.endDate)}
              </Typography>
            </Box>
          )}
          
          {/* Collapse All Button */}
          {expandedAccordions.size > 0 && (
            <Button
              size="small"
              variant="outlined"
              startIcon={<UnfoldLessIcon />}
              onClick={handleCollapseAll}
              sx={{ minWidth: 'auto' }}
            >
              Collapse All
            </Button>
          )}
        </Box>
      </Box>
      
      {Object.entries(groupedConfigurations).map(([hotelId, hotelGroup]) => {
        const hotel = hotelGroup.hotelDetails;
        const configurations = hotelGroup.configurations;
        const hotelSummary = getHotelSummary(configurations);
        
        // Hotel-level date validation using overall check-in/check-out dates
        const hotelDateValidation = isHotelDateInRange(
          hotelSummary.overallCheckIn, 
          hotelSummary.overallCheckOut
        );
        
        console.log(`Hotel ${hotelId} date validation result:`, hotelDateValidation);
        
        const hasDateIssues = !hotelDateValidation.valid;
        const isActiveHotel = configurations.some(config => config.originalIndex === activeHotelIndex);
        
        return (
          <Box key={hotelId} sx={{ mb: 2 }}>
            {/* Date Range Warning - Hotel Level Only */}
            {hasDateIssues && (
              <Alert 
                severity="warning" 
                icon={<WarningIcon fontSize="inherit" />}
                sx={{ mb: 1 }}
              >
                <Typography variant="body2">
                  <strong>Hotel Date Issue:</strong> {hotelDateValidation.message}
                  <br />
                  <em>Please adjust the hotel room dates to fit within the tour schedule.</em>
                </Typography>
              </Alert>
            )}
            
            <Accordion 
              expanded={expandedAccordions.has(hotelId)}
              onChange={() => handleAccordionChange(hotelId)}
              sx={{ 
                borderColor: isActiveHotel ? '#3554D1' : 'divider',
                bgcolor: isActiveHotel ? 'rgba(53, 84, 209, 0.05)' : 'transparent',
                '&:before': { display: 'none' },
                boxShadow: isActiveHotel ? 2 : 1,
                border: hasDateIssues ? '2px solid #ff9800' : '1px solid rgba(0, 0, 0, 0.12)'
              }}
            >
              <AccordionSummary
                expandIcon={<ExpandMoreIcon />}
                sx={{
                  '& .MuiAccordionSummary-content': {
                    alignItems: 'center'
                  }
                }}
              >
                <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', width: '100%', mr: 2 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', flex: 1 }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', mr: 2 }}>
                      <HotelIcon sx={{ color: hasDateIssues ? '#ff9800' : '#3554D1', mr: 1 }} />
                      <Typography variant="subtitle1" fontWeight={600}>
                        {hotel.hotel_name || hotel.name || 'Hotel'}
                      </Typography>
                      {isActiveHotel && (
                        <Chip label="Active" size="small" color="primary" sx={{ ml: 1 }} />
                      )}
                      {hasDateIssues && (
                        <Chip label="Date Issue" size="small" color="warning" sx={{ ml: 1 }} />
                      )}
                    </Box>
                    
                    {/* Quick Summary Chips */}
                    <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5 }}>
                      <Chip 
                        size="small" 
                        label={`${configurations.length} room${configurations.length > 1 ? 's' : ''}`} 
                        color="primary"
                        variant="outlined"
                      />
                      {hotelSummary.totalNights > 0 && (
                        <Chip 
                          size="small" 
                          label={`${hotelSummary.totalNights} total night${hotelSummary.totalNights > 1 ? 's' : ''}`} 
                          color="secondary"
                          variant="outlined"
                        />
                      )}
                      {hotelSummary.totalGuests > 0 && (
                        <Chip 
                          size="small" 
                          label={`${hotelSummary.totalGuests} total guest${hotelSummary.totalGuests > 1 ? 's' : ''}`} 
                          color="info"
                          variant="outlined"
                        />
                      )}
                      
                      {/* Date Ranges Summary */}
                      {hotelSummary.dateRanges.length > 0 && (
                        <Chip 
                          size="small" 
                          label={hotelSummary.dateRanges.length === 1 
                            ? `${hotelSummary.dateRanges[0].checkIn} - ${hotelSummary.dateRanges[0].checkOut}`
                            : `${hotelSummary.dateRanges.length} date ranges`
                          }
                          sx={{ 
                            bgcolor: hasDateIssues ? 'rgba(255, 152, 0, 0.1)' : 'rgba(76, 175, 80, 0.1)',
                            color: hasDateIssues ? 'warning.main' : 'success.main',
                            borderColor: hasDateIssues ? 'warning.main' : 'success.main'
                          }}
                          variant="outlined"
                        />
                      )}
                    </Box>
                  </Box>
                  
                  <Box sx={{ display: 'flex', alignItems: 'center' }}>
                    {/* Total Meal Plan Cost */}
                    {hotelSummary.totalMealPlanCost > 0 && (
                      <Typography variant="body2" color="success.main" fontWeight={600} sx={{ mr: 2 }}>
                        ${hotelSummary.totalMealPlanCost.toFixed(2)}
                      </Typography>
                    )}
                  </Box>
                </Box>
              </AccordionSummary>

              <AccordionDetails>
                {/* Hotel Information Header */}
                <Grid container spacing={2} sx={{ mb: 3 }}>
                  {/* Hotel Details */}
                  <Grid item xs={12} md={6}>
                    <Box sx={{ p: 2, bgcolor: 'rgba(25, 118, 210, 0.05)', borderRadius: 1 }}>
                      <Typography variant="subtitle2" color="primary" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
                        <HotelIcon sx={{ mr: 1, fontSize: 18 }} />
                        Hotel Information
                      </Typography>
                      <Typography variant="body2" fontWeight={500}>
                        {hotel.hotel_name || hotel.name || 'Hotel Name'}
                      </Typography>
                      <Typography variant="caption" color="text.secondary" display="block">
                        {hotel.address || hotel.location || 'Hotel Location'}
                      </Typography>
                    </Box>
                  </Grid>

                  {/* Date Information */}
                  <Grid item xs={12} md={6}>
                    <Box sx={{ p: 2, bgcolor: 'rgba(33, 150, 243, 0.05)', borderRadius: 1 }}>
                      <Typography variant="subtitle2" color="primary" gutterBottom sx={{ display: 'flex', alignItems: 'center' }}>
                        <CalendarTodayIcon sx={{ mr: 1, fontSize: 18 }} />
                        Date Information
                      </Typography>
                      {hotelSummary.overallCheckIn && hotelSummary.overallCheckOut ? (
                        <>
                          <Typography variant="body2">
                            <strong>Check-in:</strong> {hotelSummary.overallCheckIn}
                          </Typography>
                          <Typography variant="body2">
                            <strong>Check-out:</strong> {hotelSummary.overallCheckOut}
                          </Typography>
                        </>
                      ) : (
                        <Typography variant="body2" color="text.secondary">
                          No dates set for hotel rooms
                        </Typography>
                      )}
                      {tourDateRange && tourDateRange.startDate && tourDateRange.endDate && (
                        <Typography variant="caption" display="block" sx={{ mt: 1, color: 'text.secondary' }}>
                          <strong>Tour:</strong> {formatDate(tourDateRange.startDate)} - {formatDate(tourDateRange.endDate)}
                        </Typography>
                      )}
                    </Box>
                  </Grid>
                </Grid>

                {/* Room Configurations */}
                <Typography variant="subtitle2" gutterBottom sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                  <RoomIcon sx={{ mr: 1, fontSize: 18 }} />
                  Room Configurations ({configurations.length})
                </Typography>

                {configurations.map((config, configIndex) => {
                  // Debug logging to check data structure
                  console.log(`Room ${configIndex + 1} config:`, config);
                  console.log(`Room ${configIndex + 1} roomTypeName:`, config.roomTypeName);
                  console.log(`Room ${configIndex + 1} bedTypeName:`, config.bedTypeName);
                  console.log(`Room ${configIndex + 1} guestMealPlans:`, config.guestMealPlans);
                  
                  // Try multiple possible field names for room type
                  const roomType = config.roomTypeName || 
                                  config.roomType || 
                                  config.room_type_name || 
                                  config.room_type || 
                                  config.selectedRoomType ||
                                  config.roomDetails?.name ||
                                  config.roomDetails?.roomTypeName ||
                                  'Unknown Room';
                  
                  // Try multiple possible field names for bed type  
                  const bedType = config.bedTypeName || 
                                 config.bedType || 
                                 config.bed_type_name || 
                                 config.bed_type ||
                                 config.selectedBedType ||
                                 config.bedDetails?.name ||
                                 config.bedDetails?.bedTypeName ||
                                 'Unknown Bed';
                  
                  // Try multiple possible field names for meal plans
                  const guestMealPlans = config.guestMealPlans || 
                                        config.mealPlans || 
                                        config.meal_plans ||
                                        config.selectedMealPlans ||
                                        config.mealPlanDetails ||
                                        [];
                  
                  // Removed individual room date validation
                  const isActive = config.originalIndex === activeHotelIndex;
                  
                  // Log the final values
                  console.log(`Room ${configIndex + 1} final values:`, {
                    roomType,
                    bedType,
                    guestMealPlans: guestMealPlans.length,
                    isActive
                  });

                  return (
                    <Box key={config.id} sx={{ mb: 2, p: 2, bgcolor: isActive ? 'rgba(53, 84, 209, 0.05)' : 'rgba(245, 245, 245, 0.5)', borderRadius: 1, border: isActive ? '2px solid #3554D1' : '1px solid #e0e0e0' }}>
                      {/* Room Header */}
                      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
                        <Box sx={{ display: 'flex', alignItems: 'center' }}>
                          <Typography variant="subtitle2" fontWeight={600}>
                            Room {configIndex + 1}
                          </Typography>
                          {isActive && (
                            <Chip label="Active" size="small" color="primary" sx={{ ml: 1 }} />
                          )}
                          {/* Debug info chip */}
                          {/* <Chip 
                            label={`Debug: ${config.id || 'No ID'}`} 
                            size="small" 
                            color="info" 
                            variant="outlined"
                            sx={{ ml: 1 }} 
                          /> */}
                        </Box>
                        
                        <Box sx={{ display: 'flex', alignItems: 'center' }}>
                          {/* Meal Plan Cost for this room */}
                          {guestMealPlans.length > 0 && (
                            <Typography variant="body2" color="success.main" fontWeight={600} sx={{ mr: 2 }}>
                              ${guestMealPlans.reduce((total, mealPlanData) => {
                                const price = mealPlanData?.price || 0;
                                return total + price;
                              }, 0).toFixed(2)}
                            </Typography>
                          )}
                          
                          {/* Remove Button */}
                          <IconButton
                            size="small"
                            color="error"
                            onClick={(e) => {
                              e.stopPropagation();
                              if (removeHotelConfiguration) {
                                removeHotelConfiguration(config.originalIndex);
                              }
                            }}
                          >
                            <DeleteIcon fontSize="small" />
                          </IconButton>
                        </Box>
                      </Box>

                      {/* Room Details Grid - No more room-level date warnings */}
                      <Grid container spacing={2}>
                        {/* Room & Bed Information - Now takes full width */}
                        <Grid item xs={12}>
                          <Box sx={{ p: 1.5, bgcolor: 'rgba(76, 175, 80, 0.05)', borderRadius: 1 }}>
                            <Typography variant="caption" color="success.main" fontWeight={600} gutterBottom display="block">
                              Room & Bed Details
                            </Typography>
                            <Grid container spacing={2}>
                              <Grid item xs={12} sm={6} md={3}>
                                <Typography variant="body2">
                                  <strong>Room:</strong> {roomType}
                                  {roomType === 'Unknown Room' && (
                                    <Chip 
                                      label="Check data" 
                                      size="small" 
                                      color="warning"
                                      sx={{ ml: 1, height: 16, fontSize: '0.6rem' }}
                                    />
                                  )}
                                </Typography>
                              </Grid>
                              <Grid item xs={12} sm={6} md={3}>
                                <Typography variant="body2">
                                  <strong>Bed:</strong> {bedType}
                                  {bedType === 'Unknown Bed' && (
                                    <Chip 
                                      label="Check data" 
                                      size="small" 
                                      color="warning"
                                      sx={{ ml: 1, height: 16, fontSize: '0.6rem' }}
                                    />
                                  )}
                                  {config.max_occupancy && (
                                    <Chip 
                                      label={`Max ${config.max_occupancy}`} 
                                      size="small" 
                                      sx={{ ml: 1, height: 16, fontSize: '0.6rem' }}
                                    />
                                  )}
                                </Typography>
                              </Grid>
                              <Grid item xs={12} sm={6} md={3}>
                                <Typography variant="body2">
                                  <strong>Nights:</strong> {config.nights || 0}
                                </Typography>
                              </Grid>
                              <Grid item xs={12} sm={6} md={3}>
                                <Typography variant="body2">
                                  <strong>Guests:</strong> {config.selectedGuests || 0}
                                </Typography>
                              </Grid>
                            </Grid>
                          </Box>
                        </Grid>

                        {/* Guest Meal Plans */}
                        {guestMealPlans.length > 0 ? (
                          <Grid item xs={12}>
                            <Box sx={{ p: 1.5, bgcolor: 'rgba(255, 152, 0, 0.05)', borderRadius: 1 }}>
                              <Typography variant="caption" color="warning.main" fontWeight={600} gutterBottom display="block">
                                Guest Meal Plans ({config.selectedGuests || 0} guest{(config.selectedGuests || 0) !== 1 ? 's' : ''})
                              </Typography>
                              
                              <Grid container spacing={1}>
                                {guestMealPlans.map((mealPlanData, guestIndex) => {
                                  const planId = mealPlanData?.id || mealPlanData;
                                  const planName = mealPlanData?.name || (planId === 'self' ? 'Room Only' : planId);
                                  const planPrice = mealPlanData?.price || 0;
                                  
                                  return (
                                    <Grid item xs={12} sm={6} md={4} key={guestIndex}>
                                      <Box sx={{ 
                                        display: 'flex', 
                                        alignItems: 'center', 
                                        p: 1, 
                                        bgcolor: 'white', 
                                        borderRadius: 1,
                                        border: '1px solid #e0e0e0'
                                      }}>
                                        <Avatar sx={{ 
                                          width: 20, 
                                          height: 20, 
                                          fontSize: '0.6rem', 
                                          bgcolor: '#3554D1', 
                                          mr: 1 
                                        }}>
                                          {guestIndex + 1}
                                        </Avatar>
                                        <Box sx={{ flex: 1 }}>
                                          <Typography variant="caption" fontWeight={500}>
                                            Guest {guestIndex + 1}
                                          </Typography>
                                          <Typography variant="caption" color="text.secondary" display="block">
                                            {planName}
                                            {planPrice > 0 && (
                                              <Chip 
                                                label={`$${planPrice}`} 
                                                size="small" 
                                                sx={{ ml: 0.5, height: 14, fontSize: '0.5rem' }}
                                              />
                                            )}
                                          </Typography>
                                        </Box>
                                      </Box>
                                    </Grid>
                                  );
                                })}
                              </Grid>
                            </Box>
                          </Grid>
                        ) : (
                          <Grid item xs={12}>
                            <Box sx={{ p: 1.5, bgcolor: 'rgba(255, 152, 0, 0.05)', borderRadius: 1 }}>
                              <Typography variant="caption" color="warning.main" fontWeight={600} gutterBottom display="block">
                                Guest Meal Plans - No Data Found
                              </Typography>
                              <Typography variant="body2" color="text.secondary">
                                No meal plans found for this room. Check if data is being saved correctly.
                              </Typography>
                              <Typography variant="caption" color="text.secondary" display="block" sx={{ mt: 1 }}>
                                Expected fields: guestMealPlans, mealPlans, meal_plans, selectedMealPlans, mealPlanDetails
                              </Typography>
                            </Box>
                          </Grid>
                        )}
                      </Grid>
                    </Box>
                  );
                })}
              </AccordionDetails>
            </Accordion>
          </Box>
        );
      })}
    </Box>
  );
};

export default HotelConfigSummary;