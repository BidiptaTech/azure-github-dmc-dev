import React, { useState } from "react";
import CustomerInfo from "./CustomerInfo";
import { useNavigate } from "react-router-dom";


const Index = () => {
  const navigate = useNavigate();
  const [validateCustomerForm, setValidateCustomerForm] = useState(
    () => () => true
  );
  const [formData, setFormData] = useState({});

  const handleFormChange = (data) => {
    // console.log('Form data received:', data);
    setFormData(data);
  };

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
          onClick={() => navigate('/dashboard/db-dashboard/tour-single/:id')}
        >
          ← Back To Details
        </button>

        <CustomerInfo
          onFormChange={handleFormChange}
          validateForm={setValidateCustomerForm}
        />
      </div>
    </>
  );
};

export default Index;
