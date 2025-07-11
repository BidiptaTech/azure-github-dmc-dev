import React, { useState, useRef, useEffect } from "react";
import { useSelector } from "react-redux";
import { 
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
  IconButton,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Avatar,

 
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
import CloseIcon from '@mui/icons-material/Close';
import ListIcon from '@mui/icons-material/List';
import PackageIcon from '@mui/icons-material/Inventory';

import { styled, alpha } from "@mui/material/styles";
import { capitalizeWords } from '../../../utils/textUtils';

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

// Sidebar styles for the modal
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





const TicketSelection = ({
  ticketOptions,
  selectedTicket,
  setSelectedTicket,
  isModalOpen,
  setIsModalOpen,
  nriStatus,
  setNriStatus,
  packages = []
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
    // Add package_type to the ticket object
    const ticketWithType = {
      ...ticket,
      package_type: ticket.type === 'package' ? 1 : 0,
      package_attraction_id: ticket.type === 'package' ? ticket.package_attraction_id : null
    };
    
    setSelectedTicket(ticketWithType);
    // Add package_type to the ticket object
 
    
    setSelectedTicket(ticketWithType);
    // Add package_type to the ticket object
 
    
    setSelectedTicket(ticketWithType);
    setIsModalOpen(true);
    setIsDropdownOpen(false);
  };

  const handleConfirmSelection = () => {
    if (selectedTicket) {
      const finalTicket = {
        ...selectedTicket,
        package_type: isPackage(selectedTicket) ? 1 : 0,
        package_attraction_id: isPackage(selectedTicket) ? selectedTicket.package_attraction_id : null
      };
      
      setSelectedTicket(finalTicket);
    setIsModalOpen(false);
    }
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


  // Get all available options (tickets + packages)
  const allOptions = [
    ...ticketOptions.map(ticket => ({ 
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
      packageDetails: pkg, // Store the full package data
      package_attraction_id: pkg.package_attraction_id || null
    }))
  ];

  // Function to check if selected item is a package
  const isPackage = (item) => {
    return item && item.type === 'attraction_package';
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
            <Box sx={{ display: 'flex', alignItems: 'center', flexGrow: 1 }}>
              {selectedTicket && (
                <Box sx={{ display: 'flex', alignItems: 'center', mr: 1 }}>
                  {isPackage(selectedTicket) ? (
                    <PackageIcon sx={{ color: 'secondary.main', fontSize: 20 }} />
                  ) : (
                    <ConfirmationNumberIcon sx={{ color: 'primary.main', fontSize: 20 }} />
                  )}
                </Box>
              )}
              <Typography 
                sx={{ 
                  fontWeight: 500, 
                  color: 'text.primary',
                  flexGrow: 1
                }}
              >
                {selectedTicket
                  ? capitalizeWords(selectedTicket.ticket_name)
                  : "Select Ticket Package"}
              </Typography>
            </Box>
            {isDropdownOpen ? 
              <KeyboardArrowUpIcon color="action" /> : 
              <KeyboardArrowDownIcon color="action" />
            }
          </StyledDropdown>
        </Tooltip>

        {isDropdownOpen && allOptions.length > 0 && (
          <Fade in={isDropdownOpen}>
            <DropdownMenu elevation={4}>
              {allOptions.map((item, index) => (
                <TicketItem
                  key={`${item.ticket_id}-${nriStatus}`}
                  isSelected={selectedTicket?.ticket_id === item.ticket_id}
                  onClick={() => handleOpenModal(item)}
                  sx={{
                    borderBottom: index < allOptions.length - 1 ? 1 : 0,
                    borderColor: 'divider'
                  }}
                >
                  <Box width="100%">
                    <Box display="flex" alignItems="center">
                      {item.type === 'attraction_package' && (
                        <Chip 
                          size="small" 
                          label="Package" 
                          color="secondary" 
                          sx={{ mr: 1, height: '18px', fontSize: '10px' }} 
                        />
                      )}
                    
                      <Typography
                        variant="body2"
                        sx={{
                          fontWeight: selectedTicket?.ticket_id === item.ticket_id ? 600 : 500
                        }}
                      >
                        {capitalizeWords(item.ticket_name)}
                      </Typography>
                    </Box>
                    <Box display="flex" alignItems="center" mt={0.5}>
                      <Box mr={1}>
                        <Typography
                          variant="caption"
                          color="primary"
                          sx={{ fontWeight: 500 }}
                        >
                          Adult: {formatPrice(nriStatus === "nri" && !isPackage(item) ? 
                            (item.dmc_adult_price_nri !== null ? item.dmc_adult_price_nri : 0) : 
                            item.dmc_adult_price, "main")}
                        </Typography>
                      </Box>
                      <Typography variant="caption" color="text.secondary" sx={{ mx: 0.5 }}>|</Typography>
                      <Box mr={1}>
                        <Typography
                          variant="caption"
                          color="success.main"
                          sx={{ fontWeight: 500 }}
                        >
                          Child: {formatPrice(nriStatus === "nri" && !isPackage(item) ? 
                            (item.dmc_child_price_nri !== null ? item.dmc_child_price_nri : 0) : 
                            item.dmc_child_price, "main")}
                        </Typography>
                      </Box>
                      <Typography variant="caption" color="text.secondary" sx={{ mx: 0.5 }}>|</Typography>
                      <Box>
                        <Typography
                          variant="caption"
                          color="warning.main"
                          sx={{ fontWeight: 500 }}
                        >
                          Senior: {formatPrice(nriStatus === "nri" && !isPackage(item) ? 
                            (item.dmc_senior_price_nri !== null ? item.dmc_senior_price_nri : 0) : 
                            item.dmc_senior_price, "main")}
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

      {/* Modal with right sidebar for ticket selection */}
      <Dialog
        open={isModalOpen}
        onClose={() => setIsModalOpen(false)}
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
          <IconButton onClick={() => setIsModalOpen(false)} size="small">
            <CloseIcon />
          </IconButton>
        </DialogTitle>
        
         
        <DialogContent sx={{ p: 0, display: 'flex', height: '600px' }}>   
          {selectedTicket && (
            <>
              {/* Main Content Area */}
              <Box sx={{ p: 3, flex: 1, overflowY: 'auto' }}>
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
                              {isPackage(selectedTicket) ?
                                (priceType === 'adult' ? 
                                  formatPrice(selectedTicket.dmc_adult_price, "main") :
                                priceType === 'child' ? 
                                  formatPrice(selectedTicket.dmc_child_price, "main") :
                                  formatPrice(selectedTicket.dmc_senior_price, "main")) :
                                formatPrice(getPrice(priceType), "main")
                              }
                          </Typography>
                          
                          <Divider sx={{ mb: 1.5 }} />
                          
                          <Box>
                            {currencyCode !== "USD" && (
                              <Box display="flex" alignItems="center" justifyContent="space-between" mb={0.5}>
                                <Typography variant="body2" fontWeight={500}>
                                    {isPackage(selectedTicket) ?
                                      (priceType === 'adult' ? 
                                        formatPrice(selectedTicket.dmc_adult_price, "usd") :
                                      priceType === 'child' ? 
                                        formatPrice(selectedTicket.dmc_child_price, "usd") :
                                        formatPrice(selectedTicket.dmc_senior_price, "usd")) :
                                      formatPrice(getPrice(priceType), "usd")
                                    }
                                </Typography>
                              </Box>
                            )}

                            {currencyCode !== "SGD" && (
                              <Box display="flex" alignItems="center" justifyContent="space-between">
                                <Typography variant="body2" fontWeight={500}>
                                    {isPackage(selectedTicket) ?
                                      (priceType === 'adult' ? 
                                        formatPrice(selectedTicket.dmc_adult_price, "sgd") :
                                      priceType === 'child' ? 
                                        formatPrice(selectedTicket.dmc_child_price, "sgd") :
                                        formatPrice(selectedTicket.dmc_senior_price, "sgd")) :
                                      formatPrice(getPrice(priceType), "sgd")
                                    }
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
                      Attraction Packages
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
                        {item.type === 'attraction_package' && (
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
                          Adult: {formatPrice(nriStatus === "nri" && !isPackage(item) ? 
                            (item.dmc_adult_price_nri !== null ? item.dmc_adult_price_nri : 0) : 
                            item.dmc_adult_price, "main")}
                        </Typography>
                        
                        <Typography variant="caption" sx={{ display: 'flex', alignItems: 'center', mb: 0.5 }}>
                          <ChildCareIcon fontSize="small" sx={{ mr: 0.5, color: 'success.main', fontSize: 14 }} />
                          Child: {formatPrice(nriStatus === "nri" && !isPackage(item) ? 
                            (item.dmc_child_price_nri !== null ? item.dmc_child_price_nri : 0) : 
                            item.dmc_child_price, "main")}
                        </Typography>
                        
                        <Typography variant="caption" sx={{ display: 'flex', alignItems: 'center' }}>
                          <AccessibilityNewIcon fontSize="small" sx={{ mr: 0.5, color: 'warning.main', fontSize: 14 }} />
                          Senior: {formatPrice(nriStatus === "nri" && !isPackage(item) ? 
                            (item.dmc_senior_price_nri !== null ? item.dmc_senior_price_nri : 0) : 
                            item.dmc_senior_price, "main")}
                        </Typography>
                      </Box>
                      
                      {item.type === 'attraction_package' && item.attractions && (
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

      {/* Dialog for displaying full text content */}
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
    </>
  );
};

export default TicketSelection;
