import React from "react";
import { Box, Container, Typography, Grid } from "@mui/material";
import {
  AutoAwesome,
  RequestQuote,
  SmartToy,
  Insights,
} from "@mui/icons-material";

const CORPORATE_NAVY = "#13357b";

const WhyChooseUs = () => {
  const features = [
    {
      id: 1,
      icon: <RequestQuote sx={{ fontSize: 28 }} />,
      title: "AI Quotation Engine",
      description:
        "Create personalized itineraries and quotes in minutes with intelligent templates and pricing.",
    },
    {
      id: 2,
      icon: <AutoAwesome sx={{ fontSize: 28 }} />,
      title: "Smart Automation",
      description:
        "Automate inquiries, bookings, supplier confirmations, and follow-ups in one workflow.",
    },
    {
      id: 3,
      icon: <SmartToy sx={{ fontSize: 28 }} />,
      title: "TravHorse AI Companion",
      description:
        "Real-time traveler engagement with alerts, tracking, messaging, and live support.",
    },
    {
      id: 4,
      icon: <Insights sx={{ fontSize: 28 }} />,
      title: "AI Insights Dashboard",
      description:
        "Predict demand, surface revenue opportunities, and remove operational bottlenecks.",
    },
  ];

  return (
    <Box sx={{ py: { xs: 8, md: 10 }, bgcolor: "#f5f7fa" }}>
      <Container maxWidth="lg">
        <Box sx={{ textAlign: "center", mb: 5 }}>
          <Typography
            sx={{
              color: CORPORATE_NAVY,
              fontWeight: 600,
              fontSize: "0.8rem",
              letterSpacing: "0.12em",
              textTransform: "uppercase",
              mb: 1.5,
            }}
          >
            Why Travclicks
          </Typography>
          <Typography
            variant="h2"
            sx={{
              fontSize: { xs: "1.75rem", md: "2.25rem" },
              fontWeight: 700,
              color: "#1a2332",
              mb: 2,
              letterSpacing: "-0.02em",
            }}
          >
            AI-Powered Travel Operations
          </Typography>
          <Typography
            sx={{
              color: "#5a6577",
              fontSize: "1rem",
              lineHeight: 1.7,
              maxWidth: 620,
              mx: "auto",
            }}
          >
            Travclicks Technologies gives travel agencies an always-on AI engine
            that automates operations, accelerates sales, and delivers real-time
            traveler experiences — inside one intelligent system.
          </Typography>
        </Box>

        <Grid container spacing={3}>
          {features.map((feature) => (
            <Grid item xs={12} sm={6} md={3} key={feature.id}>
              <Box
                sx={{
                  height: "100%",
                  bgcolor: "white",
                  border: "1px solid #e2e8f0",
                  borderRadius: "8px",
                  p: 3,
                  transition: "border-color 0.2s ease, box-shadow 0.2s ease",
                  "&:hover": {
                    borderColor: "#c5d0e0",
                    boxShadow: "0 8px 24px rgba(19, 53, 123, 0.06)",
                  },
                }}
              >
                <Box
                  sx={{
                    width: 48,
                    height: 48,
                    borderRadius: "8px",
                    bgcolor: "rgba(19, 53, 123, 0.08)",
                    color: CORPORATE_NAVY,
                    display: "flex",
                    alignItems: "center",
                    justifyContent: "center",
                    mb: 2,
                  }}
                >
                  {feature.icon}
                </Box>
                <Typography
                  sx={{
                    fontWeight: 700,
                    color: "#1a2332",
                    fontSize: "1rem",
                    mb: 1,
                  }}
                >
                  {feature.title}
                </Typography>
                <Typography
                  sx={{
                    color: "#64748b",
                    fontSize: "0.875rem",
                    lineHeight: 1.6,
                  }}
                >
                  {feature.description}
                </Typography>
              </Box>
            </Grid>
          ))}
        </Grid>
      </Container>
    </Box>
  );
};

export default WhyChooseUs;
