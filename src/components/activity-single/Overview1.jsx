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

const Overview1 = ({ vehicles: vehiclesProp, compact = false }) => {
  const location = useLocation();
  const checkoutVehicle = useSelector(
    (state) => state.pickupDrop.checkoutVehicle
  );
  const vehicles =
    vehiclesProp || location.state?.vehicles || checkoutVehicle || null;
  if (!vehicles) {
    return null;
  }
  const fieldFontSize = compact ? "12px" : "16px";
  const iconSize = compact ? 16 : 24;

  return (
    <>
      <div className={compact ? "row x-gap-10 y-gap-10" : "row x-gap-40 y-gap-40"}>
        <div className="col-12">
          <h3 className={compact ? "text-15 fw-500" : "text-22 fw-500"}>
            Overview
          </h3>

          {vehicles.description ? (
            <div
              className={compact ? "text-dark-1 text-13 mt-5" : "text-dark-1 text-15 mt-10"}
              dangerouslySetInnerHTML={{
                __html: vehicles.description,
              }}
              style={{
                color: "#333",
                minHeight: compact ? "0" : "20px",
                margin: compact ? "4px 0" : "10px 0",
              }}
            />
          ) : (
            <div
              className={compact ? "text-dark-1 text-13 mt-5" : "text-dark-1 text-15 mt-10"}
            >
              No description available
            </div>
          )}

          <Card
            elevation={compact ? 0 : 1}
            sx={{
              mt: compact ? 1 : 2.5,
              mb: compact ? 0 : 10,
              p: compact ? 1 : 2,
              borderRadius: compact ? 1 : 2,
              bgcolor: "rgba(255, 255, 255, 0.9)",
              border: "1px solid rgba(0, 0, 0, 0.1)",
            }}
          >
            <Grid container spacing={compact ? 1 : 2}>
              <Grid item xs={12} sm={6} md={compact ? 6 : 3}>
                <Box sx={{ display: "flex", alignItems: "center", gap: compact ? 0.75 : 1 }}>
                  <CarIcon sx={{ color: "#3554D1", fontSize: iconSize }} />
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    fontWeight="700"
                    fontSize={fieldFontSize}
                    lineHeight={1.3}
                  >
                    Type: {vehicles.vehicle_type || "N/A"}
                  </Typography>
                </Box>
              </Grid>
              <Grid item xs={12} sm={6} md={compact ? 6 : 3}>
                <Box sx={{ display: "flex", alignItems: "center", gap: compact ? 0.75 : 1 }}>
                  <ModelIcon sx={{ color: "#3554D1", fontSize: iconSize }} />
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    fontWeight="700"
                    fontSize={fieldFontSize}
                    lineHeight={1.3}
                  >
                    Model: {vehicles.vehicle_model || "N/A"}
                  </Typography>
                </Box>
              </Grid>
              <Grid item xs={12} sm={6} md={compact ? 6 : 3}>
                <Box sx={{ display: "flex", alignItems: "center", gap: compact ? 0.75 : 1 }}>
                  <YearIcon sx={{ color: "#3554D1", fontSize: iconSize }} />
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    fontWeight="700"
                    fontSize={fieldFontSize}
                    lineHeight={1.3}
                  >
                    Model Year: {vehicles.model_year || "N/A"}
                  </Typography>
                </Box>
              </Grid>
              <Grid item xs={12} sm={6} md={compact ? 6 : 3}>
                <Box sx={{ display: "flex", alignItems: "center", gap: compact ? 0.75 : 1 }}>
                  <SeatIcon sx={{ color: "#3554D1", fontSize: iconSize }} />
                  <Typography
                    variant="body2"
                    color="text.secondary"
                    fontWeight="700"
                    fontSize={fieldFontSize}
                    lineHeight={1.3}
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
  compact: PropTypes.bool,
};

Overview1.defaultProps = {
  vehicles: null,
  compact: false,
};

export default Overview1;
