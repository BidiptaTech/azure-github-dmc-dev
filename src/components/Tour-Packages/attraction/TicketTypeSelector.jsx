import React, { useState, useRef, useEffect } from 'react';
import { createPortal } from 'react-dom';
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
  Alert,
  Card,
  CardContent,
  Chip,
  Fade,
  Slide,
  Zoom,
  useTheme,
  alpha,
  Avatar,
  Divider,
  LinearProgress,
  Autocomplete,
  TextField,
} from '@mui/material';
import ConfirmationNumberIcon from '@mui/icons-material/ConfirmationNumber';
import CloseIcon from '@mui/icons-material/Close';
import DescriptionIcon from '@mui/icons-material/Description';
import InfoIcon from '@mui/icons-material/Info';
import GavelIcon from '@mui/icons-material/Gavel';
import CommentIcon from '@mui/icons-material/Comment';
import ErrorOutlineIcon from '@mui/icons-material/ErrorOutline';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import AttachMoneyIcon from '@mui/icons-material/AttachMoney';
import PersonIcon from '@mui/icons-material/Person';
import GroupIcon from '@mui/icons-material/Group';
import ElderlyIcon from '@mui/icons-material/Elderly';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import { useSelector } from 'react-redux';

const TicketTypeSelector = ({ selectedTicketType, onTicketTypeChange, disabled, sectionIndex, formSections, bookingDate, dayIndex }) => {
  const theme = useTheme();
  // States
  const [modalOpen, setModalOpen] = useState(false);
  const [selectedTicket, setSelectedTicket] = useState(null);
  const [textModalContent, setTextModalContent] = useState({ title: "", content: "", isOpen: false });
  const [nriStatus, setNriStatus] = useState("residential");
  const [bookingSuccess, setBookingSuccess] = useState(false);
  const [validationError, setValidationError] = useState(null);

  // Get data from Redux store
  const attractions = useSelector((state) => state.attractions.attractions);
  const attractionDetails = useSelector((state) => state.attractions.attractionDetails);
  const currentMode = useSelector((state) => state.common.bookingMode) || 'dmc';
  const currencyCode = useSelector((state) => state.auth.currencyCode) || "SGD";
  const exchangeRate = useSelector((state) => state.auth.exchangeRate) || 1;
  const usdExchangeRate = useSelector((state) => state.auth.usdExchangeRate) || 1;
  
  // Get booking date from section if not provided as prop
  const sectionBookingDate = formSections && formSections[sectionIndex] ? formSections[sectionIndex].bookingDate : null;
  const effectiveBookingDate = bookingDate || sectionBookingDate;

  // Get tickets from attraction details
  const tickets = attractionDetails?.ticket_prices || [];

  // Log props received from parent component
  useEffect(() => {
    console.log('TicketTypeSelector - Received props:', { 
      bookingDate, 
      dayIndex, 
      effectiveBookingDate,
      sectionIndex 
    });
  }, [bookingDate, dayIndex, effectiveBookingDate, sectionIndex]);

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
  const getPrice = (ticket, priceField) => {
    if (!ticket) return 0;
    
    if (nriStatus === "nri") {
      switch (priceField) {
        case "adult":
          return parseFloat(ticket.dmc_adult_price_nri) || 0;
        case "child":
          return parseFloat(ticket.dmc_child_price_nri) || 0;
        case "senior":
          return parseFloat(ticket.dmc_senior_price_nri) || 0;
        default:
          return 0;
      }
    }
    
    switch (priceField) {
      case "adult":
        return parseFloat(ticket.dmc_adult_price) || 0;
      case "child":
        return parseFloat(ticket.dmc_child_price) || 0;
      case "senior":
        return parseFloat(ticket.dmc_senior_price) || 0;
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

  // Enhanced description rendering with better styling
  const renderDescription = (description, type = "description") => {
    if (!description) return null;
    
    const words = description.split(/\s+/);
    const wordCount = words.length;
    const wordLimit = 15;
    
    const getIcon = () => {
      switch (type) {
        case "description":
          return <InfoIcon sx={{ fontSize: 20, color: 'white' }} />;
        case "remarks":
          return <CommentIcon sx={{ fontSize: 20, color: 'white' }} />;
        case "terms":
          return <GavelIcon sx={{ fontSize: 20, color: 'white' }} />;
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

    const getColor = () => {
      switch (type) {
        case "description":
          return theme.palette.info.main;
        case "remarks":
          return theme.palette.warning.main;
        case "terms":
          return theme.palette.error.main;
        default:
          return theme.palette.primary.main;
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
        <Card sx={{ 
          mt: 2,
          bgcolor: getColor(),
          borderRadius: 2,
          overflow: 'hidden',
          boxShadow: `0 4px 12px ${alpha(getColor(), 0.3)}`
        }}>
          <CardContent sx={{ p: 2 }}>
            <Box sx={{ display: 'flex', mb: 1, alignItems: 'center', gap: 1 }}>
              {getIcon()}
              <Typography fontWeight={600} color="white" variant="subtitle2">
                {getTitle()}
              </Typography>
            </Box>
            <Typography variant="body2" color="white" sx={{ lineHeight: 1.5 }}>
              {description}
            </Typography>
          </CardContent>
        </Card>
      );
    }

    const truncatedText = words.slice(0, wordLimit).join(" ");
    
    return (
      <Card sx={{ 
        mt: 2,
        bgcolor: getColor(),
        borderRadius: 2,
        overflow: 'hidden',
        boxShadow: `0 4px 12px ${alpha(getColor(), 0.3)}`
      }}>
        <CardContent sx={{ p: 2 }}>
          <Box sx={{ display: 'flex', mb: 1, alignItems: 'center', gap: 1 }}>
            {getIcon()}
            <Typography fontWeight={600} color="white" variant="subtitle2">
              {getTitle()}
            </Typography>
          </Box>
          <Typography variant="body2" color="white" sx={{ lineHeight: 1.5, mb: 1 }}>
            {truncatedText + "..."}
          </Typography>
          <Button 
            onClick={openTextModal}
            sx={{ 
              p: 0,
              minWidth: 'auto',
              textTransform: 'none',
              textDecoration: 'underline',
              color: 'white',
              fontSize: '0.875rem',
              '&:hover': {
                bgcolor: 'transparent',
                textDecoration: 'underline'
              }
            }}
          >
            Read more
          </Button>
        </CardContent>
      </Card>
    );
  };

  const handleOpenModal = (ticket) => {
    setSelectedTicket(ticket);
    setModalOpen(true);
    
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
    
    // Update the ticket selection - this will trigger the main component's Redux dispatch
    onTicketTypeChange({
      ticketId: selectedTicket?.ticket_id,
      priceType: nriStatus
    });
    
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
    <Box sx={{ position: 'relative', width: '100%' }}>
      <Autocomplete
        value={tickets.find(t => t.ticket_id === selectedTicketType) || null}
        onChange={(event, newValue) => {
          if (newValue) {
            handleOpenModal(newValue);
          }
        }}
        options={tickets}
        getOptionLabel={(option) => option.ticket_name || ''}
        noOptionsText="No tickets available"
        disabled={disabled}
        renderOption={(props, option) => {
          const { key, ...otherProps } = props;
          
          return (
            <Box component="li" key={key} {...otherProps} sx={{ flexDirection: 'column', alignItems: 'flex-start', p: 2 }}>
              <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', width: '100%', mb: 1 }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
                  <Avatar sx={{ 
                    bgcolor: selectedTicketType === option.ticket_id 
                      ? theme.palette.success.main 
                      : theme.palette.primary.main,
                    width: 32,
                    height: 32,
                  }}>
                    {selectedTicketType === option.ticket_id ? (
                      <CheckCircleIcon sx={{ fontSize: 18 }} />
                    ) : (
                      <ConfirmationNumberIcon sx={{ fontSize: 18 }} />
                    )}
                  </Avatar>
                  <Typography fontWeight={600} color="text.primary">
                    {option.ticket_name}
                  </Typography>
                </Box>
                <Box sx={{ display: 'flex', gap: 0.5 }}>
                  {option.description && (
                    <Avatar sx={{ 
                      bgcolor: alpha(theme.palette.info.main, 0.1),
                      width: 20, 
                      height: 20 
                    }}>
                      <DescriptionIcon sx={{ fontSize: 12, color: 'info.main' }} />
                    </Avatar>
                  )}
                  {option.remarks && (
                    <Avatar sx={{ 
                      bgcolor: alpha(theme.palette.warning.main, 0.1),
                      width: 20, 
                      height: 20 
                    }}>
                      <CommentIcon sx={{ fontSize: 12, color: 'warning.main' }} />
                    </Avatar>
                  )}
                  {option.terms_conditions && (
                    <Avatar sx={{ 
                      bgcolor: alpha(theme.palette.error.main, 0.1),
                      width: 20, 
                      height: 20 
                    }}>
                      <GavelIcon sx={{ fontSize: 12, color: 'error.main' }} />
                    </Avatar>
                  )}
                </Box>
              </Box>
              <Box sx={{ display: 'flex', gap: 1, flexWrap: 'wrap' }}>
                <Chip
                  icon={<PersonIcon sx={{ fontSize: 14 }} />}
                  label={`Adult: ${formatPrice(getPrice(option, "adult"), "main")}`}
                  size="small"
                  variant="outlined"
                  sx={{ fontSize: '0.7rem', height: 20 }}
                />
                <Chip
                  icon={<GroupIcon sx={{ fontSize: 14 }} />}
                  label={`Child: ${formatPrice(getPrice(option, "child"), "main")}`}
                  size="small"
                  variant="outlined"
                  sx={{ fontSize: '0.7rem', height: 20 }}
                />
                <Chip
                  icon={<ElderlyIcon sx={{ fontSize: 14 }} />}
                  label={`Senior: ${formatPrice(getPrice(option, "senior"), "main")}`}
                  size="small"
                  variant="outlined"
                  sx={{ fontSize: '0.7rem', height: 20 }}
                />
              </Box>
            </Box>
          );
        }}
        renderInput={(params) => (
          <TextField
            {...params}
            label="Select Ticket"
            fullWidth
          />
        )}
      />

      {/* Enhanced Ticket Details Modal */}
      <Modal
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        closeAfterTransition
      >
        <Fade in={modalOpen}>
          <Card sx={{
            position: 'absolute',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
            width: '95%',
            maxWidth: 900,
            maxHeight: '95vh',
            borderRadius: 3,
            overflow: 'auto',
            boxShadow: `0 24px 48px ${alpha(theme.palette.common.black, 0.3)}`,
            zIndex: 100000,
          }}>
            <CardContent sx={{ p: 0 }}>
              {/* Modal Header */}
              <Box sx={{ 
                p: 3,
                bgcolor: alpha(theme.palette.primary.main, 0.1),
                borderBottom: `1px solid ${alpha(theme.palette.primary.main, 0.2)}`,
                display: 'flex', 
                justifyContent: 'space-between', 
                alignItems: 'center' 
              }}>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                  <Avatar sx={{ 
                    bgcolor: theme.palette.primary.main, 
                    width: 48, 
                    height: 48 
                  }}>
                    <ConfirmationNumberIcon sx={{ fontSize: 24 }} />
                  </Avatar>
                  <Box>
                    <Typography variant="h5" fontWeight={700} color="primary.main">
                      Confirm Ticket Selection
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Review details and pricing before booking
                    </Typography>
                  </Box>
                </Box>
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                  {nriStatus === "nri" && (
                    <Chip
                      label="Foreigner Pricing"
                      color="warning"
                      variant="filled"
                      sx={{ fontWeight: 600 }}
                    />
                  )}
                  <IconButton 
                    onClick={() => setModalOpen(false)} 
                    sx={{ 
                      bgcolor: alpha(theme.palette.error.main, 0.1),
                      '&:hover': { bgcolor: alpha(theme.palette.error.main, 0.2) }
                    }}
                  >
                    <CloseIcon />
                  </IconButton>
                </Box>
              </Box>

              {/* Time Slot Warning */}
              {!formSections[sectionIndex]?.timeSlot && (
                <Alert 
                  severity="error" 
                  icon={<ErrorOutlineIcon />}
                  sx={{ 
                    m: 2,
                    borderRadius: 2,
                    '& .MuiAlert-message': { fontWeight: 600 }
                  }}
                >
                  Time slot is not selected! Please close this window and select a time slot first.
                </Alert>
              )}

              {/* Validation Error */}
              <Fade in={!!validationError}>
                <Box>
                  {validationError && (
                    <Alert severity="error" sx={{ m: 2, borderRadius: 2 }}>
                      {validationError}
                    </Alert>
                  )}
                </Box>
              </Fade>

              {selectedTicket && (
                <Box sx={{ p: 3 }}>
                  {/* Ticket Header */}
                  <Box sx={{ 
                    display: 'flex', 
                    alignItems: 'center', 
                    gap: 2, 
                    mb: 3,
                    p: 2,
                    borderRadius: 2,
                    bgcolor: alpha(theme.palette.success.main, 0.05),
                    border: `1px solid ${alpha(theme.palette.success.main, 0.2)}`
                  }}>
                    <Avatar sx={{ 
                      bgcolor: theme.palette.success.main,
                      width: 48,
                      height: 48
                    }}>
                      <ConfirmationNumberIcon sx={{ fontSize: 24 }} />
                    </Avatar>
                    <Box>
                      <Typography variant="h5" fontWeight={700} color="text.primary">
                        {selectedTicket.ticket_name}
                      </Typography>
                      <Typography variant="body2" color="text.secondary">
                        Selected ticket option
                      </Typography>
                    </Box>
                  </Box>

                  {/* Content Sections */}
                  <Grid container spacing={2}>
                    <Grid item xs={12} md={4}>
                      {renderDescription(stripPTags(selectedTicket.description), "description")}
                    </Grid>
                    <Grid item xs={12} md={4}>
                      {renderDescription(stripPTags(selectedTicket.remarks), "remarks")}
                    </Grid>
                    <Grid item xs={12} md={4}>
                      {renderDescription(stripPTags(selectedTicket.terms_conditions), "terms")}
                    </Grid>
                  </Grid>

                  {/* Price Type Selection */}
                  <Card sx={{ 
                    my: 3,
                    bgcolor: alpha(theme.palette.primary.main, 0.05),
                    border: `2px solid ${alpha(theme.palette.primary.main, 0.2)}`,
                    borderRadius: 2
                  }}>
                    <CardContent sx={{ p: 2 }}>
                      <Typography fontWeight={600} sx={{ mb: 2, display: 'flex', alignItems: 'center', gap: 1 }}>
                        <AttachMoneyIcon color="primary" />
                        Select Price Type:
                      </Typography>
                      <RadioGroup
                        row
                        value={nriStatus}
                        onChange={(e) => setNriStatus(e.target.value)}
                        sx={{ gap: 2 }}
                      >
                        <FormControlLabel 
                          value="residential" 
                          control={<Radio />} 
                          label={
                            <Chip 
                              label="Local Resident" 
                              color={nriStatus === "residential" ? "primary" : "default"}
                              variant={nriStatus === "residential" ? "filled" : "outlined"}
                              sx={{ fontWeight: 600 }}
                            />
                          }
                        />
                        <FormControlLabel 
                          value="nri" 
                          control={<Radio />} 
                          label={
                            <Chip 
                              label="Foreigner" 
                              color={nriStatus === "nri" ? "warning" : "default"}
                              variant={nriStatus === "nri" ? "filled" : "outlined"}
                              sx={{ fontWeight: 600 }}
                            />
                          }
                        />
                      </RadioGroup>
                    </CardContent>
                  </Card>

                  {/* Enhanced Pricing Display */}
                  <Box sx={{ mb: 3 }}>
                    <Typography variant="h6" align="center" sx={{ mb: 2, fontWeight: 700 }}>
                      {nriStatus === "residential" ? "🏠 Local Resident Prices" : "🌍 Foreigner Prices"}
                    </Typography>

                    <Grid container spacing={2}>
                      {[
                        { type: "adult", icon: PersonIcon, color: "primary" },
                        { type: "child", icon: GroupIcon, color: "info" },
                        { type: "senior", icon: ElderlyIcon, color: "warning" }
                      ].map(({ type, icon: Icon, color }) => (
                        <Grid item xs={12} md={4} key={type}>
                          <Card sx={{ 
                            border: `2px solid ${alpha(theme.palette[color].main, 0.2)}`,
                            borderRadius: 2,
                            bgcolor: alpha(theme.palette[color].main, 0.05),
                            transition: 'all 0.3s ease',
                            '&:hover': {
                              boxShadow: `0 8px 24px ${alpha(theme.palette[color].main, 0.2)}`,
                              transform: 'translateY(-2px)'
                            }
                          }}>
                            <CardContent sx={{ textAlign: 'center', p: 2.5 }}>
                              <Avatar sx={{ 
                                bgcolor: theme.palette[color].main,
                                mx: 'auto',
                                mb: 1.5,
                                width: 48,
                                height: 48
                              }}>
                                <Icon sx={{ fontSize: 24 }} />
                              </Avatar>
                              <Typography variant="h6" color={`${color}.main`} fontWeight={700} sx={{ mb: 1 }}>
                                {type.charAt(0).toUpperCase() + type.slice(1)}
                              </Typography>
                              <Typography variant="h5" fontWeight={700} color="text.primary" sx={{ mb: 1 }}>
                                {formatPrice(getPrice(selectedTicket, type), "main")}
                              </Typography>
                              {currencyCode !== "USD" && (
                                <Typography variant="body2" color="text.secondary">
                                  {formatPrice(getPrice(selectedTicket, type), "usd")}
                                </Typography>
                              )}
                              {currencyCode !== "SGD" && (
                                <Typography variant="body2" color="text.secondary">
                                  {formatPrice(getPrice(selectedTicket, type), "sgd")}
                                </Typography>
                              )}
                            </CardContent>
                          </Card>
                        </Grid>
                      ))}
                    </Grid>
                  </Box>

                  <Divider sx={{ my: 3 }} />

                  {/* Action Buttons */}
                  <Box sx={{ display: 'flex', justifyContent: 'flex-end', gap: 2 }}>
                    <Button 
                      variant="outlined" 
                      size="large"
                      onClick={() => setModalOpen(false)}
                      sx={{ 
                        minWidth: 120,
                        borderRadius: 2,
                        textTransform: 'none',
                        fontWeight: 600
                      }}
                    >
                      Cancel
                    </Button>
                    <Tooltip 
                      title={!formSections[sectionIndex]?.timeSlot ? "Please select a time slot first" : ""}
                      arrow
                    >
                      <span>
                        <Button 
                          variant="contained" 
                          color="success" 
                          size="large"
                          onClick={handleConfirmSelection}
                          disabled={!formSections[sectionIndex]?.timeSlot}
                          startIcon={<CheckCircleIcon />}
                          sx={{ 
                            minWidth: 140,
                            borderRadius: 2,
                            textTransform: 'none',
                            fontWeight: 700,
                            boxShadow: `0 4px 12px ${alpha(theme.palette.success.main, 0.3)}`,
                            '&:hover': {
                              boxShadow: `0 6px 16px ${alpha(theme.palette.success.main, 0.4)}`,
                            }
                          }}
                        >
                          Book Now
                        </Button>
                      </span>
                    </Tooltip>
                  </Box>
                  
                  {/* Reminder */}
                  <Box sx={{ 
                    mt: 2, 
                    p: 2,
                    borderRadius: 2,
                    bgcolor: alpha(theme.palette.warning.main, 0.1),
                    border: `1px solid ${alpha(theme.palette.warning.main, 0.3)}`
                  }}>
                    <Typography 
                      variant="body2" 
                      color="warning.dark" 
                      sx={{ 
                        display: 'flex', 
                        alignItems: 'center', 
                        justifyContent: 'center',
                        fontWeight: 600,
                        gap: 1
                      }}
                    >
                      <Box component="span" sx={{ fontSize: '1.2rem' }}>⚠️</Box>
                      Remember: A time slot must be selected to complete the booking.
                    </Typography>
                  </Box>
                </Box>
              )}
            </CardContent>
          </Card>
        </Fade>
      </Modal>

      {/* Text Content Modal */}
      <Modal
        open={textModalContent.isOpen}
        onClose={() => setTextModalContent({...textModalContent, isOpen: false})}
        closeAfterTransition
      >
        <Fade in={textModalContent.isOpen}>
          <Card sx={{
            position: 'absolute',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
            width: '90%',
            maxWidth: 700,
            borderRadius: 3,
            maxHeight: '80vh',
            overflow: 'auto',
            boxShadow: `0 24px 48px ${alpha(theme.palette.common.black, 0.2)}`,
            zIndex: 100000,
          }}>
            <CardContent sx={{ p: 0 }}>
              <Box sx={{ 
                p: 3,
                bgcolor: alpha(theme.palette.primary.main, 0.1),
                borderBottom: `1px solid ${alpha(theme.palette.primary.main, 0.2)}`,
                display: 'flex', 
                justifyContent: 'space-between', 
                alignItems: 'center' 
              }}>
                <Typography variant="h6" fontWeight={700} color="primary.main">
                  {textModalContent.title}
                </Typography>
                <IconButton 
                  onClick={() => setTextModalContent({...textModalContent, isOpen: false})}
                  sx={{ 
                    bgcolor: alpha(theme.palette.error.main, 0.1),
                    '&:hover': { bgcolor: alpha(theme.palette.error.main, 0.2) }
                  }}
                >
                  <CloseIcon />
                </IconButton>
              </Box>
              <Box sx={{ p: 3 }}>
                <Typography variant="body1" sx={{ lineHeight: 1.7, mb: 3 }}>
                  {textModalContent.content}
                </Typography>
                <Box sx={{ display: 'flex', justifyContent: 'flex-end' }}>
                  <Button 
                    variant="contained"
                    onClick={() => setTextModalContent({...textModalContent, isOpen: false})}
                    sx={{ 
                      borderRadius: 2,
                      textTransform: 'none',
                      fontWeight: 600
                    }}
                  >
                    Close
                  </Button>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Fade>
      </Modal>
      
      {/* Success Alert */}
      <Zoom in={bookingSuccess}>
        <Alert 
          severity="success" 
          sx={{ 
            position: 'fixed', 
            bottom: 20, 
            right: 20, 
            zIndex: 1500,
            borderRadius: 2,
            boxShadow: `0 8px 24px ${alpha(theme.palette.success.main, 0.3)}`,
            fontWeight: 600
          }}
          icon={<CheckCircleIcon />}
        >
          Booking information saved successfully to the tour package data!
        </Alert>
      </Zoom>
    </Box>
  );
};

export default TicketTypeSelector; 