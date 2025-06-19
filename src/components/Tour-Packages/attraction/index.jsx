import React, { useState, useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
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

  useEffect(() => {
    console.log('Attractions Data:', attractions);
  }, [attractions]);

  const handleAddMore = () => {
    setFormSections([...formSections, { ...initialFormState }]);
  };

  const handleRemoveSection = (indexToRemove) => {
    setFormSections(formSections.filter((_, index) => index !== indexToRemove));
  };

  const handleInputChange = (sectionIndex, field, value) => {
    const newFormSections = [...formSections];
    
    if (field === 'pax') {
      // Only update if values have actually changed
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
      // When attraction changes, reset the time slot but keep other fields
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        [field]: value,
        timeSlot: ''
      };
      setFormSections(newFormSections);
    } else if (field === 'ticketType') {
      // Handle new ticket type data structure
      newFormSections[sectionIndex] = {
        ...newFormSections[sectionIndex],
        ticketType: value.ticketId,
        priceType: value.priceType
      };
      setFormSections(newFormSections);
    } else {
      // For other fields (timeSlot)
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

    // Get prices based on mode and price type (residential/nri)
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
    
    // Format opening hours
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
    // Check if there's at least one booking
    if (formSections.length === 0) {
      setValidationError("Please add at least one attraction.");
      return false;
    }
    
    // Validate each booking section
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
      
      // Check if at least one person is selected
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
    
    // Create bookings data structure
    const bookingsData = {
      type: 'attraction',
      bookings: formSections.map((section, index) => {
        const summaryData = getBookingSummary(section);
        
        // Calculate total price
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
    
    // Dispatch action to store in Redux (tourPackage slice)
    dispatch(addAttractionBookings(bookingsData));
    
    setBookingSuccess(true);
    
    // Reset form after successful booking
    setTimeout(() => {
      setBookingSuccess(false);
    }, 5000);
  };

  if (!attractions || attractions.length === 0) {
    return (
      <Container>
        <Typography variant="h6" sx={{ textAlign: 'center', my: 4 }}>
          Please search for attractions first
        </Typography>
      </Container>
    );
  }

  return (
    <Container maxWidth="xl">
      <Typography variant="h5" gutterBottom sx={{ mb: 4 }}>
        Book Attraction Tickets
      </Typography>
      
      {validationError && (
        <Alert severity="error" sx={{ mb: 2 }}>
          {validationError}
        </Alert>
      )}
      
      {bookingSuccess && (
        <Alert severity="success" sx={{ mb: 2 }}>
          Booking information saved successfully to the tour package data!
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
              <Box sx={{ flex: '0 0 30%' }}>
                <AttractionListing
                  attractions={attractions}
                  selectedAttraction={section.attraction}
                  onAttractionChange={(value) => handleInputChange(sectionIndex, 'attraction', value)}
                />
              </Box>

              <Tooltip 
                title={!section.attraction ? "Please select an attraction first" : ""}
                placement="top"
                arrow
              >
                <Box sx={{ 
                  flex: '1', 
                  display: 'flex', 
                  gap: 2,
                  opacity: section.attraction ? 1 : 0.5,
                  pointerEvents: section.attraction ? 'auto' : 'none',
                  cursor: section.attraction ? 'auto' : 'not-allowed'
                }}>
                  <Box sx={{ flex: '1' }}>
                    <PaxSelector
                      initialAdults={searchParams?.adults || 1}
                      initialChildren={searchParams?.children || 0}
                      onPaxChange={(value) => handleInputChange(sectionIndex, 'pax', value)}
                      disabled={!section.attraction}
                    />
                  </Box>

                  <Box sx={{ flex: '1' }}>
                    <TimeSlotSelector
                      selectedTimeSlot={section.timeSlot}
                      onTimeSlotChange={(value) => handleInputChange(sectionIndex, 'timeSlot', value)}
                      attraction={section.attraction}
                      disabled={!section.attraction}
                    />
                  </Box>

                  <Box sx={{ flex: '1' }}>
                    <TicketTypeSelector
                      selectedTicketType={section.ticketType}
                      onTicketTypeChange={(value) => handleInputChange(sectionIndex, 'ticketType', value)}
                      disabled={!section.attraction}
                      sectionIndex={sectionIndex}
                      formSections={formSections}
                    />
                  </Box>
                </Box>
              </Tooltip>
            </Box>

            <Box sx={{ mt: 2, display: 'flex', gap: 2 }}>
              <Tooltip 
                title={!section.attraction ? "Please select an attraction first" : ""}
                placement="top"
                arrow
              >
                <span style={{ width: '100%' }}>
                  <Button
                    variant="outlined"
                    fullWidth
                    size="large"
                    onClick={() => handleOpenModal(sectionIndex)}
                    disabled={!section.attraction}
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

      {/* Booking Summary Modal */}
      <BookingSummaryModal
        open={openModal}
        onClose={handleCloseModal}
        bookingData={selectedSectionIndex !== null ? getBookingSummary(formSections[selectedSectionIndex]) : null}
        bookingIndex={selectedSectionIndex}
      />
    </Container>
  );
} 