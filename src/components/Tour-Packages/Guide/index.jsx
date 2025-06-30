import React, { useState, useEffect, useCallback, useMemo } from 'react';
import {
  Typography,
  Container,
  Button,
  Box,
  Grid,
  Card,
  CardContent,
  Stack,
  IconButton,
  Tooltip,
  Alert,
  Chip,
  Fade,
  Collapse,
  useTheme,
  alpha,
  Paper,
} from '@mui/material';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import VisibilityIcon from '@mui/icons-material/Visibility';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import PeopleIcon from '@mui/icons-material/People';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import BusinessCenterIcon from '@mui/icons-material/BusinessCenter';
import PersonIcon from '@mui/icons-material/Person';
import AssistantIcon from '@mui/icons-material/Assistant';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import { useDispatch, useSelector } from 'react-redux';
import GuideListing from './GuideListing';
import TimeSelection from './TimeSelection';
import PackageSelection from './PackageSelection';
import PassengerSelection from './PassengerSelection';
import GuideBookingSummaryModal from './GuideBookingSummaryModal';
import { setAllServices } from '../../../slice/tour-packages/tourPackageSlice';

const initialFormState = {
  guide: '',
  pickUpTime: '',
  pickUpTimeHour: null,
  hourlyPackage: '',
  bookingDate: new Date().toISOString().split('T')[0],
  priceBreakdown: {
    basePrice: 0,
    nightSurcharge: 0,
    totalPrice: 0,
    nightHours: 0,
    dayHours: 0
  },
  pax: {
    Adults: 1,
    Children: 0
  }
};

export default function GuideComponent() {
  const theme = useTheme();
  const dispatch = useDispatch();
  const selectedGuide = useSelector((state) => state.tourguide.selectedGuide);
  const guides = useSelector((state) => state.tourguide.Guides);
  const status = useSelector((state) => state.tourguide.status);
  const searchParams = useSelector((state) => state.tourguide.searchParams);
  const currentMode = useSelector((state) => state.common.bookingMode) || 'dmc';
  const agentId = useSelector((state) => state.editing?.agentId);
  const tourId = useSelector((state) => state.hotels.id);
  
  // Get existing services from Redux state
  const existingServices = useSelector((state) => state.tourPackages.AllServices || []);

  // Initialize form state with search params
  const defaultSection = useMemo(() => ({
    ...initialFormState,
    bookingDate: searchParams?.date || new Date().toISOString().split('T')[0],
    pax: {
      Adults: searchParams?.adults || searchParams?.adult || 1,
      Children: searchParams?.children || 0
    }
  }), [searchParams]);

  const [formSections, setFormSections] = useState([{ ...defaultSection }]);
  const [openModal, setOpenModal] = useState(false);
  const [selectedSectionIndex, setSelectedSectionIndex] = useState(null);
  const [validationError, setValidationError] = useState(false);
  const [bookingSuccess, setBookingSuccess] = useState(false);
  const [expandedSections, setExpandedSections] = useState([0]);
  // Track which sections have already been saved to Redux
  const [savedSectionIds, setSavedSectionIds] = useState([]);

  // Define helper functions at the beginning
  const getSelectedGuide = (guideId) => {
    return guides.find(g => g.id === guideId);
  };

  const getCompletionStatus = (section) => {
    const steps = [
      section.guide,
      section.pax.Adults + section.pax.Children > 0,
      section.pickUpTime,
      section.hourlyPackage
    ];
    return steps.filter(Boolean).length;
  };

  // Define memoized functions early
  const getBookingSummary = useCallback((booking) => {
    const selectedGuideDetails = guides.find(g => g.id === booking.guide) || {};
    
    return {
      guide: selectedGuideDetails,
      guideName: selectedGuideDetails.guide_name || 'Guide',
      city: selectedGuideDetails.city || searchParams?.location?.city || '',
      country: selectedGuideDetails.country || searchParams?.location?.country || '',
      pickUpTime: booking.pickUpTime,
      pickUpTimeHour: booking.pickUpTimeHour,
      duration: booking.hourlyPackage,
      pax: booking.pax,
      mode: currentMode,
      priceBreakdown: booking.priceBreakdown || { basePrice: 0, nightSurcharge: 0, totalPrice: 0 },
      image: selectedGuideDetails.image || '/placeholder-guide.jpg',
      languages: selectedGuideDetails.languages || [],
      experience: selectedGuideDetails.experience_years || 'Not specified',
      bookingDate: booking.bookingDate
    };
  }, [guides, searchParams, currentMode]);

  const validateBookings = useCallback(() => {
    if (formSections.length === 0) {
      setValidationError("Please add at least one guide booking.");
      return false;
    }
    
    // Only validate complete sections
    const completeSections = formSections.filter(section => 
      section.guide && 
      section.pickUpTime && 
      section.hourlyPackage && 
      (section.pax.Adults + section.pax.Children > 0)
    );
    
    if (completeSections.length === 0) {
      // Don't show error when auto-validating
      return false;
    }
    
    setValidationError(null);
    return true;
  }, [formSections, setValidationError]);

  const handleBookNow = useCallback(() => {
    if (!validateBookings()) {
      return;
    }
    
    // Filter out incomplete sections
    const completeSections = formSections.filter(section => 
      section.guide && 
      section.pickUpTime && 
      section.hourlyPackage && 
      (section.pax.Adults + section.pax.Children > 0)
    );
    
    if (completeSections.length === 0) {
      return; // No complete sections to save
    }
    
    // Clone the existing services array, but remove ALL previous guide services
    const servicesWithoutGuides = existingServices.filter(service => service.type !== "guide");
    
    // Create new guide services for the current complete sections
    const guideServices = completeSections.map((section, index) => {
      const summaryData = getBookingSummary(section);
      
      // Create unique booking ID - use formSections index to maintain identity
      const sectionIndex = formSections.indexOf(section);
      const bookingId = `guide-${Date.now()}-${sectionIndex}`;
      
      // Create the guide booking data
      // Get the guide details for this specific section
      const selectedGuideDetails = getSelectedGuide(section.guide);
      
      const bookingData = {
        id: bookingId,
        guide_id: section.guide,
        guide_name: summaryData.guideName,
        image: summaryData.image,
        dmc_Id: agentId,
        Mode: currentMode,
        entrypickup: section.pickUpTime,
        entrytime: section.pickUpTimeHour,
        adults: section.pax.Adults,
        children: section.pax.Children,
        hours: section.hourlyPackage,
        basePrice: section.priceBreakdown.basePrice,
        surcharge: section.priceBreakdown.nightSurcharge,
        totalPrice: section.priceBreakdown.totalPrice,
        pickupdate: section.bookingDate,
        Tax: selectedGuideDetails?.tax_percentage,
        Night_Start_Time: selectedGuideDetails?.night_start_time,
        Night_End_Time: selectedGuideDetails?.night_end_time,
        // Keep some fields that might still be needed
        city: summaryData.city,
        country: summaryData.country,
        languages: summaryData.languages,
        experience: summaryData.experience
      };
      
      console.log(`Guide booking data for section ${index}:`, bookingData);
      
      // Create a new guide service entry for this booking
      return {
        type: "guide",
        agent_id: agentId,
        tour_id: tourId,
        data: [bookingData]
      };
    });
    
    // Combine non-guide services with new guide services
    const updatedServices = [...servicesWithoutGuides, ...guideServices];
    
    console.log("Guide - Dispatching updated services to Redux:", updatedServices);
    
    // Dispatch the updated services
    dispatch(setAllServices(updatedServices));
    
    setBookingSuccess(true);
    
    setTimeout(() => {
      setBookingSuccess(false);
    }, 5000);
  }, [formSections, existingServices, validateBookings, dispatch, currentMode, getBookingSummary]);

  useEffect(() => {
    console.log('Guide Status:', status);
    console.log('Guides Data:', guides);
    console.log('Search Params:', searchParams);
  }, [status, guides, searchParams]);
  
  // Effect to automatically dispatch completed guide bookings to Redux
  useEffect(() => {
    // Skip if no form sections or during loading
    if (formSections.length === 0 || status === 'loading') return;
    
    // Find sections that are complete but not yet saved
    const newCompleteSections = formSections.filter((section, index) => {
      // Check if all required fields are filled
      const isComplete = (
        section.guide && 
        section.pickUpTime && 
        section.hourlyPackage && 
        (section.pax.Adults + section.pax.Children > 0)
      );
      
      // Generate a unique ID for this section based on its contents
      const sectionSignature = `${section.guide}-${section.pickUpTime}-${section.hourlyPackage}`;
      
      // Check if this section has already been saved
      const isSaved = savedSectionIds.includes(sectionSignature);
      
      // Return true if this section is complete and not yet saved
      return isComplete && !isSaved;
    });
    
    // If we found new complete sections, update Redux
    if (newCompleteSections.length > 0) {
      // Get signatures for the new sections
      const newSectionSignatures = newCompleteSections.map(section => 
        `${section.guide}-${section.pickUpTime}-${section.hourlyPackage}`
      );
      
      // Wait a bit to avoid too many Redux updates
      const timeoutId = setTimeout(() => {
        // Call handleBookNow
        handleBookNow();
        
        // Mark these sections as saved
        setSavedSectionIds(prev => [...prev, ...newSectionSignatures]);
      }, 500);
      
      return () => clearTimeout(timeoutId);
    }
  }, [formSections, handleBookNow, status, savedSectionIds]);

  const handleAddMore = () => {
    const newIndex = formSections.length;
    setFormSections([...formSections, { ...defaultSection }]);
    setExpandedSections([...expandedSections, newIndex]);
  };

  const handleRemoveSection = (indexToRemove) => {
    // Get the section being removed
    const sectionToRemove = formSections[indexToRemove];
    
    // Remove the section from formSections
    setFormSections(formSections.filter((_, index) => index !== indexToRemove));
    
    // Update expanded sections
    setExpandedSections(expandedSections.filter(index => index !== indexToRemove).map(index => index > indexToRemove ? index - 1 : index));
    
    // Remove section signature from saved IDs
    if (sectionToRemove) {
      const sectionSignature = `${sectionToRemove.guide}-${sectionToRemove.pickUpTime}-${sectionToRemove.hourlyPackage}`;
      setSavedSectionIds(prev => prev.filter(signature => signature !== sectionSignature));
    }
  };

  const toggleSectionExpand = (index) => {
    if (expandedSections.includes(index)) {
      setExpandedSections(expandedSections.filter(i => i !== index));
    } else {
      setExpandedSections([...expandedSections, index]);
    }
  };

  const handleInputChange = (sectionIndex, field, value) => {
    const newFormSections = [...formSections];
    
    if (field === 'guide') {
      newFormSections[sectionIndex] = {
        ...defaultSection,
        guide: value,
        bookingDate: newFormSections[sectionIndex].bookingDate,
        pax: newFormSections[sectionIndex].pax
      };
    } else if (field === 'pickUpTime') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        pickUpTime: value.value,
        pickUpTimeHour: value.hourValue
      };
    } else if (field === 'hourlyPackage') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        hourlyPackage: value.value,
        priceBreakdown: value.priceBreakdown || { basePrice: 0, nightSurcharge: 0, totalPrice: 0, nightHours: 0, dayHours: 0 }
      };
    } else if (field === 'pax') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        pax: value
      };
    } else {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        [field]: value
      };
    }
    
    setFormSections(newFormSections);
    
    // Check if the current section is now complete
    const updatedSection = newFormSections[sectionIndex];
    const isComplete = 
      updatedSection.guide && 
      updatedSection.pickUpTime && 
      updatedSection.hourlyPackage && 
      (updatedSection.pax.Adults + updatedSection.pax.Children > 0);
    
    // Generate signature for this section
    const oldSectionSignature = formSections[sectionIndex] ? 
      `${formSections[sectionIndex].guide}-${formSections[sectionIndex].pickUpTime}-${formSections[sectionIndex].hourlyPackage}` : '';
    
    const newSectionSignature = 
      `${updatedSection.guide}-${updatedSection.pickUpTime}-${updatedSection.hourlyPackage}`;
    
    // If the data changed, remove the old signature from saved list
    if (oldSectionSignature !== newSectionSignature) {
      setSavedSectionIds(prev => 
        prev.filter(signature => signature !== oldSectionSignature)
      );
      
      console.log(`Guide booking section ${sectionIndex + 1} data changed, will be re-evaluated for saving`);
    }
      
    // If the section just became complete, show success message
    if (isComplete) {
      // The useEffect will handle dispatching to Redux
      console.log(`Guide booking section ${sectionIndex + 1} is now complete`);
    }
  };

  const handleOpenModal = (index) => {
    setSelectedSectionIndex(index);
    setOpenModal(true);
  };

  const handleCloseModal = () => {
    setOpenModal(false);
    setSelectedSectionIndex(null);
  };

  if (!guides || guides.length === 0) {
    return (
      <Container maxWidth="xl">
        <Card 
          elevation={3}
          sx={{
            borderRadius: 3,
            background: 'linear-gradient(135deg, #2196f3 0%, #1976d2 100%)',
            color: 'white',
            mb: 2,
            mx: 'auto',
          }}
        >
          <CardContent sx={{ py: 2, textAlign: 'center' }}>
            <PersonIcon sx={{ fontSize: 64, color: '#FFD700', mb: 2 }} />
            <Typography variant="h6" color="white">
              Please search for guides first
            </Typography>
          </CardContent>
        </Card>
      </Container>
    );
  }

  return (
    <Container maxWidth="xl" sx={{ py: 2, position: 'relative' }}>
      {/* Header Card with Gradient Background */}
      <Card 
        elevation={3}
        sx={{
          borderRadius: 3,
          background: 'linear-gradient(135deg, #2196f3 0%, #1976d2 100%)',
          color: 'white',
          mb: 3,
          mx: 'auto',
        }}
      >
        <CardContent sx={{ py: 1}}>
          <Box display="flex" alignItems="center" justifyContent="space-between">
            <Box display="flex" alignItems="center">
              <AssistantIcon sx={{ mr: 2, fontSize: 32, color: '#FFD700' }} />
              <Box>
                <Typography variant="h5" fontWeight="600" sx={{ color: 'white' }}>
                  Book Tour Guide Services
                </Typography>
                <Typography variant="body2" sx={{ color: 'rgba(255, 255, 255, 0.8)' }}>
                  Select professional guides and configure your tour package
                </Typography>
              </Box>
            </Box>
            <Chip 
              label={`${formSections.length} Booking${formSections.length > 1 ? 's' : ''}`}
              sx={{ 
                bgcolor: 'rgba(255, 255, 255, 0.2)',
                color: 'white',
                fontWeight: 600,
                border: '1px solid rgba(255, 255, 255, 0.3)'
              }}
            />
          </Box>
        </CardContent>
      </Card>
      
      <Fade in={validationError} timeout={300}>
        <Box>
          {validationError && (
            <Alert severity="error" sx={{ mb: 2, borderRadius: 2 }}>
              {validationError}
            </Alert>
          )}
        </Box>
      </Fade>
      
      <Fade in={bookingSuccess} timeout={300}>
        <Box>
          {bookingSuccess && (
            <Alert severity="success" sx={{ mb: 2, borderRadius: 2 }}>
              Guide booking information saved successfully to the tour package data!
            </Alert>
          )}
        </Box>
      </Fade>
      
      <Grid container spacing={2}>
        {formSections.map((section, sectionIndex) => {
          const selectedGuideDetails = getSelectedGuide(section.guide);
          const completionStatus = getCompletionStatus(section);
          const isExpanded = expandedSections.includes(sectionIndex);
          
          return (
            <Grid item xs={12} key={sectionIndex}>
              <Card 
                elevation={2}
                sx={{ 
                  borderRadius: 3,
                  border: `2px solid ${alpha('#2196f3', 0.2)}`,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    boxShadow: `0 8px 24px ${alpha('#2196f3', 0.15)}`,
                    transform: 'translateY(-2px)',
                  }
                }}
              >
                <CardContent sx={{ p: 0 }}>
                  {/* Header */}
                  <Box sx={{ 
                    p: 2,
                    bgcolor: alpha('#2196f3', 0.05),
                    borderBottom: `1px solid ${alpha('#2196f3', 0.1)}`,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between'
                  }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                      <Chip 
                        label={`Booking ${sectionIndex + 1}`}
                        sx={{ 
                          bgcolor: '#2196f3',
                          color: 'white',
                          fontWeight: 600
                        }}
                        size="small"
                      />
                      <Chip 
                        label={`${completionStatus}/4 Complete`}
                        color={completionStatus === 4 ? "success" : "warning"}
                        size="small"
                        variant="outlined"
                      />
                      {selectedGuideDetails && (
                        <Chip 
                          icon={<LocationOnIcon sx={{ fontSize: 16 }} />}
                          label={selectedGuideDetails.city}
                          size="small"
                          variant="outlined"
                          sx={{ 
                            borderColor: '#2196f3',
                            color: '#2196f3'
                          }}
                        />
                      )}
                    </Box>
                    
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                      <Tooltip title={isExpanded ? "Collapse" : "Expand"}>
                        <IconButton 
                          size="small" 
                          onClick={() => toggleSectionExpand(sectionIndex)}
                          sx={{ 
                            bgcolor: alpha('#2196f3', 0.1),
                            '&:hover': { bgcolor: alpha('#2196f3', 0.2) }
                          }}
                        >
                          <i className={`icon-chevron-${isExpanded ? 'up' : 'down'}`} />
                        </IconButton>
                      </Tooltip>
                      
                      {section.guide && (
                        <Button
                          variant="outlined"
                          size="large"
                          onClick={() => handleOpenModal(sectionIndex)}
                          disabled={!section.guide}
                          startIcon={<VisibilityIcon />}
                          sx={{
                            borderRadius: 2,
                            px: 4,
                            py: 1,
                            fontSize: '0.875rem',
                            fontWeight: 600,
                            textTransform: 'none',
                            borderColor: '#2196f3',
                            color: '#2196f3',
                            '&:hover': {
                              borderColor: '#1976d2',
                              bgcolor: alpha('#2196f3', 0.05),
                              transform: 'translateY(-1px)',
                            },
                            transition: 'all 0.3s ease',
                          }}
                        >
                          View Summary
                        </Button>
                      )}
                                  
                      {sectionIndex > 0 && (
                        <Tooltip title="Remove Booking">
                          <IconButton 
                            size="small"
                            color="error" 
                            onClick={() => handleRemoveSection(sectionIndex)}
                            sx={{ 
                              bgcolor: alpha(theme.palette.error.main, 0.1),
                              '&:hover': { bgcolor: alpha(theme.palette.error.main, 0.2) }
                            }}
                          >
                            <DeleteIcon sx={{ fontSize: 18 }} />
                          </IconButton>
                        </Tooltip>
                      )}
                    </Box>
                  </Box>

                  {/* Summary when collapsed */}
                  {!isExpanded && selectedGuideDetails && (
                    <Box sx={{ p: 2 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                        <Box 
                          component="img"
                          src={selectedGuideDetails.image || '/placeholder-guide.jpg'}
                          alt={selectedGuideDetails.guide_name}
                          sx={{ 
                            width: 60, 
                            height: 60, 
                            borderRadius: 2,
                            objectFit: 'cover',
                            border: `2px solid ${alpha('#2196f3', 0.2)}`
                          }}
                        />
                        <Box sx={{ flex: 1 }}>
                          <Typography variant="h6" fontWeight={600} sx={{ mb: 0.5 }}>
                            {selectedGuideDetails.guide_name}
                          </Typography>
                          <Box sx={{ display: 'flex', gap: 2, flexWrap: 'wrap' }}>
                            {section.pax.Adults + section.pax.Children > 0 && (
                              <Chip 
                                icon={<PeopleIcon sx={{ fontSize: 16 }} />}
                                label={`${section.pax.Adults + section.pax.Children} Pax`}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#2196f3',
                                  color: '#2196f3'
                                }}
                              />
                            )}
                            {section.pickUpTime && (
                              <Chip 
                                icon={<AccessTimeIcon sx={{ fontSize: 16 }} />}
                                label={section.pickUpTime}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#2196f3',
                                  color: '#2196f3'
                                }}
                              />
                            )}
                            {section.hourlyPackage && (
                              <Chip 
                                icon={<BusinessCenterIcon sx={{ fontSize: 16 }} />}
                                label={`${section.hourlyPackage}h Package`}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#2196f3',
                                  color: '#2196f3'
                                }}
                              />
                            )}
                          </Box>
                        </Box>
                      </Box>
                    </Box>
                  )}

                  {/* Expanded Content */}
                  <Collapse in={isExpanded} timeout={300}>
                    <Paper 
                      elevation={0} 
                      sx={{ 
                        m: 2,
                        p: 0, 
                        borderRadius: 2,
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdropFilter: 'blur(10px)'
                      }}
                    >
                      <Grid container spacing={2} alignItems="flex-end">
                        {/* Guide Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <PersonIcon sx={{ mr: 1, color: '#2196f3', fontSize: 20 }} />
                              <Typography variant="subtitle2" fontWeight="600" color="text.primary">
                                Select Guide
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <GuideListing 
                                value={section.guide}
                                onChange={(field, value) => handleInputChange(sectionIndex, field, value)}
                                disabled={status === 'loading'}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Guests Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <PeopleIcon sx={{ mr: 1, color: '#2e7d32', fontSize: 20 }} />
                              <Typography 
                                variant="subtitle2" 
                                fontWeight="600"
                                color={!section.guide ? "text.disabled" : "text.primary"}
                              >
                                Select Guests
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <PassengerSelection
                                value={section.pax}
                                onChange={(value) => handleInputChange(sectionIndex, 'pax', value)}
                                disabled={!section.guide || status === 'loading'}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Time Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <AccessTimeIcon sx={{ mr: 1, color: '#ff9800', fontSize: 20 }} />
                              <Typography 
                                variant="subtitle2" 
                                fontWeight="600"
                                color={!section.guide ? "text.disabled" : "text.primary"}
                              >
                                Pickup Time
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <TimeSelection
                                value={section.pickUpTime}
                                onChange={(e) => handleInputChange(sectionIndex, 'pickUpTime', e)}
                                disabled={!section.guide || status === 'loading'}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Package Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <BusinessCenterIcon sx={{ mr: 1, color: '#9c27b0', fontSize: 20 }} />
                              <Typography 
                                variant="subtitle2" 
                                fontWeight="600"
                                color={!section.guide || !section.pickUpTime ? "text.disabled" : "text.primary"}
                              >
                                Select Package
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <PackageSelection
                                value={section.hourlyPackage}
                                onChange={(e) => handleInputChange(sectionIndex, 'hourlyPackage', e)}
                                disabled={!section.guide || !section.pickUpTime || status === 'loading'}
                                pickUpTime={section.pickUpTime ? { value: section.pickUpTime, hourValue: section.pickUpTimeHour } : null}
                                bookingDate={section.bookingDate}
                                formSection={section}
                              />
                            </Box>
                          </Box>
                        </Grid>
                      </Grid>
                    </Paper>
                  </Collapse>
                </CardContent>
              </Card>
            </Grid>
          );
        })}

        {/* Add More Card */}
        <Grid item xs={12}>
          <Card 
            sx={{ 
              borderRadius: 3,
              border: `2px dashed ${alpha('#2196f3', 0.4)}`,
              bgcolor: alpha('#2196f3', 0.02),
              cursor: 'pointer',
              transition: 'all 0.3s ease',
              '&:hover': {
                bgcolor: alpha('#2196f3', 0.05),
                borderColor: '#2196f3',
                transform: 'translateY(-1px)',
              }
            }}
            onClick={handleAddMore}
          >
            <CardContent sx={{ py: 2 }}>
              <Box sx={{ 
                display: 'flex', 
                alignItems: 'center', 
                justifyContent: 'center',
                gap: 2
              }}>
                <AddIcon sx={{ fontSize: 32, color: '#2196f3' }} />
                <Typography variant="h6" color="#2196f3" fontWeight={600}>
                  Add More
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>
        
        
      </Grid>

      <GuideBookingSummaryModal
        open={openModal}
        onClose={handleCloseModal}
        bookingData={selectedSectionIndex !== null ? formSections[selectedSectionIndex] : null}
        bookingIndex={selectedSectionIndex}
        guideDetails={selectedGuide}
      />
    </Container>
  );
} 