import React, { useState, useRef, useEffect } from "react";
import { useSelector } from "react-redux";
import { 
  Dialog, 
  DialogActions, 
  DialogContent, 
  DialogTitle,
  Button,
  Box,
  Typography,
  Paper,
  Radio,
  RadioGroup,
  FormControlLabel,
  FormControl,
  Grid,
  Card,
  CardContent,
  Chip,
  Tooltip,
  Zoom,
  Fade,
  Badge,
  Divider,
  Avatar
} from "@mui/material";
import ConfirmationNumberIcon from "@mui/icons-material/ConfirmationNumber";
import InfoIcon from "@mui/icons-material/Info";
import NotificationsIcon from "@mui/icons-material/Notifications";
import SecurityIcon from "@mui/icons-material/SecurityOutlined";
import KeyboardArrowDownIcon from "@mui/icons-material/KeyboardArrowDown";
import KeyboardArrowUpIcon from "@mui/icons-material/KeyboardArrowUp";
import PersonIcon from '@mui/icons-material/Person';
import ChildCareIcon from '@mui/icons-material/ChildCare';
import AccessibilityNewIcon from '@mui/icons-material/AccessibilityNew';
import LocalOfferIcon from '@mui/icons-material/LocalOffer';
import CurrencyExchangeIcon from '@mui/icons-material/CurrencyExchange';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import CancelIcon from '@mui/icons-material/Cancel';
import VerifiedIcon from '@mui/icons-material/Verified';
import ArticleIcon from '@mui/icons-material/Article';
import LanguageIcon from '@mui/icons-material/Language';
import AttachMoneyIcon from '@mui/icons-material/AttachMoney';
import { styled, alpha } from "@mui/material/styles";

const StyledDropdown = styled(Paper)(({ theme, isOpen }) => ({
  display: 'flex',
  alignItems: 'center',
  border: `1px solid ${isOpen ? theme.palette.primary.main : theme.palette.mode === 'dark' ? alpha(theme.palette.common.white, 0.15) : '#e5e7eb'}`,
  borderRadius: '8px',
  padding: '10px 12px',
  cursor: 'pointer',
  transition: 'all 0.2s',
  boxShadow: isOpen ? '0 4px 12px rgba(53, 84, 209, 0.15)' : 'none',
  backgroundColor: isOpen ? alpha(theme.palette.primary.main, 0.02) : theme.palette.background.paper,
  "&:hover": {
    boxShadow: '0 2px 8px rgba(53, 84, 209, 0.12)',
    borderColor: theme.palette.primary.main,
    backgroundColor: alpha(theme.palette.primary.main, 0.02),
  }
}));

const DropdownMenu = styled(Paper)(({ theme }) => ({
  position: 'absolute',
  zIndex: 1000,
  marginTop: '2px',
  boxShadow: '0 6px 20px rgba(0,0,0,0.15)',
  borderRadius: '8px',
  backgroundColor: theme.palette.background.paper,
  width: 'calc(100% + 6px)',
  left: '-2px',
  maxHeight: '250px',
  overflowY: 'auto',
  border: `1px solid ${alpha(theme.palette.primary.main, 0.2)}`,
}));

const TicketItem = styled(Box)(({ theme, isSelected }) => ({
  padding: '12px',
  cursor: 'pointer',
  backgroundColor: isSelected ? alpha(theme.palette.primary.main, 0.08) : theme.palette.background.paper,
  borderBottom: `1px solid ${theme.palette.grey[100]}`,
  display: 'flex',
  alignItems: 'center',
  '&:last-child': {
    borderBottom: 'none'
  },
  '&:hover': {
    backgroundColor: alpha(theme.palette.primary.main, 0.05)
  },
  transition: 'background-color 0.2s'
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

const StyledBadge = styled(Badge)(({ theme }) => ({
  '& .MuiBadge-badge': {
    right: -3,
    top: -2,
    padding: '0 4px',
  },
}));

const AnimatedAvatar = styled(Avatar)(({ theme }) => ({
  backgroundColor: theme.palette.primary.main,
  transition: 'all 0.3s',
  '&:hover': {
    transform: 'rotate(10deg)',
  }
}));

const TicketSelection = ({
  ticketOptions,
  selectedTicket,
  setSelectedTicket,
  isModalOpen,
  setIsModalOpen,
  nriStatus,
  setNriStatus
}) => {
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [textModalContent, setTextModalContent] = useState({ title: "", content: "", isOpen: false });
  const dropdownRef = useRef(null);

  // Get currency information from Redux store
  const currencyCode = useSelector((state) => state.auth.currencyCode) || "SGD";
  const exchangeRate = useSelector((state) => state.auth.exchangeRate) || 1;
  const usdExchangeRate =
    useSelector((state) => state.auth.usdExchangeRate) || 1;

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsDropdownOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const handleOpenModal = (ticket) => {
    setSelectedTicket(ticket);
    setIsModalOpen(true);
    setIsDropdownOpen(false);
  };

  const handleConfirmSelection = () => {
    // Log the selected ticket for debugging
    console.log("Confirming ticket selection:", selectedTicket);
    
    // Make sure the nriStatus state is properly updated in the parent component
    const currentStatus = nriStatus;
    console.log("Confirming with NRI status:", currentStatus);
    
    // Ensure the selected ticket is properly set in the parent component
    if (selectedTicket) {
      setSelectedTicket(selectedTicket);
    }
    
    setIsModalOpen(false);
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
  const getPrice = (priceField) => {
    if (!selectedTicket) return 0;
    
    // For NRI prices
    if (nriStatus === "nri") {
      switch (priceField) {
        case "adult":
          return selectedTicket.dmc_adult_price_nri !== null ? selectedTicket.dmc_adult_price_nri : 0;
        case "child":
          return selectedTicket.dmc_child_price_nri !== null ? selectedTicket.dmc_child_price_nri : 0;
        case "senior":
          return selectedTicket.dmc_senior_price_nri !== null ? selectedTicket.dmc_senior_price_nri : 0;
        default:
          return 0;
      }
    }
    
    // For residential prices
    switch (priceField) {
      case "adult":
        return selectedTicket.dmc_adult_price || 0;
      case "child":
        return selectedTicket.dmc_child_price || 0;
      case "senior":
        return selectedTicket.dmc_senior_price || 0;
      default:
        return 0;
    }
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
          return <NotificationsIcon fontSize="small" color="warning" sx={{ mr: 1 }} />;
        case "terms":
          return <SecurityIcon fontSize="small" color="error" sx={{ mr: 1 }} />;
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

  // Handle NRI status changes with immediate parent notification
  const handleNriStatusChange = (event) => {
    console.log("Changing NRI status to:", event.target.value);
    setNriStatus(event.target.value);
  };

  const stripPTags = (text) => {
    if (!text) return "";
    
    // Create a temporary div to parse HTML
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = text;
    
    // Get the text content, which automatically strips HTML tags
    let cleanedText = tempDiv.textContent || tempDiv.innerText;
    
    // Clean up any remaining HTML entities
    cleanedText = cleanedText
      .replace(/&nbsp;/g, ' ')
      .replace(/&quot;/g, '"')
      .replace(/&amp;/g, '&')
      .replace(/&lt;/g, '<')
      .replace(/&gt;/g, '>')
      .replace(/&#39;/g, "'");
    
    // Remove extra whitespace and trim
    cleanedText = cleanedText.replace(/\s+/g, ' ').trim();
    
    return cleanedText;
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

  return (
    <>
      <Box className="text-15 text-light-1 ls-2 lh-16" ref={dropdownRef} sx={{ position: 'relative' }}>
        <Tooltip 
          title={selectedTicket ? "Change ticket selection" : "Select a ticket package"} 
          arrow 
          placement="top"
        >
          <StyledDropdown 
            isOpen={isDropdownOpen}
            onClick={() => setIsDropdownOpen(!isDropdownOpen)}
          >
            <Typography 
              sx={{ 
                fontWeight: 500, 
                color: 'text.primary',
                flexGrow: 1
              }}
            >
              {selectedTicket
                ? selectedTicket.ticket_name
                : "Select Ticket Package"}
            </Typography>
            {isDropdownOpen ? 
              <KeyboardArrowUpIcon color="action" /> : 
              <KeyboardArrowDownIcon color="action" />
            }
          </StyledDropdown>
        </Tooltip>

        {isDropdownOpen && ticketOptions.length > 0 && (
          <Fade in={isDropdownOpen}>
            <DropdownMenu elevation={4}>
              {ticketOptions.map((ticket, index) => (
                <TicketItem
                  key={`${ticket.ticket_id}-${nriStatus}`}
                  isSelected={selectedTicket?.ticket_id === ticket.ticket_id}
                  onClick={() => handleOpenModal(ticket)}
                  sx={{
                    borderBottom: index < ticketOptions.length - 1 ? 1 : 0,
                    borderColor: 'divider'
                  }}
                >
                  <Box width="100%">
                    <Box display="flex" alignItems="center">
                      <Typography
                        variant="body2"
                        sx={{
                          fontWeight: selectedTicket?.ticket_id === ticket.ticket_id ? 600 : 500
                        }}
                      >
                        {ticket.ticket_name}
                      </Typography>
                    </Box>
                    <Box display="flex" alignItems="center" mt={0.5}>
                      <Box mr={1}>
                        <Typography
                          variant="caption"
                          color="primary"
                          sx={{ fontWeight: 500 }}
                        >
                          Adult: {formatPrice(nriStatus === "nri" ? 
                            (ticket.dmc_adult_price_nri !== null ? ticket.dmc_adult_price_nri : 0) : 
                            ticket.dmc_adult_price, "main")}
                        </Typography>
                      </Box>
                      <Typography variant="caption" color="text.secondary" sx={{ mx: 0.5 }}>|</Typography>
                      <Box mr={1}>
                        <Typography
                          variant="caption"
                          color="success.main"
                          sx={{ fontWeight: 500 }}
                        >
                          Child: {formatPrice(nriStatus === "nri" ? 
                            (ticket.dmc_child_price_nri !== null ? ticket.dmc_child_price_nri : 0) : 
                            ticket.dmc_child_price, "main")}
                        </Typography>
                      </Box>
                      <Typography variant="caption" color="text.secondary" sx={{ mx: 0.5 }}>|</Typography>
                      <Box>
                        <Typography
                          variant="caption"
                          color="warning.main"
                          sx={{ fontWeight: 500 }}
                        >
                          Senior: {formatPrice(nriStatus === "nri" ? 
                            (ticket.dmc_senior_price_nri !== null ? ticket.dmc_senior_price_nri : 0) : 
                            ticket.dmc_senior_price, "main")}
                        </Typography>
                      </Box>
                    </Box>
                  </Box>
                </TicketItem>
              ))}
            </DropdownMenu>
          </Fade>
        )}
      </Box>

      {/* Modal for ticket selection */}
      <Dialog
        open={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        maxWidth="lg"
        fullWidth
        TransitionComponent={Zoom}
      >
        <DialogTitle>
          <Box display="flex" alignItems="center" justifyContent="space-between">
            <Box display="flex" alignItems="center">
              <ConfirmationNumberIcon color="primary" sx={{ mr: 1.5, fontSize: 28 }} />
              <Typography variant="h6" component="span">
                Confirm Ticket Selection
              </Typography>
              {nriStatus === "nri" ? (
                <Chip 
                icon={<LanguageIcon />}
                label={nriStatus === "nri" ? "Foreigner Pricing" : "Local Pricing"}
                color="primary" 
                size="small" 
                sx={{ ml: 1.5 }} 
              />
              ) : (
                <Chip 
                  icon={<LanguageIcon />}
                  label={nriStatus === "nri" ? "Foreigner Pricing" : "Local Pricing"}
                  color="primary" 
                  size="small" 
                  sx={{ ml: 1.5 }} 
                />
              )}
            </Box>
            <Tooltip title="Close" arrow>
              <Button
                color="inherit"
                size="small"
                onClick={() => setIsModalOpen(false)}
                sx={{ minWidth: 'auto' }}
              >
                <CancelIcon />
              </Button>
            </Tooltip>
          </Box>
        </DialogTitle>
        
        <DialogContent dividers>
          {selectedTicket && (
            <Box px={1} py={2}>
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
                    {selectedTicket.ticket_name}
                  </Typography>
                </Box>
              </Box>

              <Grid container spacing={2}>
                <Grid item xs={12} md={4}>
                  {selectedTicket.description && renderDescription(stripPTags(selectedTicket.description), "description")}
                </Grid>
                <Grid item xs={12} md={4}>
                  {selectedTicket.remarks && renderDescription(stripPTags(selectedTicket.remarks), "remarks")}
                </Grid>
                <Grid item xs={12} md={4}>
                  {selectedTicket.terms_conditions && renderDescription(stripPTags(selectedTicket.terms_conditions), "terms")}
                </Grid>
              </Grid>

              {/* Price Type Selection Radio Buttons */}
              <Paper 
                variant="outlined" 
                sx={{ 
                  mt: 3,
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

              <Box 
                textAlign="center" 
                mb={2} 
                py={1} 
                bgcolor={alpha('#3554D1', 0.05)} 
                borderRadius={1}
              >
                <Typography variant="h6" display="flex" alignItems="center" justifyContent="center">
                  <AttachMoneyIcon sx={{ mr: 1 }} />
                  {nriStatus === "residential" ? "Local Prices" : "Foreigner Prices"}
                </Typography>
              </Box>

              <Grid container spacing={2}>
                {['adult', 'child', 'senior'].map(priceType => (
                  <Grid item xs={12} md={4} key={`${priceType}-${nriStatus}`}>
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
                            {formatPrice(getPrice(priceType), "main")}
                          </Typography>
                          
                          <Divider sx={{ mb: 1.5 }} />
                          
                          <Box>
                            {currencyCode !== "USD" && (
                              <Box display="flex" alignItems="center" justifyContent="space-between" mb={0.5}>
                                {/* <Typography variant="body2" color="text.secondary">USD:</Typography> */}
                                <Typography variant="body2" fontWeight={500}>
                                  {formatPrice(getPrice(priceType), "usd")}
                                </Typography>
                              </Box>
                            )}

                            {currencyCode !== "SGD" && (
                              <Box display="flex" alignItems="center" justifyContent="space-between">
                                {/* <Typography variant="body2" color="text.secondary">SGD:</Typography> */}
                                <Typography variant="body2" fontWeight={500}>
                                  {formatPrice(getPrice(priceType), "sgd")}
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
            </Box>
          )}
        </DialogContent>
        
        <DialogActions sx={{ p: 2 }}>
          <Button 
            variant="outlined" 
            color="primary" 
            onClick={() => setIsModalOpen(false)}
            startIcon={<CancelIcon />}
          >
            Cancel
          </Button>
          <Button 
            variant="contained" 
            color="primary" 
            onClick={handleConfirmSelection}
            startIcon={<CheckCircleIcon />}
          >
            Confirm Selection
          </Button>
        </DialogActions>
      </Dialog>

      {/* New dialog for displaying full text content */}
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
        <DialogContent dividers>
          <Box p={1}>
            <Typography variant="body1">{textModalContent.content}</Typography>
          </Box>
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
    </>
  );
};

export default TicketSelection;
