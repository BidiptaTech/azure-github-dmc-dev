import React, { useState, useEffect } from 'react';
import { 
  Box, Typography, Grid, Card, CardContent, Paper, Chip, Avatar,
  Accordion, AccordionSummary, AccordionDetails, IconButton, Alert, Button, Divider,
  Tooltip, ButtonGroup, Badge, LinearProgress, Stepper, Step, StepLabel, StepContent
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
import EditIcon from '@mui/icons-material/Edit';
import AddIcon from '@mui/icons-material/Add';
import ContentCopyIcon from '@mui/icons-material/ContentCopy';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import PendingIcon from '@mui/icons-material/Pending';
import ErrorOutlineIcon from '@mui/icons-material/ErrorOutline';
import TaskAltIcon from '@mui/icons-material/TaskAlt';
import PlayCircleOutlineIcon from '@mui/icons-material/PlayCircleOutline';
import AddBusinessIcon from '@mui/icons-material/AddBusiness';
import { useSelector } from 'react-redux';

/**
 * Enhanced Hotel Configuration Summary component with better UX
 */
const HotelConfigSummary = ({ 
  hotelConfigurations, 
  activeHotelIndex, 
  selectHotelConfiguration, 
  removeHotelConfiguration,
  onAddMoreRooms,
  onAddNewHotel,
  tourDateRange 
}) => {
  const [expandedAccordions, setExpandedAccordions] = useState(new Set());
  const PriceHide = useSelector((state) => state.auth.PriceHide);   
  // Get tour dates from Redux state to check for date conflicts
  const searchCriteria = useSelector(state => state.tourPackages?.searchCriteria);
  const tourDates = searchCriteria?.dates || [];

  // Calculate overall booking status
  const bookingStatus = React.useMemo(() => {
    if (!hotelConfigurations || hotelConfigurations.length === 0) {
      return { 
        status: 'empty', 
        message: 'No hotels added yet',
        completedRooms: 0,
        totalRooms: 0,
        progress: 0
      };
    }

    const totalRooms = hotelConfigurations.length;
    const completedRooms = hotelConfigurations.filter(config => 
      config.hotelId && config.roomTypeId && config.bedTypeId
    ).length;
    
    const progress = totalRooms > 0 ? (completedRooms / totalRooms) * 100 : 0;

    if (completedRooms === 0) {
      return { 
        status: 'started', 
        message: 'Hotel booking in progress',
        completedRooms,
        totalRooms,
        progress
      };
    } else if (completedRooms === totalRooms) {
      return { 
        status: 'complete', 
        message: 'All hotels fully configured',
        completedRooms,
        totalRooms,
        progress
      };
    } else {
      return { 
        status: 'partial', 
        message: 'Some hotels need configuration',
        completedRooms,
        totalRooms,
        progress
      };
    }
  }, [hotelConfigurations]);

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

  // Ensure at least one accordion is expanded if there are hotels
  useEffect(() => {
    if (Object.keys(groupedConfigurations).length > 0 && expandedAccordions.size === 0) {
      const firstHotelId = Object.keys(groupedConfigurations)[0];
      setExpandedAccordions(new Set([firstHotelId]));
    }
  }, [groupedConfigurations, expandedAccordions.size]);

  // Empty state with enhanced onboarding
  if (Object.keys(groupedConfigurations).length === 0) {
    return (
      <Paper 
        elevation={0} 
        sx={{ 
          p: 2.5,
          textAlign: 'center', 
          background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
          color: 'white',
          borderRadius: 2,
          position: 'relative',
          overflow: 'hidden'
        }}
      >
        {/* Background decoration */}
        <Box
          sx={{
            position: 'absolute',
            top: -30,
            right: -30,
            width: 120,
            height: 120,
            borderRadius: '50%',
            background: 'rgba(255, 255, 255, 0.1)',
            zIndex: 0
          }}
        />
        
        <Box sx={{ position: 'relative', zIndex: 1 }}>
          <HotelIcon sx={{ fontSize: 48, mb: 1.5, opacity: 0.9 }} />
          <Typography variant="h5" gutterBottom sx={{ fontWeight: 600 }}>
            Let's Book Your Hotels! 🏨
          </Typography>
          
          {/* Tour date display */}
          {tourDateRange && tourDateRange.startDate && tourDateRange.endDate && (
            <Paper 
              sx={{ 
                p: 1.5,
                mb: 2,
                bgcolor: 'rgba(255, 255, 255, 0.15)', 
                backdropFilter: 'blur(10px)',
                borderRadius: 2,
                display: 'inline-block'
              }}
            >
              <Typography variant="caption" sx={{ display: 'flex', alignItems: 'center', color: 'white' }}>
                <CalendarTodayIcon sx={{ mr: 1, fontSize: 16 }} />
                Tour Dates: {new Date(tourDateRange.startDate).toLocaleDateString()} - {new Date(tourDateRange.endDate).toLocaleDateString()}
              </Typography>
              <Typography variant="caption" sx={{ display: 'block', color: 'rgba(255,255,255,0.8)', mt: 0.5 }}>
                Hotels will use these dates by default until you select specific nights
              </Typography>
            </Paper>
          )}
        </Box>
      </Paper>
    );
  }

  // Status indicator component
  const StatusIndicator = ({ status, progress, completedRooms, totalRooms }) => {
    const getStatusConfig = () => {
      switch (status) {
        case 'complete':
          return {
            icon: <TaskAltIcon />,
            color: 'success',
            bgcolor: '#e8f5e8',
            borderColor: '#4caf50'
          };
        case 'partial':
          return {
            icon: <PendingIcon />,
            color: 'warning',
            bgcolor: '#fff8e1',
            borderColor: '#ff9800'
          };
        default:
          return {
            icon: <PlayCircleOutlineIcon />,
            color: 'primary',
            bgcolor: '#e3f2fd',
            borderColor: '#2196f3'
          };
      }
    };

    const config = getStatusConfig();

    return (
      <Paper 
        elevation={2}
        sx={{ 
          p: 3, 
          mb: 3, 
          bgcolor: config.bgcolor,
          border: `2px solid ${config.borderColor}`,
          borderRadius: 2
        }}
      >
        <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
          <Avatar sx={{ bgcolor: config.borderColor, mr: 2 }}>
            {config.icon}
          </Avatar>
          <Box sx={{ flex: 1 }}>
            <Typography variant="h6" sx={{ color: config.borderColor, fontWeight: 600 }}>
              Hotel Booking Status
            </Typography>
            <Typography variant="body2" color="text.secondary">
              {completedRooms} of {totalRooms} rooms configured
            </Typography>
          </Box>
          <Chip 
            label={`${Math.round(progress)}% Complete`}
            color={config.color}
            variant="filled"
            sx={{ fontWeight: 600 }}
          />
        </Box>
        
        <LinearProgress 
          variant="determinate" 
          value={progress} 
          sx={{ 
            height: 8, 
            borderRadius: 4,
            bgcolor: 'rgba(0,0,0,0.1)',
            '& .MuiLinearProgress-bar': {
              borderRadius: 4
            }
          }}
        />
      </Paper>
    );
  };

  // Quick action buttons
  const QuickActions = () => (
    <Paper elevation={1} sx={{ p: 3, mb: 3, borderRadius: 2, bgcolor: '#f8f9fa' }}>
      <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center', color: 'primary.main' }}>
        <EditIcon sx={{ mr: 1 }} />
        Quick Actions
      </Typography>
      
      <Grid container spacing={2}>
        <Grid item xs={12} sm={6}>
          <Button
            fullWidth
            variant="outlined"
            size="large"
            startIcon={<AddIcon />}
            onClick={onAddMoreRooms}
            disabled={!onAddMoreRooms}
            sx={{ 
              py: 1.5,
              justifyContent: 'flex-start',
              textTransform: 'none',
              fontWeight: 500
            }}
          >
            <Box sx={{ textAlign: 'left', ml: 1 }}>
              <Typography variant="subtitle2">Add More Rooms</Typography>
              <Typography variant="caption" color="text.secondary">
                Same hotel, additional rooms
              </Typography>
            </Box>
          </Button>
        </Grid>
        
        <Grid item xs={12} sm={6}>
          <Button
            fullWidth
            variant="contained"
            size="large"
            startIcon={<AddBusinessIcon />}
            onClick={onAddNewHotel}
            sx={{ 
              py: 1.5,
              justifyContent: 'flex-start',
              textTransform: 'none',
              fontWeight: 500
            }}
          >
            <Box sx={{ textAlign: 'left', ml: 1 }}>
              <Typography variant="subtitle2" color="white">Add Different Hotel</Typography>
              <Typography variant="caption" sx={{ color: 'rgba(255,255,255,0.8)' }}>
                New hotel location
              </Typography>
            </Box>
          </Button>
        </Grid>
      </Grid>
    </Paper>
  );

  // Function to check if hotel overall dates are within tour date range
  const isHotelDateInRange = (overallCheckIn, overallCheckOut) => {
    if (!tourDateRange || !tourDateRange.startDate || !tourDateRange.endDate) {
      return { valid: true, message: '' };
    }

    if (!overallCheckIn || !overallCheckOut) {
      return { valid: true, message: '' };
    }

    const hotelCheckIn = new Date(overallCheckIn);
    const hotelCheckOut = new Date(overallCheckOut);
    const tourStart = new Date(tourDateRange.startDate);
    const tourEnd = new Date(tourDateRange.endDate);

    hotelCheckIn.setHours(0, 0, 0, 0);
    hotelCheckOut.setHours(0, 0, 0, 0);
    tourStart.setHours(0, 0, 0, 0);
    tourEnd.setHours(0, 0, 0, 0);

    if (hotelCheckIn < tourStart) {
      return { 
        valid: false, 
        message: `Check-in date is before tour start date.`
      };
    }

    if (hotelCheckOut > tourEnd) {
      return { 
        valid: false, 
        message: `Check-out date is after tour end date.`
      };
    }

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
    // Hotel nights should be the same for all rooms in the same hotel, so take from first room
    const hotelNights = configurations.length > 0 ? (configurations[0].nights || 0) : 0;
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
    
    let overallCheckIn = null;
    let overallCheckOut = null;
    let isUsingTourDates = false;
    
    if (dateRanges.length > 0) {
      // Use specific hotel dates if available
      overallCheckIn = new Date(Math.min(...dateRanges.map(range => range.checkInDate.getTime())));
      overallCheckOut = new Date(Math.max(...dateRanges.map(range => range.checkOutDate.getTime())));
      overallCheckIn = formatDate(overallCheckIn);
      overallCheckOut = formatDate(overallCheckOut);
    } else if (tourDateRange && tourDateRange.startDate && tourDateRange.endDate) {
      // Fallback to tour dates if no specific hotel dates are selected
      overallCheckIn = formatDate(tourDateRange.startDate);
      overallCheckOut = formatDate(tourDateRange.endDate);
      isUsingTourDates = true;
    }
    
    return { 
      totalRooms: configurations.length,
      hotelNights, 
      totalGuests, 
      totalMealPlanCost, 
      dateRanges,
      overallCheckIn,
      overallCheckOut,
      isUsingTourDates
    };
  };
  
  // Helper function to detect date conflicts
  const hasDateConflict = (config, tourDates) => {
    if (!config.selectedNightIndices || config.selectedNightIndices.length === 0 || !tourDates || tourDates.length === 0) {
      return false;
    }

    const maxNightIndex = tourDates.length - 2; // -1 for length to index, -1 because last date is checkout
    return config.selectedNightIndices.some(nightIndex => nightIndex > maxNightIndex);
  };

  // Helper function to get status info including date conflicts
  const getHotelStatusInfo = (config, tourDates) => {
    const hasRooms = config.rooms && config.rooms.length > 0;
    const hasNights = config.selectedNightIndices && config.selectedNightIndices.length > 0;
    const hasConflict = hasDateConflict(config, tourDates);
    const hotelName = config.hotelDetails?.hotel_name || `Hotel ${config.id}`;
    
    // Date conflict takes priority
    if (hasConflict) {
      return {
        status: 'conflict',
        color: 'error',
        icon: <WarningIcon />,
        text: 'Date Issue',
        fullText: `${hotelName} - Date Conflict`,
        description: 'Selected nights are outside the current date range'
      };
    }
    
    if (hasRooms && hasNights) {
      return {
        status: 'complete',
        color: 'success',
        icon: <CheckCircleIcon />,
        text: 'Complete',
        fullText: `${hotelName} - Fully Configured`,
        description: 'Hotel, rooms, and nights selected'
      };
    } else if (hasRooms || hasNights) {
      return {
        status: 'partial',
        color: 'warning',
        icon: <PendingIcon />,
        text: 'Partial',
        fullText: `${hotelName} - Partially Configured`,
        description: hasRooms ? 'Rooms selected, choose nights' : 'Nights selected, choose rooms'
      };
    } else {
      return {
        status: 'incomplete',
        color: 'default',
        icon: <PendingIcon />,
        text: 'Not Started',
        fullText: `${hotelName} - Not Started`,
        description: 'No configuration yet'
      };
    }
  };

  return (
    <Box sx={{ mb: 2 }}>
      {/* Status Overview */}
      <StatusIndicator 
        status={bookingStatus.status}
        progress={bookingStatus.progress}
        completedRooms={bookingStatus.completedRooms}
        totalRooms={bookingStatus.totalRooms}
      />

      {/* Quick Actions */}
      <QuickActions />

      {/* Hotel Configurations */}
      <Typography variant="h6" gutterBottom sx={{ display: 'flex', alignItems: 'center', mb: 1.5 }}>
        <HotelIcon sx={{ mr: 1, color: 'primary.main' }} />
        Your Hotel Bookings ({Object.keys(groupedConfigurations).length})
      </Typography>
      
      {Object.entries(groupedConfigurations).map(([hotelId, hotelGroup]) => {
        const hotel = hotelGroup.hotelDetails;
        const configurations = hotelGroup.configurations;
        const hotelSummary = getHotelSummary(configurations);
        
        const hotelDateValidation = isHotelDateInRange(
          hotelSummary.overallCheckIn, 
          hotelSummary.overallCheckOut
        );
        
        const hasDateIssues = !hotelDateValidation.valid;
        const isActiveHotel = configurations.some(config => config.originalIndex === activeHotelIndex);
        
        // Check for date conflicts in any room configuration
        const hasDateConflicts = configurations.some(config => hasDateConflict(config, tourDates));
        
        // Calculate completion status for this hotel
        const completedRooms = configurations.filter(config => 
          config.hotelId && config.roomTypeId && config.bedTypeId
        ).length;
        const totalRooms = configurations.length;
        const hotelProgress = totalRooms > 0 ? (completedRooms / totalRooms) * 100 : 0;
        
        // Get the overall status for this hotel
        const statusInfo = getHotelStatusInfo(configurations[0], tourDates);
        const hasAnyIssues = hasDateIssues || hasDateConflicts;
        
        const isExpanded = expandedAccordions.has(hotelId);
        
        return (
          <Accordion 
            key={hotelId}
            expanded={isExpanded}
            onChange={() => handleAccordionChange(hotelId)}
            elevation={isActiveHotel ? 4 : 1}
            sx={{ 
              mb: 2,
              border: hasDateConflicts ? '2px solid #f44336' : isActiveHotel ? '2px solid #3554D1' : hasDateIssues ? '2px solid #ff9800' : '1px solid rgba(0, 0, 0, 0.12)',
              borderRadius: 2,
              background: hasDateConflicts
                ? 'linear-gradient(135deg, rgba(244, 67, 54, 0.05) 0%, rgba(244, 67, 54, 0.02) 100%)'
                : isActiveHotel 
                  ? 'linear-gradient(135deg, rgba(53, 84, 209, 0.05) 0%, rgba(53, 84, 209, 0.02) 100%)'
                  : 'white',
              transition: 'all 0.3s ease',
              '&:before': {
                display: 'none' // Remove default MUI accordion border
              }
            }}
          >
            {/* Date Range Warning */}
            {(hasDateIssues || hasDateConflicts) && (
              <Alert 
                severity={hasDateConflicts ? "error" : "warning"} 
                icon={<WarningIcon />}
                sx={{ borderRadius: '8px 8px 0 0', border: 'none' }}
              >
                <Typography variant="body2">
                  {hasDateConflicts ? (
                    <>
                      <strong>Night Selection Issue:</strong> Some selected nights are outside the updated tour date range.
                      <br />
                      <em>Please update your night selection in the configuration section below.</em>
                    </>
                  ) : (
                    <>
                      <strong>Date Conflict:</strong> {hotelDateValidation.message}
                      <br />
                      <em>Please adjust the room dates to fit within your tour schedule.</em>
                    </>
                  )}
                </Typography>
              </Alert>
            )}
            
            <AccordionSummary
              expandIcon={
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                  {hasDateConflicts && <ErrorOutlineIcon sx={{ color: 'error.main', fontSize: 18 }} />}
                  {hasDateIssues && !hasDateConflicts && <WarningIcon sx={{ color: 'warning.main', fontSize: 18 }} />}
                  {isActiveHotel && <EditIcon sx={{ color: 'primary.main', fontSize: 16 }} />}
                  <ExpandMoreIcon />
                </Box>
              }
              sx={{ 
                p: 2,
                minHeight: '64px',
                '& .MuiAccordionSummary-content': { 
                  margin: 0,
                  '&.Mui-expanded': { margin: 0 }
                },
                '&:hover': {
                  bgcolor: 'rgba(0, 0, 0, 0.02)'
                }
              }}
            >
              {/* Hotel Header - Compact Summary */}
              <Box sx={{ display: 'flex', alignItems: 'center', flex: 1, pr: 2 }}>
                <Avatar 
                  sx={{
                    bgcolor: hasDateConflicts ? '#f44336' : hasDateIssues ? '#ff9800' : '#3554D1', 
                    width: 40,
                    height: 40,
                    mr: 1.5
                  }}
                >
                  <HotelIcon fontSize="small" />
                </Avatar>
                
                <Box sx={{ flex: 1 }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', flexWrap: 'wrap', gap: 1 }}>
                    <Typography variant="h6" sx={{ fontWeight: 600 }}>
                      {hotel.hotel_name || hotel.name || 'Hotel'}
                    </Typography>
                    
                    {isActiveHotel && (
                      <Chip label="Editing" size="small" color="primary" />
                    )}
                    
                    {hasDateConflicts && (
                      <Chip 
                        label="Date Issue" 
                        size="small" 
                        color="error" 
                        icon={<ErrorOutlineIcon />}
                      />
                    )}
                    
                    {hasDateIssues && !hasDateConflicts && (
                      <Chip label="Issue" size="small" color="warning" />
                    )}
                    
                    <Chip 
                      label={`${Math.round(hotelProgress)}% Complete`}
                      size="small" 
                      color={hotelProgress === 100 ? 'success' : hotelProgress > 0 ? 'warning' : 'default'}
                    />
                  </Box>
                  
                  <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5, mt: 0.5 }}>
                    <Chip 
                      size="small" 
                      label={`${totalRooms} Room${totalRooms !== 1 ? 's' : ''}`}
                      variant="outlined"
                      color="primary"
                    />
                    {hotelSummary.hotelNights > 0 ? (
                      <Chip 
                        size="small" 
                        label={`${hotelSummary.hotelNights} Night${hotelSummary.hotelNights !== 1 ? 's' : ''}`}
                        variant="outlined"
                        color="secondary"
                      />
                    ) : hotelSummary.isUsingTourDates ? (
                      <Chip 
                        size="small" 
                        label="Tour Dates"
                        variant="outlined"
                        color="warning"
                        sx={{ fontStyle: 'italic' }}
                      />
                    ) : null}
                    <Chip 
                      size="small" 
                      label={`${hotelSummary.totalGuests} Guest${hotelSummary.totalGuests !== 1 ? 's' : ''}`}
                      variant="outlined"
                      color="info"
                    />
                  </Box>
                </Box>
                
                {/* Price Display - Compact */}
                {hotelSummary.totalMealPlanCost > 0 && PriceHide === "0" ? (
                  <Chip
                    label={`$${hotelSummary.totalMealPlanCost.toFixed(2)}`}
                    size="small"
                    color="success"
                    variant="filled"
                    sx={{ fontWeight: 600 }}
                  />
                ):(
                  <Chip
                    label="Price hide"
                    size="small"
                    color="success"
                    variant="filled"
                    sx={{ fontWeight: 600 }}
                  />
                  )}
              </Box>
            </AccordionSummary>

            <AccordionDetails sx={{ p: 1 }}>
              {/* Full Hotel Details */}
              <Box sx={{ p: 2 }}>
                <Typography variant="body2" color="text.secondary" gutterBottom>
                  📍 {hotel.address || hotel.location || 'Hotel Location'}
                </Typography>
                
                {hotelSummary.overallCheckIn && hotelSummary.overallCheckOut && (
                  <Typography variant="body2" color="text.secondary" gutterBottom>
                    📅 {hotelSummary.overallCheckIn} - {hotelSummary.overallCheckOut}
                    {hotelSummary.isUsingTourDates && (
                      <Typography component="span" variant="caption" sx={{ 
                        ml: 1, 
                        color: 'warning.main', 
                        fontStyle: 'italic' 
                      }}>
                        (Tour dates - select nights to customize)
                      </Typography>
                    )}
                  </Typography>
                )}
              </Box>

              {/* Room Cards */}
              <Typography variant="subtitle1" gutterBottom sx={{ fontWeight: 600, mb: 1.5 }}>
                Room Configurations
              </Typography>

              <Grid container spacing={1.5}>
                {configurations.map((config, configIndex) => {
                  const isActive = config.originalIndex === activeHotelIndex;
                  const isComplete = config.hotelId && config.roomTypeId && config.bedTypeId;

                  return (
                    <Grid item xs={12} sm={6} md={4} key={config.id}>
                      <Card
                        elevation={isActive ? 3 : 1}
                        sx={{
                          cursor: 'pointer',
                          border: isActive ? '2px solid #3554D1' : '1px solid rgba(0, 0, 0, 0.12)',
                          borderRadius: 2,
                          transition: 'all 0.2s ease',
                          background: isActive 
                            ? 'linear-gradient(135deg, rgba(53, 84, 209, 0.08) 0%, rgba(53, 84, 209, 0.02) 100%)'
                            : 'white',
                          '&:hover': {
                            transform: 'translateY(-2px)',
                            boxShadow: 4
                          }
                        }}
                        onClick={() => selectHotelConfiguration(config.originalIndex)}
                      >
                        <CardContent sx={{ p: 1.5 }}>
                          {/* Room Header */}
                          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 1.5 }}>
                            <Box sx={{ display: 'flex', alignItems: 'center' }}>
                              <Avatar sx={{ 
                                bgcolor: isComplete ? '#4caf50' : '#ff9800', 
                                width: 32, 
                                height: 32, 
                                mr: 1 
                              }}>
                                {isComplete ? <CheckCircleIcon fontSize="small" /> : <PendingIcon fontSize="small" />}
                              </Avatar>
                              <Typography variant="subtitle2" sx={{ fontWeight: 600 }}>
                                Room {configIndex + 1}
                              </Typography>
                            </Box>
                            
                            <IconButton
                              size="small"
                              color="error"
                              onClick={(e) => {
                                e.stopPropagation();
                                removeHotelConfiguration(config.originalIndex);
                              }}
                            >
                              <DeleteIcon fontSize="small" />
                            </IconButton>
                          </Box>

                          {/* Room Details */}
                          <Box sx={{ mb: 1.5 }}>
                            <Typography variant="body2" color="text.secondary" gutterBottom>
                              <strong>Room:</strong> {config.roomTypeName || 'Not selected'}
                            </Typography>
                            <Typography variant="body2" color="text.secondary" gutterBottom>
                              <strong>Bed:</strong> {config.bedTypeName || 'Not selected'}
                            </Typography>
                            <Typography variant="body2" color="text.secondary" gutterBottom>
                              <strong>Guests:</strong> {config.selectedGuests || 0} • <strong>Nights:</strong> {config.nights || 0}
                            </Typography>
                            
                            {/* Selected Meal Plans */}
                            <Box sx={{ mt: 1 }}>
                              <Typography variant="caption" sx={{ 
                                fontWeight: 600, 
                                color: 'primary.main',
                                display: 'flex',
                                alignItems: 'center'
                              }}>
                                <RestaurantMenuIcon sx={{ fontSize: 14, mr: 0.5 }} />
                                Meal Plans:
                              </Typography>
                              
                              {config.guestMealPlans && config.guestMealPlans.length > 0 ? (
                                <Box sx={{ mt: 0.5 }}>
                                  <Box sx={{ display: 'flex', flexDirection: 'column', gap: 0.25, mb: 0.5 }}>
                                    {config.guestMealPlans.map((mealPlan, mealIndex) => (
                                      <Box key={mealIndex} sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                                        <Typography variant="caption" sx={{ 
                                          color: 'text.secondary',
                                          fontSize: '0.7rem'
                                        }}>
                                          Guest {mealIndex + 1}: {mealPlan?.name || 'Room Only'}
                                        </Typography>
                                        {mealPlan?.price > 0 && PriceHide === "0" ? (
                                          <Typography variant="caption" sx={{ 
                                            color: 'success.main',
                                            fontSize: '0.65rem',
                                            fontWeight: 600
                                          }}>
                                            ${mealPlan.price.toFixed(2)}
                                          </Typography>
                                        ):(
                                          <Typography variant="caption" sx={{ 
                                            color: 'success.main',
                                            fontSize: '0.65rem',
                                            fontWeight: 600
                                          }}>
                                            Price hide
                                          </Typography>
                                        )}
                                      </Box>
                                    ))}
                                  </Box>
                                  
                                  {/* Room Meal Total */}
                                  {(() => {
                                    const roomMealTotal = config.guestMealPlans.reduce((sum, meal) => sum + (meal?.price || 0), 0);
                                    return roomMealTotal > 0 && (
                                      <Box sx={{ 
                                        pt: 0.5, 
                                        borderTop: '1px dashed rgba(76, 175, 80, 0.3)',
                                        display: 'flex', 
                                        justifyContent: 'space-between',
                                        alignItems: 'center'
                                      }}>
                                        <Typography variant="caption" sx={{ 
                                          fontWeight: 600,
                                          fontSize: '0.7rem',
                                          color: 'success.main'
                                        }}>
                                          Room Total:
                                        </Typography>
                                        {PriceHide === "0" ? (
                                        <Chip 
                                          label={`$${roomMealTotal.toFixed(2)}`}
                                          size="small" 
                                          color="success"
                                          variant="filled"
                                          sx={{ 
                                            height: 18, 
                                            fontSize: '0.65rem',
                                            fontWeight: 600
                                          }}
                                        />
                                        ):(
                                          <Chip 
                                            label="Price hide"
                                            size="small"
                                            color="success"
                                            variant="filled"
                                            sx={{ fontWeight: 600 }}
                                          />
                                        )}
                                      </Box>
                                    );
                                  })()}
                                </Box>
                              ) : (
                                <Box sx={{ mt: 0.5 }}>
                                  <Typography variant="caption" sx={{ 
                                    color: 'warning.main',
                                    fontSize: '0.7rem',
                                    fontStyle: 'italic'
                                  }}>
                                    No meal plans selected yet
                                  </Typography>
                                </Box>
                              )}
                            </Box>
                          </Box>

                          {/* Action Indicator */}
                          <Box sx={{ textAlign: 'center' }}>
                            {isActive ? (
                              <Chip 
                                label="Currently Editing" 
                                size="small" 
                                color="primary" 
                                variant="filled"
                              />
                            ) : (
                              <Chip 
                                label="Click to Edit" 
                                size="small" 
                                variant="outlined"
                                sx={{ '&:hover': { bgcolor: 'primary.main', color: 'white' } }}
                              />
                            )}
                          </Box>
                        </CardContent>
                      </Card>
                    </Grid>
                  );
                })}
              </Grid>

              {/* Hotel-specific Actions */}
              <Box sx={{ mt: 2, pt: 1.5, borderTop: '1px dashed rgba(0, 0, 0, 0.12)' }}>
                <Grid container spacing={2}>
                  <Grid item xs={12} sm={6}>
                    <Button
                      fullWidth
                      variant="outlined"
                      startIcon={<AddIcon />}
                      onClick={() => onAddMoreRooms && onAddMoreRooms()}
                      sx={{ textTransform: 'none' }}
                    >
                      Add More Rooms to This Hotel
                    </Button>
                  </Grid>
                  <Grid item xs={12} sm={6}>
                    <Button
                      fullWidth
                      variant="text"
                      color="error"
                      startIcon={<DeleteIcon />}
                      onClick={() => {
                        // Remove all rooms of this hotel
                        configurations.forEach(config => {
                          removeHotelConfiguration(config.originalIndex);
                        });
                      }}
                      sx={{ textTransform: 'none' }}
                    >
                      Remove This Hotel
                    </Button>
                  </Grid>
                </Grid>
              </Box>
            </AccordionDetails>
          </Accordion>
        );
      })}
    </Box>
  );
};

export default HotelConfigSummary;