import React from "react";
import { useSelector } from "react-redux";
import PersonIcon from '@mui/icons-material/Person';
import Typography from '@mui/material/Typography';

const GuestSearch = () => {
  // Fetch searchParams from Redux state
  const searchParams = useSelector((state) => state.restaurants.searchParams);

  // Use searchParams to set default values for Adults and Children
  const adults = searchParams?.adults > 0 ? searchParams.adults : 1; // Ensure at least 1 adult
  const children = searchParams?.children || 0; // Default to 0 children if not available

  return (
    <div className="js-form-dd js-form-counters">
      <h4 className="text-15 fw-500 ls-2 lh-16">Number of travelers</h4>
      <div className="text-15 text-light-1 ls-2 lh-16" style={{ display: 'flex', alignItems: 'center' }}>
        <PersonIcon sx={{ mr: 1, fontSize: 16, color: "#3554D1" }} />
        <Typography sx={{ fontSize: '15px' }}>
          <span className="js-count-adult">{adults}</span> adults -{" "}
          <span className="js-count-child">{children}</span> children
        </Typography>
      </div>
    </div>
  );
};

export default GuestSearch;
