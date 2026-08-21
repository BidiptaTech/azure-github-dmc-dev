import React, { useState, useEffect } from "react";
import { useLocation } from "react-router-dom";
import {
  Card,
  CardContent,
  CardHeader,
  CardMedia,
  Grid,
  Box,
  Typography,
  Paper,
  Chip,
  Dialog,
  DialogContent,
  IconButton,
  Skeleton,
} from "@mui/material";
import PhoneIcon from "@mui/icons-material/Phone";
import EmailIcon from "@mui/icons-material/Email";
import WorkHistoryIcon from "@mui/icons-material/WorkHistory";
import VpnKeyIcon from "@mui/icons-material/VpnKey";
import EventIcon from "@mui/icons-material/Event";
import LanguageIcon from "@mui/icons-material/Language";
import WarningIcon from "@mui/icons-material/Warning";
import CloseIcon from "@mui/icons-material/Close";
import AccessTimeIcon from "@mui/icons-material/AccessTime";

const Overview = () => {
  const location = useLocation();
  const { guide } = location.state;
  const [isLoading, setIsLoading] = useState(true); // For skeleton loader
  const [openImageDialog, setOpenImageDialog] = useState(false);
  const [selectedImage, setSelectedImage] = useState("");

  useEffect(() => {
    // Simulate a loading delay
    const timer = setTimeout(() => {
      setIsLoading(false);
    }, 2000); // Adjust timeout as needed
    return () => clearTimeout(timer);
  }, []);

  const LanguageOptions = guide.guide.languages.map((lang) => ({
    language: lang.language,
    proficiency: lang.proficiency,
  }));

  return (
    <>
      <div className="row x-gap-40 y-gap-40">
        <div className="col-12">
          <h3 className="text-22 fw-500">Overview</h3>

          <div className="text-dark-1 text-15 mt-10">
            <div
              dangerouslySetInnerHTML={{ __html: guide.guide.description }}
            />
          </div>

          {/* Contact Information Card */}
          <Card
            elevation={1}
            sx={{
              mt: 2.5,
              p: 2,
              borderRadius: 2,
              bgcolor: "rgba(255, 255, 255, 0.9)",
              border: "1px solid rgba(0, 0, 0, 0.1)",
            }}
          >
            <Grid container spacing={2}>
              <Grid item xs={12} md={4}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                  <PhoneIcon sx={{ color: "#3554D1" }} />
            <Typography
              variant="body2"
              color="text.secondary"
                    fontWeight="500"
            >
                    {guide.guide.contact_no}
            </Typography>
                </Box>
              </Grid>
              <Grid item xs={12} md={4}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                  <EmailIcon sx={{ color: "#3554D1" }} />
            <Typography
              variant="body2"
              color="text.secondary"
                    fontWeight="500"
            >
                    {guide.guide.email}
            </Typography>
                </Box>
              </Grid>
              <Grid item xs={12} md={4}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                  <WorkHistoryIcon sx={{ color: "#3554D1" }} />
            <Typography
              variant="body2"
              color="text.secondary"
                    fontWeight="500"
            >
                    {guide.guide.experience_years} years of experience
            </Typography>
                </Box>
              </Grid>
            </Grid>
          </Card>
          </div>

        {/* License Details and Languages Section */}
        <div className="col-12">
          <Grid container spacing={3} sx={{ mt: 1, px: 2 }}>
            {/* License Details Section */}
            <Grid item xs={12} md={5}>
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
                  title="License Details"
                  sx={{
                    background:
                      "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
                    color: "white",
                    "& .MuiTypography-root": { fontWeight: 300 },
                    py: 1,
                    height: "auto",
                    minHeight: "48px",
                    "& .MuiCardHeader-content": {
                      margin: "0",
                    },
                    "& .MuiCardHeader-title": {
                      fontSize: "1rem",
                    },
                  }}
                />
                <CardContent>
                  <Grid container spacing={2}>
                    <Grid item xs={12}>
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
                          setSelectedImage(guide.guide.license_image);
                          setOpenImageDialog(true);
                        }}
                      >
                        {isLoading ? (
                          <Skeleton variant="rectangular" height={180} />
              ) : (
                <CardMedia
                  component="img"
                  image={guide.guide.license_image}
                  alt="License Image"
                  sx={{
                              height: 180,
                              objectFit: "cover",
                  }}
                />
              )}
            </Card>
          </Grid>
                    <Grid item xs={12}>
                      <Box sx={{ mt: 1 }}>
            <Typography
              variant="body2"
                          sx={{
                            display: "flex",
                            alignItems: "center",
                            gap: 1,
                            mb: 1,
                          }}
                        >
                          <VpnKeyIcon sx={{ color: "#3554D1" }} />
                          <span style={{ fontWeight: 500 }}>
                            License Number:
                          </span>{" "}
                          {guide.guide.government_license_no}
            </Typography>
            <Typography
              variant="body2"
                          sx={{ display: "flex", alignItems: "center", gap: 1 }}
                        >
                          <EventIcon sx={{ color: "#3554D1" }} />
                          <span style={{ fontWeight: 500 }}>
                            Expiry Date:
                          </span>{" "}
                          {new Date(
                            guide.guide.license_exp_date
                          ).toLocaleDateString("en-GB")}
                        </Typography>
                      </Box>
                    </Grid>
                  </Grid>
                </CardContent>
              </Card>
            </Grid>

            {/* Languages Section */}
            <Grid item xs={12} md={5}>
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
                  title="Available Languages"
                  sx={{
                    background:
                      "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
                    color: "white",
                    "& .MuiTypography-root": { fontWeight: 300 },
                    py: 1,
                    height: "auto",
                    minHeight: "48px",
                    "& .MuiCardHeader-content": {
                      margin: "0",
                    },
                    "& .MuiCardHeader-title": {
                      fontSize: "1rem",
                    },
                  }}
                />
                <CardContent>
                  <Grid container spacing={2}>
                    {LanguageOptions.map((option, index) => (
                      <Grid item xs={12} key={index}>
                        <Paper
                          elevation={0}
                          sx={{
                            p: 1.5,
                            bgcolor: "rgba(53, 84, 209, 0.05)",
                            border: "1px solid rgba(53, 84, 209, 0.1)",
                            borderRadius: 1,
                            display: "flex",
                            justifyContent: "space-between",
                            alignItems: "center",
                          }}
                        >
                          <Box
                            sx={{
                              display: "flex",
                              alignItems: "center",
                              gap: 1,
                            }}
                          >
                            <LanguageIcon sx={{ color: "#3554D1" }} />
                            <Typography variant="body2" fontWeight={500}>
                              {option.language}
            </Typography>
                          </Box>
                          <Chip
                            label={option.proficiency}
                            size="small"
                            sx={{
                              bgcolor: "rgba(53, 84, 209, 0.1)",
                              color: "#3554D1",
                              fontWeight: 500,
                            }}
                          />
                        </Paper>
                      </Grid>
                    ))}
                  </Grid>
                </CardContent>
              </Card>
            </Grid>
          </Grid>
        </div>

        {/* Booking Warning Section */}
        {guide.bookingDetails && guide.bookingDetails.length > 0 && (
          <div className="col-12">
            <Card
              sx={{
                mt: 2,
                bgcolor: "#fff4e5",
                border: "2px solid #ff9800",
                borderRadius: 2,
              }}
            >
              <CardContent>
                <Typography
                  variant="h6"
                  sx={{
                    color: "#d84315",
                    display: "flex",
                    alignItems: "center",
                    gap: 1,
                    mb: 2,
                  }}
                >
                  <WarningIcon /> Guide Already Booked
                </Typography>
                <Grid container spacing={2}>
            {guide.bookingDetails.map((booking, index) => (
                    <Grid item xs={12} key={index}>
                      <Paper
                        elevation={0}
                        sx={{
                          p: 2,
                          bgcolor: "rgba(255, 152, 0, 0.05)",
                          border: "1px solid rgba(255, 152, 0, 0.2)",
                          borderRadius: 1,
                        }}
                      >
                        <Box
                          sx={{ display: "flex", alignItems: "center", mb: 1 }}
                        >
                          <Typography
                            variant="subtitle1"
                            sx={{
                              color: "#d84315",
                              fontWeight: 600,
                              display: "flex",
                              alignItems: "center",
                              gap: 1,
                            }}
                          >
                            <EventIcon />
                            Booking {index + 1}
                          </Typography>
                        </Box>
                        <Grid container spacing={3}>
                          <Grid item xs={12} sm={6}>
                            <Box
                              sx={{
                                p: 1.5,
                                bgcolor: "rgba(255, 255, 255, 0.5)",
                                borderRadius: 1,
                                border: "1px solid rgba(255, 152, 0, 0.3)",
                              }}
                            >
                  <Typography
                    variant="body2"
                                color="textSecondary"
                                sx={{ mb: 0.5 }}
                              >
                                Start Time
                              </Typography>
                              <Typography
                                variant="body1"
                    fontWeight="bold"
                                color="#d84315"
                                sx={{
                                  display: "flex",
                                  alignItems: "center",
                                  gap: 1,
                                }}
                              >
                                <AccessTimeIcon sx={{ fontSize: "1.2rem" }} />
                    {booking.start_time}
                  </Typography>
                            </Box>
                          </Grid>
                          <Grid item xs={12} sm={6}>
                            <Box
                              sx={{
                                p: 1.5,
                                bgcolor: "rgba(255, 255, 255, 0.5)",
                                borderRadius: 1,
                                border: "1px solid rgba(255, 152, 0, 0.3)",
                              }}
                            >
                  <Typography
                    variant="body2"
                                color="textSecondary"
                                sx={{ mb: 0.5 }}
                              >
                                End Time
                              </Typography>
                              <Typography
                                variant="body1"
                    fontWeight="bold"
                                color="#d84315"
                                sx={{
                                  display: "flex",
                                  alignItems: "center",
                                  gap: 1,
                                }}
                              >
                                <AccessTimeIcon sx={{ fontSize: "1.2rem" }} />
                    {booking.end_time}
                  </Typography>
                            </Box>
                          </Grid>
                        </Grid>
                      </Paper>
                    </Grid>
                  ))}
                </Grid>
              </CardContent>
            </Card>
          </div>
        )}
      </div>

      {/* Image Dialog */}
      <Dialog
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
            alt="License"
            style={{
              width: "100%",
              height: "auto",
              display: "block",
            }}
          />
        </DialogContent>
      </Dialog>
    </>
  );
};

export default Overview;
