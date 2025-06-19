import React, { useState, useRef, useEffect } from 'react';
import { 
  Grid, 
  Modal,
  Box,
  Typography,
  IconButton,
  Radio,
  RadioGroup,
  FormControlLabel,
  Button,
  Tooltip,
  Alert
} from '@mui/material';
import { useSelector, useDispatch } from 'react-redux';
import { addAttractionBookings } from '../../../slice/tour-packages/tourPackageSlice';
import ConfirmationNumberIcon from '@mui/icons-material/ConfirmationNumber';
import CloseIcon from '@mui/icons-material/Close';
import DescriptionIcon from '@mui/icons-material/Description';
import InfoIcon from '@mui/icons-material/Info';
import GavelIcon from '@mui/icons-material/Gavel';
import CommentIcon from '@mui/icons-material/Comment';
import ErrorOutlineIcon from '@mui/icons-material/ErrorOutline';

const TicketTypeSelector = ({ selectedTicketType, onTicketTypeChange, disabled, sectionIndex, formSections }) => {
  const dispatch = useDispatch();
  // States
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [modalOpen, setModalOpen] = useState(false);
  const [selectedTicket, setSelectedTicket] = useState(null);
  const [textModalContent, setTextModalContent] = useState({ title: "", content: "", isOpen: false });
  const [nriStatus, setNriStatus] = useState("residential"); // residential or nri
  const [bookingSuccess, setBookingSuccess] = useState(false);
  const [validationError, setValidationError] = useState(null);
  const dropdownRef = useRef(null);

  // Get data from Redux store
  const attractions = useSelector((state) => state.attractions.attractions);
  const attractionDetails = useSelector((state) => state.attractions.attractionDetails);
  const currentMode = useSelector((state) => state.common.bookingMode) || 'dmc';
  const currencyCode = useSelector((state) => state.auth.currencyCode) || "SGD";
  const exchangeRate = useSelector((state) => state.auth.exchangeRate) || 1;
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate) || 1;

  // Get tickets from attraction details
  const tickets = attractionDetails?.ticket_prices || [];

  // Handle click outside dropdown
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsDropdownOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  // Format price in different currencies
  const formatPrice = (price, type) => {
    if (!price) return "0.00";

    switch (type) {
      case "main":
        return `${currencyCode} ${Math.ceil(price * exchangeRate)}`;
      case "usd":
        return `USD ${Math.ceil(price * usdExchangeRate)}`;
      case "sgd":
        return `SGD ${Math.ceil(price)}`;
      default:
        return `SGD ${Math.ceil(price)}`;
    }
  };

  // Get price based on price type (residential or NRI)
  const getPrice = (priceField) => {
    if (!selectedTicket) return 0;
    
    if (nriStatus === "nri") {
      switch (priceField) {
        case "adult":
          return parseFloat(selectedTicket.dmc_adult_price_nri) || 0;
        case "child":
          return parseFloat(selectedTicket.dmc_child_price_nri) || 0;
        case "senior":
          return parseFloat(selectedTicket.dmc_senior_price_nri) || 0;
        default:
          return 0;
      }
    }
    
    switch (priceField) {
      case "adult":
        return parseFloat(selectedTicket.dmc_adult_price) || 0;
      case "child":
        return parseFloat(selectedTicket.dmc_child_price) || 0;
      case "senior":
        return parseFloat(selectedTicket.dmc_senior_price) || 0;
      default:
        return 0;
    }
  };

  // Strip HTML tags from text
  const stripPTags = (text) => {
    if (!text) return "";
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = text;
    let cleanedText = tempDiv.textContent || tempDiv.innerText;
    cleanedText = cleanedText
      .replace(/&nbsp;/g, ' ')
      .replace(/&quot;/g, '"')
      .replace(/&amp;/g, '&')
      .replace(/&lt;/g, '<')
      .replace(/&gt;/g, '>')
      .replace(/&#39;/g, "'");
    return cleanedText.replace(/\s+/g, ' ').trim();
  };

  // Handle description truncation and modal
  const renderDescription = (description, type = "description") => {
    if (!description) return null;
    
    const words = description.split(/\s+/);
    const wordCount = words.length;
    const wordLimit = 10;
    
    const getIcon = () => {
      switch (type) {
        case "description":
          return <i className="icon-info-circle" style={{ fontSize: '24px', marginRight: '10px', color: 'white' }} />;
        case "remarks":
          return <i className="icon-notification" style={{ fontSize: '24px', marginRight: '10px', color: 'white' }} />;
        case "terms":
          return <i className="icon-shield" style={{ fontSize: '24px', marginRight: '10px', color: 'white' }} />;
        default:
          return null;
      }
    };

    const getTitle = () => {
      switch (type) {
        case "description":
          return "Description";
        case "remarks":
          return "Remarks";
        case "terms":
          return "Terms & Conditions";
        default:
          return "";
      }
    };

    const openTextModal = () => {
      setTextModalContent({
        title: getTitle(),
        content: description,
        isOpen: true
      });
    };

    if (wordCount <= wordLimit) {
      return (
        <Box sx={{ 
          mt: 2, 
          p: 1.5, 
          bgcolor: 'primary.main', 
          borderRadius: 1,
          color: 'white'
        }}>
          <Box sx={{ display: 'flex', mb: 0.5, alignItems: 'center' }}>
            {getIcon()}
            <Typography fontWeight={500} color="white">{getTitle()}</Typography>
          </Box>
          <Typography variant="body2" color="white">{description}</Typography>
        </Box>
      );
    }

    const truncatedText = words.slice(0, wordLimit).join(" ");
    
    return (
      <Box sx={{ 
        mt: 2, 
        p: 1.5, 
        bgcolor: 'primary.main', 
        borderRadius: 1,
        color: 'white'
      }}>
        <Box sx={{ display: 'flex', mb: 0.5, alignItems: 'center' }}>
          {getIcon()}
          <Typography fontWeight={500} color="white">{getTitle()}</Typography>
        </Box>
        <Typography variant="body2" color="white">
          {truncatedText + "..."}
        </Typography>
        <Button 
          onClick={openTextModal}
          sx={{ 
            mt: 0.5,
            p: 0,
            minWidth: 'auto',
            textTransform: 'none',
            textDecoration: 'underline',
            color: 'white'
          }}
        >
          Show more
        </Button>
      </Box>
    );
  };

  const handleOpenModal = (ticket) => {
    setSelectedTicket(ticket);
    setModalOpen(true);
    setIsDropdownOpen(false);
    
    // Check time slot immediately when modal opens
    const section = formSections[sectionIndex];
    if (!section?.timeSlot || section.timeSlot.trim() === '') {
      setValidationError("⚠️ Time slot selection is required! Please select a time slot before booking.");
    } else {
      setValidationError(null);
    }
  };
  
  const validateBooking = () => {
    if (!formSections || formSections.length === 0) {
      setValidationError("No booking information available.");
      return false;
    }

    const section = formSections[sectionIndex];
    if (!section) {
      setValidationError("Booking section not found.");
      return false;
    }

    // Check for attraction selection
    if (!section.attraction) {
      setValidationError("Please select an attraction first.");
      return false;
    }

    // Enhanced time slot validation - make it more prominent
    if (!section.timeSlot || section.timeSlot.trim() === '') {
      setValidationError("⚠️ Time slot selection is required! Please select a time slot before booking.");
      return false;
    }

    // Check if at least one person is selected
    const totalPax = section.pax.Adults + section.pax.Children + section.pax.Seniors;
    if (totalPax <= 0) {
      setValidationError("Please select at least one person.");
      return false;
    }

    setValidationError(null);
    return true;
  };
  
  const getBookingSummary = (booking) => {
    const selectedAttraction = attractions.find(a => a.id === booking.attraction);
    
    const ticketDetails = attractionDetails?.ticket_prices?.find(
      ticket => ticket.ticket_id === booking.ticketType
    );

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

    return {
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
  };
  
  const handleBookNow = () => {
    if (!validateBooking()) {
      return;
    }
    
    // Extra check specifically for time slot
    const section = formSections[sectionIndex];
    if (!section.timeSlot || section.timeSlot.trim() === '') {
      setValidationError("⚠️ Time slot selection is required! Please select a time slot before booking.");
      return;
    }
    
    // First update the ticket selection
    onTicketTypeChange({
      ticketId: selectedTicket?.ticket_id,
      priceType: nriStatus
    });
    
    // Then create bookings data structure for all sections
    const bookingsData = {
      type: 'attraction',
      bookings: formSections.map((section, index) => {
        // For the current section, use the selected ticket and price type
        const updatedSection = index === sectionIndex 
          ? { ...section, ticketType: selectedTicket?.ticket_id, priceType: nriStatus }
          : section;
          
        const summaryData = getBookingSummary(updatedSection);
        
        // Calculate total price
        const adultTotal = summaryData.adultPrice * updatedSection.pax.Adults;
        const childTotal = summaryData.childPrice * updatedSection.pax.Children;
        const seniorTotal = summaryData.seniorPrice * updatedSection.pax.Seniors;
        const totalPrice = adultTotal + childTotal + seniorTotal;
        
        return {
          id: `attraction-${Date.now()}-${index}`,
          attractionId: updatedSection.attraction,
          attractionName: summaryData.attraction,
          location: summaryData.location,
          city: summaryData.city,
          country: summaryData.country,
          timeSlot: updatedSection.timeSlot,
          ticketType: updatedSection.ticketType,
          ticketName: summaryData.ticketType,
          priceType: updatedSection.priceType || 'residential',
          pax: updatedSection.pax,
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
    setModalOpen(false);
    
    // Reset success message after a delay
    setTimeout(() => {
      setBookingSuccess(false);
    }, 5000);
  };

  const handleConfirmSelection = () => {
    const section = formSections[sectionIndex];
    
    // Primary time slot check - show a specific message
    if (!section?.timeSlot || section.timeSlot.trim() === '') {
      setValidationError("⚠️ Please select a time slot first! The time slot is required to complete the booking.");
      return;
    }
    
    handleBookNow();
  };

  return (
    <Grid item xs={12} sm={6} md={3}>
      <div ref={dropdownRef}>
        <Box
          onClick={() => !disabled && setIsDropdownOpen(!isDropdownOpen)}
          sx={{
            display: 'flex',
            alignItems: 'center',
            bgcolor: 'background.paper',
            border: '1px solid',
            borderColor: 'divider',
            borderRadius: 1,
            p: 1.5,
            cursor: disabled ? 'not-allowed' : 'pointer',
            opacity: disabled ? 0.7 : 1,
            transition: 'all 0.2s',
            boxShadow: isDropdownOpen ? '0 4px 12px rgba(0,0,0,0.1)' : 'none',
          }}
        >
          <ConfirmationNumberIcon sx={{ mr: 1, color: 'primary.main' }} />
          <Typography fontWeight={500}>
            {tickets.find(t => t.ticket_id === selectedTicketType)?.ticket_name || "Select Ticket"}
          </Typography>
          <Box sx={{ ml: 'auto' }}>
            <i className={`icon-chevron-${isDropdownOpen ? 'up' : 'down'}`} />
          </Box>
        </Box>

        {isDropdownOpen && tickets.length > 0 && (
          <Box
            sx={{
              position: 'absolute',
              zIndex: 1000,
              mt: 0.25,
              boxShadow: '0 4px 16px rgba(0,0,0,0.1)',
              borderRadius: 1,
              bgcolor: 'background.paper',
              width: 'calc(100% - 32px)',
              maxHeight: '250px',
              overflowY: 'auto',
            }}
          >
            {tickets.map((ticket, index) => (
              <Box
                key={`${ticket.ticket_id}-${nriStatus}`}
                onClick={() => handleOpenModal(ticket)}
                sx={{
                  p: 1.5,
                  cursor: 'pointer',
                  bgcolor: selectedTicketType === ticket.ticket_id ? '#FFE4E6' : 'background.paper',
                  color: selectedTicketType === ticket.ticket_id ? 'text.primary' : 'inherit',
                  borderBottom: index < tickets.length - 1 ? '1px solid' : 'none',
                  borderColor: 'divider',
                  '&:hover': {
                    bgcolor: selectedTicketType === ticket.ticket_id ? '#FFE4E6' : '#f5f5f5',
                  },
                }}
              >
                <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                  <Box sx={{ display: 'flex', alignItems: 'center', flex: 1 }}>
                    <ConfirmationNumberIcon sx={{ 
                      mr: 1, 
                      color: selectedTicketType === ticket.ticket_id ? 'primary.main' : 'primary.main' 
                    }} />
                    <Box>
                      <Typography 
                        fontWeight={selectedTicketType === ticket.ticket_id ? 600 : 500}
                        color="text.primary"
                      >
                        {ticket.ticket_name}
                      </Typography>
                      <Typography 
                        variant="body2" 
                        color="primary.main"
                        fontWeight={500}
                      >
                        Adult: {formatPrice(getPrice("adult"), "main")} |
                        Child: {formatPrice(getPrice("child"), "main")} |
                        Senior: {formatPrice(getPrice("senior"), "main")}
                      </Typography>
                    </Box>
                  </Box>
                  <Box sx={{ display: 'flex', gap: 1 }}>
                    {ticket.description && (
                      <Tooltip title="Description available">
                        <DescriptionIcon sx={{ 
                          color: 'action.active',
                          fontSize: 20 
                        }} />
                      </Tooltip>
                    )}
                    {ticket.remarks && (
                      <Tooltip title="Remarks available">
                        <CommentIcon sx={{ 
                          color: 'action.active',
                          fontSize: 20 
                        }} />
                      </Tooltip>
                    )}
                    {ticket.terms_conditions && (
                      <Tooltip title="Terms & Conditions available">
                        <GavelIcon sx={{ 
                          color: 'action.active',
                          fontSize: 20 
                        }} />
                      </Tooltip>
                    )}
                  </Box>
                </Box>
              </Box>
            ))}
          </Box>
        )}
      </div>

      {/* Ticket Details Modal */}
      <Modal
        open={modalOpen}
        onClose={() => setModalOpen(false)}
      >
        <Box sx={{
          position: 'absolute',
          top: '50%',
          left: '50%',
          transform: 'translate(-50%, -50%)',
          width: '90%',
          maxWidth: 800,
          maxHeight: '90vh',
          bgcolor: 'background.paper',
          boxShadow: 24,
          borderRadius: 2,
          overflow: 'auto',
          p: 3,
        }}>
          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
            <Typography variant="h6">
              Confirm Ticket Selection
              {nriStatus === "nri" && (
                <Typography 
                  component="span" 
                  sx={{ 
                    ml: 1,
                    px: 1,
                    py: 0.5,
                    bgcolor: 'primary.main',
                    color: 'white',
                    borderRadius: 1,
                    fontSize: '0.875rem'
                  }}
                >
                  Foreigner Pricing
                </Typography>
              )}
            </Typography>
            <IconButton onClick={() => setModalOpen(false)} size="small">
              <CloseIcon />
            </IconButton>
          </Box>

          {!formSections[sectionIndex]?.timeSlot && (
            <Box 
              sx={{ 
                mb: 2, 
                p: 2, 
                bgcolor: 'error.light', 
                borderRadius: 1,
                display: 'flex',
                alignItems: 'center',
                gap: 1
              }}
            >
              <ErrorOutlineIcon color="error" />
              <Typography color="error.dark" fontWeight="500">
                Time slot is not selected! Please close this window and select a time slot first.
              </Typography>
            </Box>
          )}

          {validationError && (
            <Alert severity="error" sx={{ mb: 2 }}>
              {validationError}
            </Alert>
          )}

          {selectedTicket && (
            <>
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }}>
                <ConfirmationNumberIcon sx={{ fontSize: 36, color: 'primary.main', mr: 2 }} />
                <Typography variant="h5" fontWeight={500}>
                  {selectedTicket.ticket_name}
                </Typography>
              </Box>

              <Box sx={{ display: 'flex', gap: 2 }}>
                <Box flex={1}>
                  {renderDescription(stripPTags(selectedTicket.description), "description")}
                </Box>
                <Box flex={1}>
                  {renderDescription(stripPTags(selectedTicket.remarks), "remarks")}
                </Box>
                <Box flex={1}>
                  {renderDescription(stripPTags(selectedTicket.terms_conditions), "terms")}
                </Box>
              </Box>

              <Box sx={{ 
                my: 2, 
                p: 2, 
                bgcolor: 'primary.main', 
                borderRadius: 1,
                display: 'flex',
                alignItems: 'center',
                color: 'white'
              }}>
                <Typography fontWeight={500} sx={{ mr: 2, color: 'white' }}>Select Price Type:</Typography>
                <RadioGroup
                  row
                  value={nriStatus}
                  onChange={(e) => setNriStatus(e.target.value)}
                >
                  <FormControlLabel 
                    value="residential" 
                    control={<Radio sx={{ 
                      color: 'white',
                      '&.Mui-checked': {
                        color: 'white',
                      },
                    }} />} 
                    label={<Typography color="white">Local</Typography>}
                  />
                  <FormControlLabel 
                    value="nri" 
                    control={<Radio sx={{ 
                      color: 'white',
                      '&.Mui-checked': {
                        color: 'white',
                      },
                    }} />} 
                    label={<Typography color="white">Foreigner</Typography>}
                  />
                </RadioGroup>
              </Box>

              <Typography variant="h6" align="center" sx={{ mb: 2 }}>
                {nriStatus === "residential" ? "Local Prices" : "Foreigner Prices"}
              </Typography>

              <Grid container spacing={2}>
                {["adult", "child", "senior"].map((type) => (
                  <Grid item xs={12} md={4} key={type}>
                    <Box sx={{ p: 2, border: 1, borderColor: 'divider', borderRadius: 1 }}>
                      <Typography color="text.secondary" sx={{ mb: 1 }}>
                        {type.charAt(0).toUpperCase() + type.slice(1)} Price
                      </Typography>
                      <Typography variant="h6" fontWeight={500}>
                        {formatPrice(getPrice(type), "main")}
                      </Typography>
                      {currencyCode !== "USD" && (
                        <Typography variant="body2" color="text.secondary">
                          {formatPrice(getPrice(type), "usd")}
                        </Typography>
                      )}
                      {currencyCode !== "SGD" && (
                        <Typography variant="body2" color="text.secondary">
                          {formatPrice(getPrice(type), "sgd")}
                        </Typography>
                      )}
                    </Box>
                  </Grid>
                ))}
              </Grid>

              <Box sx={{ mt: 3, display: 'flex', justifyContent: 'flex-end', gap: 2 }}>
                <Button variant="outlined" onClick={() => setModalOpen(false)}>
                  Cancel
                </Button>
                <Tooltip title={!formSections[sectionIndex]?.timeSlot ? "Please select a time slot first" : ""}>
                  <span>
                    <Button 
                      variant="contained" 
                      color="success" 
                      onClick={handleConfirmSelection}
                      disabled={!formSections[sectionIndex]?.timeSlot}
                    >
                      Book Now
                    </Button>
                  </span>
                </Tooltip>
              </Box>
              
              {/* Time slot reminder */}
              <Typography 
                variant="body2" 
                color="warning.main" 
                sx={{ mt: 2, display: 'flex', alignItems: 'center', justifyContent: 'center' }}
              >
                <Box component="span" sx={{ mr: 1, fontSize: '1.2rem' }}>⚠️</Box>
                Remember: A time slot must be selected to complete the booking.
              </Typography>
            </>
          )}
        </Box>
      </Modal>

      {/* Text Content Modal */}
      <Modal
        open={textModalContent.isOpen}
        onClose={() => setTextModalContent({...textModalContent, isOpen: false})}
      >
        <Box sx={{
          position: 'absolute',
          top: '50%',
          left: '50%',
          transform: 'translate(-50%, -50%)',
          width: '90%',
          maxWidth: 800,
          bgcolor: 'background.paper',
          boxShadow: 24,
          borderRadius: 2,
          p: 3,
        }}>
          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
            <Typography variant="h6">{textModalContent.title}</Typography>
            <IconButton onClick={() => setTextModalContent({...textModalContent, isOpen: false})} size="small">
              <CloseIcon />
            </IconButton>
          </Box>
          <Typography variant="body1" sx={{ mb: 2 }}>
            {textModalContent.content}
          </Typography>
          <Box sx={{ display: 'flex', justifyContent: 'flex-end' }}>
            <Button 
              variant="contained"
              onClick={() => setTextModalContent({...textModalContent, isOpen: false})}
            >
              Close
            </Button>
          </Box>
        </Box>
      </Modal>
      
      {bookingSuccess && (
        <Alert severity="success" sx={{ mt: 2, position: 'fixed', bottom: 20, right: 20, zIndex: 1500 }}>
          Booking information saved successfully to the tour package data!
        </Alert>
      )}
    </Grid>
  );
};

export default TicketTypeSelector; 