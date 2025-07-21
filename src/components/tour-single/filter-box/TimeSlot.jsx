import React, { useState, useRef, useEffect } from "react";
import { useLocation } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { useParams } from "react-router-dom";
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import Typography from '@mui/material/Typography';
// import { fetchAttractionDetails } from "@/slice/attractions/attractionSlice";

export default function TimeSlot({ setSelectedTime }) {
  const location = useLocation();
  const attraction = location.state?.attraction || {};

  // console.log("Attraction Data sk imran0025:", attraction);
   const dispatch = useDispatch();
    const { id } = useParams();
  
    // Fetch attraction details from Redux
    const attractionDetails = useSelector((state) => state.attractions.attractionDetails);
    const loading = useSelector((state) => state.attractions.loading);
    const error = useSelector((state) => state.attractions.error);
  
    // React.useEffect(() => {
    //   if (id) {
    //     dispatch(fetchAttractionDetails({ attractionId: id }));
    //   }
    // }, [dispatch, id]);
  
    // console.log("Fetched Attraction Details777:", attractionDetails);

  // Use time_slots instead of open_time and close_time
  const timeSlots = attractionDetails?.time_slots || [];
  
  // State to store selected time for display and to control dropdown
  const [selectedTimeValue, setSelectedTimeValue] = useState("");
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const dropdownRef = useRef(null);

  // Auto-select first time slot when time slots are available
  useEffect(() => {
    if (timeSlots.length > 0 && !selectedTimeValue) {
      const firstTimeSlot = timeSlots[0];
      setSelectedTimeValue(firstTimeSlot);
      setSelectedTime(firstTimeSlot);
    }
  }, [timeSlots, selectedTimeValue, setSelectedTime]);

  // Handle clicks outside to close dropdown
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
        setIsDropdownOpen(false);
      }
    };

    if (isDropdownOpen) {
      document.addEventListener('mousedown', handleClickOutside);
    }
    
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [isDropdownOpen]);

  const handleTimeChange = (time) => {
    setSelectedTimeValue(time);
    setSelectedTime(time);
    setIsDropdownOpen(false);
  };

  return (
    <div className="text-15 text-light-1 ls-2 lh-16 custom_dual_datepicker" ref={dropdownRef}>
      <div 
        onClick={() => setIsDropdownOpen(!isDropdownOpen)}
        style={{ 
          display: "flex", 
          alignItems: "center",
          backgroundColor: "rgba(255, 255, 255, 0.9)",
          borderRadius: "10px",
          boxShadow: "0 4px 10px rgba(0, 0, 0, 0.05)",
          padding: "15px 14px",
          transition: "all 0.3s ease",
          position: "relative",
          cursor: "pointer",
        }}
      >
        <AccessTimeIcon sx={{ mr: 2, fontSize: 20, color: "#3554D1" }} />
        <Typography sx={{ fontWeight: 500 }}>
          {selectedTimeValue || "Select Time"}
        </Typography>
      </div>
      
      {isDropdownOpen && (
        <div 
          className="shadow-2 dropdown-menu" 
          style={{
            position: 'absolute',
            zIndex: 1000,
            display: 'block',
            marginTop: '10px',
            animation: 'fadeIn 0.3s ease-in-out',
            right: 0,
            left: 0,
            boxShadow: '0 8px 20px rgba(0, 0, 0, 0.15)',
            borderRadius: '12px',
            backgroundColor: 'white',
            padding: '10px 0',
            maxHeight: '300px',
            overflowY: 'auto'
          }}
        >
          <div 
            className="text-15 py-10 px-15" 
            style={{ color: '#64748B', fontWeight: 600, borderBottom: '1px solid #e5e7eb' }}
          >
            Select Time
          </div>
          
          {timeSlots.length > 0 ? (
            timeSlots.map((time, index) => (
              <div
                key={index}
                onClick={() => handleTimeChange(time)}
                style={{
                  padding: '12px 15px',
                  cursor: 'pointer',
                  backgroundColor: selectedTimeValue === time ? 'rgba(53, 84, 209, 0.05)' : 'transparent',
                  color: selectedTimeValue === time ? '#3554D1' : '#1a1a1a',
                  fontWeight: selectedTimeValue === time ? 500 : 400,
                  transition: 'all 0.2s ease',
                  display: 'flex',
                  alignItems: 'center'
                }}
                onMouseOver={(e) => {
                  e.currentTarget.style.backgroundColor = 'rgba(53, 84, 209, 0.05)';
                }}
                onMouseOut={(e) => {
                  if (selectedTimeValue !== time) {
                    e.currentTarget.style.backgroundColor = 'transparent';
                  }
                }}
              >
                <AccessTimeIcon sx={{ mr: 2, fontSize: 16, color: selectedTimeValue === time ? '#3554D1' : '#64748B' }} />
                {time}
              </div>
            ))
          ) : (
            <div className="text-15 py-10 px-15" style={{ color: '#64748B', textAlign: 'center' }}>
              No time slots available
            </div>
          )}
        </div>
      )}
    </div>
  );
}
