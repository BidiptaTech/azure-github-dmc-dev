import React from "react";
import { Modal, Table, Button, Empty } from "antd";
import CheckCircleOutlinedIcon from '@mui/icons-material/CheckCircleOutlined';
import CancelOutlinedIcon from '@mui/icons-material/CancelOutlined';
import QuestionAnswerIcon from '@mui/icons-material/QuestionAnswer';

const EnquiryModal = ({
  isEnquiryModalVisible,
  handleCloseEnquiryModal,
  enquiryAmount,
  handleEnquiryAmountChange,
  enquiryComment,
  setEnquiryComment,
  commentError,
  setCommentError,
  enquiryHistory,
  loadingEnquiryHistory,
  bookings,
  submitEnquiry,
  handleEnquirySubmit
}) => {
  // Calculate max amount once
  const calculateMaxAmount = () => {
    let totalOriginalPrice = 0;

    // Get all services
    const hotels = bookings.hotel || bookings.data?.hotel || [];
    const entryPorts =
      bookings.entry_port || bookings.data?.entry_port || [];
    const attractions =
      bookings.attraction || bookings.data?.attraction || [];

    // Calculate total original price
    hotels.forEach((hotel) => {
      if (hotel.totalPrice) {
        totalOriginalPrice += parseFloat(hotel.totalPrice);
      }
    });

    entryPorts.forEach((port) => {
      if (port.totalPrice) {
        totalOriginalPrice += parseFloat(port.totalPrice);
      }
    });

    attractions.forEach((attraction) => {
      if (attraction.totalPrice) {
        totalOriginalPrice += parseFloat(attraction.totalPrice);
      }
    });

    return Math.ceil(totalOriginalPrice);
  };

  // Calculate max amount based on current price from enquiry history
  const getCurrentPrice = () => {
    if (enquiryHistory && enquiryHistory.length > 0) {
      // Get the latest enquiry's current_price
      const latestEnquiry = enquiryHistory[enquiryHistory.length - 1];
      if (latestEnquiry.current_price) {
        return parseFloat(latestEnquiry.current_price);
      }
    }
    // Fallback to calculated max amount
    return calculateMaxAmount();
  };

  const maxAmount = getCurrentPrice();
  const currentAmount = parseFloat(enquiryAmount) || 0;
  const isAmountExceeded = currentAmount > maxAmount;
  // Check if any entry in enquiryHistory has Assigned as "Agent"
  const isAssignedToAgent = enquiryHistory.some(item => item.assigned === "Agent");

  return (
    <Modal
      title="Make an Enquiry"
      open={isEnquiryModalVisible}
      onCancel={handleCloseEnquiryModal}
      footer={null}
      width="800px"
      centered
    >
      <div style={{ padding: "20px" }}>
        <div style={{ marginBottom: "20px" }}>
          <h3 style={{ marginBottom: 0, color: "#3554D1", fontSize: "18px" }}>
            Enquiry Details
          </h3>

          <div style={{ marginBottom: "20px", marginTop: "10px" }}>
            <Table
              dataSource={enquiryHistory}
              columns={[
                {
                  title: "Tour Id",
                  dataIndex: "tour_id",
                  key: "tour_id",
                  width: "8%",
                },
                {
                  title: "Created",
                  dataIndex: "created",
                  key: "created",
                  width: "10%",
                  render: (date) => date || "N/A",
                },
                {
                  title: "Updated",
                  dataIndex: "updated",
                  key: "updated",
                  width: "10%",
                  render: (date) => date || "N/A",
                },
                {
                  title: "Pending",
                  dataIndex: "pending_days",
                  key: "pending_days",
                  width: "8%",
                  render: (days) => days || "N/A",
                },
                {
                  title: "Assigned",
                  dataIndex: "assigned",
                  key: "assigned",
                  width: "10%",
                },
                {
                  title: " Actual Price",
                  dataIndex: "actual_price",
                  key: "actual_price",
                  width: "12%",
                  render: (price) => `$${parseFloat(price).toFixed(2)}`,
                },
                {
                  title: "Current Price",
                  dataIndex: "current_price",
                  key: "current_price",
                  width: "12%",
                  render: (price) => `$${parseFloat(price).toFixed(2)}`,
                },
                {
                  title: "Comment",
                  dataIndex: "comment",
                  key: "comment",
                  width: "15%",
                  ellipsis: false,
                },
                {
                  title: "Remarks",
                  dataIndex: "remarks",
                  key: "remarks",
                  width: "15%",
                  ellipsis: false,
                },
                {
                  title: "Status",
                  dataIndex: "status",
                  key: "status",
                  width: "10%",
                  render: (status) => {
                    let color = "";

                    // Map different statuses to colors
                    if (status && typeof status === "string") {
                      // Default color handling for various statuses
                      if (status.toLowerCase().includes("pending")) {
                        color = "#faad14";
                      } else if (status.toLowerCase().includes("accept")) {
                        color = "#52c41a";
                      } else if (
                        status.toLowerCase().includes("reject") ||
                        status.toLowerCase().includes("cancel")
                      ) {
                        color = "#f5222d";
                      } else {
                        color = "#1890ff";
                      }
                    }

                    return (
                      <span
                        style={{
                          color: color || "#1890ff",
                          backgroundColor: color
                            ? `${color}15`
                            : "rgba(24, 144, 255, 0.1)",
                          padding: "3px 8px",
                          borderRadius: "4px",
                          fontSize: "12px",
                          fontWeight: "bold",
                        }}
                      >
                        {status || "Unknown"}
                      </span>
                    );
                  },
                },
              ]}
              loading={loadingEnquiryHistory}
              pagination={false}
              rowKey={(record) =>
                record.id ||
                record.tour_id ||
                Math.random().toString(36).substring(7)
              }
              locale={{
                emptyText: <Empty description="No enquiry history found" />,
              }}
              style={{
                border: "1px solid rgba(53, 84, 209, 0.1)",
                borderRadius: "8px",
                overflow: "hidden",
              }}
              size="small"
            />
          </div>

          {/* Only show negotiation section if assigned to agent */}
          {isAssignedToAgent && (
            <>
              <div
                style={{
                  marginBottom: "20px",
                  padding: "15px",
                  borderRadius: "8px",
                  border: "1px solid rgba(53, 84, 209, 0.1)",
                  backgroundColor: "rgba(53, 84, 209, 0.03)",
                }}
              >
                <label
                  htmlFor="enquiryAmount"
                  style={{
                    display: "block",
                    marginBottom: "8px",
                    fontSize: "14px",
                    fontWeight: "bold",
                    color: "#555",
                  }}
                >
                  Negotiated Amount
                </label>

                <div style={{ display: "flex", alignItems: "center" }}>
                  <div
                    style={{
                      display: "flex",
                      alignItems: "center",
                      width: "100%",
                      border: "1px solid #ddd",
                      borderRadius: "4px",
                      overflow: "hidden",
                    }}
                  >
                    <div
                      style={{
                        padding: "8px 12px",
                        backgroundColor: "#f5f5f5",
                        borderRight: "1px solid #ddd",
                        color: "#555",
                        fontWeight: "bold",
                      }}
                    >
                      $
                    </div>
                    <input
                      id="enquiryAmount"
                      type="number"
                      min="0"
                      max={maxAmount}
                                             value={enquiryAmount || ""}
                                                                                           onChange={(e) => {
                          const value = parseFloat(e.target.value) || 0;
                          
                          // Allow empty value or values up to maxAmount
                          if (e.target.value === "" || value <= maxAmount) {
                            handleEnquiryAmountChange(e);
                          } else {
                            // If value exceeds max, revert to the previous valid value
                            e.target.value = enquiryAmount || "";
                          }
                        }}
                                             onBlur={(e) => {
                         // Ensure value doesn't exceed max on blur
                         const value = parseFloat(e.target.value) || 0;
                         if (value > maxAmount) {
                           const syntheticEvent = {
                             ...e,
                             target: {
                               ...e.target,
                               value: maxAmount.toString()
                             }
                           };
                           handleEnquiryAmountChange(syntheticEvent);
                         }
                       }}
                       onKeyDown={(e) => {
                         // Prevent typing if it would exceed maxAmount
                         const currentValue = parseFloat(e.target.value) || 0;
                         const key = e.key;
                         
                         // Allow backspace, delete, arrow keys, etc.
                         if (['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Enter'].includes(key)) {
                           return;
                         }
                         
                         // Allow numbers and decimal point
                         if (/^[0-9.]$/.test(key)) {
                           const newValue = parseFloat(currentValue.toString() + key) || 0;
                           if (newValue > maxAmount) {
                             e.preventDefault();
                           }
                         } else {
                           // Prevent other keys
                           e.preventDefault();
                         }
                       }}
                      placeholder="Enter negotiated amount"
                      style={{
                        flex: 1,
                        padding: "8px 12px",
                        border: "none",
                        outline: "none",
                        fontSize: "14px",
                        // Remove increment/decrement arrows
                        WebkitAppearance: "none",
                        MozAppearance: "textfield",
                        backgroundColor: isAmountExceeded ? "#ffebee" : "transparent",
                      }}
                    />
                  </div>
                </div>

                {/* Error message for exceeded amount */}
                {isAmountExceeded && (
                  <div
                    style={{
                      marginTop: "8px",
                      padding: "8px 12px",
                      backgroundColor: "#ffebee",
                      border: "1px solid #f44336",
                      borderRadius: "4px",
                      fontSize: "12px",
                      color: "#d32f2f",
                    }}
                  >
                                         ⚠️ Negotiated amount cannot exceed the current price of ${maxAmount.toFixed(2)}
                  </div>
                )}

                {/* Message showing max amount */}
                <div
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    marginTop: "8px",
                    padding: "6px 10px",
                    backgroundColor: isAmountExceeded ? "#ffebee" : "rgba(53, 84, 209, 0.08)",
                    borderRadius: "4px",
                    fontSize: "12px",
                  }}
                >
                  <span style={{ color: isAmountExceeded ? "#d32f2f" : "#555" }}>
                    The negotiated amount must be less than or equal to the current price.
                  </span>
                  <span style={{ fontWeight: "bold", color: isAmountExceeded ? "#d32f2f" : "#3554D1" }}>
                                         Current Price: ${maxAmount.toFixed(2)}
                  </span>
                </div>
              </div>

              <div
                style={{
                  marginBottom: "20px",
                  padding: "15px",
                  borderRadius: "8px",
                  border: commentError
                    ? "1px solid #f44336"
                    : "1px solid rgba(53, 84, 209, 0.1)",
                  backgroundColor: commentError
                    ? "rgba(244, 67, 54, 0.03)"
                    : "rgba(53, 84, 209, 0.03)",
                }}
              >
                <label
                  htmlFor="enquiryComment"
                  style={{
                    display: "block",
                    marginBottom: "8px",
                    fontSize: "14px",
                    fontWeight: "bold",
                    color: "#555",
                  }}
                >
                  Comment <span style={{ color: "#f44336" }}>*</span>
                </label>

                <textarea
                  id="enquiryComment"
                  value={enquiryComment}
                  onChange={(e) => {
                    setEnquiryComment(e.target.value);
                    setCommentError(false);
                  }}
                  placeholder="Enter your comment for this enquiry"
                  rows="4"
                  style={{
                    width: "100%",
                    padding: "10px",
                    borderRadius: "4px",
                    border: commentError ? "1px solid #f44336" : "1px solid #ddd",
                    resize: "vertical",
                    fontSize: "14px",
                  }}
                />

                {commentError && (
                  <p
                    style={{
                      color: "#f44336",
                      margin: "5px 0 0",
                      fontSize: "12px",
                    }}
                  >
                    Comment is required
                  </p>
                )}
              </div>

              <div
                style={{
                  display: "flex",
                  justifyContent: "flex-end",
                  gap: "10px",
                }}
              >
                <Button
                  onClick={() => submitEnquiry("cancel")}
                  variant="contained"
                  color="error"
                  startIcon={<CancelOutlinedIcon />}
                  sx={{
                    fontSize: "14px",
                  }}
                >
                  Cancel
                </Button>

                <Button
                  onClick={() => submitEnquiry("accept")}
                  variant="contained"
                  color="success"
                  startIcon={<CheckCircleOutlinedIcon />}
                  disabled={isAmountExceeded}
                  sx={{
                    fontSize: "14px",
                    opacity: isAmountExceeded ? 0.6 : 1,
                  }}
                >
                  Accept & Booking
                </Button>

                <Button
                  onClick={handleEnquirySubmit}
                  variant="contained"
                  startIcon={<QuestionAnswerIcon />}
                  disabled={isAmountExceeded}
                  sx={{
                    backgroundColor: "#3554D1",
                    "&:hover": {
                      backgroundColor: "#2a43a7",
                    },
                    opacity: isAmountExceeded ? 0.6 : 1,
                  }}
                >
                  Submit Enquiry
                </Button>
              </div>
            </>
          )}
          
          {!isAssignedToAgent && (
            <div
              style={{
                padding: "20px",
                textAlign: "center",
                backgroundColor: "#f5f7fc",
                borderRadius: "8px",
                marginTop: "15px",
              }}
            >
              <h3 style={{ color: "#3554D1", marginBottom: "10px" }}>
                Tour is not assigned to you
              </h3>
              <p style={{ color: "#666" }}>
                This enquiry is not currently assigned to you as an agent.
                You'll be able to negotiate and respond when it's assigned to you.
              </p>
            </div>
          )}
        </div>
      </div>
    </Modal>
  );
};

export default EnquiryModal; 