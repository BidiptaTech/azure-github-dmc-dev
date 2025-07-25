import React from "react";
import { Button } from "@mui/material";
import { Modal, Table, Empty } from "antd";
import CheckCircleOutlinedIcon from "@mui/icons-material/CheckCircleOutlined";
import CancelOutlinedIcon from "@mui/icons-material/CancelOutlined";
import QuestionAnswerIcon from "@mui/icons-material/QuestionAnswer";

const EnquiryModal = ({
  isEnquiryModalVisible,
  handleCloseEnquiryModal,
  enquiryHistory,
  loadingEnquiryHistory,
  assigned,
  enquiryAmount,
  handleEnquiryAmountChange,
  totalPrice,
  enquiryComment,
  setEnquiryComment,
  commentError,
  setCommentError,
  submitEnquiry,
  handleEnquirySubmit,
}) => {
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
                  render: (price) =>
                    price ? `SGD${parseFloat(price).toFixed(0)}` : "N/A",
                },
                {
                  title: "Current Price",
                  dataIndex: "current_price",
                  key: "current_price",
                  width: "12%",
                  render: (price) =>
                    price ? `SGD${parseFloat(price).toFixed(0)}` : "N/A",
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
                  title: "Status",
                  dataIndex: "status",
                  key: "status",
                  width: "10%",
                  render: (status) => {
                    let color = "";
                    // Map different statuses to colors
                    if (status && typeof status === "string") {
                      // Default color handling for various statuses
                      if (
                        status === "0" ||
                        status.toLowerCase().includes("pending") ||
                        status.toLowerCase().includes("enquiry")
                      ) {
                        color = "#faad14";
                      } else if (
                        status === "1" ||
                        status.toLowerCase().includes("updated") ||
                        status.toLowerCase().includes("accept")
                      ) {
                        color = "#1890ff";
                      } else if (
                        status === "2" ||
                        status.toLowerCase().includes("booked")
                      ) {
                        color = "#52c41a";
                      } else if (
                        status === "3" ||
                        status.toLowerCase().includes("reject") ||
                        status.toLowerCase().includes("cancel")
                      ) {
                        color = "#f5222d";
                      } else {
                        color = "#1890ff";
                      }
                    }

                    // Convert numeric status to human-readable text
                    const getStatusText = (status) => {
                      if (!status) return "Unknown";

                      switch (status.toString()) {
                        case "0":
                          return "Enquiry";
                        case "1":
                          return "Updated Enquiry";
                        case "2":
                          return "Booked";
                        case "3":
                          return "Cancelled";
                        default:
                          return status;
                      }
                    };

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
                        {getStatusText(status)}
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

          {/* Show form ONLY when assigned is null or exactly "Agent". Hide for all other values  */}
          {(assigned === null || assigned === "Agent") && (
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
                      SGD
                    </div>
                    <input
                      id="enquiryAmount"
                      type="number"
                      min="0"
                      value={enquiryAmount || totalPrice}
                      onChange={handleEnquiryAmountChange}
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
                      }}
                    />
                  </div>
                </div>

                {/* Message showing max amount */}
                <div
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    marginTop: "8px",
                    padding: "6px 10px",
                    backgroundColor: "rgba(53, 84, 209, 0.08)",
                    borderRadius: "4px",
                    fontSize: "12px",
                  }}
                >
                  <span style={{ color: "#555" }}>
                    The negotiated amount must be less than or equal to the base
                    price.
                  </span>
                  <span style={{ fontWeight: "bold", color: "#3554D1" }}>
                    Max: SGD {totalPrice}
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
                    border: commentError
                      ? "1px solid #f44336"
                      : "1px solid #ddd",
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
                  sx={{
                    fontSize: "14px",
                  }}
                >
                  Accept & Booking
                </Button>

                <Button
                  onClick={handleEnquirySubmit}
                  variant="contained"
                  startIcon={<QuestionAnswerIcon />}
                  sx={{
                    backgroundColor: "#3554D1",
                    "&:hover": {
                      backgroundColor: "#2a43a7",
                    },
                  }}
                >
                  Submit Enquiry
                </Button>
              </div>
            </>
          )}
        </div>
      </div>
    </Modal>
  );
};

export default EnquiryModal;
