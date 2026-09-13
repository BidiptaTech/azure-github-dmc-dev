import React from "react";
import { 
  Button, 
  Box, 
  Typography, 
  Card, 
  CardContent, 
  TextField, 
  InputAdornment,
  Chip,
  Paper,
  Divider,
  Alert,
  Stack
} from "@mui/material";
import { Modal, Table, Empty } from "antd";
import CheckCircleOutlinedIcon from "@mui/icons-material/CheckCircleOutlined";
import CancelOutlinedIcon from "@mui/icons-material/CancelOutlined";
import QuestionAnswerIcon from "@mui/icons-material/QuestionAnswer";
import AttachMoneyIcon from "@mui/icons-material/AttachMoney";
import CommentIcon from "@mui/icons-material/Comment";
import HistoryIcon from "@mui/icons-material/History";
import InfoIcon from "@mui/icons-material/Info";
import WarningIcon from "@mui/icons-material/Warning";
import TrendingUpIcon from "@mui/icons-material/TrendingUp";
import AccessTimeIcon from "@mui/icons-material/AccessTime";
import PersonIcon from "@mui/icons-material/Person";
import MonetizationOnIcon from "@mui/icons-material/MonetizationOn";

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
  // Calculate max amount and validation
  // Use current_price from enquiryHistory if available, otherwise use totalPrice
  const getCurrentPrice = () => {
    if (enquiryHistory && enquiryHistory.length > 0) {
      // Get the latest enquiry's current_price
      const latestEnquiry = enquiryHistory[enquiryHistory.length - 1];
      if (latestEnquiry.current_price) {
        return parseFloat(latestEnquiry.current_price);
      }
    }
    return totalPrice || 0;
  };

  const maxAmount = getCurrentPrice();
  const currentAmount = parseFloat(enquiryAmount) || 0;
  const isAmountExceeded = currentAmount > maxAmount;
  return (
    <Modal
      title={
        <Box display="flex" alignItems="center" gap={1}>
          <HistoryIcon color="primary" />
          <Typography variant="h6" component="span" sx={{ fontWeight: 600 }}>
            Enquiry Management
          </Typography>
        </Box>
      }
      open={isEnquiryModalVisible}
      onCancel={handleCloseEnquiryModal}
      footer={null}
      width="950px"
      centered
      styles={{
        body: { padding: 0 }
      }}
    >
      <Box sx={{ p: 1 }}>
        <Card elevation={0} sx={{ mb: 1, border: '1px solid', borderColor: 'divider' }}>
          <CardContent>
            <Box display="flex" alignItems="center" gap={1} mb={2}>
              <InfoIcon color="primary" />
              <Typography variant="h6" sx={{ fontWeight: 600, color: 'primary.main' }}>
                Enquiry History
              </Typography>
            </Box>

          <div style={{ marginBottom: "5px", marginTop: "10px" }}>
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
                  title: "Actual Price",
                  dataIndex: "actual_price",
                  key: "actual_price",
                  width: "12%",
                  render: (price) => (
                    <Box display="flex" alignItems="center" gap={0.5}>
                      <MonetizationOnIcon sx={{ fontSize: 16, color: 'success.main' }} />
                      <Typography variant="body2" sx={{ fontWeight: 600 }}>
                        {price ? `SGD${parseFloat(price).toFixed(0)}` : "N/A"}
                      </Typography>
                    </Box>
                  ),
                },
                {
                  title: "Current Price",
                  dataIndex: "current_price",
                  key: "current_price",
                  width: "12%",
                  render: (price) => (
                    <Box display="flex" alignItems="center" gap={0.5}>
                      <TrendingUpIcon sx={{ fontSize: 16, color: 'primary.main' }} />
                      <Typography variant="body2" sx={{ fontWeight: 600 }}>
                        {price ? `SGD${parseFloat(price).toFixed(0)}` : "N/A"}
                      </Typography>
                    </Box>
                  ),
                },
                {
                  title: "Comment",
                  dataIndex: "comment",
                  key: "comment",
                  width: "15%",
                  ellipsis: false,
                  render: (comment) => (
                    <Box display="flex" alignItems="center" gap={0.5}>
                      <CommentIcon sx={{ fontSize: 16, color: 'text.secondary' }} />
                      <Typography variant="body2">
                        {comment || "N/A"}
                      </Typography>
                    </Box>
                  ),
                },
                {
                  title: "Remarks",
                  dataIndex: "remarks",
                  key: "remarks",
                  width: "15%",
                  ellipsis: false,
                  render: (remarks) => (
                    <Typography variant="body2">
                      {remarks || "N/A"}
                    </Typography>
                  ),
                },
                {
                  title: "Created",
                  dataIndex: "created",
                  key: "created",
                  width: "10%",
                  render: (date) => (
                    <Box display="flex" alignItems="center" gap={0.5}>
                      <AccessTimeIcon sx={{ fontSize: 16, color: 'text.secondary' }} />
                      <Typography variant="body2">
                        {date || "N/A"}
                      </Typography>
                    </Box>
                  ),
                },
                {
                  title: "Updated",
                  dataIndex: "updated",
                  key: "updated",
                  width: "10%",
                  render: (date) => (
                    <Box display="flex" alignItems="center" gap={0.5}>
                      <AccessTimeIcon sx={{ fontSize: 16, color: 'text.secondary' }} />
                      <Typography variant="body2">
                        {date || "N/A"}
                      </Typography>
                    </Box>
                  ),
                },
                {
                  title: "Pending",
                  dataIndex: "pending_days",
                  key: "pending_days",
                  width: "8%",
                  render: (days) => (
                    <Chip 
                      label={days || "N/A"} 
                      size="small" 
                      color={days > 7 ? "error" : days > 3 ? "warning" : "success"}
                      variant="outlined"
                    />
                  ),
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
                      <Chip
                        label={getStatusText(status)}
                        size="small"
                        sx={{
                          backgroundColor: color ? `${color}15` : "rgba(24, 144, 255, 0.1)",
                          color: color || "#1890ff",
                          fontWeight: "bold",
                          border: `1px solid ${color || "#1890ff"}`,
                        }}
                      />
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
          </CardContent>
        </Card>

        {/* Show form ONLY when assigned is null or exactly "Agent". Hide for all other values  */}
        {(assigned === null || assigned === "Agent") && (
          <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider' }}>
            <CardContent sx={{ p: 2 }}>
              <Box display="flex" alignItems="center" gap={1} mb={1.5}>
                <AttachMoneyIcon color="primary" />
                <Typography variant="h6" sx={{ fontWeight: 600, color: 'primary.main' }}>
                  Negotiate Amount
                </Typography>
              </Box>
              <TextField
                label="Negotiated Amount"
                id="enquiryAmount"
                type="number"
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
                InputProps={{
                  startAdornment: (
                    <InputAdornment position="start">
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                        <MonetizationOnIcon color="primary" />
                        <Typography variant="body2" sx={{ fontWeight: 600 }}>
                          SGD
                        </Typography>
                      </Box>
                    </InputAdornment>
                  ),
                }}
                fullWidth
                error={isAmountExceeded}
                helperText={isAmountExceeded ? `Amount cannot exceed SGD ${maxAmount.toFixed(2)}` : `Maximum: SGD ${maxAmount.toFixed(2)}`}
                sx={{ mb: 0.5 }}
              />

              {isAmountExceeded && (
                <Alert 
                  severity="error" 
                  icon={<WarningIcon />}
                  sx={{ mb: 0.5 }}
                >
                  Negotiated amount cannot exceed the current price of SGD {maxAmount.toFixed(2)}
                </Alert>
              )}

              <Alert 
                severity="info" 
                icon={<InfoIcon />}
                sx={{ mb: 0.5 }}
              >
                <Box display="flex" justifyContent="space-between" alignItems="center">
                  <Typography variant="body2">
                    The negotiated amount must be less than or equal to the current price.
                  </Typography>
                  <Typography variant="body2" sx={{ fontWeight: 600, color: 'primary.main' }}>
                    Current Price: SGD {maxAmount.toFixed(2)}
                  </Typography>
                </Box>
              </Alert>
            </CardContent>
          </Card>
        )}

        {/* Comment Section */}
          <Card elevation={0} sx={{ border: '1px solid', borderColor: 'divider', mt: 1.5 }}>
            <CardContent sx={{ p: 2 }}>
              <Box display="flex" alignItems="center" gap={1} mb={1.5}>
                <CommentIcon color="primary" />
                <Typography variant="h6" sx={{ fontWeight: 600, color: 'primary.main' }}>
                  Add Comment
                </Typography>
              </Box>

              <TextField
                label="Comment"
                id="enquiryComment"
                value={enquiryComment}
                onChange={(e) => {
                  setEnquiryComment(e.target.value);
                  setCommentError(false);
                }}
                placeholder="Enter your comment for this enquiry"
                multiline
                rows={3}
                fullWidth
                required
                error={commentError}
                helperText={commentError ? "Comment is required" : ""}
                sx={{ mb: 1.5 }}
              />
   

              <Stack direction="row" spacing={1.5} justifyContent="flex-end">
                <Button
                  onClick={() => submitEnquiry("cancel")}
                  variant="contained"
                  color="error"
                  startIcon={<CancelOutlinedIcon />}
                  size="medium"
                >
                  Cancel
                </Button>

                <Button
                  onClick={() => submitEnquiry("accept")}
                  variant="contained"
                  color="success"
                  startIcon={<CheckCircleOutlinedIcon />}
                  disabled={isAmountExceeded || (enquiryAmount && enquiryAmount > 0)}
                  size="medium"
                >
                  Accept & Booking
                </Button>

                <Button
                  onClick={handleEnquirySubmit}
                  variant="contained"
                  startIcon={<QuestionAnswerIcon />}
                  disabled={isAmountExceeded}
                  size="medium"
                  sx={{
                    backgroundColor: "#3554D1",
                    "&:hover": {
                      backgroundColor: "#2a43a7",
                    },
                  }}
                >
                  Submit Enquiry
                </Button>
              </Stack>
    

            </CardContent>
          </Card>
      </Box>
    </Modal>
  );
};

export default EnquiryModal;
