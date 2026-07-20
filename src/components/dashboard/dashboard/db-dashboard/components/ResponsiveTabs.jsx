import React, { useState, useEffect } from 'react';
import {
  Box,
  Tabs,
  Tab,
  Button,
  Typography,
  useTheme,
  useMediaQuery,
  Stack,
  Chip,
  Fade,
  Collapse,
} from '@mui/material';
import {
  KeyboardArrowDown,
  KeyboardArrowUp,
} from '@mui/icons-material';

// Custom Tab Panel component
function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`responsive-tabpanel-${index}`}
      aria-labelledby={`responsive-tab-${index}`}
      {...other}
      style={{ padding: "20px 0" }}
    >
      {value === index && <Box>{children}</Box>}
    </div>
  );
}

function a11yProps(index) {
  return {
    id: `responsive-tab-${index}`,
    "aria-controls": `responsive-tabpanel-${index}`,
  };
}

const ResponsiveTabs = ({ 
  tabs = [], 
  value = 0, 
  onChange = () => {}, 
  children = [],
  sx = {} 
}) => {
  const theme = useTheme();
  const isMobile = useMediaQuery(theme.breakpoints.down('sm'));
  const isTablet = useMediaQuery(theme.breakpoints.down('md'));
  const isSmallDesktop = useMediaQuery(theme.breakpoints.down('lg'));
  
  const [showAllTabs, setShowAllTabs] = useState(false);

  // Determine if we should show the collapsed view
  // Collapse on mobile, tablet, and when there are many tabs on smaller screens
  const shouldCollapse = isMobile || isTablet || (isSmallDesktop && tabs.length > 3);

  const handleTabChange = (event, newValue) => {
    onChange(event, newValue);
  };

  const handleViewAllTabs = () => {
    setShowAllTabs(!showAllTabs);
  };

  // Get current tab info
  const currentTab = tabs[value] || {};

  if (!shouldCollapse) {
    // Desktop view - show normal tabs
    return (
      <Box sx={sx}>
        <Tabs
          value={value}
          onChange={handleTabChange}
          aria-label="dashboard tabs"
          indicatorColor="primary"
          textColor="primary"
          sx={{
            "& .MuiTab-root": {
              textTransform: "none",
              fontWeight: 500,
              fontSize: { xs: "0.8rem", sm: "0.9rem", md: "0.95rem" },
              minHeight: { xs: "48px", sm: "52px", md: "56px" },
              display: "flex",
              flexDirection: "row",
              alignItems: "center",
              gap: { xs: "6px", sm: "8px" },
              px: { xs: 1, sm: 1.5, md: 2 },
            },
          }}
        >
          {tabs.map((tab, index) => (
            <Tab
              key={index}
              icon={tab.icon}
              label={tab.label}
              iconPosition="start"
              {...a11yProps(index)}
            />
          ))}
        </Tabs>
        
        {children.map((child, index) => (
          <TabPanel key={index} value={value} index={index}>
            {child}
          </TabPanel>
        ))}
      </Box>
    );
  }

  // Mobile/Tablet view - show collapsed tabs with dropdown
  return (
    <Box sx={sx}>
      {/* Current Tab Display */}
      <Box
        sx={{
          display: "flex",
          alignItems: "center",
          justifyContent: "space-between",
          p: { xs: 1.5, sm: 2 },
          backgroundColor: "white",
          borderRadius: "12px 12px 0 0",
          borderBottom: 1,
          borderColor: "divider",
          boxShadow: "0 2px 10px rgba(0,0,0,0.05)",
          minHeight: { xs: "56px", sm: "64px" },
        }}
      >
        <Stack direction="row" alignItems="center" spacing={{ xs: 1.5, sm: 2 }}>
          {currentTab.icon && (
            <Box sx={{ color: "primary.main", fontSize: { xs: "1.2rem", sm: "1.4rem" } }}>
              {currentTab.icon}
            </Box>
          )}
          <Typography variant="body1" fontWeight={600} color="primary" sx={{ fontSize: { xs: "0.9rem", sm: "1rem" } }}>
            {currentTab.label || "Select Tab"}
          </Typography>
        </Stack>

        {/* View All Tabs Button */}
        <Button
          variant="outlined"
          size="small"
          onClick={handleViewAllTabs}
          endIcon={showAllTabs ? <KeyboardArrowUp /> : <KeyboardArrowDown />}
          sx={{
            borderRadius: "20px",
            textTransform: "none",
            fontWeight: 500,
            fontSize: { xs: "0.75rem", sm: "0.8rem" },
            borderColor: "primary.main",
            color: "primary.main",
            px: { xs: 1.5, sm: 2 },
            py: { xs: 0.5, sm: 0.75 },
            "&:hover": {
              backgroundColor: "primary.50",
              borderColor: "primary.dark",
            },
          }}
        >
          View All Tabs
        </Button>
      </Box>

      {/* Collapsible Tab List */}
      <Collapse in={showAllTabs} timeout="auto">
        <Box
          sx={{
            backgroundColor: "white",
            borderBottom: 1,
            borderColor: "divider",
            p: { xs: 1.5, sm: 2 },
          }}
        >
          <Stack spacing={1}>
            {tabs.map((tab, index) => (
              <Button
                key={index}
                fullWidth
                variant={value === index ? "contained" : "outlined"}
                startIcon={tab.icon}
                onClick={() => handleTabChange(null, index)}
                sx={{
                  justifyContent: "flex-start",
                  textTransform: "none",
                  fontWeight: value === index ? 600 : 500,
                  fontSize: { xs: "0.8rem", sm: "0.9rem" },
                  borderRadius: "8px",
                  py: { xs: 1, sm: 1.5 },
                  px: { xs: 1.5, sm: 2 },
                  ...(value === index && {
                    backgroundColor: "primary.main",
                    color: "white",
                    "&:hover": {
                      backgroundColor: "primary.dark",
                    },
                  }),
                  ...(value !== index && {
                    borderColor: "grey.300",
                    color: "text.primary",
                    "&:hover": {
                      backgroundColor: "grey.50",
                      borderColor: "primary.main",
                    },
                  }),
                }}
              >
                <Stack direction="row" alignItems="center" spacing={{ xs: 1.5, sm: 2 }} sx={{ width: "100%" }}>
                  <Box sx={{ color: value === index ? "white" : "primary.main", fontSize: { xs: "1.1rem", sm: "1.2rem" } }}>
                    {tab.icon}
                  </Box>
                  <Typography
                    variant="body2"
                    sx={{
                      fontWeight: value === index ? 600 : 500,
                      fontSize: { xs: "0.8rem", sm: "0.9rem" },
                      color: value === index ? "white" : "text.primary",
                    }}
                  >
                    {tab.label}
                  </Typography>
                  {value === index && (
                    <Chip
                      label="Active"
                      size="small"
                      sx={{
                        backgroundColor: "white",
                        color: "primary.main",
                        fontWeight: 600,
                        fontSize: { xs: "0.6rem", sm: "0.7rem" },
                        height: { xs: 20, sm: 24 },
                        ml: "auto",
                      }}
                    />
                  )}
                </Stack>
              </Button>
            ))}
          </Stack>
        </Box>
      </Collapse>


      {/* Tab Content */}
      <Fade in={true} timeout={300}>
        <Box>
          {children.map((child, index) => (
            <TabPanel key={index} value={value} index={index}>
              {child}
            </TabPanel>
          ))}
        </Box>
      </Fade>
    </Box>
  );
};

export default ResponsiveTabs;
