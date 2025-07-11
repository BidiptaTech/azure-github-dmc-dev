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
  FormControl,
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
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Paper,
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
import ChildCareIcon from '@mui/icons-material/ChildCare';
import AccessibilityNewIcon from '@mui/icons-material/AccessibilityNew';
import LocalOfferIcon from '@mui/icons-material/LocalOffer';
import CurrencyExchangeIcon from '@mui/icons-material/CurrencyExchange';
import CancelIcon from '@mui/icons-material/Cancel';
import VerifiedIcon from '@mui/icons-material/Verified';
import ArticleIcon from '@mui/icons-material/Article';
import LanguageIcon from '@mui/icons-material/Language';
import ListIcon from '@mui/icons-material/List';
import PackageIcon from '@mui/icons-material/Inventory';
import { useSelector } from 'react-redux';
import { styled } from '@mui/material/styles';
import { capitalizeWords } from '../../../utils/textUtils';

// Styled components
const ModalSidebar = styled(Box)(({ theme }) => ({
  width: '280px',
  borderLeft: `1px solid ${theme.palette.divider}`,
  backgroundColor: alpha(theme.palette.primary.main, 0.02),
  height: '100%',
  padding: theme.spacing(2),
  display: 'flex',
  flexDirection: 'column',
}));

const SidebarHeader = styled(Box)(({ theme }) => ({
  backgroundColor: theme.palette.primary.main,
  color: theme.palette.common.white,
  padding: theme.spacing(2),
  borderRadius: theme.shape.borderRadius,
  marginBottom: theme.spacing(2),
}));

const ContentBox = styled(Box)(({ theme }) => ({
  marginTop: theme.spacing(2),
  padding: theme.spacing(1.5, 2),
  backgroundColor: alpha(theme.palette.primary.main, 0.05),
  borderRadius: theme.shape.borderRadius,
  position: 'relative',
  overflow: 'hidden',
  '&:before': {
    content: '""',
    position: 'absolute',
    top: 0,
    left: 0,
    width: '4px',
    height: '100%',
    backgroundColor: theme.palette.primary.main,
  }
}));

const PriceCard = styled(Card)(({ theme }) => ({
  height: '100%',
  boxShadow: 'none',
  border: `1px solid ${theme.palette.divider}`,
  borderRadius: theme.shape.borderRadius,
  transition: 'all 0.3s',
  position: 'relative',
  overflow: 'hidden',
  '&:hover': {
    transform: 'translateY(-3px)',
    boxShadow: '0 10px 20px rgba(0,0,0,0.08)',
    '& .price-icon': {
      transform: 'scale(1.1)',
    }
  },
  '& .price-icon': {
    transition: 'transform 0.3s',
  }
}));

const PriceCardHeader = styled(Box)(({ theme, type }) => {
  let color = theme.palette.primary.main;
  if (type === 'child') color = theme.palette.success.main;
  if (type === 'senior') color = theme.palette.warning.main;
  
  return {
    backgroundColor: alpha(color, 0.1),
    padding: theme.spacing(1.5),
    borderBottom: `1px solid ${alpha(color, 0.2)}`,
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between'
  };
});

const TicketTypeSelector = ({ selectedTicketType, onTicketTypeChange, disabled, sectionIndex, formSections, bookingDate, dayIndex, packages = [] }) => {
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
      sectionIndex,
      packages,
      'packages length': packages?.length,
      'tickets length': tickets?.length
    });
    console.log('TicketTypeSelector - Full packages data:', packages);
    console.log('TicketTypeSelector - Full tickets data:', tickets);
  }, [bookingDate, dayIndex, effectiveBookingDate, sectionIndex, packages, tickets]);

  // Get all available options (tickets + packages)
  const allOptions = [
    ...tickets.map(ticket => ({ 
      ...ticket, 
      type: 'attraction' 
    })),
    ...packages.map(pkg => ({
      ticket_id: `pkg_${pkg.id}`,
      ticket_name: pkg.name,
      dmc_adult_price: parseFloat(pkg.adult_price || 0),
      dmc_child_price: parseFloat(pkg.child_price || 0),
      dmc_senior_price: parseFloat(pkg.senior_citizen_price || 0),
      description: pkg.description,
      attractions: pkg.attractions,
      type: 'attraction_package',
      packageDetails: pkg,
      package_attraction_id: pkg.package_attraction_id || null
    }))
  ];

  // Debug allOptions
  useEffect(() => {
    console.log('TicketTypeSelector - allOptions:', allOptions);
    console.log('TicketTypeSelector - allOptions length:', allOptions.length);
    console.log('TicketTypeSelector - packages in allOptions:', allOptions.filter(item => item.type === 'package'));
  }, [allOptions]);

  // Function to check if selected item is a package
  const isPackage = (item) => {
    return item && item.type === 'attraction_package';
  };

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
    
    // For packages, use the package prices directly
    if (isPackage(ticket)) {
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
    }
    
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

  // Handle description truncation and toggle
  const renderDescription = (description, type = "description") => {
    if (!description) return null;
    
    const words = description.split(/\s+/);
    const wordCount = words.length;
    const wordLimit = 10;
    
    const getIcon = () => {
      switch (type) {
        case "description":
          return <InfoIcon fontSize="small" color="primary" sx={{ mr: 1 }} />;
        case "remarks":
          return <CommentIcon fontSize="small" color="warning" sx={{ mr: 1 }} />;
        case "terms":
          return <GavelIcon fontSize="small" color="error" sx={{ mr: 1 }} />;
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
        <ContentBox>
          <Box display="flex" alignItems="center" mb={0.5}>
              {getIcon()}
            <Typography variant="subtitle2">{getTitle()}</Typography>
            </Box>
          <Typography variant="body2">{description}</Typography>
        </ContentBox>
      );
    }

    const truncatedText = words.slice(0, wordLimit).join(" ");
    
    return (
      <ContentBox>
        <Box display="flex" alignItems="center" mb={0.5}>
            {getIcon()}
          <Typography variant="subtitle2">{getTitle()}</Typography>
          </Box>
        <Typography variant="body2">
            {truncatedText + "..."}
          </Typography>
          <Button 
          onClick={(e) => {
            e.preventDefault();
            e.stopPropagation();
            openTextModal();
          }}
            sx={{ 
              p: 0,
            mt: 0.5, 
              minWidth: 'auto',
              textTransform: 'none',
            fontSize: '14px' 
          }}
          endIcon={<ArticleIcon fontSize="small" />}
        >
          Show more
          </Button>
      </ContentBox>
    );
  };

  // Get icon for price type
  const getPriceIcon = (type) => {
    switch(type) {
      case 'adult':
        return <PersonIcon />;
      case 'child':
        return <ChildCareIcon />;
      case 'senior':
        return <AccessibilityNewIcon />;
      default:
        return <LocalOfferIcon />;
    }
  };

  // Get color for price type
  const getPriceColor = (type) => {
    switch(type) {
      case 'adult':
        return 'primary';
      case 'child':
        return 'success';
      case 'senior':
        return 'warning';
      default:
        return 'primary';
    }
  };

  const handleOpenModal = (ticket) => {
    // Add package_type and type to the ticket object
    const ticketWithType = {
      ...ticket,
      type: isPackage(ticket) ? 'attraction_package' : 'attraction',
      package_type: isPackage(ticket) ? 1 : 0,
      package_attraction_id: isPackage(ticket) ? ticket.package_attraction_id : null
    };
    
    setSelectedTicket(ticketWithType);
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
    const ticketData = {
      ticketId: selectedTicket?.ticket_id,
      priceType: nriStatus,
      type: isPackage(selectedTicket) ? 'attraction_package' : 'attraction'
    };
    
    console.log('TicketTypeSelector - handleBookNow - Passing ticket data:', ticketData);
    console.log('TicketTypeSelector - handleBookNow - Selected ticket type:', ticketData.type);
    
    onTicketTypeChange(ticketData);
    
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
    
    // Update the ticket selection with final details
    if (selectedTicket) {
      const finalTicket = {
        ...selectedTicket,
        type: isPackage(selectedTicket) ? 'attraction_package' : 'attraction',
        package_type: isPackage(selectedTicket) ? 1 : 0,
        package_attraction_id: isPackage(selectedTicket) ? selectedTicket.package_attraction_id : null
      };
      
      // Update the ticket selection
      const ticketData = {
        ticketId: finalTicket.ticket_id,
        priceType: nriStatus,
        type: finalTicket.type
      };
      
      console.log('TicketTypeSelector - Passing ticket data:', ticketData);
      console.log('TicketTypeSelector - Selected ticket type:', finalTicket.type);
      
      onTicketTypeChange(ticketData);
    }
    
    handleBookNow();
  };

  // Handle NRI status changes
  const handleNriStatusChange = (event) => {
    console.log("Changing NRI status to:", event.target.value);
    setNriStatus(event.target.value);
  };

  return (
    <Box sx={{ position: 'relative', width: '100%' }}>
      <Autocomplete
        value={allOptions.find(t => t.ticket_id === selectedTicketType) || null}
        onChange={(event, newValue) => {
          if (newValue) {
            handleOpenModal(newValue);
          }
        }}
        options={allOptions}
        getOptionLabel={(option) => capitalizeWords(option.ticket_name || '')}
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
                  <Box>
                  <Typography fontWeight={600} color="text.primary">
                    {capitalizeWords(option.ticket_name)}
                  </Typography>
                    {isPackage(option) && (
                      <Chip 
                        size="small" 
                        label="Package" 
                        color="secondary" 
                        sx={{ mt: 0.5, height: '18px', fontSize: '10px' }} 
                      />
                    )}
                  </Box>
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
                  {!isPackage(option) && option.remarks && (
                    <Avatar sx={{ 
                      bgcolor: alpha(theme.palette.warning.main, 0.1),
                      width: 20, 
                      height: 20 
                    }}>
                      <CommentIcon sx={{ fontSize: 12, color: 'warning.main' }} />
                    </Avatar>
                  )}
                  {!isPackage(option) && option.terms_conditions && (
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
              {isPackage(option) && option.attractions && (
                <Box sx={{ mt: 1 }}>
                  <Typography 
                    variant="caption" 
                    color="text.secondary"
                    sx={{ display: 'flex', alignItems: 'center' }}
                  >
                    <ListIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                    {option.attractions.length} attraction{option.attractions.length !== 1 ? 's' : ''}
                  </Typography>
                </Box>
              )}
            </Box>
          );
        }}
        renderInput={(params) => {
          const selectedOption = allOptions.find(t => t.ticket_id === selectedTicketType);
          return (
          <TextField
            {...params}
            label="Select Ticket"
            fullWidth
              InputProps={{
                ...params.InputProps,
                startAdornment: selectedOption ? (
                  <Box sx={{ display: 'flex', alignItems: 'center', mr: 1 }}>
                    {isPackage(selectedOption) ? (
                      <PackageIcon sx={{ color: 'secondary.main', fontSize: 20 }} />
                    ) : (
                      <ConfirmationNumberIcon sx={{ color: 'primary.main', fontSize: 20 }} />
                    )}
                  </Box>
                ) : null,
                ...params.InputProps.startAdornment
              }}
            />
          );
        }}
      />

      {/* Enhanced Ticket Details Modal with Sidebar */}
      <Dialog
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        maxWidth="lg"
        fullWidth
        TransitionComponent={Zoom}
      >
        <DialogTitle sx={{ px: 3, py: 2, display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: '1px solid', borderColor: 'divider' }}>
          <Box display="flex" alignItems="center">
            <ConfirmationNumberIcon color="primary" sx={{ mr: 1.5, fontSize: 28 }} />
            <Typography variant="h6">
              {isPackage(selectedTicket) ? 'Confirm Package Selection' : 'Confirm Ticket Selection'}
                    </Typography>
            {!isPackage(selectedTicket) && (
                    <Chip
                icon={<LanguageIcon />}
                label={nriStatus === "nri" ? "Foreigner Pricing" : "Local Pricing"}
                color="primary" 
                size="small" 
                sx={{ ml: 1.5 }} 
              />
            )}
            {isPackage(selectedTicket) && (
              <Chip 
                icon={<ListIcon />}
                label={`${selectedTicket.attractions?.length || 0} Attractions`}
                color="secondary" 
                size="small" 
                sx={{ ml: 1.5 }} 
              />
            )}
          </Box>
          <IconButton onClick={() => setModalOpen(false)} size="small">
                    <CloseIcon />
                  </IconButton>
        </DialogTitle>

        <DialogContent sx={{ p: 0, display: 'flex', height: '600px' }}>   
          {selectedTicket && (
            <>
              {/* Main Content Area */}
              <Box sx={{ p: 3, flex: 1, overflowY: 'auto' }}>
              {/* Time Slot Warning */}
              {!formSections[sectionIndex]?.timeSlot && (
                <Alert 
                  severity="error" 
                  icon={<ErrorOutlineIcon />}
                  sx={{ 
                      mb: 2,
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
                      <Alert severity="error" sx={{ mb: 2, borderRadius: 2 }}>
                      {validationError}
                    </Alert>
                  )}
                </Box>
              </Fade>

                <Box 
                  display="flex" 
                  alignItems="center" 
                  mb={2.5}
                  sx={{
                    backgroundColor: alpha('#3554D1', 0.05),
                    p: 2,
                    borderRadius: 1,
                    border: '1px solid',
                    borderColor: 'primary.light',
                  }}
                >
                  <Box
                    sx={{
                      backgroundColor: 'primary.main',
                      borderRadius: '50%',
                      width: 48,
                      height: 48,
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      mr: 2
                    }}
                  >
                    <VerifiedIcon sx={{ color: 'white', fontSize: 28 }} />
                  </Box>
                  <Box flexGrow={1}>
                    <Typography variant="h6" color="primary.main">
                        {capitalizeWords(selectedTicket.ticket_name)}
                      </Typography>
                    {isPackage(selectedTicket) && (
                      <Typography variant="caption" color="text.secondary">
                        Package with {selectedTicket.attractions?.length || 0} attractions
                      </Typography>
                    )}
                    </Box>
                  </Box>

                {/* Price Type Selection Radio Buttons - Only for regular tickets */}
                {!isPackage(selectedTicket) && (
                  <Paper 
                    variant="outlined" 
                    sx={{ 
                      mt: 1,
                      mb: 2.5, 
                      p: 2,
                      bgcolor: alpha('#3554D1', 0.05),
                      borderRadius: 1
                    }}
                  >
                    <FormControl component="fieldset">
                      <Box display="flex" alignItems="center">
                        <CurrencyExchangeIcon sx={{ mr: 1, color: 'primary.main' }} />
                        <Typography fontWeight={500} mr={3}>Select Price Type:</Typography>
                      <RadioGroup
                        row
                          name="priceType"
                        value={nriStatus}
                          onChange={handleNriStatusChange}
                      >
                        <FormControlLabel 
                          value="residential" 
                            control={<Radio color="primary" />} 
                          label={
                              <Box display="flex" alignItems="center">
                                <Box component="span" mr={0.5}>Local</Box>
                                {nriStatus === 'residential' && (
                                  <CheckCircleIcon fontSize="small" color="primary" />
                                )}
                              </Box>
                            } 
                            sx={{
                              '& .MuiFormControlLabel-label': {
                                color: nriStatus === 'residential' ? 'primary.main' : 'text.primary',
                                fontWeight: nriStatus === 'residential' ? 500 : 400
                              }
                            }}
                        />
                        <FormControlLabel 
                          value="nri" 
                            control={<Radio color="primary" />} 
                          label={
                              <Box display="flex" alignItems="center">
                                <Box component="span" mr={0.5}>Foreigner</Box>
                                {nriStatus === 'nri' && (
                                  <CheckCircleIcon fontSize="small" color="primary" />
                                )}
                              </Box>
                            }
                            sx={{
                              '& .MuiFormControlLabel-label': {
                                color: nriStatus === 'nri' ? 'primary.main' : 'text.primary',
                                fontWeight: nriStatus === 'nri' ? 500 : 400
                              }
                            }}
                        />
                      </RadioGroup>
                      </Box>
                    </FormControl>
                  </Paper>
                )}

                {/* Price Cards in Grid */}
                <Box 
                  textAlign="center" 
                  mb={2} 
                  py={1} 
                  bgcolor={alpha('#3554D1', 0.05)} 
                  borderRadius={1}
                >
                  <Typography variant="h6" display="flex" alignItems="center" justifyContent="center">
                    <AttachMoneyIcon sx={{ mr: 1 }} />
                    {isPackage(selectedTicket) ? 'Package Pricing' : (nriStatus === "residential" ? "Local Prices" : "Foreigner Prices")}
                    </Typography>
                </Box>

                    <Grid container spacing={2}>
                  {['adult', 'child', 'senior'].map(priceType => (
                    <Grid item xs={12} sm={4} key={`${priceType}-${nriStatus}`}>
                      <Fade in={true} timeout={500}>
                        <PriceCard>
                          <PriceCardHeader type={priceType}>
                            <Box display="flex" alignItems="center">
                              {getPriceIcon(priceType)}
                              <Typography 
                                variant="subtitle1" 
                                fontWeight={500}
                                sx={{ ml: 1 }}
                              >
                                {priceType.charAt(0).toUpperCase() + priceType.slice(1)} Price
                              </Typography>
                            </Box>
                            <Avatar 
                              className="price-icon"
                              sx={{ 
                                width: 32, 
                                height: 32, 
                                bgcolor: `${getPriceColor(priceType)}.main`,
                              }}
                            >
                              {priceType === 'adult' ? 'A' : priceType === 'child' ? 'C' : 'S'}
                              </Avatar>
                          </PriceCardHeader>
                          
                          <CardContent>                          
                            <Typography 
                              variant="h5" 
                              component="div" 
                              fontWeight={600}
                              color={`${getPriceColor(priceType)}.main`}
                              textAlign="center"
                              mb={1.5}
                            >
                              {formatPrice(getPrice(selectedTicket, priceType), "main")}
                              </Typography>
                            
                            <Divider sx={{ mb: 1.5 }} />
                            
                            <Box>
                              {currencyCode !== "USD" && (
                                <Box display="flex" alignItems="center" justifyContent="space-between" mb={0.5}>
                                  <Typography variant="body2" fontWeight={500}>
                                    {formatPrice(getPrice(selectedTicket, priceType), "usd")}
                                </Typography>
                                </Box>
                              )}

                              {currencyCode !== "SGD" && (
                                <Box display="flex" alignItems="center" justifyContent="space-between">
                                  <Typography variant="body2" fontWeight={500}>
                                    {formatPrice(getPrice(selectedTicket, priceType), "sgd")}
                                </Typography>
                                </Box>
                              )}
                            </Box>
                            </CardContent>
                        </PriceCard>
                      </Fade>
                        </Grid>
                      ))}
                    </Grid>
                
                {/* Package Info - If selected ticket is a package */}
                {isPackage(selectedTicket) && selectedTicket.attractions && selectedTicket.attractions.length > 0 && (
                  <Box mt={4}>
                    <Typography 
                      variant="h6" 
                      sx={{ 
                        mb: 2, 
                        display: 'flex', 
                        alignItems: 'center',
                        color: 'primary.main' 
                      }}
                    >
                      <ListIcon sx={{ mr: 1 }} />
                      Included Attractions
                    </Typography>
                    <Grid container spacing={2}>
                      {selectedTicket.attractions.map((attraction, index) => (
                        <Grid item xs={12} sm={6} md={4} key={`attraction-${attraction.attraction_id}`}>
                          <Paper 
                            elevation={0}
                            sx={{
                              p: 2,
                              mb: 2,
                              border: '1px solid',
                              borderColor: 'divider',
                              borderRadius: 1,
                              transition: 'all 0.3s ease',
                              height: '100%',
                              '&:hover': {
                                boxShadow: '0 4px 12px rgba(0,0,0,0.1)',
                                transform: 'translateY(-3px)',
                              }
                            }}
                          >
                            <Box 
                              sx={{ 
                                width: '100%', 
                                height: 140, 
                                mb: 1.5,
                                borderRadius: 1,
                                overflow: 'hidden',
                                position: 'relative'
                              }}
                            >
                              <img 
                                src={attraction.master_image} 
                                alt={attraction.name}
                                style={{
                                  width: '100%',
                                  height: '100%',
                                  objectFit: 'cover'
                                }}
                              />
                  </Box>
                            <Typography 
                              variant="subtitle1" 
                              fontWeight={600}
                              sx={{ mb: 0.5 }}
                            >
                              {attraction.name}
                            </Typography>
                            <Typography 
                              variant="body2" 
                              color="text.secondary"
                              sx={{ mb: 1 }}
                            >
                              {attraction.location}, {attraction.country}
                            </Typography>
                          </Paper>
                        </Grid>
                      ))}
                    </Grid>
                  </Box>
                )}
                
                {/* Description sections */}
                <Grid container spacing={2} mt={1}>
                  {selectedTicket.description && (
                    <Grid item xs={12} md={isPackage(selectedTicket) ? 12 : 4}>
                      {renderDescription(stripPTags(selectedTicket.description), "description")}
                    </Grid>
                  )}
                  {!isPackage(selectedTicket) && selectedTicket.remarks && (
                    <Grid item xs={12} md={4}>
                      {renderDescription(stripPTags(selectedTicket.remarks), "remarks")}
                    </Grid>
                  )}
                  {!isPackage(selectedTicket) && selectedTicket.terms_conditions && (
                    <Grid item xs={12} md={4}>
                      {renderDescription(stripPTags(selectedTicket.terms_conditions), "terms")}
                    </Grid>
                  )}
                </Grid>
              </Box>
              
              {/* Right Sidebar */}
              <ModalSidebar>
                <SidebarHeader>
                  <Box display="flex" alignItems="center">
                    <ListIcon sx={{ mr: 1 }} />
                    <Typography variant="subtitle1" fontWeight={600}>
                      Attraction Options
                    </Typography>
                  </Box>
                </SidebarHeader>
                
                <Box sx={{ overflow: 'auto', flexGrow: 1 }}>
                  {allOptions.map((item, index) => (
                    <Paper
                      key={item.ticket_id}
                      elevation={0}
                      sx={{
                        p: 1.5,
                        mb: 1.5,
                        backgroundColor: selectedTicket?.ticket_id === item.ticket_id 
                          ? alpha('#3554D1', 0.08) 
                          : 'background.paper',
                        border: '1px solid',
                        borderColor: selectedTicket?.ticket_id === item.ticket_id 
                          ? 'primary.main' 
                          : 'divider',
                        borderRadius: 1,
                        cursor: 'pointer',
                        '&:hover': {
                          backgroundColor: alpha('#3554D1', 0.05),
                          borderColor: alpha('#3554D1', 0.3),
                        }
                      }}
                      onClick={() => setSelectedTicket(item)}
                    >
                      <Box display="flex" alignItems="center">
                        {isPackage(item) && (
                          <Chip 
                            size="small" 
                            label="Package" 
                            color="secondary" 
                            sx={{ mr: 1, height: '18px', fontSize: '10px' }} 
                          />
                        )}
                        <Typography 
                          variant="body2" 
                          fontWeight={selectedTicket?.ticket_id === item.ticket_id ? 600 : 400}
                          color={selectedTicket?.ticket_id === item.ticket_id ? 'primary' : 'textPrimary'}
                        >
                          {capitalizeWords(item.ticket_name)}
                        </Typography>
                      </Box>
                      
                      <Box display="flex" flexDirection="column" mt={1}>
                        <Typography variant="caption" sx={{ display: 'flex', alignItems: 'center', mb: 0.5 }}>
                          <PersonIcon fontSize="small" sx={{ mr: 0.5, color: 'primary.main', fontSize: 14 }} />
                          Adult: {formatPrice(getPrice(item, "adult"), "main")}
                        </Typography>
                        
                        <Typography variant="caption" sx={{ display: 'flex', alignItems: 'center', mb: 0.5 }}>
                          <ChildCareIcon fontSize="small" sx={{ mr: 0.5, color: 'success.main', fontSize: 14 }} />
                          Child: {formatPrice(getPrice(item, "child"), "main")}
                        </Typography>
                        
                        <Typography variant="caption" sx={{ display: 'flex', alignItems: 'center' }}>
                          <AccessibilityNewIcon fontSize="small" sx={{ mr: 0.5, color: 'warning.main', fontSize: 14 }} />
                          Senior: {formatPrice(getPrice(item, "senior"), "main")}
                        </Typography>
                      </Box>
                      
                      {isPackage(item) && item.attractions && (
                        <Box mt={1}>
                          <Typography 
                            variant="caption" 
                            color="text.secondary"
                            sx={{ display: 'flex', alignItems: 'center' }}
                          >
                            <ListIcon fontSize="small" sx={{ mr: 0.5, fontSize: 14 }} />
                            {item.attractions.length} attraction{item.attractions.length !== 1 ? 's' : ''}
                          </Typography>
                        </Box>
                      )}
                      
                      {selectedTicket?.ticket_id === item.ticket_id && (
                        <Chip 
                          label="Selected" 
                          color="primary" 
                          size="small" 
                          icon={<CheckCircleIcon />} 
                          sx={{ mt: 1, fontSize: 11 }}
                        />
                      )}
                    </Paper>
                  ))}
                </Box>
              </ModalSidebar>
            </>
          )}
        </DialogContent>
        
        <DialogActions sx={{ p: 2, borderTop: '1px solid', borderColor: 'divider' }}>
                    <Button 
                      variant="outlined" 
            color="primary" 
                      onClick={() => setModalOpen(false)}
            startIcon={<CancelIcon />}
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
                color="primary" 
                          onClick={handleConfirmSelection}
                          disabled={!formSections[sectionIndex]?.timeSlot}
                          startIcon={<CheckCircleIcon />}
              >
                Confirm Selection
                        </Button>
                      </span>
                    </Tooltip>
        </DialogActions>
      </Dialog>

      {/* Text Content Modal */}
      <Dialog
        open={textModalContent.isOpen}
        onClose={() => setTextModalContent({...textModalContent, isOpen: false})}
        maxWidth="md"
        fullWidth
        TransitionComponent={Zoom}
      >
        <DialogTitle>
          <Box display="flex" alignItems="center">
            <ArticleIcon color="primary" sx={{ mr: 1.5 }} />
            <Typography variant="h6" component="span">
                  {textModalContent.title}
                </Typography>
              </Box>
        </DialogTitle>
        <DialogContent>
          <Typography variant="body1">{textModalContent.content}</Typography>
        </DialogContent>
        <DialogActions>
                  <Button 
                    variant="contained"
            color="primary" 
                    onClick={() => setTextModalContent({...textModalContent, isOpen: false})}
            startIcon={<CheckCircleIcon />}
                  >
                    Close
                  </Button>
        </DialogActions>
      </Dialog>
      
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