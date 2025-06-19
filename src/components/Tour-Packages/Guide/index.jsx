import React, { useState, useEffect, useCallback, useMemo } from 'react';
import {
  Typography,
  Container,
  Button,
  Box,
  Grid,
  Paper,
  Stack,
  IconButton,
  Tooltip,
  Alert,
} from '@mui/material';
import DeleteIcon from '@mui/icons-material/Delete';
import { useDispatch, useSelector } from 'react-redux';
import GuideListing from './GuideListing';
import TimeSelection from './TimeSelection';
import PackageSelection from './PackageSelection';
import PassengerSelection from './PassengerSelection';
import GuideBookingSummaryModal from './GuideBookingSummaryModal';
import { addGuideBookings } from '../../../slice/tour-packages/tourPackageSlice';

export default function GuideComponent() {
  const dispatch = useDispatch();
  const selectedGuide = useSelector((state) => state.tourguide.selectedGuide);
  const guides = useSelector((state) => state.tourguide.Guides);
  const status = useSelector((state) => state.tourguide.status);
  const searchParams = useSelector((state) => state.tourguide.searchParams);
  const currentMode = useSelector((state) => state.common.bookingMode) || 'dmc';

  // Initialize form sections with stable default values using useMemo
  const defaultSection = useMemo(() => ({
    guide: '',
    pickUpTime: '',
    pickUpTimeHour: null, // Store the hour value for calculations
    hourlyPackage: '',
    priceBreakdown: {
      basePrice: 0,
      nightSurcharge: 0,
      totalPrice: 0,
      nightHours: 0,
      dayHours: 0
    },
    pax: {
      Adults: searchParams?.adults || searchParams?.adult || 1,
      Children: searchParams?.children || 0
    }
  }), [searchParams?.adults, searchParams?.adult, searchParams?.children]);

  const [formSections, setFormSections] = useState([]);
  const [openModal, setOpenModal] = useState(false);
  const [selectedSectionIndex, setSelectedSectionIndex] = useState(null);
  const [validationError, setValidationError] = useState(false);
  const [bookingSuccess, setBookingSuccess] = useState(false);
  
  // Initialize form with one section when component mounts or searchParams changes
  useEffect(() => {
    if (searchParams && guides?.length > 0 && formSections.length === 0) {
      setFormSections([{ ...defaultSection }]);
    }
  }, [searchParams, guides, defaultSection]);

  // Debug logs with proper dependency array
  useEffect(() => {
    if (process.env.NODE_ENV === 'development') {
      console.log('Guide Status:', status);
      console.log('Guides Data:', guides);
      console.log('Selected Guide:', selectedGuide);
      console.log('Search Params:', searchParams);
      console.log('Default Section:', defaultSection);
      console.log('Form Sections:', formSections);
    }
  }, [status, guides, selectedGuide, searchParams, defaultSection, formSections]);

  // Memoize handlers to prevent unnecessary re-renders
  const handleAddMore = useCallback(() => {
    setFormSections(prev => [...prev, defaultSection]);
  }, [defaultSection]);

  const handleRemoveSection = useCallback((indexToRemove) => {
    setFormSections(prev => prev.filter((_, index) => index !== indexToRemove));
  }, []);

  const handleFieldChange = useCallback((sectionIndex, field, value) => {
    setFormSections(prev => {
      // Don't update state if the value hasn't changed
      if (prev[sectionIndex] && prev[sectionIndex][field] === value) {
        return prev;
      }
      
      const newSections = [...prev];
      
      // Special handling for guide field to reset related fields
      if (field === 'guide') {
        newSections[sectionIndex] = {
          ...defaultSection,
          guide: value,
          pickUpTime: '', // Reset pickup time when guide changes
          pickUpTimeHour: null,
          hourlyPackage: '', // Reset package when guide changes
          priceBreakdown: { basePrice: 0, nightSurcharge: 0, totalPrice: 0, nightHours: 0, dayHours: 0 }
        };
      } 
      // Special handling for pickup time to store hour value
      else if (field === 'pickUpTime') {
        newSections[sectionIndex] = {
          ...newSections[sectionIndex],
          pickUpTime: value.value,
          pickUpTimeHour: value.hourValue
        };
      }
      // Special handling for hourlyPackage to store price breakdown
      else if (field === 'hourlyPackage') {
        newSections[sectionIndex] = {
          ...newSections[sectionIndex],
          hourlyPackage: value.value,
          priceBreakdown: value.priceBreakdown || { basePrice: 0, nightSurcharge: 0, totalPrice: 0, nightHours: 0, dayHours: 0 }
        };
        
        // Automatically trigger booking when hourlyPackage is selected
        if (value.value) {
          setTimeout(() => {
            handleBookNow(newSections);
          }, 500); // Add a small delay to ensure state is updated
        }
      }
      else {
        newSections[sectionIndex] = {
          ...newSections[sectionIndex],
          [field]: value
        };
      }
      
      return newSections;
    });
  }, [defaultSection]);

  const handlePaxChange = useCallback((sectionIndex, value) => {
    setFormSections(prev => {
      // Don't update if the values haven't changed
      const currentPax = prev[sectionIndex]?.pax;
      if (currentPax && 
          currentPax.Adults === value.Adults && 
          currentPax.Children === value.Children) {
        return prev;
      }
      
      const newSections = [...prev];
      newSections[sectionIndex] = {
        ...newSections[sectionIndex],
        pax: value
      };
      return newSections;
    });
  }, []);

  const handleOpenModal = useCallback((index) => {
    setSelectedSectionIndex(index);
    setOpenModal(true);
  }, []);

  const handleCloseModal = useCallback(() => {
    setOpenModal(false);
    setSelectedSectionIndex(null);
  }, []);

  const validateBookings = useCallback((sections = formSections) => {
    // Check if there's at least one booking
    if (sections.length === 0) {
      setValidationError("Please add at least one guide booking.");
      return false;
    }
    
    // Validate each booking section
    for (let i = 0; i < sections.length; i++) {
      const section = sections[i];
      
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
      
      // Check if at least one person is selected
      const totalPax = section.pax.Adults + section.pax.Children;
      if (totalPax <= 0) {
        setValidationError(`Booking #${i + 1}: Please select at least one person.`);
        return false;
      }
    }
    
    setValidationError(null);
    return true;
  }, []);

  const getBookingSummary = useCallback((booking) => {
    const selectedGuideDetails = guides.find(g => g.id === booking.guide) || {};
    
    // Get price breakdown from the booking data
    const priceBreakdown = booking.priceBreakdown || { 
      basePrice: 0, 
      nightSurcharge: 0, 
      totalPrice: 0 
    };
    
    return {
      guide: selectedGuideDetails,
      pickUpTime: booking.pickUpTime,
      pickUpTimeHour: booking.pickUpTimeHour,
      duration: booking.hourlyPackage,
      pax: booking.pax,
      mode: currentMode,
      priceBreakdown: priceBreakdown
    };
  }, [guides, currentMode]);

  const handleBookNow = useCallback((sectionsToBook = formSections) => {
    if (!validateBookings(sectionsToBook)) {
      return;
    }
    
    // Create bookings data structure
    const bookingsData = {
      type: 'guide',
      bookings: sectionsToBook.map((section, index) => {
        const summaryData = getBookingSummary(section);
        const selectedGuideDetails = guides.find(g => g.id === section.guide) || {};
        
        return {
          id: `guide-${Date.now()}-${index}`,
          guideId: section.guide,
          guideName: selectedGuideDetails.guide_name || 'Guide',
          city: selectedGuideDetails.city || searchParams?.location?.city || '',
          country: selectedGuideDetails.country || searchParams?.location?.country || '',
          pickUpTime: section.pickUpTime,
          pickUpTimeHour: section.pickUpTimeHour,
          duration: section.hourlyPackage,
          priceBreakdown: section.priceBreakdown,
          pax: section.pax,
          image: selectedGuideDetails.image || '/placeholder-guide.jpg',
          mode: currentMode,
          languages: selectedGuideDetails.languages || [],
          experience: selectedGuideDetails.experience_years || 'Not specified'
        };
      })
    };
    
    console.log("Guide bookings data:", bookingsData);
    
    // Dispatch action to store in tour packages Redux slice
    dispatch(addGuideBookings(bookingsData));
    
    setBookingSuccess(true);
    
    // Reset form after successful booking
    setTimeout(() => {
      setBookingSuccess(false);
    }, 5000);
  }, [formSections, guides, validateBookings, getBookingSummary, dispatch, currentMode, searchParams]);

  if (!guides || guides.length === 0) {
    return (
      <Container>
        <Typography variant="h6" sx={{ textAlign: 'center', my: 4 }}>
          Please search for guides first
        </Typography>
      </Container>
    );
  }

  return (
    <Container maxWidth="xl">
      <Typography variant="h5" gutterBottom sx={{ mb: 4 }}>
        Guide Booking
      </Typography>
      
      {validationError && (
        <Alert severity="error" sx={{ mb: 2 }}>
          {validationError}
        </Alert>
      )}
      
      {bookingSuccess && (
        <Alert severity="success" sx={{ mb: 2 }}>
          Guide booking information saved successfully!
        </Alert>
      )}
      
      <Stack spacing={4}>
        {formSections.map((section, sectionIndex) => (
          <Paper key={sectionIndex} elevation={2} sx={{ p: 3 }}>
            <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 2 }}>
              <Typography variant="h6">Booking #{sectionIndex + 1}</Typography>
              {sectionIndex > 0 && (
                <IconButton 
                  color="error" 
                  onClick={() => handleRemoveSection(sectionIndex)}
                  size="small"
                >
                  <DeleteIcon />
                </IconButton>
              )}
            </Box>

            <Box sx={{ display: 'flex', gap: 2, flexWrap: 'nowrap' }}>
              <Box sx={{ flex: '0 0 25%' }}>
                <GuideListing 
                  value={section.guide}
                  onChange={(field, value) => handleFieldChange(sectionIndex, field, value)}
                  disabled={status === 'loading'}
                />
              </Box> 

              <Tooltip 
                title={!section.guide ? "Please select a guide first" : ""}
                placement="top"
                arrow
              >
                <Box sx={{ 
                  flex: '1', 
                  display: 'flex', 
                  gap: 2,
                  opacity: section.guide ? 1 : 0.5,
                  pointerEvents: section.guide ? 'auto' : 'none',
                  cursor: section.guide ? 'auto' : 'not-allowed'
                }}>
                  <Box sx={{ flex: '1' }}>
                    <PassengerSelection
                      value={section.pax}
                      onChange={(value) => handlePaxChange(sectionIndex, value)}
                      disabled={!section.guide || status === 'loading'}
                    />
                  </Box>

                  <Box sx={{ flex: '1' }}>
                    <TimeSelection
                      value={section.pickUpTime}
                      onChange={(e) => handleFieldChange(sectionIndex, 'pickUpTime', e)}
                      disabled={!section.guide || status === 'loading'}
                    />
                  </Box>

                  <Box sx={{ flex: '1' }}>
                    <PackageSelection
                      value={section.hourlyPackage}
                      onChange={(e) => handleFieldChange(sectionIndex, 'hourlyPackage', e)}
                      disabled={!section.guide || !section.pickUpTime || status === 'loading'}
                      pickUpTime={section.pickUpTime ? { value: section.pickUpTime, hourValue: section.pickUpTimeHour } : null}
                    />
                  </Box>
                </Box>
              </Tooltip>
            </Box>

            <Box sx={{ mt: 2, display: 'flex', gap: 2 }}>
              <Tooltip 
                title={!section.guide ? "Please select a guide first" : ""}
                placement="top"
                arrow
              >
                <span style={{ width: '100%' }}>
                  <Button
                    variant="outlined"
                    fullWidth
                    size="large"
                    onClick={() => handleOpenModal(sectionIndex)}
                    disabled={!section.guide}
                    sx={{ height: 48 }}
                  >
                    View Summary
                  </Button>
                </span>
              </Tooltip>
            </Box>
          </Paper>
        ))}

        <Box sx={{ mt: 2 }}>
          <Button
            variant="contained"
            fullWidth
            size="large"
            onClick={handleAddMore}
            sx={{ height: 48 }}
          >
            Add More Booking
          </Button>
        </Box>
      </Stack>

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