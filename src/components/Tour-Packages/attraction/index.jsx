import React, { useState, useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
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
import ConfirmationNumberIcon from '@mui/icons-material/ConfirmationNumber';
import AttractionsIcon from '@mui/icons-material/Attractions';
import TourIcon from '@mui/icons-material/Tour';
import { selectAttractions } from '../../../slice/attractions/attractionSlice';
import AttractionListing from './AttractionListing';
import PaxSelector from './PaxSelector';
import TimeSlotSelector from './TimeSlotSelector';
import TicketTypeSelector from './TicketTypeSelector';
import BookingSummaryModal from './BookingSummaryModal';

const initialFormState = {
  attraction: '',
  pax: {
    Adults: 0,
    Children: 0,
    Seniors: 0
  },
  timeSlot: '',
  ticketType: ''
};

export default function AttractionComponent() {
  const theme = useTheme();
  const dispatch = useDispatch();
  const attractions = useSelector(selectAttractions);
  const searchParams = useSelector((state) => state.attractions.searchParams);
  const attractionDetails = useSelector((state) => state.attractions.attractionDetails);
  const currentMode = useSelector((state) => state.common.bookingMode) || 'dmc';
  const [formSections, setFormSections] = useState([{ ...initialFormState }]);
  const [openModal, setOpenModal] = useState(false);
  const [selectedSectionIndex, setSelectedSectionIndex] = useState(null);
  const [validationError, setValidationError] = useState(false);
  const [bookingSuccess, setBookingSuccess] = useState(false);
  const [expandedSections, setExpandedSections] = useState([0]);

  useEffect(() => {
    console.log('Attractions Data:', attractions);
  }, [attractions]);

  const handleAddMore = () => {
    const newIndex = formSections.length;
    setFormSections([...formSections, { ...initialFormState }]);
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
    
    if (field === 'pax') {
      const currentPax = newFormSections[sectionIndex].pax;
      if (
        currentPax.Adults !== value.Adults ||
        currentPax.Children !== value.Children ||
        currentPax.Seniors !== value.Seniors
      ) {
        newFormSections[sectionIndex] = {
          ...newFormSections[sectionIndex],
          pax: {
            Adults: value.Adults || 0,
            Children: value.Children || 0,
            Seniors: value.Seniors || 0
          }
        };
        setFormSections(newFormSections);
      }
    } else if (field === 'attraction') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        [field]: value,
        timeSlot: ''
      };
      setFormSections(newFormSections);
    } else if (field === 'ticketType') {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        ticketType: value.ticketId,
        priceType: value.priceType
      };
      setFormSections(newFormSections);
    } else {
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        [field]: value
      };
      setFormSections(newFormSections);
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

  const getBookingSummary = (booking) => {
    console.log('Getting summary for booking:', booking);
    
    const selectedAttraction = attractions.find(a => a.id === booking.attraction);
    console.log('Selected attraction:', selectedAttraction);
    
    const ticketDetails = attractionDetails?.ticket_prices?.find(
      ticket => ticket.ticket_id === booking.ticketType
    );
    console.log('Ticket details:', ticketDetails);

    let adultPrice, childPrice, seniorPrice;
    const isNRI = booking.priceType === 'nri';
    
    if (currentMode === 'dmc') {
      if (isNRI) {
        adultPrice = parseFloat(ticketDetails?.dmc_adult_price_nri) || 0;
        childPrice = parseFloat(ticketDetails?.dmc_child_price_nri) || 0;
        seniorPrice = parseFloat(ticketDetails?.dmc_senior_price_nri) || 0;
      } else {
        adultPrice = parseFloat(ticketDetails?.dmc_adult_price) || 0;
        childPrice = parseFloat(ticketDetails?.dmc_child_price) || 0;
        seniorPrice = parseFloat(ticketDetails?.dmc_senior_price) || 0;
      }
    } else {
      adultPrice = parseFloat(selectedAttraction?.travClicks_adult_price) || 0;
      childPrice = parseFloat(selectedAttraction?.travClicks_child_price) || 0;
      seniorPrice = parseFloat(selectedAttraction?.travClicks_senior_price) || 0;
    }
    
    const formatOpeningHours = () => {
      const times = [];
      if (selectedAttraction?.morning_opening === 1) times.push("Morning");
      if (selectedAttraction?.afternoon_opening === 1) times.push("Afternoon");
      if (selectedAttraction?.evening_opening === 1) times.push("Evening");
      if (selectedAttraction?.night_opening === 1) times.push("Night");
      return times.join(", ") || "Not specified";
    };

    const summaryData = {
      attraction: selectedAttraction?.attraction_name || 'Not selected',
      location: selectedAttraction?.city || 'Not specified',
      country: selectedAttraction?.country || 'Not specified',
      city: selectedAttraction?.city || 'Not specified',
      image: selectedAttraction?.image || '/placeholder-image.jpg',
      description: selectedAttraction?.description || 'No description available',
      pax: booking.pax || { Adults: 0, Children: 0, Seniors: 0 },
      timeSlot: booking.timeSlot || 'Not selected',
      ticketType: ticketDetails?.ticket_name || 'Not selected',
      ticketDescription: ticketDetails?.description || 'No description available',
      adultPrice,
      childPrice,
      seniorPrice,
      openingHours: formatOpeningHours(),
      terms: selectedAttraction?.terms || ticketDetails?.terms || 'No terms and conditions specified',
      remarks: selectedAttraction?.remarks || ticketDetails?.remarks || 'No additional remarks',
      mode: currentMode,
      address: selectedAttraction?.address || 'Address not specified',
      category: selectedAttraction?.category || 'Category not specified',
      duration: selectedAttraction?.duration || 'Duration not specified',
      cancellation_policy: selectedAttraction?.cancellation_policy || ticketDetails?.cancellation_policy || 'Cancellation policy not specified',
      inclusions: selectedAttraction?.inclusions || ticketDetails?.inclusions || 'Inclusions not specified',
      exclusions: selectedAttraction?.exclusions || ticketDetails?.exclusions || 'Exclusions not specified',
      tax_percentage: selectedAttraction?.tax_percentage || ticketDetails?.tax_percentage,
      tax_amount: selectedAttraction?.tax_amount || ticketDetails?.tax_amount,
      currency: selectedAttraction?.currency || 'SGD',
      priceType: booking.priceType || 'residential',
    };

    console.log('Summary data:', summaryData);
    return summaryData;
  };

  const validateBookings = () => {
    if (formSections.length === 0) {
      setValidationError("Please add at least one attraction.");
      return false;
    }
    
    for (let i = 0; i < formSections.length; i++) {
      const section = formSections[i];
      
      if (!section.attraction) {
        setValidationError(`Booking #${i + 1}: Please select an attraction.`);
        return false;
      }
      
      if (!section.timeSlot) {
        setValidationError(`Booking #${i + 1}: Please select a time slot.`);
        return false;
      }
      
      if (!section.ticketType) {
        setValidationError(`Booking #${i + 1}: Please select a ticket type.`);
        return false;
      }
      
      const totalPax = section.pax.Adults + section.pax.Children + section.pax.Seniors;
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
      type: 'attraction',
      bookings: formSections.map((section, index) => {
        const summaryData = getBookingSummary(section);
        
        const adultTotal = summaryData.adultPrice * section.pax.Adults;
        const childTotal = summaryData.childPrice * section.pax.Children;
        const seniorTotal = summaryData.seniorPrice * section.pax.Seniors;
        const totalPrice = adultTotal + childTotal + seniorTotal;
        
        return {
          id: `attraction-${Date.now()}-${index}`,
          attractionId: section.attraction,
          attractionName: summaryData.attraction,
          location: summaryData.location,
          city: summaryData.city,
          country: summaryData.country,
          timeSlot: section.timeSlot,
          ticketType: section.ticketType,
          ticketName: summaryData.ticketType,
          priceType: section.priceType || 'residential',
          pax: section.pax,
          prices: {
            adult: summaryData.adultPrice,
            child: summaryData.childPrice,
            senior: summaryData.seniorPrice,
          },
          totalPrice: totalPrice,
          image: summaryData.image,
          mode: currentMode,
        };
      })
    };
    
    console.log("Attraction bookings data:", bookingsData);
    
    setBookingSuccess(true);
    
    setTimeout(() => {
      setBookingSuccess(false);
    }, 5000);
  };

  const getCompletionStatus = (section) => {
    const steps = [
      section.attraction,
      section.pax.Adults + section.pax.Children + section.pax.Seniors > 0,
      section.timeSlot,
      section.ticketType
    ];
    return steps.filter(Boolean).length;
  };

  const getSelectedAttraction = (attractionId) => {
    return attractions.find(a => a.id === attractionId);
  };

  if (!attractions || attractions.length === 0) {
    return (
      <Container maxWidth="xl">
        <Card 
          elevation={3}
          sx={{
            borderRadius: 3,
            background: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
            color: 'white',
            mb: 2,
            mx: 'auto',
          }}
        >
          <CardContent sx={{ py: 2, textAlign: 'center' }}>
            <AttractionsIcon sx={{ fontSize: 64, color: '#FFD700', mb: 2 }} />
            <Typography variant="h6" color="white">
              Please search for attractions first
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
          background: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)',
          color: 'white',
          mb: 3,
          mx: 'auto',
        }}
      >
        <CardContent sx={{ py: 1}}>
          <Box display="flex" alignItems="center" justifyContent="space-between">
            <Box display="flex" alignItems="center">
              <TourIcon sx={{ mr: 2, fontSize: 32, color: '#FFD700' }} />
              <Box>
                <Typography variant="h5" fontWeight="600" sx={{ color: 'white' }}>
                  Book Attraction Tickets
                </Typography>
                <Typography variant="body2" sx={{ color: 'rgba(255, 255, 255, 0.8)' }}>
                  Select attractions and configure your perfect tour package
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
              Booking information saved successfully to the tour package data!
            </Alert>
          )}
        </Box>
      </Fade>
      
      <Grid container spacing={2}>
        {formSections.map((section, sectionIndex) => {
          const selectedAttraction = getSelectedAttraction(section.attraction);
          const completionStatus = getCompletionStatus(section);
          const isExpanded = expandedSections.includes(sectionIndex);
          
          return (
            <Grid item xs={12} key={sectionIndex}>
              <Card 
                elevation={2}
                sx={{ 
                  borderRadius: 3,
                  border: `2px solid ${alpha('#ff6b6b', 0.2)}`,
                  transition: 'all 0.3s ease',
                  '&:hover': {
                    boxShadow: `0 8px 24px ${alpha('#ff6b6b', 0.15)}`,
                    transform: 'translateY(-2px)',
                  }
                }}
              >
                <CardContent sx={{ p: 0 }}>
                  {/* Header */}
                  <Box sx={{ 
                    p: 2,
                    bgcolor: alpha('#ff6b6b', 0.05),
                    borderBottom: `1px solid ${alpha('#ff6b6b', 0.1)}`,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between'
                  }}>
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                      <Chip 
                        label={`Booking ${sectionIndex + 1}`}
                        sx={{ 
                          bgcolor: '#ff6b6b',
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
                      {selectedAttraction && (
                        <Chip 
                          icon={<LocationOnIcon sx={{ fontSize: 16 }} />}
                          label={selectedAttraction.city}
                          size="small"
                          variant="outlined"
                          sx={{ 
                            borderColor: '#ff6b6b',
                            color: '#ff6b6b'
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
                            bgcolor: alpha('#ff6b6b', 0.1),
                            '&:hover': { bgcolor: alpha('#ff6b6b', 0.2) }
                          }}
                        >
                          <i className={`icon-chevron-${isExpanded ? 'up' : 'down'}`} />
                        </IconButton>
                      </Tooltip>
                      
                      {/* {section.attraction && (
                        <Tooltip title="View Summary">
                          <IconButton 
                            size="small" 
                            onClick={() => handleOpenModal(sectionIndex)}
                            sx={{ 
                              bgcolor: alpha(theme.palette.info.main, 0.1),
                              '&:hover': { bgcolor: alpha(theme.palette.info.main, 0.2) }
                            }}
                          >
                            <VisibilityIcon sx={{ fontSize: 18 }} />
                          </IconButton>
                        </Tooltip>
                      )} */}
                      {section.attraction && (
                        <Button
                              variant="outlined"
                              size="large"
                              onClick={() => handleOpenModal(sectionIndex)}
                              disabled={!section.attraction}
                              startIcon={<VisibilityIcon />}
                              sx={{
                                borderRadius: 2,
                                px: 4,
                                py: 1,
                                fontSize: '0.875rem',
                                fontWeight: 600,
                                textTransform: 'none',
                                borderColor: '#ff6b6b',
                                color: '#ff6b6b',
                                '&:hover': {
                                  borderColor: '#ee5a24',
                                  bgcolor: alpha('#ff6b6b', 0.05),
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
                  {!isExpanded && selectedAttraction && (
                    <Box sx={{ p: 2 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                        <Box 
                          component="img"
                          src={selectedAttraction.image}
                          alt={selectedAttraction.attraction_name}
                          sx={{ 
                            width: 60, 
                            height: 60, 
                            borderRadius: 2,
                            objectFit: 'cover',
                            border: `2px solid ${alpha('#ff6b6b', 0.2)}`
                          }}
                        />
                        <Box sx={{ flex: 1 }}>
                          <Typography variant="h6" fontWeight={600} sx={{ mb: 0.5 }}>
                            {selectedAttraction.attraction_name}
                          </Typography>
                          <Box sx={{ display: 'flex', gap: 2, flexWrap: 'wrap' }}>
                            {section.pax.Adults + section.pax.Children + section.pax.Seniors > 0 && (
                              <Chip 
                                icon={<PeopleIcon sx={{ fontSize: 16 }} />}
                                label={`${section.pax.Adults + section.pax.Children + section.pax.Seniors} Pax`}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#ff6b6b',
                                  color: '#ff6b6b'
                                }}
                              />
                            )}
                            {section.timeSlot && (
                              <Chip 
                                icon={<AccessTimeIcon sx={{ fontSize: 16 }} />}
                                label={section.timeSlot}
                                size="small"
                                variant="outlined"
                                sx={{ 
                                  borderColor: '#ff6b6b',
                                  color: '#ff6b6b'
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
                        {/* Attraction Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <AttractionsIcon sx={{ mr: 1, color: '#ff6b6b', fontSize: 20 }} />
                              <Typography variant="subtitle2" fontWeight="600" color="text.primary">
                                Select Attraction
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <AttractionListing
                                attractions={attractions}
                                selectedAttraction={section.attraction}
                                onAttractionChange={(value) => handleInputChange(sectionIndex, 'attraction', value)}
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
                                color={!section.attraction ? "text.disabled" : "text.primary"}
                              >
                                Select Guests
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <PaxSelector
                                initialAdults={searchParams?.adults || 1}
                                initialChildren={searchParams?.children || 0}
                                onPaxChange={(value) => handleInputChange(sectionIndex, 'pax', value)}
                                disabled={!section.attraction}
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
                                color={!section.attraction ? "text.disabled" : "text.primary"}
                              >
                                Select Time
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <TimeSlotSelector
                                selectedTimeSlot={section.timeSlot}
                                onTimeSlotChange={(value) => handleInputChange(sectionIndex, 'timeSlot', value)}
                                attraction={section.attraction}
                                disabled={!section.attraction}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* Ticket Selection */}
                        <Grid item xs={12} md={3}>
                          <Box sx={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
                            <Box display="flex" alignItems="center" mb={1} sx={{ height: '32px' }}>
                              <ConfirmationNumberIcon sx={{ mr: 1, color: '#9c27b0', fontSize: 20 }} />
                              <Typography 
                                variant="subtitle2" 
                                fontWeight="600"
                                color={!section.attraction ? "text.disabled" : "text.primary"}
                              >
                                Select Ticket
                              </Typography>
                            </Box>
                            <Box sx={{ minHeight: '48px', display: 'flex', alignItems: 'center' }}>
                              <TicketTypeSelector
                                selectedTicketType={section.ticketType}
                                onTicketTypeChange={(value) => handleInputChange(sectionIndex, 'ticketType', value)}
                                disabled={!section.attraction}
                                sectionIndex={sectionIndex}
                                formSections={formSections}
                              />
                            </Box>
                          </Box>
                        </Grid>

                        {/* View Summary Button */}
                        {/* <Grid item xs={12} sx={{ mt: 2 }}>
                          <Box display="flex" justifyContent="center">
                            <Button
                              variant="outlined"
                              size="large"
                              onClick={() => handleOpenModal(sectionIndex)}
                              disabled={!section.attraction}
                              startIcon={<VisibilityIcon />}
                              sx={{
                                borderRadius: 2,
                                px: 4,
                                py: 1,
                                fontSize: '0.875rem',
                                fontWeight: 600,
                                textTransform: 'none',
                                borderColor: '#ff6b6b',
                                color: '#ff6b6b',
                                '&:hover': {
                                  borderColor: '#ee5a24',
                                  bgcolor: alpha('#ff6b6b', 0.05),
                                  transform: 'translateY(-1px)',
                                },
                                transition: 'all 0.3s ease',
                              }}
                            >
                              View Summary
                            </Button>
                          </Box>
                        </Grid> */}
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
              border: `2px dashed ${alpha('#ff6b6b', 0.4)}`,
              bgcolor: alpha('#ff6b6b', 0.02),
              cursor: 'pointer',
              transition: 'all 0.3s ease',
              '&:hover': {
                bgcolor: alpha('#ff6b6b', 0.05),
                borderColor: '#ff6b6b',
                transform: 'translateY(-1px)',
              }
            }}
            onClick={handleAddMore}
          >
            <CardContent sx={{ py: 3 }}>
              <Box sx={{ 
                display: 'flex', 
                alignItems: 'center', 
                justifyContent: 'center',
                gap: 2
              }}>
                <AddIcon sx={{ fontSize: 32, color: '#ff6b6b' }} />
                <Typography variant="h6" color="#ff6b6b" fontWeight={600}>
                  Add More
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      <BookingSummaryModal
        open={openModal}
        onClose={handleCloseModal}
        bookingData={selectedSectionIndex !== null ? getBookingSummary(formSections[selectedSectionIndex]) : null}
        bookingIndex={selectedSectionIndex}
      />
    </Container>
  );
} 