import React from "react";
import { Button, TextField, Typography } from "@mui/material";
import { Modal } from "antd";
import CategoriesAccordion from "./TourDetails";

const TourDetailsModal1 = ({
  isModalVisible,
  handleCloseModal,
  markupAmount,
  setMarkupAmount,
  discountAmount,
  setDiscountAmount,
  handlePrint,
}) => {
  return (
    <Modal
      title="Tour Details"
      open={isModalVisible}
      onCancel={handleCloseModal}
      footer={null}
      width="100vw"
      style={{
        top: 0,
        height: "100vh",
        padding: 0,
      }}
      styles={{
        body: {
          height: "calc(100vh - 55px)",
          overflowY: "auto",
          padding: 0,
        },
      }}
      centered
    >
      <div
        style={{
          display: "flex",
          alignItems: "center",
          justifyContent: "space-between",
          padding: "20px",
          borderBottom: "1px solid #e0e0e0",
          backgroundColor: "#f9f9f9",
        }}
      >
        <h2 style={{ margin: 0, color: "#2196F3" }}>Tour Details</h2>

        <div
          style={{
            display: "flex",
            alignItems: "center",
            gap: "20px",
          }}
        >
          <div
            style={{
              display: "flex",
              alignItems: "center",
              gap: "15px",
            }}
          >
            <Typography
              component="label"
              htmlFor="markup"
              sx={{
                fontSize: "15px",
                fontWeight: "500",
                color: "#424242",
                fontFamily: '"Roboto", "Helvetica", "Arial", sans-serif',
              }}
            >
              Markup Amount (SGD):
            </Typography>
            <TextField
              id="markup"
              size="small"
              variant="outlined"
              InputProps={{
                inputProps: { min: 0 },
              }}
              value={markupAmount}
              onChange={(e) => {
                const value = Number(e.target.value);
                setMarkupAmount(value >= 0 ? value : 0);
              }}
              sx={{
                width: "120px",
                "& .MuiOutlinedInput-root": {
                  "&:hover fieldset": {
                    borderColor: "#2196F3",
                  },
                  "&.Mui-focused fieldset": {
                    borderColor: "#2196F3",
                  },
                },
              }}
            />
          </div>

          <div
            style={{
              display: "flex",
              alignItems: "center",
              gap: "15px",
            }}
          >
            <Typography
              component="label"
              htmlFor="discount"
              sx={{
                fontSize: "15px",
                fontWeight: "500",
                color: "#424242",
                fontFamily: '"Roboto", "Helvetica", "Arial", sans-serif',
              }}
            >
              Discount Amount (SGD):
            </Typography>
            <TextField
              id="discount"
              size="small"
              variant="outlined"
              InputProps={{
                inputProps: { min: 0 },
              }}
              value={discountAmount}
              onChange={(e) => {
                const value = Number(e.target.value);
                setDiscountAmount(value >= 0 ? value : 0);
              }}
              sx={{
                width: "120px",
                "& .MuiOutlinedInput-root": {
                  "&:hover fieldset": {
                    borderColor: "#2196F3",
                  },
                  "&.Mui-focused fieldset": {
                    borderColor: "#2196F3",
                  },
                },
              }}
            />
          </div>

          <Button
            onClick={handlePrint}
            variant="contained"
            sx={{
              backgroundColor: "#4CAF50",
              "&:hover": {
                backgroundColor: "#45a049",
              },
            }}
          >
            Print/Download PDF
          </Button>

          {/* Only show Make an Enquiry button if this tour doesn't have a processed enquiry */}
          {/* {bookingType1 === "enquiry" && (
            <Button
              onClick={handleOpenEnquiryModal}
              variant="outlined"
              sx={{
                borderColor: "#3554D1",
                color: "#3554D1",
                "&:hover": {
                  backgroundColor: "rgba(53, 84, 209, 0.05)",
                  borderColor: "#3554D1",
                },
              }}
            >
              Make an Enquiry
            </Button>
          )} */}
        </div>
      </div>

      <CategoriesAccordion />
    </Modal>
  );
};

export default TourDetailsModal1;
