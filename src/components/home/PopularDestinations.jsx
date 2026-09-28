import React, { useState } from "react";
import {
  Box,
  Container,
  Typography,
  Grid,
  Chip,
  Stack,
  Fade,
  Grow,
} from "@mui/material";

const CORPORATE_NAVY = "#13357b";

const PopularDestinations = ({ countries, destinations: destinationsProp }) => {
  const countryList = countries ?? [
    "Singapore",
    "Malaysia",
    "Thailand",
    "Indonesia",
    "Australia",
    "New Zealand",
    "Laos",
    "Cambodia",
    "UAE",
  ];
  const [selectedCountry, setSelectedCountry] = useState(countryList[0]);

  const destinations = destinationsProp ?? [];

  const filteredDestinations = destinations.filter(
    (dest) => dest.country === selectedCountry
  );

  return (
    <Box sx={{ py: { xs: 8, md: 10 }, bgcolor: "#f5f7fa" }} id="destinations">
      <Container maxWidth="lg">
        <Fade in timeout={600}>
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
              Coverage
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
              Destinations We Enable
            </Typography>

            <Typography
              sx={{
                color: "#5a6577",
                fontSize: "1rem",
                lineHeight: 1.7,
                maxWidth: 560,
                mx: "auto",
                mb: 4,
              }}
            >
              Markets where our partners operate — Singapore, Malaysia, Thailand,
              Indonesia, Australia, New Zealand, Laos, Cambodia, and UAE.
            </Typography>

            <Stack
              direction="row"
              spacing={1}
              useFlexGap
              sx={{
                justifyContent: "center",
                flexWrap: "wrap",
                gap: 1,
                maxWidth: 720,
                mx: "auto",
              }}
            >
              {countryList.map((country) => (
                <Chip
                  key={country}
                  label={country}
                  onClick={() => setSelectedCountry(country)}
                  sx={{
                    bgcolor:
                      selectedCountry === country ? CORPORATE_NAVY : "white",
                    color: selectedCountry === country ? "white" : "#334155",
                    fontWeight: 600,
                    fontSize: "0.8rem",
                    px: 1,
                    py: 1.75,
                    borderRadius: "6px",
                    border: "1px solid",
                    borderColor:
                      selectedCountry === country
                        ? CORPORATE_NAVY
                        : "#d0d7e2",
                    transition: "all 0.2s ease",
                    "&:hover": {
                      bgcolor:
                        selectedCountry === country
                          ? "#0f2d6b"
                          : "#eef2f7",
                    },
                    cursor: "pointer",
                    height: 32,
                  }}
                />
              ))}
            </Stack>
          </Box>
        </Fade>

        <Grid container spacing={3}>
          {filteredDestinations.map((destination, index) => (
            <Grid item xs={12} sm={6} md={6} key={destination.id}>
              <Grow in timeout={500 + index * 120}>
                <Box
                  sx={{
                    display: "flex",
                    bgcolor: "white",
                    borderRadius: "8px",
                    overflow: "hidden",
                    border: "1px solid #e2e8f0",
                    height: "100%",
                    transition: "box-shadow 0.25s ease, border-color 0.25s ease",
                    "&:hover": {
                      boxShadow: "0 8px 24px rgba(19, 53, 123, 0.08)",
                      borderColor: "#c5d0e0",
                    },
                  }}
                >
                  <Box
                    sx={{
                      width: { xs: 110, sm: 140 },
                      flexShrink: 0,
                      backgroundImage: `url(${destination.image})`,
                      backgroundSize: "cover",
                      backgroundPosition: "center",
                    }}
                  />
                  <Box sx={{ p: 2.5, display: "flex", flexDirection: "column", justifyContent: "center" }}>
                    <Typography
                      sx={{
                        fontWeight: 700,
                        color: CORPORATE_NAVY,
                        fontSize: "1.05rem",
                        mb: 0.75,
                      }}
                    >
                      {destination.name}
                    </Typography>
                    <Typography
                      sx={{
                        color: "#64748b",
                        fontSize: "0.875rem",
                        lineHeight: 1.55,
                      }}
                    >
                      {destination.description}
                    </Typography>
                  </Box>
                </Box>
              </Grow>
            </Grid>
          ))}
        </Grid>
      </Container>
    </Box>
  );
};

export default PopularDestinations;
