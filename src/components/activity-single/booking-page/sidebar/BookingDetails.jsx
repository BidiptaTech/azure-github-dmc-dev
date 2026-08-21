import { useEffect, useState } from "react";
import { styled } from "@mui/material/styles";
import { Box, Typography, List, Card, CardContent, Grid } from "@mui/material";
import PersonIcon from "@mui/icons-material/Person";
import CheckCircleIcon from "@mui/icons-material/CheckCircle"; // Import CheckCircleIcon
import dayjs from "dayjs";
import { useDispatch, useSelector } from "react-redux";
import { setHotelDetails } from "@/slice/hotel/HotelDetailsSlice";
import PricingSummary from "./PricingSummary";
import PricingSummary1 from "./PricingSummary1";

const RoomCard = styled(Card)(({ theme }) => ({
  background: "white",
  borderRadius: "15px",
  marginBottom: "20px",
  transition: "transform 0.2s ease-in-out",
  "&:hover": {
    transform: "translateY(-5px)",
    boxShadow: "0 8px 20px rgba(53, 84, 209, 0.15)",
  },
}));

const RoomTypeHeader = styled(Box)(({ theme }) => ({
  background: "linear-gradient(135deg, #3554D1 0%, #5073FB 100%)",
  padding: "15px 20px",
  borderTopLeftRadius: "15px",
  borderTopRightRadius: "15px",
  color: "white",
}));

const BookingDetails = ({ image, bookingDetails }) => {
  const type = useSelector((state) => state.tourguide.type);
  const PriceHide = useSelector((state) => state.auth.PriceHide);
  console.log("typeeee", type);
  console.log("detailsss", bookingDetails);
  const formatDate = (date) => {
    if (!date) return "Not Selected";
    return dayjs(date).format("ddd, D MMM'YY");
  };

  return type === "entryport" ||
    type === "travelpoint" ||
    type === "travelpointzone" ? (
    <div className="px-30 py-30 border-light rounded-4">
      <div className="text-20 fw-500 mb-30">Your booking details</div>
      <div className="row x-gap-15 y-gap-20">
        <div className="col-auto">
          <img
            src={image}
            alt="image"
            className="size-140 rounded-4 object-cover"
          />
        </div>
        <div className="col">
          <div className="d-flex x-gap-5 pb-10">
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
          </div>
          <div className="lh-17 fw-500">{bookingDetails[0].vehicles_name}</div>
          <div className="text-14 lh-15 mt-5">{bookingDetails[0].city}</div>
        </div>
      </div>

      <div className="border-top-light mt-30 mb-20" />
      <div className="row y-gap-10 justify-between">
        <div className="col-auto">
          <div className="text-15 fw-bold">Pick Up Location</div>
          <div className="fw-500">{bookingDetails[0].entrypickup}</div>
        </div>
        <div className="col-auto">
          <div className="text-15 fw-bold">Drop Off Location</div>
          <div className="fw-500">{bookingDetails[0].entrydropoff}</div>
        </div>
      </div>

      <div className="border-top-light mt-30 mb-20" />
      <Box
        sx={{
          display: "flex",
          justifyContent: "space-between",
          gap: 2,
          flexWrap: "wrap",
          backgroundColor: "#F8FAFF",
          p: 2,
          borderRadius: "8px",
          border: "1px solid rgba(53, 84, 209, 0.1)",
        }}
      >
        <RoomCard>
          <RoomTypeHeader>
            <Typography variant="h6" sx={{ fontWeight: 600, color: "white" }}>
              Booking Date, Time and Passenger Details
            </Typography>
          </RoomTypeHeader>
          <CardContent sx={{ p: 3 }}>
            <Grid container spacing={2}>
              <Grid item xs={12}>
                <Box
                  sx={{
                    display: "flex",
                    justifyContent: "space-between",
                    gap: 2,
                  }}
                >
                  <Box
                    sx={{ display: "flex", flexDirection: "column", gap: 1 }}
                  >
                    <Typography
                      variant="subtitle1"
                      sx={{ fontWeight: 600, color: "#3554D1" }}
                    >
                      Booking Date
                    </Typography>
                    <Typography
                      variant="body1"
                      sx={{ fontWeight: 500, color: "#3554D1" }}
                    >
                      {formatDate(bookingDetails[0].bookingDate)}
                      {/* // .toLocaleDateString("en-GB")
                        // .replace(/\//g, "-") */}
                    </Typography>
                  </Box>
                  <Box
                    sx={{ display: "flex", flexDirection: "column", gap: 1 }}
                  >
                    <Typography
                      variant="subtitle1"
                      sx={{ fontWeight: 600, color: "#3554D1" }}
                    >
                      Time
                    </Typography>
                    <Typography
                      variant="body1"
                      sx={{ fontWeight: 500, color: "#3554D1" }}
                    >
                      {bookingDetails[0].entrytime}
                    </Typography>
                  </Box>
                </Box>
              </Grid>

              {/* Guest Details Section */}
              <Grid item xs={12}>
                <Box
                  sx={{
                    mb: 3,
                    pb: 2,
                    borderBottom: "1px dashed rgba(53, 84, 209, 0.2)",
                    "&:last-child": { borderBottom: "none", mb: 0, pb: 0 },
                  }}
                >
                  <Typography
                    variant="subtitle1"
                    sx={{ fontWeight: 600, color: "#3554D1" }}
                  >
                    Guest Details
                  </Typography>
                  <Box
                    sx={{
                      display: "flex",
                      gap: 2,
                      flexWrap: "nowrap",
                      justifyContent: "flex-start",
                    }}
                  >
                    <Box
                      sx={{
                        display: "flex",
                        flexDirection: "column",
                        alignItems: "center",
                        backgroundColor: "#F8FAFF",
                        p: 0.7,
                        borderRadius: "8px",
                        border: "1px solid rgba(53, 84, 209, 0.1)",
                      }}
                    >
                      <Box sx={{ display: "flex", alignItems: "center" }}>
                        <PersonIcon sx={{ color: "#3554D1", mr: 1 }} />
                        <Typography
                          variant="body1"
                          sx={{ fontWeight: 500, color: "#3554D1" }}
                        >
                          Adults: {bookingDetails[0].adults}
                        </Typography>
                      </Box>
                    </Box>
                    <Box
                      sx={{
                        display: "flex",
                        flexDirection: "column",
                        alignItems: "center",
                        backgroundColor: "#F8FAFF",
                        p: 0.7,
                        borderRadius: "8px",
                        border: "1px solid rgba(53, 84, 209, 0.1)",
                      }}
                    >
                      <Box sx={{ display: "flex", alignItems: "center" }}>
                        <PersonIcon sx={{ color: "#3554D1", mr: 1 }} />
                        <Typography
                          variant="body1"
                          sx={{ fontWeight: 500, color: "#3554D1" }}
                        >
                          Children: {bookingDetails[0].children}
                        </Typography>
                      </Box>
                    </Box>
                  </Box>
                </Box>
              </Grid>
            </Grid>
          </CardContent>
        </RoomCard>
      </Box>
      {PriceHide === "0" && (
        <>
          <div className="border-top-light mt-30 mb-20" />
          <PricingSummary
            totalPrice={bookingDetails[0].totalPrice}
            rooms={null}
            Tax={bookingDetails[0].Tax}
          />
        </>
      )}
    </div>
  ) : type === "exitport" ? (
    <div className="px-30 py-30 border-light rounded-4">
      <div className="text-20 fw-500 mb-30">Your booking details</div>
      <div className="row x-gap-15 y-gap-20">
        <div className="col-auto">
          <img
            src={image}
            alt="image"
            className="size-140 rounded-4 object-cover"
          />
        </div>
        {/* End .col */}
        <div className="col">
          <div className="d-flex x-gap-5 pb-10">
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
          </div>
          {/* End ratings */}
          <div className="lh-17 fw-500">{bookingDetails[0].vehicles_name}</div>
          <div className="text-14 lh-15 mt-5">{bookingDetails[0].city}</div>
        </div>
        {/* End .col */}
      </div>
      {/* End .row */}

      <div className="border-top-light mt-30 mb-20" />
      <div className="row y-gap-10 justify-between">
        <div className="col-auto">
          <div className="text-15 fw-bold">Pick Up Location</div>
          <div className="fw-500">{bookingDetails[0].exitpickup}</div>
        </div>
        <div className="col-auto">
          <div className="text-15 fw-bold">Drop Off Location</div>
          <div className="fw-500">{bookingDetails[0].exitdropoff}</div>
        </div>
      </div>
      {/* End row */}
      <div className="border-top-light mt-30 mb-20" />
      <Box
        sx={{
          display: "flex",
          justifyContent: "space-between",
          gap: 2,
          flexWrap: "wrap",
          backgroundColor: "#F8FAFF",
          p: 2,
          borderRadius: "8px",
          border: "1px solid rgba(53, 84, 209, 0.1)",
        }}
      >
        <RoomCard>
          <RoomTypeHeader>
            <Typography variant="h6" sx={{ fontWeight: 600, color: "white" }}>
              Booking Date, Time and Passenger Details
            </Typography>
          </RoomTypeHeader>
          <CardContent sx={{ p: 3 }}>
            {/* Booking Date and Time */}
            <Box
              sx={{
                display: "flex",
                justifyContent: "space-between",
                gap: 2,
              }}
            >
              <Box sx={{ display: "flex", flexDirection: "column", gap: 1 }}>
                <Typography
                  variant="subtitle1"
                  sx={{ fontWeight: 600, color: "#3554D1" }}
                >
                  Booking Date
                </Typography>
                <Typography
                  variant="body1"
                  sx={{ fontWeight: 500, color: "#3554D1" }}
                >
                  {formatDate(bookingDetails[0].bookingDate)}
                  {/* // .toLocaleDateString("en-GB")
                    // .replace(/\//g, "-") */}
                </Typography>
              </Box>
              <Box sx={{ display: "flex", flexDirection: "column", gap: 1 }}>
                <Typography
                  variant="subtitle1"
                  sx={{ fontWeight: 600, color: "#3554D1" }}
                >
                  Time
                </Typography>
                <Typography
                  variant="body1"
                  sx={{ fontWeight: 500, color: "#3554D1" }}
                >
                  {bookingDetails[0].entrytime}
                </Typography>
              </Box>
            </Box>

            {/* Passenger Details Section */}
            <Box
              sx={{
                mt: 3,
                pb: 2,
                borderBottom: "1px dashed rgba(53, 84, 209, 0.2)",
              }}
            >
              <Typography
                variant="subtitle1"
                sx={{ fontWeight: 600, color: "#3554D1" }}
              >
                Guest Details
              </Typography>
              <Box
                sx={{
                  display: "flex",
                  gap: 2,
                  flexWrap: "nowrap",
                  justifyContent: "flex-start",
                }}
              >
                {/* Adults Box */}
                <Box
                  sx={{
                    display: "flex",
                    flexDirection: "column",
                    alignItems: "center",
                    backgroundColor: "#F8FAFF",
                    p: 0.7,
                    borderRadius: "8px",
                    border: "1px solid rgba(53, 84, 209, 0.1)",
                  }}
                >
                  <Box sx={{ display: "flex", alignItems: "center" }}>
                    <PersonIcon sx={{ color: "#3554D1", mr: 1 }} />
                    <Typography
                      variant="body1"
                      sx={{ fontWeight: 500, color: "#3554D1" }}
                    >
                      Adults: {bookingDetails[0].adults}
                    </Typography>
                  </Box>
                </Box>

                {/* Children Box */}
                <Box
                  sx={{
                    display: "flex",
                    flexDirection: "column",
                    alignItems: "center",
                    backgroundColor: "#F8FAFF",
                    p: 0.7,
                    borderRadius: "8px",
                    border: "1px solid rgba(53, 84, 209, 0.1)",
                  }}
                >
                  <Box sx={{ display: "flex", alignItems: "center" }}>
                    <PersonIcon sx={{ color: "#3554D1", mr: 1 }} />
                    <Typography
                      variant="body1"
                      sx={{ fontWeight: 500, color: "#3554D1" }}
                    >
                      Children: {bookingDetails[0].children}
                    </Typography>
                  </Box>
                </Box>
              </Box>
            </Box>
          </CardContent>
        </RoomCard>
      </Box>
      {PriceHide === "0" && (
        <>
          <div className="border-top-light mt-30 mb-20" />

          <PricingSummary
            totalPrice={bookingDetails[0].totalPrice}
            rooms={null}
            Tax={bookingDetails[0].Tax}
          />
        </>
      )}
    </div>
  ) : type === "travelhourly" ? (
    <div className="px-30 py-30 border-light rounded-4">
      <div className="text-20 fw-500 mb-30">Your booking details</div>
      <div className="row x-gap-15 y-gap-20">
        <div className="col-auto">
          <img
            src={image}
            alt="image"
            className="size-140 rounded-4 object-cover"
          />
        </div>
        {/* End .col */}
        <div className="col">
          <div className="d-flex x-gap-5 pb-10">
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
          </div>
          {/* End ratings */}
          <div className="lh-17 fw-500">{bookingDetails[0].vehicles_name}</div>
          <div className="text-14 lh-15 mt-5">{bookingDetails[0].city}</div>
        </div>
        {/* End .col */}
      </div>
      {/* End .row */}

      <div className="border-top-light mt-30 mb-20" />
      <div className="row y-gap-10 justify-between">
        <div className="col-auto">
          <div className="text-15 fw-bold">Pick Up Location</div>
          <div className="fw-500">{bookingDetails[0].entrypickup}</div>
        </div>
      </div>
      {/* End row */}
      <div className="border-top-light mt-30 mb-20" />
      <Box
        sx={{
          display: "flex",
          justifyContent: "space-between",
          gap: 2,
          flexWrap: "wrap",
          backgroundColor: "#F8FAFF",
          p: 2,
          borderRadius: "8px",
          border: "1px solid rgba(53, 84, 209, 0.1)",
        }}
      >
        <RoomCard>
          <RoomTypeHeader>
            <Typography variant="h6" sx={{ fontWeight: 600, color: "white" }}>
              Booking Date, Time and Passenger Details
            </Typography>
          </RoomTypeHeader>
          <CardContent sx={{ p: 3 }}>
            {/* Booking Date and Total Hours */}
            <Box
              sx={{
                display: "flex",
                justifyContent: "space-between",
                gap: 2,
              }}
            >
              <Box sx={{ display: "flex", flexDirection: "column", gap: 1 }}>
                <Typography
                  variant="subtitle1"
                  sx={{ fontWeight: 600, color: "#3554D1" }}
                >
                  Booking Date
                </Typography>
                <Typography
                  variant="body1"
                  sx={{ fontWeight: 500, color: "#3554D1" }}
                >
                  {formatDate(bookingDetails[0].bookingDate)}
                  {/* // .toLocaleDateString("en-GB")
                    // .replace(/\//g, "-") */}
                </Typography>
              </Box>
              <Box sx={{ display: "flex", flexDirection: "column", gap: 1 }}>
                <Typography
                  variant="subtitle1"
                  sx={{ fontWeight: 600, color: "#3554D1" }}
                >
                  Total Hours
                </Typography>
                <Typography
                  variant="body1"
                  sx={{ fontWeight: 500, color: "#3554D1" }}
                >
                  {bookingDetails[0].selectedHours}
                </Typography>
              </Box>
            </Box>

            {/* Passenger Details Section */}
            <Box
              sx={{
                mt: 3,
                pb: 2,
                borderBottom: "1px dashed rgba(53, 84, 209, 0.2)",
              }}
            >
              <Typography
                variant="subtitle1"
                sx={{ fontWeight: 600, color: "#3554D1" }}
              >
                Guest Details
              </Typography>
              <Box
                sx={{
                  display: "flex",
                  gap: 2,
                  flexWrap: "nowrap",
                  justifyContent: "flex-start",
                }}
              >
                {/* Adults Box */}
                <Box
                  sx={{
                    display: "flex",
                    flexDirection: "column",
                    alignItems: "center",
                    backgroundColor: "#F8FAFF",
                    p: 0.7,
                    borderRadius: "8px",
                    border: "1px solid rgba(53, 84, 209, 0.1)",
                  }}
                >
                  <Box sx={{ display: "flex", alignItems: "center" }}>
                    <PersonIcon sx={{ color: "#3554D1", mr: 1 }} />
                    <Typography
                      variant="body1"
                      sx={{ fontWeight: 500, color: "#3554D1" }}
                    >
                      Adults: {bookingDetails[0].adults}
                    </Typography>
                  </Box>
                </Box>

                {/* Children Box */}
                <Box
                  sx={{
                    display: "flex",
                    flexDirection: "column",
                    alignItems: "center",
                    backgroundColor: "#F8FAFF",
                    p: 0.7,
                    borderRadius: "8px",
                    border: "1px solid rgba(53, 84, 209, 0.1)",
                  }}
                >
                  <Box sx={{ display: "flex", alignItems: "center" }}>
                    <PersonIcon sx={{ color: "#3554D1", mr: 1 }} />
                    <Typography
                      variant="body1"
                      sx={{ fontWeight: 500, color: "#3554D1" }}
                    >
                      Children: {bookingDetails[0].children}
                    </Typography>
                  </Box>
                </Box>
              </Box>
            </Box>
          </CardContent>
        </RoomCard>
      </Box>
      {PriceHide === "0" && (
        <>
          <div className="border-top-light mt-30 mb-20" />

          <PricingSummary
            totalPrice={bookingDetails[0].totalPrice}
            rooms={null}
            Tax={bookingDetails[0].Tax}
          />
        </>
      )}
    </div>
  ) : type === "guide" ? (
    <div className="px-30 py-30 border-light rounded-4">
      <div className="text-20 fw-500 mb-30">Your booking details</div>
      <div className="row x-gap-15 y-gap-20">
        <div className="col-auto">
          <img
            src={image}
            alt="image"
            className="size-140 rounded-4 object-cover"
          />
        </div>
        {/* End .col */}
        <div className="col">
          <div className="d-flex x-gap-5 pb-10">
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
            <i className="icon-star text-yellow-1 text-10" />
          </div>
          {/* End ratings */}
          <div className="lh-17 fw-500">{bookingDetails[0].guide_name}</div>
          <div className="text-14 lh-15 mt-5">
            {bookingDetails[0].entrypickup}
          </div>
        </div>
        {/* End .col */}
      </div>
      {/* End .row */}

      {/* <div className="border-top-light mt-30 mb-20" />
      <div className="row y-gap-10 justify-between">
        <div className="col-auto">
          <div className="text-15 fw-bold">Pick Up Location</div>
          <div className="fw-500">{bookingDetails[0].entrypickup}</div>
        </div>
      </div> */}
      {/* End row */}
      <div className="border-top-light mt-30 mb-20" />
      <Box
        sx={{
          display: "flex",
          justifyContent: "space-between",
          gap: 2,
          flexWrap: "wrap",
          backgroundColor: "#F8FAFF",
          p: 2,
          borderRadius: "8px",
          border: "1px solid rgba(53, 84, 209, 0.1)",
        }}
      >
        <RoomCard>
          <RoomTypeHeader>
            <Typography variant="h6" sx={{ fontWeight: 600, color: "white" }}>
              Booking Date, Time, and Passenger Details
            </Typography>
          </RoomTypeHeader>
          <CardContent sx={{ p: 3 }}>
            {/* Booking Date and Time */}
            <Box
              sx={{
                display: "flex",
                justifyContent: "space-between",
                gap: 2,
              }}
            >
              <Box sx={{ display: "flex", flexDirection: "column", gap: 1 }}>
                <Typography
                  variant="subtitle1"
                  sx={{ fontWeight: 600, color: "#3554D1" }}
                >
                  Booking Date
                </Typography>
                <Typography
                  variant="body1"
                  sx={{ fontWeight: 500, color: "#3554D1" }}
                >
                  {formatDate(bookingDetails[0].bookingDate)}
                  {/* // .toLocaleDateString("en-GB")
                    // .replace(/\//g, "-") */}
                </Typography>
              </Box>
              <Box sx={{ display: "flex", flexDirection: "column", gap: 1 }}>
                <Typography
                  variant="subtitle1"
                  sx={{ fontWeight: 600, color: "#3554D1" }}
                >
                  Time
                </Typography>
                <Typography
                  variant="body1"
                  sx={{ fontWeight: 500, color: "#3554D1" }}
                >
                  {bookingDetails[0].entrytime}
                </Typography>
              </Box>
            </Box>

            {/* Total Hours */}
            <Box
              sx={{
                mt: 2,
                pb: 2,
                borderBottom: "1px dashed rgba(53, 84, 209, 0.2)",
              }}
            >
              <Typography
                variant="subtitle1"
                sx={{ fontWeight: 600, color: "#3554D1" }}
              >
                Total Hours
              </Typography>
              <Typography
                variant="body1"
                sx={{ fontWeight: 500, color: "#3554D1" }}
              >
                {bookingDetails[0].hours}
              </Typography>
            </Box>

            {/* Passenger Details */}
            <Box
              sx={{
                mt: 3,
                pb: 2,
                borderBottom: "1px dashed rgba(53, 84, 209, 0.2)",
              }}
            >
              <Typography
                variant="subtitle1"
                sx={{ fontWeight: 600, color: "#3554D1" }}
              >
                Guest Details
              </Typography>
              <Box
                sx={{
                  mt: 3,
                  display: "flex",
                  gap: 2,
                  flexWrap: "nowrap",
                  justifyContent: "flex-start",
                }}
              >
                {/* Adults Box */}
                <Box
                  sx={{
                    display: "flex",
                    flexDirection: "column",
                    alignItems: "center",
                    backgroundColor: "#F8FAFF",
                    p: 0.7,
                    borderRadius: "8px",
                    border: "1px solid rgba(53, 84, 209, 0.1)",
                  }}
                >
                  <Box sx={{ display: "flex", alignItems: "center" }}>
                    <PersonIcon sx={{ color: "#3554D1", mr: 1 }} />
                    <Typography
                      variant="body1"
                      sx={{ fontWeight: 500, color: "#3554D1" }}
                    >
                      Adults: {bookingDetails?.[0]?.adults ?? 0}
                    </Typography>
                  </Box>
                </Box>

                {/* Children Box */}
                <Box
                  sx={{
                    display: "flex",
                    flexDirection: "column",
                    alignItems: "center",
                    backgroundColor: "#F8FAFF",
                    p: 0.7,
                    borderRadius: "8px",
                    border: "1px solid rgba(53, 84, 209, 0.1)",
                  }}
                >
                  <Box sx={{ display: "flex", alignItems: "center" }}>
                    <PersonIcon sx={{ color: "#3554D1", mr: 1 }} />
                    <Typography
                      variant="body1"
                      sx={{ fontWeight: 500, color: "#3554D1" }}
                    >
                      Children: {bookingDetails?.[0]?.children ?? 0}
                    </Typography>
                  </Box>
                </Box>
              </Box>
            </Box>
          </CardContent>
        </RoomCard>
      </Box>
      {PriceHide === "1" && (
        <>
          <div className="border-top-light mt-30 mb-20" />

          <PricingSummary1
            totalPrice={bookingDetails[0].totalPrice}
            surcharge={bookingDetails[0].surcharge}
            basePrice={bookingDetails[0].basePrice}
            Tax={bookingDetails[0].Tax}
          />
        </>
      )}
    </div>
  ) : null;
};

export default BookingDetails;
