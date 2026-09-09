import React, { useState, useRef } from "react";
import CustomerInfo from "./CustomerInfo";
import { useNavigate } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { setDateService } from "../../../slice/common/dateServicesSlice";
import {
  createBooking,
  setRestaurantsService,
} from "../../../slice/restaurant/RestaurantsSlice";
import Cookies from "js-cookie";
import { Box } from "@mui/material";
import { setUserInfo } from "../../../slice/common/customerInfo";

const Index = () => {

  
  const [validateCustomerForm, setValidateCustomerForm] = useState(
    () => () => true
  );
  const AgentId = Cookies.get("AgentId") || "0";

  const restaurantBookings = useSelector(
    (state) => state.restaurants?.restaurantBookings || []
  );
  //  console.log('restaurantBookings77777',restaurantBookings);
  

 const selectedRestaurant = useSelector(
  (state) => state.restaurants?.selectedRestaurant || []
);

  const tourdetails = useSelector((state) => state.hotels.tourdetails);
  const [formData, setFormData] = useState([{}]);

  const handleFormChange = (data) => {
    // console.log('Form data received from Restaurants:', data);

    setFormData(data);
  };

  const navigate = useNavigate();
  const dispatch = useDispatch();

  const customerInfoRef = useRef();

  
  
 
  return (
    <>
      <div className="row x-gap-40 items-center">
      <button
          className="button px-15 py-8 bg-blue-1 text-white rounded position-relative"
          style={{
            left: "-80px",
            width: "auto",
            whiteSpace: "nowrap",
            minHeight: "40px",
          }}
          onClick={() => navigate('/dashboard/db-dashboard/restaurants-details/:id')}
        >
          ← Back To Details
        </button>
        <CustomerInfo
          ref={customerInfoRef}
          onFormChange={handleFormChange}
          validateForm={setValidateCustomerForm}
        />
     
      {/* <Box
        sx={{
          position: "relative",
          top: { xs: "0px", md: "0px" },
          display: "flex",
          justifyContent: "flex-start",
          mt: 2,
        }}
      >
        <button
          className="button h-60 px-24 -dark-1 bg-blue-1 text-white"
          onClick={handleSubmit}
        >
          Submit <div className="icon-arrow-top-right ml-15" />
        </button>
      </Box> */}
      </div>
    </>
  );
};

export default Index; 