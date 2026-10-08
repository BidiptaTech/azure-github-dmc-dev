import React from 'react';
import {
  Box,
  Typography,
  Divider,
  List,
  Chip,
  Avatar,
} from "@mui/material";
import {
  LocationOn as LocationIcon,
  Phone as PhoneIcon,
  Email as EmailIcon,
} from '@mui/icons-material';

const RenderGuideDetails = ({ details, tabValue }) => {
  if (!details || details.length === 0) {
    return (
      <Box sx={{ p: 3, textAlign: "center" }}>
        <Typography variant="body1" color="textSecondary">
          No guide details available
        </Typography>
      </Box>
    );
  }

  return (
    <List sx={{ width: "100%", bgcolor: "background.paper" }}>
      {details.map((guide, index) => (
        <React.Fragment key={guide.id}>
          <Box sx={{ mb: 4 }}>
            <Box
              sx={{
                display: "flex",
                flexDirection: { xs: "column", md: "row" },
                gap: 2,
                mb: 2,
              }}
            >
              <Avatar
                src={guide.image}
                alt={guide.name}
                sx={{
                  width: 100,
                  height: 100,
                  boxShadow: 2,
                }}
              />
              <Box sx={{ flex: 1 }}>
                <Typography variant="h6" fontWeight={600} gutterBottom>
                  {guide.salutation} {guide.name}
                </Typography>
                <Box sx={{ display: "flex", alignItems: "center", mb: 1 }}>
                  <LocationIcon
                    fontSize="small"
                    color="action"
                    sx={{ mr: 1 }}
                  />
                  <Typography variant="body2" color="text.secondary">
                    {guide.city}, {guide.country}
                  </Typography>
                </Box>
                <Box sx={{ display: "flex", alignItems: "center", mb: 1 }}>
                  <PhoneIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                  <Typography variant="body2" color="text.secondary">
                    {guide.contact_no}
                  </Typography>
                </Box>
                <Box sx={{ display: "flex", alignItems: "center", mb: 1 }}>
                  <EmailIcon fontSize="small" color="action" sx={{ mr: 1 }} />
                  <Typography variant="body2" color="text.secondary">
                    {guide.email}
                  </Typography>
                </Box>
                <Box sx={{ display: "flex", mt: 1 }}>
                  <Chip
                    size="small"
                    label={`${guide.experience_years} yrs exp`}
                    color="primary"
                    variant="outlined"
                    sx={{ mr: 1 }}
                  />
                  {guide.guide_gender && (
                    <Chip
                      size="small"
                      label={guide.guide_gender}
                      color="primary"
                      variant="outlined"
                      sx={{ mr: 1 }}
                    />
                  )}
                  {guide.guide_age && (
                    <Chip
                      size="small"
                      label={`${guide.guide_age} years`}
                      color="primary"
                      variant="outlined"
                    />
                  )}
                </Box>
              </Box>
              <Box
                sx={{
                  display: "flex",
                  flexDirection: "column",
                  justifyContent: "center",
                  alignItems: "center",
                  px: 2,
                }}
              >
                <Typography variant="h6" color="primary" fontWeight={600}>
                  ${guide.day_rate}
                </Typography>
                <Typography variant="caption" color="text.secondary">
                  per day
                </Typography>
                <Typography
                  variant="body2"
                  color="primary"
                  fontWeight={600}
                  mt={1}
                >
                  ${guide.hourly_price}
                </Typography>
                <Typography variant="caption" color="text.secondary">
                  per hour
                </Typography>
              </Box>
            </Box>

            {tabValue === 1 && (
              <Box mt={2}>
                <Typography variant="subtitle2" gutterBottom>
                  About the Guide:
                </Typography>
                <Box
                  sx={{
                    borderRadius: 1,
                    p: 2,
                    bgcolor: "rgba(0,0,0,0.02)",
                    maxHeight: "200px",
                    overflow: "auto",
                  }}
                  dangerouslySetInnerHTML={{ __html: guide.description }}
                />
              </Box>
            )}
          </Box>
          {index < details.length - 1 && <Divider sx={{ my: 2 }} />}
        </React.Fragment>
      ))}
    </List>
  );
};

export default RenderGuideDetails; 