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
  Chip,
  Tooltip,
  Zoom,
  Fade,
  IconButton,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,

 
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
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import ArticleIcon from '@mui/icons-material/Article';
import CloseIcon from '@mui/icons-material/Close';
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
  maxHeight: '150px',
  height: '300px',
  overflowY: 'auto',
  border: `1px solid ${alpha(theme.palette.primary.main, 0.2)}`,
  // Custom scrollbar styling
  '&::-webkit-scrollbar': {
    width: '8px',
  },
  '&::-webkit-scrollbar-track': {
    background: alpha(theme.palette.grey[300], 0.3),
    borderRadius: '4px',
  },
  '&::-webkit-scrollbar-thumb': {
    background: alpha(theme.palette.primary.main, 0.6),
    borderRadius: '4px',
    '&:hover': {
      background: alpha(theme.palette.primary.main, 0.8),
    },
  },
  '&::-webkit-scrollbar-thumb:hover': {
    background: alpha(theme.palette.primary.main, 0.8),
  },
  // Firefox scrollbar styling
  scrollbarWidth: 'thin',
  scrollbarColor: `${alpha(theme.palette.primary.main, 0.6)} ${alpha(theme.palette.grey[300], 0.3)}`,
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
  marginTop: theme.spacing(0.75),
  padding: theme.spacing(0.75, 1),
  backgroundColor: alpha(theme.palette.primary.main, 0.05),
  borderRadius: 4,
  position: 'relative',
  overflow: 'hidden',
  '&:before': {
    content: '""',
    position: 'absolute',
    top: 0,
    left: 0,
    width: '2px',
    height: '100%',
    backgroundColor: theme.palette.primary.main,
  }
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
  // Add PriceHide selector
  const PriceHide = useSelector((state) => state.auth.PriceHide);

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

  // Handle description truncation and toggle
  const renderDescription = (description, type = "description") => {
    if (!description) return null;
    
    const words = description.split(/\s+/);
    const wordCount = words.length;
    const wordLimit = 10;
    
    const getIcon = () => {
      switch (type) {
        case "description":
          return <InfoIcon sx={{ mr: 0.5, fontSize: 15 }} color="primary" />;
        case "remarks":
          return <NotificationsIcon sx={{ mr: 0.5, fontSize: 15 }} color="warning" />;
        case "terms":
          return <SecurityIcon sx={{ mr: 0.5, fontSize: 15 }} color="error" />;
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
          <Box display="flex" alignItems="center" mb={0.25}>
            {getIcon()}
            <Typography sx={{ fontSize: 13, fontWeight: 600, lineHeight: 1.3 }}>{getTitle()}</Typography>
          </Box>
          <Typography sx={{ fontSize: 12, lineHeight: 1.4 }}>{description}</Typography>
        </ContentBox>
      );
    }
    
    const truncatedText = words.slice(0, wordLimit).join(" ");
    
    return (
        <ContentBox>
        <Box display="flex" alignItems="center" mb={0.25}>
          {getIcon()}
          <Typography sx={{ fontSize: 13, fontWeight: 600, lineHeight: 1.3 }}>{getTitle()}</Typography>
        </Box>
        <Typography sx={{ fontSize: 12, lineHeight: 1.4 }}>
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
            mt: 0.25, 
            minWidth: 'auto', 
            minHeight: 0,
            textTransform: 'none',
            fontSize: 12,
            lineHeight: 1.3
          }}
          endIcon={<ArticleIcon sx={{ fontSize: 14 }} />}
        >
          Show more
        </Button>
      </ContentBox>
    );
  };

  // Handle NRI status changes with immediate parent notification
  const handleNriStatusChange = (event) => {
    // console.log("Changing NRI status to:", event.target.value);
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

  const amountFor = (item, priceType) => {
    if (!item) return 0;
    const nri = nriStatus === "nri" && !isPackage(item);
    if (priceType === "adult") {
      return nri
        ? (item.dmc_adult_price_nri !== null ? item.dmc_adult_price_nri : 0)
        : (item.dmc_adult_price || 0);
    }
    if (priceType === "child") {
      return nri
        ? (item.dmc_child_price_nri !== null ? item.dmc_child_price_nri : 0)
        : (item.dmc_child_price || 0);
    }
    return nri
      ? (item.dmc_senior_price_nri !== null ? item.dmc_senior_price_nri : 0)
      : (item.dmc_senior_price || 0);
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
                    {/* Price display - only show if PriceHide is "0" */}
                    {PriceHide === "0" && (
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
                    )}
                    
                    {/* Show message when prices are hidden in dropdown */}
                    {PriceHide !== "0" && (
                      <Box mt={0.5}>
                        <Typography 
                          variant="caption" 
                          color="text.secondary"
                          sx={{ fontStyle: 'italic', fontSize: '11px' }}
                        >
                          Price Hide
                        </Typography>
                      </Box>
                    )}
                  </Box>
                </TicketItem>
              ))}
            </DropdownMenu>
          </Fade>
        )}
      </Box>

      {/* Compact ticket confirmation modal */}
      <Dialog
        open={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        maxWidth="sm"
        fullWidth
        TransitionComponent={Zoom}
        PaperProps={{
          sx: {
            maxWidth: 520,
            width: "100%",
            m: 1.5,
            borderRadius: 1,
          },
        }}
      >
        <DialogTitle
          sx={{
            px: 1.75,
            py: 1,
            display: "flex",
            alignItems: "center",
            gap: 1,
            borderBottom: "1px solid",
            borderColor: "divider",
          }}
        >
          <ConfirmationNumberIcon color="primary" sx={{ fontSize: 18 }} />
          <Box sx={{ flex: 1, minWidth: 0 }}>
            <Typography sx={{ fontSize: 14, fontWeight: 700, lineHeight: 1.2 }}>
              {isPackage(selectedTicket) ? "Confirm package" : "Confirm ticket"}
            </Typography>
            {selectedTicket && (
              <Typography noWrap sx={{ fontSize: 12, color: "text.secondary", lineHeight: 1.3 }}>
                {capitalizeWords(selectedTicket.ticket_name)}
                {isPackage(selectedTicket)
                  ? ` · ${selectedTicket.attractions?.length || 0} attractions`
                  : ""}
              </Typography>
            )}
          </Box>
          <IconButton onClick={() => setIsModalOpen(false)} size="small" sx={{ p: 0.25 }}>
            <CloseIcon sx={{ fontSize: 18 }} />
          </IconButton>
        </DialogTitle>

        <DialogContent
          sx={{
            px: 1.75,
            pb: 1.25,
            "&&": { pt: 1.25 },
          }}
        >
          {selectedTicket && (
            <>
              {!isPackage(selectedTicket) && (
                <Box sx={{ display: "flex", alignItems: "center", minHeight: 28, mb: 0.75 }}>
                  <Typography sx={{ fontSize: 12, color: "text.secondary", mr: 1 }}>
                    Price
                  </Typography>
                  <RadioGroup
                    row
                    name="priceType"
                    value={nriStatus}
                    onChange={handleNriStatusChange}
                    sx={{
                      "& .MuiFormControlLabel-root": { mr: 1.25, ml: 0 },
                      "& .MuiRadio-root": { p: 0.35 },
                      "& .MuiSvgIcon-root": { fontSize: 16 },
                    }}
                  >
                    <FormControlLabel
                      value="residential"
                      control={<Radio size="small" color="primary" />}
                      label={<Typography sx={{ fontSize: 13, lineHeight: 1.2 }}>Local</Typography>}
                    />
                    <FormControlLabel
                      value="nri"
                      control={<Radio size="small" color="primary" />}
                      label={<Typography sx={{ fontSize: 13, lineHeight: 1.2 }}>Foreigner</Typography>}
                    />
                  </RadioGroup>
                </Box>
              )}

              <Box
                sx={{
                  display: "grid",
                  gridTemplateColumns: "repeat(3, minmax(0, 1fr))",
                  gap: 0.75,
                }}
              >
                {["adult", "child", "senior"].map((priceType) => (
                  <Box
                    key={`${priceType}-${nriStatus}`}
                    sx={{
                      border: "1px solid",
                      borderColor: "divider",
                      borderRadius: 1,
                      px: 1,
                      py: 0.75,
                      minWidth: 0,
                    }}
                  >
                    <Box sx={{ display: "flex", alignItems: "center", gap: 0.4, mb: 0.25 }}>
                      <Box
                        sx={{
                          display: "flex",
                          color: `${getPriceColor(priceType)}.main`,
                          "& svg": { fontSize: 15 },
                        }}
                      >
                        {getPriceIcon(priceType)}
                      </Box>
                      <Typography
                        sx={{
                          fontSize: 12,
                          fontWeight: 600,
                          lineHeight: 1.2,
                          textTransform: "capitalize",
                        }}
                      >
                        {priceType}
                      </Typography>
                    </Box>
                    {PriceHide === "0" ? (
                      <>
                        <Typography
                          sx={{
                            fontSize: 14,
                            fontWeight: 700,
                            lineHeight: 1.25,
                            color: `${getPriceColor(priceType)}.main`,
                          }}
                        >
                          {formatPrice(amountFor(selectedTicket, priceType), "main")}
                        </Typography>
                        {currencyCode !== "USD" && (
                          <Typography sx={{ fontSize: 11, lineHeight: 1.3, color: "text.secondary" }}>
                            {formatPrice(amountFor(selectedTicket, priceType), "usd")}
                          </Typography>
                        )}
                        {currencyCode !== "SGD" && (
                          <Typography sx={{ fontSize: 11, lineHeight: 1.3, color: "text.secondary" }}>
                            {formatPrice(amountFor(selectedTicket, priceType), "sgd")}
                          </Typography>
                        )}
                      </>
                    ) : (
                      <Typography sx={{ fontSize: 12, fontStyle: "italic", color: "text.secondary" }}>
                        Price Hide
                      </Typography>
                    )}
                  </Box>
                ))}
              </Box>

              {isPackage(selectedTicket) && selectedTicket.attractions?.length > 0 && (
                <Box sx={{ mt: 1 }}>
                  <Typography sx={{ fontSize: 13, fontWeight: 600, lineHeight: 1.3, mb: 0.5 }}>
                    Included
                  </Typography>
                  <Box sx={{ display: "flex", gap: 0.75, overflowX: "auto", pb: 0.25 }}>
                    {selectedTicket.attractions.map((attraction) => (
                      <Box
                        key={`attraction-${attraction.attraction_id}`}
                        sx={{ width: 96, flex: "0 0 96px" }}
                      >
                        <Box
                          component="img"
                          src={attraction.master_image}
                          alt={attraction.name}
                          sx={{
                            width: 96,
                            height: 52,
                            objectFit: "cover",
                            borderRadius: 0.5,
                            display: "block",
                          }}
                        />
                        <Typography noWrap sx={{ fontSize: 11, lineHeight: 1.3, mt: 0.25 }}>
                          {attraction.name}
                        </Typography>
                      </Box>
                    ))}
                  </Box>
                </Box>
              )}

              {selectedTicket.description &&
                renderDescription(stripPTags(selectedTicket.description), "description")}
              {!isPackage(selectedTicket) &&
                selectedTicket.remarks &&
                renderDescription(stripPTags(selectedTicket.remarks), "remarks")}
              {!isPackage(selectedTicket) &&
                selectedTicket.terms_conditions &&
                renderDescription(stripPTags(selectedTicket.terms_conditions), "terms")}

              <Typography sx={{ fontSize: 13, fontWeight: 600, lineHeight: 1.3, mt: 1, mb: 0.5 }}>
                Options
              </Typography>
              <Box
                sx={{
                  maxHeight: 140,
                  overflowY: "auto",
                  border: "1px solid",
                  borderColor: "divider",
                  borderRadius: 1,
                }}
              >
                {allOptions.map((item) => {
                  const isSelected = selectedTicket?.ticket_id === item.ticket_id;
                  return (
                    <Box
                      key={item.ticket_id}
                      onClick={() => setSelectedTicket(item)}
                      sx={{
                        px: 1,
                        py: 0.6,
                        display: "flex",
                        alignItems: "center",
                        gap: 0.6,
                        cursor: "pointer",
                        borderBottom: "1px solid",
                        borderColor: "divider",
                        bgcolor: isSelected ? alpha("#3554D1", 0.08) : "transparent",
                        "&:last-child": { borderBottom: "none" },
                        "&:hover": { bgcolor: alpha("#3554D1", 0.05) },
                      }}
                    >
                      {isSelected && (
                        <CheckCircleIcon sx={{ fontSize: 14, color: "primary.main" }} />
                      )}
                      {item.type === "attraction_package" && (
                        <Typography
                          sx={{ fontSize: 10, fontWeight: 700, color: "secondary.main", lineHeight: 1 }}
                        >
                          PKG
                        </Typography>
                      )}
                      <Typography
                        noWrap
                        sx={{
                          flex: 1,
                          minWidth: 0,
                          fontSize: 12,
                          lineHeight: 1.3,
                          fontWeight: isSelected ? 600 : 400,
                          color: isSelected ? "primary.main" : "text.primary",
                        }}
                      >
                        {capitalizeWords(item.ticket_name)}
                      </Typography>
                      {PriceHide === "0" ? (
                        <Typography
                          sx={{
                            fontSize: 11,
                            color: "text.secondary",
                            whiteSpace: "nowrap",
                            lineHeight: 1.3,
                          }}
                        >
                          {formatPrice(amountFor(item, "adult"), "main")}
                        </Typography>
                      ) : (
                        <Typography sx={{ fontSize: 11, fontStyle: "italic", color: "text.secondary" }}>
                          Price Hide
                        </Typography>
                      )}
                    </Box>
                  );
                })}
              </Box>
            </>
          )}
        </DialogContent>

        <DialogActions
          sx={{
            px: 1.75,
            py: 1,
            gap: 1,
            borderTop: "1px solid",
            borderColor: "divider",
          }}
        >
          <Button
            size="small"
            variant="outlined"
            color="primary"
            onClick={() => setIsModalOpen(false)}
            sx={{ fontSize: 13, py: 0.4, minHeight: 32, textTransform: "none", px: 1.5 }}
          >
            Cancel
          </Button>
          <Button
            size="small"
            variant="contained"
            color="primary"
            onClick={handleConfirmSelection}
            sx={{ fontSize: 13, py: 0.4, minHeight: 32, textTransform: "none", px: 1.5 }}
          >
            Confirm
          </Button>
        </DialogActions>
      </Dialog>

      <Dialog
        open={textModalContent.isOpen}
        onClose={() => setTextModalContent({ ...textModalContent, isOpen: false })}
        maxWidth="sm"
        fullWidth
        TransitionComponent={Zoom}
        PaperProps={{ sx: { maxWidth: 480, m: 1.5 } }}
      >
        <DialogTitle sx={{ px: 1.75, py: 1, "&&": { pb: 0.75 } }}>
          <Box display="flex" alignItems="center">
            <ArticleIcon color="primary" sx={{ mr: 0.75, fontSize: 18 }} />
            <Typography sx={{ fontSize: 14, fontWeight: 700 }}>{textModalContent.title}</Typography>
          </Box>
        </DialogTitle>
        <DialogContent sx={{ px: 1.75, pb: 1, "&&": { pt: 0.75 } }}>
          <Typography sx={{ fontSize: 13, lineHeight: 1.45 }}>{textModalContent.content}</Typography>
        </DialogContent>
        <DialogActions sx={{ px: 1.75, py: 1 }}>
          <Button
            size="small"
            variant="contained"
            color="primary"
            onClick={() => setTextModalContent({ ...textModalContent, isOpen: false })}
            sx={{ fontSize: 13, py: 0.4, minHeight: 32, textTransform: "none" }}
          >
            Close
          </Button>
        </DialogActions>
      </Dialog>

    </>
  );
};

export default TicketSelection;





