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
import { useSelector } from "react-redux";

const Overview2 = ({ vehicles: vehiclesProp, compact = false }) => {
  const location = useLocation();
  const checkoutVehicle = useSelector(
    (state) => state.localtour.checkoutVehicle
  );
  const vehicles =
    vehiclesProp || location.state?.vehicles || checkoutVehicle || null;
  if (!vehicles) return null;

  return (
    <>
      <div className="row x-gap-40 y-gap-40">
        <div className="col-12">
          <h3 className={compact ? "text-18 fw-500" : "text-22 fw-500"}>
            Overview
          </h3>
          <div className="text-dark-1 text-15 mt-10">
            {vehicles.description ? (
              <div dangerouslySetInnerHTML={{ __html: vehicles.description }} />
            ) : (
              "No description available"
            )}
          </div>

          <Card
            elevation={1}
            sx={{
              mt: compact ? 1.5 : 2.5,
              mb: compact ? 0 : 10,
              p: compact ? 1.5 : 2,
              borderRadius: 2,
              bgcolor: "rgba(255, 255, 255, 0.9)",
              border: "1px solid rgba(0, 0, 0, 0.1)",
            }}
          >
            <Grid container spacing={2}>
              <Grid item xs={12} sm={6} md={3}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                  <CarIcon sx={{ color: "#3554D1" }} />
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    fontWeight="700"
                    fontSize={compact ? "14px" : "16px"}
                  >
                    Type: {vehicles.vehicle_type || "N/A"}
                  </Typography>
                </Box>
              </Grid>
              <Grid item xs={12} sm={6} md={3}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                  <ModelIcon sx={{ color: "#3554D1" }} />
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    fontWeight="700"
                    fontSize={compact ? "14px" : "16px"}
                  >
                    Model: {vehicles.vehicle_model || "N/A"}
                  </Typography>
                </Box>
              </Grid>
              <Grid item xs={12} sm={6} md={3}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                  <YearIcon sx={{ color: "#3554D1" }} />
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    fontWeight="700"
                    fontSize={compact ? "14px" : "16px"}
                  >
                    Model Year: {vehicles.model_year || "N/A"}
                  </Typography>
                </Box>
              </Grid>
              <Grid item xs={12} sm={6} md={3}>
                <Box sx={{ display: "flex", alignItems: "center", gap: 1 }}>
                  <SeatIcon sx={{ color: "#3554D1" }} />
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    fontWeight="700"
                    fontSize={compact ? "14px" : "16px"}
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

Overview2.propTypes = {
  vehicles: PropTypes.shape({
    description: PropTypes.string,
    vehicle_type: PropTypes.string,
    vehicle_model: PropTypes.string,
    model_year: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
    seating_capacity: PropTypes.oneOfType([
      PropTypes.number,
      PropTypes.string,
    ]),
  }),
  compact: PropTypes.bool,
};

Overview2.defaultProps = {
  vehicles: null,
  compact: false,
};

export default Overview2;
