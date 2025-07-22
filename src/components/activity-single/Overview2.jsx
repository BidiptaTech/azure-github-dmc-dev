import React, { useState } from "react";
import { useLocation } from "react-router-dom";
import {
  Card,
  CardHeader,
  CardContent,
  Grid,
  Box,
  Typography,
  Paper,
  Dialog,
  DialogContent,
  IconButton,
  Skeleton,
  CardMedia,
} from "@mui/material";
import {
  DirectionsCar as CarIcon,
  EventSeat as SeatIcon,
  Build as ModelIcon,
  DateRange as YearIcon,
  Close as CloseIcon,
} from "@mui/icons-material";

const Overview2 = () => {
  const location = useLocation();
  const { vehicles } = location.state;
  // const [isLoading] = useState(false);
  // const [openImageDialog, setOpenImageDialog] = useState(false);
  // const [selectedImage, setSelectedImage] = useState("");

  return (
    <>
      <div className="row x-gap-40 y-gap-40">
        <div className="col-12">
          <h3 className="text-22 fw-500">Overview</h3>
          <div className="text-dark-1 text-15 mt-10">
            <div dangerouslySetInnerHTML={{ __html: vehicles.description }} />
          </div>

          {/* Vehicle Information Card */}
          <Card
            elevation={1}
            sx={{
              mt: 2.5,
              mb: 10,
              p: 2,
              borderRadius: 2,
              bgcolor: "rgba(255, 255, 255, 0.9)",
              border: "1px solid rgba(0, 0, 0, 0.1)",
            }}
          >
            <Grid container spacing={2}>
              <Grid item xs={12} md={3}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                  <CarIcon sx={{ color: "#3554D1" }} />
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    fontWeight="700"
                    fontSize="16px"
                  >
                    Type: {vehicles.vehicle_type}
                  </Typography>
                </Box>
              </Grid>
              <Grid item xs={12} md={3}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                  <ModelIcon sx={{ color: "#3554D1" }} />
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    fontWeight="700"
                    fontSize="16px"
                  >
                    Model: {vehicles.vehicle_model}
                  </Typography>
                </Box>
              </Grid>
              <Grid item xs={12} md={3}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                  <YearIcon sx={{ color: "#3554D1" }} />
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    fontWeight="700"
                    fontSize="16px"
                  >
                    Model Year: {vehicles.model_year}
                  </Typography>
                </Box>
              </Grid>
              <Grid item xs={12} md={3}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                  <SeatIcon sx={{ color: "#3554D1" }} />
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    fontWeight="700"
                    fontSize="16px"
                  >
                    Capacity: {vehicles.seating_capacity} seats
                  </Typography>
                </Box>
              </Grid>
            </Grid>
          </Card>
        </div>

        {/* Vehicle Details Section */}
        {/* <div className="col-12">
          <Grid container spacing={3} sx={{ mt: 1, px: 2 }}> */}
        {/* Vehicle Images Section */}
        {/* <Grid item xs={12} md={6}>
              <Card
                elevation={2}
                sx={{
                  height: "100%",
                  borderRadius: 2,
                  overflow: "hidden",
                  transition: "transform 0.2s ease",
                  "&:hover": {
                    transform: "translateY(-4px)",
                    boxShadow: "0 6px 16px rgba(0,0,0,0.1)",
                  },
                  maxWidth: "500px",
                  mx: "auto",
                }}
              >
                <CardHeader
                  title="Vehicle Images"
                  sx={{
                    background:
                      "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
                    color: "white",
                    "& .MuiTypography-root": { fontWeight: 300 },
                  }}
                />
                <CardContent>
                  <Grid container spacing={2}>
                    {vehicles.images?.map((image, index) => (
                      <Grid item xs={12} key={index}>
                        <Card
                          sx={{
                            cursor: "pointer",
                            overflow: "hidden",
                            borderRadius: 1,
                            "&:hover": {
                              boxShadow: "0 4px 12px rgba(0,0,0,0.1)",
                            },
                          }}
                          onClick={() => {
                            setSelectedImage(image);
                            setOpenImageDialog(true);
                          }}
                        >
                          {isLoading ? (
                            <Skeleton variant="rectangular" height={180} />
                          ) : (
                            <CardMedia
                              component="img"
                              image={image}
                              alt={`Vehicle Image ${index + 1}`}
                              sx={{
                                height: 180,
                                objectFit: "cover",
                              }}
                            />
                          )}
                        </Card>
                      </Grid>
                    ))}
                  </Grid>
                </CardContent>
              </Card>
            </Grid> */}

        {/* Vehicle Features Section */}
        {/* <Grid item xs={12} md={6}>
              <Card
                elevation={2}
                sx={{
                  height: "100%",
                  borderRadius: 2,
                  overflow: "hidden",
                  transition: "transform 0.2s ease",
                  "&:hover": {
                    transform: "translateY(-4px)",
                    boxShadow: "0 6px 16px rgba(0,0,0,0.1)",
                  },
                  maxWidth: "500px",
                  mx: "auto",
                }}
              >
                <CardHeader
                  title="Vehicle Features"
                  sx={{
                    background:
                      "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
                    color: "white",
                    "& .MuiTypography-root": { fontWeight: 300 },
                  }}
                />
                <CardContent>
                  <Grid container spacing={2}>
                    {vehicles.features?.map((feature, index) => (
                      <Grid item xs={12} key={index}>
                        <Paper
                          elevation={0}
                          sx={{
                            p: 1.5,
                            bgcolor: "rgba(53, 84, 209, 0.05)",
                            border: "1px solid rgba(53, 84, 209, 0.1)",
                            borderRadius: 1,
                          }}
                        >
                          <Typography variant="body2" fontWeight={500}>
                            {feature}
                          </Typography>
                        </Paper>
                      </Grid>
                    ))}
                  </Grid>
                </CardContent>
              </Card>
            </Grid> */}
        {/* </Grid>
        </div> */}
      </div>

      {/* Image Dialog */}
      {/* <Dialog
        open={openImageDialog}
        onClose={() => setOpenImageDialog(false)}
        maxWidth="lg"
        fullWidth
      >
        <DialogContent sx={{ p: 0, position: "relative" }}>
          <IconButton
            onClick={() => setOpenImageDialog(false)}
            sx={{
              position: "absolute",
              right: 8,
              top: 8,
              color: "white",
              bgcolor: "rgba(0,0,0,0.5)",
              "&:hover": {
                bgcolor: "rgba(0,0,0,0.7)",
              },
            }}
          >
            <CloseIcon />
          </IconButton>
          <img
            src={selectedImage}
            alt="Vehicle"
            style={{
              width: "100%",
              height: "auto",
              display: "block",
            }}
          />
        </DialogContent>
      </Dialog> */}
    </>
  );
};

export default Overview2;
