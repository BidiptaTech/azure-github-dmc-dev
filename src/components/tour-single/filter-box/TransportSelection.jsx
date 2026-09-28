import React, { useState, useRef, useEffect } from "react";
import { Modal } from "react-bootstrap";
import DirectionsBusIcon from "@mui/icons-material/DirectionsBus";
import AirportShuttleIcon from "@mui/icons-material/AirportShuttle";
import NoTransferIcon from '@mui/icons-material/DoNotDisturbAlt';

const TransportSelection = ({
  transportOption,
  setTransportOption,
  hasTransportOptions,
  vehicles = [],
  selectedVehicle,
  setSelectedVehicle,
  guestCounts,
  selectedTicket,
  currencyCode = "SGD",
  exchangeRate = 1,
  usdExchangeRate = 1,
}) => {
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalType, setModalType] = useState(null); // "private" or "shared"
  const dropdownRef = useRef(null);

  // console.log('vehicles',vehicles?.[0]?.sharable);
  

  // Calculate total number of travelers
  const totalTravelers =
    parseInt(guestCounts?.Adults || 0) + parseInt(guestCounts?.Children || 0);

  // Filter vehicles by sharable value and capacity
  const privateVehicles = vehicles.filter(
    (v) =>
      v.is_available &&
      v.sharable === 0 || v.sharable === 2 &&
      v.attraction_shared_transport_price &&
      parseFloat(v.attraction_shared_transport_price) > 0 &&
      parseInt(v.seating_capacity || 0) >= totalTravelers
  );

  const sharedVehicles = vehicles.filter(
    (v) =>
      v.is_available &&
      v.sharable === 1 || v.sharable === 2 &&
      v.attraction_shared_transport_price &&
      parseFloat(v.attraction_shared_transport_price) > 0 &&
      parseInt(v.seating_capacity || 0) >= totalTravelers
  );

  // Reset selected vehicle if capacity becomes insufficient
  useEffect(() => {
    if (selectedVehicle) {
      const vehicleCapacity = parseInt(selectedVehicle.seating_capacity || 0);
      if (vehicleCapacity < totalTravelers) {
        setSelectedVehicle(null);
        setTransportOption(null);
      }
    }
  }, [
    guestCounts,
    selectedVehicle,
    setSelectedVehicle,
    setTransportOption,
    totalTravelers,
  ]);

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsDropdownOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const handleOpenModal = (type) => {
    setModalType(type);
    setIsModalOpen(true);
    setIsDropdownOpen(false);
  };

  const handleSelectNoTransport = () => {
    setTransportOption(null);
    setSelectedVehicle(null);
    setIsDropdownOpen(false);
  };

  const handleVehicleSelect = (vehicle) => {
    setSelectedVehicle(vehicle);
    setTransportOption(modalType);
    setIsModalOpen(false);
  };

  // Format price in different currencies
  const formatPrice = (price, type) => {
    if (!price) return "0.00";

    switch (type) {
      case "main":
        return `${currencyCode} ${Math.ceil(price * exchangeRate)}`;
      case "usd":
        return `USD ${Math.ceil(price * usdExchangeRate)}`;
      case "sgd":
        return `SGD ${Math.ceil(price)}`;
      default:
        return `SGD ${Math.ceil(price)}`;
    }
  };

  // Get label for selected transport option
  const getTransportLabel = () => {
    if (!transportOption) return "No Transport";
    if (transportOption === "private") {
      return selectedVehicle 
        ? `Private - ${selectedVehicle.vehicle_name}` 
        : "Private Transport";
    }
    if (transportOption === "shared") {
      return selectedVehicle 
        ? `Shared - ${selectedVehicle.vehicle_name}` 
        : "Shared Transport";
    }
    return "Select Transport";
  };

  // Get icon for transport option
  const getTransportIcon = (option) => {
    if (option === "private") {
      return <DirectionsBusIcon style={{ marginRight: 8, fontSize: "small", color: "#3554D1" }} />;
    }
    if (option === "shared") {
      return <AirportShuttleIcon style={{ marginRight: 8, fontSize: "small", color: "#3554D1" }} />;
    }
    return <NoTransferIcon style={{ marginRight: 8, fontSize: "small", color: "#3554D1" }} />;
  };

  return (
    <>
      <div className="text-15 text-light-1 ls-2 lh-16" ref={dropdownRef}>
        <div
          onClick={() => setIsDropdownOpen(!isDropdownOpen)}
          className="d-flex align-items-center"
          style={{
            backgroundColor: "white",
            border: "1px solid #e5e7eb",
            borderRadius: "8px",
            padding: "10px 12px",
            cursor: "pointer",
            transition: "all 0.2s",
            boxShadow: isDropdownOpen ? "0 4px 12px rgba(0,0,0,0.1)" : "none",
          }}
        >
          {getTransportIcon(transportOption)}
          <span style={{ fontWeight: 500, color: "#1a1a1a" }}>
            {getTransportLabel()}
          </span>
          <i
            className={`icon-chevron-sm-${
              isDropdownOpen ? "up" : "down"
            } text-7 ml-10`}
            style={{ marginLeft: "auto" }}
          ></i>
        </div>

        {isDropdownOpen && (
          <div
            style={{
              position: "absolute",
              zIndex: 1000,
              marginTop: "2px",
              boxShadow: "0 4px 16px rgba(0,0,0,0.1)",
              borderRadius: "8px",
              backgroundColor: "white",
              width: "calc(100% - 32px)",
              maxHeight: "250px",
              overflowY: "auto",
            }}
          >
            {/* No Transport Option */}
            <div
              onClick={handleSelectNoTransport}
              style={{
                padding: "10px 12px",
                cursor: "pointer",
                backgroundColor: transportOption === null ? "rgba(53, 84, 209, 0.05)" : "white",
                borderBottom: "1px solid #f3f4f6",
                display: "flex",
                alignItems: "center",
              }}
            >
              <NoTransferIcon style={{ marginRight: 8, fontSize: "small", color: "#3554D1" }} />
              <div>
                <div style={{ fontWeight: transportOption === null ? 600 : 500, fontSize: "14px" }}>
                  No Transport
                </div>
                <div style={{ color: "#64748B", fontSize: "12px" }}>
                  Ticket only, no transportation included
                </div>
              </div>
            </div>

            {/* Private Transport Option */}
            {privateVehicles.length > 0 && (
              <div
                onClick={() => handleOpenModal("private")}
                style={{
                  padding: "10px 12px",
                  cursor: "pointer",
                  backgroundColor: 
                    transportOption === "private" ? "rgba(53, 84, 209, 0.05)" : "white",
                  borderBottom: "1px solid #f3f4f6",
                  display: "flex",
                  alignItems: "center",
                }}
              >
                <DirectionsBusIcon style={{ marginRight: 8, fontSize: "small", color: "#3554D1" }} />
                <div>
                  <div style={{ fontWeight: transportOption === "private" ? 600 : 500, fontSize: "14px" }}>
                    Private Transport
                  </div>
                  <div style={{ color: "#64748B", fontSize: "12px" }}>
                    {selectedVehicle && transportOption === "private" 
                      ? selectedVehicle.vehicle_name 
                      : "Select a private vehicle"}
                  </div>
                </div>
              </div>
            )}

            {/* Shared Transport Option */}
            {sharedVehicles.length > 0 && (
              <div
                onClick={() => handleOpenModal("shared")}
                style={{
                  padding: "10px 12px",
                  cursor: "pointer",
                  backgroundColor: 
                    transportOption === "shared" ? "rgba(53, 84, 209, 0.05)" : "white",
                  display: "flex",
                  alignItems: "center",
                }}
              >
                <AirportShuttleIcon style={{ marginRight: 8, fontSize: "small", color: "#3554D1" }} />
                <div>
                  <div style={{ fontWeight: transportOption === "shared" ? 600 : 500, fontSize: "14px" }}>
                    Shared Transport
                  </div>
                  <div style={{ color: "#64748B", fontSize: "12px" }}>
                    {selectedVehicle && transportOption === "shared" 
                      ? selectedVehicle.vehicle_name 
                      : "Select a shared vehicle"}
                  </div>
                </div>
              </div>
            )}
          </div>
        )}
      </div>

      {/* Modal for vehicle selection */}
      <Modal
        show={isModalOpen}
        onHide={() => setIsModalOpen(false)}
        centered
        backdrop="static"
        size="lg"
      >
        <Modal.Header closeButton>
          <Modal.Title>
            {modalType === "private" ? "Select Private Vehicle" : "Select Shared Vehicle"}
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <div className="px-10 py-15">
            <div className="mb-20">
              <h4 className="text-18 fw-500 mb-10">
                {modalType === "private" 
                  ? "Available Private Vehicles" 
                  : "Available Shared Vehicles"}
              </h4>
              <p className="text-15 text-light-1">
                Please select a vehicle that meets your requirements:
              </p>
            </div>

            <div className="row">
              {(modalType === "private" ? privateVehicles : sharedVehicles).map((vehicle) => (
                <div key={vehicle.vehicle_id} className="col-md-6 mb-20">
                  <div 
                    className={`py-15 px-20 border rounded-4 h-100 ${
                      selectedVehicle?.vehicle_id === vehicle.vehicle_id &&
                      transportOption === modalType
                        ? "border-blue-1 bg-blue-1-05"
                        : ""
                    }`}
                    style={{ cursor: "pointer" }}
                    onClick={() => handleVehicleSelect(vehicle)}
                  >
                    <div className="d-flex align-items-center mb-10">
                      <input
                        type="radio"
                        checked={
                          selectedVehicle?.vehicle_id === vehicle.vehicle_id &&
                          transportOption === modalType
                        }
                        readOnly
                        style={{ marginRight: 10 }}
                      />
                      <div className="fw-500">{vehicle.vehicle_name}</div>
                    </div>
                    <div className="d-flex justify-content-between mb-10">
                      <div className="text-14">
                        <div className="mb-5">Type: {vehicle.vehicle_type}</div>
                        <div>Capacity: {vehicle.seating_capacity} seats</div>
                      </div>
                      <div className="text-16 fw-500 text-blue-1">
                        {formatPrice(
                          modalType === "private"
                            ? vehicle.attraction_private_transport_price
                            : vehicle.attraction_shared_transport_price,
                          "main"
                        )}
                        {modalType === "shared" && "/person"}
                      </div>
                    </div>
                    {vehicle.description && (
                      <div
                        className="text-14 text-light-1 mt-5"
                        dangerouslySetInnerHTML={{
                          __html: vehicle.description,
                        }}
                      ></div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </Modal.Body>
        <Modal.Footer>
          <button
            className="button -md -outline-blue-1 text-blue-1"
            onClick={() => setIsModalOpen(false)}
          >
            Cancel
          </button>
        </Modal.Footer>
      </Modal>
    </>
  );
};

export default TransportSelection; 