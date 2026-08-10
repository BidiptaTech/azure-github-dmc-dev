import React, { useEffect, useState, useRef } from "react";
import { createPortal } from "react-dom";
import { useSelector, useDispatch } from "react-redux";
import { fetchLists } from "@/slice/common/TourlistSlice";
import { fetchAgencies, fetchAgentList, fetchAgentListByAgency, setSelectedAgency, resetAgencies, resetAgentList } from '@/slice/common/agentListSlice';

import { 
  Container, 
  Box, 
  Typography,
  Paper,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Card,
  CardContent,
  Stack,
  Chip,
  Divider,
  Avatar,
  Fade,
  Grow,
  TextField,
  InputAdornment,
  CircularProgress
} from "@mui/material";
import { Grid, Tab, Tabs } from "@mui/material";
import {
    EventNote,
  EmailOutlined,
  CardGiftcardOutlined,
  Public,
  People,
  FilterList,
  Analytics,
  TrendingUp,
  Assessment,
  Search,
  Clear
} from "@mui/icons-material";
import BasicTabs from "./TabStatus";

import EnquiryList from "./EnquiryList";
import { fetchEnquiries } from "@/slice/enquiries/enquiryListSlice";
import PreDefinePackages from "../PreDefine-Packages";
import { setAgentId } from "@/slice/common/EditSlice";
import { AnimatedBox, StaggeredContainer } from "@/components/dashboard/DashboardAnimations";

const SalesManagerDashboard = () => {
  const dispatch = useDispatch();
    const [mainTabValue, setMainTabValue] = useState(0);
    const [searchTerm, setSearchTerm] = useState('');
    const searchInputRef = useRef(null);

      const handleMainTabChange = (event, newValue) => {
    setMainTabValue(newValue);
  };
    const {agents, agencies, selectedAgency, loading, agenciesLoading, error} = useSelector((state) => state.agentList);
    const countrylist = useSelector((state) => state.auth.user_country);
    const dmcId = useSelector((state) => state.dmc.dmcId);
    const selectedAgent = useSelector((state) => state.editing.agentId) || '';
    const [selectedCountry, setSelectedCountry] = useState('');
    const [clickedAgency, setClickedAgency] = useState(null);
    const [showAgentSubmenu, setShowAgentSubmenu] = useState(false);
    const [agencyAgents, setAgencyAgents] = useState({});
    const [loadingAgencies, setLoadingAgencies] = useState({});
    const [showAgencyDropdown, setShowAgencyDropdown] = useState(false);
    const dropdownRef = useRef(null);
    // console.log(agents,"agentlist");
    console.log("countrylist",countrylist);
    console.log("dmcId",dmcId);

        useEffect(() => {
    // Initial fetch of enquiries - will use agent ID from Redux if available
    const storedAgentId = selectedAgent;
    if (storedAgentId) {
      // If we have an agent ID in Redux, use it for initial fetch
      dispatch(fetchEnquiries({ agentId: storedAgentId, reset: true, start: 0, limit: 30 }));
      dispatch(fetchLists({ agentId: storedAgentId, reset: true }));
    } else {
      // Otherwise fetch all enquiries
      dispatch(fetchEnquiries({ reset: true, start: 0, limit: 30 }));
      dispatch(fetchLists({ reset: true }));
    }
  }, [dispatch, selectedAgent])
   const handleChange = (event) => {
      const agentId = event.target.value;
      // Store agent ID in Redux first
      dispatch(setAgentId(agentId));
      // Then fetch data with the selected agent ID
      if (agentId) {
        dispatch(fetchEnquiries({ agentId, reset: true, start: 0, limit: 30 }));
        dispatch(fetchLists({ agentId, reset: true }));
      } else {
        // If "All Agents" is selected (empty string)
        dispatch(fetchEnquiries({ reset: true, start: 0, limit: 30 }));
        dispatch(fetchLists({ reset: true }));
      }
      console.log('Selected agent:', agentId);
    };

    const handleCountryChange = async (event) => {
      const value = event.target.value;
      setSelectedCountry(value);
      
      // Reset agencies and agents when country changes
      dispatch(resetAgencies());
      dispatch(setAgentId('')); // Reset selected agent
      setAgencyAgents({}); // Clear agency agents cache
      setClickedAgency(null);
      setShowAgentSubmenu(false);
      setShowAgencyDropdown(false); // Reset dropdown state
      setLoadingAgencies({}); // Clear loading states
      
      if (value) {
        const response = await dispatch(fetchAgencies({dmcId, country: value}));
        console.log("agencies response", response);
      }
      console.log('Selected country:', value);
    };

    const handleAgencyClick = async (event, agencyId) => {
      event.preventDefault();
      event.stopPropagation();
      
      setClickedAgency(agencyId);
      setShowAgentSubmenu(true);
      // Keep main dropdown open - don't close it
      
      // Set loading state for this agency
      setLoadingAgencies(prev => ({
        ...prev,
        [agencyId]: true
      }));
      
      // Fetch agents for this agency if not already cached
      if (agencyId && !agencyAgents[agencyId]) {
        try {
          const response = await dispatch(fetchAgentListByAgency(agencyId));
          console.log("Agent response for agency", agencyId, ":", response.payload);
          if (response.payload) {
            setAgencyAgents(prev => ({
              ...prev,
              [agencyId]: response.payload
            }));
          }
        } catch (error) {
          console.error("Error fetching agents for agency", agencyId, ":", error);
        } finally {
          // Clear loading state
          setLoadingAgencies(prev => ({
            ...prev,
            [agencyId]: false
          }));
        }
      } else {
        // If already cached, just clear loading state
        setLoadingAgencies(prev => ({
          ...prev,
          [agencyId]: false
        }));
      }
    };

    const handleAgencyLeave = () => {
      // Only close if not clicking on the same agency
      if (clickedAgency) {
        setShowAgentSubmenu(false);
        setClickedAgency(null);
      }
    };

    const handleAgentSelect = (agentId) => {
      dispatch(setAgentId(agentId));
      setShowAgentSubmenu(false);
      setClickedAgency(null);
      setShowAgencyDropdown(false); // Close main dropdown when agent is selected
      
      if (agentId) {
        dispatch(fetchEnquiries({ agentId, reset: true, start: 0, limit: 30 }));
        dispatch(fetchLists({ agentId, reset: true }));
      } else {
        dispatch(fetchEnquiries({ reset: true, start: 0, limit: 30 }));
        dispatch(fetchLists({ reset: true }));
      }
      console.log('Selected agent:', agentId);
    };

    // Filter agents based on search term
    const filteredAgents = agents.filter(agent => {
      if (!searchTerm) return true;
      const searchLower = searchTerm.toLowerCase();
      return (
        agent.name.toLowerCase().includes(searchLower) ||
        (agent.company_name && agent.company_name.toLowerCase().includes(searchLower))
      );
    });

    // Handle search input change
    const handleSearchChange = (event) => {
      setSearchTerm(event.target.value);
    };

    // Clear search
    const handleClearSearch = () => {
      setSearchTerm('');
    };

    // Focus search input when dropdown opens
    useEffect(() => {
      if (searchInputRef.current) {
        const timer = setTimeout(() => {
          searchInputRef.current?.focus();
        }, 100);
        return () => clearTimeout(timer);
      }
    }, []);

    // Close submenu when clicking outside
    useEffect(() => {
      const handleClickOutside = (event) => {
        if ((showAgentSubmenu || showAgencyDropdown) && !event.target.closest('.nested-dropdown-container')) {
          setShowAgentSubmenu(false);
          setClickedAgency(null);
          setShowAgencyDropdown(false);
        }
      };

      document.addEventListener('mousedown', handleClickOutside);
      return () => {
        document.removeEventListener('mousedown', handleClickOutside);
      };
    }, [showAgentSubmenu, showAgencyDropdown]);
  // We've moved the fetchLists call to the first useEffect where we can include agentId
  function TabPanel(props) {
    const { children, value, index, ...other } = props;
  
    return (
      <div
        role="tabpanel"
        hidden={value !== index}
        id={`dashboard-tabpanel-${index}`}
        aria-labelledby={`dashboard-tab-${index}`}
        {...other}
        style={{ padding: "20px 0" }}
      >
        {value === index && <Box>{children}</Box>}
      </div>
    );
  }
function a11yProps(index) {
  return {
    id: `dashboard-tab-${index}`,
    "aria-controls": `dashboard-tabpanel-${index}`,
  };
}


  return (
    <Container maxWidth="xl" sx={{ overflow: "visible" }}>
      <StaggeredContainer>
        <AnimatedBox direction="left" delay={200}>
          <Card
            elevation={0}
            sx={{
              background: "white",
              borderRadius: "16px",
              mb: 4, // Increased margin bottom to provide more space
              overflow: "visible", // Changed from "hidden" to allow dropdowns to show
              position: "relative",
              boxShadow: "0 4px 20px rgba(0,0,0,0.08)",
              border: "1px solid rgba(0,0,0,0.05)",
              zIndex: 10, // Higher z-index to ensure dropdown appears above other cards
            }}
          >
            <CardContent sx={{ p: 4 }}>
              <Stack direction="row" justifyContent="space-between" alignItems="center" spacing={3}>
                <Fade in={true} timeout={800}>
                  <Stack direction="row" alignItems="center" spacing={3}>
                    <Avatar
                      sx={{
                        width: 56,
                        height: 56,
                        background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                        boxShadow: "0 4px 12px rgba(102, 126, 234, 0.3)",
                      }}
                    >
                      <Analytics sx={{ fontSize: 28, color: "white" }} />
                    </Avatar>
                    
                    <Stack spacing={0.5}>
                      <Typography
                        variant="h4"
                        component="h1"
                        sx={{
                          fontWeight: 700,
                          color: "text.primary",
                          fontSize: { xs: "1.5rem", sm: "1.8rem" },
                        }}
                      >
                        Management Hub
                      </Typography>
                      <Stack direction="row" alignItems="center" spacing={1}>
                        <TrendingUp sx={{ fontSize: 16, color: "text.secondary" }} />
                        <Typography
                          variant="body2"
                          sx={{
                            color: "text.secondary",
                            fontSize: "0.9rem",
                          }}
                        >
                          Monitor team performance and analyze booking trends
                        </Typography>
                      </Stack>
                    </Stack>
                  </Stack>
                </Fade>

                <Grow in={true} timeout={1000}>
                  <Stack spacing={2} alignItems="flex-end" sx={{ position: "relative", zIndex: 1, overflow: "visible" }}>
                    <Stack direction="row" alignItems="center" spacing={2}>
                      <Stack direction="row" alignItems="center" spacing={1}>
                        <People sx={{ fontSize: 18, color: "text.secondary" }} />
                        <Typography
                          variant="body2"
                          sx={{
                            color: "text.primary",
                            fontSize: "0.85rem",
                            fontWeight: 500,
                          }}
                        >
                          Team Filter
                        </Typography>
                      </Stack>
                      <Chip
                        label="Active"
                        size="small"
                        sx={{
                          background: "rgba(76, 175, 80, 0.1)",
                          color: "rgb(76, 175, 80)",
                          border: "1px solid rgba(76, 175, 80, 0.2)",
                          fontSize: "0.75rem",
                        }}
                      />
                    </Stack>
                    

                    {/* Dropdowns Container */}
                    <Stack direction="row" spacing={2} sx={{ mb: 2, position: "relative", zIndex: 1, overflow: "visible" }}>
                      {/* Country Selection Dropdown */}
                      <Paper
                        elevation={0}
                        sx={{
                          background: "#f8f9fa",
                          borderRadius: "12px",
                          border: "1px solid rgba(0, 0, 0, 0.1)",
                          minWidth: 250,
                          flex: 1,
                        }}
                      >
                      <FormControl
                        variant="outlined"
                        size="small"
                        fullWidth
                        sx={{
                          "& .MuiOutlinedInput-root": {
                            backgroundColor: "transparent",
                            "& fieldset": {
                              border: "none",
                            },
                            "&:hover fieldset": {
                              border: "none",
                            },
                            "&.Mui-focused fieldset": {
                              border: "none",
                            },
                          },
                          "& .MuiInputLabel-root": {
                            color: "text.secondary",
                            "&.Mui-focused": {
                              color: "primary.main",
                            },
                          },
                          "& .MuiSelect-select": {
                            color: "text.primary",
                            padding: "12px 16px",
                            display: "flex",
                            alignItems: "center",
                            gap: "8px",
                          },
                          "& .MuiSelect-icon": {
                            color: "text.secondary",
                          },
                        }}
                      >
                        <InputLabel id="country-select-label">
                          <Stack direction="row" alignItems="center" spacing={1}>
                            <Public sx={{ fontSize: 16 }} />
                            <span>Select Country</span>
                          </Stack>
                        </InputLabel>
                        <Select
                          labelId="country-select-label"
                          id="country-select"
                          value={selectedCountry || ""}
                          onChange={handleCountryChange}
                          label="Select Country"
                          sx={{
                            "& .MuiOutlinedInput-root": {
                              backgroundColor: "#f8fafc",
                              borderColor: "#e2e8f0",
                              "&:hover": {
                                borderColor: "#94a3b8",
                              },
                              "&.Mui-focused": {
                                borderColor: "#3b82f6",
                                boxShadow: "0 0 0 2px rgba(59, 130, 246, 0.1)",
                              },
                            },
                            "& .MuiSelect-icon": {
                              color: "#64748b",
                            },
                            "& .MuiInputLabel-root": {
                              color: "#475569",
                              "&.Mui-focused": {
                                color: "#3b82f6",
                              },
                            },
                          }}
                          MenuProps={{
                            PaperProps: {
                              sx: {
                                maxHeight: 300,
                                boxShadow: "0 10px 25px rgba(0, 0, 0, 0.15)",
                                border: "1px solid #e2e8f0",
                                borderRadius: "8px",
                                "& .MuiMenuItem-root": {
                                  fontSize: "0.9rem",
                                  padding: "12px 16px",
                                  "&:hover": {
                                    backgroundColor: "#f1f5f9",
                                  },
                                  "&.Mui-selected": {
                                    backgroundColor: "#dbeafe",
                                    color: "#1e40af",
                                    "&:hover": {
                                      backgroundColor: "#bfdbfe",
                                    },
                                  },
                                },
                              },
                            },
                          }}
                        >
                          <MenuItem value="">
                            <Stack direction="row" alignItems="center" spacing={1}>
                              <Public sx={{ fontSize: 18, color: "#3b82f6" }} />
                              <Box>
                                <Typography variant="body2" sx={{ fontWeight: 600, color: "#1e293b" }}>
                                  All Countries
                                </Typography>
                                <Typography variant="caption" sx={{ color: "#64748b", fontSize: "0.75rem" }}>
                                  View all countries
                                </Typography>
                              </Box>
                            </Stack>
                          </MenuItem>
                          {countrylist && countrylist.length > 0 ? (
                            countrylist.map((country) => (
                              <MenuItem key={country.code} value={country.name}>
                                <Stack direction="row" alignItems="center" spacing={1}>
                                  <Box
                                    sx={{
                                      width: 24,
                                      height: 24,
                                      borderRadius: "50%",
                                      backgroundColor: "#3b82f6",
                                      display: "flex",
                                      alignItems: "center",
                                      justifyContent: "center",
                                      fontSize: "0.7rem",
                                      fontWeight: 600,
                                      color: "white",
                                    }}
                                  >
                                    {country.code}
                                  </Box>
                                  <Box sx={{ minWidth: 0, flex: 1 }}>
                                    <Typography variant="body2" sx={{ fontWeight: 500, color: "#1e293b", lineHeight: 1.2 }}>
                                      {country.name}
                                    </Typography>
                                    {/* <Typography 
                                      variant="caption" 
                                      sx={{ 
                                        color: "#059669", 
                                        fontSize: "0.7rem",
                                        lineHeight: 1.2,
                                        display: "block",
                                        fontWeight: 500
                                      }}
                                    >
                                      +{country.country_code}
                                    </Typography> */}
                                  </Box>
                                </Stack>
                              </MenuItem>
                            ))
                          ) : (
                            <MenuItem disabled>
                              <Stack direction="row" alignItems="center" spacing={1}>
                                <Public sx={{ fontSize: 18, color: "#64748b" }} />
                                <Typography variant="body2" sx={{ color: "#64748b", fontStyle: "italic" }}>
                                  No countries available
                                </Typography>
                              </Stack>
                            </MenuItem>
                          )}
                        </Select>
                      </FormControl>
                      </Paper>

                      {/* Nested Agency-Agent Selection Dropdown */}
                      <Paper
                        className="nested-dropdown-container"
                        elevation={0}
                        sx={{
                          background: "#f8f9fa",
                          borderRadius: "12px",
                          border: "1px solid rgba(0, 0, 0, 0.1)",
                          minWidth: 300,
                          flex: 1,
                          position: "relative",
                          overflow: "visible", // Ensure dropdowns are not clipped
                          zIndex: 20, // Higher z-index for dropdown container
                        }}
                      >
                        <Box sx={{ position: "relative", width: "100%", zIndex: 1 }}>
                          <Box
                            ref={dropdownRef}
                            onClick={() => {
                              console.log("Dropdown clicked, current state:", showAgencyDropdown);
                              console.log("Agencies data:", agencies);
                              console.log("Selected country:", selectedCountry);
                              setShowAgencyDropdown(!showAgencyDropdown);
                            }}
                            sx={{
                              backgroundColor: "#f8fafc",
                              border: "1px solid #e2e8f0",
                              borderRadius: "8px",
                              padding: "12px 16px",
                              cursor: "pointer",
                              display: "flex",
                              alignItems: "center",
                              justifyContent: "space-between",
                              minHeight: "40px",
                              "&:hover": {
                                borderColor: "#94a3b8",
                              },
                              "&:focus": {
                                borderColor: "#3b82f6",
                                boxShadow: "0 0 0 2px rgba(59, 130, 246, 0.1)",
                                outline: "none",
                              },
                            }}
                          >
                            <Stack direction="row" alignItems="center" spacing={1}>
                              <People sx={{ fontSize: 16, color: "#64748b" }} />
                              <Typography variant="body2" sx={{ color: "#475569" }}>
                                {selectedAgent ? "Agent Selected" : "Select Agency & Agent"}
                              </Typography>
                            </Stack>
                            <Typography variant="caption" sx={{ color: "#64748b" }}>
                              ▼
                            </Typography>
                          </Box>
                          
                          {showAgencyDropdown && (
                            <Paper
                              elevation={8}
                              sx={{
                                position: "absolute",
                                top: "100%",
                                left: 0,
                                right: 0,
                                maxHeight: 400,
                                overflow: "visible",
                                backgroundColor: "white",
                                borderRadius: "8px",
                                border: "1px solid #e2e8f0",
                                boxShadow: "0 10px 25px rgba(0, 0, 0, 0.15)",
                                zIndex: 99999999, // Even higher z-index to appear above everything
                                mt: 1,
                                minHeight: 200,
                              }}
                            >
                              {/* Search Input in Dropdown */}
                              <Box 
                                sx={{ 
                                  p: 2, 
                                  borderBottom: "1px solid #e2e8f0", 
                                  backgroundColor: "#f8fafc",
                                  position: "sticky",
                                  top: 0,
                                  zIndex: 1
                                }}
                                onClick={(e) => e.stopPropagation()}
                                onMouseDown={(e) => e.stopPropagation()}
                              >
                                <TextField
                                  ref={searchInputRef}
                                  fullWidth
                                  size="small"
                                  placeholder="Search agencies and agents..."
                                  value={searchTerm}
                                  onChange={handleSearchChange}
                                  autoFocus={false}
                                  InputProps={{
                                    startAdornment: (
                                      <InputAdornment position="start">
                                        <Search sx={{ fontSize: 18, color: "#64748b" }} />
                                      </InputAdornment>
                                    ),
                                    endAdornment: searchTerm && (
                                      <InputAdornment position="end">
                                        <Clear 
                                          sx={{ 
                                            fontSize: 18, 
                                            color: "#64748b", 
                                            cursor: "pointer",
                                            "&:hover": { color: "#374151" }
                                          }} 
                                          onClick={(e) => {
                                            e.stopPropagation();
                                            handleClearSearch();
                                          }}
                                        />
                                      </InputAdornment>
                                    ),
                                    sx: {
                                      "& fieldset": { border: "1px solid #e2e8f0" },
                                      "&:hover fieldset": { border: "1px solid #94a3b8" },
                                      "&.Mui-focused fieldset": { border: "1px solid #3b82f6" },
                                      "& input": {
                                        padding: "8px 12px",
                                        fontSize: "0.9rem",
                                        color: "#1e293b",
                                        "&::placeholder": {
                                          color: "#64748b",
                                          opacity: 1,
                                        },
                                      },
                                    },
                                  }}
                                  onKeyDown={(e) => e.stopPropagation()}
                                  onKeyUp={(e) => e.stopPropagation()}
                                  onKeyPress={(e) => e.stopPropagation()}
                                />
                              </Box>
                              
                              <Box
                                sx={{
                                  maxHeight: 300,
                                  overflow: "auto",
                                  "&::-webkit-scrollbar": {
                                    width: "6px",
                                  },
                                  "&::-webkit-scrollbar-track": {
                                    backgroundColor: "transparent",
                                  },
                                  "&::-webkit-scrollbar-thumb": {
                                    backgroundColor: "#cbd5e1",
                                    borderRadius: "3px",
                                    "&:hover": {
                                      backgroundColor: "#94a3b8",
                                    },
                                  },
                                }}
                              >
                                <Box
                                  onClick={() => handleAgentSelect('')}
                                  sx={{
                                    padding: "12px 16px",
                                    cursor: "pointer",
                                    "&:hover": {
                                      backgroundColor: "#f1f5f9",
                                    },
                                    borderBottom: "1px solid #f1f5f9",
                                  }}
                                >
                                  <Stack direction="row" alignItems="center" spacing={1}>
                                    <Assessment sx={{ fontSize: 18, color: "#3b82f6" }} />
                                    <Box>
                                      <Typography variant="body2" sx={{ fontWeight: 600, color: "#1e293b" }}>
                                        All Agents
                                      </Typography>
                                      <Typography variant="caption" sx={{ color: "#64748b", fontSize: "0.75rem" }}>
                                        View all agents
                                      </Typography>
                                    </Box>
                                  </Stack>
                                </Box>
                              
                                {agencies && agencies.length > 0 ? (
                                  agencies.map((agency) => (
                                  <Box
                                    key={agency.agency_id}
                                    onClick={(event) => handleAgencyClick(event, agency.agency_id)}
                                    sx={{ 
                                      position: "relative",
                                      padding: "12px 16px",
                                      cursor: "pointer",
                                      "&:hover": {
                                        backgroundColor: "#f1f5f9",
                                      },
                                      borderBottom: "1px solid #f1f5f9",
                                    }}
                                  >
                                    <Stack direction="row" alignItems="center" spacing={1}>
                                      <Avatar 
                                        sx={{ 
                                          width: 24, 
                                          height: 24, 
                                          fontSize: "0.8rem",
                                          backgroundColor: "#3b82f6",
                                          color: "white",
                                          fontWeight: 600
                                        }}
                                      >
                                        {agency.agency_name.charAt(0).toUpperCase()}
                                      </Avatar>
                                      <Box sx={{ minWidth: 0, flex: 1 }}>
                                        <Typography variant="body2" sx={{ fontWeight: 500, color: "#1e293b", lineHeight: 1.2 }}>
                                          {agency.agency_name}
                                        </Typography>
                                        <Typography 
                                          variant="caption" 
                                          sx={{ 
                                            color: "#059669", 
                                            fontSize: "0.7rem",
                                            lineHeight: 1.2,
                                            display: "block",
                                            overflow: "hidden",
                                            textOverflow: "ellipsis",
                                            whiteSpace: "nowrap",
                                            fontWeight: 500
                                          }}
                                        >
                                          {agency.city}, {agency.country}
                                        </Typography>
                                      </Box>
                                      <Typography variant="caption" sx={{ color: "#64748b", fontSize: "0.7rem" }}>
                                        →
                                      </Typography>
                                    </Stack>
                                  
                                    {/* Nested Agent Submenu */}
                                    {clickedAgency === agency.agency_id && showAgentSubmenu && (
                                      <Paper
                                        elevation={8}
                                        sx={{
                                          position: "absolute",
                                          left: "100%",
                                          top: 0,
                                          minWidth: 250,
                                          maxWidth: 300,
                                          maxHeight: 300,
                                          overflow: "auto",
                                          backgroundColor: "white",
                                          borderRadius: "8px",
                                          border: "1px solid #e2e8f0",
                                          boxShadow: "0 10px 25px rgba(0, 0, 0, 0.15)",
                                          zIndex: 99999999, // Even higher z-index for nested submenu
                                        }}
                                      >
                                        {loadingAgencies[agency.agency_id] ? (
                                          <Box sx={{ padding: "16px", textAlign: "center" }}>
                                            <Stack direction="row" alignItems="center" spacing={2} justifyContent="center">
                                              <CircularProgress size={20} sx={{ color: "#3b82f6" }} />
                                              <Typography variant="body2" sx={{ color: "#64748b" }}>
                                                Loading agents...
                                              </Typography>
                                            </Stack>
                                          </Box>
                                        ) : agencyAgents[agency.agency_id] && Array.isArray(agencyAgents[agency.agency_id]) && agencyAgents[agency.agency_id].length > 0 ? (
                                          agencyAgents[agency.agency_id]
                                            .filter(agent => {
                                              if (!searchTerm) return true;
                                              const searchLower = searchTerm.toLowerCase();
                                              return (
                                                (agent.agent_name && agent.agent_name.toLowerCase().includes(searchLower))
                                              );
                                            })
                                            .map((agent, index) => {
                                              // Safety check for agent object
                                              if (!agent || typeof agent !== 'object') {
                                                console.warn('Invalid agent object:', agent);
                                                return null;
                                              }
                                              
                                              return (
                                                <Box
                                                  key={agent.agent_id || `agent-${index}`} 
                                                  onClick={() => handleAgentSelect(agent.agent_id)}
                                                  sx={{ 
                                                    padding: "8px 12px",
                                                    cursor: "pointer",
                                                    borderBottom: "1px solid #f1f5f9",
                                                    "&:last-child": { borderBottom: "none" },
                                                    "&:hover": {
                                                      backgroundColor: "#f1f5f9",
                                                    },
                                                  }}
                                                >
                                                  <Stack direction="row" alignItems="center" spacing={1}>
                                                    <Avatar 
                                                      sx={{ 
                                                        width: 20, 
                                                        height: 20, 
                                                        fontSize: "0.7rem",
                                                        backgroundColor: "#3b82f6",
                                                        color: "white",
                                                        fontWeight: 600
                                                      }}
                                                    >
                                                      {agent.agent_name ? agent.agent_name.charAt(0).toUpperCase() : '?'}
                                                    </Avatar>
                                                    <Box sx={{ minWidth: 0, flex: 1 }}>
                                                      <Typography variant="body2" sx={{ fontWeight: 500, color: "#1e293b", lineHeight: 1.2 }}>
                                                        {agent.agent_name || 'Unknown Agent'}
                                                      </Typography>
                                                    </Box>
                                                  </Stack>
                                                </Box>
                                              );
                                            })
                                            .filter(Boolean) // Remove null values
                                        ) : (
                                          <Box sx={{ padding: "16px", textAlign: "center" }}>
                                            <Typography variant="body2" sx={{ color: "#64748b", fontStyle: "italic" }}>
                                              No agents found
                                            </Typography>
                                          </Box>
                                        )}
                                      </Paper>
                                    )}
                                  </Box>
                                  ))
                                ) : selectedCountry ? (
                                  <Box sx={{ padding: "16px", textAlign: "center" }}>
                                    <Stack direction="row" alignItems="center" spacing={1} justifyContent="center">
                                      <People sx={{ fontSize: 18, color: "#64748b" }} />
                                      <Typography variant="body2" sx={{ color: "#64748b", fontStyle: "italic" }}>
                                        {agenciesLoading ? "Loading agencies..." : "No agencies found"}
                                      </Typography>
                                    </Stack>
                                  </Box>
                                ) : (
                                  <Box sx={{ padding: "16px", textAlign: "center" }}>
                                    <Stack direction="row" alignItems="center" spacing={1} justifyContent="center">
                                      <People sx={{ fontSize: 18, color: "#64748b" }} />
                                      <Typography variant="body2" sx={{ color: "#64748b", fontStyle: "italic" }}>
                                        Select a country first
                                      </Typography>
                                    </Stack>
                                  </Box>
                                )}
                              </Box>
                            </Paper>
                          )}
                        </Box>
                      </Paper>
                    </Stack>
                  </Stack>
                </Grow>
              </Stack>
            </CardContent>
          </Card>
        </AnimatedBox>




        <AnimatedBox direction="left" delay={400}>
          <Card
            elevation={0}
            sx={{
              background: "white",
              borderRadius: "16px",
              boxShadow: "0 4px 20px rgba(0,0,0,0.08)",
              overflow: "visible",
              border: "1px solid rgba(0,0,0,0.05)",
              mb: 3, // Add margin bottom for proper spacing
              position: "relative", // Ensure proper positioning
              zIndex: 10, // Even lower z-index to ensure dropdown appears above
            }}
          >
            <CardContent sx={{ p: 3}}>
              <Stack direction="row" alignItems="center" justifyContent="space-between" sx={{ mb: 2 }}>
                <Stack direction="row" alignItems="center" spacing={2}>
                  <Avatar
                    sx={{
                      width: 36,
                      height: 36,
                      background: "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
                    }}
                  >
                    <Assessment sx={{ fontSize: 20, color: "white" }} />
                  </Avatar>
                  <Stack>
                    <Typography variant="h6" fontWeight="bold" color="text.primary">
                      Management Overview
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
                      Detailed analytics and performance tracking
                    </Typography>
                  </Stack>
                </Stack>
                
                <Chip
                  label={`${filteredAgents.length} Agent${filteredAgents.length !== 1 ? 's' : ''}${searchTerm ? ' (filtered)' : ''}`}
                  size="small"
                  sx={{
                    backgroundColor: "rgba(102, 126, 234, 0.1)",
                    color: "rgb(102, 126, 234)",
                    fontWeight: 600,
                    "& .MuiChip-label": {
                      px: 2,
                    },
                  }}
                />
              </Stack>
              
              <Divider sx={{ borderColor: "rgba(0,0,0,0.05)", mb: 2 }} />
              
              <Box sx={{ overflow: "visible" }}>
                <Tabs
                  value={mainTabValue}
                  onChange={handleMainTabChange}
                  aria-label="dashboard tabs"
                  indicatorColor="primary"
                  textColor="primary"
                  sx={{
                    "& .MuiTab-root": {
                      textTransform: "none",
                      fontWeight: 500,
                      fontSize: "14px",
                      minHeight: "56px",
                      display: "flex",
                      flexDirection: "row",
                      alignItems: "center",
                      gap: "8px",
                      borderRadius: "8px",
                      mx: 0.5,
                      my: 1,
                      transition: "all 0.3s ease",
                      "&:hover": {
                        backgroundColor: "rgba(102, 126, 234, 0.05)",
                      },
                      "&.Mui-selected": {
                        backgroundColor: "rgba(102, 126, 234, 0.1)",
                        color: "rgb(102, 126, 234)",
                      },
                    },
                    "& .MuiTabs-indicator": {
                      height: 3,
                      borderRadius: "3px 3px 0 0",
                    },
                  }}
                >
                  <Tab
                    icon={<EventNote />}
                    label="Bookings Details"
                    iconPosition="start"
                    {...a11yProps(0)}
                  />
                  <Tab 
                    icon={<EmailOutlined />} 
                    label="Booking Enquiries" 
                    iconPosition="start" 
                    {...a11yProps(1)} 
                  />
                  <Tab 
                    icon={<CardGiftcardOutlined />} 
                    label="Fixed Itinerary Packages" 
                    iconPosition="start" 
                    {...a11yProps(2)} 
                  />
                </Tabs>
              </Box>
            </CardContent>
          </Card>
        </AnimatedBox>

        <AnimatedBox direction="up" delay={600}>
          <TabPanel value={mainTabValue} index={0} sx={{ p: 0, overflow: "visible", zIndex: 0 }}>
            {/* Insert your booking details content here */}
            <BasicTabs />
          </TabPanel>
        </AnimatedBox>
        
        <AnimatedBox direction="up" delay={600}>
          <TabPanel value={mainTabValue} index={1} sx={{ p: 0, overflow: "visible", zIndex: 0 }}>
            {/* Insert your booking enquiries content here */}
            <Box sx={{ p: 3, backgroundColor: "white", borderRadius: "0 0 12px 12px", overflow: "visible" }}>
              <EnquiryList />
            </Box>
          </TabPanel>
        </AnimatedBox>
        
        <AnimatedBox direction="up" delay={600}>
          <TabPanel value={mainTabValue} index={2} sx={{ p: 0, overflow: "visible", zIndex: 0 }}>
            {/* Insert your predefine packages content here */}
            <Box sx={{ p: 3, backgroundColor: "white", borderRadius: "0 0 12px 12px", overflow: "visible" }}>
              <PreDefinePackages />
            </Box>
          </TabPanel>
        </AnimatedBox>
      </StaggeredContainer>
    </Container>
  );
};

export default SalesManagerDashboard; 