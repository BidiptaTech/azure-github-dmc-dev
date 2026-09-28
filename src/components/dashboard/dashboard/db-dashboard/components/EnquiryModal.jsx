import React, { useMemo } from "react";
import {
  Button,
  Box,
  Typography,
  Card,
  CardContent,
  TextField,
  InputAdornment,
  Chip,
  Alert,
  Stack,
  Grid,
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
import MonetizationOnIcon from "@mui/icons-material/MonetizationOn";
import PublicIcon from "@mui/icons-material/Public";

const EnquiryModal = ({
  isEnquiryModalVisible,
  handleCloseEnquiryModal,
  enquiryHistory,
  loadingEnquiryHistory,
  assigned,
  countryEnquiryAmounts = {},
  handleCountryEnquiryAmountChange,
  enquiryComment,
  setEnquiryComment,
  commentError,
  setCommentError,
  submitEnquiry,
  handleEnquirySubmit,
}) => {
  const countryRows = useMemo(() => {
    const map = {};
    (Array.isArray(enquiryHistory) ? enquiryHistory : []).forEach((item) => {
      const country = String(item?.country || "").trim();
      if (!country) return;
      const actual = parseFloat(item.actual_price);
      const current = parseFloat(item.current_price);
      const currency = String(item?.currency || "").trim() || "SGD";
      map[country] = {
        country,
        currency,
        actual_price: Number.isFinite(actual)
          ? actual
          : Number.isFinite(current)
            ? current
            : 0,
        current_price: Number.isFinite(current)
          ? current
          : Number.isFinite(actual)
            ? actual
            : 0,
      };
    });
    return Object.values(map);
  }, [enquiryHistory]);

  const formatMoney = (amount, currency = "SGD") =>
    `${currency} ${Math.ceil(parseFloat(amount) || 0).toLocaleString()}`;

  const canNegotiate = assigned === null || assigned === "Agent";

  const exceededCountries = countryRows.filter((row) => {
    const max = row.current_price || row.actual_price || 0;
    const value = parseFloat(countryEnquiryAmounts[row.country]);
    return Number.isFinite(value) && value > max;
  });
  const isAmountExceeded = exceededCountries.length > 0;

  const totalsByCurrency = useMemo(() => {
    const totals = {};
    countryRows.forEach((row) => {
      const currency = row.currency || "SGD";
      if (!totals[currency]) {
        totals[currency] = { actual: 0, current: 0, negotiated: 0 };
      }
      const negotiatedRaw = parseFloat(countryEnquiryAmounts[row.country]);
      const negotiated = Number.isFinite(negotiatedRaw)
        ? negotiatedRaw
        : row.current_price || row.actual_price || 0;
      totals[currency].actual += row.actual_price || 0;
      totals[currency].current += row.current_price || 0;
      totals[currency].negotiated += negotiated;
    });
    return totals;
  }, [countryRows, countryEnquiryAmounts]);

  const negotiatedTotalsLabel = Object.entries(totalsByCurrency)
    .map(([currency, values]) => formatMoney(values.negotiated, currency))
    .join(" · ");

  return (
    <Modal
      title={
        <Box display="flex" alignItems="center" gap={1}>
          <HistoryIcon color="primary" />
          <Typography variant="h6" component="span" sx={{ fontWeight: 600 }}>
            Country-wise Enquiry
          </Typography>
        </Box>
      }
      open={isEnquiryModalVisible}
      onCancel={handleCloseEnquiryModal}
      footer={null}
      width="980px"
      centered
      styles={{
        body: { padding: 0 },
      }}
    >
      <Box sx={{ p: 1.5 }}>
        <Card elevation={0} sx={{ mb: 1.5, border: "1px solid", borderColor: "divider" }}>
          <CardContent sx={{ pb: "12px !important" }}>
            <Box display="flex" alignItems="center" gap={1} mb={1.5}>
              <InfoIcon color="primary" />
              <Typography variant="h6" sx={{ fontWeight: 600, color: "primary.main" }}>
                Enquiry Overview
              </Typography>
            </Box>

            <Table
              dataSource={enquiryHistory}
              columns={[
                {
                  title: "Tour Id",
                  dataIndex: "tour_id",
                  key: "tour_id",
                  width: "10%",
                },
                {
                  title: "Country",
                  dataIndex: "country",
                  key: "country",
                  width: "14%",
                  render: (country) => (
                    <Chip
                      icon={<PublicIcon sx={{ fontSize: "16px !important" }} />}
                      label={country || "N/A"}
                      size="small"
                      sx={{
                        backgroundColor: "rgba(53, 84, 209, 0.08)",
                        color: "#3554D1",
                        fontWeight: 600,
                      }}
                    />
                  ),
                },
                {
                  title: "Assigned",
                  dataIndex: "assigned",
                  key: "assigned",
                  width: "10%",
                  render: (value) => value || "—",
                },
                {
                  title: "Actual Price",
                  dataIndex: "actual_price",
                  key: "actual_price",
                  width: "14%",
                  render: (price, record) => (
                    <Box display="flex" alignItems="center" gap={0.5}>
                      <MonetizationOnIcon sx={{ fontSize: 16, color: "success.main" }} />
                      <Typography variant="body2" sx={{ fontWeight: 600 }}>
                        {price != null && price !== ""
                          ? formatMoney(price, record?.currency || "SGD")
                          : "N/A"}
                      </Typography>
                    </Box>
                  ),
                },
                {
                  title: "Current Price",
                  dataIndex: "current_price",
                  key: "current_price",
                  width: "14%",
                  render: (price, record) => (
                    <Box display="flex" alignItems="center" gap={0.5}>
                      <TrendingUpIcon sx={{ fontSize: 16, color: "primary.main" }} />
                      <Typography variant="body2" sx={{ fontWeight: 600 }}>
                        {price != null && price !== ""
                          ? formatMoney(price, record?.currency || "SGD")
                          : "N/A"}
                      </Typography>
                    </Box>
                  ),
                },
                {
                  title: "Comment",
                  dataIndex: "comment",
                  key: "comment",
                  width: "18%",
                  render: (value) => value || "—",
                },
                {
                  title: "Remarks",
                  dataIndex: "remarks",
                  key: "remarks",
                  width: "12%",
                  render: (value) => value || "—",
                },
                {
                  title: "Status",
                  dataIndex: "status",
                  key: "status",
                  width: "12%",
                  render: (status) => {
                    let color = "#1890ff";
                    if (status && typeof status === "string") {
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
                      }
                    }

                    const getStatusText = (value) => {
                      if (!value) return "Open";
                      switch (String(value)) {
                        case "0":
                          return "Enquiry";
                        case "1":
                          return "Updated Enquiry";
                        case "2":
                          return "Booked";
                        case "3":
                          return "Cancelled";
                        default:
                          return value;
                      }
                    };

                    return (
                      <Chip
                        label={getStatusText(status)}
                        size="small"
                        sx={{
                          backgroundColor: `${color}15`,
                          color,
                          fontWeight: "bold",
                          border: `1px solid ${color}`,
                        }}
                      />
                    );
                  },
                },
              ]}
              loading={loadingEnquiryHistory}
              pagination={false}
              rowKey={(record, index) =>
                `${record.tour_id || "tour"}-${record.country || "country"}-${index}`
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
          </CardContent>
        </Card>

        {canNegotiate ? (
          <Card elevation={0} sx={{ border: "1px solid", borderColor: "divider", mb: 1.5 }}>
            <CardContent sx={{ p: 2, pb: "12px !important" }}>
              <Box display="flex" alignItems="center" justifyContent="space-between" mb={1.5}>
                <Box display="flex" alignItems="center" gap={1}>
                  <AttachMoneyIcon color="primary" />
                  <Typography variant="h6" sx={{ fontWeight: 600, color: "primary.main" }}>
                    Country-wise Negotiation
                  </Typography>
                </Box>
                <Chip
                  label={`Total negotiated: ${negotiatedTotalsLabel || "—"}`}
                  sx={{
                    backgroundColor: "rgba(53, 84, 209, 0.08)",
                    color: "#3554D1",
                    fontWeight: 700,
                  }}
                />
              </Box>

              <Alert severity="info" icon={<InfoIcon />} sx={{ mb: 1.5 }}>
                Negotiate each country separately. Actual/current prices come from enquiry-status.
                Comment below applies to the whole enquiry.
              </Alert>

              <Grid container spacing={1.5}>
                {countryRows.map((row) => {
                  const currency = row.currency || "SGD";
                  const maxAmount = row.current_price || row.actual_price || 0;
                  const value = countryEnquiryAmounts[row.country];
                  const numericValue = parseFloat(value);
                  const exceeded =
                    Number.isFinite(numericValue) && numericValue > maxAmount;

                  return (
                    <Grid item xs={12} md={6} key={row.country}>
                      <Box
                        sx={{
                          p: 1.5,
                          borderRadius: 2,
                          border: "1px solid",
                          borderColor: exceeded ? "error.light" : "divider",
                          backgroundColor: exceeded
                            ? "rgba(244, 67, 54, 0.04)"
                            : "rgba(248, 250, 252, 1)",
                          height: "100%",
                        }}
                      >
                        <Box display="flex" alignItems="center" gap={1} mb={1}>
                          <PublicIcon sx={{ color: "#3554D1", fontSize: 18 }} />
                          <Typography sx={{ fontWeight: 700, color: "#0f172a" }}>
                            {row.country}
                          </Typography>
                          <Chip size="small" label={currency} sx={{ ml: "auto", fontWeight: 600 }} />
                        </Box>

                        <Stack direction="row" spacing={1} mb={1.25}>
                          <Chip
                            size="small"
                            label={`Actual ${formatMoney(row.actual_price, currency)}`}
                            variant="outlined"
                          />
                          <Chip
                            size="small"
                            color="primary"
                            variant="outlined"
                            label={`Current ${formatMoney(row.current_price, currency)}`}
                          />
                        </Stack>

                        <TextField
                          label="Negotiated Amount"
                          type="number"
                          value={value ?? ""}
                          onChange={(e) => {
                            const next = e.target.value;
                            if (next === "") {
                              handleCountryEnquiryAmountChange?.(row.country, "");
                              return;
                            }
                            const parsed = parseFloat(next);
                            if (!Number.isFinite(parsed)) return;
                            if (parsed <= maxAmount) {
                              handleCountryEnquiryAmountChange?.(row.country, next);
                            }
                          }}
                          onBlur={(e) => {
                            const parsed = parseFloat(e.target.value);
                            if (!Number.isFinite(parsed)) {
                              handleCountryEnquiryAmountChange?.(
                                row.country,
                                String(Math.ceil(maxAmount))
                              );
                              return;
                            }
                            if (parsed > maxAmount) {
                              handleCountryEnquiryAmountChange?.(
                                row.country,
                                String(Math.ceil(maxAmount))
                              );
                            }
                          }}
                          InputProps={{
                            startAdornment: (
                              <InputAdornment position="start">
                                <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
                                  <MonetizationOnIcon color="primary" fontSize="small" />
                                  <Typography variant="body2" sx={{ fontWeight: 600 }}>
                                    {currency}
                                  </Typography>
                                </Box>
                              </InputAdornment>
                            ),
                          }}
                          fullWidth
                          size="small"
                          error={exceeded}
                          helperText={
                            exceeded
                              ? `Cannot exceed ${formatMoney(maxAmount, currency)}`
                              : `Max ${formatMoney(maxAmount, currency)}`
                          }
                        />
                      </Box>
                    </Grid>
                  );
                })}
              </Grid>

              {countryRows.length === 0 && (
                <Empty description="No country-wise price data available" />
              )}

              {isAmountExceeded && (
                <Alert severity="error" icon={<WarningIcon />} sx={{ mt: 1.5 }}>
                  Negotiated amount exceeds current price for:{" "}
                  {exceededCountries.map((item) => item.country).join(", ")}
                </Alert>
              )}

              <Box
                sx={{
                  mt: 1.5,
                  p: 1.25,
                  borderRadius: 1.5,
                  backgroundColor: "rgba(53, 84, 209, 0.05)",
                  display: "flex",
                  flexDirection: "column",
                  gap: 0.75,
                }}
              >
                {Object.entries(totalsByCurrency).map(([currency, values]) => (
                  <Box
                    key={currency}
                    sx={{
                      display: "flex",
                      justifyContent: "space-between",
                      gap: 2,
                      flexWrap: "wrap",
                    }}
                  >
                    <Typography variant="body2" sx={{ color: "#475569" }}>
                      Actual ({currency}): <b>{formatMoney(values.actual, currency)}</b>
                    </Typography>
                    <Typography variant="body2" sx={{ color: "#475569" }}>
                      Current ({currency}): <b>{formatMoney(values.current, currency)}</b>
                    </Typography>
                    <Typography variant="body2" sx={{ color: "#3554D1", fontWeight: 700 }}>
                      Negotiated ({currency}): {formatMoney(values.negotiated, currency)}
                    </Typography>
                  </Box>
                ))}
              </Box>
            </CardContent>
          </Card>
        ) : (
          <Card elevation={0} sx={{ border: "1px solid", borderColor: "divider", mb: 1.5 }}>
            <CardContent>
              <Typography variant="h6" sx={{ color: "#3554D1", mb: 0.5 }}>
                Tour is not assigned to you
              </Typography>
              <Typography variant="body2" sx={{ color: "#64748b" }}>
                This enquiry is not currently assigned to you as an agent. You can negotiate
                when it is assigned to you.
              </Typography>
            </CardContent>
          </Card>
        )}

        <Card elevation={0} sx={{ border: "1px solid", borderColor: "divider" }}>
          <CardContent sx={{ p: 2 }}>
            <Box display="flex" alignItems="center" gap={1} mb={1.5}>
              <CommentIcon color="primary" />
              <Typography variant="h6" sx={{ fontWeight: 600, color: "primary.main" }}>
                Common Comment
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
              placeholder="Enter one comment for this enquiry (applies to all countries)"
              multiline
              rows={3}
              fullWidth
              required
              error={commentError}
              helperText={commentError ? "Comment is required" : ""}
              sx={{ mb: 1.5 }}
            />

            {canNegotiate && (
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
                  disabled={isAmountExceeded}
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
            )}
          </CardContent>
        </Card>
      </Box>
    </Modal>
  );
};

export default EnquiryModal;
