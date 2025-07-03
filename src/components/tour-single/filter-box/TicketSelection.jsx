import React, { useState, useRef, useEffect } from "react";
import { Modal } from "react-bootstrap";
import { useSelector } from "react-redux";
import ConfirmationNumberIcon from "@mui/icons-material/ConfirmationNumber";

const TicketSelection = ({
  ticketOptions,
  selectedTicket,
  setSelectedTicket,
  isModalOpen,
  setIsModalOpen,
  nriStatus,
  setNriStatus
}) => {
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  // const [showFullDescription, setShowFullDescription] = useState(false);
  const [textModalContent, setTextModalContent] = useState({ title: "", content: "", isOpen: false });
  const dropdownRef = useRef(null);

  // Get currency information from Redux store
  // const currencySymbol = useSelector((state) => state.auth.currencySymbol);
  const currencyCode = useSelector((state) => state.auth.currencyCode) || "SGD";
  const exchangeRate = useSelector((state) => state.auth.exchangeRate) || 1;
  const usdExchangeRate =
    useSelector((state) => state.auth.usdExchangeRate) || 1;

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsDropdownOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  // Reset description state when modal opens with a new ticket
  // useEffect(() => {
  //   if (isModalOpen) {
  //     setShowFullDescription(false);
  //   }
  // }, [isModalOpen, selectedTicket]);

  const handleOpenModal = (ticket) => {
    setSelectedTicket(ticket);
    setIsModalOpen(true);
    setIsDropdownOpen(false);
    // setShowFullDescription(false);
  };

  const handleConfirmSelection = () => {
    // Log the selected ticket for debugging
    console.log("Confirming ticket selection:", selectedTicket);
    
    // Make sure the nriStatus state is properly updated in the parent component
    const currentStatus = nriStatus;
    console.log("Confirming with NRI status:", currentStatus);
    
    // Ensure the selected ticket is properly set in the parent component
    if (selectedTicket) {
      setSelectedTicket(selectedTicket);
    }
    
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

  // Get price based on price type (residential or NRI)
  const getPrice = (priceField) => {
    if (!selectedTicket) return 0;
    
    // For NRI prices
    if (nriStatus === "nri") {
      switch (priceField) {
        case "adult":
          return selectedTicket.dmc_adult_price_nri !== null ? selectedTicket.dmc_adult_price_nri : 0;
        case "child":
          return selectedTicket.dmc_child_price_nri !== null ? selectedTicket.dmc_child_price_nri : 0;
        case "senior":
          return selectedTicket.dmc_senior_price_nri !== null ? selectedTicket.dmc_senior_price_nri : 0;
        default:
          return 0;
      }
    }
    
    // For residential prices
    switch (priceField) {
      case "adult":
        return selectedTicket.dmc_adult_price || 0;
      case "child":
        return selectedTicket.dmc_child_price || 0;
      case "senior":
        return selectedTicket.dmc_senior_price || 0;
      default:
        return 0;
    }
  };

  // Handle description truncation and toggle
  const renderDescription = (description, type = "description") => {
    if (!description) return null;
    
    const words = description.split(/\s+/);
    const wordCount = words.length;
    const wordLimit = 10; // Changed from 200 to 10 per requirement
    
    const getIcon = () => {
      switch (type) {
        case "description":
          return <i className="icon-info-circle text-15 text-blue-1 mr-10"></i>;
        case "remarks":
          return (
            <i className="icon-notification text-15 text-yellow-1 mr-10"></i>
          );
        case "terms":
          return <i className="icon-shield text-15 text-red-1 mr-10"></i>;
        default:
          return null;
      }
    };

    const getTitle = () => {
      switch (type) {
        case "description":
          return "Description";
        case "remarks":
          return "Remarks";
        case "terms":
          return "Terms & Conditions";
        default:
          return "";
      }
    };
    
    const openTextModal = () => {
      setTextModalContent({
        title: getTitle(),
        content: description,
        isOpen: true
      });
    };
    
    if (wordCount <= wordLimit) {
      return (
        <div className="mt-15 py-10 px-15 bg-blue-1-05 rounded-4">
          <div className="d-flex mb-5">
            {getIcon()}
            <span className="fw-500">{getTitle()}</span>
          </div>
          <p className="text-15">{description}</p>
        </div>
      );
    }
    
    const truncatedText = words.slice(0, wordLimit).join(" ");
    
    return (
      <div className="mt-15 py-10 px-15 bg-blue-1-05 rounded-4">
        <div className="d-flex mb-5">
          {getIcon()}
          <span className="fw-500">{getTitle()}</span>
        </div>
        <p className="text-15">
          {truncatedText + "..."}
        </p>
        <button 
          onClick={(e) => {
            e.preventDefault();
            e.stopPropagation();
            openTextModal();
          }}
          className="text-14 text-blue-1 underline mt-5"
          style={{
            background: "none",
            border: "none",
            cursor: "pointer",
            padding: 0,
          }}
        >
          Show more
        </button>
      </div>
    );
  };

  // Handle NRI status changes with immediate parent notification
  const handleNriStatusChange = (newStatus) => {
    console.log("Changing NRI status to:", newStatus);
    setNriStatus(newStatus);
  };

  const stripPTags = (text) => {
    if (!text) return "";
    
    // Create a temporary div to parse HTML
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = text;
    
    // Get the text content, which automatically strips HTML tags
    let cleanedText = tempDiv.textContent || tempDiv.innerText;
    
    // Clean up any remaining HTML entities
    cleanedText = cleanedText
      .replace(/&nbsp;/g, ' ')
      .replace(/&quot;/g, '"')
      .replace(/&amp;/g, '&')
      .replace(/&lt;/g, '<')
      .replace(/&gt;/g, '>')
      .replace(/&#39;/g, "'");
    
    // Remove extra whitespace and trim
    cleanedText = cleanedText.replace(/\s+/g, ' ').trim();
    
    return cleanedText;
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
          <ConfirmationNumberIcon
            style={{ marginRight: 8, fontSize: "small", color: "#3554D1" }}
          />
          <span style={{ fontWeight: 500, color: "#1a1a1a" }}>
            {selectedTicket
              ? selectedTicket.ticket_name
              : "Select Ticket Package"}
          </span>
          <i
            className={`icon-chevron-sm-${
              isDropdownOpen ? "up" : "down"
            } text-7 ml-10`}
            style={{ marginLeft: "auto" }}
          ></i>
        </div>

        {isDropdownOpen && ticketOptions.length > 0 && (
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
            {ticketOptions.map((ticket, index) => (
              <div
                key={`${ticket.ticket_id}-${nriStatus}`}
                onClick={() => handleOpenModal(ticket)}
                style={{
                  padding: "10px 12px",
                  cursor: "pointer",
                  backgroundColor:
                    selectedTicket?.ticket_id === ticket.ticket_id
                      ? "rgba(53, 84, 209, 0.05)"
                      : "white",
                  borderBottom:
                    index < ticketOptions.length - 1
                      ? "1px solid #f3f4f6"
                      : "none",
                  display: "flex",
                  alignItems: "center",
                }}
              >
                <ConfirmationNumberIcon
                  style={{
                    marginRight: 8,
                    fontSize: "small",
                    color: "#3554D1",
                  }}
                />
                <div>
                  <div
                    style={{
                      fontWeight:
                        selectedTicket?.ticket_id === ticket.ticket_id
                          ? 600
                          : 500,
                      fontSize: "14px",
                    }}
                  >
                    {ticket.ticket_name}
                  </div>
                  <div
                    style={{
                      color: "#3554D1",
                      fontSize: "13px",
                      fontWeight: 500,
                    }}
                  >
                    Adult: {formatPrice(nriStatus === "nri" ? 
                      (ticket.dmc_adult_price_nri !== null ? ticket.dmc_adult_price_nri : 0) : 
                      ticket.dmc_adult_price, "main")} |
                    Child: {formatPrice(nriStatus === "nri" ? 
                      (ticket.dmc_child_price_nri !== null ? ticket.dmc_child_price_nri : 0) : 
                      ticket.dmc_child_price, "main")} |
                    Senior: {formatPrice(nriStatus === "nri" ? 
                      (ticket.dmc_senior_price_nri !== null ? ticket.dmc_senior_price_nri : 0) : 
                      ticket.dmc_senior_price, "main")}
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      <Modal
        show={isModalOpen}
        onHide={() => setIsModalOpen(false)}
        centered
        backdrop="static"
        size="lg"
      >
        <Modal.Header closeButton>
          <Modal.Title>
            Confirm Ticket Selection 
            {nriStatus === "nri" && <span className="text-15 ml-10 badge bg-blue-1 text-white">Foreigner Pricing</span>}
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          {selectedTicket && (
            <div className="px-10 py-15">
              <div className="d-flex align-items-center mb-20">
                <ConfirmationNumberIcon
                  style={{ fontSize: 36, color: "#3554D1", marginRight: 15 }}
                />
                <div className="flex-grow-1">
                  <h4 className="text-18 fw-500">
                    {selectedTicket.ticket_name}
                  </h4>
                </div>
              </div>

              <div className="d-flex justify-content-between">
                <div className="flex-grow-1">
                  {selectedTicket.description && renderDescription(stripPTags(selectedTicket.description), "description")}
                </div>
                <div className="flex-grow-1">
                  {selectedTicket.remarks && renderDescription(stripPTags(selectedTicket.remarks), "remarks")}
                </div>
                <div className="flex-grow-1">
                  {selectedTicket.terms_conditions && renderDescription(stripPTags(selectedTicket.terms_conditions), "terms")}
                </div>
              </div>

              {/* Price Type Selection Radio Buttons */}
              <div className="d-flex mb-20 bg-blue-1-05 p-15 rounded-4">
                <div className="fw-500 mr-20">Select Price Type:</div>
                <div className="form-radio d-flex mr-30">
                  <div className="radio">
                    <input
                      type="radio"
                      name="priceType"
                      id="residential"
                      checked={nriStatus === "residential"}
                      onChange={() => handleNriStatusChange("residential")}
                    />
                    <label 
                      htmlFor="residential" 
                      className={`radio__label ${nriStatus === "residential" ? "text-blue-1 fw-500" : ""}`}
                    >
                      Local
                    </label>
                  </div>
                </div>
                <div className="form-radio d-flex">
                  <div className="radio">
                    <input
                      type="radio"
                      name="priceType"
                      id="nri"
                      checked={nriStatus === "nri"}
                      onChange={() => handleNriStatusChange("nri")}
                    />
                    <label 
                      htmlFor="nri" 
                      className={`radio__label ${nriStatus === "nri" ? "text-blue-1 fw-500" : ""}`}
                    >
                      Foreigner
                    </label>
                  </div>
                </div>
              </div>

              <div className="row">
                <div className="col-md-12 mb-15">
                  <div className="fw-500 text-18 text-center">
                    {nriStatus === "residential" ? "Local Prices" : "Foreigner Prices"}
                  </div>
                </div>
                <div className="col-md-4">
                  <div className="py-10 px-15 border rounded-4 mb-10" key={`adult-${nriStatus}`}>
                    <div className="text-14 text-light-1 mb-10">
                      Adult Price
                    </div>
                    {/* Main currency */}
                    <div className="text-16 fw-500">
                      {formatPrice(getPrice("adult"), "main")}
                    </div>

                    {/* USD price if not already USD */}
                    {currencyCode !== "USD" && (
                      <div className="text-14 mt-5 text-light-1">
                        {formatPrice(getPrice("adult"), "usd")}
                      </div>
                    )}

                    {/* SGD price if not already SGD */}
                    {currencyCode !== "SGD" && (
                      <div className="text-14 mt-5 text-light-1">
                        {formatPrice(getPrice("adult"), "sgd")}
                      </div>
                    )}
                  </div>
                </div>
                <div className="col-md-4">
                  <div className="py-10 px-15 border rounded-4 mb-10" key={`child-${nriStatus}`}>
                    <div className="text-14 text-light-1 mb-10">
                      Child Price
                    </div>
                    {/* Main currency */}
                    <div className="text-16 fw-500">
                      {formatPrice(getPrice("child"), "main")}
                    </div>

                    {/* USD price if not already USD */}
                    {currencyCode !== "USD" && (
                      <div className="text-14 mt-5 text-light-1">
                        {formatPrice(getPrice("child"), "usd")}
                      </div>
                    )}

                    {/* SGD price if not already SGD */}
                    {currencyCode !== "SGD" && (
                      <div className="text-14 mt-5 text-light-1">
                        {formatPrice(getPrice("child"), "sgd")}
                      </div>
                    )}
                  </div>
                </div>
                <div className="col-md-4">
                  <div className="py-10 px-15 border rounded-4 mb-10" key={`senior-${nriStatus}`}>
                    <div className="text-14 text-light-1 mb-10">
                      Senior Price
                    </div>
                    {/* Main currency */}
                    <div className="text-16 fw-500">
                      {formatPrice(getPrice("senior"), "main")}
                    </div>

                    {/* USD price if not already USD */}
                    {currencyCode !== "USD" && (
                      <div className="text-14 mt-5 text-light-1">
                        {formatPrice(getPrice("senior"), "usd")}
                      </div>
                    )}

                    {/* SGD price if not already SGD */}
                    {currencyCode !== "SGD" && (
                      <div className="text-14 mt-5 text-light-1">
                        {formatPrice(getPrice("senior"), "sgd")}
                      </div>
                    )}
                  </div>
                </div>
              </div>
            </div>
          )}
        </Modal.Body>
        <Modal.Footer>
          <button
            className="button -md -blue-1 bg-blue-1 text-white"
            onClick={handleConfirmSelection}
          >
            Confirm Selection
          </button>
          <button
            className="button -md -outline-blue-1 text-blue-1"
            onClick={() => setIsModalOpen(false)}
          >
            Cancel
          </button>
        </Modal.Footer>
      </Modal>

      {/* New modal for displaying full text content */}
      <Modal
        show={textModalContent.isOpen}
        onHide={() => setTextModalContent({...textModalContent, isOpen: false})}
        centered
        size="lg"
      >
        <Modal.Header closeButton>
          <Modal.Title>{textModalContent.title}</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <div className="px-10 py-15">
            <p className="text-15">{textModalContent.content}</p>
          </div>
        </Modal.Body>
        <Modal.Footer>
          <button 
            className="button -md -blue-1 bg-blue-1 text-white"
            onClick={() => setTextModalContent({...textModalContent, isOpen: false})}
          >
            Close
          </button>
        </Modal.Footer>
      </Modal>
    </>
  );
};

export default TicketSelection;
