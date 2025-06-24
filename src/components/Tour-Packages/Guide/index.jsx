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

  useEffect(() => {
    console.log('Guide Status:', status);
    console.log('Guides Data:', guides);
    console.log('Search Params:', searchParams);
  }, [status, guides, searchParams]);

  const handleAddMore = () => {
    const newIndex = formSections.length;
    setFormSections([...formSections, { ...defaultSection }]);
    setExpandedSections([...expandedSections, newIndex]);
  };

  const handleRemoveSection = (indexToRemove) => {
    setFormSections(formSections.filter((_, index) => index !== indexToRemove));
    setExpandedSections(expandedSections.filter(index => index !== indexToRemove).map(index => index > indexToRemove ? index - 1 : index));
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
  };

  const handleOpenModal = (index) => {
    setSelectedSectionIndex(index);
    setOpenModal(true);
  };

  const handleCloseModal = () => {
    setOpenModal(false);
    setSelectedSectionIndex(null);
  };

  const getBookingSummary = (booking) => {
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
  };

  const validateBookings = () => {
    if (formSections.length === 0) {
      setValidationError("Please add at least one guide booking.");
      return false;
    }
    
    for (let i = 0; i < formSections.length; i++) {
      const section = formSections[i];
      
      if (!section.guide) {
        setValidationError(`Booking #${i + 1}: Please select a guide.`);
        return false;
      }
      
      if (!section.pickUpTime) {
        setValidationError(`Booking #${i + 1}: Please select a pickup time.`);
        return false;
      }
      
      if (!section.hourlyPackage) {
        setValidationError(`Booking #${i + 1}: Please select a package.`);
        return false;
      }
      
      const totalPax = section.pax.Adults + section.pax.Children;
      if (totalPax <= 0) {
        setValidationError(`Booking #${i + 1}: Please select at least one person.`);
        return false;
      }
    }
    
    setValidationError(null);
    return true;
  };

  const handleBookNow = () => {
    if (!validateBookings()) {
      return;
    }
    
    const bookingsData = {
      type: 'guide',
      bookings: formSections.map((section, index) => {
        const summaryData = getBookingSummary(section);
        
        return {
          id: `guide-${Date.now()}-${index}`,
          guideId: section.guide,
          guideName: summaryData.guideName,
          city: summaryData.city,
          country: summaryData.country,
          pickUpTime: section.pickUpTime,
          pickUpTimeHour: section.pickUpTimeHour,
          duration: section.hourlyPackage,
          priceBreakdown: section.priceBreakdown,
          pax: section.pax,
          image: summaryData.image,
          mode: currentMode,
          languages: summaryData.languages,
          experience: summaryData.experience,
          bookingDate: section.bookingDate
        };
      })
    };
    
    console.log("Guide bookings data:", bookingsData);
    dispatch(setAllServices(bookingsData));
    
    setBookingSuccess(true);
    
    setTimeout(() => {
      setBookingSuccess(false);
    }, 5000);
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

  const getSelectedGuide = (guideId) => {
    return guides.find(g => g.id === guideId);
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