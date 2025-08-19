import React, { useState, useEffect } from "react";
import { Tooltip } from "react-tooltip";
import "../../../styles/TourStatus.css";
import { fetchBookingid } from "../../../slice/common/BookingSlice";
import { useDispatch, useSelector } from "react-redux";
import { Box } from "@mui/material";
import hotelIcon from "../../../../public/icons/resort.png";
import airplane from "../../../../public/icons/airplane.png";
import airplane2 from "../../../../public/icons/airplane2.png";
import car1 from "../../../../public/icons/car1.png";
import attractionIcon from "../../../../public/icons/attraction.png";
// import travellersIcon from "../../../../public/icons/travelling.png";
import restaurant from "../../../../public/icons/restaurants.png";
import guideIcon from "../../../../public/icons/tour-guides.png";
import AttractionModal from "../../tour-list/common/AttractionModal";
import RestaurantModal from "../../restaurants/common/RestaurantModal";
import PortModal from "@/components/activity-list/activity-list-v2/PickupDropModal";
import LocalTourModal from "@/components/activity-list/activity-list-v3/LocaltourModal";
import TourguideModal from "@/components/activity-list/activity-list-v1/TourguideModal";
import HotelModal from "@/components/hotel-list/common/HotelModal";

export default function TourStatus() {
  const [selectedDate, setSelectedDate] = useState(null); // Track selected date
  const [modalOpen, setModalOpen] = useState(false); // Modal open state
  const [restaurantModalOpen, setRestaurantModalOpen] = useState(false); // Restaurant modal state
  const [localtourModalOpen, setlocaltourModalOpen] = useState(false); // Restaurant modal state
  const [guideModalOpen, setguideModalOpen] = useState(false);
  const [entryexitModalOpen, setentryexitModalOpen] = useState(false); // Restaurant modal state
  const [range, setRange] = useState([]);
  const [hotelModalOpen, setHotelModalOpen] = useState(false);
  const [portType, setPortType] = useState(""); // "entry" or "exit"

  const { checkIn, checkOut } = useSelector((state) => state.bookings);
  const bookings = useSelector((state) => state.attractions.services || []);
   // console.log("Attraction bookings from Redux:", bookings);

  const restaurantBooking = useSelector(
    (state) => state.restaurants.services || []
  );
  // console.log('Restaurant bookings from Redux:', restaurantBooking);

  const travelPoint = useSelector(
    (state) => state.localtour.pointtopoint || []
  );
  // console.log("point 2 point", travelPoint);
  const travelHourly = useSelector((state) => state.localtour.hourly || []);
  // console.log("hourlyveh", travelHourly);

  const travelZone = useSelector((state) => state.localtour.Zonebook || []);
  // console.log("travelzzzzzz", travelZone);
  const travel = [...travelHourly, ...travelPoint, ...travelZone];
  // console.log("travellll", travel);
  const formData = useSelector((state) => state.pickupDrop.entryport || []);
  const formData1 = useSelector((state) => state.pickupDrop.exitport || []);
  const entryexit = [...formData, ...formData1];
  const bookedguide = useSelector((state) => state.tourguide.bookedguide || []);

  const dateService = useSelector((state) => state.dateService.services || []);
  // console.log("dateService123", dateService);
  // const dateKey = Object.keys(dateService)[0];
  // console.log("dateKey", dateKey);

  const dispatch = useDispatch();
  const { status } = useSelector((state) => state.bookings || []);
  // console.log(status,"status");

  // Get hotel bookings from redux store
  //here i am gett all the data from response and store in a redux and send data to the hotel modal
  const hotelBookings = useSelector((state) => state.hotels.hotelService || []);
  //  console.log(hotelBookings,"hotelBookings");

  useEffect(() => {
    let newDateServiceDates = new Set();

    const formatDate = (date) => {
      if (!date) return "";
      const [year, month, day] = date.split("-").map(Number); // Expects "YYYY-MM-DD"
      return `${year}-${String(month).padStart(2, "0")}-${String(day).padStart(
        2,
        "0"
      )}`;
    };

    if (typeof dateService === "string") {
      const formatted = formatDate(dateService);
      if (formatted) newDateServiceDates.add(formatted);
    } else if (Array.isArray(dateService)) {
      dateService.forEach((service) => {
        const formatted = formatDate(service.date);
        if (formatted) newDateServiceDates.add(formatted);
      });
    } else if (dateService && typeof dateService === "object") {
      (dateService.data || []).forEach((service) => {
        const formatted = formatDate(service.date);
        if (formatted) newDateServiceDates.add(formatted);
      });
    }

    // setDateServiceDates(newDateServiceDates); // Uncomment to update state
  }, [dateService]);

  const parseDate = (dateStr) => {
    const [day, month, year] = dateStr.split("/").map(Number); // Expects "DD/MM/YYYY"
    return new Date(Date.UTC(year, month - 1, day)); // Creates UTC date to prevent offset issues
  };

  useEffect(() => {
    if (checkIn && checkOut) {
      const startDate = parseDate(checkIn);
      const endDate = parseDate(checkOut);

      if (!isNaN(startDate) && !isNaN(endDate) && startDate <= endDate) {
        const updatedRange = [];
        let currentDate = new Date(startDate);

        while (currentDate <= endDate) {
          updatedRange.push(new Date(currentDate));
          currentDate.setUTCDate(currentDate.getUTCDate() + 1); // Use UTC date addition
        }

        setRange(updatedRange);
      } else {
        setRange([]);
      }
    } else {
      setRange([]);
    }
  }, [checkIn, checkOut]);

  useEffect(() => {
    if (status === "idle") {
      dispatch(fetchBookingid());
    }
  }, [dispatch, status]);

  const formatDate = (date) => date.toISOString().split("T")[0];

  const handleHotelClick = (date) => {
    setSelectedDate(formatDate(date));
    setHotelModalOpen(true);
  };

  const handleAttractionClick = (date) => {
    setSelectedDate(formatDate(date));
    setModalOpen(true);
  };

  const handleRestaurantClick = (date) => {
    setSelectedDate(formatDate(date));
    setRestaurantModalOpen(true);
  };

  const handleLocaltourClick = (date) => {
    setSelectedDate(formatDate(date));
    setlocaltourModalOpen(true);
  };

  const handleEntryPortClick = (date) => {
    setSelectedDate(formatDate(date));
    setPortType("entry");
    setentryexitModalOpen(true);
  };

  const handleExitPortClick = (date) => {
    setSelectedDate(formatDate(date));
    setPortType("exit");
    setentryexitModalOpen(true);
  };

  const handleGuideClick = (date) => {
    setSelectedDate(formatDate(date));
    setguideModalOpen(true);
  };

  const subBoxIcons = [
    { icon: hotelIcon, name: "Hotel", color: "#4caf50" },
    { icon: airplane, name: "Pickup/Drop(Entry Port)", color: "#ff9800" },
    { icon: airplane2, name: "Pickup/Drop(Exit Port)", color: "#2196f3" },
    {
      icon: attractionIcon,
      name: "Attraction & Experiences",
      color: "#9c27b0",
    },
    { icon: guideIcon, name: "Tour Guide", color: "#795548" },
    { icon: restaurant, name: "Restaurant", color: "#607d8b" },
    { icon: car1, name: "Travellers", color: "#f44336" },
  ];

  const formatDateForDisplay = (date) => {
    const d = new Date(date);

    // Get day name (Mon, Tue, etc.)
    const dayName = new Intl.DateTimeFormat("en-US", {
      weekday: "short",
    }).format(d);

    // Get day of month (1-31)
    const day = d.getDate();

    // Get month name (Jan, Feb, etc.)
    const monthName = new Intl.DateTimeFormat("en-US", {
      month: "short",
    }).format(d);

    // Get year and take last 2 digits
    const year = d.getFullYear().toString().substr(-2);

    // Format as "Mon, 24 Jan'25"
    return `${dayName}, ${day} ${monthName}'${year}`;
  };

  return (
    <div className="tour-status-container">
      <div className="range-box">
        {range.length > 0 ? (
          <div className="boxes-container">
            {range.map((date, index) => {
              const formattedDate = formatDate(date);
              const services = dateService[formattedDate]?.services || {};
              // console.log("services", services);

              const attractionCount = (services.attraction?.count || 0) + (services.attraction_package?.count || 0);
              // console.log("attractionCount", attractionCount);
              const restaurantCount = services.restaurant?.count || 0;
              const localtravelPointCount = services.travel_point?.count || 0;
              const localtravelHourCount = services.travel_hourly?.count || 0;
              const localtravelZoneCount = services.local_transport?.count || 0;
              const localtourCount =
                localtravelPointCount +
                localtravelHourCount +
                localtravelZoneCount;
              const entrycount = services.entry_port?.count || 0;
              const exitcount = services.exit_port?.count || 0;
              const portcount = entrycount + exitcount;
              const guidecount = services.guide?.count || 0;

              // Add hotel count
              const hotelCount = services.hotel?.count || 0;

              // Filter icons based on the current day
              const visibleIcons = subBoxIcons.filter((icon) => {
                if (icon.name === "Pickup/Drop(Entry Port)") {
                  return index === 0;
                }
                if (icon.name === "Pickup/Drop(Exit Port)") {
                  return index === range.length - 1;
                }
                return true;
              });

              return (
                <div
                  key={index}
                  className="number-box"
                  style={{
                    width: "180px",
                    height: "80px",
                    // backgroundColor: "#f0e70d1a",
                    backgroundColor: "#0dcaf033",
                    border: "1.5px solid rgba(5, 4, 4, 0.98)",
                    borderRadius: "8px",
                    padding: "10px 8px",
                    boxShadow: "0 2px 8px rgba(0,0,0,0.05)",
                    transition: "all 0.2s ease",
                    position: "relative",
                    overflow: "hidden",
                  }}
                >
                  <div
                    style={{
                      fontWeight: "600",
                      fontSize: "0.85rem",
                      color: "#333",
                      textAlign: "center",
                      marginBottom: "10px",
                      padding: "2px 0",
                      borderBottom: "1px dashed rgba(0,0,0,0.1)",
                      position: "relative",
                    }}
                  >
                    {formatDateForDisplay(date)}
                  </div>
                  <div
                    className="sub-box-container"
                    style={{
                      display: "grid",
                      gridTemplateColumns: `repeat(${visibleIcons.length}, 1fr)`,
                      gap: "4px",
                      width: "100%",
                      marginTop: "5px",
                    }}
                  >
                    {visibleIcons.map((icon, subIndex) => {
                      // Determine if the icon should be active (has a count)
                      let count = 0;
                      if (icon.name === "Hotel") count = hotelCount;
                      else if (icon.name === "Pickup/Drop(Entry Port)")
                        count = entrycount;
                      else if (icon.name === "Pickup/Drop(Exit Port)")
                        count = exitcount;
                      else if (icon.name === "Attraction & Experiences")
                        count = attractionCount;
                      else if (icon.name === "Tour Guide") count = guidecount;
                      else if (icon.name === "Restaurant")
                        count = restaurantCount;
                      else if (icon.name === "Travellers")
                        count = localtourCount;

                      const isActive = count > 0;

                      return (
                        <div
                          key={subIndex}
                          className="sub-box"
                          style={{
                            width: "20px",
                            height: "20px",
                            textAlign: "center",
                            lineHeight: "50px",
                            cursor: isActive ? "pointer" : "default",
                            marginLeft:
                              visibleIcons.length === 5
                                ? "6px"
                                : visibleIcons.length === 6
                                ? "3px"
                                : "4px",
                            position: "relative",
                            opacity: isActive ? 1 : 0.5,
                            filter: isActive ? "none" : "grayscale(60%)",
                            transition: "all 0.2s ease",
                          }}
                          onClick={() => {
                            if (!isActive) return;

                            const iconName = icon.name;
                            if (iconName === "Hotel" && hotelCount > 0)
                              handleHotelClick(date);
                            else if (
                              iconName === "Attraction & Experiences" &&
                              attractionCount > 0
                            )
                              handleAttractionClick(date);
                            else if (
                              iconName === "Pickup/Drop(Entry Port)" &&
                              entrycount > 0
                            )
                              handleEntryPortClick(date);
                            else if (
                              iconName === "Pickup/Drop(Exit Port)" &&
                              exitcount > 0
                            )
                              handleExitPortClick(date);
                            else if (
                              iconName === "Travellers" &&
                              localtourCount > 0
                            )
                              handleLocaltourClick(date);
                            else if (
                              iconName === "Tour Guide" &&
                              guidecount > 0
                            )
                              handleGuideClick(date);
                            else if (
                              iconName === "Restaurant" &&
                              restaurantCount > 0
                            )
                              handleRestaurantClick(date);
                          }}
                        >
                          <div style={{ position: "relative" }}>
                            <img
                              src={icon.icon}
                              alt={icon.name}
                              data-tooltip-content={icon.name}
                              data-tooltip-id={`tooltip-${subIndex}-${index}`}
                              style={{
                                width: "15px",
                                height: "15px",
                                transition: "transform 0.2s ease",
                              }}
                            />
                            {count > 0 && (
                              <div
                                style={{
                                  position: "absolute",
                                  top: 3,
                                  right: -10,
                                  backgroundColor: icon.color || "blue",
                                  color: "white",
                                  borderRadius: "50%",
                                  width: "14px",
                                  height: "15px",
                                  display: "flex",
                                  justifyContent: "center",
                                  alignItems: "center",
                                  fontSize: "10px",
                                  boxShadow: "0 1px 3px rgba(0,0,0,0.2)",
                                }}
                              >
                                {count}
                              </div>
                            )}
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              );
            })}
          </div>
        ) : (
          <Box
            style={{
              textAlign: "center",
              padding: "20px",
              color: "#666",
              fontStyle: "italic",
              backgroundColor: "rgba(0,0,0,0.02)",
              borderRadius: "8px",
            }}
          >
            No date range selected
          </Box>
        )}
      </div>

      {/* Tooltips */}
      {subBoxIcons.map((icon, iconIndex) =>
        range.map((_, dateIndex) => (
          <Tooltip
            key={`tooltip-${iconIndex}-${dateIndex}`}
            id={`tooltip-${iconIndex}-${dateIndex}`}
            effect="solid"
            place="top"
            style={{
              borderRadius: "4px",
              fontSize: "12px",
              padding: "4px 8px",
              boxShadow: "0 2px 10px rgba(0,0,0,0.1)",
            }}
          />
        ))
      )}

      {/* Modals */}
      <PortModal
        open={entryexitModalOpen}
        onClose={() => setentryexitModalOpen(false)}
        bookings={portType === "entry" ? formData : formData1}
        date={selectedDate}
        portType={portType}
      />
      <AttractionModal
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        bookings={bookings}
        date={selectedDate}
      />
      <LocalTourModal
        open={localtourModalOpen}
        onClose={() => setlocaltourModalOpen(false)}
        bookings={travel}
        date={selectedDate}
      />
      <TourguideModal
        open={guideModalOpen}
        onClose={() => setguideModalOpen(false)}
        bookings={bookedguide}
        date={selectedDate}
      />
      <RestaurantModal
        open={restaurantModalOpen}
        onClose={() => setRestaurantModalOpen(false)}
        bookings={restaurantBooking}
        date={selectedDate}
      />
      <HotelModal
        open={hotelModalOpen}
        onClose={() => setHotelModalOpen(false)}
        bookings={hotelBookings}
        date={selectedDate}
      />

      <style jsx>{`
        .tour-status-container {
          padding: 15px;
          background: linear-gradient(145deg, #ffffff, #f9f9f9);
          border-radius: 12px;
          box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        }

        .boxes-container {
          display: flex;
          overflow-x: auto;
          gap: 12px;
          padding: 8px 4px;
          -webkit-overflow-scrolling: touch;
          scrollbar-width: thin;
          scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
        }

        .boxes-container::-webkit-scrollbar {
          height: 6px;
        }

        .boxes-container::-webkit-scrollbar-track {
          background: rgba(0, 0, 0, 0.05);
          border-radius: 10px;
        }

        .boxes-container::-webkit-scrollbar-thumb {
          background: rgba(0, 0, 0, 0.15);
          border-radius: 10px;
        }

        .boxes-container::-webkit-scrollbar-thumb:hover {
          background: rgba(0, 0, 0, 0.25);
        }

        .number-box {
          position: relative;
          transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .number-box:hover {
          transform: translateY(-2px);
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        }

        .sub-box:hover img {
          transform: scale(1.1);
        }
      `}</style>
    </div>
  );
}
