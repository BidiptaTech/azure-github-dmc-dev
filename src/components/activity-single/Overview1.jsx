import React from "react";
import { Card, Grid, Box, Typography } from "@mui/material";
import {
  DirectionsCar as CarIcon,
  EventSeat as SeatIcon,
  Build as ModelIcon,
  DateRange as YearIcon,
} from "@mui/icons-material";
import PropTypes from "prop-types";
import { useLocation } from "react-router-dom";

const Overview1 = () => {
  const location = useLocation();
  const { vehicles } = location.state;
  if (!vehicles) {
    return null;
  }
  console.log("vehicles5", vehicles);
  return (
    <>
      <div className="row x-gap-40 y-gap-40">
        <div className="col-12">
          <h3 className="text-22 fw-500">Overview</h3>
          {/* Temporary debug render */}

          {vehicles.description ? (
            <div
              className="text-dark-1 text-15 mt-10"
              dangerouslySetInnerHTML={{
                __html: vehicles.description,
              }}
              style={{
                color: "#333",
                minHeight: "20px",
                margin: "10px 0",
              }}
            />
          ) : (
            <div className="text-dark-1 text-15 mt-10">
              No description available
            </div>
          )}

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
                    Type: {vehicles.vehicle_type || "N/A"}
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
                    Model: {vehicles.vehicle_model || "N/A"}
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
                    Model Year: {vehicles.model_year || "N/A"}
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
                    Capacity: {vehicles.seating_capacity || "N/A"} seats
                  </Typography>
                </Box>
              </Grid>
            </Grid>
          </Card>
        </div>
      </div>
    </>
  );
};

Overview1.propTypes = {
  vehicles: PropTypes.shape({
    description: PropTypes.string,
    vehicle_type: PropTypes.string,
    vehicle_model: PropTypes.string,
    model_year: PropTypes.number,
    seating_capacity: PropTypes.number,
  }),
};

Overview1.defaultProps = {
  vehicles: null,
};

export default Overview1;
