import React from 'react';
import {
  Box,
  Typography,
  Divider,
  List,
  Chip,
} from "@mui/material";
import {
  LocationOn as LocationIcon,
  AccessTime as AccessTimeIcon,
} from '@mui/icons-material';

const RenderAttractionDetails = ({ details, tabValue }) => {
  if (!details || details.length === 0) {
    return (
      <Box sx={{ p: 3, textAlign: "center" }}>
        <Typography variant="body1" color="textSecondary">
          No attraction details available
        </Typography>
      </Box>
    );
  }

  return (
    <List sx={{ width: "100%", bgcolor: "background.paper" }}>
      {details.map((attraction, index) => (
        <React.Fragment key={attraction.id}>
          <Box sx={{ mb: 4 }}>
            <Box
              sx={{
                display: "flex",
                flexDirection: { xs: "column", md: "row" },
                gap: 2,
                mb: 2,
              }}
            >
              <Box
                sx={{
                  width: { xs: "100%", md: 200 },
                  height: 150,
                  position: "relative",
                  borderRadius: 2,
                  overflow: "hidden",
                }}
              >
                <Box
                  component="img"
                  src={attraction.master_image}
                  alt={attraction.name}
                  sx={{
                    width: "100%",
                    height: "100%",
                    objectFit: "cover",
                  }}
                />
              </Box>
              <Box sx={{ flex: 1 }}>
                <Typography variant="h6" fontWeight={600} gutterBottom>
                  {attraction.name}
                </Typography>
                <Box sx={{ display: "flex", alignItems: "center", mb: 1 }}>
                  <LocationIcon
                    fontSize="small"
                    color="action"
                    sx={{ mr: 1 }}
                  />
                  <Typography variant="body2" color="text.secondary">
                    {attraction.location}, {attraction.country}
                  </Typography>
                </Box>
                <Box sx={{ display: "flex", alignItems: "center", mb: 1 }}>
                  <AccessTimeIcon
                    fontSize="small"
                    color="action"
                    sx={{ mr: 1 }}
                  />
                  <Typography variant="body2" color="text.secondary">
                    Open: {JSON.parse(attraction.open_time)[0]} - Close:{" "}
                    {JSON.parse(attraction.close_time)[0]}
                  </Typography>
                </Box>
                <Box
                  sx={{ display: "flex", flexWrap: "wrap", gap: 1, mt: 1 }}
                >
                  {attraction.morning_opening ? (
                    <Chip
                      size="small"
                      label="Morning"
                      color="primary"
                      variant="outlined"
                    />
                  ) : null}
                  {attraction.afternoon_opening ? (
                    <Chip
                      size="small"
                      label="Afternoon"
                      color="primary"
                      variant="outlined"
                    />
                  ) : null}
                  {attraction.evening_opening ? (
                    <Chip
                      size="small"
                      label="Evening"
                      color="primary"
                      variant="outlined"
                    />
                  ) : null}
                  {attraction.night_opening ? (
                    <Chip
                      size="small"
                      label="Night"
                      color="primary"
                      variant="outlined"
                    />
                  ) : null}
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
                  {attraction.adult_price > 0
                    ? `$${attraction.adult_price}`
                    : "Price not set"}
                </Typography>
                <Typography variant="caption" color="text.secondary">
                  per adult
                </Typography>
                {attraction.child_price > 0 && (
                  <>
                    <Typography
                      variant="body2"
                      color="primary"
                      fontWeight={600}
                      mt={1}
                    >
                      ${attraction.child_price}
                    </Typography>
                    <Typography variant="caption" color="text.secondary">
                      per child
                    </Typography>
                  </>
                )}
              </Box>
            </Box>

            {tabValue === 1 && (
              <Box mt={2}>
                <Typography variant="subtitle2" gutterBottom>
                  Description:
                </Typography>
                <Box
                  sx={{
                    borderRadius: 1,
                    p: 2,
                    bgcolor: "rgba(0,0,0,0.02)",
                    maxHeight: "200px",
                    overflow: "auto",
                  }}
                  dangerouslySetInnerHTML={{ __html: attraction.description }}
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

export default RenderAttractionDetails; 