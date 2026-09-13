import React from 'react';
import {
  Box,
  Typography,
  Divider,
  List,
  Chip,
  Card,
  CardContent,
  Grid,
} from "@mui/material";
import {
  LocationOn as LocationIcon,
  ConfirmationNumber as PackageIcon,
  AccessTime as AccessTimeIcon,
} from '@mui/icons-material';

const RenderPackagedAttractionsDetai = ({ details, tabValue }) => {
  if (!details || details.length === 0) {
    return (
      <Box sx={{ p: 3, textAlign: "center" }}>
        <Typography variant="body1" color="textSecondary">
          No packaged attraction details available
        </Typography>
      </Box>
    );
  }

  return (
    <List sx={{ width: "100%", bgcolor: "background.paper" }}>
      {details.map((packageAttraction, index) => (
        <React.Fragment key={packageAttraction.id}>
          <Box sx={{ mb: 4 }}>
            {/* Package Header */}
            <Box
              sx={{
                display: "flex",
                flexDirection: { xs: "column", md: "row" },
                gap: 2,
                mb: 3,
                p: 2,
                bgcolor: "primary.light",
                borderRadius: 2,
                color: "white",
              }}
            >
              <Box sx={{ flex: 1 }}>
                <Box sx={{ display: "flex", alignItems: "center", mb: 1 }}>
                  <PackageIcon sx={{ mr: 1 }} />
                  <Typography variant="h6" fontWeight={600}>
                    {packageAttraction.name}
                  </Typography>
                </Box>
                <Typography variant="body2" sx={{ opacity: 0.9 }}>
                  Package includes {packageAttraction.attraction_details?.length || 0} attractions
                </Typography>
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
                <Typography variant="h6" fontWeight={600}>
                  {packageAttraction.adult_price > 0
                    ? `$${packageAttraction.adult_price}`
                    : "Price not set"}
                </Typography>
                <Typography variant="caption" sx={{ opacity: 0.9 }}>
                  per adult
                </Typography>
                {packageAttraction.child_price > 0 && (
                  <>
                    <Typography variant="body2" fontWeight={600} mt={1}>
                      ${packageAttraction.child_price}
                    </Typography>
                    <Typography variant="caption" sx={{ opacity: 0.9 }}>
                      per child
                    </Typography>
                  </>
                )}
                {packageAttraction.senior_citizen_price > 0 && (
                  <>
                    <Typography variant="body2" fontWeight={600} mt={1}>
                      ${packageAttraction.senior_citizen_price}
                    </Typography>
                    <Typography variant="caption" sx={{ opacity: 0.9 }}>
                      per senior
                    </Typography>
                  </>
                )}
              </Box>
            </Box>

            {/* Package Description */}
            {tabValue === 1 && packageAttraction.description && (
              <Box sx={{ mb: 3 }}>
                <Typography variant="subtitle2" gutterBottom>
                  Package Description:
                </Typography>
                <Box
                  sx={{
                    borderRadius: 1,
                    p: 2,
                    bgcolor: "rgba(0,0,0,0.02)",
                    maxHeight: "200px",
                    overflow: "auto",
                  }}
                  dangerouslySetInnerHTML={{ __html: packageAttraction.description }}
                />
              </Box>
            )}

            {/* Included Attractions */}
            <Box sx={{ mb: 2 }}>
              <Typography variant="h6" fontWeight={600} gutterBottom>
                Included Attractions:
              </Typography>
            </Box>

            <Grid container spacing={2}>
              {packageAttraction.attraction_details?.map((attraction, attractionIndex) => (
                <Grid item xs={12} md={6} key={attraction.attraction_id}>
                  <Card sx={{ height: "100%" }}>
                    <CardContent>
                      <Box
                        sx={{
                          display: "flex",
                          flexDirection: { xs: "column", sm: "row" },
                          gap: 2,
                        }}
                      >
                        {attraction.master_image && (
                          <Box
                            sx={{
                              width: { xs: "100%", sm: 120 },
                              height: 100,
                              position: "relative",
                              borderRadius: 1,
                              overflow: "hidden",
                              flexShrink: 0,
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
                        )}
                        <Box sx={{ flex: 1 }}>
                          <Typography variant="subtitle1" fontWeight={600} gutterBottom>
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
                          <Chip
                            size="small"
                            label={`Attraction ${attractionIndex + 1}`}
                            color="primary"
                            variant="outlined"
                          />
                        </Box>
                      </Box>
                    </CardContent>
                  </Card>
                </Grid>
              ))}
            </Grid>
          </Box>
          {index < details.length - 1 && <Divider sx={{ my: 2 }} />}
        </React.Fragment>
      ))}
    </List>
  );
};

export default RenderPackagedAttractionsDetai;     